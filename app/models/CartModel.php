<?php
class CartModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    }

    public function getCart(): array {
        return $_SESSION['cart'];
    }

    public function getTotalQty(): int {
        return array_sum(array_column($_SESSION['cart'], 'quantity'));
    }

    public function getTotalPrice(): int {
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function addItem(int $productId, string $name, int $price, string $image = '', int $qty = 1): void {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $qty;
        } else {
            $_SESSION['cart'][$productId] = [
                'product_id' => $productId,
                'name'       => $name,
                'price'      => $price,
                'image'      => $image,
                'quantity'   => $qty,
            ];
        }
    }

    public function updateQty(int $productId, int $qty): void {
        if ($qty <= 0) {
            $this->removeItem($productId);
            return;
        }
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] = $qty;
        }
    }

    public function removeItem(int $productId): void {
        unset($_SESSION['cart'][$productId]);
    }

    public function clearCart(): void {
        $_SESSION['cart'] = [];
    }

    public function getNextOrderId(): int {
        $stmt = $this->conn->query("SELECT COALESCE(MAX(id), 0) + 1 FROM orders");
        return (int)$stmt->fetchColumn();
    }

    public function placeOrder(
        string $name,
        string $phone,
        string $email,
        string $address,
        string $note = '',
        string $paymentMethod = 'cod'
    ): int|false {
        if (empty($_SESSION['cart'])) return false;

        $total = $this->getTotalPrice();
        $paymentMethod = $this->normalizePaymentMethod($paymentMethod);
        $paymentStatus = 'unpaid';

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "INSERT INTO orders (customer_name, customer_phone, customer_email, customer_address, note, total_price, payment_method, payment_status)
                 VALUES (:name, :phone, :email, :address, :note, :total, :payment_method, :payment_status)"
            );
            $stmt->execute([
                ':name'           => $name,
                ':phone'          => $phone,
                ':email'          => $email !== '' ? $email : null,
                ':address'        => $address,
                ':note'           => $note,
                ':total'          => $total,
                ':payment_method' => $paymentMethod,
                ':payment_status' => $paymentStatus,
            ]);
            $orderId = (int)$this->conn->lastInsertId();

            $stmt2 = $this->conn->prepare(
                "INSERT INTO order_items (order_id, product_id, name, price, quantity, image)
                 VALUES (:order_id, :product_id, :name, :price, :quantity, :image)"
            );
            foreach ($_SESSION['cart'] as $item) {
                $stmt2->execute([
                    ':order_id'   => $orderId,
                    ':product_id' => $item['product_id'],
                    ':name'       => $item['name'],
                    ':price'      => $item['price'],
                    ':quantity'   => $item['quantity'],
                    ':image'      => $item['image'],
                ]);
            }

            $this->conn->commit();
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }

        $this->clearCart();
        return $orderId;
    }

    public function placeOrderFromItems(
        string $name,
        string $phone,
        string $email,
        string $address,
        string $note = '',
        string $paymentMethod = 'cod',
        array $items = []
    ): int|false {
        $normalizedItems = $this->normalizeOrderItems($items);
        if (empty($normalizedItems)) {
            return false;
        }

        $total = 0;
        $resolvedItems = [];

        try {
            foreach ($normalizedItems as $item) {
                $product = $this->findProductSnapshot((int) $item['product_id']);
                if (!$product) {
                    return false;
                }

                $quantity = max(1, (int) $item['quantity']);
                $price = (int) $product['price'];
                $image = $item['image'] ?? ($product['image'] ?? '');
                $nameSnapshot = trim((string) ($item['name'] ?? $product['name']));

                $resolvedItems[] = [
                    'product_id' => (int) $product['id'],
                    'name' => $nameSnapshot,
                    'price' => $price,
                    'quantity' => $quantity,
                    'image' => (string) $image,
                ];

                $total += $price * $quantity;
            }

            $this->conn->beginTransaction();

            $paymentMethod = $this->normalizePaymentMethod($paymentMethod);
            $paymentStatus = 'unpaid';
            $stmt = $this->conn->prepare(
                "INSERT INTO orders (customer_name, customer_phone, customer_email, customer_address, note, total_price, payment_method, payment_status)
                 VALUES (:name, :phone, :email, :address, :note, :total, :payment_method, :payment_status)"
            );
            $stmt->execute([
                ':name'           => $name,
                ':phone'          => $phone,
                ':email'          => $email !== '' ? $email : null,
                ':address'        => $address,
                ':note'           => $note,
                ':total'          => $total,
                ':payment_method' => $paymentMethod,
                ':payment_status' => $paymentStatus,
            ]);

            $orderId = (int) $this->conn->lastInsertId();

            $stmt2 = $this->conn->prepare(
                "INSERT INTO order_items (order_id, product_id, name, price, quantity, image)
                 VALUES (:order_id, :product_id, :name, :price, :quantity, :image)"
            );
            foreach ($resolvedItems as $item) {
                $stmt2->execute([
                    ':order_id'   => $orderId,
                    ':product_id' => $item['product_id'],
                    ':name'       => $item['name'],
                    ':price'      => $item['price'],
                    ':quantity'   => $item['quantity'],
                    ':image'      => $item['image'],
                ]);
            }

            $this->conn->commit();
            return $orderId;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    public function getAllOrders(): array {
        $stmt = $this->conn->query("SELECT * FROM orders ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Lấy danh sách đơn hàng theo email khách hàng.
     * Dùng cho trang lịch sử đơn hàng của tài khoản đã đăng nhập.
     */
    public function getOrdersByCustomerEmail(string $email): array
    {
        $email = trim(strtolower($email));
        if ($email === '') {
            return [];
        }

        $stmt = $this->conn->prepare("
            SELECT
                o.*,
                COUNT(oi.id) AS item_count
            FROM orders o
            LEFT JOIN order_items oi ON oi.order_id = o.id
            WHERE LOWER(o.customer_email) = :email
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([':email' => $email]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getOrderById(int $id): object|false {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getOrderItems(int $orderId): array {
        // Lấy thêm ảnh sản phẩm gốc để làm phương án dự phòng khi order_items.image bị thiếu
        $stmt = $this->conn->prepare("
            SELECT
                oi.*,
                COALESCE(NULLIF(oi.image, ''), p.image) AS display_image,
                p.image AS product_image
            FROM order_items oi
            LEFT JOIN product p ON p.id = oi.product_id
            WHERE oi.order_id = :id
            ORDER BY oi.id ASC
        ");
        $stmt->execute([':id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function updateOrderStatus(int $id, string $status): void {
        $allowed = ['pending', 'confirmed', 'shipping', 'done', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            $status = 'pending';
        }

        $stmt = $this->conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function updateOrderPaymentStatus(int $id, string $paymentStatus): bool
    {
        $allowed = ['unpaid', 'paid'];
        if (!in_array($paymentStatus, $allowed, true)) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE orders SET payment_status = :payment_status WHERE id = :id");
        return $stmt->execute([':payment_status' => $paymentStatus, ':id' => $id]);
    }

    public function deleteOrder(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM orders WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    private function normalizeOrderItems(array $items): array
    {
        $normalized = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $productId = (int) ($item['product_id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            $normalized[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'name' => $item['name'] ?? null,
                'price' => $item['price'] ?? null,
                'image' => $item['image'] ?? null,
            ];
        }

        return $normalized;
    }

    private function findProductSnapshot(int $productId): array|false
    {
        $stmt = $this->conn->prepare("SELECT id, name, price, image FROM product WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        return $product ?: false;
    }

    private function normalizePaymentMethod(string $paymentMethod): string
    {
        $paymentMethod = strtolower(trim($paymentMethod));

        if (in_array($paymentMethod, ['bank_transfer', 'wallet', 'banking', 'ewallet', 'e_wallet'], true)) {
            return 'banking';
        }

        return 'cod';
    }
}
?>
