@props([
    'title' => 'Confirm Delete',
    'message' => 'Are you sure you want to delete this item? This action cannot be undone.',
    'confirmText' => 'Delete',
    'cancelText' => 'Cancel',
    'size' => 'sm',
])

{{-- Usage: include once per page, then use data-bs-toggle="modal" data-bs-target="#sharedDeleteModal" on any button, with data-delete-url attribute --}}
<x-modal id="sharedDeleteModal" :title="$title" :size="$size">
    <p class="mb-0 text-muted small">{{ $message }}</p>
    <x-slot:footer>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ $cancelText }}</button>
        <button type="button" class="btn btn-danger btn-sm" id="sharedDeleteConfirmBtn">
            <i class="fas fa-trash-alt me-1"></i>{{ $confirmText }}
        </button>
    </x-slot:footer>
</x-modal>

@push('scripts')
<script>
    (function() {
        var deleteModal = document.getElementById('sharedDeleteModal');
        if (!deleteModal) return;
        var confirmBtn = document.getElementById('sharedDeleteConfirmBtn');
        if (!confirmBtn) return;
        var pendingUrl = null;
        var pendingMethod = 'DELETE';

        document.body.addEventListener('click', function(e) {
            var trigger = e.target.closest('[data-delete-url]');
            if (trigger) {
                e.preventDefault();
                pendingUrl = trigger.dataset.deleteUrl;
                pendingMethod = trigger.dataset.deleteMethod || 'DELETE';
            }
        });

        confirmBtn.addEventListener('click', function() {
            if (!pendingUrl) return;
            var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            var btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';
            $.ajax({
                url: pendingUrl,
                type: 'POST',
                data: { _method: pendingMethod, _token: csrfToken },
                success: function() {
                    var modal = bootstrap.Modal.getInstance(deleteModal);
                    if (modal) modal.hide();
                    location.reload();
                },
                error: function() {
                    Swal.fire('Error!', 'Something went wrong.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-trash-alt me-1"></i>Delete';
                }
            });
        });

        deleteModal.addEventListener('hidden.bs.modal', function() {
            pendingUrl = null;
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i>Delete';
        });
    })();
</script>
@endpush
