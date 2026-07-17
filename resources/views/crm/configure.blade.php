{{-- resources/views/crm/configure.blade.php --}}
@extends('layouts.crm')

@section('title', 'CRM — Configure Stages')

@push('styles')
    <style>
        .cfg-wrapper { max-width: 1120px; margin: 0 auto; padding: 1.5rem; }
        .cfg-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .cfg-header h4 { margin: 0; font-size: 1.15rem; font-weight: 700; color: #1f2937; }
        .cfg-header p { margin: 2px 0 0; font-size: .78rem; color: #6b7280; }
        .cfg-grid { display: grid; grid-template-columns: 1fr 380px; gap: 1.25rem; align-items: start; }
        @media (max-width: 900px) { .cfg-grid { grid-template-columns: 1fr; } }

        .cfg-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
        .cfg-card-header { padding: .65rem 1rem; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: .78rem; color: #374151; background: #fafbfc; display: flex; align-items: center; justify-content: space-between; }

        .cfg-stage-list { padding: .75rem; display: flex; flex-direction: column; gap: 6px; min-height: 120px; }

        .cfg-stage { display: flex; align-items: center; gap: 10px; padding: .55rem .75rem; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; cursor: grab; transition: box-shadow .15s, border-color .15s; }
        .cfg-stage:hover { box-shadow: 0 2px 8px rgba(0,0,0,.06); border-color: #cbd5e1; }
        .cfg-stage.dragging { opacity: .4; }
        .cfg-stage.inactive { opacity: .55; background: #fafbfc; }
        .cfg-stage.inactive .cfg-stage-name { text-decoration: line-through; color: #9ca3af; }

        .cfg-drag { color: #d1d5db; font-size: 1rem; cursor: grab; flex-shrink: 0; transition: color .12s; }
        .cfg-stage:hover .cfg-drag { color: #9ca3af; }

        .cfg-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; border: 2px solid rgba(0,0,0,.08); }

        .cfg-stage-body { flex: 1; min-width: 0; }
        .cfg-stage-name { font-weight: 600; font-size: .82rem; color: #1f2937; line-height: 1.2; }
        .cfg-stage-meta { font-size: .68rem; color: #9ca3af; margin-top: 1px; }

        .cfg-stage-side { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }

        .cfg-count { font-size: .7rem; color: #6b7280; background: #f3f4f6; padding: 1px 8px; border-radius: 10px; font-weight: 500; white-space: nowrap; }

        .cfg-badge { font-size: .6rem; border-radius: 4px; padding: 1px 6px; font-weight: 600; letter-spacing: .02em; }
        .cfg-badge-won { background: #d1fae5; color: #065f46; }
        .cfg-badge-lost { background: #fee2e2; color: #991b1b; }
        .cfg-badge-inactive { background: #f3f4f6; color: #9ca3af; }

        .cfg-actions { display: flex; gap: 2px; }
        .cfg-actions button, .cfg-actions a, .cfg-actions form { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 5px; border: 1px solid transparent; background: transparent; cursor: pointer; transition: all .12s; font-size: .72rem; color: #9ca3af; padding: 0; text-decoration: none; }
        .cfg-actions button:hover, .cfg-actions a:hover { background: #f3f4f6; border-color: #e5e7eb; color: #374151; }
        .cfg-actions .text-danger:hover { background: #fef2f2; border-color: #fecaca; color: #dc2626; }

        .cfg-form { padding: 1.25rem; }
        .cfg-form .form-label { font-size: .75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
        .cfg-form .form-control, .cfg-form .form-select { font-size: .82rem; padding: .4rem .65rem; border-radius: 6px; border-color: #e5e7eb; background: #fafbfc; }
        .cfg-form .form-control:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); background: #fff; }
        .cfg-form textarea.form-control { resize: vertical; }
        .cfg-form .form-text { font-size: .7rem; color: #9ca3af; margin-top: 3px; }

        .cfg-color-row { display: flex; align-items: center; gap: .6rem; margin-bottom: .6rem; }
        .cfg-color-swatch { width: 30px; height: 30px; border-radius: 7px; border: 2px solid rgba(0,0,0,.1); flex-shrink: 0; }
        .cfg-color-hex { font-size: .75rem; font-family: ui-monospace, monospace; color: #6b7280; flex: 1; }
        .cfg-color-custom { display: inline-flex; align-items: center; gap: .3rem; font-size: .74rem; font-weight: 500; padding: .25rem .6rem; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; background: #fafbfc; color: #374151; transition: all .12s; position: relative; }
        .cfg-color-custom:hover { background: #f3f4f6; border-color: #d1d5db; }
        .cfg-color-custom input[type="color"] { position: absolute; opacity: 0; width: 0; height: 0; }
        .cfg-color-grid { display: grid; grid-template-columns: repeat(10, 1fr); gap: 4px; padding: .6rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; }
        .cfg-color-swatch-btn { width: 100%; aspect-ratio: 1; border-radius: 5px; border: 2px solid transparent; cursor: pointer; transition: .12s; padding: 0; }
        .cfg-color-swatch-btn:hover { transform: scale(1.15); }
        .cfg-color-swatch-btn.selected { border-color: #fff; box-shadow: 0 0 0 2px #6366f1; transform: scale(1.12); }

        .cfg-toggle { display: flex; align-items: center; justify-content: space-between; padding: .5rem 0; }
        .cfg-toggle label { font-size: .8rem; font-weight: 500; color: #374151; margin: 0; cursor: pointer; }
        .cfg-switch { position: relative; width: 36px; height: 20px; flex-shrink: 0; }
        .cfg-switch input { opacity: 0; width: 0; height: 0; }
        .cfg-switch-slider { position: absolute; inset: 0; background: #d1d5db; border-radius: 20px; cursor: pointer; transition: .15s; }
        .cfg-switch-slider::before { content: ''; position: absolute; width: 15px; height: 15px; left: 3px; top: 2.5px; background: #fff; border-radius: 50%; transition: .15s; }
        .cfg-switch input:checked+.cfg-switch-slider { background: #6366f1; }
        .cfg-switch input:checked+.cfg-switch-slider::before { transform: translateX(16px); }

        .cfg-empty { text-align: center; padding: 2rem; color: #9ca3af; font-size: .82rem; }
        .cfg-empty i { font-size: 1.6rem; display: block; margin-bottom: .5rem; color: #d1d5db; }

        .stage-sortable-ghost { opacity: .3; background: #eef2ff; border: 1px dashed #6366f1; border-radius: 8px; }
    </style>
@endpush

@section('content')
    <div class="cfg-wrapper">

        <div class="cfg-header">
            <div>
                <h4>Pipeline Stages</h4>
                <p>Drag to reorder · Configure stage names, colors, and behaviour</p>
            </div>
            <a href="{{ route('crm.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Dashboard
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2" role="alert" style="font-size:.82rem">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="cfg-grid">

            {{-- ═══ LEFT: Stage list ═══ --}}
            <div class="cfg-card">
                <div class="cfg-card-header">
                    <span><i class="fas fa-layer-group me-1 text-muted"></i> Stages ({{ $stages->count() }})</span>
                    <span style="font-size:.7rem;color:#9ca3af;font-weight:400">Drag <i class="fas fa-grip-vertical"></i> to reorder</span>
                </div>
                <div class="cfg-stage-list" id="stageList">
                    @forelse ($stages as $stage)
                        <div class="cfg-stage @if (!$stage->is_active) inactive @endif" data-id="{{ $stage->id }}">
                            <span class="cfg-drag"><i class="fas fa-grip-vertical"></i></span>
                            <span class="cfg-dot" style="background:{{ $stage->color }}"></span>
                            <div class="cfg-stage-body">
                                <div class="cfg-stage-name">{{ $stage->name }}</div>
                                <div class="cfg-stage-meta">{{ $stage->students()->count() }} student{{ $stage->students()->count() !== 1 ? 's' : '' }}</div>
                            </div>
                            <div class="cfg-stage-side">
                                <span class="cfg-count">{{ $stage->students()->count() }}</span>
                                <div class="cfg-badges" style="display:flex;gap:3px">
                                    @if ($stage->is_won_stage)
                                        <span class="cfg-badge cfg-badge-won">Won</span>
                                    @endif
                                    @if ($stage->is_lost_stage)
                                        <span class="cfg-badge cfg-badge-lost">Lost</span>
                                    @endif
                                    @if (!$stage->is_active)
                                        <span class="cfg-badge cfg-badge-inactive">Off</span>
                                    @endif
                                </div>
                                <div class="cfg-actions">
                                    <button onclick="loadEditForm({{ $stage->id }}, '{{ addslashes($stage->name) }}', '{{ $stage->color }}', '{{ addslashes($stage->description ?? '') }}', {{ $stage->is_won_stage ? 'true' : 'false' }}, {{ $stage->is_lost_stage ? 'true' : 'false' }}, {{ $stage->max_days_in_stage ?? 'null' }})" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button onclick="toggleActive({{ $stage->id }}, this)" title="{{ $stage->is_active ? 'Disable' : 'Enable' }}">
                                        <i class="fas fa-{{ $stage->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                    @if ($stage->students()->count() === 0)
                                        <x-confirm-delete
                                            url="{{ route('crm.configure.destroy', $stage) }}"
                                            label=""
                                            title="Delete stage?"
                                            message="Delete this stage? This cannot be undone."
                                        />
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="cfg-empty">
                            <i class="fas fa-layer-group"></i>
                            No stages yet — create your first one!
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ═══ RIGHT: Create / Edit form ═══ --}}
            <div class="cfg-card">
                <div class="cfg-card-header">
                    <span id="formTitle"><i class="fas fa-plus-circle me-1 text-muted"></i> Add Stage</span>
                </div>
                <div class="cfg-form">

                    {{-- Create form --}}
                    <form id="stageCreateForm" action="{{ route('crm.configure.store') }}" method="POST">
                        @csrf
                        @include('crm.partials._stage_form', [
                            'stage' => null,
                            'mode' => 'create',
                        ])
                        <button type="submit" class="btn btn-sm btn-dark w-100 mt-2">
                            <i class="fas fa-plus me-1"></i> Create Stage
                        </button>
                    </form>

                    {{-- Edit form (hidden until Edit clicked) --}}
                    <form id="stageEditForm" style="display:none" method="POST">
                        @csrf @method('PUT')
                        @include('crm.partials._stage_form', [
                            'stage' => null,
                            'mode' => 'edit',
                        ])
                        <div class="d-flex gap-2 mt-2">
                            <button type="submit" class="btn btn-sm btn-dark flex-grow-1">
                                <i class="fas fa-save me-1"></i> Update Stage
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="showCreateForm()">
                                Cancel
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script>
        const COLORS = [
            '#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16',
            '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#3b82f6',
            '#6366f1', '#8b5cf6', '#a855f7', '#ec4899', '#64748b',
        ];

        Sortable.create(document.getElementById('stageList'), {
            handle: '.cfg-drag',
            animation: 150,
            ghostClass: 'stage-sortable-ghost',
            onEnd: function() {
                const ids = [...document.querySelectorAll('.cfg-stage')].map(el => el.dataset.id);
                fetch('{{ route('crm.configure.reorder') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order: ids })
                });
            }
        });

        function loadEditForm(id, name, color, desc, isWon, isLost, maxDays) {
            document.getElementById('stageCreateForm').style.display = 'none';
            const form = document.getElementById('stageEditForm');
            form.style.display = 'block';
            form.action = `/crm/configure/${id}`;
            document.getElementById('formTitle').innerHTML = '<i class="fas fa-pen me-1 text-muted"></i> Edit Stage';

            form.querySelector('[name="name"]').value = name;
            form.querySelector('[name="description"]').value = desc;
            form.querySelector('[name="max_days_in_stage"]').value = maxDays ?? '';
            form.querySelector('[name="is_won_stage"]').checked = isWon;
            form.querySelector('[name="is_lost_stage"]').checked = isLost;

            form.querySelectorAll('.cfg-color-swatch-btn').forEach(s => {
                s.classList.toggle('selected', s.dataset.color === color);
            });
            form.querySelector('[name="color"]').value = color;
            form.querySelector('.cfg-color-swatch').style.background = color;
            form.querySelector('.cfg-color-hex').textContent = color;
            form.querySelector('.cfg-color-custom input[type="color"]').value = color;
        }

        function showCreateForm() {
            document.getElementById('stageCreateForm').style.display = 'block';
            document.getElementById('stageEditForm').style.display = 'none';
            document.getElementById('formTitle').innerHTML = '<i class="fas fa-plus-circle me-1 text-muted"></i> Add Stage';
        }

        function toggleActive(id, btn) {
            fetch(`/crm/configure/${id}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    btn.innerHTML = data.is_active
                        ? '<i class="fas fa-pause"></i>'
                        : '<i class="fas fa-play"></i>';
                    btn.title = data.is_active ? 'Disable' : 'Enable';
                    window.location.reload();
                });
        }
    </script>
@endpush
