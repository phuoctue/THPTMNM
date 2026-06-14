(function (window, document) {
    "use strict";

    function getEl(id) {
        return document.getElementById(id);
    }

    function showAlert(message, type) {
        var el = getEl("orderSuccessAlert");
        if (!el) {
            return;
        }

        el.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning", "alert-info");
        el.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : type === "info" ? "alert-info" : "alert-danger");
        el.textContent = message || "";
    }

    function setLoading(isLoading) {
        var loading = getEl("orderSuccessLoading");
        var content = getEl("orderSuccessContent");
        if (loading) {
            loading.classList.toggle("d-none", !isLoading);
        }
        if (content && isLoading) {
            content.classList.add("d-none");
        }
    }

    function getOrderId() {
        var match = window.location.search.match(/[?&]order_id=(\d+)/);
        return match ? Number(match[1]) : 0;
    }

    function renderItems(items) {
        var wrap = getEl("orderSuccessItems");
        if (!wrap) {
            return;
        }

        wrap.innerHTML = items.map(function (item) {
            return [
                '<div class="success-item">',
                '  <div>',
                '    <div class="fw-bold">' + window.AppUI.escapeHtml(item.name || "") + "</div>",
                '    <div class="text-muted small">SL: ' + Number(item.quantity || 0) + "</div>",
                "  </div>",
                '  <div class="fw-bold">' + window.AppUI.formatCurrency((item.price || 0) * (item.quantity || 0)) + "</div>",
                "</div>",
            ].join("");
        }).join("");
    }

    async function loadOrder() {
        if (!window.ApiClient.hasValidToken()) {
            if (window.ApiClient.getToken()) {
                window.ApiClient.clearToken();
            }
            window.location.href = "/auth/login";
            return;
        }

        var orderId = getOrderId();
        if (!orderId) {
            showAlert("Không tìm thấy mã đơn hàng.", "danger");
            return;
        }

        setLoading(true);
        try {
            var response = await window.ApiClient.get("/orders/" + encodeURIComponent(orderId));
            var payload = response && response.data ? response.data : null;
            if (!payload || !payload.order) {
                throw new Error("Không tìm thấy đơn hàng.");
            }

            var order = payload.order;
            var items = payload.items || [];
            getEl("orderSuccessId").textContent = "#" + String(order.id || orderId);
            getEl("orderSuccessTotal").textContent = window.AppUI.formatCurrency(order.total_price || 0);
            getEl("orderSuccessPayment").textContent = String(order.payment_method || "cod").toUpperCase();
            renderItems(items);

            var content = getEl("orderSuccessContent");
            if (content) {
                content.classList.remove("d-none");
            }
        } catch (error) {
            showAlert(error && error.message ? error.message : "Không thể tải đơn hàng.", "danger");
        } finally {
            setLoading(false);
        }
    }

    document.addEventListener("DOMContentLoaded", loadOrder);
})(window, document);
