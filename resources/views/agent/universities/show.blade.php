@extends('layouts.app')

@section('content')
<div class=" mt-4">
    <div class="d-flex justify-content-between px-5 mb-3">
        <div class="detail">
            <h2>{{ $university->name }} ({{ $university->short_name ?? '' }})</h2>
            <p><strong>Country:</strong> {{ $university->country }}</p>
            <p><strong>City:</strong> {{ $university->city }}</p>
            <p><strong>Email:</strong> {{ $university->contact_email }}</p>
            <p><strong>Website:</strong> <a href="{{ $university->website }}" target="_blank">{{ $university->website }}</a></p>
            <p>{{ $university->description }}</p>
        </div>
        <div class="business-logo">
            @if($university->university_logo)
            <img src="{{ asset('images/uni_logo/' . $university->university_logo) }}" alt="Logo" class="uni-logo">
            @endif
        </div>
    </div>
</div>
<hr>

<h3>Courses</h3>


@if($university->courses->count())
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Course Code</th>
            <th>Title</th>
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
            <td>{{ $course->duration }}</td>
            <td>{{$course->fee}}</td>
            <td>{{ $course->intakes }}</td>
            <td>{{ $course->moi_requirement }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<p>No courses found for this university.</p>
@endif
</div>
@endsection
