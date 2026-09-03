@extends('layouts.app')
@section('title', 'Choose Your Level')
@section('subtitle', 'Join one level to access every course inside it')
@section('content')
<div class="alert alert-info"><strong>How it works:</strong> Join Beginner or Intermediate once. You will automatically receive every published course the Admin places inside that level.</div>
<div class="row g-4">
@foreach(['beginner'=>'Beginner','intermediate'=>'Intermediate'] as $levelKey=>$levelName)
@php $levelCourses=$courses->where('level',$levelKey); $isCurrent=auth()->user()->learning_level===$levelKey; @endphp
<div class="col-lg-6"><div class="card h-100 {{ $isCurrent?'border-primary':'' }}">
<div class="card-header d-flex justify-content-between align-items-center"><div><h3 class="mb-1">{{ $levelName }}</h3><p class="text-muted mb-0">{{ $levelKey==='beginner'?'Start with basic Swahili words':'Build conversation and stronger vocabulary' }}</p></div>@if($isCurrent)<span class="badge bg-success">My Current Level</span>@endif</div>
<div class="card-body d-flex flex-column">
<h6>Courses inside {{ $levelName }}:</h6><div class="list-group mb-4">
@forelse($levelCourses as $course)<div class="list-group-item"><i class="ti ti-book text-primary me-2"></i><strong>{{ $course->title }}</strong><div class="small text-muted ms-4">{{ Str::limit($course->description,70) }}</div></div>
@empty<div class="list-group-item text-muted">The Admin has not added courses here yet.</div>@endforelse
</div>
<form class="mt-auto" action="{{ route('student.levels.enroll',$levelKey) }}" method="POST">@csrf<button class="btn {{ $isCurrent?'btn-outline-primary':'btn-primary' }} btn-lg w-100" {{ $isCurrent?'disabled':'' }}>{{ $isCurrent?'Already Joined':'Join '.$levelName }}</button></form>
</div></div></div>
@endforeach
</div>
@endsection
