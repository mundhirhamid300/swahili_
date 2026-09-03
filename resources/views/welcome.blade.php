<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.template-head')
    <title>Swahili Learning for Foreigners</title>
</head>
<body class="lms-home">
    <section class="lms-hero lms-hero-bleed" style="--lms-hero-image: url('{{ template_asset('img/authentication/swahili-home-hero.jpg') }}?v=4')">
        <div class="lms-hero-overlay"></div>
        <div class="container lms-hero-content">
            <img src="{{ template_asset('img/logo-white.svg') }}" alt="Swahili Learning" class="lms-hero-brand">
            <h1 class="lms-hero-title">Karibu — Learn Kiswahili</h1>
            <p class="lms-hero-lead">Courses, AI translation, and an AI tutor built for foreigners starting from scratch.</p>
            <div class="lms-hero-actions">
                <a href="{{ route('register') }}" class="btn btn-light btn-lg px-4">Get Started</a>
                <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4">Sign In</a>
            </div>
        </div>
    </section>

    <section class="lms-home-features">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <i class="ti ti-book fs-1 mb-2 text-primary"></i>
                    <h5>Courses &amp; Lessons</h5>
                    <p class="small text-muted mb-0">Clear Beginner and Intermediate learning paths</p>
                </div>
                <div class="col-md-4">
                    <i class="ti ti-language fs-1 mb-2 text-primary"></i>
                    <h5>AI Translator</h5>
                    <p class="small text-muted mb-0">OpenAI translate + ElevenLabs listen</p>
                </div>
                <div class="col-md-4">
                    <i class="ti ti-chart-line fs-1 mb-2 text-primary"></i>
                    <h5>Track Progress</h5>
                    <p class="small text-muted mb-0">Follow your learning progress as you study</p>
                </div>
            </div>
        </div>
    </section>

    @include('layouts.partials.template-scripts')
</body>
</html>
