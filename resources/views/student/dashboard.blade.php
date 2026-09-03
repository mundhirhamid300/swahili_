@extends('layouts.app')

@section('title', 'Learning Home')

@section('content')
@include('components.welcome-banner', [
    'title' => 'Karibu, '.auth()->user()->name.'!',
    'subtitle' => $continueLearning ? 'Your course is ready. Read the words and listen to the recorded pronunciation.' : 'Choose Beginner or Intermediate and start learning Kiswahili.',
    'actions' => $continueLearning
        ? '<a href="'.route('student.courses.learn', $continueLearning['course']).'" class="btn btn-light"><i class="ti ti-player-play me-1"></i>Continue Learning</a>'
        : '<a href="'.route('student.available-courses').'" class="btn btn-light"><i class="ti ti-search me-1"></i>Find a Course</a>',
])

@if($continueLearning && $todaysPlan)
<div class="card border-primary mb-4"><div class="card-body p-4">
    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
        <div><span class="badge bg-primary mb-2">Continue course</span><h3>{{ $continueLearning['course']->title }}</h3><p class="text-muted mb-0">{{ ucfirst($continueLearning['course']->level) }}</p></div>
        <a href="{{ route('student.courses.learn', $continueLearning['course']) }}" class="btn btn-primary btn-lg"><i class="ti ti-player-play me-1"></i>Open Course</a>
    </div>
    <div class="row g-3">
        <div class="col-md-6"><div class="border rounded p-4 h-100"><span class="badge bg-primary mb-2">Step 1</span><h5><i class="ti ti-book-2 me-2"></i>Read every word</h5><p class="text-muted mb-0">Reveal its meaning and pronunciation guide.</p></div></div>
        <div class="col-md-6"><div class="border rounded p-4 h-100"><span class="badge bg-primary mb-2">Step 2</span><h5><i class="ti ti-volume me-2"></i>Listen to the Admin's voice</h5><p class="text-muted mb-0">This course contains {{ $todaysPlan['flashcards']->count() }} recorded word{{ $todaysPlan['flashcards']->count() === 1 ? '' : 's' }}.</p></div></div>
    </div>
</div></div>
@else
<div class="card mb-4"><div class="card-body p-5 text-center"><i class="ti ti-books fs-1 text-primary"></i><h3 class="mt-3">Start your first course</h3><p class="text-muted">Choose your level, join a course and practice its words and audio.</p><a href="{{ route('student.available-courses') }}" class="btn btn-primary btn-lg">Find a Course</a></div></div>
@endif

<div class="row g-3 mb-4">
    <x-stat-card icon="book-2" :value="$stats['enrolledCourses']" label="My Courses" />
    <x-stat-card icon="circle-check" :value="$stats['completedLessons']" label="Courses Practiced" />
    <x-stat-card icon="volume" :value="$todaysPlan ? $todaysPlan['flashcards']->count() : 0" label="Words in Next Lesson" />
    <x-stat-card icon="flame" :value="$stats['learning_streak']" label="Learning Streak" />
</div>

<div class="card"><div class="card-header"><h5 class="mb-1">My courses</h5><p class="text-muted mb-0">Open a course to read and listen.</p></div><div class="card-body">
@forelse($enrollments as $enrollment)
<div class="d-flex justify-content-between align-items-center py-3 {{ !$loop->last ? 'border-bottom' : '' }}"><div><h6>{{ $enrollment->course->title }}</h6><span class="badge bg-primary">{{ ucfirst($enrollment->course->level) }}</span></div><a href="{{ route('student.courses.learn', $enrollment->course) }}" class="btn btn-primary">Read &amp; Listen</a></div>
@empty <p class="text-muted mb-0">You have not joined a course yet.</p> @endforelse
</div></div>
@endsection
