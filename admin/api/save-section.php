<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../includes/HybridPageManager.php';

// בדיקת הרשאות
if (!isLoggedIn() || !hasPermission('manage_themes')) {
    http_response_code(403);
    echo json_encode(['error' => 'אין הרשאה']);
    exit;
}

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['action'])) {
        throw new Exception('פעולה לא צוינה');
    }
    
    $db = Database::getInstance()->getConnection();
    $store = getCurrentStore();
    
    if (!$store) {
        throw new Exception('חנות לא נמצאה');
    }
    
    $hybridManager = new HybridPageManager($db, $store['id']);
    
    switch ($input['action']) {
        case 'add_section':
            $result = addSection($hybridManager, $input['section']);
            break;
            
        case 'update_section':
            $result = updateSection($hybridManager, $input['section']);
            break;
            
        case 'delete_section':
            $result = deleteSection($hybridManager, $input['section_id']);
            break;
            
        case 'reorder_sections':
            $result = reorderSections($hybridManager, $input['sections']);
            break;
            
        default:
            throw new Exception('פעולה לא תקינה');
    }
    
    echo json_encode(['success' => true, 'data' => $result]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * הוספת סקשן חדש
 */
function addSection($hybridManager, $sectionData) {
    // בגישה ההיברידית, הוספת סקשן אומרת שמירת הגדרות מותאמות אישית
    $hybridManager->saveCustomSection(
        'home',
        $sectionData['id'],
        $sectionData['type'],
        $sectionData['settings'] ?? []
    );
    
    return [
        'section_id' => $sectionData['id'],
        'message' => 'הסקשן נוסף בהצלחה'
    ];
}

/**
 * עדכון סקשן קיים
 */
function updateSection($hybridManager, $sectionData) {
    // בגישה ההיברידית, עדכון סקשן אומרת שמירת הגדרות מותאמות אישית
    $hybridManager->saveCustomSection(
        'home',
        $sectionData['id'],
        $sectionData['type'],
        $sectionData['settings'] ?? []
    );
    
    return [
        'section_id' => $sectionData['id'],
        'message' => 'הסקשן עודכן בהצלחה'
    ];
}

/**
 * מחיקת סקשן
 */
function deleteSection($hybridManager, $sectionId) {
    // בגישה ההיברידית, מחיקת סקשן אומרת מחיקת הגדרות מותאמות אישית
    // הסקשן עדיין יופיע עם הגדרות ברירת מחדל
    $hybridManager->saveCustomSection('home', $sectionId, '', []);
    
    return [
        'section_id' => $sectionId,
        'message' => 'הסקשן נמחק בהצלחה'
    ];
}

/**
 * סידור מחדש של סקשנים
 */
function reorderSections($hybridManager, $sections) {
    // בגישה ההיברידית, סידור מחדש אומר עדכון מבנה העמוד
    $pageStructure = [];
    
    foreach ($sections as $section) {
        if (is_array($section) && isset($section['type'])) {
            $pageStructure[] = $section['type'];
        } else {
            // אם זה רק ID, נצטרך לנחש את הסוג
            $sectionId = is_array($section) ? $section['id'] : $section;
            if (strpos($sectionId, 'hero') !== false) {
                $pageStructure[] = 'hero';
            } elseif (strpos($sectionId, 'featured') !== false) {
                $pageStructure[] = 'featured-products';
            } elseif ($sectionId === 'header') {
                $pageStructure[] = 'header';
            } elseif ($sectionId === 'footer') {
                $pageStructure[] = 'footer';
            }
        }
    }
    
    $hybridManager->savePageStructure('home', $pageStructure);
    
    return [
        'message' => 'סדר הסקשנים עודכן בהצלחה'
    ];
}
?> 