@extends('layouts.app')

@section('title', 'Edit Course')
@section('subtitle', $course->title)
@section('page-actions')
    <a href="{{ route('courses.words.index', $course) }}" class="btn btn-info text-white">
        <i class="ti ti-microphone me-1"></i> Manage Words &amp; Voice
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8">
        <div class="alert alert-info">
            Click <strong>Manage Words &amp; Voice</strong> to write words, meanings, pronunciation guides and record your voice directly inside this course.
        </div>
        <x-page-card title="Edit Course">
            <form action="{{ route('courses.update', $course) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                @include('courses._form')
                <div class="d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">Update Course</button>
                    <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </x-page-card>
    </div>
</div>
@endsection
