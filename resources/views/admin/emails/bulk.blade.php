@extends('layouts.admin')

@push('styles')
    <style>
        .nav-pills .nav-link {
            color: #820b5c;
            background-color: transparent;
        }

        .nav-pills .nav-link.active {
            color: #ffffff;
            background-color: #820b5c;
        }

        .nav-pills .nav-link:not(.active):hover {
            color: #1a0262;
        }

        .rp-item label,
        .rp-item {
            cursor: pointer;
        }

        .cw-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            padding: 2px 8px;
            max-width: 100%;
        }

        .cw-chip .cw-chip-txt {
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .cw-chip button {
            border: none;
            background: none;
            padding: 0;
            line-height: 1;
            color: #ef4444;
            font-weight: 700;
            cursor: pointer;
        }
    </style>
@endpush

@section('admin-content')
    <x-page-header title="Send Bulk Email">
        <x-slot:actions>
            <a href="{{ route('admin.emails.bulk.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                style="font-size:12px;">
                <i class="fas fa-list me-1"></i> Campaigns
            </a>
            <a href="{{ route('mail.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                style="font-size:12px;">
                <i class="fas fa-envelope me-1"></i> Mailboxes
            </a>
        </x-slot:actions>
    </x-page-header>

    @if (session('success'))
        <div class="alert alert-success py-2" style="font-size:13px;">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.emails.bulk.send') }}" method="POST" enctype="multipart/form-data" id="bulkForm">
        @csrf

        <div class="row g-3">
            {{-- Recipients --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong style="font-size:14px;"><i class="fas fa-users me-1"></i> Select Recipients</strong>
                        <span class="badge bg-primary" id="selectedCount">0 selected</span>
                    </div>
                    <div class="card-body p-2">
                        @error('emails')
                            <div class="alert alert-danger py-1 px-2 mb-2" style="font-size:12px;">{{ $message }}</div>
                        @enderror

                        <div class="mb-2 d-flex gap-1">
                            <input type="text" id="recipientSearch" class="form-control form-control-sm"
                                placeholder="Search all recipients (name or email)…" style="font-size:12px;"
                                oninput="window.filterRecipients && window.filterRecipients(this.value)"
                                onkeydown="if(event.key==='Enter'){event.preventDefault();}">
                        </div>

                        <ul class="nav nav-pills nav-sm mb-2" style="font-size:12px;">
                            <li class="nav-item"><button type="button" class="nav-link active" data-tab="students">Students
                                    ({{ $students->count() }})</button></li>
                            <li class="nav-item"><button type="button" class="nav-link" data-tab="agents">Agents
                                    ({{ $agents->count() }})</button></li>
                        </ul>

                        {{-- Students --}}
                        <div class="recipient-panel" id="panel-students">
                            <div class="d-flex justify-content-end mb-2">
                                <label
                                    class="btn btn-sm btn-outline-secondary rounded-pill px-2 mb-0 d-flex align-items-center gap-1 text-nowrap"
                                    style="font-size:11px;">
                                    <input type="checkbox" class="rp-all"> All
                                </label>
                            </div>
                            <div class="rp-list"
                                style="max-height:340px;overflow-y:auto;border:1px solid #eee;border-radius:8px;">
                                @forelse ($students as $s)
                                    <label class="d-flex align-items-center gap-2 px-2 py-1 border-bottom rp-item"
                                        data-search="{{ strtolower($s->first_name . ' ' . $s->last_name . ' ' . $s->email) }}">
                                        <input type="checkbox" name="emails[]" value="{{ $s->email }}" class="rp-check"
                                            data-name="{{ trim($s->first_name . ' ' . $s->last_name) ?: $s->email }}">
                                        <span style="min-width:0;">
                                            <span class="d-block text-truncate"
                                                style="font-size:12.5px;">{{ trim($s->first_name . ' ' . $s->last_name) ?: $s->email }}</span>
                                            <span class="d-block text-muted text-truncate"
                                                style="font-size:11px;">{{ $s->email }}</span>
                                        </span>
                                    </label>
                                @empty
                                    <div class="text-muted text-center py-3" style="font-size:12px;">No students with email.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Agents --}}
                        <div class="recipient-panel" id="panel-agents" style="display:none;">
                            <div class="d-flex justify-content-end mb-2">
                                <label
                                    class="btn btn-sm btn-outline-secondary rounded-pill px-2 mb-0 d-flex align-items-center gap-1 text-nowrap"
                                    style="font-size:11px;">
                                    <input type="checkbox" class="rp-all"> All
                                </label>
                            </div>
                            <div class="rp-list"
                                style="max-height:340px;overflow-y:auto;border:1px solid #eee;border-radius:8px;">
                                @forelse ($agents as $a)
                                    <label class="d-flex align-items-center gap-2 px-2 py-1 border-bottom rp-item"
                                        data-search="{{ strtolower(($a->business_name ?: $a->name) . ' ' . $a->email) }}">
                                        <input type="checkbox" name="emails[]" value="{{ $a->email }}" class="rp-check"
                                            data-name="{{ $a->business_name ?: $a->name }}">
                                        <span style="min-width:0;">
                                            <span class="d-block text-truncate"
                                                style="font-size:12.5px;">{{ $a->business_name ?: $a->name }}</span>
                                            <span class="d-block text-muted text-truncate"
                                                style="font-size:11px;">{{ $a->email }}</span>
                                        </span>
                                    </label>
                                @empty
                                    <div class="text-muted text-center py-3" style="font-size:12px;">No agents with email.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Selected recipients --}}
                        <div class="mt-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span style="font-size:11px;font-weight:700;">Selected recipients</span>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0" style="font-size:11px;"
                                    onclick="window.clearAll()">Clear all</button>
                            </div>
                            <div id="selectedRecipients" class="d-flex flex-wrap gap-1"
                                style="max-height:120px;overflow-y:auto;">
                                <span class="text-muted" style="font-size:11px;">No recipients selected.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Message --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Subject <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="subject"
                                class="form-control form-control-sm @error('subject') is-invalid @enderror"
                                value="{{ old('subject') }}" style="font-size:13px;" required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Body <span
                                    class="text-danger">*</span></label>
                            <textarea name="body" class="form-control @error('body') is-invalid @enderror" rows="14"
                                style="font-size:14px;background:#f8fafc;line-height:1.7;" required>{{ old('body') }}</textarea>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Attachments</label>
                            <label
                                class="btn btn-outline-secondary rounded-pill px-3 mb-0 d-inline-flex align-items-center gap-2"
                                style="font-size:12px;cursor:pointer;">
                                <i class="fas fa-paperclip"></i> Choose Files
                                <input type="file" name="attachments[]" class="d-none" multiple>
                            </label>
                            <small class="text-muted ms-2" style="font-size:11px;">PDF, DOC, DOCX, JPG, PNG, ZIP (max 10MB
                                each)</small>
                            <div id="attachmentList" class="mt-2 d-flex flex-wrap gap-1"></div>
                        </div>

                        <div class="form-check mb-2">
                            <input type="hidden" name="send_now" value="0">
                            <input type="checkbox" name="send_now" id="sendNow" value="1" checked>
                            <label class="form-check-label" for="sendNow" style="font-size:12px;">Send immediately (no
                                queue worker needed)</label>
                        </div>

                        <div class="pt-3 border-top d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-primary rounded-pill px-4" style="font-size:13px;"
                                onclick="return confirm('Send this bulk email to the selected recipients?');">
                                <i class="fas fa-paper-plane me-1"></i> Send Bulk Email
                            </button>
                            <span class="text-muted" style="font-size:12px;">Sends individually through the configured
                                SMTP server.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        // Global functions defined immediately so the input's inline oninput always works.
        window.filterRecipients = function(value) {
            var q = String(value || '').trim().toLowerCase();
            var counts = {};

            ['students', 'agents'].forEach(function(name) {
                var panel = document.getElementById('panel-' + name);
                if (!panel) return;
                var visible = 0;
                panel.querySelectorAll('.rp-item').forEach(function(item) {
                    var hay = (item.getAttribute('data-search') || '').toLowerCase();
                    var show = (q === '' || hay.indexOf(q) !== -1);
                    if (show) {
                        // Remove inline display so Bootstrap's d-flex applies again.
                        item.style.removeProperty('display');
                    } else {
                        // Inline !important beats Bootstrap's .d-flex { display:flex !important }.
                        item.style.setProperty('display', 'none', 'important');
                    }
                    if (show) visible++;
                });
                counts[name] = visible;
            });

            if (q !== '') {
                var activeBtn = document.querySelector('[data-tab].active');
                var cur = activeBtn ? activeBtn.dataset.tab : 'students';
                var other = cur === 'students' ? 'agents' : 'students';
                if (counts[cur] === 0 && counts[other] > 0) {
                    window.switchRecipientTab(other);
                }
            }
        };

        window.switchRecipientTab = function(name) {
            document.querySelectorAll('[data-tab]').forEach(function(b) {
                b.classList.toggle('active', b.dataset.tab === name);
            });
            var ps = document.getElementById('panel-students');
            var pa = document.getElementById('panel-agents');
            if (ps) ps.style.display = name === 'students' ? '' : 'none';
            if (pa) pa.style.display = name === 'agents' ? '' : 'none';
        };

        function cwEscape(s) {
            var d = document.createElement('div');
            d.textContent = (s == null ? '' : String(s));
            return d.innerHTML;
        }

        window.renderSelectedRecipients = function() {
            var box = document.getElementById('selectedRecipients');
            var badge = document.getElementById('selectedCount');
            var checked = document.querySelectorAll('.rp-check:checked');

            if (badge) badge.textContent = checked.length + ' selected';
            if (!box) return;

            if (!checked.length) {
                box.innerHTML = '<span class="text-muted" style="font-size:11px;">No recipients selected.</span>';
                return;
            }

            var html = '';
            Array.prototype.forEach.call(checked, function(cb) {
                var name = cb.getAttribute('data-name') || cb.value;
                var email = cb.value;
                html += '<span class="cw-chip">' +
                    '<span class="cw-chip-txt">' + cwEscape(name) + ' &lt;' + cwEscape(email) + '&gt;</span>' +
                    '<button type="button" data-remove-email="' + cwEscape(email) +
                    '" title="Remove">&times;</button>' +
                    '</span>';
            });
            box.innerHTML = html;
        };

        window.clearAll = function() {
            document.querySelectorAll('.rp-check').forEach(function(c) {
                c.checked = false;
            });
            document.querySelectorAll('.rp-all').forEach(function(a) {
                a.checked = false;
            });
            window.renderSelectedRecipients();
        };

        (function() {
            function ready(fn) {
                if (document.readyState !== 'loading') {
                    fn();
                } else {
                    document.addEventListener('DOMContentLoaded', fn);
                }
            }

            ready(function() {
                document.querySelectorAll('[data-tab]').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        window.switchRecipientTab(this.dataset.tab);
                    });
                });

                document.querySelectorAll('.rp-all').forEach(function(all) {
                    all.addEventListener('change', function() {
                        var panel = this.closest('.recipient-panel');
                        panel.querySelectorAll('.rp-item').forEach(function(item) {
                            if (item.style.display === 'none') return;
                            item.querySelector('.rp-check').checked = all.checked;
                        });
                        window.renderSelectedRecipients();
                    });
                });

                document.querySelectorAll('.rp-check').forEach(function(c) {
                    c.addEventListener('change', window.renderSelectedRecipients);
                });

                var selectedBox = document.getElementById('selectedRecipients');
                if (selectedBox) {
                    selectedBox.addEventListener('click', function(e) {
                        var btn = e.target.closest('button[data-remove-email]');
                        if (!btn) return;
                        var email = btn.getAttribute('data-remove-email');
                        document.querySelectorAll('.rp-check').forEach(function(cb) {
                            if (cb.value === email) cb.checked = false;
                        });
                        window.renderSelectedRecipients();
                    });
                }

                document.querySelectorAll('input[name="attachments[]"]').forEach(function(input) {
                    input.addEventListener('change', function() {
                        var list = document.getElementById('attachmentList');
                        if (!list) return;
                        list.innerHTML = '';
                        for (var i = 0; i < this.files.length; i++) {
                            list.innerHTML +=
                                '<span class="badge bg-light text-dark px-2 py-1 rounded-pill" style="font-size:11px;"><i class="fas fa-paperclip me-1"></i>' +
                                this.files[i].name + '</span>';
                        }
                    });
                });

                window.renderSelectedRecipients();
            });
        })();
    </script>
@endsection
