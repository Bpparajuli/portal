@php
    $prefix = $prefix ?? match(true) {
        request()->routeIs('admin.*') => 'admin',
        request()->routeIs('agent.*') => 'agent',
        request()->routeIs('staff.*') => 'staff',
        default => 'guest',
    };
    $showAdminActions = Auth::check() && (Auth::user()->is_admin || Auth::user()->is_staff);
@endphp

{{-- Hero --}}
<div class="uni-listing-hero">
    <div class="container">
        <div class="uni-listing-hero-content">
            <h1 class="uni-listing-hero-title">Explore top universities worldwide</h1>
            <p class="uni-listing-hero-sub">Find the perfect path for your students</p>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="uni-listing-stats-row">
    <div class="container">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="uni-listing-stat-card">
                    <div class="uni-listing-stat-icon" style="background:linear-gradient(135deg,#6d28d9,#8b5cf6)"><i class="fas fa-university"></i></div>
                    <div class="uni-listing-stat-num">{{ $universities->total() }}+</div>
                    <div class="uni-listing-stat-label">Partner Universities</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="uni-listing-stat-card">
                    <div class="uni-listing-stat-icon" style="background:linear-gradient(135deg,#059669,#34d399)"><i class="fas fa-book-open"></i></div>
                    <div class="uni-listing-stat-num">{{ \App\Models\Course::count() }}+</div>
                    <div class="uni-listing-stat-label">Available Courses</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="uni-listing-stat-card">
                    <div class="uni-listing-stat-icon" style="background:linear-gradient(135deg,#0284c7,#38bdf8)"><i class="fas fa-globe-asia"></i></div>
                    <div class="uni-listing-stat-num">{{ \App\Models\University::select('country')->distinct()->count() }}+</div>
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
        <h5 class="mb-0 fw-bold"><i class="fas fa-university text-primary me-2"></i>Universities</h5>
        <a href="{{ route($prefix . '.universities.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New
        </a>
    </div>
</div>
@endif

{{-- Filter --}}
<div class="container mt-4">
    @include('partials.uni_filter')
</div>

{{-- Grid --}}
<div class="uni-listing-grid-section">
    <div class="container">
        <div class="uni-listing-grid" id="universityGrid">
            @forelse($universities as $uni)
                @include('shared.university-card', ['university' => $uni, 'prefix' => $prefix])
            @empty
                <div class="text-center py-5" style="grid-column:1/-1">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h4>No universities found</h4>
                    <p class="text-muted">Try adjusting your filters</p>
                    <button onclick="location.reload()" class="btn btn-primary mt-3">
                        <i class="fas fa-sync-alt me-2"></i> Reset Filters
                    </button>
                </div>
            @endforelse
        </div>

        @if ($universities->hasPages())
            <div class="mt-4">
                {{ $universities->appends(request()->query())->links() }}
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
.uni-listing-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }

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

<script>
function toggleTheme() {
    const html = document.documentElement;
    const t = html.getAttribute('data-theme');
    if (t === 'dark') { html.removeAttribute('data-theme'); localStorage.setItem('theme','light'); }
    else { html.setAttribute('data-theme','dark'); localStorage.setItem('theme','dark'); }
}
const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'dark') document.documentElement.setAttribute('data-theme','dark');
function openCourseModal(id) { new bootstrap.Modal(document.getElementById('courseModal'+id)).show(); }
function closeCourseModal(id) { const m = bootstrap.Modal.getInstance(document.getElementById('courseModal'+id)); if(m) m.hide(); }
</script>
