<?php
/**
 * Buttons Repeater Component - רכיב ריפיטר כפתורים
 * קורא כפתורים קיימים ומאפשר עריכה מלאה
 */

function renderButtonsRepeater($options = []) {
    $defaults = [
        'title' => 'כפתורי פעולה',
        'basePath' => 'content.buttons',
        'uniqueId' => 'buttons_' . uniqid(),
        'maxButtons' => 5
    ];
    
    $opts = array_merge($defaults, $options);
    $uid = $opts['uniqueId'];
    
    $html = '<div class="buttons-repeater-component border border-gray-200 rounded-lg p-4 mb-4" data-component="' . $uid . '" data-path="' . $opts['basePath'] . '">';
    
    // כותרת עם מידע
    $html .= '<div class="flex items-center justify-between mb-4">';
    $html .= '<h3 class="font-medium text-sm text-gray-900 flex items-center gap-2">';
    $html .= '<i class="ri-cursor-line text-green-600"></i>';
    $html .= htmlspecialchars($opts['title']);
    $html .= '</h3>';
    $html .= '<div class="text-xs text-gray-500">';
    $html .= 'מקסימום ' . $opts['maxButtons'] . ' כפתורים';
    $html .= '</div>';
    $html .= '</div>';
    
    // רשימת כפתורים קיימים
    $html .= '<div class="buttons-list space-y-3" id="buttons-list-' . $uid . '">';
    $html .= '<!-- כפתורים יטענו כאן דינמית -->';
    $html .= '</div>';
    
    // כפתור הוספה
    $html .= '<button type="button" class="add-button-btn mt-3 w-full border-2 border-dashed border-gray-300 rounded-lg p-3 text-gray-500 hover:border-green-500 hover:text-green-600 transition-colors">';
    $html .= '<i class="ri-add-line mr-2"></i>';
    $html .= 'הוסף כפתור';
    $html .= '</button>';
    
    // Template לכפתור חדש (נסתר)
    $html .= '<template id="button-template-' . $uid . '">';
    $html .= renderButtonTemplate($opts);
    $html .= '</template>';
    
    $html .= '</div>';
    
    return $html;
}

/**
 * יצירת template לכפתור
 */
function renderButtonTemplate($opts) {
    $html = '<div class="button-item border border-gray-200 rounded-lg p-3 mb-3" data-button-index="{{INDEX}}">';
    
    // כותרת כפתור עם פעולות
    $html .= '<div class="flex items-center justify-between mb-3">';
    $html .= '<div class="flex items-center gap-2">';
    $html .= '<i class="ri-cursor-line text-green-600"></i>';
    $html .= '<span class="font-medium text-sm text-gray-900">כפתור {{INDEX}}</span>';
    $html .= '</div>';
    $html .= '<div class="flex gap-1">';
    $html .= '<button type="button" class="move-up-btn p-1 text-gray-400 hover:text-blue-600 transition-colors" title="העבר למעלה">';
    $html .= '<i class="ri-arrow-up-line text-sm"></i>';
    $html .= '</button>';
    $html .= '<button type="button" class="move-down-btn p-1 text-gray-400 hover:text-blue-600 transition-colors" title="העבר למטה">';
    $html .= '<i class="ri-arrow-down-line text-sm"></i>';
    $html .= '</button>';
    $html .= '<button type="button" class="remove-button-btn p-1 text-gray-400 hover:text-red-600 transition-colors" title="מחק כפתור">';
    $html .= '<i class="ri-delete-bin-line text-sm"></i>';
    $html .= '</button>';
    $html .= '</div>';
    $html .= '</div>';
    
    // שדות כפתור
    $html .= '<div class="grid grid-cols-2 gap-3 mb-3">';
    
    // טקסט כפתור
    $html .= '<div>';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">טקסט כפתור</label>';
    $html .= '<input type="text" class="w-full border border-gray-300 rounded px-3 py-2 text-xs button-text-input" placeholder="לחץ כאן" data-field="text">';
    $html .= '</div>';
    
    // קישור
    $html .= '<div>';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">קישור</label>';
    $html .= '<input type="url" class="w-full border border-gray-300 rounded px-3 py-2 text-xs button-url-input" placeholder="https://example.com" data-field="url">';
    $html .= '</div>';
    
    $html .= '</div>';
    
    // אייקון וסוג כפתור
    $html .= '<div class="grid grid-cols-2 gap-3 mb-3">';
    
    // אייקון
    $html .= '<div>';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">אייקון</label>';
    $html .= '<button type="button" class="icon-picker-btn w-full border border-gray-300 rounded px-3 py-2 text-xs text-right bg-white hover:bg-gray-50 transition-colors flex items-center justify-between" data-field="icon">';
    $html .= '<span class="selected-icon-display text-gray-500">בחר אייקון</span>';
    $html .= '<i class="ri-star-line text-blue-600"></i>';
    $html .= '</button>';
    $html .= '<input type="hidden" class="button-icon-input" data-field="icon">';
    $html .= '</div>';
    
    // סוג כפתור
    $html .= '<div>';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">סוג כפתור</label>';
            $html .= '<select class="w-full border border-gray-300 rounded px-3 py-2 text-xs button-type-select" data-field="style">';
    $html .= '<option value="solid" selected>צבע מלא</option>';
    $html .= '<option value="outline">מתאר</option>';
    $html .= '<option value="black">שחור</option>';
    $html .= '<option value="white">לבן</option>';
    $html .= '<option value="underline">קו בלבד</option>';
    $html .= '</select>';
    $html .= '</div>';
    
    $html .= '</div>';
    
    // עיצוב כפתור
    $html .= '<div class="grid grid-cols-3 gap-3">';
    
    // צבע רקע
    $html .= '<div>';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">צבע רקע</label>';
    $html .= '<input type="color" class="w-full h-8 border border-gray-300 rounded cursor-pointer button-bg-color" value="#3b82f6" data-field="styles.background-color">';
    $html .= '</div>';
    
    // צבע טקסט
    $html .= '<div>';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">צבע טקסט</label>';
    $html .= '<input type="color" class="w-full h-8 border border-gray-300 rounded cursor-pointer button-text-color" value="#ffffff" data-field="styles.color">';
    $html .= '</div>';
    
    // עגול פינות
    $html .= '<div>';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">עגול פינות</label>';
            $html .= '<select class="w-full border border-gray-300 rounded px-3 py-2 text-xs button-border-radius" data-field="styles.border-radius">';
    $html .= '<option value="4px">מעט (4px)</option>';
    $html .= '<option value="6px" selected>רגיל (6px)</option>';
    $html .= '<option value="8px">הרבה (8px)</option>';
    $html .= '<option value="12px">מאוד עגול (12px)</option>';
    $html .= '<option value="20px">כמעט עיגול (20px)</option>';
    $html .= '</select>';
    $html .= '</div>';
    
    $html .= '</div>';
    
    $html .= '</div>';
    
    // מודל בחירת אייקונים
    $html .= '<div id="iconPickerModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">';
    $html .= '<div class="flex items-center justify-center min-h-screen p-4">';
    $html .= '<div class="bg-white rounded-lg max-w-2xl w-full max-h-96 overflow-hidden">';
    
    // כותרת המודל
    $html .= '<div class="flex items-center justify-between p-4 border-b">';
    $html .= '<h3 class="text-lg font-semibold">בחר אייקון</h3>';
    $html .= '<button type="button" class="close-modal text-gray-400 hover:text-gray-600">';
    $html .= '<i class="ri-close-line text-xl"></i>';
    $html .= '</button>';
    $html .= '</div>';
    
    // תוכן המודל
    $html .= '<div class="p-4 overflow-y-auto max-h-80">';
    
    // אפשרות "אין אייקון"
    $html .= '<div class="grid grid-cols-8 gap-3 mb-4">';
    $html .= '<button type="button" class="icon-option p-3 border border-gray-300 rounded hover:bg-blue-50 hover:border-blue-300 transition-colors text-center" data-icon="">';
    $html .= '<div class="text-2xl text-gray-400 mb-1">×</div>';
    $html .= '<div class="text-xs text-gray-600">אין אייקון</div>';
    $html .= '</button>';
    
    // אייקונים פופולריים
    $popularIcons = [
        'arrow-right-line' => 'חץ ימין',
        'arrow-left-line' => 'חץ שמאל', 
        'arrow-up-line' => 'חץ למעלה',
        'arrow-down-line' => 'חץ למטה',
        'home-line' => 'בית',
        'user-line' => 'משתמש',
        'mail-line' => 'מייל',
        'phone-line' => 'טלפון',
        'shopping-cart-line' => 'עגלת קניות',
        'heart-line' => 'לב',
        'star-line' => 'כוכב',
        'download-line' => 'הורדה',
        'upload-line' => 'העלאה',
        'share-line' => 'שיתוף',
        'search-line' => 'חיפוש',
        'settings-line' => 'הגדרות',
        'edit-line' => 'עריכה',
        'delete-line' => 'מחיקה',
        'add-line' => 'הוספה',
        'check-line' => 'אישור',
        'close-line' => 'סגירה',
        'menu-line' => 'תפריט',
        'eye-line' => 'עין',
        'lock-line' => 'נעילה',
        'unlock-line' => 'פתיחה',
        'calendar-line' => 'לוח שנה',
        'time-line' => 'שעון',
        'map-pin-line' => 'מיקום',
        'camera-line' => 'מצלמה',
        'image-line' => 'תמונה',
        'video-line' => 'וידאו',
        'music-line' => 'מוזיקה',
        'file-line' => 'קובץ',
        'folder-line' => 'תיקייה',
        'external-link-line' => 'קישור חיצוני',
        'information-line' => 'מידע',
        'error-warning-line' => 'אזהרה',
        'question-line' => 'שאלה',
        'thumb-up-line' => 'לייק',
        'thumb-down-line' => 'דיסלייק',
        'facebook-line' => 'פייסבוק',
        'instagram-line' => 'אינסטגרם',
        'twitter-line' => 'טוויטר',
        'linkedin-line' => 'לינקדאין',
        'whatsapp-line' => 'ווטסאפ',
        'telegram-line' => 'טלגרם',
        'youtube-line' => 'יוטיוב'
    ];
    
    foreach ($popularIcons as $icon => $name) {
        $html .= '<button type="button" class="icon-option p-3 border border-gray-300 rounded hover:bg-blue-50 hover:border-blue-300 transition-colors text-center" data-icon="' . $icon . '">';
        $html .= '<i class="ri-' . $icon . ' text-2xl text-gray-600 block mb-1"></i>';
        $html .= '<div class="text-xs text-gray-600">' . $name . '</div>';
        $html .= '</button>';
    }
    
    $html .= '</div>'; // סגירת grid
    $html .= '</div>'; // סגירת תוכן
    $html .= '</div>'; // סגירת מודל פנימי
    $html .= '</div>'; // סגירת overlay
    $html .= '</div>'; // סגירת מודל
    
    return $html;
}

// שימוש ישיר
if (isset($_GET['render'])) {
    echo renderButtonsRepeater($_GET);
}
?> 