<?php
/**
 * Buttons Repeater Component - רכיב ריפיטר כפתורים
 */

/**
 * רינדור ריפיטר כפתורים
 */
function renderButtonsRepeater($inputId, $containerId, $options = []) {
    $defaultOptions = [
        'minButtons' => 1,
        'maxButtons' => 5,
        'showIcons' => true,
        'showHover' => true
    ];
    
    $options = array_merge($defaultOptions, $options);
    ?>
    
    <div class="buttons-repeater">
        <div class="flex items-center justify-between mb-3">
            <label class="block text-sm font-medium text-gray-700">כפתורים</label>
            <button type="button" 
                    class="add-button-btn px-3 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
                    data-add="<?php echo esc_attr($containerId); ?>">
                <i class="ri-add-line"></i>
                הוסף כפתור
            </button>
        </div>
        
        <!-- Buttons Container -->
        <div id="<?php echo esc_attr($containerId); ?>" class="space-y-3">
            <!-- Buttons will be dynamically generated here -->
        </div>
        
        <!-- Hidden input to store buttons data -->
        <input type="hidden" 
               id="<?php echo esc_attr($inputId); ?>" 
               name="buttons" 
               value="">
    </div>
    
    <script>
    // Initialize buttons repeater when DOM is ready
    (function() {
        // Wait for ButtonsRepeater class to be available
        function initButtonsRepeater() {
            if (typeof window.ButtonsRepeater !== 'undefined') {
                // Initialize the repeater
                const repeater = new window.ButtonsRepeater(
                    '<?php echo esc_js($containerId); ?>',
                    '<?php echo esc_js($inputId); ?>',
                    [], // Empty initial data - will be loaded by Hero.js
                    <?php echo json_encode($options); ?>
                );
                
                console.log('ButtonsRepeater initialized for <?php echo esc_js($containerId); ?>');
            } else {
                console.warn('ButtonsRepeater class not found, retrying in 100ms...');
                setTimeout(initButtonsRepeater, 100);
            }
        }
        
        // Try to initialize immediately or wait for DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initButtonsRepeater);
        } else {
            initButtonsRepeater();
        }
    })();
    </script>
    
    <?php
}

/**
 * רינדור כפתור יחיד בתבנית
 */
function renderSingleButtonTemplate() {
    ?>
    <template id="buttonItemTemplate">
        <div class="button-item border border-gray-200 rounded-lg p-4 bg-white">
            <div class="flex justify-between items-center mb-3">
                <h4 class="font-medium text-gray-700">כפתור</h4>
                <button type="button" class="text-red-500 hover:text-red-700 delete-button">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-1 gap-3">
                <!-- Text and Link -->
                <div class="grid grid-cols-2 gap-3">
                    <input type="text" placeholder="טקסט הכפתור" 
                           class="border border-gray-300 rounded px-3 py-2 text-sm">
                    <input type="text" placeholder="קישור" 
                           class="border border-gray-300 rounded px-3 py-2 text-sm">
                </div>
                
                <!-- Options -->
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox">
                        פתח בחלון חדש
                    </label>
                    
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox">
                        רוחב מלא
                    </label>
                </div>
                
                <!-- Button Style -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">סגנון</label>
                    <div class="flex gap-2">
                        <button type="button" class="btn-style-option px-3 py-1 text-xs border rounded" data-style="filled">מלא</button>
                        <button type="button" class="btn-style-option px-3 py-1 text-xs border rounded" data-style="outline">מסגרת</button>
                        <button type="button" class="btn-style-option px-3 py-1 text-xs border rounded" data-style="ghost">שקוף</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
    <?php
}
?> 