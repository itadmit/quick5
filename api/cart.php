<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/CartManager.php';

try {
    $database = Database::getInstance();
    $db = $database->getConnection();
    $cartManager = new CartManager($db);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            handleGet($cartManager, $action);
            break;
            
        case 'POST':
            handlePost($cartManager, $action);
            break;
            
        case 'PUT':
            handlePut($cartManager, $action);
            break;
            
        case 'DELETE':
            handleDelete($cartManager, $action);
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'שיטה לא נתמכת'
            ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    error_log("Cart API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'שגיאת שרת פנימית'
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * טיפול בבקשות GET
 */
function handleGet($cartManager, $action) {
    switch ($action) {
        case 'get':
            // קבלת העגלה המלאה
            $cart = $cartManager->getCart();
            echo json_encode([
                'success' => true,
                'cart' => $cart,
                'item_count' => $cartManager->getItemCount(),
                'total' => $cartManager->getTotal()
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'count':
            // קבלת מספר פריטים בלבד
            echo json_encode([
                'success' => true,
                'count' => $cartManager->getItemCount(),
                'total' => $cartManager->getTotal()
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'פעולה לא מוכרת'
            ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * טיפול בבקשות POST - הוספה לעגלה
 */
function handlePost($cartManager, $action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'נתונים לא תקינים'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    switch ($action) {
        case 'add':
            // הוספת מוצר לעגלה
            $productId = $input['product_id'] ?? null;
            $variantId = $input['variant_id'] ?? null;
            $quantity = $input['quantity'] ?? 1;
            
            if (!$productId) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'מספר מוצר חסר'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $result = $cartManager->addItem($productId, $variantId, $quantity);
            
            if ($result['success']) {
                http_response_code(200);
            } else {
                http_response_code(400);
            }
            
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'פעולה לא מוכרת'
            ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * טיפול בבקשות PUT - עדכון כמות
 */
function handlePut($cartManager, $action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'נתונים לא תקינים'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    switch ($action) {
        case 'update':
            // עדכון כמות פריט
            $itemKey = $input['item_key'] ?? null;
            $quantity = $input['quantity'] ?? null;
            
            if (!$itemKey || $quantity === null) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'מפתח פריט או כמות חסרים'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $result = $cartManager->updateQuantity($itemKey, $quantity);
            
            if ($result['success']) {
                http_response_code(200);
            } else {
                http_response_code(400);
            }
            
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'פעולה לא מוכרת'
            ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * טיפול בבקשות DELETE - הסרה מהעגלה
 */
function handleDelete($cartManager, $action) {
    switch ($action) {
        case 'remove':
            // הסרת פריט בודד
            $itemKey = $_GET['item_key'] ?? null;
            
            if (!$itemKey) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'מפתח פריט חסר'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $result = $cartManager->removeItem($itemKey);
            
            if ($result['success']) {
                http_response_code(200);
            } else {
                http_response_code(400);
            }
            
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;
            
        case 'clear':
            // ניקוי העגלה המלאה
            $result = $cartManager->clearCart();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'פעולה לא מוכרת'
            ], JSON_UNESCAPED_UNICODE);
    }
}
?> 