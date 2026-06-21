{{-- resources/views/crm/configure.blade.php --}}
@extends('layouts.crm')

@section('title', 'CRM — Configure Stages')

@push('styles')
    <style>
        .config-grid { display: grid; grid-template-columns: 1fr 360px; gap: var(--hd-md); padding: var(--hd-md); }
        @media (max-width: 900px) { .config-grid { grid-template-columns: 1fr; } }
        .config-card { background: #fff; border: 1px solid #e8e5ee; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(26,2,98,.04); }
        .config-card-header { padding: 10px 14px; border-bottom: 1px solid #e8e5ee; font-weight: 700; font-size: .72rem; color: #374151; background: #faf9fc; display: flex; align-items: center; justify-content: space-between; }
        .stage-list { padding: 12px 14px; }
        .stage-row { display: flex; align-items: center; gap: 8px; padding: 5px 8px; border: 1px solid #e8e5ee; border-radius: 6px; margin-bottom: 3px; background: #fff; cursor: grab; transition: box-shadow .12s; font-size: .68rem; }
        .stage-row:hover { box-shadow: 0 2px 8px rgba(26,2,98,.06); border-color: #d4c4ec; }
        .stage-row.dragging { opacity: .4; }
        .drag-handle { color: #9ca3af; font-size: .8rem; cursor: grab; flex-shrink: 0; }
        .stage-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .stage-name { flex-grow: 1; font-weight: 600; color: #1f2937; font-size: .72rem; }
        .stage-badges { display: flex; gap: 2px; }
        .stage-badge { font-size: .55rem; border-radius: 3px; padding: 1px 5px; font-weight: 600; }
        .badge-won { background: #d1fae5; color: #065f46; }
        .badge-lost { background: #fee2e2; color: #991b1b; }
        .badge-inactive { background: #f3f4f6; color: #9ca3af; }
        .stage-actions { display: flex; gap: 2px; }
        .stage-actions button { font-size: .6rem; padding: 1px 5px; border-radius: 3px; border: 1px solid #e5e7eb; background: #fff; cursor: pointer; transition: all .12s; }
        .stage-actions button:hover { border-color: #d4c4ec; background: #ede5f8; }

        .cform-group { margin-bottom: var(--hd-md); }
        .cform-group label { font-size: var(--hd-font-xs); font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; display: block; margin-bottom: 2px; }
        .cform-group input[type="text"], .cform-group input[type="number"], .cform-group textarea, .cform-group select { width: 100%; padding: 4px 8px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: .68rem; background: #f9fafb; transition: border-color .12s, background .12s; }
        .cform-group input:focus, .cform-group textarea:focus { outline: none; border-color: #1a0262; background: #fff; box-shadow: 0 0 0 2px rgba(26,2,98,.08); }

        .toggle-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--hd-sm); }
        .toggle-row label { font-size: .68rem; color: #1f2937; margin: 0; }
        .toggle-switch { position: relative; width: 34px; height: 18px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider { position: absolute; inset: 0; background: #d1d5db; border-radius: 18px; cursor: pointer; transition: .15s; }
        .toggle-slider::before { content: ''; position: absolute; width: 13px; height: 13px; left: 3px; top: 2.5px; background: #fff; border-radius: 50%; transition: .15s; }
        .toggle-switch input:checked+.toggle-slider { background: #1a0262; }
        .toggle-switch input:checked+.toggle-slider::before { transform: translateX(16px); }
    </style>
@endpush

@section('content')
    <div style="padding:var(--hd-md)">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--hd-md);">
            <div>
                <h4 style="margin:0;font-size:var(--hd-font-lg);font-weight:700;">Configure Stages</h4>
                <div style="font-size:var(--hd-font-xs);color:#6b7280;">Drag to reorder · Admin only</div>
            </div>
            <a href="{{ route('crm.dashboard') }}" class="btn btn-sm btn-outline-purple">← Back</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="config-grid">

            {{-- ═══ LEFT: Stage list ═══ --}}
            <div class="config-card">
                <div class="config-card-header">
                    Pipeline Stages ({{ $stages->count() }})
                    <span class="text-muted" style="font-size:.75rem; font-weight:400">Drag to reorder</span>
                </div>
                <div class="stage-list" id="stageList">
                    @foreach ($stages as $stage)
                        <div class="stage-row" data-id="{{ $stage->id }}">
                            <span class="drag-handle">⠿</span>
                            <span class="stage-dot" style="background:{{ $stage->color }}"></span>
                            <span class="stage-name">{{ $stage->name }}</span>
                            <span style="font-size:.72rem; color:#6b7280">{{ $stage->students()->count() }}
                                students</span>
                            <div class="stage-badges">
                                @if ($stage->is_won_stage)
                                    <span class="stage-badge badge-won">Won</span>
                                @endif
                                @if ($stage->is_lost_stage)
                                    <span class="stage-badge badge-lost">Lost</span>
                                @endif
                                @if (!$stage->is_active)
                                    <span class="stage-badge badge-inactive">Inactive</span>
                                @endif
                            </div>
                            <div class="stage-actions">
                                <button
                                    onclick="loadEditForm({{ $stage->id }}, '{{ addslashes($stage->name) }}', '{{ $stage->color }}', '{{ addslashes($stage->description ?? '') }}', {{ $stage->is_won_stage ? 'true' : 'false' }}, {{ $stage->is_lost_stage ? 'true' : 'false' }}, {{ $stage->max_days_in_stage ?? 'null' }})">
                                    ✏️ Edit
                                </button>
                                <button onclick="toggleActive({{ $stage->id }}, this)">
                                    {{ $stage->is_active ? '🔒 Disable' : '✅ Enable' }}
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
                    @endforeach
                </div>
            </div>

            {{-- ═══ RIGHT: Create / Edit form ═══ --}}
            <div>
                <div class="config-card">
                    <div class="config-card-header">
                        <span id="formTitle">➕ Add New Stage</span>
                    </div>
                    <div style="padding:var(--hd-lg)">

                        {{-- Create form --}}
                        <form id="stageCreateForm" action="{{ route('crm.configure.store') }}" method="POST">
                            @csrf
                            @include('crm.partials._stage_form', [
                                'stage' => null,
                                'mode' => 'create',
                            ])
                            <button type="submit" class="btn btn-sm btn-solid-dark w-100 mt-2">Create Stage</button>
                        </form>

                        {{-- Edit form (hidden until Edit clicked) --}}
                        <form id="stageEditForm" style="display:none" method="POST">
                            @csrf @method('PUT')
                            @include('crm.partials._stage_form', [
                                'stage' => null,
                                'mode' => 'edit',
                            ])
                            <div class="d-flex gap-2 mt-2">
                                <button type="submit" class="btn btn-sm btn-solid-dark flex-grow-1">Update Stage</button>
                                <button type="button" class="btn btn-sm btn-outline-purple" onclick="showCreateForm()">Cancel</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    {{-- SortableJS for drag-to-reorder --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script>
        const COLORS = [
            '#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16',
            '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#3b82f6',
            '#6366f1', '#8b5cf6', '#a855f7', '#ec4899', '#64748b',
        ];

        // ── Drag to reorder ──────────────────────────────────────────────
        Sortable.create(document.getElementById('stageList'), {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function() {
                const ids = [...document.querySelectorAll('.stage-row')].map(el => el.dataset.id);
                fetch('{{ route('crm.configure.reorder') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        order: ids
                    })
                });
            }
        });

        // ── Load edit form ───────────────────────────────────────────────
        function loadEditForm(id, name, color, desc, isWon, isLost, maxDays) {
            document.getElementById('stageCreateForm').style.display = 'none';
            const form = document.getElementById('stageEditForm');
            form.style.display = 'block';
            form.action = `/crm/configure/${id}`;
            document.getElementById('formTitle').textContent = '✏️ Edit Stage';

            // Populate fields in edit form
            form.querySelector('[name="name"]').value = name;
            form.querySelector('[name="description"]').value = desc;
            form.querySelector('[name="max_days_in_stage"]').value = maxDays ?? '';
            form.querySelector('[name="is_won_stage"]').checked = isWon;
            form.querySelector('[name="is_lost_stage"]').checked = isLost;

            // Highlight selected color
            form.querySelectorAll('.color-swatch').forEach(s => {
                s.classList.toggle('selected', s.dataset.color === color);
            });
            form.querySelector('[name="color"]').value = color;
        }

        function showCreateForm() {
            document.getElementById('stageCreateForm').style.display = 'block';
            document.getElementById('stageEditForm').style.display = 'none';
            document.getElementById('formTitle').textContent = '➕ Add New Stage';
        }

        // ── Toggle active ────────────────────────────────────────────────
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
                    btn.textContent = data.is_active ? '🔒 Disable' : '✅ Enable';
                    window.location.reload();
                });
        }
    </script>
@endpush
