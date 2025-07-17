<?php
/**
 * Builder Renderer - פשוט ונקי
 * מציג את דף הבית עם בלוק Hero יחיד
 */

class BuilderRenderer {
    private $storeId;
    private $store;
    
    public function __construct($storeId = null) {
        $this->storeId = $storeId;
        $this->store = $this->getStore();
    }
    
    private function getStore() {
        if (isset($GLOBALS['CURRENT_STORE'])) {
            return $GLOBALS['CURRENT_STORE'];
        }
        
        // Fallback to get current store
        require_once __DIR__ . '/../includes/StoreResolver.php';
        $storeResolver = new StoreResolver();
        return $storeResolver->getCurrentStore();
    }
    
    /**
     * רנדור דף הבית
     */
    public function renderHomePage() {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="he" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($this->store['name'] ?? 'החנות שלי'); ?></title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Hebrew:wght@300;400;500;600;700&family=Heebo:wght@300;400;500;600;700&family=Assistant:wght@300;400;500;600;700&family=Varela+Round&family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Open+Sans+Hebrew:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <script>
                tailwind.config = {
                    theme: {
                        extend: {
                            fontFamily: {
                                'hebrew': ['Noto Sans Hebrew', 'sans-serif']
                            }
                        }
                    }
                }
            </script>
            <style>
                body {
                    font-family: 'Noto Sans Hebrew', sans-serif;
                }
                
                /* Global Section Width Classes */
                .section-width-container {
                    max-width: 1200px;
                    margin-left: auto;
                    margin-right: auto;
                    padding-left: 20px;
                    padding-right: 20px;
                }
                
                .section-width-full {
                    width: 100%;
                    margin-left: 0;
                    margin-right: 0;
                    padding-left: 0;
                    padding-right: 0;
                }
                
                .section-width-custom {
                    margin-left: auto;
                    margin-right: auto;
                    padding-left: 20px;
                    padding-right: 20px;
                }
            </style>
        </head>
        <body class="font-hebrew">
            
            <?php 
            // Include Hero Block
            $this->includeBlock('hero');
            ?>
            
            <!-- Builder Preview Mode Footer -->
            <?php if (isset($_GET['builder_preview']) && $_GET['builder_preview'] == '1'): ?>
            <div style="position: fixed; bottom: 10px; left: 10px; background: rgba(0,0,0,0.8); color: white; padding: 8px 12px; border-radius: 6px; font-size: 12px; z-index: 9999;">
                Builder Preview Mode
            </div>
            <?php endif; ?>
            
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * כלול בלוק
     */
    private function includeBlock($blockName) {
        $blockFile = __DIR__ . "/blocks/{$blockName}.php";
        
        if (file_exists($blockFile)) {
            include $blockFile;
        } else {
            echo "<!-- Block '{$blockName}' not found -->";
        }
    }
} 