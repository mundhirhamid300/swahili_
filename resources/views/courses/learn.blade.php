@extends('layouts.app')
@section('title', $course->title)
@section('subtitle', ucfirst($course->level).' · Read, reveal meaning and listen')
@section('page-actions')<a href="{{ route('student.my-courses') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Back to My Courses</a>@endsection
@section('content')
<x-page-card title="About This Course"><p class="mb-0">{{ $course->description }}</p></x-page-card>
<div class="mt-4"><x-page-card title="Words & Pronunciation">
@if(!$lesson || $lesson->flashcards->isEmpty())<div class="alert alert-info mb-0">The Admin has not added words to this course yet.</div>
@else
<div class="row justify-content-center"><div class="col-xl-8">
<div class="flashcard-study mb-4" id="wordCard"><div class="flashcard-inner"><div class="flashcard-front"><h2 id="swahiliWord">—</h2><p class="mb-0 opacity-75">Tap to reveal meaning</p></div><div class="flashcard-back"><h3 id="englishMeaning">—</h3><p id="pronunciation" class="text-muted mb-0"></p></div></div></div>
<div class="d-flex justify-content-center gap-2 flex-wrap"><button id="previousWord" class="btn btn-outline-secondary"><i class="ti ti-chevron-left"></i> Previous</button><button id="listenWord" class="btn btn-primary btn-lg"><i class="ti ti-volume me-1"></i>Listen</button><button id="nextWord" class="btn btn-outline-secondary">Next <i class="ti ti-chevron-right"></i></button></div>
<p class="text-center text-muted mt-3"><span id="wordNumber">1</span> / {{ $lesson->flashcards->count() }}</p>
</div></div>
@endif
</x-page-card></div>
@endsection
@if($lesson && $lesson->flashcards->isNotEmpty())
@push('scripts')<script>(()=>{const words=@json($lesson->flashcards->values()),base=@json(asset('storage'));let i=0,a=null;const card=document.getElementById('wordCard');function show(){const w=words[i];a?.pause();card.classList.remove('flipped');swahiliWord.textContent=w.swahili_word;englishMeaning.textContent=w.english_meaning;pronunciation.textContent=w.pronunciation?'['+w.pronunciation+']':'';wordNumber.textContent=i+1}card.onclick=()=>card.classList.toggle('flipped');previousWord.onclick=()=>{i=(i-1+words.length)%words.length;show()};nextWord.onclick=()=>{i=(i+1)%words.length;show()};listenWord.onclick=()=>{a?.pause();a=new Audio(base+'/'+words[i].audio_path);a.play()};show()})();</script>@endpush
@endif
