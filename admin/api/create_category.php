<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$auth = new Authentication();
$auth->requireLogin();

$currentUser = $auth->getCurrentUser();
if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'משתמש לא מחובר']);
    exit;
}

// קבלת מידע החנות
$stmt = Database::getInstance()->getConnection()->prepare("
    SELECT * FROM stores WHERE user_id = ? LIMIT 1
");
$stmt->execute([$currentUser['id']]);
$store = $stmt->fetch();

if (!$store) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'חנות לא נמצאה']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$name = trim($input['name'] ?? '');
$description = trim($input['description'] ?? '');

if (empty($name)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'שם הקטגוריה חובה']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // בדיקה אם הקטגוריה כבר קיימת
    $stmt = $db->prepare("
        SELECT id FROM categories 
        WHERE store_id = ? AND name = ?
    ");
    $stmt->execute([$store['id'], $name]);
    
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'קטגוריה עם השם הזה כבר קיימת']);
        exit;
    }
    
    // יצירת הקטגוריה
    $stmt = $db->prepare("
        INSERT INTO categories (store_id, name, description, slug, status, created_at)
        VALUES (?, ?, ?, ?, 'active', NOW())
    ");
    
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9א-ת]/', '-', $name));
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    $stmt->execute([$store['id'], $name, $description, $slug]);
    
    $categoryId = $db->lastInsertId();
    
    echo json_encode([
        'success' => true, 
        'message' => 'הקטגוריה נוצרה בהצלחה',
        'category' => [
            'id' => $categoryId,
            'name' => $name,
            'description' => $description
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Error creating category: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'שגיאה ביצירת הקטגוריה']);
}
?> 