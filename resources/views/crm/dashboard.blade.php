@extends('layouts.crm')

@section('title', 'CRM Pipeline')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endpush

@section('content')
    <div class="container-fluid">
        @include('crm.components.dashboard.core-utilities')

        @include('crm.components.dashboard.stat-filters')

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

    {{-- FAB defined in core-utilities --}}
    <button class="fab-add-student" data-bs-toggle="modal" data-bs-target="#addStudentModal">
        <i class="fas fa-plus"></i>
    </button>

    @include('crm.components.dashboard.modals')
@endsection

@push('scripts')
    <script>
        (function() {
            'use strict';
            var CRM = window.CrmCore?.getInstance();
            var timer, view = '{{ $view ?? 'kanban' }}';

            window.addEventListener('crm:beforeViewChange', function(e) {
                if (view === 'calendar' && e.detail.view !== 'calendar') {
                    if (window.CrmCalendar && typeof window.CrmCalendar.destroy === 'function') window
                        .CrmCalendar.destroy();
                }
            });

            if (view === 'calendar') {
                if (typeof FullCalendar !== 'undefined') {
                    setTimeout(function() {
                        if (window.CrmCalendar && typeof window.CrmCalendar.init === 'function') window
                            .CrmCalendar.init();
                    }, 80);
                }
            } else if (view === 'kanban') {
                setTimeout(function() {
                    if (window.CrmKanban && typeof window.CrmKanban.init === 'function') window.CrmKanban
                .init();
                    if (window.CrmWeeklyReview && typeof window.CrmWeeklyReview.load === 'function') window
                        .CrmWeeklyReview.load();
                }, 80);
            }
        })();
    </script>
@endpush
