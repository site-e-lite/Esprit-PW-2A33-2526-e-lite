<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['avatar'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file provided']);
    exit;
}

require_once __DIR__ . '/Model/User.php';

$file = $_FILES['avatar'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Upload error: ' . $file['error']]);
    exit;
}

// Validate file type using MIME type
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowed)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file format. Allowed: JPG, PNG, GIF, WebP']);
    exit;
}

// Check file size (max 5MB)
$maxSize = 5 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large. Maximum size is 5MB']);
    exit;
}

// Check minimum file size
if ($file['size'] < 1024) {
    http_response_code(400);
    echo json_encode(['error' => 'File is too small']);
    exit;
}

// Create upload directory
$uploadDir = 'uploads/profile_pictures/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create upload directory']);
        exit;
    }
}

// Generate unique filename
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$newName = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
$targetPath = $uploadDir . $newName;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save file']);
    exit;
}

// Get current user to remove old avatar
$user = User::findById($_SESSION['user_id']);
if (!empty($user['photo']) && file_exists($user['photo'])) {
    @unlink($user['photo']);
}

// Update database
if (!User::updatePhoto($_SESSION['user_id'], $targetPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update profile']);
    exit;
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Profile picture updated successfully',
    'url' => '/' . $targetPath
]);
