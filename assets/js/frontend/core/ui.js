(function (window) {
    "use strict";

    function escapeHtml(value) {
        return String(value == null ? "" : value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatCurrency(value) {
        var number = Number(value || 0);
        return new Intl.NumberFormat("vi-VN").format(number) + " ₫";
    }

    function showToast(message, type) {
        var body = document.getElementById("appToastBody");
        var header = document.getElementById("appToastHeader");
        var title = document.getElementById("appToastTitle");
        var toastEl = document.getElementById("appToast");

        if (body) {
            body.textContent = message || "Đã xử lý thành công.";
        }

        if (header) {
            header.classList.remove("bg-success", "bg-danger", "bg-warning");
            header.classList.add(type === "error" ? "bg-danger" : type === "warning" ? "bg-warning" : "bg-success");
        }

        if (title) {
            title.textContent = type === "error" ? "Lỗi" : type === "warning" ? "Cảnh báo" : "Thông báo";
        }

        if (toastEl && window.bootstrap && bootstrap.Toast) {
            bootstrap.Toast.getOrCreateInstance(toastEl).show();
        }
    }

    window.AppUI = {
        escapeHtml: escapeHtml,
        formatCurrency: formatCurrency,
        toast: showToast,
    };
})(window);
