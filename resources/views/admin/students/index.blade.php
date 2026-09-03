@extends('layouts.app')

@section('title', 'Student Management')
@section('subtitle', 'Manage learners and learning profiles')
@section('page-actions')
    <a href="{{ route('admin.students.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i> Add Student</a>
@endsection

@section('content')
<form method="GET" class="card mb-3">
    <div class="card-body row g-2 align-items-end">
        <div class="col-md-4">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name or email">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="learning_level" class="form-select">
                <option value="">All levels</option>
                @foreach(['beginner','intermediate'] as $level)
                <option value="{{ $level }}" @selected(request('learning_level') === $level)>{{ ucfirst($level) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Search</button>
        </div>
    </div>
</form>

<x-data-table :headers="['Name', 'Email', 'Country', 'Level', 'Status', 'Actions']">
    @forelse($students as $student)
    <tr>
        <td>
            <div class="d-flex align-items-center">
                <span class="avatar avatar-sm me-2">
                    <img src="{{ $student->avatar_url }}" alt="{{ $student->name }}" class="rounded-circle object-fit-cover w-100 h-100">
                </span>
                <span class="fw-medium">{{ $student->name }}</span>
            </div>
        </td>
        <td>{{ $student->email }}</td>
        <td>{{ $student->country ?: '—' }}</td>
        <td>{{ $student->learning_level ? ucfirst($student->learning_level) : '—' }}</td>
        <td>
            <span class="badge bg-{{ $student->status === 'active' ? 'success' : 'secondary' }}">
                {{ ucfirst($student->status) }}
            </span>
        </td>
        <td>
            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-primary">Edit</a>
            @if(auth()->user()->isSuperAdmin())
            <form action="{{ route('admin.students.promote-admin', $student) }}" method="POST" class="d-inline" onsubmit="return confirm('Make {{ addslashes($student->name) }} an Administrator? They will gain access to course and student management.')">
                @csrf
                <button class="btn btn-sm btn-outline-warning"><i class="ti ti-user-up me-1"></i>Make Admin</button>
            </form>
            @endif
            <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this student?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger">Delete</button>
            </form>
        </td>
    </tr>
    @empty
    <tr><td colspan="6" class="text-center text-muted py-4">No students found.</td></tr>
    @endforelse
</x-data-table>
<div class="mt-3">{{ $students->links() }}</div>
@endsection
