@extends('layouts.admin')
@section('admin-content')
    <div class="container-fluid px-4 py-4">
        <x-page-header title="Recycle Bin" subtitle="Restore or permanently delete soft-deleted records">
            <x-slot:actions>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i
                        class="fas fa-arrow-left me-1"></i>Back to Dashboard</a>
            </x-slot:actions>
        </x-page-header>

        @if (!empty($groups['_debug']))
            <!-- DBG: {{ json_encode($groups['_debug']) }} -->
            <div class="alert alert-info small py-1 px-3 mb-2">
                <strong>Debug:</strong>
                type={{ $groups['_debug']['type'] ?? '?' }},
                ids=[{{ implode(',', $groups['_debug']['ids'] ?? []) }}],
                activities={{ $groups['_debug']['rows'] ?? 0 }},
                error={{ $groups['_debug']['error'] ?? 'none' }}
            </div>
        @endif

        @forelse($groups as $type => $group)
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center"
                        style="width:36px;height:36px;background:#fce4ec;">
                        <i class="fas {{ $group['icon'] }}" style="color:#c62828;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold">{{ $group['label'] }}</h6>
                        <small class="text-muted">{{ $group['count'] }} record{{ $group['count'] !== 1 ? 's' : '' }} in
                            trash</small>
                    </div>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <small class="text-muted" id="selectedCount-{{ $type }}">0 selected</small>
                        <button type="button" class="btn btn-sm btn-outline-success"
                            id="restoreSelectedBtn-{{ $type }}" disabled
                            onclick="bulkAction('{{ $type }}', 'restore')"><i class="fas fa-undo me-1"></i>Restore
                            Selected</button>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                            id="deleteSelectedBtn-{{ $type }}" disabled
                            onclick="bulkAction('{{ $type }}', 'delete')"><i
                                class="fas fa-trash-alt me-1"></i>Delete Selected</button>
                        <form method="POST" action="{{ route('admin.trash.restore-all', $type) }}" class="d-inline"
                            onsubmit="return confirm('Restore all {{ strtolower($group['label']) }}?')">
                            @csrf @method('PUT')
                            <button type="submit" class="btn btn-sm btn-outline-success"><i
                                    class="fas fa-undo me-1"></i>Restore All</button>
                        </form>
                        <x-confirm-delete url="{{ route('admin.trash.empty', $type) }}" label="Empty Trash"
                            title="Empty Trash?"
                            message="Permanently delete all {{ $group['count'] }} {{ strtolower($group['label']) }}? This cannot be undone!"
                            class="btn btn-sm btn-outline-danger" />
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th width="40"><input type="checkbox" class="select-all-{{ $type }}"
                                            onchange="toggleAll(this, '{{ $type }}')"></th>
                                    <th width="60">ID</th>
                                    <th>Title / Name</th>
                                    @if (!empty($group['has_student']))
                                        <th width="160">Student</th>
                                    @endif
                                    <th width="140">Deleted By</th>
                                    <th width="180">Deleted At</th>
                                    <th width="140" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($group['records'] as $record)
                                    <tr>
                                        <td><input type="checkbox" value="{{ $record->id }}"
                                                class="cb-{{ $type }}"
                                                onchange="updateSelected('{{ $type }}')"></td>
                                        <td class="small text-muted">{{ $record->id }}</td>
                                        <td>
                                            <span class="fw-semibold small">
                                                @if ($group['title'] === 'id')
                                                    Application #{{ $record->id }}
                                                @elseif($group['title'] === 'message')
                                                    {{ Str::limit(strip_tags($record->message ?? ''), 60) }}
                                                @elseif($group['title'] === 'description')
                                                    {{ Str::limit(strip_tags($record->description ?? ''), 60) }}
                                                @elseif($group['title'] === 'content')
                                                    {{ Str::words(strip_tags($record->content ?? ''), 60) }}
                                                @elseif($group['title'] === 'status')
                                                    {{ $record->status ?? '—' }}
                                                @elseif($group['title'] === 'subject')
                                                    {{ Str::limit($record->subject ?? '', 60) }}
                                                @else
                                                    {{ $record->{$group['title']} ?? '—' }}
                                                @endif
                                            </span>
                                            @if ($group['title'] === 'full_name' && $record->email)
                                                <small class="d-block text-muted">{{ $record->email }}</small>
                                            @endif
                                        </td>
                                        @if (!empty($group['has_student']))
                                            <td class="small">
                                                {{ $record->student?->full_name ?? '—' }}
                                                @if ($record->student?->email)
                                                    <br><small class="text-muted">{{ $record->student->email }}</small>
                                                @endif
                                            </td>
                                        @endif
                                        <td class="small">
                                            <span class="fw-semibold">{{ $record->deleted_by_name }}</span>
                                        </td>
                                        <td class="small text-muted">
                                            {{ $record->deleted_at ? $record->deleted_at->format('M d, Y h:i A') : '—' }}
                                        </td>
                                        <td class="text-end">
                                            <form method="POST"
                                                action="{{ route('admin.trash.restore', [$type, $record->id]) }}"
                                                class="d-inline" onsubmit="return confirm('Restore this record?')">
                                                @csrf @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-success py-0 px-2"
                                                    title="Restore"><i class="fas fa-undo"></i></button>
                                            </form>
                                            <x-confirm-delete
                                                url="{{ route('admin.trash.force-delete', [$type, $record->id]) }}"
                                                label="" title="Delete permanently?"
                                                message="Permanently delete this record? This cannot be undone!"
                                                class="btn btn-sm btn-danger py-0 px-2" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <div class="mb-3"><i class="fas fa-trash-alt fa-4x text-muted opacity-25"></i></div>
                <h5 class="text-muted">Trash is empty</h5>
                <p class="text-muted small">No soft-deleted records found.</p>
            </div>
        @endforelse
    </div>

    @push('scripts')
        <script>
            function toggleAll(source, type) {
                document.querySelectorAll('.cb-' + type).forEach(cb => cb.checked = source.checked);
                updateSelected(type);
            }

            function updateSelected(type) {
                const checked = document.querySelectorAll('.cb-' + type + ':checked').length;
                document.getElementById('selectedCount-' + type).textContent = checked + ' selected';
                document.getElementById('restoreSelectedBtn-' + type).disabled = checked === 0;
                document.getElementById('deleteSelectedBtn-' + type).disabled = checked === 0;
            }

            function bulkAction(type, action) {
                const checked = document.querySelectorAll('.cb-' + type + ':checked');
                if (!checked.length) return;
                const count = checked.length;
                const ids = Array.from(checked).map(cb => cb.value);
                const label = action === 'restore' ? 'restore' : 'permanently delete';
                Swal.fire({
                    title: (action === 'restore' ? 'Restore' : 'Delete') + ' ' + count + ' items?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: action === 'restore' ? '#198754' : '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, ' + label + ' them!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    const route = action === 'restore' ? '{{ route('admin.trash.bulk-restore') }}' :
                        '{{ route('admin.trash.bulk-force-delete') }}';
                    fetch(route, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            ids,
                            model_type: type
                        })
                    }).then(r => r.json()).then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message || 'Something went wrong.'
                            });
                        }
                    }).catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Something went wrong.'
                        });
                    });
                });
            }
        </script>
    @endpush
@endsection
