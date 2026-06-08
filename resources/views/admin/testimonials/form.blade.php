@extends('layouts.admin')

@section('admin-content')
<div class="container-fluid p-4">
    <x-page-header title="{{ $testimonial->exists ? 'Edit Testimonial' : 'Create Testimonial' }}" subtitle="{{ $testimonial->exists ? $testimonial->name : 'Add a new customer testimonial' }}">
        <x-slot:actions>
            <a href="{{ route('admin.testimonials.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Testimonials
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if($testimonial->exists)
                    @method('PUT')
                @endif

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $testimonial->name) }}" placeholder="Customer name" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Location</label>
                        <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                               value="{{ old('location', $testimonial->location) }}" placeholder="e.g. New York, USA">
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Photo</label>
                        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($testimonial->exists && $testimonial->image)
                            <div class="mt-2">
                                <img src="{{ $testimonial->image_url }}" alt="Preview" style="width:80px;height:80px;object-fit:cover;border-radius:50%;">
                                <small class="text-muted ms-2">Current photo</small>
                            </div>
                        @endif
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Content <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control @error('content') is-invalid @enderror"
                                  rows="6" placeholder="Write the testimonial text..." required>{{ old('content', $testimonial->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Rating <span class="text-danger">*</span></label>
                        <select name="rating" class="form-select @error('rating') is-invalid @enderror">
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ old('rating', $testimonial->rating) == $i ? 'selected' : '' }}>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                            @endfor
                        </select>
                        @error('rating')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                               value="{{ old('sort_order', $testimonial->sort_order) }}" placeholder="0" min="0">
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <div class="form-check form-switch mt-4">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1" {{ old('is_active', $testimonial->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="isActive">Is Active</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>{{ $testimonial->exists ? 'Update Testimonial' : 'Create Testimonial' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
