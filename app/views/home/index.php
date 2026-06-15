<?php include 'app/views/shares/header.php'; ?>

<?php
require_once 'app/libs/ViewHelper.php';
$flash = ViewHelper::consumeFlash();
$errors = $flash['errors'];
$success = $flash['success'];

if (!function_exists('home_product_value')) {
    function home_product_value($product, string $key, mixed $default = null): mixed
    {
        if (is_array($product) && array_key_exists($key, $product)) {
            return $product[$key];
        }

        if (is_object($product) && isset($product->{$key})) {
            return $product->{$key};
        }

        return $default;
    }
}

if (!function_exists('home_product_image_exists')) {
    function home_product_image_exists($product): bool
    {
        $image = (string) home_product_value($product, 'image', '');
        return $image !== '' && file_exists($image);
    }
}
?>

<style>
/* ── Page title ──────────────────────────────────────────────────────── */
.page-title {
    font-size: 1.8rem; font-weight: 800;
    color: var(--dark); display: flex; align-items: center; gap: .6rem;
}
.page-title i { color: var(--primary); }

/* ════════════════════════════════════════════════════════════════════════
   PRODUCT SLIDESHOW BANNER
   ════════════════════════════════════════════════════════════════════════ */
.slider-wrap {
    position: relative;
    border-radius: 22px;
    overflow: hidden;
    margin-bottom: 2rem;
    height: 260px;
    box-shadow: 0 12px 40px rgba(79,70,229,.25);
    background: var(--dark);
}

/* Track */
.slider-track {
    display: flex;
    height: 100%;
    transition: transform .65s cubic-bezier(.77,0,.175,1);
    will-change: transform;
}

/* Each slide */
.slide {
    min-width: 100%;
    height: 100%;
    position: relative;
    display: flex;
    align-items: center;
    overflow: hidden;
}

/* Dynamic per-slide gradient injected via inline style */
.slide__bg {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
    filter: brightness(.45) saturate(1.2);
    transition: transform 8s ease;
    transform: scale(1.04);
}
.slide.is-active .slide__bg { transform: scale(1); }

/* Geometric accent shape */
.slide::before {
    content: '';
    position: absolute;
    right: -60px; top: -60px;
    width: 320px; height: 320px;
    border-radius: 50%;
    background: rgba(255,255,255,.04);
    pointer-events: none;
}
.slide::after {
    content: '';
    position: absolute;
    right: 80px; bottom: -80px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: rgba(255,255,255,.04);
    pointer-events: none;
}

/* Product image */
.slide__img-col {
    position: relative;
    z-index: 2;
    width: 230px;
    height: 100%;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
}
.slide__img {
    max-height: 190px;
    max-width: 200px;
    object-fit: contain;
    border-radius: 12px;
    filter: drop-shadow(0 8px 24px rgba(0,0,0,.5));
    transition: transform .4s ease;
}
.slide.is-active .slide__img { transform: translateY(-6px); }
.slide__img-placeholder {
    width: 140px; height: 140px;
    border-radius: 16px;
    background: rgba(255,255,255,.08);
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,.3); font-size: 3rem;
}

/* Text col */
.slide__body {
    position: relative;
    z-index: 2;
    flex: 1;
    padding: 1.5rem 2rem 1.5rem .5rem;
}
.slide__cat {
    display: inline-flex; align-items: center; gap: .35rem;
    font-size: .68rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: 2px;
    color: #fcd34d;
    background: rgba(245,158,11,.18);
    border: 1px solid rgba(245,158,11,.35);
    border-radius: 20px;
    padding: .18rem .7rem;
    margin-bottom: .65rem;
}
.slide__name {
    font-size: 1.5rem; font-weight: 800;
    color: #fff; line-height: 1.25;
    margin-bottom: .5rem;
    /* clamp to 2 lines */
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.slide__price {
    font-size: 1.6rem; font-weight: 800;
    color: #fcd34d;
    margin-bottom: 1.1rem;
}
.slide__price small {
    font-size: .85rem; font-weight: 600; color: rgba(255,255,255,.5);
}
.slide__actions { display: flex; gap: .6rem; flex-wrap: wrap; }
.slide__btn-primary {
    display: inline-flex; align-items: center; gap: .45rem;
    background: var(--accent);
    color: var(--dark); font-weight: 800; font-size: .82rem;
    border-radius: 22px; padding: .5rem 1.3rem;
    text-decoration: none;
    transition: background .2s, transform .2s, box-shadow .2s;
    box-shadow: 0 4px 14px rgba(245,158,11,.4);
}
.slide__btn-primary:hover {
    background: #fcd34d; color: var(--dark); text-decoration: none;
    transform: translateY(-2px);
}
.slide__btn-secondary {
    display: inline-flex; align-items: center; gap: .45rem;
    background: rgba(255,255,255,.12);
    border: 1.5px solid rgba(255,255,255,.25);
    color: rgba(255,255,255,.85); font-weight: 700; font-size: .82rem;
    border-radius: 22px; padding: .5rem 1.3rem;
    text-decoration: none;
    transition: background .2s;
}
.slide__btn-secondary:hover {
    background: rgba(255,255,255,.2); color: #fff; text-decoration: none;
}

/* Counter badge */
.slider-counter {
    position: absolute;
    top: 1rem; right: 1rem;
    z-index: 10;
    background: rgba(0,0,0,.35);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,.15);
    color: rgba(255,255,255,.85);
    font-size: .72rem; font-weight: 800;
    border-radius: 20px;
    padding: .22rem .7rem;
    letter-spacing: .5px;
}

/* Prev / Next arrows */
.slider-arrow {
    position: absolute;
    top: 50%; transform: translateY(-50%);
    z-index: 10;
    width: 38px; height: 38px;
    border-radius: 50%;
    background: rgba(255,255,255,.12);
    border: 1.5px solid rgba(255,255,255,.2);
    backdrop-filter: blur(6px);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: background .2s, transform .2s;
    font-size: .85rem;
}
.slider-arrow:hover { background: rgba(255,255,255,.25); transform: translateY(-50%) scale(1.1); }
.slider-arrow--prev { left: 1rem; }
.slider-arrow--next { right: 1rem; }

/* Dots */
.slider-dots {
    position: absolute;
    bottom: .85rem; left: 50%; transform: translateX(-50%);
    z-index: 10;
    display: flex; gap: .4rem; align-items: center;
}
.slider-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: rgba(255,255,255,.35);
    cursor: pointer;
    transition: background .25s, width .3s, border-radius .3s;
    border: none; padding: 0;
}
.slider-dot.is-active {
    background: var(--accent);
    width: 22px;
    border-radius: 4px;
}

/* Progress bar */
.slider-progress {
    position: absolute;
    bottom: 0; left: 0;
    height: 3px;
    background: var(--accent);
    z-index: 10;
    transform-origin: left;
    animation: progressBar 4.5s linear infinite;
    border-radius: 0 2px 2px 0;
}
@keyframes progressBar {
    from { width: 0; }
    to   { width: 100%; }
}
.slider-progress.paused { animation-play-state: paused; }

/* ── Search result info ──────────────────────────────────────────────── */
.search-result-info {
    font-size: .88rem; color: #6b7280; font-weight: 600;
    margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem; flex-wrap: wrap;
}
.search-result-info strong { color: var(--primary); }
.badge-keyword {
    background: #eef0ff; color: var(--primary);
    border-radius: 6px; padding: .15rem .5rem;
    font-size: .82rem; font-weight: 700;
}

/* ── Product grid ────────────────────────────────────────────────────── */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1.5rem;
}
.product-card {
    background: #fff; border-radius: 16px;
    box-shadow: var(--card-shadow); overflow: hidden;
    transition: transform .25s, box-shadow .25s;
    display: flex; flex-direction: column;
}
.product-card:hover { transform: translateY(-5px); box-shadow: 0 12px 32px rgba(79,70,229,.18); }
.product-card__img-wrap {
    width: 100%; height: 200px; background: #eef0ff;
    display: flex; align-items: center; justify-content: center; overflow: hidden;
}
.product-card__img { max-width:100%; max-height:200px; width:auto; height:auto; object-fit:contain; display:block; padding:8px; }
.product-card__img-placeholder {
    width:100%; height:200px;
    background:linear-gradient(135deg,#eef0ff,#e0e7ff);
    display:flex; align-items:center; justify-content:center; color:#a5b4fc; font-size:2.5rem;
}
.product-card__body { padding:1rem 1.1rem .8rem; flex:1; display:flex; flex-direction:column; }
.product-card__category { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--primary); margin-bottom:.3rem; }
.product-card__name { font-size:1rem; font-weight:700; color:var(--dark); margin-bottom:.4rem; flex:1; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.product-card__price { font-size:1.15rem; font-weight:800; color:var(--primary); }
.product-card__price span { font-size:.8rem; font-weight:600; color:#9ca3af; }
.product-card__footer {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:.5rem;
    padding:.7rem 1.1rem 1rem;
    border-top:1px solid #f1f1ff;
}
.product-card__footer .btn {
    font-size:.78rem;
    padding:.46rem .52rem;
    border-radius:8px;
    min-height:40px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:.3rem;
    font-weight:800;
    line-height:1.2;
    text-align:center;
}
.product-card__action-form {
    margin:0;
    width:100%;
}
.product-card__action-form .btn {
    width:100%;
}

/* ── Empty state ─────────────────────────────────────────────────────── */
.empty-state { text-align:center; padding:4rem 2rem; color:#9ca3af; }
.empty-state i { font-size:4rem; margin-bottom:1rem; display:block; }

/* ── Pagination ──────────────────────────────────────────────────────── */
.pagination-wrap { display:flex; justify-content:center; align-items:center; margin-top:2.5rem; gap:.35rem; flex-wrap:wrap; }
.page-btn { min-width:38px; height:38px; border-radius:10px; border:1.5px solid #e5e7eb; background:#fff; color:#374151; font-weight:700; font-size:.88rem; display:inline-flex; align-items:center; justify-content:center; text-decoration:none; transition:all .2s; padding:0 .6rem; }
.page-btn:hover { background:#eef0ff; border-color:var(--primary); color:var(--primary); text-decoration:none; }
.page-btn.active { background:var(--primary); border-color:var(--primary); color:#fff; pointer-events:none; }
.page-btn.disabled { opacity:.4; pointer-events:none; cursor:default; }
.page-ellipsis { min-width:32px; height:38px; display:inline-flex; align-items:center; justify-content:center; color:#9ca3af; font-weight:700; }
</style>

<?php
// Chuẩn bị dữ liệu cho slider — lấy tối đa 6 sản phẩm có ảnh ưu tiên
$sliderProducts = array_values(array_filter($products, fn($p) =>
    home_product_image_exists($p)
));
// Nếu không đủ ảnh, thêm sản phẩm không ảnh vào
if (count($sliderProducts) < 3) {
    foreach ($products as $p) {
        if (!in_array($p, $sliderProducts)) $sliderProducts[] = $p;
        if (count($sliderProducts) >= 6) break;
    }
}
$sliderProducts = array_slice($sliderProducts, 0, 6);

// Màu gradient cho mỗi slide
$slideGradients = [
    'linear-gradient(135deg,#1e1b4b 0%,#3730a3 60%,#4f46e5 100%)',
    'linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%)',
    'linear-gradient(135deg,#2d1b69 0%,#11998e 100%)',
    'linear-gradient(135deg,#1e3a5f 0%,#2980b9 100%)',
    'linear-gradient(135deg,#4a0048 0%,#6a0572 50%,#a300d4 100%)',
    'linear-gradient(135deg,#0f2027 0%,#203a43 50%,#2c5364 100%)',
];
?>

<!-- ══════════════════════════════════════════════════════════════════════
     PRODUCT SLIDESHOW BANNER (chỉ hiện khi không tìm kiếm)
     ══════════════════════════════════════════════════════════════════════ -->
<?php if ($search === '' && count($sliderProducts) > 0): ?>
<div class="slider-wrap" id="productSlider">

    <!-- Progress bar -->
    <div class="slider-progress" id="sliderProgress"></div>

    <!-- Counter -->
    <div class="slider-counter">
        <span id="sliderCurrent">1</span> / <?php echo count($sliderProducts); ?>
    </div>

    <!-- Arrows -->
    <button class="slider-arrow slider-arrow--prev" id="sliderPrev" aria-label="Trước">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="slider-arrow slider-arrow--next" id="sliderNext" aria-label="Sau">
        <i class="fas fa-chevron-right"></i>
    </button>

    <!-- Track -->
    <div class="slider-track" id="sliderTrack">
        <?php foreach ($sliderProducts as $i => $sp):
            $grad = $slideGradients[$i % count($slideGradients)];
        ?>
        <div class="slide <?php echo $i === 0 ? 'is-active' : ''; ?>">

            <!-- Background gradient -->
            <div class="slide__bg" style="background: <?php echo $grad; ?>;"></div>

            <!-- Product image -->
            <div class="slide__img-col">
                <?php if (home_product_image_exists($sp)): ?>
                    <img src="/<?php echo htmlspecialchars((string) home_product_value($sp, 'image', '')); ?>"
                         alt="<?php echo htmlspecialchars((string) home_product_value($sp, 'name', '')); ?>"
                         class="slide__img">
                <?php else: ?>
                    <div class="slide__img-placeholder"><i class="fas fa-box-open"></i></div>
                <?php endif; ?>
            </div>

            <!-- Text -->
            <div class="slide__body">
                <div class="slide__cat">
                    <i class="fas fa-tag"></i>
                    <?php echo htmlspecialchars((string) home_product_value($sp, 'category_name', 'Sản phẩm nổi bật')); ?>
                </div>
                <div class="slide__name"><?php echo htmlspecialchars((string) home_product_value($sp, 'name', '')); ?></div>
                <div class="slide__price">
                    <?php echo number_format((float) home_product_value($sp, 'price', 0), 0, ',', '.'); ?>
                    <small>₫</small>
                </div>
                <div class="slide__actions">
                    <a href="/Product/show/<?php echo (int) home_product_value($sp, 'id', 0); ?>"
                       class="slide__btn-primary">
                        <i class="fas fa-eye"></i> Xem chi tiết
                    </a>
                </div>
            </div>

        </div>
        <?php endforeach; ?>
    </div>

    <!-- Dots -->
    <div class="slider-dots" id="sliderDots">
        <?php for ($i = 0; $i < count($sliderProducts); $i++): ?>
            <button class="slider-dot <?php echo $i === 0 ? 'is-active' : ''; ?>"
                    data-index="<?php echo $i; ?>" aria-label="Slide <?php echo $i+1; ?>"></button>
        <?php endfor; ?>
    </div>

</div>

<script>
(function () {
    const track    = document.getElementById('sliderTrack');
    const dots     = document.querySelectorAll('.slider-dot');
    const slides   = document.querySelectorAll('.slide');
    const progress = document.getElementById('sliderProgress');
    const counter  = document.getElementById('sliderCurrent');
    const total    = slides.length;
    let current    = 0;
    let timer      = null;
    const INTERVAL = 4500;

    function goTo(idx) {
        slides[current].classList.remove('is-active');
        dots[current].classList.remove('is-active');
        current = (idx + total) % total;
        slides[current].classList.add('is-active');
        dots[current].classList.add('is-active');
        track.style.transform = `translateX(-${current * 100}%)`;
        counter.textContent   = current + 1;
        // reset progress animation
        progress.style.animation = 'none';
        progress.offsetHeight;   // reflow
        progress.style.animation = '';
    }

    function startAuto() {
        clearInterval(timer);
        timer = setInterval(() => goTo(current + 1), INTERVAL);
    }

    // Arrows
    document.getElementById('sliderPrev').addEventListener('click', () => { goTo(current - 1); startAuto(); });
    document.getElementById('sliderNext').addEventListener('click', () => { goTo(current + 1); startAuto(); });

    // Dots
    dots.forEach(dot => {
        dot.addEventListener('click', () => { goTo(+dot.dataset.index); startAuto(); });
    });

    // Pause on hover
    const wrap = document.getElementById('productSlider');
    wrap.addEventListener('mouseenter', () => {
        clearInterval(timer);
        progress.classList.add('paused');
    });
    wrap.addEventListener('mouseleave', () => {
        progress.classList.remove('paused');
        startAuto();
    });

    // Touch / swipe
    let touchX = 0;
    wrap.addEventListener('touchstart', e => { touchX = e.touches[0].clientX; }, { passive: true });
    wrap.addEventListener('touchend',   e => {
        const diff = touchX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 40) { goTo(diff > 0 ? current + 1 : current - 1); startAuto(); }
    });

    startAuto();
})();
</script>
<?php endif; ?>

<?php require 'app/views/shares/flash.php'; ?>

<!-- ── Header row ─────────────────────────────────────────────────────── -->
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap" style="gap:1rem; margin-top: <?php echo ($search === '' && count($sliderProducts) > 0) ? '0' : '0'; ?>">
    <h1 class="page-title mb-0">
        <i class="fas fa-box-open"></i>
        <?php echo $search !== '' ? 'Kết quả tìm kiếm' : 'Danh sách sản phẩm'; ?>
        <small class="text-muted" style="font-size:.85rem; font-weight:600;">(<?php echo $total; ?>)</small>
    </h1>
    
</div>

<!-- Thông tin kết quả tìm kiếm -->
<?php if ($search !== ''): ?>
<div class="search-result-info mb-3">
    <?php if ($total > 0): ?>
        <i class="fas fa-search" style="color:var(--primary)"></i>
        Tìm thấy <strong><?php echo $total; ?></strong> sản phẩm cho
        <span class="badge-keyword"><?php echo htmlspecialchars($search); ?></span>
    <?php else: ?>
        <i class="fas fa-search"></i>
        Không tìm thấy sản phẩm nào cho
        <span class="badge-keyword"><?php echo htmlspecialchars($search); ?></span>
    <?php endif; ?>
    &nbsp;·&nbsp;
    <a href="/Product" style="color:var(--primary); font-weight:700;">
        <i class="fas fa-times mr-1"></i>Xem tất cả
    </a>
</div>
<?php endif; ?>

<!-- ── Product grid ────────────────────────────────────────────────────── -->
<?php if (empty($products)): ?>
    <div class="empty-state">
        <i class="fas fa-box-open"></i>
        <?php if ($search !== ''): ?>
            <p class="font-weight-700 h5">Không tìm thấy sản phẩm nào</p>
            <a href="/Product" class="btn btn-outline-primary mt-2">
                <i class="fas fa-list mr-1"></i> Xem tất cả
            </a>
        <?php else: ?>
            <p class="font-weight-700 h5">Chưa có sản phẩm nào</p>
            
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
        <div class="product-card">
        <?php if (home_product_image_exists($product)): ?>
    <a href="/Product/show/<?php echo (int) home_product_value($product, 'id', 0); ?>" 
       class="product-card__img-wrap">
        <img src="/<?php echo htmlspecialchars((string) home_product_value($product, 'image', '')); ?>"
             alt="<?php echo htmlspecialchars((string) home_product_value($product, 'name', '')); ?>"
             class="product-card__img">
    </a>
<?php else: ?>
    <a href="/Product/show/<?php echo (int) home_product_value($product, 'id', 0); ?>" 
       class="product-card__img-placeholder">
        <i class="fas fa-image"></i>
    </a>
<?php endif; ?>

            <div class="product-card__body">
                <div class="product-card__category">
                    <?php echo htmlspecialchars((string) home_product_value($product, 'category_name', 'Chưa phân loại')); ?>
                </div>
                <div class="product-card__name"><?php echo htmlspecialchars((string) home_product_value($product, 'name', '')); ?></div>
                <div class="product-card__price">
                    <?php echo number_format((float) home_product_value($product, 'price', 0), 0, ',', '.'); ?><span> ₫</span>
                </div>
            </div>

            <div class="product-card__footer">
                <a href="/Product/show/<?php echo (int) home_product_value($product, 'id', 0); ?>"
                   class="btn btn-primary btn-sm w-100" title="Xem chi tiết">
                    <i class="fas fa-eye"></i> Xem chi tiết
                </a>
                <form action="/Cart/add" method="POST" class="product-card__action-form">
                    <input type="hidden" name="product_id" value="<?php echo (int) home_product_value($product, 'id', 0); ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn-success btn-sm" title="Thêm vào giỏ hàng">
                        <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Pagination ────────────────────────────────────────────────────── -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination-wrap">
        <a href="?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($search); ?>"
           class="page-btn <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
            <i class="fas fa-chevron-left"></i>
        </a>
        <?php
        $pages = [];
        for ($p = 1; $p <= $totalPages; $p++) {
            if ($p == 1 || $p == $totalPages || abs($p - $currentPage) <= 2) $pages[] = $p;
        }
        $prev = null;
        foreach ($pages as $p):
            if ($prev !== null && $p - $prev > 1): ?>
                <span class="page-ellipsis">…</span>
            <?php endif; ?>
            <a href="?page=<?php echo $p; ?>&search=<?php echo urlencode($search); ?>"
               class="page-btn <?php echo $p == $currentPage ? 'active' : ''; ?>">
                <?php echo $p; ?>
            </a>
        <?php $prev = $p; endforeach; ?>
        <a href="?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($search); ?>"
           class="page-btn <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
            <i class="fas fa-chevron-right"></i>
        </a>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php include 'app/views/shares/footer.php'; ?>
