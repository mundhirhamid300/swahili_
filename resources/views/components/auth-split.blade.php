@props([
    'image' => 'authentication-01.svg',
    'imageAlt' => 'Authentication',
    'dense' => false,
])

<div
    class="login-wrapper lms-auth-split {{ $dense ? 'lms-auth-dense' : '' }}"
    style="--lms-auth-background: url('{{ template_asset('img/authentication/'.$image) }}?v=4')"
>
    <div class="login-img">
        <img src="{{ template_asset('img/authentication/'.$image) }}?v=4" alt="{{ $imageAlt }}" loading="eager" decoding="async" fetchpriority="high">
    </div>
    <div class="login-content user-login lms-login-panel">
        <div class="login-userset">
            {{ $slot }}
        </div>
    </div>
</div>
