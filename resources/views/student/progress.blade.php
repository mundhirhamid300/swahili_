@extends('layouts.app')
@section('title', 'My Learning Progress')
@section('subtitle', 'Courses you have practiced')
@section('content')
<div class="row g-4">@forelse($enrollments as $enrollment)
<div class="col-md-6"><div class="card h-100"><div class="card-body">
<div class="d-flex justify-content-between"><div><span class="badge bg-primary">{{ ucfirst($enrollment->course->level) }}</span><h4 class="mt-2">{{ $enrollment->course->title }}</h4></div><strong>{{ $enrollment->progress_data['percentage'] }}%</strong></div>
<div class="progress my-3" style="height:9px"><div class="progress-bar" style="width:{{ $enrollment->progress_data['percentage'] }}%"></div></div>
<a href="{{ route('student.courses.learn', $enrollment->course) }}" class="btn btn-primary"><i class="ti ti-volume me-1"></i>Practice Words &amp; Audio</a>
</div></div></div>
@empty<div class="col-12"><div class="alert alert-info">You have not joined a course yet.</div></div>@endforelse</div>
@endsection
