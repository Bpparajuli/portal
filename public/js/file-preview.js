(function() {
    'use strict';

    const modalEl = document.getElementById('universalModal');
    if (!modalEl) return;

    const modalContent = document.getElementById('universalModalContent');
    const zoomInBtn = document.getElementById('zoomInBtn');
    const zoomOutBtn = document.getElementById('zoomOutBtn');
    const resetZoomBtn = document.getElementById('resetZoomBtn');
    const downloadBtn = document.getElementById('downloadPreviewBtn');

    let zoomLevel = 1;
    let activeImage = null;
    let currentFileUrl = null;
    let currentFileName = null;
    let isDragging = false, startX = 0, startY = 0, translateX = 0, translateY = 0;

    function getFileType(url) {
        const ext = url.split('.').pop().toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(ext)) return 'image';
        if (['pdf'].includes(ext)) return 'pdf';
        if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(ext)) return 'office';
        if (['txt', 'csv', 'rtf'].includes(ext)) return 'text';
        return 'other';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    window.showPreview = function(fileUrl, fileName) {
        currentFileUrl = fileUrl;
        currentFileName = fileName || fileUrl.split('/').pop();
        document.getElementById('previewFileName').innerText = currentFileName;

        const fileType = getFileType(fileUrl);
        modalContent.innerHTML = '';
        zoomLevel = 1;
        activeImage = null;
        translateX = 0;
        translateY = 0;
        zoomInBtn.style.display = 'none';
        zoomOutBtn.style.display = 'none';
        resetZoomBtn.style.display = 'none';

        if (fileType === 'image') {
            const img = document.createElement('img');
            img.src = fileUrl;
            img.className = 'img-fluid transition-transform preview-image';
            img.style.cursor = 'grab';
            modalContent.appendChild(img);
            activeImage = img;
            zoomInBtn.style.display = 'inline-flex';
            zoomOutBtn.style.display = 'inline-flex';
            resetZoomBtn.style.display = 'inline-flex';

            img.addEventListener('mousedown', function(e) {
                if (zoomLevel <= 1) return;
                isDragging = true;
                startX = e.clientX - translateX;
                startY = e.clientY - translateY;
                img.style.cursor = 'grabbing';
                e.preventDefault();
            });
            window.addEventListener('mousemove', function(e) {
                if (!isDragging) return;
                translateX = e.clientX - startX;
                translateY = e.clientY - startY;
                img.style.transform = 'scale(' + zoomLevel + ') translate(' + translateX + 'px, ' + translateY + 'px)';
            });
            window.addEventListener('mouseup', function() {
                if (isDragging) {
                    isDragging = false;
                    img.style.cursor = 'grab';
                }
            });
        } else if (fileType === 'pdf') {
            modalContent.innerHTML = '<iframe src="' + fileUrl + '#toolbar=0&navpanes=0&scrollbar=1&view=FitH" class="preview-iframe" allow="autoplay"></iframe>';
        } else if (fileType === 'office') {
            var encoded = encodeURIComponent(fileUrl);
            modalContent.innerHTML = '<div class="alert alert-info mb-2 small"><i class="fas fa-info-circle me-2"></i>Loading document preview... <a href="' + fileUrl + '" target="_blank" class="alert-link">Open in new tab</a> if preview doesn\'t load.</div><iframe class="preview-iframe" src="https://view.officeapps.live.com/op/embed.aspx?src=' + encoded + '"></iframe>';
        } else if (fileType === 'text') {
            fetch(fileUrl)
                .then(function(res) { return res.text(); })
                .then(function(text) {
                    modalContent.innerHTML = '<pre class="p-3 text-start bg-white w-100 h-100 overflow-auto" style="max-height: 70vh;">' + escapeHtml(text) + '</pre>';
                })
                .catch(function() {
                    modalContent.innerHTML = '<p class="text-danger">Unable to load text file.</p>';
                });
        } else {
            modalContent.innerHTML = '<div class="text-center"><p class="text-muted">Cannot preview this file type. Please download instead.</p><a href="' + fileUrl + '" target="_blank" class="btn btn-primary btn-sm">Download File</a></div>';
        }

        var modalInstance = new bootstrap.Modal(modalEl);
        modalInstance.show();
    };

    downloadBtn.addEventListener('click', function() {
        if (currentFileUrl) {
            var link = document.createElement('a');
            link.href = currentFileUrl;
            link.download = currentFileName || 'download';
            link.target = '_blank';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    });

    zoomInBtn.addEventListener('click', function() {
        if (activeImage) {
            zoomLevel = Math.min(zoomLevel + 0.25, 5);
            activeImage.style.transform = 'scale(' + zoomLevel + ') translate(' + translateX + 'px, ' + translateY + 'px)';
        }
    });

    zoomOutBtn.addEventListener('click', function() {
        if (activeImage) {
            zoomLevel = Math.max(zoomLevel - 0.25, 1);
            if (zoomLevel <= 1) { translateX = 0; translateY = 0; }
            activeImage.style.transform = 'scale(' + zoomLevel + ') translate(' + translateX + 'px, ' + translateY + 'px)';
        }
    });

    resetZoomBtn.addEventListener('click', function() {
        if (activeImage) {
            zoomLevel = 1;
            translateX = 0;
            translateY = 0;
            activeImage.style.transform = 'scale(1) translate(0,0)';
        }
    });

    modalEl.addEventListener('hidden.bs.modal', function() {
        modalContent.innerHTML = '';
        activeImage = null;
        zoomLevel = 1;
        translateX = 0;
        translateY = 0;
        currentFileUrl = null;
        currentFileName = null;
    });

    document.body.addEventListener('click', function(e) {
        var previewBtn = e.target.closest('[data-preview]');
        if (previewBtn) {
            e.preventDefault();
            showPreview(previewBtn.dataset.preview, previewBtn.dataset.fileName || null);
            return;
        }
        var sopBtn = e.target.closest('[data-sop-url]');
        if (sopBtn) {
            e.preventDefault();
            showPreview(sopBtn.dataset.sopUrl, 'SOP - ' + (sopBtn.dataset.studentName || 'Student') + '.pdf');
            return;
        }
    });

})();
