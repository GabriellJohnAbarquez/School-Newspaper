<?php
// PATH: ./core/handleForms.php
require_once __DIR__ . '/../classloader.php';

/**
 * Helper: process image input (file upload or URL)
 */
function processImageInput($inputNameFile, $inputNameURL) {
    // URL takes priority
    if (!empty($_POST[$inputNameURL])) {
        $url = trim($_POST[$inputNameURL]);
        if (filter_var($url, FILTER_VALIDATE_URL)) return $url;
    }

    if (!empty($_FILES[$inputNameFile]) && $_FILES[$inputNameFile]['error'] === UPLOAD_ERR_OK) {
        $u = $_FILES[$inputNameFile];
        $allowed = ['image/png','image/jpeg','image/jpg','image/gif','image/webp'];
        if (!in_array($u['type'], $allowed)) return null;

        // Server path to uploads folder
        $uploadsDir = __DIR__ . '/../uploads';
        if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

        $ext = pathinfo($u['name'], PATHINFO_EXTENSION);
        $filename = uniqid('img_') . '.' . $ext;
        $target = $uploadsDir . '/' . $filename;

        if (move_uploaded_file($u['tmp_name'], $target)) {
            // Return web-accessible relative path
            return '/School_Newspaper/uploads/' . $filename; 
        }
    }
    return null;
}


/* ---------- REGISTER ---------- */
if (isset($_POST['insertNewUserBtn'])) {
    $username = htmlspecialchars(trim($_POST['username'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');
    $role = isset($_POST['role']) ? (int)$_POST['role'] : 0;

    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $_SESSION['message'] = "Please fill in all fields.";
        $_SESSION['status'] = '400';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }

    if ($password !== $confirm) {
        $_SESSION['message'] = "Passwords do not match.";
        $_SESSION['status'] = '400';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }

    if ($userObj->usernameExists($username)) {
        $_SESSION['message'] = "Username is already taken.";
        $_SESSION['status'] = '400';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }

    $ok = $userObj->registerUser($username, $email, $password, $role);
    $_SESSION['message'] = $ok ? "Registered successfully. You may login now." : "Registration failed (email may exist).";
    $_SESSION['status'] = $ok ? '200' : '400';
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit;
}

/* ---------- LOGIN ---------- */
if (isset($_POST['loginUserBtn'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $_SESSION['message'] = "Please fill in all fields.";
        $_SESSION['status'] = '400';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }

    $user = $userObj->loginUser($email, $password);
    if ($user) {
        if ($user['is_admin'] == 1) {
            header("Location: ../admin/index.php");
        } else {
            header("Location: ../writer/index.php");
        }
        exit;
    } else {
        $_SESSION['message'] = "Invalid credentials.";
        $_SESSION['status'] = '400';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }
}

/* ---------- LOGOUT ---------- */
if (isset($_GET['logoutUserBtn'])) {
    $userObj->logout();
    // redirect to root index page after logout
    header("Location: /School_Newspaper/index.php");
    exit;
}

/* ---------- CREATE ARTICLE ---------- */
if (isset($_POST['insertArticleBtn']) || isset($_POST['insertAdminArticleBtn'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $author_id = $_SESSION['user_id'] ?? null;
    $is_admin = isset($_POST['insertAdminArticleBtn']) ? 1 : 0;
    $image_path = processImageInput('image', 'image_url');

    if ($title === '' || $description === '' || !$author_id) {
        $_SESSION['message'] = "Please provide title, content and be logged in.";
        $_SESSION['status'] = '400';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? ($is_admin ? '../admin/index.php' : '../writer/index.php')));
        exit;
    }

    if ($articleObj->createArticle($title, $description, $author_id, $image_path, $is_admin)) {
        $_SESSION['message'] = "Article submitted successfully.";
        $_SESSION['status'] = '200';
        header("Location: " . ($is_admin ? '../admin/index.php' : '../writer/index.php'));
        exit;
    } else {
        $_SESSION['message'] = "Failed to create article.";
        $_SESSION['status'] = '400';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? ($is_admin ? '../admin/index.php' : '../writer/index.php')));
        exit;
    }
}

/* ---------- EDIT ARTICLE ---------- */
if (isset($_POST['editArticleBtn'])) {
    $article_id = (int)($_POST['article_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_path = processImageInput('image', 'image_url');

    $can = $articleObj->userCanEdit($article_id, $_SESSION['user_id']) || $userObj->isAdmin();
    if (!$can) {
        $_SESSION['message'] = "Permission denied.";
        $_SESSION['status'] = '400';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }

    if ($articleObj->updateArticle($article_id, $title, $description, $image_path)) {
        $_SESSION['message'] = "Article updated successfully.";
        $_SESSION['status'] = '200';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    } else {
        $_SESSION['message'] = "Update failed.";
        $_SESSION['status'] = '400';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }
}

/* ---------- DELETE ARTICLE ---------- */
if (isset($_POST['deleteArticleBtn'])) {
    $article_id = (int)($_POST['article_id'] ?? 0);
    $article = $articleObj->getArticles($article_id);

    if (!$article) {
        $_SESSION['message'] = "Article not found.";
        $_SESSION['status'] = '400';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../writer/index.php'));
        exit;
    }

    if (!($userObj->isAdmin() || $article['author_id'] == $_SESSION['user_id'])) {
        $_SESSION['message'] = "Permission denied.";
        $_SESSION['status'] = '400';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../writer/index.php'));
        exit;
    }

    if ($articleObj->deleteArticle($article_id)) {
        $_SESSION['message'] = "Article deleted successfully.";
        $_SESSION['status'] = '200';
    } else {
        $_SESSION['message'] = "Failed to delete article.";
        $_SESSION['status'] = '400';
    }

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../writer/index.php'));
    exit;
}

/* ---------- UPDATE VISIBILITY ---------- */
if (isset($_POST['updateArticleVisibility'])) {
    $article_id = (int)($_POST['article_id'] ?? 0);
    $status = (int)($_POST['status'] ?? 0);
    $articleObj->updateArticleVisibility($article_id, $status);

    $_SESSION['message'] = $status ? "Article approved." : "Article rejected.";
    $_SESSION['status'] = '200';
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin/index.php'));
    exit;
}

/* ---------- REQUEST EDIT ---------- */
if (isset($_POST['requestEditBtn'])) {
    $article_id = (int)($_POST['article_id'] ?? 0);
    $requester_id = $_SESSION['user_id'] ?? null;
    $msg = trim($_POST['message'] ?? null);

    if (!$requester_id || !$article_id) {
        $_SESSION['message'] = "Invalid request.";
        $_SESSION['status'] = '400';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../writer/index.php'));
        exit;
    }

    if ($articleObj->createEditRequest($article_id, $requester_id, $msg)) {
        $article = $articleObj->getArticles($article_id);
        if ($article) {
            $articleObj->addNotification($article['author_id'], "{$_SESSION['username']} requested edit access to \"{$article['title']}\".");
        }
        $_SESSION['message'] = "Edit request sent.";
        $_SESSION['status'] = '200';
    } else {
        $_SESSION['message'] = "Failed to send request.";
        $_SESSION['status'] = '400';
    }

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../writer/index.php'));
    exit;
}

/* ---------- RESPOND TO EDIT REQUEST ---------- */
/* ---------- RESPOND TO EDIT REQUEST ---------- */
if (isset($_POST['respondEditRequest'])) {
    $request_id = (int)($_POST['request_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $req = $articleObj->getEditRequestById($request_id);

    if (!$req) {
        $_SESSION['message'] = "Request not found.";
        $_SESSION['status'] = '400';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin/index.php'));
        exit;
    }

    $article = $articleObj->getArticles($req['article_id']);
    if (!$article || ($article['author_id'] != $_SESSION['user_id'] && !$userObj->isAdmin())) {
        $_SESSION['message'] = "Permission denied.";
        $_SESSION['status'] = '400';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin/index.php'));
        exit;
    }

    if ($action === 'accept') {
        $articleObj->updateEditRequestStatus($request_id, 'accepted');
        $articleObj->grantSharedArticle($req['article_id'], $req['requester_id']);
        $articleObj->addNotification($req['requester_id'], "Your edit request for \"{$article['title']}\" was accepted.");
        $_SESSION['message'] = "Request accepted.";
        $_SESSION['status'] = '200';
    } else {
        $articleObj->updateEditRequestStatus($request_id, 'rejected');
        $articleObj->addNotification($req['requester_id'], "Your edit request for \"{$article['title']}\" was rejected.");
        $_SESSION['message'] = "Request rejected.";
        $_SESSION['status'] = '200';
    }

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../admin/index.php'));
    exit;
}


/* ---------- MARK NOTIFICATION READ ---------- */
if (isset($_POST['markNotificationRead'])) {
    $nid = (int)($_POST['notification_id'] ?? 0);
    $articleObj->markNotificationRead($nid);
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../writer/index.php'));
    exit;
}
?>
