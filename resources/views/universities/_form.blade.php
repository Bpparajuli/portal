@csrf

<div class="mb-3">
    <label for="name" class="form-label">University Name</label>
    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $university->name ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="location" class="form-label">Location</label>
    <input type="text" name="location" id="location" class="form-control" value="{{ old('location', $university->location ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea name="description" id="description" rows="4" class="form-control">{{ old('description', $university->description ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label for="logo" class="form-label">Logo</label>
    <input type="file" name="logo" id="logo" class="form-control">
    @if(!empty($university->logo))
    <div class="mt-2">
        <img src="{{ asset('storage/' . $university->logo) }}" alt="Logo" height="50">
    </div>
    @endif
</div>

<button type="submit" class="btn btn-primary">
    {{ isset($university) ? 'Update University' : 'Add University' }}
</button>
<a href="{{ route('universities.index') }}" class="btn btn-secondary">Cancel</a>
