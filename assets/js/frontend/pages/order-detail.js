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
        var alertEl = getEl("orderDetailAlert");
        if (!alertEl) {
            return;
        }

        alertEl.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning", "alert-info");
        alertEl.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : type === "info" ? "alert-info" : "alert-danger");
        alertEl.textContent = message || "";
    }

    function setLoading(isLoading) {
        var loading = getEl("orderDetailLoading");
        var content = getEl("orderDetailContent");
        if (loading) {
            loading.classList.toggle("d-none", !isLoading);
        }
        if (content && isLoading) {
            content.classList.add("d-none");
        }
    }

    function getOrderId() {
        var match = window.location.pathname.match(/\/Cart\/orderDetail\/(\d+)/i);
        if (match) {
            return Number(match[1]);
        }

        match = window.location.search.match(/[?&]order_id=(\d+)/i);
        return match ? Number(match[1]) : 0;
    }

    function getOrderBadgeClass(status) {
        switch (status) {
            case "confirmed":
                return "text-bg-info";
            case "shipping":
                return "text-bg-primary";
            case "done":
                return "text-bg-success";
            case "cancelled":
                return "text-bg-danger";
            default:
                return "text-bg-secondary";
        }
    }

    function getPaymentBadgeClass(status) {
        return status === "paid" ? "text-bg-success" : "text-bg-warning";
    }

    function parseStoredUser() {
        try {
            return JSON.parse(localStorage.getItem("my_store_user") || "null");
        } catch (error) {
            return null;
        }
    }

    function isAdminUser() {
        var user = parseStoredUser();
        return !!user && String(user.role || "") === "admin";
    }

    function renderOrder(order, items) {
        getEl("orderDetailId").textContent = "#" + String(order.id || 0);
        getEl("orderDetailCustomerName").textContent = order.customer_name || "";
        getEl("orderDetailPhone").textContent = order.customer_phone || "";
        getEl("orderDetailEmail").textContent = order.customer_email || "-";
        getEl("orderDetailAddress").textContent = order.customer_address || "";
        getEl("orderDetailStatusBadge").className = "badge fs-6 " + getOrderBadgeClass(String(order.status || "pending"));
        getEl("orderDetailStatusBadge").textContent = String(order.status || "pending");
        getEl("orderDetailPaymentBadge").className = "badge " + getPaymentBadgeClass(String(order.payment_status || "unpaid"));
        getEl("orderDetailPaymentBadge").textContent = String(order.payment_method || "cod").toUpperCase() + " / " + String(order.payment_status || "unpaid");
        getEl("orderDetailCreatedAt").textContent = order.created_at || "";
        getEl("orderDetailTotal").textContent = window.AppUI.formatCurrency(order.total_price || 0);
        getEl("orderDetailItemsCount").textContent = String(items.length);

        var statusSelect = getEl("orderDetailStatus");
        if (statusSelect) {
            statusSelect.value = String(order.status || "pending");
        }

        var paymentSelect = getEl("orderDetailPaymentStatus");
        if (paymentSelect) {
            paymentSelect.value = String(order.payment_status || "unpaid");
        }

        var adminBox = getEl("orderDetailAdminBox");
        if (adminBox) {
            adminBox.classList.toggle("d-none", !isAdminUser());
        }
    }

    function renderItems(items) {
        var body = getEl("orderDetailItems");
        if (!body) {
            return;
        }

        if (!items.length) {
            body.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Không có sản phẩm nào trong đơn.</td></tr>';
            return;
        }

        body.innerHTML = items.map(function (item) {
            var image = item.display_image || item.image || item.product_image || "";
            image = image ? "/" + String(image).replace(/^\/+/, "") : "";
            var lineTotal = Number(item.price || 0) * Number(item.quantity || 0);

            return [
                "<tr>",
                '  <td class="ps-3">',
                '    <div class="d-flex align-items-center gap-3">',
                image
                    ? '<div class="rounded-3 bg-light d-flex align-items-center justify-content-center overflow-hidden" style="width: 56px; height: 56px;"><img src="' + image + '" class="w-100 h-100 object-fit-cover" alt="' + window.AppUI.escapeHtml(item.name || "") + '"></div>'
                    : '<div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted" style="width: 56px; height: 56px;"><i class="fas fa-image"></i></div>',
                '      <div>',
                '        <div class="fw-semibold">' + window.AppUI.escapeHtml(item.name || "") + "</div>",
                '        <small class="text-muted">Mã SP: #' + window.AppUI.escapeHtml(item.product_id || "") + "</small>",
                "      </div>",
                "    </div>",
                "  </td>",
                '  <td class="fw-semibold">' + window.AppUI.formatCurrency(item.price || 0) + "</td>",
                '  <td><span class="badge text-bg-secondary">' + Number(item.quantity || 0) + "</span></td>",
                '  <td class="text-end pe-3 fw-semibold text-success">' + window.AppUI.formatCurrency(lineTotal) + "</td>",
                "</tr>",
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
            showAlert("Không tìm thấy đơn hàng.", "danger");
            return;
        }

        setLoading(true);
        try {
            var response = await window.ApiClient.get("/orders/" + encodeURIComponent(orderId));
            var payload = response && response.data ? response.data : null;
            if (!payload || !payload.order) {
                throw new Error("Không tìm thấy đơn hàng.");
            }

            renderOrder(payload.order, payload.items || []);
            renderItems(payload.items || []);

            var content = getEl("orderDetailContent");
            if (content) {
                content.classList.remove("d-none");
            }
        } catch (error) {
            showAlert(error && error.message ? error.message : "Không thể tải chi tiết đơn hàng.", "danger");
        } finally {
            setLoading(false);
        }
    }

    async function saveOrder(event) {
        event.preventDefault();

        if (!isAdminUser()) {
            showAlert("Bạn không có quyền cập nhật đơn hàng.", "danger");
            return;
        }

        var orderId = getOrderId();
        var data = new FormData(event.currentTarget);

        try {
            var response = await window.ApiClient.put("/orders/" + encodeURIComponent(orderId), {
                status: String(data.get("status") || "pending"),
                payment_status: String(data.get("payment_status") || ""),
            });

            if (window.AppUI) {
                window.AppUI.toast(response.message || "Đã cập nhật đơn hàng.", "success");
            }
            await loadOrder();
        } catch (error) {
            showAlert(error && error.message ? error.message : "Cập nhật đơn hàng thất bại.", "danger");
        }
    }

    boot(function () {
        var form = getEl("orderDetailForm");
        if (form) {
            form.addEventListener("submit", saveOrder);
        }

        loadOrder();
    });
})(window, document);
