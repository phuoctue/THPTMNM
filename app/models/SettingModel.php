<?php
/**
 * SettingModel.php
 * Lưu và đọc cấu hình hệ thống theo cặp key/value.
 */

require_once 'app/config/database.php';

class SettingModel
{
    private PDO $conn;
    private string $table = 'app_settings';

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function get(string $key, $default = null)
    {
        try {
            $sql = "SELECT setting_value FROM {$this->table} WHERE setting_key = :setting_key LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':setting_key', $key);
            $stmt->execute();

            $value = $stmt->fetchColumn();
            return $value === false ? $default : $value;
        } catch (PDOException $e) {
            error_log('SettingModel get error: ' . $e->getMessage());
            return $default;
        }
    }

    public function getMany(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }

    public function set(string $key, string $value): bool
    {
        try {
            $sql = "INSERT INTO {$this->table} (setting_key, setting_value, updated_at)
                    VALUES (:setting_key, :setting_value, CURRENT_TIMESTAMP)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':setting_key', $key);
            $stmt->bindValue(':setting_value', $value);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('SettingModel set error: ' . $e->getMessage());
            return false;
        }
    }

    public function saveMany(array $settings): bool
    {
        $this->conn->beginTransaction();
        try {
            foreach ($settings as $key => $value) {
                $ok = $this->set((string) $key, (string) $value);
                if (!$ok) {
                    $this->conn->rollBack();
                    return false;
                }
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('SettingModel saveMany error: ' . $e->getMessage());
            return false;
        }
    }

    public function all(): array
    {
        try {
            $stmt = $this->conn->prepare("SELECT setting_key, setting_value FROM {$this->table} ORDER BY setting_key ASC");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($rows as $row) {
                $result[$row['setting_key']] = $row['setting_value'];
            }

            return $result;
        } catch (PDOException $e) {
            error_log('SettingModel all error: ' . $e->getMessage());
            return [];
        }
    }
}
