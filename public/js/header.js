// document.addEventListener("DOMContentLoaded", () => {
//     const menuToggle = document.querySelector(".menu-toggle");
//     const headerNav = document.querySelector(".header-nav");

//     if (menuToggle && headerNav) {
//         menuToggle.addEventListener("click", () => {
//             headerNav.classList.toggle("is-active");
//             const isExpanded =
//                 menuToggle.getAttribute("aria-expanded") === "true" || false;
//             menuToggle.setAttribute("aria-expanded", !isExpanded);
//         });
//     }

//     const notificationToggle = document.querySelector(".notification-toggle");
//     if (notificationToggle) {
//         notificationToggle.addEventListener("click", (e) => {
//             e.stopPropagation();
//             const menu = document.querySelector(
//                 notificationToggle.getAttribute("aria-controls")
//             );
//             const isExpanded =
//                 notificationToggle.getAttribute("aria-expanded") === "true" ||
//                 false;
//             notificationToggle.setAttribute("aria-expanded", !isExpanded);
//             menu.style.display = isExpanded ? "none" : "block";
//         });

//         document.addEventListener("click", (e) => {
//             if (!notificationToggle.contains(e.target)) {
//                 notificationToggle.setAttribute("aria-expanded", "false");
//                 const menu = document.querySelector(
//                     notificationToggle.getAttribute("aria-controls")
//                 );
//                 menu.style.display = "none";
//             }
//         });
//     }
// });
document.addEventListener("DOMContentLoaded", () => {
    const menuToggle = document.querySelector(".menu-toggle");
    const headerNav = document.querySelector(".header-nav");
    const notifToggle = document.querySelector(".notification-toggle");

    menuToggle.addEventListener("click", () => {
        const isExpanded = menuToggle.getAttribute("aria-expanded") === "true";
        menuToggle.setAttribute("aria-expanded", !isExpanded);
        headerNav.classList.toggle("is-active");
    });

    // Basic notification toggle for desktop
    if (notifToggle) {
        notifToggle.addEventListener("click", () => {
            const isExpanded =
                notifToggle.getAttribute("aria-expanded") === "true";
            notifToggle.setAttribute("aria-expanded", !isExpanded);
        });
    }
});
