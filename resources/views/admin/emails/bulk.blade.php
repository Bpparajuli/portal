@extends('layouts.admin')
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
</style>
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

                        <ul class="nav nav-pills nav-sm mb-2" style="font-size:12px;">
                            <li class="nav-item"><button type="button" class="nav-link active" data-tab="students">Students
                                    ({{ $students->count() }})</button></li>
                            <li class="nav-item"><button type="button" class="nav-link" data-tab="agents">Agents
                                    ({{ $agents->count() }})</button></li>
                        </ul>

                        {{-- Students --}}
                        <div class="recipient-panel" id="panel-students">
                            <div class="d-flex gap-2 mb-2">
                                <input type="text" class="form-control form-control-sm rp-search"
                                    placeholder="Search students…" style="font-size:12px;">
                                <label
                                    class="btn btn-sm btn-outline-secondary rounded-pill px-2 mb-0 d-flex align-items-center gap-1 text-nowrap"
                                    style="font-size:11px;">
                                    <input type="checkbox" class="rp-all"> All
                                </label>
                            </div>
                            <div class="rp-list"
                                style="max-height:360px;overflow-y:auto;border:1px solid #eee;border-radius:8px;">
                                @forelse ($students as $s)
                                    <label class="d-flex align-items-center gap-2 px-2 py-1 border-bottom rp-item"
                                        data-search="{{ strtolower($s->first_name . ' ' . $s->last_name . ' ' . $s->email) }}">
                                        <input type="checkbox" name="emails[]" value="{{ $s->email }}" class="rp-check">
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
                            <div class="d-flex gap-2 mb-2">
                                <input type="text" class="form-control form-control-sm rp-search"
                                    placeholder="Search agents…" style="font-size:12px;">
                                <label
                                    class="btn btn-sm btn-outline-secondary rounded-pill px-2 mb-0 d-flex align-items-center gap-1 text-nowrap"
                                    style="font-size:11px;">
                                    <input type="checkbox" class="rp-all"> All
                                </label>
                            </div>
                            <div class="rp-list"
                                style="max-height:360px;overflow-y:auto;border:1px solid #eee;border-radius:8px;">
                                @forelse ($agents as $a)
                                    <label class="d-flex align-items-center gap-2 px-2 py-1 border-bottom rp-item"
                                        data-search="{{ strtolower(($a->business_name ?: $a->name) . ' ' . $a->email) }}">
                                        <input type="checkbox" name="emails[]" value="{{ $a->email }}"
                                            class="rp-check">
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

                        <div class="mt-2 text-end">
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" style="font-size:11px;"
                                onclick="clearAll()">Clear selection</button>
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

                        <div class="pt-3 border-top d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-primary rounded-pill px-4" style="font-size:13px;"
                                onclick="return confirm('Queue this bulk email for the selected recipients?');">
                                <i class="fas fa-paper-plane me-1"></i> Queue Bulk Email
                            </button>
                            <span class="text-muted" style="font-size:12px;">Sends individually through the configured
                                SMTP server; runs in the background.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            (function() {
                // Tabs
                document.querySelectorAll('[data-tab]').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('[data-tab]').forEach(function(b) {
                            b.classList.remove('active');
                        });
                        this.classList.add('active');
                        document.getElementById('panel-students').style.display = this.dataset.tab ===
                            'students' ? '' : 'none';
                        document.getElementById('panel-agents').style.display = this.dataset.tab ===
                            'agents' ? '' : 'none';
                    });
                });

                // Select all per panel
                document.querySelectorAll('.rp-all').forEach(function(all) {
                    all.addEventListener('change', function() {
                        var panel = this.closest('.recipient-panel');
                        panel.querySelectorAll('.rp-item').forEach(function(item) {
                            if (item.style.display === 'none') return;
                            item.querySelector('.rp-check').checked = all.checked;
                        });
                        updateCount();
                    });
                });

                // Search filter per panel
                document.querySelectorAll('.rp-search').forEach(function(box) {
                    box.addEventListener('input', function() {
                        var panel = this.closest('.recipient-panel');
                        var q = this.value.trim().toLowerCase();
                        panel.querySelectorAll('.rp-item').forEach(function(item) {
                            item.style.display = item.dataset.search.indexOf(q) !== -1 ? '' :
                                'none';
                        });
                    });
                });

                // Count updates
                function updateCount() {
                    var n = document.querySelectorAll('.rp-check:checked').length;
                    document.getElementById('selectedCount').textContent = n + ' selected';
                }
                document.querySelectorAll('.rp-check').forEach(function(c) {
                    c.addEventListener('change', updateCount);
                });

                window.clearAll = function() {
                    document.querySelectorAll('.rp-check').forEach(function(c) {
                        c.checked = false;
                    });
                    document.querySelectorAll('.rp-all').forEach(function(a) {
                        a.checked = false;
                    });
                    updateCount();
                };

                // Attachments preview
                document.querySelectorAll('input[name="attachments[]"]').forEach(function(input) {
                    input.addEventListener('change', function() {
                        var list = document.getElementById('attachmentList');
                        list.innerHTML = '';
                        for (var i = 0; i < this.files.length; i++) {
                            list.innerHTML +=
                                '<span class="badge bg-light text-dark px-2 py-1 rounded-pill" style="font-size:11px;"><i class="fas fa-paperclip me-1"></i>' +
                                this.files[i].name + '</span>';
                        }
                    });
                });

                updateCount();
            })();
        </script>
    @endpush
@endsection
