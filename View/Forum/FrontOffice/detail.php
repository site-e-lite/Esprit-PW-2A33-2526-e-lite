<?php
/**
 * Forum Detail View - View/Forum/FrontOffice/detail.php
 * Display forum with posts and access control
 * URL: /forum/{forumId}
 */

session_start();

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Utils/PermissionHelper.php';
require_once __DIR__ . '/../../../Controller/Forum/ForumController.php';

// Get forum ID from URL
if (!isset($forumId)) {
    $forumId = (int)($_GET['id'] ?? $_GET['forumId'] ?? 0);
}

if ($forumId === 0) {
    http_response_code(404);
    echo "Forum not found";
    exit;
}

$db = Config::getInstance()->getConnexion();
$userId = $_SESSION['user_id'] ?? 0;
$isLoggedIn = $userId > 0;

// Get forum data
$stmt = $db->prepare("SELECT f.*, c.titre as courseTitre FROM forum f LEFT JOIN course c ON f.idCourse = c.idCourse WHERE f.idForum = :id");
$stmt->execute([':id' => $forumId]);
$forum = $stmt->fetch();

if (!$forum) {
    http_response_code(404);
    echo "Forum not found";
    exit;
}

// Check access permission
$forumController = new ForumController();
$canAccess = $isLoggedIn && $forumController->canAccessCourseForum($userId, $forum['idCourse']);

if (!$canAccess && !($forum['idCourse'] === null)) {
    // Forum linked to course and no access
    http_response_code(403);
    include __DIR__ . '/../../../View/layout/header.php';
    echo '<div style="padding: 40px; text-align: center; color: #999;">';
    echo '<h1>Access Denied</h1>';
    echo '<p>You must be enrolled in the course to access this forum.</p>';
    echo '</div>';
    include __DIR__ . '/../../../View/layout/footer.php';
    exit;
}

// Get forum posts
$stmt = $db->prepare("
    SELECT p.*, u.nom, u.prenom, u.photo,
           (SELECT COUNT(*) FROM post WHERE idUser = u.idUser) as userPostCount
    FROM post p
    JOIN user u ON p.idUser = u.idUser
    WHERE p.idForum = :forumId
    ORDER BY p.datePost DESC
    LIMIT 50
");
$stmt->execute([':forumId' => $forumId]);
$posts = $stmt->fetchAll();

// Handle new post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'post') {
    if (!$isLoggedIn) {
        http_response_code(403);
        echo "You must be logged in to post";
        exit;
    }
    
    if (!$forumController->canPostInForum($userId, $forumId)) {
        http_response_code(403);
        echo "You cannot post in this forum";
        exit;
    }
    
    $contenu = trim($_POST['contenu'] ?? '');
    
    if (empty($contenu)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Post cannot be empty'];
    } else {
        $stmt = $db->prepare("INSERT INTO post (contenu, datePost, idUser, idForum) VALUES (:contenu, NOW(), :userId, :forumId)");
        
        if ($stmt->execute([
            ':contenu' => $contenu,
            ':userId' => $userId,
            ':forumId' => $forumId
        ])) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Post created successfully'];
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
}

$basePath = isset($basePath) ? $basePath : '';

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($forum['titre']) ?> - e-lite</title>
    <link rel="stylesheet" href="<?= $basePath ?>/View/assets/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .forum-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; }
        .forum-header h1 { margin: 0; font-size: 28px; }
        .forum-header p { margin: 10px 0 0 0; opacity: 0.9; }
        .forum-container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .forum-stats { display: flex; gap: 30px; margin-bottom: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px; }
        .stat { }
        .stat-value { font-size: 24px; font-weight: bold; color: #667eea; }
        .stat-label { font-size: 12px; color: #999; margin-top: 5px; }
        .post-list { list-style: none; padding: 0; margin: 0; }
        .post-item { background: white; border: 1px solid #e0e0e0; padding: 20px; margin-bottom: 15px; border-radius: 8px; }
        .post-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
        .post-author { display: flex; align-items: center; gap: 10px; }
        .post-avatar { width: 40px; height: 40px; border-radius: 50%; background: #667eea; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .post-author-info { }
        .post-author-info strong { display: block; }
        .post-author-info small { color: #999; }
        .post-time { color: #999; font-size: 12px; }
        .post-content { line-height: 1.6; color: #333; margin-bottom: 10px; }
        .post-actions { display: flex; gap: 10px; font-size: 12px; }
        .post-actions a { color: #667eea; text-decoration: none; cursor: pointer; }
        .post-actions a:hover { text-decoration: underline; }
        .post-form { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .post-form h3 { margin-top: 0; }
        .post-form textarea { width: 100%; min-height: 120px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit; }
        .post-form button { background: #667eea; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-top: 10px; }
        .post-form button:hover { background: #5568d3; }
        .empty-forum { text-align: center; padding: 40px 20px; color: #999; }
        .flash { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .flash.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../../../View/layout/header.php'; ?>

<div class="forum-header">
    <div style="max-width: 900px; margin: 0 auto;">
        <h1><?= htmlspecialchars($forum['titre']) ?></h1>
        <?php if (!empty($forum['courseTitre'])): ?>
            <p><i class="fas fa-book"></i> From: <?= htmlspecialchars($forum['courseTitre']) ?></p>
        <?php endif; ?>
    </div>
</div>

<div class="forum-container">
    <?php if (isset($_SESSION['flash'])): 
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
    ?>
        <div class="flash <?= htmlspecialchars($flash['type']) ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="forum-stats">
        <div class="stat">
            <div class="stat-value"><?= count($posts) ?></div>
            <div class="stat-label">Posts</div>
        </div>
        <div class="stat">
            <div class="stat-value"><?= date('M d, Y', strtotime($forum['dateCreation'])) ?></div>
            <div class="stat-label">Created</div>
        </div>
    </div>

    <?php if ($isLoggedIn && $forumController->canPostInForum($userId, $forumId)): ?>
        <div class="post-form">
            <h3>Post a Message</h3>
            <form method="POST">
                <input type="hidden" name="action" value="post">
                <textarea name="contenu" placeholder="Share your thoughts, questions, or insights..." required></textarea>
                <button type="submit"><i class="fas fa-paper-plane"></i> Post</button>
            </form>
        </div>
    <?php elseif (!$isLoggedIn): ?>
        <div style="background: #fff3cd; padding: 15px; border-radius: 4px; margin-bottom: 20px; color: #856404;">
            <strong>Login Required:</strong> Please <a href="<?= $basePath ?>/login">login</a> to post messages.
        </div>
    <?php endif; ?>

    <?php if (!empty($posts)): ?>
        <ul class="post-list">
            <?php foreach ($posts as $post): ?>
                <li class="post-item">
                    <div class="post-header">
                        <div class="post-author">
                            <div class="post-avatar">
                                <?= strtoupper(substr($post['prenom'] ?? '', 0, 1)) ?>
                            </div>
                            <div class="post-author-info">
                                <strong><?= htmlspecialchars($post['prenom'] . ' ' . $post['nom']) ?></strong>
                                <small><?= $post['userPostCount'] ?> posts</small>
                            </div>
                        </div>
                        <div class="post-time">
                            <i class="fas fa-clock"></i> <?= date('M d, Y H:i', strtotime($post['datePost'])) ?>
                        </div>
                    </div>
                    <div class="post-content">
                        <?= nl2br(htmlspecialchars($post['contenu'])) ?>
                    </div>
                    <?php if ($isLoggedIn && ($userId === $post['idUser'] || PermissionHelper::isAdmin($userId))): ?>
                        <div class="post-actions">
                            <a href="#" onclick="alert('Edit feature coming soon')">Edit</a>
                            <a href="#" onclick="alert('Delete feature coming soon')">Delete</a>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="empty-forum">
            <i class="fas fa-comments" style="font-size: 40px; margin-bottom: 10px; opacity: 0.3;"></i>
            <p>No posts yet. Be the first to start the discussion!</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../../View/layout/footer.php'; ?>
</body>
</html>
