<?php
/**
 * Spacing Control Component - קומפוננט שליטה במרווחים
 * 4 כיוונים עם אייקונים: למעלה, ימין, למטה, שמאל
 */

function renderSpacingControl($options = []) {
    $defaults = [
        'title' => 'מרווחים',
        'basePath' => 'styles',
        'showPadding' => true,
        'showMargin' => true,
        'icon' => 'ri-expand-diagonal-line',
        'iconColor' => 'text-blue-600'
    ];
    
    $opts = array_merge($defaults, $options);
    
    // יצירת ID ייחודי לאקורדיון
    $accordionId = 'accordion_' . uniqid();
    
    $html = '<div class="spacing-control-component border border-gray-200 rounded-lg mb-4">';
    
    // כותרת האקורדיון - לחיצה
    $html .= '<button type="button" class="accordion-header w-full p-4 flex items-center justify-between text-right transition-colors hover:bg-gray-50" ';
    $html .= 'onclick="toggleAccordion(\'' . $accordionId . '\')">';
    
    // צד ימין - אייקון וטקסט
    $html .= '<div class="flex items-center gap-2">';
    $html .= '<i class="' . $opts['icon'] . ' ' . $opts['iconColor'] . '"></i>';
    $html .= '<span class="font-medium text-sm text-gray-900">' . htmlspecialchars($opts['title']) . '</span>';
    $html .= '</div>';
    
    // צד שמאל - חץ
    $html .= '<i class="ri-arrow-down-s-line text-gray-500 transition-transform duration-200" id="arrow_' . $accordionId . '"></i>';
    $html .= '</button>';
    
    // תוכן האקורדיון
    $html .= '<div id="' . $accordionId . '" class="accordion-content" style="display: none;">';
    $html .= '<div class="p-4 border-t border-gray-200">';
    
    // Padding Section
    if ($opts['showPadding']) {
        $html .= '<div class="mb-6">';
        $html .= '<h4 class="text-xs font-medium text-gray-700 mb-3">מרווח פנימי (Padding)</h4>';
        $html .= renderDirectionalInputs($opts['basePath'], 'padding');
        $html .= '</div>';
    }
    
    // Margin Section  
    if ($opts['showMargin']) {
        $html .= '<div class="mb-4">';
        $html .= '<h4 class="text-xs font-medium text-gray-700 mb-3">מרווח חיצוני (Margin)</h4>';
        $html .= renderDirectionalInputs($opts['basePath'], 'margin');
        $html .= '</div>';
    }
    
    $html .= '</div>'; // סיום padding container
    $html .= '</div>'; // סיום accordion-content
    $html .= '</div>'; // סיום component
    
    return $html;
}

/**
 * יצירת 4 inputs עם אייקונים לכיוונים
 */
function renderDirectionalInputs($basePath, $type) {
    $directions = [
        'top' => ['icon' => 'ri-arrow-up-line', 'label' => 'למעלה'],
        'right' => ['icon' => 'ri-arrow-right-line', 'label' => 'ימין'], 
        'bottom' => ['icon' => 'ri-arrow-down-line', 'label' => 'למטה'],
        'left' => ['icon' => 'ri-arrow-left-line', 'label' => 'שמאל']
    ];
    
    $html = '<div class="grid grid-cols-4 gap-3">';
    
    foreach ($directions as $direction => $info) {
        $path = $basePath . '.' . $type . '-' . $direction;
        
        $html .= '<div class="text-center">';
        
        // אייקון וכיתוב
        $html .= '<div class="mb-2">';
        $html .= '<i class="' . $info['icon'] . ' text-lg text-gray-600 block mb-1"></i>';
        $html .= '<span class="text-xs text-gray-500">' . $info['label'] . '</span>';
        $html .= '</div>';
        
        // Input field
        $html .= '<input type="number" ';
        $html .= 'class="w-full border border-gray-300 rounded px-2 py-1 text-sm text-center" ';
        $html .= 'placeholder="0" ';
        $html .= 'min="0" ';
        $html .= 'max="200" ';
        $html .= 'data-path="' . $path . '">';
        
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * קומפוננט HTML Attributes - לID ו-Class
 */
function renderHtmlAttributes($options = []) {
    $defaults = [
        'title' => 'מאפייני HTML',
        'basePath' => 'attributes',
        'showId' => true,
        'showClass' => true
    ];
    
    $opts = array_merge($defaults, $options);
    
    // יצירת ID ייחודי לאקורדיון
    $accordionId = 'accordion_' . uniqid();
    
    $html = '<div class="html-attributes-component border border-gray-200 rounded-lg mb-4">';
    
    // כותרת האקורדיון - לחיצה
    $html .= '<button type="button" class="accordion-header w-full p-4 flex items-center justify-between text-right transition-colors hover:bg-gray-50" ';
    $html .= 'onclick="toggleAccordion(\'' . $accordionId . '\')">';
    
    // צד ימין - אייקון וטקסט
    $html .= '<div class="flex items-center gap-2">';
    $html .= '<i class="ri-code-line text-purple-600"></i>';
    $html .= '<span class="font-medium text-sm text-gray-900">' . htmlspecialchars($opts['title']) . '</span>';
    $html .= '</div>';
    
    // צד שמאל - חץ
    $html .= '<i class="ri-arrow-down-s-line text-gray-500 transition-transform duration-200" id="arrow_' . $accordionId . '"></i>';
    $html .= '</button>';
    
    // תוכן האקורדיון
    $html .= '<div id="' . $accordionId . '" class="accordion-content" style="display: none;">';
    $html .= '<div class="p-4 border-t border-gray-200">';
    $html .= '<div class="grid grid-cols-1 gap-4">';
    
    // ID Field
    if ($opts['showId']) {
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">';
        $html .= 'מזהה יחודי (ID)';
        $html .= '<span class="text-gray-500 font-normal"> - אופציונאלי</span>';
        $html .= '</label>';
        $html .= '<input type="text" ';
        $html .= 'class="w-full border border-gray-300 rounded px-3 py-2 text-sm" ';
        $html .= 'placeholder="my-unique-id" ';
        $html .= 'data-path="' . $opts['basePath'] . '.id">';
        $html .= '<div class="text-xs text-gray-500 mt-1">למטרות CSS/JS מתקדמות</div>';
        $html .= '</div>';
    }
    
    // Class Field
    if ($opts['showClass']) {
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">';
        $html .= 'מחלקות CSS (Classes)';
        $html .= '<span class="text-gray-500 font-normal"> - אופציונאלי</span>';
        $html .= '</label>';
        $html .= '<input type="text" ';
        $html .= 'class="w-full border border-gray-300 rounded px-3 py-2 text-sm" ';
        $html .= 'placeholder="my-class another-class" ';
        $html .= 'data-path="' . $opts['basePath'] . '.class">';
        $html .= '<div class="text-xs text-gray-500 mt-1">הפרד מחלקות ברווח</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>'; // סיום grid
    $html .= '</div>'; // סיום padding container
    $html .= '</div>'; // סיום accordion-content
    $html .= '</div>'; // סיום component
    
    return $html;
}

// שימוש ישיר
if (isset($_GET['render'])) {
    if ($_GET['type'] === 'spacing') {
        echo renderSpacingControl($_GET);
    } elseif ($_GET['type'] === 'attributes') {
        echo renderHtmlAttributes($_GET);
    }
}
?>

 