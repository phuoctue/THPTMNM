(function (window, document) {
    "use strict";

    function getEl(id) {
        return document.getElementById(id);
    }

    function showAlert(message, type) {
        var el = getEl("productFormAlert");
        if (!el) {
            return;
        }

        el.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning", "alert-info");
        el.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : type === "info" ? "alert-info" : "alert-danger");
        el.textContent = message || "";
    }

    function setBusy(isBusy) {
        var btn = getEl("adminProductSubmitBtn");
        var spinner = btn ? btn.querySelector(".spinner-border") : null;
        if (btn) {
            btn.disabled = isBusy;
        }
        if (spinner) {
            spinner.classList.toggle("d-none", !isBusy);
        }
    }

    function getProductId() {
        var form = getEl("adminProductForm");
        return form ? Number(form.dataset.productId || 0) : 0;
    }

    async function loadCategories() {
        var select = getEl("adminProductCategorySelect");
        if (!select) {
            return;
        }

        try {
            var response = await window.ApiClient.get("/categories");
            var categories = response && response.data ? response.data : [];
            select.innerHTML = '<option value="">Chọn danh mục</option>' + categories.map(function (category) {
                return '<option value="' + category.id + '">' + window.AppUI.escapeHtml(category.name || "") + "</option>";
            }).join("");
        } catch (error) {
            showAlert("Không thể tải danh mục.", "danger");
        }
    }

    async function loadProduct() {
        var productId = getProductId();
        if (!productId) {
            return;
        }

        try {
            var response = await window.ApiClient.get("/products/" + encodeURIComponent(productId));
            var product = response && response.data ? response.data : null;
            if (!product) {
                throw new Error("Không tìm thấy sản phẩm.");
            }

            var form = getEl("adminProductForm");
            form.name.value = product.name || "";
            form.description.value = product.description || "";
            form.price.value = product.price || 0;
            form.category_id.value = product.category_id || "";

            var preview = getEl("productImagePreview");
            if (product.image && preview) {
                preview.src = "/" + String(product.image).replace(/^\/+/, "");
                preview.style.display = "block";
            }
        } catch (error) {
            showAlert(error && error.message ? error.message : "Không thể tải sản phẩm.", "danger");
        }
    }

    async function submitForm(event) {
        event.preventDefault();
        showAlert("", "info");
        setBusy(true);

        var form = event.currentTarget;
        var productId = getProductId();
        var payload = new FormData(form);
        if (productId > 0) {
            payload.append("_method", "PUT");
        }

        try {
            var endpoint = productId > 0 ? "/products/" + encodeURIComponent(productId) : "/products";
            var response = await window.ApiClient.request(endpoint, {
                method: "POST",
                body: payload,
            });

            window.AppUI.toast(response.message || "Đã lưu sản phẩm.", "success");
            window.location.href = "/Product";
        } catch (error) {
            var message = error && error.payload && error.payload.errors
                ? Object.values(error.payload.errors).join(" | ")
                : (error && error.message ? error.message : "Không thể lưu sản phẩm.");
            showAlert(message, "danger");
        } finally {
            setBusy(false);
        }
    }

    function bindPreview() {
        var input = getEl("adminProductImageInput");
        var preview = getEl("productImagePreview");
        if (!input || !preview) {
            return;
        }

        input.addEventListener("change", function () {
            var file = input.files && input.files[0];
            if (!file) {
                preview.style.display = "none";
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = "block";
            };
            reader.readAsDataURL(file);
        });
    }

    document.addEventListener("DOMContentLoaded", async function () {
        if (!window.ApiClient.hasValidToken()) {
            if (window.ApiClient.getToken()) {
                window.ApiClient.clearToken();
            }
            window.location.href = "/auth/login";
            return;
        }

        await loadCategories();
        await loadProduct();
        bindPreview();

        var form = getEl("adminProductForm");
        if (form) {
            form.addEventListener("submit", submitForm);
        }
    });
})(window, document);
