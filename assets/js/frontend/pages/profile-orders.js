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
        var loading = getEl("profileOrdersLoading");
        var content = getEl("profileOrdersContent");
        if (loading) {
            loading.classList.toggle("d-none", !isLoading);
        }
        if (content && isLoading) {
            content.classList.add("d-none");
        }
    }

    function showAlert(message, type) {
        var alertEl = getEl("profileOrdersAlert");
        if (!alertEl) {
            return;
        }

        alertEl.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning", "alert-info");
        alertEl.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : type === "info" ? "alert-info" : "alert-danger");
        alertEl.textContent = message || "";
    }

    function orderBadge(status) {
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

    function paymentBadge(status) {
        return status === "paid" ? "text-bg-success" : "text-bg-warning";
    }

    function renderSummary(orders) {
        getEl("profileOrdersCount").textContent = String(orders.length);
        var spent = orders.reduce(function (sum, order) {
            return sum + Number(order.total_price || 0);
        }, 0);
        getEl("profileOrdersSpent").textContent = window.AppUI.formatCurrency(spent);
        getEl("profileOrdersRole").textContent = "customer";
    }

    function renderOrders(orders) {
        var body = getEl("profileOrdersTableBody");
        var content = getEl("profileOrdersContent");
        var empty = getEl("profileOrdersEmpty");

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
            var detailUrl = "/Cart/orderDetail/" + encodeURIComponent(order.id);
            var paymentMethod = String(order.payment_method || "cod").toUpperCase();
            var paymentStatus = String(order.payment_status || "unpaid");
            var status = String(order.status || "pending");
            var itemCount = typeof order.item_count !== "undefined" ? Number(order.item_count) : Array.isArray(order.items) ? order.items.length : 0;

            return [
                "<tr>",
                '  <td class="ps-4 fw-semibold">#' + window.AppUI.escapeHtml(order.id || "") + "</td>",
                '  <td class="text-muted">' + window.AppUI.escapeHtml(order.created_at || "") + "</td>",
                '  <td><span class="badge ' + paymentBadge(paymentStatus) + '">' + paymentMethod + " / " + window.AppUI.escapeHtml(paymentStatus) + "</span></td>",
                '  <td class="fw-semibold text-success">' + window.AppUI.formatCurrency(order.total_price || 0) + "</td>",
                '  <td><span class="badge ' + orderBadge(status) + '">' + window.AppUI.escapeHtml(status) + "</span></td>",
                '  <td><span class="badge text-bg-light text-dark">' + itemCount + " sản phẩm</span></td>",
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
            var response = await window.ApiClient.get("/profile/orders");
            var orders = response && response.data ? response.data : [];
            renderSummary(Array.isArray(orders) ? orders : []);
            renderOrders(Array.isArray(orders) ? orders : []);
        } catch (error) {
            showAlert(error && error.message ? error.message : "Không thể tải đơn hàng.", "danger");
        } finally {
            setLoading(false);
        }
    }

    boot(loadOrders);
})(window, document);
