document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    // ===========================
    // 1️⃣ Navigation active toggle
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
    // 2️⃣ Bootstrap form validation
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
        const slideWidth = slider.querySelector(".dash-testimonial-card")
            ? slider.querySelector(".dash-testimonial-card").offsetWidth + 30 // 30 = gap
            : 320;

        nextBtn.addEventListener("click", () => {
            if (scrollAmount < slider.scrollWidth - slider.clientWidth) {
                scrollAmount += slideWidth;
                slider.scrollTo({ left: scrollAmount, behavior: "smooth" });
            }
        });

        prevBtn.addEventListener("click", () => {
            if (scrollAmount > 0) {
                scrollAmount -= slideWidth;
                slider.scrollTo({ left: scrollAmount, behavior: "smooth" });
            }
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
});
