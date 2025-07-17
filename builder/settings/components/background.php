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
                        <label for="<?php echo $prefix; ?>BgImageSize" class="block text-sm font-medium text-gray-700 mb-2">גודל תמונה</label>
                        <select id="<?php echo $prefix; ?>BgImageSize" 
                                name="bgImageSize"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="cover">כיסוי מלא</option>
                            <option value="contain">התאמה לקונטיינר</option>
                            <option value="auto">גודל מקורי</option>
                            <option value="100% 100%">מתיחה מלאה</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="<?php echo $prefix; ?>BgImagePosition" class="block text-sm font-medium text-gray-700 mb-2">מיקום תמונה</label>
                        <select id="<?php echo $prefix; ?>BgImagePosition" 
                                name="bgImagePosition"
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
                    <label for="<?php echo $prefix; ?>BgImageRepeat" class="block text-sm font-medium text-gray-700 mb-2">חזרת תמונה</label>
                    <select id="<?php echo $prefix; ?>BgImageRepeat" 
                            name="bgImageRepeat"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="no-repeat">ללא חזרה</option>
                        <option value="repeat">חזרה בכל הכיוונים</option>
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
                    <label for="<?php echo $prefix; ?>BgVideo" class="block text-sm font-medium text-gray-700 mb-2">וידאו למחשב</label>
                    <input type="url" 
                           id="<?php echo $prefix; ?>BgVideo" 
                           name="bgVideo"
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="https://example.com/video.mp4">
                </div>
                
                <div>
                    <label for="<?php echo $prefix; ?>BgTabletVideo" class="block text-sm font-medium text-gray-700 mb-2">וידאו לטאבלט (אופציונלי)</label>
                    <input type="url" 
                           id="<?php echo $prefix; ?>BgTabletVideo" 
                           name="bgTabletVideo"
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="https://example.com/video-tablet.mp4">
                </div>
                
                <div>
                    <label for="<?php echo $prefix; ?>BgMobileVideo" class="block text-sm font-medium text-gray-700 mb-2">וידאו למובייל (אופציונלי)</label>
                    <input type="url" 
                           id="<?php echo $prefix; ?>BgMobileVideo" 
                           name="bgMobileVideo"
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="https://example.com/video-mobile.mp4">
                </div>
            </div>
        </div>
        
        <!-- Background Overlay -->
        <div class="mt-6 pt-4 border-t border-gray-200">
            <div class="space-y-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" id="<?php echo $prefix; ?>BgOverlay" name="bgOverlay">
                    <span class="text-sm font-medium text-gray-700">הוסף שכבת רקע</span>
                </label>
                
                <div id="overlayControls" class="hidden space-y-3">
                    <div>
                        <label for="<?php echo $prefix; ?>BgOverlayColor" class="block text-sm font-medium text-gray-700 mb-2">צבע שכבה</label>
                        <input type="color" 
                               id="<?php echo $prefix; ?>BgOverlayColor" 
                               name="bgOverlayColor"
                               value="#000000"
                               class="w-full h-8 border border-gray-300 rounded cursor-pointer">
                    </div>
                    
                    <div>
                        <label for="<?php echo $prefix; ?>BgOverlayOpacity" class="block text-sm font-medium text-gray-700 mb-2">שקיפות שכבה</label>
                        <input type="range" 
                               id="<?php echo $prefix; ?>BgOverlayOpacity" 
                               name="bgOverlayOpacity"
                               min="0" max="1" step="0.1" value="0.4"
                               class="w-full">
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>שקוף</span>
                            <span>אטום</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hidden input for background type -->
        <input type="hidden" id="<?php echo $prefix; ?>BgType" name="bgType" value="color">
    </div>
    
    <script>
    (function() {
        const prefix = '<?php echo $prefix; ?>';
        
        // Background type switcher
        const bgTypeButtons = document.querySelectorAll('.bg-type-btn');
        const bgPanels = document.querySelectorAll('.bg-panel');
        const bgTypeInput = document.getElementById(prefix + 'BgType');
        
        bgTypeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const bgType = this.dataset.bgType;
                
                // Update active button
                bgTypeButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Show/hide panels
                bgPanels.forEach(panel => panel.classList.add('hidden'));
                const targetPanel = document.getElementById('bg' + bgType.charAt(0).toUpperCase() + bgType.slice(1) + 'Controls');
                if (targetPanel) {
                    targetPanel.classList.remove('hidden');
                }
                
                // Update hidden input
                if (bgTypeInput) {
                    bgTypeInput.value = bgType;
                    bgTypeInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
        
        // Color picker sync
        const colorPicker = document.getElementById(prefix + 'BgColor');
        const colorText = document.getElementById(prefix + 'BgColorText');
        
        if (colorPicker && colorText) {
            colorPicker.addEventListener('input', function() {
                colorText.value = this.value;
                colorText.dispatchEvent(new Event('input', { bubbles: true }));
            });
            
            colorText.addEventListener('input', function() {
                if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                    colorPicker.value = this.value;
                    colorPicker.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        }
        
        // Overlay toggle
        const overlayCheckbox = document.getElementById(prefix + 'BgOverlay');
        const overlayControls = document.getElementById('overlayControls');
        
        if (overlayCheckbox && overlayControls) {
            overlayCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    overlayControls.classList.remove('hidden');
                } else {
                    overlayControls.classList.add('hidden');
                }
            });
        }
    })();
    </script>
    <?php
}
?> 