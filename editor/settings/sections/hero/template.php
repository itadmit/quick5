<?php
/**
 * Hero Section Template - תמלילט סקשן הירו
 */

// הגדרת פונקציות עזר אם לא קיימות
if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

// ברירות מחדל
$defaultSettings = [
    'title' => 'ברוכים הבאים לחנות שלנו',
    'subtitle' => 'גלו את המוצרים הטובים ביותר במחירים הטובים ביותר',
    'titleColor' => '#FFFFFF',
    'subtitleColor' => '#E5E7EB',
    'bgColor' => '#3B82F6',
    'heightDesktop' => '75vh',
    'heightMobile' => '50vh',
    'bgType' => 'color'
];

$settings = isset($section['settings']) ? array_merge($defaultSettings, $section['settings']) : $defaultSettings;
$sectionId = $section['id'] ?? 'hero_' . uniqid();

// יצירת CSS דינמי
$css = generateHeroCSS($sectionId, $settings);
?>

<style>
<?php echo $css; ?>

/* Ensure consistent styling with JavaScript render */
#<?php echo $sectionId; ?> .hero-title {
    color: <?php echo esc_attr($settings['titleColor']); ?>;
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 1rem;
}

#<?php echo $sectionId; ?> .hero-subtitle {
    color: <?php echo esc_attr($settings['subtitleColor']); ?>;
    font-size: 1.25rem;
    margin-bottom: 2rem;
}

@media (min-width: 768px) {
    #<?php echo $sectionId; ?> .hero-title {
        font-size: 3.75rem;
    }
    #<?php echo $sectionId; ?> .hero-subtitle {
        font-size: 1.5rem;
    }
}
</style>

<section id="<?php echo esc_attr($sectionId); ?>" class="hero-section relative overflow-hidden">
    <!-- Background Video (if applicable) -->
    <?php if (($settings['bgTypeDesktop'] ?? $settings['bgType']) === 'video' && !empty($settings['bgVideoDesktop'])): ?>
        <video class="absolute inset-0 w-full h-full object-cover hidden md:block" 
               autoplay <?php echo !empty($settings['bgVideoMutedDesktop']) ? 'muted' : ''; ?> 
               <?php echo !empty($settings['bgVideoLoopDesktop']) ? 'loop' : ''; ?>>
            <source src="<?php echo esc_url($settings['bgVideoDesktop']); ?>" type="video/mp4">
        </video>
    <?php endif; ?>
    
    <?php if (($settings['bgTypeMobile'] ?? $settings['bgType']) === 'video' && !empty($settings['bgVideoMobile'])): ?>
        <video class="absolute inset-0 w-full h-full object-cover md:hidden" 
               autoplay <?php echo !empty($settings['bgVideoMutedMobile']) ? 'muted' : ''; ?> 
               <?php echo !empty($settings['bgVideoLoopMobile']) ? 'loop' : ''; ?>>
            <source src="<?php echo esc_url($settings['bgVideoMobile']); ?>" type="video/mp4">
        </video>
    <?php endif; ?>
    
    <!-- Overlay -->
    <?php if (!empty($settings['overlayColor']) && ($settings['overlayOpacity'] ?? 0) > 0): ?>
        <div class="absolute inset-0 z-10" style="background-color: <?php echo esc_attr($settings['overlayColor']); ?>; opacity: <?php echo esc_attr($settings['overlayOpacity']); ?>;"></div>
    <?php endif; ?>
    
    <!-- Content Container -->
    <div class="relative z-20 container mx-auto px-4 h-full flex items-center">
        <div class="hero-content w-full text-center">
            <!-- Title -->
            <?php if (!empty($settings['title'])): ?>
                <h1 class="hero-title">
                    <?php echo esc_html($settings['title']); ?>
                </h1>
            <?php endif; ?>
            
            <!-- Subtitle -->
            <?php if (!empty($settings['subtitle'])): ?>
                <p class="hero-subtitle">
                    <?php echo esc_html($settings['subtitle']); ?>
                </p>
            <?php endif; ?>
            
            <!-- Buttons -->
            <div class="hero-buttons flex flex-wrap gap-4 justify-center">
                <?php if (!empty($settings['buttons'])): ?>
                    <?php foreach ($settings['buttons'] as $button): ?>
                        <?php if (!empty($button['text'])): ?>
                            <a href="<?php echo esc_url($button['url'] ?? '#'); ?>" 
                               class="bg-white text-gray-900 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                                <?php echo esc_html($button['text']); ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <a href="#" class="bg-white text-gray-900 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                        קנה עכשיו
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
/**
 * יצירת CSS דינמי עבור הירו
 */
function generateHeroCSS($sectionId, $settings) {
    $css = '';
    
    // Base styles
    $css .= "#{$sectionId} {\n";
    
    // Height - Desktop
    $heightDesktop = $settings['heightDesktop'] ?? '75vh';
    if ($heightDesktop === 'custom') {
        $heightDesktop = ($settings['customHeightDesktop'] ?? 600) . 'px';
    }
    $css .= "  height: {$heightDesktop};\n";
    
    // Background - Desktop
    $bgTypeDesktop = $settings['bgTypeDesktop'] ?? $settings['bgType'] ?? 'color';
    switch ($bgTypeDesktop) {
        case 'color':
            $bgColor = $settings['bgColorDesktop'] ?? $settings['bgColor'] ?? '#3B82F6';
            $css .= "  background-color: {$bgColor};\n";
            break;
        
        case 'gradient':
            $gradient1 = $settings['bgGradient1Desktop'] ?? '#3B82F6';
            $gradient2 = $settings['bgGradient2Desktop'] ?? '#1E40AF';
            $direction = $settings['bgGradientDirectionDesktop'] ?? 'to-b';
            $css .= "  background: linear-gradient({$direction}, {$gradient1}, {$gradient2});\n";
            break;
        
        case 'image':
            if (!empty($settings['bgImageDesktop'])) {
                $css .= "  background-image: url('" . esc_url($settings['bgImageDesktop']) . "');\n";
                $css .= "  background-size: " . ($settings['bgSizeDesktop'] ?? 'cover') . ";\n";
                $css .= "  background-position: " . ($settings['bgPositionDesktop'] ?? 'center') . ";\n";
                $css .= "  background-repeat: " . ($settings['bgRepeatDesktop'] ?? 'no-repeat') . ";\n";
            }
            break;
    }
    
    // Padding - Desktop
    if (!empty($settings['paddingTopDesktop'])) {
        $css .= "  padding-top: " . $settings['paddingTopDesktop'] . "px;\n";
    }
    if (!empty($settings['paddingBottomDesktop'])) {
        $css .= "  padding-bottom: " . $settings['paddingBottomDesktop'] . "px;\n";
    }
    if (!empty($settings['paddingLeftDesktop'])) {
        $css .= "  padding-left: " . $settings['paddingLeftDesktop'] . "px;\n";
    }
    if (!empty($settings['paddingRightDesktop'])) {
        $css .= "  padding-right: " . $settings['paddingRightDesktop'] . "px;\n";
    }
    
    $css .= "}\n\n";
    
    // Mobile styles
    $css .= "@media (max-width: 767px) {\n";
    $css .= "  #{$sectionId} {\n";
    
    // Height - Mobile
    $heightMobile = $settings['heightMobile'] ?? '50vh';
    if ($heightMobile === 'custom') {
        $heightMobile = ($settings['customHeightMobile'] ?? 400) . 'px';
    }
    $css .= "    height: {$heightMobile};\n";
    
    // Background - Mobile
    $bgTypeMobile = $settings['bgTypeMobile'] ?? $settings['bgType'] ?? 'color';
    switch ($bgTypeMobile) {
        case 'color':
            $bgColor = $settings['bgColorMobile'] ?? $settings['bgColor'] ?? '#3B82F6';
            $css .= "    background-color: {$bgColor};\n";
            break;
        
        case 'gradient':
            $gradient1 = $settings['bgGradient1Mobile'] ?? '#3B82F6';
            $gradient2 = $settings['bgGradient2Mobile'] ?? '#1E40AF';
            $direction = $settings['bgGradientDirectionMobile'] ?? 'to-b';
            $css .= "    background: linear-gradient({$direction}, {$gradient1}, {$gradient2});\n";
            break;
        
        case 'image':
            if (!empty($settings['bgImageMobile'])) {
                $css .= "    background-image: url('" . esc_url($settings['bgImageMobile']) . "');\n";
                $css .= "    background-size: " . ($settings['bgSizeMobile'] ?? 'cover') . ";\n";
                $css .= "    background-position: " . ($settings['bgPositionMobile'] ?? 'center') . ";\n";
                $css .= "    background-repeat: " . ($settings['bgRepeatMobile'] ?? 'no-repeat') . ";\n";
            }
            break;
    }
    
    // Padding - Mobile
    if (!empty($settings['paddingTopMobile'])) {
        $css .= "    padding-top: " . $settings['paddingTopMobile'] . "px;\n";
    }
    if (!empty($settings['paddingBottomMobile'])) {
        $css .= "    padding-bottom: " . $settings['paddingBottomMobile'] . "px;\n";
    }
    if (!empty($settings['paddingLeftMobile'])) {
        $css .= "    padding-left: " . $settings['paddingLeftMobile'] . "px;\n";
    }
    if (!empty($settings['paddingRightMobile'])) {
        $css .= "    padding-right: " . $settings['paddingRightMobile'] . "px;\n";
    }
    
    $css .= "  }\n";
    $css .= "}\n\n";
    
    // Title styles - Desktop
    $css .= "#{$sectionId} .hero-title {\n";
    if (!empty($settings['titleFontSizeDesktop'])) {
        $css .= "  font-size: " . $settings['titleFontSizeDesktop'] . "px;\n";
    }
    if (!empty($settings['titleFontFamilyDesktop'])) {
        $css .= "  font-family: '" . $settings['titleFontFamilyDesktop'] . "';\n";
    }
    if (!empty($settings['titleFontWeightDesktop'])) {
        $css .= "  font-weight: " . $settings['titleFontWeightDesktop'] . ";\n";
    }
    if (!empty($settings['titleColorDesktop'])) {
        $css .= "  color: " . $settings['titleColorDesktop'] . ";\n";
    }
    if (!empty($settings['titleLineHeightDesktop'])) {
        $css .= "  line-height: " . $settings['titleLineHeightDesktop'] . ";\n";
    }
    if (!empty($settings['titleTextAlignDesktop'])) {
        $css .= "  text-align: " . $settings['titleTextAlignDesktop'] . ";\n";
    }
    $css .= "}\n\n";
    
    // Title styles - Mobile
    $css .= "@media (max-width: 767px) {\n";
    $css .= "  #{$sectionId} .hero-title {\n";
    if (!empty($settings['titleFontSizeMobile'])) {
        $css .= "    font-size: " . $settings['titleFontSizeMobile'] . "px;\n";
    }
    if (!empty($settings['titleColorMobile'])) {
        $css .= "    color: " . $settings['titleColorMobile'] . ";\n";
    }
    if (!empty($settings['titleTextAlignMobile'])) {
        $css .= "    text-align: " . $settings['titleTextAlignMobile'] . ";\n";
    }
    $css .= "  }\n";
    $css .= "}\n\n";
    
    // Subtitle styles - Desktop
    $css .= "#{$sectionId} .hero-subtitle {\n";
    if (!empty($settings['subtitleFontSizeDesktop'])) {
        $css .= "  font-size: " . $settings['subtitleFontSizeDesktop'] . "px;\n";
    }
    if (!empty($settings['subtitleFontFamilyDesktop'])) {
        $css .= "  font-family: '" . $settings['subtitleFontFamilyDesktop'] . "';\n";
    }
    if (!empty($settings['subtitleFontWeightDesktop'])) {
        $css .= "  font-weight: " . $settings['subtitleFontWeightDesktop'] . ";\n";
    }
    if (!empty($settings['subtitleColorDesktop'])) {
        $css .= "  color: " . $settings['subtitleColorDesktop'] . ";\n";
    }
    if (!empty($settings['subtitleLineHeightDesktop'])) {
        $css .= "  line-height: " . $settings['subtitleLineHeightDesktop'] . ";\n";
    }
    if (!empty($settings['subtitleTextAlignDesktop'])) {
        $css .= "  text-align: " . $settings['subtitleTextAlignDesktop'] . ";\n";
    }
    $css .= "}\n\n";
    
    // Subtitle styles - Mobile
    $css .= "@media (max-width: 767px) {\n";
    $css .= "  #{$sectionId} .hero-subtitle {\n";
    if (!empty($settings['subtitleFontSizeMobile'])) {
        $css .= "    font-size: " . $settings['subtitleFontSizeMobile'] . "px;\n";
    }
    if (!empty($settings['subtitleColorMobile'])) {
        $css .= "    color: " . $settings['subtitleColorMobile'] . ";\n";
    }
    if (!empty($settings['subtitleTextAlignMobile'])) {
        $css .= "    text-align: " . $settings['subtitleTextAlignMobile'] . ";\n";
    }
    $css .= "  }\n";
    $css .= "}\n\n";
    
    // Button styles
    $css .= "#{$sectionId} .btn {\n";
    $css .= "  display: inline-block;\n";
    $css .= "  padding: 12px 24px;\n";
    $css .= "  border-radius: 6px;\n";
    $css .= "  text-decoration: none;\n";
    $css .= "  font-weight: 500;\n";
    $css .= "  transition: all 0.2s;\n";
    $css .= "}\n\n";
    
    $css .= "#{$sectionId} .btn-primary {\n";
    $css .= "  background-color: #3B82F6;\n";
    $css .= "  color: white;\n";
    $css .= "}\n\n";
    
    $css .= "#{$sectionId} .btn-primary:hover {\n";
    $css .= "  background-color: #2563EB;\n";
    $css .= "}\n\n";
    
    $css .= "#{$sectionId} .btn-secondary {\n";
    $css .= "  background-color: #6B7280;\n";
    $css .= "  color: white;\n";
    $css .= "}\n\n";
    
    $css .= "#{$sectionId} .btn-outline {\n";
    $css .= "  background-color: transparent;\n";
    $css .= "  color: white;\n";
    $css .= "  border: 2px solid white;\n";
    $css .= "}\n\n";
    
    return $css;
}
?> 