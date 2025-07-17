<?php
// Database configuration for MAMP
define('DB_HOST', '127.0.0.1');  // Use IP instead of localhost
define('DB_PORT', '8889');        // MAMP default port
define('DB_NAME', 'quickshop5');
define('DB_USER', 'root');
define('DB_PASS', 'root');  // MAMP default password
define('DB_CHARSET', 'utf8mb4');

// PDO DSN - use IP and port explicitly to avoid socket issues
define('DB_DSN', 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET);

// Application settings - זיהוי דינמי של הפורט
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';

// אם זה localhost, וודא שאנחנו משתמשים בפורט הנכון
if (strpos($host, 'localhost') !== false) {
    // אם לא מצוין פורט ב-HTTP_HOST, נסה לגלות מ-SERVER_PORT
    if (!strpos($host, ':')) {
        $port = $_SERVER['SERVER_PORT'] ?? '8000';
        $host = 'localhost:' . $port;
    }
}

define('APP_URL', $scheme . '://' . $host);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');

// Security settings
define('SECRET_KEY', 'your_secret_key_here_change_in_production');
define('HASH_ALGO', 'sha256');

// Database connection function
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci"
            ];
            
            $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            throw new Exception('Connection failed: ' . $e->getMessage());
        }
    }
    
    return $pdo;
}

// Test database connection
function testConnection() {
    try {
        $pdo = getDB();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?> 