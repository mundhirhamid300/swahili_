<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordResetController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    public function requestForm() { return view('auth.forgot-password'); }

    public function sendOtp(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $user = User::where('email', $data['email'])->first();
        if ($user) $this->otpService->send('password-reset', $user->email);
        $request->session()->put('password_reset_email', strtolower($data['email']));

        return redirect()->route('password.otp')->with('status', 'If that email exists, a 6-digit OTP has been sent.');
    }

    public function otpForm(Request $request)
    {
        abort_unless($request->session()->has('password_reset_email'), 419);
        return view('auth.verify-otp', ['email' => $request->session()->get('password_reset_email'), 'purpose' => 'password-reset']);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate(['otp' => ['required', 'digits:6']]);
        $email = $request->session()->get('password_reset_email');
        abort_unless($email, 419);
        if (! $this->otpService->verify('password-reset', $email, $data['otp'])) return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        $request->session()->put('password_reset_verified', true);

        return redirect()->route('password.reset');
    }

    public function resendOtp(Request $request)
    {
        $email = $request->session()->get('password_reset_email');
        abort_unless($email, 419);
        if (User::where('email', $email)->exists()) $this->otpService->send('password-reset', $email);

        return back()->with('status', 'If that email exists, a new OTP was sent.');
    }

    public function resetForm(Request $request)
    {
        abort_unless($request->session()->get('password_reset_verified'), 419);
        return view('auth.reset-password');
    }

    public function reset(Request $request)
    {
        abort_unless($request->session()->get('password_reset_verified'), 419);
        $data = $request->validate(['password' => ['required', 'confirmed', Password::defaults()]]);
        $user = User::where('email', $request->session()->get('password_reset_email'))->firstOrFail();
        $user->update(['password' => Hash::make($data['password']), 'must_change_password' => false]);
        $request->session()->forget(['password_reset_email', 'password_reset_verified']);

        return redirect()->route('login')->with('status', 'Password changed successfully. You can now sign in.');
    }

    public function forceForm()
    {
        abort_unless(auth()->user()?->must_change_password, 404);

        return view('auth.force-password-change');
    }

    public function forceUpdate(Request $request)
    {
        abort_unless($request->user()?->must_change_password, 404);
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'different:current_password', 'confirmed', Password::defaults()],
        ], [
            'current_password.current_password' => 'The temporary password is incorrect.',
            'password.different' => 'Your new password must be different from the temporary password.',
        ]);

        $request->user()->update([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
        ]);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('success', 'Your new password has been saved.');
    }
}
