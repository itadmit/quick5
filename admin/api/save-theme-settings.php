<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/ThemeManager.php';

// בדיקת הרשאות
if (!isLoggedIn() || !hasPermission('manage_themes')) {
    http_response_code(403);
    echo json_encode(['error' => 'אין הרשאה']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'שיטה לא מורשית']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('נתונים לא תקינים');
    }
    
    $themeManager = new ThemeManager();
    $store = getCurrentStore();
    
    if (!$store) {
        throw new Exception('לא נמצאה חנות');
    }
    
    // שמירת הגדרות התבנית
    if (isset($input['settings'])) {
        $themeManager->updateStoreThemeSettings($store['id'], $input['settings']);
    }
    
    // שמירת תבנית מותאמת
    if (isset($input['template_type']) && isset($input['template_content'])) {
        $themeManager->saveStoreCustomTemplate(
            $store['id'],
            $input['template_type'],
            $input['template_content']
        );
    }
    
    echo json_encode(['success' => true, 'message' => 'הגדרות נשמרו בהצלחה']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 