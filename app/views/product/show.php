<?php include 'app/views/shares/header.php'; ?>

<style>
    .detail-shell {
        background: rgba(255,255,255,.76);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,.55);
        border-radius: 24px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        max-width: 980px;
        margin: 0 auto;
    }
    .detail-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #fff;
        padding: 1.25rem 1.5rem;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
    }
    .detail-image {
        min-height: 420px;
        background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
    }
    .detail-image img {
        max-width: 100%;
        max-height: 360px;
        object-fit: contain;
    }
    .detail-image .placeholder {
        font-size: 5rem;
        color: #a5b4fc;
    }
    .detail-body {
        padding: 1.5rem;
    }
    .detail-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .3rem .7rem;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        font-size: .75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .detail-title {
        font-size: clamp(1.7rem, 3vw, 2.4rem);
        font-weight: 900;
        color: #0f172a;
        margin: .85rem 0 .5rem;
    }
    .detail-price {
        font-size: 1.7rem;
        font-weight: 900;
        color: #ef4444;
        margin-bottom: 1rem;
    }
    .detail-desc {
        color: #475569;
        line-height: 1.75;
        margin-bottom: 1.5rem;
        white-space: pre-line;
    }
    .detail-actions {
        display: flex;
        gap: .75rem;
        flex-wrap: wrap;
    }
    .detail-meta {
        font-size: .85rem;
        color: #94a3b8;
    }
    @media (max-width: 991px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
        .detail-image {
            min-height: 320px;
        }
    }
</style>

<main class="container">
    <section class="detail-shell">
        <div class="detail-hero">
            <h1 class="h4 fw-bold mb-1">Chi tiết sản phẩm</h1>
            <p class="mb-0 text-white-50">Dữ liệu được load trực tiếp từ <code>/api/products/{id}</code>.</p>
        </div>

        <div class="p-3 p-md-4">
            <div id="productDetailAlert" class="alert d-none" role="alert"></div>
            <div id="productDetailLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <div class="mt-3 text-muted">Đang tải sản phẩm...</div>
            </div>

            <div id="productDetailContent" class="d-none">
                <div class="detail-grid">
                    <div class="detail-image">
                        <img id="productDetailImage" alt="">
                        <div id="productDetailPlaceholder" class="placeholder d-none"><i class="fas fa-box-open"></i></div>
                    </div>
                    <div class="detail-body">
                        <div id="productDetailCategory" class="detail-badge">Danh mục</div>
                        <h2 id="productDetailName" class="detail-title">Tên sản phẩm</h2>
                        <div id="productDetailPrice" class="detail-price">0 ₫</div>
                        <div id="productDetailDesc" class="detail-desc"></div>
                        <div class="detail-meta mb-3">Mã sản phẩm: <strong id="productDetailId">#0</strong></div>

                        <div class="detail-actions">
                            <button type="button" id="productAddToCartBtn" class="btn btn-success btn-lg">
                                <i class="fas fa-cart-plus me-1"></i> Thêm vào giỏ
                            </button>
                            <a href="/Home" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="/assets/js/frontend/pages/product-detail-page.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
