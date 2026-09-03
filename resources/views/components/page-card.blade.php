@props(['title' => null])

<div class="card">
    @if($title)
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title mb-0">{{ $title }}</h5>
        @isset($actions)
            <div class="d-flex gap-2 flex-wrap">{{ $actions }}</div>
        @endisset
    </div>
    @endif
    <div class="card-body {{ $bodyClass ?? '' }}">
        {{ $slot }}
    </div>
</div>
