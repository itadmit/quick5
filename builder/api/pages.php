<?php
/**
 * Generic Pages API - לכל הדפים בכל החנויות
 * מחליף את save-hero.php ו-load-hero.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'לא מחובר']);
    exit;
}

// Get current store ID
$storeId = getCurrentStoreId();
if (!$storeId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'לא נמצאה חנות']);
    exit;
}

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            handleLoadPage($storeId);
            break;
        case 'POST':
            handleSavePage($storeId);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    error_log("Error in pages API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * טעינת דף - מהמסד או מדמו
 */
function handleLoadPage($storeId) {
    $pageType = $_GET['page_type'] ?? 'home';
    
    try {
        global $pdo;
        
        // Try to load from database first
        $stmt = $pdo->prepare("
            SELECT page_data, updated_at 
            FROM builder_pages 
            WHERE store_id = ? AND page_type = ?
        ");
        $stmt->execute([$storeId, $pageType]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Found in database
            $pageData = json_decode($result['page_data'], true);
            echo json_encode([
                'success' => true,
                'source' => 'database',
                'page_data' => $pageData,
                'last_updated' => $result['updated_at']
            ]);
        } else {
            // Load demo data
            $demoData = loadDemoData($pageType);
            echo json_encode([
                'success' => true,
                'source' => 'demo',
                'page_data' => $demoData,
                'last_updated' => null
            ]);
        }
        
    } catch (Exception $e) {
        throw new Exception('שגיאה בטעינת הדף: ' . $e->getMessage());
    }
}

/**
 * שמירת דף למסד נתונים
 */
function handleSavePage($storeId) {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['page_type']) || !isset($data['page_data'])) {
        throw new Exception('נתונים לא תקינים');
    }
    
    $pageType = $data['page_type'];
    $pageData = $data['page_data'];
    
    // Validate page data
    validatePageData($pageData);
    
    try {
        global $pdo;
        
        // Check if page exists
        $stmt = $pdo->prepare("
            SELECT id FROM builder_pages 
            WHERE store_id = ? AND page_type = ?
        ");
        $stmt->execute([$storeId, $pageType]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            // Update existing page
            $stmt = $pdo->prepare("
                UPDATE builder_pages 
                SET page_data = ?, updated_at = NOW()
                WHERE store_id = ? AND page_type = ?
            ");
            $stmt->execute([
                json_encode($pageData, JSON_UNESCAPED_UNICODE),
                $storeId,
                $pageType
            ]);
        } else {
            // Insert new page
            $stmt = $pdo->prepare("
                INSERT INTO builder_pages (store_id, page_type, page_data, is_published, created_at, updated_at)
                VALUES (?, ?, ?, 1, NOW(), NOW())
            ");
            $stmt->execute([
                $storeId,
                $pageType,
                json_encode($pageData, JSON_UNESCAPED_UNICODE)
            ]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'הדף נשמר בהצלחה',
            'page_type' => $pageType,
            'store_id' => $storeId
        ]);
        
    } catch (Exception $e) {
        throw new Exception('שגיאה בשמירת הדף: ' . $e->getMessage());
    }
}

/**
 * טעינת נתוני דמו
 */
function loadDemoData($pageType) {
    $demoFile = __DIR__ . "/../demo/{$pageType}.json";
    
    if (file_exists($demoFile)) {
        $demoData = file_get_contents($demoFile);
        return json_decode($demoData, true);
    }
    
    // Default demo structure if file doesn't exist
    return [
        'page_type' => $pageType,
        'sections' => [
            [
                'type' => 'hero',
                'id' => 'hero_1',
                'order' => 1,
                'data' => getDefaultHeroData()
            ]
        ]
    ];
}

/**
 * אימות נתוני דף
 */
function validatePageData($pageData) {
    if (!is_array($pageData)) {
        throw new Exception('page_data חייב להיות מערך');
    }
    
    if (!isset($pageData['sections']) || !is_array($pageData['sections'])) {
        throw new Exception('sections חייב להיות מערך');
    }
    
    foreach ($pageData['sections'] as $section) {
        if (!isset($section['type']) || !isset($section['id'])) {
            throw new Exception('כל section חייב להכיל type ו-id');
        }
    }
    
    return true;
}

/**
 * נתוני Hero ברירת מחדל
 */
function getDefaultHeroData() {
    return [
        'title' => 'ברוכים הבאים לחנות שלנו',
        'subtitle' => 'גלה את המוצרים הטובים ביותר במחירים הכי טובים',
        'buttons' => [
            [
                'text' => 'קנה עכשיו',
                'link' => '/products',
                'newTab' => false,
                'style' => 'filled'
            ]
        ],
        'bgType' => 'color',
        'bgColor' => '#3B82F6',
        'titleColor' => '#FFFFFF',
        'subtitleColor' => '#E5E7EB',
        'titleFontSize' => 42,
        'subtitleFontSize' => 18,
        'width' => 'container',
        'contentPosition' => 'center-center',
        'heightType' => 'vh',
        'heightValue' => 500
    ];
}

/**
 * קבלת ID החנות הנוכחית
 */
function getCurrentStoreId() {
    // Logic to get current store ID based on domain/subdomain
    // For now, return store ID 1 (will be implemented properly)
    return 1;
}
?> 