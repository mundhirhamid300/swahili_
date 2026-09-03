@extends('layouts.app')
@section('title', 'Courses by Level')
@section('subtitle', 'Create any course under Beginner or Intermediate, then add its words and your voice')
@section('page-actions')<a href="{{ route('courses.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>New Course</a>@endsection
@section('content')
<form method="GET" class="card mb-4"><div class="card-body row g-2 align-items-end"><div class="col-md-8"><label class="form-label">Search courses</label><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Example: Greetings, Numbers, Family"></div><div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">All</option><option value="published" @selected(request('status')==='published')>Published</option><option value="draft" @selected(request('status')==='draft')>Draft</option></select></div><div class="col-md-2"><button class="btn btn-primary w-100">Search</button></div></div></form>

@foreach(['beginner' => 'Beginner', 'intermediate' => 'Intermediate'] as $levelKey => $levelName)
@php $levelCourses = $courses->where('level', $levelKey); @endphp
<section class="mb-5">
<div class="d-flex justify-content-between align-items-center mb-3"><div><h3 class="mb-1">{{ $levelName }}</h3><p class="text-muted mb-0">{{ $levelKey === 'beginner' ? 'Basic words and everyday foundations' : 'Longer vocabulary and conversation' }}</p></div><span class="badge bg-primary fs-6">{{ $levelCourses->count() }} courses</span></div>
<div class="row g-4">
@forelse($levelCourses as $course)
<div class="col-md-6 col-xl-4"><div class="card h-100 overflow-hidden">@if($course->thumbnail)<img src="{{ asset('storage/'.$course->thumbnail) }}" class="card-img-top" alt="{{ $course->title }}" style="height:110px;object-fit:cover">@else<div class="d-flex align-items-center justify-content-center bg-primary-transparent text-primary" style="height:88px"><i class="ti ti-book fs-1"></i></div>@endif<div class="card-body d-flex flex-column p-3"><div><span class="badge bg-{{ $course->status==='published'?'success':'secondary' }}">{{ ucfirst($course->status) }}</span><h4 class="mt-2 mb-1">{{ $course->title }}</h4><p class="text-muted fs-13 mb-3">{{ Str::limit($course->description, 75) }}</p></div><div class="mt-auto d-flex flex-wrap gap-2"><a href="{{ route('courses.words.index', $course) }}" class="btn btn-sm btn-primary"><i class="ti ti-microphone me-1"></i>Words ({{ $course->lessons->sum('flashcards_count') }})</a><a href="{{ route('courses.edit', $course) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-edit me-1"></i>Edit</a><form method="POST" action="{{ route('courses.destroy', $course) }}" onsubmit="return confirm('Delete {{ addslashes($course->title) }} and all its words/audio? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash me-1"></i>Delete</button></form></div></div></div></div>
@empty<div class="col-12"><div class="border rounded p-4 text-center text-muted">No {{ $levelName }} courses yet. <a href="{{ route('courses.create', ['level'=>$levelKey]) }}">Create one now</a>.</div></div>@endforelse
</div></section>
@endforeach
@endsection
