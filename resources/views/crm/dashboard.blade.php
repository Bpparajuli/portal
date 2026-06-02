{{-- resources/views/crm/dashboard.blade.php --}}
@extends('layouts.crm')

@section('title', 'CRM Pipeline')

@push('styles')
    {{-- External Libraries --}}
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        :root {
            --crm-primary: #4f46e5;
            --crm-primary-dark: #1a0262;
            --crm-danger: #ef4444;
            --crm-success: #10b981;
            --crm-warning: #f59e0b;
            --crm-info: #3b82f6;
            --crm-gray: #6b7280;
            --crm-border: #e5e7eb;
            --crm-bg: #f3f4f6;
        }

        body {
            background: #f8fafc;
        }

        /* FAB Button */
        .fab-add-student {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--crm-primary);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            transition: all 0.2s;
        }

        .fab-add-student:hover {
            background: var(--crm-primary-dark);
            transform: scale(1.05);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        {{-- Include Core Utilities (ONCE at page level) --}}
        @include('crm.components.dashboard.core-utilities')

        {{-- Stats & Filters --}}
        @include('crm.components.dashboard.stat-filters')

        {{-- Dynamic View based on current selection --}}
        @php $currentView = $view ?? 'kanban'; @endphp

        @if ($currentView === 'kanban')
            @include('crm.components.dashboard.kanban-board')
            @include('crm.components.dashboard.pipeline-weekly')
        @elseif($currentView === 'list')
            @include('crm.components.dashboard.list-view')
        @elseif($currentView === 'calendar')
            @include('crm.components.dashboard.calendar-view')
        @endif

    </div>

    {{-- FAB Button --}}
    <button class="fab-add-student" data-bs-toggle="modal" data-bs-target="#addStudentModal">
        <i class="fas fa-plus"></i>
    </button>

    {{-- Modals --}}
    @include('crm.components.dashboard.modals')
@endsection

@push('scripts')
    <script>
        // ============================================
        // DASHBOARD INITIALIZATION
        // ============================================
        (function() {
            'use strict';

            const CRM = window.CrmCore.getInstance();
            let searchTimer = null;
            const currentView = '{{ $view ?? 'kanban' }}';

            // Auto-submit search on input
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => {
                        document.getElementById('crmFilterForm')?.submit();
                    }, 500);
                });
            }

            // Listen for view change events
            window.addEventListener('crm:beforeViewChange', function(e) {
                // Destroy calendar if switching away from calendar view
                if (currentView === 'calendar' && e.detail.view !== 'calendar') {
                    if (window.CrmCalendar && typeof window.CrmCalendar.destroy === 'function') {
                        window.CrmCalendar.destroy();
                    }
                }
            });

            // Initialize components based on current view
            if (currentView === 'calendar') {
                // Wait for FullCalendar to load
                if (typeof FullCalendar !== 'undefined') {
                    setTimeout(() => {
                        if (window.CrmCalendar && typeof window.CrmCalendar.init === 'function') {
                            window.CrmCalendar.init();
                        }
                    }, 100);
                }
            } else if (currentView === 'kanban') {
                setTimeout(() => {
                    if (window.CrmKanban && typeof window.CrmKanban.init === 'function') {
                        window.CrmKanban.init();
                    }
                    if (window.CrmWeeklyReview && typeof window.CrmWeeklyReview.load === 'function') {
                        window.CrmWeeklyReview.load();
                    }
                }, 100);
            }
        })();
    </script>
@endpush
