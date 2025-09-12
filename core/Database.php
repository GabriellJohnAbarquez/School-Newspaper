<?php
// PATH: ./core/Database.php
class Database {
    protected $pdo;
    private $host = 'localhost';
    private $db = 'school newspaper'; // keep as-is per your setup
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8mb4';

    public function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (\PDOException $e) {
            // friendly error
            die("DB Connection failed: " . $e->getMessage());
        }
    }

    protected function executeQuery($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    protected function executeQuerySingle($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    protected function executeNonQuery($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    protected function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}
?>
