<?php
require_once 'config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "בודק מבנה מאפיינים:\n\n";
    
    // בדיקת טבלאות קיימות
    echo "=== טבלאות קיימות ===\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "Table: $table\n";
    }
    
    // נסה לבדוק את טבלת product_attributes
    echo "\n=== טבלת product_attributes ===\n";
    $stmt = $pdo->query("SELECT * FROM product_attributes ORDER BY id LIMIT 10");
    $product_attrs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($product_attrs as $attr) {
        echo "ID: {$attr['id']}, Product ID: {$attr['product_id']}, Name: {$attr['name']}, Value: {$attr['value']}\n";
    }
    
    echo "\n=== טבלת attribute_values ===\n";
    $stmt = $pdo->query("SELECT * FROM attribute_values ORDER BY id LIMIT 10");
    $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($values as $val) {
        echo "ID: {$val['id']}, Value: {$val['value']}\n";
    }
    
    echo "\n=== בדיקת המוצר שיובא ===\n";
    $stmt = $pdo->query("SELECT * FROM products WHERE sku = '1230527050'");
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        echo "מוצר נמצא: {$product['name']}, ID: {$product['id']}\n";
        
        // בדיקת וריאציות
        $stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ?");
        $stmt->execute([$product['id']]);
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "מספר וריאציות: " . count($variants) . "\n";
        
        foreach ($variants as $variant) {
            echo "Variant: {$variant['sku']}, Attributes: {$variant['attributes']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "שגיאה: " . $e->getMessage() . "\n";
}
?> 