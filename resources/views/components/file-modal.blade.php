{{-- 🌐 Master Universal File Preview Modal --}}
<style>
    /* Keep your existing styles and add these */
    .modal-content {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        background: var(--active);
        color: white;
        border-bottom: none;
    }

    .modal-header .modal-title {
        color: white;
    }

    .modal-header .modal-title i {
        color: white !important;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .modal-footer {
        border-top: 1px solid #dee2e6;
        background-color: #f8f9fa;
        border-radius: 0 0 0.5rem 0.5rem;
    }

    .transition-transform {
        transition: transform 0.25s ease;
    }

    .preview-iframe {
        width: 100%;
        min-height: 70vh;
        border: none;
    }

    .preview-image {
        max-height: 70vh;
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

<div class="modal fade" id="universalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt me-2 text-primary"></i>
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
                    <button type="button" id="zoomInBtn" class="btn btn-outline-primary btn-sm me-2"
                        style="display: none;">
                        <i class="fas fa-search-plus me-1"></i>Zoom In
                    </button>
                    <button type="button" id="zoomOutBtn" class="btn btn-outline-primary btn-sm me-2"
                        style="display: none;">
                        <i class="fas fa-search-minus me-1"></i>Zoom Out
                    </button>
                    <button type="button" id="resetZoomBtn" class="btn btn-outline-secondary btn-sm"
                        style="display: none;">
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 🌐 Universal Modal Preview
        const modalEl = document.getElementById("universalModal");
        const modalContent = document.getElementById("universalModalContent");
        const zoomInBtn = document.getElementById("zoomInBtn");
        const zoomOutBtn = document.getElementById("zoomOutBtn");
        const resetZoomBtn = document.getElementById("resetZoomBtn");
        const downloadBtn = document.getElementById("downloadPreviewBtn");

        let zoomLevel = 1;
        let activeImage = null;
        let currentFileUrl = null;
        let currentFileName = null;

        // Drag state
        let isDragging = false,
            startX = 0,
            startY = 0,
            translateX = 0,
            translateY = 0;

        function getFileType(url) {
            const ext = url.split(".").pop().toLowerCase();
            if (["jpg", "jpeg", "png", "gif", "bmp", "webp"].includes(ext))
                return "image";
            if (["pdf"].includes(ext)) return "pdf";
            if (["doc", "docx", "xls", "xlsx", "ppt", "pptx"].includes(ext))
                return "office";
            if (["txt", "csv", "rtf"].includes(ext)) return "text";
            return "other";
        }

        function showPreview(fileUrl, fileName) {
            currentFileUrl = fileUrl;
            currentFileName = fileName || fileUrl.split('/').pop();

            // Update filename in modal
            document.getElementById("previewFileName").innerText = currentFileName;

            const fileType = getFileType(fileUrl);

            modalContent.innerHTML = "";
            zoomLevel = 1;
            activeImage = null;
            translateX = 0;
            translateY = 0;

            // Hide zoom controls initially
            zoomInBtn.style.display = "none";
            zoomOutBtn.style.display = "none";
            resetZoomBtn.style.display = "none";

            if (fileType === "image") {
                const img = document.createElement("img");
                img.src = fileUrl;
                img.className = "img-fluid transition-transform preview-image";
                img.style.cursor = "grab";
                modalContent.appendChild(img);
                activeImage = img;

                // Show zoom controls for images
                zoomInBtn.style.display = "inline-flex";
                zoomOutBtn.style.display = "inline-flex";
                resetZoomBtn.style.display = "inline-flex";

                // 🖱️ Drag/Pan events
                img.addEventListener("mousedown", (e) => {
                    if (zoomLevel <= 1) return;
                    isDragging = true;
                    startX = e.clientX - translateX;
                    startY = e.clientY - translateY;
                    img.style.cursor = "grabbing";
                    e.preventDefault();
                });

                window.addEventListener("mousemove", (e) => {
                    if (!isDragging) return;
                    translateX = e.clientX - startX;
                    translateY = e.clientY - startY;
                    img.style.transform =
                        `scale(${zoomLevel}) translate(${translateX}px, ${translateY}px)`;
                });

                window.addEventListener("mouseup", () => {
                    if (isDragging) {
                        isDragging = false;
                        img.style.cursor = "grab";
                    }
                });
            } else if (fileType === "pdf") {
                modalContent.innerHTML = `
                    <iframe 
                        src="${fileUrl}#toolbar=0&navpanes=0&scrollbar=1&view=FitH" 
                        class="preview-iframe"
                        style="width:100%; min-height:70vh; border:none;"
                        allow="autoplay"
                    ></iframe>`;
            } else if (fileType === "office") {
                const encoded = encodeURIComponent(fileUrl);
                modalContent.innerHTML = `
                    <div class="alert alert-info mb-2 small">
                        <i class="fas fa-info-circle me-2"></i>
                        Loading document preview... 
                        <a href="${fileUrl}" target="_blank" class="alert-link">Open in new tab</a> if preview doesn't load.
                    </div>
                    <iframe class="preview-iframe" 
                        src="https://view.officeapps.live.com/op/embed.aspx?src=${encoded}"
                        style="width:100%; min-height:65vh; border:none;">
                    </iframe>`;
            } else if (fileType === "text") {
                fetch(fileUrl)
                    .then((res) => res.text())
                    .then((text) => {
                        modalContent.innerHTML =
                            `<pre class="p-3 text-start bg-white w-100 h-100 overflow-auto" style="max-height: 70vh;">${escapeHtml(text)}</pre>`;
                    })
                    .catch(() => {
                        modalContent.innerHTML =
                            `<p class="text-danger">Unable to load text file.</p>`;
                    });
            } else {
                modalContent.innerHTML = `
                    <div class="text-center">
                        <p class="text-muted">Cannot preview this file type. Please download instead.</p>
                        <a href="${fileUrl}" target="_blank" class="btn btn-primary btn-sm">⬇️ Download File</a>
                    </div>`;
            }

            const modalInstance = new bootstrap.Modal(modalEl);
            modalInstance.show();
        }

        // Handle download button
        downloadBtn.addEventListener("click", function() {
            if (currentFileUrl) {
                const link = document.createElement('a');
                link.href = currentFileUrl;
                link.download = currentFileName || 'download';
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });

        // 🔍 Zoom Controls
        zoomInBtn.addEventListener("click", () => {
            if (activeImage) {
                zoomLevel = Math.min(zoomLevel + 0.25, 5);
                activeImage.style.transform =
                    `scale(${zoomLevel}) translate(${translateX}px, ${translateY}px)`;
            }
        });

        zoomOutBtn.addEventListener("click", () => {
            if (activeImage) {
                zoomLevel = Math.max(zoomLevel - 0.25, 1);
                if (zoomLevel <= 1) {
                    translateX = 0;
                    translateY = 0;
                }
                activeImage.style.transform =
                    `scale(${zoomLevel}) translate(${translateX}px, ${translateY}px)`;
            }
        });

        resetZoomBtn.addEventListener("click", () => {
            if (activeImage) {
                zoomLevel = 1;
                translateX = 0;
                translateY = 0;
                activeImage.style.transform = `scale(${zoomLevel}) translate(0,0)`;
            }
        });

        // ♻️ Reset modal on close
        modalEl.addEventListener("hidden.bs.modal", () => {
            modalContent.innerHTML = "";
            activeImage = null;
            zoomLevel = 1;
            translateX = 0;
            translateY = 0;
            currentFileUrl = null;
            currentFileName = null;
        });

        // Handle ALL preview buttons (data-preview and data-sop-url)
        document.body.addEventListener("click", function(e) {
            // Check for data-preview attribute (your old buttons)
            const previewBtn = e.target.closest("[data-preview]");
            if (previewBtn) {
                e.preventDefault();
                const fileUrl = previewBtn.dataset.preview;
                const fileName = previewBtn.dataset.fileName || null;
                showPreview(fileUrl, fileName);
                return;
            }

            // Check for data-sop-url attribute (SOP buttons)
            const sopBtn = e.target.closest("[data-sop-url]");
            if (sopBtn) {
                e.preventDefault();
                const fileUrl = sopBtn.dataset.sopUrl;
                const studentName = sopBtn.dataset.studentName;
                const fileName = `SOP - ${studentName}.pdf`;
                showPreview(fileUrl, fileName);
                return;
            }
        });

        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    });
</script>
