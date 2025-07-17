<?php
/**
 * Spacing Component - רכיב הגדרות מרווחים
 */

/**
 * רינדור בקרי מרווחים
 */
function renderSpacingControls($prefix, $defaultData = []) {
    ?>
    <!-- Spacing Section -->
    <div class="spacing-controls space-y-4">
        <!-- Padding -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">פדינג (px)</label>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <input type="number" id="<?php echo $prefix; ?>PaddingTop" name="paddingTop" 
                           value="<?php echo $defaultData['paddingTop'] ?? '80'; ?>" placeholder="למעלה" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <input type="number" id="<?php echo $prefix; ?>PaddingBottom" name="paddingBottom" 
                           value="<?php echo $defaultData['paddingBottom'] ?? '80'; ?>" placeholder="למטה" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <input type="number" id="<?php echo $prefix; ?>PaddingRight" name="paddingRight" 
                           value="<?php echo $defaultData['paddingRight'] ?? '20'; ?>" placeholder="ימין" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <input type="number" id="<?php echo $prefix; ?>PaddingLeft" name="paddingLeft" 
                           value="<?php echo $defaultData['paddingLeft'] ?? '20'; ?>" placeholder="שמאל" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
        
        <!-- Margin -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">מרגין (px)</label>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <input type="number" id="<?php echo $prefix; ?>MarginTop" name="marginTop" 
                           value="<?php echo $defaultData['marginTop'] ?? '0'; ?>" placeholder="למעלה" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <input type="number" id="<?php echo $prefix; ?>MarginBottom" name="marginBottom" 
                           value="<?php echo $defaultData['marginBottom'] ?? '0'; ?>" placeholder="למטה" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <input type="number" id="<?php echo $prefix; ?>MarginRight" name="marginRight" 
                           value="<?php echo $defaultData['marginRight'] ?? '0'; ?>" placeholder="ימין" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <input type="number" id="<?php echo $prefix; ?>MarginLeft" name="marginLeft" 
                           value="<?php echo $defaultData['marginLeft'] ?? '0'; ?>" placeholder="שמאל" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
        
        <!-- Quick Spacing Presets -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">הגדרות מהירות</label>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" class="spacing-preset px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-preset="none">
                    ללא מרווחים
                </button>
                <button type="button" class="spacing-preset px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-preset="small">
                    מרווח קטן
                </button>
                <button type="button" class="spacing-preset px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-preset="medium">
                    מרווח בינוני
                </button>
                <button type="button" class="spacing-preset px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-preset="large">
                    מרווח גדול
                </button>
                <button type="button" class="spacing-preset px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-preset="xlarge">
                    מרווח ענק
                </button>
                <button type="button" class="spacing-preset px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-preset="custom">
                    מותאם אישית
                </button>
            </div>
        </div>
    </div>
    
    <script>
    // Spacing controls logic
    (function() {
        const presetBtns = document.querySelectorAll('.spacing-preset');
        const paddingInputs = {
            top: document.getElementById('<?php echo $prefix; ?>PaddingTop'),
            bottom: document.getElementById('<?php echo $prefix; ?>PaddingBottom'),
            right: document.getElementById('<?php echo $prefix; ?>PaddingRight'),
            left: document.getElementById('<?php echo $prefix; ?>PaddingLeft')
        };
        const marginInputs = {
            top: document.getElementById('<?php echo $prefix; ?>MarginTop'),
            bottom: document.getElementById('<?php echo $prefix; ?>MarginBottom'),
            right: document.getElementById('<?php echo $prefix; ?>MarginRight'),
            left: document.getElementById('<?php echo $prefix; ?>MarginLeft')
        };
        
        const presets = {
            none: { padding: [0, 0, 0, 0], margin: [0, 0, 0, 0] },
            small: { padding: [20, 20, 10, 10], margin: [10, 10, 0, 0] },
            medium: { padding: [40, 40, 20, 20], margin: [20, 20, 0, 0] },
            large: { padding: [80, 80, 40, 40], margin: [40, 40, 0, 0] },
            xlarge: { padding: [120, 120, 60, 60], margin: [60, 60, 0, 0] }
        };
        
        presetBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const preset = this.dataset.preset;
                
                if (preset === 'custom') {
                    return;
                }
                
                if (presets[preset]) {
                    const { padding, margin } = presets[preset];
                    
                    paddingInputs.top.value = padding[0];
                    paddingInputs.bottom.value = padding[1];
                    paddingInputs.right.value = padding[2];
                    paddingInputs.left.value = padding[3];
                    
                    marginInputs.top.value = margin[0];
                    marginInputs.bottom.value = margin[1];
                    marginInputs.right.value = margin[2];
                    marginInputs.left.value = margin[3];
                    
                    Object.values(paddingInputs).forEach(input => {
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                    Object.values(marginInputs).forEach(input => {
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                }
                
                presetBtns.forEach(b => b.classList.remove('bg-blue-500', 'text-white'));
                this.classList.add('bg-blue-500', 'text-white');
            });
        });
    })();
    </script>
    <?php
}
?> 