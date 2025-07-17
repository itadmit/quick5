<?php
require_once '../includes/auth.php';
require_once '../includes/ThemeManager.php';

// בדיקת הרשאות
if (!isLoggedIn() || !hasPermission('manage_themes')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'אין הרשאה']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'שיטה לא מורשית']);
    exit;
}

try {
    $store = getCurrentStore();
    $themeManager = new ThemeManager();
    
    // קבלת הנתונים מהבקשה
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('נתונים לא תקינים');
    }
    
    // שמירת הגדרות עיצוב ותוכן בלבד
    $designSettings = [
        'colors' => $input['colors'] ?? [],
        'fonts' => $input['fonts'] ?? [],
        'layout' => $input['layout'] ?? [],
        'hero' => $input['hero'] ?? [],
        'menu' => $input['menu'] ?? [],
        'footer' => $input['footer'] ?? []
    ];
    
    $themeManager->saveThemeDesignSettings($store['id'], $designSettings);
    
    echo json_encode([
        'success' => true,
        'message' => 'הגדרות העיצוב נשמרו בהצלחה'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'שגיאה בשמירת ההגדרות: ' . $e->getMessage()
    ]);
}
?> 