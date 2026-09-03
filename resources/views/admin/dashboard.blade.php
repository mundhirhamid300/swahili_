@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
@include('components.welcome-banner', [
    'title' => 'Welcome, '.auth()->user()->name,
    'subtitle' => 'Manage learning content and users from one place.',
    'actions' => '<a href="'.route('courses.create').'" class="btn btn-light"><i class="ti ti-plus me-1"></i>Create Course</a>',
])

<div class="row g-3 mb-4">
    <x-stat-card icon="school" :value="$stats['students']" label="Students" />
    <x-stat-card icon="book" :value="$stats['courses']" label="Courses" />
    <x-stat-card icon="user-check" :value="$stats['enrollments']" label="Enrollments" />
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Set up learning content</h5></div>
    <div class="card-body">
        <p class="text-muted mb-4">Follow these steps in order. Each step takes you directly to the correct page.</p>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="border rounded p-4 h-100">
                    <span class="badge bg-primary mb-3">Step 1</span>
                    <h5>Create a course</h5>
                    <p class="text-muted">Add the course title, level and description.</p>
                    <a href="{{ route('courses.create') }}" class="btn btn-primary">Create Course</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-4 h-100">
                    <span class="badge bg-primary mb-3">Step 2</span>
                    <h5>Add lessons and quizzes</h5>
                    <p class="text-muted">Open a course, add lessons, then add quiz questions.</p>
                    <a href="{{ route('courses.index') }}" class="btn btn-outline-primary">Manage Courses</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-4 h-100">
                    <span class="badge bg-primary mb-3">Step 3</span>
                    <h5>Add words and your voice</h5>
                    <p class="text-muted">Create flashcards and record pronunciation for students.</p>
                    <a href="{{ route('courses.index') }}" class="btn btn-outline-primary">Open Courses &amp; Lessons</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4"><a href="{{ route('admin.students.index') }}" class="card card-body h-100 text-decoration-none"><i class="ti ti-school fs-2 text-primary mb-2"></i><h5>Manage Students</h5><p class="text-muted mb-0">View, edit or deactivate student accounts.</p></a></div>
    <div class="col-md-4"><a href="{{ route('admin.progress.index') }}" class="card card-body h-100 text-decoration-none"><i class="ti ti-chart-bar fs-2 text-primary mb-2"></i><h5>Student Progress</h5><p class="text-muted mb-0">Review lesson completion and quiz results.</p></a></div>
    <div class="col-md-4"><a href="{{ route('admin.enrollments.index') }}" class="card card-body h-100 text-decoration-none"><i class="ti ti-user-check fs-2 text-primary mb-2"></i><h5>View Enrollments</h5><p class="text-muted mb-0">See which students joined each course.</p></a></div>
</div>
@endsection
