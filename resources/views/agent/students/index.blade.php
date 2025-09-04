@extends('layouts.app')

@section('content')
<div class="student-page">

    {{-- Filter Section --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('agent.students.index') }}" class="filter-form">
            <div class="filter-grid">
                <!-- Search -->
                <div class="filter-field">
                    <label for="search">Search by Name </label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" class="filter-input">
                </div>
                <!-- University -->
                <div class="filter-field">
                    <label for="university">University</label>
                    <select id="university" name="university" class="filter-select">
                        <option value="">All</option>
                        @foreach($universities as $university)
                        <option value="{{ $university->id }}" {{ request('university') == $university->id ? 'selected' : '' }}>
                            {{ $university->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Course -->
                <div class="filter-field">
                    <label for="course_title">Course</label>
                    <select id="course_title" name="course_title" class="filter-select">
                        <option value="">All</option>
                        @foreach($courses as $course)
                        <option value="{{ $course->title }}" {{ request('course_title') == $course->title ? 'selected' : '' }}>
                            {{ $course->title }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div class="filter-field">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="filter-select">
                        <option value="">All</option>
                        @foreach(\App\Models\Student::STATUS as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Actions -->
                <div class="filter-field d-flex justify-content-around align-items-center">
                    <div class="filter-actions">
                        <a href="{{ route('agent.students.index') }}" class="btn btn-clear">Clear All</a>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-apply">Apply </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Students Table --}}
    <div class="table-card">
        <div class="table-header">
            <h2 class="table-title">All Students</h2>
            <a href="{{ route('agent.students.create') }}" class="btn btn-primary add-btn">
                + Add Student
            </a>
        </div>

        <table class="student-table">
            <thead>
                <tr>
                    <th>Profile</th>
                    <th>Name</th>
                    <th>Email / Contact</th>
                    <th>Agent</th>
                    <th>Application Status</th>
                    <th>University</th>
                    <th>Course</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                <tr>
                    <td>
                        @if($student->students_photo)
                        <img src="{{ asset($student->students_photo) }}" alt="photo" class="student-photo">
                        @else
                        <div class="no-photo">No Photo</div>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('agent.students.show',$student->id) }}" class="student-link">
                            {{ $student->first_name }} {{ $student->last_name }}
                        </a>
                    </td>
                    <td>{{ $student->email }} <br> {{ $student->phone_number }}</td>
                    <td>{{ $student->agent?->business_name ?? $student->agent?->username }}</td>
                    <td>
                        @php
                        $statusClass = match($student->student_status) {
                        'accepted' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'secondary',
                        };
                        @endphp
                        <span class="badge bg-{{ $statusClass }}">
                            {{ ucfirst($student->student_status ?? 'N/A') }}
                        </span>
                    </td>
                    <td>{{ $student->university?->name ?? 'N/A' }}</td>
                    <td>{{ $student->course?->title ?? 'N/A' }}</td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('agent.students.edit',$student->id) }}" class="btn btn-edit">Edit</a>
                            <a href="{{ route('agent.documents.create', $student->id) }}" class="btn btn-sm btn-primary">📂 Upload Document</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="empty-row">No students found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination-wrap">
            {{ $students->links() }}
        </div>
    </div>
</div>
@endsection
