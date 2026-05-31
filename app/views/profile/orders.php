<?php
// Bảo vệ trang này - chỉ người đã đăng nhập mới vào được
require_once 'app/libs/AuthHelper.php';
AuthHelper::requireLogin();

$user = AuthHelper::getCurrentUser();
// $orders = $orders ?? [];
?>

<?php require_once 'app/views/shares/header.php'; ?>

<div style="margin-top: 20px;">
    <div class="row">
        <!-- SIDEBAR PROFILE MENU -->
        <div class="col-md-3">
            <div class="card shadow-sm" style="border-radius: 10px; border: none;">
                <div class="card-body">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="
                            width: 80px;
                            height: 80px;
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                            font-size: 32px;
                            font-weight: 800;
                            margin: 0 auto 15px;
                        ">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <h5 style="margin: 0; font-weight: 700;">
                            <?php echo htmlspecialchars($user['name']); ?>
                        </h5>
                    </div>

                    <hr style="margin: 15px 0;">

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <a href="/profile" class="btn btn-sm" style="
                            background: #f5f5f5;
                            color: #333;
                            border-radius: 8px;
                            text-decoration: none;
                            padding: 10px;
                            text-align: center;
                            font-weight: 600;
                            border: 1px solid #ddd;
                        ">
                            <i class="fas fa-user"></i> Hồ sơ
                        </a>
                        <a href="/profile/edit" class="btn btn-sm" style="
                            background: #f5f5f5;
                            color: #333;
                            border-radius: 8px;
                            text-decoration: none;
                            padding: 10px;
                            text-align: center;
                            font-weight: 600;
                            border: 1px solid #ddd;
                        ">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                        <a href="/profile/changePassword" class="btn btn-sm" style="
                            background: #f5f5f5;
                            color: #333;
                            border-radius: 8px;
                            text-decoration: none;
                            padding: 10px;
                            text-align: center;
                            font-weight: 600;
                            border: 1px solid #ddd;
                        ">
                            <i class="fas fa-lock"></i> Đổi mật khẩu
                        </a>
                        <a href="/profile/orders" class="btn btn-sm" style="
                            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
                            color: white;
                            border-radius: 8px;
                            text-decoration: none;
                            padding: 10px;
                            text-align: center;
                            font-weight: 600;
                        ">
                            <i class="fas fa-receipt"></i> Đơn hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ORDERS LIST -->
        <div class="col-md-9">
            <div class="card shadow-sm" style="border-radius: 10px; border: none;">
                <div class="card-header" style="
                    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border-radius: 10px 10px 0 0;
                    padding: 20px;
                    border: none;
                ">
                    <h5 style="margin: 0; font-weight: 700;">
                        <i class="fas fa-receipt"></i> Lịch sử đơn hàng
                    </h5>
                </div>

                <div class="card-body" style="padding: 30px;">
                    <!-- COMING SOON MESSAGE -->
                    <div style="
                        text-align: center;
                        padding: 60px 20px;
                        color: #999;
                    ">
                        <div style="font-size: 48px; margin-bottom: 20px;">
                            📦
                        </div>
                        <h4 style="color: #666; font-weight: 600; margin: 0 0 10px 0;">
                            Chức năng đang được phát triển
                        </h4>
                        <p style="margin: 0; font-size: 0.95rem;">
                            Tính năng xem lịch sử đơn hàng sẽ sớm có sẵn.<br>
                            Bạn có thể quay lại sau để kiểm tra.
                        </p>
                        <a href="/" style="
                            display: inline-block;
                            margin-top: 20px;
                            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
                            color: white;
                            padding: 10px 30px;
                            border-radius: 8px;
                            text-decoration: none;
                            font-weight: 600;
                        ">
                            <i class="fas fa-home"></i> Về trang chủ
                        </a>
                    </div>

                    <!-- FUTURE: ORDERS TABLE
                    <table class="table table-hover">
                        <thead style="background: #f5f5f5; border-radius: 8px;">
                            <tr>
                                <th>#</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php /* foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> đ</td>
                                <td>
                                    <span class="badge badge-info"><?php echo $order['status']; ?></span>
                                </td>
                                <td>
                                    <a href="/orders/<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        Xem chi tiết
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; */ ?>
                        </tbody>
                    </table>
                    -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>
