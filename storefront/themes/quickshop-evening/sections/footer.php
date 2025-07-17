<?php
/**
 * Footer Section
 * סקשן פוטר עם קישורים, רשתות חברתיות וזכויות יוצרים
 */

// הגדרות ברירת מחדל
$defaultSettings = [
    'copyright' => '© 2024 ניקול בוטיק. כל הזכויות שמורות.',
    'show_social' => true,
    'show_links' => true,
    'background_color' => '#1f2937',
    'text_color' => '#ffffff',
    'social_facebook' => '',
    'social_instagram' => '',
    'social_twitter' => '',
    'social_youtube' => ''
];

$settings = array_merge($defaultSettings, $sectionSettings ?? []);

// קישורים מהירים
$quickLinks = [
    ['title' => 'דף הבית', 'url' => '/'],
    ['title' => 'מוצרים', 'url' => '/products'],
    ['title' => 'אודות', 'url' => '/about'],
    ['title' => 'צור קשר', 'url' => '/contact']
];

// קישורי מידע
$infoLinks = [
    ['title' => 'תנאי שירות', 'url' => '/terms'],
    ['title' => 'מדיניות פרטיות', 'url' => '/privacy'],
    ['title' => 'מדיניות החזרות', 'url' => '/returns'],
    ['title' => 'משלוחים', 'url' => '/shipping']
];
?>

<div class="py-12" style="background-color: <?= htmlspecialchars($settings['background_color']) ?>; color: <?= htmlspecialchars($settings['text_color']) ?>;">
    <div class="container-custom mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- תוכן הפוטר הראשי -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
            
            <!-- מידע על החנות -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold mb-4">
                    <?= htmlspecialchars($store['name'] ?? 'החנות שלנו') ?>
                </h3>
                <?php if (!empty($store['description'])): ?>
                    <p class="text-sm opacity-80 leading-relaxed">
                        <?= htmlspecialchars($store['description']) ?>
                    </p>
                <?php endif; ?>
                
                <!-- מידע קשר -->
                <div class="space-y-2 text-sm">
                    <?php if (!empty($store['phone'])): ?>
                        <div class="flex items-center">
                            <i class="ri-phone-line ml-2 opacity-60"></i>
                            <span><?= htmlspecialchars($store['phone']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($store['email'])): ?>
                        <div class="flex items-center">
                            <i class="ri-mail-line ml-2 opacity-60"></i>
                            <span><?= htmlspecialchars($store['email']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($store['address'])): ?>
                        <div class="flex items-center">
                            <i class="ri-map-pin-line ml-2 opacity-60"></i>
                            <span><?= htmlspecialchars($store['address']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- קישורים מהירים -->
            <?php if ($settings['show_links']): ?>
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold mb-4">קישורים מהירים</h3>
                    <ul class="space-y-2">
                        <?php foreach ($quickLinks as $link): ?>
                            <li>
                                <a href="<?= htmlspecialchars($link['url']) ?>" 
                                   class="text-sm opacity-80 hover:opacity-100 transition-opacity">
                                    <?= htmlspecialchars($link['title']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- מידע ומדיניות -->
            <?php if ($settings['show_links']): ?>
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold mb-4">מידע ומדיניות</h3>
                    <ul class="space-y-2">
                        <?php foreach ($infoLinks as $link): ?>
                            <li>
                                <a href="<?= htmlspecialchars($link['url']) ?>" 
                                   class="text-sm opacity-80 hover:opacity-100 transition-opacity">
                                    <?= htmlspecialchars($link['title']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- רשתות חברתיות -->
            <?php if ($settings['show_social']): ?>
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold mb-4">עקבו אחרינו</h3>
                    <div class="flex space-x-4 space-x-reverse">
                        <?php if (!empty($settings['social_facebook'])): ?>
                            <a href="<?= htmlspecialchars($settings['social_facebook']) ?>" 
                               target="_blank" 
                               class="text-2xl opacity-80 hover:opacity-100 transition-opacity"
                               title="Facebook">
                                <i class="ri-facebook-fill"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['social_instagram'])): ?>
                            <a href="<?= htmlspecialchars($settings['social_instagram']) ?>" 
                               target="_blank" 
                               class="text-2xl opacity-80 hover:opacity-100 transition-opacity"
                               title="Instagram">
                                <i class="ri-instagram-fill"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['social_twitter'])): ?>
                            <a href="<?= htmlspecialchars($settings['social_twitter']) ?>" 
                               target="_blank" 
                               class="text-2xl opacity-80 hover:opacity-100 transition-opacity"
                               title="Twitter">
                                <i class="ri-twitter-fill"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['social_youtube'])): ?>
                            <a href="<?= htmlspecialchars($settings['social_youtube']) ?>" 
                               target="_blank" 
                               class="text-2xl opacity-80 hover:opacity-100 transition-opacity"
                               title="YouTube">
                                <i class="ri-youtube-fill"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- הרשמה לניוזלטר -->
                    <div class="mt-6">
                        <h4 class="text-sm font-medium mb-2">הרשמה לניוזלטר</h4>
                        <form class="flex">
                            <input type="email" 
                                   placeholder="כתובת אימייל" 
                                   class="flex-1 px-3 py-2 text-sm bg-white bg-opacity-20 border border-white border-opacity-30 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 placeholder-white placeholder-opacity-60">
                            <button type="submit" 
                                    class="px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 border border-white border-opacity-30 rounded-l-lg transition-colors">
                                <i class="ri-send-plane-line"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- קו הפרדה -->
        <div class="border-t border-white border-opacity-20 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                
                <!-- זכויות יוצרים -->
                <div class="text-sm opacity-80">
                    <?= htmlspecialchars($settings['copyright']) ?>
                </div>
                
                <!-- לוגו תשלומים -->
                <div class="flex items-center space-x-4 space-x-reverse">
                    <span class="text-sm opacity-60">אמצעי תשלום:</span>
                    <div class="flex space-x-2 space-x-reverse">
                        <i class="ri-visa-line text-xl opacity-60"></i>
                        <i class="ri-mastercard-line text-xl opacity-60"></i>
                        <i class="ri-paypal-line text-xl opacity-60"></i>
                    </div>
                </div>
                
                <!-- חזרה לראש העמוד -->
                <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" 
                        class="flex items-center text-sm opacity-80 hover:opacity-100 transition-opacity">
                    <i class="ri-arrow-up-line ml-1"></i>
                    חזרה לראש העמוד
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .footer-section a {
        transition: opacity 0.3s ease;
    }
    
    .footer-section a:hover {
        opacity: 1;
    }
    
    @media (max-width: 768px) {
        .footer-section .grid {
            grid-template-columns: 1fr;
            gap: 2rem;
        }
    }
</style> 