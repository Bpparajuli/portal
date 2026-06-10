@props([
    'id' => 'dataTable',
    'title' => '',
    'headers' => [],
    'rows' => null,
    'rowComponent' => null,
    'rowView' => null,
    'routePrefix' => 'admin',
    'totalRequiredDocs' => 0,
    'emptyMessage' => 'No records found.',
    'pagination' => null,
    'searchable' => true,
    'searchId' => null,
    'actions' => null,
    'isAgent' => false,
    'isStaff' => false,
    'isMgmt' => false,
    'theme' => 'default',
    'tableClass' => '',
])

@php
    $searchId = $searchId ?? $id . '-search';
    $isEmpty = !$rows || (is_object($rows) && method_exists($rows, 'count') && $rows->count() === 0);
@endphp

<div class="card border-0 shadow-sm rounded-3 mb-4">
    @if($title || $searchable || $actions)
    <div class="card-header bg-white border-0 px-4 pt-4 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
        @if($title)
            <h6 class="fw-bold mb-0 small text-uppercase text-muted">{{ $title }}</h6>
        @endif
        @if($searchable)
            <div class="d-flex align-items-center gap-2 ms-auto">
                <i class="fas fa-search text-muted" style="font-size:0.75rem;"></i>
                <input type="text" class="form-control form-control-sm datatable-search"
                       id="{{ $searchId }}" placeholder="Search..." style="max-width:220px;"
                       data-table="{{ $id }}">
            </div>
        @endif
        @if($actions)
            <div class="d-flex gap-2">{{ $actions }}</div>
        @endif
    </div>
    @endif

    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 {{ $tableClass }}" id="{{ $id }}">
            @if(count($headers) > 0)
            <thead>
                <tr>
                    @foreach($headers as $header)
                    <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            @endif
            <tbody>
                @if($isEmpty)
                    <tr>
                        <td colspan="{{ count($headers) ?: 1 }}" class="text-center py-5 text-muted small">
                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @elseif($rowView)
                    @foreach($rows as $student)
                        @include($rowView, [
                            'student' => $student,
                            'routePrefix' => $routePrefix,
                            '__routePrefix' => $routePrefix,
                            '__prefix' => $routePrefix,
                            'isAgent' => $isAgent,
                            'isStaff' => $isStaff,
                            'isMgmt' => $isMgmt,
                            '__isAgent' => $isAgent,
                            '__isStaff' => $isStaff,
                            '__isMgmt' => $isMgmt,
                            'totalRequiredDocs' => $totalRequiredDocs,
                        ])
                    @endforeach
                @elseif($rowComponent)
                    @foreach($rows as $row)
                        <x-dynamic-component
                            :component="$rowComponent"
                            :$row
                            :$routePrefix
                            :$totalRequiredDocs
                            :$isAgent
                            :$isStaff
                            :$isMgmt
                        />
                    @endforeach
                @else
                    {{ $slot ?? '' }}
                @endif
            </tbody>
        </table>
    </div>

    @if($pagination && method_exists($pagination, 'hasPages') && $pagination->hasPages())
    <div class="card-footer bg-white border-0 px-4 py-3">
        {{ $pagination->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.datatable-search').forEach(function (input) {
        var timer;
        input.addEventListener('keyup', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                var table = document.getElementById(input.dataset.table);
                if (!table) return;
                var q = input.value.toLowerCase();
                table.querySelectorAll('tbody tr').forEach(function (row) {
                    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            }, 300);
        });
    });
});
</script>
@endpush
