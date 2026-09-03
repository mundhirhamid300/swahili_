<script src="{{ template_asset('js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ template_asset('js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/lms.js') }}?v={{ @filemtime(public_path('js/lms.js')) ?: time() }}"></script>
@stack('scripts')
