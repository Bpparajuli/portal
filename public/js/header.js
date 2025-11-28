document.addEventListener("DOMContentLoaded", () => {
    // ===== Mobile menu toggle =====
    const menuToggle = document.querySelector(".menu-toggle");
    const headerNav = document.querySelector(".header-nav");

    if (menuToggle) {
        menuToggle.addEventListener("click", () => {
            const isExpanded =
                menuToggle.getAttribute("aria-expanded") === "true";
            menuToggle.setAttribute("aria-expanded", !isExpanded);
            headerNav.classList.toggle("is-active");
        });
    }

    // ===== Notification dropdowns =====
    const notifToggles = document.querySelectorAll(".notification-toggle");

    notifToggles.forEach((toggle) => {
        toggle.addEventListener("click", (e) => {
            e.stopPropagation(); // Prevent immediate close by document click

            const isExpanded = toggle.getAttribute("aria-expanded") === "true";

            // Close all other dropdowns
            notifToggles.forEach((other) => {
                if (other !== toggle) {
                    other.setAttribute("aria-expanded", "false");
                }
            });

            // Toggle current dropdown
            toggle.setAttribute("aria-expanded", !isExpanded);
        });
    });

    // ===== Close dropdowns when clicking outside =====
    document.addEventListener("click", () => {
        notifToggles.forEach((toggle) => {
            toggle.setAttribute("aria-expanded", "false");
        });
    });

    // ===== Optional: close dropdown on ESC key =====
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            notifToggles.forEach((toggle) => {
                toggle.setAttribute("aria-expanded", "false");
            });
        }
    });
});
