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
        var alertEl = getEl("productDetailAlert");
        if (!alertEl) {
            return;
        }

        alertEl.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning", "alert-info");
        alertEl.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : type === "info" ? "alert-info" : "alert-danger");
        alertEl.textContent = message || "";
    }

    function setLoading(isLoading) {
        var loading = getEl("productDetailLoading");
        var content = getEl("productDetailContent");
        if (loading) {
            loading.classList.toggle("d-none", !isLoading);
        }
        if (content && isLoading) {
            content.classList.add("d-none");
        }
    }

    function getProductId() {
        var match = window.location.pathname.match(/\/Product\/show\/(\d+)/i);
        return match ? Number(match[1]) : 0;
    }

    function renderProduct(product) {
        var content = getEl("productDetailContent");
        var imageEl = getEl("productDetailImage");
        var placeholder = getEl("productDetailPlaceholder");

        if (!product || !content) {
            return;
        }

        getEl("productDetailCategory").textContent = product.category_name || "Chưa phân loại";
        getEl("productDetailName").textContent = product.name || "";
        getEl("productDetailPrice").textContent = window.AppUI.formatCurrency(product.price || 0);
        getEl("productDetailDesc").textContent = product.description || "";
        getEl("productDetailId").textContent = "#" + String(product.id || 0);

        if (product.image) {
            var image = "/" + String(product.image).replace(/^\/+/, "");
            imageEl.src = image;
            imageEl.alt = product.name || "";
            imageEl.classList.remove("d-none");
            placeholder.classList.add("d-none");
        } else {
            imageEl.removeAttribute("src");
            imageEl.classList.add("d-none");
            placeholder.classList.remove("d-none");
        }

        content.classList.remove("d-none");
    }

    async function loadProduct() {
        var productId = getProductId();
        if (!productId) {
            showAlert("Không tìm thấy sản phẩm.", "danger");
            return;
        }

        setLoading(true);
        try {
            var response = await window.ApiClient.get("/products/" + encodeURIComponent(productId));
            var product = response && response.data ? response.data : null;
            if (!product) {
                throw new Error("Không tìm thấy sản phẩm.");
            }
            renderProduct(product);
        } catch (error) {
            showAlert(error && error.message ? error.message : "Không thể tải sản phẩm.", "danger");
        } finally {
            setLoading(false);
        }
    }

    async function addToCart() {
        var productId = getProductId();
        if (!window.ApiClient.hasValidToken()) {
            if (window.ApiClient.getToken()) {
                window.ApiClient.clearToken();
            }
            window.location.href = "/auth/login";
            return;
        }

        try {
            var response = await window.ApiClient.post("/cart/add", {
                product_id: productId,
                quantity: 1,
            });

            var badge = getEl("cartQtyBadge");
            if (badge && response && response.data && typeof response.data.total_qty !== "undefined") {
                badge.textContent = String(response.data.total_qty);
            }

            window.AppUI.toast(response.message || "Đã thêm vào giỏ hàng.", "success");
        } catch (error) {
            window.AppUI.toast(error && error.message ? error.message : "Không thể thêm vào giỏ hàng.", "error");
        }
    }

    boot(function () {
        var button = getEl("productAddToCartBtn");
        if (button) {
            button.addEventListener("click", addToCart);
        }

        loadProduct();
    });
})(window, document);
