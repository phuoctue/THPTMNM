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
        var loading = getEl("adminUsersLoading");
        var content = getEl("adminUsersContent");
        if (loading) {
            loading.classList.toggle("d-none", !isLoading);
        }
        if (content && isLoading) {
            content.classList.add("d-none");
        }
    }

    function showAlert(message, type) {
        var alertEl = getEl("adminUsersAlert");
        if (!alertEl) {
            return;
        }

        alertEl.classList.remove("d-none", "alert-success", "alert-danger", "alert-warning", "alert-info");
        alertEl.classList.add(type === "success" ? "alert-success" : type === "warning" ? "alert-warning" : type === "info" ? "alert-info" : "alert-danger");
        alertEl.textContent = message || "";
    }

    function renderUsers(users) {
        var body = getEl("adminUsersTableBody");
        var content = getEl("adminUsersContent");
        var empty = getEl("adminUsersEmpty");

        if (!body) {
            return;
        }

        if (!users.length) {
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

        body.innerHTML = users.map(function (user) {
            var editUrl = "/admin/users/edit/" + encodeURIComponent(user.id);
            var avatar = user.avatar ? "/" + String(user.avatar).replace(/^\/+/, "") : "";
            var status = String(user.status || "active");
            var role = String(user.role || "customer");
            var verified = user.email_verified_at ? "Đã xác thực" : "Chưa xác thực";

            return [
                '<tr data-user-id="' + user.id + '"',
                ' data-full-name="' + window.AppUI.escapeHtml(user.full_name || "") + '"',
                ' data-email="' + window.AppUI.escapeHtml(user.email || "") + '"',
                ' data-phone="' + window.AppUI.escapeHtml(user.phone || "") + '"',
                ' data-address="' + window.AppUI.escapeHtml(user.address || "") + '"',
                ' data-role="' + window.AppUI.escapeHtml(role) + '"',
                ' data-status="' + window.AppUI.escapeHtml(status) + '">',
                '  <td>#' + window.AppUI.escapeHtml(user.id) + "</td>",
                '  <td>',
                '    <div class="d-flex align-items-center gap-2">',
                avatar
                    ? '<div class="rounded-circle overflow-hidden" style="width:40px;height:40px;"><img src="' + avatar + '" class="w-100 h-100 object-fit-cover" alt="Avatar"></div>'
                    : '<div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width:40px;height:40px;background:linear-gradient(135deg,#2563eb 0%,#7c3aed 100%);">' + window.AppUI.escapeHtml((user.full_name || "U").trim().charAt(0).toUpperCase()) + "</div>",
                '      <div>',
                '        <div class="fw-bold">' + window.AppUI.escapeHtml(user.full_name || "") + "</div>",
                '        <small class="text-muted">' + window.AppUI.escapeHtml(user.email || "") + "</small>",
                "      </div>",
                "    </div>",
                "  </td>",
                '  <td>' + window.AppUI.escapeHtml(user.phone || "-") + '<div class="small text-muted">' + window.AppUI.escapeHtml(user.address || "") + "</div></td>",
                '  <td><span class="badge text-bg-' + (role === "admin" ? "dark" : "primary") + '">' + window.AppUI.escapeHtml(role) + "</span></td>",
                '  <td><span class="badge text-bg-' + (status === "locked" ? "danger" : "success") + '">' + window.AppUI.escapeHtml(status) + "</span></td>",
                '  <td><span class="badge text-bg-' + (user.email_verified_at ? "success" : "warning") + '">' + verified + "</span></td>",
                '  <td class="text-end">',
                '    <div class="btn-group btn-group-sm flex-wrap" role="group">',
                '      <a href="' + editUrl + '" class="btn btn-outline-primary">Sửa</a>',
                '      <button type="button" class="btn btn-outline-warning js-toggle-status" data-user-id="' + user.id + '" data-status="' + status + '">Khóa/Mở</button>',
                '      <button type="button" class="btn btn-outline-danger js-delete-user" data-user-id="' + user.id + '">Xóa</button>',
                "    </div>",
                "  </td>",
                "</tr>",
            ].join("");
        }).join("");
    }

    async function loadUsers() {
        if (!window.ApiClient.hasValidToken()) {
            window.location.href = "/auth/login";
            return;
        }

        setLoading(true);
        try {
            var response = await window.ApiClient.get("/users");
            var users = response && response.data ? response.data : [];
            renderUsers(Array.isArray(users) ? users : []);
        } catch (error) {
            showAlert(error && error.message ? error.message : "Không thể tải danh sách người dùng.", "danger");
        } finally {
            setLoading(false);
        }
    }

    async function updateStatus(user) {
        var nextStatus = String(user.status || "active") === "locked" ? "active" : "locked";
        try {
            var response = await window.ApiClient.put("/users/" + encodeURIComponent(user.id), {
                full_name: user.full_name || "",
                email: user.email || "",
                phone: user.phone || "",
                address: user.address || "",
                role: user.role || "customer",
                status: nextStatus,
            });
            window.AppUI.toast(response.message || "Đã cập nhật trạng thái.", "success");
            await loadUsers();
        } catch (error) {
            showAlert(error && error.message ? error.message : "Cập nhật trạng thái thất bại.", "danger");
        }
    }

    async function deleteUser(user) {
        if (!window.confirm("Xóa người dùng này?")) {
            return;
        }

        try {
            var response = await window.ApiClient.delete("/users/" + encodeURIComponent(user.id));
            window.AppUI.toast(response.message || "Đã xóa người dùng.", "success");
            await loadUsers();
        } catch (error) {
            showAlert(error && error.message ? error.message : "Xóa người dùng thất bại.", "danger");
        }
    }

    function bindEvents() {
        var body = getEl("adminUsersTableBody");
        if (!body) {
            return;
        }

        body.addEventListener("click", function (event) {
            var toggleBtn = event.target.closest(".js-toggle-status");
            var deleteBtn = event.target.closest(".js-delete-user");
            var row = event.target.closest("tr[data-user-id]");
            if (!row) {
                return;
            }

            var user = {
                id: Number(row.getAttribute("data-user-id")),
                full_name: row.getAttribute("data-full-name") || "",
                email: row.getAttribute("data-email") || "",
                phone: row.getAttribute("data-phone") || "",
                address: row.getAttribute("data-address") || "",
                role: row.getAttribute("data-role") || "customer",
                status: row.getAttribute("data-status") || "active",
            };

            if (toggleBtn) {
                updateStatus(user);
            }
            if (deleteBtn) {
                deleteUser(user);
            }
        });
    }

    boot(function () {
        bindEvents();
        loadUsers();
    });
})(window, document);
