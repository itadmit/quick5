<?php
// קובץ הכניסה הראשי לכל החנויות
session_start();

// הכללת קבצים הכרחיים
require_once 'config/database.php';
require_once 'includes/Router.php';

try {
    // יצירת router ושליחת הבקשה לטיפול
    $router = new Router();
    $router->route();
    
} catch (Exception $e) {
    // לוג השגיאה
    error_log("Router Error: " . $e->getMessage());
    
    // הצגת שגיאה למשתמש
    http_response_code(500);
    
    $errorMessage = 'שגיאה בטעינת הדף';
    if (isset($_GET['debug']) && $_GET['debug'] === '1') {
        $errorMessage .= ': ' . $e->getMessage();
    }
    
    echo "<!DOCTYPE html>
    <html dir='rtl' lang='he'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>שגיאה - QuickShop</title>
        <script src='https://cdn.tailwindcss.com'></script>
    </head>
    <body class='bg-gray-100'>
        <div class='min-h-screen flex items-center justify-center'>
            <div class='text-center'>
                <h1 class='text-6xl font-bold text-red-400 mb-4'>500</h1>
                <h2 class='text-2xl font-semibold text-gray-700 mb-4'>{$errorMessage}</h2>
                <p class='text-gray-600 mb-8'>משהו השתבש בטעינת הדף</p>
                <a href='/' class='bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors'>
                    נסה שוב
                </a>
            </div>
        </div>
    </body>
    </html>";
}
?>