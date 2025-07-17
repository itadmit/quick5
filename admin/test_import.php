<?php
// בדיקה פשוטה של טעינת הקבצים
echo "<h1>בדיקת עמוד הייבוא</h1>";

try {
    require_once '../includes/auth.php';
    echo "✅ auth.php נטען בהצלחה<br>";
    
    require_once '../config/database.php';
    echo "✅ database.php נטען בהצלחה<br>";
    
    require_once '../includes/CsvImporter.php';
    echo "✅ CsvImporter.php נטען בהצלחה<br>";
    
    require_once '../includes/ImageUploader.php';
    echo "✅ ImageUploader.php נטען בהצלחה<br>";
    
    // בדיקת מסד נתונים
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    echo "✅ חיבור למסד הנתונים תקין<br>";
    
    // בדיקת טבלת import_jobs
    $stmt = $pdo->query("SHOW TABLES LIKE 'import_jobs'");
    if ($stmt->fetch()) {
        echo "✅ טבלת import_jobs קיימת<br>";
    } else {
        echo "❌ טבלת import_jobs לא קיימת<br>";
    }
    
    echo "<br><strong>הכל תקין! ניתן לגשת ל:</strong><br>";
    echo "<a href='import/'>עמוד ייבוא מוצרים</a>";
    
} catch (Exception $e) {
    echo "❌ שגיאה: " . $e->getMessage();
}
?> 