<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Live smoke against the real MySQL database and configured AI keys.
 * Does not wipe data.
 */
class LiveAllRolesSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // phpunit.xml forces sqlite/:memory: — point this live suite at MySQL from .env
        $env = $this->readProjectEnv([
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'swahili_lms',
            'DB_USERNAME' => 'root',
            'DB_PASSWORD' => '',
        ]);

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $env['DB_HOST'],
            'database.connections.mysql.port' => $env['DB_PORT'],
            'database.connections.mysql.database' => $env['DB_DATABASE'],
            'database.connections.mysql.username' => $env['DB_USERNAME'],
            'database.connections.mysql.password' => $env['DB_PASSWORD'],
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    /**
     * @param  array<string, string>  $defaults
     * @return array<string, string>
     */
    private function readProjectEnv(array $defaults): array
    {
        $path = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'.env';
        if (! is_file($path)) {
            return $defaults;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            if (! array_key_exists($key, $defaults)) {
                continue;
            }
            $value = trim($value);
            if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                $value = substr($value, 1, -1);
            }
            $defaults[$key] = $value;
        }

        return $defaults;
    }

    public function test_mysql_is_reachable(): void
    {
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertGreaterThan(0, User::count());
    }

    public function test_admin_pages_and_access_rules(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->whereIn('role', ['super_admin', 'admin'])->firstOrFail();

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/enrollments')->assertOk();
        $this->actingAs($admin)->get('/admin/progress')->assertOk();
        $this->actingAs($admin)->get('/courses')->assertOk();
        $course = \App\Models\Course::firstOrFail();
        $this->actingAs($admin)->get('/courses/'.$course->id.'/words')->assertOk();
        $this->actingAs($admin)->get('/courses/'.$course->id.'/lessons')->assertRedirect('/courses/'.$course->id.'/words');
        $this->actingAs($admin)->get('/profile')->assertOk();
        $this->actingAs($admin)->get('/student/dashboard')->assertRedirect('/admin/dashboard');
    }

    public function test_super_admin_is_protected_and_transfer_is_exclusive(): void
    {
        DB::beginTransaction();

        try {
            $owner = User::where('role', 'super_admin')->firstOrFail();
            $owner->update(['password' => Hash::make('TransferTest123!'), 'status' => 'active']);
            $newOwner = User::create([
                'name' => 'Temporary Transfer Admin',
                'email' => 'temporary-transfer-admin@example.test',
                'password' => Hash::make('TemporaryAdmin123!'),
                'role' => 'admin',
                'status' => 'active',
                'learning_level' => 'beginner',
            ]);

            $this->actingAs($owner)
                ->delete(route('admin.users.destroy', $owner))
                ->assertSessionHas('error');
            $this->assertDatabaseHas('users', ['id' => $owner->id, 'role' => 'super_admin']);

            $this->actingAs($owner)->put(route('admin.users.update', $owner), [
                'name' => $owner->name,
                'email' => $owner->email,
                'role' => 'admin',
                'status' => 'inactive',
                'password' => '',
                'password_confirmation' => '',
            ])->assertRedirect(route('admin.users.index'));
            $this->assertDatabaseHas('users', ['id' => $owner->id, 'role' => 'super_admin', 'status' => 'active']);

            $this->actingAs($owner)->post(route('admin.users.transfer-super-admin', $newOwner), [
                'current_password' => 'TransferTest123!',
                'confirm_transfer' => '1',
            ])->assertRedirect(route('admin.dashboard'));

            $this->assertDatabaseHas('users', ['id' => $owner->id, 'role' => 'admin', 'status' => 'active']);
            $this->assertDatabaseHas('users', ['id' => $newOwner->id, 'role' => 'super_admin', 'status' => 'active']);

            $formerOwner = $owner->fresh();
            $this->actingAs($formerOwner)
                ->get(route('admin.users.index'))
                ->assertRedirect(route('admin.dashboard'));
            $this->actingAs($formerOwner)
                ->delete(route('admin.users.destroy', $newOwner))
                ->assertRedirect(route('admin.dashboard'));
            $this->assertDatabaseHas('users', ['id' => $newOwner->id, 'role' => 'super_admin']);
        } finally {
            DB::rollBack();
        }
    }

    public function test_admin_is_created_active_and_must_change_temporary_password(): void
    {
        DB::beginTransaction();

        try {
            $owner = User::where('role', 'super_admin')->firstOrFail();
            $this->actingAs($owner)->post(route('admin.users.store'), [
                'name' => 'New Test Admin',
                'email' => 'invited-admin@example.test',
                'role' => 'admin',
                'status' => 'active',
                'password' => 'TemporaryAdmin123!',
                'password_confirmation' => 'TemporaryAdmin123!',
            ])->assertRedirect(route('admin.users.index'));

            $invited = User::where('email', 'invited-admin@example.test')->firstOrFail();
            $this->assertSame('active', $invited->status);
            $this->assertTrue($invited->must_change_password);
            $this->assertNull($invited->invitation_token_hash);

            Auth::logout();
            $this->post('/login', [
                'email' => $invited->email,
                'password' => 'TemporaryAdmin123!',
            ])->assertRedirect(route('password.force.form'));
            $this->get(route('admin.dashboard'))->assertRedirect(route('password.force.form'));

            $this->put(route('password.force.update'), [
                'current_password' => 'TemporaryAdmin123!',
                'password' => 'NewPrivateAdmin123!',
                'password_confirmation' => 'NewPrivateAdmin123!',
            ])->assertRedirect(route('admin.dashboard'));

            $this->assertFalse($invited->fresh()->must_change_password);
            $this->get(route('admin.dashboard'))->assertOk();
        } finally {
            DB::rollBack();
        }
    }

    public function test_existing_student_can_be_changed_to_administrator(): void
    {
        DB::beginTransaction();

        try {
            $owner = User::where('role', 'super_admin')->firstOrFail();
            $student = User::create([
                'name' => 'Existing Student',
                'email' => 'promote-existing-student@example.test',
                'password' => 'OldStudent123!',
                'role' => 'student',
                'status' => 'active',
                'learning_level' => 'beginner',
            ]);

            $this->actingAs($owner)->post(route('admin.users.store'), [
                'name' => 'Promoted Administrator',
                'email' => $student->email,
                'role' => 'admin',
                'status' => 'active',
                'password' => 'TemporaryAdmin456!',
                'password_confirmation' => 'TemporaryAdmin456!',
            ])->assertRedirect(route('admin.users.index'));

            $promoted = $student->fresh();
            $this->assertSame($student->id, $promoted->id);
            $this->assertSame('admin', $promoted->role);
            $this->assertSame('active', $promoted->status);
            $this->assertTrue($promoted->must_change_password);
            $this->assertTrue(Hash::check('TemporaryAdmin456!', $promoted->password));
        } finally {
            DB::rollBack();
        }
    }

    public function test_student_pages_and_access_rules(): void
    {
        $student = User::where('email', 'student@gmail.com')->where('role', 'student')->firstOrFail();

        $this->actingAs($student)->get('/student/dashboard')->assertOk();
        $this->actingAs($student)->get('/student/courses')->assertOk();
        $this->actingAs($student)->get('/student/courses/available')->assertOk();
        $this->actingAs($student)->get('/student/progress')->assertOk();
        $this->actingAs($student)->get('/student/ai-tutor')->assertOk();
        $this->actingAs($student)->get('/profile')->assertOk();
        $this->actingAs($student)->get('/admin/dashboard')->assertRedirect('/student/dashboard');
    }

    public function test_student_ai_tutor_returns_learning_cards_without_extra_tables(): void
    {
        $student = User::where('email', 'student@gmail.com')->where('role', 'student')->firstOrFail();
        config(['services.openai.key' => 'test-key']);
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [['message' => ['content' => json_encode([
                    'swahili' => 'Habari za asubuhi',
                    'meaning' => 'Good morning',
                    'pronunciation' => 'ha-BA-ri za a-su-BU-hi',
                    'example_swahili' => 'Habari za asubuhi, rafiki yangu.',
                    'example_meaning' => 'Good morning, my friend.',
                    'note' => 'A polite morning greeting.',
                ])]]],
                'usage' => ['total_tokens' => 80],
            ]),
        ]);

        $this->actingAs($student)
            ->postJson('/student/ai-tutor/message', ['message' => 'Good morning'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('details.swahili', 'Habari za asubuhi')
            ->assertJsonPath('details.pronunciation', 'ha-BA-ri za a-su-BU-hi')
            ->assertJsonPath('speak_text', 'Habari za asubuhi');
    }

    public function test_guest_public_pages(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/forgot-password')->assertOk();
    }
}
