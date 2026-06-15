<?php

class CategoryModel
{
    private PDO $conn;
    private string $table = 'category';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getCategories(): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryById(int $id): array|false
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    public function create(array $data): int|false
    {
        $stmt = $this->conn->prepare("
            INSERT INTO {$this->table} (name, description)
            VALUES (:name, :description)
        ");
        $ok = $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
        ]);

        return $ok ? (int) $this->conn->lastInsertId() : false;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET name = :name, description = :description
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function exists(int $id): bool
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function countProducts(int $categoryId): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM product WHERE category_id = :category_id");
        $stmt->execute([':category_id' => $categoryId]);
        return (int) $stmt->fetchColumn();
    }
}
