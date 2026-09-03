@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
<x-auth-split image="swahili-login-banner.jpg" imageAlt="Learn Swahili — Karibu">
    <div class="login-logo login-info">
        <a href="{{ url('/') }}">
            <img src="{{ template_asset('img/logo.svg') }}" alt="{{ config('app.name') }}">
        </a>
    </div>

    <div class="login-userheading">
        <h3>Sign In</h3>
        <h4>Access your Swahili learning dashboard — Karibu!</h4>
    </div>

    @if(session('status'))
        <div class="alert alert-success mb-3">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-login">
            <label>Email Address</label>
            <div class="form-addons">
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Enter your email"
                    class="@error('email') is-invalid @enderror"
                    required
                    autofocus
                >
            </div>
            @error('email')
                <span class="auth-field-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-login">
            <label>Password</label>
            <div class="pass-group">
                <input type="password" name="password" class="pass-input" placeholder="Enter your password" required>
                <span class="ti ti-eye-off toggle-password"></span>
            </div>
        </div>

        <div class="form-login authentication-check">
            <div class="row">
                <div class="col-6">
                    <div class="custom-control custom-checkbox">
                        <label class="checkboxs mb-0" for="remember">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span class="checkmarks"></span>
                            Remember me
                        </label>
                    </div>
                </div>
                <div class="col-6 text-end"><a href="{{ route('password.request') }}">Forgot password?</a></div>
            </div>
        </div>

        <div class="form-login">
            <button type="submit" class="btn btn-login">Sign In</button>
        </div>
    </form>

    <div class="signinform text-center">
        <h4>Don't have an account? <a href="{{ route('register') }}">Sign Up</a></h4>
    </div>

</x-auth-split>
@endsection
