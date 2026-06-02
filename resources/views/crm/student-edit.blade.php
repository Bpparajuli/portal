{{-- resources/views/crm/student-edit.blade.php --}}
@extends('layouts.crm')

@section('title', 'Edit Student - ' . $student->full_name)

@push('styles')
    <style>
        .edit-student-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        .form-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .form-section-header {
            padding: 1rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            font-size: 1rem;
            color: #1e293b;
        }

        .form-section-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
            color: #334155;
        }

        .form-group .required::after {
            content: '*';
            color: #ef4444;
            margin-left: 0.25rem;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #ef4444;
        }

        .invalid-feedback {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin: -0.5rem;
        }

        .col-md-3,
        .col-md-4,
        .col-md-6,
        .col-md-8,
        .col-md-12 {
            padding: 0.5rem;
        }

        .col-md-3 {
            flex: 0 0 25%;
        }

        .col-md-4 {
            flex: 0 0 33.333%;
        }

        .col-md-6 {
            flex: 0 0 50%;
        }

        .col-md-8 {
            flex: 0 0 66.666%;
        }

        .col-md-12 {
            flex: 0 0 100%;
        }

        @media (max-width: 768px) {

            .col-md-3,
            .col-md-4,
            .col-md-6,
            .col-md-8 {
                flex: 0 0 100%;
            }
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-secondary {
            background: #64748b;
            color: white;
            border-color: #64748b;
        }

        .btn-secondary:hover {
            background: #475569;
        }

        .btn-outline-secondary {
            background: transparent;
            border-color: #cbd5e1;
            color: #64748b;
        }

        .btn-outline-secondary:hover {
            background: #f1f5f9;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }

        .photo-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            margin-bottom: 1rem;
        }

        .photo-upload-area {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .photo-upload-area:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }

        .tag-input-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 0.5rem;
            min-height: 42px;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: #e2e8f0;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.75rem;
        }

        .tag-remove {
            cursor: pointer;
            color: #64748b;
            font-weight: bold;
        }

        .tag-remove:hover {
            color: #ef4444;
        }

        .tag-input {
            border: none;
            outline: none;
            flex: 1;
            min-width: 100px;
            padding: 0.25rem;
        }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .text-muted {
            color: #64748b;
            font-size: 0.75rem;
        }

        hr {
            margin: 1rem 0;
            border: none;
            border-top: 1px solid #e2e8f0;
        }
    </style>
@endpush

@section('content')
    <div class="edit-student-container">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Edit Student</h2>
                <p class="text-muted">{{ $student->full_name }} (ID: {{ $student->id }})</p>
            </div>
            <div>
                <a href="{{ route('crm.student.show', $student) }}" class="btn btn-outline-secondary">
                    ← Back to Student Profile
                </a>
            </div>
        </div>

        {{-- Success/Error Messages --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following errors:</strong>
                <ul class="mt-2 mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Edit Form --}}
        <form action="{{ route('crm.student.update', $student) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Personal Information --}}
            <div class="form-section">
                <div class="form-section-header">
                    📸 Personal Information
                </div>
                <div class="form-section-body">
                    <div class="row">
                        {{-- Photo Upload --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Student Photo</label>
                                <div class="text-center">
                                    @if ($student->students_photo && Storage::disk('public')->exists($student->students_photo))
                                        <img src="{{ Storage::url($student->students_photo) }}" class="photo-preview"
                                            id="photoPreview" alt="Student Photo">
                                    @else
                                        <div class="photo-preview bg-light d-flex align-items-center justify-content-center"
                                            style="width:150px;height:150px;">
                                            <i class="fa-solid fa-user text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="photo-upload-area mt-2" onclick="document.getElementById('photoInput').click()">
                                    <i class="fa-solid fa-cloud-upload-alt"></i>
                                    <span>Click to upload photo</span>
                                </div>
                                <input type="file" name="students_photo" id="photoInput" class="d-none" accept="image/*">
                                <small class="text-muted">Supported formats: JPG, PNG, GIF (Max: 5MB)</small>
                            </div>
                        </div>

                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="required">First Name</label>
                                        <input type="text" name="first_name"
                                            class="form-control @error('first_name') is-invalid @enderror"
                                            value="{{ old('first_name', $student->first_name) }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="required">Last Name</label>
                                        <input type="text" name="last_name"
                                            class="form-control @error('last_name') is-invalid @enderror"
                                            value="{{ old('last_name', $student->last_name) }}" required>
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            value="{{ old('email', $student->email) }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input type="text" name="phone_number"
                                            class="form-control @error('phone_number') is-invalid @enderror"
                                            value="{{ old('phone_number', $student->phone_number) }}">
                                        @error('phone_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date of Birth</label>
                                        <input type="date" name="dob"
                                            class="form-control @error('dob') is-invalid @enderror"
                                            value="{{ old('dob', $student->dob?->format('Y-m-d')) }}">
                                        @error('dob')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Gender</label>
                                        <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                            <option value="">Select Gender</option>
                                            @foreach (\App\Models\Student::GENDERS as $gender)
                                                <option value="{{ $gender }}"
                                                    {{ old('gender', $student->gender) == $gender ? 'selected' : '' }}>
                                                    {{ $gender }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Marital Status</label>
                                        <select name="marital_status" class="form-select">
                                            <option value="">Select Marital Status</option>
                                            @foreach (\App\Models\Student::MARITAL_STATUSES as $status)
                                                <option value="{{ $status }}"
                                                    {{ old('marital_status', $student->marital_status) == $status ? 'selected' : '' }}>
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="form-section">
                <div class="form-section-header">
                    📍 Contact Information
                </div>
                <div class="form-section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Permanent Address</label>
                                <textarea name="permanent_address" rows="2" class="form-control @error('permanent_address') is-invalid @enderror">{{ old('permanent_address', $student->permanent_address) }}</textarea>
                                @error('permanent_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Temporary Address</label>
                                <textarea name="temporary_address" rows="2"
                                    class="form-control @error('temporary_address') is-invalid @enderror">{{ old('temporary_address', $student->temporary_address) }}</textarea>
                                @error('temporary_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Passport & Nationality --}}
            <div class="form-section">
                <div class="form-section-header">
                    🛂 Passport & Nationality
                </div>
                <div class="form-section-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nationality</label>
                                <input type="text" name="nationality" class="form-control"
                                    value="{{ old('nationality', $student->nationality) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Passport Number</label>
                                <input type="text" name="passport_number" class="form-control"
                                    value="{{ old('passport_number', $student->passport_number) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Passport Expiry Date</label>
                                <input type="date" name="passport_expiry" class="form-control"
                                    value="{{ old('passport_expiry', $student->passport_expiry?->format('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Education Information --}}
            <div class="form-section">
                <div class="form-section-header">
                    🎓 Education Information
                </div>
                <div class="form-section-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Qualification</label>
                                <input type="text" name="qualification" class="form-control"
                                    value="{{ old('qualification', $student->qualification) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Passed Year</label>
                                <input type="number" name="passed_year" class="form-control"
                                    value="{{ old('passed_year', $student->passed_year) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Education Board</label>
                                <input type="text" name="education_board" class="form-control"
                                    value="{{ old('education_board', $student->education_board) }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Last Grades / GPA</label>
                                <input type="text" name="last_grades" class="form-control"
                                    value="{{ old('last_grades', $student->last_grades) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Education Gap (Years)</label>
                                <input type="number" name="gap" class="form-control"
                                    value="{{ old('gap', $student->gap) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Applying For</label>
                                <select name="applying_for" class="form-select">
                                    <option value="">Select Program Type</option>
                                    <option value="Bachelor">Bachelor</option>
                                    <option value="Master">Master</option>
                                    <option value="Diploma">Diploma</option>
                                    <option value="Language Course">Language Course</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Study Preferences --}}
            <div class="form-section">
                <div class="form-section-header">
                    🌍 Study Preferences
                </div>
                <div class="form-section-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Preferred Country</label>
                                <input type="text" name="preferred_country" class="form-control"
                                    value="{{ old('preferred_country', $student->preferred_country) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Preferred City</label>
                                <input type="text" name="preferred_city" class="form-control"
                                    value="{{ old('preferred_city', $student->preferred_city) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Preferred Course</label>
                                <input type="text" name="preferred_course" class="form-control"
                                    value="{{ old('preferred_course', $student->preferred_course) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Preferred University</label>
                                <input type="text" name="preferred_university" class="form-control"
                                    value="{{ old('preferred_university', $student->preferred_university) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tags & Remarks --}}
            <div class="form-section">
                <div class="form-section-header">
                    🏷️ Tags & Remarks
                </div>
                <div class="form-section-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Tags</label>
                                <div class="tag-input-container" id="tagsContainer">
                                    @if ($student->tags && is_array($student->tags))
                                        @foreach ($student->tags as $tag)
                                            <span class="tag">
                                                {{ $tag }}
                                                <span class="tag-remove" onclick="removeTag(this)">×</span>
                                            </span>
                                        @endforeach
                                    @endif
                                    <input type="text" class="tag-input" id="tagInput"
                                        placeholder="Type tag and press Enter..." onkeypress="handleTagKeypress(event)">
                                </div>
                                <input type="hidden" name="tags" id="tagsHidden"
                                    value="{{ json_encode($student->tags ?? []) }}">
                                <small class="text-muted">Press Enter to add a tag</small>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Remarks / Notes</label>
                                <textarea name="remarks" rows="3" class="form-control">{{ old('remarks', $student->remarks) }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Rating (1-3 stars)</label>
                                <select name="rating" class="form-select">
                                    <option value="">No Rating</option>
                                    <option value="1" {{ old('rating', $student->rating) == 1 ? 'selected' : '' }}>⭐
                                        (1 Star)</option>
                                    <option value="2" {{ old('rating', $student->rating) == 2 ? 'selected' : '' }}>⭐⭐
                                        (2 Stars)</option>
                                    <option value="3" {{ old('rating', $student->rating) == 3 ? 'selected' : '' }}>
                                        ⭐⭐⭐ (3 Stars)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Assign to Agent</label>
                            <select class="form-select" id="modal_agent_id" name="agent_id">
                                <option value="">Auto-assign</option>
                                @foreach ($agents ?? [] as $agent)
                                    <option value="{{ $agent->id }}"
                                        {{ old('agent_id', $student->agent_id) == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->business_name ?? $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="form-actions">
                <a href="{{ route('crm.student.show', $student) }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // Photo preview
        document.getElementById('photoInput')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('photoPreview');
                    if (preview) {
                        preview.src = e.target.result;
                    } else {
                        const container = document.querySelector('.photo-preview');
                        if (container) {
                            container.innerHTML =
                                `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
                        }
                    }
                };
                reader.readAsDataURL(file);
            }
        });

        // Tags management
        let tags = [];

        function initializeTags() {
            const hiddenInput = document.getElementById('tagsHidden');
            if (hiddenInput.value) {
                tags = JSON.parse(hiddenInput.value);
            }
            renderTags();
        }

        function renderTags() {
            const container = document.getElementById('tagsContainer');
            const tagSpans = container.querySelectorAll('.tag');
            tagSpans.forEach(span => span.remove());

            tags.forEach(tag => {
                const tagSpan = document.createElement('span');
                tagSpan.className = 'tag';
                tagSpan.innerHTML = `${tag} <span class="tag-remove" onclick="removeTag(this)">×</span>`;
                container.insertBefore(tagSpan, container.querySelector('.tag-input'));
            });

            document.getElementById('tagsHidden').value = JSON.stringify(tags);
        }

        function addTag(tag) {
            tag = tag.trim();
            if (tag && !tags.includes(tag)) {
                tags.push(tag);
                renderTags();
            }
        }

        function removeTag(element) {
            const tagSpan = element.closest('.tag');
            const tagText = tagSpan.textContent.replace('×', '').trim();
            tags = tags.filter(t => t !== tagText);
            renderTags();
        }

        function handleTagKeypress(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                const input = event.target;
                addTag(input.value);
                input.value = '';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', initializeTags);
    </script>
@endpush
