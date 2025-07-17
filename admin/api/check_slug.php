<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../config/database.php';

// בדיקת אוטורזציה
if (!isLoggedIn() || !hasRole(['admin', 'store_manager'])) {
    http_response_code(401);
    echo json_encode(['error' => 'לא מורשה']);
    exit;
}

// בדיקת method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// קבלת נתונים
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['slug']) || empty(trim($input['slug']))) {
    echo json_encode(['available' => false, 'error' => 'slug חסר']);
    exit;
}

$slug = trim($input['slug']);

try {
    $db = Database::getInstance()->getConnection();
    
    // קבלת store_id של המשתמש הנוכחי
    $storeStmt = $db->prepare("SELECT id FROM stores WHERE user_id = ? LIMIT 1");
    $storeStmt->execute([$_SESSION['user_id']]);
    $store = $storeStmt->fetch();
    
    if (!$store) {
        echo json_encode(['available' => false, 'error' => 'לא נמצאה חנות']);
        exit;
    }
    
    $storeId = $store['id'];
    
    // בדיקה אם הslug קיים בחנות זו
    $stmt = $db->prepare("SELECT id FROM products WHERE slug = ? AND store_id = ?");
    $stmt->execute([$slug, $storeId]);
    $existingProduct = $stmt->fetch();
    
    // הslug זמין אם לא נמצא מוצר קיים
    $available = !$existingProduct;
    
    echo json_encode([
        'available' => $available,
        'slug' => $slug
    ]);
    
} catch (Exception $e) {
    error_log("Slug check error: " . $e->getMessage());
    echo json_encode(['available' => false, 'error' => 'שגיאה בבדיקה']);
}
?> 