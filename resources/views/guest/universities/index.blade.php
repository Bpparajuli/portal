@extends('layouts.app')

@section('content')

<h2>University List</h2>

@if($universities->count())
<table class="table table-bordered table-striped">
    <thead class="bg-secondary text-white">
        <tr>
            <th class="text-white">ID</th>
            <th class="text-white">University Logo</th>
            <th class="text-white">Name</th>
            <th class="text-white">Short Name</th>
            <th class="text-white">Country</th>
            <th class="text-white">City</th>
            <th class="text-white">Website</th>
            <th class="text-white">Contact Email</th>
            <th class="text-white">Courses</th>
        </tr>
    </thead>
    <tbody>
        @foreach($universities as $university)
        <tr>
            <td onclick="window.location='{{ route('guest.universities.show', $university->id) }}'" style="cursor:pointer;">{{ $university->id }}</td>
            <td onclick="window.location='{{ route('guest.universities.show', $university->id) }}'" style="cursor:pointer;">
                @if($university->university_logo)
                <img src="{{ asset('images/uni_logo/' . $university->university_logo) }}" width="40" alt="Logo">
                @endif
            </td>
            <td onclick="window.location='{{ route('guest.universities.show', $university->id) }}'" style="cursor:pointer;">{{ $university->name }}</td>
            <td onclick="window.location='{{ route('guest.universities.show', $university->id) }}'" style="cursor:pointer;">{{ $university->short_name ?? 'N/A' }}</td>
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
                @if($university->courses->count())
                <button class="badge bg-success" type="button" data-bs-toggle="collapse" data-bs-target="#coursesCollapse{{ $university->id }}" aria-expanded="false">
                    View <br>Courses ({{ $university->courses->count() }})
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
@endsection
