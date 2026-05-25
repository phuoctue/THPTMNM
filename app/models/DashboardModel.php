<?php
class DashboardModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getSummary(): array {
        $summary = [
            'product_count' => 0,
            'category_count' => 0,
            'order_count' => 0,
            'revenue_total' => 0,
        ];

        $summary['product_count'] = (int)$this->conn->query("SELECT COUNT(*) FROM product")->fetchColumn();
        $summary['category_count'] = (int)$this->conn->query("SELECT COUNT(*) FROM category")->fetchColumn();
        $summary['order_count'] = (int)$this->conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $summary['revenue_total'] = (int)$this->conn->query(
            "SELECT COALESCE(SUM(total_price),0) FROM orders WHERE status <> 'cancelled'"
        )->fetchColumn();

        return $summary;
    }

    public function getRecentOrders(int $limit = 6): array {
        $stmt = $this->conn->prepare(
            "SELECT id, customer_name, total_price, status, payment_method, created_at
             FROM orders
             ORDER BY created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getTopProducts(int $limit = 5): array {
        $stmt = $this->conn->prepare(
            "SELECT oi.product_id,
                    oi.name,
                    COALESCE(NULLIF(MAX(p.image), ''), MAX(oi.image)) AS image,
                    SUM(oi.quantity) AS sold_qty,
                    SUM(oi.quantity * oi.price) AS sold_amount
             FROM order_items oi
             LEFT JOIN product p ON p.id = oi.product_id
             GROUP BY oi.product_id, oi.name
             ORDER BY sold_qty DESC, sold_amount DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
?>
