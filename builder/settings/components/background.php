<!-- Background Section -->
<div class="bg-gray-50 rounded-lg p-4">
    <div class="flex items-center gap-2 mb-3">
        <i class="ri-image-fill text-purple-600"></i>
        <h4 class="font-medium text-gray-900">רקע</h4>
    </div>
    
    <div class="space-y-4">
        <!-- Background Type -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">סוג רקע</label>
            <div class="grid grid-cols-4 gap-2">
                <button type="button" class="bg-type-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors <?php echo (!isset($defaultData['bgType']) || $defaultData['bgType'] === 'color') ? 'bg-blue-50 border-blue-300' : ''; ?>" data-bg-type="color">
                    <i class="ri-palette-line text-lg"></i>
                    <div class="text-xs mt-1">צבע</div>
                </button>
                <button type="button" class="bg-type-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors <?php echo (isset($defaultData['bgType']) && $defaultData['bgType'] === 'gradient') ? 'bg-blue-50 border-blue-300' : ''; ?>" data-bg-type="gradient">
                    <i class="ri-contrast-2-line text-lg"></i>
                    <div class="text-xs mt-1">גרדיאנט</div>
                </button>
                <button type="button" class="bg-type-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors <?php echo (isset($defaultData['bgType']) && $defaultData['bgType'] === 'image') ? 'bg-blue-50 border-blue-300' : ''; ?>" data-bg-type="image">
                    <i class="ri-image-line text-lg"></i>
                    <div class="text-xs mt-1">תמונה</div>
                </button>
                <button type="button" class="bg-type-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors <?php echo (isset($defaultData['bgType']) && $defaultData['bgType'] === 'video') ? 'bg-blue-50 border-blue-300' : ''; ?>" data-bg-type="video">
                    <i class="ri-video-line text-lg"></i>
                    <div class="text-xs mt-1">סרטון</div>
                </button>
            </div>
            <input type="hidden" id="<?php echo $sectionType; ?>BgType" name="bgType" value="<?php echo $defaultData['bgType'] ?? 'color'; ?>">
        </div>
        
        <!-- Background Color -->
        <div id="bgColorSection" class="<?php echo (isset($defaultData['bgType']) && $defaultData['bgType'] !== 'color') ? 'hidden' : ''; ?>">
            <label class="block text-sm font-medium text-gray-700 mb-1">צבע רקע</label>
            <input type="color" id="<?php echo $sectionType; ?>BgColor" name="bgColor" 
                   value="<?php echo $defaultData['bgColor'] ?? '#3B82F6'; ?>" 
                   class="w-full h-10 border border-gray-300 rounded-md">
        </div>
        
        <!-- Background Gradient -->
        <div id="bgGradientSection" class="<?php echo (!isset($defaultData['bgType']) || $defaultData['bgType'] !== 'gradient') ? 'hidden' : ''; ?> space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">צבע 1</label>
                    <input type="color" id="<?php echo $sectionType; ?>BgGradient1" name="bgGradient1" 
                           value="<?php echo $defaultData['bgGradient1'] ?? '#3B82F6'; ?>" 
                           class="w-full h-10 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">צבע 2</label>
                    <input type="color" id="<?php echo $sectionType; ?>BgGradient2" name="bgGradient2" 
                           value="<?php echo $defaultData['bgGradient2'] ?? '#1E40AF'; ?>" 
                           class="w-full h-10 border border-gray-300 rounded-md">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">כיוון</label>
                <select id="<?php echo $sectionType; ?>BgGradientDirection" name="bgGradientDirection" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="to-r" <?php echo (isset($defaultData['bgGradientDirection']) && $defaultData['bgGradientDirection'] === 'to-r') ? 'selected' : ''; ?>>שמאל לימין</option>
                    <option value="to-l" <?php echo (isset($defaultData['bgGradientDirection']) && $defaultData['bgGradientDirection'] === 'to-l') ? 'selected' : ''; ?>>ימין לשמאל</option>
                    <option value="to-t" <?php echo (isset($defaultData['bgGradientDirection']) && $defaultData['bgGradientDirection'] === 'to-t') ? 'selected' : ''; ?>>מטה למעלה</option>
                    <option value="to-b" <?php echo (!isset($defaultData['bgGradientDirection']) || $defaultData['bgGradientDirection'] === 'to-b') ? 'selected' : ''; ?>>מעלה למטה</option>
                    <option value="to-br" <?php echo (isset($defaultData['bgGradientDirection']) && $defaultData['bgGradientDirection'] === 'to-br') ? 'selected' : ''; ?>>אלכסון למטה-ימין</option>
                    <option value="to-bl" <?php echo (isset($defaultData['bgGradientDirection']) && $defaultData['bgGradientDirection'] === 'to-bl') ? 'selected' : ''; ?>>אלכסון למטה-שמאל</option>
                </select>
            </div>
        </div>
        
        <!-- Background Image -->
        <div id="bgImageSection" class="<?php echo (!isset($defaultData['bgType']) || $defaultData['bgType'] !== 'image') ? 'hidden' : ''; ?> space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">כתובת תמונה (מחשב)</label>
                <input type="url" id="<?php echo $sectionType; ?>BgImage" name="bgImage" 
                       value="<?php echo $defaultData['bgImage'] ?? ''; ?>"
                       placeholder="https://example.com/image.jpg" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">כתובת תמונה למובייל <span class="text-xs text-gray-500">(אופציונלי - אם ריק יעשה שימוש בתמונת המחשב)</span></label>
                <input type="url" id="<?php echo $sectionType; ?>BgImageMobile" name="bgImage_mobile" 
                       value="<?php echo $defaultData['bgImage_mobile'] ?? ''; ?>"
                       placeholder="https://example.com/mobile-image.jpg" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">גודל תמונה</label>
                    <select id="<?php echo $sectionType; ?>BgImageSize" name="bgImageSize" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="cover" <?php echo (!isset($defaultData['bgImageSize']) || $defaultData['bgImageSize'] === 'cover') ? 'selected' : ''; ?>>כיסוי מלא</option>
                        <option value="contain" <?php echo (isset($defaultData['bgImageSize']) && $defaultData['bgImageSize'] === 'contain') ? 'selected' : ''; ?>>התאמה מלאה</option>
                        <option value="auto" <?php echo (isset($defaultData['bgImageSize']) && $defaultData['bgImageSize'] === 'auto') ? 'selected' : ''; ?>>גודל מקורי</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">מיקום תמונה</label>
                    <select id="<?php echo $sectionType; ?>BgImagePosition" name="bgImagePosition" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="center" <?php echo (!isset($defaultData['bgImagePosition']) || $defaultData['bgImagePosition'] === 'center') ? 'selected' : ''; ?>>מרכז</option>
                        <option value="top" <?php echo (isset($defaultData['bgImagePosition']) && $defaultData['bgImagePosition'] === 'top') ? 'selected' : ''; ?>>למעלה</option>
                        <option value="bottom" <?php echo (isset($defaultData['bgImagePosition']) && $defaultData['bgImagePosition'] === 'bottom') ? 'selected' : ''; ?>>למטה</option>
                        <option value="left" <?php echo (isset($defaultData['bgImagePosition']) && $defaultData['bgImagePosition'] === 'left') ? 'selected' : ''; ?>>שמאל</option>
                        <option value="right" <?php echo (isset($defaultData['bgImagePosition']) && $defaultData['bgImagePosition'] === 'right') ? 'selected' : ''; ?>>ימין</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">חזרתיות תמונה</label>
                <select id="<?php echo $sectionType; ?>BgImageRepeat" name="bgImageRepeat" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="no-repeat" <?php echo (!isset($defaultData['bgImageRepeat']) || $defaultData['bgImageRepeat'] === 'no-repeat') ? 'selected' : ''; ?>>ללא חזרה</option>
                    <option value="repeat" <?php echo (isset($defaultData['bgImageRepeat']) && $defaultData['bgImageRepeat'] === 'repeat') ? 'selected' : ''; ?>>חזרה מלאה</option>
                    <option value="repeat-x" <?php echo (isset($defaultData['bgImageRepeat']) && $defaultData['bgImageRepeat'] === 'repeat-x') ? 'selected' : ''; ?>>חזרה אופקית</option>
                    <option value="repeat-y" <?php echo (isset($defaultData['bgImageRepeat']) && $defaultData['bgImageRepeat'] === 'repeat-y') ? 'selected' : ''; ?>>חזרה אנכית</option>
                </select>
            </div>
        </div>
        
        <!-- Background Video -->
        <div id="bgVideoSection" class="<?php echo (!isset($defaultData['bgType']) || $defaultData['bgType'] !== 'video') ? 'hidden' : ''; ?> space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">כתובת סרטון (MP4) - מחשב</label>
                <input type="url" id="<?php echo $sectionType; ?>BgVideo" name="bgVideo" 
                       value="<?php echo $defaultData['bgVideo'] ?? ''; ?>"
                       placeholder="https://example.com/video.mp4" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">כתובת סרטון למובייל <span class="text-xs text-gray-500">(אופציונלי - אם ריק יעשה שימוש בסרטון המחשב)</span></label>
                <input type="url" id="<?php echo $sectionType; ?>BgVideoMobile" name="bgVideo_mobile" 
                       value="<?php echo $defaultData['bgVideo_mobile'] ?? ''; ?>"
                       placeholder="https://example.com/mobile-video.mp4" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">עוצמת החשכה (<span id="<?php echo $sectionType; ?>VideoOverlayDisplay"><?php echo $defaultData['bgVideoOverlay'] ?? 30; ?>%</span>)</label>
                <input type="range" id="<?php echo $sectionType; ?>BgVideoOverlay" name="bgVideoOverlay" 
                       min="0" max="100" step="5" 
                       value="<?php echo $defaultData['bgVideoOverlay'] ?? 30; ?>"
                       class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                       oninput="document.getElementById('<?php echo $sectionType; ?>VideoOverlayDisplay').textContent = this.value + '%'">
            </div>
        </div>
    </div>
</div> 