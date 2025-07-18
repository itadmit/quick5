<?php
/**
 * Text Input Component - קומפוננט שדה טקסט
 */

function renderTextInput($options = []) {
    $defaults = [
        'label' => 'טקסט',
        'placeholder' => '',
        'path' => '',
        'required' => false,
        'maxlength' => null,
        'class' => 'settings-input',
        'type' => 'input', // 'input' או 'textarea'
        'rows' => 3 // רק עבור textarea
    ];
    
    $opts = array_merge($defaults, $options);
    
    $html = '<div class="mb-4">';
    
    // Label
    $html .= '<label class="settings-label">';
    $html .= htmlspecialchars($opts['label']);
    if ($opts['required']) {
        $html .= ' <span class="text-red-500">*</span>';
    }
    $html .= '</label>';
    
    // Input או Textarea
    if ($opts['type'] === 'textarea') {
        // Textarea
        $html .= '<textarea';
        $html .= ' class="w-full border border-gray-300 rounded px-3 py-2 text-sm resize-none"';
        $html .= ' data-path="' . htmlspecialchars($opts['path']) . '"';
        $html .= ' rows="' . intval($opts['rows']) . '"';
        
        if ($opts['placeholder']) {
            $html .= ' placeholder="' . htmlspecialchars($opts['placeholder']) . '"';
        }
        
        if ($opts['required']) {
            $html .= ' required';
        }
        
        if ($opts['maxlength']) {
            $html .= ' maxlength="' . intval($opts['maxlength']) . '"';
        }
        
        $html .= '></textarea>';
    } else {
        // Input רגיל
        $html .= '<input type="text"';
        $html .= ' class="' . htmlspecialchars($opts['class']) . '"';
        $html .= ' data-path="' . htmlspecialchars($opts['path']) . '"';
        
        if ($opts['placeholder']) {
            $html .= ' placeholder="' . htmlspecialchars($opts['placeholder']) . '"';
        }
        
        if ($opts['required']) {
            $html .= ' required';
        }
        
        if ($opts['maxlength']) {
            $html .= ' maxlength="' . intval($opts['maxlength']) . '"';
        }
        
        $html .= '>';
    }
    
    $html .= '</div>';
    
    return $html;
}

// שימוש ישיר אם נקרא הקובץ עם פרמטרים
if (isset($_GET['render'])) {
    echo renderTextInput($_GET);
}
?> 