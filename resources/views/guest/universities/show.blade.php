@extends('layouts.app')

@section('content')
<div class="container p-4">

    {{-- University Header --}}
    <div class="card shadow-lg border-0 mb-5">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-2">
                    <i class="fas fa-university text-primary me-2"></i>
                    {{ $university->name }}
                    <small class="text-muted">({{ $university->short_name ?? 'N/A' }})</small>
                </h2>
                <p class="mb-1"><i class="fas fa-globe-asia text-secondary me-2"></i><strong>Country:</strong> {{ $university->country }}</p>
                <p class="mb-1"><i class="fas fa-city text-secondary me-2"></i><strong>City:</strong> {{ $university->city ?? 'N/A' }}</p>
                <p class="mb-1"><i class="fas fa-envelope text-secondary me-2"></i><strong>Email:</strong> {{ $university->contact_email ?? 'N/A' }}</p>
                <p class="mb-1"><i class="fas fa-link text-secondary me-2"></i><strong>Website:</strong>
                    <a href="{{ $university->website }}" target="_blank">{{ $university->website }}</a>
                </p>
                <p class="mt-3 text-muted"><i class="fas fa-info-circle text-secondary me-2"></i>{{ $university->description ?? 'No description available.' }}</p>
            </div>

            <div class="text-center">
                @if($university->university_logo)
                <img src="{{ asset('storage/uni_logo/' . $university->university_logo) }}" alt="Logo" class="img-fluid rounded shadow-sm" style="max-height: 120px;">
                @else
                <div class="bg-light text-muted p-4 rounded">
                    <i class="fas fa-university fa-3x"></i>
                    <p class="mt-2 small">No Logo</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Courses Section --}}
    <div class="m-1">
        <h3 class="fw-bold mb-3">
            <i class="fas fa-book-open text-secondary me-2"></i> Courses Offered
        </h3>

        @if($university->courses->count())
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th><i class="fas fa-hashtag me-1"></i> Code</th>
                        <th><i class="fas fa-book me-1"></i> Title</th>
                        <th><i class="fas fa-clock me-1"></i> Duration</th>
                        <th><i class="fas fa-dollar-sign me-1"></i> Fee</th>
                        <th><i class="fas fa-calendar-alt me-1"></i> Intakes</th>
                        <th><i class="fas fa-language me-1"></i> MOI Requirement</th>
                        <th><i class="fas fa-graduation-cap me-1"></i> Scholarships</th>
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
                        <td>{{ $course->scholarships }} </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="alert alert-info">
            <i class="fas fa-exclamation-circle me-2"></i> No courses found for this university.
        </div>
        @endif
    </div>
</div>
@endsection
