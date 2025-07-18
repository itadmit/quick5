<?php
/**
 * Hero Block - המודול האמיתי שמציג את ה-Hero באתר
 */

// Load the global section width helper
require_once __DIR__ . '/../includes/SectionWidthHelper.php';

// Button style helper functions
function getButtonClasses($style) {
    switch ($style) {
        case 'filled':
            return 'hover:opacity-90';
        case 'outline':
            return 'border-2 hover:bg-opacity-10';
        case 'white':
            return 'bg-white text-gray-900 hover:bg-gray-100';
        case 'black':
            return 'bg-black text-white hover:bg-gray-900';
        case 'text':
            return 'bg-transparent hover:bg-opacity-10';
        default:
            return 'hover:opacity-90';
    }
}

function getButtonInlineStyles($style, $heroData) {
    $bgColor = $heroData['buttonBgColor'] ?? '#F59E0B';
    $textColor = $heroData['buttonTextColor'] ?? '#FFFFFF';
    $borderColor = $heroData['buttonBorderColor'] ?? $bgColor;
    $bgColorHover = $heroData['buttonBgColorHover'] ?? '#E5A712';
    $textColorHover = $heroData['buttonTextColorHover'] ?? '#FFFFFF';
    $borderColorHover = $heroData['buttonBorderColorHover'] ?? $bgColorHover;
    
    switch ($style) {
        case 'filled':
            return "background-color: {$bgColor}; color: {$textColor}; border: 2px solid {$borderColor};";
        case 'outline':
            return "background-color: transparent; color: {$bgColor}; border: 2px solid {$borderColor};";
        case 'white':
            return "background-color: #FFFFFF; color: #1F2937; border: 2px solid #FFFFFF;";
        case 'black':
            return "background-color: #000000; color: #FFFFFF; border: 2px solid #000000;";
        case 'text':
            return "background-color: transparent; color: {$textColor}; text-decoration: underline; border: none;";
        default:
            return "background-color: {$bgColor}; color: {$textColor}; border: 2px solid {$borderColor};";
    }
}

// Load hero data from database (same as API)
function getHeroData() {
    try {
        // Include database and auth
        require_once __DIR__ . '/../../includes/config.php';
        require_once __DIR__ . '/../../includes/auth.php';
        
        // Get current store ID
        $storeId = 1; // For now, use default store
        $pageType = 'home';
        
        $pdo = getDB();
        
        // Try to load from database first
        $stmt = $pdo->prepare("
            SELECT page_data, updated_at 
            FROM builder_pages 
            WHERE store_id = ? AND page_type = ?
        ");
        $stmt->execute([$storeId, $pageType]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Found in database
            $pageData = json_decode($result['page_data'], true);
            if ($pageData && isset($pageData['sections'])) {
                $heroSection = array_filter($pageData['sections'], function($section) {
                    return $section['type'] === 'hero';
                });
                
                if (!empty($heroSection)) {
                    $heroSection = array_shift($heroSection);
                    if (isset($heroSection['data'])) {
                        return array_merge(getDefaultHeroData(), $heroSection['data']);
                    }
                }
            }
        }
        
        // No data in database, return defaults
        return getDefaultHeroData();
        
    } catch (Exception $e) {
        error_log("Error loading hero data: " . $e->getMessage());
        return getDefaultHeroData();
    }
}

// Default hero data
function getDefaultHeroData() {
    return [
        // Content
        'title' => 'ברוכים הבאים לחנות שלנו',
        'subtitle' => 'גלה את המוצרים הטובים ביותר במחירים הכי טובים',
        'buttons' => [
            [
                'text' => 'קנה עכשיו',
                'link' => '/products',
                'newTab' => false,
                'style' => 'filled'
            ]
        ],
        // Legacy single button fields for backward compatibility
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
        
        // Layout
        'width' => 'container',
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
        'titleFontWeight' => 'normal',
        'titleFontStyle' => 'normal',
        'titleTextDecoration' => 'none',
        'titleLineHeight' => '1.2',
        'titleTextTransform' => 'none',
        'titleTag' => 'h2',
        'subtitleFontSize' => 18,
        'subtitleFontFamily' => "'Noto Sans Hebrew', sans-serif",
        'subtitleFontWeight' => 'normal',
        'subtitleFontStyle' => 'normal',
        'subtitleTextDecoration' => 'none',
        'subtitleLineHeight' => '1.5',
        'subtitleTextTransform' => 'none',
        'subtitleTag' => 'p',
        'buttonFontSize' => 16,
        'buttonFontFamily' => "'Noto Sans Hebrew', sans-serif",
        'buttonFontWeight' => 'normal',
        'buttonFontStyle' => 'normal',
        'buttonTextDecoration' => 'none',
        'buttonPaddingTop' => 12,
        'buttonPaddingBottom' => 12,
        'buttonPaddingRight' => 32,
        'buttonPaddingLeft' => 32,
        'buttonMarginTop' => 0,
        'buttonMarginBottom' => 0,
        'buttonShadow' => 'false',
        
        // Button States (normal/hover)
        'buttonStateMode' => 'normal',
        'buttonBgColorHover' => '#E5A712',
        'buttonTextColorHover' => '#FFFFFF',
        'buttonBorderColorHover' => '#E5A712',
        
        // Responsive Typography - Add empty defaults
        'titleFontSize_tablet' => '',
        'titleFontFamily_tablet' => '',
        'titleFontWeight_tablet' => '',
        'titleFontStyle_tablet' => '',
        'titleTextDecoration_tablet' => '',
        'titleLineHeight_tablet' => '',
        'titleTextTransform_tablet' => '',
        'subtitleFontSize_tablet' => '',
        'subtitleFontFamily_tablet' => '',
        'subtitleFontWeight_tablet' => '',
        'subtitleFontStyle_tablet' => '',
        'subtitleTextDecoration_tablet' => '',
        'subtitleLineHeight_tablet' => '',
        'subtitleTextTransform_tablet' => '',
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
        'titleFontSize_mobile' => '',
        'titleFontFamily_mobile' => '',
        'titleFontWeight_mobile' => '',
        'titleFontStyle_mobile' => '',
        'titleTextDecoration_mobile' => '',
        'titleLineHeight_mobile' => '',
        'titleTextTransform_mobile' => '',
        'subtitleFontSize_mobile' => '',
        'subtitleFontFamily_mobile' => '',
        'subtitleFontWeight_mobile' => '',
        'subtitleFontStyle_mobile' => '',
        'subtitleTextDecoration_mobile' => '',
        'subtitleLineHeight_mobile' => '',
        'subtitleTextTransform_mobile' => '',
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
        
        // Custom
        'customClass' => '',
        'customId' => ''
    ];
}

// Helper functions for Hero specific features
function getWidthClass($heroData) {
    // תמיד נחזיר relative z-10 לתוכן הפנימי
    return 'relative z-10';
}

function getCustomWidthStyle($heroData) {
    // לא צריך עוד - הרוחב נקבע ברמת הסקשן
    return '';
}

function getContentPositionClasses($position) {
    $positions = [
        'top-left' => 'items-start justify-end text-left',
        'top-center' => 'items-start justify-center text-center',
        'top-right' => 'items-start justify-start text-right',
        'center-left' => 'items-center justify-end text-left',
        'center-center' => 'items-center justify-center text-center',
        'center-right' => 'items-center justify-start text-right',
        'bottom-left' => 'items-end justify-end text-left',
        'bottom-center' => 'items-end justify-center text-center',
        'bottom-right' => 'items-end justify-start text-right'
    ];
    
    return $positions[$position] ?? $positions['center-center'];
}

function getBackgroundStyle($data) {
    $style = '';
    
    switch ($data['bgType']) {
        case 'color':
            $style = "background-color: {$data['bgColor']};";
            break;
            
        case 'gradient':
            $direction = $data['bgGradientDirection'] ?? 'to-b';
            $color1 = $data['bgGradient1'] ?? '#3B82F6';
            $color2 = $data['bgGradient2'] ?? '#1E40AF';
            
            $gradientMap = [
                'to-t' => 'to top',
                'to-b' => 'to bottom',
                'to-l' => 'to left',
                'to-r' => 'to right',
                'to-tl' => 'to top left',
                'to-tr' => 'to top right',
                'to-bl' => 'to bottom left',
                'to-br' => 'to bottom right'
            ];
            
            $cssDirection = $gradientMap[$direction] ?? 'to bottom';
            $style = "background: linear-gradient({$cssDirection}, {$color1}, {$color2});";
            break;
            
        case 'image':
            if (!empty($data['bgImage'])) {
                $bgSize = $data['bgImageSize'] ?? 'cover';
                $bgPosition = $data['bgImagePosition'] ?? 'center';
                $bgRepeat = $data['bgImageRepeat'] ?? 'no-repeat';
                $style = "background-image: url('{$data['bgImage']}'); background-size: {$bgSize}; background-position: {$bgPosition}; background-repeat: {$bgRepeat};";
            } else {
                $style = "background-color: {$data['bgColor']};";
            }
            break;
            
        case 'video':
            $style = "background-color: {$data['bgColor']};";
            break;
            
        default:
            $style = "background-color: {$data['bgColor']};";
    }
    
    return $style;
}

function getSpacingStyle($data) {
    $style = '';
    
    // Padding
    $style .= "padding-top: {$data['paddingTop']}px; ";
    $style .= "padding-bottom: {$data['paddingBottom']}px; ";
    
    // For full width, don't add horizontal padding - let CSS handle it
    if (($data['width'] ?? 'container') !== 'full') {
        $style .= "padding-right: {$data['paddingRight']}px; ";
        $style .= "padding-left: {$data['paddingLeft']}px; ";
    }
    
    // Margin
    $style .= "margin-top: {$data['marginTop']}px; ";
    $style .= "margin-bottom: {$data['marginBottom']}px; ";
    
    // Don't add horizontal margins - let CSS classes handle centering
    // Only add horizontal margins if there's no width setting (legacy support)
    if (!isset($data['width'])) {
        $style .= "margin-right: {$data['marginRight']}px; ";
        $style .= "margin-left: {$data['marginLeft']}px; ";
    }
    
    return $style;
}

function getHeightStyle($data, $deviceType = 'desktop') {
    $style = '';
    
    // Get responsive height values
    $heightType = getResponsiveValue($data, 'heightType', $deviceType) ?: ($data['heightType'] ?? 'auto');
    $heightValue = getResponsiveValue($data, 'heightValue', $deviceType) ?: ($data['heightValue'] ?? '500');
    
    if ($heightType === 'auto' || empty($heightType)) {
        $style .= 'min-height: 500px;'; // Default minimum height
    } else {
        $style .= "height: {$heightValue}{$heightType};";
    }
    
    // Add responsive heights comment for clarity
    $hasTabletHeight = !empty($data['heightType_tablet']) && $data['heightType_tablet'] !== '';
    $hasMobileHeight = !empty($data['heightType_mobile']) && $data['heightType_mobile'] !== '';
    
    if ($hasTabletHeight || $hasMobileHeight) {
        $style .= ' /* Responsive heights handled by CSS */';
    }
    
    return $style;
}

$heroData = getHeroData();

// Detect mobile/tablet from User-Agent for server-side responsive rendering
function detectDeviceType() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Simple mobile detection
    if (preg_match('/Mobile|Android|iPhone|iPad|BlackBerry|Windows Phone/i', $userAgent)) {
        // More specific tablet detection
        if (preg_match('/iPad|Android.*Tablet|Windows.*Touch/i', $userAgent)) {
            return 'tablet';
        }
        return 'mobile';
    }
    
    return 'desktop';
}

// Helper function to get responsive value on server-side
function getResponsiveValue($data, $baseName, $deviceType = 'desktop') {
    if ($deviceType === 'desktop') {
        return $data[$baseName] ?? '';
    }
    
    $responsiveProperty = $baseName . '_' . $deviceType;
    $responsiveValue = $data[$responsiveProperty] ?? '';
    
    // If responsive value exists and is not empty, use it
    if ($responsiveValue !== '' && $responsiveValue !== null && $responsiveValue !== "0") {
        return $responsiveValue;
    }
    
    // Otherwise, fallback to desktop value
    return $data[$baseName] ?? '';
}

// Detect current device type
$deviceType = detectDeviceType();

// Use global width system for section
$sectionWidthClass = getSectionWidthClass($heroData);
$sectionWidthStyle = getSectionWidthStyle($heroData);

// Hero specific settings
$positionClasses = getContentPositionClasses($heroData['contentPosition']);
$backgroundStyle = getBackgroundStyle($heroData);
$spacingStyle = getSpacingStyle($heroData);
$heightStyle = getHeightStyle($heroData, $deviceType);
$customClass = !empty($heroData['customClass']) ? ' ' . $heroData['customClass'] : '';
$customId = !empty($heroData['customId']) ? $heroData['customId'] : 'heroSection';
?>

<!-- Hero Section -->
<section id="<?php echo htmlspecialchars($customId); ?>" 
         class="hero-section relative overflow-hidden flex <?php echo $positionClasses; ?> <?php echo $sectionWidthClass; ?><?php echo $customClass; ?>"
         style="<?php echo $backgroundStyle; ?> <?php echo $spacingStyle; ?> <?php echo $heightStyle; ?> <?php echo $sectionWidthStyle; ?>"
         data-builder-block="hero">
        
        <!-- Background Video (if applicable) -->
        <?php if ($heroData['bgType'] === 'video' && (!empty($heroData['bgVideo']) || !empty($heroData['bgVideo_mobile']))): ?>
        <?php
        // Choose the right video source based on device type
        $videoSrc = $heroData['bgVideo']; // default desktop
        if ($deviceType === 'mobile' && !empty($heroData['bgVideo_mobile'])) {
            $videoSrc = $heroData['bgVideo_mobile'];
        } elseif ($deviceType === 'tablet' && !empty($heroData['bgVideo_tablet'])) {
            $videoSrc = $heroData['bgVideo_tablet'];
        }
        ?>
        <video id="heroBackgroundVideo" autoplay muted loop class="absolute inset-0 w-full h-full object-cover z-0">
            <source id="heroVideoSource" src="<?php echo htmlspecialchars($videoSrc); ?>" type="video/mp4">
        </video>
        <?php 
        $videoOverlay = isset($heroData['bgVideoOverlay']) ? intval($heroData['bgVideoOverlay']) : 30;
        ?>
        <div class="absolute inset-0 bg-black z-0" style="opacity: <?php echo $videoOverlay / 100; ?>;"></div>
        <?php endif; ?>
        
        <!-- Dynamic Button Hover Styles -->
        <?php 
        $bgColorHover = $heroData['buttonBgColorHover'] ?? '#E5A712';
        $textColorHover = $heroData['buttonTextColorHover'] ?? '#FFFFFF';
        $borderColorHover = $heroData['buttonBorderColorHover'] ?? $bgColorHover;
        ?>
        <style id="heroButtonHoverStyles">
            #heroSection .hero-button:hover {
                background-color: <?php echo $bgColorHover; ?> !important;
                color: <?php echo $textColorHover; ?> !important;
                border-color: <?php echo $borderColorHover; ?> !important;
            }
            
            #heroSection .hero-button.outline-style:hover {
                background-color: <?php echo $bgColorHover; ?> !important;
                color: <?php echo $textColorHover; ?> !important;
            }
        </style>
        
        <div class="relative z-10 w-full">
        <!-- Title -->
        <?php if (!empty(trim($heroData['title']))): ?>
        <?php 
        $titleTag = $heroData['titleTag'] ?? 'h2';
        ?>
        <<?php echo $titleTag; ?> id="heroTitle" 
            class="mb-6 leading-tight"
            style="color: <?php echo htmlspecialchars($heroData['titleColor']); ?>; font-size: <?php echo getResponsiveValue($heroData, 'titleFontSize', $deviceType) ?: $heroData['titleFontSize']; ?>px; font-family: <?php echo getResponsiveValue($heroData, 'titleFontFamily', $deviceType) ?: $heroData['titleFontFamily']; ?>; font-weight: <?php echo getResponsiveValue($heroData, 'titleFontWeight', $deviceType) ?: $heroData['titleFontWeight']; ?>; font-style: <?php echo getResponsiveValue($heroData, 'titleFontStyle', $deviceType) ?: $heroData['titleFontStyle']; ?>; text-decoration: <?php echo getResponsiveValue($heroData, 'titleTextDecoration', $deviceType) ?: $heroData['titleTextDecoration']; ?>; line-height: <?php echo getResponsiveValue($heroData, 'titleLineHeight', $deviceType) ?: ($heroData['titleLineHeight'] ?? '1.2'); ?>; text-transform: <?php echo getResponsiveValue($heroData, 'titleTextTransform', $deviceType) ?: ($heroData['titleTextTransform'] ?? 'none'); ?>;">
            <?php echo htmlspecialchars($heroData['title']); ?>
        </<?php echo $titleTag; ?>>
        <?php endif; ?>
        
        <!-- Content -->
        <?php if (!empty(trim($heroData['subtitle']))): ?>
        <?php 
        $subtitleTag = $heroData['subtitleTag'] ?? 'p';
        ?>
        <<?php echo $subtitleTag; ?> id="heroSubtitle" 
           class="mb-8 max-w-2xl mx-auto leading-relaxed"
           style="color: <?php echo htmlspecialchars($heroData['subtitleColor']); ?>; font-size: <?php echo getResponsiveValue($heroData, 'subtitleFontSize', $deviceType) ?: $heroData['subtitleFontSize']; ?>px; font-family: <?php echo getResponsiveValue($heroData, 'subtitleFontFamily', $deviceType) ?: $heroData['subtitleFontFamily']; ?>; font-weight: <?php echo getResponsiveValue($heroData, 'subtitleFontWeight', $deviceType) ?: $heroData['subtitleFontWeight']; ?>; font-style: <?php echo getResponsiveValue($heroData, 'subtitleFontStyle', $deviceType) ?: $heroData['subtitleFontStyle']; ?>; text-decoration: <?php echo getResponsiveValue($heroData, 'subtitleTextDecoration', $deviceType) ?: $heroData['subtitleTextDecoration']; ?>; line-height: <?php echo getResponsiveValue($heroData, 'subtitleLineHeight', $deviceType) ?: ($heroData['subtitleLineHeight'] ?? '1.5'); ?>; text-transform: <?php echo getResponsiveValue($heroData, 'subtitleTextTransform', $deviceType) ?: ($heroData['subtitleTextTransform'] ?? 'none'); ?>;">
            <?php echo strip_tags($heroData['subtitle'], '<br><strong><b><em><i><u><span>'); ?>
        </<?php echo $subtitleTag; ?>>
        <?php endif; ?>
        
        <!-- Buttons -->
        <?php 
        $hasVisibleButtons = false;
        if (!empty($heroData['buttons']) && is_array($heroData['buttons'])) {
            foreach ($heroData['buttons'] as $button) {
                if (!empty(trim($button['text'] ?? ''))) {
                    $hasVisibleButtons = true;
                    break;
                }
            }
        } else if (!empty(trim($heroData['buttonText'] ?? ''))) {
            $hasVisibleButtons = true;
        }
        ?>
        
        <?php if ($hasVisibleButtons): ?>
        <div class="<?php echo ($heroData['buttonsMobileDisplay'] ?? 'horizontal') === 'vertical' ? 'flex flex-col sm:flex-row' : 'flex flex-wrap'; ?> gap-4 justify-center">
            <?php if (!empty($heroData['buttons']) && is_array($heroData['buttons'])): ?>
                <?php foreach ($heroData['buttons'] as $index => $button): ?>
                    <?php if (!empty(trim($button['text'] ?? ''))): ?>
                    <?php 
                        // פינות מעוגלות - מספר בפיקסלים
                        $roundedValue = $button['rounded'] ?? 0;
                        
                        $fullWidth = ($button['fullWidth'] ?? false) ? 'w-full' : '';
                        $icon = $button['icon'] ?? '';
                        $hasIcon = !empty($icon);
                    ?>
                    <a id="heroButton<?php echo $index; ?>" 
                       href="<?php echo htmlspecialchars($button['link'] ?? '#'); ?>"
                       <?php echo ($button['newTab'] ?? false) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
                       class="hero-button inline-flex items-center justify-center <?php echo $fullWidth; ?> transition-all duration-300 transform hover:scale-105 <?php echo getButtonClasses($button['style'] ?? 'filled'); ?> <?php echo ($button['style'] === 'outline') ? 'outline-style' : ''; ?>"
                       style="border-radius: <?php echo $roundedValue; ?>px; font-size: <?php echo getResponsiveValue($heroData, 'buttonFontSize', $deviceType) ?: ($heroData['buttonFontSize'] ?? 16); ?>px; font-family: <?php echo getResponsiveValue($heroData, 'buttonFontFamily', $deviceType) ?: ($heroData['buttonFontFamily'] ?? "'Noto Sans Hebrew', sans-serif"); ?>; font-weight: <?php echo getResponsiveValue($heroData, 'buttonFontWeight', $deviceType) ?: ($heroData['buttonFontWeight'] ?? 'normal'); ?>; font-style: <?php echo getResponsiveValue($heroData, 'buttonFontStyle', $deviceType) ?: ($heroData['buttonFontStyle'] ?? 'normal'); ?>; text-decoration: <?php echo getResponsiveValue($heroData, 'buttonTextDecoration', $deviceType) ?: ($heroData['buttonTextDecoration'] ?? 'none'); ?>; padding-top: <?php echo isset($button['paddingTop']) && !empty($button['paddingTop']) ? $button['paddingTop'] : ($heroData['buttonPaddingTop'] ?? 12); ?>px; padding-bottom: <?php echo isset($button['paddingBottom']) && !empty($button['paddingBottom']) ? $button['paddingBottom'] : ($heroData['buttonPaddingBottom'] ?? 12); ?>px; padding-right: <?php echo isset($button['paddingRight']) && !empty($button['paddingRight']) ? $button['paddingRight'] : ($heroData['buttonPaddingRight'] ?? 32); ?>px; padding-left: <?php echo isset($button['paddingLeft']) && !empty($button['paddingLeft']) ? $button['paddingLeft'] : ($heroData['buttonPaddingLeft'] ?? 32); ?>px; margin-top: <?php echo isset($button['marginTop']) && !empty($button['marginTop']) ? $button['marginTop'] : ($heroData['buttonMarginTop'] ?? 0); ?>px; margin-bottom: <?php echo isset($button['marginBottom']) && !empty($button['marginBottom']) ? $button['marginBottom'] : ($heroData['buttonMarginBottom'] ?? 0); ?>px; <?php echo ($heroData['buttonShadow'] === 'true') ? 'box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);' : ''; ?> <?php echo getButtonInlineStyles($button['style'] ?? 'filled', $heroData); ?>">
                        <?php if ($hasIcon): ?>
                            <i class="<?php echo htmlspecialchars($icon); ?>" style="margin-left: 8px;"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($button['text']); ?>
                    </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback to legacy single button -->
                <?php if (!empty(trim($heroData['buttonText'] ?? ''))): ?>
                <a id="heroButton" 
                   href="<?php echo htmlspecialchars($heroData['buttonLink'] ?? '#'); ?>"
                   <?php echo ($heroData['buttonNewTab'] ?? false) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
                   class="hero-button inline-block rounded-lg hover:opacity-90 transition-all duration-300 transform hover:scale-105"
                   style="background-color: <?php echo htmlspecialchars($heroData['buttonBgColor'] ?? '#F59E0B'); ?>; color: <?php echo htmlspecialchars($heroData['buttonTextColor'] ?? '#FFFFFF'); ?>; font-size: <?php echo getResponsiveValue($heroData, 'buttonFontSize', $deviceType) ?: ($heroData['buttonFontSize'] ?? 16); ?>px; font-family: <?php echo getResponsiveValue($heroData, 'buttonFontFamily', $deviceType) ?: ($heroData['buttonFontFamily'] ?? "'Noto Sans Hebrew', sans-serif"); ?>; font-weight: <?php echo getResponsiveValue($heroData, 'buttonFontWeight', $deviceType) ?: ($heroData['buttonFontWeight'] ?? 'normal'); ?>; font-style: <?php echo getResponsiveValue($heroData, 'buttonFontStyle', $deviceType) ?: ($heroData['buttonFontStyle'] ?? 'normal'); ?>; text-decoration: <?php echo getResponsiveValue($heroData, 'buttonTextDecoration', $deviceType) ?: ($heroData['buttonTextDecoration'] ?? 'none'); ?>; padding-top: <?php echo $heroData['buttonPaddingTop'] ?? 12; ?>px; padding-bottom: <?php echo $heroData['buttonPaddingBottom'] ?? 12; ?>px; padding-right: <?php echo $heroData['buttonPaddingRight'] ?? 32; ?>px; padding-left: <?php echo $heroData['buttonPaddingLeft'] ?? 32; ?>px; margin-top: <?php echo $heroData['buttonMarginTop'] ?? 0; ?>px; margin-bottom: <?php echo $heroData['buttonMarginBottom'] ?? 0; ?>px; <?php echo ($heroData['buttonShadow'] === 'true') ? 'box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);' : ''; ?>">
                    <?php echo htmlspecialchars($heroData['buttonText']); ?>
                </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Hero Script - Builder Preview + Regular Site -->
    <script>
        (function() {
            // Set hero data immediately for video switching
            window.lastHeroData = <?php echo json_encode($heroData); ?>;
            
            // Ensure Remix Icons are loaded
            if (!document.querySelector('link[href*="remixicon"]')) {
                const link = document.createElement('link');
                link.href = 'https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css';
                link.rel = 'stylesheet';
                document.head.appendChild(link);
                console.log('Hero: Remix Icons CSS loaded');
            }
            
            // Ensure section width CSS is loaded for both builder and regular site
            if (!document.querySelector('style[data-section-width]')) {
                const style = document.createElement('style');
                style.setAttribute('data-section-width', 'true');
                style.textContent = `
                    .section-width-container {
                        max-width: 1200px !important;
                        margin-left: auto !important;
                        margin-right: auto !important;
                        padding-left: 20px !important;
                        padding-right: 20px !important;
                    }
                    
                    .section-width-full {
                        width: 100% !important;
                        margin-left: 0 !important;
                        margin-right: 0 !important;
                        padding-left: 0 !important;
                        padding-right: 0 !important;
                    }
                    
                    .section-width-custom {
                        margin-left: auto !important;
                        margin-right: auto !important;
                        padding-left: 20px !important;
                        padding-right: 20px !important;
                    }
                `;
                document.head.appendChild(style);
                console.log('Hero: Section width CSS loaded');
            }
            
            <?php if (isset($_GET['builder_preview'])): ?>
        
        // Debug mode for iframe - change to true for debugging
        const IFRAME_DEBUG = false;
        
        const iframeLog = (...args) => {
            if (IFRAME_DEBUG) {
                console.log('[Iframe Debug]', ...args);
            }
        };
        
        // Listen for messages from builder
        window.addEventListener('message', function(event) {
            if (event.data.source === 'pageBuilder' && event.data.action === 'updateHero') {
                iframeLog('Received hero update:', event.data.data);
                
                // Update responsive mode if provided
                if (event.data.responsiveMode) {
                    window.currentResponsiveMode = event.data.responsiveMode;
                    console.log('Hero iframe: Updated responsive mode from message:', event.data.responsiveMode);
                }
                
                console.log('Hero iframe: Current responsive mode during update:', window.currentResponsiveMode);
                updateHeroDisplay(event.data.data);
            }
            
            // Listen for responsive mode changes from builder
            if (event.data.type === 'responsiveModeChanged') {
                console.log('Hero: Responsive mode changed to:', event.data.mode);
                window.currentResponsiveMode = event.data.mode;
                if (window.lastHeroData) {
                    updateHeroDisplay(window.lastHeroData);
                }
            }
            
            // Handle button icon update
            if (event.data.source === 'pageBuilder' && event.data.action === 'updateButtonIcon') {
                const { index, icon } = event.data.data;
                const buttonElement = document.getElementById(`heroButton${index}`);
                if (buttonElement) {
                    // Get current text content
                    const textNodes = Array.from(buttonElement.childNodes).filter(node => 
                        node.nodeType === Node.TEXT_NODE || (node.nodeType === Node.ELEMENT_NODE && node.tagName !== 'I')
                    );
                    const buttonText = buttonElement.textContent.replace(/^\s*[^\w\u05D0-\u05EA]*\s*/, '').trim();
                    
                    // Clear button content
                    buttonElement.innerHTML = '';
                    
                    // Add icon if provided
                    if (icon && icon.trim()) {
                        const iconElement = document.createElement('i');
                        iconElement.className = icon;
                        iconElement.style.marginLeft = '8px';
                        buttonElement.appendChild(iconElement);
                    }
                    
                    // Add text back
                    buttonElement.appendChild(document.createTextNode(buttonText));
                }
            }
            
            // Handle responsive mode changes
            if (event.data.type === 'responsiveModeChanged') {
                window.currentResponsiveMode = event.data.mode;
                console.log('Hero iframe: Responsive mode changed to:', event.data.mode);
                
                // Re-update display with current data and new responsive mode
                if (window.lastHeroData) {
                    updateHeroDisplay(window.lastHeroData);
                }
            }
        });
        
        // Send ready message to builder
        function notifyBuilderReady() {
            if (window.parent !== window) {
                iframeLog('Notifying builder that iframe is ready');
                window.parent.postMessage({
                    source: 'builderPreview',
                    action: 'ready'
                }, '*');
            }
        }
        
        // Initialize responsive mode to desktop
        window.currentResponsiveMode = 'desktop';
        
        // Helper function to get responsive value (with fallback to desktop)
        function getResponsiveValueHelper(data, baseName, mode = null) {
            const currentMode = mode || 'desktop';
            
            if (currentMode === 'desktop') {
                return data[baseName];
            }
            
            const responsiveProperty = `${baseName}_${currentMode}`;
            const responsiveValue = data[responsiveProperty];
            
            console.log(`getResponsiveValueHelper: ${baseName} for ${currentMode}:`, {
                responsiveProperty,
                responsiveValue,
                fallback: data[baseName]
            });
            
            // If responsive value exists and is not empty, use it
            // Check for meaningful values (not empty, null, undefined, or string "0")
            if (responsiveValue !== undefined && responsiveValue !== '' && responsiveValue !== null && responsiveValue !== "0") {
                return responsiveValue;
            }
            
            // Otherwise, fallback to desktop value
            return data[baseName];
        }
        
        // עדכון חלק של כפתורים ללא ריצוד
        function updateButtonsSmooth(container, buttons, heroData) {
            const existingButtons = Array.from(container.children);
            const visibleButtons = buttons.filter(button => button.text && button.text.trim() !== '');
            
            // עדכון או יצירת כפתורים קיימים
            visibleButtons.forEach((button, index) => {
                let buttonElement = existingButtons[index];
                const buttonId = `heroButton${index}`;
                
                if (!buttonElement || buttonElement.id !== buttonId) {
                    // יצירת כפתור חדש אם לא קיים
                    buttonElement = document.createElement('a');
                    buttonElement.id = buttonId;
                    
                    // הוספה במקום הנכון
                    if (index < container.children.length) {
                        container.insertBefore(buttonElement, container.children[index]);
                    } else {
                        container.appendChild(buttonElement);
                    }
                }
                
                // עדכון תוכן ומאפיינים בלבד
                updateSingleButtonSmooth(buttonElement, button, heroData, index);
            });
            
            // הסרת כפתורים מיותרים
            while (container.children.length > visibleButtons.length) {
                container.removeChild(container.lastChild);
            }
        }
        
        // עדכון כפתור יחיד ללא ריצוד
        function updateSingleButtonSmooth(buttonElement, button, heroData, index) {
            // עדכון קישור
            buttonElement.href = button.link || '#';
            
            // עדכון target
            if (button.newTab) {
                buttonElement.target = '_blank';
                buttonElement.rel = 'noopener noreferrer';
            } else {
                buttonElement.target = '';
                buttonElement.rel = '';
            }
            
            // עדכון תוכן - רק אם השתנה
            let buttonContent = '';
            if (button.icon && button.icon.trim()) {
                buttonContent += `<i class="${button.icon}" style="margin-left: 8px;"></i>`;
            }
            buttonContent += button.text;
            
            if (buttonElement.innerHTML !== buttonContent) {
                buttonElement.innerHTML = buttonContent;
            }
            
            // עדכון classes
            let classes = 'inline-flex items-center justify-center transition-all duration-200 ease-in-out transform hover:scale-105';
            if (button.fullWidth) {
                classes += ' w-full';
            }
            classes += ' hero-button ' + getButtonClassesJS(button.style || 'filled');
            if (button.style === 'outline') {
                classes += ' outline-style';
            }
            
            if (buttonElement.className !== classes) {
                buttonElement.className = classes;
            }
            
            // עדכון סגנונות - רק אם נדרש
            const buttonCurrentMode = window.currentResponsiveMode || 'desktop';
            const styles = [];
            
            // Rounded corners
            const buttonRounded = button.rounded || 0;
            styles.push(`border-radius: ${buttonRounded}px`);
            
            const buttonFontSize = getResponsiveValueHelper(heroData, 'buttonFontSize', buttonCurrentMode);
            const buttonFontFamily = getResponsiveValueHelper(heroData, 'buttonFontFamily', buttonCurrentMode);
            const buttonFontWeight = getResponsiveValueHelper(heroData, 'buttonFontWeight', buttonCurrentMode);
            const buttonFontStyle = getResponsiveValueHelper(heroData, 'buttonFontStyle', buttonCurrentMode);
            const buttonTextDecoration = getResponsiveValueHelper(heroData, 'buttonTextDecoration', buttonCurrentMode);
            const buttonShadow = getResponsiveValueHelper(heroData, 'buttonShadow', buttonCurrentMode);
            
            // Padding with fallback
            const buttonPaddingTop = button.paddingTop || getResponsiveValueHelper(heroData, 'buttonPaddingTop', buttonCurrentMode) || 12;
            const buttonPaddingBottom = button.paddingBottom || getResponsiveValueHelper(heroData, 'buttonPaddingBottom', buttonCurrentMode) || 12;
            const buttonPaddingRight = button.paddingRight || getResponsiveValueHelper(heroData, 'buttonPaddingRight', buttonCurrentMode) || 32;
            const buttonPaddingLeft = button.paddingLeft || getResponsiveValueHelper(heroData, 'buttonPaddingLeft', buttonCurrentMode) || 32;
            
            // Margin with fallback
            const buttonMarginTop = button.marginTop !== undefined && button.marginTop !== '' ? button.marginTop : getResponsiveValueHelper(heroData, 'buttonMarginTop', buttonCurrentMode) || 0;
            const buttonMarginBottom = button.marginBottom !== undefined && button.marginBottom !== '' ? button.marginBottom : getResponsiveValueHelper(heroData, 'buttonMarginBottom', buttonCurrentMode) || 0;
            
            if (buttonFontSize) styles.push(`font-size: ${buttonFontSize}px`);
            if (buttonFontFamily) styles.push(`font-family: ${buttonFontFamily}`);
            if (buttonFontWeight) styles.push(`font-weight: ${buttonFontWeight}`);
            if (buttonFontStyle) styles.push(`font-style: ${buttonFontStyle}`);
            if (buttonTextDecoration) styles.push(`text-decoration: ${buttonTextDecoration}`);
            if (buttonPaddingTop !== undefined && buttonPaddingTop !== '') styles.push(`padding-top: ${buttonPaddingTop}px`);
            if (buttonPaddingBottom !== undefined && buttonPaddingBottom !== '') styles.push(`padding-bottom: ${buttonPaddingBottom}px`);
            if (buttonPaddingRight !== undefined && buttonPaddingRight !== '') styles.push(`padding-right: ${buttonPaddingRight}px`);
            if (buttonPaddingLeft !== undefined && buttonPaddingLeft !== '') styles.push(`padding-left: ${buttonPaddingLeft}px`);
            if (buttonMarginTop !== undefined && buttonMarginTop !== '') styles.push(`margin-top: ${buttonMarginTop}px`);
            if (buttonMarginBottom !== undefined && buttonMarginBottom !== '') styles.push(`margin-bottom: ${buttonMarginBottom}px`);
            
            // Shadow
            if (buttonShadow === 'true') {
                styles.push('box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15)');
            }
            
            // Style-specific inline styles
            styles.push(getButtonInlineStylesJS(button.style || 'filled', heroData));
            
            const newStyle = styles.join('; ');
            if (buttonElement.style.cssText !== newStyle) {
                buttonElement.style.cssText = newStyle;
            }
        }

        // Convert responsive background data to legacy format
        function convertResponsiveDataToLegacy(data) {
            // Get current responsive mode
            const currentMode = window.currentResponsiveMode || 'desktop';
            
            // Check if we have new responsive styles format
            if (data.styles && data.styles[currentMode]) {
                const responsive = data.styles[currentMode];
                
                // Map background type
                if (responsive['background-type']) {
                    data.bgType = responsive['background-type'];
                }
                
                // Map background color
                if (responsive['background-color']) {
                    data.bgColor = responsive['background-color'];
                }
                
                // Map background image
                if (responsive['background-image']) {
                    if (currentMode === 'mobile') {
                        data.bgImage_mobile = responsive['background-image'];
                    } else {
                        data.bgImage = responsive['background-image'];
                    }
                }
                
                // Map background video
                if (responsive['background-video']) {
                    if (currentMode === 'mobile') {
                        data.bgVideo_mobile = responsive['background-video'];
                    } else {
                        data.bgVideo = responsive['background-video'];
                    }
                }
                
                // Map video overlay opacity
                if (responsive['video-overlay-opacity']) {
                    data.bgVideoOverlay = parseInt(responsive['video-overlay-opacity']);
                }
                
                // Map gradient colors
                if (responsive['gradient-color1']) {
                    data.bgGradient1 = responsive['gradient-color1'];
                }
                if (responsive['gradient-color2']) {
                    data.bgGradient2 = responsive['gradient-color2'];
                }
                
                // Map gradient direction
                if (responsive['gradient-direction']) {
                    // Convert from CSS direction to legacy format
                    const directionMap = {
                        'to bottom': 'to-b',
                        'to top': 'to-t',
                        'to left': 'to-l',
                        'to right': 'to-r',
                        '45deg': 'to-br',
                        '135deg': 'to-bl'
                    };
                    data.bgGradientDirection = directionMap[responsive['gradient-direction']] || 'to-b';
                }
                
                // Map background size, repeat etc.
                if (responsive['background-size']) {
                    data.bgImageSize = responsive['background-size'];
                }
                if (responsive['background-repeat']) {
                    data.bgImageRepeat = responsive['background-repeat'];
                }
                if (responsive['image-overlay-opacity']) {
                    data.bgImageOverlay = parseInt(responsive['image-overlay-opacity']);
                }
                
                console.log('🔄 Converted responsive data to legacy:', {
                    bgType: data.bgType,
                    bgVideo: data.bgVideo,
                    bgVideoOverlay: data.bgVideoOverlay
                });
            }
            
            return data;
        }

        // Update hero display in real-time
        function updateHeroDisplay(data) {
            // Convert responsive data to legacy format first
            data = convertResponsiveDataToLegacy(data);
            
            // Store the latest data for responsive mode changes
            window.lastHeroData = data;
            
            const heroSection = document.getElementById('<?php echo htmlspecialchars($customId); ?>');
            const heroTitle = document.getElementById('heroTitle');
            const heroSubtitle = document.getElementById('heroSubtitle');
            const heroButton = document.getElementById('heroButton');
            
            if (heroSection) {
                // Update background
                updateBackground(heroSection, data);
                
                // Update spacing
                updateSpacing(heroSection, data);
                
                // Update positioning
                updatePositioning(heroSection, data);
            }
            
            // Update title - show/hide based on content
            if (data.title && data.title.trim() !== '') {
                // Show title if it has content
                if (!heroTitle) {
                    // Create title element if it doesn't exist (use selected tag)
                    const titleTag = data.titleTag || 'h2';
                    const newTitle = document.createElement(titleTag);
                    newTitle.id = 'heroTitle';
                    newTitle.className = 'mb-6 leading-tight';
                    heroSection.querySelector('div').insertBefore(newTitle, heroSection.querySelector('div').firstChild);
                }
                const currentTitle = heroTitle || document.getElementById('heroTitle');
                
                // Update tag if needed
                if (data.titleTag && currentTitle.tagName.toLowerCase() !== data.titleTag.toLowerCase()) {
                    const newTitle = document.createElement(data.titleTag);
                    newTitle.id = 'heroTitle';
                    newTitle.className = currentTitle.className;
                    newTitle.style.cssText = currentTitle.style.cssText;
                    newTitle.textContent = currentTitle.textContent;
                    currentTitle.parentNode.replaceChild(newTitle, currentTitle);
                    currentTitle = newTitle;
                }
                
                currentTitle.textContent = data.title;
                currentTitle.style.display = 'block';
                if (data.titleColor) currentTitle.style.color = data.titleColor;
                
                // Use responsive typography values
                const titleCurrentMode = window.currentResponsiveMode || 'desktop';
                
                console.log('Hero: Updating title typography for mode:', titleCurrentMode);
                
                const titleFontSize = getResponsiveValueHelper(data, 'titleFontSize', titleCurrentMode);
                const titleFontFamily = getResponsiveValueHelper(data, 'titleFontFamily', titleCurrentMode);
                const titleFontWeight = getResponsiveValueHelper(data, 'titleFontWeight', titleCurrentMode);
                const titleFontStyle = getResponsiveValueHelper(data, 'titleFontStyle', titleCurrentMode);
                const titleTextDecoration = getResponsiveValueHelper(data, 'titleTextDecoration', titleCurrentMode);
                const titleLineHeight = getResponsiveValueHelper(data, 'titleLineHeight', titleCurrentMode);
                const titleTextTransform = getResponsiveValueHelper(data, 'titleTextTransform', titleCurrentMode);
                
                console.log('Hero: Title font size for', titleCurrentMode + ':', titleFontSize);
                
                if (titleFontSize) currentTitle.style.fontSize = titleFontSize + 'px';
                if (titleFontFamily) currentTitle.style.fontFamily = titleFontFamily;
                if (titleFontWeight) currentTitle.style.fontWeight = titleFontWeight;
                if (titleFontStyle) currentTitle.style.fontStyle = titleFontStyle;
                if (titleTextDecoration) currentTitle.style.textDecoration = titleTextDecoration;
                if (titleLineHeight) currentTitle.style.lineHeight = titleLineHeight;
                if (titleTextTransform) currentTitle.style.textTransform = titleTextTransform;
            } else if (heroTitle) {
                // Hide title if it's empty
                heroTitle.style.display = 'none';
            }
            
            // Update subtitle - show/hide based on content
            if (data.subtitle && data.subtitle.trim() !== '') {
                // Show subtitle if it has content
                if (!heroSubtitle) {
                    // Create subtitle element if it doesn't exist (use selected tag)
                    const subtitleTag = data.subtitleTag || 'p';
                    const newSubtitle = document.createElement(subtitleTag);
                    newSubtitle.id = 'heroSubtitle';
                    newSubtitle.className = 'mb-8 max-w-2xl mx-auto leading-relaxed';
                    const container = heroSection.querySelector('div');
                    const titleElement = container.querySelector('#heroTitle');
                    if (titleElement) {
                        titleElement.insertAdjacentElement('afterend', newSubtitle);
                    } else {
                        container.insertBefore(newSubtitle, container.firstChild);
                    }
                }
                const currentSubtitle = heroSubtitle || document.getElementById('heroSubtitle');
                
                // Update tag if needed
                if (data.subtitleTag && currentSubtitle.tagName.toLowerCase() !== data.subtitleTag.toLowerCase()) {
                    const newSubtitle = document.createElement(data.subtitleTag);
                    newSubtitle.id = 'heroSubtitle';
                    newSubtitle.className = currentSubtitle.className;
                    newSubtitle.style.cssText = currentSubtitle.style.cssText;
                    newSubtitle.innerHTML = currentSubtitle.innerHTML;
                    currentSubtitle.parentNode.replaceChild(newSubtitle, currentSubtitle);
                    currentSubtitle = newSubtitle;
                }
                
                // Support HTML tags in subtitle content
                const allowedTags = ['br', 'strong', 'b', 'em', 'i', 'u', 'span'];
                const cleanHTML = data.subtitle.replace(/<(?!\/?(?:br|strong|b|em|i|u|span)(?:\s|>))[^>]*>/gi, '');
                currentSubtitle.innerHTML = cleanHTML;
                currentSubtitle.style.display = 'block';
                if (data.subtitleColor) currentSubtitle.style.color = data.subtitleColor;
                
                // Use responsive typography values
                const subtitleCurrentMode = window.currentResponsiveMode || 'desktop';
                
                console.log('Hero: Updating subtitle typography for mode:', subtitleCurrentMode);
                
                const subtitleFontSize = getResponsiveValueHelper(data, 'subtitleFontSize', subtitleCurrentMode);
                const subtitleFontFamily = getResponsiveValueHelper(data, 'subtitleFontFamily', subtitleCurrentMode);
                const subtitleFontWeight = getResponsiveValueHelper(data, 'subtitleFontWeight', subtitleCurrentMode);
                const subtitleFontStyle = getResponsiveValueHelper(data, 'subtitleFontStyle', subtitleCurrentMode);
                const subtitleTextDecoration = getResponsiveValueHelper(data, 'subtitleTextDecoration', subtitleCurrentMode);
                const subtitleLineHeight = getResponsiveValueHelper(data, 'subtitleLineHeight', subtitleCurrentMode);
                const subtitleTextTransform = getResponsiveValueHelper(data, 'subtitleTextTransform', subtitleCurrentMode);
                
                if (subtitleFontSize) currentSubtitle.style.fontSize = subtitleFontSize + 'px';
                if (subtitleFontFamily) currentSubtitle.style.fontFamily = subtitleFontFamily;
                if (subtitleFontWeight) currentSubtitle.style.fontWeight = subtitleFontWeight;
                if (subtitleFontStyle) currentSubtitle.style.fontStyle = subtitleFontStyle;
                if (subtitleTextDecoration) currentSubtitle.style.textDecoration = subtitleTextDecoration;
                if (subtitleLineHeight) currentSubtitle.style.lineHeight = subtitleLineHeight;
                if (subtitleTextTransform) currentSubtitle.style.textTransform = subtitleTextTransform;
            } else if (heroSubtitle) {
                // Hide subtitle if it's empty
                heroSubtitle.style.display = 'none';
            }
            
            // Update buttons (support both multiple and single button modes)
            updateHeroButtons(heroSection, data);
            
            // Update button hover styles
            updateButtonHoverStyles(data);
            
                         // Update section width using global system
             if (heroSection && data.width) {
                 iframeLog('Updating section width:', {
                     width: data.width,
                     customWidth: data.customWidth,
                     customUnit: data.customWidthUnit
                 });
                 
                 // Remove old width classes (both old and new)
                 heroSection.classList.remove('container', 'mx-auto', 'w-full', 'max-w-custom', 
                     'section-width-container', 'section-width-full', 'section-width-custom');
                 
                 // Clear any existing width/margin/padding styles that might interfere
                 heroSection.style.maxWidth = '';
                 heroSection.style.marginLeft = '';
                 heroSection.style.marginRight = '';
                 heroSection.style.width = '';
                 // Don't clear vertical padding as it's controlled by spacing settings
                 // But clear horizontal padding for full width
                 if (data.width === 'full') {
                     heroSection.style.paddingLeft = '';
                     heroSection.style.paddingRight = '';
                 }
                 
                 // Apply new global width classes - let CSS handle the styling
                 switch (data.width) {
                     case 'container':
                         heroSection.classList.add('section-width-container');
                         iframeLog('Applied container width class');
                         break;
                     case 'full':
                         heroSection.classList.add('section-width-full');
                         iframeLog('Applied full width class');
                         break;
                     case 'custom':
                         heroSection.classList.add('section-width-custom');
                         // Only for custom width, we need to set the max-width value
                         const customWidth = data.customWidth || 800;
                         const customUnit = data.customWidthUnit || 'px';
                         heroSection.style.maxWidth = `${customWidth}${customUnit}`;
                         iframeLog('Applied custom width class with max-width:', `${customWidth}${customUnit}`);
                         break;
                 }
             }
        }
        
        function updateHeroButtons(heroSection, data) {
            // Check if we have any buttons with text first
            let hasVisibleButtons = false;
            
            if (data.buttons && Array.isArray(data.buttons)) {
                hasVisibleButtons = data.buttons.some(button => button.text && button.text.trim() !== '');
            } else if (data.buttonText && data.buttonText.trim() !== '') {
                hasVisibleButtons = true;
            }
            
            // Find or create buttons container
            let buttonsContainer = heroSection.querySelector('.flex.gap-4') || 
                                    heroSection.querySelector('.flex.flex-wrap.gap-4') || 
                                    heroSection.querySelector('.flex.flex-col.gap-4');
            
            if (hasVisibleButtons) {
                // Show buttons container
                if (!buttonsContainer) {
                    // Create container if it doesn't exist
                    buttonsContainer = document.createElement('div');
                    buttonsContainer.className = 'flex flex-wrap gap-4 justify-center';
                    heroSection.querySelector('div').appendChild(buttonsContainer);
                }
                buttonsContainer.style.display = 'flex';
                
                // Update buttons - support both multiple buttons and legacy single button
                if (data.buttons && Array.isArray(data.buttons)) {
                    // Update container classes for mobile display
                    const mobileDisplay = data.buttonsMobileDisplay || 'horizontal';
                    console.log('Hero iframe: buttonsMobileDisplay from data:', data.buttonsMobileDisplay);
                    console.log('Hero iframe: Final mobileDisplay value:', mobileDisplay);
                    console.log('Hero iframe: Updating mobile display mode to:', mobileDisplay);
                    if (mobileDisplay === 'vertical') {
                        buttonsContainer.className = 'flex flex-col sm:flex-row gap-4 justify-center';
                        console.log('Hero iframe: Applied vertical mobile display classes');
                    } else {
                        buttonsContainer.className = 'flex flex-wrap gap-4 justify-center';
                        console.log('Hero iframe: Applied horizontal mobile display classes');
                    }
                    console.log('Hero iframe: Final container className:', buttonsContainer.className);
                    
                    // עדכון חלק של כפתורים - בלי מחיקה מלאה
                    updateButtonsSmooth(buttonsContainer, data.buttons, data);
                    
                } else {
                    // Fallback to legacy single button mode
                    if (data.buttonText && data.buttonText.trim() !== '') {
                        let heroButton = heroSection.querySelector('#heroButton');
                        
                        if (!heroButton) {
                            // Create button if it doesn't exist
                            heroButton = document.createElement('a');
                            heroButton.id = 'heroButton';
                            heroButton.className = 'inline-block rounded-lg hover:opacity-90 transition-all duration-300 transform hover:scale-105';
                            buttonsContainer.appendChild(heroButton);
                        }
                        
                        heroButton.textContent = data.buttonText;
                        heroButton.style.display = 'inline-block';
                        if (data.buttonLink) heroButton.href = data.buttonLink;
                        if (data.buttonNewTab) {
                            heroButton.target = data.buttonNewTab ? '_blank' : '_self';
                            heroButton.rel = data.buttonNewTab ? 'noopener noreferrer' : '';
                        }
                        if (data.buttonBgColor) heroButton.style.backgroundColor = data.buttonBgColor;
                        if (data.buttonTextColor) heroButton.style.color = data.buttonTextColor;
                        if (data.buttonFontSize) heroButton.style.fontSize = data.buttonFontSize + 'px';
                        if (data.buttonFontFamily) heroButton.style.fontFamily = data.buttonFontFamily;
                        if (data.buttonFontWeight) heroButton.style.fontWeight = data.buttonFontWeight;
                        if (data.buttonFontStyle) heroButton.style.fontStyle = data.buttonFontStyle;
                        if (data.buttonTextDecoration) heroButton.style.textDecoration = data.buttonTextDecoration;
                        
                        // Button shadow
                        if (data.buttonShadow) {
                            heroButton.style.boxShadow = data.buttonShadow === 'true' ? '0 4px 8px rgba(0, 0, 0, 0.15)' : '';
                        }
                        
                        // Button padding
                        if (data.buttonPaddingTop !== undefined) heroButton.style.paddingTop = data.buttonPaddingTop + 'px';
                        if (data.buttonPaddingBottom !== undefined) heroButton.style.paddingBottom = data.buttonPaddingBottom + 'px';
                        if (data.buttonPaddingRight !== undefined) heroButton.style.paddingRight = data.buttonPaddingRight + 'px';
                        if (data.buttonPaddingLeft !== undefined) heroButton.style.paddingLeft = data.buttonPaddingLeft + 'px';
                        
                        // Button margin
                        if (data.buttonMarginTop !== undefined) heroButton.style.marginTop = data.buttonMarginTop + 'px';
                        if (data.buttonMarginBottom !== undefined) heroButton.style.marginBottom = data.buttonMarginBottom + 'px';
                    }
                }
            } else if (buttonsContainer) {
                // Hide buttons container if no visible buttons
                buttonsContainer.style.display = 'none';
            }
            
            // Update responsive background video - only if video source changed
            if (typeof updateBackgroundVideo === 'function' && data.bgType === 'video') {
                updateBackgroundVideo();
            }
        }
        
        function getButtonClassesJS(style) {
            switch (style) {
                case 'filled': return 'hover:opacity-90';
                case 'outline': return 'border-2 hover:bg-opacity-10';
                case 'white': return 'bg-white text-gray-900 hover:bg-gray-100';
                case 'black': return 'bg-black text-white hover:bg-gray-900';
                case 'text': return 'bg-transparent hover:bg-opacity-10';
                default: return 'hover:opacity-90';
            }
        }
        
        function getButtonInlineStylesJS(style, data) {
            const bgColor = data.buttonBgColor || '#F59E0B';
            const textColor = data.buttonTextColor || '#FFFFFF';
            const borderColor = data.buttonBorderColor || bgColor;
            const bgColorHover = data.buttonBgColorHover || '#E5A712';
            const textColorHover = data.buttonTextColorHover || '#FFFFFF';
            const borderColorHover = data.buttonBorderColorHover || bgColorHover;
            
            switch (style) {
                case 'filled': return `background-color: ${bgColor}; color: ${textColor}; border: 2px solid ${borderColor}`;
                case 'outline': return `background-color: transparent; color: ${bgColor}; border: 2px solid ${borderColor}`;
                case 'white': return 'background-color: #FFFFFF; color: #1F2937; border: 2px solid #FFFFFF';
                case 'black': return 'background-color: #000000; color: #FFFFFF; border: 2px solid #000000';
                case 'text': return `background-color: transparent; color: ${textColor}; text-decoration: underline; border: none`;
                default: return `background-color: ${bgColor}; color: ${textColor}; border: 2px solid ${borderColor}`;
            }
        }
        
        function updateButtonHoverStyles(data) {
            const bgColorHover = data.buttonBgColorHover || '#E5A712';
            const textColorHover = data.buttonTextColorHover || '#FFFFFF';
            const borderColorHover = data.buttonBorderColorHover || bgColorHover;
            
            // Find or create style element
            let styleElement = document.getElementById('heroButtonHoverStyles');
            if (!styleElement) {
                styleElement = document.createElement('style');
                styleElement.id = 'heroButtonHoverStyles';
                document.head.appendChild(styleElement);
            }
            
            // Update hover styles
            styleElement.textContent = `
                #heroSection .hero-button:hover {
                    background-color: ${bgColorHover} !important;
                    color: ${textColorHover} !important;
                    border-color: ${borderColorHover} !important;
                }
                
                #heroSection .hero-button.outline-style:hover {
                    background-color: ${bgColorHover} !important;
                    color: ${textColorHover} !important;
                }
            `;
        }
        
        function updateBackground(section, data) {
            // Get current responsive mode from builder
            const bgCurrentMode = window.currentResponsiveMode || 'desktop';
            const isMobile = bgCurrentMode === 'mobile';
            const isTablet = bgCurrentMode === 'tablet';
            
            console.log('Hero: Updating background for mode:', bgCurrentMode, 'isMobile:', isMobile, 'isTablet:', isTablet);
            
            // Get background type from new responsive structure
            const device = isMobile ? 'mobile' : 'desktop';
            const backgroundType = data.styles?.[device]?.['background-type'] || data.bgType || 'color';
            
            // הסרת רכיבי וידאו רק אם עוברים לסוג רקע אחר
            if (backgroundType !== 'video') {
                const existingVideo = section.querySelector('#heroBackgroundVideo');
                if (existingVideo) {
                    existingVideo.remove();
                }
                
                const existingOverlay = section.querySelector('.absolute.inset-0.bg-black.z-0');
                if (existingOverlay) {
                    existingOverlay.remove();
                }
            }
            
            let backgroundStyle = '';
            
            switch (backgroundType) {
                case 'color':
                    backgroundStyle = `background: ${data.bgColor};`;
                    break;
                    
                case 'gradient':
                    const gradientMap = {
                        'to-t': 'to top',
                        'to-b': 'to bottom',
                        'to-l': 'to left',
                        'to-r': 'to right',
                        'to-tl': 'to top left',
                        'to-tr': 'to top right',
                        'to-bl': 'to bottom left',
                        'to-br': 'to bottom right'
                    };
                    const cssDirection = gradientMap[data.bgGradientDirection] || 'to bottom';
                    backgroundStyle = `background: linear-gradient(${cssDirection}, ${data.bgGradient1}, ${data.bgGradient2});`;
                    break;
                    
                case 'image':
                    // Use responsive image
                    let imageUrl = data.bgImage;
                    if (isMobile && data.bgImage_mobile) {
                        imageUrl = data.bgImage_mobile;
                        console.log('Hero: Using mobile background image:', imageUrl);
                    } else {
                        console.log('Hero: Using desktop background image:', imageUrl);
                    }
                    
                    if (imageUrl) {
                        const bgSize = data.bgImageSize || 'cover';
                        const bgPosition = data.bgImagePosition || 'center';
                        const bgRepeat = data.bgImageRepeat || 'no-repeat';
                        backgroundStyle = `background: url('${imageUrl}') ${bgRepeat} ${bgPosition}; background-size: ${bgSize};`;
                    } else {
                        backgroundStyle = `background: ${data.bgColor || '#3B82F6'};`;
                    }
                    break;
                    
                case 'video':
                    // Use responsive video
                    let videoUrl = data.bgVideo;
                    if (isMobile && data.bgVideo_mobile) {
                        videoUrl = data.bgVideo_mobile;
                        console.log('Hero: Using mobile background video:', videoUrl);
                    } else {
                        console.log('Hero: Using desktop background video:', videoUrl);
                    }
                    
                    if (videoUrl) {
                        // עדכון וידאו קיים במקום יצירת חדש
                        let video = section.querySelector('#heroBackgroundVideo');
                        let source = section.querySelector('#heroVideoSource');
                        let overlay = section.querySelector('.absolute.inset-0.bg-black.z-0');
                        
                        // יצירת וידאו רק אם לא קיים
                        if (!video) {
                            video = document.createElement('video');
                            video.id = 'heroBackgroundVideo';
                            video.autoplay = true;
                            video.muted = true;
                            video.loop = true;
                            video.className = 'absolute inset-0 w-full h-full object-cover z-0';
                            
                            source = document.createElement('source');
                            source.id = 'heroVideoSource';
                            source.type = 'video/mp4';
                            video.appendChild(source);
                            
                            section.insertBefore(video, section.firstChild);
                        }
                        
                        // עדכון המקור רק אם השתנה
                        if (source && source.src !== videoUrl) {
                            source.src = videoUrl;
                            video.load(); // Reload only when source changes
                        }
                        
                        // עדכון או יצירת overlay
                        if (!overlay) {
                            overlay = document.createElement('div');
                            overlay.className = 'absolute inset-0 bg-black z-0';
                            section.insertBefore(overlay, video.nextSibling);
                        }
                        
                        const overlayOpacity = (data.bgVideoOverlay || 30) / 100;
                        overlay.style.opacity = overlayOpacity;
                    }
                    backgroundStyle = `background: ${data.bgColor || '#3B82F6'};`;
                    break;
                    
                default:
                    backgroundStyle = `background: ${data.bgColor || '#3B82F6'};`;
            }
            
            // עדכון הסגנונות
            const currentStyle = section.style.cssText;
            const cleanStyle = currentStyle.replace(/background[^;]*;?/g, '');
            section.style.cssText = cleanStyle + backgroundStyle;
            
            // עדכון גובה הסקשן - תמיכה רספונסיבית
            const heightCurrentMode = window.currentResponsiveMode || 'desktop';
            
            // קבלת הגדרות גובה בהתאם למצב
            let heightType, heightValue;
            
            if (heightCurrentMode === 'mobile' && data.heightType_mobile && data.heightType_mobile !== 'auto') {
                heightType = data.heightType_mobile;
                heightValue = data.heightValue_mobile;
                console.log('Hero: Using mobile height:', heightValue + heightType);
            } else if (heightCurrentMode === 'tablet' && data.heightType_tablet && data.heightType_tablet !== 'auto') {
                heightType = data.heightType_tablet;
                heightValue = data.heightValue_tablet;
                console.log('Hero: Using tablet height:', heightValue + heightType);
            } else {
                heightType = data.heightType;
                heightValue = data.heightValue;
                console.log('Hero: Using desktop height:', heightValue + heightType);
            }
            
            // החלת הגובה
            if (heightType && heightType !== 'auto' && heightValue) {
                // מנע שימוש באחוזים - המר ל-vh אם צריך
                if (heightType === '%') {
                    heightType = 'vh';
                    console.log('Hero: Converting percentage height to vh');
                }
                section.style.height = `${heightValue}${heightType}`;
                section.style.minHeight = 'unset';
                console.log('Hero: Applied height:', section.style.height);
            } else {
                section.style.height = 'auto';
                section.style.minHeight = '500px';
                console.log('Hero: Applied auto height with min-height 500px');
            }
        }
        
        function updateSpacing(section, data) {
            if (data.paddingTop !== undefined) section.style.paddingTop = data.paddingTop + 'px';
            if (data.paddingBottom !== undefined) section.style.paddingBottom = data.paddingBottom + 'px';
            
            // For full width, don't set horizontal padding - let CSS classes handle it
            if ((data.width || 'container') !== 'full') {
                if (data.paddingRight !== undefined) section.style.paddingRight = data.paddingRight + 'px';
                if (data.paddingLeft !== undefined) section.style.paddingLeft = data.paddingLeft + 'px';
            } else {
                // Clear any existing horizontal padding for full width
                section.style.paddingRight = '';
                section.style.paddingLeft = '';
            }
            
            if (data.marginTop !== undefined) section.style.marginTop = data.marginTop + 'px';
            if (data.marginBottom !== undefined) section.style.marginBottom = data.marginBottom + 'px';
            
            // Don't set horizontal margins when width system is in use - let CSS classes handle centering
            if (!data.width) {
                // Only add horizontal margins if there's no width setting (legacy support)
                if (data.marginRight !== undefined) section.style.marginRight = data.marginRight + 'px';
                if (data.marginLeft !== undefined) section.style.marginLeft = data.marginLeft + 'px';
            } else {
                // Clear any existing horizontal margins when using width system
                section.style.marginRight = '';
                section.style.marginLeft = '';
            }
        }
        
        function updatePositioning(section, data) {
            if (data.contentPosition) {
                const positions = {
                    'top-left': 'items-start justify-end text-left',
                    'top-center': 'items-start justify-center text-center',
                    'top-right': 'items-start justify-start text-right',
                    'center-left': 'items-center justify-end text-left',
                    'center-center': 'items-center justify-center text-center',
                    'center-right': 'items-center justify-start text-right',
                    'bottom-left': 'items-end justify-end text-left',
                    'bottom-center': 'items-end justify-center text-center',
                    'bottom-right': 'items-end justify-start text-right'
                };
                
                // Remove old position classes
                Object.values(positions).forEach(classes => {
                    classes.split(' ').forEach(cls => section.classList.remove(cls));
                });
                
                // Add new position classes
                const newClasses = positions[data.contentPosition] || positions['center-center'];
                newClasses.split(' ').forEach(cls => section.classList.add(cls));
                
                // Update text alignment for content elements
                const heroTitle = section.querySelector('#heroTitle');
                const heroSubtitle = section.querySelector('#heroSubtitle');
                const container = section.querySelector('div');
                
                let textAlign = 'center';
                if (data.contentPosition.includes('left')) textAlign = 'left';
                else if (data.contentPosition.includes('right')) textAlign = 'right';
                
                if (heroTitle) heroTitle.style.textAlign = textAlign;
                if (heroSubtitle) heroSubtitle.style.textAlign = textAlign;
                if (container) container.style.textAlign = textAlign;
            }
        }
        
        // Make hero clickable in builder mode
        document.getElementById('<?php echo htmlspecialchars($customId); ?>').addEventListener('click', function(e) {
            if (window.parent !== window) {
                e.preventDefault();
                window.parent.postMessage({
                    source: 'builderPreview',
                    action: 'heroClicked'
                }, '*');
            }
        });
        
        // Handle responsive background video switching
        function updateBackgroundVideo() {
            const video = document.getElementById('heroBackgroundVideo');
            const source = document.getElementById('heroVideoSource');
            
            if (!video || !source || !window.lastHeroData) return;
            
            const data = window.lastHeroData;
            
            // Only proceed if background type is video
            if (data.bgType !== 'video') return;
            
            const isMobile = window.innerWidth <= 768;
            
            let videoUrl = data.bgVideo;
            if (isMobile && data.bgVideo_mobile) {
                videoUrl = data.bgVideo_mobile;
                console.log('Hero: Using mobile background video:', videoUrl);
            } else {
                console.log('Hero: Using desktop background video:', videoUrl);
            }
            
            // Always update if we have a video URL (including first load)
            if (videoUrl) {
                const currentSrc = source.src;
                const newSrc = videoUrl.startsWith('http') ? videoUrl : window.location.origin + '/' + videoUrl;
                
                if (currentSrc !== newSrc) {
                    console.log('Hero: Video source changed from', currentSrc, 'to', newSrc);
                    source.src = newSrc;
                    video.load(); // Reload the video with new source
                    console.log('Hero: Video source updated to:', newSrc);
                }
            }
        }
        
        // Handle window resize for responsive video
        window.addEventListener('resize', updateBackgroundVideo);
        
        // Initial video setup for both builder and regular frontend
        document.addEventListener('DOMContentLoaded', function() {
            updateBackgroundVideo();
            
            <?php if (isset($_GET['builder_preview'])): ?>
            setTimeout(notifyBuilderReady, 100);
            <?php endif; ?>
        });
        
        // Also notify if already loaded
        if (document.readyState === 'complete') {
            updateBackgroundVideo();
            
            <?php if (isset($_GET['builder_preview'])): ?>
            setTimeout(notifyBuilderReady, 100);
            <?php endif; ?>
        }
        
        // Run immediately to set video source as early as possible
        updateBackgroundVideo();
        
        })(); // End main function
    </script>
    <?php endif; ?>
</section> 