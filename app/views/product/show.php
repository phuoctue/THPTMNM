<?php include 'app/views/shares/header.php'; ?>

<style>
.detail-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
    overflow: hidden;
    max-width: 760px;
    margin: 0 auto;
}
.detail-card__img-wrap {
    width: 100%;
    height: 320px;
    background: linear-gradient(135deg,#eef0ff,#e0e7ff);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 16px;
}
.detail-card__img-wrap img {
    max-width: 100%;
    max-height: 288px;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 8px;
}
.detail-card__img-placeholder { color: #a5b4fc; font-size: 5rem; }
.detail-card__body { padding: 2rem 2.5rem; }
.detail-card__category {
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: var(--primary);
    background: #eef0ff;
    display: inline-block;
    padding: .25rem .75rem;
    border-radius: 20px;
    margin-bottom: 1rem;
}
.detail-card__name { font-size: 1.8rem; font-weight: 800; color: var(--dark); margin-bottom: .5rem; }
.detail-card__price { font-size: 1.5rem; font-weight: 800; color: var(--primary); margin-bottom: 1.2rem; }
.detail-card__desc { color: #6b7280; line-height: 1.7; font-size: .95rem; margin-bottom: 1.5rem; }
.detail-card__id { font-size: .8rem; color: #9ca3af; margin-bottom: 1.5rem; }
</style>

<div class="detail-card">
    <div class="detail-card__img-wrap">
        <?php if (!empty($product->image) && file_exists($product->image)): ?>
            <img src="/<?php echo htmlspecialchars($product->image); ?>" alt="<?php echo htmlspecialchars($product->name); ?>">
        <?php else: ?>
            <div class="detail-card__img-placeholder"><i class="fas fa-image"></i></div>
        <?php endif; ?>
    </div>

    <div class="detail-card__body">
        <div class="detail-card__id"># <?php echo $product->id; ?></div>
        <div class="detail-card__category"><?php echo htmlspecialchars($product->category_name ?? 'Chưa phân loại'); ?></div>
        <div class="detail-card__name"><?php echo htmlspecialchars($product->name); ?></div>
        <div class="detail-card__price"><?php echo number_format($product->price, 0, ',', '.'); ?> đ</div>
        <div class="detail-card__desc"><?php echo nl2br(htmlspecialchars($product->description)); ?></div>

        <div class="d-flex flex-wrap" style="gap:.75rem">
            <form action="/Cart/add" method="POST" class="mb-0">
                <input type="hidden" name="product_id" value="<?php echo (int)$product->id; ?>">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-cart-plus mr-1"></i> Thêm vào giỏ
                </button>
            </form>
            <a href="/Product/edit/<?php echo $product->id; ?>" class="btn btn-warning">
                <i class="fas fa-edit mr-1"></i> Chỉnh sửa
            </a>
            <a href="/Product" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Quay lại
            </a>
            <a href="/Product/delete/<?php echo $product->id; ?>" class="btn btn-danger ml-auto">
                <i class="fas fa-trash mr-1"></i> Xóa
            </a>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>


