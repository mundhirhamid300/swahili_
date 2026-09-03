@extends('layouts.app')

@section('title', 'Administrators')
@section('subtitle', 'Manage other administrator accounts')
@section('page-actions')
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i> Add Administrator</a>
@endsection

@section('content')
<form method="GET" class="card mb-3">
    <div class="card-body row g-2 align-items-end">
        <div class="col-md-5">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name or email">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending confirmation</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary w-100">Search</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100">Clear Filters</a>
        </div>
    </div>
</form>

<div class="alert alert-info">
  <strong>Super Admin protection is active.</strong> Your account cannot be blocked or deleted. Students are managed separately under <a href="{{ route('admin.students.index') }}">Students</a>.
</div>

@if($errors->has('transfer') || $errors->has('current_password') || $errors->has('confirm_transfer'))
<div class="alert alert-danger"><i class="ti ti-alert-triangle me-1"></i>{{ $errors->first() }}</div>
@endif

<x-data-table :headers="['Name', 'Email', 'Role', 'Status', 'Actions']">
    @forelse($users as $user)
    <tr>
        <td>
            <div class="d-flex align-items-center">
                <span class="avatar avatar-sm me-2">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle object-fit-cover w-100 h-100">
                </span>
                <span class="fw-medium">{{ $user->name }}</span>
            </div>
        </td>
        <td>{{ $user->email }}</td>
        <td>
            <span class="badge bg-{{ $user->isSuperAdmin() ? 'danger' : 'primary' }}">{{ $user->isSuperAdmin() ? 'Super Admin' : 'Administrator' }}</span>
            @if($user->id === auth()->id())<span class="badge bg-light text-dark">You</span>@endif
        </td>
        <td>
            <span class="badge bg-{{ $user->status === 'active' ? 'success' : ($user->status === 'pending' ? 'warning' : 'secondary') }}">
                {{ $user->status === 'pending' ? 'Pending confirmation' : ucfirst($user->status) }}
            </span>
        </td>
        <td>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary">Edit</a>
            @if(!$user->isSuperAdmin() && $user->status === 'active')
            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#transferSuperAdmin{{ $user->id }}">
                <i class="ti ti-crown me-1"></i>Make Super Admin
            </button>
            @endif
            @if(!$user->isSuperAdmin() && $user->id !== auth()->id())
            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete user?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger">Delete</button>
            </form>
            @else
                <span class="badge bg-success-transparent text-success"><i class="ti ti-shield-lock me-1"></i>Protected</span>
            @endif
        </td>
    </tr>
    @empty
    <tr><td colspan="5" class="text-center text-muted py-4">No other administrators found.</td></tr>
    @endforelse
</x-data-table>
<div class="mt-3">{{ $users->links() }}</div>

@foreach($users as $user)
@if(!$user->isSuperAdmin() && $user->status === 'active')
<div class="modal fade" id="transferSuperAdmin{{ $user->id }}" tabindex="-1" aria-labelledby="transferTitle{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <span class="d-grid place-items-center rounded-circle bg-danger-transparent text-danger" style="width:52px;height:52px;"><i class="ti ti-alert-triangle fs-2"></i></span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.users.transfer-super-admin', $user) }}" method="POST">
                @csrf
                <div class="modal-body pt-3">
                    <h4 id="transferTitle{{ $user->id }}">Transfer Super Admin ownership?</h4>
                    <div class="alert alert-danger mt-3">
                        <strong>This changes control immediately.</strong><br>
                        {{ $user->name }} will become the only Super Admin. Your account will become a regular Administrator and you will no longer be able to manage, delete or block administrators.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Enter your current password to continue</label>
                        <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                    </div>
                    <label class="d-flex gap-2 align-items-start border rounded p-3">
                        <input type="checkbox" name="confirm_transfer" value="1" class="form-check-input mt-1" required>
                        <span>I understand that I will lose Super Admin control immediately after this transfer.</span>
                    </label>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="ti ti-crown me-1"></i>Transfer Super Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
@endsection
