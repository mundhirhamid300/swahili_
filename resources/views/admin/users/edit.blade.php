@extends('layouts.app')

@section('title', 'Edit User')
@section('subtitle', $user->name)

@section('content')
<div class="row">
    <div class="col-xl-8">
        <x-page-card title="Edit User">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf @method('PUT')
                @include('admin.users._form')
                <div class="d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">Update User</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </x-page-card>
    </div>
</div>
@endsection
