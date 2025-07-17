<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$auth = new Authentication();

// בדיקה אם המשתמש מחובר
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'משתמש לא מחובר']);
    exit;
}

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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$query = trim($_GET['q'] ?? '');
$limit = min(10, max(1, (int)($_GET['limit'] ?? 10))); // מקסימום 10 תוצאות

if (empty($query)) {
    echo json_encode(['success' => true, 'products' => []]);
    exit;
}

if (strlen($query) < 2) {
    echo json_encode(['success' => true, 'products' => []]);
    exit;
}

// ניקוי הקלט מתווים מסוכנים
$query = preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $query);

if (empty($query)) {
    echo json_encode(['success' => true, 'products' => []]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // חיפוש מוצרים לפי שם, SKU או תיאור
    $stmt = $db->prepare("
        SELECT 
            p.id,
            p.name,
            p.sku,
            p.price,
            p.status,
            p.description,
            p.inventory_quantity,
            p.track_inventory,
            GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as category_name,
            (SELECT url FROM product_media WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
        FROM products p
        LEFT JOIN product_categories pc ON p.id = pc.product_id
        LEFT JOIN categories c ON pc.category_id = c.id
        WHERE p.store_id = ? 
        AND (
            p.name LIKE ? 
            OR p.sku LIKE ? 
            OR p.description LIKE ?
        )
        AND p.status IN ('active', 'draft')
        GROUP BY p.id
        ORDER BY 
            CASE 
                WHEN p.name LIKE ? THEN 1
                WHEN p.sku LIKE ? THEN 2
                ELSE 3
            END,
            p.name ASC
        LIMIT ?
    ");
    
    $searchTerm = '%' . $query . '%';
    $exactStart = $query . '%';
    
    $stmt->execute([
        $store['id'],
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $exactStart,
        $exactStart,
        $limit
    ]);
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // פורמט התוצאות
    $formattedProducts = array_map(function($product) {
        // קיצור התיאור
        $shortDescription = '';
        if ($product['description']) {
            $shortDescription = mb_strlen($product['description']) > 100 
                ? mb_substr($product['description'], 0, 100) . '...' 
                : $product['description'];
        }
        
        // בדיקת סטטוס מלאי
        $stockStatus = 'available';
        if ($product['track_inventory'] && $product['inventory_quantity'] !== null) {
            if ($product['inventory_quantity'] <= 0) {
                $stockStatus = 'out_of_stock';
            } elseif ($product['inventory_quantity'] <= 5) {
                $stockStatus = 'low_stock';
            }
        }
        
        return [
            'id' => (int)$product['id'],
            'name' => $product['name'],
            'sku' => $product['sku'],
            'price' => $product['price'] ? number_format($product['price'], 2) : null,
            'status' => $product['status'],
            'description' => $shortDescription,
            'category_name' => $product['category_name'],
            'inventory_quantity' => $product['inventory_quantity'],
            'stock_status' => $stockStatus,
            'image' => $product['image'],
            'display_name' => $product['name'] . ($product['sku'] ? ' (' . $product['sku'] . ')' : '')
        ];
    }, $products);
    
    echo json_encode([
        'success' => true,
        'products' => $formattedProducts,
        'query' => $query,
        'total' => count($formattedProducts),
        'debug' => [
            'store_id' => $store['id'],
            'original_query' => $_GET['q'] ?? '',
            'cleaned_query' => $query,
            'sql_executed' => true
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Error searching products: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'שגיאה בחיפוש מוצרים']);
}
?> 