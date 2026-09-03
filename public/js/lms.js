/**
 * Lightweight LMS UI helpers (replaces heavy template script.js for faster loads).
 */
(function () {
    function hideLoader() {
        var loader = document.getElementById('global-loader');
        if (!loader) return;
        loader.classList.add('is-hidden');
        loader.style.display = 'none';
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', hideLoader);
    } else {
        hideLoader();
    }
    window.addEventListener('pageshow', hideLoader);

    document.addEventListener('DOMContentLoaded', function () {
        var mobileBtn = document.getElementById('mobile_btn');
        if (mobileBtn) {
            mobileBtn.addEventListener('click', function (e) {
                e.preventDefault();
                document.body.classList.toggle('slide-nav');
            });
        }

        var toggleBtn = document.getElementById('toggle_btn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function (e) {
                e.preventDefault();
                document.body.classList.toggle('mini-sidebar');
            });
        }

        document.querySelectorAll('.sidebar-overlay, #sidebar .sidebar-close').forEach(function (el) {
            el.addEventListener('click', function () {
                document.body.classList.remove('slide-nav');
            });
        });

        function bindPasswordToggle(selector, inputSelector) {
            document.querySelectorAll(selector).forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var input = document.querySelector(inputSelector);
                    if (!input) return;
                    var show = input.getAttribute('type') === 'password';
                    input.setAttribute('type', show ? 'text' : 'password');
                    btn.classList.toggle('ti-eye');
                    btn.classList.toggle('ti-eye-off');
                });
            });
        }

        bindPasswordToggle('.toggle-password', '.pass-input');
        bindPasswordToggle('.toggle-passwords', '.pass-inputs');

        document.querySelectorAll('.alert').forEach(function (alert) {
            setTimeout(function () {
                if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                    bootstrap.Alert.getOrCreateInstance(alert).close();
                }
            }, 5000);
        });
    });
})();
