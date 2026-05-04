<?php
session_start();
require_once 'config.php';
require_once 'Model/User.php';

if (!isset($_SESSION['user_id'])) {
    die('Not logged in');
}

// Get user
$user = User::findById($_SESSION['user_id']);
echo "<h1>User Profile Debug</h1>";
echo "<p>User ID: " . htmlspecialchars($_SESSION['user_id']) . "</p>";
echo "<p>Current photo in DB: " . htmlspecialchars($user['photo'] ?? 'NULL') . "</p>";

if (!empty($user['photo'])) {
    $path = $user['photo'];
    echo "<p>Photo path: " . htmlspecialchars($path) . "</p>";
    echo "<p>File exists (relative): " . (file_exists($path) ? 'YES' : 'NO') . "</p>";
    echo "<p>File size: " . (file_exists($path) ? filesize($path) : 'N/A') . " bytes</p>";
    echo "<img src='/" . htmlspecialchars($path) . "' style='max-width:200px; border: 1px solid #ccc;' alt='User photo'>";
}

echo "<hr>";
echo "<h2>Upload Directory Contents</h2>";
if (is_dir('uploads/profile_pictures/')) {
    $files = glob('uploads/profile_pictures/*');
    echo "<ul>";
    foreach ($files as $f) {
        echo "<li>" . htmlspecialchars(basename($f)) . " (" . filesize($f) . " bytes)</li>";
    }
    echo "</ul>";
} else {
    echo "Directory does not exist";
}
?>
