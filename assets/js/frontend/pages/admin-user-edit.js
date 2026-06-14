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

    function getUserId() {
        var match = window.location.pathname.match(/\/admin\/users\/edit\/(\d+)/i);
        return match ? Number(match[1]) : 0;
    }

    function setLoading(isLoading) {
        var loading = getEl("adminUserEditLoading");
        var content = getEl("adminUserEditContent");
        if (loading) {
            loading.classList.toggle("d-none", !isLoading);
        }
        if (content && isLoading) {
            content.classList.add("d-none");
        }
    }

    function showAlert(message, type) {
        var alertEl = getEl("adminUserEditAlert");
        if (!alertEl) {
            return;
        }

        alertEl.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning", "alert-info");
        alertEl.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : type === "info" ? "alert-info" : "alert-danger");
        alertEl.textContent = message || "";
    }

    function setBusy(isBusy) {
        var btn = getEl("adminUserEditSubmitBtn");
        var spinner = btn ? btn.querySelector(".spinner-border") : null;
        if (btn) {
            btn.disabled = isBusy;
        }
        if (spinner) {
            spinner.classList.toggle("d-none", !isBusy);
        }
    }

    function renderUser(user) {
        var form = getEl("adminUserEditForm");
        if (!form) {
            return;
        }

        form.full_name.value = user.full_name || "";
        form.email.value = user.email || "";
        form.phone.value = user.phone || "";
        form.address.value = user.address || "";
        form.role.value = user.role || "customer";
        form.status.value = user.status || "active";

        getEl("adminUserName").textContent = user.full_name || "-";
        getEl("adminUserEmailText").textContent = user.email || "-";

        var avatar = getEl("adminUserAvatar");
        var fallback = getEl("adminUserAvatarFallback");
        var initial = (user.full_name || "U").trim().charAt(0).toUpperCase();
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

    async function loadUser() {
        var id = getUserId();
        if (!id) {
            showAlert("Không tìm thấy người dùng.", "danger");
            return;
        }

        if (!window.ApiClient.hasValidToken()) {
            window.location.href = "/auth/login";
            return;
        }

        setLoading(true);
        try {
            var response = await window.ApiClient.get("/users/" + encodeURIComponent(id));
            var user = response && response.data ? response.data : null;
            if (!user) {
                throw new Error("Không tải được người dùng.");
            }
            renderUser(user);
            getEl("adminUserEditContent").classList.remove("d-none");
        } catch (error) {
            showAlert(error && error.message ? error.message : "Không thể tải thông tin người dùng.", "danger");
        } finally {
            setLoading(false);
        }
    }

    async function submitUser(event) {
        event.preventDefault();

        var id = getUserId();
        var form = event.currentTarget;
        setBusy(true);

        try {
            var response = await window.ApiClient.put("/users/" + encodeURIComponent(id), {
                full_name: String(form.full_name.value || "").trim(),
                email: String(form.email.value || "").trim(),
                phone: String(form.phone.value || "").trim(),
                address: String(form.address.value || "").trim(),
                role: String(form.role.value || "customer"),
                status: String(form.status.value || "active"),
            });

            window.AppUI.toast(response.message || "Đã cập nhật người dùng.", "success");
            await loadUser();
        } catch (error) {
            var message = error && error.payload && error.payload.errors
                ? Object.values(error.payload.errors).join(" | ")
                : (error && error.message ? error.message : "Cập nhật người dùng thất bại.");
            showAlert(message, "danger");
        } finally {
            setBusy(false);
        }
    }

    async function deleteUser() {
        var id = getUserId();
        if (!window.confirm("Xóa người dùng này?")) {
            return;
        }

        try {
            var response = await window.ApiClient.delete("/users/" + encodeURIComponent(id));
            window.AppUI.toast(response.message || "Đã xóa người dùng.", "success");
            window.location.href = "/admin/users";
        } catch (error) {
            showAlert(error && error.message ? error.message : "Xóa người dùng thất bại.", "danger");
        }
    }

    boot(function () {
        var form = getEl("adminUserEditForm");
        if (form) {
            form.addEventListener("submit", submitUser);
        }

        var deleteBtn = getEl("adminUserDeleteBtn");
        if (deleteBtn) {
            deleteBtn.addEventListener("click", deleteUser);
        }

        loadUser();
    });
})(window, document);
