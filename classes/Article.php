<?php
// PATH: ./classes/Article.php
require_once __DIR__ . '/../core/Database.php';

class Article extends Database {

    /* ===================== ARTICLES ===================== */

    // Create a new article (with optional category)
    public function createArticle($title, $content, $author_id, $image_path = null, $is_active = 0, $category_id = null) {
        $sql = "INSERT INTO articles (title, content, author_id, image_path, is_active, category_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        return $this->executeNonQuery($sql, [$title, $content, $author_id, $image_path, (int)$is_active, $category_id ?: null]);
    }

    // Get all articles or a single article by ID
    public function getArticles($id = null) {
        if ($id) {
            $sql = "SELECT a.*, u.username, u.is_admin, c.name AS category_name
                    FROM articles a
                    JOIN school_publication_users u ON a.author_id = u.user_id
                    LEFT JOIN categories c ON a.category_id = c.category_id
                    WHERE a.article_id = ?";
            return $this->executeQuerySingle($sql, [$id]);
        }
        $sql = "SELECT a.*, u.username, u.is_admin, c.name AS category_name
                FROM articles a
                JOIN school_publication_users u ON a.author_id = u.user_id
                LEFT JOIN categories c ON a.category_id = c.category_id
                ORDER BY a.created_at DESC";
        return $this->executeQuery($sql);
    }

    // Get only published articles
    public function getActiveArticles() {
        $sql = "SELECT a.*, u.username, u.is_admin, c.name AS category_name
                FROM articles a
                JOIN school_publication_users u ON a.author_id = u.user_id
                LEFT JOIN categories c ON a.category_id = c.category_id
                WHERE a.is_active = 1
                ORDER BY a.created_at DESC";
        return $this->executeQuery($sql);
    }

    // Get articles by a specific user
    public function getArticlesByUserID($user_id) {
        $sql = "SELECT a.*, u.username, u.is_admin, c.name AS category_name
                FROM articles a
                JOIN school_publication_users u ON a.author_id = u.user_id
                LEFT JOIN categories c ON a.category_id = c.category_id
                WHERE a.author_id = ?
                ORDER BY a.created_at DESC";
        return $this->executeQuery($sql, [$user_id]);
    }

    // Update an article (with optional category)
    public function updateArticle($article_id, $title, $content, $image_path = null, $category_id = null) {
        $sql = "UPDATE articles SET title = ?, content = ?, category_id = ?";
        $params = [$title, $content, $category_id];

        if ($image_path !== null) {
            $sql .= ", image_path = ?";
            $params[] = $image_path;
        }

        $sql .= " WHERE article_id = ?";
        $params[] = $article_id;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }



    // Update article visibility (admin approve/reject)
    public function updateArticleVisibility($id, $is_active) {
        $sql = "UPDATE articles SET is_active = ? WHERE article_id = ?";
        return $this->executeNonQuery($sql, [(int)$is_active, $id]);
    }

    // Delete article
    public function deleteArticle($id) {
        $sql = "DELETE FROM articles WHERE article_id = ?";
        return $this->executeNonQuery($sql, [$id]);
    }

    /* ===================== NOTIFICATIONS ===================== */

    public function addNotification($user_id, $message) {
        $sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        return $this->executeNonQuery($sql, [$user_id, $message]);
    }

    public function getNotifications($user_id) {
        $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
        return $this->executeQuery($sql, [$user_id]);
    }

    public function markNotificationRead($notification_id) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE notification_id = ?";
        return $this->executeNonQuery($sql, [$notification_id]);
    }

    /* ===================== EDIT REQUESTS ===================== */

    public function createEditRequest($article_id, $requester_id, $message = null) {
        $sql = "INSERT INTO edit_requests (article_id, requester_id, message) VALUES (?, ?, ?)";
        return $this->executeNonQuery($sql, [$article_id, $requester_id, $message]);
    }

    public function updateEditRequestStatus($request_id, $status) {
        $sql = "UPDATE edit_requests SET status = ? WHERE request_id = ?";
        return $this->executeNonQuery($sql, [$status, $request_id]);
    }

    public function getEditRequestsForAuthor($author_id) {
        $sql = "SELECT er.*, a.title, u.username AS requester_name
                FROM edit_requests er
                JOIN articles a ON er.article_id = a.article_id
                JOIN school_publication_users u ON er.requester_id = u.user_id
                WHERE a.author_id = ? AND er.status = 'pending'
                ORDER BY er.created_at DESC";
        return $this->executeQuery($sql, [$author_id]);
    }

    public function getEditRequestById($request_id) {
        $sql = "SELECT * FROM edit_requests WHERE request_id = ?";
        return $this->executeQuerySingle($sql, [$request_id]);
    }

    /* ===================== SHARED ARTICLES ===================== */

    public function grantSharedArticle($article_id, $user_id) {
        $sql = "INSERT INTO shared_articles (article_id, user_id) VALUES (?, ?)";
        return $this->executeNonQuery($sql, [$article_id, $user_id]);
    }

    public function getSharedArticles($user_id) {
        $sql = "SELECT a.*, u.username, u.is_admin, c.name AS category_name
                FROM shared_articles s
                JOIN articles a ON s.article_id = a.article_id
                JOIN school_publication_users u ON a.author_id = u.user_id
                LEFT JOIN categories c ON a.category_id = c.category_id
                WHERE s.user_id = ?
                ORDER BY s.granted_at DESC";
        return $this->executeQuery($sql, [$user_id]);
    }

    // Check if user can edit
    public function userCanEdit($article_id, $user_id) {
        $row = $this->executeQuerySingle("SELECT author_id FROM articles WHERE article_id = ?", [$article_id]);
        if (!$row) return false;
        if ($row['author_id'] == $user_id) return true;
        $c = $this->executeQuerySingle("SELECT COUNT(*) AS cnt FROM shared_articles WHERE article_id = ? AND user_id = ?", [$article_id, $user_id]);
        return ($c && isset($c['cnt']) && $c['cnt'] > 0);
    }

    /* ===================== ADMIN / FEEDS ===================== */

    // Fetch pending articles
    public function getPendingArticles() {
        $sql = "SELECT a.*, u.username, u.is_admin, c.name AS category_name
                FROM articles a
                JOIN school_publication_users u ON a.author_id = u.user_id
                LEFT JOIN categories c ON a.category_id = c.category_id
                WHERE a.is_active = 0
                ORDER BY a.created_at DESC";
        return $this->executeQuery($sql);
    }

    // Fetch all articles regardless of status
    public function getAllArticles() {
        $sql = "SELECT a.*, u.username, u.is_admin, c.name AS category_name
                FROM articles a
                JOIN school_publication_users u ON a.author_id = u.user_id
                LEFT JOIN categories c ON a.category_id = c.category_id
                ORDER BY a.created_at DESC";
        return $this->executeQuery($sql);
    }

    // Feed for user (own + shared + others)
    public function getFeedForUser($user_id) {
        $sql = "SELECT a.*, u.username, u.is_admin, c.name AS category_name,
                CASE
                    WHEN a.author_id = ? THEN 'own'
                    WHEN s.user_id IS NOT NULL THEN 'shared'
                    ELSE 'other'
                END AS relation
                FROM articles a
                LEFT JOIN shared_articles s ON a.article_id = s.article_id AND s.user_id = ?
                JOIN school_publication_users u ON a.author_id = u.user_id
                LEFT JOIN categories c ON a.category_id = c.category_id
                ORDER BY a.created_at DESC";
        return $this->executeQuery($sql, [$user_id, $user_id]);
    }

    /* ===================== CATEGORIES ===================== */

    public function getCategories() {
        $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function addCategory($name) {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
        return $stmt->execute([':name' => $name]);
    }

    public function deleteCategory($id) {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE category_id=?");
        return $stmt->execute([$id]);
    }

}
?>
