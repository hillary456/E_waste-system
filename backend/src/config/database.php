<?php
class Database {
    private $host = "localhost";
    private $db_name = "cfs_kenya";
    private $username = "root";
    private $password = "";  
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) { 
            header('Content-Type: application/json');
            http_response_code(500);  
            echo json_encode(array(
                "message" => "Database connection error. Is MySQL running?", 
                "details" => $exception->getMessage()
            ));
            exit;  
        }
        return $this->conn;
    }
}
?>