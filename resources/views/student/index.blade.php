@extends('layout.app')

@section('content')
<div class="p-2">
    <h3>My Students</h3>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-3">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Search name or ID" value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                <option value="approved" {{ request('status')=='approved'?'selected':'' }}>Approved</option>
                <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>Rejected</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="university_id" class="form-select">
                <option value="">All Universities</option>
                @foreach($universities as $uni)
                <option value="{{ $uni->id }}" {{ request('university_id')==$uni->id?'selected':'' }}>
                    {{ $uni->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="course_id" class="form-select">
                <option value="">All Courses</option>
                @foreach($courses as $course)
                <option value="{{ $course->id }}" {{ request('course_id')==$course->id?'selected':'' }}>
                    {{ $course->title }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary">Filter</button>
        </div>
    </form>

    <!-- Students Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>DOB</th>
                <th>Preferred Country</th>
                <th>Current Status</th>
                <th>Last Application</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
            <tr>
                <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                <td>{{ $student->dob }}</td>
                <td>{{ $student->preferred_country }}</td>
                <td>{{ ucfirst($student->student_status) }}</td>
                <td>{{ $student->applications->first()->created_at ?? 'N/A' }}</td>
                <td>
                    <a href="{{ route('student.show', $student->id) }}" class="btn btn-sm btn-info">View</a>
                    <a href="{{ route('student.edit', $student->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <a href="{{ route('student.apply', $student->id) }}" class="btn btn-sm btn-success">Apply Now</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $students->links() }}
</div>
@endsection
