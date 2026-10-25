@extends('layouts.app')

@section('title', 'Mailboxes')
@section('page-title', 'Mailboxes')

@section('content')
    @php $isAdmin = auth()->user()->is_admin; @endphp

    @if (session('success'))
        <div class="alert alert-success py-2" style="font-size:13px;">{!! session('success') !!}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger py-2" style="font-size:13px;">{!! session('error') !!}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <strong style="font-size:14px;">Mailboxes</strong>
                <div class="text-muted" style="font-size:11px;">
                    Mail lives in your mail client (Roundcube). The portal tracks unread counts and opens the right inbox.
                </div>
            </div>
            <div class="d-flex gap-2">
                @if ($isAdmin)
                    <form action="{{ route('mail.poll') }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size:12px;">
                            <i class="fas fa-sync me-1"></i> Refresh now
                        </button>
                    </form>
                    <button class="btn btn-sm btn-primary rounded-pill px-3" style="font-size:12px;"
                        data-bs-toggle="modal" data-bs-target="#mailboxModal" onclick="openMailboxModal()">
                        <i class="fas fa-plus me-1"></i> New Mailbox
                    </button>
                @endif
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" style="font-size:13px;">
                    <thead style="background:#f9fafb;">
                        <tr>
                            <th>Label</th>
                            <th>Email</th>
                            <th>Category</th>
                            <th>Unread</th>
                            <th>Total</th>
                            <th>Webmail</th>
                            <th>Last checked</th>
                            @if ($isAdmin)
                                <th>Access</th>
                                <th></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mailboxes as $mb)
                            <tr>
                                <td class="fw-semibold">{{ $mb->label }}</td>
                                <td>{{ $mb->email }}</td>
                                <td>
                                    @if ($mb->category)
                                        <span class="badge bg-light text-dark">{{ ucfirst($mb->category) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($mb->last_unread_count > 0)
                                        <span class="badge bg-danger">{{ $mb->last_unread_count }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $mb->last_total_count }}</td>
                                <td>
                                    @if ($mb->webmail_url)
                                        <a href="{{ $mb->webmail_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0" style="font-size:11px;">
                                            <i class="fas fa-external-link-alt me-1"></i> Open
                                        </a>
                                    @else
                                        <span class="text-muted" style="font-size:11px;">Not set</span>
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size:11px;">
                                    {{ $mb->last_checked_at ? $mb->last_checked_at->diffForHumans() : 'Never' }}
                                    @if ($mb->last_error)
                                        <br><span class="text-danger" title="{{ $mb->last_error }}">error</span>
                                    @endif
                                </td>
                                @if ($isAdmin)
                                    @php $mbUsers = array_map('intval', (array) ($accessMap[(string) $mb->id] ?? [])); @endphp
                                    <td style="font-size:11px;max-width:180px;">
                                        @if (count($mbUsers))
                                            <span style="color:#374151;">{{ collect($mbUsers)->map(fn($id) => $staffNames[$id] ?? null)->filter()->join(', ') }}</span>
                                        @else
                                            <span class="text-muted">Admins only</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0" style="font-size:11px;"
                                            onclick="editMailbox({{ $mb->id }})">Edit</button>
                                        <form action="{{ route('mail.destroy', $mb) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Remove this mailbox?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0" style="font-size:11px;">Del</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 9 : 7 }}" class="text-center text-muted py-4">No mailboxes configured yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($isAdmin)
        <div class="modal fade" id="mailboxModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="mailboxForm" method="POST" action="{{ route('mail.store') }}">
                        @csrf
                        <input type="hidden" name="_method" id="mailboxMethod" value="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="mailboxModalTitle">New Mailbox</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:12px;">Label *</label>
                                    <input type="text" name="label" id="mb_label" class="form-control form-control-sm" required placeholder="e.g. Documents">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:12px;">Email address *</label>
                                    <input type="email" name="email" id="mb_email" class="form-control form-control-sm" required placeholder="document@ideacs.com.np">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:12px;">Category</label>
                                    <select name="category" id="mb_category" class="form-select form-select-sm">
                                        <option value="">None</option>
                                        <option value="portal">Portal (copy of everything)</option>
                                        <option value="admin">Admin (main alerts)</option>
                                        <option value="document">Document</option>
                                        <option value="student">Student</option>
                                        <option value="application">Application</option>
                                        <option value="enquiry">Enquiry</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:12px;">Webmail URL</label>
                                    <input type="url" name="webmail_url" id="mb_webmail" class="form-control form-control-sm" placeholder="https://mail.ideacs.com.np">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold" style="font-size:12px;">IMAP host</label>
                                    <input type="text" name="imap_host" id="mb_host" class="form-control form-control-sm" placeholder="ideacs.com.np">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold" style="font-size:12px;">Port</label>
                                    <input type="number" name="imap_port" id="mb_port" class="form-control form-control-sm" value="993">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:12px;">IMAP username</label>
                                    <input type="text" name="imap_username" id="mb_username" class="form-control form-control-sm" placeholder="document@ideacs.com.np">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:12px;">IMAP password</label>
                                    <input type="password" name="imap_password" id="mb_password" class="form-control form-control-sm" placeholder="Leave blank to keep current">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:12px;">Encryption</label>
                                    <select name="imap_encryption" id="mb_encryption" class="form-select form-select-sm">
                                        <option value="ssl">SSL (993)</option>
                                        <option value="tls">TLS (143)</option>
                                        <option value="none">None</option>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="mb_active" value="1" checked>
                                        <label class="form-check-label" for="mb_active" style="font-size:12px;">Active (poll for unread)</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold" style="font-size:12px;">Staff access</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        @forelse ($staffUsers as $su)
                                            <div class="form-check">
                                                <input class="form-check-input mb-user" type="checkbox" name="users[]"
                                                    value="{{ $su->id }}" id="mb_user_{{ $su->id }}">
                                                <label class="form-check-label" for="mb_user_{{ $su->id }}" style="font-size:12px;">{{ $su->name }}</label>
                                            </div>
                                        @empty
                                            <div class="text-muted" style="font-size:12px;">No staff users yet.</div>
                                        @endforelse
                                    </div>
                                    <small class="text-muted" style="font-size:11px;">Admins always see all mailboxes. Tick the staff allowed to view this mailbox.</small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">Save Mailbox</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@push('scripts')
<script type="application/json" id="mailboxesData">@json($mailboxData)</script>
<script>
var MAILBOXES = JSON.parse(document.getElementById('mailboxesData').textContent);

function openMailboxModal() {
    document.getElementById('mailboxForm').action = "{{ route('mail.store') }}";
    document.getElementById('mailboxMethod').value = 'POST';
    document.getElementById('mailboxModalTitle').textContent = 'New Mailbox';
    ['label','email','category','webmail','host','username','password'].forEach(function (f) {
        document.getElementById('mb_' + f).value = '';
    });
    document.getElementById('mb_port').value = 993;
    document.getElementById('mb_encryption').value = 'ssl';
    document.getElementById('mb_active').checked = true;
    document.querySelectorAll('.mb-user').forEach(function (c) { c.checked = false; });
}

function editMailbox(id) {
    var mb = MAILBOXES.find(function (m) { return m.id === id; });
    if (!mb) return;
    document.getElementById('mailboxForm').action = "{{ url('mail') }}/" + mb.id;
    document.getElementById('mailboxMethod').value = 'PUT';
    document.getElementById('mailboxModalTitle').textContent = 'Edit Mailbox';
    document.getElementById('mb_label').value = mb.label || '';
    document.getElementById('mb_email').value = mb.email || '';
    document.getElementById('mb_category').value = mb.category || '';
    document.getElementById('mb_webmail').value = mb.webmail_url || '';
    document.getElementById('mb_host').value = mb.imap_host || '';
    document.getElementById('mb_port').value = mb.imap_port || 993;
    document.getElementById('mb_username').value = mb.imap_username || '';
    document.getElementById('mb_password').value = '';
    document.getElementById('mb_encryption').value = mb.imap_encryption || 'ssl';
    document.getElementById('mb_active').checked = !!mb.is_active;
    var assigned = (mb.user_ids || []).map(function (u) { return parseInt(u); });
    document.querySelectorAll('.mb-user').forEach(function (c) {
        c.checked = assigned.indexOf(parseInt(c.value)) !== -1;
    });
    new bootstrap.Modal(document.getElementById('mailboxModal')).show();
}
</script>
@endpush
@endsection
