{{-- resources/views/admin/backups/index.blade.php --}}
@extends('layouts.admin')
@push('styles')
    <style>
        .backups-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin-bottom: 1.25rem;
        }

        .backups-header h4 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
            color: var(--primary);
        }

        .backups-header p {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin: 0.15rem 0 0 0;
        }

        .backups-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .backup-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #f0f0f0;
            overflow: hidden;
        }

        .backup-card .card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.65rem 1rem;
            border-bottom: 1px solid #f0f0f0;
            background: #fafafa;
        }

        .backup-card .card-top h6 {
            font-size: 0.78rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .backup-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 1rem;
            border-bottom: 1px solid #f5f5f5;
            gap: 0.8rem;
        }

        .backup-item:last-child {
            border-bottom: none;
        }

        .backup-item:hover {
            background: #fafafa;
        }

        .backup-info {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex: 1;
            min-width: 0;
        }

        .backup-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--primary-soft);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .backup-details {
            min-width: 0;
        }

        .backup-name {
            font-size: 0.78rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .backup-meta {
            font-size: 0.66rem;
            color: var(--text-muted);
            display: flex;
            gap: 0.6rem;
            margin-top: 0.1rem;
        }

        .backup-actions {
            display: flex;
            gap: 0.35rem;
            flex-shrink: 0;
        }

        .empty-backups {
            text-align: center;
            padding: 3rem 1.5rem;
            color: var(--text-muted);
        }

        .empty-backups i {
            font-size: 2.5rem;
            opacity: 0.3;
            margin-bottom: 0.8rem;
        }

        .empty-backups p {
            font-size: 0.82rem;
            margin-bottom: 1rem;
        }

        .creating-spinner {
            display: none;
        }

        .btn-creating .creating-spinner {
            display: inline-block;
        }

        .btn-creating .btn-text {
            display: none;
        }
    </style>
@endpush
@section('admin-content')
    <div class="container-fluid px-4 py-4" style="max-width:1100px;">
        {{-- HEADER --}}
        <div class="backups-header">
            <div>
                <h4><i class="fas fa-shield-alt me-2" style="color:var(--primary);"></i>Backup Manager</h4>
                <p>Create and manage database backups and project archives</p>
            </div>
            <div class="backups-actions">
                <form action="{{ route('admin.backup.create') }}" method="POST" class="d-inline" id="createBackupForm">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm" id="createBackupBtn">
                        <span class="btn-text"><i class="fas fa-plus-circle me-1"></i> Create New SQL Backup</span>
                        <span class="creating-spinner"><i class="fas fa-spinner fa-spin me-1"></i> Creating...</span>
                    </button>
                </form>
                <a href="{{ route('admin.backup.download-zip') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-file-archive me-1"></i> Download Project ZIP
                </a>
            </div>
        </div>

        {{-- ALERTS --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2" style="font-size:0.82rem;border-radius:8px;">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" style="font-size:0.7rem;"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show py-2" style="font-size:0.82rem;border-radius:8px;">
                <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" style="font-size:0.7rem;"></button>
            </div>
        @endif

        {{-- EXISTING BACKUPS LIST --}}
        <div class="backup-card">
            <div class="card-top">
                <h6><i class="fas fa-database"></i> Database Backups</h6>
                <span style="font-size:0.68rem;color:var(--text-muted);">{{ $backups->count() }} file(s)</span>
            </div>

            @forelse($backups as $b)
                <div class="backup-item">
                    <div class="backup-info">
                        <div class="backup-icon"><i class="fas fa-file-code"></i></div>
                        <div class="backup-details">
                            <div class="backup-name">{{ $b['filename'] }}</div>
                            <div class="backup-meta">
                                <span><i class="fas fa-weight me-1"></i>{{ $b['size'] }} MB</span>
                                <span><i class="fas fa-clock me-1"></i>{{ $b['date'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="backup-actions">
                        <a href="{{ route('admin.backup.download-file', base64_encode($b['filename'])) }}"
                            class="btn btn-sm btn-icon" title="Download">
                            <i class="fas fa-download" style="color:var(--primary);"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-icon btn-delete-confirm"
                            data-url="{{ route('admin.backup.delete', $b['filename']) }}" data-name="{{ $b['filename'] }}"
                            title="Delete">
                            <i class="fas fa-trash-alt" style="color:var(--danger);"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="empty-backups">
                    <i class="fas fa-database"></i>
                    <p>No backups yet. Click <strong>"Create New Backup"</strong> to generate your first database backup.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- INFO CARD --}}
        <div
            style="margin-top:1rem;padding:0.8rem 1rem;background:#fff;border-radius:10px;border:1px solid #f0f0f0;font-size:0.72rem;color:var(--text-muted);display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;">
            <i class="fas fa-info-circle" style="color:var(--info);font-size:0.85rem;"></i>
            <span>Backups are stored on the server and can be downloaded or deleted anytime.</span>
            <span style="margin-left:auto;font-size:0.68rem;">
                <i class="fas fa-folder me-1"></i>storage/app/backups/
            </span>
        </div>
    </div>

    <script>
        document.getElementById('createBackupForm')?.addEventListener('submit', function(e) {
            const btn = document.getElementById('createBackupBtn');
            btn.disabled = true;
            btn.classList.add('btn-creating');
        });
    </script>
@endsection
