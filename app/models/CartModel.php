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
        $paymentStatus = $paymentMethod === 'banking' ? 'paid' : 'unpaid';

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

    public function getAllOrders(): array {
        $stmt = $this->conn->query("SELECT * FROM orders ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getOrderById(int $id): object|false {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getOrderItems(int $orderId): array {
        $stmt = $this->conn->prepare("SELECT * FROM order_items WHERE order_id = :id");
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
}
?>
