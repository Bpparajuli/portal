@php
    $prefix = $prefix ?? match(true) {
        request()->routeIs('admin.*') => 'admin',
        request()->routeIs('agent.*') => 'agent',
        request()->routeIs('staff.*') => 'staff',
        default => 'guest',
    };
    $showAdminActions = Auth::check() && (Auth::user()->is_admin || Auth::user()->is_staff);
    $countries = $countries ?? \App\Models\University::select('country')->distinct()->pluck('country');
    $courseTypes = $courseTypes ?? \App\Models\Course::distinct()->pluck('course_type');
@endphp

{{-- Hero --}}
<div class="uni-listing-hero">
    <div class="container">
        <div class="uni-listing-hero-content">
            <h1 class="uni-listing-hero-title">Browse courses from top universities</h1>
            <p class="uni-listing-hero-sub">Find the right program for every student</p>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="uni-listing-stats-row">
    <div class="container">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="uni-listing-stat-card">
                    <div class="uni-listing-stat-icon" style="background:linear-gradient(135deg,#6d28d9,#8b5cf6)"><i class="fas fa-book-open"></i></div>
                    <div class="uni-listing-stat-num">{{ $courses->total() }}+</div>
                    <div class="uni-listing-stat-label">Available Courses</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="uni-listing-stat-card">
                    <div class="uni-listing-stat-icon" style="background:linear-gradient(135deg,#059669,#34d399)"><i class="fas fa-university"></i></div>
                    <div class="uni-listing-stat-num">{{ \App\Models\University::count() }}+</div>
                    <div class="uni-listing-stat-label">Partner Universities</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="uni-listing-stat-card">
                    <div class="uni-listing-stat-icon" style="background:linear-gradient(135deg,#0284c7,#38bdf8)"><i class="fas fa-globe-asia"></i></div>
                    <div class="uni-listing-stat-num">{{ $countries->count() }}+</div>
                    <div class="uni-listing-stat-label">Countries</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="uni-listing-stat-card">
                    <div class="uni-listing-stat-icon" style="background:linear-gradient(135deg,#d97706,#fbbf24)"><i class="fas fa-smile"></i></div>
                    <div class="uni-listing-stat-num">3k+</div>
                    <div class="uni-listing-stat-label">Happy Students</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Admin actions --}}
@if($showAdminActions)
<div class="container mt-3 mb-2">
    <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-3 shadow-sm border">
        <h5 class="mb-0 fw-bold"><i class="fas fa-book-open text-primary me-2"></i>Courses</h5>
        <a href="{{ route($prefix . '.courses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New
        </a>
    </div>
</div>
@endif

{{-- Filters --}}
<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="GET" action="{{ url()->current() }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Course title or code...">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Country</label>
                    <select name="country" class="form-select">
                        <option value="">All Countries</option>
                        @foreach($countries as $c)
                            <option value="{{ $c }}" {{ request('country') == $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Course Type</label>
                    <select name="course_type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($courseTypes as $ct)
                            <option value="{{ $ct }}" {{ request('course_type') == $ct ? 'selected' : '' }}>{{ $ct }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary flex-fill"><i class="fas fa-undo"></i></a>
                    <button type="submit" class="btn btn-success flex-fill"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Grid --}}
<div class="uni-listing-grid-section">
    <div class="container">
        <div class="uni-listing-grid" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr))">
            @forelse($courses as $course)
                @include('shared.course-card', ['courseItem' => $course, 'prefix' => $prefix])
            @empty
                <div class="text-center py-5" style="grid-column:1/-1">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h4>No courses found</h4>
                    <p class="text-muted">Try adjusting your filters</p>
                    <button onclick="location.reload()" class="btn btn-primary mt-3">
                        <i class="fas fa-sync-alt me-2"></i> Reset Filters
                    </button>
                </div>
            @endforelse
        </div>

        @if ($courses->hasPages())
            <div class="mt-4">
                {{ $courses->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<style>
.uni-listing-hero {
    background: linear-gradient(135deg, #6d28d9 0%, #0f172a 100%);
    padding: 3.5rem 1rem;
    position: relative; overflow: hidden;
}
.uni-listing-hero::before {
    content: ''; position: absolute; inset: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.08)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
    background-size: cover; opacity: 0.3;
}
.uni-listing-hero-content { position: relative; z-index: 1; text-align: center; }
.uni-listing-hero-title { font-size: 1.6rem; font-weight: 800; color: #fff; margin-bottom: 0.3rem; }
.uni-listing-hero-sub { font-size: 1.05rem; color: rgba(255,255,255,0.8); margin: 0; }

.uni-listing-stats-row { margin-top: -2rem; margin-bottom: 2rem; position: relative; z-index: 2; }
.uni-listing-stat-card {
    background: #fff; border-radius: 16px; padding: 1.25rem 1rem; text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.uni-listing-stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
.uni-listing-stat-icon { width: 48px; height: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; }
.uni-listing-stat-icon i { font-size: 1.3rem; color: #fff; }
.uni-listing-stat-num { font-size: 1.6rem; font-weight: 800; color: #1e293b; line-height: 1.2; }
.uni-listing-stat-label { font-size: 0.8rem; color: #64748b; margin-top: 0.2rem; }

.uni-listing-grid-section { padding: 2rem 0 3rem; }
.uni-listing-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }

@media (max-width:768px) {
    .uni-listing-hero { padding: 2.5rem 1rem; }
    .uni-listing-hero-title { font-size: 1.3rem; }
    .uni-listing-hero-sub { font-size: 0.9rem; }
    .uni-listing-stat-num { font-size: 1.3rem; }
    .uni-listing-stat-icon { width: 40px; height: 40px; }
    .uni-listing-stat-icon i { font-size: 1.1rem; }
    .uni-listing-grid { grid-template-columns: 1fr; gap: 1rem; }
}
</style>
