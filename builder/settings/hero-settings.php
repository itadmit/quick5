<?php
// הגדרת נתוני ברירת מחדל לדוגמה
$sectionType = 'hero';
$defaultData = [
    'title' => 'ברוכים הבאים לחנות שלנו',
    'subtitle' => 'גלה את המוצרים הטובים ביותר במחירים הכי טובים',
    'buttonText' => 'קנה עכשיו',
    'buttonLink' => '/products',
    'buttonNewTab' => false,
    'width' => 'full',
    'customWidth' => 800,
    'customWidthUnit' => 'px',
    'contentPosition' => 'center-center',
    'heightType' => 'auto',
    'heightValue' => 500,
    'bgType' => 'color',
    'bgColor' => '#3B82F6',
    'bgGradient1' => '#3B82F6',
    'bgGradient2' => '#1E40AF',
    'bgGradientDirection' => 'to-b',
    'bgImage' => '',
    'bgImageSize' => 'cover',
    'bgImagePosition' => 'center',
    'bgImageRepeat' => 'no-repeat',
    'bgVideo' => '',
    'titleColor' => '#FFFFFF',
    'subtitleColor' => '#E5E7EB',
    'buttonBgColor' => '#F59E0B',
    'buttonTextColor' => '#FFFFFF',
    'buttonBorderColor' => '#F59E0B',
    'buttonBgColorHover' => '#E5A712',
    'buttonTextColorHover' => '#FFFFFF',
    'buttonBorderColorHover' => '#E5A712',
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
    'paddingTop' => 80,
    'paddingBottom' => 80,
    'paddingRight' => 20,
    'paddingLeft' => 20,
    'marginTop' => 0,
    'marginBottom' => 0,
    'marginRight' => 0,
    'marginLeft' => 0,
    'buttonsMobileDisplay' => 'horizontal',
    'customClass' => '',
    'customId' => ''
];
?>

<div class="p-6 h-full overflow-y-auto">
    <div class="flex items-center gap-3 mb-6">
        <button id="backButton" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition-colors">
            <i class="ri-arrow-right-line text-gray-600"></i>
        </button>
        <h3 id="settingsTitle" class="text-lg font-medium text-gray-900">הגדרות Hero</h3>
    </div>
    
    <!-- Hero Settings Form -->
    <form id="heroForm" class="space-y-6">
        
        <?php 
        // תוכן
        include 'components/content.php'; 
        ?>
        
        <?php 
        // רקע
        include 'components/background.php'; 
        ?>
        
        <?php 
        // צבעים
        include 'components/colors.php'; 
        ?>
        
        <?php 
        // פריסה
        include 'components/layout.php'; 
        ?>
        
        <?php 
        // מרווחים
        include 'components/spacing.php'; 
        ?>
        
        <?php 
        // טיפוגרפיה
        include 'components/typography.php'; 
        ?>
        
        <?php 
        // התאמה אישית
        include 'components/custom.php'; 
        ?>
        
        <!-- Hidden input for mobile display setting -->
        <input type="hidden" id="heroButtonsMobileDisplay" name="buttonsMobileDisplay" value="<?php echo $defaultData['buttonsMobileDisplay'] ?? 'horizontal'; ?>">
        
    </form>
</div> 