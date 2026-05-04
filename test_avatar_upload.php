<?php
session_start();
require_once 'Model/User.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $user = User::findById($_SESSION['user_id']);
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['avatar']['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowed)) {
        $error = "Format non autorisé.";
    } elseif ($_FILES['avatar']['size'] > 5 * 1024 * 1024) {
        $error = "Fichier trop volumineux (max 5 Mo).";
    } else {
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $newName = "avatar_" . $_SESSION['user_id'] . "_" . time() . "." . $ext;
        $target = "uploads/profile_pictures/" . $newName;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
            if (!empty($user['photo']) && file_exists($user['photo'])) unlink($user['photo']);
            User::update($_SESSION['user_id'], ['photo' => $target]);
            $success = "Avatar mis à jour !";
        } else {
            $error = "Erreur lors du téléchargement.";
        }
    }
}
$user = User::findById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Avatar Upload</title>
    <style>body { font-family: Arial; background: #0a0a0c; color: #f0f0f0; padding: 2rem; }</style>
</head>
<body>
    <h1>Test Avatar Upload</h1>
    <?php if (isset($success)) echo "<p style='color:green'>$success</p>"; ?>
    <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
    <div style="margin-bottom: 1rem;">
        <?php if (!empty($user['photo']) && file_exists($user['photo'])): ?>
            <img src="/<?= $user['photo'] ?>" style="width: 100px; border-radius: 50%;"><br>
        <?php else: ?>
            <p>Pas d'avatar</p>
        <?php endif; ?>
    </div>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp" required>
        <button type="submit">Upload Avatar</button>
    </form>
    <p><a href="/profile">Retour au profil</a></p>
</body>
</html>
