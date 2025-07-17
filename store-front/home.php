<?php
/**
 * עמוד בית של החנות - store-front/home.php
 * מציג את הדף הנבנה מסקשנים דינמיים
 */

require_once '../includes/config.php';
require_once '../config/database.php';
require_once '../includes/StoreResolver.php';

// חיבור למסד נתונים
$pdo = Database::getInstance()->getConnection();

// אתחול מנהל החנות
$storeResolver = new StoreResolver();
$store = $storeResolver->getCurrentStore();

if (!$store) {
    http_response_code(404);
    include '../404.php';
    exit;
}

// טעינת נתוני הדף מבסיס הנתונים
try {
    $stmt = $pdo->prepare("SELECT page_data, is_published FROM builder_pages WHERE store_id = ? AND page_type = 'home' ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute([$store['id']]);
    $pageData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // DEBUG: הוספת לוגים לבדיקה
    error_log("🔧 DEBUG store-front: Store ID = " . $store['id']);
    error_log("🔧 DEBUG store-front: Page data exists = " . ($pageData ? 'YES' : 'NO'));
    if ($pageData) {
        error_log("🔧 DEBUG store-front: Is published = " . ($pageData['is_published'] ? 'YES' : 'NO'));
        error_log("🔧 DEBUG store-front: Raw page_data = " . $pageData['page_data']);
    }
    
    // בסביבת פיתוח - הצג גם דפים שלא פורסמו
    $isDevelopment = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);
    
    if (!$pageData || (!$pageData['is_published'] && !$isDevelopment)) {
        // אם אין דף פרסום - דף ריק
        $sections = [];
        error_log("🔧 DEBUG store-front: No published page - empty sections");
    } else {
        $sections = json_decode($pageData['page_data'], true) ?: [];
        error_log("🔧 DEBUG store-front: Loaded sections count = " . count($sections));
    }
} catch (Exception $e) {
    error_log("🔧 DEBUG store-front: Error loading page data: " . $e->getMessage());
    $sections = getDefaultSections();
}



?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($store['name'] ?? 'החנות שלי'); ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- Noto Sans Hebrew Font -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Hebrew:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Noto Sans Hebrew', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <!-- הדר -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <h1 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($store['name'] ?? 'החנות שלי'); ?></h1>
                </div>
                
                <nav class="hidden md:flex items-center gap-6">
                    <a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">בית</a>
                    <a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">מוצרים</a>
                    <a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">קטגוריות</a>
                    <a href="#" class="text-gray-600 hover:text-gray-900 transition-colors">צור קשר</a>
                </nav>
                
                <div class="flex items-center gap-3">
                    <button class="p-2 text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="ri-search-line text-xl"></i>
                    </button>
                    <button class="p-2 text-gray-600 hover:text-gray-900 transition-colors relative">
                        <i class="ri-shopping-cart-line text-xl"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- תוכן דינמי מסקשנים -->
    <main>
        <?php if (!empty($sections)): ?>
            <?php foreach ($sections as $section): ?>
                <?php renderSection($section); ?>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- דף ריק - הסקשנים יתווספו דינמית על ידי העורך -->
        <?php endif; ?>
    </main>

    <!-- פוטר -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4"><?php echo htmlspecialchars($store['name'] ?? 'החנות שלי'); ?></h3>
                    <p class="text-gray-400">החנות המקוונת הטובה ביותר עבור כל הצרכים שלכם</p>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">קישורים מהירים</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">מוצרים</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">קטגוריות</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">מידע</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">צור קשר</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">תמיכה</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">מדיניות החזרות</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">תנאי שימוש</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">מדיניות פרטיות</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($store['name'] ?? 'החנות שלי'); ?>. כל הזכויות שמורות.</p>
            </div>
        </div>
    </footer>

    <!-- DEBUG INFO -->
    <div id="debug-info" style="position: fixed; bottom: 10px; left: 10px; background: rgba(0,0,0,0.8); color: white; padding: 10px; font-size: 12px; border-radius: 5px; z-index: 9999; display: none;">
        <strong>🔧 DEBUG INFO:</strong><br>
        Store ID: <?php echo $store['id']; ?><br>
        Sections Count: <?php echo count($sections); ?><br>
        <?php if (isset($pageData)): ?>
            Is Published: <?php echo $pageData['is_published'] ? 'YES' : 'NO'; ?><br>
            Page Data: <?php echo htmlspecialchars(substr($pageData['page_data'], 0, 100)); ?>...<br>
        <?php else: ?>
            No page data found<br>
        <?php endif; ?>
    </div>
    
    <!-- JavaScript עבור עדכון דינמי מהעורך -->
    <script>
    // הצגת debug info כשלוחצים Ctrl+D
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'd') {
            e.preventDefault();
            const debugDiv = document.getElementById('debug-info');
            debugDiv.style.display = debugDiv.style.display === 'none' ? 'block' : 'none';
        }
    });
    </script>
    <script>
    // מאזין להודעות מהעורך
    window.addEventListener('message', function(event) {
        // בדיקת מקור ההודעה (אמצעי אבטחה)
        if (event.origin !== window.location.origin) {
            return;
        }
        
        if (event.data.type === 'updateSections') {
            updatePageSections(event.data.sections);
        }
    });
    
    /**
     * עדכון סקשנים בעמוד
     */
    function updatePageSections(sections) {
        const mainElement = document.querySelector('main');
        if (!mainElement) return;
        
        console.log('🔧 DEBUG: Updating sections', sections);
        
        // ניקוי התוכן הקיים (כולל style tags ישנים)
        mainElement.innerHTML = '';
        
        // מחיקת CSS ישן
        document.querySelectorAll('style[data-hero-section]').forEach(style => style.remove());
        
        // יצירת סקשנים חדשים
        sections.forEach(section => {
            console.log('🔧 DEBUG: Rendering section', section);
            
            const sectionHTML = renderSectionHTML(section);
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = sectionHTML;
            
            // העברת כל הילדים (כולל style tags)
            while (tempDiv.firstChild) {
                mainElement.appendChild(tempDiv.firstChild);
            }
        });
        
        console.log('🔧 DEBUG: Sections updated, main content:', mainElement.innerHTML.substring(0, 200));
    }
    
    /**
     * יצירת HTML עבור סקשן
     */
    function renderSectionHTML(section) {
        if (section.type === 'hero') {
            return renderHeroSection(section);
        }
        
        // Fallback לסקשנים אחרים
        return `
            <div class="py-16 bg-gray-100 text-center">
                <h2 class="text-xl text-gray-800">סקשן: ${section.type}</h2>
                <p class="text-gray-600 mt-2">${section.id}</p>
            </div>
        `;
    }
    
    /**
     * יצירת HTML עבור סקשן הירו
     */
    // Generate CSS for a single button
    function generateButtonCSS(sectionId, button, index) {
        // ברירות מחדל לכפתור
        const defaults = {
            bgColor: '#3B82F6',
            textColor: '#FFFFFF',
            borderColor: '#3B82F6',
            hoverBgColor: '#2563EB',
            hoverTextColor: '#FFFFFF',
            hoverBorderColor: '#2563EB',
            paddingTop: '12',
            paddingBottom: '12',
            paddingLeft: '24',
            paddingRight: '24',
            marginTop: '0',
            marginBottom: '8',
            marginLeft: '4',
            marginRight: '4'
        };
        
        const btn = { ...defaults, ...button };
        
        let css = `
            #${sectionId} .hero-button-${index} {
                display: inline-block;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.2s ease;
                cursor: pointer;
                border: 2px solid ${btn.borderColor};
                background-color: ${btn.bgColor};
                color: ${btn.textColor};
        `;
        
        // Padding
        css += `padding: ${btn.paddingTop}px ${btn.paddingRight}px ${btn.paddingBottom}px ${btn.paddingLeft}px;`;
        
        // Margin  
        css += `margin: ${btn.marginTop}px ${btn.marginRight}px ${btn.marginBottom}px ${btn.marginLeft}px;`;
        
        // אם זה כפתור מותאם אישית, השתמש בצבעים מותאמים אישית
        if (button.style === 'custom') {
            // הצבעים כבר מוגדרים למעלה דרך btn object
        } else {
            // סגנונות מוגדרים מראש
            switch (button.style) {
                case 'primary':
                    css += `
                        background-color: #3B82F6;
                        color: white;
                        border-color: #3B82F6;
                    `;
                    break;
                case 'secondary':
                    css += `
                        background-color: #6B7280;
                        color: white;
                        border-color: #6B7280;
                    `;
                    break;
                case 'outline':
                    css += `
                        background-color: transparent;
                        color: white;
                        border-color: white;
                    `;
                    break;
            }
        }
        
        css += `}`;
        
        // Hover states
        css += `
            #${sectionId} .hero-button-${index}:hover {
        `;
        
        if (button.style === 'custom') {
            css += `
                background-color: ${btn.hoverBgColor};
                color: ${btn.hoverTextColor};
                border-color: ${btn.hoverBorderColor};
            `;
        } else {
            switch (button.style) {
                case 'primary':
                    css += `background-color: #2563EB; border-color: #2563EB;`;
                    break;
                case 'secondary':
                    css += `background-color: #4B5563; border-color: #4B5563;`;
                    break;
                case 'outline':
                    css += `background-color: white; color: #1F2937; border-color: white;`;
                    break;
            }
        }
        
        css += `}`;
        
        return css;
    }
    
    // Generate HTML for a single button
    function generateButtonHTML(button, index) {
        const target = button.openInNewTab ? 'target="_blank"' : '';
        const href = button.url || '#';
        const text = button.text || 'כפתור';
        
        return `<a href="${href}" ${target} class="hero-button-${index}">${text}</a>`;
    }

    function renderHeroSection(section) {
        // עיבוד ברירות מחדל מקיפות
        const settings = {
            title: 'ברוכים הבאים לחנות שלנו',
            subtitle: 'גלו את המוצרים הטובים ביותר במחירים הטובים ביותר',
            titleColor: '#FFFFFF',
            subtitleColor: '#E5E7EB',
            bgColor: '#3B82F6',
            heightDesktop: '75vh',
            buttons: [],
            // Typography defaults
            heroTitleFontSize: '48',
            heroTitleTextType: 'h1',
            heroTitleFontFamily: 'Noto Sans Hebrew',
            heroTitleFontWeight: '700',
            heroTitleFontStyle: 'normal',
            heroTitleLineHeight: '1.2',
            heroTitleLetterSpacing: '0',
            heroTitleTextAlign: 'right',
            heroTitleTextDecoration: 'none',
            heroTitleTextTransform: 'none',
            heroSubtitleFontSize: '18',
            heroSubtitleTextType: 'p',
            heroSubtitleFontFamily: 'Noto Sans Hebrew',
            heroSubtitleFontWeight: '400',
            heroSubtitleFontStyle: 'normal',
            heroSubtitleLineHeight: '1.5',
            heroSubtitleLetterSpacing: '0',
            heroSubtitleTextAlign: 'right',
            heroSubtitleTextDecoration: 'none',
            heroSubtitleTextTransform: 'none',
            ...section.settings
        };
        
        console.log('🔧 DEBUG: Rendering hero with settings:', settings);
        
        // יצירת CSS דינמי 
        const heightDesktop = settings.heightDesktop === 'custom' 
            ? (settings.customHeightDesktop || 600) + 'px' 
            : settings.heightDesktop;
            
        let css = `
            #${section.id} {
                height: ${heightDesktop};
                background-color: ${settings.bgColor};
            }
        `;
        
        // Title styles עם כל הגדרות הטיפוגרפיה
        const titleTag = settings.heroTitleTextType || 'h1';
        css += `
            #${section.id} .hero-title {
                color: ${settings.titleColor};
                margin-bottom: 1rem;
                font-size: ${settings.heroTitleFontSize || 48}px;
                font-family: "${settings.heroTitleFontFamily || 'Noto Sans Hebrew'}", sans-serif;
                font-weight: ${settings.heroTitleFontWeight || 700};
                font-style: ${settings.heroTitleFontStyle || 'normal'};
                line-height: ${settings.heroTitleLineHeight || 1.2};
                letter-spacing: ${settings.heroTitleLetterSpacing || 0}px;
                text-align: ${settings.heroTitleTextAlign || 'right'};
                text-decoration: ${settings.heroTitleTextDecoration || 'none'};
                text-transform: ${settings.heroTitleTextTransform || 'none'};
            }
        `;
        
        // Subtitle styles עם כל הגדרות הטיפוגרפיה
        const subtitleTag = settings.heroSubtitleTextType || 'p';
        css += `
            #${section.id} .hero-subtitle {
                color: ${settings.subtitleColor};
                margin-bottom: 2rem;
                font-size: ${settings.heroSubtitleFontSize || 18}px;
                font-family: "${settings.heroSubtitleFontFamily || 'Noto Sans Hebrew'}", sans-serif;
                font-weight: ${settings.heroSubtitleFontWeight || 400};
                font-style: ${settings.heroSubtitleFontStyle || 'normal'};
                line-height: ${settings.heroSubtitleLineHeight || 1.5};
                letter-spacing: ${settings.heroSubtitleLetterSpacing || 0}px;
                text-align: ${settings.heroSubtitleTextAlign || 'right'};
                text-decoration: ${settings.heroSubtitleTextDecoration || 'none'};
                text-transform: ${settings.heroSubtitleTextTransform || 'none'};
            }
        `;
        
        // Button styles
        if (settings.buttons && settings.buttons.length > 0) {
            settings.buttons.forEach((button, index) => {
                if (button && button.text) {
                    css += generateButtonCSS(section.id, button, index);
                }
            });
        }
        
        // Mobile responsive styles
        css += `
            @media (max-width: 767px) {
                #${section.id} .hero-title {
                    font-size: ${settings.heroTitleFontSize ? Math.round(settings.heroTitleFontSize * 0.7) : 32}px;
                }
                #${section.id} .hero-subtitle {
                    font-size: ${settings.heroSubtitleFontSize ? Math.round(settings.heroSubtitleFontSize * 0.9) : 16}px;
                }
            }
        `;
        
        // Generate buttons HTML
        let buttonsHTML = '';
        if (settings.buttons && settings.buttons.length > 0) {
            buttonsHTML = '<div class="hero-buttons flex flex-wrap gap-4 justify-center">';
            settings.buttons.forEach((button, index) => {
                if (button && button.text) {
                    buttonsHTML += generateButtonHTML(button, index);
                }
            });
            buttonsHTML += '</div>';
        } else {
            // Default button
            buttonsHTML = `
                <div class="hero-buttons flex flex-wrap gap-4 justify-center">
                    <a href="#" class="bg-white text-gray-900 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                        קנה עכשיו
                    </a>
                </div>
            `;
        }
        
        return `
            <style data-hero-section="${section.id}">${css}</style>
            <section id="${section.id}" class="hero-section relative overflow-hidden">
                <!-- Content Container -->
                <div class="relative z-20 container mx-auto px-4 h-full flex items-center">
                    <div class="hero-content w-full text-center">
                        <!-- Title -->
                        ${settings.title ? `<${titleTag} class="hero-title">${settings.title}</${titleTag}>` : ''}
                        
                        <!-- Subtitle -->
                        ${settings.subtitle ? `<${subtitleTag} class="hero-subtitle">${settings.subtitle}</${subtitleTag}>` : ''}
                        
                        <!-- Buttons -->
                        ${buttonsHTML}
                    </div>
                </div>
            </section>
        `;
    }
    </script>

</body>
</html>

<?php
/**
 * רינדור סקשן
 */
function renderSection($section) {
    $sectionType = $section['type'];
    $sectionPath = "../editor/settings/sections/{$sectionType}/template.php";
    
    if (file_exists($sectionPath)) {
        include $sectionPath;
    } else {
        // fallback לסקשן בסיסי - אבל גם הודעת debug
        echo '<div class="py-16 bg-gray-100 text-center">';
        echo '<h2 class="text-xl text-gray-800">סקשן: ' . htmlspecialchars($sectionType) . '</h2>';
        echo '<p class="text-gray-600 text-sm mt-2">Template not found: ' . htmlspecialchars($sectionPath) . '</p>';
        echo '</div>';
    }
}
?> 