@extends('layouts.app')

@section('title', 'Confirm Password Change')
@section('subtitle', 'Enter the security code sent to your email')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <x-page-card title="Email Confirmation">
            <p>We sent a 6-digit OTP to <strong>{{ \Illuminate\Support\Str::mask($email, '*', 2, max(1, strpos($email, '@') - 3)) }}</strong>.</p>
            <p class="text-muted">Your password has not changed yet. The code expires in 10 minutes.</p>
            @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
            <form method="POST" action="{{ route('profile.password.otp.verify') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">6-digit OTP</label>
                    <input name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" class="form-control text-center fs-3 @error('otp') is-invalid @enderror" required autofocus>
                    @error('otp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-primary w-100">Confirm & Change Password</button>
            </form>
            <form method="POST" action="{{ route('profile.password.otp.resend') }}" class="text-center mt-3">
                @csrf
                <button class="btn btn-link">Resend OTP</button>
            </form>
            <a href="{{ route('profile') }}" class="btn btn-outline-secondary w-100 mt-2">Back to Profile</a>
        </x-page-card>
    </div>
</div>
@endsection
