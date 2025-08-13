@extends('layout.app')

@section('content')
<div class="container mt-4">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>University List</h2>
        <a href="{{ route('universities.create') }}" class="btn btn-primary">Add New University</a>
    </div>

    @if($universities->count())
    <table class="table table-striped table-hover border">
        <thead class="bg-primary text-white">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Short Name</th>
                <th>Country</th>
                <th>City</th>
                <th>Website</th>
                <th>Contact Email</th>
                <th>Actions</th>
                <th>Courses</th>
            </tr>
        </thead>
        <tbody>
            @foreach($universities as $university)
            <tr>
                <td>{{ $university->id }}</td>
                <td>{{ $university->name }}</td>
                <td>{{ $university->short_name ?? 'N/A' }}</td>
                <td>{{ $university->country }}</td>
                <td>{{ $university->city ?? 'N/A' }}</td>
                <td>
                    @if($university->website)
                    <a href="{{ $university->website }}" target="_blank">{{ $university->website }}</a>
                    @else
                    N/A
                    @endif
                </td>
                <td>{{ $university->contact_email ?? 'N/A' }}</td>
                <td>
                    <a href="{{ route('universities.edit', $university->id) }}" class="btn btn-sm btn-info me-1">Edit</a>
                    <form action="{{ route('universities.destroy', $university->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this university?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
                <td>
                    @if($university->courses->count())
                    <button class="btn btn-sm btn-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#coursesCollapse{{ $university->id }}" aria-expanded="false">
                        View Courses ({{ $university->courses->count() }})
                    </button>
                    @else
                    <span class="text-muted">No Courses</span>
                    @endif
                </td>
            </tr>

            {{-- Collapsible Courses Row --}}
            <tr class="collapse" id="coursesCollapse{{ $university->id }}">
                <td colspan="9">
                    <div class="card card-body bg-light mt-2 mb-2 p-2">
                        @if($university->courses->count())
                        <h6>Courses at {{ $university->short_name ?? $university->name }}:</h6>
                        <table class="table table-sm table-bordered bg-white">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Course Code</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Duration</th>
                                    <th>Fee</th>
                                    <th>Intakes</th>
                                    <th>MOI Requirement</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($university->courses as $course)
                                <tr>
                                    <td>{{ $course->course_code }}</td>
                                    <td>{{ $course->title }}</td>
                                    <td>{{ $course->description ?? 'N/A' }}</td>
                                    <td>{{ $course->duration ?? 'N/A' }}</td>
                                    <td>${{ number_format($course->fee, 2) }}</td>
                                    <td>{{ $course->intakes ?? 'N/A' }}</td>
                                    <td>{{ $course->moi_requirement ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <p class="text-muted text-center">No courses listed for {{ $university->name }}.</p>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center">
        {{ $universities->links() }}
    </div>
    @else
    <p class="alert alert-info">No universities found.</p>
    @endif
</div>
@endsection
