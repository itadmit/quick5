<?php
/**
 * סקריפט לניקוי כל התמונות והנתונים מהטסטים - QuickShop5
 */

require_once __DIR__ . '/config/database.php';

echo "=== סקריפט ניקוי תמונות טסט ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // סטטיסטיקות לפני הניקוי
    echo "📊 סטטיסטיקות לפני הניקוי:\n";
    echo "-----------------------------\n";
    
    // ספירת תמונות במסד הנתונים
    $stmt = $db->query("SELECT COUNT(*) as count FROM product_media");
    $dbImages = $stmt->fetch()['count'];
    echo "🗄️  תמונות במסד הנתונים: " . number_format($dbImages) . "\n";
    
    // ספירת מוצרים
    $stmt = $db->query("SELECT COUNT(*) as count FROM products");
    $dbProducts = $stmt->fetch()['count'];
    echo "📦 מוצרים במסד הנתונים: " . number_format($dbProducts) . "\n";
    
    // ספירת וריאציות
    $stmt = $db->query("SELECT COUNT(*) as count FROM product_variants");
    $dbVariants = $stmt->fetch()['count'];
    echo "🔀 וריאציות במסד הנתונים: " . number_format($dbVariants) . "\n";
    
    // ספירת קבצים בתיקיות
    $uploadsDir = __DIR__ . '/uploads/stores';
    $imageCount = 0;
    $totalSize = 0;
    
    if (is_dir($uploadsDir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($uploadsDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'])) {
                    $imageCount++;
                    $totalSize += $file->getSize();
                }
            }
        }
    }
    
    echo "📁 קבצי תמונות בשרת: " . number_format($imageCount) . "\n";
    echo "💾 נפח כולל: " . formatBytes($totalSize) . "\n\n";
    
    // בדיקת הזמנות
    $stmt = $db->query("SELECT COUNT(*) as count FROM orders");
    $dbOrders = $stmt->fetch()['count'];
    
    if ($dbOrders > 0) {
        echo "🚨 זוהו " . number_format($dbOrders) . " הזמנות במערכת!\n\n";
    }
    
    // אישור מהמשתמש
    echo "⚠️  אזהרה: פעולה זו תמחק את כל הנתונים הבאים:\n";
    echo "   • כל התמונות בתיקיית uploads/stores/\n";
    echo "   • כל הרשומות בטבלת product_media\n";
    echo "   • כל המוצרים והוריאציות שלהם\n";
    echo "   • כל הקטגוריות והמאפיינים\n";
    if ($dbOrders > 0) {
        echo "   • כל " . number_format($dbOrders) . " ההזמנות\n";
    }
    echo "\n";
    
    echo "האם אתה בטוח שברצונך להמשיך? (y/N): ";
    $confirmation = trim(fgets(STDIN));
    
    if (strtolower($confirmation) !== 'y') {
        echo "❌ פעולה בוטלה.\n";
        exit;
    }
    
    // אם יש הזמנות, נשאל שאלה נוספת
    if ($dbOrders > 0) {
        echo "\n🔥 אזהרה נוספת: זה ימחק גם את כל ההזמנות!\n";
        echo "האם אתה בטוח שברצונך למחוק גם את ההזמנות? (y/N): ";
        $orderConfirmation = trim(fgets(STDIN));
        
        if (strtolower($orderConfirmation) !== 'y') {
            echo "❌ פעולה בוטלה.\n";
            exit;
        }
    }
    
    echo "\n🧹 מתחיל ניקוי...\n";
    echo "========================\n";
    
    // התחלת טרנזקציה
    $db->beginTransaction();
    
    // 1. מחיקת נתונים ממסד הנתונים
    echo "1️⃣  מנקה מסד נתונים...\n";
    
    $tables = [
        'product_media' => 'תמונות מוצרים',
        'product_categories' => 'קישורי קטגוריות',
        'product_attributes' => 'מאפיינים',
        'product_variants' => 'וריאציות',
        'attribute_values' => 'ערכי מאפיינים',
        'order_items' => 'פריטי הזמנות',
        'orders' => 'הזמנות',
        'products' => 'מוצרים',
        'categories' => 'קטגוריות',
        'import_jobs' => 'משימות ייבוא'
    ];
    
    $hasErrors = false;
    
    foreach ($tables as $table => $description) {
        try {
            // בדיקה אם הטבלה קיימת
            $stmt = $db->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() == 0) {
                echo "   ⏭️  $description: טבלה לא קיימת, מדלג\n";
                continue;
            }
            
            $stmt = $db->query("DELETE FROM $table");
            $deleted = $stmt->rowCount();
            echo "   ✅ $description: " . number_format($deleted) . " רשומות נמחקו\n";
        } catch (Exception $e) {
            echo "   ⚠️  שגיאה במחיקת $description: " . $e->getMessage() . "\n";
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                echo "      💡 מדלג על טבלה בגלל קישורי מפתח זר\n";
            }
            $hasErrors = true;
        }
    }
    
    // איפוס AUTO_INCREMENT
    echo "   🔄 מאפס מונים...\n";
    foreach (array_keys($tables) as $table) {
        try {
            $db->query("ALTER TABLE $table AUTO_INCREMENT = 1");
        } catch (Exception $e) {
            // לא קריטי אם נכשל
        }
    }
    
    // 2. מחיקת קבצים פיזיים
    echo "\n2️⃣  מנקה קבצים...\n";
    
    $deletedFiles = 0;
    $deletedSize = 0;
    
    if (is_dir($uploadsDir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($uploadsDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size = $file->getSize();
                if (unlink($file->getRealPath())) {
                    $deletedFiles++;
                    $deletedSize += $size;
                }
            } elseif ($file->isDir()) {
                // מחיקת תיקיות ריקות
                @rmdir($file->getRealPath());
            }
        }
        
        // מחיקת תיקיות החנויות עצמן
        $storesDirs = glob($uploadsDir . '/*', GLOB_ONLYDIR);
        foreach ($storesDirs as $storeDir) {
            @rmdir($storeDir);
        }
    }
    
    echo "   ✅ נמחקו " . number_format($deletedFiles) . " קבצים (" . formatBytes($deletedSize) . ")\n";
    
    // 3. ניקוי תיקיות ייבוא
    echo "\n3️⃣  מנקה תיקיות ייבוא...\n";
    $importsDir = __DIR__ . '/uploads/imports';
    if (is_dir($importsDir)) {
        $importFiles = glob($importsDir . '/*');
        $importDeleted = 0;
        foreach ($importFiles as $file) {
            if (is_file($file) && unlink($file)) {
                $importDeleted++;
            }
        }
        echo "   ✅ נמחקו " . number_format($importDeleted) . " קבצי ייבוא\n";
    }
    
    // אישור או ביטול טרנזקציה
    if ($hasErrors) {
        echo "\n⚠️  היו שגיאות במחיקת מסד הנתונים, מבטל שינויים...\n";
        $db->rollBack();
        echo "   📁 קבצים נמחקו: " . number_format($deletedFiles) . " (" . formatBytes($deletedSize) . ")\n";
        echo "   🗄️  מסד נתונים: שינויים בוטלו\n\n";
        echo "💡 ניקוי חלקי הושלם - הקבצים נמחקו אבל מסד הנתונים לא שונה\n";
    } else {
        $db->commit();
        
        echo "\n✅ ניקוי הושלם בהצלחה!\n";
        echo "========================\n";
        echo "📊 סיכום:\n";
        echo "   • " . number_format($dbImages) . " תמונות נמחקו ממסד הנתונים\n";
        echo "   • " . number_format($deletedFiles) . " קבצים נמחקו מהשרת\n";
        echo "   • " . formatBytes($deletedSize) . " שטח פונה\n";
        echo "   • " . number_format($dbProducts) . " מוצרים נמחקו\n";
        echo "   • " . number_format($dbVariants) . " וריאציות נמחקו\n\n";
        
        echo "🎉 המערכת נקייה ומוכנה לשימוש מחדש!\n";
    }

} catch (Exception $e) {
    // ביטול טרנזקציה במקרה של שגיאה
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    echo "❌ שגיאה: " . $e->getMessage() . "\n";
    echo "💡 הניקוי לא הושלם. בדוק את השגיאה ונסה שוב.\n";
    exit(1);
}

/**
 * המרת בייטים לפורמט קריא
 */
function formatBytes($size, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    
    return round($size, $precision) . ' ' . $units[$i];
}

?> 