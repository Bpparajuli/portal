<div class="row">
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $university->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
        <input type="text" id="country" name="country" value="{{ old('country', $university->country ?? '') }}" class="form-control @error('country') is-invalid @enderror" required>
        @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="city" class="form-label">City</label>
        <input type="text" id="city" name="city" value="{{ old('city', $university->city ?? '') }}" class="form-control @error('city') is-invalid @enderror">
        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="website" class="form-label">Website</label>
        <input type="url" id="website" name="website" value="{{ old('website', $university->website ?? '') }}" class="form-control @error('website') is-invalid @enderror">
        @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="mb-3">
    <label for="address" class="form-label">Address</label>
    <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $university->address ?? '') }}</textarea>
    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $university->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
