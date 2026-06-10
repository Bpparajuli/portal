@php $isEdit = isset($student) && $student->exists; @endphp

<div class="form-compact">
    @if(isset($agents))
    <div class="edit-section">
        <div class="edit-section-header" style="border-left-color:#6366f1;"><i class="fas fa-user-tie" style="color:#6366f1;"></i>Assignment</div>
        <div class="edit-section-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Agent</label>
                    <select name="agent_id" class="form-select">
                        <option value="">Unassigned</option>
                        @php $sel = old('agent_id', $student->agent_id ?? null); @endphp
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ $sel == $agent->id ? 'selected' : '' }}>{{ $agent->business_name ?? $agent->username }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Source</label>
                    <input type="text" name="source" class="form-control" value="{{ old('source', $student->source ?? '') }}" placeholder="e.g. Website, Referral">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Rating</label>
                    <select name="rating" class="form-select">
                        @for($i=1;$i<=5;$i++)
                            <option value="{{ $i }}" {{ old('rating', $student->rating) == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Pin</label>
                    <input type="number" name="pin" class="form-control" value="{{ old('pin', $student->pin ?? '') }}" min="0" max="1" step="1" placeholder="0/1">
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="edit-section">
        <div class="edit-section-header" style="border-left-color:#3b82f6;"><i class="fas fa-user" style="color:#3b82f6;"></i>Personal Information</div>
        <div class="edit-section-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $student->first_name) }}" placeholder="Enter first name">
                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name <span class="text-danger">*</span> <span class="text-muted fw-normal">(incl. middle)</span></label>
                    <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $student->last_name) }}" placeholder="Enter last name">
                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="dob" class="form-control" value="{{ old('dob', optional($student->dob)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">— Select —</option>
                        @foreach(\App\Models\Student::GENDERS as $g)
                            <option value="{{ $g }}" {{ old('gender', $student->gender) === $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Marital Status</label>
                    <select name="marital_status" class="form-select">
                        <option value="">— Select —</option>
                        @foreach(\App\Models\Student::MARITAL_STATUSES as $m)
                            <option value="{{ $m }}" {{ old('marital_status', $student->marital_status) === $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nationality</label>
                    <input type="text" name="nationality" class="form-control" value="{{ old('nationality', $student->nationality) }}" placeholder="e.g. Nepalese">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tags <span class="text-muted fw-normal">(comma separated)</span></label>
                    <input type="text" name="tags" class="form-control" value="{{ old('tags', is_array($student->tags) ? implode(', ', $student->tags) : $student->tags) }}" placeholder="e.g. urgent, vip, scholarship">
                </div>
            </div>
        </div>
    </div>

    <div class="edit-section">
        <div class="edit-section-header" style="border-left-color:#10b981;"><i class="fas fa-address-card" style="color:#10b981;"></i>Contact</div>
        <div class="edit-section-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $student->email) }}" placeholder="student@example.com">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $student->phone_number) }}" placeholder="+977-XXXXXXXXX">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Permanent Address</label>
                    <input type="text" name="permanent_address" class="form-control" value="{{ old('permanent_address', $student->permanent_address) }}" placeholder="Street, City, Country">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Temporary Address</label>
                    <input type="text" name="temporary_address" class="form-control" value="{{ old('temporary_address', $student->temporary_address) }}" placeholder="If different from permanent">
                </div>
            </div>
        </div>
    </div>

    <div class="edit-section">
        <div class="edit-section-header" style="border-left-color:#8b5cf6;"><i class="fas fa-passport" style="color:#8b5cf6;"></i>Passport</div>
        <div class="edit-section-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Passport Number</label>
                    <input type="text" name="passport_number" class="form-control" value="{{ old('passport_number', $student->passport_number) }}" placeholder="e.g. PA1234567">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Expiry Date</label>
                    <input type="date" name="passport_expiry" class="form-control" value="{{ old('passport_expiry', optional($student->passport_expiry)->format('Y-m-d')) }}">
                </div>
            </div>
        </div>
    </div>

    <div class="edit-section">
        <div class="edit-section-header" style="border-left-color:#f59e0b;"><i class="fas fa-graduation-cap" style="color:#f59e0b;"></i>Academic</div>
        <div class="edit-section-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Qualification</label>
                    <input type="text" name="qualification" class="form-control" value="{{ old('qualification', $student->qualification) }}" placeholder="e.g. Bachelor's">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Passed Year</label>
                    <input type="number" name="passed_year" class="form-control" value="{{ old('passed_year', $student->passed_year) }}" min="1990" max="{{ date('Y') }}" placeholder="YYYY">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Gap (yrs)</label>
                    <input type="number" name="gap" class="form-control" value="{{ old('gap', $student->gap) }}" min="0" step="1" placeholder="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Grades</label>
                    <input type="text" name="last_grades" class="form-control" value="{{ old('last_grades', $student->last_grades) }}" placeholder="e.g. 3.5 GPA">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Board</label>
                    <input type="text" name="education_board" class="form-control" value="{{ old('education_board', $student->education_board) }}" placeholder="e.g. TU">
                </div>
            </div>
        </div>
    </div>

    <div class="edit-section">
        <div class="edit-section-header" style="border-left-color:#06b6d4;"><i class="fas fa-globe-americas" style="color:#06b6d4;"></i>Preferences</div>
        <div class="edit-section-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Preferred Country</label>
                    <input type="text" name="preferred_country" class="form-control" value="{{ old('preferred_country', $student->preferred_country) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Preferred City</label>
                    <input type="text" name="preferred_city" class="form-control" value="{{ old('preferred_city', $student->preferred_city) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Preferred Course</label>
                    <input type="text" name="preferred_course" class="form-control" value="{{ old('preferred_course', $student->preferred_course) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Preferred University</label>
                    <input type="text" name="preferred_university" class="form-control" value="{{ old('preferred_university', $student->preferred_university) }}">
                </div>
            </div>
        </div>
    </div>

    <div class="edit-section">
        <div class="edit-section-header" style="border-left-color:#ec4899;"><i class="fas fa-pen-to-square" style="color:#ec4899;"></i>Remarks &amp; Photo</div>
        <div class="edit-section-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Remarks / Notes</label>
                    <textarea name="remarks" rows="3" class="form-control" placeholder="Add any remarks or notes about this student...">{{ old('remarks', $student->remarks) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label d-block">Photo</label>
                    @if($isEdit && $student->students_photo)
                        <img src="{{ Storage::url($student->students_photo) }}" id="editPhotoPreview" style="width:72px;height:72px;object-fit:cover;border-radius:10px;margin-bottom:6px;display:block;border:1px solid #e5e7eb;" alt="">
                    @else
                        <div id="editPhotoPlaceholder" style="width:72px;height:72px;border-radius:10px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;margin-bottom:6px;border:1px dashed #d1d5db;"><i class="fas fa-camera text-muted" style="font-size:1.2rem;"></i></div>
                        <img id="editPhotoPreview" style="width:72px;height:72px;object-fit:cover;border-radius:10px;margin-bottom:6px;display:none;border:1px solid #e5e7eb;" alt="">
                    @endif
                    <input type="file" name="students_photo" id="editPhotoInput" class="form-control" accept="image/jpeg,image/png,image/jpg" style="font-size:0.82rem;padding:6px 10px;">
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var input = document.getElementById('editPhotoInput');
    if (!input) return;
    input.addEventListener('change', function() {
        var f = this.files[0];
        if (!f) return;
        var r = new FileReader();
        r.onload = function(e) {
            var prev = document.getElementById('editPhotoPreview');
            var ph = document.getElementById('editPhotoPlaceholder');
            if (prev) { prev.src = e.target.result; prev.style.display = 'block'; }
            if (ph) ph.style.display = 'none';
        };
        r.readAsDataURL(f);
    });
})();
</script>
@endpush