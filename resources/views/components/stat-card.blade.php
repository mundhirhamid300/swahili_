@props(['icon', 'value', 'label', 'badge' => null, 'badgeClass' => 'bg-success'])

<div class="col-xl-3 col-sm-6 d-flex">
    <div class="card flex-fill">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <span class="avatar avatar-md bg-primary mb-3">
                    <i class="ti ti-{{ $icon }} fs-16"></i>
                </span>
                @if(!empty($badge))
                    <span class="badge {{ $badgeClass ?? 'bg-success' }} fw-normal mb-3">{{ $badge }}</span>
                @endif
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="mb-1">{{ $value }}</h2>
                    <p class="fs-13 mb-0">{{ $label }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
