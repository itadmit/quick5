<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in
requireLogin();

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Validate required fields
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'שם הקטגוריה הוא שדה חובה']);
        exit;
    }
    
    // Check if category already exists for this store
    $stmt = $db->prepare("SELECT id FROM categories WHERE name = ? AND store_id = ?");
    $stmt->execute([$name, $_SESSION['store_id']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'קטגוריה בשם זה כבר קיימת']);
        exit;
    }
    
    // Create URL slug
    $slug = createSlug($name);
    
    // Insert new category
    $stmt = $db->prepare("
        INSERT INTO categories (store_id, name, slug, description, status, created_at) 
        VALUES (?, ?, ?, ?, 'active', NOW())
    ");
    
    $stmt->execute([
        $_SESSION['store_id'],
        $name,
        $slug,
        $description
    ]);
    
    $categoryId = $db->lastInsertId();
    
    // Return success response with category data
    echo json_encode([
        'success' => true,
        'message' => 'הקטגוריה נוצרה בהצלחה',
        'category' => [
            'id' => $categoryId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error creating category: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'שגיאת שרת פנימית']);
}

function createSlug($string) {
    // Remove Hebrew vowels and diacritics
    $string = preg_replace('/[\x{05B0}-\x{05BD}\x{05BF}\x{05C1}-\x{05C2}\x{05C4}-\x{05C5}\x{05C7}]/u', '', $string);
    
    // Convert to lowercase and replace spaces with hyphens
    $slug = strtolower(trim($string));
    $slug = preg_replace('/\s+/', '-', $slug);
    
    // Remove any characters that aren't letters, numbers, hyphens, or Hebrew
    $slug = preg_replace('/[^\p{Hebrew}a-z0-9\-]/u', '', $slug);
    
    // Remove multiple consecutive hyphens
    $slug = preg_replace('/-+/', '-', $slug);
    
    // Remove leading/trailing hyphens
    $slug = trim($slug, '-');
    
    return $slug;
}
?> 