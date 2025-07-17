<?php
/**
 * קונפיגורציית מסד נתונים
 * QuickShop5 - SaaS לחנויות וירטואליות
 */

class Database {
    // הגדרות MAMP לפיתוח
    private const DB_HOST = '127.0.0.1';
    private const DB_PORT = '8889';
    private const DB_NAME = 'quickshop5';
    private const DB_USER = 'root';
    private const DB_PASS = 'root';
    private const DB_CHARSET = 'utf8mb4';
    
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . self::DB_HOST . ";port=" . self::DB_PORT . ";dbname=" . self::DB_NAME . ";charset=" . self::DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->pdo = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
            
        } catch (PDOException $e) {
            die("שגיאת חיבור למסד נתונים: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    // מניעת שכפול
    private function __clone() {}
    public function __wakeup() {}
} 