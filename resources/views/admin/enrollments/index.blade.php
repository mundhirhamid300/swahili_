@extends('layouts.app')

@section('title', 'Enrollments')
@section('subtitle', 'All student course enrollments')

@section('content')
<form method="GET" class="card mb-3">
    <div class="card-body row g-2 align-items-end">
        <div class="col-md-7">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search student or course">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All status</option>
                @foreach(['active','completed','dropped'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Search</button>
        </div>
    </div>
</form>

<x-data-table :headers="['Student', 'Course', 'Status', 'Enrolled Date']">
    @forelse($enrollments as $enrollment)
    <tr>
        <td class="fw-medium">{{ $enrollment->user->name }}</td>
        <td>{{ $enrollment->course->title }}</td>
        <td><span class="badge bg-primary">{{ ucfirst($enrollment->status) }}</span></td>
        <td>{{ $enrollment->enrolled_at->format('M d, Y') }}</td>
    </tr>
    @empty
    <tr><td colspan="4" class="text-center text-muted py-4">No enrollments found.</td></tr>
    @endforelse
</x-data-table>
<div class="mt-3">{{ $enrollments->links() }}</div>
@endsection
