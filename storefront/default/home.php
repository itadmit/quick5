<?php
// קבלת מידע החנות מהגלובלים
$store = $GLOBALS['CURRENT_STORE'] ?? null;

if (!$store) {
    header('HTTP/1.1 404 Not Found');
    exit('Store not found');
}

// הגדרת משתנים לדף
$currentPage = 'home';
$pageTitle = 'דף הבית';
$pageDescription = $store['description'] ?: 'חנות אונליין מתקדמת עם מוצרים איכותיים';

// קבלת מוצרים אחרונים
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT p.*, pm.url as image 
        FROM products p
        LEFT JOIN product_media pm ON p.id = pm.product_id AND pm.is_primary = 1
        WHERE p.store_id = ? AND p.status = 'active'
        ORDER BY p.created_at DESC 
        LIMIT 8
    ");
    $stmt->execute([$store['id']]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $products = [];
}

// Include header
include __DIR__ . '/header.php';
?>

    <!-- Hero Section -->
    <section class="bg-gradient-to-l from-blue-600 to-purple-700 text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                ברוכים הבאים ל<?= htmlspecialchars($store['name']) ?>
            </h1>
            <?php if ($store['description']): ?>
                <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto opacity-90">
                    <?= htmlspecialchars($store['description']) ?>
                </p>
            <?php endif; ?>
            <div class="space-y-4 sm:space-y-0 sm:space-x-4 sm:space-x-reverse sm:flex sm:justify-center">
                <a href="/category/all" class="inline-block bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                    עיין במוצרים
                </a>
                <?php if ($products): ?>
                    <a href="/product/<?= htmlspecialchars($products[0]['slug']) ?>" class="inline-block border border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                        המוצר החדש ביותר
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <?php if ($products): ?>
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">המוצרים החדשים שלנו</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">גלה את המוצרים האחרונים שהגיעו לחנות - איכות מעולה ומחירים משתלמים</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="aspect-w-1 aspect-h-1 w-full">
                            <?php if ($product['image']): ?>
                                <img src="<?= htmlspecialchars($product['image']) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                     class="w-full h-48 object-cover">
                            <?php else: ?>
                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                    <i class="ri-image-line text-4xl text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                <?= htmlspecialchars($product['name']) ?>
                            </h3>
                            
                            <?php if ($product['short_description']): ?>
                                <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                                    <?= htmlspecialchars($product['short_description']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-2xl font-bold text-primary">
                                                                            ₪<?= number_format($product['price'] ?? 0, 2) ?>
                                </span>
                                
                                <a href="/product/<?= htmlspecialchars($product['slug']) ?>" 
                                   class="btn-primary text-white px-4 py-2 rounded-lg hover:opacity-90 transition-opacity">
                                    צפה במוצר
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-12">
                <a href="/category/all" class="btn-primary text-white px-8 py-3 rounded-lg hover:opacity-90 transition-opacity">
                    צפה בכל המוצרים
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Store Info -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="ri-truck-line text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">משלוח מהיר</h3>
                    <p class="text-gray-600">משלוח חינם מעל ₪200 לכל הארץ</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-primary text-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="ri-shield-check-line text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">אחריות מלאה</h3>
                    <p class="text-gray-600">אחריות יצרן על כל המוצרים</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-primary text-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="ri-customer-service-line text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">שירות לקוחות</h3>
                    <p class="text-gray-600">זמינים עבורכם בכל שעות היום</p>
                </div>
            </div>
        </div>
    </section>

<?php
// Include footer
include __DIR__ . '/footer.php';
?> 