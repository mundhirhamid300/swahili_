@extends('layouts.app')

@section('title', 'Create Your Private Password')
@section('subtitle', 'Your temporary password can only be used for initial access')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7 col-xl-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="d-grid align-items-center justify-content-center rounded-circle bg-warning-transparent text-warning" style="width:54px;height:54px;"><i class="ti ti-key fs-2"></i></span>
                    <div><h3 class="mb-1">Password change required</h3><p class="text-muted mb-0">Create a password known only to you before entering the dashboard.</p></div>
                </div>

                <form action="{{ route('password.force.update') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Temporary Password</label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password" autofocus>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Use at least 8 characters with upper and lower case letters and a number.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                    </div>
                    <button class="btn btn-primary btn-lg w-100"><i class="ti ti-shield-check me-1"></i>Save New Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
