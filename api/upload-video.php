<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';

// בדיקת הרשאות
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'נדרשת התחברות']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $store = getCurrentStore();
    if (!$store) {
        throw new Exception('חנות לא נמצאה');
    }

    if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('לא נבחר קובץ סרטון או שגיאה בהעלאה');
    }

    $file = $_FILES['video'];
    $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/avi', 'video/mov'];
    $maxSize = 100 * 1024 * 1024; // 100MB

    // בדיקת סוג הקובץ
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('סוג קובץ לא נתמך. נתמכים: MP4, WebM, OGG, AVI, MOV');
    }

    // בדיקת גודל הקובץ
    if ($file['size'] > $maxSize) {
        throw new Exception('הקובץ גדול מדי. גודל מקסימלי: 100MB');
    }

    // יצירת תיקיית העלאות
    $uploadDir = "uploads/stores/{$store['slug']}/videos/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // יצירת שם קובץ ייחודי
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;

    // העלאת הקובץ
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('שגיאה בשמירת הקובץ');
    }

    // יצירת URL יחסי
    $videoUrl = '/' . $filepath;

    echo json_encode([
        'success' => true,
        'url' => $videoUrl,
        'filename' => $filename,
        'size' => $file['size'],
        'type' => $mimeType
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 