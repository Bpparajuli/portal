@extends('layouts.admin')
@section('admin-content')
    <x-page-header title="Bulk Email Campaigns">
        <x-slot:actions>
            <form action="{{ route('admin.emails.bulk.process') }}" method="POST" class="d-inline"
                onsubmit="return confirm('Process any queued bulk emails now?');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size:12px;">
                    <i class="fas fa-play me-1"></i> Process Queue
                </button>
            </form>
            <a href="{{ route('admin.emails.bulk') }}" class="btn btn-sm btn-primary rounded-pill px-3" style="font-size:12px;">
                <i class="fas fa-plus me-1"></i> New Campaign
            </a>
            <a href="{{ route('mail.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size:12px;">
                <i class="fas fa-envelope me-1"></i> Mailboxes
            </a>
        </x-slot:actions>
    </x-page-header>

    @if (session('success'))
        <div class="alert alert-success py-2" style="font-size:13px;">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" style="font-size:13px;">
                    <thead style="background:#f9fafb;">
                        <tr>
                            <th>Subject</th>
                            <th>Group</th>
                            <th>Recipients</th>
                            <th>Sent</th>
                            <th>Failed</th>
                            <th>Status</th>
                            <th>Queued</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($campaigns as $c)
                            <tr>
                                <td class="fw-semibold">{{ $c->subject }}</td>
                                <td><span class="badge bg-light text-dark">{{ ucfirst($c->recipient_group) }}</span></td>
                                <td>{{ $c->recipients_count }}</td>
                                <td class="text-success">{{ $c->sent_count }}</td>
                                <td class="{{ $c->failed_count ? 'text-danger' : 'text-muted' }}">{{ $c->failed_count }}</td>
                                <td>
                                    @php
                                        $badge = match ($c->status) {
                                            'completed' => 'success',
                                            'sending' => 'info',
                                            'failed' => 'danger',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ ucfirst($c->status) }}</span>
                                </td>
                                <td class="text-muted">{{ $c->created_at?->format('d M Y, g:i A') }}</td>
                                <td class="text-nowrap">
                                    <form action="{{ route('admin.emails.bulk.resend', $c) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Resend this campaign to its recipients?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0"
                                            style="font-size:11px;" title="Resend">
                                            <i class="fas fa-redo me-1"></i>Resend
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.emails.bulk.destroy', $c) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Remove this campaign?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0"
                                            style="font-size:11px;" title="Remove">
                                            <i class="fas fa-trash-alt me-1"></i>Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No bulk email campaigns yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $campaigns->links() }}</div>

    @if ($campaigns->whereIn('status', ['queued', 'sending'])->count())
        <script>setTimeout(function () { window.location.reload(); }, 8000);</script>
    @endif
@endsection
