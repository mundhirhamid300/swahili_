@extends('layouts.guest')

@section('title', 'Sign Up')

@section('content')
<x-auth-split image="swahili-register-banner.jpg" imageAlt="Join Swahili Learning" :dense="true">
    <div class="login-logo login-info">
        <a href="{{ url('/') }}">
            <img src="{{ template_asset('img/logo.svg') }}" alt="{{ config('app.name') }}">
        </a>
    </div>

    <div class="login-userheading">
        <h3>Create Account</h3>
        <h4>Join foreigners learning Kiswahili from scratch</h4>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-login">
            <label>Full Name</label>
            <div class="form-addons">
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Enter your full name" class="@error('name') is-invalid @enderror" required autofocus>
            </div>
            @error('name')<span class="auth-field-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-login">
            <label>Email Address</label>
            <div class="form-addons">
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" class="@error('email') is-invalid @enderror" required>
            </div>
            @error('email')<span class="auth-field-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-login">
            <div class="row g-2">
                <div class="col-6">
                    <label>Country</label>
                    <div class="form-addons">
                        <select name="country" class="auth-select" required>
                            <option value="">Select country</option>
                            @foreach($countries as $country)<option value="{{ $country }}" @selected(old('country')===$country)>{{ $country }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <label>Learning Level</label>
                    <div class="form-addons">
                        <select name="learning_level" class="auth-select">
                            <option value="beginner" @selected(old('learning_level', 'beginner') === 'beginner')>Beginner</option>
                            <option value="intermediate" @selected(old('learning_level') === 'intermediate')>Intermediate</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-login">
            <div class="row g-2">
                <div class="col-6">
                    <label>Password</label>
                    <div class="pass-group">
                        <input type="password" name="password" id="registerPassword" class="pass-input" placeholder="Password" required autocomplete="new-password" aria-describedby="passwordStrengthLabel passwordStrengthHint">
                        <span class="ti ti-eye-off toggle-password"></span>
                    </div>
                    <div class="password-strength mt-2" id="passwordStrength" hidden>
                        <div class="password-strength-bar">
                            <span class="password-strength-fill" id="passwordStrengthFill"></span>
                        </div>
                        <p class="password-strength-label mb-0" id="passwordStrengthLabel"></p>
                    </div>
                    <p class="password-strength-hint mb-0" id="passwordStrengthHint">Use 8+ characters with upper &amp; lower case and a number.</p>
                    @error('password')<span class="auth-field-error">{{ $message }}</span>@enderror
                </div>
                <div class="col-6">
                    <label>Confirm</label>
                    <div class="pass-group">
                        <input type="password" name="password_confirmation" id="registerPasswordConfirm" class="pass-inputs" placeholder="Confirm" required autocomplete="new-password">
                        <span class="ti ti-eye-off toggle-passwords"></span>
                    </div>
                    <p class="password-match-hint mb-0 mt-2" id="passwordMatchHint" hidden></p>
                </div>
            </div>
        </div>

        <div class="form-login">
            <button type="submit" class="btn btn-login">Create Account</button>
            <p class="text-muted mt-2 mb-0" style="font-size:12px;">We will email you a 6-digit OTP to verify your account.</p>
        </div>
    </form>

    <div class="signinform text-center">
        <h4>Already have an account? <a href="{{ route('login') }}">Sign In</a></h4>
    </div>

</x-auth-split>
@endsection

@push('scripts')
<script>
(function () {
    const input = document.getElementById('registerPassword');
    const confirm = document.getElementById('registerPasswordConfirm');
    const wrap = document.getElementById('passwordStrength');
    const fill = document.getElementById('passwordStrengthFill');
    const label = document.getElementById('passwordStrengthLabel');
    const matchHint = document.getElementById('passwordMatchHint');

    if (!input || !wrap || !fill || !label) return;

    function isStrong(password) {
        return password.length >= 8
            && /[a-z]/.test(password)
            && /[A-Z]/.test(password)
            && /\d/.test(password)
            && !/\s/.test(password);
    }

    function updateStrength() {
        const value = input.value;

        if (!value) {
            wrap.hidden = true;
            wrap.classList.remove('is-weak', 'is-strong');
            fill.style.width = '0%';
            label.textContent = '';
            updateMatch();
            return;
        }

        wrap.hidden = false;
        const strong = isStrong(value);

        wrap.classList.toggle('is-strong', strong);
        wrap.classList.toggle('is-weak', !strong);
        fill.style.width = strong ? '100%' : '40%';
        label.textContent = strong ? 'Strong' : 'Weak';

        updateMatch();
    }

    function updateMatch() {
        if (!confirm || !matchHint) return;

        if (!confirm.value) {
            matchHint.hidden = true;
            matchHint.textContent = '';
            matchHint.classList.remove('is-match', 'is-mismatch');
            return;
        }

        matchHint.hidden = false;
        const match = input.value === confirm.value;
        matchHint.classList.toggle('is-match', match);
        matchHint.classList.toggle('is-mismatch', !match);
        matchHint.textContent = match ? 'Passwords match' : 'Passwords do not match';
    }

    input.addEventListener('input', updateStrength);
    if (confirm) confirm.addEventListener('input', updateMatch);
})();
</script>
@endpush
