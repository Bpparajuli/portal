@extends('layouts.guest')
@php $role = 'guest'; @endphp

@section('content')
<div class="container py-4">
    <a href="{{ route('guest.universities.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="fas fa-arrow-left"></i> Back to Universities
    </a>

    <div class="card shadow-lg border-0 mb-5" data-aos="fade-up">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
            <div class="mb-3 mb-md-0">
                <h2 class="fw-bold mb-2 text-primary">
                    <i class="fas fa-university me-2"></i> {{ $university->name }}
                    @if($university->short_name)
                        <small class="text-muted">({{ $university->short_name }})</small>
                    @endif
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
                    <img src="{{ asset('storage/uni_logo/' . $university->university_logo) }}" alt="University Logo"
                         class="img-fluid rounded shadow-sm border" style="max-height: 130px;">
                @else
                    <div class="bg-light text-muted p-4 rounded">
                        <i class="fas fa-university fa-3x"></i>
                        <p class="mt-2 small">No Logo</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card shadow-lg border-0 mb-5">
        <h3 class="fw-bold text-secondary mb-4 px-4 pt-4">
            <i class="fas fa-book-open me-2"></i> Courses Offered
        </h3>

        @if($university->courses->count())
        <div class="table-responsive px-4 pb-4">
            <table class="table table-hover table-striped align-middle shadow-sm">
                <thead class="table-dark text-center">
                    <tr>
                        <th>Course Code</th>
                        <th>Title</th>
                        <th>Duration</th>
                        <th>Fee</th>
                        <th>Intakes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach($university->courses as $course)
                    <tr>
                        <td>{{ $course->course_code ?? '&mdash;' }}</td>
                        <td>
                            <a href="{{ route('guest.courses.show', $course->id) }}" class="fw-semibold">
                                {{ $course->title }}
                            </a>
                        </td>
                        <td>{{ $course->duration ?? '&mdash;' }}</td>
                        <td>@if($course->fee)<span class="text-success fw-bold">{{ $course->fee }}</span>@else <span class="text-muted">&mdash;</span>@endif</td>
                        <td>
                            @php
                                $intakeList = is_array($course->intakes) ? $course->intakes : explode(',', $course->intakes ?? '');
                            @endphp
                            @foreach(array_slice($intakeList, 0, 2) as $intake)
                                <span class="badge bg-info me-1">{{ trim($intake) }}</span>
                            @endforeach
                            @if(count($intakeList) > 2)
                                <span class="badge bg-secondary">+{{ count($intakeList) - 2 }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('guest.courses.show', $course->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="alert alert-info shadow-sm mx-4 mb-4">
            <i class="fas fa-exclamation-circle me-2"></i> No courses found for this university.
        </div>
        @endif
    </div>
</div>
@endsection
