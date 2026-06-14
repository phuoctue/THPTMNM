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
        var loading = getEl("profileEditLoading");
        var content = getEl("profileEditContent");
        if (loading) {
            loading.classList.toggle("d-none", !isLoading);
        }
        if (content && isLoading) {
            content.classList.add("d-none");
        }
    }

    function showAlert(message, type) {
        var alertEl = getEl("profileEditAlert");
        if (!alertEl) {
            return;
        }

        alertEl.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning", "alert-info");
        alertEl.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : type === "info" ? "alert-info" : "alert-danger");
        alertEl.textContent = message || "";
    }

    function setBusy(isBusy) {
        var btn = getEl("profileEditSubmitBtn");
        var spinner = btn ? btn.querySelector(".spinner-border") : null;
        if (btn) {
            btn.disabled = isBusy;
        }
        if (spinner) {
            spinner.classList.toggle("d-none", !isBusy);
        }
    }

    function setFormValues(user) {
        var form = getEl("profileEditForm");
        if (!form) {
            return;
        }

        form.full_name.value = user.full_name || user.name || "";
        form.email.value = user.email || "";
        form.phone.value = user.phone || "";
        form.address.value = user.address || "";
        getEl("profileEditName").textContent = user.full_name || user.name || "-";
        getEl("profileEditEmail").textContent = user.email || "-";

        var avatar = getEl("profileEditAvatarPreview");
        var fallback = getEl("profileEditAvatarFallback");
        var initial = (user.full_name || user.name || "U").trim().charAt(0).toUpperCase();
        if (user.avatar) {
            avatar.src = "/" + String(user.avatar).replace(/^\/+/, "");
            avatar.classList.remove("d-none");
            fallback.classList.add("d-none");
        } else {
            fallback.textContent = initial;
            avatar.classList.add("d-none");
            fallback.classList.remove("d-none");
        }
    }

    async function loadProfile() {
        if (!window.ApiClient.hasValidToken()) {
            if (window.ApiClient.getToken()) {
                window.ApiClient.clearToken();
            }
            window.location.href = "/auth/login";
            return;
        }

        setLoading(true);
        try {
            var response = await window.ApiClient.get("/profile");
            var user = response && response.data ? response.data : null;
            if (!user) {
                throw new Error("Không tải được hồ sơ.");
            }
            setFormValues(user);
            getEl("profileEditContent").classList.remove("d-none");
        } catch (error) {
            showAlert(error && error.message ? error.message : "Không thể tải hồ sơ.", "danger");
        } finally {
            setLoading(false);
        }
    }

    async function submitProfile(event) {
        event.preventDefault();

        var form = event.currentTarget;
        var data = new FormData(form);

        setBusy(true);
        try {
            var response = await window.ApiClient.post("/profile/update", data);
            if (window.AppUI) {
                window.AppUI.toast(response.message || "Đã cập nhật hồ sơ.", "success");
            }
            if (response && response.data) {
                setFormValues(response.data);
            }
        } catch (error) {
            var message = error && error.payload && error.payload.errors
                ? Object.values(error.payload.errors).join(" | ")
                : (error && error.message ? error.message : "Cập nhật hồ sơ thất bại.");
            showAlert(message, "danger");
        } finally {
            setBusy(false);
        }
    }

    boot(function () {
        var form = getEl("profileEditForm");
        if (form) {
            form.addEventListener("submit", submitProfile);
        }

        loadProfile();
    });
})(window, document);
