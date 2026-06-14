<?php include 'app/views/shares/header.php'; ?>

<style>
    .home-hero {
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.98), rgba(30, 41, 59, 0.94));
        color: #fff;
        border-radius: 28px;
        padding: 2rem;
        box-shadow: var(--card-shadow);
        position: relative;
        overflow: hidden;
    }

    .home-hero::before,
    .home-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.06);
        pointer-events: none;
    }

    .home-hero::before {
        width: 280px;
        height: 280px;
        top: -120px;
        right: -100px;
    }

    .home-hero::after {
        width: 180px;
        height: 180px;
        bottom: -90px;
        left: -40px;
    }

    .home-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(245, 158, 11, 0.15);
        border: 1px solid rgba(245, 158, 11, 0.3);
        color: #fbbf24;
        border-radius: 999px;
        padding: 0.3rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.3px;
        margin-bottom: 1rem;
    }

    .home-title {
        font-size: clamp(2rem, 4vw, 3.6rem);
        line-height: 1.05;
        font-weight: 900;
        margin-bottom: 0.9rem;
    }

    .home-copy {
        max-width: 640px;
        color: rgba(255, 255, 255, 0.78);
        font-size: 1.02rem;
        margin-bottom: 1.5rem;
    }

    .home-search {
        display: grid;
        grid-template-columns: 1.8fr 0.9fr 0.9fr 0.9fr auto;
        gap: 0.7rem;
        align-items: center;
    }

    .home-search .form-control,
    .home-search .form-select {
        height: 50px;
        border-radius: 14px;
        border-color: rgba(255, 255, 255, 0.15);
        background: rgba(255, 255, 255, 0.92);
    }

    .home-search .btn {
        height: 50px;
        border-radius: 14px;
        font-weight: 800;
        padding-inline: 1.1rem;
    }

    .home-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.35rem;
    }

    .home-meta-card {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 18px;
        padding: 0.85rem 1rem;
        min-width: 150px;
    }

    .home-meta-card strong {
        display: block;
        font-size: 1.15rem;
    }

    .section-shell {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(14px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 24px;
        padding: 1.25rem;
        box-shadow: var(--card-shadow);
        margin-top: 1.25rem;
    }

    .section-head {
        display: flex;
        justify-content: space-between;
        align-items: end;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .section-title {
        margin: 0;
        font-size: 1.45rem;
        font-weight: 900;
    }

    .section-subtitle {
        margin: 0.2rem 0 0;
        color: #64748b;
        font-size: 0.93rem;
    }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1rem;
    }

    .product-card {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(148, 163, 184, 0.15);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        flex-direction: column;
        min-height: 100%;
    }

    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.12);
    }

    .product-card__image {
        height: 210px;
        background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .product-card__image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 1rem;
    }

    .product-card__placeholder {
        width: 72px;
        height: 72px;
        border-radius: 20px;
        background: rgba(59, 130, 246, 0.08);
        color: #60a5fa;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
    }

    .product-card__body {
        padding: 1rem 1rem 0.9rem;
        flex: 1;
    }

    .product-card__category {
        color: #0ea5e9;
        font-size: 0.72rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 0.35rem;
    }

    .product-card__name {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0.45rem;
        min-height: 2.7rem;
    }

    .product-card__price {
        font-size: 1.15rem;
        font-weight: 900;
        color: #ef4444;
    }

    .product-card__footer {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.6rem;
        padding: 0 1rem 1rem;
    }

    .product-card__footer .btn {
        border-radius: 12px;
        min-height: 42px;
        font-weight: 800;
    }

    .loading-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1rem;
    }

    .loading-card {
        border-radius: 20px;
        background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 37%, #f3f4f6 63%);
        background-size: 400% 100%;
        animation: shimmer 1.4s ease infinite;
        min-height: 310px;
    }

    @keyframes shimmer {
        0% { background-position: 100% 0; }
        100% { background-position: -100% 0; }
    }

    .empty-state {
        text-align: center;
        padding: 3.5rem 1rem;
        color: #64748b;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 0.85rem;
        color: #94a3b8;
    }

    .pagination-wrap {
        display: flex;
        justify-content: center;
        gap: 0.35rem;
        flex-wrap: wrap;
        margin-top: 1.25rem;
    }

    .page-btn {
        min-width: 40px;
        min-height: 40px;
        border-radius: 12px;
        border: 1px solid #dbe1ea;
        background: #fff;
        color: #334155;
        font-weight: 800;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 0.75rem;
    }

    .page-btn.active {
        background: #0f172a;
        color: #fff;
        border-color: #0f172a;
        pointer-events: none;
    }

    .page-btn.disabled {
        opacity: 0.45;
        pointer-events: none;
    }

    @media (max-width: 991px) {
        .home-search {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 576px) {
        .home-hero {
            padding: 1.25rem;
            border-radius: 22px;
        }

        .home-search {
            grid-template-columns: 1fr;
        }

        .product-card__footer {
            grid-template-columns: 1fr;
        }
    }
</style>

<main class="container">
    <section class="home-hero">
        <div class="home-kicker"><i class="fas fa-bolt"></i> Mua sắm nhanh, dữ liệu từ API</div>
        <h1 class="home-title">Khám phá sản phẩm mới nhất ngay trên giao diện JavaScript</h1>
        <p class="home-copy">
            Trang chủ đang được chuyển sang mô hình API Backend + JavaScript Frontend.
            Danh sách sản phẩm, tìm kiếm và phân trang sẽ được tải trực tiếp từ <code>/api/products</code>.
        </p>

        <form id="homeSearchForm" class="home-search" autocomplete="off">
            <input type="search" name="search" class="form-control" placeholder="Tìm sản phẩm..." />
            <select name="category_id" id="categoryFilter" class="form-select">
                <option value="">Tất cả danh mục</option>
            </select>
            <input type="number" name="min_price" class="form-control" placeholder="Giá từ" min="0" step="1000" />
            <input type="number" name="max_price" class="form-control" placeholder="Giá đến" min="0" step="1000" />
            <button type="submit" class="btn btn-warning text-dark">
                <i class="fas fa-search me-1"></i>Tìm kiếm
            </button>
        </form>

        <div class="home-meta">
            <div class="home-meta-card">
                <strong id="homeTotalProducts">0</strong>
                <span>Tổng sản phẩm</span>
            </div>
            <div class="home-meta-card">
                <strong>API First</strong>
                <span>JWT + localStorage</span>
            </div>
            <div class="home-meta-card">
                <strong>Responsive</strong>
                <span>Mobile friendly</span>
            </div>
        </div>
    </section>

    <section class="section-shell">
        <div class="section-head">
            <div>
                <h2 class="section-title">Danh sách sản phẩm</h2>
                <p class="section-subtitle" id="productListHint">Đang tải sản phẩm từ API...</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label for="sortFilter" class="small text-muted fw-bold mb-0">Sắp xếp</label>
                <select id="sortFilter" class="form-select form-select-sm" style="width: 180px;">
                    <option value="created_at:desc">Mới nhất</option>
                    <option value="created_at:asc">Cũ nhất</option>
                    <option value="price:asc">Giá tăng dần</option>
                    <option value="price:desc">Giá giảm dần</option>
                    <option value="name:asc">Tên A-Z</option>
                </select>
            </div>
        </div>

        <div class="loading-grid" id="productLoadingGrid">
            <div class="loading-card"></div>
            <div class="loading-card"></div>
            <div class="loading-card"></div>
            <div class="loading-card"></div>
        </div>

        <div class="product-grid d-none" id="productGrid"></div>
        <div class="empty-state d-none" id="productEmptyState">
            <i class="fas fa-box-open d-block"></i>
            <h3 class="h5 fw-bold mb-1">Không tìm thấy sản phẩm</h3>
            <p class="mb-0">Hãy thử đổi từ khóa hoặc bộ lọc để xem thêm kết quả.</p>
        </div>

        <nav class="pagination-wrap" id="productPagination" aria-label="Pagination"></nav>
    </section>
</main>

<script src="/assets/js/frontend/pages/home.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
