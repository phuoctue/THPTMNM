<?php
require_once __DIR__ . '/../libs/EnvHelper.php';

class Database {
    private string $host;
    private string $db_name;
    private string $username;
    private string $password;

    public $conn;

    public function __construct()
    {
        $this->host = (string) EnvHelper::get('DB_HOST', 'localhost');
        $this->db_name = (string) EnvHelper::get('DB_NAME', 'my_store');
        $this->username = (string) EnvHelper::get('DB_USER', 'root');
        $this->password = (string) EnvHelper::get('DB_PASS', '');
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );

            $this->conn->exec("set names utf8mb4");

        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
