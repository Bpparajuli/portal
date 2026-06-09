@extends('layouts.admin')
@section('admin-content')
    <x-page-header title="Enquiries">
        <x-slot:actions>
        </x-slot:actions>
    </x-page-header>

    @if($enquiries->count())
    <div class="card mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th style="width:110px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enquiries as $enquiry)
                    <tr class="{{ $enquiry->is_read ? '' : 'fw-semibold' }}">
                        <td>{{ $enquiry->name }}</td>
                        <td class="small">{{ $enquiry->email }}</td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width:300px">{{ $enquiry->subject }}</span>
                        </td>
                        <td class="small text-muted">{{ $enquiry->created_at->format('M d, Y H:i') }}</td>
                        <td>
                            <span class="badge bg-{{ $enquiry->is_read ? 'secondary' : 'success' }} bg-opacity-10 text-{{ $enquiry->is_read ? 'secondary' : 'success' }} rounded-pill">
                                {{ $enquiry->is_read ? 'Read' : 'New' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewEnquiry({{ $enquiry->id }})" title="Quick View"><i class="fas fa-eye"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-primary" title="Reply" onclick="replyEnquiry({{ $enquiry->id }})"><i class="fas fa-reply"></i></button>
                                <x-confirm-delete
                                    action="admin.enquiries.destroy"
                                    :id="$enquiry->id"
                                    label=""
                                    title="Delete Enquiry?"
                                    message="This will permanently delete this enquiry."
                                    mode="form"
                                    class="btn btn-sm btn-outline-danger"
                                />
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($enquiries->hasPages())
        <div class="card-footer bg-white">{{ $enquiries->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
    @else
    <x-empty-state icon="fa-inbox" title="No enquiries yet" description="There are no incoming enquiries to display." />
    @endif

<div class="modal fade" id="enquiryViewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSubject">Enquiry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="modalReplyBtn"><i class="fas fa-reply me-1"></i>Reply</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="enquiryReplyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" id="enquiryReplyForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-reply me-2 text-primary"></i>Send Reply</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">To:</label>
                        <span class="text-muted" id="replyToEmail"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subject</label>
                        <input type="text" name="subject" class="form-control" id="replySubject" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                        <textarea name="reply_message" class="form-control" rows="8" placeholder="Type your reply here..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane me-2"></i>Send Reply</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const enquiries = @json($enquiries->items());
function viewEnquiry(id) {
    const e = enquiries.find(x => x.id === id);
    if (!e) return;
    document.getElementById('modalSubject').textContent = e.subject;
    document.getElementById('modalBody').innerHTML = `
        <div class="mb-3"><strong>From:</strong> ${e.name} &lt;${e.email}&gt;${e.phone ? '<br><strong>Phone:</strong> ' + e.phone : ''}</div>
        <div class="mb-2"><strong>Date:</strong> ${new Date(e.created_at).toLocaleString()}</div>
        <hr>
        <div>${(e.message || '').replace(/\n/g, '<br>')}</div>
    `;
    document.getElementById('modalReplyBtn').onclick = function() { replyEnquiry(e.id); };
    new bootstrap.Modal(document.getElementById('enquiryViewModal')).show();
}
function replyEnquiry(id) {
    const e = enquiries.find(x => x.id === id);
    if (!e) return;
    document.getElementById('replyToEmail').textContent = e.email;
    document.getElementById('replySubject').value = 'Re: ' + e.subject;
    document.getElementById('enquiryReplyForm').action = '/admin/enquiries/' + e.id + '/reply';
    const viewModal = bootstrap.Modal.getInstance(document.getElementById('enquiryViewModal'));
    if (viewModal) viewModal.hide();
    new bootstrap.Modal(document.getElementById('enquiryReplyModal')).show();
}
</script>
@endpush
@endsection
