document.addEventListener("DOMContentLoaded", function () {
    const dropdownToggles = document.querySelectorAll(".notification-toggle");

    dropdownToggles.forEach((toggle) => {
        toggle.addEventListener("click", function (event) {
            event.preventDefault();
            const menuId = this.getAttribute("data-dropdown-toggle");
            const menu = document.getElementById(menuId);

            // Hide other open dropdowns
            document
                .querySelectorAll(".notification-menu.show")
                .forEach((openMenu) => {
                    if (openMenu.id !== menuId) {
                        openMenu.classList.remove("show");
                    }
                });

            menu.classList.toggle("show");
        });
    });

    // Close the dropdown if the user clicks outside of it
    window.addEventListener("click", function (event) {
        if (!event.target.closest(".notification-dropdown")) {
            document
                .querySelectorAll(".notification-menu.show")
                .forEach((menu) => {
                    menu.classList.remove("show");
                });
        }
    });
});
