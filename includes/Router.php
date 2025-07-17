<?php
require_once 'StoreResolver.php';
require_once 'ProductManager.php';
require_once 'ThemeManager.php';

class Router {
    private $storeResolver;
    private $store;
    private $themeManager;
    private $routes = [];
    
    public function __construct() {
        $this->storeResolver = new StoreResolver();
        $this->store = $this->storeResolver->getCurrentStore();
        $this->themeManager = new ThemeManager($this->store['id'] ?? null);
        $this->setupRoutes();
    }
    
    /**
     * הגדרת נתיבים
     */
    private function setupRoutes() {
        $this->routes = [
            // דף הבית של החנות
            '' => 'handleHomePage',
            '/' => 'handleHomePage',
            
            // דף מוצר
            '/product/([^/]+)' => 'handleProductPage',
            
            // דף קטגוריה  
            '/category/([^/]+)' => 'handleCategoryPage',
            
            // דפי מערכת
            '/cart' => 'handleCartPage',
            '/checkout' => 'handleCheckoutPage',
            '/search' => 'handleSearchPage',
            
            // בילדר דפים
            '/builder' => 'handleBuilderPage',
            '/builder/' => 'handleBuilderPage',
            
            // API endpoints
            '/api/cart' => 'handleCartAPI',
            '/api/product/([^/]+)' => 'handleProductAPI',
            
            // דפים סטטיים
            '/about' => 'handleAboutPage',
            '/contact' => 'handleContactPage',
            '/terms' => 'handleTermsPage',
            '/privacy' => 'handlePrivacyPage',
        ];
    }
    
        /**
     * ניתוב הבקשה
     */
    public function route() {
        // בדיקה אם החנות קיימת
        if (!$this->storeResolver->isValidStore()) {
            $this->handle404('החנות לא נמצאה או לא פעילה');
            return;
        }

        // בדיקה אם זה בקשה לניהול
        if ($this->storeResolver->isAdminRequest()) {
            $this->redirectToAdmin();
            return;
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $requestUri = strtok($requestUri, '?'); // הסרת query parameters
        $requestUri = rtrim($requestUri, '/'); // הסרת slash בסוף
        
        // חיפוש נתיב מתאים
        foreach ($this->routes as $pattern => $handler) {
            if ($this->matchRoute($pattern, $requestUri)) {
                $this->$handler();
                return;
            }
        }
        
        // אם לא נמצא נתיב - 404
        $this->handle404();
    }
    
    /**
     * בדיקת התאמת נתיב
     */
    private function matchRoute($pattern, $uri) {
        // המרה לregex
        $regex = str_replace('/', '\/', $pattern);
        $regex = '/^' . $regex . '$/';
        
        if (preg_match($regex, $uri, $matches)) {
            // שמירת פרמטרים שנלכדו
            $_GET['route_params'] = array_slice($matches, 1);
            return true;
        }
        
        return false;
    }
    
    /**
     * דף הבית של החנות
     */
    private function handleHomePage() {
        $this->setStoreContext();
        
        // בדיקה אם זה מצב תצוגה מקדימה
        $isPreview = isset($_GET['preview']) && $_GET['preview'] == '1';
        $isBuilderPreview = isset($_GET['builder_preview']) && $_GET['builder_preview'] == '1';
        
        // אם זה תצוגה מקדימה של הבילדר
        if ($isBuilderPreview) {
            $this->handleBuilderPreview();
            return;
        }
        
        // שימוש במערכת הבילדר לדף הבית
        require_once __DIR__ . '/../builder/BuilderRenderer.php';
        $builderRenderer = new BuilderRenderer($this->store['id'] ?? null);
        
        // רנדור דף הבית שנבנה
        echo $builderRenderer->renderHomePage();
    }
    
    /**
     * תצוגה מקדימה של הבילדר
     */
    private function handleBuilderPreview() {
        // הגדרת הקשר מיוחד לבילדר
        $GLOBALS['CURRENT_STORE'] = $this->store;
        $GLOBALS['STORE_CONTEXT'] = $this->storeResolver->getStoreContext();
        $GLOBALS['STORE_RESOLVER'] = $this->storeResolver;
        $GLOBALS['IS_BUILDER_PREVIEW'] = true;
        
        // טעינת מנהל הבילדר
        require_once __DIR__ . '/../builder/BuilderRenderer.php';
        $builderRenderer = new BuilderRenderer($this->store['id'] ?? null);
        
        // רנדור דף הבית עם הבילדר - זה מה שצריך להיות ב-iframe
        echo $builderRenderer->renderHomePage();
    }
    
    /**
     * דף מוצר
     */
    private function handleProductPage() {
        $productSlug = $_GET['route_params'][0] ?? '';
        
        if (!$productSlug) {
            $this->handle404('מוצר לא נמצא');
            return;
        }
        
        $productManager = new ProductManager();
        $product = $productManager->getProductBySlug($productSlug);
        
        if (!$product || $product['store_id'] != $this->store['id']) {
            $this->handle404('מוצר לא נמצא');
            return;
        }
        
        // הגדרת המוצר בגלובלים
        $GLOBALS['CURRENT_PRODUCT'] = $product;
        
        $this->setStoreContext();
        
        // רנדור עם מערכת התבניות החדשה
        $pageData = [
            'title' => $product['name'] . ' - ' . $this->store['name'],
            'description' => $product['short_description'] ?? $product['description'] ?? '',
            'template' => 'product',
            'body_class' => 'product-page'
        ];
        
        $this->themeManager->renderPage('product', $pageData);
    }
    
    /**
     * דף קטגוריה
     */
    private function handleCategoryPage() {
        $categorySlug = $_GET['route_params'][0] ?? '';
        
        if (!$categorySlug || $categorySlug === 'all') {
            // כל המוצרים - לא צריך קטגוריה ספציפית
            $GLOBALS['CURRENT_CATEGORY'] = null;
        } else {
            // חיפוש קטגוריה ספציפית
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("
                    SELECT * FROM categories 
                    WHERE slug = ? AND store_id = ?
                    LIMIT 1
                ");
                $stmt->execute([$categorySlug, $this->store['id']]);
                $category = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$category) {
                    $this->handle404('קטגוריה לא נמצאה');
                    return;
                }
                
                $GLOBALS['CURRENT_CATEGORY'] = $category;
            } catch (Exception $e) {
                error_log("Error loading category: " . $e->getMessage());
                $this->handle404('שגיאה בטעינת הקטגוריה');
                return;
            }
        }
        
        $this->setStoreContext();
        
        // רנדור עם מערכת התבניות החדשה
        $category = $GLOBALS['CURRENT_CATEGORY'] ?? null;
        $pageData = [
            'title' => $category ? $category['name'] . ' - ' . $this->store['name'] : 'כל המוצרים - ' . $this->store['name'],
            'description' => $category['description'] ?? 'צפה בכל המוצרים שלנו',
            'template' => 'category',
            'body_class' => 'category-page'
        ];
        
        $this->themeManager->renderPage('category', $pageData);
    }
    
    /**
     * דף עגלת קניות
     */
    private function handleCartPage() {
        $this->setStoreContext();
        include $this->getStoreFrontFile('cart.php');
    }
    
    /**
     * דף תשלום
     */
    private function handleCheckoutPage() {
        $this->setStoreContext();
        include $this->getStoreFrontFile('checkout.php');
    }
    
    /**
     * דף חיפוש
     */
    private function handleSearchPage() {
        $this->setStoreContext();
        include $this->getStoreFrontFile('search.php');
    }
    
    /**
     * API עגלת קניות
     */
    private function handleCartAPI() {
        // הפניה לAPI הקיים עם context של החנות
        if ($this->store) {
            $_SESSION['current_store_id'] = $this->store['id'];
        }
        include $this->getApiFile('cart.php');
    }
    
    /**
     * API מוצר
     */
    private function handleProductAPI() {
        $productSlug = $_GET['route_params'][0] ?? '';
        if ($this->store) {
            $_SESSION['current_store_id'] = $this->store['id'];
        }
        $_GET['slug'] = $productSlug;
        include $this->getApiFile('product.php');
    }
    
    /**
     * דף הבילדר
     */
    private function handleBuilderPage() {
        // בדיקת הרשאות (אם נדרש)
        // if (!isset($_SESSION['admin_logged_in'])) {
        //     header('Location: /admin/login.php');
        //     exit;
        // }
        
        // הפניה לדף הבילדר
        include __DIR__ . '/../builder/index.php';
    }
    
    /**
     * דף אודות
     */
    private function handleAboutPage() {
        $this->setStoreContext();
        include $this->getStoreFrontFile('about.php');
    }
    
    /**
     * דף צור קשר
     */
    private function handleContactPage() {
        $this->setStoreContext();
        include $this->getStoreFrontFile('contact.php');
    }
    
    /**
     * תנאי שימוש
     */
    private function handleTermsPage() {
        $this->setStoreContext();
        include $this->getStoreFrontFile('terms.php');
    }
    
    /**
     * מדיניות פרטיות
     */
    private function handlePrivacyPage() {
        $this->setStoreContext();
        include $this->getStoreFrontFile('privacy.php');
    }
    
    /**
     * הגדרת קונטקסט החנות
     */
    private function setStoreContext() {
        // הגדרת משתנים גלובליים לחנות
        $GLOBALS['CURRENT_STORE'] = $this->store;
        $GLOBALS['STORE_CONTEXT'] = $this->storeResolver->getStoreContext();
        $GLOBALS['STORE_RESOLVER'] = $this->storeResolver;
        
        // הגדרת session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if ($this->store) {
            $_SESSION['current_store_id'] = $this->store['id'];
        }
    }
    
    /**
     * קבלת נתיב קובץ לחזית החנות
     */
    private function getStoreFrontFile($filename) {
        // בדיקה אם יש theme מותאם לחנות
        if ($this->store) {
            $storeThemePath = __DIR__ . "/../storefront/themes/{$this->store['slug']}/{$filename}";
            if (file_exists($storeThemePath)) {
                return $storeThemePath;
            }
        }
        
        // Theme ברירת מחדל
        $defaultThemePath = __DIR__ . "/../storefront/default/{$filename}";
        if (file_exists($defaultThemePath)) {
            return $defaultThemePath;
        }
        
        // קובץ ישן במערכת (backward compatibility)
        $legacyPath = __DIR__ . "/../{$filename}";
        if (file_exists($legacyPath)) {
            return $legacyPath;
        }
        
        throw new Exception("Template file not found: {$filename}");
    }
    
    /**
     * קבלת נתיב קובץ API
     */
    private function getApiFile($filename) {
        $apiPath = __DIR__ . "/../api/{$filename}";
        if (file_exists($apiPath)) {
            return $apiPath;
        }
        
        throw new Exception("API file not found: {$filename}");
    }
    
    /**
     * טיפול ב-404
     */
    private function handle404($message = 'הדף לא נמצא') {
        http_response_code(404);
        $this->setStoreContext();
        
        $errorPage = $this->getStoreFrontFile('404.php');
        if (file_exists($errorPage)) {
            include $errorPage;
        } else {
            $this->renderDefault404($message);
        }
    }
    
    /**
     * רנדור 404 ברירת מחדל
     */
    private function renderDefault404($message) {
        $storeName = $this->store ? $this->store['name'] : 'החנות';
        
        echo "<!DOCTYPE html>
        <html dir='rtl' lang='he'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>דף לא נמצא - {$storeName}</title>
            <script src='https://cdn.tailwindcss.com'></script>
        </head>
        <body class='bg-gray-100'>
            <div class='min-h-screen flex items-center justify-center'>
                <div class='text-center'>
                    <h1 class='text-6xl font-bold text-gray-400 mb-4'>404</h1>
                    <h2 class='text-2xl font-semibold text-gray-700 mb-4'>{$message}</h2>
                    <p class='text-gray-600 mb-8'>הדף שחיפשת לא נמצא ב{$storeName}</p>
                    <a href='/' class='bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors'>
                        חזור לדף הבית
                    </a>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * הפניה לניהול
     */
    private function redirectToAdmin() {
        $adminUrl = 'http://localhost:8000/admin';
        
        if (strpos($_SERVER['HTTP_HOST'], 'quick-shop.co.il') !== false) {
            $adminUrl = 'https://admin.quick-shop.co.il';
        }
        
        header("Location: {$adminUrl}");
        exit;
    }
    
    /**
     * קבלת מידע חנות נוכחית
     */
    public function getCurrentStore() {
        return $this->store;
    }
    
    /**
     * קבלת store resolver
     */
    public function getStoreResolver() {
        return $this->storeResolver;
    }
}
?> 