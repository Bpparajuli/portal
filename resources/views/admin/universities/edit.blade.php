@extends('layouts.app')

@section('content')
<div class=" p-4">
    <h2>Edit University</h2>

    <form action="{{ route('admin.universities.update', $university->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('admin.universities.form')
        <a href="{{ route('admin.universities.index') }}" class="btn btn-danger">Cancel</a>
        <button type="submit" class="btn btn-success">Update University</button>
    </form>

    <hr>
    <h3>Courses at {{ $university->short_name ?? $university->name }}</h3>
    <div class="d-flex justify-content-between mb-3">
        <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">Add New Course</a>
        <a href="{{ route('admin.universities.index') }}" class="btn btn-secondary">Back to Universities list</a>
    </div>
    @if($university->courses->count())
    <div class="table-responsive">
        <table class=" table table-striped align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Course Code</th>
                    <th>Title</th>
                    <th>Duration</th>
                    <th>Fee</th>
                    <th>Intakes</th>
                    <th>MOI Requirement</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($university->courses as $course)
                <tr>
                    <td><a href="{{ route('admin.courses.show', $course->id) }}">{{ $course->course_code }}</a></td>
                    <td><a href="{{ route('admin.courses.show', $course->id) }}">{{ $course->title }}</a></td>
                    <td>{{ $course->duration }}</td>
                    <td>{{$course->fee }}</td>
                    <td>{{ $course->intakes }}</td>
                    <td>{{ $course->moi_requirement }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-sm btn-dark"><i class="fa fa-pencil"></i></a>
                            <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this course?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No courses found for this university.</p>
        @endif
    </div>
</div>
@endsection
