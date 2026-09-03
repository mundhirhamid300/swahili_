@extends('layouts.app')

@section('title', 'Course Progress')
@section('subtitle', $course->title)

@section('content')
<x-data-table :headers="['Student', 'Email', 'Progress', 'Lessons Done', 'Status']">
    @forelse($enrollments as $enrollment)
    <tr><td class="fw-medium">{{ $enrollment->user->name }}</td><td>{{ $enrollment->user->email }}</td><td style="min-width:140px"><div class="progress mb-1" style="height:8px"><div class="progress-bar" style="width:{{ $enrollment->progress_data['percentage'] }}%"></div></div><small>{{ $enrollment->progress_data['percentage'] }}%</small></td><td>{{ $enrollment->completed_lessons }} / {{ $enrollment->progress_data['total'] }}</td><td><span class="badge bg-{{ $enrollment->status === 'completed' ? 'success' : 'info' }}">{{ ucfirst($enrollment->status) }}</span></td></tr>
    @empty
    <tr><td colspan="5" class="text-center text-muted py-4">No students enrolled yet.</td></tr>
    @endforelse
</x-data-table>

<div class="card mt-4"><div class="card-header"><h5 class="mb-1">Recent quiz attempts</h5><p class="text-muted mb-0">Review scores and leave helpful feedback.</p></div><div class="card-body">
    @forelse($attempts as $attempt)
    <div class="border rounded p-3 mb-3"><div class="d-flex justify-content-between gap-2 mb-2"><strong>{{ $attempt->user->name }} · {{ $attempt->lesson->title }}</strong><span class="badge bg-{{ $attempt->passed ? 'success' : 'danger' }}">{{ $attempt->score }}% · {{ $attempt->passed ? 'Passed' : 'Failed' }}</span></div>
        @if($attempt->admin_feedback)<p class="small">{{ $attempt->admin_feedback }}</p>@endif
        <form action="{{ route('admin.attempts.feedback', $attempt) }}" method="POST" class="d-flex flex-wrap gap-2">@csrf<input name="admin_feedback" class="form-control flex-grow-1" maxlength="500" value="{{ old('admin_feedback', $attempt->admin_feedback) }}" placeholder="Write feedback" required><button class="btn btn-outline-primary">Save Feedback</button></form>
    </div>
    @empty<p class="text-muted mb-0">No quiz attempts yet.</p>@endforelse
</div></div>
@endsection
