(function (window, document) {
    "use strict";

    var state = {
        page: 1,
        perPage: 12,
        total: 0,
        lastPage: 1,
    };

    function getEl(id) {
        return document.getElementById(id);
    }

    function showAlert(message, type) {
        var el = getEl("adminProductAlert");
        if (!el) {
            return;
        }

        el.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning", "alert-info");
        el.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : type === "info" ? "alert-info" : "alert-danger");
        el.textContent = message || "";
    }

    function setLoading(isLoading) {
        var loading = getEl("adminProductLoading");
        var tableWrap = getEl("adminProductTableWrap");
        if (loading) {
            loading.classList.toggle("d-none", !isLoading);
        }
        if (tableWrap && isLoading) {
            tableWrap.classList.add("d-none");
        }
    }

    function renderEmpty() {
        var empty = getEl("adminProductEmpty");
        var tableWrap = getEl("adminProductTableWrap");
        var pagination = getEl("adminProductPagination");

        if (tableWrap) {
            tableWrap.classList.add("d-none");
        }
        if (empty) {
            empty.classList.remove("d-none");
        }
        if (pagination) {
            pagination.innerHTML = "";
        }
    }

    function renderTable(items) {
        var body = getEl("adminProductTableBody");
        var tableWrap = getEl("adminProductTableWrap");
        var empty = getEl("adminProductEmpty");

        if (!body) {
            return;
        }

        if (!items.length) {
            renderEmpty();
            body.innerHTML = "";
            return;
        }

        if (empty) {
            empty.classList.add("d-none");
        }
        if (tableWrap) {
            tableWrap.classList.remove("d-none");
        }

        body.innerHTML = items.map(function (item) {
            var image = item.image ? "/" + String(item.image).replace(/^\/+/, "") : "";
            return [
                '<tr data-product-id="' + item.id + '">',
                '  <td>',
                image
                    ? '<img class="admin-thumb" src="' + image + '" alt="">'
                    : '<div class="admin-thumb d-flex align-items-center justify-content-center text-primary"><i class="fas fa-box-open"></i></div>',
                "  </td>",
                '  <td>',
                '    <div class="fw-bold">' + window.AppUI.escapeHtml(item.name || "") + '</div>',
                '    <div class="text-muted small">' + window.AppUI.escapeHtml(item.description || "") + '</div>',
                "  </td>",
                '  <td>' + window.AppUI.escapeHtml(item.category_name || "Chưa phân loại") + "</td>",
                '  <td class="fw-bold text-primary">' + window.AppUI.formatCurrency(item.price || 0) + "</td>",
                '  <td>',
                '    <div class="d-flex gap-2">',
                '      <a href="/Product/edit/' + item.id + '" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>',
                '      <button type="button" class="btn btn-danger btn-sm js-delete-product"><i class="fas fa-trash"></i></button>',
                "    </div>",
                "  </td>",
                "</tr>",
            ].join("");
        }).join("");
    }

    function renderPagination() {
        var wrap = getEl("adminProductPagination");
        if (!wrap) {
            return;
        }

        if (state.lastPage <= 1) {
            wrap.innerHTML = "";
            return;
        }

        var pages = [];
        for (var i = 1; i <= state.lastPage; i += 1) {
            if (i === 1 || i === state.lastPage || Math.abs(i - state.page) <= 2) {
                pages.push(i);
            }
        }

        var html = [];
        html.push('<button class="page-btn ' + (state.page <= 1 ? "disabled" : "") + '" data-page="' + (state.page - 1) + '"><i class="fas fa-chevron-left"></i></button>');
        var prev = null;
        pages.forEach(function (page) {
            if (prev !== null && page - prev > 1) {
                html.push('<span class="page-btn disabled">…</span>');
            }
            html.push('<button class="page-btn ' + (page === state.page ? "active" : "") + '" data-page="' + page + '">' + page + "</button>");
            prev = page;
        });
        html.push('<button class="page-btn ' + (state.page >= state.lastPage ? "disabled" : "") + '" data-page="' + (state.page + 1) + '"><i class="fas fa-chevron-right"></i></button>');
        wrap.innerHTML = html.join("");
    }

    async function loadCategories() {
        var select = getEl("adminProductCategoryFilter");
        if (!select) {
            return;
        }

        try {
            var response = await window.ApiClient.get("/categories");
            var categories = response && response.data ? response.data : [];
            select.innerHTML = '<option value="">Tất cả danh mục</option>' + categories.map(function (category) {
                return '<option value="' + category.id + '">' + window.AppUI.escapeHtml(category.name || "") + "</option>";
            }).join("");
        } catch (error) {
            // ignore
        }
    }

    async function loadProducts() {
        if (!window.ApiClient.hasValidToken()) {
            if (window.ApiClient.getToken()) {
                window.ApiClient.clearToken();
            }
            window.location.href = "/auth/login";
            return;
        }

        setLoading(true);
        try {
            var form = getEl("adminProductFilterForm");
            var data = new FormData(form);
            var params = new URLSearchParams();
            params.set("search", String(data.get("search") || "").trim());
            params.set("category_id", String(data.get("category_id") || "").trim());
            params.set("sort_by", String(data.get("sort_by") || "created_at"));
            params.set("sort_dir", String(data.get("sort_dir") || "desc"));
            params.set("page", String(state.page));
            params.set("per_page", String(state.perPage));

            var response = await window.ApiClient.get("/products?" + params.toString());
            var items = response && response.data ? response.data : [];
            var pagination = response && response.meta && response.meta.pagination ? response.meta.pagination : null;
            state.total = pagination ? pagination.total : items.length;
            state.lastPage = pagination ? pagination.last_page : 1;
            state.page = pagination ? pagination.current_page : state.page;

            renderTable(Array.isArray(items) ? items : []);
            renderPagination();
            if (!items.length) {
                renderEmpty();
            }
        } catch (error) {
            showAlert(error && error.message ? error.message : "Không thể tải sản phẩm.", "danger");
            renderEmpty();
        } finally {
            setLoading(false);
        }
    }

    async function deleteProduct(productId) {
        if (!window.confirm("Xóa sản phẩm này?")) {
            return;
        }

        try {
            await window.ApiClient.delete("/products/" + encodeURIComponent(productId));
            window.AppUI.toast("Đã xóa sản phẩm.", "success");
            loadProducts();
        } catch (error) {
            showAlert(error && error.message ? error.message : "Xóa sản phẩm thất bại.", "danger");
        }
    }

    function bindEvents() {
        var form = getEl("adminProductFilterForm");
        var body = getEl("adminProductTableBody");
        var pagination = getEl("adminProductPagination");

        if (form) {
            form.addEventListener("submit", function (event) {
                event.preventDefault();
                state.page = 1;
                loadProducts();
            });
            form.addEventListener("change", function () {
                state.page = 1;
                loadProducts();
            });
        }

        if (pagination) {
            pagination.addEventListener("click", function (event) {
                var button = event.target.closest("[data-page]");
                if (!button || button.classList.contains("disabled")) {
                    return;
                }

                var page = parseInt(button.getAttribute("data-page"), 10);
                if (!page || page < 1) {
                    return;
                }

                state.page = page;
                loadProducts();
            });
        }

        if (body) {
            body.addEventListener("click", function (event) {
                var button = event.target.closest(".js-delete-product");
                if (!button) {
                    return;
                }

                var row = button.closest("tr");
                var productId = row ? row.getAttribute("data-product-id") : "";
                if (productId) {
                    deleteProduct(productId);
                }
            });
        }
    }

    document.addEventListener("DOMContentLoaded", async function () {
        bindEvents();
        await loadCategories();
        await loadProducts();
    });
})(window, document);
