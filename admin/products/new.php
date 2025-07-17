<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/ProductManager.php';

$auth = new Authentication();
$auth->requireLogin();

$currentUser = $auth->getCurrentUser();
if (!$currentUser) {
    header('Location: /admin/login.php');
    exit;
}

// קבלת מידע החנות
require_once __DIR__ . '/../../config/database.php';
$stmt = Database::getInstance()->getConnection()->prepare("
    SELECT * FROM stores WHERE user_id = ? LIMIT 1
");
$stmt->execute([$currentUser['id']]);
$store = $stmt->fetch();

if (!$store) {
    header('Location: /admin/');
    exit;
}

$productManager = new ProductManager();

// טיפול בשמירת מוצר
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productData = [
        'name' => $_POST['name'] ?? '',
        'slug' => $_POST['slug'] ?? null,
        'description' => $_POST['description'] ?? '',
        'short_description' => $_POST['short_description'] ?? '',
        'price' => !empty($_POST['price']) ? $_POST['price'] : null,
        'compare_price' => !empty($_POST['compare_price']) ? $_POST['compare_price'] : null,
        'cost_price' => !empty($_POST['cost_price']) ? $_POST['cost_price'] : null,
        'sku' => $_POST['sku'] ?? null,
        'barcode' => $_POST['barcode'] ?? null,
        'track_inventory' => isset($_POST['track_inventory']) ? 1 : 0,
        'inventory_quantity' => !empty($_POST['inventory_quantity']) ? $_POST['inventory_quantity'] : 0,
        'allow_backorders' => isset($_POST['allow_backorders']) ? 1 : 0,
        'weight' => !empty($_POST['weight']) ? $_POST['weight'] : null,
        'requires_shipping' => isset($_POST['requires_shipping']) ? 1 : 0,
        'is_physical' => isset($_POST['is_physical']) ? 1 : 0,
        'status' => $_POST['status'] ?? 'active',
        'featured' => isset($_POST['featured']) ? 1 : 0,
        'vendor' => $_POST['vendor'] ?? null,
        'product_type' => $_POST['product_type'] ?? null,
        'tags' => $_POST['tags'] ?? null,
        'seo_title' => $_POST['seo_title'] ?? null,
        'seo_description' => $_POST['seo_description'] ?? null,
        'seo_keywords' => $_POST['seo_keywords'] ?? null,
        'categories' => $_POST['categories'] ?? [],
        'media' => $_POST['media'] ?? [],
        'attributes' => $_POST['attributes'] ?? [],
        'variants' => $_POST['variants'] ?? [],
        'accordions' => $_POST['accordions'] ?? [],
        'badges' => $_POST['badges'] ?? [],
        'related_products' => $_POST['related_products'] ?? [],
        'related_types' => $_POST['related_types'] ?? [],
        'upsell_products' => $_POST['upsell_products'] ?? [],
        'upsell_descriptions' => $_POST['upsell_descriptions'] ?? [],
        'bundles' => $_POST['bundles'] ?? [],
        'auto_suggest_category' => isset($_POST['auto_suggest_category']) ? 1 : 0,
        'auto_suggest_price' => isset($_POST['auto_suggest_price']) ? 1 : 0,
        'auto_suggest_vendor' => isset($_POST['auto_suggest_vendor']) ? 1 : 0,
        'auto_suggest_tags' => isset($_POST['auto_suggest_tags']) ? 1 : 0,
        'gallery_attribute' => $_POST['gallery_attribute'] ?? null,
        'attribute_media' => $_POST['attribute_media'] ?? []
    ];
    
    $result = $productManager->createProduct($store['id'], $productData);
    
    if ($result['success']) {
        header('Location: /admin/products/?created=' . $result['product_id']);
        exit;
    } else {
        $error = $result['message'];
    }
}

// קבלת קטגוריות
$stmt = Database::getInstance()->getConnection()->prepare("
    SELECT * FROM categories WHERE store_id = ? ORDER BY name ASC
");
$stmt->execute([$store['id']]);
$categories = $stmt->fetchAll();
$pageTitle = 'מוצר חדש';
?>

<?php include '../templates/header.php'; ?>
    <link rel="stylesheet" href="/admin/assets/css/style.css">
    <script src="/admin/assets/js/slug-manager.js"></script>
    <script src="/admin/assets/js/image-upload-handler.js"></script>
    <script src="/admin/assets/js/product-form.js"></script>
    <script src="/admin/assets/js/product-form-variants.js"></script>
    <script src="/admin/assets/js/color-auto-picker.js"></script>
    <script>
        // Store info for image upload
        window.storeId = <?= $store['id'] ?>;
        
        // Additional scripts specific to product page
    </script>
</head>
<body class="bg-gray-50">

    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-4">
                    <a href="/admin/" class="text-gray-600 hover:text-gray-900">
                        <i class="ri-arrow-right-line text-xl"></i>
                    </a>
                    <h1 class="text-xl font-semibold text-gray-900">מוצר חדש</h1>
                </div>
                
                <div class="flex items-center gap-3">
                    <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        שמירה כטיוטה
                    </button>
                    <button type="submit" form="product-form" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700">
                        פרסום מוצר
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <?php if (isset($error)): ?>
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="ri-error-warning-line text-red-500 ml-3"></i>
                    <span class="text-red-700"><?= htmlspecialchars($error) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <form id="product-form" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Basic Info -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">פרטים בסיסיים</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">שם המוצר *</label>
                            <input type="text" id="name" name="name" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="הזן שם מוצר">
                        </div>
                        
                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                                כתובת URL (slug)
                                <span class="text-xs text-gray-500 font-normal">- נוצרת אוטומטית</span>
                            </label>
                            <div class="relative">
                                <input type="text" id="slug" name="slug"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="slug-נוצר-אוטומטית">
                                <div id="slug-indicator" class="absolute left-3 top-2 hidden">
                                    <div id="slug-available" class="hidden flex items-center">
                                        <i class="ri-check-line text-green-500"></i>
                                        <span class="text-xs text-green-600 mr-1">זמין</span>
                                    </div>
                                    <div id="slug-taken" class="hidden flex items-center">
                                        <i class="ri-close-line text-red-500"></i>
                                        <span class="text-xs text-red-600 mr-1">תפוס</span>
                                    </div>
                                    <div id="slug-checking" class="hidden flex items-center">
                                        <i class="ri-loader-4-line text-blue-500 animate-spin"></i>
                                        <span class="text-xs text-blue-600 mr-1">בודק...</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                <span id="slug-preview">domain.com/product/</span><span id="slug-text"></span>
                            </p>
                        </div>
                        
                        <div>
                            <label for="short_description" class="block text-sm font-medium text-gray-700 mb-2">תיאור קצר</label>
                            <textarea id="short_description" name="short_description" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="תיאור קצר שיופיע ברשימת המוצרים (עד 500 תווים)"></textarea>
                        </div>
                        
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">תיאור מפורט</label>
                            <textarea id="description" name="description" rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="תיאור מלא ומפורט של המוצר..."></textarea>
                        </div>
                        
                        <!-- Product Details Row - removed, moving to inventory section -->
                        <!-- Vendor and Type Row - removed, moving to inventory section -->
                        
                        <!-- Tags moved to sidebar after badges section -->
                    </div>
                </div>
                
                <!-- כל הפרטים הועברו לעמודה השמאלית -->
                
                <!-- Pricing Section -->
                <div id="pricing-section" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">תמחור</h2>
                    
                    <div id="general-pricing" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">מחיר רגיל *</label>
                            <div class="relative">
                                <input type="number" id="price" name="price" step="0.01" min="0" required
                                    class="w-full px-3 py-2 pl-8 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <span class="absolute left-3 top-2 text-gray-500">₪</span>
                            </div>
                        </div>
                        
                        <div>
                            <label for="compare_price" class="block text-sm font-medium text-gray-700 mb-2">מחיר לפני הנחה</label>
                            <div class="relative">
                                <input type="number" id="compare_price" name="compare_price" step="0.01" min="0"
                                    class="w-full px-3 py-2 pl-8 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <span class="absolute left-3 top-2 text-gray-500">₪</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">יוצג כמחיר מחוק אם גבוה ממחיר הרגיל</p>
                        </div>
                        
                        <div>
                            <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-2">מחיר עלות</label>
                            <div class="relative">
                                <input type="number" id="cost_price" name="cost_price" step="0.01" min="0"
                                    class="w-full px-3 py-2 pl-8 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <span class="absolute left-3 top-2 text-gray-500">₪</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">לחישובי רווחיות (לא יוצג ללקוחות)</p>
                        </div>
                    </div>
                    
                    <div id="variants-pricing-notice" class="hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center gap-2">
                            <i class="ri-information-line text-blue-600"></i>
                            <span class="text-sm text-blue-800">כשיש וריאציות, התמחור מוגדר בנפרד לכל וריאציה בטבלת הוריאציות למטה</span>
                        </div>
                    </div>
                </div>

                <!-- סעיף SEO הועבר לעמודה השמאלית -->

                <!-- Media Gallery -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">גלריית תמונות</h2>
                    
                    <div id="media-gallery" class="space-y-4">
                        <div id="media-upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-400 transition-colors">
                            <i class="ri-image-add-line text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-600 mb-2">גרור תמונות לכאן או לחץ להעלאה</p>
                            <input type="file" id="media-upload" multiple accept="image/*,video/*" class="hidden">
                            <button type="button" class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100">
                                בחר קבצים
                            </button>
                        </div>
                        
                        <div id="media-preview" class="grid grid-cols-2 md:grid-cols-4 gap-4 hidden">
                            <!-- Media items will be added here -->
                        </div>
                        
                        <!-- Gallery Settings - Only visible when variants are enabled -->
                        <div id="gallery-settings" class="hidden mt-6 bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="ri-gallery-line text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-blue-900">הגדרת גלרייה למוצר</h4>
                                        <p class="text-xs text-blue-700">חלק את התמונות בגלרייה לפי ערכי מאפיין (למשל: לפי צבע)</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="enable-gallery-attribute" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            
                            <div id="gallery-attribute-settings" class="hidden">
                                <select name="gallery_attribute" id="gallery-attribute-select" 
                                    class="w-full px-3 py-2 border border-blue-300 rounded-lg text-sm bg-white">
                                    <option value="">בחר מאפיין לחלוקת הגלרייה</option>
                                </select>
                                <p class="text-xs text-blue-600 mt-2">לאחר בחירת מאפיין, תוכל להעלות תמונות ספציפיות לכל ערך בסעיף "גלרייה לפי ערכי מאפיין" למטה</p>
                            </div>
                        </div>
                        
                        <!-- Gallery per attribute value -->
                        <div id="gallery-per-attribute" class="hidden mt-6">
                            <div class="bg-amber-50 rounded-lg p-4 border border-amber-200">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center">
                                        <i class="ri-palette-line text-amber-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-amber-900">גלרייה לפי ערכי מאפיין</h4>
                                        <p class="text-xs text-amber-700">העלה תמונות ספציפיות לכל ערך (למשל: תמונות אדומות לצבע אדום)</p>
                                    </div>
                                </div>
                                <div id="attribute-galleries" class="space-y-4">
                                    <!-- Attribute galleries will be added here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Variants & Attributes -->
                <?php include 'variants.php'; ?>

                <!-- Custom Fields (Accordions/Tabs) -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">שדות מותאמים אישית</h2>
                    <p class="text-sm text-gray-600 mb-4">הוסף שדות נוספים שיוצגו כאקורדיונים או טאבים בעמוד המוצר</p>
                    
                    <div id="accordions-container" class="space-y-4">
                        <!-- No default accordion items -->
                    </div>
                    
                    <button type="button" id="add-accordion" 
                        class="mt-4 flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <div class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="ri-add-line text-blue-600 text-xs"></i>
                        </div>
                        הוסף שדה מותאם
                    </button>
                </div>

                <!-- Upsell / Cross-sell -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <button type="button" class="w-full p-6 text-right flex items-center justify-between hover:bg-gray-50 transition-colors" 
                        onclick="toggleAccordion('upsell-cross-sell')">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="ri-link-m text-purple-600"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-medium text-gray-900">מוצרים משלימים ומכירות צולבות</h2>
                                <p class="text-sm text-gray-500">הגדר מוצרים קשורים, שדרוגים וחבילות למכירה משולבת</p>
                            </div>
                        </div>
                        <i class="ri-arrow-down-s-line text-gray-400 transform transition-transform duration-200" id="upsell-cross-sell-arrow"></i>
                    </button>
                    
                    <div id="upsell-cross-sell-content" class="hidden p-6 pt-0">
                        <div class="space-y-8">
                        
                            <!-- Related Products -->
                            <div class="bg-blue-50 rounded-xl p-6 border border-blue-100 mt-4">
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="ri-links-line text-blue-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3 class="text-lg font-medium text-gray-800">מוצרים קשורים (Cross-sell)</h3>
                                            <div class="relative group">
                                                <div class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center cursor-help">
                                                    <i class="ri-information-line text-blue-600 text-xs"></i>
                                                </div>
                                                <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block z-10">
                                                    <div class="bg-gray-900 text-white text-xs rounded-lg p-3 whitespace-nowrap shadow-lg">
                                                        <div class="font-medium mb-1">דוגמא למוצרים קשורים:</div>
                                                        <div>• למוצר "אוזניות" → מוצר קשור: "מטען נייד"</div>
                                                        <div>• למוצר "חולצה" → מוצר קשור: "מכנסיים"</div>
                                                        <div class="mt-1 text-gray-300">יוצגו בעמוד המוצר כ"מוצרים משלימים"</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-sm text-blue-700">מוצרים שיוצגו כ"מוצרים דומים" או "לקוחות שקנו מוצר זה קנו גם"</p>
                                    </div>
                                </div>
                            
                            <div id="recommended-products" class="space-y-2">
                                <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg">
                                    <input type="text" name="related_products[]" 
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm product-search"
                                        placeholder="חפש מוצר מומלץ... (התחל להקליד)">
                                    <input type="hidden" name="related_types[]" value="recommended">
                                    <button type="button" onclick="this.closest('.flex').remove()" 
                                        class="text-red-500 hover:text-red-700 p-1">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                            </div>
                            
                                <button type="button" id="add-recommended-product" 
                                    class="mt-3 flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition-colors">
                                    <div class="w-4 h-4 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="ri-add-line text-blue-600 text-xs"></i>
                                    </div>
                                    הוסף מוצר מומלץ
                                </button>
                            </div>

                            <!-- לקוחות שרכשו גם -->
                            <div class="bg-orange-50 rounded-xl p-6 border border-orange-100">
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="ri-shopping-cart-2-line text-orange-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3 class="text-lg font-medium text-gray-800">לקוחות שרכשו גם</h3>
                                        </div>
                                        <p class="text-sm text-orange-700">מוצרים שלקוחות קנו יחד עם המוצר הזה</p>
                                    </div>
                                </div>
                            
                            <div id="frequently-bought-products" class="space-y-2">
                                <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg">
                                    <input type="text" name="related_products[]" 
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm product-search"
                                        placeholder="חפש מוצר שנרכש יחד... (התחל להקליד)">
                                    <input type="hidden" name="related_types[]" value="frequently_bought">
                                    <button type="button" onclick="this.closest('.flex').remove()" 
                                        class="text-red-500 hover:text-red-700 p-1">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                            </div>
                            
                                <button type="button" id="add-frequently-bought-product" 
                                    class="mt-3 flex items-center gap-2 px-4 py-2 text-sm font-medium text-orange-600 bg-white border border-orange-200 rounded-lg hover:bg-orange-50 transition-colors">
                                    <div class="w-4 h-4 bg-orange-100 rounded-full flex items-center justify-center">
                                        <i class="ri-add-line text-orange-600 text-xs"></i>
                                    </div>
                                    הוסף מוצר שנרכש יחד
                                </button>
                            </div>

                            <!-- השלם את הלוק -->
                            <div class="bg-purple-50 rounded-xl p-6 border border-purple-100">
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="ri-shirt-line text-purple-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3 class="text-lg font-medium text-gray-800">השלם את הלוק</h3>
                                        </div>
                                        <p class="text-sm text-purple-700">מוצרים שמשלימים את הלוק או הסגנון של המוצר</p>
                                    </div>
                                </div>
                            
                            <div id="complete-look-products" class="space-y-2">
                                <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg">
                                    <input type="text" name="related_products[]" 
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm product-search"
                                        placeholder="חפש מוצר להשלמת הלוק... (התחל להקליד)">
                                    <input type="hidden" name="related_types[]" value="complete_look">
                                    <button type="button" onclick="this.closest('.flex').remove()" 
                                        class="text-red-500 hover:text-red-700 p-1">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                            </div>
                            
                                <button type="button" id="add-complete-look-product" 
                                    class="mt-3 flex items-center gap-2 px-4 py-2 text-sm font-medium text-purple-600 bg-white border border-purple-200 rounded-lg hover:bg-purple-50 transition-colors">
                                    <div class="w-4 h-4 bg-purple-100 rounded-full flex items-center justify-center">
                                        <i class="ri-add-line text-purple-600 text-xs"></i>
                                    </div>
                                    הוסף מוצר להשלמת הלוק
                                </button>
                            </div>

                            <!-- Upsell Products -->
                            <div class="bg-green-50 rounded-xl p-6 border border-green-100">
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="ri-arrow-up-circle-line text-green-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3 class="text-lg font-medium text-gray-800">מוצרי שדרוג (Upsell)</h3>
                                            <div class="relative group">
                                                <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center cursor-help">
                                                    <i class="ri-information-line text-green-600 text-xs"></i>
                                                </div>
                                                <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block z-10">
                                                    <div class="bg-gray-900 text-white text-xs rounded-lg p-3 whitespace-nowrap shadow-lg">
                                                        <div class="font-medium mb-1">דוגמא למוצרי שדרוג:</div>
                                                        <div>• במקום "אוזניות רגילות" → "אוזניות אלחוטיות פרימיום"</div>
                                                        <div>• במקום "תיק קטן" → "תיק גדול עם תאים נוספים"</div>
                                                        <div class="mt-1 text-gray-300">יוצגו כ"שדרג את הקנייה שלך" במחיר גבוה יותר</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-sm text-green-700">מוצרים יקרים יותר שיוצעו כחלופה או שדרוג למוצר זה</p>
                                    </div>
                                </div>
                            
                            <div id="upsell-products" class="space-y-2">
                                <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg">
                                    <input type="text" name="upsell_products[]" 
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm product-search"
                                        placeholder="חפש מוצר לשדרוג...">
                                    <input type="text" name="upsell_descriptions[]" 
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                        placeholder="סיבה לשדרוג (למשל: איכות גבוהה יותר)">
                                    <button type="button" onclick="this.closest('.flex').remove()" 
                                        class="text-red-500 hover:text-red-700 p-1">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                            </div>
                            
                                <button type="button" id="add-upsell-product" 
                                    class="mt-3 flex items-center gap-2 px-4 py-2 text-sm font-medium text-green-600 bg-white border border-green-200 rounded-lg hover:bg-green-50 transition-colors">
                                    <div class="w-4 h-4 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="ri-add-line text-green-600 text-xs"></i>
                                    </div>
                                    הוסף מוצר שדרוג
                                </button>
                            </div>

                        <!-- Product Bundles -->
                        <div>
                            <h3 class="text-md font-medium text-gray-800 mb-3 flex items-center gap-2">
                                <i class="ri-gift-line text-purple-600"></i>
                                חבילות מוצרים
                            </h3>
                            <p class="text-sm text-gray-600 mb-3">צור חבילות של מספר מוצרים במחיר מיוחד</p>
                            
                            <div id="product-bundles" class="space-y-4">
                                <!-- Bundle template will be added by JS -->
                            </div>
                            
                            <button type="button" id="add-product-bundle" 
                                class="mt-3 flex items-center gap-2 px-3 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100">
                                <i class="ri-add-line"></i>
                                צור חבילה חדשה
                            </button>
                        </div>

                            <!-- Auto Suggestions -->
                            <div class="bg-yellow-50 rounded-xl p-6 border border-yellow-100">
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="ri-lightbulb-line text-yellow-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3 class="text-lg font-medium text-gray-800">הצעות אוטומטיות</h3>
                                            <div class="relative group">
                                                <div class="w-5 h-5 bg-yellow-100 rounded-full flex items-center justify-center cursor-help">
                                                    <i class="ri-information-line text-yellow-600 text-xs"></i>
                                                </div>
                                                <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block z-10">
                                                    <div class="bg-gray-900 text-white text-xs rounded-lg p-3 whitespace-nowrap shadow-lg">
                                                        <div class="font-medium mb-1">איך עובדות הצעות אוטומטיות:</div>
                                                        <div>• אם לא תסמן כלום - לא יוצגו הצעות אוטומטיות</div>
                                                        <div>• אם תסמן "קטגוריה" - יוצגו מוצרים מאותה קטגוריה</div>
                                                        <div>• אם תסמן "מחיר דומה" - יוצגו מוצרים ב±20% מהמחיר</div>
                                                        <div class="mt-1 text-gray-300">ההצעות יוצגו רק אם לא הגדרת מוצרים קשורים ידנית</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-sm text-yellow-700">המערכת תציע מוצרים באופן אוטומטי לפי הכללים שתבחר</p>
                               
                                    </div>
                                </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">הצג מוצרים מאותה קטגוריה</label>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="auto_suggest_category" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">הצג מוצרים במחיר דומה</label>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="auto_suggest_price" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">הצג מוצרים מאותו יצרן</label>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="auto_suggest_vendor" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">הצג מוצרים עם תגיות דומות</label>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="auto_suggest_tags" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                                </div>

                                <div class="mt-2 p-3 bg-yellow-100 rounded-lg">
                                            <p class="text-xs text-yellow-800">
                                                <i class="ri-information-line ml-1"></i>
                                                אם לא תבחר אף כלל, לא יוצגו הצעות אוטומטיות והמערכת תסתמך רק על המוצרים שהגדרת ידנית למעלה.
                                            </p>
                                        </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Product Status & Featured - First Box -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">סטטוס וחשיפה</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">סטטוס מוצר</label>
                            <select id="status" name="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="active">פעיל</option>
                                <option value="draft">טיוטה</option>
                                <option value="archived">בארכיון</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">סטטוס המוצר באתר</p>
                        </div>
                        
                        <div class="pt-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" id="featured" name="featured" value="1"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-sm font-medium text-gray-700">מוצר מומלץ</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1 mr-6">המוצר יוצג בקטגוריית "מומלצים" ובמקומות בולטים באתר</p>
                        </div>
                    </div>
                </div>

                <!-- Inventory Management -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">ניהול מלאי ופרטי מוצר</h3>
                    
                    <div class="space-y-6">
                        <!-- Core Product Details - Always Visible -->
                        <div class="space-y-4">
                            <!-- SKU -->
                            <div>
                                <label for="sku" class="block text-sm font-medium text-gray-700 mb-2">SKU</label>
                                <input type="text" id="sku" name="sku"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="קוד מוצר ייחודי">
                                <p class="text-xs text-gray-500 mt-1">מזהה ייחודי למוצר במערכת</p>
                            </div>
                            
                            <!-- Barcode -->
                            <div>
                                <label for="barcode" class="block text-sm font-medium text-gray-700 mb-2">ברקוד</label>
                                <input type="text" id="barcode" name="barcode"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="ברקוד המוצר">
                                <p class="text-xs text-gray-500 mt-1">ברקוד לסריקה בקופה</p>
                            </div>
                        </div>
                        
                        <!-- Accordion for Optional Details -->
                        <div class="border border-gray-200 rounded-lg">
                            <button type="button" 
                                class="w-full flex items-center justify-between p-4 text-right hover:bg-gray-50 transition-colors"
                                onclick="toggleAccordion('optional-details')">
                                <div class="flex items-center gap-2">
                                    <i class="ri-information-line text-gray-500"></i>
                                    <span class="text-sm font-medium text-gray-800">פרטים נוספים (אופציונלי)</span>
                                </div>
                                <i class="ri-arrow-down-s-line text-gray-500 transform transition-transform" id="optional-details-icon"></i>
                            </button>
                            
                            <div id="optional-details" class="hidden border-t border-gray-200 p-4 space-y-4">
                                <!-- Weight -->
                                <div>
                                    <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">משקל (ק"ג)</label>
                                    <input type="number" id="weight" name="weight" step="0.001" min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="0.000">
                                    <p class="text-xs text-gray-500 mt-1">משקל למשלוח</p>
                                </div>
                                
                                <!-- Vendor -->
                                <div>
                                    <label for="vendor" class="block text-sm font-medium text-gray-700 mb-2">יצרן/ספק</label>
                                    <input type="text" id="vendor" name="vendor"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="שם היצרן או הספק">
                                    <p class="text-xs text-gray-500 mt-1">יועיל למעקב ורכש</p>
                                </div>
                                
                                <!-- Product Type -->
                                <div>
                                    <label for="product_type" class="block text-sm font-medium text-gray-700 mb-2">סוג מוצר</label>
                                    <input type="text" id="product_type" name="product_type"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="למשל: בגדים, אלקטרוניקה, ספרים">
                                    <p class="text-xs text-gray-500 mt-1">קטגוריית המוצר הכללית</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Inventory Section - Hidden when variants are enabled -->
                        <div id="inventory-section">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="text-md font-medium text-gray-800">מעקב מלאי</h4>
                                    <p class="text-xs text-gray-500">פעל מעקב אחר כמויות במלאי ומנע מכירת יתר</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="track_inventory" name="track_inventory" value="1" checked class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            
                            <div id="inventory-fields" class="space-y-4">
                                <div id="inventory-quantity-container">
                                    <label for="inventory_quantity" class="block text-sm font-medium text-gray-700 mb-2">כמות במלאי</label>
                                    <input type="number" id="inventory_quantity" name="inventory_quantity" min="0" value="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <p class="text-xs text-gray-500 mt-1">הכמות הנוכחית הזמינה למכירה</p>
                                </div>
                                
                                <div class="pt-2" id="allow-backorders-container">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" id="allow_backorders" name="allow_backorders" value="1"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm font-medium text-gray-700">אפשר הזמנה מראש</span>
                                    </label>
                                    <p class="text-xs text-gray-500 mt-1 mr-6">אפשר ללקוחות להזמין גם כשאין במלאי</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">קטגוריות</h3>
                    
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        <?php foreach ($categories as $category): ?>
                            <div class="flex items-center gap-3">
                                <input type="checkbox" id="category_<?= $category['id'] ?>" 
                                    name="categories[]" value="<?= $category['id'] ?>"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                <label for="category_<?= $category['id'] ?>" class="text-sm text-gray-700">
                                    <?= htmlspecialchars($category['name']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="button" onclick="openNewCategoryModal()" 
                        class="mt-4 text-sm text-blue-600 hover:text-blue-700 flex items-center gap-2">
                        <div class="w-4 h-4 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="ri-add-line text-blue-600 text-xs"></i>
                        </div>
                        יצירת קטגוריה חדשה
                    </button>
                </div>

                <!-- Badges -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">מדבקות</h3>
                    
                    <div id="badges-container" class="space-y-3">
                        <!-- Badges will be added here -->
                    </div>
                    
                    <button type="button" id="add-badge" 
                        class="mt-4 flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <div class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="ri-add-line text-blue-600 text-xs"></i>
                        </div>
                        הוסף מדבקה
                    </button>
                </div>

                <!-- Tags -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">תגיות</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <label for="tag-input" class="block text-sm font-medium text-gray-700 mb-2">הוסף תגיות</label>
                            <input type="text" id="tag-input" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                placeholder="הקלד תגית ולחץ Enter או פסיק">
                            <p class="text-xs text-gray-500 mt-1">הקלד תגית ולחץ Enter או פסיק להוספה</p>
                        </div>
                        
                        <!-- Tags Display -->
                        <div id="tags-display" class="min-h-[40px] p-3 border border-gray-200 rounded-lg bg-gray-50">
                            <div id="tags-container" class="flex flex-wrap gap-2">
                                <!-- Tags will appear here as bubbles -->
                            </div>
                        </div>
                        
                        <!-- Hidden input for form submission -->
                        <input type="hidden" id="tags" name="tags" value="">
                    </div>
                </div>

                <!-- SEO Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
                        <i class="ri-search-eye-line text-green-600"></i>
                        אופטימיזציה למנועי חיפוש (SEO)
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="seo_title" class="block text-sm font-medium text-gray-700 mb-2">כותרת SEO</label>
                            <input type="text" id="seo_title" name="seo_title" maxlength="255"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="כותרת מותאמת למנועי חיפוש">
                            <p class="text-xs text-gray-500 mt-1">אם ריק, ישתמש בשם המוצר. מומלץ עד 60 תווים</p>
                        </div>
                        
                        <div>
                            <label for="seo_description" class="block text-sm font-medium text-gray-700 mb-2">תיאור SEO</label>
                            <textarea id="seo_description" name="seo_description" rows="3" maxlength="500"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="תיאור קצר המסביר מה המוצר ולמה לקנות אותו"></textarea>
                            <p class="text-xs text-gray-500 mt-1">יוצג בתוצאות החיפוש. מומלץ 150-160 תווים</p>
                        </div>
                        
                        <div>
                            <label for="seo_keywords" class="block text-sm font-medium text-gray-700 mb-2">מילות מפתח</label>
                            <input type="text" id="seo_keywords" name="seo_keywords"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="מילות מפתח מופרדות בפסיקים">
                            <p class="text-xs text-gray-500 mt-1">מילים שאנשים יחפשו כדי למצוא את המוצר</p>
                        </div>
                    </div>
                </div>

            </div>

        </form>
    </div>

    <!-- New Category Modal -->
    <div id="newCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">יצירת קטגוריה חדשה</h3>
                <button type="button" onclick="closeNewCategoryModal()" 
                    class="text-gray-400 hover:text-gray-600">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            
            <form id="newCategoryForm" class="p-6 space-y-4">
                <div>
                    <label for="new_category_name" class="block text-sm font-medium text-gray-700 mb-2">שם הקטגוריה *</label>
                    <input type="text" id="new_category_name" name="name" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="הזן שם קטגוריה">
                </div>
                
                <div>
                    <label for="new_category_description" class="block text-sm font-medium text-gray-700 mb-2">תיאור</label>
                    <textarea id="new_category_description" name="description" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="תיאור אופציונלי לקטגוריה"></textarea>
                </div>
                
                <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeNewCategoryModal()" 
                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        ביטול
                    </button>
                    <button type="submit" 
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        <span class="submit-text">יצור קטגוריה</span>
                        <span class="loading-text hidden">יוצר...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Accordion functionality
        function toggleAccordion(id) {
            // Try to find the content element with or without "-content" suffix
            let content = document.getElementById(id);
            if (!content) {
                content = document.getElementById(id + '-content');
            }
            
            // Try to find the icon element with different possible suffixes
            let icon = document.getElementById(id + '-icon');
            if (!icon) {
                icon = document.getElementById(id + '-arrow');
            }
            
            // Only proceed if we found the content element
            if (content) {
                if (content.classList.contains('hidden')) {
                    content.classList.remove('hidden');
                    if (icon) icon.style.transform = 'rotate(180deg)';
                } else {
                    content.classList.add('hidden');
                    if (icon) icon.style.transform = 'rotate(0deg)';
                }
            } else {
                console.warn('Accordion element not found:', id);
            }
        }

        // Variants switcher - show/hide gallery settings AND inventory
        document.getElementById('has-variants').addEventListener('change', function() {
            const gallerySettings = document.getElementById('gallery-settings');
            const inventorySection = document.getElementById('inventory-section');
            
            if (this.checked) {
                // Show gallery settings
                gallerySettings.classList.remove('hidden');
                
                // Hide inventory section - not relevant for variants
                inventorySection.classList.add('hidden');
                
                // Add notification
                if (!document.getElementById('inventory-variants-notice')) {
                    const notice = document.createElement('div');
                    notice.id = 'inventory-variants-notice';
                    notice.className = 'bg-blue-50 border border-blue-200 rounded-lg p-4';
                    notice.innerHTML = `
                        <div class="flex items-center gap-2">
                            <i class="ri-information-line text-blue-600"></i>
                            <span class="text-sm text-blue-800 font-medium">מלאי ברמת הוריאציות</span>
                        </div>
                        <p class="text-xs text-blue-700 mt-1">כאשר למוצר יש וריאציות, כמות המלאי וההזמנות מראש מנוהלים ברמת כל וריאציה בנפרד.</p>
                    `;
                    inventorySection.parentNode.insertBefore(notice, inventorySection.nextSibling);
                }
            } else {
                // Hide gallery settings
                gallerySettings.classList.add('hidden');
                
                // Show inventory section
                inventorySection.classList.remove('hidden');
                
                // Remove notification
                const notice = document.getElementById('inventory-variants-notice');
                if (notice) {
                    notice.remove();
                }
                
                // Reset gallery settings when variants are disabled
                document.getElementById('enable-gallery-attribute').checked = false;
                document.getElementById('gallery-attribute-settings').classList.add('hidden');
                document.getElementById('gallery-attribute-select').value = '';
            }
        });

        // Gallery attribute switcher
        document.getElementById('enable-gallery-attribute').addEventListener('change', function() {
            const settings = document.getElementById('gallery-attribute-settings');
            if (this.checked) {
                settings.classList.remove('hidden');
                // עדכן את הרשימה של מאפיינים זמינים
                if (typeof updateGalleryAttributeDropdown === 'function') {
                    updateGalleryAttributeDropdown();
                }
            } else {
                settings.classList.add('hidden');
                // Reset the select value when disabled
                document.getElementById('gallery-attribute-select').value = '';
                // הסתר את הגלרייה
                const gallerySection = document.getElementById('gallery-per-attribute');
                if (gallerySection) {
                    gallerySection.classList.add('hidden');
                }
            }
        });
        
        // מאזין לשינויים במאפיינים כדי לעדכן את הגלרייה
        document.addEventListener('input', function(e) {
            if (e.target.matches('input[name*="[name]"]') || e.target.matches('input[name*="[values]"][name*="[value]"]')) {
                // עדכן את הרשימה של מאפיינים זמינים עם השהיה קטנה
                if (typeof updateGalleryAttributeDropdown === 'function') {
                    setTimeout(updateGalleryAttributeDropdown, 100);
                }
            }
        });
        
        // טיפול בבחירת מאפיין לגלרייה
        document.addEventListener('change', function(e) {
            if (e.target.id === 'gallery-attribute-select') {
                if (typeof updateAttributeGalleries === 'function') {
                    updateAttributeGalleries();
                }
            }
        });
    </script>
    
    <?php include '../templates/footer.php'; ?>
</body>
</html> 