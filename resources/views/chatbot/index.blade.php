@extends('layouts.app')

@section('title', 'Mwalimu AI')
@section('subtitle', 'Translate any word into Kiswahili and learn how to pronounce it')

@section('page-actions')
<form action="{{ route('student.chatbot.new') }}" method="POST">@csrf
    <button class="btn btn-outline-primary"><i class="ti ti-refresh me-1"></i>New chat</button>
</form>
@endsection

@push('styles')
<style>
.tutor-shell{max-width:1050px;margin:0 auto}.tutor-hero{background:linear-gradient(135deg,#082f4f 0%,#12628f 58%,#ff8b38 140%);border-radius:24px;color:#fff;overflow:hidden;position:relative}.tutor-hero:after{content:"";position:absolute;width:280px;height:280px;border-radius:50%;background:rgba(255,255,255,.08);right:-80px;top:-140px}.tutor-avatar{width:64px;height:64px;display:grid;place-items:center;border-radius:20px;background:rgba(255,255,255,.16);font-size:32px}.tutor-chat{height:min(58vh,620px);min-height:430px;overflow-y:auto;background:linear-gradient(180deg,#f8fbfe,#fff);scroll-behavior:smooth}.tutor-bubble{max-width:82%;padding:15px 17px;border-radius:18px;box-shadow:0 5px 18px rgba(15,48,73,.07)}.tutor-bubble-user{margin-left:auto;background:#0b3b61;color:#fff;border-bottom-right-radius:5px}.tutor-bubble-ai{background:#fff;border:1px solid #e4edf4;border-bottom-left-radius:5px}.tutor-result{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:12px}.tutor-result-item{background:#f6f9fc;border-radius:12px;padding:11px 13px}.tutor-result-item strong{display:block;color:#68798a;font-size:11px;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px}.tutor-result-item.primary{background:#fff1e6}.tutor-result-item.primary span{font-size:20px;font-weight:700;color:#132f45}.tutor-input-wrap{background:#fff;border:1px solid #dae5ed;border-radius:18px;padding:8px;box-shadow:0 12px 35px rgba(15,48,73,.1)}.tutor-input{border:0!important;box-shadow:none!important;min-height:48px}.tutor-send{width:50px;height:50px;border-radius:14px}.tutor-suggestion{border:1px solid #dce7ef;background:#fff;border-radius:999px;padding:8px 13px;color:#284b65;transition:.2s}.tutor-suggestion:hover{border-color:#ff8b38;color:#d9620a;transform:translateY(-1px)}.typing-dot{display:inline-block;width:7px;height:7px;border-radius:50%;background:#789;animation:tutorPulse 1s infinite}.typing-dot:nth-child(2){animation-delay:.15s}.typing-dot:nth-child(3){animation-delay:.3s}@keyframes tutorPulse{0%,60%,100%{opacity:.3;transform:translateY(0)}30%{opacity:1;transform:translateY(-4px)}}@media(max-width:575px){.tutor-result{grid-template-columns:1fr}.tutor-bubble{max-width:94%}.tutor-chat{min-height:390px;height:55vh}}
</style>
@endpush

@section('content')
<div class="tutor-shell">
    <section class="tutor-hero p-4 p-lg-5 mb-4">
        <div class="d-flex align-items-center gap-3 position-relative" style="z-index:1">
            <span class="tutor-avatar"><i class="ti ti-sparkles"></i></span>
            <div><span class="badge bg-success mb-2">AI tutor online</span><h2 class="text-white mb-1">Jambo! I am Mwalimu AI</h2><p class="text-white-50 mb-0">Ask in English or your own language—I will teach you the natural Kiswahili expression.</p></div>
        </div>
    </section>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4">
            <div><h5 class="mb-0"><i class="ti ti-message-circle text-primary me-2"></i>Swahili practice</h5><small class="text-muted">Requests left today: <strong id="aiRemaining">{{ $aiRemaining }}</strong></small></div>
            <span class="badge bg-light text-dark"><i class="ti ti-shield-check text-success me-1"></i>Kiswahili only</span>
        </div>

        <div class="tutor-chat p-3 p-md-4" id="chatMessages" aria-live="polite">
            <div class="d-flex mb-3">
                <div class="tutor-bubble tutor-bubble-ai">
                    <strong class="d-block text-primary mb-1">Mwalimu AI</strong>
                    <span>Type any word or phrase—for example <em>“Good morning”</em>, <em>“airport”</em>, or <em>“How are you?”</em></span>
                </div>
            </div>

            @foreach($messages as $turn)
                <div class="d-flex mb-3"><div class="tutor-bubble tutor-bubble-user">{{ $turn['message'] }}</div></div>
                @php($details = $turn['details'] ?? [])
                <div class="d-flex mb-3"><div class="tutor-bubble tutor-bubble-ai">
                    <strong class="d-block text-primary mb-2">Mwalimu AI</strong>
                    @if(!empty($details['swahili']))
                        <div class="tutor-result">
                            <div class="tutor-result-item primary"><strong>Kiswahili</strong><span>{{ $details['swahili'] }}</span></div>
                            <div class="tutor-result-item"><strong>Meaning</strong><span>{{ $details['meaning'] ?? '' }}</span></div>
                            <div class="tutor-result-item"><strong>Pronunciation</strong><span>{{ $details['pronunciation'] ?? '' }}</span></div>
                            <div class="tutor-result-item"><strong>Example</strong><span>{{ $details['example_swahili'] ?? '' }}@if(!empty($details['example_meaning']))<small class="d-block text-muted mt-1">{{ $details['example_meaning'] }}</small>@endif</span></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-3 listen-btn" data-text="{{ $details['swahili'] }}"><i class="ti ti-volume me-1"></i>Listen</button>
                    @else
                        <span>{{ $turn['response'] }}</span>
                    @endif
                    @if(!empty($details['note']))<p class="small text-muted mb-0 mt-2">{{ $details['note'] }}</p>@endif
                </div></div>
            @endforeach
        </div>

        <div class="card-footer bg-white p-3 p-md-4">
            <div class="d-flex flex-wrap gap-2 mb-3">
                @foreach(['Good morning','Where is the market?','Thank you very much','I would like some water'] as $suggestion)
                    <button type="button" class="tutor-suggestion">{{ $suggestion }}</button>
                @endforeach
            </div>
            <form id="chatForm" class="tutor-input-wrap d-flex align-items-end gap-2">
                @csrf
                <textarea id="chatInput" name="message" class="form-control tutor-input" rows="1" maxlength="500" placeholder="Write a word or phrase…" required></textarea>
                <button type="submit" id="chatSendBtn" class="btn btn-primary tutor-send flex-shrink-0" aria-label="Send"><i class="ti ti-send"></i></button>
            </form>
            <p id="chatError" class="text-danger small mt-2 mb-0 d-none"></p>
            <p class="text-muted small mt-2 mb-0"><i class="ti ti-info-circle me-1"></i>AI can make mistakes. Compare important pronunciation with the recorded course audio.</p>
        </div>
    </div>
    <audio id="chatAudio" class="d-none"></audio>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const config = {send: @json(route('student.chatbot.send')), speak: @json(route('student.chatbot.speak')), cloudVoice: @json($speechConfig['cloud_tts'] ?? false)};
    const messages = document.getElementById('chatMessages');
    const form = document.getElementById('chatForm');
    const input = document.getElementById('chatInput');
    const send = document.getElementById('chatSendBtn');
    const error = document.getElementById('chatError');
    const audio = document.getElementById('chatAudio');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    const escape = value => String(value || '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
    const scroll = () => messages.scrollTop = messages.scrollHeight;
    const addUser = text => messages.insertAdjacentHTML('beforeend', `<div class="d-flex mb-3"><div class="tutor-bubble tutor-bubble-user">${escape(text)}</div></div>`);
    const addTyping = () => { messages.insertAdjacentHTML('beforeend','<div class="d-flex mb-3" id="typingBubble"><div class="tutor-bubble tutor-bubble-ai"><span class="typing-dot"></span> <span class="typing-dot"></span> <span class="typing-dot"></span></div></div>'); scroll(); };
    const addTutor = data => {
        const d = data.details || {};
        const result = d.swahili ? `<div class="tutor-result"><div class="tutor-result-item primary"><strong>Kiswahili</strong><span>${escape(d.swahili)}</span></div><div class="tutor-result-item"><strong>Meaning</strong><span>${escape(d.meaning)}</span></div><div class="tutor-result-item"><strong>Pronunciation</strong><span>${escape(d.pronunciation)}</span></div><div class="tutor-result-item"><strong>Example</strong><span>${escape(d.example_swahili)}${d.example_meaning ? `<small class="d-block text-muted mt-1">${escape(d.example_meaning)}</small>` : ''}</span></div></div><button type="button" class="btn btn-sm btn-outline-primary mt-3 listen-btn" data-text="${escape(data.speak_text)}"><i class="ti ti-volume me-1"></i>Listen</button>` : `<span>${escape(data.response)}</span>`;
        messages.insertAdjacentHTML('beforeend', `<div class="d-flex mb-3"><div class="tutor-bubble tutor-bubble-ai"><strong class="d-block text-primary mb-2">Mwalimu AI</strong>${result}${d.note ? `<p class="small text-muted mb-0 mt-2">${escape(d.note)}</p>` : ''}</div></div>`);
        scroll();
    };

    async function listen(text) {
        if (!text) return;
        if (config.cloudVoice) {
            try {
                const response = await fetch(config.speak,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},body:JSON.stringify({text})});
                const data = await response.json();
                if (data.url) { audio.src=data.url; await audio.play(); return; }
            } catch (_) {}
        }
        if ('speechSynthesis' in window) { speechSynthesis.cancel(); const utterance=new SpeechSynthesisUtterance(text); utterance.lang='sw-KE'; utterance.rate=.82; speechSynthesis.speak(utterance); }
    }

    document.addEventListener('click', event => {
        const suggestion=event.target.closest('.tutor-suggestion'); if(suggestion){input.value=suggestion.textContent.trim();input.focus();}
        const button=event.target.closest('.listen-btn'); if(button)listen(button.dataset.text);
    });
    input.addEventListener('keydown', event => { if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();form.requestSubmit();} });
    form.addEventListener('submit', async event => {
        event.preventDefault(); const message=input.value.trim(); if(!message)return;
        error.classList.add('d-none'); addUser(message); input.value=''; send.disabled=true; addTyping();
        try {
            const response=await fetch(config.send,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({message})});
            const data=await response.json(); document.getElementById('typingBubble')?.remove();
            if(!response.ok||!data.success)throw new Error(data.message||'Samahani, Mwalimu AI hapatikani kwa sasa. Tafadhali jaribu tena baada ya muda mfupi.');
            addTutor(data); if(data.ai_remaining!==undefined)document.getElementById('aiRemaining').textContent=data.ai_remaining;
        } catch (exception) { document.getElementById('typingBubble')?.remove(); error.textContent=exception.message||'Samahani, huduma haipatikani kwa sasa. Tafadhali jaribu tena.'; error.classList.remove('d-none'); }
        finally { send.disabled=false; input.focus(); }
    });
    scroll();
})();
</script>
@endpush
