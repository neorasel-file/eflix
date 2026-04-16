<?php
// config/database.php - Database configuration for Supabase PostgreSQL
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

class Database {
    private $host;
    private $port;
    private $dbname;
    private $user;
    private $password;
    private $conn;

    public function __construct() {
        $this->host = $_ENV['SUPABASE_HOST'] ?? 'aws-0-ap-south-1.pooler.supabase.com';
        $this->port = $_ENV['SUPABASE_PORT'] ?? '5432';
        $this->dbname = $_ENV['SUPABASE_DATABASE'] ?? 'postgres';
        $this->user = $_ENV['SUPABASE_USER'] ?? 'postgres.fzfrogtwasnljkywwmpg';
        $this->password = $_ENV['SUPABASE_PASSWORD'] ?? '';
    }

    public function connect() {
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";
            $this->conn = new PDO($dsn, $this->user, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $this->conn;
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders) RETURNING id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        return $stmt->fetchColumn();
    }

    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
        }
        $set = implode(', ', $set);
        $sql = "UPDATE $table SET $set WHERE $where";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(array_merge($data, $whereParams));
    }

    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
}

// Helper function to get DB connection
function getDB() {
    $db = new Database();
    return $db->connect();
}
?>