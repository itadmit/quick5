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
$themeSettings = $themeManager->getStoreThemeSettings($store['id']);

// הקסטומיזר מטפל רק בעיצוב ותוכן

$pageTitle = 'קסטומיזר';
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - QuickShop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // תצורת Tailwind CSS
        tailwind.config = {
            content: [],
            theme: {
                fontFamily: {
                    'sans': ['"Noto Sans Hebrew"', 'system-ui', 'sans-serif'],
                }
            },
            corePlugins: {
                preflight: false,
            }
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Hebrew:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/customizer.css">
    <style>
        body { font-family: 'Noto Sans Hebrew', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 h-16 flex items-center justify-between px-6 fixed top-0 left-0 right-0 z-50">
        <div class="flex items-center gap-4">
            <a href="../settings/theme.php" class="text-gray-600 hover:text-gray-800 transition-colors">
                <i class="ri-arrow-right-line text-xl"></i>
            </a>
            <h1 class="text-lg font-semibold text-gray-900">קסטומיזר</h1>
            <div class="text-sm text-gray-500">
                <?php echo $currentTheme['display_name'] ?? 'QuickShop Evening'; ?>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- Device Toggle -->
            <div class="flex bg-gray-100 rounded-lg p-1">
                <button id="desktop-view" class="px-3 py-1 text-sm bg-white shadow-sm rounded-md text-gray-700">
                    <i class="ri-computer-line"></i>
                </button>
                <button id="mobile-view" class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 rounded-md">
                    <i class="ri-smartphone-line"></i>
                </button>
            </div>
            
            <!-- Theme Settings Modal Button -->
            <button id="theme-settings-btn" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                <i class="ri-palette-line ml-2"></i>
                עיצוב תבנית
            </button>
            
            <button id="save-changes" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                <i class="ri-save-line ml-2"></i>
                שמור סקשנים
            </button>
            
            <!-- Auto-save indicator - מבוטל -->
            <div id="auto-save-indicator" class="hidden px-3 py-2 text-sm text-gray-500" style="display: none;">
                <i class="ri-check-line text-green-500 ml-1"></i>
                נשמר אוטומטית
            </div>
            
            <a href="<?php echo "http://" . ($store['subdomain'] ?? 'yogev') . ".localhost:8888/"; ?>" target="_blank" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                <i class="ri-external-link-line ml-2"></i>
                צפה באתר
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex h-screen pt-16">
        <!-- Sections Panel -->
        <div id="sections-container" class="sections-panel bg-white shadow-lg border-l border-gray-200 overflow-y-auto" style="width: 320px; position: relative;">
            <div class="p-6">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">סקשנים</h2>
                    <button id="add-section-btn" class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="ri-add-line"></i>
                    </button>
                </div>

                <!-- Status Message -->
                <div id="unsaved-changes-notice" class="hidden mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="ri-information-line text-yellow-600 ml-2"></i>
                        <span class="text-sm text-yellow-800">יש לך שינויים לא שמורים. לחץ על "שמור שינויים" כדי לשמור אותם.</span>
                    </div>
                </div>

                <!-- Sections List -->
                <div id="sections-list" class="space-y-3">
                    <!-- הסקשנים ייטענו דינמית מהדטאבייס -->
                </div>

                <!-- Add Section Button -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <button id="add-section-main" class="w-full flex items-center justify-center px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-gray-400 hover:text-gray-600 transition-colors">
                        <i class="ri-add-line ml-2"></i>
                        הוסף סקשן
                    </button>
                </div>
            </div>
        </div>

        <!-- Preview Container -->
        <div class="preview-container" style="width: calc(100% - 320px);">
            <div class="h-full bg-gray-50 flex items-center justify-center">
                <div id="preview-frame-container" class="w-full h-full max-w-full transition-all duration-300">
                    <iframe 
                        id="preview-frame" 
                        src="<?php echo "http://" . ($store['slug'] ?? 'yogev') . ".localhost:8888/?preview=1"; ?>" 
                        class="w-full h-full border-0 bg-white shadow-lg"
                        style="border-radius: 8px;">
                    </iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Theme Settings Modal -->
    <div id="theme-settings-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">עיצוב תבנית</h2>
                    <button id="close-theme-modal" class="text-gray-400 hover:text-gray-600">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="flex h-[600px]">
                    <!-- Settings Panel -->
                    <div class="w-80 bg-gray-50 border-l border-gray-200 overflow-y-auto">
                        <div class="p-6">
                            <!-- Theme Settings Tabs -->
                            <div class="flex border-b border-gray-200 mb-6">
                                <button class="theme-tab-button px-4 py-2 text-sm font-medium text-blue-600 border-b-2 border-blue-600" data-tab="colors">
                                    <i class="ri-palette-line ml-2"></i>
                                    צבעים
                                </button>
                                <button class="theme-tab-button px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700" data-tab="typography">
                                    <i class="ri-text ml-2"></i>
                                    טיפוגרפיה
                                </button>
                            </div>

                            <!-- Colors Tab -->
                            <div id="colors-tab" class="theme-tab-content">
                                <?php include 'tabs/design.php'; ?>
                            </div>

                            <!-- Typography Tab -->
                            <div id="typography-tab" class="theme-tab-content hidden">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">גופן ראשי</label>
                                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option>Noto Sans Hebrew</option>
                                            <option>Assistant</option>
                                            <option>Rubik</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">גודל גופן בסיסי</label>
                                        <input type="range" min="12" max="20" value="16" class="w-full">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Panel -->
                    <div class="flex-1 bg-gray-100 flex items-center justify-center">
                        <div class="w-full h-full">
                            <iframe 
                                id="theme-preview-frame" 
                                src="<?php echo "http://" . ($store['slug'] ?? 'yogev') . ".localhost:8888/?preview=1"; ?>" 
                                class="w-full h-full border-0">
                            </iframe>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
                    <button id="cancel-theme-changes" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                        ביטול
                    </button>
                    <button id="save-theme-changes" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        שמור שינויים
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Section Modal -->
    <div id="add-section-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[80vh] overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">הוסף סקשן</h2>
                    <button id="close-add-section-modal" class="text-gray-400 hover:text-gray-600">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="p-6 overflow-y-auto" style="max-height: 500px;">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Sections -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">סקשנים בסיסיים</h3>
                            <div class="space-y-3">
                                <div class="section-template border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-colors" data-section="hero">
                                    <div class="flex items-center">
                                        <i class="ri-image-line text-gray-600 ml-3"></i>
                                        <div>
                                            <h4 class="font-medium text-gray-900">Hero</h4>
                                            <p class="text-sm text-gray-500">תמונת פתיחה עם טקסט וכפתור</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="section-template border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-colors" data-section="text">
                                    <div class="flex items-center">
                                        <i class="ri-text text-gray-600 ml-3"></i>
                                        <div>
                                            <h4 class="font-medium text-gray-900">טקסט</h4>
                                            <p class="text-sm text-gray-500">בלוק טקסט פשוט</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="section-template border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-colors" data-section="image">
                                    <div class="flex items-center">
                                        <i class="ri-image-2-line text-gray-600 ml-3"></i>
                                        <div>
                                            <h4 class="font-medium text-gray-900">תמונה</h4>
                                            <p class="text-sm text-gray-500">תמונה בודדת</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Layout Sections -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">פריסות</h3>
                            <div class="space-y-3">
                                <div class="section-template border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-colors" data-section="two-columns">
                                    <div class="flex items-center">
                                        <i class="ri-layout-column-line text-gray-600 ml-3"></i>
                                        <div>
                                            <h4 class="font-medium text-gray-900">שתי עמודות</h4>
                                            <p class="text-sm text-gray-500">פריסה של שתי עמודות</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="section-template border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-colors" data-section="three-columns">
                                    <div class="flex items-center">
                                        <i class="ri-layout-3-line text-gray-600 ml-3"></i>
                                        <div>
                                            <h4 class="font-medium text-gray-900">שלוש עמודות</h4>
                                            <p class="text-sm text-gray-500">פריסה של שלוש עמודות</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="section-template border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-colors" data-section="four-columns">
                                    <div class="flex items-center">
                                        <i class="ri-layout-4-line text-gray-600 ml-3"></i>
                                        <div>
                                            <h4 class="font-medium text-gray-900">ארבע עמודות</h4>
                                            <p class="text-sm text-gray-500">פריסה של ארבע עמודות</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/hero-settings.js"></script>
    <script src="assets/js/sections-database.js"></script>
    <script src="assets/js/sections-settings.js"></script>
    <script src="assets/js/sections-manager-core.js"></script>
    <script src="assets/js/customizer-main.js"></script>
    <script>
        // Initialize customizer
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded - Initializing customizer...');
            const customizer = new Customizer();
            customizer.init();
        });
    </script>
</body>
</html> 