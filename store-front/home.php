<?php
/**
 * עמוד בית של החנות - store-front/home.php
 * מציג את הדף הנבנה מסקשנים דינמיים
 */

require_once '../includes/config.php';
require_once '../config/database.php';
require_once '../includes/StoreResolver.php';

// חיבור למסד נתונים
$pdo = Database::getInstance()->getConnection();

// אתחול מנהל החנות
$storeResolver = new StoreResolver();
$store = $storeResolver->getCurrentStore();

if (!$store) {
    http_response_code(404);
    include '../404.php';
    exit;
}

// טעינת נתוני הדף מבסיס הנתונים
try {
    $stmt = $pdo->prepare("SELECT page_data, is_published FROM builder_pages WHERE store_id = ? AND page_type = 'home' ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute([$store['id']]);
    $pageData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pageData || !$pageData['is_published']) {
        // אם אין דף פרסום - הצג דף ברירת מחדל
        $sections = getDefaultSections();
    } else {
        $sections = json_decode($pageData['page_data'], true) ?: getDefaultSections();
    }
} catch (Exception $e) {
    error_log("Error loading page data: " . $e->getMessage());
    $sections = getDefaultSections();
}

/**
 * סקשנים ברירת מחדל
 */
function getDefaultSections() {
    return [
        [
            'type' => 'hero',
            'id' => 'hero_1',
            'settings' => [
                'title' => 'ברוכים הבאים לחנות שלנו',
                'subtitle' => 'גלו את המוצרים הטובים ביותר במחירים הטובים ביותר',
                'bgColor' => '#3B82F6',
                'titleColor' => '#FFFFFF',
                'subtitleColor' => '#E5E7EB'
            ]
        ]
    ];
}

?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($store['store_name']); ?></title>
    
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
    </style>
</head>
<body class="bg-gray-50">
    
    <!-- הדר -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <h1 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($store['store_name']); ?></h1>
                </div>
                
                <nav class="hidden md:flex items-center gap-6">
                    <a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">בית</a>
                    <a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">מוצרים</a>
                    <a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">קטגוריות</a>
                    <a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">צור קשר</a>
                </nav>
                
                <div class="flex items-center gap-3">
                    <button class="p-2 text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="ri-search-line text-xl"></i>
                    </button>
                    <button class="p-2 text-gray-600 hover:text-gray-900 transition-colors relative">
                        <i class="ri-shopping-cart-line text-xl"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- תוכן דינמי מסקשנים -->
    <main>
        <?php if (!empty($sections)): ?>
            <?php foreach ($sections as $section): ?>
                <?php renderSection($section); ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="container mx-auto px-4 py-16 text-center">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">הדף בבנייה</h2>
                <p class="text-gray-600">בקרוב יועמד דף זה עם תוכן מעניין!</p>
            </div>
        <?php endif; ?>
    </main>

    <!-- פוטר -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4"><?php echo htmlspecialchars($store['store_name']); ?></h3>
                    <p class="text-gray-400">החנות המקוונת הטובה ביותר עבור כל הצרכים שלכם</p>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">קישורים מהירים</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">מוצרים</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">קטגוריות</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">מידע</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">צור קשר</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">תמיכה</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">מדיניות החזרות</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">תנאי שימוש</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">מדיניות פרטיות</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($store['store_name']); ?>. כל הזכויות שמורות.</p>
            </div>
        </div>
    </footer>

</body>
</html>

<?php
/**
 * רינדור סקשן
 */
function renderSection($section) {
    $sectionType = $section['type'];
    $sectionPath = "../editor/settings/sections/{$sectionType}/template.php";
    
    if (file_exists($sectionPath)) {
        include $sectionPath;
    } else {
        // fallback לסקשן בסיסי
        echo '<div class="py-16 bg-gray-100 text-center">';
        echo '<h2 class="text-xl text-gray-800">סקשן: ' . htmlspecialchars($sectionType) . '</h2>';
        echo '</div>';
    }
}
?> 