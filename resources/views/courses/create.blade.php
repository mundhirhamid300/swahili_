@extends('layouts.app')

@section('title', 'Create Course')
@section('subtitle', 'Add a new learning course')

@section('content')
<div class="row">
    <div class="col-xl-8">
        <x-page-card title="Course Details">
            <form action="{{ route('courses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('courses._form')
                <div class="d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">Create Course</button>
                    <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </x-page-card>
    </div>
</div>
@endsection
