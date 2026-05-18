<?php
class CartModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /** Trả về toàn bộ giỏ hàng (mảng associative keyed by product_id) */
    public function getCart(): array {
        return $_SESSION['cart'];
    }

    /** Tổng số lượng items trong giỏ */
    public function getTotalQty(): int {
        return array_sum(array_column($_SESSION['cart'], 'quantity'));
    }

    /** Tổng tiền */
    public function getTotalPrice(): int {
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    // ── CRUD cart ─────────────────────────────────────────────────────────

    /** Thêm hoặc tăng số lượng sản phẩm */
    public function addItem(int $productId, string $name, int $price,
                            string $image = '', int $qty = 1): void {
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

    /** Cập nhật số lượng (nếu qty <= 0 thì xoá) */
    public function updateQty(int $productId, int $qty): void {
        if ($qty <= 0) {
            $this->removeItem($productId);
            return;
        }
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] = $qty;
        }
    }

    /** Xoá một sản phẩm */
    public function removeItem(int $productId): void {
        unset($_SESSION['cart'][$productId]);
    }

    /** Xoá toàn bộ giỏ */
    public function clearCart(): void {
        $_SESSION['cart'] = [];
    }

    // ── Lưu đơn hàng vào DB ───────────────────────────────────────────────

    public function placeOrder(string $name, string $phone,
                               string $address, string $note = ''): int|false {
        if (empty($_SESSION['cart'])) return false;

        $total = $this->getTotalPrice();

        // Insert orders
        $stmt = $this->conn->prepare(
            "INSERT INTO orders (customer_name, customer_phone, customer_address, note, total_price)
             VALUES (:name, :phone, :address, :note, :total)"
        );
        $stmt->execute([
            ':name'    => $name,
            ':phone'   => $phone,
            ':address' => $address,
            ':note'    => $note,
            ':total'   => $total,
        ]);
        $orderId = (int) $this->conn->lastInsertId();

        // Insert order_items
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

        $this->clearCart();
        return $orderId;
    }

    // ── Lấy đơn hàng (cho trang quản lý) ─────────────────────────────────

    public function getAllOrders(): array {
        $stmt = $this->conn->query(
            "SELECT * FROM orders ORDER BY created_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getOrderById(int $id): object|false {
        $stmt = $this->conn->prepare(
            "SELECT * FROM orders WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getOrderItems(int $orderId): array {
        $stmt = $this->conn->prepare(
            "SELECT * FROM order_items WHERE order_id = :id"
        );
        $stmt->execute([':id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function updateOrderStatus(int $id, string $status): void {
        $stmt = $this->conn->prepare(
            "UPDATE orders SET status = :status WHERE id = :id"
        );
        $stmt->execute([':status' => $status, ':id' => $id]);
    }
}
?>