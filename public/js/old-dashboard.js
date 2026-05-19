document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    // ===========================
    // 1️⃣ Navigation Active Toggle
    // ===========================
    const navItems = document.querySelectorAll(".nav-item");
    if (navItems.length) {
        navItems.forEach((navItem) => {
            navItem.addEventListener("click", () => {
                navItems.forEach((item) => item.classList.remove("active"));
                navItem.classList.add("active");
            });
        });
    }

    // ===========================
    // 2️⃣ Bootstrap Form Validation
    // ===========================
    const forms = document.querySelectorAll(".needs-validation");
    if (forms.length) {
        Array.from(forms).forEach(function (form) {
            form.addEventListener("submit", function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add("was-validated");
            });
        });
    }

    // ===========================
    // 3️⃣ Animate on Scroll (AOS)
    // ===========================
    if (typeof AOS !== "undefined") {
        AOS.init({
            duration: 1000,
            once: true,
        });
    }

    // ===========================
    // 4️⃣ Testimonial Slider
    // ===========================
    const slider = document.getElementById("testimonial-slider");
    const prevBtn = document.querySelector(".slider-btn.prev");
    const nextBtn = document.querySelector(".slider-btn.next");

    if (slider && prevBtn && nextBtn) {
        let scrollAmount = 0;
        const card = slider.querySelector(".dash-testimonial-card");
        const gap = parseInt(getComputedStyle(slider).gap || 16);
        const slideWidth = card.offsetWidth + gap;

        nextBtn.addEventListener("click", () => {
            scrollAmount = Math.min(
                scrollAmount + slideWidth,
                slider.scrollWidth - slider.clientWidth
            );
            slider.scrollTo({ left: scrollAmount, behavior: "smooth" });
        });

        prevBtn.addEventListener("click", () => {
            scrollAmount = Math.max(scrollAmount - slideWidth, 0);
            slider.scrollTo({ left: scrollAmount, behavior: "smooth" });
        });
    }

    // ===========================
    // 5️⃣ University Logo Auto Slider
    // ===========================
    const uniSlider = document.getElementById("uni-slider");
    if (uniSlider) {
        let scrollStep = 1;

        function autoSlide() {
            uniSlider.scrollLeft += scrollStep;
            if (
                uniSlider.scrollLeft + uniSlider.clientWidth >=
                    uniSlider.scrollWidth ||
                uniSlider.scrollLeft <= 0
            ) {
                scrollStep *= -1; // reverse direction
            }
        }
        setInterval(autoSlide, 10);
    }

    // ===========================
    // 6️⃣ ChartJS Initialization (Reusable)
    // ===========================
    if (typeof Chart !== "undefined") {
        window.DashboardCharts = {
            initLineChart: (id, labels, data, color = "#4f46e5") => {
                const ctx = document.getElementById(id);
                if (!ctx) return;
                new Chart(ctx, {
                    type: "line",
                    data: {
                        labels,
                        datasets: [
                            {
                                data,
                                borderColor: color,
                                backgroundColor: "rgba(79,70,229,0.12)",
                                fill: true,
                                tension: 0.3,
                                pointRadius: 3,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } },
                    },
                });
            },
            initBarChart: (id, labels, data, color = "#3b82f6") => {
                const ctx = document.getElementById(id);
                if (!ctx) return;
                new Chart(ctx, {
                    type: "bar",
                    data: {
                        labels,
                        datasets: [{ data, backgroundColor: color }],
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } },
                    },
                });
            },
            initDoughnutChart: (id, labels, data, colors, cutout = "0%") => {
                const ctx = document.getElementById(id);
                if (!ctx) return;
                new Chart(ctx, {
                    type: "doughnut",
                    data: {
                        labels,
                        datasets: [{ data, backgroundColor: colors }],
                    },
                    options: {
                        responsive: true,
                        cutout,
                        plugins: { legend: { display: false } },
                    },
                });
            },
        };
    }
});
