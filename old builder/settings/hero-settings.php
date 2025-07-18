<?php
/**
 * Hero Settings - הגדרות סקשן הירו
 */

echo "<!-- Hero Settings PHP loaded at " . date('Y-m-d H:i:s') . " -->\n";

// Include component functions
require_once __DIR__ . '/components/typography.php';
require_once __DIR__ . '/components/colors.php';
require_once __DIR__ . '/components/spacing.php';
require_once __DIR__ . '/components/background.php';
require_once __DIR__ . '/components/layout.php';
require_once __DIR__ . '/components/buttons-repeater.php';

echo "<!-- All components loaded successfully -->\n";
?>

<div id="heroSettings" class="section-settings p-6">
    <!-- Header -->
    <div class="settings-header border-b border-gray-200 pb-4 mb-6">
        <div class="flex items-center gap-3 mb-2">
            <button id="backButton" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition-colors">
                <i class="ri-arrow-right-line text-gray-600"></i>
            </button>
            <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                <i class="ri-image-line"></i>
                הגדרות Hero
            </h2>
        </div>
        <p class="text-gray-600 text-sm">ערוך את המראה והתוכן של סקשן הפתיחה</p>
    </div>

    <form id="heroForm" class="space-y-6">
        <!-- Content Tab -->
        <div class="tab-content" data-tab="content">
            <!-- Title & Subtitle -->
            <div class="settings-group">
                <h3 class="settings-group-title">תוכן</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="heroTitle" class="block text-sm font-medium text-gray-700 mb-2">כותרת ראשית</label>
                        <input type="text" id="heroTitle" name="title" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="ברוכים הבאים לחנות שלנו">
                    </div>
                    
                    <div>
                        <label for="heroSubtitle" class="block text-sm font-medium text-gray-700 mb-2">תת כותרת</label>
                        <textarea id="heroSubtitle" name="subtitle" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="גלה את המוצרים הטובים ביותר במחירים הכי טובים"></textarea>
                    </div>
                </div>
            </div>

            <!-- Typography -->
            <div class="settings-group">
                <h3 class="settings-group-title">טיפוגרפיה</h3>
                
                <!-- Title Typography -->
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-600 mb-3">כותרת ראשית</h4>
                    <?php renderTypographyControls('heroTitle', 'כותרת ראשית'); ?>
                </div>
                
                <!-- Subtitle Typography -->
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-600 mb-3">תת כותרת</h4>
                    <?php renderTypographyControls('heroSubtitle', 'תת כותרת'); ?>
                </div>
                
                <!-- Button Typography -->
                <div>
                    <h4 class="text-sm font-medium text-gray-600 mb-3">כפתורים</h4>
                    <?php renderTypographyControls('heroButton', 'כפתורים'); ?>
                </div>
            </div>

            <!-- Colors -->
            <div class="settings-group">
                <h3 class="settings-group-title">צבעים</h3>
                <div class="grid grid-cols-2 gap-4">
                    <?php renderColorPicker('heroTitleColor', 'צבע כותרת', '#1F2937'); ?>
                    <?php renderColorPicker('heroSubtitleColor', 'צבע תת כותרת', '#6B7280'); ?>
                </div>
            </div>

            <!-- Buttons -->
            <div class="settings-group">
                <h3 class="settings-group-title">כפתורים</h3>
                <?php renderButtonsRepeater('heroButtons', 'buttonsContainer'); ?>
            </div>
        </div>

        <!-- Design Tab -->
        <div class="tab-content" data-tab="design">
            <!-- Background -->
            <div class="settings-group">
                <h3 class="settings-group-title">רקע</h3>
                <?php renderBackgroundControls('hero'); ?>
            </div>

            <!-- Layout -->
            <div class="settings-group">
                <h3 class="settings-group-title">פריסה</h3>
                <?php renderLayoutControls('hero'); ?>
            </div>

            <!-- Content Position -->
            <div class="settings-group">
                <h3 class="settings-group-title">מיקום תוכן</h3>
                <div class="grid grid-cols-3 gap-2">
                    <?php
                    $positions = [
                        'top-left' => 'למעלה שמאל',
                        'top-center' => 'למעלה מרכז',
                        'top-right' => 'למעלה ימין',
                        'center-left' => 'מרכז שמאל',
                        'center-center' => 'מרכז מרכז',
                        'center-right' => 'מרכז ימין',
                        'bottom-left' => 'למטה שמאל',
                        'bottom-center' => 'למטה מרכז',
                        'bottom-right' => 'למטה ימין'
                    ];
                    
                    foreach ($positions as $value => $label):
                    ?>
                        <button type="button" 
                                class="position-btn px-3 py-2 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors"
                                data-position="<?php echo esc_attr($value); ?>">
                            <?php echo esc_html($label); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Spacing -->
            <div class="settings-group">
                <h3 class="settings-group-title">מרווחים</h3>
                <?php renderSpacingControls('hero'); ?>
            </div>
        </div>

        <!-- Advanced Tab -->
        <div class="tab-content" data-tab="advanced">
            <!-- Responsive Visibility -->
            <div class="settings-group">
                <h3 class="settings-group-title">תצוגה רספונסיבית</h3>
                <div class="space-y-3">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" id="heroHideMobile" name="hideMobile">
                        <span class="text-sm text-gray-700">הסתר במובייל</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" id="heroHideTablet" name="hideTablet">
                        <span class="text-sm text-gray-700">הסתר בטאבלט</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" id="heroHideDesktop" name="hideDesktop">
                        <span class="text-sm text-gray-700">הסתר במחשב</span>
                    </label>
                </div>
            </div>

            <!-- Animation -->
            <div class="settings-group">
                <h3 class="settings-group-title">אנימציה</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="heroAnimation" class="block text-sm font-medium text-gray-700 mb-2">סוג אנימציה</label>
                        <select id="heroAnimation" name="animation" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="none">ללא אנימציה</option>
                            <option value="fadeIn">עמעום פנימה</option>
                            <option value="slideUp">החלקה מלמטה</option>
                            <option value="slideDown">החלקה מלמעלה</option>
                            <option value="slideLeft">החלקה משמאל</option>
                            <option value="slideRight">החלקה מימין</option>
                            <option value="zoomIn">זום פנימה</option>
                            <option value="zoomOut">זום החוצה</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="heroAnimationDelay" class="block text-sm font-medium text-gray-700 mb-2">עיכוב (ms)</label>
                        <input type="number" id="heroAnimationDelay" name="animationDelay" min="0" max="5000" step="100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="0">
                    </div>
                </div>
            </div>

            <!-- Parallax -->
            <div class="settings-group">
                <h3 class="settings-group-title">אפקט פרלקס</h3>
                <div class="space-y-3">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" id="heroParallax" name="parallax">
                        <span class="text-sm text-gray-700">הפעל אפקט פרלקס</span>
                    </label>
                    
                    <div id="parallaxControls" class="hidden">
                        <label for="heroParallaxSpeed" class="block text-sm font-medium text-gray-700 mb-2">מהירות פרלקס</label>
                        <input type="range" id="heroParallaxSpeed" name="parallaxSpeed" min="0.1" max="2" step="0.1" value="0.5"
                               class="w-full">
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>איטי</span>
                            <span>מהיר</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden inputs for complex data -->
        <input type="hidden" id="heroButtons" name="buttons" value="">
        <input type="hidden" id="heroContentPosition" name="contentPosition" value="">
    </form>
</div>

<style>
.settings-group {
    @apply border border-gray-200 rounded-lg p-4 bg-white;
}

.settings-group-title {
    @apply text-base font-medium text-gray-800 mb-4 pb-2 border-b border-gray-100;
}

.position-btn.active {
    @apply bg-blue-100 border-blue-300 text-blue-700;
}

.bg-type-btn.active {
    @apply bg-blue-100 border-blue-300 text-blue-700;
}

.tab-content {
    @apply space-y-6;
}

#parallaxControls.hidden {
    @apply hidden;
}
</style>

<script>
// Toggle parallax controls
document.getElementById('heroParallax').addEventListener('change', function() {
    const controls = document.getElementById('parallaxControls');
    if (this.checked) {
        controls.classList.remove('hidden');
    } else {
        controls.classList.add('hidden');
    }
});
</script> 