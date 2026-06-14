(function (window, document) {
    "use strict";

    function getEl(id) {
        return document.getElementById(id);
    }

    function showAlert(message, type) {
        var el = getEl("categoryFormAlert");
        if (!el) {
            return;
        }

        el.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning", "alert-info");
        el.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : type === "info" ? "alert-info" : "alert-danger");
        el.textContent = message || "";
    }

    function setBusy(isBusy) {
        var btn = getEl("adminCategorySubmitBtn");
        var spinner = btn ? btn.querySelector(".spinner-border") : null;
        if (btn) {
            btn.disabled = isBusy;
        }
        if (spinner) {
            spinner.classList.toggle("d-none", !isBusy);
        }
    }

    function getCategoryId() {
        var form = getEl("adminCategoryForm");
        return form ? Number(form.dataset.categoryId || 0) : 0;
    }

    async function loadCategory() {
        var categoryId = getCategoryId();
        if (!categoryId) {
            return;
        }

        try {
            var response = await window.ApiClient.get("/categories/" + encodeURIComponent(categoryId));
            var category = response && response.data ? response.data : null;
            if (!category) {
                throw new Error("Không tìm thấy danh mục.");
            }

            var form = getEl("adminCategoryForm");
            form.name.value = category.name || "";
            form.description.value = category.description || "";
        } catch (error) {
            showAlert(error && error.message ? error.message : "Không thể tải danh mục.", "danger");
        }
    }

    async function submitForm(event) {
        event.preventDefault();
        setBusy(true);

        var form = event.currentTarget;
        var categoryId = getCategoryId();
        var payload = new FormData(form);
        if (categoryId > 0) {
            payload.append("_method", "PUT");
        }

        try {
            var endpoint = categoryId > 0 ? "/categories/" + encodeURIComponent(categoryId) : "/categories";
            var response = await window.ApiClient.request(endpoint, {
                method: "POST",
                body: payload,
            });

            window.AppUI.toast(response.message || "Đã lưu danh mục.", "success");
            window.location.href = "/Category";
        } catch (error) {
            var message = error && error.payload && error.payload.errors
                ? Object.values(error.payload.errors).join(" | ")
                : (error && error.message ? error.message : "Không thể lưu danh mục.");
            showAlert(message, "danger");
        } finally {
            setBusy(false);
        }
    }

    document.addEventListener("DOMContentLoaded", async function () {
        if (!window.ApiClient.hasValidToken()) {
            if (window.ApiClient.getToken()) {
                window.ApiClient.clearToken();
            }
            window.location.href = "/auth/login";
            return;
        }

        await loadCategory();

        var form = getEl("adminCategoryForm");
        if (form) {
            form.addEventListener("submit", submitForm);
        }
    });
})(window, document);
