@extends('layouts.app')

@section('title', 'Student Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endpush
@section('content') <div class="students-page">

        {{-- ── Page Header ── --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h1 class="page-title mb-0"><i class="fa-solid fa-users me-2 text-primary"></i> Student Management</h1>
                <p class="text-muted small mb-0 mt-1">All students across all agents</p>
            </div>
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i> Add Student
            </a>
        </div>

        {{-- ── Filters ── --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('admin.students.index') }}" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold mb-1">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="form-control form-control-sm" placeholder="Name or email…" id="searchInput"
                                onkeyup="debounceSubmit()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Applied Country</label>
                            <select name="applied_country" class="form-select form-select-sm"
                                onchange="submitWithSingleFilter('applied_country', this.value)" id="appliedCountrySelect">
                                <option value="">Applied Countries ({{ $countries->count() }})</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->name }}"
                                        {{ request('applied_country') == $country->name ? 'selected' : '' }}>
                                        {{ $country->name }} ({{ $country->count }} apps)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Applied University</label>
                            <select name="university" class="form-select form-select-sm"
                                onchange="submitWithSingleFilter('university', this.value)" id="universitySelect">
                                <option value="">All Universities ({{ $universities->count() }})</option>
                                @foreach ($universities as $uni)
                                    <option value="{{ $uni->id }}"
                                        {{ request('university') == $uni->id ? 'selected' : '' }}>
                                        {{ $uni->name }}– {{ $uni->city }} ({{ $uni->applications_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Agent</label>
                            <select name="agent" class="form-select form-select-sm"
                                onchange="submitWithSingleFilter('agent', this.value)" id="agentSelect">
                                <option value="">Applying Agents ({{ $agents->count() }})</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}"
                                        {{ request('agent') == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->business_name ?? $agent->username }}
                                        ({{ $agent->students_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Application Status</label>
                            <select name="status" class="form-select form-select-sm"
                                onchange="submitWithSingleFilter('status', this.value)" id="statusSelect">
                                <option value="">All Status ({{ $applicationStatuses->count() }})</option>
                                @foreach ($applicationStatuses as $status)
                                    <option value="{{ $status->id }}"
                                        {{ request('status') == $status->id ? 'selected' : '' }}>
                                        {{ $status->name }} ({{ $status->applications_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-1">
                            @if (request()->hasAny(['search', 'status', 'agent', 'university', 'applied_country']))
                                <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-danger w-100"
                                    onclick="return clearAllFilters()">
                                    Clear Filters
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Preserve quick_filter in filter form --}}
                    @if (request('quick_filter'))
                        <input type="hidden" name="quick_filter" value="{{ request('quick_filter') }}">
                    @endif
                </form>
            </div>
        </div>

        {{-- ── Quick Filters ── --}}
        <div class="d-flex justify-content-between p-2 ">

            <div class="d-flex">
                @php
                    $qf = request('quick_filter');
                    $base = request()->except('quick_filter');
                @endphp
                <a href="{{ route('admin.students.index', array_merge($base, ['quick_filter' => 'applied'])) }}"
                    class="quick-pill {{ $qf === 'applied' ? 'active-applied' : '' }}">
                    Applied
                </a>
                <a href="{{ route('admin.students.index', array_merge($base, ['quick_filter' => 'not_applied'])) }}"
                    class="quick-pill {{ $qf === 'not_applied' ? 'active-not' : '' }}">
                    Not Applied
                </a>
                <a href="{{ route('admin.students.index', array_merge($base)) }}"
                    class="quick-pill {{ !$qf ? 'active-all' : '' }}">
                    All Students
                </a>
            </div>

            <div class="d-flex gap-2 align-items-center">
                <span class="small text-muted">Sort:</span>

                <a href="{{ request()->fullUrlWithQuery(['order' => 'asc']) }}"
                    class="btn btn-sm {{ request('order', 'desc') == 'asc' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Old Students First
                </a>

                <a href="{{ request()->fullUrlWithQuery(['order' => 'desc']) }}"
                    class="btn btn-sm {{ request('order', 'desc') == 'desc' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                    New Students First
                </a>
            </div>

        </div>
        {{-- ── Table 1: All Students ── --}}
        @include('admin.students._table', [
            'students' => $table1Students,
            'title' => 'All Students',
            'pageName' => 'page1',
            'totalRequiredDocs' => $totalRequiredDocs,
        ])

        {{-- ── Table 2: Partner Students ── --}}
        @include('admin.students._table', [
            'students' => $table2Students,
            'title' => 'Students of Idea Consultancy',
            'pageName' => 'page2',
            'totalRequiredDocs' => $totalRequiredDocs,
        ])


        <script>
            let debounceTimer;
            let isSubmitting = false;

            function submitWithSingleFilter(filterName, filterValue) {
                if (isSubmitting) return;
                isSubmitting = true;

                // Build URL with ONLY the selected filter, resetting all others
                let url = '{{ route('admin.students.index') }}?';
                const params = [];

                // Add ONLY the selected filter if it has a value
                if (filterValue && filterValue !== '') {
                    params.push(`${filterName}=${encodeURIComponent(filterValue)}`);
                }

                // Add quick_filter if exists
                const quickFilter = document.querySelector('input[name="quick_filter"]');
                if (quickFilter && quickFilter.value) {
                    params.push(`quick_filter=${encodeURIComponent(quickFilter.value)}`);
                }

                url += params.join('&');

                // Redirect to the new URL (all other filters are reset)
                window.location.href = url;

                setTimeout(() => {
                    isSubmitting = false;
                }, 500);
            }

            function debounceSubmit() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    if (isSubmitting) return;
                    isSubmitting = true;

                    // Get ONLY the search value, reset all other filters
                    const searchValue = document.getElementById('searchInput').value;

                    // Build URL with ONLY search filter
                    let url = '{{ route('admin.students.index') }}?';
                    const params = [];

                    if (searchValue && searchValue !== '') {
                        params.push(`search=${encodeURIComponent(searchValue)}`);
                    }

                    // Add quick_filter if exists
                    const quickFilter = document.querySelector('input[name="quick_filter"]');
                    if (quickFilter && quickFilter.value) {
                        params.push(`quick_filter=${encodeURIComponent(quickFilter.value)}`);
                    }

                    url += params.join('&');

                    // Redirect to the new URL
                    window.location.href = url;

                    setTimeout(() => {
                        isSubmitting = false;
                    }, 500);
                }, 500);
            }

            function clearAllFilters() {
                // Clear all input values visually
                document.getElementById('searchInput').value = '';
                document.getElementById('countrySelect').value = '';
                document.getElementById('universitySelect').value = '';
                document.getElementById('agentSelect').value = '';
                document.getElementById('statusSelect').value = '';

                // Remove quick_filter if exists
                const quickFilter = document.querySelector('input[name="quick_filter"]');
                if (quickFilter) {
                    quickFilter.value = '';
                }

                // Redirect to base URL without any filters
                window.location.href = '{{ route('admin.students.index') }}';

                return false; // Prevent default anchor behavior
            }

            // Auto-submit when user presses Enter in search field
            document.getElementById('searchInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    debounceSubmit();
                }
            });

            // Sync the select elements with URL params on page load
            document.addEventListener('DOMContentLoaded', function() {
                const urlParams = new URLSearchParams(window.location.search);

                // Only set values that exist in URL, others remain empty
                if (urlParams.has('country')) {
                    document.getElementById('countrySelect').value = urlParams.get('country');
                }
                if (urlParams.has('university')) {
                    document.getElementById('universitySelect').value = urlParams.get('university');
                }
                if (urlParams.has('agent')) {
                    document.getElementById('agentSelect').value = urlParams.get('agent');
                }
                if (urlParams.has('status')) {
                    document.getElementById('statusSelect').value = urlParams.get('status');
                }
                if (urlParams.has('search')) {
                    document.getElementById('searchInput').value = urlParams.get('search');
                }
            });
        </script>
    @endsection
