(function (window, document) {
    "use strict";

    var state = {
        page: 1,
        perPage: 12,
        total: 0,
        lastPage: 1,
    };

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

    function readFilters() {
        var form = getEl("homeSearchForm");
        var formData = form ? new FormData(form) : new FormData();
        var sortValue = getEl("sortFilter") ? getEl("sortFilter").value : "created_at:desc";
        var sortParts = sortValue.split(":");

        return {
            search: String(formData.get("search") || "").trim(),
            category_id: String(formData.get("category_id") || "").trim(),
            min_price: String(formData.get("min_price") || "").trim(),
            max_price: String(formData.get("max_price") || "").trim(),
            sort_by: sortParts[0] || "created_at",
            sort_dir: sortParts[1] || "desc",
            page: state.page,
            per_page: state.perPage,
        };
    }

    function syncUrl(filters) {
        var url = new URL(window.location.href);
        Object.keys(filters).forEach(function (key) {
            if (filters[key] === "" || filters[key] === null || typeof filters[key] === "undefined") {
                url.searchParams.delete(key);
            } else {
                url.searchParams.set(key, String(filters[key]));
            }
        });
        window.history.replaceState({}, "", url.toString());
    }

    function applyUrlToFilters() {
        var params = new URLSearchParams(window.location.search);
        var form = getEl("homeSearchForm");
        var sortFilter = getEl("sortFilter");

        if (!form) {
            return;
        }

        if (params.get("search") !== null) {
            form.search.value = params.get("search");
        }
        if (params.get("category_id") !== null) {
            form.category_id.value = params.get("category_id");
        }
        if (params.get("min_price") !== null) {
            form.min_price.value = params.get("min_price");
        }
        if (params.get("max_price") !== null) {
            form.max_price.value = params.get("max_price");
        }
        if (params.get("sort_by") && params.get("sort_dir") && sortFilter) {
            sortFilter.value = params.get("sort_by") + ":" + params.get("sort_dir");
        }
        if (params.get("page") !== null) {
            state.page = Math.max(1, parseInt(params.get("page"), 10) || 1);
        }
    }

    function renderLoading(isLoading) {
        var loading = getEl("productLoadingGrid");
        var grid = getEl("productGrid");

        if (loading) {
            loading.classList.toggle("d-none", !isLoading);
        }
        if (grid && isLoading) {
            grid.classList.add("d-none");
        }
    }

    function renderEmpty(message) {
        var grid = getEl("productGrid");
        var empty = getEl("productEmptyState");
        var pagination = getEl("productPagination");

        if (grid) {
            grid.classList.add("d-none");
            grid.innerHTML = "";
        }
        if (empty) {
            empty.classList.remove("d-none");
            var paragraph = empty.querySelector("p");
            if (paragraph) {
                paragraph.textContent = message || "Không tìm thấy sản phẩm.";
            }
        }
        if (pagination) {
            pagination.innerHTML = "";
        }
    }

    function renderProducts(products) {
        var grid = getEl("productGrid");
        var empty = getEl("productEmptyState");

        if (!grid) {
            return;
        }

        if (!products.length) {
            renderEmpty("Không tìm thấy sản phẩm phù hợp.");
            return;
        }

        if (empty) {
            empty.classList.add("d-none");
        }

        grid.classList.remove("d-none");
        grid.innerHTML = products.map(function (product) {
            var image = product.image ? "/" + String(product.image).replace(/^\/+/, "") : "";
            var detailUrl = "/Product/show/" + encodeURIComponent(product.id);

            return [
                '<article class="product-card">',
                '  <a class="product-card__image" href="' + detailUrl + '">',
                image
                    ? '    <img src="' + image + '" alt="' + window.AppUI.escapeHtml(product.name || "") + '">'
                    : '    <span class="product-card__placeholder"><i class="fas fa-box-open"></i></span>',
                "  </a>",
                '  <div class="product-card__body">',
                '    <div class="product-card__category">' + window.AppUI.escapeHtml(product.category_name || "Chưa phân loại") + "</div>",
                '    <div class="product-card__name">' + window.AppUI.escapeHtml(product.name || "") + "</div>",
                '    <div class="product-card__price">' + window.AppUI.formatCurrency(product.price) + "</div>",
                "  </div>",
                '  <div class="product-card__footer">',
                '    <a href="' + detailUrl + '" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye me-1"></i>Chi tiết</a>',
                '    <button type="button" class="btn btn-success btn-sm js-add-to-cart" data-product-id="' + encodeURIComponent(product.id) + '"><i class="fas fa-cart-plus me-1"></i>Thêm</button>',
                "  </div>",
                "</article>",
            ].join("");
        }).join("");
    }

    function renderPagination() {
        var wrap = getEl("productPagination");
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

        var previous = null;
        pages.forEach(function (page) {
            if (previous !== null && page - previous > 1) {
                html.push('<span class="page-btn disabled">…</span>');
            }
            html.push('<button class="page-btn ' + (page === state.page ? "active" : "") + '" data-page="' + page + '">' + page + "</button>");
            previous = page;
        });

        html.push('<button class="page-btn ' + (state.page >= state.lastPage ? "disabled" : "") + '" data-page="' + (state.page + 1) + '"><i class="fas fa-chevron-right"></i></button>');
        wrap.innerHTML = html.join("");
    }

    function updateSummary(total, lastPage, currentPage) {
        state.total = total || 0;
        state.lastPage = lastPage || 1;
        state.page = currentPage || 1;

        var totalEl = getEl("homeTotalProducts");
        var hint = getEl("productListHint");
        if (totalEl) {
            totalEl.textContent = String(state.total);
        }
        if (hint) {
            hint.textContent = state.total
                ? "Đang hiển thị kết quả từ API cho trang " + state.page + "."
                : "Không có dữ liệu phù hợp với bộ lọc hiện tại.";
        }
    }

    async function loadCategories() {
        var select = getEl("categoryFilter");
        if (!select || !window.ApiClient) {
            return;
        }

        try {
            var response = await window.ApiClient.get("/categories");
            var categories = response && response.data ? response.data : [];
            var currentValue = select.value;

            select.innerHTML = '<option value="">Tất cả danh mục</option>' + (Array.isArray(categories) ? categories : []).map(function (category) {
                return '<option value="' + String(category.id) + '">' + window.AppUI.escapeHtml(category.name || "") + "</option>";
            }).join("");
            select.value = currentValue || "";
        } catch (error) {
            // Ignore category load errors so the main list still works.
        }
    }

    async function loadProducts() {
        if (!window.ApiClient || !window.AppUI) {
            return;
        }

        renderLoading(true);
        var emptyState = getEl("productEmptyState");
        if (emptyState) {
            emptyState.classList.add("d-none");
        }

        var filters = readFilters();
        syncUrl(filters);

        try {
            var response = await window.ApiClient.get("/products?" + new URLSearchParams(filters).toString());
            var products = response && response.data ? response.data : [];
            var pagination = response && response.meta && response.meta.pagination ? response.meta.pagination : null;
            var currentPage = pagination ? pagination.current_page : state.page;
            var lastPage = pagination ? pagination.last_page : 1;
            var total = pagination ? pagination.total : products.length;

            updateSummary(total, lastPage, currentPage);
            renderProducts(Array.isArray(products) ? products : []);
            renderPagination();
        } catch (error) {
            renderEmpty("Không thể tải danh sách sản phẩm từ API.");
            if (window.AppUI) {
                window.AppUI.toast(error && error.message ? error.message : "Không thể tải sản phẩm", "error");
            }
        } finally {
            renderLoading(false);
        }
    }

    async function addToCart(productId) {
        if (!window.ApiClient.hasValidToken()) {
            if (window.ApiClient.getToken()) {
                window.ApiClient.clearToken();
            }
            window.location.href = "/auth/login";
            return;
        }

        try {
            var response = await window.ApiClient.post("/cart/add", {
                product_id: Number(productId),
                quantity: 1,
            });

            var qty = response && response.data && typeof response.data.total_qty !== "undefined"
                ? response.data.total_qty
                : null;
            var badge = getEl("cartQtyBadge");
            if (badge && qty !== null) {
                badge.textContent = String(qty);
            }

            if (window.AppUI) {
                window.AppUI.toast(response.message || "Đã thêm vào giỏ hàng!");
            }
        } catch (error) {
            if (window.AppUI) {
                window.AppUI.toast(error && error.message ? error.message : "Không thể thêm vào giỏ hàng", "error");
            }
        }
    }

    function bindEvents() {
        var form = getEl("homeSearchForm");
        var sort = getEl("sortFilter");
        var pagination = getEl("productPagination");
        var grid = getEl("productGrid");

        if (form) {
            form.addEventListener("submit", function (event) {
                event.preventDefault();
                state.page = 1;
                loadProducts();
            });
        }

        if (sort) {
            sort.addEventListener("change", function () {
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

        if (grid) {
            grid.addEventListener("click", function (event) {
                var button = event.target.closest(".js-add-to-cart");
                if (!button) {
                    return;
                }

                addToCart(button.getAttribute("data-product-id"));
            });
        }
    }

    boot(function () {
        applyUrlToFilters();
        bindEvents();
        loadCategories().then(loadProducts);
    });
})(window, document);
