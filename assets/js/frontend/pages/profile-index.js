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
        var loading = getEl("profileLoading");
        var content = getEl("profileContent");
        if (loading) {
            loading.classList.toggle("d-none", !isLoading);
        }
        if (content && isLoading) {
            content.classList.add("d-none");
        }
    }

    function showAlert(message, type) {
        var alertEl = getEl("profileAlert");
        if (!alertEl) {
            return;
        }

        alertEl.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning", "alert-info");
        alertEl.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : type === "info" ? "alert-info" : "alert-danger");
        alertEl.textContent = message || "";
    }

    function renderProfile(user) {
        getEl("profileName").textContent = user.full_name || user.name || "-";
        getEl("profileEmail").textContent = user.email || "-";
        getEl("profileFullName").textContent = user.full_name || user.name || "-";
        getEl("profilePhone").textContent = user.phone || "-";
        getEl("profileRoleText").textContent = user.role === "admin" ? "Admin" : "Customer";
        getEl("profileCreatedAt").textContent = user.created_at || "-";
        getEl("profileAddress").textContent = user.address || "-";
        getEl("profileRoleBadge").textContent = user.role || "customer";
        getEl("profileVerifiedBadge").textContent = user.email_verified_at ? "Đã xác thực" : "Chưa xác thực";
        getEl("profileVerifiedBadge").className = "badge " + (user.email_verified_at ? "text-bg-success" : "text-bg-warning");

        var avatar = getEl("profileAvatar");
        var fallback = getEl("profileAvatarFallback");
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
            renderProfile(user);
            getEl("profileContent").classList.remove("d-none");
        } catch (error) {
            showAlert(error && error.message ? error.message : "Không thể tải hồ sơ.", "danger");
        } finally {
            setLoading(false);
        }
    }

    boot(loadProfile);
})(window, document);
