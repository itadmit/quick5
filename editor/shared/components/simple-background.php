<?php
/**
 * Simple Background Component - ללא מחשב/מובייל נפרדים
 * מבנה פשוט עם שדות נוספים למובייל כאופציה
 */

/**
 * יצירת קומפוננט רקע פשוט
 */
function renderSimpleBackground($opts = []) {
    $defaults = [
        'basePath' => 'styles',
        'showVideo' => true,
        'showMobileFields' => true,
        'label' => 'הגדרות רקע'
    ];
    
    $opts = array_merge($defaults, $opts);
    $basePath = $opts['basePath'];
    
    $html = '';
    
    // כותרת
    $html .= '<div class="mb-4">';
    $html .= '<h3 class="text-sm font-medium text-gray-900 mb-4">' . $opts['label'] . '</h3>';
    
    // בחירת סוג רקע
    $html .= '<div class="mb-4">';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">סוג רקע</label>';
    $html .= '<div class="grid grid-cols-4 gap-2">';
    
    $bgTypes = [
        'color' => ['icon' => 'ri-paint-fill', 'text' => 'צבע'],
        'image' => ['icon' => 'ri-image-fill', 'text' => 'תמונה'], 
        'gradient' => ['icon' => 'ri-contrast-2-fill', 'text' => 'גרדיאנט'],
        'video' => ['icon' => 'ri-video-fill', 'text' => 'וידאו']
    ];
    
    foreach ($bgTypes as $type => $info) {
        if ($type === 'video' && !$opts['showVideo']) continue;
        
        $html .= '<button type="button" class="simple-bg-type-btn border border-gray-300 rounded-lg p-2 text-center hover:border-purple-500 hover:bg-purple-50 transition-all cursor-pointer"';
        $html .= ' data-type="' . $type . '" data-path="' . $basePath . '.background-type">';
        $html .= '<i class="' . $info['icon'] . ' text-lg block  text-gray-600"></i>';
        $html .= '<span class="text-xs text-gray-700 flex justify-center leading-tight">' . $info['text'] . '</span>';
        $html .= '</button>';
    }
    
    $html .= '</div>';
    $html .= '</div>';
    
    // הגדרות דינמיות לכל סוג רקע
    $html .= '<div class="simple-bg-settings-area">';
    
    // הגדרות צבע
    $html .= '<div class="simple-bg-setting" data-type="color" style="display: none;">';
    $html .= '<div class="mb-3">';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">צבע רקע</label>';
    $html .= '<input type="color" class="color-input w-full h-10 border border-gray-300 rounded cursor-pointer"';
    $html .= ' data-path="' . $basePath . '.background-color" value="#000000">';
    $html .= '</div>';
    $html .= '</div>';
    
    // הגדרות תמונה
    $html .= '<div class="simple-bg-setting" data-type="image" style="display: none;">';
    
    // תמונה עיקרית
    $html .= '<div class="mb-3">';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">תמונת רקע</label>';
    $html .= '<div class="flex gap-2">';
            $html .= '<input type="url" class="flex-1 border border-gray-300 rounded px-3 py-2 text-xs" placeholder="https://example.com/image.jpg"';
    $html .= ' data-path="' . $basePath . '.background-image">';
    $html .= '<button type="button" class="clear-btn bg-gray-500 text-white px-3 py-2 rounded text-xs hover:bg-gray-600" data-target="input[data-path=\'' . $basePath . '.background-image\']">נקה</button>';
    $html .= '</div>';
    $html .= '</div>';
    
    // תמונה למובייל (אופציונאלי)
    if ($opts['showMobileFields']) {
        $html .= '<div class="mb-3">';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">תמונה למובייל <span class="text-gray-500">(אופציונאלי)</span></label>';
        $html .= '<div class="flex gap-2">';
        $html .= '<input type="url" class="flex-1 border border-gray-300 rounded px-3 py-2 text-xs" placeholder="https://example.com/mobile-image.jpg"';
        $html .= ' data-path="' . $basePath . '.background-image-mobile">';
        $html .= '<button type="button" class="clear-btn bg-gray-500 text-white px-3 py-2 rounded text-xs hover:bg-gray-600" data-target="input[data-path=\'' . $basePath . '.background-image-mobile\']">נקה</button>';
        $html .= '</div>';
        $html .= '<div class="text-xs text-gray-500 mt-1">אם ריק, יוצג התמונה הראשית גם במובייל</div>';
        $html .= '</div>';
    }
    
    // הגדרות תמונה
    $html .= '<div class="grid grid-cols-2 gap-3 mb-3">';
    $html .= '<div>';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">גודל תמונה</label>';
            $html .= '<select class="w-full border border-gray-300 rounded px-3 py-2 text-xs" data-path="' . $basePath . '.background-size">';
    $html .= '<option value="cover">כיסוי מלא (Cover)</option>';
    $html .= '<option value="contain">התאמה מלאה (Contain)</option>';
    $html .= '<option value="auto">גודל מקורי (Auto)</option>';
    $html .= '</select>';
    $html .= '</div>';
    $html .= '<div>';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">חזרתיות</label>';
            $html .= '<select class="w-full border border-gray-300 rounded px-3 py-2 text-xs" data-path="' . $basePath . '.background-repeat">';
    $html .= '<option value="no-repeat">ללא חזרה</option>';
    $html .= '<option value="repeat">חזרה מלאה</option>';
    $html .= '<option value="repeat-x">חזרה אופקית</option>';
    $html .= '<option value="repeat-y">חזרה אנכית</option>';
    $html .= '</select>';
    $html .= '</div>';
    $html .= '</div>';
    
    // שכבת חשכה לתמונה
    $html .= '<div class="mb-3">';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">שכבת חשכה <span class="overlay-value">0%</span></label>';
    $html .= '<input type="range" min="0" max="80" value="0" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"';
    $html .= ' data-path="' . $basePath . '.image-overlay-opacity">';
    $html .= '</div>';
    
    $html .= '</div>';
    
    // הגדרות גרדיאנט
    $html .= '<div class="simple-bg-setting" data-type="gradient" style="display: none;">';
    
    // צבע פולבק לגרדיאנט
    $html .= '<div class="mb-3">';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">צבע פולבק <span class="text-gray-500">(למקרה שהגרדיאנט לא נתמך)</span></label>';
    $html .= '<input type="color" class="color-input w-full h-10 border border-gray-300 rounded cursor-pointer"';
    $html .= ' data-path="' . $basePath . '.background-color" value="#000000">';
    $html .= '</div>';
    
    $html .= '<div class="grid grid-cols-2 gap-3 mb-3">';
    $html .= '<div>';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">צבע ראשון</label>';
    $html .= '<input type="color" class="gradient-color w-full h-10 border border-gray-300 rounded cursor-pointer"';
    $html .= ' data-path="' . $basePath . '.gradient-color1" value="#000000">';
    $html .= '</div>';
    $html .= '<div>';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">צבע שני</label>';
    $html .= '<input type="color" class="gradient-color w-full h-10 border border-gray-300 rounded cursor-pointer"';
    $html .= ' data-path="' . $basePath . '.gradient-color2" value="#ffffff">';
    $html .= '</div>';
    $html .= '</div>';
    
    $html .= '<div class="mb-3">';
    $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">כיוון גרדיאנט</label>';
            $html .= '<select class="w-full border border-gray-300 rounded px-3 py-2 text-xs" data-path="' . $basePath . '.gradient-direction">';
    $html .= '<option value="to bottom">מלמעלה למטה</option>';
    $html .= '<option value="to top">מלמטה למעלה</option>';
    $html .= '<option value="to right">משמאל לימין</option>';
    $html .= '<option value="to left">מימין לשמאל</option>';
    $html .= '<option value="45deg">אלכסוני (45°)</option>';
    $html .= '<option value="135deg">אלכסוני הפוך (135°)</option>';
    $html .= '</select>';
    $html .= '</div>';
    
    $html .= '</div>';
    
    // הגדרות וידאו
    if ($opts['showVideo']) {
        $html .= '<div class="simple-bg-setting" data-type="video" style="display: none;">';
        
        // וידאו עיקרי
        $html .= '<div class="mb-3">';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">וידאו רקע (MP4)</label>';
        $html .= '<div class="flex gap-2">';
        $html .= '<input type="url" class="flex-1 border border-gray-300 rounded px-3 py-2 text-xs" placeholder="https://example.com/video.mp4"';
        $html .= ' data-path="' . $basePath . '.background-video">';
        $html .= '<button type="button" class="clear-btn bg-gray-500 text-white px-3 py-2 rounded text-xs hover:bg-gray-600" data-target="input[data-path=\'' . $basePath . '.background-video\']">נקה</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        // וידאו למובייל (אופציונאלי)
        if ($opts['showMobileFields']) {
            $html .= '<div class="mb-3">';
            $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">וידאו למובייל <span class="text-gray-500">(אופציונאלי)</span></label>';
            $html .= '<div class="flex gap-2">';
            $html .= '<input type="url" class="flex-1 border border-gray-300 rounded px-3 py-2 text-xs" placeholder="https://example.com/mobile-video.mp4"';
            $html .= ' data-path="' . $basePath . '.background-video-mobile">';
            $html .= '<button type="button" class="clear-btn bg-gray-500 text-white px-3 py-2 rounded text-xs hover:bg-gray-600" data-target="input[data-path=\'' . $basePath . '.background-video-mobile\']">נקה</button>';
            $html .= '</div>';
            $html .= '<div class="text-xs text-gray-500 mt-1">אם ריק, יוצג הוידאו הראשי גם במובייל</div>';
            $html .= '</div>';
        }
        
        // שכבת חשכה לוידאו
        $html .= '<div class="mb-3">';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">שכבת חשכה <span class="overlay-value">0%</span></label>';
        $html .= '<input type="range" min="0" max="80" value="0" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"';
        $html .= ' data-path="' . $basePath . '.video-overlay-opacity">';
        $html .= '</div>';
        
        // הגדרות וידאו
        $html .= '<div class="grid grid-cols-2 gap-3 mb-3">';
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">השתקה</label>';
        $html .= '<label class="flex items-center">';
        $html .= '<input type="checkbox" class="mr-2" checked data-path="' . $basePath . '.video-muted">';
        $html .= '<span class="text-sm text-gray-700">השתק וידאו</span>';
        $html .= '</label>';
        $html .= '</div>';
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">לולאה</label>';
        $html .= '<label class="flex items-center">';
        $html .= '<input type="checkbox" class="mr-2" checked data-path="' . $basePath . '.video-loop">';
        $html .= '<span class="text-sm text-gray-700">נגן בלולאה</span>';
        $html .= '</label>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>';
    }
    
    $html .= '</div>'; // סגירת simple-bg-settings-area
    $html .= '</div>'; // סגירת הcontainer הראשי
    
    return $html;
} 