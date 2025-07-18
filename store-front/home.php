<?php
/**
 * עמוד בית של החנות - store-front/home.php
 * מציג את הדף הנבנה מסקשנים דינמיים עם הבילדר החדש
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
    
    // בסביבת פיתוח - הצג גם דפים שלא פורסמו
    $isDevelopment = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);
    
    if (!$pageData || (!$pageData['is_published'] && !$isDevelopment)) {
        // אם אין דף - הודעה לעריכה
        $sections = [];
    } else {
        $sections = json_decode($pageData['page_data'], true) ?: [];
    }
} catch (Exception $e) {
    error_log("Error loading page data: " . $e->getMessage());
    $sections = [];
}

// טעינת מערכת הרינדור של סקשנים
require_once '../editor/sections/hero/template.php';

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
            direction: rtl;
        }
        
        /* CSS משתנים למענה */
        @media (max-width: 768px) {
            .mobile-only { display: block !important; }
            .desktop-only { display: none !important; }
        }
        
        @media (min-width: 769px) {
            .mobile-only { display: none !important; }
            .desktop-only { display: block !important; }
        }
        
        /* אנימציות כניסה */
        [data-animation] {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        [data-animation].animate-in {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* סוגי אנימציות ספציפיים */
        [data-animation="fade-in"] {
            transform: none;
        }
        
        [data-animation="fade-in"].animate-in {
            transform: none;
        }
        
        [data-animation="slide-up"] {
            transform: translateY(50px);
        }
        
        [data-animation="slide-up"].animate-in {
            transform: translateY(0);
        }
        
        [data-animation="zoom-in"] {
            transform: scale(0.9);
            opacity: 0;
        }
        
        [data-animation="zoom-in"].animate-in {
            transform: scale(1);
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- רינדור סקשנים -->
    <main>
        <?php if (!empty($sections)): ?>
            <?php foreach ($sections as $section): ?>
                <?php
                echo '<script>console.log("🔧 DEBUG: Updating sections", ' . json_encode($sections) . ');</script>';
                echo '<script>console.log("🔧 DEBUG: Rendering section", ' . json_encode($section) . ');</script>';
                
                if ($section['type'] === 'hero') {
                    echo renderHeroSection($section);
                } elseif ($section['type'] === 'categories') {
                    require_once '../editor/sections/categories/template.php';
                    echo renderCategoriesSection($section);
                }
                ?>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- הודעת ברירת מחדל אם אין סקשנים -->
            <div class="min-h-screen flex items-center justify-center bg-gray-100">
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">ברוכים הבאים לחנות</h1>
                    <p class="text-gray-600 mb-6">הדף נמצא בשלבי בנייה</p>
                    <a href="/editor/" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                        עריכת הדף
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // JavaScript לטיפול ב-responsive behavior
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Store front loaded successfully (from store-front/home.php)');
            
            // אתחול אנימציות
            initAnimations();
        });
        
        /**
         * אתחול אנימציות כניסה
         */
        function initAnimations() {
            // אלמנטים עם אנימציות
            const animatedElements = document.querySelectorAll('[data-animation]');
            
            if (animatedElements.length === 0) {
                console.log('🎭 No animated elements found');
                return;
            }
            
            console.log(`🎭 Found ${animatedElements.length} animated elements`);
            
            // Intersection Observer לזיהוי כניסה לתצוגה
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const element = entry.target;
                        const animationType = element.dataset.animation;
                        
                        console.log(`🎭 Triggering animation: ${animationType}`);
                        
                        // הפעלת האנימציה
                        setTimeout(() => {
                            element.classList.add('animate-in');
                        }, 100); // עיכוב קטן לאפקט חלק יותר
                        
                        // הפסקת המעקב אחרי האלמנט (אנימציה חד-פעמית)
                        observer.unobserve(element);
                    }
                });
            }, {
                threshold: 0.1, // 10% מהאלמנט בתצוגה
                rootMargin: '0px 0px -50px 0px' // הפעלה מעט לפני שהאלמנט מופיע במלואו
            });
            
            // מעקב אחרי כל האלמנטים המונפשים
            animatedElements.forEach(element => {
                observer.observe(element);
            });
        }
        
        // האזנה להודעות מהעורך (Editor)
        window.addEventListener('message', function(event) {
            // בדיקת מקור ההודעה
            if (event.origin !== window.location.origin) {
                return;
            }
            
            if (event.data.type === 'updateSections') {
                console.log('📨 Received updateSections message (store-front/home.php):', event.data.sections);
                updatePageSections(event.data.sections);
            } else if (event.data.type === 'updateButton') {
                console.log('📨 Received updateButton message:', event.data);
                updateButtonInDOM(event.data.buttonIndex, event.data.buttonData);
            } else if (event.data.type === 'deviceChanged') {
                console.log('📨 Received deviceChanged message:', event.data.device);
                updatePreviewDevice(event.data.device);
            }
        });
        
        console.log('🔧 Message listener registered (store-front/home.php)');
        
        /**
         * עדכון סקשנים בעמוד בזמן אמת
         */
        function updatePageSections(sections) {
            const mainElement = document.querySelector('main');
            if (!mainElement) return;
            
            console.log('🔧 DEBUG: Updating sections in real-time', sections);
            
            // ניקוי התוכן הקיים
            mainElement.innerHTML = '';
            
            // מחיקת style tags ישנים
            document.querySelectorAll('style[data-hero-section]').forEach(style => style.remove());
            
            // יצירת סקשנים חדשים
            sections.forEach(section => {
                console.log('🔧 DEBUG: Rendering section in real-time', section);
                
                if (section.type === 'hero') {
                    // שליחת בקשה לרינדור הירו
                    renderHeroSectionRealTime(section, mainElement);
                } else if (section.type === 'categories') {
                    // שליחת בקשה לרינדור קטגוריות
                    renderCategoriesSectionRealTime(section, mainElement);
                }
            });
            
            // אתחול אנימציות לסקשנים החדשים
            setTimeout(() => {
                initAnimations();
            }, 100);
        }
        
        /**
         * רינדור הירו בזמן אמת
         */
        async function renderHeroSectionRealTime(section, container) {
            try {
                // שליחת בקשה לטמפלט הירו
                const response = await fetch('../editor/sections/hero/template.php?render=1', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(section)
                });
                
                if (response.ok) {
                    const html = await response.text();
                    container.insertAdjacentHTML('beforeend', html);
                    console.log('✅ Hero section rendered in real-time');
                } else {
                    console.error('❌ Failed to render hero section:', response.status);
                    fallbackRenderHero(section, container);
                }
                
            } catch (error) {
                console.error('❌ Error rendering hero section:', error);
                fallbackRenderHero(section, container);
            }
        }
        
        /**
         * רינדור נוקשה של הירו אם הטמפלט לא זמין
         */
        function fallbackRenderHero(section, container) {
            const titleText = getNestedValue(section, 'content.title.text', 'כותרת');
            const subtitleText = getNestedValue(section, 'content.subtitle.text', 'תת-כותרת');
            const bgColor = getNestedValue(section, 'styles.desktop.background-color', '#3b82f6');
            const height = getNestedValue(section, 'styles.desktop.height', '100vh');
            
            const html = `
                <section style="
                    background-color: ${bgColor};
                    height: ${height};
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-family: 'Noto Sans Hebrew', sans-serif;
                    direction: rtl;
                ">
                    <div style="text-align: center; color: white;">
                        <h1 style="font-size: 48px; font-weight: bold; margin-bottom: 20px;">
                            ${titleText}
                        </h1>
                        <p style="font-size: 18px; color: #e5e7eb; margin-bottom: 30px;">
                            ${subtitleText}
                        </p>
                        <a href="#" style="
                            background-color: white;
                            color: #3b82f6;
                            padding: 12px 24px;
                            border-radius: 6px;
                            text-decoration: none;
                            font-weight: 500;
                        ">קנה עכשיו</a>
                    </div>
                </section>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
        }

        /**
         * רינדור קטגוריות בזמן אמת
         */
        async function renderCategoriesSectionRealTime(section, container) {
            try {
                // שליחת בקשה לטמפלט קטגוריות
                const response = await fetch('../editor/sections/categories/template.php?render=1', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(section)
                });
                
                if (response.ok) {
                    const html = await response.text();
                    container.insertAdjacentHTML('beforeend', html);
                    console.log('✅ Categories section rendered in real-time');
                } else {
                    console.error('❌ Failed to render categories section:', response.status);
                    fallbackRenderCategories(section, container);
                }
                
            } catch (error) {
                console.error('❌ Error rendering categories section:', error);
                fallbackRenderCategories(section, container);
            }
        }

        /**
         * רינדור נוקשה של קטגוריות אם הטמפלט לא זמין
         */
        function fallbackRenderCategories(section, container) {
            const titleText = getNestedValue(section, 'content.title.text', 'קטגוריות המוצרים שלנו');
            const subtitleText = getNestedValue(section, 'content.subtitle.text', 'בחרו מתוך מגוון רחב של קטגוריות');
            const bgColor = getNestedValue(section, 'styles.background-color', '#f9fafb');
            const categories = getNestedValue(section, 'content.grid.categories', []);
            
            const html = `
                <section style="
                    background-color: ${bgColor};
                    padding: 80px 20px;
                    font-family: 'Noto Sans Hebrew', sans-serif;
                    direction: rtl;
                ">
                    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
                        <h2 style="font-size: 36px; font-weight: bold; margin-bottom: 20px; color: #1f2937;">
                            ${titleText}
                        </h2>
                        <p style="font-size: 18px; color: #6b7280; margin-bottom: 48px;">
                            ${subtitleText.replace(/\n/g, '<br>')}
                        </p>
                        <div style="
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                            gap: 24px;
                        ">
                            ${categories.map(category => `
                                <a href="${category.url || '#'}" style="
                                    background: white;
                                    border-radius: 12px;
                                    overflow: hidden;
                                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                                    transition: all 0.3s ease;
                                    text-decoration: none;
                                    color: inherit;
                                ">
                                    ${category.image ? `<img src="${category.image}" alt="${category.name}" style="width: 100%; height: 200px; object-fit: cover;">` : ''}
                                    <div style="padding: 20px; font-size: 18px; font-weight: 600; text-align: center; color: #1f2937;">
                                        ${category.name}
                                    </div>
                                </a>
                            `).join('')}
                        </div>
                    </div>
                </section>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
        }
        
        /**
         * עדכון מיידי של כפתור ב-DOM ללא רינדור מלא
         */
        function updateButtonInDOM(buttonIndex, buttonData) {
            console.log(`🔘 Updating button ${buttonIndex} in DOM:`, buttonData);
            
            // מצא את הכפתור בDM
            const heroSection = document.querySelector('.hero-section');
            if (!heroSection) {
                console.warn('⚠️ Hero section not found for button update');
                return;
            }
            
            const buttons = heroSection.querySelectorAll('.hero-button');
            const targetButton = buttons[buttonIndex];
            
            if (!targetButton) {
                console.warn(`⚠️ Button ${buttonIndex} not found in DOM`);
                return;
            }
            
            try {
                // עדכון טקסט הכפתור
                if (buttonData.text) {
                    // שמור על אייקון אם קיים
                    const icon = targetButton.querySelector('i');
                    if (icon && buttonData.icon) {
                        // וידוא שהאייקון מתחיל ב-ri- (Remix Icons) או Font Awesome
                        let iconClass = buttonData.icon;
                        if (!iconClass.startsWith('ri-') && !iconClass.startsWith('fa-') && !iconClass.startsWith('fas ') && !iconClass.startsWith('fab ')) {
                            iconClass = 'ri-' + iconClass;
                        }
                        icon.className = iconClass + ' mr-2';
                    } else if (buttonData.icon && !icon) {
                        // הוסף אייקון חדש
                        let iconClass = buttonData.icon;
                        if (!iconClass.startsWith('ri-') && !iconClass.startsWith('fa-') && !iconClass.startsWith('fas ') && !iconClass.startsWith('fab ')) {
                            iconClass = 'ri-' + iconClass;
                        }
                        targetButton.innerHTML = `<i class="${iconClass} mr-2"></i>` + buttonData.text;
                    } else if (!buttonData.icon && icon) {
                        // הסר אייקון
                        targetButton.innerHTML = buttonData.text;
                    } else {
                        // רק טקסט
                        targetButton.textContent = buttonData.text;
                    }
                }
                
                // עדכון URL
                if (buttonData.url) {
                    targetButton.href = buttonData.url;
                }
                
                // עדכון סגנון הכפתור
                if (buttonData.styles || buttonData.style) {
                    const style = buttonData.style || 'solid';
                    const bgColor = buttonData.styles?.['background-color'] || '#3b82f6';
                    const textColor = buttonData.styles?.color || '#ffffff';
                    const borderRadius = buttonData.styles?.['border-radius'] || '6px';
                    
                    // הסר classes ישנים
                    targetButton.className = targetButton.className.replace(/button-\w+/g, '');
                    
                    // הוסף class חדש
                    targetButton.classList.add(`button-${style}`);
                    
                    // עדכון inline styles
                    applyButtonStyle(targetButton, style, bgColor, textColor, borderRadius);
                }
                
                console.log(`✅ Button ${buttonIndex} updated successfully`);
                
            } catch (error) {
                console.error(`❌ Error updating button ${buttonIndex}:`, error);
            }
        }
        
        /**
         * החלת סגנון על כפתור
         */
        function applyButtonStyle(button, style, bgColor, textColor, borderRadius) {
            const baseStyle = {
                padding: '12px 24px',
                borderRadius: borderRadius,
                fontWeight: '500',
                textDecoration: 'none',
                display: 'inline-flex',
                alignItems: 'center',
                justifyContent: 'center',
                cursor: 'pointer',
                transition: 'all 0.2s ease',
                border: '2px solid transparent'
            };
            
            let specificStyle = {};
            
            switch (style) {
                case 'solid':
                case 'primary':
                    specificStyle = {
                        backgroundColor: bgColor,
                        color: textColor,
                        borderColor: bgColor
                    };
                    break;
                case 'outline':
                case 'secondary':
                    specificStyle = {
                        backgroundColor: 'transparent',
                        color: bgColor,
                        borderColor: bgColor
                    };
                    break;
                case 'black':
                    specificStyle = {
                        backgroundColor: '#000000',
                        color: '#ffffff',
                        borderColor: '#000000'
                    };
                    break;
                case 'white':
                    specificStyle = {
                        backgroundColor: '#ffffff',
                        color: '#000000',
                        borderColor: '#ffffff'
                    };
                    break;
                case 'underline':
                    specificStyle = {
                        backgroundColor: 'transparent',
                        color: bgColor,
                        border: 'none',
                        borderBottom: '2px solid ' + bgColor,
                        borderRadius: '0'
                    };
                    break;
            }
            
            // החל את הסגנונות
            Object.assign(button.style, baseStyle, specificStyle);
        }
        
        /**
         * עדכון התצוגה המקדימה למכשיר חדש
         */
        function updatePreviewDevice(device) {
            console.log(`📱 Updating preview device to: ${device}`);
            
            // עדכן data attribute על הbody
            document.body.setAttribute('data-preview-device', device);
            
            // הוסף CSS class זמני להדגשת המעבר
            document.body.classList.add(`preview-${device}`);
            document.body.classList.remove(`preview-${device === 'desktop' ? 'mobile' : 'desktop'}`);
            
            // אפקט ויזואלי קל למעבר
            document.body.style.transition = 'all 0.3s ease';
            
            setTimeout(() => {
                document.body.style.transition = '';
                console.log(`✅ Preview updated to ${device} device`);
            }, 300);
        }
        
        /**
         * קבלת ערך מarray עמוק
         */
        function getNestedValue(obj, path, defaultValue) {
            if (!obj || !path) return defaultValue;
            
            const keys = path.split('.');
            let current = obj;
            
            for (const key of keys) {
                if (current && typeof current === 'object' && key in current) {
                    current = current[key];
                } else {
                    return defaultValue;
                }
            }
            
            return current !== undefined ? current : defaultValue;
        }
    </script>
</body>
</html> 