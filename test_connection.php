<?php
require_once 'includes/config.php';

echo "<h1>בדיקת חיבור מסד נתונים</h1>";

try {
    // Test basic connection without database
    $pdo_test = new PDO('mysql:host=localhost:8889', 'root', 'root');
    echo "<p style='color: green;'>✅ חיבור לשרת MySQL הצליח</p>";
    
    // Check if database exists
    $stmt = $pdo_test->query("SHOW DATABASES LIKE 'quickshop5'");
    $result = $stmt->fetch();
    
    if ($result) {
        echo "<p style='color: green;'>✅ מסד הנתונים quickshop5 קיים</p>";
        
        // Test connection to specific database
        $pdo = getDB();
        echo "<p style='color: green;'>✅ חיבור למסד הנתונים quickshop5 הצליח</p>";
        
        // Check tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "<p style='color: green;'>✅ נמצאו " . count($tables) . " טבלאות במסד הנתונים</p>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠️ מסד הנתונים קיים אבל ריק (אין טבלאות)</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ מסד הנתונים quickshop5 לא קיים</p>";
        echo "<p>אני יוצר את מסד הנתונים...</p>";
        
        $pdo_test->exec("CREATE DATABASE quickshop5 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p style='color: green;'>✅ מסד הנתונים quickshop5 נוצר בהצלחה</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ שגיאה: " . $e->getMessage() . "</p>";
    
    // Additional debug info
    echo "<h3>מידע לדיבוג:</h3>";
    echo "<p>Host: localhost:8889</p>";
    echo "<p>User: root</p>";
    echo "<p>Password: root</p>";
    echo "<p>Database: quickshop5</p>";
    
    echo "<h3>נסה את ההגדרות הבאות:</h3>";
    echo "<ul>";
    echo "<li>localhost:3306 (פורט רגיל)</li>";
    echo "<li>127.0.0.1:8889</li>";
    echo "<li>localhost ללא פורט</li>";
    echo "</ul>";
}
?> 