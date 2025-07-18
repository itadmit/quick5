<?php
/**
 * Select Component - קומפוננט רשימה נפתחת
 */

function renderSelect($options = []) {
    $defaults = [
        'label' => 'בחר אפשרות',
        'path' => '',
        'required' => false,
        'options' => [],
        'class' => 'settings-input'
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
    
    // Select
    $html .= '<select';
    $html .= ' class="' . htmlspecialchars($opts['class']) . '"';
    $html .= ' data-path="' . htmlspecialchars($opts['path']) . '"';
    
    if ($opts['required']) {
        $html .= ' required';
    }
    
    $html .= '>';
    
    // Options
    foreach ($opts['options'] as $value => $text) {
        $html .= '<option value="' . htmlspecialchars($value) . '">';
        $html .= htmlspecialchars($text);
        $html .= '</option>';
    }
    
    $html .= '</select>';
    $html .= '</div>';
    
    return $html;
}

// שימוש ישיר
if (isset($_GET['render'])) {
    echo renderSelect($_GET);
}
?> 