{{-- Universal File Preview Modal --}}
<div class="modal fade" id="universalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt me-2"></i>
                    <span id="previewFileTitle">File Preview</span> - <span id="previewFileName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="universalModalContent" class="text-center">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Loading document...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="zoom-controls">
                    <button type="button" id="zoomInBtn" class="btn btn-outline-primary btn-sm me-2" style="display:none;">
                        <i class="fas fa-search-plus me-1"></i>Zoom In
                    </button>
                    <button type="button" id="zoomOutBtn" class="btn btn-outline-primary btn-sm me-2" style="display:none;">
                        <i class="fas fa-search-minus me-1"></i>Zoom Out
                    </button>
                    <button type="button" id="resetZoomBtn" class="btn btn-outline-secondary btn-sm" style="display:none;">
                        <i class="fas fa-sync-alt me-1"></i>Reset
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Close
                    </button>
                    <button type="button" id="downloadPreviewBtn" class="btn btn-primary">
                        <i class="fas fa-download me-2"></i>Download
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/file-preview.js') }}"></script>
@endpush
