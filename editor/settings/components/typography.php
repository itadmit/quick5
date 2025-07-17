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
function renderTypographyControls($prefix, $label) {
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
    ?>
    
    <div class="typography-controls space-y-4">
        <!-- Font Size & Family -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="<?php echo $prefix; ?>FontSize" class="block text-xs font-medium text-gray-600 mb-1">גודל גופן (px)</label>
                <input type="number" 
                       id="<?php echo $prefix; ?>FontSize" 
                       name="fontSize"
                       min="8" max="200" step="1"
                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                       placeholder="16">
            </div>
            
            <div>
                <label for="<?php echo $prefix; ?>FontFamily" class="block text-xs font-medium text-gray-600 mb-1">משפחת גופן</label>
                <select id="<?php echo $prefix; ?>FontFamily" 
                        name="fontFamily"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <?php foreach ($fontFamilies as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <!-- Font Weight & Style -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="<?php echo $prefix; ?>FontWeight" class="block text-xs font-medium text-gray-600 mb-1">עובי גופן</label>
                <select id="<?php echo $prefix; ?>FontWeight" 
                        name="fontWeight"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <?php foreach ($fontWeights as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="<?php echo $prefix; ?>FontStyle" class="block text-xs font-medium text-gray-600 mb-1">סגנון גופן</label>
                <select id="<?php echo $prefix; ?>FontStyle" 
                        name="fontStyle"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <?php foreach ($fontStyles as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <!-- Line Height & Letter Spacing -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="<?php echo $prefix; ?>LineHeight" class="block text-xs font-medium text-gray-600 mb-1">גובה שורה</label>
                <input type="number" 
                       id="<?php echo $prefix; ?>LineHeight" 
                       name="lineHeight"
                       min="0.5" max="5" step="0.1"
                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                       placeholder="1.4">
            </div>
            
            <div>
                <label for="<?php echo $prefix; ?>LetterSpacing" class="block text-xs font-medium text-gray-600 mb-1">מרווח תווים (px)</label>
                <input type="number" 
                       id="<?php echo $prefix; ?>LetterSpacing" 
                       name="letterSpacing"
                       min="-5" max="20" step="0.1"
                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                       placeholder="0">
            </div>
        </div>
        
        <!-- Text Decoration & Transform -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="<?php echo $prefix; ?>TextDecoration" class="block text-xs font-medium text-gray-600 mb-1">קישוט טקסט</label>
                <select id="<?php echo $prefix; ?>TextDecoration" 
                        name="textDecoration"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <?php foreach ($textDecorations as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="<?php echo $prefix; ?>TextTransform" class="block text-xs font-medium text-gray-600 mb-1">השנית טקסט</label>
                <select id="<?php echo $prefix; ?>TextTransform" 
                        name="textTransform"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <?php foreach ($textTransforms as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <!-- Text Alignment -->
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-2">יישור טקסט</label>
            <div class="grid grid-cols-4 gap-1">
                <button type="button" class="text-align-btn px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-align="right">
                    <i class="ri-align-right"></i>
                </button>
                <button type="button" class="text-align-btn px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-align="center">
                    <i class="ri-align-center"></i>
                </button>
                <button type="button" class="text-align-btn px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-align="left">
                    <i class="ri-align-left"></i>
                </button>
                <button type="button" class="text-align-btn px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-align="justify">
                    <i class="ri-align-justify"></i>
                </button>
            </div>
            <input type="hidden" id="<?php echo $prefix; ?>TextAlign" name="textAlign" value="right">
        </div>
    </div>
    
    <script>
    // Typography controls logic
    (function() {
        const alignBtns = document.querySelectorAll('.text-align-btn');
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
        
        // Set default alignment
        const defaultAlign = alignBtns[0]; // right by default for Hebrew
        if (defaultAlign) {
            defaultAlign.classList.add('bg-blue-500', 'text-white');
        }
    })();
    </script>
    <?php
}
?> 