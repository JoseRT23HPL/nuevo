<?php
// config/database.php
class Database {
    private $host = "localhost";
    private $db_name = "ferreteria_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            $this->conn->set_charset("utf8");
        } catch(Exception $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>