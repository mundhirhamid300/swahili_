@extends('layouts.app')

@section('title', 'My Profile')
@section('subtitle', 'Update your account information')

@section('content')
<div class="row">
    <div class="col-xl-8">
        <x-page-card title="Profile Information">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 mb-4">
                    <img id="avatar-preview" src="{{ $user->avatar_url }}" alt="Profile picture" class="rounded-circle border object-fit-cover" width="96" height="96">
                    <div class="flex-grow-1">
                        <label for="avatar" class="form-label fw-semibold">Profile Picture</label>
                        <input id="avatar" type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">Choose JPG, PNG or WEBP, maximum 2 MB.</div>
                        @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($user->avatar)
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_avatar" value="1" id="remove-avatar">
                            <label class="form-check-label text-danger" for="remove-avatar">Remove current picture and use default avatar</label>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control bg-light" value="{{ $user->email }}" readonly>
                    <div class="form-text">This is the verified email used for security codes.</div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Country</label>
                        <select name="country" class="form-select"><option value="">Select country</option>@foreach($countries as $country)<option value="{{ $country }}" @selected(old('country',$user->country)===$country)>{{ $country }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Learning Level</label>
                        <select name="learning_level" class="form-select">
                            @foreach(['beginner','intermediate'] as $level)
                            <option value="{{ $level }}" {{ $user->learning_level === $level ? 'selected' : '' }}>{{ ucfirst($level) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </x-page-card>

        <div class="mt-4">
        <x-page-card title="Change Password Securely">
            <p class="text-muted">Enter your current password yourself. We will email you an OTP before saving the new password.</p>
            <form action="{{ route('profile.password.otp.send') }}" method="POST" autocomplete="off">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" value="" autocomplete="off" placeholder="Enter your current password" required>
                    @error('current_password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" value="" autocomplete="new-password" required>
                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" value="" autocomplete="new-password" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="ti ti-mail-lock me-1"></i>Send OTP & Continue</button>
            </form>
        </x-page-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('avatar')?.addEventListener('change', function () {
    const file = this.files?.[0];
    if (file) document.getElementById('avatar-preview').src = URL.createObjectURL(file);
});
window.addEventListener('pageshow', function () {
    document.querySelectorAll('input[type="password"]').forEach(input => input.value = '');
});
</script>
@endpush
