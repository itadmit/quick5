<?php
/**
 * Hero Section - Advanced
 * סקשן תמונת פתיחה מתקדם עם תמיכה בכל האפשרויות
 */

// הגדרות ברירת מחדל
$defaultSettings = [
    'title' => '',
    'subtitle' => '',
    'description' => '', // תמיכה בשני השמות
    'content' => '',
    'bg_type' => 'color',
    'bg_color' => '#1e40af',
    'background_color' => '#1e40af', // תמיכה בשני השמות
    'gradient_start' => '#1e40af',
    'gradient_end' => '#3b82f6',
    'gradient_direction' => 'to bottom',
    'bg_image' => '',
    'bg_image_size' => 'cover',
    'bg_video_url' => '',
    'bg_video_file' => '',
    'video_type' => 'file',
    'video_autoplay' => true,
    'video_loop' => true,
    'video_muted' => true,
    'video_overlay' => true,
    'video_overlay_color' => '#000000',
    'title_color' => '#ffffff',
    'text_color' => '#ffffff', // תמיכה בשני השמות
    'title_size' => 48,
    'subtitle_color' => '#e5e7eb',
    'subtitle_size' => 24,
    'content_color' => '#d1d5db',
    'content_size' => 16,
    'button_bg_color' => '#f59e0b',
    'button_color' => '#f59e0b', // תמיכה בשני השמות
    'button_text_color' => '#ffffff',
    'button_font_size' => 16,
    'button_text' => '',
    'button_link' => '#',
    'padding_top' => 80,
    'padding_bottom' => 80,
    'padding_left' => 20,
    'padding_right' => 20,
    'margin_top' => 0,
    'margin_bottom' => 0,
    'margin_left' => 0,
    'margin_right' => 0,
    'custom_css' => '',
    'buttons' => []
];

$settings = array_merge($defaultSettings, $sectionSettings ?? []);

// תמיכה בשמות שונים של אותן הגדרות
if (!empty($settings['description']) && empty($settings['subtitle'])) {
    $settings['subtitle'] = $settings['description'];
}
if (!empty($settings['background_color']) && empty($settings['bg_color'])) {
    $settings['bg_color'] = $settings['background_color'];
}
if (!empty($settings['text_color']) && empty($settings['title_color'])) {
    $settings['title_color'] = $settings['text_color'];
}
if (!empty($settings['button_color']) && empty($settings['button_bg_color'])) {
    $settings['button_bg_color'] = $settings['button_color'];
}

// יצירת סגנון הרקע
$backgroundStyle = '';
switch ($settings['bg_type']) {
    case 'color':
        $backgroundStyle = "background-color: {$settings['bg_color']};";
        break;
    case 'gradient':
        $backgroundStyle = "background: linear-gradient({$settings['gradient_direction']}, {$settings['gradient_start']}, {$settings['gradient_end']});";
        break;
    case 'image':
        if (!empty($settings['bg_image'])) {
            $backgroundStyle = "background-image: url('{$settings['bg_image']}'); background-size: {$settings['bg_image_size']}; background-position: center; background-repeat: no-repeat;";
        }
        break;
    case 'video':
        // הסרטון יטופל בנפרד - רקע שחור כברירת מחדל
        $backgroundStyle = "background-color: #000000;";
        break;
}

// יצירת סגנון המרווחים
$spacingStyle = "
    padding-top: {$settings['padding_top']}px;
    padding-bottom: {$settings['padding_bottom']}px;
    padding-left: {$settings['padding_left']}px;
    padding-right: {$settings['padding_right']}px;
    margin-top: {$settings['margin_top']}px;
    margin-bottom: {$settings['margin_bottom']}px;
    margin-left: {$settings['margin_left']}px;
    margin-right: {$settings['margin_right']}px;
";

// יצירת סגנון הטקסט
$titleStyle = "color: {$settings['title_color']}; font-size: {$settings['title_size']}px;";
$subtitleStyle = "color: {$settings['subtitle_color']}; font-size: {$settings['subtitle_size']}px;";
$contentStyle = "color: {$settings['content_color']}; font-size: {$settings['content_size']}px;";

// יצירת סגנון הכפתורים
$buttonStyle = "
    background-color: {$settings['button_bg_color']};
    color: {$settings['button_text_color']};
    font-size: {$settings['button_font_size']}px;
";
?>

<section id="<?= $sectionId ?>" class="hero-section relative overflow-hidden" 
         style="<?= $backgroundStyle . $spacingStyle ?>">
    
    <!-- סרטון רקע -->
    <?php if ($settings['bg_type'] === 'video'): ?>
        <?php 
        $videoSrc = '';
        if ($settings['video_type'] === 'url' && !empty($settings['bg_video_url'])) {
            $videoSrc = $settings['bg_video_url'];
        } elseif ($settings['video_type'] === 'file' && !empty($settings['bg_video_file'])) {
            $videoSrc = $settings['bg_video_file'];
        }
        ?>
        
        <?php if (!empty($videoSrc)): ?>
            <div class="absolute inset-0 z-0">
                <?php if (strpos($videoSrc, 'youtube.com') !== false || strpos($videoSrc, 'youtu.be') !== false): ?>
                    <!-- YouTube Video -->
                    <?php
                    $videoId = '';
                    if (strpos($videoSrc, 'youtube.com') !== false) {
                        parse_str(parse_url($videoSrc, PHP_URL_QUERY), $params);
                        $videoId = $params['v'] ?? '';
                    } elseif (strpos($videoSrc, 'youtu.be') !== false) {
                        $videoId = basename(parse_url($videoSrc, PHP_URL_PATH));
                    }
                    ?>
                    <?php if (!empty($videoId)): ?>
                        <iframe class="w-full h-full object-cover" 
                                src="https://www.youtube.com/embed/<?= $videoId ?>?autoplay=<?= $settings['video_autoplay'] ? '1' : '0' ?>&mute=<?= $settings['video_muted'] ? '1' : '0' ?>&loop=<?= $settings['video_loop'] ? '1' : '0' ?>&controls=0&showinfo=0&rel=0&modestbranding=1&playsinline=1"
                                frameborder="0" allowfullscreen></iframe>
                    <?php endif; ?>
                <?php elseif (strpos($videoSrc, 'vimeo.com') !== false): ?>
                    <!-- Vimeo Video -->
                    <?php
                    $videoId = basename(parse_url($videoSrc, PHP_URL_PATH));
                    ?>
                    <iframe class="w-full h-full object-cover" 
                            src="https://player.vimeo.com/video/<?= $videoId ?>?autoplay=<?= $settings['video_autoplay'] ? '1' : '0' ?>&muted=<?= $settings['video_muted'] ? '1' : '0' ?>&loop=<?= $settings['video_loop'] ? '1' : '0' ?>&controls=0&title=0&byline=0&portrait=0&background=1"
                            frameborder="0" allowfullscreen></iframe>
                <?php else: ?>
                    <!-- Regular Video File -->
                    <video class="w-full h-full object-cover" 
                           <?= $settings['video_autoplay'] ? 'autoplay' : '' ?>
                           <?= $settings['video_muted'] ? 'muted' : '' ?>
                           <?= $settings['video_loop'] ? 'loop' : '' ?>
                           playsinline>
                        <source src="<?= htmlspecialchars($videoSrc) ?>" type="video/mp4">
                        <source src="<?= htmlspecialchars($videoSrc) ?>" type="video/webm">
                    </video>
                <?php endif; ?>
                
                <!-- שכבת כיסוי -->
                <?php if ($settings['video_overlay']): ?>
                    <div class="absolute inset-0" style="background-color: <?= htmlspecialchars($settings['video_overlay_color']) ?>;"></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- תוכן -->
    <div class="relative z-10 max-w-4xl mx-auto text-center">
        
        <!-- כותרת ראשית -->
        <?php if (!empty($settings['title'])): ?>
            <h1 class="font-bold mb-4 leading-tight" style="<?= $titleStyle ?>">
                <?= htmlspecialchars($settings['title']) ?>
            </h1>
        <?php endif; ?>
        
        <!-- תת כותרת -->
        <?php if (!empty($settings['subtitle']) || !empty($settings['description'])): ?>
            <h2 class="font-medium mb-6 leading-relaxed" style="<?= $subtitleStyle ?>">
                <?= htmlspecialchars($settings['subtitle'] ?? $settings['description'] ?? '') ?>
            </h2>
        <?php endif; ?>
        
        <!-- תוכן טקסט -->
        <?php if (!empty($settings['content'])): ?>
            <div class="mb-8 leading-relaxed" style="<?= $contentStyle ?>">
                <?= nl2br(htmlspecialchars($settings['content'])) ?>
            </div>
        <?php endif; ?>
        
        <!-- כפתורים -->
        <?php if (!empty($settings['button_text']) || (!empty($settings['buttons']) && is_array($settings['buttons']))): ?>
            <div class="flex flex-wrap justify-center gap-4">
                <?php if (!empty($settings['button_text'])): ?>
                    <!-- כפתור בפורמט הישן -->
                    <a href="<?= htmlspecialchars($settings['button_link'] ?? '#') ?>" 
                       class="hero-button hero-button-solid px-6 py-3 rounded-lg font-medium transition-all duration-200 hover:transform hover:scale-105"
                       style="<?= $buttonStyle ?>">
                        <?= htmlspecialchars($settings['button_text']) ?>
                    </a>
                <?php endif; ?>
                
                <?php if (!empty($settings['buttons']) && is_array($settings['buttons'])): ?>
                    <!-- כפתורים בפורמט החדש -->
                    <?php foreach ($settings['buttons'] as $button): ?>
                        <?php if (!empty($button['label'])): ?>
                            <a href="<?= htmlspecialchars($button['link'] ?? '#') ?>" 
                               class="hero-button hero-button-<?= $button['style'] ?? 'solid' ?> px-6 py-3 rounded-lg font-medium transition-all duration-200 hover:transform hover:scale-105"
                               style="<?= $buttonStyle ?>">
                                <?= htmlspecialchars($button['label']) ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    </div>
</section>

<!-- CSS מותאם אישית -->
<?php if (!empty($settings['custom_css'])): ?>
    <style>
        <?= $settings['custom_css'] ?>
    </style>
<?php endif; ?>

<!-- סגנונות כפתורים -->
<style>
    .hero-button-solid {
        background-color: <?= $settings['button_bg_color'] ?>;
        color: <?= $settings['button_text_color'] ?>;
        border: 2px solid <?= $settings['button_bg_color'] ?>;
    }
    
    .hero-button-outline {
        background-color: transparent;
        color: <?= $settings['button_bg_color'] ?>;
        border: 2px solid <?= $settings['button_bg_color'] ?>;
    }
    
    .hero-button-underline {
        background-color: transparent;
        color: <?= $settings['button_bg_color'] ?>;
        border: none;
        border-bottom: 2px solid <?= $settings['button_bg_color'] ?>;
        border-radius: 0;
    }
    
    .hero-button-text {
        background-color: transparent;
        color: <?= $settings['button_bg_color'] ?>;
        border: none;
    }
    
    .hero-button-solid:hover {
        background-color: <?= $settings['button_text_color'] ?>;
        color: <?= $settings['button_bg_color'] ?>;
    }
    
    .hero-button-outline:hover {
        background-color: <?= $settings['button_bg_color'] ?>;
        color: <?= $settings['button_text_color'] ?>;
    }
    
    .hero-button-underline:hover {
        border-bottom-width: 3px;
    }
    
    .hero-button-text:hover {
        opacity: 0.8;
    }
</style> 