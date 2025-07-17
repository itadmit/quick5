<?php
/**
 * מנהל עמודים היברידי - גישה חדשה ויעילה
 */
class HybridPageManager {
    private $db;
    private $storeId;
    
    // הגדרות ברירת מחדל לכל סוג סקשן
    private $defaultSectionSettings = [
        'header' => [
            'store_name' => 'החנות שלי',
            'show_search' => true,
            'show_cart' => true,
            'background_color' => '#ffffff',
            'text_color' => '#1f2937'
        ],
        'hero' => [
            'title' => 'ברוכים הבאים',
            'subtitle' => 'גלה את המוצרים הטובים ביותר',
            'description' => 'גלה את המוצרים הטובים ביותר', // תמיכה בשני השמות
            'content' => '',
            'bg_type' => 'color',
            'bg_color' => '#1e40af',
            'background_color' => '#1e40af', // תמיכה בשני השמות
            'gradient_start' => '#1e40af',
            'gradient_end' => '#3b82f6',
            'gradient_direction' => 'to bottom',
            'bg_image' => '',
            'bg_image_size' => 'cover',
            'bg_video_url' => '',
            'bg_video_file' => '',
            'video_type' => 'file',
            'video_autoplay' => true,
            'video_loop' => true,
            'video_muted' => true,
            'video_overlay' => true,
            'video_overlay_color' => '#000000',
            'title_color' => '#ffffff',
            'text_color' => '#ffffff', // תמיכה בשני השמות
            'title_size' => 48,
            'subtitle_color' => '#e5e7eb',
            'subtitle_size' => 24,
            'content_color' => '#d1d5db',
            'content_size' => 16,
            'button_text' => 'קנה עכשיו',
            'button_link' => '/products',
            'button_bg_color' => '#f59e0b',
            'button_color' => '#f59e0b', // תמיכה בשני השמות
            'button_text_color' => '#ffffff',
            'button_font_size' => 16,
            'padding_top' => 80,
            'padding_bottom' => 80,
            'padding_left' => 20,
            'padding_right' => 20,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_left' => 0,
            'margin_right' => 0,
            'custom_css' => '',
            'buttons' => []
        ],
        'featured-products' => [
            'title' => 'מוצרים מומלצים',
            'products_count' => 4,
            'columns' => 4,
            'show_price' => true,
            'show_add_to_cart' => true
        ],
        'testimonials' => [
            'title' => 'מה הלקוחות אומרים',
            'testimonials_count' => 3,
            'show_stars' => true,
            'background_color' => '#f8fafc'
        ],
        'footer' => [
            'copyright' => '© 2024 החנות שלי. כל הזכויות שמורות.',
            'contact_email' => '',
            'contact_phone' => '',
            'facebook_link' => '',
            'instagram_link' => '',
            'background_color' => '#1f2937',
            'text_color' => '#ffffff'
        ]
    ];
    
    public function __construct($db, $storeId) {
        $this->db = $db;
        $this->storeId = $storeId;
    }
    
    /**
     * טעינת עמוד בית - גישה היברידית
     */
    public function getHomePage() {
        // 1. טעינת המבנה הכללי של העמוד
        $pageStructure = $this->getPageStructure('home');
        
        // 2. טעינת הגדרות גלובליות
        $globalSettings = $this->getGlobalSettings('home');
        
        // 3. טעינת הגדרות מותאמות אישית
        $customSettings = $this->getCustomSections('home');
        
        // 4. מיזוג הכל יחד
        $sections = [];
        foreach ($pageStructure as $order => $sectionType) {
            $sectionId = $this->generateSectionId($sectionType, $order);
            
            // התחלה מהגדרות ברירת מחדל
            $settings = $this->defaultSectionSettings[$sectionType] ?? [];
            
            // הוספת הגדרות גלובליות
            $settings = array_merge($settings, $globalSettings);
            
            // הוספת הגדרות מותאמות אישית (אם קיימות)
            if (isset($customSettings[$sectionId])) {
                $settings = array_merge($settings, $customSettings[$sectionId]);
            }
            
            $sections[] = [
                'id' => $sectionId,
                'section_id' => $sectionId,
                'type' => $sectionType,
                'section_type' => $sectionType,
                'settings' => $settings,
                'order' => $order + 1,
                'section_order' => $order + 1,
                'visible' => true,
                'is_visible' => true
            ];
        }
        
        return $sections;
    }
    
    /**
     * יצירת ID לסקשן
     */
    private function generateSectionId($sectionType, $order) {
        // עבור header ו-footer נשתמש בשם הסקשן
        if ($sectionType === 'header' || $sectionType === 'footer') {
            return $sectionType;
        }
        
        // עבור שאר הסקשנים נוסיף מספר סדר
        return $sectionType . '-' . ($order + 1);
    }
    
    /**
     * טעינת מבנה העמוד
     */
    private function getPageStructure($pageType) {
        $stmt = $this->db->prepare("
            SELECT page_structure 
            FROM store_pages 
            WHERE store_id = ? AND page_type = ?
        ");
        $stmt->execute([$this->storeId, $pageType]);
        $result = $stmt->fetch();
        
        if ($result && $result['page_structure']) {
            return json_decode($result['page_structure'], true);
        }
        
        // ברירת מחדל אם לא קיים
        return ['header', 'hero', 'featured-products', 'footer'];
    }
    
    /**
     * טעינת הגדרות גלובליות
     */
    private function getGlobalSettings($pageType) {
        $stmt = $this->db->prepare("
            SELECT global_settings 
            FROM store_pages 
            WHERE store_id = ? AND page_type = ?
        ");
        $stmt->execute([$this->storeId, $pageType]);
        $result = $stmt->fetch();
        
        if ($result && $result['global_settings']) {
            return json_decode($result['global_settings'], true);
        }
        
        return [];
    }
    
    /**
     * טעינת הגדרות מותאמות אישית
     */
    private function getCustomSections($pageType) {
        $stmt = $this->db->prepare("
            SELECT section_id, custom_settings 
            FROM custom_sections 
            WHERE store_id = ? AND page_type = ? AND is_visible = 1
            ORDER BY section_order
        ");
        $stmt->execute([$this->storeId, $pageType]);
        $results = $stmt->fetchAll();
        
        $customSettings = [];
        foreach ($results as $row) {
            if ($row['custom_settings']) {
                $customSettings[$row['section_id']] = json_decode($row['custom_settings'], true);
            }
        }
        
        return $customSettings;
    }
    
    /**
     * שמירת הגדרות מותאמות אישית
     */
    public function saveCustomSection($pageType, $sectionId, $sectionType, $customSettings) {
        // שמירה רק של הגדרות שונות מברירת המחדל
        $defaultSettings = $this->defaultSectionSettings[$sectionType] ?? [];
        $onlyCustomSettings = [];
        
        foreach ($customSettings as $key => $value) {
            if (!isset($defaultSettings[$key]) || $defaultSettings[$key] !== $value) {
                $onlyCustomSettings[$key] = $value;
            }
        }
        
        if (empty($onlyCustomSettings)) {
            // אם אין הגדרות מותאמות, מוחקים את הרשומה
            $stmt = $this->db->prepare("
                DELETE FROM custom_sections 
                WHERE store_id = ? AND page_type = ? AND section_id = ?
            ");
            $stmt->execute([$this->storeId, $pageType, $sectionId]);
        } else {
            // שמירת הגדרות מותאמות
            $stmt = $this->db->prepare("
                INSERT INTO custom_sections (store_id, page_type, section_id, section_type, custom_settings, section_order) 
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                custom_settings = VALUES(custom_settings),
                section_order = VALUES(section_order),
                updated_at = NOW()
            ");
            
            // חישוב סדר הסקשן
            $order = $this->calculateSectionOrder($pageType, $sectionId);
            
            $stmt->execute([
                $this->storeId, 
                $pageType, 
                $sectionId, 
                $sectionType, 
                json_encode($onlyCustomSettings),
                $order
            ]);
        }
    }
    
    /**
     * חישוב סדר הסקשן
     */
    private function calculateSectionOrder($pageType, $sectionId) {
        $pageStructure = $this->getPageStructure($pageType);
        
        foreach ($pageStructure as $order => $sectionType) {
            $generatedId = $this->generateSectionId($sectionType, $order);
            if ($generatedId === $sectionId) {
                return $order + 1;
            }
        }
        
        return 1;
    }
    
    /**
     * שמירת מבנה עמוד
     */
    public function savePageStructure($pageType, $structure) {
        $stmt = $this->db->prepare("
            INSERT INTO store_pages (store_id, page_type, page_structure) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            page_structure = VALUES(page_structure),
            updated_at = NOW()
        ");
        
        $stmt->execute([
            $this->storeId, 
            $pageType, 
            json_encode($structure)
        ]);
    }
    
    /**
     * שמירת הגדרות גלובליות
     */
    public function saveGlobalSettings($pageType, $settings) {
        $stmt = $this->db->prepare("
            INSERT INTO store_pages (store_id, page_type, global_settings) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            global_settings = VALUES(global_settings),
            updated_at = NOW()
        ");
        
        $stmt->execute([
            $this->storeId, 
            $pageType, 
            json_encode($settings)
        ]);
    }
    
    /**
     * העברת נתונים מהטבלה הישנה
     */
    public function migrateFromOldSystem() {
        // קריאת הנתונים מהטבלה הישנה
        $stmt = $this->db->prepare("
            SELECT * FROM theme_sections 
            WHERE store_id = ? 
            ORDER BY section_order
        ");
        $stmt->execute([$this->storeId]);
        $oldSections = $stmt->fetchAll();
        
        if (empty($oldSections)) {
            return false;
        }
        
        // יצירת מבנה עמוד
        $pageStructure = [];
        $customSettings = [];
        
        foreach ($oldSections as $section) {
            $sectionType = $section['section_type'];
            $pageStructure[] = $sectionType;
            
            if ($section['settings']) {
                $settings = json_decode($section['settings'], true);
                $defaultSettings = $this->defaultSectionSettings[$sectionType] ?? [];
                
                // שמירה רק של הגדרות שונות מברירת המחדל
                $onlyCustomSettings = [];
                foreach ($settings as $key => $value) {
                    if (!isset($defaultSettings[$key]) || $defaultSettings[$key] !== $value) {
                        $onlyCustomSettings[$key] = $value;
                    }
                }
                
                if (!empty($onlyCustomSettings)) {
                    $customSettings[$section['section_id']] = [
                        'type' => $sectionType,
                        'settings' => $onlyCustomSettings,
                        'order' => $section['section_order']
                    ];
                }
            }
        }
        
        // שמירת מבנה העמוד
        $this->savePageStructure('home', $pageStructure);
        
        // שמירת הגדרות מותאמות אישית
        foreach ($customSettings as $sectionId => $data) {
            $this->saveCustomSection('home', $sectionId, $data['type'], $data['settings']);
        }
        
        return true;
    }
}
?> 