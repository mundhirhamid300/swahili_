<div class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <a href="{{ url('/') }}" class="logo logo-normal"><img src="{{ template_asset('img/logo.svg') }}" alt="Swahili LMS"></a>
        <a href="{{ url('/') }}" class="logo logo-white"><img src="{{ template_asset('img/logo-white.svg') }}" alt="Swahili LMS"></a>
        <a href="{{ url('/') }}" class="logo-small"><img src="{{ template_asset('img/logo-small.svg') }}" alt="Swahili LMS"></a>
        <a id="toggle_btn" href="javascript:void(0);" title="Collapse sidebar"><i class="ti ti-layout-sidebar-left-collapse fs-18"></i></a>
    </div>

    <div class="sidebar-header p-3 pb-0 pt-2">
        <div class="rounded bg-light p-2 mb-4 sidebar-profile d-flex align-items-center">
            <span class="avatar avatar-md avatar-rounded flex-shrink-0">
                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="img-fluid object-fit-cover w-100 h-100">
            </span>
            <div class="sidebar-profile-info ms-2">
                <h6 class="fs-14 fw-bold mb-1">{{ auth()->user()->name }}</h6>
                <p class="fs-12 mb-0 text-muted">{{ auth()->user()->role_label }} account</p>
            </div>
        </div>
    </div>

    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                @if(auth()->user()->isAdmin())
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">Overview</h6>
                        <ul><li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="ti ti-home fs-16 me-2"></i><span>Dashboard</span></a></li></ul>
                    </li>
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">Learning Content</h6>
                        <ul>
                            <li><a href="{{ route('courses.index') }}" class="{{ request()->routeIs('courses.*') ? 'active' : '' }}"><i class="ti ti-book fs-16 me-2"></i><span>Courses, Words &amp; Voice</span></a></li>
                        </ul>
                    </li>
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">People</h6>
                        <ul>
                            <li><a href="{{ route('admin.students.index') }}" class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}"><i class="ti ti-school fs-16 me-2"></i><span>Students</span></a></li>
                            @if(auth()->user()->isSuperAdmin())
                            <li><a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="ti ti-users fs-16 me-2"></i><span>Administrators</span></a></li>
                            @endif
                            <li><a href="{{ route('admin.enrollments.index') }}" class="{{ request()->routeIs('admin.enrollments.*') ? 'active' : '' }}"><i class="ti ti-user-check fs-16 me-2"></i><span>Course Enrollments</span></a></li>
                            <li><a href="{{ route('admin.progress.index') }}" class="{{ request()->routeIs('admin.progress.*') ? 'active' : '' }}"><i class="ti ti-chart-bar fs-16 me-2"></i><span>Student Progress</span></a></li>
                        </ul>
                    </li>
                @else
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">Start Here</h6>
                        <ul><li><a href="{{ route('student.dashboard') }}" class="{{ request()->routeIs('student.dashboard') ? 'active' : '' }}"><i class="ti ti-home fs-16 me-2"></i><span>Learning Home</span></a></li></ul>
                    </li>
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">Learn</h6>
                        <ul>
                            <li><a href="{{ route('student.my-courses') }}" class="{{ request()->routeIs('student.my-courses') || request()->routeIs('student.courses.learn') ? 'active' : '' }}"><i class="ti ti-player-play fs-16 me-2"></i><span>My Courses &amp; Audio</span></a></li>
                            <li><a href="{{ route('student.available-courses') }}" class="{{ request()->routeIs('student.available-courses') ? 'active' : '' }}"><i class="ti ti-search fs-16 me-2"></i><span>Find a Course</span></a></li>
                            <li><a href="{{ route('student.chatbot') }}" class="{{ request()->routeIs('student.chatbot*') ? 'active' : '' }}"><i class="ti ti-sparkles fs-16 me-2"></i><span>Mwalimu AI</span></a></li>
                        </ul>
                    </li>
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">My Results</h6>
                        <ul>
                            <li><a href="{{ route('student.progress') }}" class="{{ request()->routeIs('student.progress') ? 'active' : '' }}"><i class="ti ti-chart-line fs-16 me-2"></i><span>Course Progress</span></a></li>
                        </ul>
                    </li>
                @endif

                <li class="submenu-open">
                    <h6 class="submenu-hdr">My Account</h6>
                    <ul>
                        <li><a href="{{ route('profile') }}" class="{{ request()->routeIs('profile') ? 'active' : '' }}"><i class="ti ti-user-circle fs-16 me-2"></i><span>Profile &amp; Password</span></a></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" id="sidebar-logout-form" class="js-logout-form">@csrf
                                <button type="submit" class="btn btn-outline-danger w-100 text-start"><i class="ti ti-logout fs-16 me-2"></i><span>Sign Out</span></button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
