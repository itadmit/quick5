<?php
/**
 * Typography Component - רכיב הגדרות טיפוגרפיה
 */

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
    
    <div class="typography-controls grid grid-cols-1 gap-4">
        <!-- Font Size & Family -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="<?php echo $prefix; ?>FontSize" class="block text-xs font-medium text-gray-600 mb-1">גודל גופן (px)</label>
                <input type="number" 
                       id="<?php echo $prefix; ?>FontSize" 
                       name="<?php echo strtolower(str_replace('hero', '', $prefix)); ?>FontSize"
                       min="8" max="200" step="1"
                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                       placeholder="16">
            </div>
            
            <div>
                <label for="<?php echo $prefix; ?>FontFamily" class="block text-xs font-medium text-gray-600 mb-1">משפחת גופן</label>
                <select id="<?php echo $prefix; ?>FontFamily" 
                        name="<?php echo strtolower(str_replace('hero', '', $prefix)); ?>FontFamily"
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
                        name="<?php echo strtolower(str_replace('hero', '', $prefix)); ?>FontWeight"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <?php foreach ($fontWeights as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="<?php echo $prefix; ?>FontStyle" class="block text-xs font-medium text-gray-600 mb-1">סגנון גופן</label>
                <select id="<?php echo $prefix; ?>FontStyle" 
                        name="<?php echo strtolower(str_replace('hero', '', $prefix)); ?>FontStyle"
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
                       name="<?php echo strtolower(str_replace('hero', '', $prefix)); ?>LineHeight"
                       min="0.5" max="5" step="0.1"
                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                       placeholder="1.2">
            </div>
            
            <div>
                <label for="<?php echo $prefix; ?>LetterSpacing" class="block text-xs font-medium text-gray-600 mb-1">מרווח אותיות (px)</label>
                <input type="number" 
                       id="<?php echo $prefix; ?>LetterSpacing" 
                       name="<?php echo strtolower(str_replace('hero', '', $prefix)); ?>LetterSpacing"
                       min="-5" max="20" step="0.1"
                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                       placeholder="0">
            </div>
        </div>
        
        <!-- Text Decoration & Transform -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="<?php echo $prefix; ?>TextDecoration" class="block text-xs font-medium text-gray-600 mb-1">עיטור טקסט</label>
                <select id="<?php echo $prefix; ?>TextDecoration" 
                        name="<?php echo strtolower(str_replace('hero', '', $prefix)); ?>TextDecoration"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <?php foreach ($textDecorations as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="<?php echo $prefix; ?>TextTransform" class="block text-xs font-medium text-gray-600 mb-1">המרת אותיות</label>
                <select id="<?php echo $prefix; ?>TextTransform" 
                        name="<?php echo strtolower(str_replace('hero', '', $prefix)); ?>TextTransform"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <?php foreach ($textTransforms as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    
    <?php
}

/**
 * רינדור בוחר גודל גופן עם תמיכה רספונסיבית
 */
function renderResponsiveFontSize($prefix, $label) {
    ?>
    <div class="responsive-font-size">
        <label class="block text-xs font-medium text-gray-600 mb-2"><?php echo esc_html($label); ?></label>
        
        <!-- Desktop -->
        <div class="mb-2">
            <div class="flex items-center gap-2 mb-1">
                <i class="ri-computer-line text-gray-500"></i>
                <span class="text-xs text-gray-600">מחשב</span>
            </div>
            <input type="number" 
                   id="<?php echo $prefix; ?>FontSize" 
                   name="<?php echo strtolower(str_replace('hero', '', $prefix)); ?>FontSize"
                   min="8" max="200" step="1"
                   class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                   placeholder="16">
        </div>
        
        <!-- Tablet -->
        <div class="mb-2">
            <div class="flex items-center gap-2 mb-1">
                <i class="ri-tablet-line text-gray-500"></i>
                <span class="text-xs text-gray-600">טאבלט</span>
            </div>
            <input type="number" 
                   id="<?php echo $prefix; ?>FontSize_tablet" 
                   name="<?php echo strtolower(str_replace('hero', '', $prefix)); ?>FontSize_tablet"
                   min="8" max="200" step="1"
                   class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                   placeholder="ברירת מחדל מחשב">
        </div>
        
        <!-- Mobile -->
        <div>
            <div class="flex items-center gap-2 mb-1">
                <i class="ri-smartphone-line text-gray-500"></i>
                <span class="text-xs text-gray-600">מובייל</span>
            </div>
            <input type="number" 
                   id="<?php echo $prefix; ?>FontSize_mobile" 
                   name="<?php echo strtolower(str_replace('hero', '', $prefix)); ?>FontSize_mobile"
                   min="8" max="200" step="1"
                   class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                   placeholder="ברירת מחדל מחשב">
        </div>
    </div>
    <?php
}

/**
 * רינדור בוחר משפחת גופן עם תצוגה מקדימה
 */
function renderFontFamilyPicker($prefix, $label) {
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
    ?>
    
    <div class="font-family-picker">
        <label for="<?php echo $prefix; ?>FontFamily" class="block text-xs font-medium text-gray-600 mb-2"><?php echo esc_html($label); ?></label>
        
        <select id="<?php echo $prefix; ?>FontFamily" 
                name="<?php echo strtolower(str_replace('hero', '', $prefix)); ?>FontFamily"
                class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
            <?php foreach ($fontFamilies as $value => $displayLabel): ?>
                <option value="<?php echo esc_attr($value); ?>" 
                        style="font-family: '<?php echo esc_attr($value); ?>', sans-serif;">
                    <?php echo esc_html($displayLabel); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <!-- Preview -->
        <div class="mt-2 p-2 bg-gray-50 rounded text-sm" id="<?php echo $prefix; ?>FontPreview">
            <span style="font-family: 'Noto Sans Hebrew', sans-serif;">דוגמת טקסט - The quick brown fox</span>
        </div>
    </div>
    
    <script>
    document.getElementById('<?php echo $prefix; ?>FontFamily').addEventListener('change', function() {
        const preview = document.getElementById('<?php echo $prefix; ?>FontPreview');
        const selectedFont = this.value;
        preview.querySelector('span').style.fontFamily = `"${selectedFont}", sans-serif`;
    });
    </script>
    
    <?php
}

/**
 * רינדור בקרי כותרת עם תגי HTML
 */
function renderHeadingControls($prefix, $label) {
    $headingTags = [
        'h1' => 'כותרת ראשית (H1)',
        'h2' => 'כותרת משנית (H2)', 
        'h3' => 'כותרת משלישית (H3)',
        'h4' => 'כותרת רביעית (H4)',
        'h5' => 'כותרת חמישית (H5)',
        'h6' => 'כותרת שישית (H6)',
        'p' => 'פסקה (P)',
        'span' => 'טקסט פשוט (SPAN)',
        'div' => 'תיבת תוכן (DIV)'
    ];
    ?>
    
    <div class="heading-controls">
        <label for="<?php echo $prefix; ?>Tag" class="block text-xs font-medium text-gray-600 mb-2"><?php echo esc_html($label); ?> - תג HTML</label>
        
        <select id="<?php echo $prefix; ?>Tag" 
                name="<?php echo strtolower(str_replace('hero', '', $prefix)); ?>Tag"
                class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 mb-3">
            <?php foreach ($headingTags as $value => $displayLabel): ?>
                <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($displayLabel); ?></option>
            <?php endforeach; ?>
        </select>
        
        <!-- Typography controls -->
        <?php renderTypographyControls($prefix, $label); ?>
    </div>
    
    <?php
}
?>