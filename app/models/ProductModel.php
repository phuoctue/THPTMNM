<?php

class ProductModel
{
    private PDO $conn;
    private string $table_name = 'product';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getProducts(array $filters = []): array
    {
        $where = [];
        $params = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $where[] = '(p.name LIKE :search OR p.description LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $categoryId = (int) ($filters['category_id'] ?? 0);
        if ($categoryId > 0) {
            $where[] = 'p.category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        $minPrice = $filters['min_price'] ?? null;
        if ($minPrice !== null && $minPrice !== '' && is_numeric($minPrice)) {
            $where[] = 'p.price >= :min_price';
            $params[':min_price'] = (float) $minPrice;
        }

        $maxPrice = $filters['max_price'] ?? null;
        if ($maxPrice !== null && $maxPrice !== '' && is_numeric($maxPrice)) {
            $where[] = 'p.price <= :max_price';
            $params[':max_price'] = (float) $maxPrice;
        }

        $sortBy = strtolower((string) ($filters['sort_by'] ?? 'p.id'));
        $sortDir = strtolower((string) ($filters['sort_dir'] ?? 'desc'));
        $allowedSortBy = [
            'id' => 'p.id',
            'name' => 'p.name',
            'price' => 'p.price',
            'created_at' => 'p.created_at',
        ];
        $orderBy = $allowedSortBy[$sortBy] ?? 'p.id';
        $sortDir = in_array($sortDir, ['asc', 'desc'], true) ? $sortDir : 'desc';

        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 12)));
        $limit = (int) ($filters['limit'] ?? 8);
        $offset = (int) ($filters['offset'] ?? 0);

        $sql = "
            SELECT p.id, p.name, p.description, p.price, p.category_id, p.image, p.created_at,
                   c.name AS category_name
            FROM {$this->table_name} p
            LEFT JOIN category c ON p.category_id = c.id
        ";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= " ORDER BY {$orderBy} {$sortDir} LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function countProducts(array $filters = []): int
    {
        $where = [];
        $params = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $where[] = '(name LIKE :search OR description LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $categoryId = (int) ($filters['category_id'] ?? 0);
        if ($categoryId > 0) {
            $where[] = 'category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        $minPrice = $filters['min_price'] ?? null;
        if ($minPrice !== null && $minPrice !== '' && is_numeric($minPrice)) {
            $where[] = 'price >= :min_price';
            $params[':min_price'] = (float) $minPrice;
        }

        $maxPrice = $filters['max_price'] ?? null;
        if ($maxPrice !== null && $maxPrice !== '' && is_numeric($maxPrice)) {
            $where[] = 'price <= :max_price';
            $params[':max_price'] = (float) $maxPrice;
        }

        $sql = "SELECT COUNT(*) FROM {$this->table_name}";
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function getProductById(int $id)
    {
        $query = "
            SELECT p.*, c.name AS category_name
            FROM {$this->table_name} p
            LEFT JOIN category c ON p.category_id = c.id
            WHERE p.id = :id
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function addProduct(string $name, string $description, float|int $price, int $category_id, ?string $image): bool
    {
        $query = "INSERT INTO {$this->table_name}
                  (name, description, price, category_id, image)
                  VALUES (:name, :description, :price, :category_id, :image)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindValue(':image', $image);

        return $stmt->execute();
    }

    public function updateProduct(int $id, string $name, string $description, float|int $price, int $category_id, ?string $image): bool
    {
        $query = "UPDATE {$this->table_name}
                  SET name = :name,
                      description = :description,
                      price = :price,
                      category_id = :category_id,
                      image = :image
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindValue(':image', $image);

        return $stmt->execute();
    }

    public function deleteProduct(int $id): bool
    {
        $query = "DELETE FROM {$this->table_name} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function categoryExists(int $categoryId): bool
    {
        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM category WHERE id = :id');
        $stmt->bindValue(':id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }
}
