<?php
/**
 * Responsive Background Component - רקע עם מצבי מחשב/מובייל  
 */

function renderResponsiveBackground($options = []) {
    $defaults = [
        'title' => 'רקע',
        'basePath' => 'styles',
        'showColor' => true,
        'showImage' => true,
        'showGradient' => true,
        'showVideo' => true,
        'uniqueId' => 'bg_' . uniqid()
    ];
    
    $opts = array_merge($defaults, $options);
    $uid = $opts['uniqueId'];
    
    $html = '<div class="responsive-background-component border border-gray-200 rounded-lg p-4 mb-4" data-component="' . $uid . '" data-base-path="' . $opts['basePath'] . '">';
    
    // Header עם כותרת ומצב switcher
    $html .= '<div class="flex items-center justify-between mb-4">';
    
    // כותרת
    $html .= '<h4 class="font-medium text-sm text-gray-900 flex items-center gap-2">';
    $html .= '<i class="ri-palette-line text-purple-600"></i>';
    $html .= htmlspecialchars($opts['title']);
    $html .= '</h4>';
    
    // Device Mode Switcher
    $html .= '<div class="device-switcher flex bg-gray-100 rounded-lg p-1">';
    $html .= '<button type="button" class="device-btn active px-3 py-1 text-xs rounded transition-all bg-white shadow-sm text-gray-900" data-device="desktop">';
    $html .= '<i class="ri-computer-line mr-1"></i>מחשב';
    $html .= '</button>';
    $html .= '<button type="button" class="device-btn px-3 py-1 text-xs rounded transition-all hover:bg-white text-gray-700" data-device="mobile">';
    $html .= '<i class="ri-smartphone-line mr-1"></i>מובייל';
    $html .= '</button>';
    $html .= '</div>';
    
    $html .= '</div>';
    
    // Settings Area - Desktop
    $html .= '<div class="device-settings" data-device="desktop">';
    $html .= renderBackgroundSettings($opts, 'desktop');
    $html .= '</div>';
    
    // Settings Area - Mobile  
    $html .= '<div class="device-settings" data-device="mobile" style="display: none;">';
    $html .= '<div class="mb-3 p-2 bg-blue-50 rounded text-xs text-blue-700">';
    $html .= '<i class="ri-information-line mr-1"></i>';
    $html .= 'הגדרות מובייל. אם ריק, יורש מהגדרות מחשב';
    $html .= '</div>';
    $html .= renderBackgroundSettings($opts, 'mobile');
    $html .= '</div>';
    
    $html .= '</div>';
    
    return $html;
}

/**
 * יצירת הגדרות רקע לmobile/desktop
 */
function renderBackgroundSettings($opts, $device) {
    $basePath = $opts['basePath'] . '.' . $device;
    
    $html = '';
    
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
        
        $html .= '<button type="button" class="bg-type-btn border border-gray-300 rounded-lg p-3 text-center hover:border-purple-500 hover:bg-purple-50 transition-all cursor-pointer"';
        $html .= ' data-type="' . $type . '" data-path="' . $basePath . '.background-type">';
        $html .= '<i class="' . $info['icon'] . ' text-lg block mb-1 text-gray-600"></i>';
        $html .= '<span class="text-xs text-gray-700">' . $info['text'] . '</span>';
        $html .= '</button>';
    }
    
    $html .= '</div>';
    $html .= '</div>';
    
    // אזור הגדרות דינמי
    $html .= '<div class="bg-settings-area">';
    
    // הגדרות צבע
    if ($opts['showColor']) {
        $html .= '<div class="bg-setting" data-type="color" style="display: none;">';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">צבע רקע</label>';
        $html .= '<div class="flex items-center gap-3">';
        
        $html .= '<input type="color" class="w-16 h-10 border border-gray-300 rounded-lg cursor-pointer"';
        $html .= ' data-path="' . $basePath . '.background-color" data-responsive="true" value="#3b82f6">';
        
        $html .= '<button type="button" class="clear-btn text-xs bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg transition-colors" data-target="[data-path=\'' . $basePath . '.background-color\']">';
        $html .= '<i class="ri-close-line mr-1"></i>נקה';
        $html .= '</button>';
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    // הגדרות תמונה
    if ($opts['showImage']) {
        $html .= '<div class="bg-setting" data-type="image" style="display: none;">';
        $html .= '<div class="space-y-4">';
        
        // URL תמונה
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">קישור לתמונה</label>';
        $html .= '<div class="flex gap-2">';
        $html .= '<input type="url" class="settings-input text-sm flex-1 border border-gray-300 rounded-lg px-3 py-2" placeholder="https://example.com/image.jpg"';
        $html .= ' data-path="' . $basePath . '.background-image" data-responsive="true">';
        $html .= '<button type="button" class="upload-btn text-xs bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">';
        $html .= '<i class="ri-upload-line mr-1"></i>העלאה';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        // תצוגה מקדימה
        $html .= '<div class="image-preview hidden bg-gray-50 rounded-lg p-3">';
        $html .= '<img class="w-full h-32 object-cover rounded-lg border" alt="תצוגה מקדימה">';
        $html .= '</div>';
        
        // הגדרות תמונה מתקדמות
        $html .= '<div class="grid grid-cols-2 gap-4">';
        
        // גודל
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">גודל תמונה</label>';
        $html .= '<select class="settings-input text-sm w-full border border-gray-300 rounded-lg px-3 py-2" data-path="' . $basePath . '.background-size" data-responsive="true">';
        $html .= '<option value="cover">כיסוי מלא (Cover)</option>';
        $html .= '<option value="contain">התאמה מלאה (Contain)</option>';
        $html .= '<option value="auto">גודל מקורי</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        // חזרה
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">חזרתיות</label>';
        $html .= '<select class="settings-input text-sm w-full border border-gray-300 rounded-lg px-3 py-2" data-path="' . $basePath . '.background-repeat" data-responsive="true">';
        $html .= '<option value="no-repeat">ללא חזרה</option>';
        $html .= '<option value="repeat">חזרה מלאה</option>';
        $html .= '<option value="repeat-x">חזרה אופקית</option>';
        $html .= '<option value="repeat-y">חזרה אנכית</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        // שכבת חשכה
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">שכבת חשכה</label>';
        $html .= '<div class="flex items-center gap-3">';
        $html .= '<input type="range" min="0" max="80" step="5" value="0" class="flex-1 range-slider"';
        $html .= ' data-path="' . $basePath . '.image-overlay-opacity" data-responsive="true">';
        $html .= '<span class="text-xs min-w-[50px] text-center font-medium overlay-value bg-gray-100 px-2 py-1 rounded">0%</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    // הגדרות גרדיאנט
    if ($opts['showGradient']) {
        $html .= '<div class="bg-setting" data-type="gradient" style="display: none;">';
        $html .= '<div class="space-y-4">';
        
        $html .= '<div class="grid grid-cols-2 gap-4">';
        
        // צבע ראשון
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">צבע ראשון</label>';
        $html .= '<input type="color" class="w-full h-12 border border-gray-300 rounded-lg cursor-pointer gradient-color"';
        $html .= ' data-path="' . $basePath . '.gradient-color1" value="#3b82f6" data-responsive="true">';
        $html .= '</div>';
        
        // צבע שני
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">צבע שני</label>';
        $html .= '<input type="color" class="w-full h-12 border border-gray-300 rounded-lg cursor-pointer gradient-color"';
        $html .= ' data-path="' . $basePath . '.gradient-color2" value="#1e40af" data-responsive="true">';
        $html .= '</div>';
        
        $html .= '</div>';
        
        // כיוון
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">כיוון גרדיאנט</label>';
        $html .= '<select class="settings-input text-sm w-full border border-gray-300 rounded-lg px-3 py-2" data-path="' . $basePath . '.gradient-direction" data-responsive="true">';
        $html .= '<option value="to bottom">מלמעלה למטה</option>';
        $html .= '<option value="to top">מלמטה למעלה</option>';
        $html .= '<option value="to right">משמאל לימין</option>';
        $html .= '<option value="to left">מימין לשמאל</option>';
        $html .= '<option value="45deg">אלכסוני (45°)</option>';
        $html .= '<option value="135deg">אלכסוני (135°)</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    // הגדרות וידאו
    if ($opts['showVideo']) {
        $html .= '<div class="bg-setting" data-type="video" style="display: none;">';
        $html .= '<div class="space-y-4">';
        
        // URL וידאו
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">קישור לוידאו (MP4)</label>';
        $html .= '<input type="url" class="settings-input text-sm w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="https://example.com/video.mp4"';
        $html .= ' data-path="' . $basePath . '.background-video" data-responsive="true">';
        $html .= '</div>';
        
        // שכבת חשכה לוידאו
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">שכבת חשכה</label>';
        $html .= '<div class="flex items-center gap-3">';
        $html .= '<input type="range" min="0" max="80" step="5" value="0" class="flex-1 range-slider"';
        $html .= ' data-path="' . $basePath . '.video-overlay-opacity" data-responsive="true">';
        $html .= '<span class="text-xs min-w-[50px] text-center font-medium overlay-value bg-gray-100 px-2 py-1 rounded">0%</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        // אפשרויות וידאו
        $html .= '<div class="grid grid-cols-2 gap-4">';
        
        $html .= '<label class="flex items-center text-sm text-gray-700 cursor-pointer">';
        $html .= '<input type="checkbox" class="mr-2 rounded" data-path="' . $basePath . '.video-muted" data-responsive="true" checked>';
        $html .= '<i class="ri-volume-mute-line mr-1"></i>השתק';
        $html .= '</label>';
        
        $html .= '<label class="flex items-center text-sm text-gray-700 cursor-pointer">';
        $html .= '<input type="checkbox" class="mr-2 rounded" data-path="' . $basePath . '.video-loop" data-responsive="true" checked>';
        $html .= '<i class="ri-repeat-line mr-1"></i>לולאה';
        $html .= '</label>';
        
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

// שימוש ישיר
if (isset($_GET['render'])) {
    echo renderResponsiveBackground($_GET);
}
?> 