@extends('layouts.app')

@section('content')
<div class="container py-4 uni-page">
    {{-- Filter Section --}}
    @include('partials.uni_filter')

    {{-- Universities Cards --}}
    <div class="row mt-4 g-4">
        @forelse($universities as $uni)
        <div class="col-md-4 col-lg-3">
            <div class="uni-card shadow-sm border-0 rounded-3 h-100 d-flex flex-column justify-content-between">
                <div class="uni-card-header text-center p-3">
                    <a href="{{ route('admin.universities.show', $uni->id) }}">
                        @if($uni->university_logo)
                        <img src="{{ asset('storage/uni_logo/'.$uni->university_logo) }}" class="uni-logo rounded shadow-sm" alt="{{ $uni->name }}">
                        @else
                        <div class="no-logo bg-light border rounded p-4 text-muted">
                            <i class="fas fa-university fa-2x"></i>
                            <p class="small mt-2 mb-0">No Logo</p>
                        </div>
                        @endif
                    </a>
                </div>

                <div class="uni-card-body text-center px-3 pb-3">
                    <a href="{{ route('admin.universities.show', $uni->id) }}" class="text-decoration-none text-dark">
                        <h5 class="fw-bold mb-1">{{ $uni->name }}</h5>
                        <p class="text-secondary small mb-1">{{ $uni->short_name ?? 'N/A' }}</p>
                    </a>
                    <p class="mb-1"><i class="fas fa-map-marker-alt text-muted me-1"></i> {{ $uni->city ?? 'N/A' }}, {{ $uni->country }}</p>

                    @if($uni->website)
                    <p class="small">
                        <a href="{{ $uni->website }}" target="_blank" class="uni-web-link text-decoration-none">
                            <i class="fas fa-globe me-1 text-info"></i>{{ $uni->website }}
                        </a>
                    </p>
                    @endif

                    <p class="small text-muted mb-2"><i class="fas fa-envelope me-1"></i>{{ $uni->contact_email ?? 'N/A' }}</p>
                </div>

                <div class="uni-card-footer d-flex justify-content-between align-items-center border-top p-3">
                    <div>
                        @if($uni->courses->count())
                        <button class="btn btn-sm btn-outline-primary" onclick="openCourseModal({{ $uni->id }})">
                            <i class="fas fa-book-open me-1"></i> {{ $uni->courses->count() }} Courses
                        </button>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.universities.edit', $uni->id) }}" class="btn btn-sm btn-outline-info" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        @if(auth()->id() === 1)
                        <form action="{{ route('admin.universities.destroy', $uni->id) }}" method="POST" onsubmit="return confirm('Delete this university?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Delete">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Courses Modal --}}
        <div id="courseModal{{ $uni->id }}" class="uni-modal">
            <div class="uni-modal-content shadow-lg rounded">
                <span class="uni-modal-close" onclick="closeCourseModal({{ $uni->id }})">&times;</span>
                <h4 class="mb-3 fw-bold"><i class="fas fa-book text-primary me-2"></i>Courses at {{ $uni->name }}</h4>
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-bordered small">
                        <thead class="table-dark">
                            <tr>
                                <th>Code</th>
                                <th>Title</th>
                                <th>Description</th>
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
                                <td>{{ $course->course_code }}</td>
                                <td>{{ $course->title }}</td>
                                <td>{{ $course->description ?? 'N/A' }}</td>
                                <td>{{ $course->duration ?? 'N/A' }}</td>
                                <td>{{ $course->fee }}</td>
                                <td>{{ $course->intakes ?? 'N/A' }}</td>
                                <td>{{ $course->moi_requirement ?? 'N/A' }}</td>
                                <td>{{ $course->scholarships ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-sm btn-info">Edit</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @empty
        <p class="text-center text-muted mt-5">No universities found.</p>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="pagination-wrap mt-5 d-flex justify-content-center">
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

@push('styles')
<style>
    /* --- Card Styling --- */
    .uni-card {
        transition: all 0.25s ease-in-out;
        background: #fff;
    }

    .uni-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    }

    .uni-logo {
        max-height: 80px;
        object-fit: contain;
    }

    .btn-outline-primary {
        font-size: 0.85rem;
        border-radius: 20px;
    }

    .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: #fff;
    }

    /* --- Modal --- */
    .uni-modal {
        display: none;
        position: fixed;
        z-index: 1050;
        padding-top: 70px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .uni-modal-content {
        background-color: #fff;
        margin: auto;
        padding: 20px;
        width: 90%;
        max-width: 1000px;
        animation: fadeIn 0.3s;
    }

    .uni-modal-close {
        float: right;
        font-size: 1.5rem;
        font-weight: bold;
        color: #aaa;
        cursor: pointer;
    }

    .uni-modal-close:hover {
        color: #000;
    }

    /* --- Table --- */
    .table th,
    .table td {
        text-align: center;
        vertical-align: middle;
    }

    /* --- Pagination --- */
    .pagination-wrap .pagination {
        justify-content: center;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

</style>
@endpush
