@extends('layouts.app')
@section('title', $course->title.' — Words & Voice')
@section('subtitle', 'Write every word, its meaning, pronunciation guide, and record your own voice')
@section('page-actions')
<a href="{{ route('courses.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Back to Courses</a>
@endsection
@section('content')
<div class="alert alert-primary"><strong>This is the course content.</strong> You do not need to create a lesson. Add all words for <strong>{{ $course->title }}</strong> directly below.</div>
<div class="row g-4">
<div class="col-xl-5"><x-page-card title="Add a Word & Record Voice">
    <form action="{{ route('courses.words.store', $course) }}" method="POST" enctype="multipart/form-data">@csrf
        @include('flashcards._form')
        <button class="btn btn-primary w-100"><i class="ti ti-device-floppy me-1"></i>Save Word &amp; Voice</button>
    </form>
</x-page-card></div>
<div class="col-xl-7"><x-page-card title="Saved Words ({{ $words->total() }})">
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Swahili</th><th>Meaning</th><th>Your Voice</th><th>Actions</th></tr></thead><tbody>
@forelse($words as $word)<tr><td><strong>{{ $word->swahili_word }}</strong><div class="small text-muted">{{ $word->pronunciation }}</div></td><td>{{ $word->english_meaning }}</td><td><audio controls preload="none" style="max-width:180px"><source src="{{ str_starts_with($word->audio_path, 'http') ? $word->audio_path : asset('storage/'.$word->audio_path) }}"></audio></td><td><a class="btn btn-sm btn-primary" href="{{ route('courses.words.edit', [$course, $word]) }}">Edit</a> <form class="d-inline" method="POST" action="{{ route('courses.words.destroy', [$course, $word]) }}" onsubmit="return confirm('Delete this word and audio?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form></td></tr>
@empty<tr><td colspan="4" class="text-center text-muted py-4">No words yet. Add the first word using the form.</td></tr>@endforelse
</tbody></table></div>{{ $words->links() }}
</x-page-card></div>
</div>
@endsection
