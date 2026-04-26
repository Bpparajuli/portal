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
                                class="form-control form-control-sm" placeholder="Name or email…">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Applied Country</label>
                            <select name="country" class="form-select form-select-sm">
                                <option value="">All Countries</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country }}"
                                        {{ request('country') == $country ? 'selected' : '' }}>
                                        {{ $country }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Applied University</label>
                            <select name="university" class="form-select form-select-sm">
                                <option value="">All Universities</option>
                                @foreach ($universities as $uni)
                                    <option value="{{ $uni->id }}">
                                        {{ $uni->name }}– {{ $uni->city }} ({{ $uni->applications_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Agent</label>
                            <select name="agent" class="form-select form-select-sm">
                                <option value="">All Agents</option>
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
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach ($applicationStatuses as $status)
                                    <option value="{{ $status->application_status }}"
                                        {{ request('status') == $status->application_status ? 'selected' : '' }}>
                                        {{ $status->application_status }} ({{ $status->total }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-1 d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                            @if (request()->hasAny(['search', 'status', 'agent', 'university']))
                                <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-danger">
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

        </>

    @endsection
