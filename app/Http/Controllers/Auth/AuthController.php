<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LevelEnrollmentService;
use App\Services\OtpService;
use App\Support\Countries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function __construct(
        private LevelEnrollmentService $levelEnrollmentService,
        private OtpService $otpService
    ) {}

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        $user = Auth::user();
        if ($user->status === 'pending') {
            Auth::logout();

            return back()->withErrors([
                'email' => $user->role === 'admin'
                    ? 'Confirm the invitation link sent to your email before signing in.'
                    : 'Your account is not active yet.',
            ])->onlyInput('email');
        }

        if ($user->status !== 'active') {
            Auth::logout();

            return back()->withErrors(['email' => 'Your account has been deactivated.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        if ($user->must_change_password) {
            return redirect()->route('password.force.form');
        }

        return redirect()->intended($this->dashboardRoute($user));
    }

    public function showRegister()
    {
        return view('auth.register', ['countries' => Countries::all()]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'country' => ['required', 'string', Rule::in(array_keys(Countries::all()))],
            'learning_level' => ['nullable', 'in:beginner,intermediate'],
        ]);

        $request->session()->put('pending_registration', [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'country' => $data['country'],
            'learning_level' => $data['learning_level'] ?? 'beginner',
        ]);

        $this->otpService->send('registration', $data['email']);

        return redirect()->route('register.otp')->with('status', 'We sent a 6-digit OTP to '.$data['email'].'.');
    }

    public function showRegistrationOtp(Request $request)
    {
        abort_unless($request->session()->has('pending_registration'), 419);

        return view('auth.verify-otp', [
            'email' => $request->session()->get('pending_registration.email'),
            'purpose' => 'registration',
        ]);
    }

    public function verifyRegistrationOtp(Request $request)
    {
        $data = $request->validate(['otp' => ['required', 'digits:6']]);
        $pending = $request->session()->get('pending_registration');
        abort_unless($pending, 419);

        if (! $this->otpService->verify('registration', $pending['email'], $data['otp'])) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        $user = User::create($pending + ['role' => 'student', 'status' => 'active', 'email_verified_at' => now()]);

        $this->levelEnrollmentService->enroll($user, $user->learning_level);
        $request->session()->forget('pending_registration');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('student.dashboard')
            ->with('success', 'Account created successfully. Welcome to Swahili Learning!');
    }

    public function resendRegistrationOtp(Request $request)
    {
        $email = $request->session()->get('pending_registration.email');
        abort_unless($email, 419);
        $this->otpService->send('registration', $email);

        return back()->with('status', 'A new OTP was sent.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showProfile()
    {
        return view('profile.edit', ['user' => auth()->user(), 'countries' => Countries::all()]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', Rule::in(array_keys(Countries::all()))],
            'learning_level' => ['nullable', 'in:beginner,intermediate'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        $user->fill([
            'name' => $data['name'],
            'country' => $data['country'] ?? null,
            'learning_level' => $data['learning_level'] ?? $user->learning_level,
        ]);

        if ($request->boolean('remove_avatar') && $user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
        }

        if ($request->hasFile('avatar')) {
            $oldAvatar = $user->avatar;
            $user->avatar = $request->file('avatar')->store('avatars', 'public');

            if ($oldAvatar) {
                Storage::disk('public')->delete($oldAvatar);
            }
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    public function sendPasswordChangeOtp(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'different:current_password', 'confirmed', PasswordRule::defaults()],
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
            'password.different' => 'The new password must be different from your current password.',
        ]);

        $request->session()->put('pending_profile_password', [
            'user_id' => $request->user()->id,
            'password_hash' => Hash::make($data['password']),
            'created_at' => now()->timestamp,
        ]);

        $this->otpService->send('password-change', $request->user()->email);

        return redirect()->route('profile.password.otp')
            ->with('status', 'A 6-digit confirmation code was sent to your email.');
    }

    public function showPasswordChangeOtp(Request $request)
    {
        $this->ensurePendingPasswordChange($request);

        return view('profile.verify-password-otp', ['email' => $request->user()->email]);
    }

    public function verifyPasswordChangeOtp(Request $request)
    {
        $pending = $this->ensurePendingPasswordChange($request);
        $data = $request->validate(['otp' => ['required', 'digits:6']]);

        if (! $this->otpService->verify('password-change', $request->user()->email, $data['otp'])) {
            return back()->withErrors(['otp' => 'The code is incorrect or has expired.']);
        }

        $request->user()->forceFill(['password' => $pending['password_hash']])->save();
        $request->session()->forget('pending_profile_password');
        $request->session()->regenerate();

        return redirect()->route('profile')->with('success', 'Your password was changed securely.');
    }

    public function resendPasswordChangeOtp(Request $request)
    {
        $this->ensurePendingPasswordChange($request);
        $this->otpService->send('password-change', $request->user()->email);

        return back()->with('status', 'A new confirmation code was sent to your email.');
    }

    private function ensurePendingPasswordChange(Request $request): array
    {
        $pending = $request->session()->get('pending_profile_password');

        abort_unless(
            is_array($pending)
            && ($pending['user_id'] ?? null) === $request->user()->id
            && isset($pending['password_hash'], $pending['created_at'])
            && (int) $pending['created_at'] >= now()->subMinutes(15)->timestamp,
            419
        );

        return $pending;
    }

    private function dashboardRoute(User $user): string
    {
        return match ($user->role) {
            'super_admin', 'admin' => route('admin.dashboard'),
            default => route('student.dashboard'),
        };
    }
}
