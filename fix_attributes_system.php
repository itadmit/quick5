<?php
require_once 'config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "מתקן מערכת מאפיינים:\n\n";
    
    // שלב 1: יצירת טבלת attributes אם לא קיימת
    echo "=== יוצר טבלת attributes ===\n";
    $sql = "CREATE TABLE IF NOT EXISTS attributes (
        id int(11) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        display_name varchar(100) NOT NULL,
        type enum('text','color','size','select') DEFAULT 'text',
        sort_order int(11) DEFAULT 0,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "טבלת attributes נוצרה בהצלחה\n";
    
    // שלב 2: הוספת מאפיינים בסיסיים
    echo "\n=== מוסיף מאפיינים בסיסיים ===\n";
    $attributes = [
        ['name' => 'color', 'display_name' => 'צבע', 'type' => 'color'],
        ['name' => 'size', 'display_name' => 'מידה', 'type' => 'size']
    ];
    
    foreach ($attributes as $attr) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO attributes (name, display_name, type) VALUES (?, ?, ?)");
        $stmt->execute([$attr['name'], $attr['display_name'], $attr['type']]);
        echo "הוסף מאפיין: {$attr['display_name']} ({$attr['name']})\n";
    }
    
    // שלב 3: קבלת ID של המאפיינים
    $stmt = $pdo->query("SELECT id, name FROM attributes WHERE name IN ('color', 'size')");
    $attr_map = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $attr_map[$row['name']] = $row['id'];
    }
    
    echo "מיפוי מאפיינים: " . print_r($attr_map, true) . "\n";
    
    // שלב 4: עדכון טבלת attribute_values
    echo "\n=== מעדכן attribute_values ===\n";
    
    // מיפוי ערכים - על בסיס הקודים מהמגנטו CSV
    $value_mappings = [
        // צבעים
        '12' => ['display_value' => 'שחור', 'attribute_id' => $attr_map['color']],
        '25' => ['display_value' => 'חום', 'attribute_id' => $attr_map['color']],
        
        // מידות
        '02' => ['display_value' => 'XS / 34', 'attribute_id' => $attr_map['size']],
        '03' => ['display_value' => 'S / 36', 'attribute_id' => $attr_map['size']],
        '04' => ['display_value' => 'M / 38', 'attribute_id' => $attr_map['size']],
        '05' => ['display_value' => 'L / 40', 'attribute_id' => $attr_map['size']]
    ];
    
    foreach ($value_mappings as $code => $mapping) {
        // בדיקה אם הערך קיים
        $stmt = $pdo->prepare("SELECT id FROM attribute_values WHERE value = ?");
        $stmt->execute([$code]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // עדכון הערך הקיים
            $stmt = $pdo->prepare("UPDATE attribute_values SET display_value = ?, attribute_id = ? WHERE value = ?");
            $stmt->execute([$mapping['display_value'], $mapping['attribute_id'], $code]);
            echo "עודכן ערך: $code -> {$mapping['display_value']}\n";
        } else {
            // יצירת ערך חדש
            $stmt = $pdo->prepare("INSERT INTO attribute_values (value, display_value, attribute_id) VALUES (?, ?, ?)");
            $stmt->execute([$code, $mapping['display_value'], $mapping['attribute_id']]);
            echo "נוצר ערך חדש: $code -> {$mapping['display_value']}\n";
        }
    }
    
    // שלב 5: עדכון product_attributes
    echo "\n=== מעדכן product_attributes ===\n";
    $stmt = $pdo->prepare("UPDATE product_attributes SET display_name = ? WHERE name = ?");
    $stmt->execute(['צבע', 'color']);
    $stmt->execute(['מידה', 'size']);
    echo "עודכנו תצוגות המאפיינים\n";
    
    // שלב 6: בדיקת תוצאות
    echo "\n=== בדיקת תוצאות ===\n";
    $stmt = $pdo->query("
        SELECT 
            av.id,
            av.value as code,
            av.display_value,
            a.display_name as attribute_name
        FROM attribute_values av
        JOIN attributes a ON av.attribute_id = a.id
        ORDER BY a.name, av.sort_order
    ");
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $row) {
        echo "ID: {$row['id']}, קוד: {$row['code']}, תצוגה: {$row['display_value']}, מאפיין: {$row['attribute_name']}\n";
    }
    
    echo "\n✅ תיקון מערכת המאפיינים הושלם!\n";
    
} catch (Exception $e) {
    echo "שגיאה: " . $e->getMessage() . "\n";
}
?> 