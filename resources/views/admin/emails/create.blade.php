@extends('layouts.admin')
@section('admin-content')
    @php $editing = isset($email) && $email->folder === 'drafts'; @endphp
    <x-page-header title="{{ $editing ? 'Edit Draft' : 'Compose Email' }}">
        <x-slot:actions>
            <a href="{{ route('admin.emails.' . ($editing ? 'drafts' : 'inbox')) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size:12px;">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="card border-0 shadow-sm" style="max-width:820px;">
        <div class="card-body p-4">
            <form action="{{ $editing ? route('admin.emails.update', $email) : route('admin.emails.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if($editing) @method('PUT') @endif

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;">To <span class="text-danger">*</span></label>
                    <select name="recipient_email" class="form-select form-select-sm @error('recipient_email') is-invalid @enderror" id="recipientSelect" style="font-size:13px;">
                        <option value="">Select a recipient...</option>
                        @foreach($users as $user)
                        <option value="{{ $user->email }}"
                            {{ old('recipient_email', $editing ? $email->recipient_email : '') == $user->email ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                        @endforeach
                    </select>
                    @error('recipient_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" onclick="toggleManualRecipient()" style="font-size:12px;color:#1a0262;">
                            <i class="fas fa-pen me-1"></i>Or enter email manually
                        </button>
                    </div>
                    <div id="manualRecipient" class="mt-2 {{ $editing && !$email->recipient_id ? '' : 'd-none' }}">
                        <input type="email" name="manual_recipient" class="form-control form-control-sm"
                               value="{{ old('manual_recipient', $editing && !$email->recipient_id ? $email->recipient_email : '') }}"
                               placeholder="Enter email address manually..." style="font-size:13px;">
                        <small class="text-muted" style="font-size:11px;">Leave recipient dropdown empty when using manual entry</small>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:13px;">Recipient Name</label>
                        <input type="text" name="recipient_name" class="form-control form-control-sm"
                               value="{{ old('recipient_name', $editing ? $email->recipient_name : '') }}" placeholder="Optional" style="font-size:13px;">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold" style="font-size:13px;">CC</label>
                        <input type="text" name="cc" class="form-control form-control-sm"
                               value="{{ old('cc', $editing ? $email->cc : '') }}" placeholder="Optional" style="font-size:13px;">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold" style="font-size:13px;">BCC</label>
                        <input type="text" name="bcc" class="form-control form-control-sm"
                               value="{{ old('bcc', $editing ? $email->bcc : '') }}" placeholder="Optional" style="font-size:13px;">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;">Subject <span class="text-danger">*</span></label>
                    <input type="text" name="subject" class="form-control form-control-sm @error('subject') is-invalid @enderror"
                           value="{{ old('subject', $editing ? $email->subject : '') }}" style="font-size:13px;">
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;">Body <span class="text-danger">*</span></label>
                    <textarea name="body" class="form-control @error('body') is-invalid @enderror" rows="14"
                              style="font-size:14px;background:#f8fafc;line-height:1.7;">{{ old('body', $editing ? $email->body : '') }}</textarea>
                    @error('body')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold" style="font-size:13px;">Attachments</label>
                    @if($editing && !empty($email->attachments))
                        <div class="mb-2 d-flex flex-wrap gap-1" id="existingAttachments">
                            @foreach($email->attachments as $att)
                            <span class="badge bg-light text-dark px-2 py-1 rounded-pill" style="font-size:11px;">
                                <i class="fas fa-paperclip me-1"></i>{{ $att['name'] ?? 'File' }}
                            </span>
                            @endforeach
                        </div>
                    @endif
                    <label class="btn btn-outline-secondary rounded-pill px-3 mb-0 d-inline-flex align-items-center gap-2" style="font-size:12px;cursor:pointer;">
                        <i class="fas fa-paperclip"></i> Choose Files
                        <input type="file" name="attachments[]" class="d-none" multiple>
                    </label>
                    <small class="text-muted ms-2" style="font-size:11px;">PDF, DOC, DOCX, JPG, PNG, ZIP (max 10MB each)</small>
                    <div id="attachmentList" class="mt-2 d-flex flex-wrap gap-1"></div>
                </div>

                <div class="d-flex gap-2 pt-3 border-top">
                    @if($editing)
                        <button type="submit" class="btn btn-primary rounded-pill px-4" style="font-size:13px;">
                            <i class="fas fa-save me-1"></i> Update Draft
                        </button>
                        <button type="button" class="btn btn-success rounded-pill px-4" onclick="sendDraft()" style="font-size:13px;">
                            <i class="fas fa-paper-plane me-1"></i> Send
                        </button>
                    @else
                        <button type="submit" class="btn btn-primary rounded-pill px-4" style="font-size:13px;">
                            <i class="fas fa-paper-plane me-1"></i> Send
                        </button>
                        <button type="submit" formaction="{{ route('admin.emails.save-draft') }}" class="btn btn-outline-secondary rounded-pill px-4" style="font-size:13px;">
                            <i class="fas fa-save me-1"></i> Save Draft
                        </button>
                    @endif
                </div>
            </form>

            @if($editing)
            <form id="sendDraftForm" action="{{ route('admin.emails.send-draft', $email) }}" method="POST" class="d-none">
                @csrf
            </form>
            @endif
        </div>
    </div>

@push('scripts')
<script>
function toggleManualRecipient() {
    const el = document.getElementById('manualRecipient');
    const sel = document.getElementById('recipientSelect');
    el.classList.toggle('d-none');
    if (!el.classList.contains('d-none')) {
        sel.disabled = true;
        sel.value = '';
        sel.style.opacity = '0.5';
    } else {
        sel.disabled = false;
        sel.style.opacity = '1';
    }
}

@if($editing)
function sendDraft() {
    if (confirm('Send this draft now?')) {
        document.getElementById('sendDraftForm').submit();
    }
}
@endif

document.querySelectorAll('input[name="attachments[]"]').forEach(function(input) {
    input.addEventListener('change', function() {
        const list = document.getElementById('attachmentList');
        if (!list) return;
        list.innerHTML = '';
        for (let i = 0; i < this.files.length; i++) {
            list.innerHTML += '<span class="badge bg-light text-dark px-2 py-1 rounded-pill" style="font-size:11px;"><i class="fas fa-paperclip me-1"></i>' + this.files[i].name + '</span>';
        }
    });
});

@if($editing && !$email->recipient_id && $email->recipient_email)
(function() {
    const sel = document.getElementById('recipientSelect');
    sel.disabled = true;
    sel.style.opacity = '0.5';
})();
@endif
</script>
@endpush
@endsection