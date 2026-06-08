@extends('layouts.staff')

@section('page-title', 'Universities')
@section('title', 'Staff | Universities')

@push('styles')
<style>
.uni-card { transition: all 0.3s ease; }
.uni-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(0,0,0,0.1) !important; }
.uni-card-logo { height: 80px; display: flex; align-items: center; justify-content: center; padding: 0.5rem; }
.uni-card-logo img { max-height: 100%; object-fit: contain; }
</style>
@endpush

@section('staff-content')
<div class="container-fluid p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h5 class="fw-bold mb-0" style="color: var(--primary);">Universities</h5>
            <p class="text-muted mb-0 small">Browse partner universities</p>
        </div>
    </div>

    <div class="row g-3">
        @forelse($universities as $uni)
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100 border-0 uni-card">
                <div class="uni-card-logo bg-light rounded-top">
                    @if($uni->university_logo)
                        <img src="{{ asset('storage/uni_logo/' . $uni->university_logo) }}" alt="{{ $uni->name }}">
                    @else
                        <i class="fas fa-university fa-2x text-muted"></i>
                    @endif
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-1">{{ $uni->name }}</h6>
                    <p class="text-muted small mb-2">
                        <i class="fas fa-map-marker-alt me-1"></i>{{ $uni->country ?? '—' }}
                        @if($uni->city) &middot; {{ $uni->city }} @endif
                    </p>
                    <div class="d-flex gap-1">
                        <span class="badge bg-info bg-opacity-10 text-info">{{ $uni->courses_count ?? $uni->courses->count() }} courses</span>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                    <span class="btn btn-outline-primary btn-sm w-100 disabled">
                        <i class="fas fa-info-circle me-1"></i> {{ $uni->courses_count ?? $uni->courses->count() }} Courses
                    </span>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center py-4 text-muted">No universities found</div>
            </div>
        </div>
        @endforelse
    </div>

    @if($universities->hasPages())
    <div class="mt-3">{{ $universities->links() }}</div>
    @endif
</div>
@endsection
