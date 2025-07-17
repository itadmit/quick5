<?php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/CsvImporter.php';

header('Content-Type: application/json; charset=utf-8');

// בדיקת הרשאות
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'אין הרשאה']);
    exit;
}

try {
    // בדיקת בקשה
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('שיטת בקשה לא תקינה');
    }

    // בדיקת קובץ
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('שגיאה בהעלאת הקובץ');
    }

    $file = $_FILES['csv_file'];
    
    // בדיקת סוג קובץ
    $fileInfo = pathinfo($file['name']);
    if (strtolower($fileInfo['extension']) !== 'csv') {
        throw new Exception('סוג קובץ לא נתמך. אנא העלה קובץ CSV');
    }

    // בדיקת גודל קובץ (10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        throw new Exception('הקובץ גדול מדי. מקסימום 10MB');
    }

    // קבלת פרמטרים
    $store_id = intval($_POST['store_id'] ?? 0);
    $skip_existing = isset($_POST['skip_existing']) && $_POST['skip_existing'] === 'true';
    $download_images = isset($_POST['download_images']) && $_POST['download_images'] === 'true';
    $create_categories = isset($_POST['create_categories']) && $_POST['create_categories'] === 'true';
    $image_domain = trim($_POST['image_domain'] ?? '');
    $image_quality = $_POST['image_quality'] ?? 'high';

    if (!$store_id) {
        throw new Exception('מזהה חנות לא תקין');
    }

    // בדיקת בעלות על החנות
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("SELECT id FROM stores WHERE id = ? AND user_id = ?");
    $stmt->execute([$store_id, $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        throw new Exception('אין הרשאה לחנות זו');
    }

    // יצירת תיקיית uploads אם לא קיימת
    $uploads_dir = "../../uploads/imports";
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0755, true);
    }

    // יצירת שם קובץ ייחודי
    $import_id = uniqid('import_', true);
    $csv_path = $uploads_dir . '/' . $import_id . '.csv';

    // העברת הקובץ
    if (!move_uploaded_file($file['tmp_name'], $csv_path)) {
        throw new Exception('שגיאה בשמירת הקובץ');
    }

    // יצירת רשומת ייבוא במסד הנתונים
    $stmt = $pdo->prepare("
        INSERT INTO import_jobs (
            import_id, store_id, user_id, filename, file_path, 
            skip_existing, download_images, create_categories, 
            image_domain, image_quality, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    $stmt->execute([
        $import_id,
        $store_id,
        $_SESSION['user_id'],
        $file['name'],
        $csv_path,
        $skip_existing ? 1 : 0,
        $download_images ? 1 : 0,
        $create_categories ? 1 : 0,
        $image_domain,
        $image_quality
    ]);

    // התחלת עיבוד ברקע דרך HTTP request
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base_path = str_replace('/admin/api/import-csv.php', '', $_SERVER['REQUEST_URI']);
    $process_url = $protocol . '://' . $host . $base_path . '/admin/api/process-import.php?import_id=' . urlencode($import_id);
    
    // הפעלה אסינכרונית דרך cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $process_url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2); // timeout מהיר כדי לא לחכות
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    curl_close($ch);

    echo json_encode([
        'success' => true,
        'import_id' => $import_id,
        'message' => 'ייבוא התחיל בהצלחה'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 