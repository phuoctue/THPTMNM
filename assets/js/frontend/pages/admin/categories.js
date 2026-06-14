(function (window, document) {
    "use strict";

    function getEl(id) {
        return document.getElementById(id);
    }

    function showAlert(message, type) {
        var el = getEl("adminCategoryAlert");
        if (!el) {
            return;
        }

        el.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning", "alert-info");
        el.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : type === "info" ? "alert-info" : "alert-danger");
        el.textContent = message || "";
    }

    function setLoading(isLoading) {
        var loading = getEl("adminCategoryLoading");
        var tableWrap = getEl("adminCategoryTableWrap");
        if (loading) {
            loading.classList.toggle("d-none", !isLoading);
        }
        if (tableWrap && isLoading) {
            tableWrap.classList.add("d-none");
        }
    }

    function renderEmpty() {
        var empty = getEl("adminCategoryEmpty");
        var tableWrap = getEl("adminCategoryTableWrap");
        if (tableWrap) {
            tableWrap.classList.add("d-none");
        }
        if (empty) {
            empty.classList.remove("d-none");
        }
    }

    function renderTable(items) {
        var body = getEl("adminCategoryTableBody");
        var tableWrap = getEl("adminCategoryTableWrap");
        var empty = getEl("adminCategoryEmpty");

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
            return [
                '<tr data-category-id="' + item.id + '">',
                '  <td><span class="badge bg-light text-dark">' + item.id + "</span></td>",
                '  <td class="fw-bold">' + window.AppUI.escapeHtml(item.name || "") + "</td>",
                '  <td class="text-muted">' + window.AppUI.escapeHtml(item.description || "") + "</td>",
                '  <td>',
                '    <div class="d-flex gap-2">',
                '      <a href="/Category/edit/' + item.id + '" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>',
                '      <button type="button" class="btn btn-danger btn-sm js-delete-category"><i class="fas fa-trash"></i></button>',
                "    </div>",
                "  </td>",
                "</tr>",
            ].join("");
        }).join("");
    }

    async function loadCategories() {
        if (!window.ApiClient.hasValidToken()) {
            if (window.ApiClient.getToken()) {
                window.ApiClient.clearToken();
            }
            window.location.href = "/auth/login";
            return;
        }

        setLoading(true);
        try {
            var response = await window.ApiClient.get("/categories");
            var items = response && response.data ? response.data : [];
            renderTable(Array.isArray(items) ? items : []);
            if (!items.length) {
                renderEmpty();
            }
        } catch (error) {
            showAlert(error && error.message ? error.message : "Không thể tải danh mục.", "danger");
            renderEmpty();
        } finally {
            setLoading(false);
        }
    }

    async function deleteCategory(categoryId) {
        if (!window.confirm("Xóa danh mục này?")) {
            return;
        }

        try {
            await window.ApiClient.delete("/categories/" + encodeURIComponent(categoryId));
            window.AppUI.toast("Đã xóa danh mục.", "success");
            loadCategories();
        } catch (error) {
            showAlert(error && error.message ? error.message : "Xóa danh mục thất bại.", "danger");
        }
    }

    function bindEvents() {
        var body = getEl("adminCategoryTableBody");
        if (!body) {
            return;
        }

        body.addEventListener("click", function (event) {
            var button = event.target.closest(".js-delete-category");
            if (!button) {
                return;
            }

            var row = button.closest("tr");
            var categoryId = row ? row.getAttribute("data-category-id") : "";
            if (categoryId) {
                deleteCategory(categoryId);
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        bindEvents();
        loadCategories();
    });
})(window, document);
