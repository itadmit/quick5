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
    
    // שמירה אוטומטית של הגדרות עיצוב ותוכן בלבד
    $designSettings = [
        'colors' => $input['colors'] ?? [],
        'fonts' => $input['fonts'] ?? [],
        'layout' => $input['layout'] ?? [],
        'hero' => $input['hero'] ?? [],
        'menu' => $input['menu'] ?? [],
        'footer' => $input['footer'] ?? []
    ];
    
    $themeManager->autoSaveThemeDesignSettings($store['id'], $designSettings);
    
    echo json_encode([
        'success' => true,
        'message' => 'שמירה אוטומטית הושלמה'
    ]);
    
} catch (Exception $e) {
    // שקט בשמירה אוטומטית - לא להציג שגיאות למשתמש
    echo json_encode([
        'success' => false,
        'message' => 'שגיאה בשמירה אוטומטית'
    ]);
}
?> 