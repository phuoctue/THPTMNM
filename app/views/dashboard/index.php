<?php include 'app/views/shares/header.php'; ?>

<style>
.dash-title { font-size: 2rem; font-weight: 800; color: #1e1b4b; margin-bottom: .2rem; }
.dash-subtitle { color: #6b7280; font-weight: 600; margin-bottom: 1.5rem; }
.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(30, 27, 75, .08);
    padding: 1rem 1.1rem;
    border: 1px solid #eef0ff;
}
.stat-label { color: #6b7280; font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; }
.stat-value { color: #1e1b4b; font-size: 1.7rem; font-weight: 800; line-height: 1.2; margin-top: .35rem; }
.dash-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(30, 27, 75, .08);
    border: 1px solid #eef0ff;
}
.dash-card .card-header {
    background: transparent;
    border-bottom: 1px solid #f2f4ff;
    font-weight: 800;
    color: #1e1b4b;
}
.top-product-row { display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:.75rem 0; border-bottom:1px solid #f3f4ff; }
.top-product-row:last-child { border-bottom:none; }
.tp-left { display:flex; align-items:center; min-width:0; gap:.7rem; }
.tp-img { width:44px; height:44px; border-radius:8px; object-fit:cover; background:#eef0ff; }
.tp-name { font-weight:700; color:#1e1b4b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:210px; }
.tp-meta { font-size:.82rem; color:#6b7280; }
</style>

<div class="container mt-4">
    <h1 class="dash-title"><i class="fas fa-chart-line text-primary mr-2"></i>Dashboard</h1>
    <div class="dash-subtitle">Tổng quan nhanh hoạt động cửa hàng</div>

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">Sản phẩm</div>
            <div class="stat-value"><?php echo number_format((int)$summary['product_count']); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Danh mục</div>
            <div class="stat-value"><?php echo number_format((int)$summary['category_count']); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Đơn hàng</div>
            <div class="stat-value"><?php echo number_format((int)$summary['order_count']); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Doanh thu</div>
            <div class="stat-value"><?php echo number_format((int)$summary['revenue_total'], 0, ',', '.'); ?> đ</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="dash-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-receipt mr-2"></i>Đơn hàng gần đây</span>
                    <a href="/Cart/orders" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Tổng</th>
                                <th>Trạng thái</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($recentOrders)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Chưa có đơn hàng.</td></tr>
                        <?php else: foreach ($recentOrders as $o): ?>
                            <tr>
                                <td>#<?php echo (int)$o->id; ?></td>
                                <td><?php echo htmlspecialchars($o->customer_name); ?></td>
                                <td><?php echo number_format((int)$o->total_price, 0, ',', '.'); ?> đ</td>
                                <td><?php echo htmlspecialchars($o->status); ?></td>
                                <td><a href="/Cart/orderDetail/<?php echo (int)$o->id; ?>" class="btn btn-sm btn-primary">Chi tiết</a></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="dash-card">
                <div class="card-header"><i class="fas fa-star mr-2"></i>Sản phẩm bán chạy</div>
                <div class="card-body pt-2 pb-2">
                    <?php if (empty($topProducts)): ?>
                        <div class="text-muted py-3">Chưa có dữ liệu bán hàng.</div>
                    <?php else: foreach ($topProducts as $p): ?>
                        <div class="top-product-row">
                            <div class="tp-left">
                                <?php if (!empty($p->image) && file_exists($p->image)): ?>
                                    <img src="/<?php echo htmlspecialchars($p->image); ?>" alt="<?php echo htmlspecialchars($p->name); ?>" class="tp-img">
                                <?php else: ?>
                                    <div class="tp-img d-flex align-items-center justify-content-center"><i class="fas fa-image text-muted"></i></div>
                                <?php endif; ?>
                                <div>
                                    <div class="tp-name"><?php echo htmlspecialchars($p->name); ?></div>
                                    <div class="tp-meta">Đã bán: <?php echo (int)$p->sold_qty; ?></div>
                                </div>
                            </div>
                            <div class="tp-meta"><?php echo number_format((int)$p->sold_amount, 0, ',', '.'); ?> đ</div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
