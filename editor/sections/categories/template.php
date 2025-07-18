<?php
/**
 * Categories Section Template - תבנית לרינדור סקשן גריד קטגוריות
 */

/**
 * רינדור סקשן קטגוריות
 */
function renderCategoriesSection($section) {
    // חילוץ נתונים
    $sectionId = $section['id'];
    $styles = $section['styles'] ?? [];
    $content = $section['content'] ?? [];
    $visibility = $section['visibility'] ?? [];
    $attributes = $section['attributes'] ?? [];
    
    // יצירת ID יחודי או שימוש במותאם אישית
    $finalId = !empty($attributes['id']) ? $attributes['id'] : $sectionId;
    
    // כיתות CSS נוספות
    $extraClasses = !empty($attributes['class']) ? ' ' . $attributes['class'] : '';
    
    // יצירת CSS רספונסיבי
    $responsiveCSS = generateCategoriesResponsiveCSS($finalId, $styles, $content);
    
    // יצירת HTML של הסקשן
    $html = generateCategoriesHTML($finalId, $extraClasses, $styles, $content, $visibility);
    
    return $responsiveCSS . "\n" . $html;
}

/**
 * יצירת CSS רספונסיבי לסקשן
 */
function generateCategoriesResponsiveCSS($sectionId, $styles, $content) {
    $css = "<style>\n";
    
    // CSS בסיסי לסקשן
    $css .= "#{$sectionId} {\n";
    
    // רקע
    if (!empty($styles['background-type'])) {
        switch ($styles['background-type']) {
            case 'color':
                if (!empty($styles['background-color'])) {
                    $css .= "    background-color: {$styles['background-color']};\n";
                }
                break;
                
            case 'image':
                if (!empty($styles['background-image'])) {
                    $css .= "    background-image: url('{$styles['background-image']}');\n";
                    $css .= "    background-size: " . ($styles['background-size'] ?? 'cover') . ";\n";
                    $css .= "    background-repeat: " . ($styles['background-repeat'] ?? 'no-repeat') . ";\n";
                    $css .= "    background-position: center;\n";
                    
                    // overlay לתמונה
                    if (!empty($styles['image-overlay-opacity'])) {
                        $opacity = $styles['image-overlay-opacity'] / 100;
                        $css .= "    position: relative;\n";
                        $css .= "}\n";
                        $css .= "#{$sectionId}::before {\n";
                        $css .= "    content: '';\n";
                        $css .= "    position: absolute;\n";
                        $css .= "    top: 0;\n";
                        $css .= "    left: 0;\n";
                        $css .= "    right: 0;\n";
                        $css .= "    bottom: 0;\n";
                        $css .= "    background-color: rgba(0, 0, 0, {$opacity});\n";
                        $css .= "    z-index: 1;\n";
                        $css .= "}\n";
                        $css .= "#{$sectionId} > * {\n";
                        $css .= "    position: relative;\n";
                        $css .= "    z-index: 2;\n";
                    }
                }
                break;
                
            case 'gradient':
                if (!empty($styles['gradient-color1']) && !empty($styles['gradient-color2'])) {
                    $direction = $styles['gradient-direction'] ?? 'to bottom';
                    $css .= "    background: linear-gradient({$direction}, {$styles['gradient-color1']}, {$styles['gradient-color2']});\n";
                }
                break;
        }
    }
    
    // רוחב הסקשן
    $sectionWidth = $styles['section-width'] ?? 'container';
    if ($sectionWidth === 'full') {
        $css .= "    width: 100%;\n";
    } elseif ($sectionWidth === 'custom' && !empty($styles['max-width'])) {
        $css .= "    max-width: {$styles['max-width']};\n";
        $css .= "    margin: 0 auto;\n";
    }
    
    // גובה
    if (!empty($styles['height'])) {
        if (is_array($styles['height'])) {
            // פורמט חדש עם desktop/mobile
            if (!empty($styles['height']['desktop'])) {
                $desktopHeight = parseHeightValue($styles['height']['desktop']);
                if ($desktopHeight) {
                    $css .= "    min-height: {$desktopHeight};\n";
                }
            }
        } else {
            // פורמט ישן - string
            $css .= "    min-height: {$styles['height']};\n";
        }
    }
    
    // מידות נוספות
    if (!empty($styles['min-height'])) {
        $css .= "    min-height: {$styles['min-height']};\n";
    }
    if (!empty($styles['max-height'])) {
        $css .= "    max-height: {$styles['max-height']};\n";
    }
    
    // מרווחים - Padding
    $paddingProperties = ['padding-top', 'padding-right', 'padding-bottom', 'padding-left'];
    foreach ($paddingProperties as $prop) {
        if (!empty($styles[$prop])) {
            $cssProp = str_replace('-', '-', $prop);
            $css .= "    {$cssProp}: {$styles[$prop]};\n";
        }
    }
    
    // מרווחים - Margin  
    $marginProperties = ['margin-top', 'margin-right', 'margin-bottom', 'margin-left'];
    foreach ($marginProperties as $prop) {
        if (!empty($styles[$prop])) {
            $cssProp = str_replace('-', '-', $prop);
            $css .= "    {$cssProp}: {$styles[$prop]};\n";
        }
    }
    
    $css .= "}\n\n";
    
    // CSS לכותרת
    if (!empty($content['title']['styles']['desktop'])) {
        $titleStyles = $content['title']['styles']['desktop'];
        $css .= "#{$sectionId} .categories-title {\n";
        
        if (!empty($titleStyles['font-size'])) $css .= "    font-size: {$titleStyles['font-size']};\n";
        if (!empty($titleStyles['font-weight'])) $css .= "    font-weight: {$titleStyles['font-weight']};\n";
        if (!empty($titleStyles['color'])) $css .= "    color: {$titleStyles['color']};\n";
        if (!empty($titleStyles['text-align'])) $css .= "    text-align: {$titleStyles['text-align']};\n";
        if (!empty($titleStyles['line-height'])) $css .= "    line-height: {$titleStyles['line-height']};\n";
        
        $css .= "}\n\n";
    }
    
    // CSS לתת-כותרת
    if (!empty($content['subtitle']['styles']['desktop'])) {
        $subtitleStyles = $content['subtitle']['styles']['desktop'];
        $css .= "#{$sectionId} .categories-subtitle {\n";
        
        if (!empty($subtitleStyles['font-size'])) $css .= "    font-size: {$subtitleStyles['font-size']};\n";
        if (!empty($subtitleStyles['font-weight'])) $css .= "    font-weight: {$subtitleStyles['font-weight']};\n";
        if (!empty($subtitleStyles['color'])) $css .= "    color: {$subtitleStyles['color']};\n";
        if (!empty($subtitleStyles['text-align'])) $css .= "    text-align: {$subtitleStyles['text-align']};\n";
        if (!empty($subtitleStyles['line-height'])) $css .= "    line-height: {$subtitleStyles['line-height']};\n";
        
        $css .= "}\n\n";
    }
    
    // CSS לגריד הקטגוריות
    $gridColumns = $content['grid']['columns']['desktop'] ?? 4;
    $gridGap = $content['grid']['gap'] ?? '24px';
    
    $css .= "#{$sectionId} .categories-grid {\n";
    $css .= "    display: grid;\n";
    $css .= "    grid-template-columns: repeat({$gridColumns}, 1fr);\n";
    $css .= "    gap: {$gridGap};\n";
    $css .= "    margin-top: 48px;\n";
    $css .= "}\n\n";
    
    // CSS לכרטיס קטגוריה
    $css .= "#{$sectionId} .category-card {\n";
    $css .= "    background: white;\n";
    $css .= "    border-radius: 12px;\n";
    $css .= "    overflow: hidden;\n";
    $css .= "    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);\n";
    $css .= "    transition: all 0.3s ease;\n";
    $css .= "    text-decoration: none;\n";
    $css .= "    color: inherit;\n";
    $css .= "}\n\n";
    
    $css .= "#{$sectionId} .category-card:hover {\n";
    $css .= "    transform: translateY(-4px);\n";
    $css .= "    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);\n";
    $css .= "}\n\n";
    
    $css .= "#{$sectionId} .category-image {\n";
    $css .= "    width: 100%;\n";
    $css .= "    height: 200px;\n";
    $css .= "    object-fit: cover;\n";
    $css .= "}\n\n";
    
    $css .= "#{$sectionId} .category-name {\n";
    $css .= "    padding: 20px;\n";
    $css .= "    font-size: 18px;\n";
    $css .= "    font-weight: 600;\n";
    $css .= "    text-align: center;\n";
    $css .= "    color: #1f2937;\n";
    $css .= "}\n\n";
    
    // CSS לכפתורים
    $css .= "#{$sectionId} .categories-buttons {\n";
    $css .= "    margin-top: 48px;\n";
    $css .= "    text-align: center;\n";
    $css .= "}\n\n";
    
    // CSS רספונסיבי - מובייל
    $css .= "@media (max-width: 768px) {\n";
    
    // גובה במובייל
    if (!empty($styles['height']) && is_array($styles['height']) && !empty($styles['height']['mobile'])) {
        $mobileHeight = parseHeightValue($styles['height']['mobile']);
        if ($mobileHeight) {
            $css .= "    #{$sectionId} {\n";
            $css .= "        min-height: {$mobileHeight};\n";
            $css .= "    }\n";
        }
    }
    
    // כותרת במובייל
    if (!empty($content['title']['styles']['mobile'])) {
        $titleMobileStyles = $content['title']['styles']['mobile'];
        $css .= "    #{$sectionId} .categories-title {\n";
        
        if (!empty($titleMobileStyles['font-size'])) $css .= "        font-size: {$titleMobileStyles['font-size']};\n";
        if (!empty($titleMobileStyles['font-weight'])) $css .= "        font-weight: {$titleMobileStyles['font-weight']};\n";
        if (!empty($titleMobileStyles['color'])) $css .= "        color: {$titleMobileStyles['color']};\n";
        if (!empty($titleMobileStyles['text-align'])) $css .= "        text-align: {$titleMobileStyles['text-align']};\n";
        if (!empty($titleMobileStyles['line-height'])) $css .= "        line-height: {$titleMobileStyles['line-height']};\n";
        
        $css .= "    }\n";
    }
    
    // תת-כותרת במובייל
    if (!empty($content['subtitle']['styles']['mobile'])) {
        $subtitleMobileStyles = $content['subtitle']['styles']['mobile'];
        $css .= "    #{$sectionId} .categories-subtitle {\n";
        
        if (!empty($subtitleMobileStyles['font-size'])) $css .= "        font-size: {$subtitleMobileStyles['font-size']};\n";
        if (!empty($subtitleMobileStyles['font-weight'])) $css .= "        font-weight: {$subtitleMobileStyles['font-weight']};\n";
        if (!empty($subtitleMobileStyles['color'])) $css .= "        color: {$subtitleMobileStyles['color']};\n";
        if (!empty($subtitleMobileStyles['text-align'])) $css .= "        text-align: {$subtitleMobileStyles['text-align']};\n";
        if (!empty($subtitleMobileStyles['line-height'])) $css .= "        line-height: {$subtitleMobileStyles['line-height']};\n";
        
        $css .= "    }\n";
    }
    
    // גריד במובייל
    $mobileColumns = $content['grid']['columns']['mobile'] ?? 2;
    $css .= "    #{$sectionId} .categories-grid {\n";
    $css .= "        grid-template-columns: repeat({$mobileColumns}, 1fr);\n";
    $css .= "        margin-top: 32px;\n";
    $css .= "    }\n";
    
    // כרטיס קטגוריה במובייל
    $css .= "    #{$sectionId} .category-image {\n";
    $css .= "        height: 150px;\n";
    $css .= "    }\n";
    
    $css .= "    #{$sectionId} .category-name {\n";
    $css .= "        padding: 16px;\n";
    $css .= "        font-size: 16px;\n";
    $css .= "    }\n";
    
    $css .= "    #{$sectionId} .categories-buttons {\n";
    $css .= "        margin-top: 32px;\n";
    $css .= "    }\n";
    
    $css .= "}\n";
    $css .= "</style>";
    
    return $css;
}

/**
 * יצירת HTML של הסקשן
 */
function generateCategoriesHTML($sectionId, $extraClasses, $styles, $content, $visibility) {
    // בדיקת visibility
    $isHiddenDesktop = !empty($visibility['hide-desktop']);
    $isHiddenMobile = !empty($visibility['hide-mobile']);
    
    $visibilityClasses = '';
    if ($isHiddenDesktop) $visibilityClasses .= ' hidden-desktop';
    if ($isHiddenMobile) $visibilityClasses .= ' hidden-mobile';
    
    // תחילת הסקשן
    $html = '<section id="' . htmlspecialchars($sectionId) . '" class="categories-section' . $extraClasses . $visibilityClasses . '" data-animation="' . ($styles['animation'] ?? 'none') . '">' . "\n";
    
    // קונטיינר פנימי
    $containerClass = ($styles['section-width'] ?? 'container') === 'container' ? 'container mx-auto px-4' : 'w-full px-4';
    $html .= '    <div class="' . $containerClass . '">' . "\n";
    
    // כותרת ותת-כותרת
    if (!empty($content['title']['text']) || !empty($content['subtitle']['text'])) {
        $html .= '        <div class="categories-header">' . "\n";
        
        if (!empty($content['title']['text'])) {
            $html .= '            <h2 class="categories-title">' . htmlspecialchars($content['title']['text']) . '</h2>' . "\n";
        }
        
        if (!empty($content['subtitle']['text'])) {
            $subtitleText = nl2br(htmlspecialchars($content['subtitle']['text']));
            $html .= '            <p class="categories-subtitle">' . $subtitleText . '</p>' . "\n";
        }
        
        $html .= '        </div>' . "\n";
    }
    
    // גריד הקטגוריות
    if (!empty($content['grid']['categories']) && is_array($content['grid']['categories'])) {
        $html .= '        <div class="categories-grid">' . "\n";
        
        foreach ($content['grid']['categories'] as $category) {
            if (empty($category['name'])) continue;
            
            $categoryName = htmlspecialchars($category['name']);
            $categoryUrl = htmlspecialchars($category['url'] ?? '#');
            $categoryImage = htmlspecialchars($category['image'] ?? '');
            
            $html .= '            <a href="' . $categoryUrl . '" class="category-card">' . "\n";
            
            if ($categoryImage) {
                $html .= '                <img src="' . $categoryImage . '" alt="' . $categoryName . '" class="category-image">' . "\n";
            }
            
            $html .= '                <div class="category-name">' . $categoryName . '</div>' . "\n";
            $html .= '            </a>' . "\n";
        }
        
        $html .= '        </div>' . "\n";
    }
    
    // כפתורים
    if (!empty($content['buttons']) && is_array($content['buttons'])) {
        $html .= '        <div class="categories-buttons">' . "\n";
        
        foreach ($content['buttons'] as $button) {
            if (empty($button['text'])) continue;
            
            $buttonText = htmlspecialchars($button['text']);
            $buttonUrl = htmlspecialchars($button['url'] ?? '#');
            $buttonStyle = $button['style'] ?? 'primary';
            
            // כיתות CSS לכפתור
            $buttonClasses = getButtonClasses($buttonStyle);
            
            $html .= '            <a href="' . $buttonUrl . '" class="' . $buttonClasses . '">' . $buttonText . '</a>' . "\n";
        }
        
        $html .= '        </div>' . "\n";
    }
    
    $html .= '    </div>' . "\n";
    $html .= '</section>' . "\n";
    
    return $html;
}

/**
 * פונקציות עזר
 */

// פיצול ערך גובה (כמו "100vh") לvalue+unit
function parseHeightValue($heightValue) {
    if (!$heightValue) return null;
    
    // אם זה object עם value ו-unit
    if (is_array($heightValue)) {
        $value = $heightValue['value'] ?? '';
        $unit = $heightValue['unit'] ?? 'px';
        
        if ($value === '' || $value === null) {
            return null;
        }
        
        if ($unit === 'auto') {
            return 'auto';
        }
        
        return $value . $unit;
    }
    
    // אם זה string (פורמט ישן)
    return $heightValue;
}

// קבלת כיתות CSS לכפתורים
function getButtonClasses($style) {
    $baseClasses = 'inline-block px-6 py-3 text-sm font-medium rounded-lg transition-all duration-200 ';
    
    switch ($style) {
        case 'primary':
            return $baseClasses . 'bg-blue-600 text-white hover:bg-blue-700';
        case 'secondary':
            return $baseClasses . 'bg-gray-600 text-white hover:bg-gray-700';
        case 'outline':
            return $baseClasses . 'border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white';
        case 'success':
            return $baseClasses . 'bg-green-600 text-white hover:bg-green-700';
        case 'white':
            return $baseClasses . 'bg-white text-gray-900 border border-gray-300 hover:bg-gray-50';
        case 'black':
            return $baseClasses . 'bg-black text-white hover:bg-gray-800';
        default:
            return $baseClasses . 'bg-blue-600 text-white hover:bg-blue-700';
    }
}

// אם הקובץ נקרא ישירות עם פרמטרים לבדיקה
if (isset($_GET['preview']) && !empty($_GET['data'])) {
    $sectionData = json_decode($_GET['data'], true);
    if ($sectionData) {
        echo renderCategoriesSection($sectionData);
    }
}

// הנדלינג בקשות POST לרינדור בזמן אמת
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['render'])) {
    $input = file_get_contents('php://input');
    $sectionData = json_decode($input, true);
    
    if ($sectionData) {
        echo renderCategoriesSection($sectionData);
    } else {
        http_response_code(400);
        echo 'Invalid section data';
    }
    exit;
}
?> 