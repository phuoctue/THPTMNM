(function (window, document) {
    "use strict";

    function boot(fn) {
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", fn);
            return;
        }
        fn();
    }

    function getEl(id) {
        return document.getElementById(id);
    }

    function showAlert(message, type) {
        var alertEl = getEl("profilePasswordAlert");
        if (!alertEl) {
            return;
        }

        alertEl.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning", "alert-info");
        alertEl.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : type === "info" ? "alert-info" : "alert-danger");
        alertEl.textContent = message || "";
    }

    function setBusy(isBusy) {
        var btn = getEl("profilePasswordSubmitBtn");
        var spinner = btn ? btn.querySelector(".spinner-border") : null;
        if (btn) {
            btn.disabled = isBusy;
        }
        if (spinner) {
            spinner.classList.toggle("d-none", !isBusy);
        }
    }

    function toggleField(id) {
        var field = getEl(id);
        if (!field) {
            return;
        }
        field.type = field.type === "password" ? "text" : "password";
    }

    async function submitPassword(event) {
        event.preventDefault();

        if (!window.ApiClient.hasValidToken()) {
            window.location.href = "/auth/login";
            return;
        }

        var form = event.currentTarget;
        var data = new FormData(form);
        setBusy(true);

        try {
            var response = await window.ApiClient.post("/profile/changePassword", {
                old_password: String(data.get("old_password") || ""),
                new_password: String(data.get("new_password") || ""),
                confirm_password: String(data.get("confirm_password") || ""),
            });

            if (window.AppUI) {
                window.AppUI.toast(response.message || "Đã đổi mật khẩu.", "success");
            }

            window.ApiClient.clearToken();
            window.setTimeout(function () {
                window.location.href = "/auth/login";
            }, 800);
        } catch (error) {
            var message = error && error.payload && error.payload.errors
                ? Object.values(error.payload.errors).join(" | ")
                : (error && error.message ? error.message : "Đổi mật khẩu thất bại.");
            showAlert(message, "danger");
        } finally {
            setBusy(false);
        }
    }

    boot(function () {
        var form = getEl("profilePasswordForm");
        if (form) {
            form.addEventListener("submit", submitPassword);
        }

        document.querySelectorAll("#profilePasswordForm [id]").forEach(function (input) {
            input.addEventListener("dblclick", function () {
                toggleField(input.id);
            });
        });
    });
})(window, document);
