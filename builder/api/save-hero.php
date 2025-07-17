<?php
/**
 * Save Hero Data API
 */

// Start session first!
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? 'http://yogev.localhost:8888'));
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'לא מחובר']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['hero'])) {
        throw new Exception('נתונים לא תקינים');
    }
    
    $heroData = $data['hero'];
    
    // Validate required fields
    $requiredFields = ['title', 'subtitle'];
    foreach ($requiredFields as $field) {
        if (!isset($heroData[$field])) {
            throw new Exception("שדה חסר: {$field}");
        }
    }
    
    // Validate buttons (either new format or legacy)
    if (!isset($heroData['buttons']) && (!isset($heroData['buttonText']) || !isset($heroData['buttonLink']))) {
        throw new Exception("חייב להיות לפחות כפתור אחד");
    }
    
    // Sanitize and validate data with all new fields
    $cleanHeroData = [
        // Content
        'title' => strip_tags(trim($heroData['title'])),
        'subtitle' => strip_tags(trim($heroData['subtitle'])),
        
        // Buttons - support both new format and legacy
        'buttons' => [],
        'buttonText' => strip_tags(trim($heroData['buttonText'] ?? '')),
        'buttonLink' => filter_var(trim($heroData['buttonLink'] ?? ''), FILTER_SANITIZE_URL),
        'buttonNewTab' => isset($heroData['buttonNewTab']) ? (bool)$heroData['buttonNewTab'] : false,
        
        // Background
        'bgType' => isset($heroData['bgType']) ? sanitizeSelect($heroData['bgType'], ['color', 'gradient', 'image', 'video']) : 'color',
        'bgColor' => isset($heroData['bgColor']) ? sanitizeColor($heroData['bgColor']) : '#3B82F6',
        'bgGradient1' => isset($heroData['bgGradient1']) ? sanitizeColor($heroData['bgGradient1']) : '#3B82F6',
        'bgGradient2' => isset($heroData['bgGradient2']) ? sanitizeColor($heroData['bgGradient2']) : '#1E40AF',
        'bgGradientDirection' => isset($heroData['bgGradientDirection']) ? sanitizeGradientDirection($heroData['bgGradientDirection']) : 'to-b',
        'bgImage' => isset($heroData['bgImage']) ? filter_var(trim($heroData['bgImage']), FILTER_SANITIZE_URL) : '',
        'bgImageSize' => isset($heroData['bgImageSize']) ? sanitizeSelect($heroData['bgImageSize'], ['cover', 'contain', 'auto']) : 'cover',
        'bgImagePosition' => isset($heroData['bgImagePosition']) ? sanitizeSelect($heroData['bgImagePosition'], ['center', 'top', 'bottom', 'left', 'right']) : 'center',
        'bgImageRepeat' => isset($heroData['bgImageRepeat']) ? sanitizeSelect($heroData['bgImageRepeat'], ['no-repeat', 'repeat', 'repeat-x', 'repeat-y']) : 'no-repeat',
        'bgImage_mobile' => isset($heroData['bgImage_mobile']) ? filter_var(trim($heroData['bgImage_mobile']), FILTER_SANITIZE_URL) : '',
        'bgVideo' => isset($heroData['bgVideo']) ? filter_var(trim($heroData['bgVideo']), FILTER_SANITIZE_URL) : '',
        'bgVideo_mobile' => isset($heroData['bgVideo_mobile']) ? filter_var(trim($heroData['bgVideo_mobile']), FILTER_SANITIZE_URL) : '',
        'bgVideoOverlay' => isset($heroData['bgVideoOverlay']) ? (int)$heroData['bgVideoOverlay'] : 30,
        
        // Colors
        'titleColor' => isset($heroData['titleColor']) ? sanitizeColor($heroData['titleColor']) : '#FFFFFF',
        'subtitleColor' => isset($heroData['subtitleColor']) ? sanitizeColor($heroData['subtitleColor']) : '#E5E7EB',
        'buttonBgColor' => isset($heroData['buttonBgColor']) ? sanitizeColor($heroData['buttonBgColor']) : '#F59E0B',
        'buttonTextColor' => isset($heroData['buttonTextColor']) ? sanitizeColor($heroData['buttonTextColor']) : '#FFFFFF',
        'buttonBorderColor' => isset($heroData['buttonBorderColor']) ? sanitizeColor($heroData['buttonBorderColor']) : '#F59E0B',
        'buttonBgColorHover' => isset($heroData['buttonBgColorHover']) ? sanitizeColor($heroData['buttonBgColorHover']) : '#E5A712',
        'buttonTextColorHover' => isset($heroData['buttonTextColorHover']) ? sanitizeColor($heroData['buttonTextColorHover']) : '#FFFFFF',
        'buttonBorderColorHover' => isset($heroData['buttonBorderColorHover']) ? sanitizeColor($heroData['buttonBorderColorHover']) : '#E5A712',
        
        // Layout
        'width' => isset($heroData['width']) ? sanitizeSelect($heroData['width'], ['container', 'full', 'custom']) : 'full',
        'customWidth' => isset($heroData['customWidth']) ? (int)$heroData['customWidth'] : 800,
        'customWidthUnit' => isset($heroData['customWidthUnit']) ? sanitizeSelect($heroData['customWidthUnit'], ['px', '%', 'vw']) : 'px',
        'contentPosition' => isset($heroData['contentPosition']) ? sanitizeContentPosition($heroData['contentPosition']) : 'center-center',
        'heightType' => isset($heroData['heightType']) ? sanitizeSelect($heroData['heightType'], ['auto', 'px', 'vh']) : 'auto',
        'heightValue' => isset($heroData['heightValue']) ? (int)$heroData['heightValue'] : 500,
        'heightType_tablet' => isset($heroData['heightType_tablet']) ? sanitizeSelect($heroData['heightType_tablet'], ['', 'auto', 'px', 'vh']) : '',
        'heightValue_tablet' => isset($heroData['heightValue_tablet']) ? (int)$heroData['heightValue_tablet'] : '',
        'heightType_mobile' => isset($heroData['heightType_mobile']) ? sanitizeSelect($heroData['heightType_mobile'], ['', 'auto', 'px', 'vh']) : '',
        'heightValue_mobile' => isset($heroData['heightValue_mobile']) ? (int)$heroData['heightValue_mobile'] : '',
        
        // Spacing
        'paddingTop' => isset($heroData['paddingTop']) ? (int)$heroData['paddingTop'] : 80,
        'paddingBottom' => isset($heroData['paddingBottom']) ? (int)$heroData['paddingBottom'] : 80,
        'paddingRight' => isset($heroData['paddingRight']) ? (int)$heroData['paddingRight'] : 20,
        'paddingLeft' => isset($heroData['paddingLeft']) ? (int)$heroData['paddingLeft'] : 20,
        'marginTop' => isset($heroData['marginTop']) ? (int)$heroData['marginTop'] : 0,
        'marginBottom' => isset($heroData['marginBottom']) ? (int)$heroData['marginBottom'] : 0,
        'marginRight' => isset($heroData['marginRight']) ? (int)$heroData['marginRight'] : 0,
        'marginLeft' => isset($heroData['marginLeft']) ? (int)$heroData['marginLeft'] : 0,
        
        // Typography
        'titleFontSize' => isset($heroData['titleFontSize']) ? (int)$heroData['titleFontSize'] : 36,
        'titleFontFamily' => isset($heroData['titleFontFamily']) ? sanitizeFontFamily($heroData['titleFontFamily']) : "'Noto Sans Hebrew', sans-serif",
        'titleFontWeight' => isset($heroData['titleFontWeight']) ? sanitizeSelect($heroData['titleFontWeight'], ['normal', 'bold']) : 'normal',
        'titleFontStyle' => isset($heroData['titleFontStyle']) ? sanitizeSelect($heroData['titleFontStyle'], ['normal', 'italic']) : 'normal',
        'titleTextDecoration' => isset($heroData['titleTextDecoration']) ? sanitizeSelect($heroData['titleTextDecoration'], ['none', 'underline']) : 'none',
        'subtitleFontSize' => isset($heroData['subtitleFontSize']) ? (int)$heroData['subtitleFontSize'] : 18,
        'subtitleFontFamily' => isset($heroData['subtitleFontFamily']) ? sanitizeFontFamily($heroData['subtitleFontFamily']) : "'Noto Sans Hebrew', sans-serif",
        'subtitleFontWeight' => isset($heroData['subtitleFontWeight']) ? sanitizeSelect($heroData['subtitleFontWeight'], ['normal', 'bold']) : 'normal',
        'subtitleFontStyle' => isset($heroData['subtitleFontStyle']) ? sanitizeSelect($heroData['subtitleFontStyle'], ['normal', 'italic']) : 'normal',
        'subtitleTextDecoration' => isset($heroData['subtitleTextDecoration']) ? sanitizeSelect($heroData['subtitleTextDecoration'], ['none', 'underline']) : 'none',
        'buttonFontSize' => isset($heroData['buttonFontSize']) ? (int)$heroData['buttonFontSize'] : 16,
        'buttonFontFamily' => isset($heroData['buttonFontFamily']) ? sanitizeFontFamily($heroData['buttonFontFamily']) : "'Noto Sans Hebrew', sans-serif",
        'buttonFontWeight' => isset($heroData['buttonFontWeight']) ? sanitizeSelect($heroData['buttonFontWeight'], ['normal', 'bold']) : 'normal',
        'buttonFontStyle' => isset($heroData['buttonFontStyle']) ? sanitizeSelect($heroData['buttonFontStyle'], ['normal', 'italic']) : 'normal',
        'buttonTextDecoration' => isset($heroData['buttonTextDecoration']) ? sanitizeSelect($heroData['buttonTextDecoration'], ['none', 'underline']) : 'none',
        'buttonPaddingTop' => isset($heroData['buttonPaddingTop']) ? (int)$heroData['buttonPaddingTop'] : 12,
        'buttonPaddingBottom' => isset($heroData['buttonPaddingBottom']) ? (int)$heroData['buttonPaddingBottom'] : 12,
        'buttonPaddingRight' => isset($heroData['buttonPaddingRight']) ? (int)$heroData['buttonPaddingRight'] : 32,
        'buttonPaddingLeft' => isset($heroData['buttonPaddingLeft']) ? (int)$heroData['buttonPaddingLeft'] : 32,
        'buttonMarginTop' => isset($heroData['buttonMarginTop']) ? (int)$heroData['buttonMarginTop'] : 0,
        'buttonMarginBottom' => isset($heroData['buttonMarginBottom']) ? (int)$heroData['buttonMarginBottom'] : 0,
        'buttonShadow' => isset($heroData['buttonShadow']) ? $heroData['buttonShadow'] : 'false',
        
        // Responsive Typography - Tablet
        'titleFontSize_tablet' => isset($heroData['titleFontSize_tablet']) && !empty($heroData['titleFontSize_tablet']) ? (int)$heroData['titleFontSize_tablet'] : '',
        'titleFontFamily_tablet' => isset($heroData['titleFontFamily_tablet']) ? $heroData['titleFontFamily_tablet'] : '',
        'titleFontWeight_tablet' => isset($heroData['titleFontWeight_tablet']) ? $heroData['titleFontWeight_tablet'] : '',
        'titleFontStyle_tablet' => isset($heroData['titleFontStyle_tablet']) ? $heroData['titleFontStyle_tablet'] : '',
        'titleTextDecoration_tablet' => isset($heroData['titleTextDecoration_tablet']) ? $heroData['titleTextDecoration_tablet'] : '',
        'subtitleFontSize_tablet' => isset($heroData['subtitleFontSize_tablet']) && !empty($heroData['subtitleFontSize_tablet']) ? (int)$heroData['subtitleFontSize_tablet'] : '',
        'subtitleFontFamily_tablet' => isset($heroData['subtitleFontFamily_tablet']) ? $heroData['subtitleFontFamily_tablet'] : '',
        'subtitleFontWeight_tablet' => isset($heroData['subtitleFontWeight_tablet']) ? $heroData['subtitleFontWeight_tablet'] : '',
        'subtitleFontStyle_tablet' => isset($heroData['subtitleFontStyle_tablet']) ? $heroData['subtitleFontStyle_tablet'] : '',
        'subtitleTextDecoration_tablet' => isset($heroData['subtitleTextDecoration_tablet']) ? $heroData['subtitleTextDecoration_tablet'] : '',
        'buttonFontSize_tablet' => isset($heroData['buttonFontSize_tablet']) && !empty($heroData['buttonFontSize_tablet']) ? (int)$heroData['buttonFontSize_tablet'] : '',
        'buttonFontFamily_tablet' => isset($heroData['buttonFontFamily_tablet']) ? $heroData['buttonFontFamily_tablet'] : '',
        'buttonFontWeight_tablet' => isset($heroData['buttonFontWeight_tablet']) ? $heroData['buttonFontWeight_tablet'] : '',
        'buttonFontStyle_tablet' => isset($heroData['buttonFontStyle_tablet']) ? $heroData['buttonFontStyle_tablet'] : '',
        'buttonTextDecoration_tablet' => isset($heroData['buttonTextDecoration_tablet']) ? $heroData['buttonTextDecoration_tablet'] : '',
        'buttonPaddingTop_tablet' => isset($heroData['buttonPaddingTop_tablet']) && !empty($heroData['buttonPaddingTop_tablet']) ? (int)$heroData['buttonPaddingTop_tablet'] : '',
        'buttonPaddingBottom_tablet' => isset($heroData['buttonPaddingBottom_tablet']) && !empty($heroData['buttonPaddingBottom_tablet']) ? (int)$heroData['buttonPaddingBottom_tablet'] : '',
        'buttonPaddingRight_tablet' => isset($heroData['buttonPaddingRight_tablet']) && !empty($heroData['buttonPaddingRight_tablet']) ? (int)$heroData['buttonPaddingRight_tablet'] : '',
        'buttonPaddingLeft_tablet' => isset($heroData['buttonPaddingLeft_tablet']) && !empty($heroData['buttonPaddingLeft_tablet']) ? (int)$heroData['buttonPaddingLeft_tablet'] : '',
        'buttonMarginTop_tablet' => isset($heroData['buttonMarginTop_tablet']) && !empty($heroData['buttonMarginTop_tablet']) ? (int)$heroData['buttonMarginTop_tablet'] : '',
        'buttonMarginBottom_tablet' => isset($heroData['buttonMarginBottom_tablet']) && !empty($heroData['buttonMarginBottom_tablet']) ? (int)$heroData['buttonMarginBottom_tablet'] : '',
        'buttonShadow_tablet' => isset($heroData['buttonShadow_tablet']) ? $heroData['buttonShadow_tablet'] : '',
        
        // Responsive Typography - Mobile
        'titleFontSize_mobile' => isset($heroData['titleFontSize_mobile']) && !empty($heroData['titleFontSize_mobile']) ? (int)$heroData['titleFontSize_mobile'] : '',
        'titleFontFamily_mobile' => isset($heroData['titleFontFamily_mobile']) ? $heroData['titleFontFamily_mobile'] : '',
        'titleFontWeight_mobile' => isset($heroData['titleFontWeight_mobile']) ? $heroData['titleFontWeight_mobile'] : '',
        'titleFontStyle_mobile' => isset($heroData['titleFontStyle_mobile']) ? $heroData['titleFontStyle_mobile'] : '',
        'titleTextDecoration_mobile' => isset($heroData['titleTextDecoration_mobile']) ? $heroData['titleTextDecoration_mobile'] : '',
        'subtitleFontSize_mobile' => isset($heroData['subtitleFontSize_mobile']) && !empty($heroData['subtitleFontSize_mobile']) ? (int)$heroData['subtitleFontSize_mobile'] : '',
        'subtitleFontFamily_mobile' => isset($heroData['subtitleFontFamily_mobile']) ? $heroData['subtitleFontFamily_mobile'] : '',
        'subtitleFontWeight_mobile' => isset($heroData['subtitleFontWeight_mobile']) ? $heroData['subtitleFontWeight_mobile'] : '',
        'subtitleFontStyle_mobile' => isset($heroData['subtitleFontStyle_mobile']) ? $heroData['subtitleFontStyle_mobile'] : '',
        'subtitleTextDecoration_mobile' => isset($heroData['subtitleTextDecoration_mobile']) ? $heroData['subtitleTextDecoration_mobile'] : '',
        'buttonFontSize_mobile' => isset($heroData['buttonFontSize_mobile']) && !empty($heroData['buttonFontSize_mobile']) ? (int)$heroData['buttonFontSize_mobile'] : '',
        'buttonFontFamily_mobile' => isset($heroData['buttonFontFamily_mobile']) ? $heroData['buttonFontFamily_mobile'] : '',
        'buttonFontWeight_mobile' => isset($heroData['buttonFontWeight_mobile']) ? $heroData['buttonFontWeight_mobile'] : '',
        'buttonFontStyle_mobile' => isset($heroData['buttonFontStyle_mobile']) ? $heroData['buttonFontStyle_mobile'] : '',
        'buttonTextDecoration_mobile' => isset($heroData['buttonTextDecoration_mobile']) ? $heroData['buttonTextDecoration_mobile'] : '',
        'buttonPaddingTop_mobile' => isset($heroData['buttonPaddingTop_mobile']) && !empty($heroData['buttonPaddingTop_mobile']) ? (int)$heroData['buttonPaddingTop_mobile'] : '',
        'buttonPaddingBottom_mobile' => isset($heroData['buttonPaddingBottom_mobile']) && !empty($heroData['buttonPaddingBottom_mobile']) ? (int)$heroData['buttonPaddingBottom_mobile'] : '',
        'buttonPaddingRight_mobile' => isset($heroData['buttonPaddingRight_mobile']) && !empty($heroData['buttonPaddingRight_mobile']) ? (int)$heroData['buttonPaddingRight_mobile'] : '',
        'buttonPaddingLeft_mobile' => isset($heroData['buttonPaddingLeft_mobile']) && !empty($heroData['buttonPaddingLeft_mobile']) ? (int)$heroData['buttonPaddingLeft_mobile'] : '',
        'buttonMarginTop_mobile' => isset($heroData['buttonMarginTop_mobile']) && !empty($heroData['buttonMarginTop_mobile']) ? (int)$heroData['buttonMarginTop_mobile'] : '',
        'buttonMarginBottom_mobile' => isset($heroData['buttonMarginBottom_mobile']) && !empty($heroData['buttonMarginBottom_mobile']) ? (int)$heroData['buttonMarginBottom_mobile'] : '',
        'buttonShadow_mobile' => isset($heroData['buttonShadow_mobile']) ? $heroData['buttonShadow_mobile'] : '',
        
        // Button Features
        'buttonsMobileDisplay' => isset($heroData['buttonsMobileDisplay']) ? sanitizeSelect($heroData['buttonsMobileDisplay'], ['horizontal', 'vertical']) : 'horizontal',
        
        // Custom
        'customClass' => isset($heroData['customClass']) ? sanitizeClassName($heroData['customClass']) : '',
        'customId' => isset($heroData['customId']) ? sanitizeId($heroData['customId']) : '',
        
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Process buttons array
    if (isset($heroData['buttons']) && is_array($heroData['buttons'])) {
        $cleanButtons = [];
        foreach ($heroData['buttons'] as $button) {
            if (is_array($button)) {
                $cleanButtons[] = [
                    'text' => strip_tags(trim($button['text'] ?? 'כפתור')),
                    'link' => filter_var(trim($button['link'] ?? '#'), FILTER_SANITIZE_URL),
                    'newTab' => isset($button['newTab']) ? (bool)$button['newTab'] : false,
                    'style' => sanitizeSelect($button['style'] ?? 'filled', ['filled', 'outline', 'white', 'black', 'text']),
                    'paddingTop' => isset($button['paddingTop']) && !empty($button['paddingTop']) ? (int)$button['paddingTop'] : '',
                    'paddingBottom' => isset($button['paddingBottom']) && !empty($button['paddingBottom']) ? (int)$button['paddingBottom'] : '',
                    'paddingRight' => isset($button['paddingRight']) && !empty($button['paddingRight']) ? (int)$button['paddingRight'] : '',
                    'paddingLeft' => isset($button['paddingLeft']) && !empty($button['paddingLeft']) ? (int)$button['paddingLeft'] : '',
                    'rounded' => isset($button['rounded']) ? max(0, min(4, (int)$button['rounded'])) : 0,
                    'fullWidth' => isset($button['fullWidth']) ? (bool)$button['fullWidth'] : false,
                    'icon' => isset($button['icon']) ? strip_tags(trim($button['icon'])) : ''
                ];
            }
        }
        $cleanHeroData['buttons'] = $cleanButtons;
    } else {
        // Convert legacy button to new format
        if (!empty($cleanHeroData['buttonText']) && !empty($cleanHeroData['buttonLink'])) {
            $cleanHeroData['buttons'] = [
                [
                    'text' => $cleanHeroData['buttonText'],
                    'link' => $cleanHeroData['buttonLink'],
                    'newTab' => $cleanHeroData['buttonNewTab'],
                    'style' => 'filled'
                ]
            ];
        }
    }
    
    // Save to JSON file
    $dataDir = __DIR__ . '/../data';
    if (!is_dir($dataDir)) {
        if (!mkdir($dataDir, 0755, true)) {
            throw new Exception('לא ניתן ליצור תיקיית נתונים');
        }
    }
    
    $heroFile = $dataDir . '/hero.json';
    $jsonData = json_encode($cleanHeroData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    if (file_put_contents($heroFile, $jsonData) === false) {
        throw new Exception('שגיאה בשמירת הקובץ');
    }
    
    // Log successful save
    error_log("Hero data saved successfully: " . $heroFile);
    
    echo json_encode([
        'success' => true,
        'message' => 'נתוני Hero נשמרו בהצלחה',
        'data' => $cleanHeroData
    ]);
    
} catch (Exception $e) {
    error_log("Error saving hero data: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Helper functions for validation
 */
function sanitizeColor($color) {
    if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
        return $color;
    }
    return '#000000'; // Default to black if invalid
}

function sanitizeSelect($value, $allowedValues) {
    return in_array($value, $allowedValues) ? $value : $allowedValues[0];
}

function sanitizeContentPosition($position) {
    $allowedPositions = [
        'top-left', 'top-center', 'top-right',
        'center-left', 'center-center', 'center-right',
        'bottom-left', 'bottom-center', 'bottom-right'
    ];
    return in_array($position, $allowedPositions) ? $position : 'center-center';
}

function sanitizeGradientDirection($direction) {
    $allowedDirections = ['to-t', 'to-b', 'to-l', 'to-r', 'to-tl', 'to-tr', 'to-bl', 'to-br'];
    return in_array($direction, $allowedDirections) ? $direction : 'to-b';
}

function sanitizeClassName($className) {
    // Remove invalid characters for CSS class names
    return preg_replace('/[^a-zA-Z0-9_-]/', '', trim($className));
}

function sanitizeId($id) {
    // Remove invalid characters for HTML IDs
    return preg_replace('/[^a-zA-Z0-9_-]/', '', trim($id));
}

function sanitizeFontFamily($fontFamily) {
    $allowedFonts = [
        "'Noto Sans Hebrew', sans-serif",
        "'Heebo', sans-serif", 
        "'Open Sans Hebrew', 'Noto Sans Hebrew', sans-serif",
        "'Assistant', sans-serif",
        "'Varela Round', sans-serif",
        "'Poppins', sans-serif",
        "'Montserrat', sans-serif"
    ];
    return in_array($fontFamily, $allowedFonts) ? $fontFamily : "'Noto Sans Hebrew', sans-serif";
} 