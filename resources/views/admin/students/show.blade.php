@extends('layouts.app')
@section('content')
<div class="mt-4">
    <div class="card shadow-lg rounded">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Student Profile</h4>
        </div>
        <div class="card-body">
            @if($student)
            <div class="row">
                <div class="col-md-4 text-center">
                    <img src="{{ $student->profile_picture ?? 'https://via.placeholder.com/150' }}" alt="Profile" class="img-fluid rounded-circle mb-3" width="150">
                    <h5>{{ $student->first_name }} {{ $student->last_name }}</h5>
                    <p class="text-muted">Student ID: {{ $student->id }}</p>
                    <p class="text-muted">Agent: {{ $student->agent->business_name ?? $student->agent->username ?? 'N/A' }}</p>
                </div>

                <div class="col-md-8">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Full Name</th>
                                <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $student->email }}</td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td>{{ $student->phone_number }}</td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td>{{ $student->address }}</td>
                            </tr>
                            <tr>
                                <th>Date of Birth</th>
                                <td>{{ $student->dob ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>University</th>
                                <td>{{ $student->university->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Course</th>
                                <td>{{ $student->course->title ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>{{ \App\Models\Student::getStatusLabel($student->student_status) }}</td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td>{{ $student->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-warning me-2">Edit</a>
                        <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </div>
            @else
            <p class="text-danger">No student data found.</p>
            @endif
        </div>
    </div>
</div>
@endsection
