<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name ?? '') }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email ?? '') }}" required>
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<input type="hidden" name="role" value="admin">
@if(!isset($user))
<input type="hidden" name="status" value="active">
<div class="alert alert-info">
    <i class="ti ti-shield-lock me-1"></i>
    Create a temporary password and give it to this administrator securely. If the email already belongs to a Student, that same account will be changed to Administrator. They must change the password at first sign-in.
</div>
<div class="mb-3">
    <label class="form-label">Temporary Password</label>
    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required>
    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Confirm Temporary Password</label>
    <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
</div>
@else
<div class="row">
    <div class="col-md-12 mb-3">
        <label class="form-label">Status</label>
        @if(isset($user) && $user->isSuperAdmin())
            <input type="hidden" name="status" value="active">
            <input type="text" class="form-control" value="Active — protected Super Admin" disabled>
            <div class="form-text">The Super Admin cannot be blocked or deactivated.</div>
        @else
        <select name="status" class="form-select" required>
            <option value="active" {{ old('status', $user->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ old('status', $user->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        @endif
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Password {{ isset($user) ? '(leave blank to keep)' : '' }}</label>
    <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}>
</div>
<div class="mb-3">
    <label class="form-label">Confirm Password</label>
    <input type="password" name="password_confirmation" class="form-control">
</div>
@endif
