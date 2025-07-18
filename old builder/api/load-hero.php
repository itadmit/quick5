<?php
/**
 * Load Hero Data API
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

try {
    $heroFile = __DIR__ . '/../data/hero.json';
    
    // Default hero data with all new fields
    $defaultHeroData = [
        // Content
        'title' => 'ברוכים הבאים לחנות שלנו',
        'subtitle' => 'גלה את המוצרים הטובים ביותר במחירים הכי טובים',
        'buttonText' => 'קנה עכשיו',
        'buttonLink' => '/products',
        'buttonNewTab' => false,
        
        // Background
        'bgType' => 'color',
        'bgColor' => '#3B82F6',
        'bgGradient1' => '#3B82F6',
        'bgGradient2' => '#1E40AF',
        'bgGradientDirection' => 'to-b',
        'bgImage' => '',
        'bgImageSize' => 'cover',
        'bgImagePosition' => 'center',
        'bgImageRepeat' => 'no-repeat',
        'bgImage_mobile' => '',
        'bgVideo' => '',
        'bgVideo_mobile' => '',
        'bgVideoOverlay' => 30,
        
        // Colors
        'titleColor' => '#FFFFFF',
        'subtitleColor' => '#E5E7EB',
        'buttonBgColor' => '#F59E0B',
        'buttonTextColor' => '#FFFFFF',
        'buttonBorderColor' => '#F59E0B',
        'buttonBgColorHover' => '#E5A712',
        'buttonTextColorHover' => '#FFFFFF',
        'buttonBorderColorHover' => '#E5A712',
        
        // Layout
        'width' => 'full',
        'customWidth' => 800,
        'customWidthUnit' => 'px',
        'contentPosition' => 'center-center',
        'heightType' => 'auto',
        'heightValue' => 500,
        'heightType_tablet' => '',
        'heightValue_tablet' => '',
        'heightType_mobile' => '',
        'heightValue_mobile' => '',
        
        // Spacing
        'paddingTop' => 80,
        'paddingBottom' => 80,
        'paddingRight' => 20,
        'paddingLeft' => 20,
        'marginTop' => 0,
        'marginBottom' => 0,
        'marginRight' => 0,
        'marginLeft' => 0,
        
        // Typography
        'titleFontSize' => 36,
        'titleFontFamily' => "'Noto Sans Hebrew', sans-serif",
        'subtitleFontSize' => 18,
        'subtitleFontFamily' => "'Noto Sans Hebrew', sans-serif",
        'buttonFontSize' => 16,
        'buttonFontFamily' => "'Noto Sans Hebrew', sans-serif",
        'buttonPaddingTop' => 12,
        'buttonPaddingBottom' => 12,
        'buttonPaddingRight' => 32,
        'buttonPaddingLeft' => 32,
        'buttonMarginTop' => 0,
        'buttonMarginBottom' => 0,
        'buttonShadow' => 'false',
        
        // Responsive Typography - Tablet
        'titleFontSize_tablet' => '',
        'titleFontFamily_tablet' => '',
        'titleFontWeight_tablet' => '',
        'titleFontStyle_tablet' => '',
        'titleTextDecoration_tablet' => '',
        'subtitleFontSize_tablet' => '',
        'subtitleFontFamily_tablet' => '',
        'subtitleFontWeight_tablet' => '',
        'subtitleFontStyle_tablet' => '',
        'subtitleTextDecoration_tablet' => '',
        'buttonFontSize_tablet' => '',
        'buttonFontFamily_tablet' => '',
        'buttonFontWeight_tablet' => '',
        'buttonFontStyle_tablet' => '',
        'buttonTextDecoration_tablet' => '',
        'buttonPaddingTop_tablet' => '',
        'buttonPaddingBottom_tablet' => '',
        'buttonPaddingRight_tablet' => '',
        'buttonPaddingLeft_tablet' => '',
        'buttonMarginTop_tablet' => '',
        'buttonMarginBottom_tablet' => '',
        'buttonShadow_tablet' => '',
        
        // Responsive Typography - Mobile
        'titleFontSize_mobile' => '',
        'titleFontFamily_mobile' => '',
        'titleFontWeight_mobile' => '',
        'titleFontStyle_mobile' => '',
        'titleTextDecoration_mobile' => '',
        'subtitleFontSize_mobile' => '',
        'subtitleFontFamily_mobile' => '',
        'subtitleFontWeight_mobile' => '',
        'subtitleFontStyle_mobile' => '',
        'subtitleTextDecoration_mobile' => '',
        'buttonFontSize_mobile' => '',
        'buttonFontFamily_mobile' => '',
        'buttonFontWeight_mobile' => '',
        'buttonFontStyle_mobile' => '',
        'buttonTextDecoration_mobile' => '',
        'buttonPaddingTop_mobile' => '',
        'buttonPaddingBottom_mobile' => '',
        'buttonPaddingRight_mobile' => '',
        'buttonPaddingLeft_mobile' => '',
        'buttonMarginTop_mobile' => '',
        'buttonMarginBottom_mobile' => '',
        'buttonShadow_mobile' => '',
        
        // Buttons
        'buttons' => [
            [
                'text' => 'קנה עכשיו',
                'link' => '/products',
                'newTab' => false,
                'style' => 'filled',
                'paddingTop' => '',
                'paddingBottom' => '',
                'paddingRight' => '',
                'paddingLeft' => '',
                'rounded' => 0,
                'fullWidth' => false,
                'icon' => ''
            ]
        ],
        
        // Button Features
        'buttonsMobileDisplay' => 'horizontal',
        
        // Custom
        'customClass' => '',
        'customId' => ''
    ];
    
    $heroData = $defaultHeroData;
    
    // Load saved data if exists
    if (file_exists($heroFile)) {
        $savedData = file_get_contents($heroFile);
        if ($savedData) {
            $decodedData = json_decode($savedData, true);
            if ($decodedData) {
                // Merge with defaults to ensure all fields exist
                $heroData = array_merge($defaultHeroData, $decodedData);
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'hero' => $heroData,
        'file_exists' => file_exists($heroFile),
        'last_modified' => file_exists($heroFile) ? date('Y-m-d H:i:s', filemtime($heroFile)) : null
    ]);
    
} catch (Exception $e) {
    error_log("Error loading hero data: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 