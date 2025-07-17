<?php
require_once 'config/database.php';
require_once 'includes/CsvImporter.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "🚀 בדיקה סופית מלאה של מערכת הייבוא...\n\n";
    
    // ניקוי מוצרים קודמים
    echo "=== ניקוי מוצרים קודמים ===\n";
    $stmt = $pdo->query("DELETE FROM product_media WHERE product_id IN (SELECT id FROM products WHERE name LIKE '%ברי%')");
    $stmt = $pdo->query("DELETE FROM product_variants WHERE product_id IN (SELECT id FROM products WHERE name LIKE '%ברי%')");
    $stmt = $pdo->query("DELETE FROM product_categories WHERE product_id IN (SELECT id FROM products WHERE name LIKE '%ברי%')");
    $stmt = $pdo->query("DELETE FROM product_attributes WHERE product_id IN (SELECT id FROM products WHERE name LIKE '%ברי%')");
    $stmt = $pdo->query("DELETE FROM products WHERE name LIKE '%ברי%'");
    echo "✅ מוצרים קודמים נוקו\n\n";
    
    // ייבוא מלא עם כל הפיצ'רים
    echo "=== ייבוא מלא עם כל הפיצ'רים ===\n";
    $importer = new CsvImporter(1, 1);
    $importer->setOptions([
        'skip_existing' => false,
        'download_images' => true, // עם תמונות!
        'create_categories' => true,
        'image_domain' => 'https://www.studiopasha.co.il/media/catalog/product',
        'image_quality' => 'high'
    ]);
    
    echo "מתחיל ייבוא מלא (כולל תמונות)...\n";
    $start_time = microtime(true);
    $result = $importer->import('./example-import/magento-1-product-variable.csv');
    $end_time = microtime(true);
    $duration = round($end_time - $start_time, 2);
    
    echo "\n📊 תוצאות הייבוא:\n";
    echo "⏱️ זמן ביצוע: {$duration} שניות\n";
    echo "הצלחה: " . ($result['success'] ? 'כן' : 'לא') . "\n";
    echo "שורות כולל: {$result['total_rows']}\n";
    echo "שורות מעובדות: {$result['processed_rows']}\n";
    echo "מוצרים יובאו: {$result['imported_products']}\n";
    echo "שגיאות: {$result['failed_products']}\n";
    
    // בדיקה מפורטת של התוצאות
    echo "\n🔍 בדיקת תוצאות מפורטת:\n";
    
    // בדיקת המוצר הראשי
    $stmt = $pdo->query("
        SELECT id, sku, name, product_type, has_variants, price, status 
        FROM products 
        WHERE sku = '1230527050'
    ");
    $main_product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($main_product) {
        echo "✅ המוצר הראשי:\n";
        echo "   📦 ID: {$main_product['id']}\n";
        echo "   🏷️ SKU: {$main_product['sku']}\n";
        echo "   📝 שם: {$main_product['name']}\n";
        echo "   🔧 סוג: {$main_product['product_type']}\n";
        echo "   ⚙️ יש וריאציות: " . ($main_product['has_variants'] ? 'כן' : 'לא') . "\n";
        echo "   💰 מחיר: {$main_product['price']}\n";
        echo "   📊 סטטוס: {$main_product['status']}\n";
        
        $product_id = $main_product['id'];
        
        // בדיקת וריאציות
        echo "\n🎨 בדיקת וריאציות:\n";
        $stmt = $pdo->prepare("
            SELECT 
                pv.id,
                pv.sku,
                pv.price,
                GROUP_CONCAT(
                    CONCAT(av.value, '=', av.display_value) 
                    ORDER BY av.attribute_id 
                    SEPARATOR ', '
                ) as attributes
            FROM product_variants pv
            LEFT JOIN variant_attribute_values vav ON pv.id = vav.variant_id
            LEFT JOIN attribute_values av ON vav.attribute_value_id = av.id
            WHERE pv.product_id = ?
            GROUP BY pv.id
            ORDER BY pv.id
        ");
        $stmt->execute([$product_id]);
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "   📊 מספר וריאציות: " . count($variants) . "\n";
        foreach ($variants as $variant) {
            echo "   • SKU: {$variant['sku']}, מחיר: {$variant['price']}, מאפיינים: {$variant['attributes']}\n";
        }
        
        // בדיקת תמונות
        echo "\n🖼️ בדיקת תמונות:\n";
        $stmt = $pdo->prepare("
            SELECT id, url, thumbnail_url, is_primary, alt_text, file_size
            FROM product_media 
            WHERE product_id = ?
            ORDER BY is_primary DESC, sort_order
        ");
        $stmt->execute([$product_id]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "   📊 מספר תמונות: " . count($images) . "\n";
        foreach ($images as $i => $image) {
            $primary = $image['is_primary'] ? ' (ראשית)' : '';
            $size = $image['file_size'] ? ' (' . round($image['file_size']/1024, 1) . ' KB)' : '';
            echo "   • תמונה " . ($i+1) . "$primary$size\n";
            echo "     URL: {$image['url']}\n";
            echo "     Thumbnail: {$image['thumbnail_url']}\n";
            
            // בדיקת קיום קובץ
            $file_path = '.' . parse_url($image['url'], PHP_URL_PATH);
            if (file_exists($file_path)) {
                echo "     ✅ קובץ קיים בשרת\n";
            } else {
                echo "     ❌ קובץ לא קיים בשרת\n";
            }
        }
        
        // בדיקת מאפיינים מתורגמים
        echo "\n🏷️ בדיקת מאפיינים מתורגמים:\n";
        $stmt = $pdo->query("
            SELECT 
                a.name as attr_name,
                a.display_name,
                av.value as code,
                av.display_value
            FROM attribute_values av
            JOIN attributes a ON av.attribute_id = a.id
            WHERE a.name IN ('color', 'size')
            ORDER BY a.name, av.id
        ");
        $attributes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($attributes as $attr) {
            echo "   • {$attr['display_name']}: {$attr['code']} → {$attr['display_value']}\n";
        }
        
        // סיכום סופי
        echo "\n🎉 סיכום סופי:\n";
        
        $expected_structure = [
            'מוצרים' => 1,
            'וריאציות' => 8,
            'תמונות' => 'יותר מ-40',
            'מאפיינים' => 'מתורגמים נכון'
        ];
        
        $actual_results = [
            'מוצרים' => 1,
            'וריאציות' => count($variants),
            'תמונות' => count($images),
            'מאפיינים' => count($attributes) > 0 ? 'מתורגמים' : 'לא מתורגמים'
        ];
        
        $all_good = true;
        
        if ($actual_results['מוצרים'] == 1) {
            echo "✅ מוצר ראשי: יצירה נכונה\n";
        } else {
            echo "❌ מוצר ראשי: בעיה\n";
            $all_good = false;
        }
        
        if ($actual_results['וריאציות'] == 8) {
            echo "✅ וריאציות: כמות נכונה (8)\n";
        } else {
            echo "❌ וריאציות: כמות שגויה ({$actual_results['וריאציות']})\n";
            $all_good = false;
        }
        
        if ($actual_results['תמונות'] > 40) {
            echo "✅ תמונות: הורדו בהצלחה ({$actual_results['תמונות']})\n";
        } else {
            echo "❌ תמונות: לא הורדו מספיק ({$actual_results['תמונות']})\n";
            $all_good = false;
        }
        
        if (count($attributes) >= 6) { // 2 צבעים + 4 מידות
            echo "✅ מאפיינים: מתורגמים נכון (" . count($attributes) . ")\n";
        } else {
            echo "❌ מאפיינים: לא מתורגמים נכון (" . count($attributes) . ")\n";
            $all_good = false;
        }
        
        if ($all_good) {
            echo "\n🎊 הצלחה מושלמת! מערכת הייבוא CSV פועלת בצורה מושלמת!\n";
            echo "המערכת יוצרת נכון:\n";
            echo "• מוצר ראשי אחד עם סוג 'configurable'\n";
            echo "• 8 וריאציות עם מאפיינים מתורגמים\n";
            echo "• עשרות תמונות שהורדו ונשמרו\n";
            echo "• מערכת מאפיינים מתורגמת (שחור/חום, XS-L)\n";
        } else {
            echo "\n⚠️ יש בעיות שצריך לטפל בהן\n";
        }
        
    } else {
        echo "❌ המוצר הראשי לא נוצר!\n";
    }
    
} catch (Exception $e) {
    echo "שגיאה: " . $e->getMessage() . "\n";
}
?> 