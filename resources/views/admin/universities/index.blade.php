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

                <div class="uni-card-header">
                    <a href="{{ route('admin.universities.show', $uni->id) }}">
                        @if($uni->university_logo)
                        <img src="{{ asset('storage/uni_logo/'.$uni->university_logo) }}" class="uni-logo-full" alt="{{ $uni->name }}">
                        @else
                        <div class="uni-no-logo">
                            <i class="fas fa-university"></i>
                            <p>No Logo</p>
                        </div>
                        @endif
                    </a>
                </div>

                <div class="uni-card-body">
                    <a href="{{ route('admin.universities.show', $uni->id) }}" class="uni-name-link">
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

                    <p class="uni-email"><i class="fas fa-envelope"></i> {{ $uni->contact_email ?? 'N/A' }}</p>
                </div>

                <div class="uni-card-footer">
                    <div>
                        @if($uni->courses->count())
                        <button class="uni-btn-outline" onclick="openCourseModal({{ $uni->id }})">
                            <i class="fas fa-book-open"></i> {{ $uni->courses->count() }} Courses
                        </button>
                        @endif
                    </div>

                    <div class="uni-actions">
                        <a href="{{ route('admin.universities.edit', $uni->id) }}" class="uni-icon-btn">
                            <i class="fa fa-edit"></i>
                        </a>

                        @if(auth()->id() === 1)
                        <form action="{{ route('admin.universities.destroy', $uni->id) }}" method="POST" onsubmit="return confirm('Delete this university?');">
                            @csrf @method('DELETE')
                            <button class="uni-icon-btn danger">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- Modal --}}
        <div id="courseModal{{ $uni->id }}" class="uni-modal">
            <div class="uni-modal-content">
                <span class="uni-modal-close" onclick="closeCourseModal({{ $uni->id }})">&times;</span>

                <h4 class="modal-title">
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
                                <td><a href="{{ route('admin.courses.show', $course->id) }}">{{ $course->course_code }}</a></td>
                                <td><a href="{{ route('admin.courses.show', $course->id) }}">{{ $course->title }}</a></td>
                                <td>{{ $course->duration ?? 'N/A' }}</td>
                                <td>{{ $course->fee }}</td>
                                <td>{{ $course->intakes ?? 'N/A' }}</td>
                                <td>{{ $course->moi_requirement ?? 'N/A' }}</td>
                                <td>{{ $course->scholarships ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.courses.edit', $course->id) }}" class="uni-btn-small">Edit</a>
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
            if (event.target == modal) modal.style.display = "none";
        });
    }

</script>

@endpush
