@extends('layouts.staff')

@section('content')<style>
    body {
        background-color: #f0f2f5;
        font-family: 'Inter', sans-serif;
    }

    .kanban-wrapper {
        display: flex;
        overflow-x: auto;
        padding: 20px;
        gap: 15px;
        height: calc(100vh - 80px);
    }

    .kanban-column {
        min-width: 280px;
        width: 280px;
        background: #ebedf0;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
    }

    .column-header {
        padding: 12px 15px;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        color: #5e6c84;
        display: flex;
        justify-content: space-between;
    }

    .card-item {
        background: #fff;
        border-radius: 4px;
        padding: 10px;
        margin-bottom: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        border-left: 4px solid #dfe1e6;
        transition: 0.2s;
        text-decoration: none !important;
        color: inherit;
        display: block;
    }

    .card-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    /* Image-Specific Border Colors */
    .b-student {
        border-left-color: #0052cc;
    }

    .b-visitor {
        border-left-color: #36b37e;
    }

    .b-work {
        border-left-color: #ffab00;
    }

    .visa-badge {
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 3px;
        background: #f4f5f7;
        color: #42526e;
        font-weight: 600;
    }

    .card-name {
        font-size: 13px;
        font-weight: 600;
        color: #172b4d;
        margin: 5px 0;
    }

    .card-info {
        font-size: 11px;
        color: #5e6c84;
    }

    .avatar-sm {
        width: 22px;
        height: 22px;
        border-radius: 50%;
    }

</style>

<div class="p-3">
    <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-2 rounded shadow-sm">
        <div class="input-group w-25">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search"></i></span>
            <input type="text" id="boardSearch" class="form-control border-start-0" placeholder="Search leads...">
        </div>
        <button class="btn btn-primary btn-sm">+ New Lead</button>
    </div>

    <div class="kanban-wrapper">
        @foreach($columns as $col)
        <div class="kanban-column">
            <div class="column-header">
                <span>{{ $col['title'] }}</span>
                <span class="text-muted">{{ count(array_filter($leads, fn($l) => $l['status'] == $col['title'])) }}</span>
            </div>
            <div class="px-2 overflow-auto flex-grow-1" id="col-{{ $col['id'] }}">
                @foreach($leads as $lead)
                @if($lead['status'] == $col['title'])
                <a href="{{ route('staff.student', $lead['id']) }}" class="card-item {{ $lead['border_class'] }}" data-search="{{ strtolower($lead['name']) }} {{ $lead['id'] }}">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="visa-badge">{{ $lead['visa_type'] }}</span>
                        <small class="text-muted" style="font-size: 9px;">#{{ $lead['id'] }}</small>
                    </div>
                    <div class="card-name">{{ $lead['name'] }}</div>
                    <div class="card-info mb-2"><i class="fas fa-plane me-1"></i> {{ $lead['country'] }}</div>
                    <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($lead['name']) }}&background=random" class="avatar-sm">
                        <span class="text-muted" style="font-size: 9px;">Updated {{ $lead['updated_at'] }}</span>
                    </div>
                </a>
                @endif
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
    document.getElementById('boardSearch').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll('.card-item').forEach(card => {
            card.style.display = card.dataset.search.includes(val) ? 'block' : 'none';
        });
    });

</script>
@endsection
