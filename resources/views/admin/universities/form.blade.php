<div class="row">
    <div class="col-md-6 mb-2">
        <label for="name" class="form-label">University Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $university->name ?? '') }}" required>
        @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-2">
        <label for="short_name" class="form-label">Short Name</label>
        <input type="text" class="form-control @error('short_name') is-invalid @enderror" id="short_name" name="short_name" value="{{ old('short_name', $university->short_name ?? '') }}">
        @error('short_name')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-2">
        <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country', $university->country ?? '') }}" required>
        @error('country')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-2">
        <label for="city" class="form-label">City</label>
        <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $university->city ?? '') }}">
        @error('city')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-2">
        <label for="website" class="form-label">Website</label>
        <input type="text" class="form-control @error('website') is-invalid @enderror" id="website" name="website" value="{{ old('website', $university->website ?? '') }}">
        @error('website')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-2">
        <label for="contact_email" class="form-label">Contact Email</label>
        <input type="email" class="form-control @error('contact_email') is-invalid @enderror" id="contact_email" name="contact_email" value="{{ old('contact_email', $university->contact_email ?? '') }}">
        @error('contact_email')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class=" mb-2">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description">{{ old('description', $university->description ?? '') }}</textarea>
    @error('description')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-6 mb-2">
    <label for="university_logo" class="form-label">University Logo</label>
    <input type="file" class="form-control @error('university_logo') is-invalid @enderror" id="university_logo" name="university_logo" accept=".jpg,.jpeg,.png,.gif,.webp">
    @error('university_logo')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror

    @if(!empty($university->university_logo))
    <div class="mt-2">
        <img src="{{ asset('storage/uni_logo/' . $university->university_logo) }}" alt="University Logo" style="max-height: 100px;">
    </div>
    @endif
    <small class="text-muted">Accepted formats: JPG, JPEG, PNG, GIF, WEBP. Max size: 5MB.</small>
</div>
