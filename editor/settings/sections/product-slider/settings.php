<?php
/**
 * Hero Section Settings - הגדרות סקשן הירו
 */

require_once '../../components/background.php';
require_once '../../components/colors.php';
require_once '../../components/typography.php';
require_once '../../components/spacing.php';

function renderHeroSettings($sectionId, $settings = []) {
    $defaultSettings = [
        'title' => 'ברוכים הבאים לחנות שלנו',
        'subtitle' => 'גלו את המוצרים הטובים ביותר במחירים הטובים ביותר',
        'titleColor' => '#FFFFFF',
        'subtitleColor' => '#E5E7EB',
        'bgColor' => '#3B82F6'
    ];
    
    $settings = array_merge($defaultSettings, $settings);
    ?>
    
    <div class="hero-settings space-y-6">
        <!-- Device Tabs -->
        <div class="device-tabs border-b border-gray-200">
            <div class="flex">
                <button class="device-tab-btn flex-1 px-4 py-2 text-sm font-medium text-center border-b-2 border-blue-500 text-blue-600 bg-blue-50" data-device="desktop">
                    <i class="ri-computer-line ml-1"></i>
                    מחשב
                </button>
                <button class="device-tab-btn flex-1 px-4 py-2 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-device="mobile">
                    <i class="ri-smartphone-line ml-1"></i>
                    מובייל
                </button>
            </div>
        </div>

        <!-- Content Tab -->
        <div class="settings-group">
            <h4 class="flex items-center gap-2 text-sm font-semibold text-gray-900 mb-4">
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

        <!-- Design Tab - Desktop -->
        <div class="settings-group device-settings" data-device="desktop">
            <h4 class="flex items-center gap-2 text-sm font-semibold text-gray-900 mb-4">
                <i class="ri-palette-line text-blue-600"></i>
                עיצוב - מחשב
            </h4>
            
            <div class="space-y-4">
                <!-- Height Control -->
                <div>
                    <label for="heroHeightDesktop" class="block text-sm font-medium text-gray-700 mb-2">גובה סקשן</label>
                    <select id="heroHeightDesktop" 
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
                <div id="customHeightDesktop" class="hidden">
                    <label for="heroCustomHeightDesktop" class="block text-sm font-medium text-gray-700 mb-2">גובה מותאם (px)</label>
                    <input type="number" 
                           id="heroCustomHeightDesktop" 
                           name="customHeightDesktop"
                           min="200" max="2000" step="10"
                           value="600"
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- Background Controls -->
                <div>
                    <h5 class="text-sm font-medium text-gray-700 mb-3">רקע</h5>
                    <?php renderBackgroundControls('heroDesktop'); ?>
                </div>
            </div>
        </div>

        <!-- Design Tab - Mobile -->
        <div class="settings-group device-settings hidden" data-device="mobile">
            <h4 class="flex items-center gap-2 text-sm font-semibold text-gray-900 mb-4">
                <i class="ri-palette-line text-blue-600"></i>
                עיצוב - מובייל
            </h4>
            
            <div class="space-y-4">
                <!-- Height Control -->
                <div>
                    <label for="heroHeightMobile" class="block text-sm font-medium text-gray-700 mb-2">גובה סקשן</label>
                    <select id="heroHeightMobile" 
                            name="heightMobile"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="auto">אוטומטי</option>
                        <option value="50vh" selected>חצי מסך (50vh)</option>
                        <option value="75vh">3/4 מסך (75vh)</option>
                        <option value="100vh">מסך מלא (100vh)</option>
                        <option value="custom">מותאם אישית</option>
                    </select>
                </div>
                
                <!-- Custom Height -->
                <div id="customHeightMobile" class="hidden">
                    <label for="heroCustomHeightMobile" class="block text-sm font-medium text-gray-700 mb-2">גובה מותאם (px)</label>
                    <input type="number" 
                           id="heroCustomHeightMobile" 
                           name="customHeightMobile"
                           min="200" max="1000" step="10"
                           value="400"
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- Background Controls -->
                <div>
                    <h5 class="text-sm font-medium text-gray-700 mb-3">רקע</h5>
                    <?php renderBackgroundControls('heroMobile'); ?>
                </div>
            </div>
        </div>

        <!-- Typography Tab - Desktop -->
        <div class="settings-group device-settings" data-device="desktop">
            <h4 class="flex items-center gap-2 text-sm font-semibold text-gray-900 mb-4">
                <i class="ri-text text-blue-600"></i>
                טיפוגרפיה - מחשב
            </h4>
            
            <div class="space-y-4">
                <!-- Title Typography -->
                <div>
                    <h5 class="text-sm font-medium text-gray-700 mb-3">כותרת</h5>
                    <?php renderTypographyControls('heroTitleDesktop', 'כותרת'); ?>
                    <?php renderColorPicker('heroTitleColorDesktop', 'צבע כותרת', $settings['titleColor']); ?>
                </div>
                
                <!-- Subtitle Typography -->
                <div>
                    <h5 class="text-sm font-medium text-gray-700 mb-3">תיאור</h5>
                    <?php renderTypographyControls('heroSubtitleDesktop', 'תיאור'); ?>
                    <?php renderColorPicker('heroSubtitleColorDesktop', 'צבע תיאור', $settings['subtitleColor']); ?>
                </div>
            </div>
        </div>

        <!-- Typography Tab - Mobile -->
        <div class="settings-group device-settings hidden" data-device="mobile">
            <h4 class="flex items-center gap-2 text-sm font-semibold text-gray-900 mb-4">
                <i class="ri-text text-blue-600"></i>
                טיפוגרפיה - מובייל
            </h4>
            
            <div class="space-y-4">
                <!-- Title Typography -->
                <div>
                    <h5 class="text-sm font-medium text-gray-700 mb-3">כותרת</h5>
                    <?php renderTypographyControls('heroTitleMobile', 'כותרת'); ?>
                    <?php renderColorPicker('heroTitleColorMobile', 'צבע כותרת', $settings['titleColor']); ?>
                </div>
                
                <!-- Subtitle Typography -->
                <div>
                    <h5 class="text-sm font-medium text-gray-700 mb-3">תיאור</h5>
                    <?php renderTypographyControls('heroSubtitleMobile', 'תיאור'); ?>
                    <?php renderColorPicker('heroSubtitleColorMobile', 'צבע תיאור', $settings['subtitleColor']); ?>
                </div>
            </div>
        </div>

        <!-- Spacing Tab - Desktop -->
        <div class="settings-group device-settings" data-device="desktop">
            <h4 class="flex items-center gap-2 text-sm font-semibold text-gray-900 mb-4">
                <i class="ri-space text-blue-600"></i>
                מרווחים - מחשב
            </h4>
            <?php renderSpacingControls('heroDesktop'); ?>
        </div>

        <!-- Spacing Tab - Mobile -->
        <div class="settings-group device-settings hidden" data-device="mobile">
            <h4 class="flex items-center gap-2 text-sm font-semibold text-gray-900 mb-4">
                <i class="ri-space text-blue-600"></i>
                מרווחים - מובייל
            </h4>
            <?php renderSpacingControls('heroMobile'); ?>
        </div>
    </div>

    <script>
    // Hero settings logic
    (function() {
        // Device tabs
        const deviceTabs = document.querySelectorAll('.device-tab-btn');
        const deviceSettings = document.querySelectorAll('.device-settings');
        
        deviceTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const device = this.dataset.device;
                
                // Update active tab
                deviceTabs.forEach(t => {
                    t.classList.remove('border-blue-500', 'text-blue-600', 'bg-blue-50');
                    t.classList.add('border-transparent', 'text-gray-500');
                });
                this.classList.remove('border-transparent', 'text-gray-500');
                this.classList.add('border-blue-500', 'text-blue-600', 'bg-blue-50');
                
                // Show/hide device settings
                deviceSettings.forEach(setting => {
                    if (setting.dataset.device === device) {
                        setting.classList.remove('hidden');
                    } else {
                        setting.classList.add('hidden');
                    }
                });
            });
        });
        
        // Height controls
        const heightSelects = ['heroHeightDesktop', 'heroHeightMobile'];
        heightSelects.forEach(selectId => {
            const select = document.getElementById(selectId);
            const device = selectId.includes('Desktop') ? 'Desktop' : 'Mobile';
            const customHeightDiv = document.getElementById('customHeight' + device);
            
            if (select && customHeightDiv) {
                select.addEventListener('change', function() {
                    if (this.value === 'custom') {
                        customHeightDiv.classList.remove('hidden');
                    } else {
                        customHeightDiv.classList.add('hidden');
                    }
                });
            }
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