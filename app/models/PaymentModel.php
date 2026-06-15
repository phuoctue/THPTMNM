<?php

class PaymentModel
{
    private PDO $conn;
    private string $table = 'payments';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create(array $data): int|false
    {
        $stmt = $this->conn->prepare("
            INSERT INTO {$this->table}
            (order_id, user_id, method, amount, provider, transaction_code, status, note)
            VALUES
            (:order_id, :user_id, :method, :amount, :provider, :transaction_code, :status, :note)
        ");

        $ok = $stmt->execute([
            ':order_id' => $data['order_id'],
            ':user_id' => $data['user_id'],
            ':method' => $data['method'],
            ':amount' => $data['amount'],
            ':provider' => $data['provider'] ?? null,
            ':transaction_code' => $data['transaction_code'] ?? null,
            ':status' => $data['status'] ?? 'pending',
            ':note' => $data['note'] ?? null,
        ]);

        return $ok ? (int) $this->conn->lastInsertId() : false;
    }

    public function getPaymentById(int $id): array|false
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    public function getPaymentByOrderId(int $orderId): array|false
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE order_id = :order_id ORDER BY id DESC LIMIT 1");
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    public function getAll(): array
    {
        $stmt = $this->conn->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $allowed = ['pending', 'completed', 'failed'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }
}
