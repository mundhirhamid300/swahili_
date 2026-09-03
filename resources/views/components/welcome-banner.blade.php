@props(['title', 'subtitle' => null, 'actions' => null])

<div class="welcome-wrap mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div class="mb-3">
            <h2 class="mb-1 text-white">{{ $title }}</h2>
            @if(!empty($subtitle))
                <p class="text-light mb-0">{{ $subtitle }}</p>
            @endif
        </div>
        @if(!empty($actions))
            <div class="d-flex align-items-center flex-wrap mb-1">{!! $actions !!}</div>
        @endif
    </div>
    <div class="welcome-bg">
        <img src="{{ template_asset('img/bg/welcome-bg-02.svg') }}" alt="" class="welcome-bg-01">
        <img src="{{ template_asset('img/bg/welcome-bg-01.svg') }}" alt="" class="welcome-bg-03">
    </div>
</div>
