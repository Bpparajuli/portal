<div class="mb-3">
    <label for="name" class="form-label">University Name</label>
    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $university->name ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="short_name" class="form-label">Short Name</label>
    <input type="text" class="form-control" id="short_name" name="short_name" value="{{ old('short_name', $university->short_name ?? '') }}">
</div>

<div class="mb-3">
    <label for="country" class="form-label">Country</label>
    <input type="text" class="form-control" id="country" name="country" value="{{ old('country', $university->country ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="city" class="form-label">City</label>
    <input type="text" class="form-control" id="city" name="city" value="{{ old('city', $university->city ?? '') }}">
</div>

<div class="mb-3">
    <label for="website" class="form-label">Website</label>
    <input type="url" class="form-control" id="website" name="website" value="{{ old('website', $university->website ?? '') }}">
</div>

<div class="mb-3">
    <label for="contact_email" class="form-label">Contact Email</label>
    <input type="email" class="form-control" id="contact_email" name="contact_email" value="{{ old('contact_email', $university->contact_email ?? '') }}">
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control" id="description" name="description">{{ old('description', $university->description ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label for="university_logo" class="form-label">University Logo</label>
    <input type="file" class="form-control" id="university_logo" name="university_logo">
    @if(!empty($university->university_logo))
    <div class="mt-2">
        <img src="{{ asset('images/universities_logo/' . $university->university_logo) }}" alt="University Logo" style="max-height: 100px;">
    </div>
    @endif
</div>
