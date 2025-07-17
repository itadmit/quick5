<?php
// קבלת מידע החנות מהגלובלים
$store = $GLOBALS['CURRENT_STORE'] ?? null;

if (!$store) {
    return;
}
?>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-2xl font-bold mb-4"><?= htmlspecialchars($store['name']) ?></h3>
                    <?php if ($store['description']): ?>
                        <p class="text-gray-300 mb-4"><?= htmlspecialchars($store['description']) ?></p>
                    <?php endif; ?>
                    <div class="flex space-x-4 space-x-reverse">
                        <?php if (!empty($store['social_facebook'])): ?>
                            <a href="<?= htmlspecialchars($store['social_facebook']) ?>" target="_blank" class="text-gray-300 hover:text-white transition-colors">
                                <i class="ri-facebook-line text-xl"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($store['social_instagram'])): ?>
                            <a href="<?= htmlspecialchars($store['social_instagram']) ?>" target="_blank" class="text-gray-300 hover:text-white transition-colors">
                                <i class="ri-instagram-line text-xl"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($store['social_twitter'])): ?>
                            <a href="<?= htmlspecialchars($store['social_twitter']) ?>" target="_blank" class="text-gray-300 hover:text-white transition-colors">
                                <i class="ri-twitter-line text-xl"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($store['social_youtube'])): ?>
                            <a href="<?= htmlspecialchars($store['social_youtube']) ?>" target="_blank" class="text-gray-300 hover:text-white transition-colors">
                                <i class="ri-youtube-line text-xl"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($store['social_tiktok'])): ?>
                            <a href="<?= htmlspecialchars($store['social_tiktok']) ?>" target="_blank" class="text-gray-300 hover:text-white transition-colors">
                                <i class="ri-tiktok-line text-xl"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">קישורים מהירים</h4>
                    <ul class="space-y-2">
                        <li><a href="/about" class="text-gray-300 hover:text-white transition-colors">אודות</a></li>
                        <li><a href="/contact" class="text-gray-300 hover:text-white transition-colors">צור קשר</a></li>
                        <li><a href="/terms" class="text-gray-300 hover:text-white transition-colors">תנאי שימוש</a></li>
                        <li><a href="/privacy" class="text-gray-300 hover:text-white transition-colors">מדיניות פרטיות</a></li>
                        <li><a href="/shipping" class="text-gray-300 hover:text-white transition-colors">משלוחים והחזרות</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">יצירת קשר</h4>
                    <ul class="space-y-2 text-gray-300">
                        <?php if (!empty($store['phone'])): ?>
                            <li>
                                <i class="ri-phone-line ml-2"></i>
                                <a href="tel:<?= htmlspecialchars($store['phone']) ?>" class="hover:text-white transition-colors">
                                    <?= htmlspecialchars($store['phone']) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (!empty($store['email'])): ?>
                            <li>
                                <i class="ri-mail-line ml-2"></i>
                                <a href="mailto:<?= htmlspecialchars($store['email']) ?>" class="hover:text-white transition-colors">
                                    <?= htmlspecialchars($store['email']) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (!empty($store['address'])): ?>
                            <li>
                                <i class="ri-map-pin-line ml-2"></i>
                                <?= htmlspecialchars($store['address']) ?>
                            </li>
                        <?php endif; ?>
                        <?php if (!empty($store['whatsapp'])): ?>
                            <li>
                                <i class="ri-whatsapp-line ml-2"></i>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $store['whatsapp']) ?>" target="_blank" class="hover:text-white transition-colors">
                                    WhatsApp
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-300">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($store['name']) ?>. כל הזכויות שמורות.</p>
                <p class="mt-2 text-sm">
                    <span class="text-gray-500">Powered by</span> 
                    <a href="https://quick-shop.co.il" class="text-primary hover:underline" target="_blank">QuickShop</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Cart Manager -->
    <script src="/assets/js/cart.js?v=<?= time() ?>"></script>
    <script>
        // Initialize Cart Manager only if not already initialized
        if (typeof window.cartManager === 'undefined') {
            window.cartManager = new CartManager();
        }
    </script>

    <?php if (isset($additionalJS)): ?>
        <?= $additionalJS ?>
    <?php endif; ?>

</body>
</html> 