<?php
require_once '../includes/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

// Check authentication
if (!isLoggedIn()) {
    header('Location: ../admin/login.php');
    exit;
}

// Check if user is accessing builder from correct subdomain
$currentHost = $_SERVER['HTTP_HOST'] ?? '';
$isMainDomain = (strpos($currentHost, 'localhost') !== false && !strpos($currentHost, '.localhost'));

if ($isMainDomain) {
    // User is on localhost:8888/builder/ instead of store_slug.localhost:8888/builder/
    // Redirect to correct store subdomain
    
    // Get user's store
    $userId = $_SESSION['admin_user_id'] ?? $_SESSION['user_id'] ?? null;
    
    if ($userId) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT slug FROM stores WHERE user_id = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$userId]);
            $userStore = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($userStore && $userStore['slug']) {
                $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $port = $_SERVER['SERVER_PORT'] ?? '8888';
                $correctUrl = $scheme . '://' . $userStore['slug'] . '.localhost:' . $port . '/builder/';
                
                header('Location: ' . $correctUrl);
                exit;
            }
        } catch (Exception $e) {
            error_log("Builder redirect error: " . $e->getMessage());
        }
    }
    
    die('לא ניתן למצוא את החנות שלך. אנא וודא שיש לך חנות פעילה.');
}

$store = getCurrentStore();
if (!$store) {
    die('חנות לא נמצאה');
}
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>בילדר דף הבית - <?php echo htmlspecialchars($store['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Hebrew:wght@300;400;500;600;700&family=Heebo:wght@300;400;500;600;700&family=Assistant:wght@300;400;500;600;700&family=Varela+Round&family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans+Hebrew:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/builder.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'hebrew': ['Noto Sans Hebrew', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Responsive Preview Modes */
        .preview-mode-desktop #previewContainer {
            width: 100%;
            height: 100%;
        }
        
        .preview-mode-tablet #previewContainer {
            width: 768px;
            height: 1024px;
            max-width: 90%;
            max-height: 90%;
        }
        
        .preview-mode-mobile #previewContainer {
            width: 375px;
            height: 812px;
            max-width: 90%;
            max-height: 90%;
        }
        
        /* Mode button styles */
        .mode-btn.active {
            background-color: white;
            color: #1f2937;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .mode-btn:not(.active) {
            color: #6b7280;
        }
        
        .mode-btn:not(.active):hover {
            color: #1f2937;
        }
    </style>
</head>
<body class="font-hebrew bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h1 class="text-xl font-semibold text-gray-900">בילדר דף הבית</h1>
                <span class="text-sm text-gray-500"><?php echo htmlspecialchars($store['name']); ?></span>
            </div>
            
            <!-- Responsive Mode Switcher -->
            <div class="flex items-center gap-2 bg-gray-100 rounded-lg p-1">
                <button id="modeDesktop" class="mode-btn px-3 py-2 rounded-md flex items-center gap-2 transition-colors" data-mode="desktop" title="מחשב">
                    <i class="ri-computer-line text-lg"></i>
                    <span class="hidden sm:inline">מחשב</span>
                </button>
                <button id="modeTablet" class="mode-btn px-3 py-2 rounded-md flex items-center gap-2 transition-colors" data-mode="tablet" title="טאבלט">
                    <i class="ri-tablet-line text-lg"></i>
                    <span class="hidden sm:inline">טאבלט</span>
                </button>
                <button id="modeMobile" class="mode-btn px-3 py-2 rounded-md flex items-center gap-2 transition-colors" data-mode="mobile" title="מובייל">
                    <i class="ri-smartphone-line text-lg"></i>
                    <span class="hidden sm:inline">מובייל</span>
                </button>
            </div>
            
            <div class="flex items-center gap-3">
                <button id="saveBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <i class="ri-save-line"></i>
                    שמור
                </button>
                <a href="../admin/" class="px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors">
                    <i class="ri-arrow-right-line"></i>
                    חזרה לניהול
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex h-screen">
        <!-- Sidebar Container -->
        <div class="w-96 bg-white border-l border-gray-200 overflow-hidden relative">
            <!-- Sliding Container -->
            <div id="slidingContainer" class="flex transition-transform duration-300 ease-in-out" style="width: 200%; transform: translateX(0%);">
                
                <!-- Settings Panel (מוקדם יותר כי זה בעברית) -->
                
                <!-- Sections Panel -->
                <div id="sectionsPanel" class="w-96 flex-shrink-0">
                    <?php include 'sections-list/sections.php'; ?>
                </div>

                <div id="settingsPanel" class="w-96 flex-shrink-0">
                    <div id="settingsContent">
                        <!-- Settings will be loaded dynamically -->
                    </div>
                </div>
                
                
            </div>
        </div>

        <!-- Preview iframe -->
        <div class="flex-1 bg-gray-100 p-4 flex items-center justify-center">
            <div id="previewContainer" class="bg-white rounded-lg shadow-sm transition-all duration-300 h-full w-full">
                <iframe id="previewFrame" 
                        src="<?php 
                        require_once '../includes/StoreResolver.php';
                        $storeResolver = new StoreResolver();
                        echo $storeResolver->getStoreBaseUrl(); 
                        ?>?builder_preview=1" 
                        class="w-full h-full rounded-lg border-0">
                </iframe>
            </div>
        </div>
    </div>

    <!-- Load modular JavaScript files -->
    <script src="assets/js/section-manager.js"></script>
<script src="assets/js/section-actions.js"></script>
<script src="assets/js/builder.js"></script>
<script src="assets/js/sections/button-features.js"></script>

    <script>
        // Store data for JavaScript
        window.storeData = <?php echo json_encode($store); ?>;
        
        // Global responsive mode management
        window.currentResponsiveMode = 'desktop';
        
        // Initialize responsive mode switcher
        document.addEventListener('DOMContentLoaded', function() {
            initResponsiveModeSwitcher();
        });
        
        function initResponsiveModeSwitcher() {
            const modeButtons = document.querySelectorAll('.mode-btn');
            const body = document.body;
            
            modeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const mode = this.getAttribute('data-mode');
                    console.log('Mode button clicked:', mode);
                    window.setResponsiveMode(mode);
                });
            });
            
            // Set initial mode
            window.setResponsiveMode('desktop');
        }
        
        window.setResponsiveMode = function setResponsiveMode(mode) {
            window.currentResponsiveMode = mode;
            const body = document.body;
            const modeButtons = document.querySelectorAll('.mode-btn');
            
            // Update body class
            body.className = body.className.replace(/preview-mode-\w+/g, '');
            body.classList.add(`preview-mode-${mode}`);
            
            // Update button states
            modeButtons.forEach(btn => {
                btn.classList.remove('active', 'bg-white', 'text-gray-900', 'shadow-sm');
                btn.classList.add('text-gray-600');
                if (btn.getAttribute('data-mode') === mode) {
                    btn.classList.add('active', 'bg-white', 'text-gray-900', 'shadow-sm');
                    btn.classList.remove('text-gray-600');
                }
            });
            
            console.log('Responsive mode changed to:', mode);
            
            // Notify sections about mode change
            if (window.currentSection && window.currentSection.onResponsiveModeChange) {
                window.currentSection.onResponsiveModeChange(mode);
            }
            
            // Update iframe if needed
            const iframe = document.getElementById('previewFrame');
            if (iframe) {
                iframe.contentWindow.postMessage({
                    type: 'responsiveModeChanged',
                    mode: mode
                }, '*');
            }
        }
        
        // Helper function to get current responsive mode
        function getCurrentResponsiveMode() {
            return window.currentResponsiveMode;
        }
        
        // Helper function to get responsive property name
        function getResponsivePropertyName(baseName, mode = null) {
            const currentMode = mode || getCurrentResponsiveMode();
            if (currentMode === 'desktop') {
                return baseName;
            }
            return `${baseName}_${currentMode}`;
        }
        
        // Helper function to get responsive value (with fallback to desktop)
        function getResponsiveValue(data, baseName, mode = null) {
            const currentMode = mode || getCurrentResponsiveMode();
            
            if (currentMode === 'desktop') {
                return data[baseName];
            }
            
            const responsiveProperty = `${baseName}_${currentMode}`;
            const responsiveValue = data[responsiveProperty];
            
            // If responsive value exists and is not empty, use it
            // Check for meaningful values (not empty, null, undefined, or string "0")
            if (responsiveValue !== undefined && responsiveValue !== '' && responsiveValue !== null && responsiveValue !== "0") {
                return responsiveValue;
            }
            
            // Otherwise, fallback to desktop value
            return data[baseName];
        }
    </script>
</body>
</html> 