@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- University Header --}}
    <div class="card shadow-lg border-0 mb-5">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
            <div class="mb-3 mb-md-0">
                <h2 class="fw-bold mb-2 text-primary">
                    <i class="fas fa-university me-2"></i> {{ $university->name }}
                    <small class="text-muted">({{ $university->short_name ?? 'N/A' }})</small>
                </h2>
                <p class="mb-1"><i class="fas fa-globe text-secondary me-2"></i><strong>Country:</strong> {{ $university->country }}</p>
                <p class="mb-1"><i class="fas fa-city text-secondary me-2"></i><strong>City:</strong> {{ $university->city ?? 'N/A' }}</p>
                <p class="mb-1"><i class="fas fa-envelope text-secondary me-2"></i><strong>Email:</strong> {{ $university->contact_email ?? 'N/A' }}</p>
                <p class="mb-1"><i class="fas fa-link text-secondary me-2"></i><strong>Website:</strong>
                    <a href="{{ $university->website }}" target="_blank" class="text-decoration-none text-info">{{ $university->website }}</a>
                </p>
                <p class="mt-3 text-muted"><i class="fas fa-info-circle text-secondary me-2"></i>{{ $university->description ?? 'No description available.' }}</p>
            </div>

            <div class="text-center">
                @if($university->university_logo)
                <img src="{{ asset('storage/uni_logo/' . $university->university_logo) }}" alt="University Logo" class="img-fluid rounded shadow-sm border" style="max-height: 130px;">
                @else
                <div class="bg-light text-muted p-4 rounded">
                    <i class="fas fa-university fa-3x"></i>
                    <p class="mt-2 small">No Logo</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Admin Actions --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-secondary mb-0">
            <i class="fas fa-book-open me-2"></i> Courses Offered
        </h3>

        <a href="{{ route('admin.courses.create', ['university_id' => $university->id]) }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-2"></i> Add New Course
        </a>
    </div>

    {{-- Courses Table --}}
    @if($university->courses->count())
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle shadow-sm">
            <thead class="table-dark text-center">
                <tr>
                    <th><i class="fas fa-hashtag me-1"></i> Code</th>
                    <th><i class="fas fa-book me-1"></i> Title</th>
                    <th><i class="fas fa-clock me-1"></i> Duration</th>
                    <th><i class="fas fa-dollar-sign me-1"></i> Fee</th>
                    <th><i class="fas fa-calendar-alt me-1"></i> Intakes</th>
                    <th><i class="fas fa-language me-1"></i> MOI</th>
                    <th><i class="fas fa-graduation-cap me-1"></i> Scholarships</th>
                    <th><i class="fas fa-cogs me-1"></i> Actions</th>
                </tr>
            </thead>
            <tbody class="text-center">
                @foreach($university->courses as $course)
                <tr>
                    <td>{{ $course->course_code }}</td>
                    <td class="fw-semibold">{{ $course->title }}</td>
                    <td>{{ $course->duration }}</td>
                    <td>{{ $course->fee }}</td>
                    <td>{{ $course->intakes }}</td>
                    <td>{{ $course->moi_requirement }}</td>
                    <td>{{ $course->scholarships ?? '-' }}</td>
                    <td>
                        <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-sm btn-outline-info me-1">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if(auth()->id() === 1)

                        <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this course?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-info shadow-sm">
        <i class="fas fa-exclamation-circle me-2"></i> No courses found for this university.
    </div>
    @endif
</div>

{{-- Optional Styling --}}
<style>
    .table th,
    .table td {
        vertical-align: middle;
    }

    .table-hover tbody tr:hover {
        background-color: #f1f3f5;
        transition: 0.2s;
    }

    .btn-outline-info:hover {
        background-color: #17a2b8;
        color: #fff;
    }

    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: #fff;
    }

</style>
@endsection
