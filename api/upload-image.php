<?php
/**
 * API להעלאת תמונות
 * תומך ב-base64 ו-FormData
 */

// מניעת שגיאות PHP מהצגה לפני JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// טיפול ב-preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// רק POST מותר
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'רק POST מותר']);
    exit;
}

try {
    require_once '../includes/config.php';
    require_once '../includes/ImageUploader.php';
    require_once '../includes/auth.php';
    
    // בדיקת הרשאות (אם נדרש)
    if (!isset($_SESSION)) session_start();
    
    // קבלת store_id - עדיפות ל-POST על פני SESSION
    $storeId = null;
    
    if (!empty($_POST['store_id'])) {
        $storeId = (int)$_POST['store_id'];
    } elseif (!empty($_SESSION['current_store_id'])) {
        $storeId = (int)$_SESSION['current_store_id'];
    }
    
    if (!$storeId) {
        throw new Exception('מזהה חנות לא סופק');
    }
    
    // קבלת מידע החנות
    $storeInfo = ImageUploader::getStoreInfo($storeId);
    if (!$storeInfo) {
        throw new Exception('חנות לא נמצאה');
    }
    
    // יצירת uploader לחנות
    $uploader = new ImageUploader($storeInfo['slug']);
    
    $result = null;
    $folder = $_POST['folder'] ?? 'products';
    
    // בדיקה אם זה base64 או file upload
    if (!empty($_POST['image_data'])) {
        // העלאה מ-base64
        $imageData = $_POST['image_data'];
        $filename = $_POST['filename'] ?? null;
        
        $result = $uploader->uploadFromBase64($imageData, $filename, $folder);
        
    } elseif (!empty($_FILES['image'])) {
        // העלאה מ-FormData
        $result = $uploader->uploadFromFormData($_FILES['image'], $folder);
        
    } else {
        throw new Exception('לא נמצאו נתוני תמונה');
    }
    
    if (!$result['success']) {
        throw new Exception($result['message']);
    }
    
    // הוספת מידע נוסף לתגובה
    $result['folder'] = $folder;
    $result['store_id'] = $storeId;
    $result['timestamp'] = time();
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?> 