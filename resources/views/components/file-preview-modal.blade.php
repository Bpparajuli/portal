{{-- Minimal global file preview modal: click any .previewable element to open --}}
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="background:transparent;">
            <div class="modal-body p-0 position-relative">
                <button type="button" class="preview-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
                <button type="button" id="previewDownloadBtn" class="preview-download" aria-label="Download" style="display:none;">
                    <i class="fas fa-download"></i>
                </button>
                <div id="previewBody" class="d-flex align-items-center justify-content-center" style="min-height:60vh;">
                    <div class="text-center py-5">
                        <div class="spinner-border text-light" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-white-50" style="font-size:0.85rem;">Loading preview...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/file-preview.js') }}"></script>
@endpush
