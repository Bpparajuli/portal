const container = document.querySelector(".container");
const registerBtn = document.querySelector(".register-btn");
const loginBtn = document.querySelector(".login-btn");

document.addEventListener("DOMContentLoaded", () => {
    const container = document.querySelector(".container");
    const registerBtn = document.querySelector(".register-btn");
    const loginBtn = document.querySelector(".login-btn");

    if (registerBtn) {
        registerBtn.addEventListener("click", () =>
            container.classList.add("active")
        );
    }

    if (loginBtn) {
        loginBtn.addEventListener("click", () =>
            container.classList.remove("active")
        );
    }
});
