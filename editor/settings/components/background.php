<?php
/**
 * Background Component - רכיב הגדרות רקע
 */

/**
 * רינדור בקרי רקע
 */
function renderBackgroundControls($prefix) {
    ?>
    <div class="background-controls">
        <!-- Background Type Selector -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-3">סוג רקע</label>
            <div class="grid grid-cols-4 gap-2">
                <button type="button" class="bg-type-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors active" data-bg-type="color">
                    <i class="ri-palette-line mb-1"></i>
                    <div>צבע</div>
                </button>
                <button type="button" class="bg-type-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-bg-type="gradient">
                    <i class="ri-contrast-line mb-1"></i>
                    <div>גרדיאנט</div>
                </button>
                <button type="button" class="bg-type-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-bg-type="image">
                    <i class="ri-image-line mb-1"></i>
                    <div>תמונה</div>
                </button>
                <button type="button" class="bg-type-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-bg-type="video">
                    <i class="ri-video-line mb-1"></i>
                    <div>וידאו</div>
                </button>
            </div>
        </div>
        
        <!-- Color Background -->
        <div id="bgColorControls" class="bg-panel">
            <label for="<?php echo $prefix; ?>BgColor" class="block text-sm font-medium text-gray-700 mb-2">צבע רקע</label>
            <div class="flex items-center gap-3">
                <input type="color" 
                       id="<?php echo $prefix; ?>BgColor" 
                       name="bgColor"
                       value="#3B82F6"
                       class="w-12 h-8 border border-gray-300 rounded cursor-pointer">
                <input type="text" 
                       id="<?php echo $prefix; ?>BgColorText" 
                       value="#3B82F6"
                       class="flex-1 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="#3B82F6">
            </div>
        </div>
        
        <!-- Gradient Background -->
        <div id="bgGradientControls" class="bg-panel hidden">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="<?php echo $prefix; ?>BgGradient1" class="block text-sm font-medium text-gray-700 mb-2">צבע ראשון</label>
                        <input type="color" 
                               id="<?php echo $prefix; ?>BgGradient1" 
                               name="bgGradient1"
                               value="#3B82F6"
                               class="w-full h-8 border border-gray-300 rounded cursor-pointer">
                    </div>
                    <div>
                        <label for="<?php echo $prefix; ?>BgGradient2" class="block text-sm font-medium text-gray-700 mb-2">צבע שני</label>
                        <input type="color" 
                               id="<?php echo $prefix; ?>BgGradient2" 
                               name="bgGradient2"
                               value="#1E40AF"
                               class="w-full h-8 border border-gray-300 rounded cursor-pointer">
                    </div>
                </div>
                
                <div>
                    <label for="<?php echo $prefix; ?>BgGradientDirection" class="block text-sm font-medium text-gray-700 mb-2">כיוון גרדיאנט</label>
                    <select id="<?php echo $prefix; ?>BgGradientDirection" 
                            name="bgGradientDirection"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="to-b">מלמעלה למטה</option>
                        <option value="to-t">מלמטה למעלה</option>
                        <option value="to-r">משמאל לימין</option>
                        <option value="to-l">מימין לשמאל</option>
                        <option value="to-br">אלכסון למטה ימין</option>
                        <option value="to-bl">אלכסון למטה שמאל</option>
                        <option value="to-tr">אלכסון למעלה ימין</option>
                        <option value="to-tl">אלכסון למעלה שמאל</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Image Background -->
        <div id="bgImageControls" class="bg-panel hidden">
            <div class="space-y-4">
                <div>
                    <label for="<?php echo $prefix; ?>BgImage" class="block text-sm font-medium text-gray-700 mb-2">URL תמונה</label>
                    <input type="url" 
                           id="<?php echo $prefix; ?>BgImage" 
                           name="bgImage"
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="https://example.com/image.jpg">
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="<?php echo $prefix; ?>BgSize" class="block text-sm font-medium text-gray-700 mb-2">גודל תמונה</label>
                        <select id="<?php echo $prefix; ?>BgSize" 
                                name="bgSize"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="cover">כיסוי מלא</option>
                            <option value="contain">התאמה</option>
                            <option value="auto">אוטומטי</option>
                            <option value="100% 100%">מתיחה</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="<?php echo $prefix; ?>BgPosition" class="block text-sm font-medium text-gray-700 mb-2">מיקום תמונה</label>
                        <select id="<?php echo $prefix; ?>BgPosition" 
                                name="bgPosition"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="center">מרכז</option>
                            <option value="top">למעלה</option>
                            <option value="bottom">למטה</option>
                            <option value="left">שמאל</option>
                            <option value="right">ימין</option>
                            <option value="top left">למעלה שמאל</option>
                            <option value="top right">למעלה ימין</option>
                            <option value="bottom left">למטה שמאל</option>
                            <option value="bottom right">למטה ימין</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label for="<?php echo $prefix; ?>BgRepeat" class="block text-sm font-medium text-gray-700 mb-2">חזרה</label>
                    <select id="<?php echo $prefix; ?>BgRepeat" 
                            name="bgRepeat"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="no-repeat">ללא חזרה</option>
                        <option value="repeat">חזרה</option>
                        <option value="repeat-x">חזרה אופקית</option>
                        <option value="repeat-y">חזרה אנכית</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Video Background -->
        <div id="bgVideoControls" class="bg-panel hidden">
            <div class="space-y-4">
                <div>
                    <label for="<?php echo $prefix; ?>BgVideo" class="block text-sm font-medium text-gray-700 mb-2">URL וידאו</label>
                    <input type="url" 
                           id="<?php echo $prefix; ?>BgVideo" 
                           name="bgVideo"
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="https://example.com/video.mp4">
                </div>
                
                <div class="flex items-center gap-2">
                    <input type="checkbox" 
                           id="<?php echo $prefix; ?>BgVideoMuted" 
                           name="bgVideoMuted"
                           checked
                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                    <label for="<?php echo $prefix; ?>BgVideoMuted" class="text-sm text-gray-700">השתק וידאו</label>
                </div>
                
                <div class="flex items-center gap-2">
                    <input type="checkbox" 
                           id="<?php echo $prefix; ?>BgVideoLoop" 
                           name="bgVideoLoop"
                           checked
                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                    <label for="<?php echo $prefix; ?>BgVideoLoop" class="text-sm text-gray-700">חזרה אוטומטית</label>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Background controls logic
    (function() {
        const bgTypeBtns = document.querySelectorAll('.bg-type-btn');
        const bgPanels = document.querySelectorAll('.bg-panel');
        
        bgTypeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const bgType = this.dataset.bgType;
                
                // Update active button
                bgTypeBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Show/hide panels
                bgPanels.forEach(panel => panel.classList.add('hidden'));
                const targetPanel = document.getElementById('bg' + bgType.charAt(0).toUpperCase() + bgType.slice(1) + 'Controls');
                if (targetPanel) {
                    targetPanel.classList.remove('hidden');
                }
            });
        });
        
        // Color input sync
        const colorInput = document.getElementById('<?php echo $prefix; ?>BgColor');
        const colorText = document.getElementById('<?php echo $prefix; ?>BgColorText');
        
        if (colorInput && colorText) {
            colorInput.addEventListener('input', function() {
                colorText.value = this.value;
            });
            
            colorText.addEventListener('input', function() {
                if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                    colorInput.value = this.value;
                }
            });
        }
    })();
    </script>
    <?php
}
?> 