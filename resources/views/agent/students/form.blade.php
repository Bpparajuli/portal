<div class="mb-3">
    <label>First Name</label>
    <input type="text" name="first_name" class="form-control" value="{{ old('first_name',$student->first_name ?? '') }}">
</div>
<div class="mb-3">
    <label>Last Name</label>
    <input type="text" name="last_name" class="form-control" value="{{ old('last_name',$student->last_name ?? '') }}">
</div>
<div class="mb-3">
    <label>DOB</label>
    <input type="date" name="dob" class="form-control" value="{{ old('dob',$student->dob ?? '') }}">
</div>
<div class="mb-3">
    <label>Gender</label>
    <select name="gender" class="form-select">
        <option value="Male" {{ old('gender',$student->gender ?? '')=='Male' ? 'selected' : '' }}>Male</option>
        <option value="Female" {{ old('gender',$student->gender ?? '')=='Female' ? 'selected' : '' }}>Female</option>
        <option value="Other" {{ old('gender',$student->gender ?? '')=='Other' ? 'selected' : '' }}>Other</option>
    </select>
</div>
<div class="mb-3">
    <label>Email</label>
    <input type="email" name="email" class="form-control" value="{{ old('email',$student->email ?? '') }}">
</div>
<div class="mb-3">
    <label>Phone</label>
    <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number',$student->phone_number ?? '') }}">
</div>
<div class="mb-3">
    <label>Address</label>
    <textarea name="address" class="form-control">{{ old('address',$student->address ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label>Passport Number</label>
    <input type="text" name="passport_number" class="form-control" value="{{ old('passport_number',$student->passport_number ?? '') }}">
</div>
<div class="mb-3">
    <label>Preferred Country</label>
    <input type="text" name="preferred_country" class="form-control" value="{{ old('preferred_country',$student->preferred_country ?? '') }}">
</div>
<div class="mb-3">
    <label>Nationality</label>
    <input type="text" name="nationality" class="form-control" value="{{ old('nationality',$student->nationality ?? '') }}">
</div>
<div class="mb-3">
    <label>University</label>
    <select name="university_id" class="form-select">
        <option value="">Select University</option>
        @foreach($universities as $uni)
        <option value="{{ $uni->id }}" {{ old('university_id',$student->university_id ?? '')==$uni->id ? 'selected':'' }}>{{ $uni->name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label>Course</label>
    <select name="course_id" class="form-select">
        <option value="">Select Course</option>
        @foreach($courses as $course)
        <option value="{{ $course->id }}" {{ old('course_id',$student->course_id ?? '')==$course->id ? 'selected':'' }}>{{ $course->title }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label>Notes</label>
    <textarea name="notes" class="form-control">{{ old('notes',$student->notes ?? '') }}</textarea>
</div>

@if(auth()->user()->is_admin)
<div class="mb-3">
    <label>Student Status</label>
    <select name="student_status" class="form-select">
        <option value="pending" {{ old('student_status',$student->student_status ?? '')=='pending' ? 'selected':'' }}>Pending</option>
        <option value="approved" {{ old('student_status',$student->student_status ?? '')=='approved' ? 'selected':'' }}>Approved</option>
        <option value="rejected" {{ old('student_status',$student->student_status ?? '')=='rejected' ? 'selected':'' }}>Rejected</option>
    </select>
</div>
@endif
