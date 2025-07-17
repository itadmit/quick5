<?php
require_once '../../includes/auth.php';
require_once '../../includes/OrderStatusManager.php';

// בדיקת הרשאות מנהל
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(401);
    echo json_encode(['error' => 'אין הרשאה']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$statusManager = new OrderStatusManager();

// קבלת store_id מהמשתמש הנוכחי
$user = getCurrentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'משתמש לא נמצא']);
    exit;
}

// קבלת החנות של המשתמש
require_once '../../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT id FROM stores WHERE user_id = ? LIMIT 1");
$stmt->execute([$user['id']]);
$store = $stmt->fetch();

if (!$store) {
    http_response_code(404);
    echo json_encode(['error' => 'חנות לא נמצאה']);
    exit;
}

$storeId = $store['id'];

try {
    switch ($method) {
        case 'GET':
            handleGet($statusManager, $storeId);
            break;
            
        case 'POST':
            handlePost($statusManager, $storeId);
            break;
            
        case 'PUT':
            handlePut($statusManager, $storeId);
            break;
            
        case 'DELETE':
            handleDelete($statusManager, $storeId);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'שיטה לא נתמכת']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'שגיאת שרת: ' . $e->getMessage()]);
}

function handleGet($statusManager, $storeId) {
    $type = $_GET['type'] ?? 'order'; // order או payment
    $includeInactive = isset($_GET['include_inactive']) && $_GET['include_inactive'] === 'true';
    
    if (isset($_GET['id'])) {
        // קבלת סטטוס בודד
        $statusId = (int)$_GET['id'];
        if ($type === 'payment') {
            $status = $statusManager->getPaymentStatus($storeId, $statusId);
        } else {
            $status = $statusManager->getOrderStatus($storeId, $statusId);
        }
        
        if ($status) {
            echo json_encode($status);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'סטטוס לא נמצא']);
        }
    } else {
        // קבלת כל הסטטוסים
        if ($type === 'payment') {
            $statuses = $statusManager->getPaymentStatuses($storeId, !$includeInactive);
        } else {
            $statuses = $statusManager->getOrderStatuses($storeId, !$includeInactive);
        }
        
        echo json_encode(['statuses' => $statuses]);
    }
}

function handlePost($statusManager, $storeId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'נתונים לא תקינים']);
        return;
    }
    
    $type = $input['type'] ?? 'order';
    
    // ולידציה
    $required = ['name', 'display_name', 'color'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "שדה $field נדרש"]);
            return;
        }
    }
    
    // יצירת slug אוטומטי אם לא סופק
    if (empty($input['slug'])) {
        $input['slug'] = generateSlug($input['name']);
    }
    
    // הגדרות ברירת מחדל
    $input['background_color'] = $input['background_color'] ?? lightenColor($input['color']);
    $input['icon'] = $input['icon'] ?? ($type === 'payment' ? 'ri-money-dollar-circle-line' : 'ri-circle-line');
    
    // ווידוא שsort_order הוא מספר תקין
    if (empty($input['sort_order']) || !is_numeric($input['sort_order'])) {
        $input['sort_order'] = getNextSortOrder($statusManager, $storeId, $type);
    }
    $input['sort_order'] = (int)$input['sort_order'];
    
    try {
        if ($type === 'payment') {
            $result = $statusManager->createPaymentStatus($storeId, $input);
        } else {
            $result = $statusManager->createOrderStatus($storeId, $input);
        }
        
        if ($result) {
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'סטטוס נוצר בהצלחה']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'שגיאה ביצירת הסטטוס']);
        }
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'unique_store_slug') !== false) {
            http_response_code(409);
            echo json_encode(['error' => 'שם הסטטוס כבר קיים']);
        } else {
            throw $e;
        }
    }
}

function handlePut($statusManager, $storeId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'נתונים לא תקינים']);
        return;
    }
    
    $statusId = (int)$input['id'];
    $type = $input['type'] ?? 'order';
    
    if ($type === 'payment') {
        $result = $statusManager->updatePaymentStatus($storeId, $statusId, $input);
    } else {
        $result = $statusManager->updateOrderStatus($storeId, $statusId, $input);
    }
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'סטטוס עודכן בהצלחה']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'שגיאה בעדכון הסטטוס או שהסטטוס הוא מערכתי']);
    }
}

function handleDelete($statusManager, $storeId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'נתונים לא תקינים']);
        return;
    }
    
    $statusId = (int)$input['id'];
    $type = $input['type'] ?? 'order';
    
    if ($type === 'payment') {
        $result = $statusManager->deletePaymentStatus($storeId, $statusId);
    } else {
        $result = $statusManager->deleteOrderStatus($storeId, $statusId);
    }
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'סטטוס נמחק בהצלחה']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'שגיאה במחיקת הסטטוס או שהסטטוס הוא מערכתי']);
    }
}

// פונקציות עזר
function generateSlug($name) {
    $slug = strtolower($name);
    $slug = preg_replace('/[^a-z0-9_-]/', '', $slug);
    return $slug;
}

function lightenColor($color) {
    // המרת צבע hex לצבע בהיר יותר לרקע
    $colorMap = [
        '#EF4444' => '#FEE2E2', // אדום
        '#F59E0B' => '#FEF3C7', // כתום
        '#10B981' => '#D1FAE5', // ירוק
        '#3B82F6' => '#DBEAFE', // כחול
        '#8B5CF6' => '#EDE9FE', // סגול
        '#6366F1' => '#E0E7FF', // אינדיגו
        '#6B7280' => '#F3F4F6'  // אפור
    ];
    
    return $colorMap[$color] ?? '#F3F4F6';
}

function getNextSortOrder($statusManager, $storeId, $type) {
    try {
        if ($type === 'payment') {
            $statuses = $statusManager->getPaymentStatuses($storeId, false);
        } else {
            $statuses = $statusManager->getOrderStatuses($storeId, false);
        }
        
        $maxOrder = 0;
        if (is_array($statuses)) {
            foreach ($statuses as $status) {
                $sortOrder = isset($status['sort_order']) ? (int)$status['sort_order'] : 0;
                if ($sortOrder > $maxOrder) {
                    $maxOrder = $sortOrder;
                }
            }
        }
        
        return $maxOrder + 1;
    } catch (Exception $e) {
        // אם יש שגיאה, תחזיר ברירת מחדל
        error_log("Error getting next sort order: " . $e->getMessage());
        return 1;
    }
}
?> 