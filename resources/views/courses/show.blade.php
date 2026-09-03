@extends('layouts.app')
@section('title', $course->title)
@section('subtitle', ucfirst($course->level).' · '.ucfirst($course->status))
@section('page-actions')
<a href="{{ route('courses.words.index', $course) }}" class="btn btn-primary"><i class="ti ti-microphone me-1"></i>Manage Words &amp; Voice</a>
<a href="{{ route('courses.edit', $course) }}" class="btn btn-outline-secondary">Edit Course</a>
@endsection
@section('content')
<x-page-card title="Course Overview"><p>{{ $course->description ?: 'No description provided.' }}</p><div class="alert alert-info mb-0">Add Swahili words, meanings, pronunciation guides and your recorded voice directly to this course.</div></x-page-card>
@endsection
