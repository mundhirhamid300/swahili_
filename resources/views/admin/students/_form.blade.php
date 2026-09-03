<div class="mb-3">
    <label class="form-label">Full Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $student->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" value="{{ old('email', $student->email ?? '') }}" required>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Country</label>
        <input type="text" name="country" class="form-control" value="{{ old('country', $student->country ?? '') }}" placeholder="e.g. USA">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Learning Level</label>
        <select name="learning_level" class="form-select">
            <option value="">Not set</option>
            @foreach(['beginner','intermediate'] as $level)
            <option value="{{ $level }}" {{ old('learning_level', $student->learning_level ?? 'beginner') === $level ? 'selected' : '' }}>{{ ucfirst($level) }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select" required>
        <option value="active" {{ old('status', $student->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
        <option value="inactive" {{ old('status', $student->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Password {{ isset($student) ? '(leave blank to keep)' : '' }}</label>
    <input type="password" name="password" class="form-control" {{ isset($student) ? '' : 'required' }}>
</div>
<div class="mb-3">
    <label class="form-label">Confirm Password</label>
    <input type="password" name="password_confirmation" class="form-control">
</div>
