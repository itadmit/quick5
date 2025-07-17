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
                    <option value="rem">rem</option>
                    <option value="vw">vw</option>
                </select>
            </div>
        </div>
        
        <!-- Height Control -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">גובה</label>
            <div class="grid grid-cols-2 gap-2 mb-3">
                <button type="button" class="height-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors active" data-height="auto">
                    אוטומטי
                </button>
                <button type="button" class="height-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-height="custom">
                    מותאם
                </button>
            </div>
            
            <!-- Custom Height Controls -->
            <div id="customHeightControls" class="hidden grid grid-cols-2 gap-3">
                <input type="number" 
                       id="<?php echo $prefix; ?>CustomHeight" 
                       name="customHeight"
                       placeholder="400"
                       class="px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select id="<?php echo $prefix; ?>CustomHeightUnit" 
                        name="customHeightUnit"
                        class="px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="px">px</option>
                    <option value="vh">vh</option>
                    <option value="rem">rem</option>
                    <option value="%">%</option>
                </select>
            </div>
        </div>
        
        <!-- Alignment -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">יישור אופקי</label>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" class="align-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-align="right">
                    <i class="ri-align-right"></i>
                    ימין
                </button>
                <button type="button" class="align-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors active" data-align="center">
                    <i class="ri-align-center"></i>
                    מרכז
                </button>
                <button type="button" class="align-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-align="left">
                    <i class="ri-align-left"></i>
                    שמאל
                </button>
            </div>
        </div>
        
        <!-- Vertical Alignment -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">יישור אנכי</label>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" class="valign-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-valign="top">
                    <i class="ri-arrow-up-line"></i>
                    למעלה
                </button>
                <button type="button" class="valign-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors active" data-valign="center">
                    <i class="ri-subtract-line"></i>
                    מרכז
                </button>
                <button type="button" class="valign-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-valign="bottom">
                    <i class="ri-arrow-down-line"></i>
                    למטה
                </button>
            </div>
        </div>
    </div>
    
    <script>
    // Layout controls logic
    (function() {
        // Width controls
        const widthBtns = document.querySelectorAll('.width-btn');
        const customWidthControls = document.getElementById('customWidthControls');
        
        widthBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const width = this.dataset.width;
                
                // Update active button
                widthBtns.forEach(b => b.classList.remove('active', 'bg-blue-500', 'text-white'));
                this.classList.add('active', 'bg-blue-500', 'text-white');
                
                // Show/hide custom controls
                if (width === 'custom') {
                    customWidthControls.classList.remove('hidden');
                } else {
                    customWidthControls.classList.add('hidden');
                }
            });
        });
        
        // Height controls
        const heightBtns = document.querySelectorAll('.height-btn');
        const customHeightControls = document.getElementById('customHeightControls');
        
        heightBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const height = this.dataset.height;
                
                // Update active button
                heightBtns.forEach(b => b.classList.remove('active', 'bg-blue-500', 'text-white'));
                this.classList.add('active', 'bg-blue-500', 'text-white');
                
                // Show/hide custom controls
                if (height === 'custom') {
                    customHeightControls.classList.remove('hidden');
                } else {
                    customHeightControls.classList.add('hidden');
                }
            });
        });
        
        // Alignment controls
        const alignBtns = document.querySelectorAll('.align-btn');
        alignBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                alignBtns.forEach(b => b.classList.remove('active', 'bg-blue-500', 'text-white'));
                this.classList.add('active', 'bg-blue-500', 'text-white');
            });
        });
        
        // Vertical alignment controls
        const valignBtns = document.querySelectorAll('.valign-btn');
        valignBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                valignBtns.forEach(b => b.classList.remove('active', 'bg-blue-500', 'text-white'));
                this.classList.add('active', 'bg-blue-500', 'text-white');
            });
        });
    })();
    </script>
    <?php
}
?> 