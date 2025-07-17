<?php
/**
 * API לשמירת דף בילדר
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache');

require_once '../../includes/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// בדיקת הרשאות
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'אין הרשאה']);
    exit;
}

// קבלת נתונים
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'נתונים לא תקינים']);
    exit;
}

$pageId = $input['pageId'] ?? null;
$storeId = $input['storeId'] ?? null;
$sections = $input['sections'] ?? [];

if (!$pageId || !$storeId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'חסרים פרמטרים נדרשים']);
    exit;
}

try {
    $pdo = Database::getInstance()->getConnection();
    
    // בדיקת הרשאות למשתמש הנוכחי
    $user = getCurrentUser();
    $stmt = $pdo->prepare("
        SELECT bp.* FROM builder_pages bp 
        JOIN stores s ON bp.store_id = s.id 
        WHERE bp.id = ? AND s.user_id = ?
    ");
    $stmt->execute([$pageId, $user['id']]);
    $page = $stmt->fetch();
    
    if (!$page) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'אין הרשאה לערוך דף זה']);
        exit;
    }
    
    // שמירת הנתונים
    $pageData = json_encode($sections, JSON_UNESCAPED_UNICODE);
    
    $stmt = $pdo->prepare("
        UPDATE builder_pages 
        SET page_data = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    
    $success = $stmt->execute([$pageData, $pageId]);
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'הדף נשמר בהצלחה',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        throw new Exception('שגיאה בשמירת הדף');
    }
    
} catch (Exception $e) {
    error_log("Save page error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'שגיאה בשמירת הדף'
    ]);
}
?> 