<?php
/**
 * Colors Component - רכיב הגדרות צבעים
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
 * רינדור בוחר צבע
 */
function renderColorPicker($id, $label, $defaultValue = '#000000') {
    ?>
    <div class="color-picker">
        <label for="<?php echo esc_attr($id); ?>" class="block text-xs font-medium text-gray-600 mb-2">
            <?php echo esc_html($label); ?>
        </label>
        <div class="flex items-center gap-3">
            <input type="color" 
                   id="<?php echo esc_attr($id); ?>" 
                   name="<?php echo esc_attr(strtolower(str_replace('hero', '', $id))); ?>Color"
                   value="<?php echo esc_attr($defaultValue); ?>"
                   class="w-12 h-8 border border-gray-300 rounded cursor-pointer">
            <input type="text" 
                   id="<?php echo esc_attr($id); ?>_text" 
                   value="<?php echo esc_attr($defaultValue); ?>"
                   class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                   placeholder="#000000">
        </div>
    </div>
    
    <script>
    // Sync color picker with text input
    (function() {
        const colorPicker = document.getElementById('<?php echo esc_attr($id); ?>');
        const textInput = document.getElementById('<?php echo esc_attr($id); ?>_text');
        
        if (colorPicker && textInput) {
            colorPicker.addEventListener('input', function() {
                textInput.value = this.value;
                textInput.dispatchEvent(new Event('input', { bubbles: true }));
            });
            
            textInput.addEventListener('input', function() {
                if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                    colorPicker.value = this.value;
                }
            });
        }
    })();
    </script>
    <?php
}

/**
 * רינדור בוחר צבע עם אפשרות שקיפות
 */
function renderColorPickerWithOpacity($id, $label, $defaultValue = '#000000', $defaultOpacity = 1) {
    ?>
    <div class="color-picker-with-opacity">
        <label class="block text-xs font-medium text-gray-600 mb-2">
            <?php echo esc_html($label); ?>
        </label>
        
        <div class="grid grid-cols-2 gap-3">
            <!-- Color -->
            <div>
                <div class="flex items-center gap-2">
                    <input type="color" 
                           id="<?php echo esc_attr($id); ?>" 
                           name="<?php echo esc_attr(strtolower(str_replace('hero', '', $id))); ?>Color"
                           value="<?php echo esc_attr($defaultValue); ?>"
                           class="w-8 h-8 border border-gray-300 rounded cursor-pointer">
                    <input type="text" 
                           id="<?php echo esc_attr($id); ?>_text" 
                           value="<?php echo esc_attr($defaultValue); ?>"
                           class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                           placeholder="#000000">
                </div>
            </div>
            
            <!-- Opacity -->
            <div>
                <label class="block text-xs text-gray-500 mb-1">שקיפות</label>
                <input type="range" 
                       id="<?php echo esc_attr($id); ?>Opacity" 
                       name="<?php echo esc_attr(strtolower(str_replace('hero', '', $id))); ?>Opacity"
                       min="0" max="1" step="0.01" 
                       value="<?php echo esc_attr($defaultOpacity); ?>"
                       class="w-full">
                <div class="flex justify-between text-xs text-gray-400">
                    <span>0%</span>
                    <span>100%</span>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * רינדור פלטת צבעים מוכנה
 */
function renderColorPalette($prefix) {
    $colors = [
        '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
        '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1',
        '#000000', '#374151', '#6B7280', '#9CA3AF', '#FFFFFF'
    ];
    ?>
    <div class="color-palette">
        <label class="block text-xs font-medium text-gray-600 mb-2">צבעים מוכנים</label>
        <div class="grid grid-cols-5 gap-2">
            <?php foreach ($colors as $color): ?>
                <button type="button" 
                        class="palette-color w-8 h-8 rounded border border-gray-300 hover:scale-110 transition-transform"
                        style="background-color: <?php echo esc_attr($color); ?>"
                        data-color="<?php echo esc_attr($color); ?>"
                        title="<?php echo esc_attr($color); ?>">
                </button>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
    // Color palette selection
    (function() {
        const paletteColors = document.querySelectorAll('.palette-color');
        
        paletteColors.forEach(colorBtn => {
            colorBtn.addEventListener('click', function() {
                const color = this.dataset.color;
                
                // Apply to active color input
                const activeColorInput = document.querySelector('input[type="color"]:focus') || 
                                       document.querySelector('input[type="color"]');
                                       
                if (activeColorInput) {
                    activeColorInput.value = color;
                    activeColorInput.dispatchEvent(new Event('input', { bubbles: true }));
                    
                    // Also update text input if exists
                    const textInput = document.getElementById(activeColorInput.id + '_text');
                    if (textInput) {
                        textInput.value = color;
                    }
                }
            });
        });
    })();
    </script>
    <?php
}
?> 