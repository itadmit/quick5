<?php
/**
 * Responsive Helper Functions
 */

/**
 * זיהוי סוג המכשיר על פי User Agent
 */
function getDeviceType() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // בדיקה למובייל
    if (preg_match('/Mobile|Android|iPhone|iPad|BlackBerry|Windows Phone/i', $userAgent)) {
        // בדיקה ספציפית לטאבלט
        if (preg_match('/iPad|Android(?!.*Mobile)|Tablet/i', $userAgent)) {
            return 'tablet';
        }
        return 'mobile';
    }
    
    return 'desktop';
}

/**
 * קבלת ערך רספונסיבי על פי המכשיר
 */
function getResponsiveValue($data, $property, $deviceType = null) {
    if ($deviceType === null) {
        $deviceType = getDeviceType();
    }
    
    // אם זה מחשב, החזר את הערך הרגיל
    if ($deviceType === 'desktop') {
        return $data[$property] ?? '';
    }
    
    // בדוק אם יש ערך ספציפי למכשיר
    $responsiveProperty = $property . '_' . $deviceType;
    if (isset($data[$responsiveProperty]) && !empty($data[$responsiveProperty])) {
        return $data[$responsiveProperty];
    }
    
    // אם לא, נסה לקבל את ערך הטאבלט (אם אנחנו במובייל)
    if ($deviceType === 'mobile') {
        $tabletProperty = $property . '_tablet';
        if (isset($data[$tabletProperty]) && !empty($data[$tabletProperty])) {
            return $data[$tabletProperty];
        }
    }
    
    // ברירת מחדל - ערך המחשב
    return $data[$property] ?? '';
}

/**
 * יצירת CSS עבור ערכים רספונסיביים
 */
function generateResponsiveCSS($data, $property, $cssProperty, $unit = 'px', $important = false) {
    $desktop = $data[$property] ?? '';
    $tablet = $data[$property . '_tablet'] ?? '';
    $mobile = $data[$property . '_mobile'] ?? '';
    
    $css = '';
    $importantStr = $important ? ' !important' : '';
    
    // Desktop value (default)
    if (!empty($desktop)) {
        $css .= "{$cssProperty}: {$desktop}{$unit}{$importantStr};\n";
    }
    
    // Tablet value
    if (!empty($tablet)) {
        $css .= "@media (max-width: 1024px) {\n";
        $css .= "  {$cssProperty}: {$tablet}{$unit}{$importantStr};\n";
        $css .= "}\n";
    }
    
    // Mobile value
    if (!empty($mobile)) {
        $css .= "@media (max-width: 768px) {\n";
        $css .= "  {$cssProperty}: {$mobile}{$unit}{$importantStr};\n";
        $css .= "}\n";
    }
    
    return $css;
}

/**
 * בדיקה אם ערך רספונסיבי קיים
 */
function hasResponsiveValue($data, $property) {
    return !empty($data[$property]) || 
           !empty($data[$property . '_tablet']) || 
           !empty($data[$property . '_mobile']);
}

/**
 * קבלת מדיה קווירי למכשיר
 */
function getMediaQuery($device) {
    switch ($device) {
        case 'mobile':
            return '@media (max-width: 768px)';
        case 'tablet':
            return '@media (min-width: 769px) and (max-width: 1024px)';
        case 'desktop':
            return '@media (min-width: 1025px)';
        default:
            return '';
    }
}

/**
 * יצירת Tailwind classes רספונסיביים
 */
function generateResponsiveTailwindClasses($data, $property, $classPrefix) {
    $classes = [];
    
    // Desktop (default)
    $desktop = $data[$property] ?? '';
    if (!empty($desktop)) {
        $classes[] = $classPrefix . $desktop;
    }
    
    // Tablet
    $tablet = $data[$property . '_tablet'] ?? '';
    if (!empty($tablet)) {
        $classes[] = 'md:' . $classPrefix . $tablet;
    }
    
    // Mobile
    $mobile = $data[$property . '_mobile'] ?? '';
    if (!empty($mobile)) {
        $classes[] = 'sm:' . $classPrefix . $mobile;
    }
    
    return implode(' ', $classes);
}

/**
 * המרת פיקסלים ל-rem
 */
function pxToRem($px, $baseFontSize = 16) {
    if (empty($px) || !is_numeric($px)) {
        return '';
    }
    
    return ($px / $baseFontSize) . 'rem';
}

/**
 * יצירת style attribute רספונסיבי
 */
function generateResponsiveStyle($data, $mappings) {
    $deviceType = getDeviceType();
    $styles = [];
    
    foreach ($mappings as $dataProperty => $cssProperty) {
        $value = getResponsiveValue($data, $dataProperty, $deviceType);
        
        if (!empty($value)) {
            // בדוק אם צריך יחידה
            $unit = '';
            if (is_numeric($value) && in_array($cssProperty, ['font-size', 'width', 'height', 'margin', 'padding', 'top', 'right', 'bottom', 'left'])) {
                $unit = 'px';
            }
            
            $styles[] = "{$cssProperty}: {$value}{$unit}";
        }
    }
    
    return implode('; ', $styles);
}

/**
 * בדיקה אם המכשיר הוא מובייל
 */
function isMobile() {
    return getDeviceType() === 'mobile';
}

/**
 * בדיקה אם המכשיר הוא טאבלט
 */
function isTablet() {
    return getDeviceType() === 'tablet';
}

/**
 * בדיקה אם המכשיר הוא מחשב
 */
function isDesktop() {
    return getDeviceType() === 'desktop';
}

/**
 * קבלת breakpoints של הפרויקט
 */
function getBreakpoints() {
    return [
        'mobile' => '768px',
        'tablet' => '1024px',
        'desktop' => '1025px'
    ];
}

/**
 * יצירת CSS grid רספונסיבי
 */
function generateResponsiveGrid($data, $property = 'columns') {
    $desktop = $data[$property] ?? 3;
    $tablet = $data[$property . '_tablet'] ?? 2;
    $mobile = $data[$property . '_mobile'] ?? 1;
    
    $css = "grid-template-columns: repeat({$desktop}, 1fr);\n";
    
    if ($tablet !== $desktop) {
        $css .= "@media (max-width: 1024px) {\n";
        $css .= "  grid-template-columns: repeat({$tablet}, 1fr);\n";
        $css .= "}\n";
    }
    
    if ($mobile !== $tablet) {
        $css .= "@media (max-width: 768px) {\n";
        $css .= "  grid-template-columns: repeat({$mobile}, 1fr);\n";
        $css .= "}\n";
    }
    
    return $css;
}

/**
 * קבלת תצוגה מקדימה לכל המכשירים
 */
function getResponsivePreview($data, $property) {
    return [
        'desktop' => getResponsiveValue($data, $property, 'desktop'),
        'tablet' => getResponsiveValue($data, $property, 'tablet'),
        'mobile' => getResponsiveValue($data, $property, 'mobile')
    ];
}
?> 