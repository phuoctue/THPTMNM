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
        var alertEl = getEl("ordersAlert");
        if (!alertEl) {
            return;
        }

        alertEl.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning", "alert-info");
        alertEl.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : type === "info" ? "alert-info" : "alert-danger");
        alertEl.textContent = message || "";
    }

    function setLoading(isLoading) {
        var loading = getEl("ordersLoading");
        var content = getEl("ordersContent");
        if (loading) {
            loading.classList.toggle("d-none", !isLoading);
        }
        if (content && isLoading) {
            content.classList.add("d-none");
        }
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

    function normalizeItemsCount(order) {
        if (typeof order.item_count !== "undefined" && order.item_count !== null) {
            return Number(order.item_count) || 0;
        }
        if (Array.isArray(order.items)) {
            return order.items.length;
        }
        return 0;
    }

    function renderSummary(orders) {
        var countEl = getEl("ordersTotalCount");
        var revenueEl = getEl("ordersTotalRevenue");
        var statusEl = getEl("ordersLatestStatus");

        if (countEl) {
            countEl.textContent = String(orders.length);
        }
        if (revenueEl) {
            var revenue = orders.reduce(function (sum, order) {
                return sum + Number(order.total_price || 0);
            }, 0);
            revenueEl.textContent = window.AppUI.formatCurrency(revenue);
        }
        if (statusEl) {
            statusEl.textContent = orders.length ? String(orders[0].status || "pending") : "---";
        }
    }

    function renderOrders(orders) {
        var body = getEl("ordersTableBody");
        var empty = getEl("ordersEmptyState");
        var content = getEl("ordersContent");

        if (!body) {
            return;
        }

        if (!orders.length) {
            if (content) {
                content.classList.add("d-none");
            }
            if (empty) {
                empty.classList.remove("d-none");
            }
            body.innerHTML = "";
            return;
        }

        if (empty) {
            empty.classList.add("d-none");
        }
        if (content) {
            content.classList.remove("d-none");
        }

        body.innerHTML = orders.map(function (order) {
            var itemCount = normalizeItemsCount(order);
            var detailUrl = "/Cart/orderDetail/" + encodeURIComponent(order.id);
            var paymentMethod = String(order.payment_method || "cod").toUpperCase();
            var paymentStatus = String(order.payment_status || "unpaid");
            var orderStatus = String(order.status || "pending");

            return [
                "<tr>",
                '  <td class="ps-4 fw-semibold">#' + window.AppUI.escapeHtml(order.id || "") + "</td>",
                '  <td>',
                '    <div class="fw-semibold">' + window.AppUI.escapeHtml(order.customer_name || "") + "</div>",
                '    <small class="text-muted">' + window.AppUI.escapeHtml(order.customer_phone || "") + "</small>",
                '    <div class="small text-muted mt-1">' + itemCount + " sản phẩm</div>",
                "  </td>",
                '  <td>',
                '    <span class="badge ' + getPaymentBadgeClass(paymentStatus) + '">',
                paymentMethod + " / " + window.AppUI.escapeHtml(paymentStatus),
                "    </span>",
                "  </td>",
                '  <td class="fw-semibold text-success">' + window.AppUI.formatCurrency(order.total_price || 0) + "</td>",
                '  <td><span class="badge ' + getOrderBadgeClass(orderStatus) + '">' + window.AppUI.escapeHtml(orderStatus) + "</span></td>",
                '  <td class="text-muted">' + window.AppUI.escapeHtml(order.created_at || "") + "</td>",
                '  <td class="text-end pe-4"><a href="' + detailUrl + '" class="btn btn-sm btn-primary"><i class="fas fa-eye me-1"></i>Chi tiết</a></td>',
                "</tr>",
            ].join("");
        }).join("");
    }

    async function loadOrders() {
        if (!window.ApiClient.hasValidToken()) {
            if (window.ApiClient.getToken()) {
                window.ApiClient.clearToken();
            }
            window.location.href = "/auth/login";
            return;
        }

        setLoading(true);
        try {
            var response = await window.ApiClient.get("/orders");
            var orders = response && response.data ? response.data : [];
            renderSummary(Array.isArray(orders) ? orders : []);
            renderOrders(Array.isArray(orders) ? orders : []);
        } catch (error) {
            showAlert(error && error.message ? error.message : "Không thể tải danh sách đơn hàng.", "danger");
        } finally {
            setLoading(false);
        }
    }

    boot(loadOrders);
})(window, document);
