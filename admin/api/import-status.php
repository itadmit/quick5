<?php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

// בדיקת הרשאות
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'אין הרשאה']);
    exit;
}

try {
    $import_id = $_GET['import_id'] ?? '';
    
    if (empty($import_id)) {
        throw new Exception('מזהה ייבוא חסר');
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // שליפת מידע על הייבוא
    $stmt = $pdo->prepare("
        SELECT 
            import_id,
            status,
            total_rows,
            processed_rows,
            imported_products,
            failed_products,
            progress_percent,
            current_step,
            error_log,
            started_at,
            completed_at
        FROM import_jobs 
        WHERE import_id = ? AND user_id = ?
    ");
    
    $stmt->execute([$import_id, $_SESSION['user_id']]);
    $job = $stmt->fetch();
    
    if (!$job) {
        throw new Exception('עבודת ייבוא לא נמצאה');
    }

    // עיבוד שגיאות
    $errors = [];
    if (!empty($job['error_log'])) {
        $errors = array_filter(explode("\n", $job['error_log']));
    }

    // קביעת אם הייבוא הושלם
    $completed = in_array($job['status'], ['completed', 'failed']);

    echo json_encode([
        'success' => true,
        'status' => $job['current_step'] ?: $job['status'],
        'progress' => floatval($job['progress_percent']),
        'total' => intval($job['total_rows']),
        'processed' => intval($job['processed_rows']),
        'imported' => intval($job['imported_products']),
        'failed' => intval($job['failed_products']),
        'completed' => $completed,
        'errors' => $errors,
        'started_at' => $job['started_at'],
        'completed_at' => $job['completed_at']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 