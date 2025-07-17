<?php
require_once 'config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "בודק סטטוס התמונות שהורדו:\n\n";
    
    // בדיקת תמונות במסד הנתונים
    echo "=== תמונות במסד הנתונים ===\n";
    $stmt = $pdo->query("
        SELECT 
            pm.id,
            pm.product_id,
            pm.type,
            pm.url,
            pm.thumbnail_url,
            pm.alt_text,
            pm.is_primary,
            pm.file_size,
            pm.dimensions,
            p.sku as product_sku,
            p.name as product_name
        FROM product_media pm
        LEFT JOIN products p ON pm.product_id = p.id
        WHERE p.sku = '1230527050' OR p.id = 27
        ORDER BY pm.product_id, pm.is_primary DESC, pm.id
    ");
    
    $media = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "מספר תמונות שנמצאו: " . count($media) . "\n\n";
    
    foreach ($media as $img) {
        echo "ID: {$img['id']}\n";
        echo "מוצר: {$img['product_name']} (SKU: {$img['product_sku']}, ID: {$img['product_id']})\n";
        echo "סוג: {$img['type']}\n";
        echo "URL: {$img['url']}\n";
        echo "Thumbnail: {$img['thumbnail_url']}\n";
        echo "Alt text: {$img['alt_text']}\n";
        echo "ראשי: " . ($img['is_primary'] ? 'כן' : 'לא') . "\n";
        echo "גודל: " . ($img['file_size'] ? number_format($img['file_size'] / 1024, 1) . ' KB' : 'לא ידוע') . "\n";
        echo "מימדים: " . ($img['dimensions'] ?: 'לא ידוע') . "\n";
        
        // בדיקת קיום הקובץ בשרת
        if ($img['url']) {
            $file_path = '.' . $img['url']; // הסרת http://localhost:8000
            if (strpos($img['url'], 'http') === 0) {
                $file_path = './uploads/' . basename($img['url']);
            }
            $file_exists = file_exists($file_path);
            echo "קובץ קיים בשרת: " . ($file_exists ? '✅' : '❌') . " ($file_path)\n";
            if ($file_exists) {
                $actual_size = filesize($file_path);
                echo "גודל קובץ בפועל: " . number_format($actual_size / 1024, 1) . " KB\n";
            }
        }
        echo "---\n";
    }
    
    // בדיקת תיקיית התמונות
    echo "\n=== בדיקת תיקיות תמונות ===\n";
    $upload_dirs = [
        './uploads/stores/yogev/products/',
        './uploads/stores/yogev/attribute-media/',
        './uploads/stores/store-1/products/',
        './uploads/products/'
    ];
    
    foreach ($upload_dirs as $dir) {
        if (is_dir($dir)) {
            $files = glob($dir . '*');
            echo "תיקיה: $dir\n";
            echo "מספר קבצים: " . count($files) . "\n";
            
            // הצגת כמה קבצים ראשונים
            $recent_files = array_slice($files, -5);
            foreach ($recent_files as $file) {
                if (is_file($file)) {
                    $filename = basename($file);
                    $size = filesize($file);
                    $date = date('Y-m-d H:i:s', filemtime($file));
                    echo "  $filename (" . number_format($size/1024, 1) . " KB, $date)\n";
                }
            }
            echo "\n";
        } else {
            echo "תיקיה לא קיימת: $dir\n";
        }
    }
    
    // בדיקת המוצר הראשי
    echo "\n=== פרטי המוצר הראשי ===\n";
    $stmt = $pdo->query("SELECT * FROM products WHERE sku = '1230527050' OR id = 27");
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        echo "מוצר: {$product['name']} (SKU: {$product['sku']})\n";
        echo "סוג: {$product['type']}\n";
        echo "סטטוס: {$product['status']}\n";
        echo "יש וריאציות: " . ($product['has_variants'] ? 'כן' : 'לא') . "\n";
        
        // ספירת וריאציות
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_variants WHERE product_id = ?");
        $stmt->execute([$product['id']]);
        $variant_count = $stmt->fetchColumn();
        echo "מספר וריאציות: $variant_count\n";
    }
    
} catch (Exception $e) {
    echo "שגיאה: " . $e->getMessage() . "\n";
}
?> 