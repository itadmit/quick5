<?php
require_once '../includes/auth.php';
require_once '../includes/ThemeManager.php';

// בדיקת הרשאות
if (!isLoggedIn() || !hasPermission('manage_themes')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'אין הרשאה']);
    exit;
}

try {
    $store = getCurrentStore();
    $themeManager = new ThemeManager();
    
    // קבלת הגדרות עיצוב ותוכן בלבד (לא הגדרות חנות)
    $settings = $themeManager->getThemeDesignSettings($store['id']);
    
    echo json_encode([
        'success' => true,
        'settings' => $settings
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'שגיאה בטעינת ההגדרות: ' . $e->getMessage()
    ]);
}
?> 