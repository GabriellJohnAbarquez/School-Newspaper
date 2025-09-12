<?php
// PATH: ./classes/User.php
require_once __DIR__ . '/../core/Database.php';

class User extends Database {
    public function startSession() {
        if (session_status() == PHP_SESSION_NONE) session_start();
    }

    public function usernameExists($username) {
        $sql = "SELECT COUNT(*) AS username_count FROM school_publication_users WHERE username = ?";
        $c = $this->executeQuerySingle($sql, [$username]);
        return ($c && $c['username_count'] > 0);
    }

    public function registerUser($username, $email, $password, $is_admin = 0) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO school_publication_users (username, email, password, is_admin) VALUES (?, ?, ?, ?)";
        try {
            return $this->executeNonQuery($sql, [$username, $email, $hashed, (int)$is_admin]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function loginUser($email, $password) {
        $this->startSession();
        $sql = "SELECT * FROM school_publication_users WHERE email = ? LIMIT 1";
        $row = $this->executeQuerySingle($sql, [$email]);
        if ($row && password_verify($password, $row['password'])) {
            // set session
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['is_admin'] = (int)$row['is_admin'];
            return $row;
        }
        return false;
    }

    public function isLoggedIn() {
        $this->startSession();
        return isset($_SESSION['user_id']);
    }

    public function isAdmin() {
        $this->startSession();
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    }

    public function logout() {
        $this->startSession();
        session_unset();
        session_destroy();
    }
}
?>
