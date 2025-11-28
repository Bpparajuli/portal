document.addEventListener("DOMContentLoaded", function () {
    // 🌟 Live file input preview
    document.querySelectorAll(".file-input").forEach((input) => {
        input.addEventListener("change", function () {
            const previewContainer =
                this.closest(".file-upload-group")?.querySelector(
                    ".live-preview"
                );
            if (!previewContainer) return;

            previewContainer.innerHTML = "";
            const file = this.files[0];
            if (!file) return;

            const ext = file.name.split(".").pop().toLowerCase();
            const url = URL.createObjectURL(file);

            if (["jpg", "jpeg", "png", "gif"].includes(ext)) {
                const img = document.createElement("img");
                img.src = url;
                Object.assign(img.style, {
                    width: "200px",
                    height: "200px",
                    objectFit: "cover",
                    borderRadius: "6px",
                });
                previewContainer.appendChild(img);
            } else if (ext === "pdf") {
                const iframe = document.createElement("iframe");
                iframe.src = url;
                iframe.width = "50%";
                iframe.height = "300px";
                previewContainer.appendChild(iframe);
            } else {
                previewContainer.innerHTML = `<small class="text-muted">Preview not available</small>`;
            }
        });
    });

    // 🌐 Universal Modal Preview
    const modalEl = document.getElementById("universalModal");
    const modalContent = document.getElementById("universalModalContent");
    const zoomInBtn = document.getElementById("zoomInBtn");
    const zoomOutBtn = document.getElementById("zoomOutBtn");
    const resetZoomBtn = document.getElementById("resetZoomBtn");

    let zoomLevel = 1;
    let activeImage = null;

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

    document.body.addEventListener("click", function (e) {
        const target = e.target.closest("[data-preview]");
        if (!target) return;

        e.preventDefault();
        const fileUrl = target.dataset.preview;
        const fileType = getFileType(fileUrl);

        modalContent.innerHTML = "";
        zoomLevel = 1;
        activeImage = null;
        translateX = 0;
        translateY = 0;

        if (fileType === "image") {
            const img = document.createElement("img");
            img.src = fileUrl;
            img.className = "img-fluid transition-transform preview-image";
            img.style.cursor = "grab";
            modalContent.appendChild(img);
            activeImage = img;

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
                img.style.transform = `scale(${zoomLevel}) translate(${translateX}px, ${translateY}px)`;
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
        allow="autoplay"
    ></iframe>`;
        } else if (fileType === "office") {
            const encoded = encodeURIComponent(fileUrl);
            modalContent.innerHTML = `
                <iframe class="preview-iframe" 
                    src="https://view.officeapps.live.com/op/embed.aspx?src=${encoded}">
                </iframe>`;
        } else if (fileType === "text") {
            fetch(fileUrl)
                .then((res) => res.text())
                .then((text) => {
                    modalContent.innerHTML = `<pre class="p-3 text-start bg-white w-100 h-100 overflow-auto">${text}</pre>`;
                })
                .catch(() => {
                    modalContent.innerHTML = `<p class="text-danger">Unable to load text file.</p>`;
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
    });

    // 🔍 Zoom Controls
    zoomInBtn.addEventListener("click", () => {
        if (activeImage) {
            zoomLevel = Math.min(zoomLevel + 0.25, 5);
            activeImage.style.transform = `scale(${zoomLevel}) translate(${translateX}px, ${translateY}px)`;
        }
    });

    zoomOutBtn.addEventListener("click", () => {
        if (activeImage) {
            zoomLevel = Math.max(zoomLevel - 0.25, 1);
            // Reset translate if zoom goes below 1
            if (zoomLevel <= 1) {
                translateX = 0;
                translateY = 0;
            }
            activeImage.style.transform = `scale(${zoomLevel}) translate(${translateX}px, ${translateY}px)`;
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
    });
});
