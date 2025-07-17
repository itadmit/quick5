<?php
require_once 'config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "בודק מבנה טבלאות מאפיינים:\n\n";
    
    // בדיקת מבנה product_variants
    echo "=== מבנה product_variants ===\n";
    $stmt = $pdo->query("DESCRIBE product_variants");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "Column: {$col['Field']}, Type: {$col['Type']}, Null: {$col['Null']}, Default: {$col['Default']}\n";
    }
    
    echo "\n=== מבנה product_attributes ===\n";
    $stmt = $pdo->query("DESCRIBE product_attributes");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "Column: {$col['Field']}, Type: {$col['Type']}, Null: {$col['Null']}, Default: {$col['Default']}\n";
    }
    
    echo "\n=== מבנה attribute_values ===\n";
    $stmt = $pdo->query("DESCRIBE attribute_values");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "Column: {$col['Field']}, Type: {$col['Type']}, Null: {$col['Null']}, Default: {$col['Default']}\n";
    }
    
    echo "\n=== דוגמת נתונים מ product_variants ===\n";
    $stmt = $pdo->query("SELECT * FROM product_variants WHERE product_id = 27 LIMIT 3");
    $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($variants as $variant) {
        echo "ID: {$variant['id']}, SKU: {$variant['sku']}\n";
        echo "עמודות זמינות: " . implode(', ', array_keys($variant)) . "\n";
        echo "---\n";
    }
    
    // בדיקת טבלת variant_attribute_values
    echo "\n=== מבנה variant_attribute_values ===\n";
    $stmt = $pdo->query("DESCRIBE variant_attribute_values");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "Column: {$col['Field']}, Type: {$col['Type']}, Null: {$col['Null']}, Default: {$col['Default']}\n";
    }
    
    echo "\n=== נתונים מ variant_attribute_values ===\n";
    $stmt = $pdo->query("SELECT * FROM variant_attribute_values LIMIT 10");
    $vav = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($vav as $row) {
        echo "Variant ID: {$row['variant_id']}, Attribute Value ID: {$row['attribute_value_id']}\n";
    }
    
} catch (Exception $e) {
    echo "שגיאה: " . $e->getMessage() . "\n";
}
?> 