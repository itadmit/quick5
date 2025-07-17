<?php
require_once 'config/database.php';
require_once 'includes/CsvImporter.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "🧹 מתחיל ניקוי מוצרים כפולים...\n\n";
    
    // 1. מחיקת כל המוצרים שקשורים ל"שורט חצאית ברי"
    echo "=== מוחק מוצרים קיימים ===\n";
    
    // מציאת כל המוצרים הקשורים
    $stmt = $pdo->query("
        SELECT id, sku, name 
        FROM products 
        WHERE name LIKE '%ברי%' OR sku LIKE '%1230527050%'
        ORDER BY id
    ");
    $products_to_delete = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "נמצאו " . count($products_to_delete) . " מוצרים למחיקה:\n";
    foreach ($products_to_delete as $p) {
        echo "- ID: {$p['id']}, SKU: {$p['sku']}, Name: {$p['name']}\n";
    }
    
    if (count($products_to_delete) > 0) {
        $product_ids = array_column($products_to_delete, 'id');
        $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
        
        // מחיקת media
        $stmt = $pdo->prepare("DELETE FROM product_media WHERE product_id IN ($placeholders)");
        $stmt->execute($product_ids);
        echo "נמחקו תמונות מוצרים\n";
        
        // מחיקת variants
        $stmt = $pdo->prepare("DELETE FROM product_variants WHERE product_id IN ($placeholders)");
        $stmt->execute($product_ids);
        echo "נמחקו וריאציות מוצרים\n";
        
        // מחיקת categories linkage
        $stmt = $pdo->prepare("DELETE FROM product_categories WHERE product_id IN ($placeholders)");
        $stmt->execute($product_ids);
        echo "נמחקו קישורי קטגוריות\n";
        
        // מחיקת attributes
        $stmt = $pdo->prepare("DELETE FROM product_attributes WHERE product_id IN ($placeholders)");
        $stmt->execute($product_ids);
        echo "נמחקו מאפיינים\n";
        
        // מחיקת המוצרים עצמם
        $stmt = $pdo->prepare("DELETE FROM products WHERE id IN ($placeholders)");
        $stmt->execute($product_ids);
        echo "נמחקו המוצרים עצמם\n";
    }
    
    echo "\n✅ ניקוי הושלם!\n\n";
    
    // 2. ייבוא מחדש
    echo "🚀 מתחיל ייבוא מחדש...\n\n";
    
    $csvFile = './example-import/magento-1-product-variable.csv';
    if (!file_exists($csvFile)) {
        throw new Exception("קובץ CSV לא נמצא: $csvFile");
    }
    
    // יצירת job ייבוא
    $import_unique_id = uniqid('import_', true);
    
    $stmt = $pdo->prepare("
        INSERT INTO import_jobs (
            import_id, store_id, user_id, filename, file_path, skip_existing, 
            download_images, create_categories, image_domain, image_quality, 
            status, total_rows, processed_rows, imported_products, failed_products, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'processing', 0, 0, 0, 0, NOW())
    ");
    
    $stmt->execute([
        $import_unique_id,
        1, // store_id
        1, // user_id
        'magento-1-product-variable.csv',
        './example-import/magento-1-product-variable.csv', // file_path
        0, // skip_existing = false
        1, // download_images = true
        1, // create_categories = true
        'https://www.studiopasha.co.il/media/catalog/product',
        'high'
    ]);
    
    $job_id = $pdo->lastInsertId();
    echo "נוצר job ייבוא ID: $job_id\n";
    
    // ביצוע הייבוא
    $importer = new CsvImporter(1, $job_id);
    $importer->setOptions([
        'skip_existing' => false,
        'download_images' => true,
        'create_categories' => true,
        'image_domain' => 'https://www.studiopasha.co.il/media/catalog/product',
        'image_quality' => 'high'
    ]);
    
    echo "מתחיל ייבוא...\n";
    $result = $importer->import($csvFile);
    
    // עדכון job
    $status = $result['success'] ? 'completed' : 'failed';
    $stmt = $pdo->prepare("
        UPDATE import_jobs 
        SET status = ?, 
            total_rows = ?, 
            processed_rows = ?, 
            imported_products = ?, 
            failed_products = ?,
            error_log = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $status,
        $result['total_rows'],
        $result['processed_rows'],
        $result['imported_products'],
        $result['failed_products'],
        $result['error_log'],
        $job_id
    ]);
    
    echo "\n📊 תוצאות הייבוא:\n";
    echo "הצלחה: " . ($result['success'] ? 'כן' : 'לא') . "\n";
    echo "שורות כולל: {$result['total_rows']}\n";
    echo "שורות מעובדות: {$result['processed_rows']}\n";
    echo "מוצרים יובאו: {$result['imported_products']}\n";
    echo "שגיאות: {$result['failed_products']}\n";
    
    if (!empty($result['error_log'])) {
        echo "\nשגיאות:\n" . $result['error_log'] . "\n";
    }
    
    // בדיקת תוצאות
    echo "\n🔍 בדיקת תוצאות:\n";
    
    // ספירת מוצרים
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE name LIKE '%ברי%'");
    $product_count = $stmt->fetchColumn();
    echo "מספר מוצרים: $product_count\n";
    
    // ספירת וריאציות
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM product_variants pv 
        JOIN products p ON pv.product_id = p.id 
        WHERE p.name LIKE '%ברי%'
    ");
    $variant_count = $stmt->fetchColumn();
    echo "מספר וריאציות: $variant_count\n";
    
    // ספירת תמונות
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM product_media pm 
        JOIN products p ON pm.product_id = p.id 
        WHERE p.name LIKE '%ברי%'
    ");
    $media_count = $stmt->fetchColumn();
    echo "מספר תמונות: $media_count\n";
    
    // הצגת המוצר הראשי
    $stmt = $pdo->query("
        SELECT id, sku, name, product_type, has_variants 
        FROM products 
        WHERE sku = '1230527050'
    ");
    $main_product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($main_product) {
        echo "\n✅ המוצר הראשי נוצר בהצלחה!\n";
        echo "ID: {$main_product['id']}, SKU: {$main_product['sku']}\n";
        echo "שם: {$main_product['name']}\n";
        echo "סוג: {$main_product['product_type']}\n";
        echo "יש וריאציות: " . ($main_product['has_variants'] ? 'כן' : 'לא') . "\n";
    } else {
        echo "\n❌ המוצר הראשי לא נוצר!\n";
    }
    
    echo "\n🎉 תהליך הושלם!\n";
    
} catch (Exception $e) {
    echo "שגיאה: " . $e->getMessage() . "\n";
}
?> 