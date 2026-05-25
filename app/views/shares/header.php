<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qu?n l� c?a h�ng</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary:#4f46e5; --primary-d:#3730a3; --accent:#f59e0b; --dark:#1e1b4b; --light-bg:#f5f5ff; --card-shadow:0 4px 20px rgba(79,70,229,.12);}        
        * { box-sizing:border-box; }
        html, body { margin:0; padding:0; }
        body { margin:0; font-family:'Nunito',sans-serif; background:var(--light-bg); color:#374151; min-height:100vh; display:flex; flex-direction:column; }
        :root { --navbar-height: 72px; }
        .main-navbar { background:rgba(30,27,75,.95); padding:.75rem 1.25rem; position:fixed; top:0; left:0; right:0; width:100%; z-index:9999; backdrop-filter:blur(12px); box-shadow:0 4px 18px rgba(0,0,0,.18); display:flex; align-items:center; justify-content:space-between; }
        .main-navbar .navbar-brand { font-size:1.4rem; font-weight:800; color:#fff!important; margin-right:1.5rem; }
        .main-navbar .navbar-brand span { color:var(--accent); }
        .navbar-actions { display:flex; align-items:center; gap:.65rem; }
        .btn-manage {
            border:none;
            border-radius:10px;
            background:linear-gradient(180deg, #5c53f0 0%, #4f46e5 100%);
            color:#fff;
            font-weight:800;
            font-size:.92rem;
            padding:.5rem .95rem;
            box-shadow:0 8px 18px rgba(79,70,229,.35);
        }
        .btn-manage:hover { background:#4338ca; color:#fff; }
        .btn-cart-link {
            display:inline-flex;
            align-items:center;
            gap:.42rem;
            text-decoration:none;
            color:#fff;
            font-weight:700;
            border-radius:10px;
            padding:.48rem .8rem;
            background:rgba(255,255,255,.1);
            border:1px solid rgba(255,255,255,.14);
        }
        .btn-cart-link:hover { color:#fff; text-decoration:none; background:rgba(255,255,255,.16); }
        .btn-cart-link .badge { font-size:.75rem; }
        .btn-home-link {
            display:inline-flex;
            align-items:center;
            gap:.42rem;
            text-decoration:none;
            color:#fff;
            font-weight:700;
            border-radius:10px;
            padding:.48rem .8rem;
            background:rgba(255,255,255,.1);
            border:1px solid rgba(255,255,255,.14);
        }
        .btn-home-link:hover { color:#fff; text-decoration:none; background:rgba(255,255,255,.16); }
        .admin-sidebar-backdrop {
            position:fixed; inset:0; background:rgba(15, 23, 42, .35);
            opacity:0; pointer-events:none; transition:opacity .22s ease; z-index:9950;
        }
        .admin-sidebar {
            position:fixed; top:var(--navbar-height); left:0; bottom:0;
            width:250px; background:#1f1b4f; color:#fff;
            transform:translateX(-100%); transition:transform .25s ease;
            z-index:9960; padding:1rem .8rem; overflow-y:auto;
            border-right:1px solid rgba(255,255,255,.08);
        }
        .admin-sidebar h6 {
            font-size:.75rem; letter-spacing:1px; text-transform:uppercase;
            color:rgba(255,255,255,.55); margin:.4rem .5rem .7rem;
        }
        .admin-nav-link {
            display:flex; align-items:center; gap:.58rem; color:rgba(255,255,255,.84);
            text-decoration:none; padding:.62rem .72rem; border-radius:9px; font-weight:700; margin-bottom:.34rem;
        }
        .admin-nav-link:hover { background:rgba(255,255,255,.1); color:#fff; text-decoration:none; }
        body.admin-mode .admin-sidebar { transform:translateX(0); }
        body.admin-mode .admin-sidebar-backdrop { opacity:1; pointer-events:auto; }
        body.admin-mode .page-wrapper { padding-left:250px; transition:padding-left .25s ease; }
        @media (max-width: 991px) {
            body.admin-mode .page-wrapper { padding-left:0; }
        }
        .page-wrapper { flex:1; padding:calc(var(--navbar-height) + 2rem) 0 3rem; }
        .btn { font-weight:700; border-radius:8px; }
        .btn-primary { background:var(--primary); border-color:var(--primary); }
    </style>
</head>
<body>
<?php
$navSearch = trim($_GET['search'] ?? '');
$cartQty = array_sum(array_column($_SESSION['cart'] ?? [], 'quantity'));
?>
<nav class="main-navbar">
    <a class="navbar-brand mb-0" href="/Home"><i class="fas fa-store"></i> Shop<span>Admin</span></a>
    <div class="navbar-actions">
        <a class="btn-home-link" href="/Home">
            <i class="fas fa-home"></i> Trang chủ
        </a>
        <button id="manageToggleBtn" type="button" class="btn-manage">
            <i class="fas fa-bars mr-1"></i> Quản lý
        </button>
        <a class="btn-cart-link" href="/Cart">
            <i class="fas fa-shopping-cart"></i> Giỏ hàng
            <span id="cartQtyBadge" class="badge badge-warning"><?php echo (int)$cartQty; ?></span>
        </a>
    </div>
</nav>

<div id="adminSidebarBackdrop" class="admin-sidebar-backdrop"></div>
<aside id="adminSidebar" class="admin-sidebar">
    <h6>Điều hướng quản trị</h6>
    <a href="/Dashboard" class="admin-nav-link"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="/Product" class="admin-nav-link"><i class="fas fa-box-open"></i> Sản phẩm</a>
    <a href="/Category" class="admin-nav-link"><i class="fas fa-tags"></i> Danh mục</a>
    <a href="/Cart/orders" class="admin-nav-link"><i class="fas fa-receipt"></i> Đơn hàng</a>
</aside>

<div class="page-wrapper">
    <div class="container">
        <?php if (!empty($_SESSION['cart_error'])): ?>
            <div class="alert alert-danger mt-2"><?php echo htmlspecialchars($_SESSION['cart_error']); unset($_SESSION['cart_error']); ?></div>
        <?php endif; ?>

