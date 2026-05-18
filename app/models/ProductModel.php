<?php
class ProductModel {
    private $conn;
    private $table_name = "product";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getProducts($search = '', $limit = 8, $offset = 0) {
        $where = '';
        if ($search !== '') {
            $where = "WHERE p.name LIKE :search OR p.description LIKE :search";
        }

        $query = "SELECT p.id, p.name, p.description, p.price, p.image,
                         c.name as category_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN category c ON p.category_id = c.id
                  $where
                  ORDER BY p.id ASC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if ($search !== '') {
            $like = "%" . $search . "%";
            $stmt->bindParam(':search', $like);
        }
        $stmt->bindParam(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function countProducts($search = '') {
        $where = '';
        if ($search !== '') {
            $where = "WHERE name LIKE :search OR description LIKE :search";
        }

        $query = "SELECT COUNT(*) FROM " . $this->table_name . " $where";
        $stmt  = $this->conn->prepare($query);

        if ($search !== '') {
            $like = "%" . $search . "%";
            $stmt->bindParam(':search', $like);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function getProductById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function addProduct($name, $description, $price, $category_id, $image) {
        $query = "INSERT INTO " . $this->table_name . "
                  (name, description, price, category_id, image)
                  VALUES
                  (:name, :description, :price, :category_id, :image)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name',        $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price',       $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':image',       $image);

        return $stmt->execute();
    }

    public function updateProduct($id, $name, $description, $price, $category_id, $image) {
        $query = "UPDATE " . $this->table_name . "
                  SET name        = :name,
                      description = :description,
                      price       = :price,
                      category_id = :category_id,
                      image       = :image
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id',          $id);
        $stmt->bindParam(':name',        $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price',       $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':image',       $image);

        return $stmt->execute();
    }

    public function deleteProduct($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>