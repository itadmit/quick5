<?php
/**
 * עורך הבילדר - editor/index.php
 * מציג iframe עם store-front/home.php ופאנל הגדרות לעריכה
 */

require_once '../includes/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/StoreResolver.php';

// חיבור למסד נתונים
$pdo = Database::getInstance()->getConnection();

// בדיקת הרשאות
requireAuth();

// אתחול מנהל החנות
$storeResolver = new StoreResolver();
$store = $storeResolver->getCurrentStore();

if (!$store) {
    http_response_code(404);
    include '../404.php';
    exit;
}

// טעינת נתוני הדף הנוכחיים
try {
    $stmt = $pdo->prepare("SELECT id, page_data, is_published FROM builder_pages WHERE store_id = ? AND page_type = 'home' ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute([$store['id']]);
    $currentPage = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentPage) {
        // יצירת דף חדש אם לא קיים
        $defaultData = json_encode([
            [
                'type' => 'hero',
                'id' => 'hero_' . uniqid(),
                'settings' => [
                    'title' => 'ברוכים הבאים לחנות שלנו',
                    'subtitle' => 'גלו את המוצרים הטובים ביותר במחירים הטובים ביותר',
                    'bgColor' => '#3B82F6',
                    'titleColor' => '#FFFFFF',
                    'subtitleColor' => '#E5E7EB'
                ]
            ]
        ]);
        
        $stmt = $pdo->prepare("INSERT INTO builder_pages (store_id, page_type, page_name, page_data, is_published) VALUES (?, 'home', 'עמוד בית', ?, 0)");
        $stmt->execute([$store['id'], $defaultData]);
        $currentPage = [
            'id' => $pdo->lastInsertId(),
            'page_data' => $defaultData,
            'is_published' => 0
        ];
    }
    
    $sections = json_decode($currentPage['page_data'], true) ?: [];
} catch (Exception $e) {
    error_log("Error loading page data: " . $e->getMessage());
    $sections = [];
}

?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>עורך הבילדר - <?php echo htmlspecialchars($store['store_name']); ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- Noto Sans Hebrew Font -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Hebrew:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Noto Sans Hebrew', sans-serif;
        }
        
        .editor-layout {
            height: 100vh;
            display: grid;
            grid-template-columns: 320px 1fr;
            grid-template-rows: 60px 1fr;
            grid-template-areas: 
                "toolbar toolbar"
                "sidebar preview";
        }
        
        .editor-toolbar {
            grid-area: toolbar;
            background: #1f2937;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
        }
        
        .editor-sidebar {
            grid-area: sidebar;
            background: #f9fafb;
            border-left: 1px solid #e5e7eb;
            overflow-y: auto;
        }
        
        .editor-preview {
            grid-area: preview;
            background: #e5e7eb;
            position: relative;
        }
        
        .preview-frame {
            width: 100%;
            height: 100%;
            border: none;
            background: white;
        }
        
        .preview-container {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        
        /* כלים צפים על ה-iframe */
        .floating-tools {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 1000;
            display: flex;
            gap: 8px;
        }
        
        .device-toggle {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .device-toggle:hover {
            background: rgba(0, 0, 0, 0.9);
        }
        
        .device-toggle.active {
            background: #3B82F6;
        }
    </style>
</head>
<body class="bg-gray-100">

    <div class="editor-layout">
        <!-- סרגל כלים עליון -->
        <div class="editor-toolbar">
            <div class="flex items-center gap-4">
                <h1 class="text-lg font-semibold">עורך הבילדר</h1>
                <span class="text-sm text-gray-300"><?php echo htmlspecialchars($store['store_name']); ?></span>
            </div>
            
            <div class="flex items-center gap-3">
                <button id="saveBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                    <i class="ri-save-line ml-1"></i>
                    שמור
                </button>
                
                <button id="publishBtn" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                    <i class="ri-global-line ml-1"></i>
                    פרסם
                </button>
                
                <button id="previewBtn" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                    <i class="ri-eye-line ml-1"></i>
                    תצוגה מקדימה
                </button>
            </div>
        </div>
        
        <!-- סייד-בר הגדרות -->
        <div class="editor-sidebar">
            <!-- טאבים -->
            <div class="border-b border-gray-200">
                <div class="flex">
                    <button class="tab-btn flex-1 px-4 py-3 text-sm font-medium text-center border-b-2 border-blue-500 text-blue-600 bg-blue-50" data-tab="sections">
                        <i class="ri-layout-grid-line ml-1"></i>
                        סקשנים
                    </button>
                    <button class="tab-btn flex-1 px-4 py-3 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="settings">
                        <i class="ri-settings-3-line ml-1"></i>
                        הגדרות
                    </button>
                </div>
            </div>
            
            <!-- תוכן הטאבים -->
            <div class="tab-content">
                <!-- טאב סקשנים -->
                <div id="sectionsTab" class="tab-panel p-4">
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">הוסף סקשן</h3>
                        <div class="grid grid-cols-2 gap-2">
                            <button class="add-section-btn p-3 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-section="hero">
                                <i class="ri-image-line mb-1 block text-lg"></i>
                                הירו
                            </button>
                            <button class="add-section-btn p-3 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-section="category-grid">
                                <i class="ri-grid-line mb-1 block text-lg"></i>
                                גריד קטגוריות
                            </button>
                            <button class="add-section-btn p-3 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-section="product-slider">
                                <i class="ri-slideshow-line mb-1 block text-lg"></i>
                                סלידר מוצרים
                            </button>
                            <button class="add-section-btn p-3 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors" data-section="text-block">
                                <i class="ri-file-text-line mb-1 block text-lg"></i>
                                בלוק טקסט
                            </button>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">סקשנים קיימים</h3>
                        <div id="existingSections" class="space-y-2">
                            <!-- הסקשנים יטענו כאן דינמית -->
                        </div>
                    </div>
                </div>
                
                <!-- טאב הגדרות -->
                <div id="settingsTab" class="tab-panel p-4 hidden">
                    <div id="sectionSettings">
                        <div class="text-center text-gray-500 py-8">
                            <i class="ri-settings-3-line text-2xl mb-2 block"></i>
                            <p class="text-sm">בחר סקשן לעריכה</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- תצוגה מקדימה -->
        <div class="editor-preview">
            <div class="floating-tools">
                <button class="device-toggle active" data-device="desktop">
                    <i class="ri-computer-line ml-1"></i>
                    דסקטופ
                </button>
                <button class="device-toggle" data-device="tablet">
                    <i class="ri-tablet-line ml-1"></i>
                    טאבלט
                </button>
                <button class="device-toggle" data-device="mobile">
                    <i class="ri-smartphone-line ml-1"></i>
                    מובייל
                </button>
            </div>
            
            <div class="preview-container">
                <iframe id="previewFrame" 
                        class="preview-frame" 
                        src="../store-front/home.php?preview=1&store=<?php echo urlencode($store['slug']); ?>">
                </iframe>
            </div>
        </div>
    </div>

    <!-- נתונים גלובליים -->
    <script>
        window.builderData = {
            storeId: <?php echo json_encode($store['id']); ?>,
            pageId: <?php echo json_encode($currentPage['id']); ?>,
            sections: <?php echo json_encode($sections); ?>,
            isPublished: <?php echo json_encode((bool)$currentPage['is_published']); ?>
        };
    </script>

    <!-- סקריפטים -->
    <script src="js/core.js"></script>
    <script src="js/render-iframe.js"></script>
    <script src="js/builder.js"></script>

</body>
</html> 