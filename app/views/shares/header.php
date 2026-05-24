<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý cửa hàng</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary:#4f46e5; --primary-d:#3730a3; --accent:#f59e0b; --dark:#1e1b4b; --light-bg:#f5f5ff; --card-shadow:0 4px 20px rgba(79,70,229,.12);}        
        * { box-sizing:border-box; }
        body { font-family:'Nunito',sans-serif; background:var(--light-bg); color:#374151; min-height:100vh; display:flex; flex-direction:column; }
        .main-navbar { background:rgba(30,27,75,.92); padding:.6rem 1.5rem; position:sticky; top:0; z-index:9999; backdrop-filter:blur(12px); box-shadow:0 2px 12px rgba(0,0,0,.25); }
        .main-navbar .navbar-brand { font-size:1.4rem; font-weight:800; color:#fff!important; margin-right:1.5rem; }
        .main-navbar .navbar-brand span { color:var(--accent); }
        .main-navbar .nav-link { color:rgba(255,255,255,.75)!important; font-weight:600; border-radius:8px; padding:.45rem .9rem!important; }
        .main-navbar .nav-link:hover, .main-navbar .nav-link.active { background:rgba(255,255,255,.12); color:#fff!important; }
        .navbar-search { display:flex; align-items:center; gap:.4rem; margin:0 1rem; }
        .navbar-search .ns-input { background:rgba(255,255,255,.1); border:1.5px solid rgba(255,255,255,.2); border-radius:22px; color:#fff; font-weight:600; font-size:.88rem; padding:.38rem 1rem .38rem 2.4rem; width:240px; outline:none; }
        .navbar-search .ns-input::placeholder { color:rgba(255,255,255,.5); }
        .navbar-search .ns-wrap { position:relative; }
        .navbar-search .ns-icon { position:absolute; left:.85rem; top:50%; transform:translateY(-50%); color:rgba(255,255,255,.5); font-size:.8rem; }
        .navbar-search .ns-btn { background:var(--accent); border:none; border-radius:22px; color:var(--dark); font-weight:800; font-size:.8rem; padding:.38rem .95rem; }
        .main-navbar .dropdown-menu { background:var(--dark); border:1px solid rgba(255,255,255,.1); border-radius:10px; margin-top:6px; min-width:180px; }
        .main-navbar .dropdown-item { color:rgba(255,255,255,.8); font-weight:600; }
        .main-navbar .dropdown-item:hover { background:rgba(255,255,255,.1); color:#fff; }
        .main-navbar .dropdown-divider { border-color:rgba(255,255,255,.1); }
        .page-wrapper { flex:1; padding:2rem 0 3rem; }
        .btn { font-weight:700; border-radius:8px; }
        .btn-primary { background:var(--primary); border-color:var(--primary); }
    </style>
</head>
<body>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$navSearch = trim($_GET['search'] ?? '');
$cartQty = array_sum(array_column($_SESSION['cart'] ?? [], 'quantity'));
?>
<nav class="navbar navbar-expand-lg main-navbar">
    <a class="navbar-brand" href="/Product"><i class="fas fa-store"></i> Shop<span>Admin</span></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#mainNav"><span style="color:#fff;font-size:1.3rem;"><i class="fas fa-bars"></i></span></button>
    <div class="collapse navbar-collapse" id="mainNav">
        <form method="GET" action="/Product" class="navbar-search mx-auto">
            <div class="ns-wrap"><i class="fas fa-search ns-icon"></i><input type="text" name="search" class="ns-input" placeholder="Tìm sản phẩm..." value="<?php echo htmlspecialchars($navSearch); ?>" autocomplete="off"></div>
            <button type="submit" class="ns-btn"><i class="fas fa-search mr-1"></i> Tìm</button>
        </form>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="prodDropdown" role="button" data-toggle="dropdown"><i class="fas fa-box-open"></i> Sản phẩm</a>
                <div class="dropdown-menu" aria-labelledby="prodDropdown">
                    <a class="dropdown-item" href="/Product"><i class="fas fa-list mr-2"></i>Danh sách</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="/Product/add"><i class="fas fa-plus mr-2"></i>Thêm sản phẩm</a>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="catDropdown" role="button" data-toggle="dropdown"><i class="fas fa-tags"></i> Danh mục</a>
                <div class="dropdown-menu" aria-labelledby="catDropdown">
                    <a class="dropdown-item" href="/Category"><i class="fas fa-list mr-2"></i>Danh sách</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="/Category/add"><i class="fas fa-plus mr-2"></i>Thêm danh mục</a>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/Cart"><i class="fas fa-shopping-cart"></i> Giỏ hàng <span class="badge badge-warning"><?php echo (int)$cartQty; ?></span></a>
            </li>
            <li class="nav-item"><a class="nav-link" href="/Cart/orders"><i class="fas fa-receipt"></i> Đơn hàng</a></li>
        </ul>
    </div>
</nav>

<div class="page-wrapper">
    <div class="container">
        <?php if (!empty($_SESSION['cart_error'])): ?>
            <div class="alert alert-danger mt-2"><?php echo htmlspecialchars($_SESSION['cart_error']); unset($_SESSION['cart_error']); ?></div>
        <?php endif; ?>
