<?php
/**
 * Typography Component - קומפוננט טיפוגרפיה מורכב
 * מכיל: גודל, משקל, צבע, יישור, רווח בין שורות
 */

function renderTypography($options = []) {
    $defaults = [
        'title' => 'טיפוגרפיה',
        'basePath' => 'styles',
        'showSize' => true,
        'showWeight' => true,
        'showColor' => true,
        'showAlign' => true,
        'showLineHeight' => true,
        'colorPresets' => ['#000000', '#ffffff', '#374151', '#6b7280', '#ef4444', '#3b82f6']
    ];
    
    $opts = array_merge($defaults, $options);
    
    $html = '<div class="typography-component border border-gray-200 rounded-lg p-4 mb-4">';
    
    // כותרת הקומפוננט
    $html .= '<h4 class="font-medium text-sm text-gray-900 mb-4 flex items-center gap-2">';
    $html .= '<i class="ri-text text-blue-600"></i>';
    $html .= htmlspecialchars($opts['title']);
    $html .= '</h4>';
    
    $html .= '<div class="grid grid-cols-2 gap-4">';
    
    // גודל פונט
    if ($opts['showSize']) {
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs">גודל פונט</label>';
        $html .= '<select class="settings-input text-xs" data-path="' . $opts['basePath'] . '.font-size">';
        $html .= '<option value="12px">קטן מאוד (12px)</option>';
        $html .= '<option value="14px">קטן (14px)</option>';
        $html .= '<option value="16px">רגיל (16px)</option>';
        $html .= '<option value="18px">בינוני (18px)</option>';
        $html .= '<option value="20px">גדול (20px)</option>';
        $html .= '<option value="24px">גדול מאוד (24px)</option>';
        $html .= '<option value="32px">ענק (32px)</option>';
        $html .= '<option value="48px">ענק מאוד (48px)</option>';
        $html .= '<option value="64px">ענק ענק (64px)</option>';
        $html .= '</select>';
        $html .= '</div>';
    }
    
    // משקל פונט
    if ($opts['showWeight']) {
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs">משקל פונט</label>';
        $html .= '<select class="settings-input text-xs" data-path="' . $opts['basePath'] . '.font-weight">';
        $html .= '<option value="300">דק (300)</option>';
        $html .= '<option value="normal">רגיל (400)</option>';
        $html .= '<option value="500">בינוני (500)</option>';
        $html .= '<option value="600">מודגש (600)</option>';
        $html .= '<option value="bold">מודגש מאוד (700)</option>';
        $html .= '<option value="800">כבד (800)</option>';
        $html .= '<option value="900">כבד מאוד (900)</option>';
        $html .= '</select>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    // שורה שנייה
    $html .= '<div class="grid grid-cols-2 gap-4 mt-4">';
    
    // יישור טקסט
    if ($opts['showAlign']) {
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs">יישור טקסט</label>';
        $html .= '<div class="flex border border-gray-300 rounded overflow-hidden">';
        
        $alignments = [
            'right' => 'ri-align-right',   // יישור ימין בעברית = איקון ימין (החלפנו הערכים!)
            'center' => 'ri-align-center', 
            'left' => 'ri-align-left',     // יישור שמאל בעברית = איקון שמאל (החלפנו הערכים!)
            'justify' => 'ri-align-justify'
        ];
        
        foreach ($alignments as $value => $icon) {
            $html .= '<button type="button" class="align-btn flex-1 p-2 text-center hover:bg-blue-50 transition-colors"';
            $html .= ' data-align="' . $value . '" data-path="' . $opts['basePath'] . '.text-align">';
            $html .= '<i class="' . $icon . '"></i>';
            $html .= '</button>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    // רווח בין שורות
    if ($opts['showLineHeight']) {
        $html .= '<div>';
        $html .= '<label class="settings-label text-xs">גובה שורה</label>';
        $html .= '<select class="settings-input text-xs" data-path="' . $opts['basePath'] . '.line-height">';
        $html .= '<option value="1">צפוף (1.0)</option>';
        $html .= '<option value="1.2">קצר (1.2)</option>';
        $html .= '<option value="1.4">רגיל (1.4)</option>';
        $html .= '<option value="1.6">נוח (1.6)</option>';
        $html .= '<option value="1.8">רחב (1.8)</option>';
        $html .= '<option value="2">רחב מאוד (2.0)</option>';
        $html .= '</select>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    // צבע טקסט
    if ($opts['showColor']) {
        $html .= '<div class="mt-4">';
        $html .= '<label class="settings-label text-xs">צבע טקסט</label>';
        $html .= '<div class="flex items-center gap-3">';
        
        // Color picker
        $html .= '<input type="color" class="w-10 h-8 border border-gray-300 rounded cursor-pointer"';
        $html .= ' data-path="' . $opts['basePath'] . '.color">';
        
        // Preset colors
        if (!empty($opts['colorPresets'])) {
            $html .= '<div class="flex gap-2">';
            foreach ($opts['colorPresets'] as $color) {
                $html .= '<button type="button" class="color-preset w-6 h-6 rounded border border-gray-300 cursor-pointer"';
                $html .= ' style="background-color: ' . htmlspecialchars($color) . '"';
                $html .= ' data-color="' . htmlspecialchars($color) . '"';
                $html .= ' data-target="[data-path=\'' . $opts['basePath'] . '.color\']">';
                $html .= '</button>';
            }
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    // JavaScript for interactive elements
    $html .= '<script>';
    
    // Alignment buttons
    $html .= 'document.querySelectorAll(".align-btn").forEach(btn => {';
    $html .= '  btn.addEventListener("click", (e) => {';
    $html .= '    const container = e.target.closest(".typography-component");';
    $html .= '    container.querySelectorAll(".align-btn").forEach(b => b.classList.remove("bg-blue-100"));';
    $html .= '    e.target.closest(".align-btn").classList.add("bg-blue-100");';
    $html .= '    ';
    $html .= '    const hiddenInput = container.querySelector(`input[data-path="${e.target.dataset.path}"]`) || ';
    $html .= '                       document.createElement("input");';
    $html .= '    hiddenInput.type = "hidden";';
    $html .= '    hiddenInput.dataset.path = e.target.dataset.path;';
    $html .= '    hiddenInput.value = e.target.dataset.align;';
    $html .= '    if (!hiddenInput.parentNode) container.appendChild(hiddenInput);';
    $html .= '    hiddenInput.dispatchEvent(new Event("change"));';
    $html .= '  });';
    $html .= '});';
    
    // Color presets
    $html .= 'document.querySelectorAll(".color-preset").forEach(btn => {';
    $html .= '  btn.addEventListener("click", (e) => {';
    $html .= '    const color = e.target.dataset.color;';
    $html .= '    const target = e.target.dataset.target;';
    $html .= '    const colorInput = document.querySelector(target);';
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
    echo renderTypography($_GET);
}
?> 