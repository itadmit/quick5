<?php
/**
 * Hero Section Template
 * מתרגם JSON לHTML עם inline styles responsive - WYSIWYG
 */

// טיפול בבקשות AJAX מהעורך
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['render'])) {
    // קריאת נתוני JSON מהבקשה
    $input = file_get_contents('php://input');
    $section = json_decode($input, true);
    
    if ($section) {
        // רינדור ויציאה
        echo renderHeroSection($section);
        exit;
    }
}

function renderHeroSection($section) {
    // ברירות מחדל פשוטות
    $defaults = [
        'id' => 'hero_' . uniqid(),
        'type' => 'hero',
        'styles' => [
            'background-type' => 'gradient',
            'gradient-color1' => '#3b82f6',
            'gradient-color2' => '#1e40af',
            'gradient-direction' => 'to bottom',
            'height' => '100vh',
            'padding-top' => '60px',
            'padding-bottom' => '60px'
        ],
        'content' => [
            'title' => [
                'text' => 'ברוכים הבאים לחנות שלנו',
                'tag' => 'h1', // HTML tag לכותרת
                'styles' => [
                    'desktop' => [
                        'font-size' => '48px',
                        'font-weight' => 'bold',
                        'color' => '#FFFFFF',
                        'text-align' => 'center',
                        'line-height' => '1.2'
                    ],
                    'mobile' => [
                        'font-size' => '32px',
                        'font-weight' => 'bold',
                        'color' => '#FFFFFF',
                        'text-align' => 'center',
                        'line-height' => '1.2'
                    ]
                ]
            ],
            'subtitle' => [
                'text' => "גלו את המוצרים הטובים ביותר\nבמחירים הטובים ביותר\nאצלנו בחנות!",
                'tag' => 'p', // HTML tag לתת-כותרת
                'styles' => [
                    'desktop' => [
                        'font-size' => '18px',
                        'color' => '#E5E7EB',
                        'text-align' => 'center',
                        'line-height' => '1.6'
                    ],
                    'mobile' => [
                        'font-size' => '16px',
                        'color' => '#E5E7EB',
                        'text-align' => 'center',
                        'line-height' => '1.6'
                    ]
                ]
            ],
            'buttons' => []
        ]
    ];
    
    // מיזוג עם ברירות מחדל - עמוק
    $data = mergeArraysDeep($defaults, $section);
    
    // ווידוא שה-id הוא string
    if (!isset($data['id']) || !is_string($data['id'])) {
        $data['id'] = 'hero_' . uniqid();
    }
    
    // זיהוי אם זה builder או front - שיפור זיהוי
    $isBuilder = isset($_GET['preview']) || isset($_GET['builder']) || 
                 (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '/editor/') !== false) ||
                 (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'preview=1') !== false) ||
                 (isset($_SERVER['HTTP_HOST']) && isset($_GET['store']));
    
    // חישוב ID סופי לפני יצירת CSS
    $customId = getNestedValue($data, 'attributes.id', '');
    $finalId = !empty($customId) ? htmlspecialchars($customId) : htmlspecialchars($data['id']);
    
    // יצירת CSS responsive עם ה-ID הסופי
    $css = generateResponsiveCSS($data, $isBuilder, $finalId);
    
    // יצירת HTML עם מאפייני HTML מותאמים
    $html = '<style>' . $css . '</style>';
    
    // בניית מאפייני HTML
    $htmlAttributes = '';
    
    // ID - כבר מחושב למעלה
    $htmlAttributes .= ' id="' . $finalId . '"';
    
    // Class - צירוף class ברירת מחדל עם classes מותאמים
    $baseClass = 'hero-section';
    $customClasses = getNestedValue($data, 'attributes.class', '');
    $finalClasses = $baseClass;
    
    // הוסף builder-hidden class אם האלמנט מוסתר בbuilder
    if ($isBuilder) {
        $visibility = getNestedValue($data, 'visibility', []);
        $hideDesktop = getNestedValue($visibility, 'hide-desktop', false);
        $hideMobile = getNestedValue($visibility, 'hide-mobile', false);
        
        if ($hideDesktop || $hideMobile) {
            $finalClasses .= ' builder-hidden';
        }
    }
    
    if (!empty($customClasses)) {
        $finalClasses .= ' ' . htmlspecialchars($customClasses);
    }
    $htmlAttributes .= ' class="' . $finalClasses . '"';
    
    // Animation - אנימציה בכניסה
    $animation = getNestedValue($data, 'styles.animation', 'none');
    if (!empty($animation) && $animation !== 'none') {
        $htmlAttributes .= ' data-animation="' . htmlspecialchars($animation) . '"';
    }
    
    $html .= '<section' . $htmlAttributes . '>';
    
    // הוספת וידאו רקע אם נדרש
    $html .= generateVideoBackground($data, $finalId);
    
    $html .= '<div class="hero-container">';
    $html .= '<div class="hero-content">';
    
    // כותרת
    $titleText = getNestedValue($data, 'content.title.text', '');
    if (!empty($titleText) && is_string($titleText)) {
        $titleTag = getNestedValue($data, 'content.title.tag', 'h1'); // ברירת מחדל h1
        $html .= '<' . htmlspecialchars($titleTag) . ' class="hero-title">' . htmlspecialchars($titleText) . '</' . htmlspecialchars($titleTag) . '>';
    }
    
    // תת-כותרת
    $subtitleText = getNestedValue($data, 'content.subtitle.text', '');
    if (!empty($subtitleText) && is_string($subtitleText)) {
        $subtitleTag = getNestedValue($data, 'content.subtitle.tag', 'p'); // ברירת מחדל p
        // המרת ירידות שורה ל-br tags
        $formattedSubtitle = nl2br(htmlspecialchars($subtitleText));
        $html .= '<' . htmlspecialchars($subtitleTag) . ' class="hero-subtitle">' . $formattedSubtitle . '</' . htmlspecialchars($subtitleTag) . '>';
    }
    
    // כפתורים
    $buttons = getNestedValue($data, 'content.buttons', []);
    if (!empty($buttons) && is_array($buttons)) {
        $html .= '<div class="hero-buttons">';
        foreach ($buttons as $index => $button) {
            if (is_array($button) && !empty($button['text']) && is_string($button['text'])) {
                $buttonStyle = generateButtonStyle($button);
                $buttonClass = generateButtonClass($button);
                $url = isset($button['url']) && is_string($button['url']) ? $button['url'] : '#';
                $icon = isset($button['icon']) && is_string($button['icon']) ? $button['icon'] : '';
                
                $html .= '<a href="' . htmlspecialchars($url) . '" style="' . $buttonStyle . '" class="hero-button ' . $buttonClass . '">';
                
                // אייקון לפני הטקסט
                if (!empty($icon)) {
                    // וידוא שהאייקון מתחיל ב-ri- (Remix Icons)
                    $iconClass = $icon;
                    
                    // תמיכה בגרסאות PHP ישנות
                    if (function_exists('str_starts_with')) {
                        if (!str_starts_with($icon, 'ri-') && !str_starts_with($icon, 'fa-') && !str_starts_with($icon, 'fas ') && !str_starts_with($icon, 'fab ')) {
                            $iconClass = 'ri-' . $icon;
                        }
                    } else {
                        // fallback לגרסאות PHP ישנות
                        if (substr($icon, 0, 3) !== 'ri-' && substr($icon, 0, 3) !== 'fa-' && substr($icon, 0, 4) !== 'fas ' && substr($icon, 0, 4) !== 'fab ') {
                            $iconClass = 'ri-' . $icon;
                        }
                    }
                    $html .= '<i class="' . htmlspecialchars($iconClass) . ' mr-2"></i>';
                }
                
                $html .= htmlspecialchars($button['text']);
                $html .= '</a>';
            }
        }
        $html .= '</div>';
    }
    
    $html .= '</div>'; // hero-content
    $html .= '</div>'; // hero-container
    
    // הוספת CSS לhover effects של כפתורים
    $html .= '<style>';
    $html .= '.hero-button { text-decoration: none !important; }';
    $html .= '.hero-button:hover { transform: translateY(-2px); }';
    $html .= '.hero-button.button-solid:hover { opacity: 0.9; }';
    $html .= '.hero-button.button-outline:hover { background-color: ' . (isset($button['styles']['background-color']) ? $button['styles']['background-color'] : '#3b82f6') . '; color: #ffffff; }';
    $html .= '.hero-button.button-black:hover { background-color: #333333; }';
    $html .= '.hero-button.button-white:hover { background-color: #f5f5f5; }';
    $html .= '.hero-button.button-underline:hover { border-bottom-width: 3px; }';
    $html .= '</style>';
    
    $html .= '</section>';
    
    return $html;
}

/**
 * מיזוג עמוק של arrays
 */
function mergeArraysDeep($array1, $array2) {
    $merged = $array1;
    
    foreach ($array2 as $key => $value) {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
            $merged[$key] = mergeArraysDeep($merged[$key], $value);
        } else {
            $merged[$key] = $value;
        }
    }
    
    return $merged;
}

/**
 * קבלת ערך מtested מתוך array עמוק
 */
function getNestedValue($array, $path, $default = null) {
    $keys = explode('.', $path);
    $current = $array;
    
    foreach ($keys as $key) {
        if (!is_array($current) || !isset($current[$key])) {
            return $default;
        }
        $current = $current[$key];
    }
    
    return $current;
}

/**
 * פרסר ערכי גובה - תמיכה בפורמט חדש וישן
 */
function parseHeightValue($heightData) {
    // אם זה ריק, החזר ריק
    if (empty($heightData)) {
        return '';
    }
    
    // אם זה string - פורמט ישן (backwards compatibility)
    if (is_string($heightData)) {
        return $heightData;
    }
    
    // אם זה array - פורמט חדש {value: "100", unit: "vh"}
    if (is_array($heightData)) {
        $value = isset($heightData['value']) ? $heightData['value'] : '';
        $unit = isset($heightData['unit']) ? $heightData['unit'] : 'vh';
        
        // אם יחידה היא auto, החזר auto
        if ($unit === 'auto') {
            return 'auto';
        }
        
        // אם יש ערך, חבר value+unit
        if (!empty($value) && is_numeric($value)) {
            return $value . $unit;
        }
    }
    
    // ברירת מחדל
    return '';
}

/**
 * יצירת CSS responsive
 */
function generateResponsiveCSS($data, $isBuilder = false, $finalId = null) {
    // ווידוא שיש ID תקין
    if (empty($finalId)) {
        if (!isset($data['id']) || !is_string($data['id'])) {
            return '';
        }
        $finalId = $data['id'];
    }
    
    $sectionId = '#' . str_replace(['<', '>', '"', "'"], '', $finalId);
    $css = '';
    
    // קבלת הגדרות מידות
    $styles = getNestedValue($data, 'styles', []);
    $sectionWidth = getNestedValue($styles, 'section-width', 'container');
    $maxWidth = getNestedValue($styles, 'max-width', '1200px');
    $minHeight = getNestedValue($styles, 'min-height', '');
    $maxHeight = getNestedValue($styles, 'max-height', '');
    
    // עיצוב בסיסי
    $css .= $sectionId . ' {';
    $css .= 'font-family: "Noto Sans Hebrew", sans-serif;';
    $css .= 'direction: rtl;';
    $css .= 'position: relative;';
    $css .= 'display: flex;';
    $css .= 'align-items: center;';
    $css .= 'justify-content: center;';
    
    // הוספת min-height ו-max-height אם הוגדרו
    if (!empty($minHeight)) {
        $css .= 'min-height: ' . htmlspecialchars($minHeight) . ';';
    }
    if (!empty($maxHeight)) {
        $css .= 'max-height: ' . htmlspecialchars($maxHeight) . ';';
    }
    
    $css .= '}';
    
    // עיצוב הקונטיינר לפי סוג הרוחב
    $css .= $sectionId . ' .hero-container {';
    $css .= 'width: 100%;';
    
    if ($sectionWidth === 'full') {
        // רוחב מלא - ללא הגבלה
        $css .= 'max-width: none;';
        $css .= 'padding: 0;';
    } elseif ($sectionWidth === 'custom' && !empty($maxWidth)) {
        // רוחב מותאם אישית
        $css .= 'max-width: ' . htmlspecialchars($maxWidth) . ';';
        $css .= 'margin: 0 auto;';
        $css .= 'padding: 0 20px;';
    } else {
        // ברירת מחדל - קונטיינר מוגבל
        $css .= 'max-width: 1200px;';
        $css .= 'margin: 0 auto;';
        $css .= 'padding: 0 20px;';
    }
    
    $css .= 'position: relative;';
    $css .= 'z-index: 10;';
    $css .= '}';
    
    $css .= $sectionId . ' .hero-content {';
    $css .= 'text-align: center;';
    $css .= 'width: 100%;';
    $css .= 'position: relative;';
    $css .= 'z-index: 10;';
    $css .= '}';
    
    $css .= $sectionId . ' .hero-title {';
    $css .= 'margin: 0 0 20px 0;';
    $css .= '}';
    
    $css .= $sectionId . ' .hero-subtitle {';
    $css .= 'margin: 0 0 30px 0;';
    $css .= '}';
    
    $css .= $sectionId . ' .hero-buttons {';
    $css .= 'display: flex;';
    $css .= 'gap: 15px;';
    $css .= 'justify-content: center;';
    $css .= 'flex-wrap: wrap;';
    $css .= '}';
    
    // Desktop styles
    $css .= '@media (min-width: 769px) {';
    
    // Section styles - desktop
    $styles = getNestedValue($data, 'styles', []);
    if (!empty($styles) && is_array($styles)) {
        $css .= $sectionId . ' {';
        $css .= generateSimpleBackgroundCSS($styles, $sectionId, false); // desktop
        
        // הוסף מרווחים
        $css .= generateSpacingCSS($styles);
        
        // גובה responsive - desktop
        $desktopHeight = getNestedValue($data, 'styles.height.desktop', '');
        $heightValue = parseHeightValue($desktopHeight);
        if (!empty($heightValue)) {
            $css .= 'height: ' . htmlspecialchars($heightValue) . ';';
        }
        
        foreach ($styles as $prop => $value) {
            if (is_string($prop) && (is_string($value) || is_numeric($value))) {
                // Skip background and spacing properties - they are handled separately
                if (!in_array($prop, ['background-type', 'background-color', 'background-image', 'background-image-mobile', 'background-size', 'background-repeat', 'background-video', 'background-video-mobile', 'gradient-color1', 'gradient-color2', 'gradient-direction', 'image-overlay-opacity', 'video-overlay-opacity', 'video-muted', 'video-loop', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left'])) {
                    $css .= convertPropertyToCSS($prop) . ': ' . $value . ';';
                }
            }
        }
        $css .= '}';
    }
    
    // Title styles - desktop
    $desktopTitleStyles = getNestedValue($data, 'content.title.styles.desktop', []);
    if (!empty($desktopTitleStyles) && is_array($desktopTitleStyles)) {
        $css .= $sectionId . ' .hero-title {';
        foreach ($desktopTitleStyles as $prop => $value) {
            if (is_string($prop) && (is_string($value) || is_numeric($value))) {
                $css .= convertPropertyToCSS($prop) . ': ' . $value . ';';
            }
        }
        $css .= '}';
    }
    
    // Subtitle styles - desktop
    $desktopSubtitleStyles = getNestedValue($data, 'content.subtitle.styles.desktop', []);
    if (!empty($desktopSubtitleStyles) && is_array($desktopSubtitleStyles)) {
        $css .= $sectionId . ' .hero-subtitle {';
        foreach ($desktopSubtitleStyles as $prop => $value) {
            if (is_string($prop) && (is_string($value) || is_numeric($value))) {
                $css .= convertPropertyToCSS($prop) . ': ' . $value . ';';
            }
        }
        $css .= '}';
    }
    
    $css .= '}'; // End desktop media query
    
    // Mobile styles
    $css .= '@media (max-width: 768px) {';
    
    // Section styles - mobile (same styles but mobile-specific images/videos if available)
    if (!empty($styles) && is_array($styles)) {
        $css .= $sectionId . ' {';
        $css .= generateSimpleBackgroundCSS($styles, $sectionId, true); // mobile
        
        // הוסף מרווחים
        $css .= generateSpacingCSS($styles);
        
        // גובה responsive - mobile
        $mobileHeight = getNestedValue($data, 'styles.height.mobile', '');
        $heightValue = parseHeightValue($mobileHeight);
        if (!empty($heightValue)) {
            $css .= 'height: ' . htmlspecialchars($heightValue) . ';';
        }
        
        foreach ($styles as $prop => $value) {
            if (is_string($prop) && (is_string($value) || is_numeric($value))) {
                // Skip background and spacing properties - they are handled separately
                if (!in_array($prop, ['background-type', 'background-color', 'background-image', 'background-image-mobile', 'background-size', 'background-repeat', 'background-video', 'background-video-mobile', 'gradient-color1', 'gradient-color2', 'gradient-direction', 'image-overlay-opacity', 'video-overlay-opacity', 'video-muted', 'video-loop', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left'])) {
                    $css .= convertPropertyToCSS($prop) . ': ' . $value . ';';
                }
            }
        }
        $css .= '}';
    }
    
    // Title styles - mobile (צבע משותף, שאר המאפיינים נפרדים)
    $mobileTitleStyles = getNestedValue($data, 'content.title.styles.mobile', []);
    $desktopTitleStyles = getNestedValue($data, 'content.title.styles.desktop', []);
    
    // שמור על הצבע מהמחשב גם במובייל
    $finalTitleStyles = array_merge($desktopTitleStyles, $mobileTitleStyles);
    if (isset($desktopTitleStyles['color'])) {
        $finalTitleStyles['color'] = $desktopTitleStyles['color'];
    }
    
    if (!empty($finalTitleStyles) && is_array($finalTitleStyles)) {
        $css .= $sectionId . ' .hero-title {';
        foreach ($finalTitleStyles as $prop => $value) {
            if (is_string($prop) && (is_string($value) || is_numeric($value)) && !empty($value)) {
                $css .= convertPropertyToCSS($prop) . ': ' . $value . ';';
            }
        }
        $css .= '}';
    }
    
    // Subtitle styles - mobile (צבע משותף, שאר המאפיינים נפרדים)
    $mobileSubtitleStyles = getNestedValue($data, 'content.subtitle.styles.mobile', []);
    $desktopSubtitleStyles = getNestedValue($data, 'content.subtitle.styles.desktop', []);
    
    // שמור על הצבע מהמחשב גם במובייל
    $finalSubtitleStyles = array_merge($desktopSubtitleStyles, $mobileSubtitleStyles);
    if (isset($desktopSubtitleStyles['color'])) {
        $finalSubtitleStyles['color'] = $desktopSubtitleStyles['color'];
    }
    
    if (!empty($finalSubtitleStyles) && is_array($finalSubtitleStyles)) {
        $css .= $sectionId . ' .hero-subtitle {';
        foreach ($finalSubtitleStyles as $prop => $value) {
            if (is_string($prop) && (is_string($value) || is_numeric($value)) && !empty($value)) {
                $css .= convertPropertyToCSS($prop) . ': ' . $value . ';';
            }
        }
        $css .= '}';
    }
    
    $css .= '}'; // End mobile media query
    
    // הוסף visibility CSS (הסתרה responsive)
    $css .= generateVisibilityCSS($data, $sectionId, $isBuilder);
    
    return $css;
}

/**
 * יצירת CSS לרקע responsive
 */
function generateSimpleBackgroundCSS($styles, $sectionId, $isMobile = false) {
    $css = '';
    
    // קבלת סוג הרקע מהמבנה הפשוט
    $backgroundType = $styles['background-type'] ?? 'color';
    
    // Debug log
    error_log("🔧 Simple background type for rendering (" . ($isMobile ? 'mobile' : 'desktop') . "): $backgroundType");
    
    // דריסה מלאה של הרקע בהתאם לסוג שנבחר
    switch ($backgroundType) {
        case 'color':
            // צבע רגיל - דריסה מלאה + הסתרת וידאו
            $backgroundColor = $styles['background-color'] ?? '#3b82f6';
            $css .= 'background: ' . $backgroundColor . ' !important;';
            $css .= 'background-image: none !important;';
            $css .= 'background-size: auto !important;';
            $css .= 'background-repeat: repeat !important;';
            $css .= 'background-position: 0% 0% !important;';
            $css .= 'background-attachment: scroll !important;';
            break;
            
        case 'gradient':
            // גרדיאנט עם פולבק צבע
            $color1 = $styles['gradient-color1'] ?? '#3b82f6';
            $color2 = $styles['gradient-color2'] ?? '#1e40af';
            $direction = $styles['gradient-direction'] ?? 'to bottom';
            $fallbackColor = $styles['background-color'] ?? $color1; // פולבק צבע מהגדרות או צבע ראשון של הגרדיאנט
            
            // קודם צבע פולבק, אחר כך גרדיאנט
            $css .= 'background: ' . $fallbackColor . ' !important;';
            $css .= 'background: linear-gradient(' . $direction . ', ' . $color1 . ', ' . $color2 . ') !important;';
            $css .= 'background-image: linear-gradient(' . $direction . ', ' . $color1 . ', ' . $color2 . ') !important;';
            $css .= 'background-size: auto !important;';
            $css .= 'background-repeat: repeat !important;';
            $css .= 'background-position: 0% 0% !important;';
            $css .= 'background-attachment: scroll !important;';
            break;
            
        case 'image':
            // קביעת תמונה לפי המצב (מובייל או דסקטופ)
            $imageUrl = '';
            if ($isMobile && !empty($styles['background-image-mobile'])) {
                $imageUrl = $styles['background-image-mobile'];
            } else {
                $imageUrl = $styles['background-image'] ?? '';
            }
            
            if (!empty($imageUrl)) {
                // תמונה - דריסה מלאה
                $size = $styles['background-size'] ?? 'cover';
                $repeat = $styles['background-repeat'] ?? 'no-repeat';
                
                // שכבת חשכה לתמונה
                $overlayOpacity = $styles['image-overlay-opacity'] ?? 0;
                if ($overlayOpacity > 0) {
                    $overlayPercent = intval($overlayOpacity) / 100;
                    $backgroundColor = 'rgba(0, 0, 0, ' . $overlayPercent . ')';
                    $css .= 'background: ' . $backgroundColor . ' url(' . $imageUrl . ') center/' . $size . ' ' . $repeat . ' !important;';
                    $css .= 'background-blend-mode: multiply !important;';
                } else {
                    $css .= 'background: url(' . $imageUrl . ') center/' . $size . ' ' . $repeat . ' !important;';
                }
                $css .= 'background-image: url(' . $imageUrl . ') !important;';
                $css .= 'background-size: ' . $size . ' !important;';
                $css .= 'background-repeat: ' . $repeat . ' !important;';
                $css .= 'background-position: center !important;';
                $css .= 'background-attachment: scroll !important;';
            } else {
                // אם אין URL לתמונה, חזרה לצבע ברירת מחדל
                $backgroundColor = $styles['background-color'] ?? '#3b82f6';
                $css .= 'background: ' . $backgroundColor . ' !important;';
                $css .= 'background-image: none !important;';
                $css .= 'background-size: auto !important;';
                $css .= 'background-repeat: repeat !important;';
                $css .= 'background-position: 0% 0% !important;';
                $css .= 'background-attachment: scroll !important;';
            }
            break;
            
        case 'video':
            // קביעת וידאו לפי המצב (מובייל או דסקטופ)
            $videoUrl = '';
            if ($isMobile && !empty($styles['background-video-mobile'])) {
                $videoUrl = $styles['background-video-mobile'];
            } else {
                $videoUrl = $styles['background-video'] ?? '';
            }
            
            if (!empty($videoUrl)) {
                // וידאו - דריסה מלאה + צבע כפולבק בזמן טעינה
                $backgroundColor = $styles['background-color'] ?? '#3b82f6';
                $css .= 'background: ' . $backgroundColor . ' !important;';
                $css .= 'background-image: none !important;';
                $css .= 'background-size: auto !important;';
                $css .= 'background-repeat: repeat !important;';
                $css .= 'background-position: 0% 0% !important;';
                $css .= 'background-attachment: scroll !important;';
                $css .= 'position: relative !important;';
                $css .= 'overflow: hidden !important;';
            } else {
                // אם אין URL לוידאו, חזרה לצבע ברירת מחדל
                $backgroundColor = $styles['background-color'] ?? '#3b82f6';
                $css .= 'background: ' . $backgroundColor . ' !important;';
                $css .= 'background-image: none !important;';
                $css .= 'background-size: auto !important;';
                $css .= 'background-repeat: repeat !important;';
                $css .= 'background-position: 0% 0% !important;';
                $css .= 'background-attachment: scroll !important;';
            }
            break;
            
        default:
            // ברירת מחדל - צבע רגיל
            $backgroundColor = $styles['background-color'] ?? '#3b82f6';
            $css .= 'background: ' . $backgroundColor . ' !important;';
            $css .= 'background-image: none !important;';
            $css .= 'background-size: auto !important;';
            $css .= 'background-repeat: repeat !important;';
            $css .= 'background-position: 0% 0% !important;';
            $css .= 'background-attachment: scroll !important;';
            break;
    }
    

    
    return $css;
}

/**
 * המרת property ל-CSS
 */
function convertPropertyToCSS($property) {
    if (!is_string($property)) {
        return '';
    }
    // המרת camelCase ל-kebab-case
    return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $property));
}

/**
 * יצירת וידאו רקע responsive
 */
function generateVideoBackground($data, $finalId = null) {
    $html = '';
    
    // בדיקה עבור המבנה הפשוט החדש
    $styles = getNestedValue($data, 'styles', []);
    
    // בדיקה ראשונה - האם סוג הרקע הוא video
    $backgroundType = $styles['background-type'] ?? 'color';
    if ($backgroundType !== 'video') {
        return ''; // אם הרקע לא וידאו, לא להציג כלום
    }
    
    $mainVideoUrl = $styles['background-video'] ?? '';
    $mobileVideoUrl = $styles['background-video-mobile'] ?? '';
    
    $overlayOpacity = $styles['video-overlay-opacity'] ?? 0;
    $videoMuted = $styles['video-muted'] ?? true;
    $videoLoop = $styles['video-loop'] ?? true;
    
    // אם יש וידאו לפחות באחד מהמצבים AND סוג הרקע הוא video
    if (!empty($mainVideoUrl) || !empty($mobileVideoUrl)) {
        $sectionId = !empty($finalId) ? str_replace(['<', '>', '"', "'"], '', $finalId) : $data['id'];
        
        // Main video (desktop by default)
        $videoSrc = !empty($mainVideoUrl) ? $mainVideoUrl : $mobileVideoUrl;
        $videoAttrs = '';
        $videoAttrs .= 'autoplay playsinline';
        $videoAttrs .= $videoMuted ? ' muted' : '';
        $videoAttrs .= $videoLoop ? ' loop' : '';
        
        $html .= '<video class="hero-video main-video" ' . $videoAttrs . ' style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1;">';
        $html .= '<source src="' . htmlspecialchars($videoSrc) . '" type="video/mp4">';
        $html .= '</video>';
        
        // Mobile video (if different from main)
        if (!empty($mobileVideoUrl) && $mobileVideoUrl !== $mainVideoUrl) {
            $html .= '<video class="hero-video mobile-video" ' . $videoAttrs . ' style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1; display: none;">';
            $html .= '<source src="' . htmlspecialchars($mobileVideoUrl) . '" type="video/mp4">';
            $html .= '</video>';
        }
        
        // Video overlay
        if ($overlayOpacity > 0) {
            $overlayPercent = intval($overlayOpacity) / 100;
            $html .= '<div class="video-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, ' . $overlayPercent . '); z-index: 2;"></div>';
        }
        
        // JavaScript לטיפול responsive
        if (!empty($mobileVideoUrl) && $mobileVideoUrl !== $mainVideoUrl) {
            $html .= '<script>';
            $html .= 'document.addEventListener("DOMContentLoaded", function() {';
            $html .= '  function updateVideoVisibility() {';
            $html .= '    const section = document.getElementById("' . htmlspecialchars($sectionId) . '");';
            $html .= '    if (!section) return;';
            $html .= '    ';
            $html .= '    const isMobile = window.innerWidth <= 768;';
            $html .= '    const mainVideo = section.querySelector(".main-video");';
            $html .= '    const mobileVideo = section.querySelector(".mobile-video");';
            $html .= '    ';
            $html .= '    if (isMobile && mobileVideo) {';
            $html .= '      if (mainVideo) mainVideo.style.display = "none";';
            $html .= '      mobileVideo.style.display = "block";';
            $html .= '      mobileVideo.play();';
            $html .= '    } else {';
            $html .= '      if (mainVideo) { mainVideo.style.display = "block"; mainVideo.play(); }';
            $html .= '      if (mobileVideo) mobileVideo.style.display = "none";';
            $html .= '    }';
            $html .= '  }';
            $html .= '  ';
            $html .= '  updateVideoVisibility();';
            $html .= '  window.addEventListener("resize", updateVideoVisibility);';
            $html .= '});';
            $html .= '</script>';
        }
    }
    
    return $html;
}

/**
 * יצירת סגנון לכפתור
 */
function generateButtonStyle($button) {
    if (!is_array($button)) {
        return '';
    }
    
    $style = '';
    $buttonStyle = isset($button['style']) ? $button['style'] : 'solid';
    
    // צבעי הכפתור מההגדרות
    $bgColor = isset($button['styles']['background-color']) ? $button['styles']['background-color'] : '#3b82f6';
    $textColor = isset($button['styles']['color']) ? $button['styles']['color'] : '#ffffff';
    $borderRadius = isset($button['styles']['border-radius']) ? $button['styles']['border-radius'] : '6px';
    
    // סגנונות בסיס לכל הכפתורים
    $baseStyles = [
        'padding' => '12px 24px',
        'border-radius' => $borderRadius,
        'font-weight' => '500',
        'text-decoration' => 'none',
        'display' => 'inline-flex',
        'align-items' => 'center',
        'justify-content' => 'center',
        'cursor' => 'pointer',
        'transition' => 'all 0.2s ease',
        'border' => '2px solid transparent'
    ];
    
    // סגנונות ספציפיים לפי סוג הכפתור
    switch ($buttonStyle) {
        case 'solid':
            $specificStyles = [
                'background-color' => $bgColor,
                'color' => $textColor,
                'border-color' => $bgColor
            ];
            break;
            
        case 'outline':
            $specificStyles = [
                'background-color' => 'transparent',
                'color' => $bgColor,
                'border-color' => $bgColor
            ];
            break;
            
        case 'black':
            $specificStyles = [
                'background-color' => '#000000',
                'color' => '#ffffff',
                'border-color' => '#000000'
            ];
            break;
            
        case 'white':
            $specificStyles = [
                'background-color' => '#ffffff',
                'color' => '#000000',
                'border-color' => '#ffffff'
            ];
            break;
            
        case 'underline':
            $specificStyles = [
                'background-color' => 'transparent',
                'color' => $bgColor,
                'border' => 'none',
                'border-bottom' => '2px solid ' . $bgColor,
                'border-radius' => '0'
            ];
            break;
            
        // תמיכה בפורמטים ישנים
        case 'primary':
            $specificStyles = [
                'background-color' => $bgColor,
                'color' => $textColor,
                'border-color' => $bgColor
            ];
            break;
            
        case 'secondary':
            $specificStyles = [
                'background-color' => 'transparent',
                'color' => $bgColor,
                'border-color' => $bgColor
            ];
            break;
            
        default:
            $specificStyles = [
                'background-color' => $bgColor,
                'color' => $textColor,
                'border-color' => $bgColor
            ];
            break;
    }
    
    // מיזוג הסגנונות
    $allStyles = array_merge($baseStyles, $specificStyles);
    
    // אם יש סגנונות מותאמים נוספים
    if (isset($button['styles']) && is_array($button['styles'])) {
        foreach ($button['styles'] as $prop => $value) {
            if (!in_array($prop, ['background-color', 'color', 'border-radius'])) {
                $allStyles[$prop] = $value;
            }
        }
    }
    
    // המרה לCSS
    foreach ($allStyles as $prop => $value) {
        if (is_string($prop) && (is_string($value) || is_numeric($value))) {
            $style .= convertPropertyToCSS($prop) . ': ' . $value . '; ';
        }
    }
    
    return $style;
}

/**
 * יצירת class עבור כפתור לפי הסגנון
 */
function generateButtonClass($button) {
    $style = isset($button['style']) ? $button['style'] : 'solid';
    
    switch ($style) {
        case 'solid':
            return 'button-solid';
        case 'outline':
            return 'button-outline';
        case 'black':
            return 'button-black';
        case 'white':
            return 'button-white';
        case 'underline':
            return 'button-underline';
        // תמיכה בפורמטים ישנים
        case 'primary':
            return 'button-solid';
        case 'secondary':
            return 'button-outline';
        default:
            return 'button-solid';
    }
}

/**
 * יצירת CSS למרווחים (padding ו-margin) בכל 4 הכיוונים
 */
function generateSpacingCSS($styles) {
    if (!is_array($styles)) {
        return '';
    }
    
    $css = '';
    
    // מערך כל סוגי המרווחים
    $spacingTypes = ['padding', 'margin'];
    $directions = ['top', 'right', 'bottom', 'left'];
    
    foreach ($spacingTypes as $type) {
        $values = [];
        $hasAnyValue = false;
        
        // אסוף ערכים לכל הכיוונים
        foreach ($directions as $direction) {
            $key = $type . '-' . $direction;
            if (isset($styles[$key]) && is_numeric($styles[$key]) && $styles[$key] > 0) {
                $values[$direction] = $styles[$key] . 'px';
                $hasAnyValue = true;
            } else {
                $values[$direction] = '0';
            }
        }
        
        // אם יש לפחות ערך אחד, צור CSS
        if ($hasAnyValue) {
            // בדוק אם כל הערכים זהים
            $uniqueValues = array_unique($values);
            if (count($uniqueValues) === 1) {
                // כל הערכים זהים - קיצור
                $css .= $type . ': ' . $values['top'] . '; ';
            } else {
                // ערכים שונים - פורמט מלא (top right bottom left)
                $css .= $type . ': ' . $values['top'] . ' ' . $values['right'] . ' ' . $values['bottom'] . ' ' . $values['left'] . '; ';
            }
        }
    }
    
    return $css;
}

/**
 * יצירת CSS לvisibility responsive
 */
function generateVisibilityCSS($data, $sectionId, $isBuilder = false) {
    $css = '';
    $visibility = getNestedValue($data, 'visibility', []);
    
    if (empty($visibility)) {
        return $css;
    }
    
    $hideDesktop = getNestedValue($visibility, 'hide-desktop', false);
    $hideMobile = getNestedValue($visibility, 'hide-mobile', false);
    
    // אם זה builder, הראה עם אינדיקציה חזותית
    if ($isBuilder) {
        // בbuilder - הראה אלמנטים מוסתרים עם אופסיטי נמוך (לא display none!)
        if ($hideDesktop || $hideMobile) {
            $css .= $sectionId . '.builder-hidden {';
            $css .= 'position: relative !important;';
            $css .= 'opacity: 0.3 !important;';
            $css .= 'display: block !important;'; // מבטל כל display none שעלול להיות
            $css .= 'visibility: visible !important;'; // מבטל כל visibility hidden
            $css .= '}';
            
            $css .= $sectionId . '.builder-hidden::before {';
            $css .= 'content: "סקשן מוסתר" !important;';
            $css .= 'position: absolute !important;';
            $css .= 'top: 50% !important;';
            $css .= 'left: 50% !important;';
            $css .= 'transform: translate(-50%, -50%) !important;';
            $css .= 'background: rgba(239, 68, 68, 0.9) !important;';
            $css .= 'color: white !important;';
            $css .= 'padding: 8px 16px !important;';
            $css .= 'font-size: 14px !important;';
            $css .= 'font-weight: bold !important;';
            $css .= 'border-radius: 6px !important;';
            $css .= 'z-index: 10000 !important;';
            $css .= 'pointer-events: none !important;';
            $css .= '}';
            
            $css .= $sectionId . '.builder-hidden::after {';
            $css .= 'content: "" !important;';
            $css .= 'position: absolute !important;';
            $css .= 'top: 0 !important;';
            $css .= 'left: 0 !important;';
            $css .= 'right: 0 !important;';
            $css .= 'bottom: 0 !important;';
            $css .= 'background: repeating-linear-gradient(45deg, rgba(239, 68, 68, 0.1) 0px, rgba(239, 68, 68, 0.1) 10px, transparent 10px, transparent 20px) !important;';
            $css .= 'pointer-events: none !important;';
            $css .= 'z-index: 9999 !important;';
            $css .= '}';
            
            if ($hideDesktop) {
                $css .= '@media (min-width: 769px) {';
                $css .= $sectionId . '.builder-hidden {';
                $css .= 'border: 3px dashed #ef4444 !important;';
                $css .= '}';
                $css .= '}';
            }
            
            if ($hideMobile) {
                $css .= '@media (max-width: 768px) {';
                $css .= $sectionId . '.builder-hidden {';
                $css .= 'border: 3px dashed #ef4444 !important;';
                $css .= '}';
                $css .= '}';
            }
        }
    } else {
        // בfront - הסתר לגמרי לפי המכשיר
        if ($hideDesktop) {
            $css .= '@media (min-width: 769px) {';
            $css .= $sectionId . ' {';
            $css .= 'display: none !important;';
            $css .= '}';
            $css .= '}';
        }
        
        if ($hideMobile) {
            $css .= '@media (max-width: 768px) {';
            $css .= $sectionId . ' {';
            $css .= 'display: none !important;';
            $css .= '}';
            $css .= '}';
        }
    }
    
    return $css;
}

// שימוש ישיר - תמיכה GET ו-POST
if (isset($_GET['render'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // POST request - נתונים ב-body
        $input = file_get_contents('php://input');
        $section = json_decode($input, true);
    } else {
        // GET request - נתונים ב-query
        $section = json_decode($_GET['section'] ?? '{}', true);
    }
    
    if ($section && is_array($section)) {
        header('Content-Type: text/html; charset=utf-8');
        echo renderHeroSection($section);
        exit;
    }
    
    exit('Invalid section data');
}
?> 