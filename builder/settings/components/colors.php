<?php
/**
 * Colors Component - רכיב הגדרות צבעים
 */

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
function renderColorPalette($id, $label) {
    $colors = [
        '#000000' => 'שחור',
        '#FFFFFF' => 'לבן', 
        '#F3F4F6' => 'אפור בהיר',
        '#6B7280' => 'אפור',
        '#374151' => 'אפור כהה',
        '#EF4444' => 'אדום',
        '#F59E0B' => 'כתום',
        '#10B981' => 'ירוק',
        '#3B82F6' => 'כחול',
        '#8B5CF6' => 'סגול',
        '#EC4899' => 'ורוד'
    ];
    ?>
    
    <div class="color-palette">
        <label class="block text-xs font-medium text-gray-600 mb-2">
            <?php echo esc_html($label); ?>
        </label>
        
        <div class="grid grid-cols-6 gap-2 mb-3">
            <?php foreach ($colors as $color => $name): ?>
                <button type="button" 
                        class="color-option w-8 h-8 rounded border-2 border-gray-300 hover:border-gray-400 transition-colors"
                        style="background-color: <?php echo esc_attr($color); ?>"
                        data-color="<?php echo esc_attr($color); ?>"
                        title="<?php echo esc_attr($name); ?>">
                </button>
            <?php endforeach; ?>
        </div>
        
        <!-- Custom color input -->
        <input type="color" 
               id="<?php echo esc_attr($id); ?>" 
               name="<?php echo esc_attr(strtolower(str_replace('hero', '', $id))); ?>Color"
               class="w-full h-8 border border-gray-300 rounded cursor-pointer">
    </div>
    
    <script>
    // Handle color palette clicks
    document.querySelectorAll('.color-option').forEach(button => {
        button.addEventListener('click', function() {
            const color = this.dataset.color;
            const input = document.getElementById('<?php echo esc_attr($id); ?>');
            if (input) {
                input.value = color;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    });
    </script>
    <?php
}
?> 