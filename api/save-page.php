<?php
/**
 * API לשמירה ופרסום דפים מהבילדר
 */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    require_once '../config/database.php';
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('נתונים לא תקינים');
    }
    
    $storeId = $data['store_id'] ?? null;
    $pageType = $data['page_type'] ?? 'home';
    $pageData = $data['page_data'] ?? [];
    $isPublished = $data['is_published'] ?? true;
    
    if (!$storeId || !is_array($pageData)) {
        throw new Exception('חסרים נתונים נדרשים');
    }
    
    $pdo = Database::getInstance()->getConnection();
    
    // בדיקה אם יש דף קיים
    $stmt = $pdo->prepare("SELECT id FROM builder_pages WHERE store_id = ? AND page_type = ?");
    $stmt->execute([$storeId, $pageType]);
    $existingPage = $stmt->fetch();
    
    if ($existingPage) {
        // עדכון דף קיים
        $stmt = $pdo->prepare("
            UPDATE builder_pages 
            SET page_data = ?, is_published = ?, updated_at = NOW()
            WHERE store_id = ? AND page_type = ?
        ");
        $stmt->execute([
            json_encode($pageData),
            $isPublished ? 1 : 0,
            $storeId,
            $pageType
        ]);
    } else {
        // יצירת דף חדש
        $stmt = $pdo->prepare("
            INSERT INTO builder_pages (store_id, page_type, page_data, is_published, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $storeId,
            $pageType,
            json_encode($pageData),
            $isPublished ? 1 : 0
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => $isPublished ? 'הדף נשמר ופורסם בהצלחה' : 'הדף נשמר בהצלחה',
        'data' => [
            'store_id' => $storeId,
            'page_type' => $pageType,
            'is_published' => $isPublished,
            'sections_count' => count($pageData)
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 