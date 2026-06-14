</div><!-- /.container -->
</div><!-- /.page-wrapper -->

<footer class="bg-dark text-white mt-auto py-4">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-md-6">
                <h5 class="fw-bold mb-1">
                    <i class="fas fa-store text-warning me-1"></i> MyStore
                </h5>
                <p class="text-muted small mb-0">
                    Giao diện đang chuyển sang kiến trúc API Backend + JavaScript Frontend.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="/Home" class="text-muted text-decoration-none me-3">Trang chủ</a>
                <a href="/Cart" class="text-muted text-decoration-none me-3">Giỏ hàng</a>
                <a href="/auth/login" class="text-muted text-decoration-none">Đăng nhập</a>
            </div>
        </div>

        <hr class="border-secondary-subtle my-3">

        <p class="text-center text-muted small mb-0">
            &copy; <?php echo date('Y'); ?> MyStore. All rights reserved.
        </p>
    </div>
</footer>

<div id="appToastWrap" aria-live="polite" aria-atomic="true" style="position: fixed; top: 84px; right: 16px; z-index: 11000; pointer-events: none;">
    <div id="appToast" class="toast shadow" role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 280px; pointer-events: auto;">
        <div id="appToastHeader" class="toast-header bg-success text-white">
            <i class="fas fa-check-circle me-2"></i>
            <strong class="me-auto" id="appToastTitle">Thông báo</strong>
            <button type="button" class="btn-close btn-close-white ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="appToastBody">Đã xử lý thành công.</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/frontend/core/ui.js"></script>
<script src="/assets/js/frontend/core/api.js"></script>
<script src="/assets/js/frontend/core/auth.js"></script>
</body>
</html>
