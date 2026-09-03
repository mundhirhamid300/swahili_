<div class="header">
    <div class="main-header">
        <div class="header-left active">
            <a href="{{ url('/') }}" class="logo logo-normal">
                <img src="{{ template_asset('img/logo.svg') }}" alt="Swahili LMS">
            </a>
            <a href="{{ url('/') }}" class="logo logo-white">
                <img src="{{ template_asset('img/logo-white.svg') }}" alt="Swahili LMS">
            </a>
            <a href="{{ url('/') }}" class="logo-small">
                <img src="{{ template_asset('img/logo-small.svg') }}" alt="Swahili LMS">
            </a>
        </div>

        <a id="mobile_btn" class="mobile_btn" href="#sidebar">
            <span class="bar-icon"><span></span><span></span><span></span></span>
        </a>

        <ul class="nav user-menu">
            <li class="nav-item nav-item-box">
                <a href="{{ route('profile') }}" data-bs-toggle="tooltip" title="Profile">
                    <i class="ti ti-settings"></i>
                </a>
            </li>

            <li class="nav-item dropdown has-arrow main-drop profile-nav">
                <a href="javascript:void(0);" class="nav-link userset" data-bs-toggle="dropdown">
                    <span class="user-info p-0">
                        <span class="user-letter">
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="img-fluid object-fit-cover w-100 h-100">
                        </span>
                    </span>
                </a>
                <div class="dropdown-menu menu-drop-user">
                    <div class="profileset d-flex align-items-center">
                        <span class="user-img me-2">
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="object-fit-cover w-100 h-100">
                        </span>
                        <div>
                            <h6 class="fw-medium">{{ auth()->user()->name }}</h6>
                            <p>{{ auth()->user()->role_label }}</p>
                        </div>
                    </div>
                    <a class="dropdown-item" href="{{ route('profile') }}"><i class="ti ti-user-circle me-2"></i>My Profile</a>
                    <hr class="my-2">
                    <form action="{{ route('logout') }}" method="POST" class="js-logout-form">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger fw-semibold border-0 bg-transparent w-100 text-start">
                            <i class="ti ti-logout me-2"></i>Sign Out
                        </button>
                    </form>
                </div>
            </li>
        </ul>

        <div class="dropdown mobile-user-menu">
            <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <i class="ti ti-dots-vertical"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="{{ route('profile') }}">My Profile</a>
                <form action="{{ route('logout') }}" method="POST" class="js-logout-form">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger fw-semibold border-0 bg-transparent w-100 text-start"><i class="ti ti-logout me-2"></i>Sign Out</button>
                </form>
            </div>
        </div>
    </div>
</div>
