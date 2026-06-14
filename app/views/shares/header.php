<?php
require_once 'app/libs/AuthHelper.php';
AuthHelper::bootstrapSession();

$isLoggedIn = AuthHelper::isLoggedIn();
$isAdmin = AuthHelper::isAdmin();
$userName = AuthHelper::getUserName() ?: 'Khách';
$userAvatar = AuthHelper::getUserAvatar();
$userRole = AuthHelper::getUserRole();
$cartQty = array_sum(array_column($_SESSION['cart'] ?? [], 'quantity'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --page-bg: #f6f7fb;
            --text-dark: #0f172a;
            --card-shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
            --nav-accent: #f59e0b;
            --nav-soft: rgba(255, 255, 255, 0.08);
        }

        body {
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--text-dark);
            background:
                radial-gradient(circle at top right, rgba(245, 158, 11, 0.08), transparent 24%),
                radial-gradient(circle at left top, rgba(59, 130, 246, 0.07), transparent 28%),
                var(--page-bg);
        }

        .main-navbar {
            background: linear-gradient(135deg, #0f172a 0%, #111827 100%);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.22);
        }

        .main-navbar .navbar-brand {
            font-weight: 900;
            letter-spacing: 0.2px;
        }

        .brand-accent {
            color: var(--nav-accent);
        }

        .nav-pill {
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: var(--nav-soft);
            color: #fff;
            border-radius: 12px;
            padding: 0.55rem 0.9rem;
            font-weight: 800;
            text-decoration: none;
        }

        .nav-pill:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.12);
        }

        .nav-pill.is-active {
            background: rgba(245, 158, 11, 0.18);
            border-color: rgba(245, 158, 11, 0.35);
        }

        .user-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.4rem 0.75rem 0.4rem 0.45rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 0.9rem;
            overflow: hidden;
        }

        .page-wrapper {
            flex: 1;
            padding: 1.75rem 0 3rem;
        }

        .admin-sidebar-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.35);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
            z-index: 1040;
        }

        .admin-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 270px;
            background: #0f172a;
            color: #fff;
            padding: 5.25rem 1rem 1rem;
            transform: translateX(-100%);
            transition: transform 0.25s ease;
            z-index: 1045;
            overflow-y: auto;
        }

        .admin-sidebar h6 {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.55);
            margin-bottom: 0.85rem;
        }

        .admin-nav-link {
            display: block;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            padding: 0.7rem 0.8rem;
            border-radius: 12px;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }

        .admin-nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }

        body.admin-mode .admin-sidebar {
            transform: translateX(0);
        }

        body.admin-mode .admin-sidebar-backdrop {
            opacity: 1;
            pointer-events: auto;
        }

        .navbar-auth-guest,
        .navbar-auth-user {
            display: none;
        }

        .navbar-auth-guest.is-visible,
        .navbar-auth-user.is-visible {
            display: flex;
        }

        .navbar-auth-user {
            align-items: center;
            gap: 0.75rem;
        }

        .navbar-user-meta {
            line-height: 1.1;
        }

        .navbar-user-name {
            display: block;
            font-weight: 800;
        }

        .navbar-user-role {
            display: block;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.55);
        }

        .navbar-quick-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-cart-badge {
            min-width: 1.35rem;
            height: 1.35rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fbbf24;
            color: #111827;
            font-size: 0.72rem;
            font-weight: 900;
            padding: 0 0.35rem;
        }

        @media (max-width: 991px) {
            .navbar-auth-user {
                margin-top: 0.75rem;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark main-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand" href="/Home">My<span class="brand-accent">Store</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbarContent" aria-controls="mainNavbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbarContent">
            <div class="navbar-nav ms-auto align-items-lg-center gap-2">
                <a class="nav-pill is-active" href="/Home"><i class="fas fa-home me-1"></i>Trang chủ</a>
                <a class="nav-pill navbar-quick-link" href="/Cart">
                    <i class="fas fa-shopping-cart"></i>
                    Giỏ hàng
                    <span class="nav-cart-badge" id="cartQtyBadge"><?php echo (int) $cartQty; ?></span>
                </a>

                <?php if ($isAdmin): ?>
                    <button type="button" id="manageToggleBtn" class="nav-pill btn btn-link text-decoration-none">
                        <i class="fas fa-bars me-1"></i>Quản lý
                    </button>
                <?php endif; ?>

                <div class="navbar-auth-guest <?php echo $isLoggedIn ? '' : 'is-visible'; ?>" id="navbarGuestActions">
                    <a class="nav-pill" href="/auth/login"><i class="fas fa-sign-in-alt me-1"></i>Đăng nhập</a>
                    <a class="nav-pill" href="/auth/register"><i class="fas fa-user-plus me-1"></i>Đăng ký</a>
                </div>

                <div class="navbar-auth-user dropdown <?php echo $isLoggedIn ? 'is-visible' : ''; ?>" id="navbarUserActions">
                    <button class="btn user-chip dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="user-avatar" id="navbarUserAvatar">
                            <?php if (!empty($userAvatar)): ?>
                                <img src="/<?php echo htmlspecialchars($userAvatar); ?>" alt="Avatar" class="w-100 h-100">
                            <?php else: ?>
                                <?php echo strtoupper(substr($userName, 0, 1)); ?>
                            <?php endif; ?>
                        </span>
                        <span class="text-start d-none d-sm-inline navbar-user-meta">
                            <span class="navbar-user-name" id="navbarUserName"><?php echo htmlspecialchars($userName); ?></span>
                            <span class="navbar-user-role" id="navbarUserRole"><?php echo htmlspecialchars($userRole === 'admin' ? 'Admin' : 'Customer'); ?></span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item" href="/profile"><i class="fas fa-user me-2"></i>Hồ sơ</a></li>
                        <li><a class="dropdown-item" href="/profile/edit"><i class="fas fa-edit me-2"></i>Chỉnh sửa hồ sơ</a></li>
                        <li><a class="dropdown-item" href="/profile/changePassword"><i class="fas fa-lock me-2"></i>Đổi mật khẩu</a></li>
                        <?php if (!AuthHelper::isEmailVerified()): ?>
                            <li><a class="dropdown-item" href="/auth/emailVerification"><i class="fas fa-envelope me-2"></i>Xác thực email</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><button type="button" class="dropdown-item text-danger" id="navbarLogoutBtn"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</button></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<div id="adminSidebarBackdrop" class="admin-sidebar-backdrop"></div>
<?php if ($isAdmin): ?>
    <aside id="adminSidebar" class="admin-sidebar">
        <h6>Quản trị hệ thống</h6>
        <a class="admin-nav-link" href="/Dashboard"><i class="fas fa-chart-line me-2"></i>Dashboard</a>
        <a class="admin-nav-link" href="/Product"><i class="fas fa-box-open me-2"></i>Sản phẩm</a>
        <a class="admin-nav-link" href="/Category"><i class="fas fa-tags me-2"></i>Danh mục</a>
        <a class="admin-nav-link" href="/Cart/orders"><i class="fas fa-receipt me-2"></i>Đơn hàng</a>
        <a class="admin-nav-link" href="/admin/users"><i class="fas fa-users me-2"></i>Người dùng</a>
        <a class="admin-nav-link" href="/admin/settings"><i class="fas fa-cog me-2"></i>Cấu hình SMTP</a>
    </aside>
<?php endif; ?>

<div class="page-wrapper">
    <div class="container">
        <?php if (!empty($_SESSION['cart_error'])): ?>
            <div class="alert alert-danger mt-2"><?php echo htmlspecialchars($_SESSION['cart_error']); unset($_SESSION['cart_error']); ?></div>
        <?php endif; ?>
