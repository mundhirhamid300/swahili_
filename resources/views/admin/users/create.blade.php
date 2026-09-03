@extends('layouts.app')

@section('title', 'Create Administrator')
@section('subtitle', 'Create an administrator account without email invitation')

@section('content')
<div class="row">
    <div class="col-xl-8">
        <x-page-card title="User Details">
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Administrator was not created.</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                @include('admin.users._form')
                <div class="d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-user-plus me-1"></i>Create Administrator</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </x-page-card>
    </div>
</div>
@endsection
