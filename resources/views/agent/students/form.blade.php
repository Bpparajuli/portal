@php
    $student = $student ?? new \App\Models\Student();
    $isEdit = isset($student) && $student->exists;
@endphp

<div class="row g-0">
    <div class="col-12">

        {{-- ── Personal Information ───────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">
                <i class="fas fa-user text-primary"></i> Personal Information
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name"
                            class="form-control @error('first_name') is-invalid @enderror"
                            value="{{ old('first_name', $student->first_name) }}" placeholder="First name">
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">
                            Last Name <span class="text-danger">*</span>
                            <span class="text-muted fw-normal">(include middle name here if any)</span>
                        </label>
                        <input type="text" name="last_name"
                            class="form-control @error('last_name') is-invalid @enderror"
                            value="{{ old('last_name', $student->last_name) }}" placeholder="Last name">
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Date of Birth</label>
                        <input type="date" name="dob" class="form-control"
                            value="{{ old('dob', optional($student->dob)->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">Select…</option>
                            @foreach (\App\Models\Student::GENDERS as $g)
                                <option value="{{ $g }}"
                                    {{ old('gender', $student->gender) === $g ? 'selected' : '' }}>
                                    {{ $g }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Marital Status</label>
                        <select name="marital_status" class="form-select">
                            <option value="">Select…</option>
                            @foreach (\App\Models\Student::MARITAL_STATUSES as $m)
                                <option value="{{ $m }}"
                                    {{ old('marital_status', $student->marital_status) === $m ? 'selected' : '' }}>
                                    {{ $m }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Nationality</label>
                        <input type="text" name="nationality" class="form-control"
                            value="{{ old('nationality', $student->nationality) }}" placeholder="e.g. Nepalese">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Contact Information ─────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">
                <i class="fas fa-address-card text-success"></i> Contact Information
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Email Address</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $student->email) }}" placeholder="student@example.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control"
                            value="{{ old('phone_number', $student->phone_number) }}" placeholder="+977 98XXXXXXXX">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Permanent Address</label>
                        <input type="text" name="permanent_address" class="form-control"
                            value="{{ old('permanent_address', $student->permanent_address) }}"
                            placeholder="Full permanent address">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Temporary Address</label>
                        <input type="text" name="temporary_address" class="form-control"
                            value="{{ old('temporary_address', $student->temporary_address) }}"
                            placeholder="Current address if different">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Passport Information ────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">
                <i class="fas fa-passport text-purple"></i> Passport Information
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Passport Number</label>
                        <input type="text" name="passport_number" class="form-control"
                            value="{{ old('passport_number', $student->passport_number) }}" placeholder="PA1234567">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Passport Expiry Date</label>
                        <input type="date" name="passport_expiry" class="form-control"
                            value="{{ old('passport_expiry', optional($student->passport_expiry)->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Academic Information ────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">
                <i class="fas fa-graduation-cap text-warning"></i> Academic Information
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Qualification</label>
                        <input type="text" name="qualification" class="form-control"
                            value="{{ old('qualification', $student->qualification) }}"
                            placeholder="e.g. Bachelor's Degree">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Passed Year</label>
                        <input type="number" name="passed_year" class="form-control"
                            value="{{ old('passed_year', $student->passed_year) }}" placeholder="e.g. 2022"
                            min="1990" max="{{ date('Y') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Gap (years)</label>
                        <input type="number" name="gap" class="form-control"
                            value="{{ old('gap', $student->gap) }}" placeholder="0" min="0" max="20">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Last Grades / GPA</label>
                        <input type="text" name="last_grades" class="form-control"
                            value="{{ old('last_grades', $student->last_grades) }}"
                            placeholder="e.g. 3.6/4.0 or 75%">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Education Board</label>
                        <input type="text" name="education_board" class="form-control"
                            value="{{ old('education_board', $student->education_board) }}"
                            placeholder="e.g. CBSE / Cambridge / NEB">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Study Preferences ───────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">
                <i class="fas fa-globe-americas text-info"></i> Study Preferences
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Preferred Country</label>
                        <input type="text" name="preferred_country" class="form-control"
                            value="{{ old('preferred_country', $student->preferred_country) }}"
                            placeholder="e.g. Canada, Australia">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Preferred City</label>
                        <input type="text" name="preferred_city" class="form-control"
                            value="{{ old('preferred_city', $student->preferred_city) }}"
                            placeholder="e.g. Toronto, Sydney">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Preferred Course</label>
                        <input type="text" name="preferred_course" class="form-control"
                            value="{{ old('preferred_course', $student->preferred_course) }}"
                            placeholder="e.g. Computer Science">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Preferred University</label>
                        <input type="text" name="preferred_university" class="form-control"
                            value="{{ old('preferred_university', $student->preferred_university) }}"
                            placeholder="University name">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Remarks & Photo ─────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">
                <i class="fas fa-pen-to-square text-secondary"></i> Remarks &amp; Photo
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold small">Remarks</label>
                        <textarea name="remarks" rows="4" class="form-control" placeholder="Any additional notes about this student…">{{ old('remarks', $student->remarks) }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small d-block">Student Photo</label>

                        {{-- Current photo (edit mode) --}}
                        @if ($isEdit && $student->students_photo)
                            <img src="{{ Storage::url($student->students_photo) }}" class="photo-thumb mb-2 d-block"
                                alt="Current Photo">
                            <div class="text-muted small mb-2">Upload a new image to replace</div>
                        @else
                            <div class="photo-placeholder mb-2" id="photoPlaceholder">
                                <i class="fas fa-camera text-muted fa-2x"></i>
                            </div>
                            <img id="photoPreview" class="photo-thumb mb-2 d-none" alt="Preview">
                        @endif

                        <input type="file" name="students_photo" id="studentPhoto"
                            class="form-control form-control-sm" accept="image/jpeg,image/png,image/jpg">
                        <div class="text-muted" style="font-size:0.7rem; margin-top:4px;">JPG/PNG, max 5MB</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
    <script>
        (function() {
            const input = document.getElementById('studentPhoto');
            if (!input) return;

            input.addEventListener('change', function() {
                const file = this.files[0];
                const preview = document.getElementById('photoPreview');
                const placeholder = document.getElementById('photoPlaceholder');

                if (file && preview) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        preview.src = e.target.result;
                        preview.classList.remove('d-none');
                        if (placeholder) placeholder.classList.add('d-none');
                    };
                    reader.readAsDataURL(file);
                } else if (preview) {
                    preview.classList.add('d-none');
                    preview.src = '';
                    if (placeholder) placeholder.classList.remove('d-none');
                }
            });
        }());
    </script>
@endpush
