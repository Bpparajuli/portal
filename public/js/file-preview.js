(function() {
    'use strict';

    var modalEl = document.getElementById('previewModal');
    if (!modalEl) return;
    var body = document.getElementById('previewBody');
    var downloadBtn = document.getElementById('previewDownloadBtn');
    var instance = null;
    var currentUrl = null;
    var currentFilename = null;

    function showPreview(url, filename) {
        currentUrl = url;
        currentFilename = filename || url.split('/').pop();
        body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-light" role="status"></div><p class="mt-3 text-white-50" style="font-size:0.85rem;">Loading preview...</p></div>';
        downloadBtn.style.display = 'none';
        if (!instance) instance = new bootstrap.Modal(modalEl, { backdrop: 'static' });
        instance.show();

        var ext = url.split('.').pop().toLowerCase();
        var isImage = ['jpg','jpeg','png','gif','bmp','webp','svg'].includes(ext);
        var isPdf = ext === 'pdf';
        var isOffice = ['doc','docx','xls','xlsx','ppt','pptx'].includes(ext);

        if (isImage) {
            var img = new Image();
            img.onload = function() {
                body.innerHTML = '';
                img.className = 'preview-img';
                img.style.cssText = 'max-width:100%;max-height:85vh;object-fit:contain;border-radius:4px;display:block;margin:0 auto;';
                body.appendChild(img);
                downloadBtn.style.display = 'flex';
            };
            img.onerror = function() {
                body.innerHTML = '<div class="text-center py-5 text-white-50"><i class="fas fa-image fa-3x mb-3"></i><p>Failed to load image.</p></div>';
            };
            img.src = url;
        } else if (isPdf) {
            body.innerHTML = '<iframe src="' + url + '#toolbar=0&navpanes=0&scrollbar=1&view=FitH" class="preview-frame"></iframe>';
            downloadBtn.style.display = 'flex';
        } else if (isOffice) {
            body.innerHTML = '<div class="text-center py-4 text-white-50"><p>Preview not available for this format. <a href="' + url + '" target="_blank" class="text-white" style="text-decoration:underline;">Open in new tab</a></p></div><iframe class="preview-frame" src="https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(url) + '"></iframe>';
            downloadBtn.style.display = 'flex';
        } else {
            body.innerHTML = '<div class="text-center py-5 text-white-50"><i class="fas fa-file fa-3x mb-3"></i><p>No preview available.</p><a href="' + url + '" target="_blank" class="btn btn-outline-light btn-sm mt-2">Download File</a></div>';
        }
    }

    downloadBtn.addEventListener('click', function() {
        if (currentUrl) {
            var a = document.createElement('a');
            a.href = currentUrl;
            a.download = currentFilename || 'download';
            a.target = '_blank';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    });

    modalEl.addEventListener('hidden.bs.modal', function() {
        body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-light" role="status"></div><p class="mt-3 text-white-50" style="font-size:0.85rem;">Loading preview...</p></div>';
        downloadBtn.style.display = 'none';
        currentUrl = null;
        currentFilename = null;
    });

    document.body.addEventListener('click', function(e) {
        var el = e.target.closest('.previewable');
        if (!el) return;
        e.preventDefault();
        e.stopPropagation();

        var url = el.dataset.url || el.getAttribute('href') || el.getAttribute('src');
        if (!url || url === '#') return;
        var filename = el.dataset.filename || null;
        showPreview(url, filename);
    });

})();
