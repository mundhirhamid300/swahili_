@extends('layouts.app')

@section('title', 'Add Student')
@section('subtitle', 'Create a new learner account')

@section('content')
<div class="row">
    <div class="col-xl-8">
        <x-page-card title="Student Details">
            <form action="{{ route('admin.students.store') }}" method="POST">
                @csrf
                @include('admin.students._form')
                <div class="d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">Create Student</button>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </x-page-card>
    </div>
</div>
@endsection
