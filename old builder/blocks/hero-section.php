<?php
/**
 * Hero Section - רכיב הירו בפרונטאנד
 */

// Helper functions for responsive values
require_once __DIR__ . '/../includes/responsive-helpers.php';

class HeroSection {
    private $data;
    private $deviceType;
    
    public function __construct($data = []) {
        $this->data = array_merge($this->getDefaultData(), $data);
        $this->deviceType = getDeviceType();
    }
    
    /**
     * קבלת נתוני ברירת מחדל
     */
    private function getDefaultData() {
        return [
            'title' => 'ברוכים הבאים לחנות שלנו',
            'subtitle' => 'גלה את המוצרים הטובים ביותר במחירים הכי טובים',
            'buttons' => [[
                'text' => 'קנה עכשיו',
                'link' => '/products',
                'newTab' => false,
                'style' => 'filled'
            ]],
            'bgType' => 'color',
            'bgColor' => '#3B82F6',
            'bgGradient1' => '#3B82F6',
            'bgGradient2' => '#1E40AF',
            'bgGradientDirection' => 'to-b',
            'bgImage' => '',
            'bgVideo' => '',
            'bgMobileVideo' => '',
            'bgTabletVideo' => '',
            'contentPosition' => 'center-center',
            'heightType' => 'auto',
            'heightValue' => 500,
            'titleFontSize' => '36',
            'titleFontFamily' => 'Noto Sans Hebrew',
            'titleFontWeight' => '700',
            'titleColor' => '#1F2937',
            'subtitleFontSize' => '18',
            'subtitleFontFamily' => 'Noto Sans Hebrew',
            'subtitleFontWeight' => '400',
            'subtitleColor' => '#6B7280',
            'buttonFontSize' => '16',
            'buttonFontFamily' => 'Noto Sans Hebrew',
            'buttonFontWeight' => '500',
            'buttonBgColor' => '#3B82F6',
            'buttonTextColor' => '#FFFFFF',
            'buttonBorderColor' => '#3B82F6'
        ];
    }
    
    /**
     * רינדור הסקשן
     */
    public function render() {
        $sectionClasses = $this->getSectionClasses();
        $contentClasses = $this->getContentClasses();
        $backgroundStyle = $this->getBackgroundStyle();
        $videoSource = $this->getVideoSource();
        
        ob_start();
        ?>
        <section class="<?php echo esc_attr($sectionClasses); ?>" style="<?php echo esc_attr($backgroundStyle); ?>">
            <?php if ($videoSource): ?>
                <video autoplay muted loop class="absolute inset-0 w-full h-full object-cover">
                    <source src="<?php echo esc_url($videoSource); ?>" type="video/mp4">
                </video>
            <?php endif; ?>
            
            <div class="container mx-auto px-4 relative z-10">
                <div class="<?php echo esc_attr($contentClasses); ?>">
                    <?php $this->renderContent(); ?>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
    
    /**
     * קבלת מחלקות הסקשן
     */
    private function getSectionClasses() {
        $classes = ['relative'];
        
        // Height
        if ($this->data['heightType'] === 'fixed') {
            $classes[] = 'h-screen';
        } elseif ($this->data['heightType'] === 'custom') {
            $classes[] = 'min-h-[' . $this->data['heightValue'] . 'px]';
        } else {
            $classes[] = 'py-20';
        }
        
        // Background overlay
        if ($this->data['bgType'] === 'video' || $this->data['bgType'] === 'image') {
            $classes[] = 'overflow-hidden';
        }
        
        return implode(' ', $classes);
    }
    
    /**
     * קבלת מחלקות התוכן
     */
    private function getContentClasses() {
        $classes = ['flex', 'h-full'];
        
        // Content position
        $position = $this->data['contentPosition'];
        $positionMap = [
            'top-left' => 'items-start justify-start',
            'top-center' => 'items-start justify-center',
            'top-right' => 'items-start justify-end',
            'center-left' => 'items-center justify-start',
            'center-center' => 'items-center justify-center',
            'center-right' => 'items-center justify-end',
            'bottom-left' => 'items-end justify-start',
            'bottom-center' => 'items-end justify-center',
            'bottom-right' => 'items-end justify-end'
        ];
        
        $classes[] = $positionMap[$position] ?? 'items-center justify-center';
        
        return implode(' ', $classes);
    }
    
    /**
     * קבלת סגנון הרקע
     */
    private function getBackgroundStyle() {
        $styles = [];
        
        switch ($this->data['bgType']) {
            case 'color':
                $styles[] = 'background-color: ' . $this->data['bgColor'];
                break;
                
            case 'gradient':
                $direction = $this->data['bgGradientDirection'];
                $directionMap = [
                    'to-t' => 'to top',
                    'to-b' => 'to bottom',
                    'to-l' => 'to left',
                    'to-r' => 'to right',
                    'to-tl' => 'to top left',
                    'to-tr' => 'to top right',
                    'to-bl' => 'to bottom left',
                    'to-br' => 'to bottom right'
                ];
                $cssDirection = $directionMap[$direction] ?? 'to bottom';
                $styles[] = 'background: linear-gradient(' . $cssDirection . ', ' . 
                           $this->data['bgGradient1'] . ', ' . $this->data['bgGradient2'] . ')';
                break;
                
            case 'image':
                if ($this->data['bgImage']) {
                    $styles[] = 'background-image: url(' . $this->data['bgImage'] . ')';
                    $styles[] = 'background-size: ' . $this->data['bgImageSize'];
                    $styles[] = 'background-position: ' . $this->data['bgImagePosition'];
                    $styles[] = 'background-repeat: ' . $this->data['bgImageRepeat'];
                }
                break;
        }
        
        return implode('; ', $styles);
    }
    
    /**
     * קבלת מקור הווידאו לפי המכשיר
     */
    private function getVideoSource() {
        if ($this->data['bgType'] !== 'video') {
            return null;
        }
        
        switch ($this->deviceType) {
            case 'mobile':
                return $this->data['bgMobileVideo'] ?: $this->data['bgVideo'];
                
            case 'tablet':
                return $this->data['bgTabletVideo'] ?: $this->data['bgVideo'];
                
            default:
                return $this->data['bgVideo'];
        }
    }
    
    /**
     * רינדור התוכן
     */
    private function renderContent() {
        ?>
        <div class="text-center max-w-4xl mx-auto">
            <?php $this->renderTitle(); ?>
            <?php $this->renderSubtitle(); ?>
            <?php $this->renderButtons(); ?>
        </div>
        <?php
    }
    
    /**
     * רינדור הכותרת
     */
    private function renderTitle() {
        if (empty($this->data['title'])) return;
        
        $titleStyle = $this->getTitleStyle();
        ?>
        <h1 class="mb-6" style="<?php echo esc_attr($titleStyle); ?>">
            <?php echo esc_html($this->data['title']); ?>
        </h1>
        <?php
    }
    
    /**
     * רינדור תת הכותרת
     */
    private function renderSubtitle() {
        if (empty($this->data['subtitle'])) return;
        
        $subtitleStyle = $this->getSubtitleStyle();
        ?>
        <p class="mb-8" style="<?php echo esc_attr($subtitleStyle); ?>">
            <?php echo esc_html($this->data['subtitle']); ?>
        </p>
        <?php
    }
    
    /**
     * רינדור הכפתורים
     */
    private function renderButtons() {
        if (empty($this->data['buttons'])) return;
        
        ?>
        <div class="flex flex-wrap gap-4 justify-center">
            <?php foreach ($this->data['buttons'] as $button): ?>
                <?php $this->renderButton($button); ?>
            <?php endforeach; ?>
        </div>
        <?php
    }
    
    /**
     * רינדור כפתור יחיד
     */
    private function renderButton($button) {
        $buttonStyle = $this->getButtonStyle($button);
        $buttonClasses = $this->getButtonClasses($button);
        $target = !empty($button['newTab']) ? '_blank' : '_self';
        $icon = !empty($button['icon']) ? $button['icon'] : '';
        
        ?>
        <a href="<?php echo esc_url($button['link']); ?>" 
           target="<?php echo esc_attr($target); ?>"
           class="<?php echo esc_attr($buttonClasses); ?>"
           style="<?php echo esc_attr($buttonStyle); ?>">
            <?php if ($icon): ?>
                <i class="<?php echo esc_attr($icon); ?>"></i>
            <?php endif; ?>
            <?php echo esc_html($button['text']); ?>
        </a>
        <?php
    }
    
    /**
     * קבלת סגנון הכותרת
     */
    private function getTitleStyle() {
        $fontSize = getResponsiveValue($this->data, 'titleFontSize', $this->deviceType);
        $fontFamily = getResponsiveValue($this->data, 'titleFontFamily', $this->deviceType);
        $fontWeight = getResponsiveValue($this->data, 'titleFontWeight', $this->deviceType);
        $color = $this->data['titleColor'];
        
        $styles = [
            'font-size: ' . $fontSize . 'px',
            'font-family: "' . $fontFamily . '", sans-serif',
            'font-weight: ' . $fontWeight,
            'color: ' . $color
        ];
        
        return implode('; ', $styles);
    }
    
    /**
     * קבלת סגנון תת הכותרת
     */
    private function getSubtitleStyle() {
        $fontSize = getResponsiveValue($this->data, 'subtitleFontSize', $this->deviceType);
        $fontFamily = getResponsiveValue($this->data, 'subtitleFontFamily', $this->deviceType);
        $fontWeight = getResponsiveValue($this->data, 'subtitleFontWeight', $this->deviceType);
        $color = $this->data['subtitleColor'];
        
        $styles = [
            'font-size: ' . $fontSize . 'px',
            'font-family: "' . $fontFamily . '", sans-serif',
            'font-weight: ' . $fontWeight,
            'color: ' . $color
        ];
        
        return implode('; ', $styles);
    }
    
    /**
     * קבלת מחלקות הכפתור
     */
    private function getButtonClasses($button) {
        $classes = ['inline-flex', 'items-center', 'gap-2', 'px-8', 'py-3', 'rounded-lg', 'transition-all', 'duration-300'];
        
        switch ($button['style']) {
            case 'outline':
                $classes[] = 'border-2';
                break;
            case 'ghost':
                $classes[] = 'bg-transparent';
                break;
            default:
                $classes[] = 'border-2';
                break;
        }
        
        if (!empty($button['fullWidth'])) {
            $classes[] = 'w-full';
        }
        
        return implode(' ', $classes);
    }
    
    /**
     * קבלת סגנון הכפתור
     */
    private function getButtonStyle($button) {
        $fontSize = getResponsiveValue($this->data, 'buttonFontSize', $this->deviceType);
        $fontFamily = getResponsiveValue($this->data, 'buttonFontFamily', $this->deviceType);
        $fontWeight = getResponsiveValue($this->data, 'buttonFontWeight', $this->deviceType);
        
        $styles = [
            'font-size: ' . $fontSize . 'px',
            'font-family: "' . $fontFamily . '", sans-serif',
            'font-weight: ' . $fontWeight
        ];
        
        // Color styles based on button style
        switch ($button['style']) {
            case 'filled':
                $styles[] = 'background-color: ' . $this->data['buttonBgColor'];
                $styles[] = 'color: ' . $this->data['buttonTextColor'];
                $styles[] = 'border-color: ' . $this->data['buttonBorderColor'];
                break;
                
            case 'outline':
                $styles[] = 'background-color: transparent';
                $styles[] = 'color: ' . $this->data['buttonBgColor'];
                $styles[] = 'border-color: ' . $this->data['buttonBorderColor'];
                break;
                
            case 'ghost':
                $styles[] = 'background-color: transparent';
                $styles[] = 'color: ' . $this->data['buttonBgColor'];
                $styles[] = 'border-color: transparent';
                break;
        }
        
        // Custom spacing
        if (!empty($button['paddingTop'])) {
            $styles[] = 'padding-top: ' . $button['paddingTop'] . 'px';
        }
        if (!empty($button['paddingBottom'])) {
            $styles[] = 'padding-bottom: ' . $button['paddingBottom'] . 'px';
        }
        if (!empty($button['paddingRight'])) {
            $styles[] = 'padding-right: ' . $button['paddingRight'] . 'px';
        }
        if (!empty($button['paddingLeft'])) {
            $styles[] = 'padding-left: ' . $button['paddingLeft'] . 'px';
        }
        
        // Border radius
        if (!empty($button['rounded'])) {
            $styles[] = 'border-radius: ' . $button['rounded'] . 'px';
        }
        
        return implode('; ', $styles);
    }
}

/**
 * פונקציה לרינדור סקשן הירו
 */
function renderHeroSection($data = []) {
    $hero = new HeroSection($data);
    return $hero->render();
}
?> 