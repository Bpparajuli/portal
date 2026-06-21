@extends('layouts.admin')
@php $currentFolder = $folder ?? 'inbox'; @endphp
@push('styles')
    <style>
        .mbx {
            display: flex;
            height: calc(100vh - 60px);
            margin: 0.25rem;
            background: #fff;
        }

        .mbx-sidebar {
            width: 210px;
            flex-shrink: 0;
            border-right: 1px solid #e9edf2;
            display: flex;
            flex-direction: column;
            background: #f8f9fb;
        }

        .mbx-sidebar-inner {
            padding: 16px 12px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .mbx-list {
            width: 400px;
            flex-shrink: 0;
            border-right: 1px solid #e9edf2;
            display: flex;
            flex-direction: column;
        }

        .mbx-detail {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            background: #fff;
        }

        .mbx-search {
            position: relative;
        }

        .mbx-search input {
            background: #eef0f4;
            border: none;
            border-radius: 6px;
            padding: 7px 12px 7px 32px;
            font-size: 12px;
            width: 100%;
            outline: none;
        }

        .mbx-search input:focus {
            background: #e4e7ed;
        }

        .mbx-search i {
            position: absolute;
            left: 10px;
            top: 9px;
            font-size: 12px;
            color: #8a94a6;
        }

        .mbx-nav-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            color: #3c4257;
            text-decoration: none;
            margin-bottom: 1px;
            transition: all .1s;
        }

        .mbx-nav-item:hover {
            background: #eef0f4;
            color: #1a0262;
        }

        .mbx-nav-item.active {
            background: #1a0262;
            color: #fff;
            font-weight: 500;
        }

        .mbx-nav-item .count {
            margin-left: auto;
            font-size: 11px;
            background: #e9edf2;
            padding: 0 7px;
            border-radius: 10px;
            line-height: 18px;
            min-width: 20px;
            text-align: center;
        }

        .mbx-nav-item.active .count {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        .mbx-compose {
            display: block;
            width: 100%;
            padding: 9px;
            border-radius: 8px;
            border: none;
            background: linear-gradient(135deg, #1a0262, #2d1b69);
            color: #fff;
            font-weight: 600;
            font-size: 13px;
            text-align: center;
            cursor: pointer;
            margin-bottom: 16px;
            text-decoration: none;
        }

        .mbx-compose:hover {
            opacity: .92;
            color: #fff;
        }

        .mbx-section-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #8a94a6;
            font-weight: 600;
            padding: 10px 10px 4px;
        }

        .mbx-list-header {
            padding: 10px 14px;
            border-bottom: 1px solid #e9edf2;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .mbx-list-toolbar {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .mbx-list-toolbar button {
            border: none;
            background: none;
            padding: 5px 8px;
            border-radius: 4px;
            color: #5a6276;
            font-size: 12px;
            cursor: pointer;
        }

        .mbx-list-toolbar button:hover {
            background: #eef0f4;
        }

        .mbx-folder-label {
            font-size: 11px;
            color: #8a94a6;
            padding: 0 14px 8px;
            flex-shrink: 0;
        }

        .mbx-emails {
            flex-grow: 1;
            overflow-y: auto;
        }

        .mbx-email {
            display: flex;
            gap: 8px;
            padding: 10px 14px;
            border-bottom: 1px solid #f1f3f7;
            cursor: pointer;
            text-decoration: none;
            transition: all .1s;
            border-left: 3px solid transparent;
        }

        .mbx-email:hover {
            background: #f8f9fb;
        }

        .mbx-email.selected {
            background: #eef3ff;
            border-left-color: #1a0262;
        }

        .mbx-email.unread {
            background: #fafbff;
        }

        .mbx-email.unread .mbx-from {
            font-weight: 600;
            color: #1a0262;
        }

        .mbx-email .mbx-chk {
            padding-top: 2px;
        }

        .mbx-email .mbx-chk input {
            margin: 0;
            accent-color: #1a0262;
        }

        .mbx-email-body {
            flex-grow: 1;
            min-width: 0;
        }

        .mbx-email-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mbx-from {
            font-size: 13px;
            color: #3c4257;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 160px;
        }

        .mbx-date {
            font-size: 11px;
            color: #8a94a6;
            white-space: nowrap;
        }

        .mbx-subject {
            font-size: 12px;
            color: #1a0262;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 1px;
        }

        .mbx-snippet {
            font-size: 11px;
            color: #8a94a6;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 1px;
        }

        .mbx-attach-icon {
            font-size: 11px;
            color: #8a94a6;
            flex-shrink: 0;
            padding-top: 2px;
        }

        .mbx-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #8a94a6;
            font-size: 13px;
            gap: 8px;
        }

        .mbx-empty i {
            font-size: 32px;
            opacity: .3;
        }

        .mbx-detail-toolbar {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 8px 20px;
            border-bottom: 1px solid #e9edf2;
            flex-shrink: 0;
        }

        .mbx-detail-toolbar a,
        .mbx-detail-toolbar button {
            border: none;
            background: none;
            padding: 5px 12px;
            border-radius: 5px;
            color: #5a6276;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .mbx-detail-toolbar a:hover,
        .mbx-detail-toolbar button:hover {
            background: #eef0f4;
            color: #1a0262;
        }

        .mbx-detail-toolbar .sep {
            width: 1px;
            height: 20px;
            background: #e9edf2;
            margin: 0 4px;
        }

        .mbx-detail-header {
            padding: 20px 20px 14px;
            border-bottom: 1px solid #f1f3f7;
            flex-shrink: 0;
        }

        .mbx-detail-subject {
            font-size: 16px;
            font-weight: 600;
            color: #1a0262;
            margin-bottom: 10px;
        }

        .mbx-detail-meta {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            font-size: 12px;
            color: #5a6276;
        }

        .mbx-detail-meta .label {
            color: #8a94a6;
        }

        .mbx-detail-meta .val {
            color: #3c4257;
        }

        .mbx-detail-body {
            flex-grow: 1;
            overflow-y: auto;
            padding: 20px;
            font-size: 14px;
            line-height: 1.7;
            color: #1e293b;
        }

        .mbx-detail-attachments {
            flex-shrink: 0;
            padding: 12px 20px;
            border-top: 1px solid #f1f3f7;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .mbx-detail-attachments a {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border: 1px solid #e9edf2;
            border-radius: 6px;
            font-size: 12px;
            color: #3c4257;
            text-decoration: none;
        }

        .mbx-detail-attachments a:hover {
            background: #f8f9fb;
        }

        .mbx-detail-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #c0c6d2;
            font-size: 14px;
            gap: 10px;
        }

        .mbx-detail-empty i {
            font-size: 48px;
            opacity: .2;
        }

        .mbx-star {
            background: none;
            border: none;
            padding: 0;
            color: #d1d5db;
            cursor: pointer;
            font-size: 13px;
            line-height: 1;
        }

        .mbx-star.active {
            color: #f59e0b;
        }

        .mbx-badge-imap {
            font-size: 9px;
            background: #e8f4fd;
            color: #0369a1;
            padding: 0 5px;
            border-radius: 3px;
            font-weight: 500;
        }
    </style>
@endpush

@section('admin-content')
    @php $myEmail = Auth::user()->email; @endphp

    <div class="d-md-none px-3 py-2 border-bottom d-flex align-items-center gap-2" style="background:#fff;">
        <button class="btn btn-sm px-1 border-0" data-bs-toggle="collapse" data-bs-target="#mbxMobileNav"><i
                class="fas fa-bars"></i></button>
        <span class="fw-semibold" style="font-size:13px;">{{ ucfirst($currentFolder) }}</span>
        <span class="text-muted" style="font-size:11px;">{{ $emails->total() }} msgs</span>
        @if ($currentFolder === 'inbox')
            <form action="{{ route('admin.emails.sync-now') }}" method="POST" class="d-inline ms-auto" id="syncFormMobile">
                @csrf
                <button type="submit" class="btn btn-sm border-0" id="syncBtnMobile" style="font-size:13px;"><i
                        class="fas fa-sync"></i></button>
            </form>
        @endif
        <a href="{{ route('admin.emails.create') }}" class="btn btn-sm btn-primary rounded-pill px-3"
            style="font-size:11px;">Compose</a>
    </div>
    <div class="collapse d-md-none" id="mbxMobileNav">
        <div class="px-2 py-2 border-bottom bg-light d-flex flex-wrap gap-1">
            <a href="{{ route('admin.emails.inbox') }}"
                class="btn btn-sm {{ $currentFolder === 'inbox' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3"
                style="font-size:11px;">Inbox @if (isset($inboxUnread) && $inboxUnread > 0)
                    <span class="badge bg-white text-primary ms-1">{{ $inboxUnread }}</span>
                @endif
            </a>
            <a href="{{ route('admin.emails.sent') }}"
                class="btn btn-sm {{ $currentFolder === 'sent' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3"
                style="font-size:11px;">Sent</a>
            <a href="{{ route('admin.emails.drafts') }}"
                class="btn btn-sm {{ $currentFolder === 'drafts' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3"
                style="font-size:11px;">Drafts</a>
        </div>
    </div>

    <div class="mbx">
        {{-- LEFT COLUMN: Sidebar --}}
        <div class="mbx-sidebar d-none d-md-flex">
            <div class="mbx-sidebar-inner">
                <a href="{{ route('admin.emails.create') }}" class="mbx-compose"><i class="fas fa-pen me-1"></i>
                    Compose</a>

                <a href="{{ route('admin.emails.inbox') }}"
                    class="mbx-nav-item {{ $currentFolder === 'inbox' ? 'active' : '' }}">
                    <i class="fas fa-inbox" style="width:16px;"></i> Inbox
                    @if (isset($inboxUnread) && $inboxUnread > 0)
                        <span class="count">{{ $inboxUnread }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.emails.sent') }}"
                    class="mbx-nav-item {{ $currentFolder === 'sent' ? 'active' : '' }}">
                    <i class="fas fa-paper-plane" style="width:16px;"></i> Sent
                    @if (isset($sentCount) && $sentCount > 0)
                        <span class="count">{{ $sentCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.emails.drafts') }}"
                    class="mbx-nav-item {{ $currentFolder === 'drafts' ? 'active' : '' }}">
                    <i class="fas fa-file" style="width:16px;"></i> Drafts
                    @if (isset($draftsCount) && $draftsCount > 0)
                        <span class="count">{{ $draftsCount }}</span>
                    @endif
                </a>
                <a href="#" class="mbx-nav-item disabled" style="opacity:.4;pointer-events:none;">
                    <i class="fas fa-exclamation-triangle" style="width:16px;"></i> Junk
                    <span class="count">0</span>
                </a>
                <a href="#" class="mbx-nav-item disabled" style="opacity:.4;pointer-events:none;">
                    <i class="fas fa-trash" style="width:16px;"></i> Trash
                    <span class="count">0</span>
                </a>
            </div>
        </div>

        {{-- MIDDLE COLUMN: Email List --}}
        <div class="mbx-list">
            <div class="mbx-list-header">
                <div class="mbx-search flex-grow-1">
                    <i class="fas fa-search"></i>
                    <input type="text" id="mbxSearch" placeholder="Search sender, subject...">
                </div>
                <div class="mbx-list-toolbar">
                    @if ($currentFolder === 'inbox')
                        <form action="{{ route('admin.emails.sync-now') }}" method="POST" class="d-inline" id="syncForm">
                            @csrf
                            <button type="submit" id="syncBtn" title="Sync IMAP"><i class="fas fa-sync"></i></button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="mbx-folder-label">{{ ucfirst($currentFolder) }} — {{ $emails->total() }} messages</div>

            @if (session('success'))
                <div class="mx-3 mt-2 mb-1 alert alert-success py-2" style="font-size:12px;border:none;border-radius:6px;">
                    {!! session('success') !!}
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" style="font-size:10px;"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="mx-3 mt-2 mb-1 alert alert-danger py-2" style="font-size:12px;border:none;border-radius:6px;">
                    {{ session('error') }}
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" style="font-size:10px;"></button>
                </div>
            @endif

            <div class="mbx-emails" id="mbxEmailList">
                @forelse($emails as $email)
                    @php
                        $isUnread = $currentFolder === 'inbox' && (!$email->status || $email->status === 'delivered');
                        $displayName =
                            $currentFolder === 'sent'
                                ? $email->recipient_name ?? $email->recipient_email
                                : $email->sender_name ?? $email->sender_email;
                        $hasAttachments = !empty($email->attachments);
                        $isSelected = $selectedEmail && $selectedEmail->id === $email->id;
                        $link =
                            $currentFolder === 'drafts' ? route('admin.emails.edit', $email) : '?view=' . $email->id;
                    @endphp
                    <a href="{{ $link }}"
                        class="mbx-email {{ $isUnread ? 'unread' : '' }} {{ $isSelected ? 'selected' : '' }}">
                        <div class="mbx-chk"><input type="checkbox"
                                onclick="event.stopPropagation();event.preventDefault();"></div>
                        <div class="mbx-email-body">
                            <div class="mbx-email-top">
                                <span class="mbx-from">
                                    {{ $displayName }}
                                    @if ($email->is_external)
                                        <span class="mbx-badge-imap ms-1">IMAP</span>
                                    @endif
                                </span>
                                <span
                                    class="mbx-date">{{ $email->sent_at ? $email->sent_at->format('M j') : $email->created_at->format('M j') }}</span>
                            </div>
                            <div class="mbx-subject">{{ $email->subject ?: '(No Subject)' }}</div>
                            <div class="mbx-snippet">{{ strip_tags($email->body ?? '') }}</div>
                        </div>
                        @if ($hasAttachments)
                            <div class="mbx-attach-icon"><i class="fas fa-paperclip"></i></div>
                        @endif
                    </a>
                @empty
                    <div class="mbx-empty">
                        <i class="fas fa-inbox"></i>
                        <span>No {{ $currentFolder }} messages</span>
                    </div>
                @endforelse
            </div>

            @if ($emails->hasPages())
                <div class="px-3 py-2 border-top d-flex justify-content-between align-items-center"
                    style="font-size:11px;color:#8a94a6;flex-shrink:0;">
                    <span>Page {{ $emails->currentPage() }}/{{ $emails->lastPage() }}</span>
                    <div class="d-flex gap-1">{{ $emails->links('pagination::bootstrap-5') }}</div>
                </div>
            @endif
        </div>

        {{-- RIGHT COLUMN: Email Detail --}}
        <div class="mbx-detail">
            @if ($selectedEmail)
                @php $email = $selectedEmail; @endphp
                @php $atts = is_array($email->attachments) ? $email->attachments : (json_decode($email->attachments ?? '[]', true) ?? []); @endphp
                <div class="mbx-detail-toolbar">
                    <a href="{{ route('admin.emails.reply', $email) }}" title="Reply"><i class="fas fa-reply"></i>
                        Reply</a>
                    <span class="sep"></span>
                    <x-confirm-delete action="admin.emails.destroy" :id="$email->id" label="Delete"
                        title="Delete Email?" message="Permanently delete this email?" class="mb-0"
                        style="border:none;background:none;padding:5px 12px;border-radius:5px;color:#5a6276;font-size:12px;display:inline-flex;align-items:center;gap:5px;" />
                    <span class="sep"></span>
                    <button type="button"
                        onclick="location.href='{{ route($currentFolder === 'inbox' ? 'admin.emails.inbox' : 'admin.emails.' . $currentFolder) }}'"
                        title="Back to list"><i class="fas fa-list"></i> List</button>
                </div>
                <div class="mbx-detail-header">
                    <div class="mbx-detail-subject">{{ $email->subject ?: '(No Subject)' }}</div>
                    <div class="mbx-detail-meta">
                        <span><span class="label">From:</span> <span
                                class="val">{{ $email->sender_name ?? $email->sender_email }}
                                &lt;{{ $email->sender_email }}&gt;</span></span>
                        @if ($email->is_external)
                            <span class="mbx-badge-imap">IMAP</span>
                        @endif
                    </div>
                    <div class="mbx-detail-meta" style="margin-top:3px;">
                        <span><span class="label">To:</span> <span
                                class="val">{{ $email->recipient_name ?? $email->recipient_email }}</span></span>
                        @if ($email->cc)
                            <span><span class="label">CC:</span> <span class="val">{{ $email->cc }}</span></span>
                        @endif
                    </div>
                    <div class="mbx-detail-meta" style="margin-top:3px;">
                        <span
                            class="val">{{ $email->sent_at ? $email->sent_at->format('D, M j, Y g:i A') : $email->created_at->format('D, M j, Y g:i A') }}</span>
                    </div>
                </div>
            <div class="mbx-detail-body">
                @if($email->body_html)
                    @php
                        $safeHtml = preg_replace(
                            ['@<script[^>]*>.*?</script>@si', '@<iframe[^>]*>.*?</iframe>@si', '@<object[^>]*>.*?</object>@si', '@<embed[^>]*>.*?</embed>@si', '@<style[^>]*>.*?</style>@si'],
                            '',
                            $email->body_html
                        );
                    @endphp
                    {!! $safeHtml !!}
                @else
                    {!! nl2br(e($email->body)) !!}
                @endif
            </div>
                @if (!empty($atts))
                    <div class="mbx-detail-attachments">
                        @foreach ($atts as $i => $att)
                            <a href="{{ route('admin.emails.download-attachment', [$email->id, $i]) }}">
                                <i class="fas fa-paperclip"></i> {{ $att['name'] ?? 'File' }}
                                <span
                                    style="font-size:10px;color:#8a94a6;">{{ isset($att['size']) ? round($att['size'] / 1024) . 'KB' : '' }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            @else
                <div class="mbx-detail-empty">
                    <i class="far fa-envelope-open"></i>
                    <span>Select a message to read</span>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        ['syncBtn', 'syncBtnMobile'].forEach(function(id) {
            document.getElementById(id)?.addEventListener('click', function() {
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                (id === 'syncBtn' ? document.getElementById('syncForm') : document.getElementById(
                    'syncFormMobile'))?.submit();
            });
        });
        document.getElementById('mbxSearch')?.addEventListener('keyup', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.mbx-email').forEach(function(e) {
                e.style.display = e.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    </script>
@endpush
