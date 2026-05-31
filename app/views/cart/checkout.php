<?php
require_once 'app/libs/ViewHelper.php';
$flash = ViewHelper::consumeFlash();
$errors = $flash['errors'];
$success = $flash['success'];
$oldData = $flash['old_data'];
?>

<?php include 'app/views/shares/header.php'; ?>
<?php
$bankName = 'TPBank';
$bankCode = 'TPB';
$bankAccountNo = '08968985867';
$bankAccountName = 'PHAM PHUOC TUE';
$transferContent = 'DH' . (int)($nextOrderId ?? 0);
$qrAmount = (int)$totalPrice;
$qrVietQrUrl = 'https://img.vietqr.io/image/' . $bankCode . '-' . $bankAccountNo . '-compact2.png'
    . '?amount=' . $qrAmount
    . '&addInfo=' . rawurlencode($transferContent)
    . '&accountName=' . rawurlencode($bankAccountName);
$qrFallbackText = 'BANK:' . $bankCode
    . '|ACC:' . $bankAccountNo
    . '|NAME:' . $bankAccountName
    . '|AMOUNT:' . $qrAmount
    . '|INFO:' . $transferContent;
$qrUrl = 'https://quickchart.io/qr?size=260&text=' . rawurlencode($qrFallbackText);
$qrFallbackUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . rawurlencode($qrFallbackText);
?>

<style>
.bank-qr-wrap {
    margin-top: 1rem;
    border: 1px solid #e7e9ff;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 10px 24px rgba(79, 70, 229, .08);
    padding: 1rem;
}
.bank-qr-title {
    font-weight: 800;
    color: #1e1b4b;
    margin-bottom: .8rem;
}
.bank-qr-box {
    width: 260px;
    height: 260px;
    margin: 0 auto .9rem;
    border-radius: 12px;
    border: 1px solid #d8dcff;
    background: #fff;
    position: relative;
    overflow: hidden;
}
.bank-qr-img { width:100%; height:100%; object-fit:contain; padding:10px; background:#fff; }
.bank-qr-note {
    text-align: center;
    color: #4b5563;
    margin-bottom: .8rem;
}
.bank-transfer-info {
    border-top: 1px dashed #dbe1ff;
    padding-top: .8rem;
}
.bank-transfer-info .row-line {
    display: flex;
    justify-content: space-between;
    gap: .75rem;
    padding: .3rem 0;
    font-size: .95rem;
}
.bank-transfer-info .label { color: #6b7280; font-weight: 700; }
.bank-transfer-info .value { color: #1f2937; font-weight: 800; text-align: right; }
.bank-transfer-info .value.accent { color: #4f46e5; }
</style>

<div class="container mt-4">
    <h2 class="mb-4"><i class="fas fa-credit-card text-success"></i> Thanh toán</h2>

    <?php require 'app/views/shares/flash.php'; ?>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="mb-3">Thông tin giao hàng</h5>
                    <form action="/Cart/placeOrder" method="POST">
                        <div class="form-group">
                            <label>Họ tên *</label>
                            <input type="text" name="customer_name" class="form-control" value="<?php echo htmlspecialchars($oldData['name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Số điện thoại *</label>
                                <input type="text" name="customer_phone" class="form-control" value="<?php echo htmlspecialchars($oldData['phone'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input type="email" name="customer_email" class="form-control" value="<?php echo htmlspecialchars($oldData['email'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Địa chỉ *</label>
                            <textarea name="customer_address" rows="3" class="form-control" required><?php echo htmlspecialchars($oldData['address'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Ghi chú</label>
                            <textarea name="note" rows="3" class="form-control"><?php echo htmlspecialchars($oldData['note'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Phương thức thanh toán</label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="pmCod" name="payment_method" value="cod" class="custom-control-input" <?php echo ($oldData['paymentMethod'] ?? 'cod') === 'cod' ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="pmCod">COD - Thanh toán khi nhan hang</label>
                            </div>
                            <div class="custom-control custom-radio mt-2">
                                <input type="radio" id="pmBanking" name="payment_method" value="banking" class="custom-control-input" <?php echo ($oldData['paymentMethod'] ?? '') === 'banking' ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="pmBanking">Chuyển khoản ngân hàng</label>
                            </div>

                            <div id="bankQrWrap" class="bank-qr-wrap" style="display:none;">
                                <div class="bank-qr-title"><i class="fas fa-qrcode mr-1 text-primary"></i> Thanh toán qua QR Code</div>
                                <div class="bank-qr-box">
                                    <img src="<?php echo htmlspecialchars($qrUrl); ?>"
                                         alt="QR thanh toán"
                                         class="bank-qr-img"
                                         onerror="if(!this.dataset.step){this.dataset.step='1';this.src='<?php echo htmlspecialchars($qrVietQrUrl, ENT_QUOTES); ?>';return;} if(this.dataset.step==='1'){this.dataset.step='2';this.src='<?php echo htmlspecialchars($qrFallbackUrl, ENT_QUOTES); ?>';return;} this.onerror=null; this.style.display='none'; this.parentNode.insertAdjacentHTML('beforeend','<div style=&quot;padding:12px;font-size:13px;color:#6b7280;text-align:center;&quot;>Khong tai duoc QR tu server ngoai.<br>Vui long chuyen khoan theo STK ben duoi.</div>');">
                                </div>
                                <div class="bank-qr-note">
                                    <div class="font-weight-700">Quét mã QR để thanh toán nhanh</div>
                                    <small>Hệ thống sẽ xác nhận tự động sau khi nhận tiền</small>
                                </div>
                                <div class="bank-transfer-info">
                                    <div class="row-line"><div class="label">Ngân hàng</div><div class="value"><?php echo htmlspecialchars($bankName); ?></div></div>
                                    <div class="row-line"><div class="label">Số tài khoản</div><div class="value"><?php echo htmlspecialchars($bankAccountNo); ?></div></div>
                                    <div class="row-line"><div class="label">Tên chủ tài khoản</div><div class="value"><?php echo htmlspecialchars($bankAccountName); ?></div></div>
                                    <div class="row-line"><div class="label">Số tiền</div><div class="value accent"><?php echo number_format($totalPrice, 0, ',', '.'); ?> ₫</div></div>
                                    <div class="row-line"><div class="label">Nội dung chuyển khoản</div><div class="value"><?php echo htmlspecialchars($transferContent); ?></div></div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check-circle mr-1"></i> Đặt hàng ngay
                        </button>
                        <a href="/Cart" class="btn btn-outline-secondary btn-lg ml-2">Quay lại giỏ hàng</a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="mb-3">Đơn hàng của bạn</h5>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="/<?php echo htmlspecialchars($item['image']); ?>"
                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                         width="48"
                                         height="48"
                                         style="object-fit: cover; border-radius: 8px; margin-right: 10px;">
                                <?php else: ?>
                                    <div style="width:48px;height:48px;border-radius:8px;background:#eef0ff;color:#6c757d;display:flex;align-items:center;justify-content:center;margin-right:10px;">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                                <div><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></div>
                            </div>
                            <strong><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> đ</strong>
                        </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <h5 class="mb-0">Tổng cộng</h5>
                        <h4 class="text-primary mb-0"><?php echo number_format($totalPrice, 0, ',', '.'); ?> đ</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var cod = document.getElementById('pmCod');
    var banking = document.getElementById('pmBanking');
    var wrap = document.getElementById('bankQrWrap');
    if (!cod || !banking || !wrap) return;

    function syncBankQr() {
        wrap.style.display = banking.checked ? 'block' : 'none';
    }
    cod.addEventListener('change', syncBankQr);
    banking.addEventListener('change', syncBankQr);
    syncBankQr();
})();
</script>

<?php include 'app/views/shares/footer.php'; ?>
