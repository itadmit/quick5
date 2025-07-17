<?php
/**
 * API למחיקה קבוצתית של מוצרים - QuickShop5
 */

header('Content-Type: application/json; charset=utf-8');

// ודא שמדובר בבקשת POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'רק בקשות POST מותרות'
    ]);
    exit;
}

try {
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../config/database.php';

    $auth = new Authentication();
    $currentUser = $auth->getCurrentUser();

    // בדיקת התחברות
    if (!$currentUser) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'נדרשת התחברות'
        ]);
        exit;
    }

    // קבלת נתונים מהבקשה
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['product_ids']) || !is_array($input['product_ids']) || empty($input['product_ids'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'לא נבחרו מוצרים למחיקה'
        ]);
        exit;
    }

    $productIds = array_filter(array_map('intval', $input['product_ids']));
    
    if (empty($productIds)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'מזהי מוצרים לא תקינים'
        ]);
        exit;
    }

    // חיבור למסד הנתונים
    $db = Database::getInstance()->getConnection();

    // קבלת מידע על החנות
    $stmt = $db->prepare("SELECT id FROM stores WHERE user_id = ? LIMIT 1");
    $stmt->execute([$currentUser['id']]);
    $store = $stmt->fetch();

    if (!$store) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'לא נמצאה חנות'
        ]);
        exit;
    }

    // ודא שכל המוצרים שייכים לחנות הזו
    $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
    $stmt = $db->prepare("
        SELECT id, name 
        FROM products 
        WHERE id IN ($placeholders) AND store_id = ?
    ");
    $stmt->execute([...$productIds, $store['id']]);
    $validProducts = $stmt->fetchAll();

    if (count($validProducts) !== count($productIds)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'חלק מהמוצרים לא נמצאו או לא שייכים לחנות שלך'
        ]);
        exit;
    }

    // התחלת טרנזקציה
    $db->beginTransaction();

    $deletedCount = 0;
    $errors = [];

    foreach ($validProducts as $product) {
        try {
            $productId = $product['id'];

            // מחיקת קישורי קטגוריות
            $stmt = $db->prepare("DELETE FROM product_categories WHERE product_id = ?");
            $stmt->execute([$productId]);

            // מחיקת מדיה של המוצר
            $stmt = $db->prepare("DELETE FROM product_media WHERE product_id = ?");
            $stmt->execute([$productId]);

            // מחיקת מאפיינים של המוצר
            $stmt = $db->prepare("DELETE FROM product_attributes WHERE product_id = ?");
            $stmt->execute([$productId]);

            // מחיקת וריאנטים
            $stmt = $db->prepare("DELETE FROM product_variants WHERE product_id = ?");
            $stmt->execute([$productId]);

            // מחיקת ערכי מאפיינים של המוצר (אם יש טבלה כזו)
            $stmt = $db->prepare("DELETE FROM attribute_values WHERE product_id = ?");
            $stmt->execute([$productId]);

            // מחיקת המוצר עצמו
            $stmt = $db->prepare("DELETE FROM products WHERE id = ? AND store_id = ?");
            $stmt->execute([$productId, $store['id']]);

            if ($stmt->rowCount() > 0) {
                $deletedCount++;
            } else {
                $errors[] = "לא ניתן למחוק את המוצר: " . htmlspecialchars($product['name']);
            }

        } catch (Exception $e) {
            $errors[] = "שגיאה במחיקת המוצר '" . htmlspecialchars($product['name']) . "': " . $e->getMessage();
        }
    }

    // אישור הטרנזקציה
    $db->commit();

    // חזרת תוצאות
    $response = [
        'success' => true,
        'deleted_count' => $deletedCount,
        'total_requested' => count($productIds)
    ];

    if (!empty($errors)) {
        $response['warnings'] = $errors;
        $response['partial_success'] = true;
    }

    echo json_encode($response);

} catch (Exception $e) {
    // ביטול הטרנזקציה במקרה של שגיאה
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    error_log("Bulk delete products error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'שגיאה פנימית במחיקת המוצרים'
    ]);
}
?> 