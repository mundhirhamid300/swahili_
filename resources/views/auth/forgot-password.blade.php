@extends('layouts.guest')
@section('title', 'Forgot Password')
@section('content')
<x-auth-split image="swahili-login-banner.jpg" imageAlt="Password recovery">
<div class="login-userheading"><h3>Forgot Password?</h3><h4>Enter your email and we will send a secure OTP.</h4></div>
<form method="POST" action="{{ route('password.email') }}">@csrf<div class="form-login"><label>Email Address</label><input type="email" name="email" value="{{ old('email') }}" required autofocus>@error('email')<span class="auth-field-error">{{ $message }}</span>@enderror</div><button class="btn btn-login w-100">Send OTP</button></form>
<div class="signinform text-center"><h4><a href="{{ route('login') }}">Back to Sign In</a></h4></div>
</x-auth-split>
@endsection
