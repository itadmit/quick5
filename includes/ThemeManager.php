<?php
/**
 * Theme Manager Class
 * מחלקה לניהול תבניות וקסטומיזר
 */

class ThemeManager {
    private $db;
    private $storeId;
    private $currentTheme;
    private $themeSettings;
    
    public function __construct($storeId = null) {
        $this->db = Database::getInstance()->getConnection();
        $this->storeId = $storeId;
        $this->loadThemeSettings();
    }
    
    /**
     * טעינת הגדרות התבנית הנוכחית
     */
    private function loadThemeSettings() {
        if (!$this->storeId) return;
        
        try {
            // קבלת הגדרות התבנית מהדטאבייס
            $stmt = $this->db->prepare("
                SELECT theme_name, theme_settings 
                FROM stores 
                WHERE id = ?
            ");
            $stmt->execute([$this->storeId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->currentTheme = $result['theme_name'] ?? 'quickshop-evening';
            $this->themeSettings = $result['theme_settings'] ? 
                json_decode($result['theme_settings'], true) : [];
            
        } catch (Exception $e) {
            error_log("ThemeManager loadThemeSettings error: " . $e->getMessage());
            $this->currentTheme = 'quickshop-evening';
            $this->themeSettings = [];
        }
    }
    
    /**
     * קבלת שם התבנית הנוכחית
     */
    public function getCurrentTheme() {
        return $this->currentTheme;
    }
    
    /**
     * קבלת הגדרות התבנית
     */
    public function getThemeSettings() {
        return $this->themeSettings;
    }
    
    /**
     * עדכון הגדרות התבנית
     */
    public function updateThemeSettings($settings) {
        if (!$this->storeId) return false;
        
        try {
            $stmt = $this->db->prepare("
                UPDATE stores 
                SET theme_settings = ? 
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                json_encode($settings),
                $this->storeId
            ]);
            
            if ($result) {
                $this->themeSettings = $settings;
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("ThemeManager updateThemeSettings error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * שינוי תבנית
     */
    public function changeTheme($themeName) {
        if (!$this->storeId) return false;
        
        // בדיקה שהתבנית קיימת
        if (!$this->themeExists($themeName)) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("
                UPDATE stores 
                SET theme_name = ? 
                WHERE id = ?
            ");
            
            $result = $stmt->execute([$themeName, $this->storeId]);
            
            if ($result) {
                $this->currentTheme = $themeName;
                $this->loadThemeSettings();
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("ThemeManager changeTheme error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * בדיקה אם תבנית קיימת
     */
    public function themeExists($themeName) {
        $themePath = __DIR__ . "/../storefront/themes/{$themeName}";
        return is_dir($themePath) && file_exists($themePath . '/theme.json');
    }
    
    /**
     * קבלת רשימת תבניות זמינות
     */
    public function getAvailableThemes() {
        $themes = [];
        $themesDir = __DIR__ . '/../storefront/themes/';
        
        if (!is_dir($themesDir)) {
            return $themes;
        }
        
        $directories = scandir($themesDir);
        foreach ($directories as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            
            $themePath = $themesDir . $dir;
            $configPath = $themePath . '/theme.json';
            
            if (is_dir($themePath) && file_exists($configPath)) {
                $config = json_decode(file_get_contents($configPath), true);
                if ($config) {
                    $themes[$dir] = $config;
                }
            }
        }
        
        return $themes;
    }
    
    /**
     * קבלת הגדרות תבנית ספציפית
     */
    public function getThemeConfig($themeName = null) {
        $themeName = $themeName ?: $this->currentTheme;
        $configPath = __DIR__ . "/../storefront/themes/{$themeName}/theme.json";
        
        if (!file_exists($configPath)) {
            return null;
        }
        
        return json_decode(file_get_contents($configPath), true);
    }
    
    /**
     * קבלת בלוקים זמינים
     */
    public function getAvailableBlocks($themeName = null) {
        $themeName = $themeName ?: $this->currentTheme;
        $blocksDir = __DIR__ . "/../storefront/themes/{$themeName}/blocks/";
        $blocks = [];
        
        if (!is_dir($blocksDir)) {
            return $blocks;
        }
        
        $directories = scandir($blocksDir);
        foreach ($directories as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            
            $blockPath = $blocksDir . $dir;
            $configPath = $blockPath . '/config.json';
            
            if (is_dir($blockPath) && file_exists($configPath)) {
                $config = json_decode(file_get_contents($configPath), true);
                if ($config) {
                    $blocks[$dir] = $config;
                }
            }
        }
        
        return $blocks;
    }
    
    /**
     * שמירת בלוקים מותאמים אישית לדף
     */
    public function savePageBlocks($pageType, $blocks) {
        if (!$this->storeId) return false;
        
        try {
            $this->db->beginTransaction();
            
            // מחיקת בלוקים קיימים
            $stmt = $this->db->prepare("
                DELETE FROM custom_blocks 
                WHERE store_id = ? AND page_type = ?
            ");
            $stmt->execute([$this->storeId, $pageType]);
            
            // הוספת בלוקים חדשים
            foreach ($blocks as $index => $block) {
                $stmt = $this->db->prepare("
                    INSERT INTO custom_blocks (
                        store_id, page_type, block_type, block_settings, 
                        sort_order, is_active
                    ) VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $this->storeId,
                    $pageType,
                    $block['type'],
                    json_encode($block['settings']),
                    $index,
                    1
                ]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("ThemeManager savePageBlocks error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * קבלת בלוקים של דף
     */
    public function getPageBlocks($pageType) {
        if (!$this->storeId) return [];
        
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM custom_blocks 
                WHERE store_id = ? AND page_type = ? AND is_active = 1
                ORDER BY sort_order ASC
            ");
            $stmt->execute([$this->storeId, $pageType]);
            
            $blocks = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $blocks[] = [
                    'id' => $row['id'],
                    'type' => $row['block_type'],
                    'settings' => json_decode($row['block_settings'], true)
                ];
            }
            
            return $blocks;
            
        } catch (Exception $e) {
            error_log("ThemeManager getPageBlocks error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * יצירת קוד PHP מבלוקים
     */
    public function generateTemplateCode($pageType, $blocks) {
        $code = "<?php\n";
        $code .= "/**\n";
        $code .= " * Generated Template - {$pageType}\n";
        $code .= " * Created by QuickShop Visual Builder\n";
        $code .= " * Date: " . date('Y-m-d H:i:s') . "\n";
        $code .= " */\n\n";
        
        $code .= "// קבלת נתוני החנות\n";
        $code .= "\$store = \$GLOBALS['CURRENT_STORE'] ?? null;\n\n";
        
        foreach ($blocks as $block) {
            $code .= $this->generateBlockCode($block);
        }
        
        return $code;
    }
    
    /**
     * יצירת קוד PHP לבלוק בודד
     */
    private function generateBlockCode($block) {
        $blockType = $block['type'];
        $settings = $block['settings'];
        
        $code = "// {$blockType} Block\n";
        $code .= "?>\n";
        
        // טעינת הבלוק
        $code .= "<?php\n";
        $code .= "\$blockFile = __DIR__ . \"/../blocks/{$blockType}/block.php\";\n";
        $code .= "if (file_exists(\$blockFile)) {\n";
        $code .= "    include \$blockFile;\n";
        $code .= "    \$renderFunction = \"render\" . ucfirst(str_replace('-', '', '{$blockType}')) . \"Block\";\n";
        $code .= "    if (function_exists(\$renderFunction)) {\n";
        $code .= "        \$settings = " . var_export($settings, true) . ";\n";
        $code .= "        \$renderFunction(\$settings, \$store);\n";
        $code .= "    }\n";
        $code .= "}\n";
        $code .= "?>\n\n";
        
        return $code;
    }
    
    /**
     * שמירת תבנית מותאמת אישית
     */
    public function saveCustomTemplate($templateName, $code) {
        if (!$this->storeId) return false;
        
        $themePath = __DIR__ . "/../storefront/themes/{$this->currentTheme}/templates/";
        
        // יצירת תיקיה אם לא קיימת
        if (!is_dir($themePath)) {
            mkdir($themePath, 0755, true);
        }
        
        $filePath = $themePath . $templateName . '.php';
        
        try {
            return file_put_contents($filePath, $code) !== false;
        } catch (Exception $e) {
            error_log("ThemeManager saveCustomTemplate error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * קבלת קוד תבנית
     */
    public function getTemplateCode($templateName) {
        $filePath = __DIR__ . "/../storefront/themes/{$this->currentTheme}/templates/{$templateName}.php";
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        return file_get_contents($filePath);
    }
    
    /**
     * מיזוג הגדרות ברירת מחדל עם הגדרות המשתמש
     */
    public function getMergedSettings() {
        $themeConfig = $this->getThemeConfig();
        $defaultSettings = [];
        
        if (isset($themeConfig['settings'])) {
            foreach ($themeConfig['settings'] as $category => $settings) {
                foreach ($settings as $key => $setting) {
                    $defaultSettings[$key] = $setting['default'] ?? '';
                }
            }
        }
        
        return array_merge($defaultSettings, $this->themeSettings);
    }
    
    /**
     * רנדור דף עם תבנית
     */
    public function renderPage($pageType, $pageData = []) {
        // הגדרת גלובלים
        $GLOBALS['CURRENT_STORE'] = $this->getStoreData();
        $GLOBALS['PAGE_DATA'] = $pageData;
        $GLOBALS['THEME_SETTINGS'] = $this->getMergedSettings();
        
        // טעינת תבנית ראשית
        $themeFile = __DIR__ . "/../storefront/themes/{$this->currentTheme}/theme.php";
        
        if (file_exists($themeFile)) {
            include $themeFile;
        } else {
            throw new Exception("Theme file not found: {$this->currentTheme}");
        }
    }
    
    /**
     * קבלת נתוני החנות
     */
    private function getStoreData() {
        if (!$this->storeId) return null;
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM stores WHERE id = ?");
            $stmt->execute([$this->storeId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("ThemeManager getStoreData error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * בדיקה אם החנות במצב תצוגה מקדימה
     */
    public function isPreviewMode() {
        return isset($_GET['preview']) && $_GET['preview'] === '1';
    }
    
    /**
     * בדיקה אם החנות במצב קסטומיזר
     */
    public function isCustomizerMode() {
        return isset($_GET['customizer']) && $_GET['customizer'] === '1';
    }
    
    /**
     * קבלת תבנית נוכחית עם פרטים מלאים
     */
    public function getCurrentThemeDetails($storeId) {
        $this->storeId = $storeId;
        $this->loadThemeSettings();
        
        $themeConfig = $this->getThemeConfig();
        if (!$themeConfig) {
            return [
                'name' => 'quickshop-evening',
                'display_name' => 'QuickShop Evening',
                'version' => '1.0.0',
                'description' => 'תבנית אלגנטית למסחר מקוון עם עיצוב מודרני'
            ];
        }
        
        return array_merge($themeConfig, ['name' => $this->currentTheme]);
    }
    
    /**
     * קבלת הגדרות תבנית לחנות
     */
    public function getStoreThemeSettings($storeId) {
        $this->storeId = $storeId;
        $this->loadThemeSettings();
        return $this->themeSettings;
    }
    
    /**
     * שמירת הגדרות תבנית
     */
    public function saveThemeSettings($storeId, $settings) {
        $this->storeId = $storeId;
        return $this->updateThemeSettings($settings);
    }
    
    /**
     * עדכון הגדרות תבנית לחנות
     */
    public function updateStoreThemeSettings($storeId, $settings) {
        try {
            $stmt = $this->db->prepare("
                UPDATE stores 
                SET theme_settings = ? 
                WHERE id = ?
            ");
            
            $settingsJson = json_encode($settings);
            return $stmt->execute([$settingsJson, $storeId]);
            
        } catch (Exception $e) {
            error_log("ThemeManager updateStoreThemeSettings error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * הגדרת תבנית לחנות
     */
    public function setTheme($storeId, $themeName) {
        $this->storeId = $storeId;
        return $this->changeTheme($themeName);
    }
    
    /**
     * קבלת תבניות מותאמות אישית
     */
    public function getCustomTemplates($storeId) {
        $this->storeId = $storeId;
        $this->loadThemeSettings();
        
        $templates = [];
        $templateTypes = ['home', 'category', 'product'];
        
        foreach ($templateTypes as $type) {
            $code = $this->getTemplateCode($type);
            if ($code) {
                $templates[$type] = $code;
            } else {
                // תבנית ברירת מחדל
                $templates[$type] = $this->getDefaultTemplateCode($type);
            }
        }
        
        return $templates;
    }
    
    /**
     * שמירת תבנית מותאמת אישית
     */
    public function saveStoreCustomTemplate($storeId, $templateType, $content) {
        $this->storeId = $storeId;
        return $this->saveCustomTemplate($templateType, $content);
    }
    
    /**
     * קבלת קוד תבנית ברירת מחדל
     */
    private function getDefaultTemplateCode($templateType) {
        $defaultCodes = [
            'home' => '<?php
/**
 * תבנית דף הבית
 */
?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center mb-8">ברוכים הבאים לחנות שלנו</h1>
    
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-8 rounded-lg mb-8">
        <h2 class="text-2xl font-bold mb-4">המוצרים הטובים ביותר</h2>
        <p class="mb-4">גלו את המגוון הרחב של המוצרים שלנו</p>
        <a href="/products" class="bg-white text-blue-600 px-6 py-2 rounded-lg hover:bg-gray-100">קנו עכשיו</a>
    </div>
    
    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- מוצרים יוצגו כאן -->
    </div>
</div>',
            
            'category' => '<?php
/**
 * תבנית דף קטגוריה
 */
?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8"><?php echo $pageData["category_name"] ?? "קטגוריה"; ?></h1>
    
    <!-- Filters -->
    <div class="mb-6">
        <div class="flex flex-wrap gap-4">
            <select class="border rounded-lg px-4 py-2">
                <option>מיון לפי</option>
                <option>מחיר נמוך לגבוה</option>
                <option>מחיר גבוה לנמוך</option>
                <option>הכי חדש</option>
            </select>
        </div>
    </div>
    
    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <!-- מוצרים יוצגו כאן -->
    </div>
</div>',
            
            'product' => '<?php
/**
 * תבנית דף מוצר
 */
?>
<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Product Images -->
        <div>
            <div class="aspect-w-1 aspect-h-1 bg-gray-200 rounded-lg mb-4">
                <img src="<?php echo $pageData["product_image"] ?? ""; ?>" alt="<?php echo $pageData["product_name"] ?? ""; ?>" class="w-full h-full object-cover rounded-lg">
            </div>
        </div>
        
        <!-- Product Info -->
        <div>
            <h1 class="text-3xl font-bold mb-4"><?php echo $pageData["product_name"] ?? "שם המוצר"; ?></h1>
            <p class="text-2xl font-bold text-green-600 mb-4">₪<?php echo $pageData["product_price"] ?? "0"; ?></p>
            <p class="text-gray-600 mb-6"><?php echo $pageData["product_description"] ?? "תיאור המוצר"; ?></p>
            
            <!-- Add to Cart -->
            <div class="flex items-center gap-4 mb-6">
                <input type="number" value="1" min="1" class="border rounded-lg px-4 py-2 w-20">
                <button class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">הוסף לעגלה</button>
            </div>
        </div>
    </div>
</div>'
        ];
        
        return $defaultCodes[$templateType] ?? '<!-- תבנית ברירת מחדל -->';
    }
    
    /**
     * קבלת הגדרות עיצוב ותוכן בלבד (לקסטומיזר)
     */
    public function getThemeDesignSettings($storeId) {
        try {
            $stmt = $this->db->prepare("
                SELECT theme_settings 
                FROM stores 
                WHERE id = ?
            ");
            $stmt->execute([$storeId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['theme_settings']) {
                $allSettings = json_decode($result['theme_settings'], true);
                
                // החזרת הגדרות עיצוב ותוכן בלבד
                return [
                    'colors' => $allSettings['colors'] ?? [],
                    'fonts' => $allSettings['fonts'] ?? [],
                    'layout' => $allSettings['layout'] ?? [],
                    'hero' => $allSettings['hero'] ?? [],
                    'menu' => $allSettings['menu'] ?? [],
                    'footer' => $allSettings['footer'] ?? []
                ];
            }
            
            return [];
            
        } catch (Exception $e) {
            error_log("ThemeManager getThemeDesignSettings error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * שמירת הגדרות עיצוב ותוכן בלבד (לקסטומיזר)
     */
    public function saveThemeDesignSettings($storeId, $designSettings) {
        try {
            // קבלת הגדרות נוכחיות
            $stmt = $this->db->prepare("
                SELECT theme_settings 
                FROM stores 
                WHERE id = ?
            ");
            $stmt->execute([$storeId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $currentSettings = [];
            if ($result && $result['theme_settings']) {
                $currentSettings = json_decode($result['theme_settings'], true);
            }
            
            // עדכון הגדרות עיצוב ותוכן בלבד
            $currentSettings['colors'] = $designSettings['colors'] ?? [];
            $currentSettings['fonts'] = $designSettings['fonts'] ?? [];
            $currentSettings['layout'] = $designSettings['layout'] ?? [];
            $currentSettings['hero'] = $designSettings['hero'] ?? [];
            $currentSettings['menu'] = $designSettings['menu'] ?? [];
            $currentSettings['footer'] = $designSettings['footer'] ?? [];
            
            // שמירה
            $stmt = $this->db->prepare("
                UPDATE stores 
                SET theme_settings = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            
            return $stmt->execute([json_encode($currentSettings), $storeId]);
            
        } catch (Exception $e) {
            error_log("ThemeManager saveThemeDesignSettings error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * שמירה אוטומטית של הגדרות עיצוב ותוכן
     */
    public function autoSaveThemeDesignSettings($storeId, $designSettings) {
        // זהה לשמירה רגילה אבל עם לוגים פחות מפורטים
        return $this->saveThemeDesignSettings($storeId, $designSettings);
    }
}