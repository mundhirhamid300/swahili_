<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>

<link rel="shortcut icon" type="image/svg+xml" href="{{ template_asset('img/favicon.svg') }}">
<link rel="apple-touch-icon" href="{{ template_asset('img/logo-small.svg') }}">

{{-- Preload critical CSS; Tabler icons cover UI — Font Awesome removed for speed --}}
<link rel="preload" href="{{ template_asset('css/bootstrap.min.css') }}" as="style">
<link rel="stylesheet" href="{{ template_asset('css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ template_asset('plugins/tabler-icons/tabler-icons.min.css') }}">
<link rel="stylesheet" href="{{ template_asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: time() }}">
@stack('styles')
