@extends('layouts.admin')
@section('title', 'Student Management')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endpush
@section('admin-content')
    <div class="container-fluid px-4 py-4">
        <x-page-header title="Student Management" subtitle="All students across all agents">
            <x-slot:actions>
                <a href="{{ route('admin.students.create') }}" class="btn btn-primary btn-sm"><i
                        class="fa-solid fa-plus me-1"></i> Add Student</a>
                <a href="{{ route('admin.exports.index') }}" class="btn btn-outline-secondary btn-sm"><i
                        class="fa-solid fa-download me-1"></i> Export</a>
            </x-slot:actions>
        </x-page-header>

        <div class="card border-0 shadow-sm mb-4 rounded-3">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('admin.students.index') }}" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold mb-1">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="form-control form-control-sm" placeholder="Name, email, agent, course..."
                                id="searchInput" onkeyup="debounceSubmit()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Country</label>
                            <select name="country" class="form-select form-select-sm"
                                onchange="submitWithSingleFilter('country', this.value)" id="countrySelect">
                                <option value="">All Countries ({{ $countries->count() }})</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->name }}"
                                        {{ request('country') == $country->name ? 'selected' : '' }}>{{ $country->name }}
                                        ({{ $country->count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">University</label>
                            <select name="university" class="form-select form-select-sm"
                                onchange="submitWithSingleFilter('university', this.value)" id="universitySelect">
                                <option value="">All</option>
                                @foreach ($universities as $uni)
                                    <option value="{{ $uni->id }}"
                                        {{ request('university') == $uni->id ? 'selected' : '' }}>{{ $uni->name }}
                                        ({{ $uni->applications_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Agent</label>
                            <select name="agent" class="form-select form-select-sm"
                                onchange="submitWithSingleFilter('agent', this.value)" id="agentSelect">
                                <option value="">All</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}"
                                        {{ request('agent') == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->business_name ?? $agent->username }} ({{ $agent->students_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm"
                                onchange="submitWithSingleFilter('status', this.value)" id="statusSelect">
                                <option value="">All</option>
                                @foreach ($applicationStatuses as $status)
                                    <option value="{{ $status->id }}"
                                        {{ request('status') == $status->id ? 'selected' : '' }}>{{ $status->name }}
                                        ({{ $status->applications_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            @if (request()->hasAny(['search', 'status', 'agent', 'university', 'country']))
                                <a href="{{ route('admin.students.index') }}"
                                    class="btn btn-sm btn-outline-danger w-100">Clear</a>
                            @endif
                        </div>
                    </div>
                    <input type="hidden" name="order" value="{{ request('order', 'desc') }}">
                    <input type="hidden" name="quick_filter" value="{{ request('quick_filter') }}">
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div class="d-flex gap-2 flex-wrap">
                @php
                    $qf = request('quick_filter');
                    $baseQ = request()->except(['quick_filter', 'page', 'page1', 'page2']);
                @endphp
                <a href="{{ route('admin.students.index', array_merge($baseQ, ['quick_filter' => 'applied'])) }}"
                    class="btn btn-sm {{ $qf === 'applied' ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill">Applied</a>
                <a href="{{ route('admin.students.index', array_merge($baseQ, ['quick_filter' => 'not_applied'])) }}"
                    class="btn btn-sm {{ $qf === 'not_applied' ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill">Not
                    Applied</a>
                <a href="{{ route('admin.students.index', $baseQ) }}"
                    class="btn btn-sm {{ !$qf ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill">All Students</a>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <span class="small text-muted">Sort:</span>
                <a href="{{ route('admin.students.index', array_merge(request()->except('order', 'page', 'page1', 'page2'), ['order' => 'asc'])) }}"
                    class="btn btn-sm {{ request('order', 'desc') == 'asc' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill">Old
                    First</a>
                <a href="{{ route('admin.students.index', array_merge(request()->except('order', 'page', 'page1', 'page2'), ['order' => 'desc'])) }}"
                    class="btn btn-sm {{ request('order', 'desc') == 'desc' ? 'btn-secondary' : 'btn-outline-secondary' }} rounded-pill">New
                    First</a>
            </div>
        </div>

        @include('admin.students._table', [
            'students' => $table1Students,
            'title' => 'All Students (except partner agents)',
            'pageName' => 'page1',
            'totalRequiredDocs' => $totalRequiredDocs,
        ])
        <div style="margin-bottom:1.5rem;"></div>
        @include('admin.students._table', [
            'students' => $table2Students,
            'title' => 'Students of Partner Agents (ID: 11-12)',
            'pageName' => 'page2',
            'totalRequiredDocs' => $totalRequiredDocs,
        ])
    </div>

    <script>
        let debounceTimer, isSubmitting = false;
        const currentQf = '{{ request('quick_filter') }}';
        const currentOrder = '{{ request('order', 'desc') }}';

        function submitWithSingleFilter(filterName, filterValue) {
            if (isSubmitting) return;
            isSubmitting = true;
            const params = new URLSearchParams();
            if (filterValue) params.set(filterName, filterValue);
            if (currentQf) params.set('quick_filter', currentQf);
            if (currentOrder) params.set('order', currentOrder);
            window.location.href = '{{ route('admin.students.index') }}?' + params.toString();
            setTimeout(() => isSubmitting = false, 500);
        }

        function debounceSubmit() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                if (isSubmitting) return;
                isSubmitting = true;
                const searchVal = document.getElementById('searchInput').value;
                const params = new URLSearchParams();
                if (searchVal) params.set('search', searchVal);
                if (currentQf) params.set('quick_filter', currentQf);
                if (currentOrder) params.set('order', currentOrder);
                window.location.href = '{{ route('admin.students.index') }}?' + params.toString();
                setTimeout(() => isSubmitting = false, 500);
            }, 500);
        }

        document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                debounceSubmit();
            }
        });
    </script>
@endsection
