<?php
/**
 * בילדר מודולרי WYSIWYG - עמוד ראשי
 */

require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../config/database.php';
require_once '../includes/StoreResolver.php';

// בדיקת הרשאות
requireAuth();

// חיבור למסד נתונים
$pdo = Database::getInstance()->getConnection();

// אתחול מנהל החנות
$storeResolver = new StoreResolver();
$store = $storeResolver->getCurrentStore();

if (!$store) {
    header('Location: ../admin/');
    exit;
}

// טעינת נתוני הדף
try {
    $stmt = $pdo->prepare("SELECT page_data, is_published FROM builder_pages WHERE store_id = ? AND page_type = 'home' ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute([$store['id']]);
    $pageData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $sections = $pageData ? json_decode($pageData['page_data'], true) : [];
    $isPublished = $pageData ? (bool)$pageData['is_published'] : false;
} catch (Exception $e) {
    $sections = [];
    $isPublished = false;
}
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>עורך האתר - <?php echo htmlspecialchars($store['name']); ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- Custom CSS for notifications -->
    <style>
        .notification-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            pointer-events: none;
        }
        
        .notification-bubble {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
            display: flex;
            items: center;
            gap: 12px;
            font-family: 'Noto Sans Hebrew', sans-serif;
            font-weight: 500;
            max-width: 400px;
            pointer-events: auto;
            transform: translateY(-100px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        .notification-bubble.show {
            transform: translateY(0);
            opacity: 1;
        }
        
        .notification-bubble.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.4);
        }
        
        .notification-bubble.warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.4);
        }
        
        .notification-bubble.loading {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }
        
        /* Accordion Styles */
        .accordion-header:hover {
            background-color: #f9fafb;
            border-radius: 0.5rem; /* תואם ל-rounded-lg של Tailwind */
        }
        
        /* פינות מעוגלות רק עבור הheader הראשון (כשהאקורדיון סגור) */
        .accordion-header:not(.open):hover {
            border-radius: 0.5rem;
        }
        
        /* כשהאקורדיון פתוח, רק הפינות העליונות מעוגלות */
        .accordion-header.open:hover {
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .accordion-content {
            transition: max-height 0.3s ease, opacity 0.3s ease;
            overflow: hidden;
        }

        .accordion-header i[class*="arrow"] {
            transition: transform 0.2s ease;
        }
        
        .notification-icon {
            font-size: 20px;
            flex-shrink: 0;
        }
        
                 .notification-text {
             flex: 1;
             text-align: center;
         }
         
         /* אנימציות לתפריט הוספת סקשן */
         .add-section-menu {
             opacity: 0;
             transform: translateY(-10px);
             transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
             pointer-events: none;
         }
         
         .add-section-menu:not(.hidden) {
             opacity: 1;
             transform: translateY(0);
             pointer-events: auto;
         }
         
         .add-section-menu.hidden {
             opacity: 0;
             transform: translateY(-10px);
             pointer-events: none;
         }
     </style>
    
    <!-- Noto Sans Hebrew -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Hebrew:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS מותאם -->
    <link href="css/builder.css" rel="stylesheet">
</head>
<body class="bg-gray-50 h-screen overflow-hidden" dir="rtl">
    
    <!-- Toolbar עליון -->
    <div class="toolbar">
        <div class="flex items-center gap-4">
            <h1 class="text-lg font-semibold text-gray-900">עורך האתר</h1>
            <div class="h-4 w-px bg-gray-300"></div>
            <span class="text-sm text-gray-600"><?php echo htmlspecialchars($store['name']); ?></span>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- מצב תצוגה -->
            <div class="flex items-center bg-gray-100 rounded-lg p-1">
                <button id="desktopView" class="device-btn figma-button bg-white text-gray-700 text-sm" data-device="desktop">
                    <i class="ri-computer-line"></i>
                    מחשב
                </button>
                <button id="mobileView" class="device-btn text-gray-600 px-3 py-2 text-sm hover:text-gray-900" data-device="mobile">
                    <i class="ri-smartphone-line"></i>
                    מובייל
                </button>
            </div>
            
            <!-- כפתור פעולה -->
            <button id="saveBtn" class="figma-button bg-blue-600 hover:bg-blue-700 text-white">
                <i class="ri-save-line"></i>
                שמור ופרסם
            </button>
        </div>
    </div>
    
    <div class="flex h-full" dir="rtl">
        <!-- פאנל צד ימין - סקשנים -->
        <div class="figma-sidebar w-80 h-full overflow-y-auto">
            <div class="p-5">
                
                <!-- כותרת + כפתור הוסף סקשן -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-base font-semibold text-gray-900">סקשנים</h2>
                    
                    <!-- כפתור הוסף סקשן עם תפריט -->
                    <div class="add-section-trigger relative">
                        <button class="figma-button">
                            <i class="ri-add-line text-lg"></i>
                        </button>
                        
                        <!-- תפריט סוגי סקשנים -->
                        <div class="add-section-menu absolute left-0 top-full mt-2 bg-white rounded-lg shadow-lg border border-gray-200 py-2 w-48 hidden" style="z-index: 9999;">
                            <button class="add-section-btn w-full px-4 py-2 text-right hover:bg-gray-50 flex items-center gap-3 justify-end" data-type="hero">
                                <span>סקשן הירו</span>
                                <i class="ri-image-line text-blue-600"></i>
                            </button>
                            <button class="add-section-btn w-full px-4 py-2 text-right hover:bg-gray-50 flex items-center gap-3 justify-end" data-type="text">
                                <span>בלוק טקסט</span>
                                <i class="ri-text text-green-600"></i>
                            </button>
                            <button class="add-section-btn w-full px-4 py-2 text-right hover:bg-gray-50 flex items-center gap-3 justify-end" data-type="products">
                                <span>רשת מוצרים</span>
                                <i class="ri-shopping-bag-line text-purple-600"></i>
                            </button>
                            <button class="add-section-btn w-full px-4 py-2 text-right hover:bg-gray-50 flex items-center gap-3 justify-end" data-type="categories">
                                <span>רשת קטגוריות</span>
                                <i class="ri-grid-line text-orange-600"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- רשימת סקשנים -->
                <div id="sectionsList" class="space-y-2">
                    <!-- סקשנים יווצרו כאן דינמית -->
                </div>
                
                <!-- מצב ריק -->
                <div id="emptySections" class="text-center py-12 text-gray-500" style="display: none;">
                    <i class="ri-layout-line text-4xl mb-4 text-gray-300"></i>
                    <p class="text-sm">עדיין לא הוספת סקשנים</p>
                    <p class="text-xs mt-1">לחץ על הכפתור + להתחלה</p>
                </div>
            </div>
            
            <!-- פאנל הגדרות -->
            <div id="settingsPanel" class="border-t border-gray-200 p-5" style="display: none;">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">הגדרות סקשן</h3>
                    <button id="closeSettings" class="text-gray-400 hover:text-gray-600">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
                
                <div id="settingsContent">
                    <!-- תוכן הגדרות יוצג כאן -->
                </div>
            </div>
        </div>
        
        <!-- iframe - תצוגת האתר -->
        <div class="flex-1 bg-white">
            <iframe 
                id="previewFrame" 
                src="../store-front/home.php?preview=1&store=<?php echo urlencode($store['slug']); ?>"
                class="w-full h-full border-none">
            </iframe>
        </div>
    </div>
    
    <!-- משתנים גלובליים -->
    <script>
        window.builderConfig = {
            sections: <?php echo json_encode($sections); ?>,
            storeSlug: '<?php echo $store['slug']; ?>',
            storeId: <?php echo $store['id']; ?>,
            isPublished: <?php echo json_encode($isPublished); ?>
        };
    </script>
    
    <!-- Device Sync Manager - מנהל סינכרון מכשירים -->
    <script src="js/device-sync-manager.js"></script>
    
    <!-- קבצי JavaScript מודולריים -->
    <script src="js/builder-core.js"></script>
    <script src="js/sections-manager.js"></script>
    <script src="js/settings-manager.js"></script>
    <script src="js/builder-main.js"></script>
    
    <!-- Simple Background Handler -->
    <script src="js/simple-background-handler.js"></script>
    
    <!-- Responsive Typography Handler -->
    <script src="js/responsive-typography-handler.js"></script>
    
    <!-- Responsive Height Handler -->
    <script src="js/responsive-height-handler.js"></script>
    
    <!-- Section Width Handler -->
    <script src="js/section-width-handler.js"></script>
    
    <!-- Accordion Handler -->
    <script src="js/accordion-handler.js"></script>
    
    <!-- Buttons Repeater Handler -->
    <script src="js/buttons-repeater-handler.js"></script>
    
    <!-- Notifications Container -->
    <div id="notificationContainer" class="notification-container"></div>
    
</body>
</html> 