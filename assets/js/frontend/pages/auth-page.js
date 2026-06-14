(function (window, document) {
    "use strict";

    function boot(fn) {
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", fn);
            return;
        }
        fn();
    }

    function getAlert() {
        return document.getElementById("authAlert");
    }

    function showAlert(message, type) {
        var alertEl = getAlert();
        if (!alertEl) {
            return;
        }

        alertEl.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning");
        alertEl.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : "alert-danger");
        alertEl.textContent = message || "Có lỗi xảy ra.";
    }

    function hideAlert() {
        var alertEl = getAlert();
        if (!alertEl) {
            return;
        }

        alertEl.classList.add("d-none");
        alertEl.textContent = "";
    }

    function setBusy(button, busy) {
        if (!button) {
            return;
        }

        var spinner = button.querySelector(".spinner-border");
        button.disabled = busy;
        if (spinner) {
            spinner.classList.toggle("d-none", !busy);
        }
    }

    function safeRedirect(target) {
        if (typeof target !== "string" || target === "") {
            return "/";
        }

        return target.charAt(0) === "/" && target.indexOf("//") !== 0 ? target : "/";
    }

    function wirePasswordToggles() {
        document.querySelectorAll("[data-toggle-password]").forEach(function (button) {
            button.addEventListener("click", function () {
                var selector = button.getAttribute("data-toggle-password");
                var field = selector ? document.querySelector(selector) : null;
                if (!field) {
                    return;
                }

                field.type = field.type === "password" ? "text" : "password";
                var icon = button.querySelector("i");
                if (icon) {
                    icon.classList.toggle("fa-eye");
                    icon.classList.toggle("fa-eye-slash");
                }
            });
        });
    }

    function bootstrapAuthRedirect() {
        if (!window.ApiClient) {
            return;
        }

        if (!window.ApiClient.hasValidToken()) {
            if (window.ApiClient.getToken()) {
                window.ApiClient.clearToken();
            }
            return;
        }

        window.location.href = "/Home";
    }

    async function loginHandler(event) {
        event.preventDefault();
        hideAlert();

        var form = event.currentTarget;
        var button = document.getElementById("loginSubmitBtn");
        var data = new FormData(form);

        setBusy(button, true);

        try {
            var response = await window.ApiClient.post("/auth/login", {
                email: String(data.get("email") || "").trim(),
                password: String(data.get("password") || ""),
                remember_me: !!data.get("remember_me"),
            });

            if (!response || !response.data || !response.data.token) {
                throw new Error("Không nhận được token đăng nhập.");
            }

            window.ApiClient.setToken(response.data.token, response.data.user || null);
            if (window.AppUI) {
                window.AppUI.toast(response.message || "Đăng nhập thành công.", "success");
            }

            var redirect = safeRedirect(String(data.get("redirect") || "/"));
            window.location.href = redirect;
        } catch (error) {
            showAlert(error && error.message ? error.message : "Đăng nhập thất bại.", "danger");
        } finally {
            setBusy(button, false);
        }
    }

    async function registerHandler(event) {
        event.preventDefault();
        hideAlert();

        var form = event.currentTarget;
        var button = document.getElementById("registerSubmitBtn");
        var data = new FormData(form);

        setBusy(button, true);

        try {
            var response = await window.ApiClient.post("/auth/register", {
                full_name: String(data.get("full_name") || "").trim(),
                email: String(data.get("email") || "").trim(),
                password: String(data.get("password") || ""),
                confirm_password: String(data.get("confirm_password") || ""),
                phone: String(data.get("phone") || "").trim(),
                address: String(data.get("address") || "").trim(),
            });

            if (window.AppUI) {
                window.AppUI.toast(response.message || "Đăng ký thành công. Vui lòng đăng nhập.", "success");
            }
            window.setTimeout(function () {
                window.location.href = "/auth/login";
            }, 800);
        } catch (error) {
            var message = error && error.payload && error.payload.errors
                ? Object.values(error.payload.errors).join(" | ")
                : (error && error.message ? error.message : "Đăng ký thất bại.");
            showAlert(message, "danger");
        } finally {
            setBusy(button, false);
        }
    }

    boot(function () {
        wirePasswordToggles();
        bootstrapAuthRedirect();

        var loginForm = document.getElementById("loginForm");
        if (loginForm) {
            loginForm.addEventListener("submit", loginHandler);
        }

        var registerForm = document.getElementById("registerForm");
        if (registerForm) {
            registerForm.addEventListener("submit", registerHandler);
        }
    });
})(window, document);
