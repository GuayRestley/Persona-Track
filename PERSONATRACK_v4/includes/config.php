<?php
class Database {
    private $host = 'localhost';
    private $user = 'root'; // Default XAMPP
    private $pass = '';
    private $dbname = 'barangay_profiling';
    public $conn;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>