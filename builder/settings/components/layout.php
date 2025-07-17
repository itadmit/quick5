<?php
/**
 * Layout Component - רכיב הגדרות פריסה
 */

/**
 * רינדור בקרי פריסה
 */
function renderLayoutControls($prefix) {
    ?>
    <div class="layout-controls space-y-4">
        <!-- Container Type -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">סוג קונטיינר</label>
            <select id="<?php echo $prefix; ?>ContainerType" 
                    name="containerType"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="container">קונטיינר רגיל</option>
                <option value="container-fluid">קונטיינר מלא</option>
                <option value="none">ללא קונטיינר</option>
            </select>
        </div>
        
        <!-- Width Control -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">רוחב</label>
            <div class="grid grid-cols-3 gap-2 mb-3">
                <button type="button" class="width-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors active" data-width="full">
                    מלא
                </button>
                <button type="button" class="width-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-width="contained">
                    מוגבל
                </button>
                <button type="button" class="width-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-width="custom">
                    מותאם
                </button>
            </div>
            
            <!-- Custom Width Controls -->
            <div id="customWidthControls" class="hidden grid grid-cols-2 gap-3">
                <input type="number" 
                       id="<?php echo $prefix; ?>CustomWidth" 
                       name="customWidth"
                       placeholder="800"
                       class="px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select id="<?php echo $prefix; ?>CustomWidthUnit" 
                        name="customWidthUnit"
                        class="px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="px">px</option>
                    <option value="%">%</option>
                    <option value="vw">vw</option>
                    <option value="rem">rem</option>
                </select>
            </div>
            
            <input type="hidden" id="<?php echo $prefix; ?>WidthType" name="widthType" value="full">
        </div>
        
        <!-- Height Control -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">גובה</label>
            <div class="space-y-3">
                <div class="grid grid-cols-3 gap-2">
                    <label class="flex items-center gap-2 p-2 border border-gray-300 rounded cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="<?php echo $prefix; ?>HeightType" value="auto" checked>
                        <span class="text-sm">אוטומטי</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 border border-gray-300 rounded cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="<?php echo $prefix; ?>HeightType" value="fixed">
                        <span class="text-sm">מסך מלא</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 border border-gray-300 rounded cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="<?php echo $prefix; ?>HeightType" value="custom">
                        <span class="text-sm">מותאם</span>
                    </label>
                </div>
                
                <!-- Custom Height Input -->
                <div id="customHeightControls" class="hidden">
                    <div class="grid grid-cols-2 gap-3">
                        <input type="number" 
                               id="<?php echo $prefix; ?>HeightValue" 
                               name="heightValue"
                               placeholder="500"
                               class="px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <select id="<?php echo $prefix; ?>HeightUnit" 
                                name="heightUnit"
                                class="px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="px">px</option>
                            <option value="vh">vh</option>
                            <option value="rem">rem</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Min/Max Height -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="<?php echo $prefix; ?>MinHeight" class="block text-sm font-medium text-gray-700 mb-2">גובה מינימלי</label>
                <input type="number" 
                       id="<?php echo $prefix; ?>MinHeight" 
                       name="minHeight"
                       placeholder="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label for="<?php echo $prefix; ?>MaxHeight" class="block text-sm font-medium text-gray-700 mb-2">גובה מקסימלי</label>
                <input type="number" 
                       id="<?php echo $prefix; ?>MaxHeight" 
                       name="maxHeight"
                       placeholder="ללא הגבלה"
                       class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        
        <!-- Text Alignment -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">יישור טקסט</label>
            <div class="grid grid-cols-4 gap-2">
                <button type="button" class="text-align-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-align="right">
                    <i class="ri-align-right"></i>
                </button>
                <button type="button" class="text-align-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors active" data-align="center">
                    <i class="ri-align-center"></i>
                </button>
                <button type="button" class="text-align-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-align="left">
                    <i class="ri-align-left"></i>
                </button>
                <button type="button" class="text-align-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-align="justify">
                    <i class="ri-align-justify"></i>
                </button>
            </div>
            <input type="hidden" id="<?php echo $prefix; ?>TextAlign" name="textAlign" value="center">
        </div>
        
        <!-- Vertical Alignment -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">יישור אנכי</label>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" class="vertical-align-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-align="top">
                    למעלה
                </button>
                <button type="button" class="vertical-align-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors active" data-align="center">
                    מרכז
                </button>
                <button type="button" class="vertical-align-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-align="bottom">
                    למטה
                </button>
            </div>
            <input type="hidden" id="<?php echo $prefix; ?>VerticalAlign" name="verticalAlign" value="center">
        </div>
    </div>
    
    <script>
    (function() {
        const prefix = '<?php echo $prefix; ?>';
        
        // Width type buttons
        const widthButtons = document.querySelectorAll('.width-btn');
        const customWidthControls = document.getElementById('customWidthControls');
        const widthTypeInput = document.getElementById(prefix + 'WidthType');
        
        widthButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const widthType = this.dataset.width;
                
                // Update active button
                widthButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Show/hide custom controls
                if (widthType === 'custom') {
                    customWidthControls.classList.remove('hidden');
                } else {
                    customWidthControls.classList.add('hidden');
                }
                
                // Update hidden input
                if (widthTypeInput) {
                    widthTypeInput.value = widthType;
                    widthTypeInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
        
        // Height type radios
        const heightRadios = document.querySelectorAll('input[name="' + prefix + 'HeightType"]');
        const customHeightControls = document.getElementById('customHeightControls');
        
        heightRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customHeightControls.classList.remove('hidden');
                } else {
                    customHeightControls.classList.add('hidden');
                }
            });
        });
        
        // Text alignment buttons
        const textAlignButtons = document.querySelectorAll('.text-align-btn');
        const textAlignInput = document.getElementById(prefix + 'TextAlign');
        
        textAlignButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const align = this.dataset.align;
                
                textAlignButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                if (textAlignInput) {
                    textAlignInput.value = align;
                    textAlignInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
        
        // Vertical alignment buttons
        const verticalAlignButtons = document.querySelectorAll('.vertical-align-btn');
        const verticalAlignInput = document.getElementById(prefix + 'VerticalAlign');
        
        verticalAlignButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const align = this.dataset.align;
                
                verticalAlignButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                if (verticalAlignInput) {
                    verticalAlignInput.value = align;
                    verticalAlignInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
    })();
    </script>
    <?php
}
?> 