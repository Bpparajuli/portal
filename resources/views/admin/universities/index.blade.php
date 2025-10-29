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
                <img src="{{ asset('storage/uni_logo/'.$uni->university_logo) }}" class="uni-logo" alt="{{ $uni->name }}">
                @endif
            </div>
            <div class="uni-card-body">
                <h3 class="uni-card-title">{{ $uni->name }}</h3>
                <p class="uni-card-subtitle">{{ $uni->short_name ?? 'N/A' }}</p>
                <p class="uni-card-location">{{ $uni->city ?? 'N/A' }}, {{ $uni->country }}</p>
                <p>
                    @if($uni->website)
                    <a href="{{ $uni->website }}" target="_blank" class="uni-web-link" rel="noopener">{{ $uni->website }}</a>
                    @endif
                </p>
                <p>{{ $uni->contact_email ?? 'N/A' }}</p>
            </div>

            <div class="uni-card-footer d-flex flex-wrap gap-2">
                @if($uni->courses->count())
                <a href="{{ route('agent.applications.create') }}?university_id={{ $uni->id }}" class="btn btn-secondary">
                    Apply to this university
                </a>
                <button class="uni-btn-toggle btn-primary" onclick="openCourseModal({{ $uni->id }})">
                    View Courses ({{ $uni->courses->count() }})
                </button>
                @endif

                {{-- Edit & Delete Buttons --}}
                <a href="{{ route('admin.universities.edit', $uni->id) }}" class="btn btn-warning">Edit</a>

                <form action="{{ route('admin.universities.destroy', $uni->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this university?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">Delete</button>
                </form>
            </div>
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($uni->courses as $course)
                        <tr>
                            <td>{{ $course->course_code }}</td>
                            <td>{{ $course->title }}</td>
                            <td>{{ $course->description ?? 'N/A' }}</td>
                            <td>{{ $course->duration ?? 'N/A' }}</td>
                            <td>{{ $course->fee}}</td>
                            <td>{{ $course->intakes ?? 'N/A' }}</td>
                            <td>{{ $course->scholarships }} </td>
                            <td>{{ $course->moi_requirement ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-sm btn-info">Edit</a>
                            </td>
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

    {{-- Pagination --}}
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
