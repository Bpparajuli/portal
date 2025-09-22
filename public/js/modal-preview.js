document.addEventListener("DOMContentLoaded", function () {
    const modalEl = document.getElementById("universalModal");
    const modalImg = document.getElementById("universalModalImage");

    if (!modalEl || !modalImg) return;

    // Event delegation: handle any element with data-preview attribute
    document.body.addEventListener("click", function (e) {
        const target = e.target.closest("[data-preview]");
        if (!target) return;

        e.preventDefault();
        const src = target.dataset.preview;
        if (!src) return;

        // Bootstrap Modal
        if (typeof bootstrap !== "undefined") {
            modalImg.src = src;
            let modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (!modalInstance) modalInstance = new bootstrap.Modal(modalEl);
            modalInstance.show();

            // clear src when modal hidden
            modalEl.addEventListener("hidden.bs.modal", function handler() {
                modalImg.src = "";
                modalEl.removeEventListener("hidden.bs.modal", handler);
            });
        } else {
            // fallback for no bootstrap
            window.open(src, "_blank");
        }
    });
});
