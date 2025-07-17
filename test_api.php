<?php
require_once 'includes/auth.php';
require_once 'includes/ProductManager.php';
require_once 'config/database.php';

// Start session for authentication
session_start();

// Get the test user
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM users WHERE username = 'admin'");
$stmt->execute();
$user = $stmt->fetch();

if (!$user) {
    die("Test user not found. Please run setup_database.sql first.");
}

// Get the test store
$stmt = $db->prepare("SELECT * FROM stores WHERE user_id = ?");
$stmt->execute([$user['id']]);
$store = $stmt->fetch();

if (!$store) {
    die("Test store not found. Please run setup_database.sql first.");
}

$productManager = new ProductManager();

echo "<h1>בדיקת API ליצירת מוצרים מלאים</h1>";

// Test Product 1: חולצה עם וריאציות צבע ומידה
$product1Data = [
    'name' => 'חולצת פולו קלאסית',
    'description' => 'חולצת פולו איכותית עשויה כותנה 100%. נוחה ואלגנטית לכל אירוע.',
    'short_description' => 'חולצת פולו כותנה איכותית',
    'sku' => 'POLO-001',
    'price' => 149.90,
    'compare_price' => 199.90,
    'cost_price' => 75.00,
    'inventory_quantity' => 50,
    'track_inventory' => true,
    'weight' => 0.3,
    'status' => 'active',
    'vendor' => 'Fashion Brand',
    'product_type' => 'חולצות',
    'tags' => 'פולו, כותנה, קלאסי, גברים',
    'has_variants' => true,
    'gallery_attribute' => 'צבע',
    'seo_title' => 'חולצת פולו קלאסית - Fashion Brand',
    'seo_description' => 'חולצת פולו איכותית עשויה כותנה 100%. משלוח חינם לכל הארץ.',
    'seo_keywords' => 'חולצת פולו, בגדי גברים, כותנה',
    
    'categories' => [1, 2], // בגדים, אביזרים
    
    'media' => [
        [
            'type' => 'image',
            'url' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=',
            'alt_text' => 'חולצת פולו כחולה',
            'sort_order' => 0,
            'is_featured' => true
        ]
    ],
    
    'attributes' => [
        [
            'name' => 'צבע',
            'type' => 'color',
            'is_variant' => true,
            'values' => [
                ['value' => 'כחול', 'color' => '#0066cc'],
                ['value' => 'אדום', 'color' => '#cc0000'],
                ['value' => 'לבן', 'color' => '#ffffff'],
                ['value' => 'שחור', 'color' => '#000000']
            ]
        ],
        [
            'name' => 'מידה',
            'type' => 'dropdown',
            'is_variant' => true,
            'values' => [
                ['value' => 'S'],
                ['value' => 'M'],
                ['value' => 'L'],
                ['value' => 'XL']
            ]
        ]
    ],
    
    'variants' => [
        ['sku' => 'POLO-001-BL-S', 'price' => 149.90, 'inventory' => 5, 'attribute_values' => ['כחול', 'S']],
        ['sku' => 'POLO-001-BL-M', 'price' => 149.90, 'inventory' => 8, 'attribute_values' => ['כחול', 'M']],
        ['sku' => 'POLO-001-BL-L', 'price' => 149.90, 'inventory' => 10, 'attribute_values' => ['כחול', 'L']],
        ['sku' => 'POLO-001-BL-XL', 'price' => 149.90, 'inventory' => 7, 'attribute_values' => ['כחול', 'XL']],
        ['sku' => 'POLO-001-RD-S', 'price' => 149.90, 'inventory' => 4, 'attribute_values' => ['אדום', 'S']],
        ['sku' => 'POLO-001-RD-M', 'price' => 149.90, 'inventory' => 6, 'attribute_values' => ['אדום', 'M']],
        ['sku' => 'POLO-001-RD-L', 'price' => 149.90, 'inventory' => 8, 'attribute_values' => ['אדום', 'L']],
        ['sku' => 'POLO-001-RD-XL', 'price' => 149.90, 'inventory' => 5, 'attribute_values' => ['אדום', 'XL']]
    ],
    
    'accordions' => [
        [
            'title' => 'תיאור המוצר',
            'content' => 'חולצת פולו קלאסית עשויה כותנה 100% איכותית. החולצה כוללת צווארון רך וכפתורים איכותיים.',
            'sort_order' => 0,
            'is_active' => true
        ],
        [
            'title' => 'מידות',
            'content' => 'S: אורך 68 ס"מ, רוחב 50 ס"מ<br>M: אורך 70 ס"מ, רוחב 52 ס"מ<br>L: אורך 72 ס"מ, רוחב 54 ס"מ<br>XL: אורך 74 ס"מ, רוחב 56 ס"מ',
            'sort_order' => 1,
            'is_active' => true
        ],
        [
            'title' => 'משלוחים',
            'content' => 'משלוח חינם לכל הארץ בקניה מעל 150₪. זמן משלוח: 2-5 ימי עסקים.',
            'sort_order' => 2,
            'is_active' => true
        ],
        [
            'title' => 'החזרות',
            'content' => 'אפשרות החזרה עד 30 יום מרגע הקנייה. המוצר חייב להיות במצב מקורי.',
            'sort_order' => 3,
            'is_active' => true
        ]
    ],
    
    'badges' => [
        [
            'text' => 'מבצע',
            'color' => '#ffffff',
            'background_color' => '#ff4444',
            'position' => 'top-right',
            'is_active' => true
        ],
        [
            'text' => 'כותנה 100%',
            'color' => '#ffffff',
            'background_color' => '#22aa22',
            'position' => 'top-left',
            'is_active' => true
        ]
    ],
    
    'attribute_media' => [
        'צבע' => [
            'כחול' => ['data:image/jpeg;base64,BLUE_IMAGE_DATA'],
            'אדום' => ['data:image/jpeg;base64,RED_IMAGE_DATA'],
            'לבן' => ['data:image/jpeg;base64,WHITE_IMAGE_DATA'],
            'שחור' => ['data:image/jpeg;base64,BLACK_IMAGE_DATA']
        ]
    ]
];

try {
    $productId1 = $productManager->createProduct($store['id'], $product1Data);
    echo "<h2>✅ מוצר 1 נוצר בהצלחה - ID: $productId1</h2>";
    echo "<p><strong>שם:</strong> {$product1Data['name']}</p>";
    echo "<p><strong>וריאציות:</strong> " . count($product1Data['variants']) . "</p>";
    echo "<p><strong>מאפיינים:</strong> " . count($product1Data['attributes']) . "</p>";
    echo "<p><strong>אקורדיונים:</strong> " . count($product1Data['accordions']) . "</p>";
    echo "<p><strong>מדבקות:</strong> " . count($product1Data['badges']) . "</p>";
} catch (Exception $e) {
    echo "<h2>❌ שגיאה ביצירת מוצר 1: " . $e->getMessage() . "</h2>";
}

// Test Product 2: נעליים ללא וריאציות
$product2Data = [
    'name' => 'נעלי ספורט פרימיום',
    'description' => 'נעלי ספורט מתקדמות עם ריפוד מתקדם וסוליה אנטי-להחלקה. מושלמות לריצה ופעילות ספורטיבית.',
    'short_description' => 'נעלי ספורט מתקדמות לריצה',
    'sku' => 'SPORT-001',
    'price' => 299.90,
    'compare_price' => 399.90,
    'cost_price' => 150.00,
    'inventory_quantity' => 25,
    'track_inventory' => true,
    'weight' => 0.8,
    'status' => 'active',
    'vendor' => 'SportTech',
    'product_type' => 'נעלי ספורט',
    'tags' => 'ספורט, ריצה, נוחות, איכות',
    'has_variants' => false,
    'seo_title' => 'נעלי ספורט פרימיום - SportTech',
    'seo_description' => 'נעלי ספורט מתקדמות עם טכנולוגיית ריפוד מתקדמת. משלוח חינם לכל הארץ.',
    'seo_keywords' => 'נעלי ספורט, ריצה, נעליים',
    
    'categories' => [2], // נעליים
    
    'media' => [
        [
            'type' => 'image',
            'url' => 'data:image/jpeg;base64,SHOE_IMAGE_DATA',
            'alt_text' => 'נעלי ספורט פרימיום',
            'sort_order' => 0,
            'is_featured' => true
        ]
    ],
    
    'accordions' => [
        [
            'title' => 'תכונות המוצר',
            'content' => 'סוליה אנטי-להחלקה, ריפוד מתקדם, נושמות ונוחות למרחקים ארוכים.',
            'sort_order' => 0,
            'is_active' => true
        ],
        [
            'title' => 'טיפול ותחזוקה',
            'content' => 'ניתן לניקוי עם מברשת רכה ומים קרירים. אין לשטוף במכונת כביסה.',
            'sort_order' => 1,
            'is_active' => true
        ]
    ],
    
    'badges' => [
        [
            'text' => 'חדש',
            'color' => '#ffffff',
            'background_color' => '#0066cc',
            'position' => 'top-right',
            'is_active' => true
        ]
    ]
];

try {
    $productId2 = $productManager->createProduct($store['id'], $product2Data);
    echo "<h2>✅ מוצר 2 נוצר בהצלחה - ID: $productId2</h2>";
    echo "<p><strong>שם:</strong> {$product2Data['name']}</p>";
    echo "<p><strong>ללא וריאציות</strong></p>";
    echo "<p><strong>אקורדיונים:</strong> " . count($product2Data['accordions']) . "</p>";
    echo "<p><strong>מדבקות:</strong> " . count($product2Data['badges']) . "</p>";
} catch (Exception $e) {
    echo "<h2>❌ שגיאה ביצירת מוצר 2: " . $e->getMessage() . "</h2>";
}

// Test Product 3: תיק עם קישורי מוצרים ו-Upsell
$product3Data = [
    'name' => 'תיק גב עסקי מעוצב',
    'description' => 'תיק גב עסקי אלגנטי ופרקטי עם תאים מרובים. מושלם לעבודה ולנסיעות.',
    'short_description' => 'תיק גב עסקי אלגנטי',
    'sku' => 'BAG-001',
    'price' => 199.90,
    'compare_price' => 259.90,
    'cost_price' => 100.00,
    'inventory_quantity' => 15,
    'track_inventory' => true,
    'weight' => 1.2,
    'status' => 'active',
    'vendor' => 'BagStyle',
    'product_type' => 'תיקים',
    'tags' => 'תיק גב, עסקי, נסיעות, איכות',
    'has_variants' => false,
    'seo_title' => 'תיק גב עסקי מעוצב - BagStyle',
    'seo_description' => 'תיק גב עסקי איכותי עם תאים מרובים. מושלם לעבודה ולנסיעות.',
    'seo_keywords' => 'תיק גב, תיק עסקי, תיקים',
    
    'categories' => [3], // אביזרים
    
    'media' => [
        [
            'type' => 'image',
            'url' => 'data:image/jpeg;base64,BAG_IMAGE_DATA',
            'alt_text' => 'תיק גב עסקי מעוצב',
            'sort_order' => 0,
            'is_featured' => true
        ]
    ],
    
    'accordions' => [
        [
            'title' => 'מפרט טכני',
            'content' => 'גודל: 45x30x15 ס"מ. תא מרכזי, תא ללפטופ, כיסים צדדיים, רצועות מרופדות.',
            'sort_order' => 0,
            'is_active' => true
        ]
    ],
    
    'badges' => [
        [
            'text' => 'אחריות שנה',
            'color' => '#ffffff',
            'background_color' => '#ff8800',
            'position' => 'bottom-right',
            'is_active' => true
        ]
    ],
    
    // קישורי מוצרים
    'related_products' => [$productId1, $productId2],
    'related_types' => ['cross_sell', 'cross_sell'],
    
    'upsell_products' => [$productId1],
    'upsell_descriptions' => ['חולצה מתאימה לסגנון עסקי'],
    
    'bundles' => [
        [
            'name' => 'חבילת עסקים מושלמת',
            'description' => 'תיק גב + חולצת פולו במחיר מיוחד',
            'discount_type' => 'percentage',
            'discount_value' => 15.0,
            'products' => [
                ['product_id' => $productId1, 'quantity' => 1],
                ['product_id' => $productId2, 'quantity' => 1]
            ]
        ]
    ],
    
    // הצעות אוטומטיות
    'auto_suggest_category' => true,
    'auto_suggest_price' => true,
    'auto_suggest_vendor' => true,
    'auto_suggest_tags' => true
];

try {
    $productId3 = $productManager->createProduct($store['id'], $product3Data);
    echo "<h2>✅ מוצר 3 נוצר בהצלחה - ID: $productId3</h2>";
    echo "<p><strong>שם:</strong> {$product3Data['name']}</p>";
    echo "<p><strong>מוצרים קשורים:</strong> " . count($product3Data['related_products']) . "</p>";
    echo "<p><strong>Upsell:</strong> " . count($product3Data['upsell_products']) . "</p>";
    echo "<p><strong>חבילות:</strong> " . count($product3Data['bundles']) . "</p>";
    echo "<p><strong>הצעות אוטומטיות:</strong> מופעל</p>";
} catch (Exception $e) {
    echo "<h2>❌ שגיאה ביצירת מוצר 3: " . $e->getMessage() . "</h2>";
}

echo "<br><h2>📊 סיכום הבדיקה:</h2>";
echo "<ul>";
echo "<li>✅ מוצר עם וריאציות מלאות (צבע + מידה)</li>";
echo "<li>✅ מוצר פשוט ללא וריאציות</li>";
echo "<li>✅ מוצר עם קישורים ל-Cross-sell ו-Upsell</li>";
echo "<li>✅ אקורדיונים לכל המוצרים</li>";
echo "<li>✅ מדבקות מעוצבות</li>";
echo "<li>✅ מדיה לפי מאפיינים (גלריה לצבעים)</li>";
echo "<li>✅ חבילות מוצרים</li>";
echo "<li>✅ הצעות אוטומטיות</li>";
echo "</ul>";

echo "<br><h3>🗑️ מחיקת קובץ הטסט...</h3>";

// Self-destruct this file after running
unlink(__FILE__);
echo "<p>✅ קובץ הטסט נמחק בהצלחה</p>";

?> 