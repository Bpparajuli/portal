@extends('layouts.app')

@section('content')
    <style>
        /* =============================================================
                                                                           UNIVERSITY PAGE STYLES
                                                                        ============================================================= */

        /* Hero Section */
        .hero-wrapper {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 80px;
            border-radius: 0 0 10px 10px;
            position: relative;
            overflow: hidden;
        }

        .hero-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.3;
        }

        .hero-content {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .hero-heading {
            font-size: 1.5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 1rem;
        }

        .hero-text {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Stats Cards */
        .stats-container {
            margin-top: -60px;
            margin-bottom: 60px;
            position: relative;
            z-index: 2;
        }

        .stats-card {
            background: var(--bg-card);
            border-radius: 20px;
            padding: 25px 20px;
            text-align: center;
            box-shadow: var(--shadow-md);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid var(--border);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .stats-icon i {
            font-size: 1.8rem;
            color: white;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .stats-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Universities Grid */
        .universities-section {
            padding: 40px 0 60px;
        }

        .universities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        /* University Card */
        .uni-card {
            background: var(--bg-card);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            border: 1px solid var(--border);
            display: flex;
            flex-direction: column;
        }

        .uni-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .uni-card-logo {
            position: relative;
            width: 100%;
            height: 140px;
            /* FIXED HEIGHT */
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9fafb;
            border-radius: 12px;
            overflow: hidden;
        }

        /* Ensure anchor fills container */
        .uni-card-logo a {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Image styling */
        .uni-card-logo img {
            width: 100%;
            height: 100%;
            padding: 5px;
            object-fit: contain;
            /* IMPORTANT: keeps logo aspect ratio */
        }

        /* Fallback icon */
        .no-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .uni-card-content {
            padding: 20px;
            flex: 1;
        }

        .uni-title-link {
            text-decoration: none;
        }

        .uni-title-link h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 10px;
            transition: color 0.2s;
            line-height: 1.3;
        }

        .uni-title-link:hover h3 {
            color: var(--primary);
        }

        .uni-location {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 15px;
        }

        .uni-location i {
            font-size: 0.8rem;
        }

        .uni-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .uni-website {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
            color: var(--primary);
            text-decoration: none;
            padding: 5px 0;
            transition: gap 0.2s;
        }

        .uni-website:hover {
            gap: 8px;
            color: var(--secondary);
        }

        .uni-card-footer {
            padding: 15px 20px 20px;
            border-top: 1px solid var(--border);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-heading {
                font-size: 2rem;
            }

            .hero-text {
                font-size: 1rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .hero-buttons .btn {
                width: 100%;
                max-width: 250px;
            }

            .stats-number {
                font-size: 1.5rem;
            }

            .stats-icon {
                width: 50px;
                height: 50px;
            }

            .stats-icon i {
                font-size: 1.4rem;
            }

            .universities-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        @media (max-width: 480px) {
            .hero-wrapper {
                padding: 60px 0;
            }

            .stats-card {
                padding: 15px;
            }

            .uni-card-logo {
                padding: 30px 15px;
            }
        }
    </style>
    {{-- Hero Section --}}
    <div class="hero-wrapper">
        <div class="container">
            <div class="hero-content">
                <p class="hero-heading">
                    Explore top universities worldwide and find the perfect path
                </p>
            </div>
        </div>
    </div>

    {{-- Stats Section --}}
    <div class="stats-container">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3 col-6">
                    <div class="stats-card">
                        <div class="stats-icon bg-gradient-primary">
                            <i class="fas fa-university"></i>
                        </div>
                        <div class="stats-number">{{ $universities->total() }}+</div>
                        <div class="stats-label">Partner Universities</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stats-card">
                        <div class="stats-icon bg-gradient-success">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="stats-number">{{ \App\Models\Course::count() }}+</div>
                        <div class="stats-label">Available Courses</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stats-card">
                        <div class="stats-icon bg-gradient-info">
                            <i class="fas fa-globe-asia"></i>
                        </div>
                        <div class="stats-number">20+</div>
                        <div class="stats-label">Countries</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stats-card">
                        <div class="stats-icon bg-gradient-warning">
                            <i class="fas fa-smile"></i>
                        </div>
                        <div class="stats-number">3k+</div>
                        <div class="stats-label">Happy Students</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="container mt-5">
        @include('partials.uni_filter')
    </div>

    {{-- Universities Grid --}}
    <div class="universities-section">
        <div class="container">
            <div class="universities-grid" id="universityGrid">
                @forelse($universities as $index => $uni)
                    <div class="uni-card">
                        {{-- Logo Section --}}
                        <div class="uni-card-logo">
                            <a href="{{ route('guest.universities.show', $uni->id) }}">
                                @if ($uni->university_logo)
                                    <img src="{{ asset('storage/uni_logo/' . $uni->university_logo) }}"
                                        alt="{{ $uni->name }}">
                                @else
                                    <div class="no-logo">
                                        <i class="fas fa-university fa-3x text-primary"></i>
                                    </div>
                                @endif
                            </a>

                        </div>

                        {{-- Content --}}
                        <div class="uni-card-content">
                            <a href="{{ route('guest.universities.show', $uni->id) }}" class="uni-title-link">
                                <h3>{{ Str::limit($uni->name, 45) }}</h3>
                            </a>

                            <div class="uni-location">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                                <span>{{ $uni->country }}</span>
                                @if ($uni->city)
                                    <span class="mx-1">•</span>
                                    <span>{{ $uni->city }}</span>
                                @endif
                            </div>

                            <div class="uni-badges">
                                @if ($uni->short_name)
                                    <span class="badge bg-primary ">
                                        {{ $uni->short_name }}
                                    </span>
                                @endif
                                <span class="badge bg-secondary ">
                                    <i class="fas fa-book me-1"></i> {{ $uni->courses->count() }} Courses
                                </span>
                            </div>

                            @if ($uni->website)
                                <a href="{{ $uni->website }}" target="_blank" class="uni-website" rel="noopener">
                                    <i class="fas fa-globe"></i> Visit Website
                                    <i class="fas fa-external-link-alt fa-xs ms-1"></i>
                                </a>
                            @endif
                        </div>

                        {{-- Footer Action --}}
                        <div class="uni-card-footer">
                            @if ($uni->courses->count())
                                <button class="btn btn-outline-primary w-60"
                                    onclick="openCourseModal({{ $uni->id }})">
                                    <i class="fas fa-book-open me-2"></i>
                                    View {{ $uni->courses->count() }} Courses
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
                    <div id="courseModal{{ $uni->id }}" class="modal fade" tabindex="-1">
                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">
                                        <i class="fas fa-book-open me-2"></i>
                                        Programs at {{ $uni->short_name ?? $uni->name }}
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white"
                                        onclick="closeCourseModal({{ $uni->id }})"></button>
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
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($uni->courses as $course)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('guest.courses.show', $course->id) }}"
                                                                class="fw-semibold">
                                                                {{ $course->course_code }}
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('guest.courses.show', $course->id) }}">
                                                                {{ Str::limit($course->title, 50) }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $course->duration ?? 'N/A' }}</td>
                                                        <td>
                                                            @if ($course->fee)
                                                                <span
                                                                    class="text-success fw-bold">${{ $course->fee }}</span>
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @php
                                                                $intakes = is_array($course->intakes)
                                                                    ? $course->intakes
                                                                    : explode(',', $course->intakes ?? '');
                                                            @endphp
                                                            @foreach (array_slice($intakes, 0, 2) as $intake)
                                                                <span class="badge bg-info me-1">{{ trim($intake) }}</span>
                                                            @endforeach
                                                            @if (count($intakes) > 2)
                                                                <span
                                                                    class="badge bg-secondary">+{{ count($intakes) - 2 }}</span>
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
                                        onclick="closeCourseModal({{ $uni->id }})">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-4x text-muted mb-3"></i>
                        <h4>No universities found</h4>
                        <p class="text-muted">Try adjusting your filters</p>
                        <button onclick="location.reload()" class="btn btn-primary mt-3">
                            <i class="fas fa-sync-alt me-2"></i> Reset Filters
                        </button>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($universities->hasPages())
                <div class="mt-5">
                    {{ $universities->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        // Dark Mode Toggle
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            if (currentTheme === 'dark') {
                html.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
            } else {
                html.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            }
        }

        // Load saved theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }

        // Modal Functions
        function openCourseModal(id) {
            const modal = new bootstrap.Modal(document.getElementById('courseModal' + id));
            modal.show();
        }

        function closeCourseModal(id) {
            const modalElement = document.getElementById('courseModal' + id);
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) modal.hide();
        }
    </script>
@endsection
