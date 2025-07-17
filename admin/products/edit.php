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

// קבלת מזהה המוצר מה-URL
$productId = $_GET['id'] ?? null;
if (!$productId) {
    header('Location: /admin/products/');
    exit;
}

$productManager = new ProductManager();

// קבלת פרטי המוצר הקיים
$stmt = Database::getInstance()->getConnection()->prepare("
    SELECT * FROM products WHERE id = ? AND store_id = ? LIMIT 1
");
$stmt->execute([$productId, $store['id']]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: /admin/products/?error=product_not_found');
    exit;
}

// קבלת הגדרות הצעות אוטומטיות
$stmt = Database::getInstance()->getConnection()->prepare("
    SELECT name, trigger_type FROM auto_suggestions WHERE store_id = ? AND is_active = 1
");
$stmt->execute([$store['id']]);
$autoSuggestions = $stmt->fetchAll();

// המרה למערך נוח לשימוש
$product['auto_suggest_category'] = 0;
$product['auto_suggest_price'] = 0;
$product['auto_suggest_vendor'] = 0;
$product['auto_suggest_tags'] = 0;

foreach ($autoSuggestions as $suggestion) {
    switch ($suggestion['trigger_type']) {
        case 'product_category':
            $product['auto_suggest_category'] = 1;
            break;
        case 'cart_value':
            $product['auto_suggest_price'] = 1;
            break;
        case 'product_specific':
            if (strpos($suggestion['name'], 'Vendor') !== false) {
                $product['auto_suggest_vendor'] = 1;
            } elseif (strpos($suggestion['name'], 'Tags') !== false) {
                $product['auto_suggest_tags'] = 1;
            }
            break;
    }
}

// קבלת קטגוריות המוצר
$stmt = Database::getInstance()->getConnection()->prepare("
    SELECT category_id FROM product_categories WHERE product_id = ?
");
$stmt->execute([$productId]);
$productCategories = array_column($stmt->fetchAll(), 'category_id');

// קבלת מדיה של המוצר
$stmt = Database::getInstance()->getConnection()->prepare("
    SELECT * FROM product_media WHERE product_id = ? ORDER BY sort_order ASC
");
$stmt->execute([$productId]);
$productMedia = $stmt->fetchAll();

// Debug - בדיקת מדיה קיימת
error_log("Media for product $productId: " . json_encode($productMedia));

// קבלת מאפיינים ווריאציות
$stmt = Database::getInstance()->getConnection()->prepare("
    SELECT pa.*, av.id as value_id, av.value, av.color_hex
    FROM product_attributes pa 
    LEFT JOIN attribute_values av ON pa.id = av.attribute_id 
    WHERE pa.product_id = ? 
    ORDER BY pa.id, av.id
");
$stmt->execute([$productId]);
$attributesData = $stmt->fetchAll();

$attributesTemp = [];
foreach ($attributesData as $attr) {
    if (!isset($attributesTemp[$attr['id']])) {
        $attributesTemp[$attr['id']] = [
            'id' => $attr['id'],
            'name' => $attr['name'],
            'type' => $attr['type'],
            'values' => []
        ];
    }
    if ($attr['value_id']) {
        $attributesTemp[$attr['id']]['values'][] = [
            'id' => $attr['value_id'],
            'value' => $attr['value'],
            'color_hex' => $attr['color_hex']
        ];
    }
}

// המרה למערך רגיל כדי שיעבוד עם variants.php
$attributes = array_values($attributesTemp);

// קבלת מדבקות המוצר
$stmt = Database::getInstance()->getConnection()->prepare("
    SELECT * FROM product_badges WHERE product_id = ?
");
$stmt->execute([$productId]);
$productBadges = $stmt->fetchAll();

// קבלת אקורדיונים של המוצר
$stmt = Database::getInstance()->getConnection()->prepare("
    SELECT * FROM product_accordions WHERE product_id = ? ORDER BY sort_order ASC
");
$stmt->execute([$productId]);
$productAccordions = $stmt->fetchAll();

// קבלת מוצרים קשורים קיימים
$stmt = Database::getInstance()->getConnection()->prepare("
    SELECT pr.*, p.name as related_product_name 
    FROM product_relationships pr
    JOIN products p ON pr.related_product_id = p.id
    WHERE pr.main_product_id = ?
    ORDER BY pr.relationship_type, pr.sort_order
");
$stmt->execute([$productId]);
$existingRelationships = $stmt->fetchAll();

// פיצול לסוגים שונים
$relatedProducts = [];
$upsellProducts = [];
$productBundles = [];

foreach ($existingRelationships as $rel) {
    switch ($rel['relationship_type']) {
        case 'related':
        case 'cross_sell':
        case 'accessory':
        case 'recommended':
        case 'frequently_bought':
        case 'complete_look':
            $relatedProducts[] = [
                'name' => $rel['related_product_name'],
                'type' => $rel['relationship_type'],
                'description' => $rel['description'] ?? ''
            ];
            break;
        case 'upsell':
            $upsellProducts[] = [
                'name' => $rel['related_product_name'],
                'description' => $rel['description'] ?? ''
            ];
            break;
    }
}

// טיפול בעדכון מוצר
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug for accordion issue
    if (!empty($_POST['accordions'])) {
        echo "<div style='background: #f0f0f0; padding: 15px; margin: 15px; border: 1px solid #ccc; direction: ltr;'>";
        echo "<h3>DEBUG: Accordions POST data</h3>";
        echo "<pre>";
        var_dump($_POST['accordions']);
        echo "</pre>";
        echo "</div>";
    }
    
    // Debug: בדיקת נתוני attribute_media
    if (!empty($_POST['attribute_media'])) {
        error_log("Attribute media received: " . print_r($_POST['attribute_media'], true));
        foreach ($_POST['attribute_media'] as $attribute => $values) {
            error_log("Attribute: $attribute");
            foreach ($values as $value => $mediaList) {
                error_log("  Value: $value, Media count: " . count($mediaList));
                foreach ($mediaList as $index => $media) {
                    $mediaLength = strlen($media);
                    error_log("    Media $index: length $mediaLength bytes");
                }
            }
        }
    } else {
        error_log("No attribute_media data received");
    }
    
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
    
    $result = $productManager->updateProduct($productId, $productData);
    
    if ($result['success']) {
        header('Location: /admin/products/edit.php?id=' . $productId . '&updated=1');
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
$pageTitle = 'עריכת מוצר - ' . htmlspecialchars($product['name']);
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
                    <a href="/admin/products/" class="text-gray-600 hover:text-gray-900">
                        <i class="ri-arrow-right-line text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900">עריכת מוצר</h1>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($product['name']) ?></p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <?php 
                    // זיהוי הפורט הנכון
                    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8888';
                    
                    // אם זה localhost, וודא שאנחנו משתמשים בפורט הנכון
                    if (strpos($host, 'localhost') !== false) {
                        // אם לא מצוין פורט ב-HTTP_HOST, נסה לגלות מ-SERVER_PORT
                        if (!strpos($host, ':')) {
                            $port = $_SERVER['SERVER_PORT'] ?? '8888';
                            $host = 'localhost:' . $port;
                        }
                    }
                    
                    $productViewUrl = $scheme . '://' . $store['slug'] . '.' . $host . '/product/' . rawurlencode($product['slug']);
                    ?>
                    <a href="#" onclick="window.open('<?= $productViewUrl ?>', '_blank'); return false;" 
                        class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200">
                        <i class="ri-external-link-line ml-1"></i>
                        צפה במוצר
                    </a>
                    <a href="/admin/products/delete.php?id=<?= $productId ?>" 
                        class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100">
                        מחק מוצר
                    </a>
                    <button type="submit" form="product-form" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700">
                        שמור שינויים
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <?php if (isset($_GET['updated'])): ?>
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="ri-check-line text-green-500 ml-3"></i>
                    <span class="text-green-700">המוצר עודכן בהצלחה!</span>
                </div>
            </div>
        <?php endif; ?>
        
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
                                placeholder="הזן שם מוצר" value="<?= htmlspecialchars($product['name']) ?>">
                        </div>
                        
                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug (כתובת URL)</label>
                            <div class="flex gap-2">
                                <input type="text" id="slug" name="slug"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="slug-נוצר-אוטומטית" value="<?= htmlspecialchars($product['slug'] ?? '') ?>">
                                <button type="button" id="generate-slug" 
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200">
                                    צור אוטומטית
                                </button>
                            </div>
                            <div class="mt-2 p-2 bg-gray-50 rounded text-sm text-gray-600 border">
                                <strong>תצוגה מקדימה:</strong><br>
                                <span id="slug-preview">domain.com/product/</span><span id="slug-text"><?= htmlspecialchars($product['slug'] ?? '') ?></span>
                            </div>
                        </div>
                        
                        <div>
                            <label for="short_description" class="block text-sm font-medium text-gray-700 mb-2">תיאור קצר</label>
                            <textarea id="short_description" name="short_description" rows="3" maxlength="500"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="תיאור קצר שיופיע ברשימת המוצרים (עד 500 תווים)"><?= htmlspecialchars($product['short_description'] ?? '') ?></textarea>
                            <p class="text-xs text-gray-500 mt-1">מקסימום 500 תווים</p>
                        </div>
                        
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">תיאור מלא</label>
                            <textarea id="description" name="description" rows="6"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="תיאור מלא ומפורט של המוצר..."><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
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
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">מחיר רגיל <span id="price-required-indicator">*</span></label>
                            <div class="relative">
                                <input type="number" id="price" name="price" step="0.01" min="0" 
                                    <?= !$product['has_variants'] ? 'required' : '' ?>
                                    class="w-full px-3 py-2 pl-8 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    value="<?= htmlspecialchars($product['price'] ?? '') ?>">
                                <span class="absolute left-3 top-2 text-gray-500">₪</span>
                            </div>
                        </div>
                        
                        <div>
                            <label for="compare_price" class="block text-sm font-medium text-gray-700 mb-2">מחיר לפני הנחה (₪)</label>
                            <input type="number" id="compare_price" name="compare_price" step="0.01" min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="0.00" 
                                value="<?= htmlspecialchars($product['compare_price'] ?? '') ?>">
                            <p class="text-xs text-gray-500 mt-1">יוצג עם קו חוצה כאשר גבוה ממחיר המכירה</p>
                        </div>
                        
                        <div>
                            <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-2">מחיר עלות (₪)</label>
                            <input type="number" id="cost_price" name="cost_price" step="0.01" min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="0.00" 
                                value="<?= htmlspecialchars($product['cost_price'] ?? '') ?>">
                            <p class="text-xs text-gray-500 mt-1">לא מוצג ללקוחות - רק לחישוב רווחיות</p>
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
                        <i class="ri-arrow-down-s-line text-gray-400 transform transition-transform duration-200" id="upsell-cross-sell-icon"></i>
                    </button>
                    
                    <div id="upsell-cross-sell-content" class="hidden p-6 pt-0">
                        <div class="space-y-8">
                        
                            <!-- מוצרים מומלצים -->
                            <div class="bg-blue-50 rounded-xl p-6 border border-blue-100 mt-4">
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="ri-star-line text-blue-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3 class="text-lg font-medium text-gray-800">מוצרים מומלצים</h3>
                                            <div class="relative group">
                                                <div class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center cursor-help">
                                                    <i class="ri-information-line text-blue-600 text-xs"></i>
                                                </div>
                                                <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block z-10">
                                                    <div class="bg-gray-900 text-white text-xs rounded-lg p-3 whitespace-nowrap shadow-lg">
                                                        <div class="font-medium mb-1">מוצרים מומלצים:</div>
                                                        <div>• מוצרים שאתה רוצה לקדם</div>
                                                        <div>• מוצרים עם מרווח רווח גבוה</div>
                                                        <div class="mt-1 text-gray-300">יוצגו בעמוד המוצר כ"מוצרים מומלצים"</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-sm text-blue-700">מוצרים שתרצה לקדם ולהציג כהמלצות בעמוד המוצר</p>
                                    </div>
                                </div>
                            
                            <div id="recommended-products" class="space-y-2">
                                <?php 
                                $recommendedProducts = array_filter($relatedProducts, function($p) { return $p['type'] === 'recommended'; });
                                if (!empty($recommendedProducts)): ?>
                                    <?php foreach ($recommendedProducts as $recommended): ?>
                                        <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg">
                                            <input type="text" name="related_products[]" 
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm product-search"
                                                placeholder="חפש מוצר מומלץ... (התחל להקליד)"
                                                value="<?= htmlspecialchars($recommended['name']) ?>">
                                            <input type="hidden" name="related_types[]" value="recommended">
                                            <button type="button" onclick="this.closest('.flex').remove()" 
                                                class="text-red-500 hover:text-red-700 p-1">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
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
                                <?php endif; ?>
                            </div>
                            
                                <button type="button" id="add-recommended-product" 
                                    class="mt-3 flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition-colors"
                                    onclick="if (window.productForm && window.productForm.addRecommendedProduct) window.productForm.addRecommendedProduct()">
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
                                            <div class="relative group">
                                                <div class="w-5 h-5 bg-orange-100 rounded-full flex items-center justify-center cursor-help">
                                                    <i class="ri-information-line text-orange-600 text-xs"></i>
                                                </div>
                                                <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block z-10">
                                                    <div class="bg-gray-900 text-white text-xs rounded-lg p-3 whitespace-nowrap shadow-lg">
                                                        <div class="font-medium mb-1">לקוחות שרכשו גם:</div>
                                                        <div>• מוצרים שנרכשו יחד עם המוצר הזה</div>
                                                        <div>• מוצרים שמשלימים את הקנייה</div>
                                                        <div class="mt-1 text-gray-300">יוצגו כ"לקוחות שרכשו מוצר זה רכשו גם"</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-sm text-orange-700">מוצרים שלקוחות קנו יחד עם המוצר הזה</p>
                                    </div>
                                </div>
                            
                            <div id="frequently-bought-products" class="space-y-2">
                                <?php 
                                $frequentlyBoughtProducts = array_filter($relatedProducts, function($p) { return $p['type'] === 'frequently_bought'; });
                                if (!empty($frequentlyBoughtProducts)): ?>
                                    <?php foreach ($frequentlyBoughtProducts as $frequentlyBought): ?>
                                        <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg">
                                            <input type="text" name="related_products[]" 
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm product-search"
                                                placeholder="חפש מוצר שנרכש יחד... (התחל להקליד)"
                                                value="<?= htmlspecialchars($frequentlyBought['name']) ?>">
                                            <input type="hidden" name="related_types[]" value="frequently_bought">
                                            <button type="button" onclick="this.closest('.flex').remove()" 
                                                class="text-red-500 hover:text-red-700 p-1">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
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
                                <?php endif; ?>
                            </div>
                            
                                <button type="button" id="add-frequently-bought-product" 
                                    class="mt-3 flex items-center gap-2 px-4 py-2 text-sm font-medium text-orange-600 bg-white border border-orange-200 rounded-lg hover:bg-orange-50 transition-colors"
                                    onclick="if (window.productForm && window.productForm.addFrequentlyBoughtProduct) window.productForm.addFrequentlyBoughtProduct()">
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
                                            <div class="relative group">
                                                <div class="w-5 h-5 bg-purple-100 rounded-full flex items-center justify-center cursor-help">
                                                    <i class="ri-information-line text-purple-600 text-xs"></i>
                                                </div>
                                                <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block z-10">
                                                    <div class="bg-gray-900 text-white text-xs rounded-lg p-3 whitespace-nowrap shadow-lg">
                                                        <div class="font-medium mb-1">השלם את הלוק:</div>
                                                        <div>• מוצרים שמשלימים את הסגנון</div>
                                                        <div>• אביזרים וחלקים נוספים</div>
                                                        <div class="mt-1 text-gray-300">יוצגו כ"השלם את הלוק"</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-sm text-purple-700">מוצרים שמשלימים את הלוק או הסגנון של המוצר</p>
                                    </div>
                                </div>
                            
                            <div id="complete-look-products" class="space-y-2">
                                <?php 
                                $completeLookProducts = array_filter($relatedProducts, function($p) { return $p['type'] === 'complete_look'; });
                                if (!empty($completeLookProducts)): ?>
                                    <?php foreach ($completeLookProducts as $completeLook): ?>
                                        <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg">
                                            <input type="text" name="related_products[]" 
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm product-search"
                                                placeholder="חפש מוצר להשלמת הלוק... (התחל להקליד)"
                                                value="<?= htmlspecialchars($completeLook['name']) ?>">
                                            <input type="hidden" name="related_types[]" value="complete_look">
                                            <button type="button" onclick="this.closest('.flex').remove()" 
                                                class="text-red-500 hover:text-red-700 p-1">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
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
                                <?php endif; ?>
                            </div>
                            
                                <button type="button" id="add-complete-look-product" 
                                    class="mt-3 flex items-center gap-2 px-4 py-2 text-sm font-medium text-purple-600 bg-white border border-purple-200 rounded-lg hover:bg-purple-50 transition-colors"
                                    onclick="if (window.productForm && window.productForm.addCompleteLookProduct) window.productForm.addCompleteLookProduct()">
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
                                <?php if (!empty($upsellProducts)): ?>
                                    <?php foreach ($upsellProducts as $upsell): ?>
                                        <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg">
                                            <input type="text" name="upsell_products[]" 
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm product-search"
                                                placeholder="חפש מוצר לשדרוג..."
                                                value="<?= htmlspecialchars($upsell['name']) ?>">
                                            <input type="text" name="upsell_descriptions[]" 
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                                placeholder="סיבה לשדרוג (למשל: איכות גבוהה יותר)"
                                                value="<?= htmlspecialchars($upsell['description']) ?>">
                                            <button type="button" onclick="this.closest('.flex').remove()" 
                                                class="text-red-500 hover:text-red-700 p-1">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
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
                                <?php endif; ?>
                            </div>
                            
                                <button type="button" id="add-upsell-product" 
                                    class="mt-3 flex items-center gap-2 px-4 py-2 text-sm font-medium text-green-600 bg-white border border-green-200 rounded-lg hover:bg-green-50 transition-colors"
                                    onclick="if (window.productForm && window.productForm.addUpsellProduct) window.productForm.addUpsellProduct()">
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
                                class="mt-3 flex items-center gap-2 px-3 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100"
                                onclick="if (window.productForm && window.productForm.addProductBundle) window.productForm.addProductBundle()">
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
                                <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>פעיל</option>
                                <option value="draft" <?= $product['status'] === 'draft' ? 'selected' : '' ?>>טיוטה</option>
                                <option value="archived" <?= $product['status'] === 'archived' ? 'selected' : '' ?>>בארכיון</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">סטטוס המוצר באתר</p>
                        </div>
                        
                        <div class="pt-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" id="featured" name="featured" value="1"
                                    <?= $product['featured'] ? 'checked' : '' ?>
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
                                    placeholder="קוד מוצר ייחודי" value="<?= htmlspecialchars($product['sku'] ?? '') ?>">
                                <p class="text-xs text-gray-500 mt-1">מזהה ייחודי למוצר במערכת</p>
                            </div>
                            
                            <!-- Barcode -->
                            <div>
                                <label for="barcode" class="block text-sm font-medium text-gray-700 mb-2">ברקוד</label>
                                <input type="text" id="barcode" name="barcode"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="ברקוד המוצר" value="<?= htmlspecialchars($product['barcode'] ?? '') ?>">
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
                                        placeholder="0.000" value="<?= htmlspecialchars($product['weight'] ?? '') ?>">
                                    <p class="text-xs text-gray-500 mt-1">משקל למשלוח</p>
                                </div>
                                
                                <!-- Vendor -->
                                <div>
                                    <label for="vendor" class="block text-sm font-medium text-gray-700 mb-2">יצרן/ספק</label>
                                    <input type="text" id="vendor" name="vendor"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="שם היצרן או הספק" value="<?= htmlspecialchars($product['vendor'] ?? '') ?>">
                                    <p class="text-xs text-gray-500 mt-1">יועיל למעקב ורכש</p>
                                </div>
                                
                                <!-- Product Type -->
                                <div>
                                    <label for="product_type" class="block text-sm font-medium text-gray-700 mb-2">סוג מוצר</label>
                                    <input type="text" id="product_type" name="product_type"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="למשל: בגדים, אלקטרוניקה, ספרים" value="<?= htmlspecialchars($product['product_type'] ?? '') ?>">
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
                                    <input type="checkbox" id="track_inventory" name="track_inventory" value="1" 
                                        <?= $product['track_inventory'] ? 'checked' : '' ?> class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            
                            <div id="inventory-fields" class="space-y-4">
                                <div id="inventory-quantity-container">
                                    <label for="inventory_quantity" class="block text-sm font-medium text-gray-700 mb-2">כמות במלאי</label>
                                    <input type="number" id="inventory_quantity" name="inventory_quantity" min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        value="<?= htmlspecialchars($product['inventory_quantity'] ?? '') ?>">
                                    <p class="text-xs text-gray-500 mt-1">הכמות הנוכחית הזמינה למכירה</p>
                                </div>
                                
                                <div class="pt-2" id="allow-backorders-container">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" id="allow_backorders" name="allow_backorders" value="1"
                                            <?= $product['allow_backorders'] ? 'checked' : '' ?>
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
                                    <?= in_array($category['id'], $productCategories) ? 'checked' : '' ?>
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
                                placeholder="כותרת מותאמת למנועי חיפוש" value="<?= htmlspecialchars($product['seo_title'] ?? '') ?>">
                            <p class="text-xs text-gray-500 mt-1">אם ריק, ישתמש בשם המוצר. מומלץ עד 60 תווים</p>
                        </div>
                        
                        <div>
                            <label for="seo_description" class="block text-sm font-medium text-gray-700 mb-2">תיאור SEO</label>
                            <textarea id="seo_description" name="seo_description" rows="3" maxlength="500"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="תיאור קצר המסביר מה המוצר ולמה לקנות אותו"><?= htmlspecialchars($product['seo_description'] ?? '') ?></textarea>
                            <p class="text-xs text-gray-500 mt-1">יוצג בתוצאות החיפוש. מומלץ 150-160 תווים</p>
                        </div>
                        
                        <div>
                            <label for="seo_keywords" class="block text-sm font-medium text-gray-700 mb-2">מילות מפתח</label>
                            <input type="text" id="seo_keywords" name="seo_keywords"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="מילות מפתח מופרדות בפסיקים" value="<?= htmlspecialchars($product['seo_keywords'] ?? '') ?>">
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
        // Store info for image upload
        window.storeId = <?= $store['id'] ?>;
        
        // נתונים קיימים של המוצר
        const existingProduct = {
            id: <?= $productId ?>,
            attributes: <?= json_encode(array_values($attributes ?? [])) ?>,
            badges: <?= json_encode($productBadges ?? []) ?>,
            accordions: <?= json_encode($productAccordions ?? []) ?>,
            media: <?= json_encode($productMedia ?? []) ?>,
            tags: <?= json_encode(isset($product['tags']) && $product['tags'] ? explode(',', $product['tags']) : []) ?>,
            gallery_attribute: <?= json_encode($product['gallery_attribute'] ?? '') ?>,
            auto_suggest_category: <?= ($product['auto_suggest_category'] ?? 0) ? 'true' : 'false' ?>,
            auto_suggest_price: <?= ($product['auto_suggest_price'] ?? 0) ? 'true' : 'false' ?>,
            auto_suggest_vendor: <?= ($product['auto_suggest_vendor'] ?? 0) ? 'true' : 'false' ?>,
            auto_suggest_tags: <?= ($product['auto_suggest_tags'] ?? 0) ? 'true' : 'false' ?>,
            requires_shipping: <?= ($product['requires_shipping'] ?? 1) ? 'true' : 'false' ?>,
            is_physical: <?= ($product['is_physical'] ?? 1) ? 'true' : 'false' ?>
        };
        
        // Debug - בדיקת מבנה המדיה
        console.log('Existing product media:', existingProduct.media);

        // אתחול ProductForm
        document.addEventListener('DOMContentLoaded', function() {
            // יצירת instance של ProductForm רק אם לא קיים
            if (typeof ProductForm !== 'undefined' && !window.productForm && !window.ProductFormInstance) {
                window.productForm = new ProductForm();
                window.ProductFormInstance = window.productForm; // הוספת reference לתאימות לאחור
                
                // אתחול חיפוש מוצרים לשדות קיימים
                if (typeof window.productForm.initExistingProductSearches === 'function') {
                    window.productForm.initExistingProductSearches();
                }
            }
            
            // המתנה קצרה לוודא שכל הפונקציות נטענו
            setTimeout(() => {
                loadExistingProductData();
            }, 100);
        });

        function loadExistingProductData() {
            // מניעת טעינה כפולה של הנתונים
            if (window.productDataLoaded) {
                console.log('⚠️ Product data already loaded, skipping');
                return;
            }
            window.productDataLoaded = true;
            // טעינת תגיות קיימות
            if (existingProduct.tags && existingProduct.tags.length > 0) {
                const tagsContainer = document.getElementById('tags-container');
                const tagsInput = document.getElementById('tags');
                
                existingProduct.tags.forEach(tag => {
                    if (tag.trim()) {
                        addTagBubble(tag.trim(), tagsContainer);
                    }
                });
                
                tagsInput.value = existingProduct.tags.join(',');
            }

            // טעינת מדבקות קיימות
            if (existingProduct.badges && existingProduct.badges.length > 0) {
                const badgesContainer = document.getElementById('badges-container');
                
                if (badgesContainer) {
                    existingProduct.badges.forEach(badge => {
                        addBadgeToContainer(badge.text, badge.color, badge.background_color, badgesContainer, badge.position);
                    });
                }
            }

            // טעינת אקורדיונים קיימים
            if (existingProduct.accordions && existingProduct.accordions.length > 0) {
                const accordionsContainer = document.getElementById('accordions-container');
                
                if (accordionsContainer && window.addAccordionToContainer) {
                    existingProduct.accordions.forEach(accordion => {
                        window.addAccordionToContainer(accordion.title, accordion.content, accordionsContainer);
                    });
                }
            }

            // טעינת מאפיינים קיימים - רק אם יש מאפיינים עם שמות
            if (existingProduct.attributes && existingProduct.attributes.length > 0) {
                // בדוק אם יש מאפיינים עם שמות אמיתיים
                const validAttributes = existingProduct.attributes.filter(attr => 
                    attr.name && attr.name.trim() && attr.values && attr.values.length > 0
                );
                
                if (validAttributes.length > 0) {
                    const hasVariantsCheckbox = document.getElementById('has-variants');
                    if (hasVariantsCheckbox) {
                        hasVariantsCheckbox.checked = true;
                        hasVariantsCheckbox.dispatchEvent(new Event('change'));
                    }
                    
                    setTimeout(() => {
                        const attributesContainer = document.getElementById('attributes-container');
                        
                        // ניקוי המאפיינים הקיימים מה-HTML כדי למנוע כפילות
                        if (attributesContainer) {
                            // רק אם יש HTML attributes שנטענו מהשרת, נוסיף גם JavaScript
                            const existingAttributeItems = attributesContainer.querySelectorAll('.attribute-item');
                            if (existingAttributeItems.length === 0) {
                                validAttributes.forEach(attribute => {
                                    addAttributeToContainer(attribute.name, attribute.type, attribute.values, attributesContainer);
                                });
                            }
                        }
                    }, 100);
                }
            }

            // טעינת גלרייה מדיה קיימת - רק תמונות ללא gallery_value (לא לפי מאפיין)
            if (existingProduct.media && existingProduct.media.length > 0) {
                const mediaPreview = document.getElementById('media-preview');
                if (mediaPreview) {
                    // בדוק אם כבר יש תמונות במדיה preview כדי למנוע כפילות
                    if (mediaPreview.children.length === 0) {
                        // סנן רק תמונות שלא שייכות לגלרייה לפי מאפיין
                        const generalMedia = existingProduct.media.filter(media => 
                            !media.gallery_value || media.gallery_value.trim() === ''
                        );
                        
                        if (generalMedia.length > 0) {
                            mediaPreview.classList.remove('hidden');
                            
                            generalMedia.forEach(media => {
                                addExistingMediaToPreview(media, mediaPreview);
                            });
                        }
                    }
                }
            }

            // הגדרת הצעות אוטומטיות
            if (existingProduct.auto_suggest_category) {
                const autoSuggestCategoryEl = document.querySelector('input[name="auto_suggest_category"]');
                if (autoSuggestCategoryEl) autoSuggestCategoryEl.checked = true;
            }
            if (existingProduct.auto_suggest_price) {
                const autoSuggestPriceEl = document.querySelector('input[name="auto_suggest_price"]');
                if (autoSuggestPriceEl) autoSuggestPriceEl.checked = true;
            }
            if (existingProduct.auto_suggest_vendor) {
                const autoSuggestVendorEl = document.querySelector('input[name="auto_suggest_vendor"]');
                if (autoSuggestVendorEl) autoSuggestVendorEl.checked = true;
            }
            if (existingProduct.auto_suggest_tags) {
                const autoSuggestTagsEl = document.querySelector('input[name="auto_suggest_tags"]');
                if (autoSuggestTagsEl) autoSuggestTagsEl.checked = true;
            }

            // הגדרות משלוח
            if (existingProduct.requires_shipping) {
                const requiresShippingEl = document.querySelector('input[name="requires_shipping"]');
                if (requiresShippingEl) requiresShippingEl.checked = true;
            }
            if (existingProduct.is_physical) {
                const isPhysicalEl = document.querySelector('input[name="is_physical"]');
                if (isPhysicalEl) isPhysicalEl.checked = true;
            }

            // גלרייה לפי מאפיין
            if (existingProduct.gallery_attribute) {
                const enableGalleryAttribute = document.getElementById('enable-gallery-attribute');
                const galleryAttributeSelect = document.getElementById('gallery-attribute-select');
                
                if (enableGalleryAttribute && galleryAttributeSelect) {
                    enableGalleryAttribute.checked = true;
                    
                    // הצג את ההגדרות
                    const settings = document.getElementById('gallery-attribute-settings');
                    if (settings) {
                        settings.classList.remove('hidden');
                    }
                    
                    // עדכן את הרשימה של מאפיינים זמינים תחילה
                    if (typeof updateGalleryAttributeDropdown === 'function') {
                        updateGalleryAttributeDropdown();
                        
                        // אחרי עדכון הרשימה, בחר את המאפיין הנכון
                        setTimeout(() => {
                            galleryAttributeSelect.value = existingProduct.gallery_attribute;
                            
                            // עדכן את הגלרייה עם התמונות הקיימות
                            if (typeof updateAttributeGalleries === 'function') {
                                updateAttributeGalleries();
                                
                                // טען תמונות קיימות לגלרייה לפי מאפיין - רק כאן
                                setTimeout(() => {
                                    loadExistingAttributeMedia();
                                }, 300);
                            }
                        }, 100);
                    }
                }
            }
        }

        // פונקציות עזר לטעינת נתונים קיימים
        function addTagBubble(tagText, container) {
            if (typeof window.productForm === 'object' && typeof window.productForm.addTagBubble === 'function') {
                window.productForm.addTagBubble(tagText, container);
            } else {
                // fallback במקרה שהפונקציה עדיין לא נטענה
                const tagBubble = document.createElement('span');
                tagBubble.className = 'inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 text-xs rounded-full';
                tagBubble.innerHTML = `
                    ${tagText}
                    <button type="button" class="text-blue-600 hover:text-blue-800 ml-1" onclick="this.parentElement.remove(); updateTagsInput();">
                        <i class="ri-close-line text-xs"></i>
                    </button>
                `;
                container.appendChild(tagBubble);
            }
        }

        function addBadgeToContainer(text, textColor, backgroundColor, container, position = 'top-right') {
            if (typeof window.productForm === 'object' && typeof window.productForm.addBadgeToContainer === 'function') {
                window.productForm.addBadgeToContainer(text, textColor, backgroundColor, container, position);
            } else {
                // fallback - יצירת badge פשוט
                const badgeDiv = document.createElement('div');
                badgeDiv.className = 'flex items-center gap-3 p-3 bg-gray-50 rounded-lg';
                badgeDiv.innerHTML = `
                    <input type="text" name="badges[text][]" value="${text}" placeholder="טקסט המדבקה" 
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <input type="color" name="badges[color][]" value="${color}" 
                        class="w-12 h-10 border border-gray-300 rounded-lg cursor-pointer">
                    <button type="button" onclick="this.closest('div').remove()" 
                        class="text-red-500 hover:text-red-700">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                `;
                container.appendChild(badgeDiv);
            }
        }



        function addAttributeToContainer(name, type, values, container) {
            // בדוק אם יש כבר מאפיין עם אותו שם (למניעת כפילות)
            const existingAttributes = container.querySelectorAll('input[name*="[name]"]');
            for (let input of existingAttributes) {
                if (input.value === name) {
                    console.log('Attribute already exists:', name);
                    return; // המאפיין כבר קיים
                }
            }
            
            if (typeof window.productFormVariants === 'object' && typeof window.productFormVariants.addAttributeToContainer === 'function') {
                window.productFormVariants.addAttributeToContainer(name, type, values, container);
            } else {
                // fallback - יצירת attribute פשוט
                const attributeDiv = document.createElement('div');
                attributeDiv.className = 'space-y-3 p-4 bg-gray-50 rounded-lg';
                const valuesStr = values.map(v => v.value).join(',');
                attributeDiv.innerHTML = `
                    <input type="text" name="attributes[name][]" value="${name}" placeholder="שם המאפיין" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <select name="attributes[type][]" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="text" ${type === 'text' ? 'selected' : ''}>טקסט</option>
                        <option value="color" ${type === 'color' ? 'selected' : ''}>צבע</option>
                        <option value="size" ${type === 'size' ? 'selected' : ''}>מידה</option>
                    </select>
                    <input type="text" name="attributes[values][]" value="${valuesStr}" placeholder="ערכים מופרדים בפסיק" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="button" onclick="this.closest('div').remove()" 
                        class="text-red-500 hover:text-red-700">
                        <i class="ri-delete-bin-line"></i> הסר מאפיין
                    </button>
                `;
                container.appendChild(attributeDiv);
            }
        }

        function addExistingMediaToPreview(media, container) {
            // בדוק אם התמונה כבר קיימת במיכל כדי למנוע כפילות
            const existingImages = container.querySelectorAll('img');
            const imageUrl = media.url || media.file_path || media.thumbnail_url;
            const thumbnailUrl = media.thumbnail_url || media.url || media.file_path;
            
            const alreadyExists = Array.from(existingImages).some(img => 
                img.src.includes(imageUrl) || img.src.includes(thumbnailUrl)
            );
            
            if (alreadyExists) {
                console.log('Image already exists in gallery, skipping:', imageUrl);
                return;
            }
            
            // יצירת preview בעיצוב החדש
            const currentIndex = container.children.length;
            const mediaItem = document.createElement('div');
            mediaItem.className = 'relative group border border-gray-200 rounded-lg overflow-hidden';
            const isPrimary = media.is_primary && media.is_primary == 1;
            
            mediaItem.innerHTML = `
                <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden rounded-lg">
                    <img src="${thumbnailUrl}" alt="${media.alt_text || ''}" class="w-full h-full object-cover">
                </div>
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-200"></div>
                
                <!-- אייקון מחיקה - שמאל מעלה -->
                <button type="button" onclick="removeGalleryImage(this);" 
                    class="absolute top-2 left-2 w-8 h-8 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-red-600 flex items-center justify-center shadow-lg">
                    <i class="ri-delete-bin-line text-sm"></i>
                </button>
                
                <!-- אייקון תמונה ראשית - ימין מעלה -->
                <button type="button" onclick="setPrimaryImage(this);" 
                    class="absolute top-2 right-2 w-8 h-8 ${isPrimary ? 'bg-yellow-500 text-white' : 'bg-white text-gray-600'} rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-yellow-500 hover:text-white flex items-center justify-center shadow-lg">
                    <i class="ri-star-${isPrimary ? 'fill' : 'line'} text-sm"></i>
                </button>
                
                <!-- מספר סידורי -->
                <div class="absolute bottom-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-all duration-200">
                    ${currentIndex + 1}
                </div>
                
                <input type="hidden" name="media[${currentIndex}][url]" value="${imageUrl}">
                <input type="hidden" name="media[${currentIndex}][thumbnail_url]" value="${thumbnailUrl}">
                <input type="hidden" name="media[${currentIndex}][type]" value="${media.type || 'image'}">
                <input type="hidden" name="media[${currentIndex}][alt_text]" value="${media.alt_text || ''}">
                <input type="hidden" name="media[${currentIndex}][sort_order]" value="${media.sort_order || 0}">
                <input type="hidden" name="media[${currentIndex}][is_primary]" value="${media.is_primary || 0}">
            `;
            
            container.appendChild(mediaItem);
            
            // עדכן את מספר המדיה אם יש instance
            if (window.ProductFormInstance) {
                window.ProductFormInstance.mediaCount = Math.max(window.ProductFormInstance.mediaCount, currentIndex + 1);
            }
        }

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
        const hasVariantsEl = document.getElementById('has-variants');
        if (hasVariantsEl) {
            hasVariantsEl.addEventListener('change', function() {
                const gallerySettings = document.getElementById('gallery-settings');
                const inventorySection = document.getElementById('inventory-section');
                
                if (this.checked) {
                    // Show gallery settings
                    if (gallerySettings) gallerySettings.classList.remove('hidden');
                    
                    // Hide inventory section - not relevant for variants
                    if (inventorySection) inventorySection.classList.add('hidden');
                    
                    // Add notification
                    if (!document.getElementById('inventory-variants-notice') && inventorySection) {
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
                    if (gallerySettings) gallerySettings.classList.add('hidden');
                    
                    // Show inventory section
                    if (inventorySection) inventorySection.classList.remove('hidden');
                    
                    // Remove notification
                    const notice = document.getElementById('inventory-variants-notice');
                    if (notice) {
                        notice.remove();
                    }
                    
                    // Reset gallery settings when variants are disabled
                    const enableGalleryAttribute = document.getElementById('enable-gallery-attribute');
                    const galleryAttributeSettings = document.getElementById('gallery-attribute-settings');
                    const galleryAttributeSelect = document.getElementById('gallery-attribute-select');
                    
                    if (enableGalleryAttribute) enableGalleryAttribute.checked = false;
                    if (galleryAttributeSettings) galleryAttributeSettings.classList.add('hidden');
                    if (galleryAttributeSelect) galleryAttributeSelect.value = '';
                }
            });
        }

        // Gallery attribute switcher
        const enableGalleryAttributeEl = document.getElementById('enable-gallery-attribute');
        if (enableGalleryAttributeEl) {
            enableGalleryAttributeEl.addEventListener('change', function() {
                const settings = document.getElementById('gallery-attribute-settings');
                if (this.checked && settings) {
                    settings.classList.remove('hidden');
                    // עדכן את הרשימה של מאפיינים זמינים
                    if (typeof updateGalleryAttributeDropdown === 'function') {
                        updateGalleryAttributeDropdown();
                    }
                } else if (settings) {
                    settings.classList.add('hidden');
                    // Reset the select value when disabled
                    const galleryAttributeSelect = document.getElementById('gallery-attribute-select');
                    if (galleryAttributeSelect) galleryAttributeSelect.value = '';
                    // הסתר את הגלרייה
                    const gallerySection = document.getElementById('gallery-per-attribute');
                    if (gallerySection) {
                        gallerySection.classList.add('hidden');
                    }
                }
            });
        }
        
        // מאזין לשינויים במאפיינים כדי לעדכן את הגלרייה - בעמוד עריכה
        document.addEventListener('input', function(e) {
            if (e.target.matches('input[name*="[name]"]') || e.target.matches('input[name*="[values]"][name*="[value]"]')) {
                // עדכן את הרשימה של מאפיינים זמינים עם השהיה קטנה
                if (typeof updateGalleryAttributeDropdown === 'function') {
                    setTimeout(updateGalleryAttributeDropdown, 100);
                }
            }
        });
        
        // טיפול בבחירת מאפיין לגלרייה - בעמוד עריכה
        document.addEventListener('change', function(e) {
            if (e.target.id === 'gallery-attribute-select') {
                if (typeof updateAttributeGalleries === 'function') {
                    updateAttributeGalleries();
                }
            }
        });
        
        // אתחול גלרייה לפי מאפיין מתבצע בתוך loadExistingProductData - מחקנו כפילות
        
        // פונקציה לטעינת תמונות קיימות לגלרייה לפי מאפיין
        function loadExistingAttributeMedia() {
            // מניעת טעינה כפולה
            if (window.attributeMediaLoaded) {
                console.log('⚠️ Attribute media already loaded, skipping');
                return;
            }
            
            // בדוק אם כבר יש תמונות בגלרייה לפי מאפיין
            const existingAttributeImages = document.querySelectorAll('.attribute-gallery-preview img');
            if (existingAttributeImages.length > 0) {
                console.log('⚠️ Attribute images already exist in DOM, skipping');
                return;
            }
            
            window.attributeMediaLoaded = true;
            
            console.log('Loading existing attribute media...');
            
            if (!existingProduct.media || existingProduct.media.length === 0) {
                console.log('No media to load');
                return;
            }
            
            // מצא תמונות עם gallery_value (תמונות לפי מאפיין)
            const attributeMedia = existingProduct.media.filter(media => 
                media.gallery_value && media.gallery_value.trim()
            );
            
            console.log('Found attribute media:', attributeMedia);
            
            attributeMedia.forEach(media => {
                const galleryValue = media.gallery_value.trim();
                console.log('Looking for upload area with value:', galleryValue);
                
                // חיפוש עם מספר אפשרויות של encoding
                let uploadArea = document.querySelector(`.attribute-gallery-upload[data-value="${galleryValue}"]`);
                
                // אם לא נמצא, נסה עם encoding
                if (!uploadArea) {
                    const encodedValue = encodeURIComponent(galleryValue);
                    uploadArea = document.querySelector(`.attribute-gallery-upload[data-value="${encodedValue}"]`);
                    console.log('Trying encoded value:', encodedValue);
                }
                
                // אם עדיין לא נמצא, נסה לחפש בכל האזורים
                if (!uploadArea) {
                    const allAreas = document.querySelectorAll('.attribute-gallery-upload');
                    for (let area of allAreas) {
                        const dataValue = area.dataset.value;
                        if (dataValue === galleryValue || 
                            decodeURIComponent(dataValue) === galleryValue ||
                            dataValue === encodeURIComponent(galleryValue)) {
                            uploadArea = area;
                            console.log('Found match with manual search:', dataValue);
                            break;
                        }
                    }
                }
                
                if (uploadArea) {
                    const preview = uploadArea.nextElementSibling;
                    if (preview && preview.classList.contains('attribute-gallery-preview')) {
                        // בדוק אם התמונה כבר קיימת כדי למנוע כפילות
                        const existingImages = preview.querySelectorAll('img');
                        const imageUrl = media.url || media.file_path;
                        const alreadyExists = Array.from(existingImages).some(img => 
                            img.src.includes(imageUrl) || img.src.includes(media.thumbnail_url)
                        );
                        
                        if (!alreadyExists) {
                            console.log('Adding existing media to gallery for value:', galleryValue);
                            
                            // יצירת preview חדש לתמונה קיימת
                            const imageDiv = document.createElement('div');
                            imageDiv.className = 'relative group';
                            
                            const thumbnailUrl = media.thumbnail_url || imageUrl;
                            
                            // חישוב מספר התמונה הנוכחית
                            const currentIndex = preview.children.length;
                            
                            imageDiv.innerHTML = `
                                <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden rounded-lg">
                                    <img src="${thumbnailUrl}" alt="${media.alt_text || ''}" class="w-full h-full object-cover">
                                </div>
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-200"></div>
                                
                                <!-- אייקון מחיקה - שמאל מעלה -->
                                <button type="button" onclick="removeAttributeGalleryImage(this);" 
                                    class="absolute top-2 left-2 w-8 h-8 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-red-600 flex items-center justify-center shadow-lg">
                                    <i class="ri-delete-bin-line text-sm"></i>
                                </button>
                                
                                <!-- אייקון תמונה ראשית - ימין מעלה -->
                                <button type="button" onclick="setAttributePrimaryImage(this);" 
                                    class="absolute top-2 right-2 w-8 h-8 ${currentIndex === 0 ? 'bg-yellow-500 text-white' : 'bg-white text-gray-600'} rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-yellow-500 hover:text-white flex items-center justify-center shadow-lg">
                                    <i class="ri-star-${currentIndex === 0 ? 'fill' : 'line'} text-sm"></i>
                                </button>
                                
                                <!-- מספר סידורי -->
                                <div class="absolute bottom-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-all duration-200">
                                    ${currentIndex + 1}
                                </div>
                                
                                <input type="hidden" name="existing_attribute_media[]" value="${media.id}">
                            `;
                            
                            preview.appendChild(imageDiv);
                            preview.classList.remove('hidden');
                            
                            // עדכן את המראה של upload area
                            uploadArea.style.borderColor = '#10b981';
                            uploadArea.style.backgroundColor = '#f0fdf4';
                            
                            const statusText = uploadArea.querySelector('.upload-status');
                            if (!statusText) {
                                const statusDiv = document.createElement('div');
                                statusDiv.className = 'upload-status text-xs text-green-600 mt-1';
                                statusDiv.textContent = `${preview.children.length} תמונות קיימות`;
                                uploadArea.appendChild(statusDiv);
                            } else {
                                statusText.textContent = `${preview.children.length} תמונות קיימות`;
                            }
                        } else {
                            console.log('Image already exists for value:', galleryValue);
                        }
                    } else {
                        console.log('Preview container not found for value:', galleryValue);
                    }
                } else {
                    console.log('Upload area not found for value:', galleryValue);
                }
            });
        }
        
        // וודא שהווריאציות פועלות נכון אחרי טעינת הדף - רק אם לא נעשה כבר
        if (existingProduct && typeof updateVariantsTable === 'function') {
            setTimeout(() => {
                // בדוק אם כבר יש טבלת וריאציות
                const container = document.getElementById('variants-section');
                if (container) {
                    const existingTables = container.querySelectorAll('.variants-table-container');
                    const attributeItems = document.querySelectorAll('.attribute-item');
                    
                    // רק אם יש מאפיינים אבל אין טבלה, או שאין כלום
                    if (attributeItems.length > 0 && existingTables.length === 0) {
                        updateVariantsTable();
                    }
                }
            }, 1500); // זמן יותר ארוך כדי לוודא שהכל נטען
        }
    </script>
    
    <?php include '../templates/footer.php'; ?>
</body>
</html> 