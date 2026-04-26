{{-- resources/views/crm/configure.blade.php --}}
@extends('layouts.app')

@section('title', 'CRM — Configure Stages')

@push('styles')
    <style>
        :root {
            --crm-bg: #f4f6fb;
            --crm-card: #fff;
            --crm-border: #e5e9f2;
            --crm-text: #1a1f36;
            --crm-muted: #6b7280;
            --crm-primary: #4f46e5;
        }

        body {
            background: var(--crm-bg);
        }

        .config-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 1.5rem;
            padding: 1.5rem;
        }

        @media (max-width: 900px) {
            .config-grid {
                grid-template-columns: 1fr;
            }
        }

        .crm-card {
            background: var(--crm-card);
            border: 1px solid var(--crm-border);
            border-radius: 12px;
            overflow: hidden;
        }

        .crm-card-header {
            padding: .85rem 1.2rem;
            border-bottom: 1px solid var(--crm-border);
            font-weight: 600;
            font-size: .9rem;
            background: #fafbff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Stage drag list */
        .stage-list {
            padding: .75rem;
        }

        .stage-row {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .65rem .85rem;
            border: 1px solid var(--crm-border);
            border-radius: 8px;
            margin-bottom: .5rem;
            background: #fff;
            cursor: grab;
            transition: box-shadow .15s;
        }

        .stage-row:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
        }

        .stage-row.dragging {
            opacity: .5;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
        }

        .drag-handle {
            color: var(--crm-muted);
            font-size: .9rem;
            cursor: grab;
            flex-shrink: 0;
        }

        .stage-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .stage-name {
            flex-grow: 1;
            font-size: .875rem;
            font-weight: 500;
            color: var(--crm-text);
        }

        .stage-badges {
            display: flex;
            gap: .3rem;
        }

        .stage-badge {
            font-size: .65rem;
            border-radius: 20px;
            padding: .15rem .5rem;
            font-weight: 600;
        }

        .badge-won {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-lost {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-inactive {
            background: #f3f4f6;
            color: #9ca3af;
        }

        .stage-actions {
            display: flex;
            gap: .3rem;
        }

        .stage-actions button {
            font-size: .72rem;
            padding: .2rem .5rem;
            border-radius: 5px;
            border: 1px solid var(--crm-border);
            background: #fff;
            cursor: pointer;
        }

        .stage-actions button:hover {
            background: var(--crm-bg);
        }

        /* Form */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            font-size: .78rem;
            font-weight: 600;
            color: var(--crm-muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            display: block;
            margin-bottom: .35rem;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: .5rem .75rem;
            border: 1px solid var(--crm-border);
            border-radius: 8px;
            font-size: .875rem;
            background: var(--crm-bg);
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--crm-primary);
            background: #fff;
        }

        /* Color picker */
        .color-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: .4rem;
        }

        .color-swatch {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: transform .1s;
        }

        .color-swatch:hover,
        .color-swatch.selected {
            transform: scale(1.15);
            border-color: #fff;
            box-shadow: 0 0 0 3px var(--crm-primary);
        }

        /* Toggle switch */
        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: .65rem;
        }

        .toggle-row label {
            font-size: .85rem;
            color: var(--crm-text);
            margin: 0;
        }

        .toggle-switch {
            position: relative;
            width: 40px;
            height: 22px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            inset: 0;
            background: #d1d5db;
            border-radius: 22px;
            cursor: pointer;
            transition: .2s;
        }

        .toggle-slider::before {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            left: 3px;
            top: 3px;
            background: #fff;
            border-radius: 50%;
            transition: .2s;
        }

        .toggle-switch input:checked+.toggle-slider {
            background: var(--crm-primary);
        }

        .toggle-switch input:checked+.toggle-slider::before {
            transform: translateX(18px);
        }
    </style>
@endpush

@section('content')
    <div class="px-3 py-4">

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0 fw-bold" style="color:var(--crm-text)">⚙️ Configure CRM Stages</h4>
                <p class="text-muted small mb-0">Drag to reorder · Click Edit to modify · Admin only</p>
            </div>
            <a href="{{ route('crm.dashboard') }}" class="btn btn-sm btn-outline-secondary">← Back to CRM</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="config-grid">

            {{-- ═══ LEFT: Stage list ═══ --}}
            <div class="crm-card">
                <div class="crm-card-header">
                    Pipeline Stages ({{ $stages->count() }})
                    <span class="text-muted" style="font-size:.75rem; font-weight:400">Drag to reorder</span>
                </div>
                <div class="stage-list" id="stageList">
                    @foreach ($stages as $stage)
                        <div class="stage-row" data-id="{{ $stage->id }}">
                            <span class="drag-handle">⠿</span>
                            <span class="stage-dot" style="background:{{ $stage->color }}"></span>
                            <span class="stage-name">{{ $stage->name }}</span>
                            <span style="font-size:.72rem; color:var(--crm-muted)">{{ $stage->students()->count() }}
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
                                    <form action="{{ route('crm.configure.destroy', $stage) }}" method="POST"
                                        onsubmit="return confirm('Delete this stage? This cannot be undone.')"
                                        style="display:inline">
                                        @csrf @method('DELETE')
                                        <button type="submit">🗑️</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ═══ RIGHT: Create / Edit form ═══ --}}
            <div>
                <div class="crm-card">
                    <div class="crm-card-header">
                        <span id="formTitle">➕ Add New Stage</span>
                    </div>
                    <div style="padding: 1.2rem">

                        {{-- Create form --}}
                        <form id="stageCreateForm" action="{{ route('crm.configure.store') }}" method="POST">
                            @csrf
                            @include('crm.partials._stage_form', ['stage' => null])
                            <button type="submit" class="btn btn-primary w-100 mt-2">Create Stage</button>
                        </form>

                        {{-- Edit form (hidden until Edit clicked) --}}
                        <form id="stageEditForm" style="display:none" method="POST">
                            @csrf @method('PUT')
                            @include('crm.partials._stage_form', ['stage' => null])
                            <div class="d-flex gap-2 mt-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">Update Stage</button>
                                <button type="button" class="btn btn-outline-secondary"
                                    onclick="showCreateForm()">Cancel</button>
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

        // ── Color picker init ────────────────────────────────────────────
        document.querySelectorAll('.color-swatch').forEach(swatch => {
            swatch.addEventListener('click', function() {
                const form = this.closest('form');
                form.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
                this.classList.add('selected');
                form.querySelector('[name="color"]').value = this.dataset.color;
            });
        });
    </script>
@endpush
