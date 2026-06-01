<?php
/**
 * UserModel.php
 * Model xử lý toàn bộ nghiệp vụ liên quan đến người dùng và các token xác thực.
 */

require_once 'app/config/database.php';

class UserModel
{
    private PDO $conn;
    private string $table = 'users';

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Đăng ký người dùng mới.
     * Trả về ID người dùng mới nếu thành công, false nếu thất bại.
     */
    public function register(array $data)
    {
        try {
            if ($this->findByEmail($data['email'], true)) {
                return false;
            }

            $sql = "INSERT INTO {$this->table}
                    (full_name, email, password, phone, address, role, status, avatar, email_verified_at, deleted_at)
                    VALUES
                    (:full_name, :email, :password, :phone, :address, :role, 'active', NULL, NULL, NULL)";
            $stmt = $this->conn->prepare($sql);

            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->bindValue(':full_name', trim($data['full_name']));
            $stmt->bindValue(':email', strtolower(trim($data['email'])));
            $stmt->bindValue(':password', $hashedPassword);
            $stmt->bindValue(':phone', trim($data['phone'] ?? ''));
            $stmt->bindValue(':address', trim($data['address'] ?? ''));
            $stmt->bindValue(':role', $data['role'] ?? 'customer');

            if (!$stmt->execute()) {
                return false;
            }

            return (int) $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log('UserModel register error: ' . $e->getMessage());
            return false;
        }
    }

    public function findByEmail(string $email, bool $includeDeleted = false)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = :email";
            if (!$includeDeleted) {
                $sql .= " AND deleted_at IS NULL";
            }
            $sql .= " LIMIT 1";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':email', strtolower(trim($email)));
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        } catch (PDOException $e) {
            error_log('UserModel findByEmail error: ' . $e->getMessage());
            return false;
        }
    }

    public function findById(int $id, bool $includeDeleted = false)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            if (!$includeDeleted) {
                $sql .= " AND deleted_at IS NULL";
            }
            $sql .= " LIMIT 1";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        } catch (PDOException $e) {
            error_log('UserModel findById error: ' . $e->getMessage());
            return false;
        }
    }

    public function getAll(bool $includeDeleted = false): array
    {
        try {
            $sql = "SELECT id, full_name, email, phone, address, role, status, avatar, email_verified_at, created_at, updated_at, deleted_at
                    FROM {$this->table}";
            if (!$includeDeleted) {
                $sql .= " WHERE deleted_at IS NULL";
            }
            $sql .= " ORDER BY created_at DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('UserModel getAll error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Cập nhật hồ sơ cá nhân.
     */
    public function updateProfile(int $id, array $data): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET full_name = :full_name, phone = :phone, address = :address WHERE id = :id AND deleted_at IS NULL";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':full_name', trim($data['full_name'] ?? ''));
            $stmt->bindValue(':phone', trim($data['phone'] ?? ''));
            $stmt->bindValue(':address', trim($data['address'] ?? ''));
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('UserModel updateProfile error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật avatar.
     */
    public function updateAvatar(int $id, ?string $avatarPath): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET avatar = :avatar WHERE id = :id AND deleted_at IS NULL";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':avatar', $avatarPath);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('UserModel updateAvatar error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Đổi mật khẩu theo mật khẩu cũ.
     */
    public function changePassword(int $id, string $oldPassword, string $newPassword): bool
    {
        try {
            $user = $this->findById($id);
            if (!$user || !password_verify($oldPassword, $user['password'])) {
                return false;
            }

            return $this->updatePassword($id, $newPassword);
        } catch (Throwable $e) {
            error_log('UserModel changePassword error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Đặt mật khẩu mới trực tiếp.
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET password = :password, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':password', password_hash($newPassword, PASSWORD_DEFAULT));
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('UserModel updatePassword error: ' . $e->getMessage());
            return false;
        }
    }

    public function verifyEmail(int $id): bool
    {
        try {
            $sql = "UPDATE {$this->table}
                    SET email_verified_at = COALESCE(email_verified_at, CURRENT_TIMESTAMP), updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id AND deleted_at IS NULL";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('UserModel verifyEmail error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateAdmin(int $id, array $data): bool
    {
        try {
            $sql = "UPDATE {$this->table}
                    SET full_name = :full_name,
                        email = :email,
                        phone = :phone,
                        address = :address,
                        role = :role,
                        status = :status,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id AND deleted_at IS NULL";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':full_name', trim($data['full_name'] ?? ''));
            $stmt->bindValue(':email', strtolower(trim($data['email'] ?? '')));
            $stmt->bindValue(':phone', trim($data['phone'] ?? ''));
            $stmt->bindValue(':address', trim($data['address'] ?? ''));
            $stmt->bindValue(':role', $data['role'] ?? 'customer');
            $stmt->bindValue(':status', $data['status'] ?? 'active');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('UserModel updateAdmin error: ' . $e->getMessage());
            return false;
        }
    }

    public function lockUser(int $id): bool
    {
        return $this->setStatus($id, 'locked');
    }

    public function unlockUser(int $id): bool
    {
        return $this->setStatus($id, 'active');
    }

    public function setStatus(int $id, string $status): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('UserModel setStatus error: ' . $e->getMessage());
            return false;
        }
    }

    public function softDelete(int $id): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET deleted_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('UserModel softDelete error: ' . $e->getMessage());
            return false;
        }
    }

    public function hardDelete(int $id): bool
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('UserModel hardDelete error: ' . $e->getMessage());
            return false;
        }
    }

    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email AND deleted_at IS NULL";
            if ($ignoreId !== null) {
                $sql .= " AND id <> :ignore_id";
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':email', strtolower(trim($email)));
            if ($ignoreId !== null) {
                $stmt->bindValue(':ignore_id', $ignoreId, PDO::PARAM_INT);
            }
            $stmt->execute();

            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('UserModel emailExists error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reset password token.
     */
    public function createPasswordResetToken(int $userId, string $email, string $selector, string $tokenHash, string $expiresAt): bool
    {
        try {
            $this->conn->prepare("DELETE FROM password_resets WHERE email = :email")
                ->execute([':email' => strtolower(trim($email))]);

            $sql = "INSERT INTO password_resets (user_id, email, selector, token_hash, expires_at, used_at, created_at)
                    VALUES (:user_id, :email, :selector, :token_hash, :expires_at, NULL, CURRENT_TIMESTAMP)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':email', strtolower(trim($email)));
            $stmt->bindValue(':selector', $selector);
            $stmt->bindValue(':token_hash', $tokenHash);
            $stmt->bindValue(':expires_at', $expiresAt);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('UserModel createPasswordResetToken error: ' . $e->getMessage());
            return false;
        }
    }

    public function findPasswordResetBySelector(string $selector)
    {
        try {
            $sql = "SELECT * FROM password_resets WHERE selector = :selector LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':selector', $selector);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        } catch (PDOException $e) {
            error_log('UserModel findPasswordResetBySelector error: ' . $e->getMessage());
            return false;
        }
    }

    public function markPasswordResetUsed(int $id): bool
    {
        try {
            $stmt = $this->conn->prepare("UPDATE password_resets SET used_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('UserModel markPasswordResetUsed error: ' . $e->getMessage());
            return false;
        }
    }

    public function createEmailVerificationToken(int $userId, string $email, string $selector, string $tokenHash, string $expiresAt): bool
    {
        try {
            $this->conn->prepare("DELETE FROM email_verification_tokens WHERE user_id = :user_id")
                ->execute([':user_id' => $userId]);

            $sql = "INSERT INTO email_verification_tokens (user_id, email, selector, token_hash, expires_at, verified_at, created_at)
                    VALUES (:user_id, :email, :selector, :token_hash, :expires_at, NULL, CURRENT_TIMESTAMP)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':email', strtolower(trim($email)));
            $stmt->bindValue(':selector', $selector);
            $stmt->bindValue(':token_hash', $tokenHash);
            $stmt->bindValue(':expires_at', $expiresAt);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('UserModel createEmailVerificationToken error: ' . $e->getMessage());
            return false;
        }
    }

    public function findEmailVerificationBySelector(string $selector)
    {
        try {
            $sql = "SELECT * FROM email_verification_tokens WHERE selector = :selector LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':selector', $selector);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        } catch (PDOException $e) {
            error_log('UserModel findEmailVerificationBySelector error: ' . $e->getMessage());
            return false;
        }
    }

    public function markEmailVerificationUsed(int $id): bool
    {
        try {
            $stmt = $this->conn->prepare("UPDATE email_verification_tokens SET verified_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('UserModel markEmailVerificationUsed error: ' . $e->getMessage());
            return false;
        }
    }

    public function createRememberToken(int $userId, string $selector, string $tokenHash, string $expiresAt): bool
    {
        try {
            $sql = "INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at, revoked_at, created_at, last_used_at)
                    VALUES (:user_id, :selector, :token_hash, :expires_at, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':selector', $selector);
            $stmt->bindValue(':token_hash', $tokenHash);
            $stmt->bindValue(':expires_at', $expiresAt);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('UserModel createRememberToken error: ' . $e->getMessage());
            return false;
        }
    }

    public function findRememberTokenBySelector(string $selector)
    {
        try {
            $sql = "SELECT * FROM remember_tokens WHERE selector = :selector LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':selector', $selector);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        } catch (PDOException $e) {
            error_log('UserModel findRememberTokenBySelector error: ' . $e->getMessage());
            return false;
        }
    }

    public function revokeRememberToken(int $id): bool
    {
        try {
            $stmt = $this->conn->prepare("UPDATE remember_tokens SET revoked_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('UserModel revokeRememberToken error: ' . $e->getMessage());
            return false;
        }
    }

    public function revokeRememberTokenByUserId(int $userId): bool
    {
        try {
            $stmt = $this->conn->prepare("UPDATE remember_tokens SET revoked_at = CURRENT_TIMESTAMP WHERE user_id = :user_id AND revoked_at IS NULL");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('UserModel revokeRememberTokenByUserId error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateRememberToken(int $id, string $selector, string $tokenHash, string $expiresAt): bool
    {
        try {
            $sql = "UPDATE remember_tokens
                    SET selector = :selector,
                        token_hash = :token_hash,
                        expires_at = :expires_at,
                        last_used_at = CURRENT_TIMESTAMP
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':selector', $selector);
            $stmt->bindValue(':token_hash', $tokenHash);
            $stmt->bindValue(':expires_at', $expiresAt);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('UserModel updateRememberToken error: ' . $e->getMessage());
            return false;
        }
    }
}
