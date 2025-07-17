<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';

// בדיקת הרשאות
if (!isLoggedIn() || !hasPermission('manage_themes')) {
    http_response_code(403);
    echo json_encode(['error' => 'אין הרשאה']);
    exit;
}

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    $store = getCurrentStore();
    
    if (!$store) {
        throw new Exception('חנות לא נמצאה');
    }
    
    // קבלת הסקשנים באמצעות המערכת ההיברידית
    require_once '../../includes/HybridPageManager.php';
    
    $hybridManager = new HybridPageManager($db, $store['id']);
    $sections = $hybridManager->getHomePage();
    
    // המרת הנתונים לפורמט המתאים לקסטומיזר
    $formattedSections = [];
    foreach ($sections as $section) {
        $formattedSections[] = [
            'id' => $section['id'],
            'type' => $section['type'],
            'settings' => $section['settings'],
            'order' => $section['order'],
            'visible' => $section['visible'] ?? $section['is_visible'] ?? true
        ];
    }
    
    $sections = $formattedSections;
    
    echo json_encode([
        'success' => true,
        'sections' => $sections
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?> 