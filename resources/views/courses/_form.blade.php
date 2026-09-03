<div class="mb-3">
    <label class="form-label">Title</label>
    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $course->title ?? '') }}" required>
    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="4">{{ old('description', $course->description ?? '') }}</textarea>
</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Level</label>
        <select name="level" class="form-select" required>
            @foreach(['beginner','intermediate'] as $level)
            <option value="{{ $level }}" {{ old('level', $course->level ?? request('level', 'beginner')) === $level ? 'selected' : '' }}>{{ ucfirst($level) }}</option>
            @endforeach
        </select>
        <div class="form-text">Beginner = greetings and basics · Intermediate = conversation</div>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Topic</label>
        <input type="text" name="topic" class="form-control" value="{{ old('topic', $course->topic ?? '') }}" placeholder="e.g. Travel, Greetings, Business">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
            <option value="draft" {{ old('status', $course->status ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ old('status', $course->status ?? '') === 'published' ? 'selected' : '' }}>Published</option>
        </select>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Thumbnail</label>
    <input type="file" name="thumbnail" class="form-control" accept="image/*">
    @isset($course) @if($course->thumbnail)
    <img src="{{ asset('storage/'.$course->thumbnail) }}" alt="Thumbnail" class="img-thumbnail mt-2" style="max-height:100px;">
    @endif @endisset
</div>
