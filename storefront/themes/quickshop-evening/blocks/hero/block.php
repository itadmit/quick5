<?php
/**
 * Hero Block - QuickShop Evening Theme
 * בלוק כותרת ראשית עם רקע ותמונה
 */

function renderHeroBlock($settings, $store) {
    // הגדרות ברירת מחדל
    $defaults = [
        'title' => 'ברוכים הבאים ל' . ($store['name'] ?? 'החנות שלנו'),
        'subtitle' => 'גלה את המוצרים הטובים ביותר במחירים הכי טובים',
        'button_text' => 'קנה עכשיו',
        'button_link' => '/category/all',
        'button_secondary_text' => 'למד עוד',
        'button_secondary_link' => '/about',
        'background_image' => '',
        'background_color' => '',
        'text_color' => 'text-white',
        'overlay' => true,
        'overlay_opacity' => 40,
        'height' => 'min-h-[500px]',
        'text_align' => 'center',
        'show_secondary_button' => true,
        'animation' => 'fade-in'
    ];
    
    $settings = array_merge($defaults, $settings);
    
    // הגדרת סגנון רקע
    $backgroundStyle = '';
    if (!empty($settings['background_image'])) {
        $backgroundStyle = "background-image: url('{$settings['background_image']}'); background-size: cover; background-position: center;";
    } elseif (!empty($settings['background_color'])) {
        $backgroundStyle = "background-color: {$settings['background_color']};";
    } else {
        $backgroundStyle = "background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));";
    }
    
    // הגדרת מחלקות CSS
    $containerClasses = [
        'relative',
        $settings['height'],
        'flex',
        'items-center',
        'justify-center',
        'overflow-hidden'
    ];
    
    $textClasses = [
        'relative',
        'z-10',
        'max-w-4xl',
        'mx-auto',
        'px-4',
        'sm:px-6',
        'lg:px-8'
    ];
    
    if ($settings['text_align'] === 'center') {
        $textClasses[] = 'text-center';
    } elseif ($settings['text_align'] === 'left') {
        $textClasses[] = 'text-left';
    } else {
        $textClasses[] = 'text-right';
    }
    
    ?>
    
    <section class="<?= implode(' ', $containerClasses) ?>" 
             style="<?= $backgroundStyle ?>" 
             data-block="hero"
             data-animation="<?= $settings['animation'] ?>">
        
        <?php if ($settings['overlay'] && !empty($settings['background_image'])): ?>
            <div class="absolute inset-0 bg-black" 
                 style="opacity: <?= $settings['overlay_opacity'] / 100 ?>"></div>
        <?php endif; ?>
        
        <div class="<?= implode(' ', $textClasses) ?>">
            <!-- Title -->
            <h1 class="text-4xl md:text-6xl font-bold <?= $settings['text_color'] ?> mb-6 leading-tight">
                <?= htmlspecialchars($settings['title']) ?>
            </h1>
            
            <!-- Subtitle -->
            <?php if (!empty($settings['subtitle'])): ?>
                <p class="text-xl md:text-2xl <?= $settings['text_color'] ?> mb-8 opacity-90 max-w-2xl mx-auto leading-relaxed">
                    <?= htmlspecialchars($settings['subtitle']) ?>
                </p>
            <?php endif; ?>
            
            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <!-- Primary Button -->
                <?php if (!empty($settings['button_text'])): ?>
                    <a href="<?= htmlspecialchars($settings['button_link']) ?>" 
                       class="inline-flex items-center justify-center px-8 py-4 bg-white text-primary font-semibold rounded-lg hover:bg-gray-100 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="ri-shopping-bag-line ml-2"></i>
                        <?= htmlspecialchars($settings['button_text']) ?>
                    </a>
                <?php endif; ?>
                
                <!-- Secondary Button -->
                <?php if ($settings['show_secondary_button'] && !empty($settings['button_secondary_text'])): ?>
                    <a href="<?= htmlspecialchars($settings['button_secondary_link']) ?>" 
                       class="inline-flex items-center justify-center px-8 py-4 border-2 border-white <?= $settings['text_color'] ?> font-semibold rounded-lg hover:bg-white hover:text-primary transition-all duration-300 transform hover:scale-105">
                        <i class="ri-information-line ml-2"></i>
                        <?= htmlspecialchars($settings['button_secondary_text']) ?>
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Scroll Indicator -->
            <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
                <div class="w-6 h-10 border-2 border-white rounded-full flex justify-center">
                    <div class="w-1 h-3 bg-white rounded-full mt-2 animate-pulse"></div>
                </div>
            </div>
        </div>
        
        <!-- Decorative Elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white bg-opacity-10 rounded-full -translate-y-32 translate-x-32 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white bg-opacity-5 rounded-full translate-y-24 -translate-x-24 animate-pulse" style="animation-delay: 1s;"></div>
    </section>
    
    <?php
}

// קבלת הגדרות ברירת מחדל לבלוק (לשימוש בקסטומיזר)
function getHeroBlockDefaults() {
    return [
        'title' => 'ברוכים הבאים לחנות שלנו',
        'subtitle' => 'גלה את המוצרים הטובים ביותר במחירים הכי טובים',
        'button_text' => 'קנה עכשיו',
        'button_link' => '/category/all',
        'button_secondary_text' => 'למד עוד',
        'button_secondary_link' => '/about',
        'background_image' => '',
        'background_color' => '',
        'text_color' => 'text-white',
        'overlay' => true,
        'overlay_opacity' => 40,
        'height' => 'min-h-[500px]',
        'text_align' => 'center',
        'show_secondary_button' => true,
        'animation' => 'fade-in'
    ];
}

// קבלת הגדרות השדות לקסטומיזר
function getHeroBlockFields() {
    return [
        'title' => [
            'type' => 'text',
            'label' => 'כותרת',
            'required' => true
        ],
        'subtitle' => [
            'type' => 'textarea',
            'label' => 'כותרת משנה',
            'rows' => 2
        ],
        'button_text' => [
            'type' => 'text',
            'label' => 'טקסט כפתור ראשי'
        ],
        'button_link' => [
            'type' => 'text',
            'label' => 'קישור כפתור ראשי'
        ],
        'show_secondary_button' => [
            'type' => 'checkbox',
            'label' => 'הצג כפתור משני'
        ],
        'button_secondary_text' => [
            'type' => 'text',
            'label' => 'טקסט כפתור משני'
        ],
        'button_secondary_link' => [
            'type' => 'text',
            'label' => 'קישור כפתור משני'
        ],
        'background_image' => [
            'type' => 'image',
            'label' => 'תמונת רקע'
        ],
        'background_color' => [
            'type' => 'color',
            'label' => 'צבע רקע'
        ],
        'text_color' => [
            'type' => 'select',
            'label' => 'צבע טקסט',
            'options' => [
                'text-white' => 'לבן',
                'text-black' => 'שחור',
                'text-gray-900' => 'אפור כהה'
            ]
        ],
        'overlay' => [
            'type' => 'checkbox',
            'label' => 'הצג שכבת כיסוי'
        ],
        'overlay_opacity' => [
            'type' => 'range',
            'label' => 'שקיפות שכבת כיסוי',
            'min' => 0,
            'max' => 100,
            'step' => 10
        ],
        'height' => [
            'type' => 'select',
            'label' => 'גובה',
            'options' => [
                'min-h-[400px]' => 'נמוך',
                'min-h-[500px]' => 'בינוני',
                'min-h-[600px]' => 'גבוה',
                'min-h-screen' => 'מלא'
            ]
        ],
        'text_align' => [
            'type' => 'select',
            'label' => 'יישור טקסט',
            'options' => [
                'center' => 'מרכז',
                'left' => 'שמאל',
                'right' => 'ימין'
            ]
        ]
    ];
}
?> 