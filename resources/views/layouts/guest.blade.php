<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.template-head')
</head>
<body class="account-page">
    {{-- No full-page loader on auth pages for instant paint --}}

    <div class="main-wrapper">
        <div class="account-content">
            @yield('content')
        </div>
    </div>

    @include('layouts.partials.template-scripts')
</body>
</html>
