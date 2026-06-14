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
        var alertEl = getEl("checkoutAlert");
        if (!alertEl) {
            return;
        }

        alertEl.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning", "alert-info");
        alertEl.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : type === "info" ? "alert-info" : "alert-danger");
        alertEl.textContent = message || "";
    }

    function setLoading(isLoading) {
        var loading = getEl("checkoutLoading");
        var content = getEl("checkoutContent");
        if (loading) {
            loading.classList.toggle("d-none", !isLoading);
        }
        if (content && isLoading) {
            content.classList.add("d-none");
        }
    }

    function setBusy(isBusy) {
        var btn = getEl("checkoutSubmitBtn");
        var spinner = btn ? btn.querySelector(".spinner-border") : null;
        if (btn) {
            btn.disabled = isBusy;
        }
        if (spinner) {
            spinner.classList.toggle("d-none", !isBusy);
        }
    }

    function renderItems(items) {
        var wrap = getEl("checkoutItems");
        if (!wrap) {
            return;
        }

        wrap.innerHTML = items.map(function (item) {
            var image = item.image ? "/" + String(item.image).replace(/^\/+/, "") : "";
            return [
                '<div class="checkout-item">',
                image
                    ? '<img class="checkout-thumb" src="' + image + '" alt="' + window.AppUI.escapeHtml(item.name || "") + '">'
                    : '<div class="checkout-thumb d-flex align-items-center justify-content-center text-primary"><i class="fas fa-box-open"></i></div>',
                '  <div class="flex-grow-1">',
                '    <div class="fw-bold">' + window.AppUI.escapeHtml(item.name || "") + '</div>',
                '    <div class="text-muted small">SL: ' + Number(item.quantity || 0) + '</div>',
                "  </div>",
                '  <div class="fw-bold">' + window.AppUI.formatCurrency((item.price || 0) * (item.quantity || 0)) + "</div>",
                "</div>",
            ].join("");
        }).join("");
    }

    async function bootstrap() {
        if (!window.ApiClient.hasValidToken()) {
            if (window.ApiClient.getToken()) {
                window.ApiClient.clearToken();
            }
            window.location.href = "/auth/login";
            return;
        }

        setLoading(true);

        try {
            var [cartResponse, meResponse] = await Promise.all([
                window.ApiClient.get("/cart"),
                window.ApiClient.get("/auth/me"),
            ]);

            var cart = cartResponse && cartResponse.data ? cartResponse.data : { items: [], total_qty: 0, total_price: 0 };
            if (!cart.items || !cart.items.length) {
                var emptyState = getEl("checkoutEmptyState");
                if (emptyState) {
                    emptyState.classList.remove("d-none");
                }
                return;
            }

            renderItems(cart.items || []);

            var totalPrice = getEl("checkoutTotalPrice");
            if (totalPrice) {
                totalPrice.textContent = window.AppUI.formatCurrency(cart.total_price || 0);
            }

            var orderCode = getEl("checkoutOrderCode");
            if (orderCode) {
                orderCode.textContent = "DH" + String(Date.now()).slice(-8);
            }

            var user = meResponse && meResponse.data ? meResponse.data : null;
            var form = getEl("checkoutForm");
            if (form && user) {
                form.customer_name.value = user.full_name || user.name || "";
                form.customer_email.value = user.email || "";
                form.customer_phone.value = user.phone || "";
                form.customer_address.value = user.address || "";
            }

            var content = getEl("checkoutContent");
            if (content) {
                content.classList.remove("d-none");
            }
        } catch (error) {
            showAlert(error && error.message ? error.message : "Không thể tải thông tin thanh toán.", "danger");
        } finally {
            setLoading(false);
        }
    }

    async function submitOrder(event) {
        event.preventDefault();

        var form = event.currentTarget;
        var data = new FormData(form);

        setBusy(true);
        try {
            var response = await window.ApiClient.post("/orders", {
                customer_name: String(data.get("customer_name") || "").trim(),
                customer_phone: String(data.get("customer_phone") || "").trim(),
                customer_email: String(data.get("customer_email") || "").trim(),
                customer_address: String(data.get("customer_address") || "").trim(),
                note: String(data.get("note") || "").trim(),
                payment_method: String(data.get("payment_method") || "cod"),
            });

            var orderId = response && response.data ? response.data.order_id : null;
            if (window.AppUI) {
                window.AppUI.toast(response.message || "Đặt hàng thành công.", "success");
            }
            window.location.href = orderId ? "/Cart/success?order_id=" + encodeURIComponent(orderId) : "/Cart";
        } catch (error) {
            var message = error && error.payload && error.payload.errors
                ? Object.values(error.payload.errors).join(" | ")
                : (error && error.message ? error.message : "Đặt hàng thất bại.");
            showAlert(message, "danger");
        } finally {
            setBusy(false);
        }
    }

    boot(function () {
        var form = getEl("checkoutForm");
        if (form) {
            form.addEventListener("submit", submitOrder);
        }

        bootstrap();
    });
})(window, document);
