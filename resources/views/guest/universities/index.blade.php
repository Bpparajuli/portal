@extends('layouts.app')

@section('content')
<div class="uni-page">

    {{-- Filter Section --}}
    @include('partials.uni_filter')

    {{-- Universities Cards --}}
    <div class="uni-cards-grid mt-3">
        @forelse($universities as $uni)
        <div class="uni-card">
            <div class="uni-card-header">
                @if($uni->university_logo)
                <a href="{{ route('guest.universities.show', $uni->id) }}">
                    <img src="{{ asset('storage/uni_logo/'.$uni->university_logo) }}" class="uni-logo" alt="{{ $uni->name }}">
                </a>
                @endif
            </div>
            <div class="uni-card-body">
                <h3 class="uni-card-title">
                    <a href="{{ route('guest.universities.show', $uni->id) }}">
                        {{ $uni->name }}
                    </a>
                </h3>
                <p class="uni-card-subtitle">
                    <a href="{{ route('guest.universities.show', $uni->id) }}">
                        {{ $uni->short_name ?? 'N/A' }} ({{ $uni->id ?? 'N/A' }})
                    </a>
                </p>
                <p class="uni-card-location">{{ $uni->city ?? 'N/A' }}, {{ $uni->country }}</p>
                <p>
                    @if($uni->website)
                    <a href="{{ $uni->website }}" target="_blank" class="uni-web-link" rel="noopener">{{ $uni->website }}</a>
                    @endif
                </p>
                <p>{{ $uni->contact_email ?? 'N/A' }}</p>
            </div>

            @if($uni->courses->count())
            <div class="uni-card-footer">
                <button class="uni-btn-toggle btn-primary" onclick="openCourseModal({{ $uni->id }})">
                    View Courses ({{ $uni->courses->count() }})
                </button>
            </div>
            @endif
        </div>

        {{-- Courses Modal --}}
        <div id="courseModal{{ $uni->id }}" class="uni-modal">
            <div class="uni-modal-content">
                <span class="uni-modal-close" onclick="closeCourseModal({{ $uni->id }})">&times;</span>
                <h3>Courses of {{ $uni->name }}</h3>
                <table class="uni-inner-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Duration</th>
                            <th>Fee</th>
                            <th>Intakes</th>
                            <th>MOI</th>
                            <th>Scholarships </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($uni->courses as $course)
                        <tr>
                            <td>{{ $course->course_code }}</td>
                            <td>{{ $course->title }}</td>
                            <td>{{ $course->description ?? 'N/A' }}</td>
                            <td>{{ $course->duration ?? 'N/A' }}</td>
                            <td>${{$course->fee}}</td>
                            <td>{{ $course->intakes ?? 'N/A' }}</td>
                            <td>{{ $course->moi_requirement ?? 'N/A' }}</td>
                            <td>{{ $course->scholarships ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @empty
        <p class="uni-no-data">No universities found.</p>
        @endforelse
    </div>

    <div class="pagination-wrap mt-4">
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

    // Close modal on outside click
    window.onclick = function(event) {
        document.querySelectorAll('.uni-modal').forEach(modal => {
            if (event.target == modal) modal.style.display = "none";
        });
    }

</script>
@endpush
