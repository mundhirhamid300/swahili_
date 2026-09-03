@extends('layouts.app')

@section('title', 'Student Progress')
@section('subtitle', 'Review course completion, quiz scores and students who need help')

@section('content')
<div class="row g-3 mb-4">
    <x-stat-card icon="chart-bar" :value="$analytics['avg_score'].'%'" label="Average Quiz Score" />
    <x-stat-card icon="circle-check" :value="$analytics['pass_rate'].'%'" label="Quiz Pass Rate" />
    <x-stat-card icon="alert-triangle" :value="$analytics['struggling_lessons']->count()" label="Lessons Needing Help" />
    <x-stat-card icon="user-off" :value="$analytics['drop_off_lessons']->count()" label="Drop-off Lessons" />
</div>

@if($needsFeedback->isNotEmpty())
<div class="card mb-4 border-warning">
    <div class="card-header"><h5 class="mb-1">Students needing feedback</h5><p class="text-muted mb-0">Send a short tip after a failed quiz attempt.</p></div>
    <div class="card-body">
        @foreach($needsFeedback as $attempt)
        <div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between gap-2 mb-2"><strong>{{ $attempt->user->name }} · {{ $attempt->lesson->title }}</strong><span class="badge bg-danger">{{ $attempt->score }}%</span></div>
            <form action="{{ route('admin.attempts.feedback', $attempt) }}" method="POST" class="d-flex flex-wrap gap-2">@csrf
                <input type="text" name="admin_feedback" class="form-control flex-grow-1" maxlength="500" placeholder="Write a helpful tip" required>
                <button class="btn btn-warning">Send Feedback</button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="card">
    <div class="card-header"><h5 class="mb-1">Progress by course</h5><p class="text-muted mb-0">Choose a course to see enrolled students and quiz attempts.</p></div>
    <div class="card-body"><div class="row g-3">
        @forelse($courses as $course)
        <div class="col-md-6 col-lg-4"><div class="border rounded p-3 h-100"><h5>{{ $course->title }}</h5><p class="text-muted">{{ $course->enrollments_count }} enrolled students</p><a href="{{ route('admin.progress.show', $course) }}" class="btn btn-primary">View Student Progress</a></div></div>
        @empty
        <div class="col-12"><p class="text-muted mb-0">No courses found.</p></div>
        @endforelse
    </div></div>
</div>
@endsection
