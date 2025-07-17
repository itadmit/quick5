<?php
/**
 * QuickShop Evening Theme - Main File
 * כמו theme.liquid בשופיפיי - קובץ ראשי לכל התבנית
 * 
 * @package QuickShop
 * @subpackage Themes
 * @version 1.0.0
 */

// קבלת נתוני החנות והעמוד
$store = $GLOBALS['CURRENT_STORE'] ?? null;
$pageData = $GLOBALS['PAGE_DATA'] ?? [];
$themeSettings = $GLOBALS['THEME_SETTINGS'] ?? [];

// טעינת הגדרות התבנית
$themeConfigPath = __DIR__ . '/theme.json';
$themeConfig = file_exists($themeConfigPath) ? 
    json_decode(file_get_contents($themeConfigPath), true) : [];

// מיזוג הגדרות ברירת מחדל עם הגדרות החנות
$defaultSettings = [];
if (isset($themeConfig['settings'])) {
    foreach ($themeConfig['settings'] as $category => $settings) {
        foreach ($settings as $key => $setting) {
            $defaultSettings[$key] = $setting['default'] ?? '';
        }
    }
}

$themeSettings = array_merge($defaultSettings, $themeSettings);

// הגדרת משתנים לתבנית
$pageTitle = $pageData['title'] ?? $store['name'] ?? 'QuickShop';
$pageDescription = $pageData['description'] ?? $store['description'] ?? '';
$bodyClass = $pageData['body_class'] ?? '';
$currentTemplate = $pageData['template'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="robots" content="index, follow">
    <meta name="author" content="<?= htmlspecialchars($store['name'] ?? 'QuickShop') ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">
    <?php if (!empty($store['logo'])): ?>
    <meta property="og:image" content="<?= htmlspecialchars($store['logo']) ?>">
    <?php endif; ?>
    
    <!-- Favicon -->
    <?php if (!empty($store['favicon'])): ?>
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($store['favicon']) ?>">
    <?php endif; ?>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // הסתרת אזהרות Tailwind CSS
        if (typeof tailwind !== 'undefined') {
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            'sans': ['<?= $themeSettings['font_family'] ?? 'Noto Sans Hebrew' ?>', 'system-ui', 'sans-serif'],
                        },
                        colors: {
                            'primary': '<?= $themeSettings['primary_color'] ?? '#3B82F6' ?>',
                            'secondary': '<?= $themeSettings['secondary_color'] ?? '#1E40AF' ?>',
                            'accent': '<?= $themeSettings['accent_color'] ?? '#F59E0B' ?>',
                        },
                        fontSize: {
                            'base': '<?= $themeSettings['font_size_base'] ?? '16px' ?>',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Noto Sans Hebrew Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Hebrew:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    

    
    <!-- Custom Theme Styles -->
    <style>
        :root {
            --color-primary: <?= $themeSettings['primary_color'] ?? '#3B82F6' ?>;
            --color-secondary: <?= $themeSettings['secondary_color'] ?? '#1E40AF' ?>;
            --color-accent: <?= $themeSettings['accent_color'] ?? '#F59E0B' ?>;
            --font-family: '<?= $themeSettings['font_family'] ?? 'Noto Sans Hebrew' ?>', sans-serif;
            --font-size-base: <?= $themeSettings['font_size_base'] ?? '16px' ?>;
        }
        
        body { 
            font-family: var(--font-family);
            font-size: <?= $themeSettings['font_size_base'] ?? '16px' ?>;
            color: <?= $themeSettings['text_color'] ?? '#1F2937' ?>;
            background-color: <?= $themeSettings['background_color'] ?? '#FFFFFF' ?>;
        }
        
        :root {
            --primary-color: <?= $themeSettings['primary_color'] ?? '#3B82F6' ?>;
            --secondary-color: <?= $themeSettings['secondary_color'] ?? '#1E40AF' ?>;
            --accent-color: <?= $themeSettings['accent_color'] ?? '#F59E0B' ?>;
            --text-color: <?= $themeSettings['text_color'] ?? '#1F2937' ?>;
            --background-color: <?= $themeSettings['background_color'] ?? '#FFFFFF' ?>;
            --container-width: <?= $themeSettings['container_width'] ?? '1200px' ?>;
        }
        
        .container-custom {
            max-width: var(--container-width);
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }
        
        /* Custom CSS from theme settings */
        <?= $themeSettings['custom_css'] ?? '' ?>
    </style>
    
    <!-- Google Tag Manager -->
    <?php if (!empty($themeSettings['google_tag_manager'])): ?>
    <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','<?= $themeSettings['google_tag_manager'] ?>');
    </script>
    <?php endif; ?>
    
    <!-- Google Analytics -->
    <?php if (!empty($themeSettings['google_analytics'])): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $themeSettings['google_analytics'] ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= $themeSettings['google_analytics'] ?>');
    </script>
    <?php endif; ?>
    
    <!-- Facebook Pixel -->
    <?php if (!empty($themeSettings['facebook_pixel'])): ?>
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?= $themeSettings['facebook_pixel'] ?>');
        fbq('track', 'PageView');
    </script>
    <noscript>
        <img height="1" width="1" style="display:none" 
             src="https://www.facebook.com/tr?id=<?= $themeSettings['facebook_pixel'] ?>&ev=PageView&noscript=1"/>
    </noscript>
    <?php endif; ?>
    
    <!-- Custom Head Code -->
    <?= $themeSettings['custom_head_code'] ?? '' ?>
</head>
<body class="bg-gray-50 font-sans antialiased <?= $bodyClass ?>">
    
    <!-- Google Tag Manager (noscript) -->
    <?php if (!empty($themeSettings['google_tag_manager'])): ?>
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=<?= $themeSettings['google_tag_manager'] ?>"
                height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <?php endif; ?>
    
    <!-- Custom Body Start Code -->
    <?= $themeSettings['custom_body_start_code'] ?? '' ?>
    
    <!-- Skip to content (accessibility) -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-primary text-white px-4 py-2 rounded-lg z-50">
        דלג לתוכן הראשי
    </a>
    
    <!-- Main Content -->
    <main id="main-content" class="site-main min-h-screen" role="main">
        <?php 
        // טעינת התוכן הראשי לפי העמוד
        $contentFile = __DIR__ . '/templates/' . $currentTemplate . '.php';
        if (file_exists($contentFile)) {
            include $contentFile;
        } else {
            // נסה לטעון תבנית ברירת מחדל
            $defaultTemplate = __DIR__ . '/templates/404.php';
            if (file_exists($defaultTemplate)) {
                include $defaultTemplate;
            } else {
                echo '<div class="container mx-auto px-4 py-16 text-center">';
                echo '<h1 class="text-4xl font-bold text-gray-900 mb-4">404</h1>';
                echo '<p class="text-gray-600">התבנית לא נמצאה</p>';
                echo '</div>';
            }
        }
        ?>
    </main>
    
    <!-- Theme JavaScript -->
    <script>
        // Theme configuration for JavaScript
        window.themeConfig = {
            colors: {
                primary: '<?= $themeSettings['primary_color'] ?? '#3B82F6' ?>',
                secondary: '<?= $themeSettings['secondary_color'] ?? '#1E40AF' ?>',
                accent: '<?= $themeSettings['accent_color'] ?? '#F59E0B' ?>'
            },
            store: {
                id: <?= $store['id'] ?? 0 ?>,
                name: '<?= htmlspecialchars($store['name'] ?? '') ?>',
                currency: '<?= $store['currency'] ?? 'ILS' ?>'
            }
        };
        
        <?php if (isset($pageData['is_preview']) && $pageData['is_preview']): ?>
        // Customizer preview mode
        window.addEventListener('message', function(event) {
            if (event.data.type === 'customizer-update') {
                // Update theme settings in real-time
                updateThemeSettings(event.data.setting, event.data.value);
            } else if (event.data.type === 'customizer-scroll') {
                // Scroll to specific section
                const element = document.querySelector(event.data.selector);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
        
        function updateThemeSettings(setting, value) {
            const root = document.documentElement;
            
            // Update CSS variables based on setting
            switch(setting) {
                case 'primary_color':
                    root.style.setProperty('--color-primary', value);
                    break;
                case 'secondary_color':
                    root.style.setProperty('--color-secondary', value);
                    break;
                case 'accent_color':
                    root.style.setProperty('--color-accent', value);
                    break;
                case 'font_family':
                    root.style.setProperty('--font-family', value);
                    break;
                case 'font_size_base':
                    root.style.setProperty('--font-size-base', value);
                    break;
                // Add more settings as needed
            }
        }
        <?php endif; ?>
    </script>
    
    <!-- Theme Scripts -->
    <?php if (file_exists(__DIR__ . '/assets/js/theme.js')): ?>
    <script src="<?= $themeConfig['assets']['js'] ?? '/storefront/themes/quickshop-evening/assets/js/theme.js' ?>"></script>
    <?php endif; ?>
    
    <!-- Custom Footer Code -->
    <?= $themeSettings['custom_footer_code'] ?? '' ?>
    
    <!-- Custom Body End Code -->
    <?= $themeSettings['custom_body_end_code'] ?? '' ?>
</body>
</html> 