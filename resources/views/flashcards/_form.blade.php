<div class="mb-3">
    <label class="form-label">Swahili Word</label>
    <input type="text" name="swahili_word" class="form-control" value="{{ old('swahili_word', $flashcard->swahili_word ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">English Meaning</label>
    <input type="text" name="english_meaning" class="form-control" value="{{ old('english_meaning', $flashcard->english_meaning ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Pronunciation Guide</label>
    <input type="text" name="pronunciation" class="form-control" value="{{ old('pronunciation', $flashcard->pronunciation ?? '') }}" placeholder="e.g. JAM-bo">
</div>
<div class="mb-3">
    <label class="form-label">Your Voice Recording {{ isset($flashcard) ? '(record again only when changing it)' : '(required)' }}</label>
    <input type="file" name="audio" id="flashcardAudioInput" class="form-control" accept="audio/mp3,audio/mpeg,audio/wav,audio/ogg,audio/webm">
    <div class="d-flex flex-wrap gap-2 mt-2">
        <button type="button" id="startAudioRecording" class="btn btn-outline-danger">
            <i class="ti ti-microphone me-1"></i> Record
        </button>
        <button type="button" id="stopAudioRecording" class="btn btn-danger" disabled>
            <i class="ti ti-player-stop me-1"></i> Stop
        </button>
    </div>
    <p id="audioRecordingStatus" class="small text-muted mt-2 mb-2">
        Press Record, say the Swahili word, then press Stop.
    </p>
    <audio
        id="audioRecordingPreview"
        controls
        class="w-100 {{ isset($flashcard) && $flashcard->audio_path ? '' : 'd-none' }}"
        @isset($flashcard)
            @if($flashcard->audio_path) src="{{ str_starts_with($flashcard->audio_path, 'http') ? $flashcard->audio_path : asset('storage/'.$flashcard->audio_path) }}" @endif
        @endisset
    ></audio>
</div>

@push('scripts')
<script>
(() => {
    const startButton = document.getElementById('startAudioRecording');
    const stopButton = document.getElementById('stopAudioRecording');
    const fileInput = document.getElementById('flashcardAudioInput');
    const preview = document.getElementById('audioRecordingPreview');
    const status = document.getElementById('audioRecordingStatus');

    if (!startButton || !navigator.mediaDevices?.getUserMedia || !window.MediaRecorder) {
        if (startButton) startButton.disabled = true;
        if (status) status.textContent = 'Microphone recording is not supported by this browser. You can upload an audio file instead.';
        return;
    }

    let recorder;
    let stream;
    let chunks = [];
    let previewUrl;

    startButton.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            chunks = [];

            const preferredType = MediaRecorder.isTypeSupported('audio/webm;codecs=opus')
                ? 'audio/webm;codecs=opus'
                : '';
            recorder = preferredType
                ? new MediaRecorder(stream, { mimeType: preferredType })
                : new MediaRecorder(stream);

            recorder.addEventListener('dataavailable', event => {
                if (event.data.size > 0) chunks.push(event.data);
            });

            recorder.addEventListener('stop', () => {
                const mimeType = recorder.mimeType || 'audio/webm';
                const blob = new Blob(chunks, { type: mimeType });
                const file = new File([blob], `flashcard-${Date.now()}.webm`, { type: mimeType });
                const transfer = new DataTransfer();
                transfer.items.add(file);
                fileInput.files = transfer.files;

                if (previewUrl) URL.revokeObjectURL(previewUrl);
                previewUrl = URL.createObjectURL(blob);
                preview.src = previewUrl;
                preview.classList.remove('d-none');
                status.textContent = 'Recording ready. Listen to the preview, then save the flashcard.';

                stream.getTracks().forEach(track => track.stop());
                startButton.disabled = false;
                stopButton.disabled = true;
            });

            recorder.start();
            startButton.disabled = true;
            stopButton.disabled = false;
            status.textContent = 'Recording… say the word clearly, then press Stop.';
        } catch (error) {
            status.textContent = 'Microphone permission was denied. Allow microphone access in the browser and try again.';
        }
    });

    stopButton.addEventListener('click', () => {
        if (recorder?.state === 'recording') recorder.stop();
    });

    fileInput.addEventListener('change', () => {
        const file = fileInput.files?.[0];
        if (!file) return;
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        preview.classList.remove('d-none');
        status.textContent = 'Audio ready. Listen to the preview, then save the flashcard.';
    });
})();
</script>
@endpush
