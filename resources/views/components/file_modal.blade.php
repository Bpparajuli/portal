{{-- 🌐 Universal File Preview Modal --}}
<div class="modal fade" id="universalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen">
        <div class="modal-content bg-light">
            <div class="modal-body text-center p-0 d-flex justify-content-center align-items-center" style="height:100vh; overflow:hidden;">
                <div id="universalModalContent" class="w-100 h-100 d-flex justify-content-center align-items-center"></div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" id="zoomInBtn" class="btn btn-outline-primary btn-sm me-2">➕ Zoom In</button>
                <button type="button" id="zoomOutBtn" class="btn btn-outline-primary btn-sm me-2">➖ Zoom Out</button>
                <button type="button" id="resetZoomBtn" class="btn btn-outline-secondary btn-sm me-2">🔄 Reset</button>
                @php
                $student = $student ?? null;
                $doc = $doc ?? null;
                @endphp
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .transition-transform {
        transition: transform 0.25s ease;
    }

    .preview-iframe {
        width: 100%;
        height: 100vh;
        border: none;
    }

    .preview-image {
        max-height: 100%;
        width: auto;
        transform: scale(1);
        cursor: grab;
    }

    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

</style>
