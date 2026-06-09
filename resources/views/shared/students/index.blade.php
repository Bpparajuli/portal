@php
    $__user = auth()->user();
    $__isMgmt = $__user->is_admin || $__user->is_admin_staff;
    $__isAgent = $__user->is_agent;
    $__isStaff = $__user->is_staff && !$__user->is_admin_staff;
    $__layout = $__isAgent ? 'layouts.agent' : ($__isStaff ? 'layouts.staff' : 'layouts.admin');
    $__section = $__isAgent ? 'agent-content' : ($__isStaff ? 'staff-content' : 'admin-content');
    $__prefix = $__isAgent ? 'agent' : ($__isStaff ? 'staff' : 'admin');
    $__routePrefix = $__prefix . '.students';
@endphp

@extends($__layout)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
    @if ($__isAgent)
        <style>
            .doc-progress-wrap {
                min-width: 130px;
            }

            .doc-progress-bar {
                height: 6px;
                border-radius: 4px;
                background: #e9ecef;
                overflow: hidden;
                margin: 3px 0;
            }

            .doc-progress-fill {
                height: 100%;
                border-radius: 4px;
                transition: width 0.3s;
            }

            .doc-progress-fill.fill-success {
                background-color: #198754;
            }

            .doc-progress-fill.fill-warning {
                background-color: #ffc107;
            }

            .doc-progress-fill.fill-danger {
                background-color: #dc3545;
            }
        </style>
    @endif
@endpush

@section($__section)
    <div class="{{ $__isMgmt ? 'container-fluid px-4 py-4' : ($__isAgent ? 'students-page' : 'container-fluid p-3') }}">

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- HEADER --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        @if ($__isMgmt)
            <x-page-header title="Student Management" subtitle="All students across all agents">
                <x-slot:actions>
                    <a href="{{ route($__routePrefix . '.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-plus me-1"></i> Add Student
                    </a>
                    <a href="{{ route('admin.exports.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-download me-1"></i> Export
                    </a>
                </x-slot:actions>
            </x-page-header>
        @elseif($__isAgent)
            <div class="page-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h1 class="page-title mb-0"><i class="fa-solid fa-users me-2"></i> Student Management</h1>
                    <p class="text-muted mb-0 mt-1 small">Manage your student pipeline</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route($__routePrefix . '.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-plus me-1"></i> Add Student
                    </a>
                </div>
            </div>
        @else
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h5 class="fw-bold mb-0" style="color: var(--primary);">Students</h5>
                    <p class="text-muted mb-0 small">Manage all students</p>
                </div>
                <form method="GET" action="{{ route($__routePrefix . '.index') }}" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Search students..." value="{{ request('search') }}" style="min-width: 220px;">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Search</button>
                </form>
            </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- AGENT STATS CARDS --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        @if ($__isAgent && isset($totalStudents))
            <div class="row g-3 mb-4">
                @foreach ([['label' => 'Total Students', 'value' => $totalStudents, 'icon' => 'fa-users', 'color' => 'primary'], ['label' => 'Total Applied', 'value' => $totalApplied ?? 0, 'icon' => 'fa-paper-plane', 'color' => 'info'], ['label' => 'Admitted/Enrolled', 'value' => $admittedEnrolled ?? 0, 'icon' => 'fa-user-graduate', 'color' => 'success'], ['label' => 'Docs Complete', 'value' => $documentCompleted ?? 0, 'icon' => 'fa-file-alt', 'color' => 'warning']] as $stat)
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 h-100 shadow-sm hover-lift">
                            <div class="card-body d-flex align-items-center gap-3 p-3">
                                <div class="rounded-3 p-3"
                                    style="background: rgba(var(--bs-{{ $stat['color'] }}-rgb), 0.1);">
                                    <i class="fas {{ $stat['icon'] }} fa-lg"
                                        style="color: var(--bs-{{ $stat['color'] }});"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">{{ $stat['label'] }}</div>
                                    <div class="fs-4 fw-bold">{{ number_format($stat['value']) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- FILTERS --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div class="card border-0 shadow-sm mb-4 rounded-3">
            <div class="card-body p-3">
                <form method="GET" action="{{ route($__routePrefix . '.index') }}" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold mb-1">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="form-control form-control-sm" placeholder="Name, email, agent, course..."
                                id="searchInput">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Country</label>
                            <select name="country" class="form-select form-select-sm" id="countrySelect">
                                <option value="">All Countries</option>
                                @foreach ($countries ?? [] as $country)
                                    <option value="{{ $country->name }}"
                                        {{ request('country') == $country->name ? 'selected' : '' }}>
                                        {{ $country->name }} ({{ $country->count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">University</label>
                            <select name="university" class="form-select form-select-sm" id="universitySelect">
                                <option value="">All</option>
                                @foreach ($universities ?? [] as $uni)
                                    <option value="{{ $uni->id }}"
                                        {{ request('university') == $uni->id ? 'selected' : '' }}>
                                        {{ $uni->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if ($__isMgmt)
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold mb-1">Agent</label>
                                <select name="agent" class="form-select form-select-sm" id="agentSelect">
                                    <option value="">All</option>
                                    @foreach ($agents ?? [] as $agent)
                                        <option value="{{ $agent->id }}"
                                            {{ request('agent') == $agent->id ? 'selected' : '' }}>
                                            {{ $agent->business_name ?? $agent->username }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Application Status</label>
                            <select name="application_status_id" class="form-select form-select-sm" id="statusSelect">
                                <option value="">All</option>
                                @foreach ($applicationStatuses ?? [] as $status)
                                    <option value="{{ $status->id }}"
                                        {{ request('application_status_id') == $status->id ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if (!$__isStaff)
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold mb-1">Document Status</label>
                                <select name="document_status" class="form-select form-select-sm">
                                    <option value="">All</option>
                                    @foreach (['Not Uploaded', 'Incomplete', 'Completed'] as $ds)
                                        <option value="{{ $ds }}"
                                            {{ request('document_status') == $ds ? 'selected' : '' }}>{{ $ds }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-1 d-flex gap-1">
                            <a href="{{ route($__routePrefix . '.index') }}" class="btn btn-outline-danger btn-sm">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        </div>
                    </div>
                    @if ($__isMgmt)
                        <input type="hidden" name="order" value="{{ request('order', 'desc') }}">
                        <input type="hidden" name="quick_filter" value="{{ request('quick_filter') }}">
                    @endif
                </form>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- ADMIN QUICK FILTERS + SORT --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        @if ($__isMgmt)
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="d-flex gap-2 flex-wrap">
                    @php
                        $qf = request('quick_filter');
                        $baseQ = request()->except(['quick_filter', 'page', 'page1', 'page2']);
                    @endphp
                    <a href="{{ route($__routePrefix . '.index', array_merge($baseQ, ['quick_filter' => 'applied'])) }}"
                        class="btn btn-sm {{ $qf === 'applied' ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill">Applied</a>
                    <a href="{{ route($__routePrefix . '.index', array_merge($baseQ, ['quick_filter' => 'not_applied'])) }}"
                        class="btn btn-sm {{ $qf === 'not_applied' ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill">Not
                        Applied</a>
                    <a href="{{ route($__routePrefix . '.index', $baseQ) }}"
                        class="btn btn-sm {{ !$qf ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill">All Students</a>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span class="small text-muted">Sort:</span>
                    <a href="{{ route($__routePrefix . '.index', array_merge(request()->except('order', 'page', 'page1', 'page2'), ['order' => 'asc'])) }}"
                        class="btn btn-sm {{ request('order', 'desc') == 'asc' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill">Old
                        First</a>
                    <a href="{{ route($__routePrefix . '.index', array_merge(request()->except('order', 'page', 'page1', 'page2'), ['order' => 'desc'])) }}"
                        class="btn btn-sm {{ request('order', 'desc') == 'desc' ? 'btn-secondary' : 'btn-outline-secondary' }} rounded-pill">New
                        First</a>
                </div>
            </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- PRIMARY TABLE --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        @php $primaryStudents = $table1Students ?? $students ?? collect(); @endphp
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;overflow:hidden;">
            <div class="d-flex justify-content-between align-items-center px-3 py-2"
                style="background:var(--primary);color:#fff;">
                <h6 class="mb-0 fw-semibold">
                    <i class="fa-solid fa-table me-2" style="opacity:0.5;"></i>
                    {{ $__isMgmt ? 'All Students' : ($__isAgent ? 'All Students' : 'Students') }}
                </h6>
                <small>{{ $primaryStudents->total() ?? $primaryStudents->count() }} student(s)</small>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="font-size:0.78rem;">
                    <thead>
                        <tr>
                            <th
                                style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">
                                Student</th>
                            <th
                                style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">
                                Contact</th>
                            @if (!$__isAgent)
                                <th
                                    style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">
                                    Agent</th>
                            @endif
                            <th
                                style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">
                                Status</th>
                            @if (!$__isStaff)
                                <th
                                    style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">
                                    Documents</th>
                            @endif
                            <th
                                style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);text-align:right;width:100px;background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @include('shared.students._table-rows', [
                            'students' => $primaryStudents,
                            '__prefix' => $__prefix,
                            '__routePrefix' => $__routePrefix,
                            '__isAgent' => $__isAgent,
                            '__isStaff' => $__isStaff,
                            '__isMgmt' => $__isMgmt,
                            'totalRequiredDocs' => $totalRequiredDocs,
                        ])
                    </tbody>
                </table>
            </div>
            @if ($primaryStudents->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-center py-3">
                    {{ $primaryStudents->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- ADMIN: SECONDARY TABLE (partner agents) --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        @if ($__isMgmt && isset($table2Students) && $table2Students->total())
            <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;overflow:hidden;">
                <div class="d-flex justify-content-between align-items-center px-3 py-2"
                    style="background:var(--primary);color:#fff;">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fa-solid fa-table me-2" style="opacity:0.5;"></i>
                        Students of Partner Agents
                    </h6>
                    <small>{{ $table2Students->total() }} student(s)</small>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0" style="font-size:0.78rem;">
                        <thead>
                            <tr>
                                <th style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">Student</th>
                                <th style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">Contact</th>
                                <th style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">Agent</th>
                                <th style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">Status</th>
                                <th style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">Documents</th>
                                <th style="padding:0.6rem 0.7rem;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:rgba(255,255,255,0.85);text-align:right;width:100px;background:var(--primary) !important;border-bottom:2px solid var(--primary-dark) !important;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('shared.students._table-rows', [
                                'students' => $table2Students,
                                '__prefix' => $__prefix,
                                '__routePrefix' => $__routePrefix,
                                '__isAgent' => false,
                                '__isStaff' => false,
                                '__isMgmt' => $__isMgmt,
                                'totalRequiredDocs' => $totalRequiredDocs,
                            ])
                        </tbody>
                    </table>
                </div>
                @if ($table2Students->hasPages())
                    <div class="card-footer bg-white border-top d-flex justify-content-center py-3">
                        {{ $table2Students->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        @endif

    </div>

    <script>
        @if ($__isMgmt)
            let debounceTimer, isSubmitting = false;
            const currentQf = '{{ request('quick_filter') }}';
            const currentOrder = '{{ request('order', 'desc') }}';

            document.querySelectorAll('#filterForm select').forEach(el => {
                el.addEventListener('change', function() {
                    if (isSubmitting) return;
                    isSubmitting = true;
                    const params = new URLSearchParams();
                    const val = this.value;
                    const name = this.name;
                    if (val) params.set(name, val);
                    if (currentQf) params.set('quick_filter', currentQf);
                    if (currentOrder) params.set('order', currentOrder);
                    window.location.href = '{{ route($__routePrefix . '.index') }}?' + params.toString();
                    setTimeout(() => isSubmitting = false, 500);
                });
            });

            function debounceSubmit() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    if (isSubmitting) return;
                    isSubmitting = true;
                    const searchVal = document.getElementById('searchInput')?.value || '';
                    const params = new URLSearchParams();
                    if (searchVal) params.set('search', searchVal);
                    if (currentQf) params.set('quick_filter', currentQf);
                    if (currentOrder) params.set('order', currentOrder);
                    window.location.href = '{{ route($__routePrefix . '.index') }}?' + params.toString();
                    setTimeout(() => isSubmitting = false, 500);
                }, 500);
            }

            document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    debounceSubmit();
                }
            });
        @elseif ($__isAgent)
            document.querySelectorAll('#filterForm select, #filterForm input[name="search"]').forEach(field => {
                field.addEventListener('change', function() {
                    document.querySelectorAll('#filterForm [name]').forEach(f => {
                        if (f.name !== this.name && f.name !== '_token') f.value = '';
                    });
                    document.getElementById('filterForm').submit();
                });
            });
            const searchInput = document.querySelector('#filterForm input[name="search"]');
            if (searchInput) {
                let t;
                searchInput.addEventListener('input', function() {
                    clearTimeout(t);
                    t = setTimeout(() => {
                        document.querySelectorAll('#filterForm [name]').forEach(f => {
                            if (f.name !== 'search' && f.name !== '_token') f.value = '';
                        });
                        document.getElementById('filterForm').submit();
                    }, 500);
                });
            }
        @endif
    </script>
@endsection
