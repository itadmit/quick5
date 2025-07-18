<?php
/**
 * Visibility Control Component - קומפוננט שליטה בתצוגה responsive
 * הסתרה במובייל או במחשב
 */

function renderVisibilityControl($options = []) {
    $defaults = [
        'title' => 'הסתרה לפי מכשיר',
        'basePath' => 'visibility',
        'icon' => 'ri-eye-line',
        'iconColor' => 'text-orange-600'
    ];
    
    $opts = array_merge($defaults, $options);
    
    // יצירת ID ייחודי לאקורדיון
    $accordionId = 'accordion_' . uniqid();
    
    $html = '<div class="visibility-control-component border border-gray-200 rounded-lg mb-4">';
    
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
    

    
    // אפשרויות הסתרה
    $html .= '<div class="grid grid-cols-2 gap-4">';
    
    // הסתר במחשב
    $html .= '<div class="text-center p-4 border border-gray-200 rounded-lg">';
    $html .= '<div class="mb-3">';
    $html .= '<i class="ri-computer-line text-2xl text-gray-600 block mb-2"></i>';
    $html .= '<span class="text-sm font-medium text-gray-700">מחשב</span>';
    $html .= '</div>';
    $html .= '<label class="flex items-center justify-center gap-2 cursor-pointer">';
    $html .= '<input type="checkbox" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500" ';
    $html .= 'data-path="' . $opts['basePath'] . '.hide-desktop">';
    $html .= '<span class="text-sm text-gray-700">הסתר במחשב</span>';
    $html .= '</label>';
    $html .= '</div>';
    
    // הסתר במובייל
    $html .= '<div class="text-center p-4 border border-gray-200 rounded-lg">';
    $html .= '<div class="mb-3">';
    $html .= '<i class="ri-smartphone-line text-2xl text-gray-600 block mb-2"></i>';
    $html .= '<span class="text-sm font-medium text-gray-700">מובייל</span>';
    $html .= '</div>';
    $html .= '<label class="flex items-center justify-center gap-2 cursor-pointer">';
    $html .= '<input type="checkbox" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500" ';
    $html .= 'data-path="' . $opts['basePath'] . '.hide-mobile">';
    $html .= '<span class="text-sm text-gray-700">הסתר במובייל</span>';
    $html .= '</label>';
    $html .= '</div>';
    
    $html .= '</div>';
    
    // אזהרה
    $html .= '<div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">';
    $html .= '<div class="flex items-start gap-2">';
    $html .= '<i class="ri-alert-line text-yellow-600 mt-0.5"></i>';
    $html .= '<div class="text-xs text-yellow-700">';
    $html .= '<strong>שים לב:</strong> אם תסמן את שני האפשרויות, האלמנט יוסתר לחלוטין!';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    $html .= '</div>'; // סיום padding container
    $html .= '</div>'; // סיום accordion-content
    $html .= '</div>'; // סיום component
    
    return $html;
}

// שימוש ישיר
if (isset($_GET['render'])) {
    echo renderVisibilityControl($_GET);
}
?> 