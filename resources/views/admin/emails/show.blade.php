@extends('layouts.admin')
@section('admin-content')
<div class="container-fluid p-4">
    <x-page-header :title="$email->subject">
        <x-slot:actions>
            <a href="{{ route('admin.emails.inbox') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
            <x-confirm-delete
                action="admin.emails.destroy"
                :id="$email->id"
                label="Delete"
                title="Delete Email?"
                message="This will permanently delete this email."
                mode="form"
                class="btn btn-outline-danger"
            />
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="mb-3">
                <div class="row mb-2"><div class="col-sm-2 text-muted fw-semibold">From:</div><div class="col-sm-10">{{ $email->sender_name }} &lt;{{ $email->sender_email }}&gt;</div></div>
                <div class="row mb-2"><div class="col-sm-2 text-muted fw-semibold">To:</div><div class="col-sm-10">{{ $email->recipient_name ?? $email->recipient_email }}</div></div>
                <div class="row mb-2"><div class="col-sm-2 text-muted fw-semibold">Date:</div><div class="col-sm-10">{{ $email->created_at->format('F d, Y \a\t h:i A') }}</div></div>
                @if($email->cc)<div class="row mb-2"><div class="col-sm-2 text-muted fw-semibold">CC:</div><div class="col-sm-10">{{ $email->cc }}</div></div>@endif
                @if(!empty($email->attachments))
                <div class="row mb-2">
                    <div class="col-sm-2 text-muted fw-semibold">Attachments:</div>
                    <div class="col-sm-10">
                        @foreach($email->attachments as $i => $att)
                        <a href="{{ route('admin.emails.download-attachment', [$email->id, $i]) }}" class="btn btn-sm btn-outline-secondary me-1 mb-1"><i class="fas fa-paperclip me-1"></i>{{ $att['name'] ?? 'File' }}</a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            <hr>
            <div class="mt-3">{!! nl2br(e($email->body)) !!}</div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold"><i class="fas fa-reply me-2"></i>Reply</h5></div>
        <div class="card-body p-4">
            <form action="{{ route('admin.emails.reply', $email) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3"><textarea name="body" class="form-control" rows="5" placeholder="Type your reply..." required></textarea></div>
                <div class="mb-3"><input type="file" name="attachments[]" class="form-control" multiple><small class="text-muted">Optional attachments</small></div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i>Send Reply</button>
            </form>
        </div>
    </div>

    @if($email->relationLoaded('replies') && $email->replies->count())
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold"><i class="fas fa-comments me-2"></i>Thread ({{ $email->replies->count() }})</h5></div>
        <div class="card-body p-4">
            @foreach($email->replies as $reply)
            <div class="d-flex gap-3 mb-4 pb-3 border-bottom">
                <div class="flex-shrink-0"><div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;"><i class="fas fa-user"></i></div></div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1"><span class="fw-semibold">{{ $reply->sender_name ?? 'System' }}</span><small class="text-muted">{{ $reply->created_at->diffForHumans() }}</small></div>
                    <p class="mb-0">{!! nl2br(e($reply->body)) !!}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
