@extends('layouts.guest')
@section('title', 'Verify Email')
@section('content')
<x-auth-split image="swahili-login-banner.jpg" imageAlt="Email verification">
<div class="login-userheading"><h3>Enter OTP</h3><h4>We sent a 6-digit security code to {{ \Illuminate\Support\Str::mask($email, '*', 2, max(1, strpos($email,'@')-3)) }}</h4></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<form method="POST" action="{{ route('password.otp.verify') }}">@csrf
<div class="form-login"><label>6-digit OTP</label><input name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" class="form-control text-center fs-3 @error('otp') is-invalid @enderror" autocomplete="one-time-code" required autofocus>@error('otp')<span class="auth-field-error">{{ $message }}</span>@enderror</div>
<button class="btn btn-login w-100">Verify OTP</button></form>
<form method="POST" action="{{ route('password.otp.resend') }}" class="text-center mt-3">@csrf<button class="btn btn-link">Resend OTP</button></form>
</x-auth-split>
@endsection
