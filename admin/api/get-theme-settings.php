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

try {
    $themeManager = new ThemeManager();
    $store = getCurrentStore();
    
    if (!$store) {
        throw new Exception('לא נמצאה חנות');
    }
    
    // קבלת הגדרות התבנית הנוכחית
    $settings = $themeManager->getStoreThemeSettings($store['id']);
    
    // קבלת פרטי התבנית
    $themeDetails = $themeManager->getCurrentThemeDetails($store['id']);
    
    // קבלת תבניות מותאמות
    $customTemplates = $themeManager->getCustomTemplates($store['id']);
    
    $response = [
        'success' => true,
        'settings' => $settings,
        'theme_details' => $themeDetails,
        'custom_templates' => $customTemplates
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 