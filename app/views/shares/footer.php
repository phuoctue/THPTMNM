</div><!-- /.container -->
</div><!-- /.page-wrapper -->

<footer class="bg-dark text-white mt-auto py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-5 mb-3 mb-md-0">
                <h5 class="font-weight-800 mb-1">
                    <i class="fas fa-store text-warning mr-1"></i> ShopAdmin
                </h5>
                <p class="text-muted small mb-0">
                    Hệ thống quản lý sản phẩm và danh mục đơn giản, hiệu quả.
                </p>
            </div>

            <div class="col-md-3 mb-3 mb-md-0">
                <h6 class="font-weight-700 text-uppercase mb-2" style="font-size:.75rem;letter-spacing:1px;color:rgba(255,255,255,.5);">Liên kết nhanh</h6>
                <ul class="list-unstyled mb-0">
                    <li><a href="/Product"     class="text-muted small"><i class="fas fa-box-open mr-1"></i>Sản phẩm</a></li>
                    <li><a href="/Product/add" class="text-muted small"><i class="fas fa-plus mr-1"></i>Thêm sản phẩm</a></li>
                    <li><a href="/Category"    class="text-muted small"><i class="fas fa-tags mr-1"></i>Danh mục</a></li>
                    <li><a href="/Category/add" class="text-muted small"><i class="fas fa-plus mr-1"></i>Thêm danh mục</a></li>
                </ul>
            </div>

            <div class="col-md-4 text-md-right">
                <h6 class="font-weight-700 text-uppercase mb-2" style="font-size:.75rem;letter-spacing:1px;color:rgba(255,255,255,.5);">Kết nối</h6>
                <a href="#" class="text-muted mr-2"><i class="fab fa-facebook-f fa-lg"></i></a>
                <a href="#" class="text-muted mr-2"><i class="fab fa-twitter fa-lg"></i></a>
                <a href="#" class="text-muted"><i class="fab fa-instagram fa-lg"></i></a>
            </div>
        </div>

        <hr style="border-color:rgba(255,255,255,.1);">
        <p class="text-center text-muted small mb-0">
            &copy; <?php echo date('Y'); ?> ShopAdmin. All rights reserved.
        </p>
    </div>
</footer>

<div id="cartToastWrap" aria-live="polite" aria-atomic="true" style="position: fixed; top: 84px; right: 16px; z-index: 11000; pointer-events: none;">
    <div id="cartToast" class="toast" role="alert" data-delay="1800" style="min-width: 280px; pointer-events: auto;">
        <div id="cartToastHeader" class="toast-header bg-success text-white">
            <i class="fas fa-check-circle mr-2"></i>
            <strong class="mr-auto" id="cartToastTitle">Thông báo</strong>
            <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body" id="cartToastBody">Đã thêm vào giỏ hàng!</div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    function setupAdminSidebar() {
        var toggleBtn = document.getElementById('manageToggleBtn');
        var backdrop = document.getElementById('adminSidebarBackdrop');
        var sidebar = document.getElementById('adminSidebar');
        if (!toggleBtn || !backdrop || !sidebar) return;

        function closeSidebar() {
            document.body.classList.remove('admin-mode');
        }

        toggleBtn.addEventListener('click', function () {
            document.body.classList.toggle('admin-mode');
        });
        backdrop.addEventListener('click', closeSidebar);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeSidebar();
        });
    }

    function syncToastOffset() {
        var toastWrap = document.getElementById('cartToastWrap');
        var nav = document.querySelector('.main-navbar');
        if (!toastWrap || !nav) return;
        var navHeight = nav.getBoundingClientRect().height || 0;
        toastWrap.style.top = (Math.ceil(navHeight) + 12) + 'px';
    }

    function updateCartBadge(count) {
        var badge = document.getElementById('cartQtyBadge') || document.querySelector('a[href="/Cart"] .badge');
        if (badge && typeof count !== 'undefined') badge.textContent = count;
    }

    function getCartBadgeCount() {
        var badge = document.getElementById('cartQtyBadge') || document.querySelector('a[href="/Cart"] .badge');
        return badge ? Number(badge.textContent || 0) : 0;
    }

    function formatVnd(value) {
        return new Intl.NumberFormat('vi-VN').format(Number(value || 0)) + ' ₫';
    }

    function showCartToast(message, type) {
        var body = document.getElementById('cartToastBody');
        var header = document.getElementById('cartToastHeader');
        var title = document.getElementById('cartToastTitle');
        if (body) body.textContent = message || 'Đã thêm vào giỏ hàng!';
        if (header) {
            header.classList.remove('bg-success', 'bg-danger', 'bg-warning');
            header.classList.add(type === 'error' ? 'bg-danger' : 'bg-success');
        }
        if (title) title.textContent = type === 'error' ? 'Lỗi' : 'Thông báo';
        if (window.jQuery && $('#cartToast').toast) {
            $('#cartToast').toast('show');
        }
    }

    function parseJsonResponse(response) {
        if (!response.ok) {
            throw new Error('Bad response');
        }
        return response.text().then(function (text) {
            try {
                return JSON.parse(text);
            } catch (e) {
                var start = text.indexOf('{');
                var end = text.lastIndexOf('}');
                if (start !== -1 && end > start) {
                    return JSON.parse(text.slice(start, end + 1));
                }
                throw new Error('Non-JSON response');
            }
        });
    }

    function renderEmptyCartState() {
        var rows = document.querySelectorAll('table tbody tr');
        if (rows.length > 0) return;

        var tableCard = document.querySelector('.card.shadow-sm.border-0');
        var totalCard = document.querySelector('.card.border-0.shadow-sm.mt-4');
        var checkoutWrap = document.querySelector('.text-right.mt-4');
        if (tableCard) tableCard.remove();
        if (totalCard) totalCard.remove();
        if (checkoutWrap) checkoutWrap.remove();

        if (document.getElementById('cart-empty-state')) return;
        var container = document.querySelector('.container.mt-4');
        if (!container) return;

        var empty = document.createElement('div');
        empty.id = 'cart-empty-state';
        empty.className = 'alert alert-info text-center p-5';
        empty.innerHTML = '<h4>Giỏ hàng đang trống</h4><p>Hãy thêm sản phẩm vào giỏ hàng</p>';
        container.appendChild(empty);
    }

    function setFormBusy(form, isBusy) {
        form.dataset.submitting = isBusy ? '1' : '0';
        var submitButton = form.querySelector('[type="submit"]');
        if (submitButton) submitButton.disabled = isBusy;
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        var actionRaw = form.getAttribute('action') || '';
        var actionPath = '';
        try {
            actionPath = new URL(actionRaw, window.location.origin).pathname;
        } catch (err) {
            actionPath = actionRaw;
        }
        if (!/\/Cart\/(add|update|remove)\/?$/i.test(actionPath)) return;

        e.preventDefault();
        if (form.dataset.submitting === '1') return;
        setFormBusy(form, true);
        var formData = new FormData(form);
        formData.set('_ajax', '1');
        var optimisticCount = null;
        if (/\/Cart\/add\/?$/i.test(actionPath)) {
            optimisticCount = getCartBadgeCount();
            updateCartBadge(optimisticCount + Number(formData.get('quantity') || 1));
        }

        fetch(actionRaw, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(parseJsonResponse)
        .then(function (data) {
            if (!data || !data.success) {
                if (optimisticCount !== null) updateCartBadge(optimisticCount);
                showCartToast((data && data.message) ? data.message : 'Thao tác thất bại.', 'error');
                setFormBusy(form, false);
                return;
            }
            updateCartBadge(data.cartCount);

            if (/\/Cart\/add\/?$/i.test(actionPath)) {
                showCartToast(data.message || 'Đã thêm vào giỏ hàng!');
                setFormBusy(form, false);
                return;
            }

            var row = form.closest('tr');
            if (!row) {
                setFormBusy(form, false);
                return;
            }

            if (/\/Cart\/remove\/?$/i.test(actionPath) || data.removed) {
                row.remove();
            } else {
                var totalCell = row.querySelector('td.font-weight-bold.text-primary');
                if (totalCell) totalCell.textContent = formatVnd(data.itemTotal);
            }

            var cartRows = document.querySelectorAll('table tbody tr');
            var totalBox = document.querySelector('.card-body .text-primary.mb-0.font-weight-bold');
            if (totalBox && typeof data.totalPrice !== 'undefined') {
                totalBox.textContent = formatVnd(data.totalPrice);
            }
            if (cartRows.length === 0) renderEmptyCartState();
            setFormBusy(form, false);
        })
        .catch(function () {
            setFormBusy(form, false);
            if (!/\/Cart\/add\/?$/i.test(actionPath)) {
                form.submit();
                return;
            }
            showCartToast('Khong the xu ly thao tac. Vui long thu lai.', 'error');
        });
    });

    document.addEventListener('change', function (e) {
        var input = e.target;
        if (!(input instanceof HTMLInputElement)) return;
        if (input.name !== 'quantity') return;

        var form = input.closest('form');
        if (!(form instanceof HTMLFormElement)) return;

        var actionRaw = form.getAttribute('action') || '';
        var actionPath = '';
        try {
            actionPath = new URL(actionRaw, window.location.origin).pathname;
        } catch (err) {
            actionPath = actionRaw;
        }
        if (!/\/Cart\/update\/?$/i.test(actionPath)) return;

        var qty = parseInt(input.value || '1', 10);
        if (!Number.isFinite(qty) || qty < 1) {
            qty = 1;
            input.value = '1';
        }

        form.requestSubmit();
    });

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        var actionRaw = form.getAttribute('action') || '';
        var actionPath = '';
        try {
            actionPath = new URL(actionRaw, window.location.origin).pathname;
        } catch (err) {
            actionPath = actionRaw;
        }
        if (!/\/(Product|Category)\/(save|update)\/?$/i.test(actionPath)) return;

        e.preventDefault();
        var formData = new FormData(form);
        formData.set('_ajax', '1');

        fetch(actionRaw, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(parseJsonResponse)
        .then(function (data) {
            if (!data || !data.success) {
                showCartToast((data && data.message) ? data.message : 'Thao tác thất bại.', 'error');
                return;
            }
            showCartToast(data.message || 'Đã lưu thành công.');
            if (/\/save\/?$/i.test(actionPath)) {
                form.reset();
                var previewBox = document.getElementById('imagePreviewBox');
                if (previewBox) previewBox.style.display = 'none';
            }
        })
        .catch(function () {
            form.submit();
        });
    });

    document.addEventListener('click', function (e) {
        var link = e.target.closest('a');
        if (!link) return;
        var href = link.getAttribute('href') || '';
        if (!/^\/(Product|Category)\/delete\/\d+$/i.test(href)) return;
        if (!confirm('Bạn có chắc muốn xóa mục này?')) return;

        e.preventDefault();
        fetch(href + '?_ajax=1', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(parseJsonResponse)
        .then(function (data) {
            if (!data || !data.success) {
                showCartToast((data && data.message) ? data.message : 'Xóa thất bại.', 'error');
                return;
            }
            showCartToast(data.message || 'Đã xóa.');
            var row = link.closest('tr');
            if (row) {
                row.remove();
                return;
            }
            var card = link.closest('.product-card');
            if (card) card.remove();
        })
        .catch(function () {
            window.location.href = href;
        });
    });

    setupAdminSidebar();
    syncToastOffset();
    window.addEventListener('resize', syncToastOffset);
})();
</script>
</body>
</html>
