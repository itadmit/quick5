<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get test store
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM stores WHERE domain = 'demo-store'");
$stmt->execute();
$store = $stmt->fetch();

if (!$store) {
    echo json_encode(['success' => false, 'message' => 'Test store not found']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$name = trim($input['name'] ?? '');
$description = trim($input['description'] ?? '');

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'שם הקטגוריה חובה']);
    exit;
}

try {
    // בדיקה אם הקטגוריה כבר קיימת
    $stmt = $db->prepare("
        SELECT id FROM categories 
        WHERE store_id = ? AND name = ?
    ");
    $stmt->execute([$store['id'], $name]);
    
    if ($stmt->fetch()) {
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
    echo json_encode(['success' => false, 'message' => 'שגיאה ביצירת הקטגוריה: ' . $e->getMessage()]);
}

// Auto-delete this test file
sleep(1);
unlink(__FILE__);
?> 