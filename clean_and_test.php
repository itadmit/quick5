<?php
require_once 'config/database.php';
require_once 'includes/CsvImporter.php';

echo "<h2>ניקוי וטסט מחדש</h2>\n";

$db = Database::getInstance();
$pdo = $db->getConnection();

// ניקוי מוצרים קיימים
echo "<h3>1. ניקוי מוצרים קיימים</h3>\n";

$tables_to_clean = [
    'product_media',
    'product_variants', 
    'product_categories',
    'attribute_values',
    'product_attributes',
    'attributes',
    'products'
];

foreach ($tables_to_clean as $table) {
    try {
        $stmt = $pdo->prepare("DELETE FROM $table WHERE 1=1");
        $stmt->execute();
        $affected = $stmt->rowCount();
        echo "נוקה טבלה $table - $affected שורות<br>\n";
    } catch (Exception $e) {
        echo "שגיאה בניקוי $table: " . $e->getMessage() . "<br>\n";
    }
}

// איפוס AUTO_INCREMENT
foreach ($tables_to_clean as $table) {
    try {
        $stmt = $pdo->prepare("ALTER TABLE $table AUTO_INCREMENT = 1");
        $stmt->execute();
    } catch (Exception $e) {
        // לא משנה אם נכשל
    }
}

echo "<div style='background: #d4edda; padding: 10px; margin: 10px;'>✅ ניקוי הושלם</div>\n";

// טסט ייבוא חדש
echo "<h3>2. טסט ייבוא חדש</h3>\n";

$csv_file = 'uploads/imports/import_6878f6c4b35a30.93184070.csv';

if (!file_exists($csv_file)) {
    echo "<div style='color: red;'>קובץ CSV לא נמצא: $csv_file</div>\n";
    exit;
}

// יצירת importer
$importer = new CsvImporter(1, 'test_clean');

try {
    echo "<div style='background: #e7f3ff; padding: 10px; margin: 10px;'>מתחיל ייבוא...</div>\n";
    
    $result = $importer->import($csv_file);
    
    echo "<div style='background: " . ($result['success'] ? '#d4edda' : '#f8d7da') . "; padding: 10px; margin: 10px;'>";
    echo "<strong>תוצאת ייבוא:</strong><br>";
    echo "הצלחה: " . ($result['success'] ? 'כן' : 'לא') . "<br>";
    echo "שורות כולל: " . ($result['total_rows'] ?? 0) . "<br>";
    echo "שורות מעובדות: " . ($result['processed_rows'] ?? 0) . "<br>";
    echo "מוצרים יובאו: " . ($result['imported_products'] ?? 0) . "<br>";
    echo "שגיאות: " . ($result['failed_products'] ?? 0) . "<br>";
    if (!empty($result['error_log'])) {
        echo "לוג שגיאות: " . htmlspecialchars($result['error_log']) . "<br>";
    }
    echo "</div>\n";

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; margin: 10px;'>";
    echo "<strong>שגיאה בייבוא:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>\n";
}

// בדיקת התוצאות במסד הנתונים
echo "<h3>3. בדיקת תוצאות</h3>\n";

try {
    // ספירת מוצרים
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE store_id = 1");
    $stmt->execute();
    $products_count = $stmt->fetch()['count'];
    
    // ספירת וריאציות
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM product_variants pv 
        JOIN products p ON pv.product_id = p.id 
        WHERE p.store_id = 1
    ");
    $stmt->execute();
    $variants_count = $stmt->fetch()['count'];
    
    // רשימת מוצרים
    $stmt = $pdo->prepare("
        SELECT id, sku, name, has_variants 
        FROM products 
        WHERE store_id = 1 
        ORDER BY id
    ");
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7;'>";
    echo "<strong>סיכום התוצאות:</strong><br>";
    echo "מספר מוצרים: $products_count<br>";
    echo "מספר וריאציות: $variants_count<br>";
    echo "<br><strong>צריך להיות:</strong><br>";
    echo "מוצרים: 1 (רק המוצר הקבוצתי)<br>";
    echo "וריאציות: 8 (כל המוצרים הפשוטים כוריאציות)<br>";
    echo "</div>\n";
    
    if (!empty($products)) {
        echo "<h4>מוצרים שנוצרו:</h4>\n";
        foreach ($products as $product) {
            echo "<div style='border: 1px solid #ccc; padding: 8px; margin: 5px;'>";
            echo "<strong>ID:</strong> {$product['id']}<br>";
            echo "<strong>SKU:</strong> {$product['sku']}<br>";
            echo "<strong>שם:</strong> " . htmlspecialchars($product['name']) . "<br>";
            echo "<strong>יש וריאציות:</strong> " . ($product['has_variants'] ? 'כן' : 'לא') . "<br>";
            
            // בדיקת וריאציות למוצר זה
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM product_variants WHERE product_id = ?");
            $stmt->execute([$product['id']]);
            $product_variants = $stmt->fetch()['count'];
            echo "<strong>מספר וריאציות:</strong> $product_variants<br>";
            echo "</div>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>שגיאה בבדיקת תוצאות: " . htmlspecialchars($e->getMessage()) . "</div>\n";
}

echo "<h3>4. לוגים מהטרמינל</h3>\n";
echo "<div style='background: #f8f9fa; padding: 10px; font-family: monospace; font-size: 12px;'>";
echo "בדוק את הלוגים ב-terminal או ב-MAMP logs כדי לראות את ה-debugging שהוספנו<br>";
echo "או הסתכל על error_log של PHP<br>";
echo "</div>\n";
?> 