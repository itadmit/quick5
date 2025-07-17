<?php
/**
 * מחלקת Authentication
 * ניהול הרשמה, התחברות ואבטחה
 */

require_once __DIR__ . '/../config/database.php';

class Authentication {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * הפעלת session בטוחה
     */
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * הרשמת משתמש חדש
     */
    public function register($email, $password, $firstName, $lastName, $phone = null) {
        try {
            // בדיקה שהמייל לא קיים
            if ($this->userExists($email)) {
                return ['success' => false, 'message' => 'כתובת המייל כבר קיימת במערכת'];
            }
            
            // הצפנת סיסמה
            $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
            
            // תאריך סיום תקופת ניסיון (14 יום)
            $trialEndsAt = date('Y-m-d H:i:s', strtotime('+14 days'));
            
            $stmt = $this->db->prepare("
                INSERT INTO users (email, password, first_name, last_name, phone, trial_ends_at) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$email, $hashedPassword, $firstName, $lastName, $phone, $trialEndsAt]);
            
            $userId = $this->db->lastInsertId();
            
            return [
                'success' => true, 
                'message' => 'ההרשמה הושלמה בהצלחה! תקופת הניסיון שלך מתחילה עכשיו',
                'user_id' => $userId
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'שגיאה ביצירת המשתמש: ' . $e->getMessage()];
        }
    }
    
    /**
     * התחברות למערכת
     */
    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, email, password, first_name, last_name, status, subscription_status, trial_ends_at 
                FROM users 
                WHERE email = ? AND status != 'cancelled'
            ");
            
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'כתובת מייל או סיסמה שגויים'];
            }
            
            // בדיקת סטטוס חשבון
            if ($user['status'] === 'suspended') {
                return ['success' => false, 'message' => 'החשבון שלך מושעה זמנית'];
            }
            
            // בדיקת תקופת ניסיון
            $trialExpired = strtotime($user['trial_ends_at']) < time();
            if ($user['subscription_status'] === 'trial' && $trialExpired) {
                return ['success' => false, 'message' => 'תקופת הניסיון הסתיימה. נדרש תשלום להמשך השימוש'];
            }
            
            // יצירת session
            $this->startSession();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['subscription_status'] = $user['subscription_status'];
            
            return [
                'success' => true, 
                'message' => 'התחברת בהצלחה!',
                'redirect' => '/admin/'
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'שגיאה בהתחברות: ' . $e->getMessage()];
        }
    }
    
    /**
     * יציאה מהמערכת
     */
    public function logout() {
        $this->startSession();
        session_destroy();
        return ['success' => true, 'message' => 'יצאת מהמערכת בהצלחה'];
    }
    
    /**
     * בדיקה אם משתמש מחובר
     */
    public function isLoggedIn() {
        $this->startSession();
        return isset($_SESSION['user_id']);
    }
    
    /**
     * קבלת פרטי משתמש מחובר
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $stmt = $this->db->prepare("
            SELECT id, email, first_name, last_name, phone, status, subscription_status, 
                   trial_ends_at, created_at 
            FROM users 
            WHERE id = ?
        ");
        
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    
    /**
     * בדיקה אם מייל קיים
     */
    private function userExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * הגנה על עמודים - העברה להתחברות אם לא מחובר
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            // מניעת redirect loop
            $currentPath = $_SERVER['REQUEST_URI'] ?? '';
            if (strpos($currentPath, '/admin/login.php') === false) {
                header('Location: /admin/login.php');
                exit;
            }
        }
    }
    
    /**
     * יצירת חנות ברירת מחדל למשתמש חדש
     */
    public function createDefaultStore($userId, $storeName, $storeSlug = null) {
        try {
            $slug = $storeSlug ?: $this->generateUniqueDomain($storeName);
            
            $stmt = $this->db->prepare("
                INSERT INTO stores (user_id, name, slug, description) 
                VALUES (?, ?, ?, ?)
            ");
            
            $description = "החנות המקוונת שלי - נבנתה עם QuickShop5";
            
            $stmt->execute([$userId, $storeName, $slug, $description]);
            
            return [
                'success' => true,
                'store_id' => $this->db->lastInsertId(),
                'slug' => $slug
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'שגיאה ביצירת חנות: ' . $e->getMessage()];
        }
    }
    
    /**
     * יצירת slug ייחודי לחנות
     */
    private function generateUniqueDomain($storeName) {
        $baseSlug = $this->slugify($storeName);
        $slug = $baseSlug;
        $counter = 1;
        
        while ($this->domainExists($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * המרת טקסט לslug
     */
    private function slugify($text) {
        // החלפת עברית ותווים מיוחדים
        $text = trim($text);
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text);
        $text = trim($text, '-');
        
        return empty($text) ? 'store' : $text;
    }
    
    /**
     * בדיקה אם דומיין קיים
     */
    private function domainExists($slug) {
        $stmt = $this->db->prepare("SELECT id FROM stores WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch() !== false;
    }
}

// פונקציות עוזר גלובליות
$auth = new Authentication();

/**
 * בדיקה אם משתמש מחובר
 */
function isLoggedIn() {
    global $auth;
    return $auth->isLoggedIn();
}

/**
 * קבלת פרטי משתמש נוכחי
 */
function getCurrentUser() {
    global $auth;
    return $auth->getCurrentUser();
}

/**
 * דרישת הרשאה - הפניה לדף התחברות אם המשתמש לא מחובר
 */
function requireAuth() {
    if (!isLoggedIn()) {
        // הפניה לדף התחברות
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
        $loginUrl = '/admin/login.php';
        if (!empty($currentUrl)) {
            $loginUrl .= '?redirect=' . urlencode($currentUrl);
        }
        header('Location: ' . $loginUrl);
        exit;
    }
}

/**
 * בדיקת הרשאות משתמש
 */
function hasRole($roles = []) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    
    // במערכת זו, כל משתמש רשום הוא admin או store_manager
    // ניתן להרחיב את זה עם טבלת roles בעתיד
    return in_array('admin', $roles) || in_array('store_manager', $roles);
}

/**
 * הגנה על דפים שדורשים התחברות
 */
function requireLogin() {
    global $auth;
    $auth->requireLogin();
}

/**
 * בדיקה אם המשתמש הוא מנהל
 */
function isAdmin() {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    
    // במערכת זו, כל משתמש רשום הוא מנהל החנות שלו
    // ניתן להרחיב את זה עם הרשאות מפורטות יותר בעתיד
    return $user['status'] === 'active';
}

/**
 * בדיקת הרשאות ספציפיות
 */
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    
    // במערכת זו, כל משתמש רשום הוא מנהל החנות שלו ויש לו את כל הרשאות
    // ניתן להרחיב את זה עם מערכת הרשאות מפורטת יותר בעתיד
    $allowedPermissions = [
        'manage_themes',
        'customize_theme', 
        'edit_theme_code',
        'manage_products',
        'manage_orders',
        'manage_users',
        'view_analytics',
        'manage_settings'
    ];
    
    return in_array($permission, $allowedPermissions);
}

/**
 * קבלת פרטי החנות הנוכחית
 */
function getCurrentStore() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $user = getCurrentUser();
    if (!$user) {
        return null;
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM stores WHERE user_id = ? LIMIT 1");
        $stmt->execute([$user['id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
} 