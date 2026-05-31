<?php
/**
 * UserModel.php - Model quản lý người dùng
 * Xử lý các thao tác liên quan đến bảng users trong database
 */

require_once 'app/config/database.php';

class UserModel {
    private $conn;
    private $table = 'users';

    public function __construct() {
        // Lấy kết nối database
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * ĐĂNG KÝ NGƯỜI DÙNG MỚI
     * 
     * @param array $data - Dữ liệu người dùng ['full_name', 'email', 'password', 'phone', 'address']
     * @return bool - True nếu thành công, False nếu thất bại
     */
    public function register($data) {
        try {
            // Kiểm tra email đã tồn tại chưa
            if ($this->findByEmail($data['email'])) {
                return false; // Email đã tồn tại
            }

            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

            // Chuẩn bị câu lệnh SQL
            $query = "INSERT INTO " . $this->table . " 
                      (full_name, email, password, phone, address, role) 
                      VALUES 
                      (:full_name, :email, :password, :phone, :address, 'customer')";

            $stmt = $this->conn->prepare($query);

            // Bind các tham số (ngăn chặn SQL Injection)
            $stmt->bindParam(':full_name', trim($data['full_name']));
            $stmt->bindParam(':email', trim(strtolower($data['email'])));
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':phone', trim($data['phone'] ?? null));
            $stmt->bindParam(':address', trim($data['address'] ?? null));

            // Thực thi câu lệnh
            if ($stmt->execute()) {
                return true;
            }
            return false;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * TÌM NGƯỜI DÙNG BẰNG EMAIL
     * 
     * @param string $email - Email cần tìm
     * @return array|false - Mảng thông tin người dùng hoặc False
     */
    public function findByEmail($email) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', strtolower(trim($email)));
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * TÌM NGƯỜI DÙNG BẰNG ID
     * 
     * @param int $id - ID người dùng
     * @return array|false - Mảng thông tin người dùng hoặc False
     */
    public function findById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * CẬP NHẬT THÔNG TIN NGƯỜI DÙNG
     * 
     * @param int $id - ID người dùng
     * @param array $data - Dữ liệu cần cập nhật
     * @return bool - True nếu thành công
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " SET ";
            $updates = [];

            // Xây dựng câu lệnh UPDATE động
            foreach ($data as $key => $value) {
                // Chỉ cho phép cập nhật các field nhất định
                if (in_array($key, ['full_name', 'phone', 'address'])) {
                    $updates[] = "$key = :$key";
                }
            }

            if (empty($updates)) {
                return false;
            }

            $query .= implode(', ', $updates) . " WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            foreach ($data as $key => $value) {
                if (in_array($key, ['full_name', 'phone', 'address'])) {
                    $stmt->bindParam(":$key", $data[$key]);
                }
            }

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * XÓA NGƯỜI DÙNG
     * 
     * @param int $id - ID người dùng
     * @return bool - True nếu thành công
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * LẤY TẤT CẢ NGƯỜI DÙNG (CHO ADMIN)
     * 
     * @return array - Mảng người dùng
     */
    public function getAll() {
        try {
            $query = "SELECT id, full_name, email, phone, role, created_at FROM " . $this->table . " ORDER BY created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ĐỔI MẬT KHẨU
     * 
     * @param int $id - ID người dùng
     * @param string $oldPassword - Mật khẩu cũ (để xác minh)
     * @param string $newPassword - Mật khẩu mới
     * @return bool - True nếu thành công
     */
    public function changePassword($id, $oldPassword, $newPassword) {
        try {
            // Lấy user để kiểm tra password cũ
            $user = $this->findById($id);
            
            if (!$user) {
                return false; // User không tồn tại
            }

            // Kiểm tra password cũ có đúng không
            if (!password_verify($oldPassword, $user['password'])) {
                return false; // Password cũ không chính xác
            }

            // Hash password mới
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update password
            $query = "UPDATE " . $this->table . " SET password = :password WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
}
?>
