<?php
/**
 * Responsive Height Component - גובה responsive עם מחשב/מובייל
 * מאפשר שליטה נפרדת על גובה הסקשן במחשב ובמובייל
 */

function renderResponsiveHeight($options = []) {
    // הגדרות ברירת מחדל
    $defaults = [
        'title' => 'גובה הסקשן',
        'basePath' => 'styles.height'
    ];
    
    $opts = array_merge($defaults, $options);
    
    // יצירת ID ייחודי לקומפוננט
    $uid = 'height_' . uniqid();
    
    $html = '';
    
    // התחלת הקומפוננט - ללא גבול כי זה בתוך מידות וגדלים
    $html .= '<div class="settings-group">';
    
    // כותרת עם אייקון
    $html .= '<h3 class="text-sm font-medium text-gray-900 mb-4 flex items-center gap-2">';
    $html .= '<i class="ri-fullscreen-line text-indigo-600"></i>';
    $html .= htmlspecialchars($opts['title']);
    $html .= '</h3>';
    
    // כותרת משנה עם device switcher
    $html .= '<div class="mb-4">';
    $html .= '<h4 class="text-xs font-medium text-gray-700 mb-3 flex items-center justify-between">';
    $html .= '<span>הגדרות גובה לפי מכשיר</span>';
    $html .= '</h4>';
    
    // Device Mode Switcher
    $html .= '<div class="device-switcher flex bg-gray-100 rounded-lg p-1" data-component-id="' . $uid . '">';
    $html .= '<button type="button" class="device-btn flex-1 px-3 py-2 text-xs font-medium rounded-md transition-colors bg-white shadow-sm" data-device="desktop">';
    $html .= '<i class="ri-computer-line mr-1"></i>מחשב';
    $html .= '</button>';
    $html .= '<button type="button" class="device-btn flex-1 px-3 py-2 text-xs font-medium rounded-md transition-colors hover:bg-white" data-device="mobile">';
    $html .= '<i class="ri-smartphone-line mr-1"></i>מובייל';
    $html .= '</button>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Settings Area - Desktop
    $html .= '<div class="device-settings" data-device="desktop">';
    $html .= renderHeightSettings($opts, 'desktop');
    $html .= '</div>';
    
    // Settings Area - Mobile  
    $html .= '<div class="device-settings" data-device="mobile" style="display: none;">';
    $html .= '<div class="mb-3 p-2 bg-blue-50 rounded text-xs text-blue-700">';
    $html .= '<i class="ri-information-line mr-1"></i>';
    $html .= 'הגדרות מובייל. אם לא נבחר ערך, יורש מהגדרות מחשב';
    $html .= '</div>';
    $html .= renderHeightSettings($opts, 'mobile');
    $html .= '</div>';
    
    $html .= '</div>';
    
    return $html;
}

/**
 * יצירת הגדרות גובה לmobile/desktop
 */
function renderHeightSettings($opts, $device) {
    $basePath = $opts['basePath'] . '.' . $device;
    
    $html = '';
    
    // בחירת גובה - אינפוט + יחידות
    $html .= '<div>';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">גובה הסקשן</label>';
    
    // Container עבור האינפוט והדרופ-דאון
    $html .= '<div class="flex gap-2">';
    
    // שדה מספר - רוחב מוגבל
    $html .= '<input type="number" ';
    $html .= 'class="flex-1 border border-gray-300 rounded px-3 py-2 text-xs" ';
    $html .= 'placeholder="100" ';
    $html .= 'min="1" ';
    $html .= 'max="9999" ';
    $html .= 'data-path="' . $basePath . '.value" ';
    $html .= 'data-responsive="true">';
    
    // דרופ-דאון יחידות - רוחב קבוע
    $html .= '<select class="w-16 border border-gray-300 rounded px-2 py-2 text-xs bg-white" ';
    $html .= 'data-path="' . $basePath . '.unit" ';
    $html .= 'data-responsive="true">';
    $html .= '<option value="vh">vh</option>';
    $html .= '<option value="px">px</option>';
    $html .= '<option value="auto">auto</option>';
    $html .= '</select>';
    
    $html .= '</div>';
    
    // הערה קטנה על השימוש
    $html .= '<div class="mt-1 text-xs text-gray-500">';
    $html .= 'vh = אחוז מגובה המסך, px = פיקסלים, auto = לפי תוכן';
    $html .= '</div>';
    
    $html .= '</div>';
    
    return $html;
}

// שימוש ישיר
if (isset($_GET['render'])) {
    echo renderResponsiveHeight($_GET);
}
?> 