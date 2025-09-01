@extends('layouts.app')

@section('content')
<div class="my-4">
    <h2>{{ isset($student) ? 'Edit Student' : 'Add New Student' }}</h2>
    <form action="{{ isset($student) ? route('admin.students.update', $student->id) : route('admin.students.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($student))
        @method('PUT')
        @endif

        <div class="accordion" id="studentAccordion">
            {{-- General Info --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingGeneral">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGeneral">
                        👤 General Info
                    </button>
                </h2>
                <div id="collapseGeneral" class="accordion-collapse collapse show" data-bs-parent="#studentAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>First Name</label>
                                <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $student->first_name ?? '') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $student->last_name ?? '') }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>DOB</label>
                                <input type="date" name="dob" class="form-control" value="{{ old('dob', isset($student->dob) ? $student->dob->format('Y-m-d') : '') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Gender</label>
                                <select name="gender" class="form-select">
                                    @foreach(\App\Models\Student::GENDERS as $gender)
                                    <option value="{{ $gender }}" {{ old('gender', $student->gender ?? '') == $gender ? 'selected' : '' }}>
                                        {{ ucfirst($gender) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Photo</label>
                                <input type="file" name="students_photo" class="form-control">
                                @if(!empty($student->students_photo))
                                <img src="{{ asset($student->students_photo) }}" width="80" class="mt-2 rounded">
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $student->email ?? '') }}">
                        </div>

                        <div class="mb-3">
                            <label>Phone</label>
                            <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $student->phone_number ?? '') }}">
                        </div>

                        <div class="mb-3">
                            <label>Permanent Address</label>
                            <textarea name="permanent_address" class="form-control">{{ old('permanent_address', $student->permanent_address ?? '') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label>Temporary Address</label>
                            <textarea name="temporary_address" class="form-control">{{ old('temporary_address', $student->temporary_address ?? '') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Nationality</label>
                                <input type="text" name="nationality" class="form-control" value="{{ old('nationality', $student->nationality ?? '') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Marital Status</label>
                                <input type="text" name="marital_status" class="form-control" value="{{ old('marital_status', $student->marital_status ?? '') }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Passport Number</label>
                                <input type="text" name="passport_number" class="form-control" value="{{ old('passport_number', $student->passport_number ?? '') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Passport Expiry</label>
                                <input type="date" name="passport_expiry" class="form-control" value="{{ old('passport_expiry', $student->passport_expiry ?? '') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Follow-up Date</label>
                            <input type="date" name="follow_up_date" class="form-control" value="{{ old('follow_up_date', $student->follow_up_date ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Academic Info --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingAcademic">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAcademic">
                        🎓 Academic Info
                    </button>
                </h2>
                <div id="collapseAcademic" class="accordion-collapse collapse" data-bs-parent="#studentAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Qualification</label>
                                <input type="text" name="qualification" class="form-control" value="{{ old('qualification', $student->qualification ?? '') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Passed Year</label>
                                <input type="number" name="passed_year" class="form-control" value="{{ old('passed_year', $student->passed_year ?? '') }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Gap Years</label>
                                <input type="number" name="gap" class="form-control" value="{{ old('gap', $student->gap ?? '') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Last Grades</label>
                                <input type="text" name="last_grades" class="form-control" value="{{ old('last_grades', $student->last_grades ?? '') }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Education Board</label>
                                <input type="text" name="education_board" class="form-control" value="{{ old('education_board', $student->education_board ?? '') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Preferred Country</label>
                                <input type="text" name="preferred_country" class="form-control" value="{{ old('preferred_country', $student->preferred_country ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Application Info --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingApplication">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseApplication">
                        📑 Application Details
                    </button>
                </h2>
                <div id="collapseApplication" class="accordion-collapse collapse" data-bs-parent="#studentAccordion">
                    <div class="accordion-body">
                        <div class="mb-3">
                            <label>Agent</label>
                            <select name="agent_id" class="form-select">
                                <option value="">Select</option>
                                @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ old('agent_id', $student->agent_id ?? '') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->business_name ?? $agent->username }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>University</label>
                            <select name="university_id" class="form-select">
                                <option value="">Select</option>
                                @foreach($universities as $uni)
                                <option value="{{ $uni->id }}" {{ old('university_id', $student->university_id ?? '') == $uni->id ? 'selected' : '' }}>
                                    {{ $uni->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Course</label>
                            <select name="course_id" class="form-select">
                                <option value="">Select</option>
                                @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ old('course_id', $student->course_id ?? '') == $course->id ? 'selected' : '' }}>
                                    {{ $course->title }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Student Status</label>
                            <select name="student_status" class="form-select">
                                @foreach(['created','viewed','applied to university','accepted','rejected','applied to another university','forwarded to embassy'] as $status)
                                <option value="{{ $status }}" {{ old('student_status', $student->student_status ?? '') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control">{{ old('notes', $student->notes ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">{{ isset($student) ? 'Update Student' : 'Add Student' }}</button>
        </div>
    </form>
</div>
@endsection
