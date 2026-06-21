@extends('layouts.admin')
@section('admin-content')
<div class="container-fluid p-0" style="max-width:960px;margin:0 auto;">
    <div class="d-flex align-items-center gap-2 px-4 py-3 border-bottom bg-white">
        <a href="{{ route('admin.emails.inbox') }}" class="btn btn-sm btn-ghost rounded-pill px-3 text-decoration-none" style="font-size:13px;color:#1a0262;">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        <div class="ms-auto d-flex gap-2">
            <x-confirm-delete
                action="admin.emails.destroy"
                :id="$email->id"
                label="Delete"
                title="Delete Email?"
                message="This will permanently delete this email."
                class="btn btn-sm btn-outline-danger rounded-pill px-3"
                style="font-size:12px;"
            />
        </div>
    </div>

    @if(session('success'))
        <div class="mx-4 mt-3 mb-0 alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm py-2" style="font-size:13px;">
            <i class="fas fa-check-circle me-1"></i> {!! session('success') !!}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" style="font-size:12px;"></button>
        </div>
    @endif

    <div class="px-4 py-4">
        <div class="d-flex align-items-start gap-3 mb-4">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                 style="width:44px;height:44px;background:{{ '#' . substr(md5($email->sender_email ?? 'default@email.com'), 0, 6) }};font-size:16px;">
                {{ strtoupper(substr($email->sender_name ?? $email->sender_email, 0, 2)) }}
            </div>
            <div class="flex-grow-1 min-w-0">
                <h5 class="mb-2 fw-bold" style="color:#1a0262;font-size:16px;">{{ $email->subject ?: '(No Subject)' }}</h5>
                <div style="font-size:13px;color:#334155;">
                    <span class="fw-semibold">{{ $email->sender_name ?? 'Unknown' }}</span>
                    <span class="text-muted">&lt;{{ $email->sender_email }}&gt;</span>
                    @if($email->is_external)
                        <span class="badge bg-info-subtle text-info-emphasis ms-1" style="font-size:9px;font-weight:500;">IMAP</span>
                    @endif
                </div>
                <div class="text-muted mt-1" style="font-size:12px;">
                    To: {{ $email->recipient_name ?? $email->recipient_email }}
                    @if($email->cc) | CC: {{ $email->cc }}@endif
                </div>
                <div class="text-muted" style="font-size:12px;">
                    {{ $email->sent_at ? $email->sent_at->format('l, F j, Y g:i A') : $email->created_at->format('l, F j, Y g:i A') }}
                </div>
            </div>
        </div>

        @php $atts = is_array($email->attachments) ? $email->attachments : (json_decode($email->attachments ?? '[]', true) ?? []); @endphp
        @if(!empty($atts))
        <div class="d-flex flex-wrap gap-2 mb-4">
            @foreach($atts as $i => $att)
            <a href="{{ route('admin.emails.download-attachment', [$email->id, $i]) }}"
               class="btn btn-sm btn-outline-secondary rounded-3 d-flex align-items-center gap-2 px-3 py-2 text-decoration-none"
               style="font-size:12px;border-color:#dee2e6;">
                <i class="fas fa-paperclip text-muted"></i>
                <span class="fw-medium">{{ $att['name'] ?? 'File' }}</span>
                <span class="text-muted" style="font-size:10px;">{{ isset($att['size']) ? round($att['size'] / 1024) . ' KB' : '' }}</span>
            </a>
            @endforeach
        </div>
        @endif

        <div class="border-top pt-4" style="line-height:1.8;font-size:14px;color:#1e293b;">
            {!! nl2br(e($email->body)) !!}
        </div>
    </div>

    <div class="border-top px-4 py-4 bg-light bg-opacity-25">
        <h6 class="fw-bold mb-3" style="color:#1a0262;font-size:14px;">
            <i class="fas fa-reply me-2"></i>Reply
        </h6>
        <form action="{{ route('admin.emails.reply', $email) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <textarea name="body" class="form-control border-0 shadow-sm" rows="4"
                          placeholder="Type your reply..." required
                          style="resize:vertical;border-radius:8px;font-size:14px;background:#fff;"></textarea>
            </div>
            <div class="mb-3 d-flex align-items-center gap-3">
                <label class="btn btn-sm btn-outline-secondary rounded-pill px-3 mb-0" style="font-size:12px;cursor:pointer;">
                    <i class="fas fa-paperclip me-1"></i> Attach Files
                    <input type="file" name="attachments[]" class="d-none" multiple>
                </label>
                <small class="text-muted" style="font-size:11px;">PDF, DOC, JPG, PNG, ZIP (max 10MB)</small>
            </div>
            <div id="replyAttachmentList" class="mb-3 d-flex flex-wrap gap-1"></div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary rounded-pill px-4" style="font-size:13px;">
                    <i class="fas fa-paper-plane me-1"></i> Send Reply
                </button>
            </div>
        </form>
    </div>

    @if($email->relationLoaded('replies') && $email->replies->count())
    <div class="border-top px-4 py-4">
        <h6 class="fw-bold mb-3" style="color:#1a0262;font-size:14px;">
            <i class="fas fa-comments me-2"></i>Thread ({{ $email->replies->count() }})
        </h6>
        @foreach($email->replies as $reply)
        <div class="d-flex gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
            <div class="flex-shrink-0">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                     style="width:34px;height:34px;background:{{ '#' . substr(md5($reply->sender_email ?? 'reply'), 0, 6) }};font-size:12px;">
                    {{ strtoupper(substr($reply->sender_name ?? $reply->sender_email, 0, 2)) }}
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold" style="font-size:13px;">{{ $reply->sender_name ?? 'System' }}</span>
                    <span class="text-muted" style="font-size:11px;">{{ $reply->created_at->diffForHumans() }}</span>
                </div>
                <div style="font-size:13px;line-height:1.7;color:#334155;">{!! nl2br(e($reply->body)) !!}</div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@push('scripts')
<script>
document.querySelector('input[name="attachments[]"]')?.addEventListener('change', function() {
    const list = document.getElementById('replyAttachmentList');
    if (!list) return;
    list.innerHTML = '';
    for (let i = 0; i < this.files.length; i++) {
        list.innerHTML += '<span class="badge bg-light text-dark px-2 py-1 rounded-pill" style="font-size:11px;"><i class="fas fa-paperclip me-1"></i>' + this.files[i].name + '</span>';
    }
});
</script>
@endpush
@endsection