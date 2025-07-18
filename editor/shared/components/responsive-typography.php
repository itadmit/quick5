<?php
/**
 * Responsive Typography Component - טיפוגרפיה responsive עם מחשב/מובייל
 * גרסה משופרת עם HTML tag selection וללא כפתורי העתק
 */

function renderResponsiveTypography($options = []) {
    $defaults = [
        'title' => 'טיפוגרפיה',
        'basePath' => 'styles',
        'showSize' => true,
        'showWeight' => true,
        'showColor' => true,
        'showAlign' => true,
        'showLineHeight' => true,
        'showTag' => true, // הוספת בחירת HTML tag
        'hideDeviceSwitcher' => false, // אפשרות להסתיר את הswitcher
        'uniqueId' => 'typo_' . uniqid()
    ];
    
    $opts = array_merge($defaults, $options);
    $uid = $opts['uniqueId'];
    
    $html = '<div class="responsive-typography-component border border-gray-200 rounded-lg p-4 mb-4" data-component="' . $uid . '">';
    
    // כותרת התוכן החדשה
    $html .= '<div class="mb-4">';
  
    
    // כותרת משנה עם device switcher (אם לא מוסתר)
    if (!$opts['hideDeviceSwitcher']) {
        $html .= '<div class="flex items-center justify-between mb-4">';
        
        // כותרת רכיב
        $html .= '<h4 class="font-medium text-sm text-gray-700 flex items-center gap-2">';
        $html .= '<i class="ri-text text-blue-600"></i>';
        $html .= htmlspecialchars($opts['title']);
        $html .= '</h4>';
        
        // Device Mode Switcher
        $html .= '<div class="device-switcher flex bg-gray-100 rounded-lg p-1" data-component-id="' . $uid . '">';
        $html .= '<button type="button" class="device-btn active px-3 py-1 text-xs rounded transition-colors bg-white shadow-sm" data-device="desktop">';
        $html .= '<i class="ri-computer-line mr-1"></i>מחשב';
        $html .= '</button>';
        $html .= '<button type="button" class="device-btn px-3 py-1 text-xs rounded transition-colors hover:bg-white" data-device="mobile">';
        $html .= '<i class="ri-smartphone-line mr-1"></i>מובייל';
        $html .= '</button>';
        $html .= '</div>';
        
        $html .= '</div>';
    } else {
        // רק כותרת בלי switcher
        $html .= '<h4 class="font-medium text-sm text-gray-700 flex items-center gap-2 mb-4">';
        $html .= '<i class="ri-text text-blue-600"></i>';
        $html .= htmlspecialchars($opts['title']);
        $html .= '</h4>';
    }
    $html .= '</div>';
    
    // Settings Area - Desktop
    $html .= '<div class="device-settings" data-device="desktop">';
    $html .= renderTypographySettings($opts, 'desktop');
    $html .= '</div>';
    
    // Settings Area - Mobile  
    $html .= '<div class="device-settings" data-device="mobile" style="display: none;">';
    $html .= '<div class="mb-3 p-2 bg-blue-50 rounded text-xs text-blue-700">';
    $html .= '<i class="ri-information-line mr-1"></i>';
    $html .= 'הגדרות מובייל. אם שדה ריק, יורש מהגדרות מחשב';
    $html .= '</div>';
    $html .= renderTypographySettings($opts, 'mobile');
    $html .= '</div>';
    
    $html .= '</div>';
    
    return $html;
}

/**
 * יצירת הגדרות טיפוגרפיה לmobile/desktop
 */
function renderTypographySettings($opts, $device) {
    $basePath = $opts['basePath'] . '.' . $device;
    
    $html = '';
    
    // בחירת HTML Tag (רק בdesktop)
    if ($opts['showTag'] && $device === 'desktop') {
        // הסר את החלק של .styles.desktop מהpath כדי לקבל את הpath הבסיסי
        $tagPath = str_replace('.styles.desktop', '.tag', $basePath);
        
        $html .= '<div class="mb-4">';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">תג HTML (חשוב לSEO)</label>';
        $html .= '<select class="w-full border border-gray-300 rounded px-3 py-2 text-xs" data-path="' . $tagPath . '">';
        $html .= '<option value="h1">כותרת ראשית (H1)</option>';
        $html .= '<option value="h2">כותרת משנה (H2)</option>';
        $html .= '<option value="h3">כותרת משנה קטנה (H3)</option>';
        $html .= '<option value="h4">כותרת רביעית (H4)</option>';
        $html .= '<option value="h5">כותרת חמישית (H5)</option>';
        $html .= '<option value="h6">כותרת שישית (H6)</option>';
        $html .= '<option value="p">פסקה (P)</option>';
        $html .= '<option value="span">טקסט פשוט (SPAN)</option>';
        $html .= '</select>';
        $html .= '</div>';
    }
    
    $html .= '<div class="grid grid-cols-2 gap-4">';
    
    // גודל פונט
    if ($opts['showSize']) {
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">גודל פונט</label>';
        $html .= '<select class="w-full border border-gray-300 rounded px-3 py-2 text-xs" data-path="' . $basePath . '.font-size" data-responsive="true">';
        $html .= '<option value="">ברירת מחדל</option>';
        
        if ($device === 'desktop') {
            $html .= '<option value="32px">קטן (32px)</option>';
            $html .= '<option value="48px">בינוני (48px)</option>';
            $html .= '<option value="64px">גדול (64px)</option>';
            $html .= '<option value="80px">ענק (80px)</option>';
        } else {
            $html .= '<option value="20px">קטן (20px)</option>';
            $html .= '<option value="28px">בינוני (28px)</option>';
            $html .= '<option value="36px">גדול (36px)</option>';
            $html .= '<option value="48px">ענק (48px)</option>';
        }
        
        $html .= '</select>';
        $html .= '</div>';
    }
    
    // משקל פונט
    if ($opts['showWeight']) {
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">משקל פונט</label>';
        $html .= '<select class="w-full border border-gray-300 rounded px-3 py-2 text-xs" data-path="' . $basePath . '.font-weight" data-responsive="true">';
        $html .= '<option value="">ברירת מחדל</option>';
        $html .= '<option value="300">דק (300)</option>';
        $html .= '<option value="normal">רגיל (400)</option>';
        $html .= '<option value="500">בינוני (500)</option>';
        $html .= '<option value="600">מודגש (600)</option>';
        $html .= '<option value="bold">מודגש מאוד (700)</option>';
        $html .= '<option value="800">כבד (800)</option>';
        $html .= '</select>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    // שורה שנייה
    $html .= '<div class="grid grid-cols-2 gap-4 mt-4">';
    
    // יישור טקסט
    if ($opts['showAlign']) {
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">יישור טקסט</label>';
        $html .= '<div class="flex border border-gray-300 rounded overflow-hidden">';
        
        $alignments = [
            'right' => 'ri-align-right',   // יישור ימין בעברית = איקון ימין (החלפנו הערכים!)
            'center' => 'ri-align-center', 
            'left' => 'ri-align-left'      // יישור שמאל בעברית = איקון שמאל (החלפנו הערכים!)
        ];
        
        foreach ($alignments as $value => $icon) {
            $html .= '<button type="button" class="align-btn flex-1 p-2 text-center hover:bg-blue-50 transition-colors border-0 bg-transparent"';
            $html .= ' data-align="' . $value . '" data-path="' . $basePath . '.text-align" data-responsive="true">';
            $html .= '<i class="' . $icon . '"></i>';
            $html .= '</button>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    // רווח בין שורות
    if ($opts['showLineHeight']) {
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">גובה שורה</label>';
        $html .= '<select class="w-full border border-gray-300 rounded px-3 py-2 text-xs" data-path="' . $basePath . '.line-height" data-responsive="true">';
        $html .= '<option value="">ברירת מחדל</option>';
        $html .= '<option value="1">צפוף (1.0)</option>';
        $html .= '<option value="1.2">קצר (1.2)</option>';
        $html .= '<option value="1.4">רגיל (1.4)</option>';
        $html .= '<option value="1.6">נוח (1.6)</option>';
        $html .= '<option value="1.8">רחב (1.8)</option>';
        $html .= '</select>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    // צבע טקסט (זהה במובייל ובמחשב)
    if ($opts['showColor']) {
        $html .= '<div class="mt-4">';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">צבע טקסט</label>';
        $html .= '<div class="flex items-center gap-3">';
        
        $html .= '<input type="color" class="w-10 h-8 border border-gray-300 rounded cursor-pointer"';
        $html .= ' data-path="' . $basePath . '.color" data-shared="true" value="#000000">';
        
        // Clear button פשוט
        $html .= '<button type="button" class="clear-color-btn text-xs bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600" data-target="[data-path=\'' . $basePath . '.color\']">';
        $html .= 'נקה';
        $html .= '</button>';
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    return $html;
}

// שימוש ישיר
if (isset($_GET['render'])) {
    echo renderResponsiveTypography($_GET);
}
?> 