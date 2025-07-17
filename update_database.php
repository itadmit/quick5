<?php
require_once 'includes/config.php';

echo "<h1>🔧 עדכון מסד נתונים - הוספת שדות חסרים</h1>";

try {
    $pdo = getDB();
    echo "<p style='color: green;'>✅ חיבור למסד נתונים הצליח</p>";
    
    // רשימת השדות שצריך להוסיף
    $fieldsToAdd = [
        'barcode' => "ALTER TABLE products ADD COLUMN barcode VARCHAR(100) AFTER sku",
        'vendor' => "ALTER TABLE products ADD COLUMN vendor VARCHAR(255) AFTER is_digital", 
        'product_type' => "ALTER TABLE products ADD COLUMN product_type VARCHAR(100) AFTER vendor",
        'tags' => "ALTER TABLE products ADD COLUMN tags TEXT AFTER product_type",
        'has_variants' => "ALTER TABLE products ADD COLUMN has_variants BOOLEAN DEFAULT FALSE AFTER featured",
        'seo_keywords' => "ALTER TABLE products ADD COLUMN seo_keywords TEXT AFTER seo_description"
    ];
    
    // בדיקה ועדכון כל שדה
    foreach ($fieldsToAdd as $fieldName => $alterQuery) {
        // בדיקה אם השדה קיים
        $stmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = ?");
        $stmt->execute([$fieldName]);
        $exists = $stmt->fetch();
        
        if (!$exists) {
            echo "<p style='color: blue;'>🔧 מוסיף שדה: $fieldName</p>";
            $pdo->exec($alterQuery);
            echo "<p style='color: green;'>✅ שדה $fieldName נוסף בהצלחה</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ שדה $fieldName כבר קיים</p>";
        }
    }
    
    // הוספת אינדקסים
    $indexes = [
        'barcode' => "CREATE INDEX idx_barcode ON products(barcode)",
        'vendor' => "CREATE INDEX idx_vendor ON products(vendor)", 
        'product_type' => "CREATE INDEX idx_product_type ON products(product_type)"
    ];
    
    echo "<h2>📊 הוספת אינדקסים</h2>";
    foreach ($indexes as $indexName => $indexQuery) {
        try {
            $pdo->exec($indexQuery);
            echo "<p style='color: green;'>✅ אינדקס $indexName נוסף</p>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "<p style='color: orange;'>⚠️ אינדקס $indexName כבר קיים</p>";
            } else {
                echo "<p style='color: red;'>❌ שגיאה באינדקס $indexName: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // הצגת מבנה הטבלה הסופי
    echo "<h2>📋 מבנה הטבלה לאחר העדכון</h2>";
    $stmt = $pdo->query("DESCRIBE products");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr><th style='padding: 8px; background: #f0f0f0;'>שדה</th><th style='padding: 8px; background: #f0f0f0;'>סוג</th><th style='padding: 8px; background: #f0f0f0;'>Null</th><th style='padding: 8px; background: #f0f0f0;'>ברירת מחדל</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Field'] ?? '') . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Type'] ?? '') . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Null'] ?? '') . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($column['Default'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2 style='color: green;'>🎉 עדכון מסד הנתונים הושלם בהצלחה!</h2>";
    echo "<p><a href='test_complete_product.php' style='color: blue; text-decoration: underline;'>לחץ כאן לבדיקת יצירת מוצר</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ שגיאה: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 