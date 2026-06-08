@extends('layouts.admin')
@section('admin-content')
    <x-page-header title="Compose Email">
        <x-slot:actions>
            <a href="{{ route('admin.emails.inbox') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.emails.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">To <span class="text-danger">*</span></label>
                    <select name="recipient_email" class="form-select @error('recipient_email') is-invalid @enderror" id="recipientSelect">
                        <option value="">Select a recipient...</option>
                        @foreach($users as $user)
                        <option value="{{ $user->email }}" {{ old('recipient_email') == $user->email ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }}) - {{ ucfirst($user->role) }}</option>
                        @endforeach
                    </select>
                    @error('recipient_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-link text-decoration-none" onclick="toggleManualRecipient()"><i class="fas fa-pen me-1"></i>Or enter email manually</button>
                    </div>
                    <div id="manualRecipient" class="mt-2 d-none">
                        <input type="email" name="manual_recipient" class="form-control form-control-sm" placeholder="Enter email address manually...">
                        <small class="text-muted">Leave recipient dropdown empty when using manual entry</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Recipient Name</label>
                    <input type="text" name="recipient_name" class="form-control" value="{{ old('recipient_name') }}" placeholder="Optional - will auto-fill from system">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                    <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" required>
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Body <span class="text-danger">*</span></label>
                    <textarea name="body" class="form-control @error('body') is-invalid @enderror" rows="12">{{ old('body') }}</textarea>
                    @error('body')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Attachments</label>
                    <input type="file" name="attachments[]" class="form-control" multiple>
                    <small class="text-muted">Supported: PDF, DOC, DOCX, JPG, PNG, ZIP (max 10MB each)</small>
                    <div id="attachmentList" class="mt-2"></div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-paper-plane me-2"></i>Send</button>
                    <button type="submit" formaction="{{ route('admin.emails.save-draft') }}" class="btn btn-outline-secondary px-4"><i class="fas fa-save me-2"></i>Save Draft</button>
                </div>
            </form>
        </div>
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

document.querySelector('input[name="attachments[]"]')?.addEventListener('change', function() {
    const list = document.getElementById('attachmentList');
    list.innerHTML = '';
    for (const f of this.files) {
        list.innerHTML += `<div class="d-inline-flex align-items-center gap-1 me-2 mb-1 p-1 bg-light rounded"><i class="fas fa-paperclip text-muted"></i><small>${f.name}</small></div>`;
    }
});
</script>
@endpush
@endsection
