@extends('layouts.admin')
@section('admin-content')
    <x-page-header title="{{ $folder === 'sent' ? 'Sent Emails' : ($folder === 'drafts' ? 'Drafts' : 'Email Inbox') }}">
        <x-slot:actions>
            <a href="{{ route('admin.emails.create') }}" class="btn btn-primary"><i class="fas fa-pen me-2"></i>Compose</a>
        </x-slot:actions>
    </x-page-header>

    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <a class="nav-link {{ $folder === 'inbox' ? 'active' : '' }}" href="{{ route('admin.emails.inbox') }}">
                <i class="fas fa-inbox me-1"></i>Inbox
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $folder === 'sent' ? 'active' : '' }}" href="{{ route('admin.emails.sent') }}">
                <i class="fas fa-paper-plane me-1"></i>Sent
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $folder === 'drafts' ? 'active' : '' }}" href="{{ route('admin.emails.drafts') }}">
                <i class="fas fa-file me-1"></i>Drafts
            </a>
        </li>
    </ul>

    @if($emails->count())
    <div class="card mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:30px"></th>
                        <th>{{ $folder === 'sent' ? 'To' : 'From' }}</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th style="width:80px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($emails as $email)
                    <tr class="{{ $email->status !== 'read' && $folder === 'inbox' ? 'fw-semibold' : '' }}" style="cursor:pointer;" onclick="window.location='{{ route('admin.emails.show', $email) }}'">
                        <td>
                            <button type="button" class="btn btn-sm btn-link text-warning p-1 js-star-toggle" data-id="{{ $email->id }}" onclick="event.stopPropagation();">
                                <i class="fas fa-star{{ $email->is_starred ? '' : '-o' }}"></i>
                            </button>
                        </td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width:180px">{{ $folder === 'sent' ? $email->recipient_name : $email->sender_name }}</span>
                        </td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width:350px">{{ $email->subject }}</span>
                        </td>
                        <td class="text-muted small">{{ $email->created_at->format('M d, Y H:i') }}</td>
                        <td>
                            <x-confirm-delete
                                action="admin.emails.destroy"
                                :id="$email->id"
                                label=""
                                title="Delete Email?"
                                message="This will permanently delete this email."
                                mode="form"
                                class="btn btn-sm btn-link text-danger p-1"
                            />
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($emails->hasPages())
        <div class="card-footer">{{ $emails->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
    @else
    <x-empty-state icon="fa-inbox" title="No emails yet" description="Your {{ $folder }} is empty." actionLabel="Compose Email" actionUrl="{{ route('admin.emails.create') }}" />
    @endif

@push('scripts')
<script>
document.querySelectorAll('.js-star-toggle').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const icon = this.querySelector('i');
        fetch('/admin/emails/' + id + '/toggle-star', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(function() { icon.classList.toggle('fa-star'); icon.classList.toggle('fa-star-o'); });
    });
});
</script>
@endpush
@endsection