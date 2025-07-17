<?php
require_once 'config/database.php';
require_once 'includes/CsvImporter.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "🧪 בודק זיהוי וריאציות...\n\n";
    
    // נמחק רק את המוצרים הנוכחיים
    $stmt = $pdo->query("DELETE FROM product_media WHERE product_id IN (SELECT id FROM products WHERE name LIKE '%ברי%')");
    $stmt = $pdo->query("DELETE FROM product_variants WHERE product_id IN (SELECT id FROM products WHERE name LIKE '%ברי%')");
    $stmt = $pdo->query("DELETE FROM product_categories WHERE product_id IN (SELECT id FROM products WHERE name LIKE '%ברי%')");
    $stmt = $pdo->query("DELETE FROM product_attributes WHERE product_id IN (SELECT id FROM products WHERE name LIKE '%ברי%')");
    $stmt = $pdo->query("DELETE FROM products WHERE name LIKE '%ברי%'");
    
    echo "נוקו מוצרים קיימים\n\n";
    
    // ייבוא מחדש עם הלוגינג החדש
    $importer = new CsvImporter(1, 1);
    $importer->setOptions([
        'skip_existing' => false,
        'download_images' => false, // בלי תמונות כדי לחסוך זמן
        'create_categories' => true,
        'image_domain' => 'https://www.studiopasha.co.il/media/catalog/product',
        'image_quality' => 'high'
    ]);
    
    echo "מתחיל ייבוא ללא תמונות...\n";
    $result = $importer->import('./example-import/magento-1-product-variable.csv');
    
    echo "\n📊 תוצאות:\n";
    echo "הצלחה: " . ($result['success'] ? 'כן' : 'לא') . "\n";
    echo "מוצרים יובאו: {$result['imported_products']}\n";
    
    // בדיקת תוצאות
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE name LIKE '%ברי%'");
    $product_count = $stmt->fetchColumn();
    echo "מספר מוצרים במסד נתונים: $product_count\n";
    
    if ($product_count == 1) {
        echo "✅ בעיה נפתרה! יש רק מוצר אחד!\n";
    } else {
        echo "❌ עדיין יש בעיה - $product_count מוצרים\n";
    }
    
} catch (Exception $e) {
    echo "שגיאה: " . $e->getMessage() . "\n";
}
?> 