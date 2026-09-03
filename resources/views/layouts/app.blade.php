<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.template-head')
</head>
<body>
    {{-- Lightweight splash: removed artificial delay; CSS hides immediately after paint --}}
    <div id="global-loader" class="lms-loader" aria-hidden="true">
        <div class="whirly-loader"></div>
    </div>

    <div class="main-wrapper">
        @auth
            @include('layouts.partials.template-header')
            @include('layouts.partials.template-sidebar')
        @endauth

        <div class="page-wrapper">
            <div class="content">
                @include('layouts.partials.alerts')

                @hasSection('page-header')
                    @yield('page-header')
                @else
                    @if(View::hasSection('title') && !request()->routeIs('*.dashboard'))
                        <div class="d-lg-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-start gap-3 mb-3 mb-lg-0">
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary flex-shrink-0"
                                    aria-label="Go back to the previous page"
                                    onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href='{{ url('/') }}'; }"
                                >
                                    <i class="ti ti-arrow-left me-1"></i> Back
                                </button>
                                <div>
                                    <h2 class="mb-1">@yield('title')</h2>
                                    @hasSection('subtitle')
                                        <p class="text-muted mb-0">@yield('subtitle')</p>
                                    @endif
                                </div>
                            </div>
                            @hasSection('page-actions')
                                <div class="d-flex gap-2 flex-wrap">@yield('page-actions')</div>
                            @endif
                        </div>
                    @endif
                @endif

                @yield('content')
            </div>

            <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
                <p class="mb-0 text-gray-9">&copy; {{ date('Y') }} {{ config('app.name') }}. All Rights Reserved.</p>
                <p class="mb-0">Learn Kiswahili with confidence</p>
            </div>
        </div>
    </div>

    @auth
        @if(!auth()->user()->isAdmin() && !request()->routeIs('student.chatbot*'))
            <a href="{{ route('student.chatbot') }}" class="mwalimu-chat-fab" aria-label="Open Mwalimu AI" title="Ask Mwalimu AI">
                <span class="mwalimu-chat-fab-icon"><i class="ti ti-message-circle"></i></span>
                <span class="mwalimu-chat-fab-label">Mwalimu AI</span>
            </a>
        @endif
    @endauth

    @auth
    <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div class="rounded-circle bg-danger-transparent text-danger d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-logout fs-3"></i>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <h4 id="logoutConfirmTitle">Are you sure you want to sign out?</h4>
                    <p class="text-muted mb-0">You will need to enter your email and password to access the system again.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmLogoutButton"><i class="ti ti-logout me-1"></i>Yes, Sign Out</button>
                </div>
            </div>
        </div>
    </div>
    @endauth

    @include('layouts.partials.template-scripts')
    @auth
    <script>
    (() => {
        let pendingLogoutForm = null;
        const modalElement = document.getElementById('logoutConfirmModal');
        if (!modalElement || !window.bootstrap) return;
        const logoutModal = bootstrap.Modal.getOrCreateInstance(modalElement);

        document.querySelectorAll('.js-logout-form').forEach(form => {
            form.addEventListener('submit', event => {
                event.preventDefault();
                pendingLogoutForm = form;
                logoutModal.show();
            });
        });

        document.getElementById('confirmLogoutButton')?.addEventListener('click', () => {
            pendingLogoutForm?.submit();
        });
    })();
    </script>
    @endauth
</body>
</html>
