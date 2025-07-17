<?php
/**
 * Hero Section Settings - הגדרות סקשן הירו
 */

require_once '../../components/background.php';
require_once '../../components/colors.php';
require_once '../../components/typography.php';
require_once '../../components/spacing.php';
require_once '../../components/button-repeater.php';

function renderHeroSettings($sectionId, $settings = []) {
    $defaultSettings = [
        'title' => 'ברוכים הבאים לחנות שלנו',
        'subtitle' => 'גלו את המוצרים הטובים ביותר במחירים הטובים ביותר',
        'titleColor' => '#FFFFFF',
        'subtitleColor' => '#E5E7EB',
        'bgColor' => '#3B82F6',
        'heroTitleFontSize' => '48',
        'heroTitleTextType' => 'h1',
        'heroTitleFontFamily' => 'Noto Sans Hebrew',
        'heroTitleFontWeight' => '700',
        'heroTitleLineHeight' => '1.2',
        'heroTitleLetterSpacing' => '0',
        'heroSubtitleFontSize' => '18',
        'heroSubtitleTextType' => 'p',
        'heroSubtitleFontFamily' => 'Noto Sans Hebrew',
        'heroSubtitleFontWeight' => '400',
        'heroSubtitleLineHeight' => '1.5',
        'heroSubtitleLetterSpacing' => '0'
    ];
    
    $settings = array_merge($defaultSettings, $settings);
    ?>
    
    <div class="hero-settings space-y-6 bg-gray-50 p-4 rounded-lg">
        <!-- Content Tab -->
        <div class="settings-group bg-white rounded-lg border border-gray-200 p-4">
            <h4 class="flex items-center gap-2 text-sm font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <i class="ri-file-text-line text-blue-600"></i>
                תוכן
            </h4>
            
            <div class="space-y-4">
                <!-- Title -->
                <div>
                    <label for="heroTitle" class="block text-sm font-medium text-gray-700 mb-2">כותרת</label>
                    <input type="text" 
                           id="heroTitle" 
                           name="title"
                           value="<?php echo esc_attr($settings['title']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="הזן כותרת">
                </div>
                
                <hr class="border-gray-100">
                
                <!-- Subtitle -->
                <div>
                    <label for="heroSubtitle" class="block text-sm font-medium text-gray-700 mb-2">תיאור</label>
                    <textarea id="heroSubtitle" 
                              name="subtitle"
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="הזן תיאור"><?php echo esc_html($settings['subtitle']); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Design Tab -->
        <div class="settings-group bg-white rounded-lg border border-gray-200 p-4">
            <h4 class="flex items-center gap-2 text-sm font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <i class="ri-palette-line text-blue-600"></i>
                עיצוב
            </h4>
            
            <div class="space-y-4">
                <!-- Height Control -->
                <div>
                    <label for="heroHeight" class="block text-sm font-medium text-gray-700 mb-2">גובה סקשן</label>
                    <select id="heroHeight" 
                            name="heightDesktop"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="auto">אוטומטי</option>
                        <option value="50vh">חצי מסך (50vh)</option>
                        <option value="75vh" selected>3/4 מסך (75vh)</option>
                        <option value="100vh">מסך מלא (100vh)</option>
                        <option value="custom">מותאם אישית</option>
                    </select>
                </div>
                
                <!-- Custom Height -->
                <div id="customHeight" class="hidden">
                    <label for="heroCustomHeight" class="block text-sm font-medium text-gray-700 mb-2">גובה מותאם (px)</label>
                    <input type="number" 
                           id="heroCustomHeight" 
                           name="customHeightDesktop"
                           min="200" max="2000" step="10"
                           value="600"
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <hr class="border-gray-100">
                
                <!-- Background Controls -->
                <div>
                    <h5 class="text-sm font-medium text-gray-700 mb-3">רקע</h5>
                    <?php renderBackgroundControls('heroDesktop'); ?>
                </div>
            </div>
        </div>

        <!-- Typography Tab -->
        <div class="settings-group bg-white rounded-lg border border-gray-200 p-4">
            <h4 class="flex items-center gap-2 text-sm font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <i class="ri-text text-blue-600"></i>
                טיפוגרפיה
            </h4>
            
            <div class="space-y-6">
                <!-- Title Typography -->
                <div>
                    <h5 class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                        <i class="ri-h-1 text-gray-500"></i>
                        כותרת
                    </h5>
                    <?php renderTypographyControls('heroTitle', 'כותרת', $settings); ?>
                    <div class="mt-3">
                        <?php renderColorPicker('heroTitleColor', 'צבע כותרת', $settings['titleColor']); ?>
                    </div>
                </div>
                
                <hr class="border-gray-200">
                
                <!-- Subtitle Typography -->
                <div>
                    <h5 class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                        <i class="ri-text text-gray-500"></i>
                        תיאור
                    </h5>
                    <?php renderTypographyControls('heroSubtitle', 'תיאור', $settings); ?>
                    <div class="mt-3">
                        <?php renderColorPicker('heroSubtitleColor', 'צבע תיאור', $settings['subtitleColor']); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buttons Tab -->
        <div class="settings-group bg-white rounded-lg border border-gray-200 p-4">
            <h4 class="flex items-center gap-2 text-sm font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <i class="ri-links-line text-blue-600"></i>
                כפתורים
            </h4>
            
            <?php 
            $buttons = $settings['buttons'] ?? [];
            renderButtonsRepeater($buttons); 
            ?>
        </div>
    </div>

    <script>
    // Hero settings logic
    (function() {
        // Height controls
        const heightSelect = document.getElementById('heroHeight');
        const customHeightDiv = document.getElementById('customHeight');
        
        if (heightSelect && customHeightDiv) {
            heightSelect.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customHeightDiv.classList.remove('hidden');
                } else {
                    customHeightDiv.classList.add('hidden');
                }
            });
        }
        
        // Debug typography changes
        const typographyInputs = document.querySelectorAll('[name*="heroTitle"], [name*="heroSubtitle"]');
        typographyInputs.forEach(input => {
            input.addEventListener('input', function() {
                console.log('🔧 DEBUG: Typography setting changed:', this.name, '=', this.value);
            });
        });
        
    })();
    </script>
    <?php
}

// קריאה לפונקציה עם הנתונים שמועברים
$input = json_decode(file_get_contents('php://input'), true);
$sectionId = $input['sectionId'] ?? '';
$settings = $input['settings'] ?? [];

renderHeroSettings($sectionId, $settings);
?> 