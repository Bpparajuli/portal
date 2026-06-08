@extends('layouts.admin')

@section('admin-content')
    <x-page-header :title="$enquiry->subject">
        <x-slot:actions>
            <a href="{{ route('admin.enquiries.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Enquiries
            </a>
            <x-confirm-delete action="admin.enquiries.destroy" :id="$enquiry->id" />
        </x-slot:actions>
    </x-page-header>

    <div class="row g-4">
        {{-- Enquiry Details --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold mb-1">{{ $enquiry->name }}</h5>
                                <p class="text-muted mb-0 small">{{ $enquiry->email }}</p>
                                @if($enquiry->phone)
                                    <p class="text-muted mb-0 small">{{ $enquiry->phone }}</p>
                                @endif
                            </div>
                            <small class="text-muted">{{ $enquiry->created_at->format('F d, Y \a\t h:i A') }}</small>
                        </div>
                    </div>
                    <hr>
                    <div class="mt-3">
                        {!! nl2br(e($enquiry->message)) !!}
                    </div>
                </div>
            </div>
        </div>

        {{-- Reply Form --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-reply me-2"></i>Send Reply</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.enquiries.reply', $enquiry) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">To: <span class="text-muted fw-normal">{{ $enquiry->email }}</span></label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Subject</label>
                            <input type="text" name="subject" class="form-control"
                                   value="Re: {{ $enquiry->subject }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                            <textarea name="reply_message" class="form-control" rows="8"
                                      placeholder="Type your reply here..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane me-2"></i>Send Reply
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
