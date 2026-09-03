@extends('layouts.app')
@section('title', 'Edit Word & Voice')
@section('subtitle', $course->title)
@section('content')
<div class="row"><div class="col-xl-7"><x-page-card title="Update Word">
<form action="{{ route('courses.words.update', [$course, $flashcard]) }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
@include('flashcards._form')
<div class="d-flex gap-2"><button class="btn btn-primary">Update Word &amp; Voice</button><a class="btn btn-outline-secondary" href="{{ route('courses.words.index', $course) }}">Cancel</a></div>
</form></x-page-card></div></div>
@endsection
