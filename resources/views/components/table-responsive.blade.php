@props(['id' => 'dataTable', 'searchable' => true])
<div class="card mb-4">
    @if($searchable)
    <div class="p-3 pb-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-search text-muted" style="font-size:var(--text-sm);"></i>
                <input type="text" class="form-control form-control-sm" id="{{ $id }}-search" placeholder="Search..." style="max-width:250px;">
            </div>
            {{ $actions ?? '' }}
        </div>
    </div>
    @endif
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="{{ $id }}">
            {{ $slot }}
        </table>
    </div>
    @if(isset($pagination))
    <div class="card-footer">
        {{ $pagination }}
    </div>
    @endif
</div>
