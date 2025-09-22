@csrf

<div class="row">
    {{-- First & Last Name --}}
    <div class="col-md-6 mb-3">
        <label for="first_name">First Name *</label>
        <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name', $student->first_name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="last_name">Last Name *</label>
        <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name', $student->last_name ?? '') }}" required>
    </div>
    {{-- DOB & Gender --}}
    <div class="col-md-6 mb-3">
        <label for="dob">Date of Birth</label>
        <input type="date" name="dob" id="dob" class="form-control" value="{{ old('dob', $student->dob ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="gender">Gender</label>
        <select name="gender" id="gender" class="form-control">
            <option value="">-- Select --</option>
            <option value="Male" {{ old('gender', $student->gender ?? '')=='Male'?'selected':'' }}>Male</option>
            <option value="Female" {{ old('gender', $student->gender ?? '')=='Female'?'selected':'' }}>Female</option>
            <option value="Other" {{ old('gender', $student->gender ?? '')=='Other'?'selected':'' }}>Other</option>
        </select>
    </div>

    {{-- Email & Phone --}}
    <div class="col-md-6 mb-3">
        <label for="email">Email *</label>
        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $student->email ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="phone_number">Phone Number</label>
        <input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ old('phone_number', $student->phone_number ?? '') }}">
    </div>

    {{-- Permanent & Temporary Address --}}
    <div class="col-md-6 mb-3">
        <label for="permanent_address">Permanent Address</label>
        <input type="text" name="permanent_address" id="permanent_address" class="form-control" value="{{ old('permanent_address', $student->permanent_address ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="temporary_address">Temporary Address</label>
        <input type="text" name="temporary_address" id="temporary_address" class="form-control" value="{{ old('temporary_address', $student->temporary_address ?? '') }}">
    </div>

    {{-- Nationality & Marital Status --}}
    <div class="col-md-6 mb-3">
        <label for="nationality">Nationality</label>
        <input type="text" name="nationality" id="nationality" class="form-control" value="{{ old('nationality', $student->nationality ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="marital_status">Marital Status</label>
        <select name="marital_status" id="marital_status" class="form-control">
            <option value="">-- Select --</option>
            <option value="Single" {{ old('marital_status', $student->marital_status ?? '')=='Single'?'selected':'' }}>Single</option>
            <option value="Married" {{ old('marital_status', $student->marital_status ?? '')=='Married'?'selected':'' }}>Married</option>
        </select>
    </div>

    {{-- Passport Number & Expiry --}}
    <div class="col-md-6 mb-3">
        <label for="passport_number">Passport Number</label>
        <input type="text" name="passport_number" id="passport_number" class="form-control" value="{{ old('passport_number', $student->passport_number ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="passport_expiry">Passport Expiry</label>
        <input type="date" name="passport_expiry" id="passport_expiry" class="form-control" value="{{ old('passport_expiry', $student->passport_expiry ?? '') }}">
    </div>

    {{-- Education --}}
    <div class="col-md-6 mb-3">
        <label for="qualification">Qualification</label>
        <input type="text" name="qualification" id="qualification" class="form-control" value="{{ old('qualification', $student->qualification ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label for="passed_year">Passed Year</label>
        <input type="text" name="passed_year" id="passed_year" class="form-control" value="{{ old('passed_year', $student->passed_year ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label for="gap">Gap (years)</label>
        <input type="number" name="gap" id="gap" class="form-control" value="{{ old('gap', $student->gap ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="last_grades">Last Grades</label>
        <input type="text" name="last_grades" id="last_grades" class="form-control" value="{{ old('last_grades', $student->last_grades ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="education_board">Education Board</label>
        <input type="text" name="education_board" id="education_board" class="form-control" value="{{ old('education_board', $student->education_board ?? '') }}">
    </div>

    {{-- Preferred Country & Course --}}
    <div class="col-md-6 mb-3">
        <label for="preferred_country">Preferred Country</label>
        <input type="text" name="preferred_country" id="preferred_country" class="form-control" value="{{ old('preferred_country', $student->preferred_country ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="preferred_course">Preferred Course</label>
        <input type="text" name="preferred_course" id="preferred_course" class="form-control" value="{{ old('preferred_course', $student->preferred_course ?? '') }}">
    </div>

    {{-- University & Course --}}
    <h6>Only if Preferred any </h6>

    <div class="col-md-6 mb-3">
        <label for="university_id">University</label>
        <select name="university_id" id="university_id" class="form-control">
            <option value="">-- Select University --</option>
            @foreach($universities as $uni)
            <option value="{{ $uni->id }}" {{ old('university_id', $student->university_id ?? '')==$uni->id?'selected':'' }}>
                {{ $uni->name }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="course_id">Course</label>
        <select name="course_id" id="course_id" class="form-control">
            <option value="">-- Select Course --</option>
            @foreach($courses as $course)
            <option value="{{ $course->id }}" {{ old('course_id', $student->course_id ?? '')==$course->id?'selected':'' }}>
                {{ $course->name }}
            </option>
            @endforeach
        </select>
    </div>

    {{-- Status, Notes, Follow-up --}}

    <div class="col-md-12 mb-3">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $student->notes ?? '') }}</textarea>
    </div>

    {{-- Student Photo --}}
    <div class="col-md-12 mb-3">
        <label for="students_photo">Student Photo</label>
        <input type="file" name="students_photo" id="students_photo" class="form-control">
        @if(!empty($student->students_photo))
        <img src="{{ asset('storage/'.$student->students_photo) }}" class="mt-2 rounded" width="120">
        @endif
    </div>
</div>
