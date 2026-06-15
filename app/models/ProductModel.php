<?php

class ProductModel
{
    private PDO $conn;
    private string $table = 'product';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getProducts(array|string $filters = [], int $limit = 10, int $offset = 0): array
    {
        $filters = $this->normalizeFilters($filters);
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(p.name LIKE :search OR p.description LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = :category_id';
            $params[':category_id'] = (int) $filters['category_id'];
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $where[] = 'p.price >= :min_price';
            $params[':min_price'] = (float) $filters['min_price'];
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $where[] = 'p.price <= :max_price';
            $params[':max_price'] = (float) $filters['max_price'];
        }

        $sql = "SELECT p.id, p.name, p.description, p.price, p.category_id, p.image, p.created_at,
                       c.name AS category_name
                FROM {$this->table} p
                LEFT JOIN category c ON p.category_id = c.id";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY p.id DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === ':search') {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            } elseif (str_contains($key, 'price')) {
                $stmt->bindValue($key, $value);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countProducts(array|string $filters = []): int
    {
        $filters = $this->normalizeFilters($filters);
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(name LIKE :search OR description LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['category_id'])) {
            $where[] = 'category_id = :category_id';
            $params[':category_id'] = (int) $filters['category_id'];
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $where[] = 'price >= :min_price';
            $params[':min_price'] = (float) $filters['min_price'];
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $where[] = 'price <= :max_price';
            $params[':max_price'] = (float) $filters['max_price'];
        }

        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function getProductById(int $id): array|false
    {
        $stmt = $this->conn->prepare("
            SELECT p.*, c.name AS category_name
            FROM {$this->table} p
            LEFT JOIN category c ON c.id = p.category_id
            WHERE p.id = :id
            LIMIT 1
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    public function create(array $data): int|false
    {
        $stmt = $this->conn->prepare("
            INSERT INTO {$this->table} (name, description, price, category_id, image)
            VALUES (:name, :description, :price, :category_id, :image)
        ");
        $ok = $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':price' => $data['price'],
            ':category_id' => $data['category_id'],
            ':image' => $data['image'] ?? null,
        ]);

        return $ok ? (int) $this->conn->lastInsertId() : false;
    }

    public function update(int $id, array $data): bool
    {
        $fields = [
            'name = :name',
            'description = :description',
            'price = :price',
            'category_id = :category_id',
        ];

        if (array_key_exists('image', $data)) {
            $fields[] = 'image = :image';
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $params = [
            ':id' => $id,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':price' => $data['price'],
            ':category_id' => $data['category_id'],
        ];

        if (array_key_exists('image', $data)) {
            $params[':image'] = $data['image'];
        }

        return $stmt->execute($params);
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

    public function hasCategoryProducts(int $categoryId): bool
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE category_id = :category_id");
        $stmt->execute([':category_id' => $categoryId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function sortByPrice(string $direction = 'asc', array $filters = []): array
    {
        $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
        $filters['sort'] = 'price';
        $products = $this->getProducts($filters, 1000, 0);

        usort($products, static function (array $a, array $b) use ($direction): int {
            $left = (float) ($a['price'] ?? 0);
            $right = (float) ($b['price'] ?? 0);
            $result = $left <=> $right;
            return $direction === 'DESC' ? -$result : $result;
        });

        return $products;
    }

    public function categoryExists(int $categoryId): bool
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM category WHERE id = :id");
        $stmt->execute([':id' => $categoryId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function normalizeFilters(array|string $filters): array
    {
        if (is_string($filters)) {
            return [
                'search' => trim($filters),
            ];
        }

        return $filters;
    }
}
