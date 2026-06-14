<?php

class CategoryModel
{
    private PDO $conn;
    private string $table_name = 'category';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getCategories(): array
    {
        $query = "SELECT * FROM {$this->table_name} ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getCategoryById(int $id)
    {
        $query = "SELECT * FROM {$this->table_name} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function addCategory(string $name, string $description = ''): bool
    {
        $query = "INSERT INTO {$this->table_name} (name, description) VALUES (:name, :description)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':description', $description);

        return $stmt->execute();
    }

    public function updateCategory(int $id, string $name, string $description = ''): bool
    {
        $query = "UPDATE {$this->table_name}
                  SET name = :name, description = :description
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':description', $description);

        return $stmt->execute();
    }

    public function deleteCategory(int $id): bool
    {
        $query = "DELETE FROM {$this->table_name} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function hasProducts(int $id): bool
    {
        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM product WHERE category_id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }
}
