@extends('layouts.admin')

@section('admin-content')
<div class="container-fluid p-4">
    <x-page-header title="Manage Testimonials" subtitle="Create and manage customer testimonials">
        <x-slot:actions>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#testimonialModal" onclick="resetTestimonialForm()">
                <i class="fas fa-plus me-2"></i>Create New
            </button>
        </x-slot:actions>
    </x-page-header>

    @if($testimonials->count())
        <x-table-responsive id="testimonialsTable" searchable="true">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Content</th>
                    <th>Rating</th>
                    <th>Active</th>
                    <th>Order</th>
                    <th style="width:140px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($testimonials as $testimonial)
                <tr>
                    <td class="fw-semibold">{{ $testimonial->name }}</td>
                    <td>{{ $testimonial->location }}</td>
                    <td class="text-muted small">{{ Str::limit($testimonial->content, 80) }}</td>
                    <td>
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $testimonial->rating)
                                <i class="fas fa-star text-warning"></i>
                            @else
                                <i class="far fa-star text-muted"></i>
                            @endif
                        @endfor
                    </td>
                    <td>
                        <x-badge type="{{ $testimonial->is_active ? 'success' : 'secondary' }}" text="{{ $testimonial->is_active ? 'Active' : 'Inactive' }}" />
                    </td>
                    <td>{{ $testimonial->sort_order }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" onclick="editTestimonial({{ $testimonial->id }})"><i class="fas fa-edit"></i></button>
                            <x-confirm-delete action="admin.testimonials.destroy" :id="$testimonial->id" />
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </x-table-responsive>
    @else
        <x-empty-state
            icon="fa-quote-right"
            title="No testimonials yet"
            description="Create your first testimonial to get started."
        />
    @endif
</div>

<div class="modal fade" id="testimonialModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="testimonialForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="testimonialMethod" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="testimonialModalTitle"><i class="fas fa-quote-right me-2 text-primary"></i>Create Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="testName" class="form-control" placeholder="Customer name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Location</label>
                            <input type="text" name="location" id="testLocation" class="form-control" placeholder="e.g. New York, USA">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Photo</label>
                            <div class="d-flex gap-2 align-items-start">
                                <div style="flex:1;">
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0"
                                    style="font-size:10px;white-space:nowrap;" data-gallery-target="test_image"
                                    data-gallery-preview="testPhotoPreview" data-gallery-hidden="testImageHidden"><i
                                        class="fas fa-images me-1"></i>Gallery</button>
                                <input type="hidden" name="image_selected" id="testImageHidden" data-gallery-field="image">
                            </div>
                            <div id="testCurrentPhoto" class="mt-2 d-none">
                                <img id="testPhotoPreview" src="" alt="Preview" style="width:80px;height:80px;object-fit:cover;border-radius:50%;">
                                <small class="text-muted ms-2">Current photo</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Content <span class="text-danger">*</span></label>
                            <textarea name="content" id="testContent" class="form-control" rows="6" placeholder="Write the testimonial text..." required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Rating <span class="text-danger">*</span></label>
                            <select name="rating" id="testRating" class="form-select">
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Sort Order</label>
                            <input type="number" name="sort_order" id="testSortOrder" class="form-control" placeholder="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-4">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" class="form-check-input" id="testIsActive" value="1" checked>
                                <label class="form-check-label fw-semibold" for="testIsActive">Is Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-2"></i>Save Testimonial</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const testimonials = @json($testimonials->items());
function resetTestimonialForm() {
    document.getElementById('testimonialModalTitle').textContent = 'Create Testimonial';
    document.getElementById('testimonialForm').action = '{{ route("admin.testimonials.store") }}';
    document.getElementById('testimonialMethod').value = 'POST';
    document.getElementById('testName').value = '';
    document.getElementById('testLocation').value = '';
    document.getElementById('testContent').value = '';
    document.getElementById('testRating').value = '5';
    document.getElementById('testSortOrder').value = '';
    document.getElementById('testIsActive').checked = true;
    document.getElementById('testCurrentPhoto').classList.add('d-none');
}
function editTestimonial(id) {
    const t = testimonials.find(x => x.id === id);
    if (!t) return;
    document.getElementById('testimonialModalTitle').textContent = 'Edit Testimonial';
    document.getElementById('testimonialForm').action = '{{ url("admin/testimonials") }}/' + id;
    document.getElementById('testimonialMethod').value = 'PUT';
    document.getElementById('testName').value = t.name || '';
    document.getElementById('testLocation').value = t.location || '';
    document.getElementById('testContent').value = t.content || '';
    document.getElementById('testRating').value = t.rating || '5';
    document.getElementById('testSortOrder').value = t.sort_order || '';
    document.getElementById('testIsActive').checked = t.is_active ? true : false;
    if (t.image_url) {
        document.getElementById('testPhotoPreview').src = t.image_url;
        document.getElementById('testCurrentPhoto').classList.remove('d-none');
    } else {
        document.getElementById('testCurrentPhoto').classList.add('d-none');
    }
    new bootstrap.Modal(document.getElementById('testimonialModal')).show();
}
</script>
@endpush
@endsection
