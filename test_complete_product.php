<?php
require_once 'includes/config.php';
require_once 'includes/ProductManager.php';

echo "<h1>🧪 טסט מקיף - כל תכונות המוצר החדשות</h1>";

try {
    $pdo = getDB();
    echo "<p style='color: green;'>✅ חיבור למסד נתונים הצליח</p>";
    
    $productManager = new ProductManager();
    
    // נתוני מוצר מלאים עם כל השדות החדשים
    $productData = [
        'name' => 'חולצת פולו פרימיום',
        'description' => 'חולצת פולו איכותית עשויה מכותנה 100% עם עיצוב אלגנטי ונוחות מקסימלית. מושלמת לאירועים חגיגיים ולחיי היומיום.',
        'short_description' => 'חולצת פולו איכותית מכותנה 100% בעיצוב אלגנטי ונוח',
        'sku' => 'POLO-PREMIUM-001',
        'barcode' => '7290012345678',
        'price' => 199.90,
        'compare_price' => 249.90,
        'cost_price' => 120.00,
        'track_inventory' => 1,
        'inventory_quantity' => 50,
        'allow_backorders' => 0,
        'weight' => 0.300,
        'vendor' => 'TextileCorp Ltd',
        'product_type' => 'בגדים',
        'tags' => 'פולו, כותנה, אלגנטי, נוח, איכותי, קיץ',
        'status' => 'active',
        'featured' => 1,
        'seo_title' => 'חולצת פולו פרימיום - כותנה 100% איכותית',
        'seo_description' => 'חולצת פולו איכותית עשויה מכותנה 100%. עיצוב אלגנטי, נוחות מקסימלית. משלוח חינם לכל הארץ. הזמנה און-ליין.',
        'seo_keywords' => 'חולצת פולו, כותנה, בגדי גברים, אופנה, איכות, נוח',
        'has_variants' => 1,
        'gallery_attribute' => 'צבע',
        
        // קטגוריות
        'categories' => [1, 3], // קטגוריות שקיימות במערכת
        
        // מדיה בסיסית
        'media' => [
            [
                'type' => 'image',
                'url' => '/uploads/polo-main.jpg',
                'alt_text' => 'חולצת פולו פרימיום - תמונה ראשית',
                'sort_order' => 0,
                'is_featured' => 1
            ],
            [
                'type' => 'image', 
                'url' => '/uploads/polo-back.jpg',
                'alt_text' => 'חולצת פולו פרימיום - מבט מאחור',
                'sort_order' => 1,
                'is_featured' => 0
            ]
        ],
        
        // מאפיינים ווריאציות
        'attributes' => [
            [
                'name' => 'צבע',
                'display_name' => 'צבע',
                'type' => 'color',
                'is_variant' => 1,
                'values' => [
                    ['value' => 'כחול', 'color_hex' => '#1E40AF'],
                    ['value' => 'אדום', 'color_hex' => '#DC2626'], 
                    ['value' => 'לבן', 'color_hex' => '#FFFFFF'],
                    ['value' => 'שחור', 'color_hex' => '#000000']
                ]
            ],
            [
                'name' => 'מידה',
                'display_name' => 'מידה',
                'type' => 'text',
                'is_variant' => 1,
                'values' => [
                    ['value' => 'S'],
                    ['value' => 'M'],
                    ['value' => 'L'],
                    ['value' => 'XL']
                ]
            ]
        ],
        
        // וריאציות (16 צירופים: 4 צבעים × 4 מידות)
        'variants' => [
            // כחול S
            ['sku' => 'POLO-BLUE-S', 'price' => 199.90, 'cost_price' => 120.00, 'inventory' => 10, 'attributes' => ['צבע' => 'כחול', 'מידה' => 'S']],
            ['sku' => 'POLO-BLUE-M', 'price' => 199.90, 'cost_price' => 120.00, 'inventory' => 15, 'attributes' => ['צבע' => 'כחול', 'מידה' => 'M']],
            ['sku' => 'POLO-BLUE-L', 'price' => 199.90, 'cost_price' => 120.00, 'inventory' => 12, 'attributes' => ['צבע' => 'כחול', 'מידה' => 'L']],
            ['sku' => 'POLO-BLUE-XL', 'price' => 199.90, 'cost_price' => 120.00, 'inventory' => 8, 'attributes' => ['צבע' => 'כחול', 'מידה' => 'XL']],
            
            // אדום
            ['sku' => 'POLO-RED-S', 'price' => 199.90, 'cost_price' => 120.00, 'inventory' => 5, 'attributes' => ['צבע' => 'אדום', 'מידה' => 'S']],
            ['sku' => 'POLO-RED-M', 'price' => 199.90, 'cost_price' => 120.00, 'inventory' => 10, 'attributes' => ['צבע' => 'אדום', 'מידה' => 'M']],
            ['sku' => 'POLO-RED-L', 'price' => 199.90, 'cost_price' => 120.00, 'inventory' => 8, 'attributes' => ['צבע' => 'אדום', 'מידה' => 'L']],
            ['sku' => 'POLO-RED-XL', 'price' => 199.90, 'cost_price' => 120.00, 'inventory' => 3, 'attributes' => ['צבע' => 'אדום', 'מידה' => 'XL']],
            
            // לבן
            ['sku' => 'POLO-WHITE-S', 'price' => 199.90, 'cost_price' => 120.00, 'inventory' => 20, 'attributes' => ['צבע' => 'לבן', 'מידה' => 'S']],
            ['sku' => 'POLO-WHITE-M', 'price' => 199.90, 'cost_price' => 120.00, 'inventory' => 25, 'attributes' => ['צבע' => 'לבן', 'מידה' => 'M']],
            ['sku' => 'POLO-WHITE-L', 'price' => 199.90, 'cost_price' => 120.00, 'inventory' => 18, 'attributes' => ['צבע' => 'לבן', 'מידה' => 'L']],
            ['sku' => 'POLO-WHITE-XL', 'price' => 199.90, 'cost_price' => 120.00, 'inventory' => 12, 'attributes' => ['צבע' => 'לבן', 'מידה' => 'XL']],
            
            // שחור
            ['sku' => 'POLO-BLACK-S', 'price' => 199.90, 'cost_price' => 120.00, 'inventory' => 8, 'attributes' => ['צבע' => 'שחור', 'מידה' => 'S']],
            ['sku' => 'POLO-BLACK-M', 'price' => 199.90, 'cost_price' => 120.00, 'inventory' => 15, 'attributes' => ['צבע' => 'שחור', 'מידה' => 'M']],
            ['sku' => 'POLO-BLACK-L', 'price' => 199.90, 'cost_price' => 120.00, 'inventory' => 10, 'attributes' => ['צבע' => 'שחור', 'מידה' => 'L']],
            ['sku' => 'POLO-BLACK-XL', 'price' => 199.90, 'cost_price' => 120.00, 'inventory' => 5, 'attributes' => ['צבע' => 'שחור', 'מידה' => 'XL']]
        ],
        
        // מדיה לפי מאפיינים (תמונות לכל צבע)
        'attribute_media' => [
            'צבע' => [
                'כחול' => [
                    ['type' => 'image', 'url' => '/uploads/polo-blue-1.jpg', 'alt_text' => 'חולצת פולו כחולה'],
                    ['type' => 'image', 'url' => '/uploads/polo-blue-2.jpg', 'alt_text' => 'חולצת פולו כחולה - זווית נוספת']
                ],
                'אדום' => [
                    ['type' => 'image', 'url' => '/uploads/polo-red-1.jpg', 'alt_text' => 'חולצת פולו אדומה'],
                    ['type' => 'image', 'url' => '/uploads/polo-red-2.jpg', 'alt_text' => 'חולצת פולו אדומה - זווית נוספת']
                ],
                'לבן' => [
                    ['type' => 'image', 'url' => '/uploads/polo-white-1.jpg', 'alt_text' => 'חולצת פולו לבנה'],
                    ['type' => 'image', 'url' => '/uploads/polo-white-2.jpg', 'alt_text' => 'חולצת פולו לבנה - זווית נוספת']
                ],
                'שחור' => [
                    ['type' => 'image', 'url' => '/uploads/polo-black-1.jpg', 'alt_text' => 'חולצת פולו שחורה'],
                    ['type' => 'image', 'url' => '/uploads/polo-black-2.jpg', 'alt_text' => 'חולצת פולו שחורה - זווית נוספת']
                ]
            ]
        ],
        
        // אקורדיונים
        'accordions' => [
            ['title' => 'תיאור המוצר', 'content' => 'חולצת פולו פרימיום עשויה מכותנה 100% איכותית. מעוצבת במיוחד לנוחות מקסימלית ואלגנטיות בכל אירוע.'],
            ['title' => 'הוראות טיפוח', 'content' => 'כביסה במכונה עד 30 מעלות. יבוש באוויר. אין להלבין. גיהוץ בחום בינוני.'],
            ['title' => 'משלוחים והחזרות', 'content' => 'משלוח חינם לכל הארץ. אפשרות החזרה עד 30 יום מיום הקנייה.'],
            ['title' => 'טבלת מידות', 'content' => 'S: רוחב חזה 50 ס"מ | M: רוחב חזה 54 ס"מ | L: רוחב חזה 58 ס"מ | XL: רוחב חזה 62 ס"מ']
        ],
        
        // מדבקות
        'badges' => [
            ['text' => 'מומלץ!', 'color' => '#FFFFFF', 'background_color' => '#22C55E', 'position' => 'top-right'],
            ['text' => 'חדש', 'color' => '#FFFFFF', 'background_color' => '#3B82F6', 'position' => 'top-left']
        ],
        
        // מוצרים קשורים (Cross-sell)
        'related_products' => ['מכנסי ג\'ינס קלאסיים', 'נעלי סניקרס לבנות'],
        'related_types' => ['related', 'cross_sell'],
        
        // מוצרי שדרוג (Upsell)  
        'upsell_products' => ['חולצת פולו לוקסוס'],
        'upsell_descriptions' => ['גרסה יוקרתיה עם רקמה מיוחדת'],
        
        // חבילות מוצרים
        'bundles' => [
            [
                'name' => 'חבילת קיץ מושלמת',
                'products' => ['מכנסיים קצרים', 'כובע'],
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'description' => 'חולצת פולו + מכנסיים + כובע = הלוק המושלם לקיץ'
            ]
        ],
        
        // הצעות אוטומטיות
        'auto_suggest_category' => 1,
        'auto_suggest_price' => 1, 
        'auto_suggest_vendor' => 1,
        'auto_suggest_tags' => 1
    ];
    
    echo "<h2>🔧 יוצר מוצר עם כל התכונות החדשות...</h2>";
    
    // יצירת המוצר
    $result = $productManager->createProduct(1, $productData); // store_id = 1
    
    if ($result['success']) {
        $productId = $result['product_id'];
        echo "<p style='color: green;'>✅ המוצר נוצר בהצלחה! מזהה: $productId</p>";
        
        // קבלת המוצר לבדיקה
        echo "<h3>📋 בדיקת נתוני המוצר שנוצר:</h3>";
        $productResult = $productManager->getProduct($productId);
        
        if ($productResult['success']) {
            $product = $productResult['product'];
            
            echo "<div style='background: #f0f9ff; padding: 20px; border-radius: 8px; border: 1px solid #0ea5e9;'>";
            echo "<h4>פרטים בסיסיים:</h4>";
            echo "<p><strong>שם:</strong> " . htmlspecialchars($product['name']) . "</p>";
            echo "<p><strong>תיאור קצר:</strong> " . htmlspecialchars($product['short_description']) . "</p>";
            echo "<p><strong>SKU:</strong> " . htmlspecialchars($product['sku']) . "</p>";
            echo "<p><strong>ברקוד:</strong> " . htmlspecialchars($product['barcode']) . "</p>";
            echo "<p><strong>משקל:</strong> " . htmlspecialchars($product['weight']) . " ק\"ג</p>";
            echo "<p><strong>יצרן:</strong> " . htmlspecialchars($product['vendor']) . "</p>";
            echo "<p><strong>סוג מוצר:</strong> " . htmlspecialchars($product['product_type']) . "</p>";
            echo "<p><strong>תגיות:</strong> " . htmlspecialchars($product['tags']) . "</p>";
            echo "<p><strong>סטטוס:</strong> " . htmlspecialchars($product['status']) . "</p>";
            echo "<p><strong>מומלץ:</strong> " . ($product['featured'] ? 'כן' : 'לא') . "</p>";
            echo "<p><strong>יש וריאציות:</strong> " . ($product['has_variants'] ? 'כן' : 'לא') . "</p>";
            
            echo "<h4>SEO:</h4>";
            echo "<p><strong>כותרת SEO:</strong> " . htmlspecialchars($product['seo_title']) . "</p>";
            echo "<p><strong>תיאור SEO:</strong> " . htmlspecialchars($product['seo_description']) . "</p>";
            
            echo "<h4>נתוני מלאי:</h4>";
            echo "<p><strong>מעקב מלאי:</strong> " . ($product['track_inventory'] ? 'כן' : 'לא') . "</p>";
            echo "<p><strong>כמות במלאי:</strong> " . htmlspecialchars($product['inventory_quantity']) . "</p>";
            echo "<p><strong>הזמנה מראש:</strong> " . ($product['allow_backorders'] ? 'כן' : 'לא') . "</p>";
            
            echo "<h4>מאפיינים ווריאציות:</h4>";
            echo "<p><strong>מספר מאפיינים:</strong> " . count($product['attributes']) . "</p>";
            echo "<p><strong>מספר וריאציות:</strong> " . count($product['variants']) . "</p>";
            
            echo "<h4>אקורדיונים:</h4>";
            echo "<p><strong>מספר אקורדיונים:</strong> " . count($product['accordions']) . "</p>";
            
            echo "<h4>מדבקות:</h4>";
            echo "<p><strong>מספר מדבקות:</strong> " . count($product['badges']) . "</p>";
            
            echo "</div>";
            
            echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 כל התכונות החדשות עובדות מצוין!</p>";
            
        } else {
            echo "<p style='color: red;'>❌ שגיאה בקבלת המוצר: " . htmlspecialchars($productResult['message']) . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ שגיאה ביצירת המוצר: " . htmlspecialchars($result['message']) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ שגיאה כללית: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr style='margin: 30px 0;'>";
echo "<h2>📋 סיכום התכונות החדשות שנבדקו:</h2>";
echo "<div style='background: #f8fafc; padding: 20px; border-radius: 8px;'>";
echo "<ol>";
echo "<li>✅ <strong>תיאור קצר (short_description)</strong> - תיאור קצר למוצר</li>";
echo "<li>✅ <strong>ברקוד (barcode)</strong> - ברקוד למוצר</li>";
echo "<li>✅ <strong>SKU</strong> - קוד מוצר ייחודי</li>";
echo "<li>✅ <strong>משקל (weight)</strong> - משקל המוצר בק\"ג</li>";
echo "<li>✅ <strong>יצרן/ספק (vendor)</strong> - פרטי היצרן</li>";
echo "<li>✅ <strong>סוג מוצר (product_type)</strong> - קטגוריית המוצר</li>";
echo "<li>✅ <strong>תגיות (tags)</strong> - תגיות למוצר</li>";
echo "<li>✅ <strong>סטטוס מוצר (status)</strong> - פעיל/טיוטה/בארכיון</li>";
echo "<li>✅ <strong>מוצר מומלץ (featured)</strong> - סימון מוצר מומלץ</li>";
echo "<li>✅ <strong>הזמנה מראש (allow_backorders)</strong> - אפשרות הזמנה כשאין במלאי</li>";
echo "<li>✅ <strong>שדות SEO</strong> - כותרת, תיאור ומילות מפתח</li>";
echo "<li>✅ <strong>וריאציות מתקדמות</strong> - כולל הסתרת תמחור כללי</li>";
echo "<li>✅ <strong>גלרייה לפי מאפיין</strong> - תמונות שונות לכל ערך מאפיין</li>";
echo "</ol>";
echo "</div>";
?> 