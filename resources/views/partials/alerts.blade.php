{{-- Validation Errors --}}
@if ($errors->any())
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
        <div class="toast show text-bg-danger" role="alert" data-bs-delay="5000">
            <div class="toast-header">
                <strong class="me-auto">Errors</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

{{-- Flash Success Message --}}
@if (session('success'))
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
        <div class="toast show text-bg-success" role="alert" data-bs-delay="5000">
            <div class="toast-header">
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                {{ session('success') }}
            </div>
        </div>
    </div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toastElList = [].slice.call(document.querySelectorAll('.toast'));
        toastElList.forEach(function(toastEl) {
            const toast = new bootstrap.Toast(toastEl, {
                delay: 3000,
                autohide: true
            });
            toast.show();
        });
    });
</script>
