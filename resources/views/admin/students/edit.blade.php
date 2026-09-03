@extends('layouts.app')

@section('title', 'Edit Student')
@section('subtitle', $student->name)

@section('content')
<div class="row">
    <div class="col-xl-8">
        <x-page-card title="Edit Student">
            <form action="{{ route('admin.students.update', $student) }}" method="POST">
                @csrf @method('PUT')
                @include('admin.students._form')
                <div class="d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">Update Student</button>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </x-page-card>
    </div>
</div>
@endsection
