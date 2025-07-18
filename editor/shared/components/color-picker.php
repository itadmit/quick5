<?php
/**
 * Color Picker Component - קומפוננט בוחר צבעים
 */

function renderColorPicker($options = []) {
    $defaults = [
        'label' => 'צבע',
        'path' => '',
        'required' => false,
        'presets' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4']
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
    
    // Color input
    $html .= '<div class="flex items-center gap-3">';
    $html .= '<input type="color"';
    $html .= ' class="w-12 h-10 border border-gray-300 rounded cursor-pointer"';
    $html .= ' data-path="' . htmlspecialchars($opts['path']) . '"';
    
    if ($opts['required']) {
        $html .= ' required';
    }
    
    $html .= '>';
    
    // Preset colors
    if (!empty($opts['presets'])) {
        $html .= '<div class="flex gap-2">';
        foreach ($opts['presets'] as $color) {
            $html .= '<button type="button" class="color-preset w-6 h-6 rounded border border-gray-300 cursor-pointer"';
            $html .= ' style="background-color: ' . htmlspecialchars($color) . '"';
            $html .= ' data-color="' . htmlspecialchars($color) . '"';
            $html .= ' title="' . htmlspecialchars($color) . '">';
            $html .= '</button>';
        }
        $html .= '</div>';
    }
    
    $html .= '</div>';
    $html .= '</div>';
    
    // JavaScript for presets
    $html .= '<script>';
    $html .= 'document.querySelectorAll(".color-preset").forEach(btn => {';
    $html .= '  btn.addEventListener("click", (e) => {';
    $html .= '    const color = e.target.dataset.color;';
    $html .= '    const colorInput = e.target.closest(".mb-4").querySelector("input[type=color]");';
    $html .= '    if (colorInput) {';
    $html .= '      colorInput.value = color;';
    $html .= '      colorInput.dispatchEvent(new Event("change"));';
    $html .= '    }';
    $html .= '  });';
    $html .= '});';
    $html .= '</script>';
    
    return $html;
}

// שימוש ישיר
if (isset($_GET['render'])) {
    echo renderColorPicker($_GET);
}
?> 