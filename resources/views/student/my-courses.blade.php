@extends('layouts.app')
@section('title', 'My Courses')
@section('subtitle', 'Open a course to read its words, meanings and hear the Admin’s voice')
@section('content')
<div class="row g-4">@forelse($enrollments as $enrollment)
<div class="col-md-6"><div class="card h-100"><div class="card-body">
<span class="badge bg-primary">{{ ucfirst($enrollment->course->level) }}</span><h4 class="mt-3">{{ $enrollment->course->title }}</h4><p class="text-muted">{{ $enrollment->course->description }}</p>
<a href="{{ route('student.courses.learn', $enrollment->course) }}" class="btn btn-primary w-100"><i class="ti ti-volume me-1"></i>Read Words &amp; Listen</a>
</div></div></div>
@empty<div class="col-12"><div class="alert alert-info">You have not joined a course. <a href="{{ route('student.available-courses') }}">Find a course</a>.</div></div>@endforelse</div>
@endsection
