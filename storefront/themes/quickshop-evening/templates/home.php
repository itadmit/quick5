<?php
/**
 * Home Template - QuickShop Evening Theme
 * תבנית דף הבית עם סקשנים דינמיים
 */
?>

<style>
/* סגנון להדגשת סקשן בקסטומיזר */
.section-highlight {
    position: relative;
    animation: highlight-pulse 2s ease-in-out;
}

.section-highlight::before {
    content: '';
    position: absolute;
    top: -5px;
    left: -5px;
    right: -5px;
    bottom: -5px;
    background: rgba(59, 130, 246, 0.3);
    border: 2px solid #3b82f6;
    border-radius: 8px;
    z-index: 1000;
    pointer-events: none;
}

@keyframes highlight-pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}
</style>

<?php

// קבלת נתוני החנות
$store = $GLOBALS['CURRENT_STORE'] ?? null;

// טעינת הסקשנים באמצעות המערכת ההיברידית החדשה
require_once __DIR__ . '/../../../../includes/HybridPageManager.php';

$sections = [];

if ($store) {
    try {
        $db = Database::getInstance()->getConnection();
        $hybridManager = new HybridPageManager($db, $store['id']);
        $sections = $hybridManager->getHomePage();
        
    } catch (Exception $e) {
        error_log("Home template error: " . $e->getMessage());
    }
}

// אם אין סקשנים, השתמש בסקשנים ברירת מחדל
if (empty($sections)) {
    $sections = [
        [
            'id' => 'hero-default',
            'type' => 'hero',
            'settings' => [
                'title' => 'ברוכים הבאים לחנות שלנו',
                'subtitle' => 'גלה את המוצרים הטובים ביותר במחירים הכי טובים',
                'button_text' => 'קנה עכשיו',
                'button_link' => '/products',
                'background_color' => '#f8fafc',
                'text_color' => '#1f2937',
                'button_color' => '#3b82f6'
            ],
            'order' => 1,
            'visible' => true
        ],
        [
            'id' => 'featured-products',
            'type' => 'featured_products',
            'settings' => [
                'title' => 'המוצרים המומלצים שלנו',
                'products_count' => 8,
                'layout' => 'grid',
                'show_prices' => true
            ],
            'order' => 2,
            'visible' => true
        ]
    ];
}

// רנדור הסקשנים
foreach ($sections as $section) {
    $visible = $section['visible'] ?? $section['is_visible'] ?? true;
    if (!$visible) continue;
    
    // המרת שם הסקשן לשם הקובץ
    $sectionFileName = str_replace('-', '_', $section['type']);
    $sectionFile = __DIR__ . "/../sections/{$sectionFileName}.php";
    if (file_exists($sectionFile)) {
        // הגדרת משתנים לסקשן
        $sectionId = $section['id'];
        $sectionType = $section['type'];
        $sectionSettings = $section['settings'];
        
        // עטיפת הסקשן בתגיות מתאימות
        if ($section['type'] === 'header') {
            echo '<header class="site-header bg-white shadow-sm sticky top-0 z-40" role="banner" data-section="' . $section['id'] . '">';
            include $sectionFile;
            echo '</header>';
        } elseif ($section['type'] === 'footer') {
            echo '<footer class="site-footer bg-gray-900 text-white" role="contentinfo" data-section="' . $section['id'] . '">';
            include $sectionFile;
            echo '</footer>';
        } else {
            echo '<section class="theme-section" data-section="' . $section['id'] . '">';
            include $sectionFile;
            echo '</section>';
        }
    } else {
        // סקשן לא נמצא - הצג הודעת שגיאה בפיתוח
        if (defined('WP_DEBUG') && WP_DEBUG) {
            echo "<!-- Section file not found: {$section['type']}.php -->";
        }
    }
}
?>

<!-- Home Page JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('⚠️ DUPLICATE: quickshop-evening/templates/home.php loaded!');
    // מאזין לעדכונים מהקסטומיזר
    window.addEventListener('message', function(event) {
        if (event.data.type === 'update-sections') {
            // עדכון דינמי של הסקשנים
            updateSectionsFromCustomizer();
        } else if (event.data.type === 'updateSection') {
            // עדכון סקשן בודד
            updateSingleSection(event.data.section);
        } else if (event.data.type === 'scroll-to-section') {
            // גלילה לסקשן
            scrollToSectionById(event.data.sectionId);
        } else if (event.data.type === 'highlight-section') {
            // הדגשת סקשן
            highlightSectionById(event.data.sectionId);
        }
    });
    
    // פונקציה לגלילה לסקשן
    function scrollToSectionById(sectionId) {
        let targetElement = null;
        
        if (sectionId === 'header') {
            targetElement = document.querySelector('header') || document.querySelector('.header');
        } else if (sectionId === 'footer') {
            targetElement = document.querySelector('footer') || document.querySelector('.footer');
        } else {
            targetElement = document.querySelector(`[data-section="${sectionId}"]`) || 
                           document.querySelector(`#${sectionId}`) ||
                           document.querySelector(`.${sectionId}`);
        }
        
        if (targetElement) {
            targetElement.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
        }
    }
    
    // פונקציה להדגשת סקשן
    function highlightSectionById(sectionId) {
        // הסרת הדגשה קודמת
        document.querySelectorAll('.section-highlight').forEach(el => {
            el.classList.remove('section-highlight');
        });
        
        let targetElement = null;
        
        if (sectionId === 'header') {
            targetElement = document.querySelector('header') || document.querySelector('.header');
        } else if (sectionId === 'footer') {
            targetElement = document.querySelector('footer') || document.querySelector('.footer');
        } else {
            targetElement = document.querySelector(`[data-section="${sectionId}"]`) || 
                           document.querySelector(`#${sectionId}`) ||
                           document.querySelector(`.${sectionId}`);
        }
        
        if (targetElement) {
            targetElement.classList.add('section-highlight');
            
            // הסרת ההדגשה אחרי 3 שניות
            setTimeout(() => {
                targetElement.classList.remove('section-highlight');
            }, 3000);
        }
    }
    
         // פונקציה לעדכון הסקשנים ללא רענון
     async function updateSectionsFromCustomizer() {
         try {
             // במקום לקרוא מה-API, נשתמש בהודעות PostMessage
             // כי יש בעיית CORS כשהתצוגה המקדימה באיפריים
             console.log('Sections updated from customizer');
             // כרגע פשוט נרענן את הדף
             location.reload();
         } catch (error) {
             console.error('Failed to update sections:', error);
             // אם נכשל, נרענן את הדף
             location.reload();
         }
     }
    
    // פונקציה לעדכון הסקשנים בדף
    function updatePageSections(sections) {
        // כאן נעדכן כל סקשן בנפרד
        sections.forEach(section => {
            updateSectionContent(section);
        });
    }
    
    // פונקציה לעדכון תוכן סקשן ספציפי
    function updateSectionContent(section) {
        const sectionElement = document.querySelector(`[data-section="${section.id}"]`);
        if (!sectionElement) return;
        
        // עדכון לפי סוג הסקשן
        switch (section.type) {
            case 'hero':
                updateHeroSection(sectionElement, section.settings);
                break;
            case 'featured_products':
                updateFeaturedProductsSection(sectionElement, section.settings);
                break;
            case 'header':
                updateHeaderSection(sectionElement, section.settings);
                break;
            case 'footer':
                updateFooterSection(sectionElement, section.settings);
                break;
        }
    }
    
    // עדכון סקשן Hero
    function updateHeroSection(element, settings) {
        const titleElement = element.querySelector('.hero-title');
        const descriptionElement = element.querySelector('.hero-description');
        const buttonElement = element.querySelector('.hero-button');
        
        if (titleElement && settings.title) {
            titleElement.textContent = settings.title;
        }
        if (descriptionElement && settings.description) {
            descriptionElement.textContent = settings.description;
        }
        if (buttonElement && settings.button_text) {
            buttonElement.textContent = settings.button_text;
        }
        
        // עדכון צבעים
        if (settings.bg_color) {
            element.style.backgroundColor = settings.bg_color;
        }
        if (settings.text_color) {
            element.style.color = settings.text_color;
        }
    }
    
    // עדכון סקשן מוצרים מומלצים
    function updateFeaturedProductsSection(element, settings) {
        const titleElement = element.querySelector('.section-title');
        if (titleElement && settings.title) {
            titleElement.textContent = settings.title;
        }
        
        // עדכון מספר עמודות
        const gridElement = element.querySelector('.products-grid');
        if (gridElement && settings.columns) {
            // הסרת כל הקלאסים של עמודות
            gridElement.classList.remove('grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3', 'lg:grid-cols-4', 'xl:grid-cols-5', 'xl:grid-cols-6');
            
            // הוספת הקלאסים החדשים
            const columnClasses = {
                2: 'grid-cols-1 md:grid-cols-2',
                3: 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
                4: 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
                5: 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5',
                6: 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6'
            };
            
            const newClasses = columnClasses[settings.columns] || columnClasses[3];
            newClasses.split(' ').forEach(cls => gridElement.classList.add(cls));
        }
    }
    
    // עדכון Header
    function updateHeaderSection(element, settings) {
        const storeNameElement = element.querySelector('.store-name');
        if (storeNameElement && settings.store_name) {
            storeNameElement.textContent = settings.store_name;
        }
        
        // עדכון הצגת חיפוש
        const searchElement = element.querySelector('.search-container');
        if (searchElement) {
            searchElement.style.display = settings.show_search ? 'block' : 'none';
        }
        
        // עדכון הצגת עגלת קניות
        const cartElement = element.querySelector('.cart-container');
        if (cartElement) {
            cartElement.style.display = settings.show_cart ? 'block' : 'none';
        }
    }
    
    // עדכון Footer
    function updateFooterSection(element, settings) {
        const copyrightElement = element.querySelector('.copyright-text');
        if (copyrightElement && settings.copyright) {
            copyrightElement.textContent = settings.copyright;
        }
        
        // עדכון הצגת קישורים חברתיים
        const socialElement = element.querySelector('.social-links');
        if (socialElement) {
            socialElement.style.display = settings.show_social ? 'block' : 'none';
        }
        
        // עדכון הצגת מידע יצירת קשר
        const contactElement = element.querySelector('.contact-info');
        if (contactElement) {
            contactElement.style.display = settings.show_contact ? 'block' : 'none';
        }
    }

    // Add to cart functionality
    window.addToCart = function(productId, quantity = 1) {
        // Show loading state
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="ri-loader-4-line animate-spin"></i>';
        button.disabled = true;
        
        // Simulate add to cart (replace with actual API call)
        setTimeout(() => {
            // Update cart count
            const currentCount = parseInt(localStorage.getItem('cart_count') || '0');
            const newCount = currentCount + quantity;
            localStorage.setItem('cart_count', newCount);
            
            // Dispatch cart updated event
            window.dispatchEvent(new CustomEvent('cartUpdated'));
            
            // Show success state
            button.innerHTML = '<i class="ri-check-line"></i> נוסף לסל';
            button.classList.add('bg-green-600');
            
            // Reset after 2 seconds
            setTimeout(() => {
                button.innerHTML = originalContent;
                button.classList.remove('bg-green-600');
                button.disabled = false;
            }, 2000);
        }, 1000);
    };
    
    // Smooth scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
            }
        });
    }, observerOptions);
    
    // Observe all sections
    document.querySelectorAll('section').forEach(section => {
        observer.observe(section);
    });
    
    // פונקציה לעדכון סקשן בודד
    function updateSingleSection(sectionData) {
        console.log('Updating single section:', sectionData);
        
        // מצא את הסקשן בדף
        const sectionElement = document.querySelector(`[data-section="${sectionData.id}"]`);
        if (!sectionElement) {
            console.log('Section element not found:', sectionData.id);
            return;
        }
        
        // עדכון הסקשן לפי הסוג
        if (sectionData.type === 'hero') {
            updateHeroSection(sectionElement, sectionData.settings);
        } else if (sectionData.type === 'featured-products') {
            updateFeaturedProductsSection(sectionElement, sectionData.settings);
        }
    }
    
    // עדכון סקשן Hero
    function updateHeroSection(element, settings) {
        console.log('Updating hero section:', settings);
        
        // עדכון הכותרת
        const title = element.querySelector('h1');
        if (title && settings.title) {
            title.textContent = settings.title;
            if (settings.title_color) title.style.color = settings.title_color;
            if (settings.title_size) title.style.fontSize = settings.title_size + 'px';
        }
        
        // עדכון תת הכותרת
        const subtitle = element.querySelector('h2');
        if (subtitle) {
            if (settings.subtitle || settings.description) {
                subtitle.textContent = settings.subtitle || settings.description;
                if (settings.subtitle_color) subtitle.style.color = settings.subtitle_color;
                if (settings.subtitle_size) subtitle.style.fontSize = settings.subtitle_size + 'px';
            }
        }
        
        // עדכון תוכן
        const content = element.querySelector('.mb-8');
        if (content && settings.content) {
            content.innerHTML = settings.content.replace(/\n/g, '<br>');
            if (settings.content_color) content.style.color = settings.content_color;
            if (settings.content_size) content.style.fontSize = settings.content_size + 'px';
        }
        
        // עדכון הרקע
        updateHeroBackground(element, settings);
        
        // עדכון כפתורים
        const buttons = element.querySelectorAll('.hero-button');
        buttons.forEach(button => {
            if (settings.button_text) {
                button.textContent = settings.button_text;
            }
            if (settings.button_bg_color || settings.button_color) {
                const btnColor = settings.button_bg_color || settings.button_color;
                button.style.backgroundColor = btnColor;
            }
            if (settings.button_text_color) {
                button.style.color = settings.button_text_color;
            }
            if (settings.button_link) {
                button.href = settings.button_link;
            }
        });
        
        // עדכון מרווחים
        updateElementSpacing(element, settings);
    }
    
    // פונקציה לעדכון רקע הירו
    function updateHeroBackground(element, settings) {
        // איפוס הרקע
        element.style.background = '';
        element.style.backgroundColor = '';
        
        switch (settings.bg_type) {
            case 'color':
                element.style.backgroundColor = settings.bg_color;
                break;
            case 'gradient':
                const direction = settings.gradient_direction || 'to bottom';
                const start = settings.gradient_start || settings.bg_color;
                const end = settings.gradient_end || '#3b82f6';
                element.style.background = `linear-gradient(${direction}, ${start}, ${end})`;
                break;
            case 'image':
                if (settings.bg_image) {
                    const size = settings.bg_image_size || 'cover';
                    element.style.background = `url('${settings.bg_image}') center/${size} no-repeat`;
                }
                break;
            case 'video':
                // נטפל בסרטון בנפרד
                element.style.backgroundColor = '#000000';
                break;
            default:
                element.style.backgroundColor = settings.bg_color || '#1e40af';
        }
    }
    
    // פונקציה לעדכון מרווחים
    function updateElementSpacing(element, settings) {
        if (settings.padding_top) element.style.paddingTop = settings.padding_top + 'px';
        if (settings.padding_bottom) element.style.paddingBottom = settings.padding_bottom + 'px';
        if (settings.padding_left) element.style.paddingLeft = settings.padding_left + 'px';
        if (settings.padding_right) element.style.paddingRight = settings.padding_right + 'px';
        
        if (settings.margin_top) element.style.marginTop = settings.margin_top + 'px';
        if (settings.margin_bottom) element.style.marginBottom = settings.margin_bottom + 'px';
        if (settings.margin_left) element.style.marginLeft = settings.margin_left + 'px';
        if (settings.margin_right) element.style.marginRight = settings.margin_right + 'px';
    }
    
    // עדכון סקשן מוצרים מומלצים
    function updateFeaturedProductsSection(element, settings) {
        console.log('Updating featured products section:', settings);
        
        // עדכון הכותרת
        const title = element.querySelector('h2');
        if (title && settings.title) {
            title.textContent = settings.title;
        }
    }
});
</script>

<!-- Add CSS for animations -->
<style>
.animate-fade-in {
    animation: fadeIn 0.6s ease-out forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style> 