(function (window, document) {
    "use strict";

    var USER_KEY = "my_store_user";

    function parseStoredUser() {
        try {
            return JSON.parse(localStorage.getItem(USER_KEY) || "null");
        } catch (error) {
            return null;
        }
    }

    function getToken() {
        return window.ApiClient ? window.ApiClient.getToken() : "";
    }

    function setAuthState(user) {
        var guest = document.getElementById("navbarGuestActions");
        var auth = document.getElementById("navbarUserActions");
        var name = document.getElementById("navbarUserName");
        var role = document.getElementById("navbarUserRole");
        var avatar = document.getElementById("navbarUserAvatar");
        var badge = document.getElementById("cartQtyBadge");

        if (!user) {
            if (guest) {
                guest.classList.add("is-visible");
            }
            if (auth) {
                auth.classList.remove("is-visible");
            }
            if (badge && badge.textContent.trim() === "") {
                badge.textContent = "0";
            }
            return;
        }

        if (guest) {
            guest.classList.remove("is-visible");
        }
        if (auth) {
            auth.classList.add("is-visible");
        }
        if (name) {
            name.textContent = user.full_name || user.name || "Người dùng";
        }
        if (role) {
            role.textContent = user.role === "admin" ? "Admin" : "Customer";
        }
        if (avatar) {
            var initial = (user.full_name || user.name || "U").trim().charAt(0).toUpperCase();
            var avatarUrl = user.avatar ? "/" + String(user.avatar).replace(/^\/+/, "") : "";
            avatar.innerHTML = avatarUrl
                ? '<img src="' + avatarUrl + '" alt="Avatar" class="w-100 h-100">'
                : initial;
        }
    }

    async function refreshNavbar() {
        var token = getToken();
        if (!token) {
            setAuthState(null);
            return;
        }

        var storedUser = parseStoredUser();
        if (storedUser) {
            setAuthState(storedUser);
        }

        try {
            var response = await window.ApiClient.get("/auth/me");
            var user = response && response.data ? response.data : null;
            if (user) {
                localStorage.setItem(USER_KEY, JSON.stringify(user));
                setAuthState(user);
            }
        } catch (error) {
            if (error && error.status === 401) {
                setAuthState(null);
            }
        }
    }

    function bindLogoutButton() {
        var btn = document.getElementById("navbarLogoutBtn");
        if (!btn) {
            return;
        }

        btn.addEventListener("click", async function () {
            try {
                await window.ApiClient.post("/auth/logout", {});
            } catch (error) {
                // Ignore logout transport errors and clear locally anyway.
            }

            window.ApiClient.clearToken();
            window.location.href = "/auth/login";
        });
    }

    function bindAdminSidebar() {
        var toggleBtn = document.getElementById("manageToggleBtn");
        var backdrop = document.getElementById("adminSidebarBackdrop");
        var sidebar = document.getElementById("adminSidebar");

        if (!toggleBtn || !backdrop || !sidebar) {
            return;
        }

        function closeSidebar() {
            document.body.classList.remove("admin-mode");
        }

        toggleBtn.addEventListener("click", function () {
            document.body.classList.toggle("admin-mode");
        });

        backdrop.addEventListener("click", closeSidebar);

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                closeSidebar();
            }
        });
    }

    function bindUnauthorizedRedirect() {
        window.addEventListener("auth:unauthorized", function () {
            window.ApiClient.clearToken();
            window.location.href = "/auth/login";
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        refreshNavbar();
        bindLogoutButton();
        bindAdminSidebar();
        bindUnauthorizedRedirect();
    });
})(window, document);
