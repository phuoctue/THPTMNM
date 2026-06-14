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

    function setLoading(isLoading) {
        var loading = getEl("adminSettingsLoading");
        var content = getEl("adminSettingsContent");
        if (loading) {
            loading.classList.toggle("d-none", !isLoading);
        }
        if (content && isLoading) {
            content.classList.add("d-none");
        }
    }

    function showAlert(message, type) {
        var alertEl = getEl("adminSettingsAlert");
        if (!alertEl) {
            return;
        }

        alertEl.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning", "alert-info");
        alertEl.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : type === "info" ? "alert-info" : "alert-danger");
        alertEl.textContent = message || "";
    }

    function setBusy(isBusy) {
        var btn = getEl("adminSettingsSubmitBtn");
        var spinner = btn ? btn.querySelector(".spinner-border") : null;
        if (btn) {
            btn.disabled = isBusy;
        }
        if (spinner) {
            spinner.classList.toggle("d-none", !isBusy);
        }
    }

    function renderSettings(settings) {
        var form = getEl("adminSettingsForm");
        if (!form) {
            return;
        }

        Object.keys(settings || {}).forEach(function (key) {
            if (form[key]) {
                form[key].value = settings[key] == null ? "" : settings[key];
            }
        });
    }

    async function loadSettings() {
        if (!window.ApiClient.hasValidToken()) {
            window.location.href = "/auth/login";
            return;
        }

        setLoading(true);
        try {
            var response = await window.ApiClient.get("/settings");
            var settings = response && response.data ? response.data : {};
            renderSettings(settings);
            getEl("adminSettingsContent").classList.remove("d-none");
        } catch (error) {
            showAlert(error && error.message ? error.message : "Không thể tải cấu hình.", "danger");
        } finally {
            setLoading(false);
        }
    }

    async function submitSettings(event) {
        event.preventDefault();

        var form = event.currentTarget;
        setBusy(true);

        try {
            var response = await window.ApiClient.put("/settings", {
                APP_URL: String(form.APP_URL.value || "").trim(),
                MAIL_MAILER: String(form.MAIL_MAILER.value || "").trim(),
                MAIL_HOST: String(form.MAIL_HOST.value || "").trim(),
                MAIL_PORT: String(form.MAIL_PORT.value || "").trim(),
                MAIL_USERNAME: String(form.MAIL_USERNAME.value || "").trim(),
                MAIL_PASSWORD: String(form.MAIL_PASSWORD.value || ""),
                MAIL_ENCRYPTION: String(form.MAIL_ENCRYPTION.value || "").trim(),
                MAIL_FROM_ADDRESS: String(form.MAIL_FROM_ADDRESS.value || "").trim(),
                MAIL_FROM_NAME: String(form.MAIL_FROM_NAME.value || "").trim(),
            });

            window.AppUI.toast(response.message || "Đã lưu cấu hình.", "success");
            renderSettings(response.data || {});
        } catch (error) {
            var message = error && error.payload && error.payload.errors
                ? Object.values(error.payload.errors).join(" | ")
                : (error && error.message ? error.message : "Lưu cấu hình thất bại.");
            showAlert(message, "danger");
        } finally {
            setBusy(false);
        }
    }

    boot(function () {
        var form = getEl("adminSettingsForm");
        if (form) {
            form.addEventListener("submit", submitSettings);
        }

        loadSettings();
    });
})(window, document);
