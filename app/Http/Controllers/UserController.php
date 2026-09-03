<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->whereIn('role', ['super_admin', 'admin']);

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $existingUser = User::where('email', $request->validated('email'))->first();

        if ($existingUser && $existingUser->isAdmin()) {
            throw ValidationException::withMessages([
                'email' => 'This person is already an Administrator.',
            ]);
        }

        $attributes = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'country' => $request->validated('country') ?? $existingUser?->country,
            'learning_level' => $existingUser?->learning_level ?? 'beginner',
            'role' => 'admin',
            'status' => 'active',
            'password' => $request->validated('password'),
            'email_verified_at' => now(),
            'must_change_password' => true,
            'invitation_token_hash' => null,
            'invitation_expires_at' => null,
            'invitation_accepted_at' => null,
        ];

        $promotedFromStudent = (bool) $existingUser;
        $user = $existingUser ?? new User();
        $user->forceFill($attributes)->save();

        Log::info('Administrator account created', [
            'administrator_id' => $user->id,
            'email' => $user->email,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', $promotedFromStudent
                ? $user->name.' was changed from Student to an active Administrator. They must use the temporary password and change it at first sign-in.'
                : $user->name.' was created as an active Administrator. Give them the temporary password securely; they must change it at first sign-in.');
    }

    public function edit(User $user)
    {
        $this->ensureStaff($user);

        return view('admin.users.edit', compact('user'));
    }

    public function update(StoreUserRequest $request, User $user)
    {
        $this->ensureStaff($user);

        $data = $request->validated();

        if ($user->isSuperAdmin()) {
            $data['role'] = 'super_admin';
            $data['status'] = 'active';
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->ensureStaff($user);

        if ($user->id === auth()->id() || $user->isSuperAdmin()) {
            return back()->with('error', 'The Super Admin account is protected and cannot be deleted.');
        }

        Log::warning('Administrator account deleted', [
            'administrator_id' => $user->id,
            'email' => $user->email,
            'deleted_by' => auth()->id(),
        ]);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    private function ensureStaff(User $user): void
    {
        abort_unless(in_array($user->role, ['super_admin', 'admin'], true), 404);
    }

    public function promoteToAdmin(Request $request, User $student)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
        abort_unless($student->role === 'student', 404);

        $student->update(['role' => 'admin', 'status' => 'active']);

        return redirect()->route('admin.users.index')
            ->with('success', $student->name.' is now an Administrator.');
    }

    public function transferSuperAdmin(Request $request, User $user)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
        $this->ensureStaff($user);

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'confirm_transfer' => ['accepted'],
        ], [
            'current_password.current_password' => 'Your password is incorrect.',
            'confirm_transfer.accepted' => 'You must confirm that you understand this transfer.',
        ]);

        if ($user->id === $request->user()->id || $user->role !== 'admin' || $user->status !== 'active') {
            throw ValidationException::withMessages([
                'transfer' => 'Choose a different active Administrator for this transfer.',
            ]);
        }

        DB::transaction(function () use ($request, $user) {
            $currentOwner = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            $newOwner = User::query()->lockForUpdate()->findOrFail($user->id);

            if (! $currentOwner->isSuperAdmin() || $newOwner->role !== 'admin' || $newOwner->status !== 'active') {
                throw ValidationException::withMessages([
                    'transfer' => 'The transfer could not be completed. Refresh the page and try again.',
                ]);
            }

            $currentOwner->update(['role' => 'admin', 'status' => 'active']);
            $newOwner->update(['role' => 'super_admin', 'status' => 'active']);
        });

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')
            ->with('success', $user->name.' is now the Super Admin. Your account is now a regular Administrator.');
    }
}
