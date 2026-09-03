@extends('layouts.guest')
@section('title', 'Create New Password')
@section('content')
<x-auth-split image="swahili-login-banner.jpg" imageAlt="Reset password">
<div class="login-userheading"><h3>Create New Password</h3><h4>Your OTP was verified. Choose a strong new password.</h4></div>
<form method="POST" action="{{ route('password.update') }}">@csrf
<div class="form-login"><label>New Password</label><input type="password" name="password" required>@error('password')<span class="auth-field-error">{{ $message }}</span>@enderror</div>
<div class="form-login"><label>Confirm Password</label><input type="password" name="password_confirmation" required></div>
<button class="btn btn-login w-100">Change Password</button></form>
</x-auth-split>
@endsection
