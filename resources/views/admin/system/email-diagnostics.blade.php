@extends('layouts.admin')
@section('admin-content')
    <x-page-header title="Email Diagnostics" subtitle="Check the mail configuration, queue and mailboxes">
        <x-slot:actions>
            <a href="{{ route('admin.settings.index', ['tab' => 'email']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size:12px;">
                <i class="fas fa-cog me-1"></i> Email Settings
            </a>
            <a href="{{ route('mail.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size:12px;">
                <i class="fas fa-envelope me-1"></i> Mailboxes
            </a>
        </x-slot:actions>
    </x-page-header>

    @if (session('success'))
        <div class="alert alert-success py-2" style="font-size:13px;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger py-2" style="font-size:13px;white-space:pre-wrap;">{{ session('error') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header fw-semibold" style="font-size:13px;">Effective Mail Config (runtime)</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0" style="font-size:12.5px;">
                        <tbody>
                            <tr><td class="text-muted">.env mailer</td><td class="fw-semibold">{{ $mail['env_default'] ?: '(unset)' }}</td></tr>
                            <tr><td class="text-muted">.env mailer is real</td><td>{{ $mail['env_mailer_is_real'] ? 'YES (env is used)' : 'NO (DB SMTP is used)' }}</td></tr>
                            <tr><td class="text-muted">DB mail enabled</td><td>{{ $mail['db_enabled'] ? 'YES' : 'NO' }}</td></tr>
                            <tr><td class="text-muted">Effective default</td><td class="fw-semibold">{{ $mail['effective_default'] }}</td></tr>
                            <tr><td class="text-muted">SMTP host</td><td>{{ $mail['smtp_host'] ?: '(empty)' }}</td></tr>
                            <tr><td class="text-muted">SMTP port</td><td>{{ $mail['smtp_port'] ?: '(empty)' }}</td></tr>
                            <tr><td class="text-muted">SMTP scheme</td><td>{{ $mail['smtp_scheme'] ?: '(auto)' }}</td></tr>
                            <tr><td class="text-muted">SMTP username</td><td>{{ $mail['smtp_username'] ?: '(empty)' }}</td></tr>
                            <tr><td class="text-muted">SMTP password</td><td>{{ $mail['smtp_password'] }}</td></tr>
                            <tr><td class="text-muted">From address</td><td>{{ $mail['from_address'] }}</td></tr>
                            <tr><td class="text-muted">From name</td><td>{{ $mail['from_name'] }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header fw-semibold" style="font-size:13px;">DB Mail Settings</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0" style="font-size:12.5px;">
                        <tbody>
                            @forelse ($db as $k => $v)
                                <tr><td class="text-muted">{{ $k }}</td><td>{{ $v === '' || $v === null ? '(empty)' : $v }}</td></tr>
                            @empty
                                <tr><td class="text-muted text-center py-3">No mail settings in DB.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header fw-semibold" style="font-size:13px;">Send a Test Email</div>
                <div class="card-body">
                    <form action="{{ route('admin.system.test-email') }}" method="POST" class="d-flex gap-2">
                        @csrf
                        <input type="email" name="to" class="form-control form-control-sm" placeholder="you@example.com" required style="font-size:13px;">
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3" style="font-size:12px;">Send Test</button>
                    </form>
                    <small class="text-muted" style="font-size:11px;">Uses the effective mailer shown above. Exact error is displayed if it fails.</small>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header fw-semibold" style="font-size:13px;">Queue</div>
                <div class="card-body" style="font-size:13px;">
                    <div>Queued jobs: <strong>{{ $queued }}</strong> &nbsp; | &nbsp; Failed jobs: <strong class="text-danger">{{ $failedJobs }}</strong></div>
                    <form action="{{ route('admin.emails.bulk.process') }}" method="POST" class="mt-2">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size:12px;">
                            <i class="fas fa-play me-1"></i> Process Queue now
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header fw-semibold" style="font-size:13px;">Mailboxes (IMAP)</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0" style="font-size:12.5px;">
                            <thead style="background:#f9fafb;">
                                <tr>
                                    <th>Label</th><th>Email</th><th>IMAP host</th><th>Unread</th><th>Last checked</th><th>Error</th><th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($mailboxes as $mb)
                                    <tr>
                                        <td class="fw-semibold">{{ $mb->label }}</td>
                                        <td>{{ $mb->email }}</td>
                                        <td>{{ $mb->imap_host }}:{{ $mb->imap_port }} ({{ $mb->imap_encryption }})</td>
                                        <td>{{ $mb->last_unread_count }}</td>
                                        <td class="text-muted">{{ $mb->last_checked_at ? $mb->last_checked_at->diffForHumans() : 'Never' }}</td>
                                        <td class="text-danger" style="max-width:260px;">{{ $mb->last_error ?: '—' }}</td>
                                        <td>
                                            <form action="{{ route('mail.test', $mb) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0" style="font-size:11px;">Test</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-3">No mailboxes.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header fw-semibold" style="font-size:13px;">Recent Bulk Email Campaigns</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0" style="font-size:12.5px;">
                            <thead style="background:#f9fafb;">
                                <tr><th>Subject</th><th>Recipients</th><th>Sent</th><th>Failed</th><th>Status</th><th>Queued</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($campaigns as $c)
                                    <tr>
                                        <td>{{ $c->subject }}</td>
                                        <td>{{ $c->recipients_count }}</td>
                                        <td class="text-success">{{ $c->sent_count }}</td>
                                        <td class="{{ $c->failed_count ? 'text-danger' : 'text-muted' }}">{{ $c->failed_count }}</td>
                                        <td><span class="badge bg-secondary">{{ ucfirst($c->status) }}</span></td>
                                        <td class="text-muted">{{ $c->created_at?->format('d M Y, H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-3">No campaigns yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if ($recentFailed->count())
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header fw-semibold" style="font-size:13px;">Recent Failed Jobs</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0" style="font-size:12px;">
                                <tbody>
                                    @foreach ($recentFailed as $f)
                                        <tr>
                                            <td class="text-muted" style="white-space:nowrap;">#{{ $f->id }}</td>
                                            <td style="max-width:900px;white-space:pre-wrap;">{{ \Illuminate\Support\Str::limit($f->exception, 400) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
