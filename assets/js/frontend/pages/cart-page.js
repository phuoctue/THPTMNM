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
        var alertEl = getEl("cartAlert");
        if (!alertEl) {
            return;
        }

        alertEl.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning", "alert-info");
        alertEl.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : type === "info" ? "alert-info" : "alert-danger");
        alertEl.textContent = message || "";
    }

    function setBusy(isBusy) {
        var loading = getEl("cartLoading");
        var content = getEl("cartContent");
        if (loading) {
            loading.classList.toggle("d-none", !isBusy);
        }
        if (content && isBusy) {
            content.classList.add("d-none");
        }
    }

    function updateSummary(summary) {
        var qty = getEl("cartTotalQty");
        var price = getEl("cartTotalPrice");
        var badge = getEl("cartQtyBadge");

        if (qty) {
            qty.textContent = String(summary.total_qty || 0);
        }
        if (price) {
            price.textContent = window.AppUI.formatCurrency(summary.total_price || 0);
        }
        if (badge) {
            badge.textContent = String(summary.total_qty || 0);
        }
    }

    function renderEmpty() {
        var empty = getEl("cartEmptyState");
        var content = getEl("cartContent");
        if (content) {
            content.classList.add("d-none");
        }
        if (empty) {
            empty.classList.remove("d-none");
        }
    }

    function renderItems(items) {
        var body = getEl("cartItems");
        var content = getEl("cartContent");
        var empty = getEl("cartEmptyState");

        if (!body) {
            return;
        }

        if (!items || !items.length) {
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

        body.innerHTML = items.map(function (item) {
            var image = item.image ? "/" + String(item.image).replace(/^\/+/, "") : "";
            return [
                '<tr data-product-id="' + item.product_id + '">',
                '  <td>',
                '    <div class="d-flex align-items-center gap-3">',
                image
                    ? '<img class="cart-thumb" src="' + image + '" alt="' + window.AppUI.escapeHtml(item.name || "") + '">'
                    : '<div class="cart-thumb d-flex align-items-center justify-content-center text-primary"><i class="fas fa-box-open"></i></div>',
                '      <div>',
                '        <div class="fw-bold">' + window.AppUI.escapeHtml(item.name || "") + '</div>',
                '      </div>',
                "    </div>",
                "  </td>",
                '  <td>' + window.AppUI.formatCurrency(item.price) + "</td>",
                '  <td>',
                '    <input type="number" min="1" class="form-control qty-input js-cart-qty" value="' + Number(item.quantity || 1) + '">',
                "  </td>",
                '  <td class="fw-bold text-primary js-item-total">' + window.AppUI.formatCurrency((item.price || 0) * (item.quantity || 0)) + "</td>",
                '  <td class="text-end cart-row-action">',
                '    <button type="button" class="btn btn-outline-danger btn-sm js-remove-item"><i class="fas fa-trash me-1"></i>Xóa</button>',
                "  </td>",
                "</tr>",
            ].join("");
        }).join("");
    }

    async function loadCart() {
        if (!window.ApiClient.hasValidToken()) {
            if (window.ApiClient.getToken()) {
                window.ApiClient.clearToken();
            }
            window.location.href = "/auth/login";
            return;
        }

        setBusy(true);
        try {
            var response = await window.ApiClient.get("/cart");
            var summary = response && response.data ? response.data : { items: [], total_qty: 0, total_price: 0 };
            renderItems(summary.items || []);
            updateSummary(summary);
            if (!summary.items || !summary.items.length) {
                renderEmpty();
            }
        } catch (error) {
            showAlert(error && error.message ? error.message : "Không thể tải giỏ hàng.", "danger");
        } finally {
            setBusy(false);
        }
    }

    async function updateQuantity(productId, quantity) {
        try {
            var response = await window.ApiClient.put("/cart/" + encodeURIComponent(productId), {
                quantity: Number(quantity),
            });
            var summary = response && response.data ? response.data : null;
            if (summary) {
                renderItems(summary.items || []);
                updateSummary(summary);
                showAlert(response.message || "Đã cập nhật giỏ hàng.", "success");
                if (!summary.items || !summary.items.length) {
                    renderEmpty();
                }
            }
        } catch (error) {
            showAlert(error && error.message ? error.message : "Cập nhật số lượng thất bại.", "danger");
        }
    }

    async function removeItem(productId) {
        try {
            var response = await window.ApiClient.delete("/cart/" + encodeURIComponent(productId));
            var summary = response && response.data ? response.data : null;
            if (summary) {
                renderItems(summary.items || []);
                updateSummary(summary);
                showAlert(response.message || "Đã xóa sản phẩm.", "success");
                if (!summary.items || !summary.items.length) {
                    renderEmpty();
                }
            }
        } catch (error) {
            showAlert(error && error.message ? error.message : "Xóa sản phẩm thất bại.", "danger");
        }
    }

    function bindEvents() {
        var body = getEl("cartItems");
        if (!body) {
            return;
        }

        body.addEventListener("change", function (event) {
            var input = event.target.closest(".js-cart-qty");
            if (!input) {
                return;
            }

            var row = input.closest("tr");
            var productId = row ? row.getAttribute("data-product-id") : "";
            var quantity = Math.max(1, parseInt(input.value, 10) || 1);
            input.value = String(quantity);
            updateQuantity(productId, quantity);
        });

        body.addEventListener("click", function (event) {
            var button = event.target.closest(".js-remove-item");
            if (!button) {
                return;
            }

            var row = button.closest("tr");
            var productId = row ? row.getAttribute("data-product-id") : "";
            if (!productId) {
                return;
            }

            if (!window.confirm("Xóa sản phẩm này khỏi giỏ hàng?")) {
                return;
            }

            removeItem(productId);
        });
    }

    boot(function () {
        bindEvents();
        loadCart();
    });
})(window, document);
