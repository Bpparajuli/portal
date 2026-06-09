@php
    $prefix = $prefix ?? match(true) {
        request()->routeIs('admin.*') => 'admin',
        request()->routeIs('agent.*') => 'agent',
        request()->routeIs('staff.*') => 'staff',
        default => 'guest',
    };
    $showAdminActions = Auth::check() && (Auth::user()->is_admin || Auth::user()->is_staff);

    $filterCityRoute = route($prefix . '.get-cities', ':country');
    $filterUniRoute = route($prefix . '.get-universities', ':city');
    $filterTypeRoute = route($prefix . '.get-course-types', ':universityId');
    $filterCourseRoute = route($prefix . '.get-courses-by-type', ['universityId'=>':universityId','type'=>':type']);
    $filterFormAction = $prefix === 'guest' ? route('guest.universities.index') : route($prefix . '.universities.index');
    $countries = \App\Models\University::select('country')->distinct()->pluck('country');
@endphp

<link rel="stylesheet" href="{{ asset('css/university.css') }}">

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
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0">Find Your University</h5>
                @auth
                @if(in_array($prefix, ['admin', 'staff']))
                <div>
                    <a href="{{ route($prefix . '.universities.create') }}" class="btn btn-success btn-sm">+ Add University</a>
                    <a href="{{ route($prefix . '.courses.create') }}" class="btn btn-success btn-sm">+ Add Course</a>
                </div>
                @endif
                @endauth
            </div>
            <form method="GET" action="{{ $filterFormAction }}">
                <div class="row my-2 g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" id="search" name="search" class="form-control" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-9">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Country</label>
                                <select id="country" class="form-select" name="country" data-cities-url="{{ $filterCityRoute }}">
                                    <option value="">All</option>
                                    @foreach($countries as $country)
                                    <option value="{{ $country }}" @selected(request('country')==$country)>{{ $country }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">City</label>
                                <select id="city" class="form-select" name="city" data-universities-url="{{ $filterUniRoute }}">
                                    <option value="">All</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">University</label>
                                <select id="university_id" class="form-select" name="university_id" data-type-url="{{ $filterTypeRoute }}">
                                    <option value="">All</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Course Type</label>
                                <select id="course_type" class="form-select" name="course_type" data-courses-url="{{ $filterCourseRoute }}">
                                    <option value="">All</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Courses</label>
                                <select id="course_id" name="course_id" class="form-select">
                                    <option value="">All</option>
                                </select>
                            </div>
                            <div class="col-md-2 mt-5 d-flex gap-2">
                                <a href="{{ $filterFormAction }}" class="btn btn-primary">Clear</a>
                                <button class="btn btn-success">Find</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Grid --}}
<div class="uni-listing-grid-section">
    <div class="container">
        <div class="uni-listing-grid" id="universityGrid">
            @forelse($universities as $uni)
            @php $u = $uni; @endphp
            <div class="uni-card" data-aos="fade-up">
                <div class="uni-card-logo">
                    <a href="{{ route($prefix . '.universities.show', $u->id) }}">
                        @if ($u->university_logo)
                            <img src="{{ asset('storage/uni_logo/' . $u->university_logo) }}"
                                 alt="{{ $u->name }}">
                        @else
                            <div class="no-logo">
                                <i class="fas fa-university fa-3x text-primary"></i>
                            </div>
                        @endif
                    </a>
                </div>
                <div class="uni-card-content">
                    <a href="{{ route($prefix . '.universities.show', $u->id) }}" class="uni-title-link">
                        <h3>{{ Str::limit($u->name, 45) }}</h3>
                    </a>
                    <div class="uni-location">
                        <i class="fas fa-map-marker-alt text-primary"></i>
                        <span>{{ $u->country }}</span>
                        @if ($u->city)
                            <span class="mx-1">&bull;</span>
                            <span>{{ $u->city }}</span>
                        @endif
                    </div>
                    <div class="uni-badges">
                        @if ($u->short_name)
                            <span class="badge bg-primary">{{ $u->short_name }}</span>
                        @endif
                        <span class="badge bg-secondary">
                            <i class="fas fa-book me-1"></i> {{ $u->courses_count ?? $u->courses->count() }} Courses
                        </span>
                    </div>
                    @if ($u->website)
                        <a href="{{ $u->website }}" target="_blank" class="uni-website" rel="noopener">
                            <i class="fas fa-globe"></i> Visit Website
                            <i class="fas fa-external-link-alt fa-xs ms-1"></i>
                        </a>
                    @endif
                    @auth
                        @php $user = auth()->user(); @endphp
                        @if($user->is_admin)
                        <div class="mt-2 d-flex gap-1">
                            @can('update', $u)
                            <a href="{{ route($prefix . '.universities.edit', $u) }}" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            @endcan
                            @can('delete', $u)
                            <x-confirm-delete
                                action="{{ $prefix }}.universities.destroy"
                                :id="$u->id"
                                label="Delete"
                                title="Delete {{ $u->name }}?"
                                message="This will permanently delete this university and all its courses."
                                class="btn btn-sm btn-outline-danger"
                            />
                            @endcan
                        </div>
                        @elseif($user->is_agent)
                        <div class="mt-2 d-flex gap-1">
                            <a href="{{ route('agent.applications.quick-start', ['university_id' => $u->id]) }}"
                               class="btn btn-sm btn-success">
                                <i class="fas fa-paper-plane me-1"></i> Apply for Course
                            </a>
                        </div>
                        @elseif($user->is_staff)
                            @can('update', $u)
                            <div class="mt-2 d-flex gap-1">
                                <a href="{{ route($prefix . '.universities.edit', $u) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                            @endcan
                        @endif
                    @endauth
                </div>
                <div class="uni-card-footer">
                    @if ($u->courses && $u->courses->count())
                        <button class="btn btn-outline-primary w-60"
                                onclick="openCourseModal({{ $u->id }})">
                            <i class="fas fa-book-open me-2"></i>
                            View {{ $u->courses->count() }} Courses
                            <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    @else
                        <button class="btn btn-secondary w-100" disabled style="opacity: 0.6;">
                            <i class="fas fa-times-circle me-2"></i>
                            No Courses Available
                        </button>
                    @endif
                </div>
            </div>
            {{-- Course Modal --}}
            <div id="courseModal{{ $u->id }}" class="modal fade" tabindex="-1">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-book-open me-2"></i>
                                Programs at {{ $u->short_name ?? $u->name }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white"
                                    onclick="closeCourseModal({{ $u->id }})"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Code</th>
                                            <th>Program Title</th>
                                            <th>Duration</th>
                                            <th>Annual Fee</th>
                                            <th>Intakes</th>
                                            <th>English Req</th>
                                            <th>Scholarship</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($u->courses as $course)
                                        <tr>
                                            <td>
                                                <a href="{{ route($prefix . '.courses.show', $course->id) }}" class="fw-semibold">
                                                    {{ $course->course_code }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route($prefix . '.courses.show', $course->id) }}">
                                                    {{ Str::limit($course->title, 50) }}
                                                </a>
                                            </td>
                                            <td>{{ $course->duration ?? 'N/A' }}</td>
                                            <td>
                                                @if ($course->fee)
                                                    <span class="text-success fw-bold">{{ $course->fee }}</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $intakes = is_array($course->intakes) ? $course->intakes : explode(',', $course->intakes ?? '');
                                                @endphp
                                                @foreach (array_slice($intakes, 0, 2) as $intake)
                                                    <span class="badge bg-info me-1">{{ trim($intake) }}</span>
                                                @endforeach
                                                @if (count($intakes) > 2)
                                                    <span class="badge bg-secondary">+{{ count($intakes) - 2 }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $course->ielts_pte_other_languages ?? 'Contact Us' }}</td>
                                            <td>
                                                @if ($course->scholarships)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle"></i> Available
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @auth
                                                    @php $authUser = auth()->user(); @endphp
                                                    @if($authUser->is_admin)
                                                        @can('update', $course)
                                                        <a href="{{ route($prefix . '.courses.edit', $course) }}" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                                                        @endcan
                                                    @elseif($authUser->is_agent)
                                                        <a href="{{ route('agent.applications.quick-start', ['course_id' => $course->id]) }}" class="btn btn-sm btn-success"><i class="fas fa-paper-plane"></i></a>
                                                    @elseif($authUser->is_staff)
                                                        @can('update', $course)
                                                        <a href="{{ route($prefix . '.courses.edit', $course) }}" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                                                        @endcan
                                                    @endif
                                                @endauth
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Click on course codes for detailed information
                            </small>
                            <button type="button" class="btn btn-secondary"
                                    onclick="closeCourseModal({{ $u->id }})">Close</button>
                        </div>
                    </div>
                </div>
            </div>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/filter.js') }}"></script>
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
