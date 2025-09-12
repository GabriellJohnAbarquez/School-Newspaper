<?php
// PATH: ./classes/Article.php
require_once __DIR__ . '/../core/Database.php';

class Article extends Database {

    // Create a new article
    public function createArticle($title, $content, $author_id, $image_path = null, $is_active = 0) {
        $sql = "INSERT INTO articles (title, content, author_id, image_path, is_active) VALUES (?, ?, ?, ?, ?)";
        return $this->executeNonQuery($sql, [$title, $content, $author_id, $image_path, (int)$is_active]);
    }
    // Get all articles or a single article by ID
    public function getArticles($id = null) {
        if ($id) {
            $sql = "SELECT a.*, u.username, u.is_admin
                    FROM articles a
                    JOIN school_publication_users u ON a.author_id = u.user_id
                    WHERE a.article_id = ?";
            return $this->executeQuerySingle($sql, [$id]);
        }
        $sql = "SELECT a.*, u.username, u.is_admin
                FROM articles a
                JOIN school_publication_users u ON a.author_id = u.user_id
                ORDER BY a.created_at DESC";
        return $this->executeQuery($sql);
    }

    // Get only published articles
    public function getActiveArticles() {
        $sql = "SELECT a.*, u.username, u.is_admin
                FROM articles a
                JOIN school_publication_users u ON a.author_id = u.user_id
                WHERE a.is_active = 1
                ORDER BY a.created_at DESC";
        return $this->executeQuery($sql);
    }

    // Get articles by a specific user
    public function getArticlesByUserID($user_id) {
        $sql = "SELECT a.*, u.username, u.is_admin
                FROM articles a
                JOIN school_publication_users u ON a.author_id = u.user_id
                WHERE a.author_id = ?
                ORDER BY a.created_at DESC";
        return $this->executeQuery($sql, [$user_id]);
    }

    // Update an article
    // Update article
    public function updateArticle($id, $title, $content, $image_path = null) {
        if ($image_path !== null) {
            $sql = "UPDATE articles SET title = ?, content = ?, image_path = ? WHERE article_id = ?";
            return $this->executeNonQuery($sql, [$title, $content, $image_path, $id]);
        } else {
            $sql = "UPDATE articles SET title = ?, content = ? WHERE article_id = ?";
            return $this->executeNonQuery($sql, [$title, $content, $id]);
        }
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

    // Notifications
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

    // Edit requests
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

    // Shared articles
    public function grantSharedArticle($article_id, $user_id) {
        $sql = "INSERT INTO shared_articles (article_id, user_id) VALUES (?, ?)";
        return $this->executeNonQuery($sql, [$article_id, $user_id]);
    }

    public function getSharedArticles($user_id) {
        $sql = "SELECT a.*, u.username, u.is_admin
                FROM shared_articles s
                JOIN articles a ON s.article_id = a.article_id
                JOIN school_publication_users u ON a.author_id = u.user_id
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

    // Fetch pending articles
    public function getPendingArticles() {
        $sql = "SELECT a.*, u.username, u.is_admin
                FROM articles a
                JOIN school_publication_users u ON a.author_id = u.user_id
                WHERE a.is_active = 0
                ORDER BY a.created_at DESC";
        return $this->executeQuery($sql);
    }

    // Fetch all articles regardless of status
    public function getAllArticles() {
        $sql = "SELECT a.*, u.username, u.is_admin
                FROM articles a
                JOIN school_publication_users u ON a.author_id = u.user_id
                ORDER BY a.created_at DESC";
        return $this->executeQuery($sql);
    }

    // Feed for user (own + shared + others)
    public function getFeedForUser($user_id) {
        $sql = "SELECT a.*, u.username, u.is_admin,
                CASE
                    WHEN a.author_id = ? THEN 'own'
                    WHEN s.user_id IS NOT NULL THEN 'shared'
                    ELSE 'other'
                END AS relation
                FROM articles a
                LEFT JOIN shared_articles s ON a.article_id = s.article_id AND s.user_id = ?
                JOIN school_publication_users u ON a.author_id = u.user_id
                ORDER BY a.created_at DESC";
        return $this->executeQuery($sql, [$user_id, $user_id]);
    }

}
?>
