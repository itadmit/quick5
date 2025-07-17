<?php
require_once '../../includes/auth.php';
require_once '../../includes/ThemeManager.php';

// בדיקת הרשאות
if (!isLoggedIn() || !hasPermission('manage_themes')) {
    header('Location: ../login.php');
    exit;
}

$store = getCurrentStore();
$themeManager = new ThemeManager();
$currentTheme = $themeManager->getCurrentThemeDetails($store['id']);
$availableThemes = $themeManager->getAvailableThemes();

// טיפול בשמירת הגדרות
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'change_theme':
                if (isset($_POST['theme_name'])) {
                    $themeManager->setTheme($store['id'], $_POST['theme_name']);
                    $currentTheme = $themeManager->getCurrentThemeDetails($store['id']);
                    $success = 'התבנית שונתה בהצלחה!';
                }
                break;
            case 'save_settings':
                $settings = [
                    'store_status' => $_POST['store_status'] ?? 'open',
                    'password_protection' => $_POST['password_protection'] ?? '',
                    'maintenance_message' => $_POST['maintenance_message'] ?? '',
                    'seo_title' => $_POST['seo_title'] ?? '',
                    'seo_description' => $_POST['seo_description'] ?? '',
                    'seo_keywords' => $_POST['seo_keywords'] ?? '',
                    'og_image' => $_POST['og_image'] ?? '',
                    'custom_css' => $_POST['custom_css'] ?? '',
                    'custom_js' => $_POST['custom_js'] ?? '',
                    'tracking_codes' => $_POST['tracking_codes'] ?? ''
                ];
                
                $themeManager->saveThemeSettings($store['id'], $settings);
                $success = 'ההגדרות נשמרו בהצלחה!';
                break;
        }
    }
}

$themeSettings = $themeManager->getStoreThemeSettings($store['id']);

$pageTitle = 'תבניות';
include '../templates/header.php';
include '../templates/sidebar.php';
?>

<!-- Main Content -->
<div class="lg:pr-64">
    <?php include '../templates/navbar.php'; ?>

    <!-- Themes Content -->
    <main class="py-8" style="background: #e9f0f3;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">תבניות</h1>
                <div class="flex gap-3">
                    <a href="<?= "http://" . ($store['slug'] ?? 'demo') . ".localhost:8888" ?>" 
                       target="_blank" 
                       class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all border border-gray-300 text-gray-700 hover:bg-gray-50">
                        <i class="ri-external-link-line ml-2"></i>
                        צפה בחנות
                    </a>
                    <button class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all bg-blue-600 text-white hover:bg-blue-700">
                        <i class="ri-upload-line ml-2"></i>
                        ייבא תבנית
                    </button>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="bg-white shadow-xl rounded-lg overflow-hidden mb-6">
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-blue-500 text-blue-600" data-tab="themes">
                            <i class="ri-layout-line ml-2"></i>
                            תבניות
                        </button>
                        <button class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="settings">
                            <i class="ri-settings-3-line ml-2"></i>
                            הגדרות
                        </button>
                    </nav>
                </div>
            </div>

            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center justify-between">
                    <span><?php echo $success; ?></span>
                    <button onclick="this.parentElement.style.display='none'" class="text-green-700 hover:text-green-900">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Themes Tab Content -->
            <div id="themes-tab" class="tab-content">
                <!-- Theme Library -->
                <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">תבניות</h2>
                    <p class="text-sm text-gray-600 mt-1">בחר תבנית עבור החנות שלך</p>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($availableThemes as $themeName => $theme): ?>
                            <div class="border rounded-lg overflow-hidden hover:shadow-lg transition-shadow <?php echo $themeName === ($currentTheme['name'] ?? 'quickshop-evening') ? 'ring-2 ring-blue-500' : ''; ?>">
                                <!-- Theme Preview -->
                                <div class="aspect-w-16 aspect-h-10 bg-gray-100">
                                    <?php if ($themeName === ($currentTheme['name'] ?? 'quickshop-evening')): ?>
                                        <div class="relative">
                                            <iframe 
                                                src="<?php echo "http://{$store['slug']}.localhost:8888/"; ?>" 
                                                class="w-full h-48 border-0"
                                                style="transform: scale(0.5); transform-origin: top left; width: 200%; height: 200%;">
                                            </iframe>
                                            <!-- Device Toggle -->
                                            <div class="absolute top-2 left-2 flex bg-white rounded-md shadow-sm p-1">
                                                <button class="px-2 py-1 text-xs bg-blue-600 text-white rounded-sm" id="desktop-preview">
                                                    <i class="ri-computer-line"></i>
                                                </button>
                                                <button class="px-2 py-1 text-xs text-gray-600 hover:text-gray-800 rounded-sm" id="mobile-preview">
                                                    <i class="ri-smartphone-line"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-full h-48 flex items-center justify-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            <div class="text-center text-white">
                                                <i class="ri-layout-line text-4xl mb-2"></i>
                                                <h3 class="font-medium"><?php echo $theme['display_name'] ?? ucfirst($themeName); ?></h3>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Theme Info -->
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="font-medium text-gray-900"><?php echo $theme['display_name'] ?? ucfirst($themeName); ?></h3>
                                        <?php if ($themeName === ($currentTheme['name'] ?? 'quickshop-evening')): ?>
                                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full flex items-center">
                                                <i class="ri-check-line ml-1"></i>
                                                פעילה
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p class="text-sm text-gray-600 mb-3"><?php echo $theme['description']; ?></p>
                                    
                                    <div class="text-xs text-gray-500 mb-3">
                                        <span>גרסה <?php echo $theme['version'] ?? '1.0.0'; ?></span>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <?php if ($themeName !== ($currentTheme['name'] ?? 'quickshop-evening')): ?>
                                        <div class="flex gap-2">
                                            <form method="post" class="flex-1">
                                                <input type="hidden" name="action" value="change_theme">
                                                <input type="hidden" name="theme_name" value="<?php echo $themeName; ?>">
                                                <button type="submit" class="w-full px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm">
                                                    פרסם
                                                </button>
                                            </form>
                                            <button class="px-3 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors text-sm">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div class="space-y-2">
                                            <a href="../customizer/" class="w-full inline-flex items-center justify-center px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm">
                                                <i class="ri-brush-line ml-1"></i>
                                                התאם אישית
                                            </a>
                                            <div class="flex gap-2">
                                                <a href="code-editor.php" class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors text-sm">
                                                    <i class="ri-code-s-slash-line ml-1"></i>
                                                    קוד
                                                </a>
                                                <button type="button" onclick="switchToSettingsTab()" class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors text-sm">
                                                    <i class="ri-settings-3-line ml-1"></i>
                                                    הגדרות
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Popular Free Themes -->
            <div class="bg-white shadow-xl rounded-lg overflow-hidden mt-8">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="ri-information-line text-blue-600 ml-2"></i>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">תבניות חינמיות פופולריות</h2>
                            <p class="text-sm text-gray-600">נוצרו עם תכונות ליבה שתוכל להתאים אישית בקלות—אין צורך בקידוד.</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Sample Free Themes -->
                        <div class="border rounded-lg overflow-hidden opacity-60">
                            <div class="w-full h-48 bg-gradient-to-br from-pink-400 to-red-500 flex items-center justify-center">
                                <div class="text-center text-white">
                                    <i class="ri-layout-line text-4xl mb-2"></i>
                                    <h3 class="font-medium">Minimal</h3>
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="font-medium text-gray-900 mb-2">Minimal</h3>
                                <p class="text-sm text-gray-600 mb-3">עיצוב מינימליסטי ונקי</p>
                                <button class="w-full px-3 py-2 bg-gray-300 text-gray-500 rounded-md cursor-not-allowed text-sm">
                                    בקרוב
                                </button>
                            </div>
                        </div>
                        
                        <div class="border rounded-lg overflow-hidden opacity-60">
                            <div class="w-full h-48 bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center">
                                <div class="text-center text-white">
                                    <i class="ri-layout-line text-4xl mb-2"></i>
                                    <h3 class="font-medium">Modern</h3>
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="font-medium text-gray-900 mb-2">Modern</h3>
                                <p class="text-sm text-gray-600 mb-3">עיצוב מודרני וחדשני</p>
                                <button class="w-full px-3 py-2 bg-gray-300 text-gray-500 rounded-md cursor-not-allowed text-sm">
                                    בקרוב
                                </button>
                            </div>
                        </div>
                        
                        <div class="border rounded-lg overflow-hidden opacity-60">
                            <div class="w-full h-48 bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center">
                                <div class="text-center text-white">
                                    <i class="ri-layout-line text-4xl mb-2"></i>
                                    <h3 class="font-medium">Classic</h3>
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="font-medium text-gray-900 mb-2">Classic</h3>
                                <p class="text-sm text-gray-600 mb-3">עיצוב קלאסי ואלגנטי</p>
                                <button class="w-full px-3 py-2 bg-gray-300 text-gray-500 rounded-md cursor-not-allowed text-sm">
                                    בקרוב
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            
            <!-- Settings Tab Content -->
            <div id="settings-tab" class="tab-content hidden">
                <form method="post" class="space-y-6">
                    <input type="hidden" name="action" value="save_settings">
                    
                    <!-- Store Status -->
                    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">סטטוס חנות</h2>
                            <p class="text-sm text-gray-600 mt-1">נהל את זמינות החנות שלך</p>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">סטטוס</label>
                                    <select name="store_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="open" <?php echo ($themeSettings['store_status'] ?? 'open') === 'open' ? 'selected' : ''; ?>>פתוח</option>
                                        <option value="password" <?php echo ($themeSettings['store_status'] ?? '') === 'password' ? 'selected' : ''; ?>>מוגן בסיסמה</option>
                                        <option value="maintenance" <?php echo ($themeSettings['store_status'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>מצב תחזוקה</option>
                                    </select>
                                </div>
                                
                                <div id="password-field" class="<?php echo ($themeSettings['store_status'] ?? 'open') !== 'password' ? 'hidden' : ''; ?>">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">סיסמה</label>
                                    <input type="password" name="password_protection" value="<?php echo htmlspecialchars($themeSettings['password_protection'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div id="maintenance-field" class="<?php echo ($themeSettings['store_status'] ?? 'open') !== 'maintenance' ? 'hidden' : ''; ?>">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">הודעת תחזוקה</label>
                                    <textarea name="maintenance_message" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="החנות נמצאת בתחזוקה. נחזור בקרוב!"><?php echo htmlspecialchars($themeSettings['maintenance_message'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Settings -->
                    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">הגדרות SEO</h2>
                            <p class="text-sm text-gray-600 mt-1">שפר את הנראות של החנות שלך במנועי חיפוש</p>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">כותרת אתר</label>
                                <input type="text" name="seo_title" value="<?php echo htmlspecialchars($themeSettings['seo_title'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="החנות שלי">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">תיאור אתר</label>
                                <textarea name="seo_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="החנות המובילה למוצרים איכותיים במחירים הטובים ביותר"><?php echo htmlspecialchars($themeSettings['seo_description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">מילות מפתח</label>
                                <input type="text" name="seo_keywords" value="<?php echo htmlspecialchars($themeSettings['seo_keywords'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="מילות מפתח מופרדות בפסיקים">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">תמונת שיתוף (Open Graph)</label>
                                <div class="flex gap-2">
                                    <input type="text" name="og_image" value="<?php echo htmlspecialchars($themeSettings['og_image'] ?? ''); ?>" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="URL לתמונה">
                                    <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                        <i class="ri-upload-line"></i>
                                        העלה תמונה
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Code -->
                    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">קוד מותאם</h2>
                            <p class="text-sm text-gray-600 mt-1">הוסף CSS ו-JavaScript מותאם לחנות שלך</p>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">CSS מותאם</label>
                                <textarea name="custom_css" rows="8" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm" placeholder="/* CSS מותאם כאן */"><?php echo htmlspecialchars($themeSettings['custom_css'] ?? ''); ?></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">JavaScript מותאם</label>
                                <textarea name="custom_js" rows="8" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm" placeholder="// JavaScript מותאם כאן"><?php echo htmlspecialchars($themeSettings['custom_js'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Tracking Codes -->
                    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">קודי מעקב</h2>
                            <p class="text-sm text-gray-600 mt-1">הוסף קודי מעקב של Google Analytics, Facebook Pixel ועוד</p>
                        </div>
                        <div class="p-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">קודי מעקב</label>
                                <textarea name="tracking_codes" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm" placeholder="<!-- קודי מעקב כאן -->"><?php echo htmlspecialchars($themeSettings['tracking_codes'] ?? ''); ?></textarea>
                                <p class="text-xs text-gray-500 mt-2">הוסף כאן קודי מעקב של Google Analytics, Facebook Pixel, Google Tag Manager ועוד</p>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                            <i class="ri-save-line ml-2"></i>
                            שמור הגדרות
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            switchTab(tabName);
        });
    });
    
    // Store status conditional fields
    const storeStatusSelect = document.querySelector('select[name="store_status"]');
    const passwordField = document.getElementById('password-field');
    const maintenanceField = document.getElementById('maintenance-field');
    
    if (storeStatusSelect && passwordField && maintenanceField) {
        storeStatusSelect.addEventListener('change', function() {
            const status = this.value;
            
            // Hide all conditional fields
            passwordField.classList.add('hidden');
            maintenanceField.classList.add('hidden');
            
            // Show relevant field based on status
            if (status === 'password') {
                passwordField.classList.remove('hidden');
            } else if (status === 'maintenance') {
                maintenanceField.classList.remove('hidden');
            }
        });
    }
});

function switchTab(tabName) {
    // Update buttons
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('border-blue-500', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    
    const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
    if (activeButton) {
        activeButton.classList.remove('border-transparent', 'text-gray-500');
        activeButton.classList.add('border-blue-500', 'text-blue-600');
    }
    
    // Update content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    const activeContent = document.getElementById(`${tabName}-tab`);
    if (activeContent) {
        activeContent.classList.remove('hidden');
    }
}

function switchToSettingsTab() {
    switchTab('settings');
}
</script>

<?php include '../templates/footer.php'; ?> 