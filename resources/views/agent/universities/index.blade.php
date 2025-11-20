@extends('layouts.app')

@section('content')
<div class="uni-page">

    {{-- Filter Section --}}
    @include('partials.uni_filter')

    {{-- Universities Grid --}}
    <div class="uni-grid">
        @forelse($universities as $uni)
        <div class="uni-col">
            <div class="uni-card">

                {{-- Header --}}
                <div class="uni-card-header">
                    <a href="{{ route('agent.universities.show', $uni->id) }}">
                        @if($uni->university_logo)
                        <img src="{{ asset('storage/uni_logo/'.$uni->university_logo) }}" class="uni-logo-full" alt="{{ $uni->name }}">
                        @else
                        <div class="uni-no-logo">
                            <i class="fas fa-university fa-2x"></i>
                            <p>No Logo</p>
                        </div>
                        @endif
                    </a>
                </div>

                {{-- Body --}}
                <div class="uni-card-body">
                    <a href="{{ route('agent.universities.show', $uni->id) }}" class="uni-name-link">
                        <p class="uni-name">{{ $uni->name }}</p>
                        <p class="uni-short-name">{{ $uni->short_name ?? 'N/A' }}- {{ $uni->city ?? 'N/A' }}</p>
                    </a>

                    <p class="uni-location"><i class="fas fa-map-marker-alt"></i> {{ $uni->country }}</p>

                    @if($uni->website)
                    <p class="uni-website">
                        <a href="{{ $uni->website }}" target="_blank" class="uni-web-link">
                            <i class="fas fa-globe"></i> {{ $uni->website }}
                        </a>
                    </p>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="uni-card-footer">
                    @if($uni->courses->count())
                    <button class="uni-btn-outline" onclick="openCourseModal({{ $uni->id }})">
                        <i class="fas fa-book-open"></i> {{ $uni->courses->count() }} Courses
                    </button>
                    @endif

                    <a href="{{ route('agent.applications.create') }}?university_id={{ $uni->id }}" class="uni-btn-success">
                        Apply Now <i class="fa-solid fa-paper-plane"></i>
                    </a>
                </div>

            </div>
        </div>

        {{-- Courses Modal --}}
        <div id="courseModal{{ $uni->id }}" class="uni-modal">
            <div class="uni-modal-content">
                <span class="uni-modal-close" onclick="closeCourseModal({{ $uni->id }})">&times;</span>

                <h4 class="uni-modal-title m-2">
                    <i class="fas fa-book"></i> Courses at {{ $uni->name }}
                </h4>

                <div class="uni-table-wrapper">
                    <table class="uni-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Title</th>
                                <th>Duration</th>
                                <th>Fee</th>
                                <th>Intakes</th>
                                <th>MOI</th>
                                <th>Scholarships</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($uni->courses as $course)
                            <tr>
                                <td><a href="{{ route('agent.courses.show', $course->id) }}">{{ $course->course_code }}</a></td>
                                <td><a href="{{ route('agent.courses.show', $course->id) }}">{{ $course->title }}</a></td>
                                <td>{{ $course->duration ?? 'N/A' }}</td>
                                <td>{{ $course->fee }}</td>
                                <td>{{ $course->intakes ?? 'N/A' }}</td>
                                <td>{{ $course->moi_requirement ?? 'N/A' }}</td>
                                <td>{{ $course->scholarships ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('agent.applications.create') }}?course_id={{ $course->id }}" class="uni-btn-success-small">
                                        <i class="fa-solid fa-paper-plane">Apply</i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        @empty
        <p class="uni-empty">No universities found.</p>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="uni-pagination">
        {{ $universities->links() }}
    </div>

</div>
@endsection

@push('scripts')
<script>
    function openCourseModal(id) {
        document.getElementById('courseModal' + id).style.display = 'block';
    }

    function closeCourseModal(id) {
        document.getElementById('courseModal' + id).style.display = 'none';
    }

    window.onclick = function(event) {
        document.querySelectorAll('.uni-modal').forEach(modal => {
            if (event.target === modal) modal.style.display = "none";
        });
    }

</script>
@endpush
