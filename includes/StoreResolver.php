<?php
class StoreResolver {
    private $db;
    private $currentStore = null;
    private $isSubdomain = false;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->resolveStore();
    }
    
    /**
     * זיהוי החנות הנוכחית לפי הדומיין/סאב-דומיין
     */
    private function resolveStore() {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        
        // בדיקה אם זה סאב-דומיין
        if ($this->isSubdomainRequest($host)) {
            $storeSlug = $this->extractStoreSlug($host);
            $this->currentStore = $this->getStoreBySlug($storeSlug);
            $this->isSubdomain = true;
        } 
        // בדיקה לדומיין מותאם אישית
        else {
            $this->currentStore = $this->getStoreByDomain($host);
            $this->isSubdomain = false;
        }
    }
    
    /**
     * בדיקה אם הבקשה היא לסאב-דומיין
     */
    private function isSubdomainRequest($host) {
        // בסביבת פיתוח
        if (strpos($host, 'localhost') !== false) {
            // store_slug.localhost:8000
            $parts = explode('.', $host);
            return count($parts) >= 2 && $parts[0] !== 'www' && $parts[0] !== 'admin';
        }
        
        // בסביבת ייצור
        // store_slug.quick-shop.co.il
        $mainDomains = ['quick-shop.co.il', 'quickshop.co.il'];
        foreach ($mainDomains as $mainDomain) {
            if (strpos($host, $mainDomain) !== false && $host !== $mainDomain) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * חילוץ slug החנות מהסאב-דומיין
     */
    private function extractStoreSlug($host) {
        // store_slug.localhost:8000 או store_slug.quick-shop.co.il
        $parts = explode('.', $host);
        return $parts[0];
    }
    
    /**
     * קבלת חנות לפי slug
     */
    private function getStoreBySlug($slug) {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as owner_name, 
                       u.email as owner_email
                FROM stores s
                JOIN users u ON s.user_id = u.id
                WHERE s.slug = ? AND s.status = 'active'
            ");
            $stmt->execute([$slug]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Store resolution error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * קבלת חנות לפי דומיין מותאם
     */
    private function getStoreByDomain($domain) {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as owner_name, 
                       u.email as owner_email
                FROM stores s
                JOIN users u ON s.user_id = u.id
                WHERE s.custom_domain = ? AND s.status = 'active'
            ");
            $stmt->execute([$domain]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Store resolution error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * קבלת החנות הנוכחית
     */
    public function getCurrentStore() {
        return $this->currentStore;
    }
    
    /**
     * בדיקה אם זה סאב-דומיין
     */
    public function isSubdomain() {
        return $this->isSubdomain;
    }
    
    /**
     * קבלת URL הבסיס של החנות
     */
    public function getStoreBaseUrl() {
        if (!$this->currentStore) return null;
        
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        
        if ($this->currentStore['custom_domain']) {
            return $scheme . '://' . $this->currentStore['custom_domain'];
        }
        
        // סאב-דומיין
        if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
            // שימוש בפורט הנוכחי במקום פורט קבוע
            $currentPort = $_SERVER['SERVER_PORT'] ?? '8888';
            return $scheme . '://' . $this->currentStore['slug'] . '.localhost:' . $currentPort;
        }
        
        return $scheme . '://' . $this->currentStore['slug'] . '.quick-shop.co.il';
    }
    
    /**
     * בניית URL למוצר בחנות
     */
    public function getProductUrl($productSlug) {
        $baseUrl = $this->getStoreBaseUrl();
        return $baseUrl ? $baseUrl . '/product/' . $productSlug : null;
    }
    
    /**
     * בניית URL לקטגוריה בחנות
     */
    public function getCategoryUrl($categorySlug) {
        $baseUrl = $this->getStoreBaseUrl();
        return $baseUrl ? $baseUrl . '/category/' . $categorySlug : null;
    }
    
    /**
     * בניית URL לדף בחנות
     */
    public function getPageUrl($page = '') {
        $baseUrl = $this->getStoreBaseUrl();
        return $baseUrl ? $baseUrl . ($page ? '/' . $page : '') : null;
    }
    
    /**
     * בדיקה אם הבקשה לדף ניהול
     */
    public function isAdminRequest() {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $path = $_SERVER['REQUEST_URI'] ?? '';
        
        // admin.quick-shop.co.il או localhost/admin
        return (strpos($host, 'admin.') === 0) || (strpos($path, '/admin') === 0);
    }
    
    /**
     * בדיקה אם החנות קיימת ופעילה
     */
    public function isValidStore() {
        return $this->currentStore && $this->currentStore['status'] === 'active';
    }
    
    /**
     * קבלת מידע מלא על החנות
     */
    public function getStoreContext() {
        if (!$this->currentStore) return null;
        
        return [
            'store' => $this->currentStore,
            'is_subdomain' => $this->isSubdomain,
            'base_url' => $this->getStoreBaseUrl(),
            'settings' => $this->getStoreSettings()
        ];
    }
    
    /**
     * קבלת הגדרות החנות
     */
    private function getStoreSettings() {
        if (!$this->currentStore) return [];
        
        try {
            $stmt = $this->db->prepare("
                SELECT setting_key, setting_value 
                FROM store_settings 
                WHERE store_id = ?
            ");
            $stmt->execute([$this->currentStore['id']]);
            
            $settings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
            return $settings;
        } catch (Exception $e) {
            error_log("Store settings error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * בדיקת זמינות slug לחנות
     */
    public static function isSlugAvailable($slug, $excludeStoreId = null) {
        $db = Database::getInstance()->getConnection();
        
        // רשימת slugs שמורים
        $reservedSlugs = ['admin', 'api', 'www', 'mail', 'ftp', 'cdn', 'static', 'assets'];
        
        if (in_array(strtolower($slug), $reservedSlugs)) {
            return false;
        }
        
        // בדיקה במסד הנתונים
        try {
            $sql = "SELECT id FROM stores WHERE slug = ?";
            $params = [$slug];
            
            if ($excludeStoreId) {
                $sql .= " AND id != ?";
                $params[] = $excludeStoreId;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            return !$stmt->fetch();
        } catch (Exception $e) {
            error_log("Slug availability check error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * יצירת slug אוטומטי מזמין
     */
    public static function generateUniqueSlug($baseName, $excludeStoreId = null) {
        $slug = self::sanitizeSlug($baseName);
        $originalSlug = $slug;
        $counter = 1;
        
        while (!self::isSlugAvailable($slug, $excludeStoreId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * ניקוי וסינון slug
     */
    public static function sanitizeSlug($text) {
        // המרה לאנגלית (transliteration)
        $hebrew_to_english = [
            'א' => 'a', 'ב' => 'b', 'ג' => 'g', 'ד' => 'd', 'ה' => 'h', 'ו' => 'v',
            'ז' => 'z', 'ח' => 'ch', 'ט' => 't', 'י' => 'i', 'כ' => 'k', 'ל' => 'l',
            'מ' => 'm', 'ן' => 'n', 'נ' => 'n', 'ס' => 's', 'ע' => 'e', 'פ' => 'p',
            'צ' => 'tz', 'ק' => 'k', 'ר' => 'r', 'ש' => 'sh', 'ת' => 't',
            'ך' => 'k', 'ם' => 'm', 'ף' => 'f', 'ץ' => 'tz'
        ];
        
        $slug = strtr($text, $hebrew_to_english);
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return $slug ?: 'store';
    }
}
?> 