<?php
/**
 * Typography Component - רכיב הגדרות טיפוגרפיה
 */

// Helper functions for escaping
if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
    }
}

/**
 * רינדור בקרי טיפוגרפיה
 */
function renderTypographyControls($prefix, $label, $currentSettings = []) {
    $fontFamilies = [
        'Noto Sans Hebrew' => 'נוטו סאנס עברית',
        'Assistant' => 'אסיסטנט',
        'Rubik' => 'רוביק',
        'David Libre' => 'דוד ליברה',
        'Frank Ruehl CLM' => 'פרנק רוהל',
        'Heebo' => 'היבו',
        'Miriam Libre' => 'מרים ליברה',
        'Open Sans' => 'אופן סאנס',
        'Roboto' => 'רובוטו',
        'Lato' => 'לאטו',
        'Montserrat' => 'מונטסראט',
        'Poppins' => 'פופינס'
    ];
    
    $fontWeights = [
        '100' => 'דק מאוד',
        '200' => 'דק',
        '300' => 'קל',
        '400' => 'רגיל',
        '500' => 'בינוני',
        '600' => 'בולט',
        '700' => 'מודגש',
        '800' => 'מודגש מאוד',
        '900' => 'שחור'
    ];
    
    $fontStyles = [
        'normal' => 'רגיל',
        'italic' => 'נטוי'
    ];
    
    $textDecorations = [
        'none' => 'ללא',
        'underline' => 'קו תחתון',
        'overline' => 'קו עליון',
        'line-through' => 'קו חוצה'
    ];
    
    $textTransforms = [
        'none' => 'ללא',
        'uppercase' => 'אותיות גדולות',
        'lowercase' => 'אותיות קטנות',
        'capitalize' => 'ראשיות גדולות'
    ];
    
    // ערכי ברירת מחדל בהתאם לסוג הטקסט
    $defaultFontSize = $prefix === 'heroTitle' ? '48' : '18';
    $defaultTextType = $prefix === 'heroTitle' ? 'h1' : 'p';
    $defaultFontWeight = $prefix === 'heroTitle' ? '700' : '400';
    ?>
    
    <div class="typography-controls space-y-4 bg-white border border-gray-200 rounded-lg p-4">
        <!-- Text Type & Font Size -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="<?php echo $prefix; ?>TextType" class="block text-xs font-medium text-gray-600 mb-1">סוג הטקסט</label>
                <select id="<?php echo $prefix; ?>TextType" 
                        name="<?php echo $prefix; ?>TextType"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="h1" <?php echo ($currentSettings[$prefix . 'TextType'] ?? $defaultTextType) === 'h1' ? 'selected' : ''; ?>>כותרת ראשית (H1)</option>
                    <option value="h2" <?php echo ($currentSettings[$prefix . 'TextType'] ?? $defaultTextType) === 'h2' ? 'selected' : ''; ?>>כותרת משנית (H2)</option>
                    <option value="h3" <?php echo ($currentSettings[$prefix . 'TextType'] ?? $defaultTextType) === 'h3' ? 'selected' : ''; ?>>כותרת שלישית (H3)</option>
                    <option value="h4" <?php echo ($currentSettings[$prefix . 'TextType'] ?? $defaultTextType) === 'h4' ? 'selected' : ''; ?>>כותרת רביעית (H4)</option>
                    <option value="h5" <?php echo ($currentSettings[$prefix . 'TextType'] ?? $defaultTextType) === 'h5' ? 'selected' : ''; ?>>כותרת חמישית (H5)</option>
                    <option value="h6" <?php echo ($currentSettings[$prefix . 'TextType'] ?? $defaultTextType) === 'h6' ? 'selected' : ''; ?>>כותרת שישית (H6)</option>
                    <option value="p" <?php echo ($currentSettings[$prefix . 'TextType'] ?? $defaultTextType) === 'p' ? 'selected' : ''; ?>>פסקה (P)</option>
                    <option value="span" <?php echo ($currentSettings[$prefix . 'TextType'] ?? $defaultTextType) === 'span' ? 'selected' : ''; ?>>טקסט רגיל (SPAN)</option>
                    <option value="div" <?php echo ($currentSettings[$prefix . 'TextType'] ?? $defaultTextType) === 'div' ? 'selected' : ''; ?>>בלוק טקסט (DIV)</option>
                </select>
            </div>
            <div>
                <label for="<?php echo $prefix; ?>FontSize" class="block text-xs font-medium text-gray-600 mb-1">גודל גופן (px)</label>
                <input type="number" 
                       id="<?php echo $prefix; ?>FontSize" 
                       name="<?php echo $prefix; ?>FontSize"
                       value="<?php echo esc_attr($currentSettings[$prefix . 'FontSize'] ?? $defaultFontSize); ?>"
                       min="8" max="200" step="1"
                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                       placeholder="<?php echo $defaultFontSize; ?>">
            </div>
        </div>
        
        <hr class="border-gray-200">
        
        <!-- Font Family & Weight -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="<?php echo $prefix; ?>FontFamily" class="block text-xs font-medium text-gray-600 mb-1">משפחת גופן</label>
                <select id="<?php echo $prefix; ?>FontFamily" 
                        name="<?php echo $prefix; ?>FontFamily"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <?php foreach ($fontFamilies as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php echo ($currentSettings[$prefix . 'FontFamily'] ?? 'Noto Sans Hebrew') === $value ? 'selected' : ''; ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="<?php echo $prefix; ?>FontWeight" class="block text-xs font-medium text-gray-600 mb-1">עובי גופן</label>
                <select id="<?php echo $prefix; ?>FontWeight" 
                        name="<?php echo $prefix; ?>FontWeight"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <?php foreach ($fontWeights as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php echo ($currentSettings[$prefix . 'FontWeight'] ?? $defaultFontWeight) === $value ? 'selected' : ''; ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <!-- Font Style -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="<?php echo $prefix; ?>FontStyle" class="block text-xs font-medium text-gray-600 mb-1">סגנון גופן</label>
                <select id="<?php echo $prefix; ?>FontStyle" 
                        name="<?php echo $prefix; ?>FontStyle"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <?php foreach ($fontStyles as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php echo ($currentSettings[$prefix . 'FontStyle'] ?? 'normal') === $value ? 'selected' : ''; ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <hr class="border-gray-200">
        
        <!-- Line Height & Letter Spacing -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="<?php echo $prefix; ?>LineHeight" class="block text-xs font-medium text-gray-600 mb-1">גובה שורה</label>
                <input type="number" 
                       id="<?php echo $prefix; ?>LineHeight" 
                       name="<?php echo $prefix; ?>LineHeight"
                       value="<?php echo esc_attr($currentSettings[$prefix . 'LineHeight'] ?? '1.4'); ?>"
                       min="0.5" max="5" step="0.1"
                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                       placeholder="1.4">
            </div>
            
            <div>
                <label for="<?php echo $prefix; ?>LetterSpacing" class="block text-xs font-medium text-gray-600 mb-1">מרווח תווים (px)</label>
                <input type="number" 
                       id="<?php echo $prefix; ?>LetterSpacing" 
                       name="<?php echo $prefix; ?>LetterSpacing"
                       value="<?php echo esc_attr($currentSettings[$prefix . 'LetterSpacing'] ?? '0'); ?>"
                       min="-5" max="20" step="0.1"
                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                       placeholder="0">
            </div>
        </div>
        
        <hr class="border-gray-200">
        
        <!-- Text Decoration & Transform -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="<?php echo $prefix; ?>TextDecoration" class="block text-xs font-medium text-gray-600 mb-1">קישוט טקסט</label>
                <select id="<?php echo $prefix; ?>TextDecoration" 
                        name="<?php echo $prefix; ?>TextDecoration"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <?php foreach ($textDecorations as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php echo ($currentSettings[$prefix . 'TextDecoration'] ?? 'none') === $value ? 'selected' : ''; ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="<?php echo $prefix; ?>TextTransform" class="block text-xs font-medium text-gray-600 mb-1">השנית טקסט</label>
                <select id="<?php echo $prefix; ?>TextTransform" 
                        name="<?php echo $prefix; ?>TextTransform"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <?php foreach ($textTransforms as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php echo ($currentSettings[$prefix . 'TextTransform'] ?? 'none') === $value ? 'selected' : ''; ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <hr class="border-gray-200">
        
        <!-- Text Alignment -->
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-2">יישור טקסט</label>
            <div class="grid grid-cols-4 gap-1">
                <button type="button" 
                        class="text-align-btn px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors <?php echo ($currentSettings[$prefix . 'TextAlign'] ?? 'right') === 'right' ? 'bg-blue-500 text-white' : ''; ?>" 
                        data-align="right">
                    <i class="ri-align-right"></i>
                </button>
                <button type="button" 
                        class="text-align-btn px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors <?php echo ($currentSettings[$prefix . 'TextAlign'] ?? 'right') === 'center' ? 'bg-blue-500 text-white' : ''; ?>" 
                        data-align="center">
                    <i class="ri-align-center"></i>
                </button>
                <button type="button" 
                        class="text-align-btn px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors <?php echo ($currentSettings[$prefix . 'TextAlign'] ?? 'right') === 'left' ? 'bg-blue-500 text-white' : ''; ?>" 
                        data-align="left">
                    <i class="ri-align-left"></i>
                </button>
                <button type="button" 
                        class="text-align-btn px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors <?php echo ($currentSettings[$prefix . 'TextAlign'] ?? 'right') === 'justify' ? 'bg-blue-500 text-white' : ''; ?>" 
                        data-align="justify">
                    <i class="ri-align-justify"></i>
                </button>
            </div>
            <input type="hidden" id="<?php echo $prefix; ?>TextAlign" name="<?php echo $prefix; ?>TextAlign" value="<?php echo esc_attr($currentSettings[$prefix . 'TextAlign'] ?? 'right'); ?>">
        </div>
    </div>
    
    <script>
    // Typography controls logic for <?php echo $prefix; ?>
    (function() {
        const alignBtns = document.querySelectorAll('#<?php echo $prefix; ?>TextAlign').closest('.typography-controls').querySelectorAll('.text-align-btn');
        const hiddenInput = document.getElementById('<?php echo $prefix; ?>TextAlign');
        
        alignBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const align = this.dataset.align;
                
                // Update active button
                alignBtns.forEach(b => b.classList.remove('bg-blue-500', 'text-white'));
                this.classList.add('bg-blue-500', 'text-white');
                
                // Update hidden input
                if (hiddenInput) {
                    hiddenInput.value = align;
                    hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        });
        
        // Trigger change events for real-time updates
        const inputs = document.querySelectorAll('#<?php echo $prefix; ?>FontSize, #<?php echo $prefix; ?>FontFamily, #<?php echo $prefix; ?>FontWeight, #<?php echo $prefix; ?>FontStyle, #<?php echo $prefix; ?>LineHeight, #<?php echo $prefix; ?>LetterSpacing, #<?php echo $prefix; ?>TextDecoration, #<?php echo $prefix; ?>TextTransform, #<?php echo $prefix; ?>TextType');
        
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                console.log('🔧 DEBUG: Updating setting', this.name, this.value);
                this.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
        
    })();
    </script>
    <?php
}
?> 