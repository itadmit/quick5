<?php
/**
 * Featured Products Section
 * סקשן מוצרים מומלצים
 */

// הגדרות ברירת מחדל
$defaultSettings = [
    'title' => 'המוצרים המומלצים שלנו',
    'subtitle' => 'הבחירה שלנו עבורך',
    'products_count' => 6,
    'columns' => 3,
    'show_prices' => true,
    'show_add_to_cart' => true,
    'background_color' => '#ffffff',
    'text_color' => '#1f2937'
];

$settings = array_merge($defaultSettings, $sectionSettings ?? []);

// קבלת מוצרים מומלצים
$featuredProducts = [];
if ($store) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT p.*, pm.url as image 
            FROM products p
            LEFT JOIN product_media pm ON p.id = pm.product_id AND pm.is_primary = 1
            WHERE p.store_id = ? AND p.status = 'active'
            ORDER BY p.featured DESC, p.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$store['id'], (int)($settings['count'] ?? $settings['products_count'])]);
        $featuredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Featured products error: " . $e->getMessage());
    }
}

// הגדרת עמודות רספונסיביות
$columnClasses = [
    2 => 'grid-cols-1 md:grid-cols-2',
    3 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
    4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
    5 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5',
    6 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6'
];
$columnClass = $columnClasses[$settings['columns']] ?? $columnClasses[4];
?>

<?php if (!empty($featuredProducts)): ?>
<section id="<?= $sectionId ?>" class="featured-products-section py-16" 
         style="background-color: <?= htmlspecialchars($settings['background_color']) ?>; color: <?= htmlspecialchars($settings['text_color']) ?>;">
    
    <div class="container-custom mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- כותרת הסקשן -->
        <?php if (!empty($settings['title']) || !empty($settings['subtitle'])): ?>
            <div class="text-center mb-12">
                <?php if (!empty($settings['title'])): ?>
                    <h2 class="section-title text-3xl md:text-4xl font-bold mb-4">
                        <?= htmlspecialchars($settings['title']) ?>
                    </h2>
                <?php endif; ?>
                
                <?php if (!empty($settings['subtitle'])): ?>
                    <p class="text-lg opacity-80 max-w-2xl mx-auto">
                        <?= htmlspecialchars($settings['subtitle']) ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- רשת המוצרים -->
        <div class="products-grid grid <?= $columnClass ?> gap-6">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="product-card group bg-white rounded-lg shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-200">
                    
                    <!-- תמונת המוצר -->
                    <div class="aspect-w-1 aspect-h-1 bg-gray-200 overflow-hidden relative">
                        <?php if ($product['image']): ?>
                            <img src="<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300">
                        <?php else: ?>
                            <div class="w-full h-64 bg-gray-100 flex items-center justify-center">
                                <i class="ri-image-line text-4xl text-gray-400"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- תג מוצר מומלץ -->
                        <?php if ($product['featured']): ?>
                            <div class="absolute top-2 right-2 bg-primary text-white px-2 py-1 rounded-full text-xs font-semibold">
                                מומלץ
                            </div>
                        <?php endif; ?>
                        
                        <!-- תג הנחה -->
                        <?php if ($product['compare_price'] && $product['compare_price'] > ($product['price'] ?? 0)): ?>
                            <?php $discount = round((($product['compare_price'] - $product['price']) / $product['compare_price']) * 100); ?>
                            <div class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                                -<?= $discount ?>%
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- פרטי המוצר -->
                    <div class="p-4">
                        <h3 class="text-lg font-semibold mb-2 line-clamp-2">
                            <a href="/product/<?= htmlspecialchars($product['slug']) ?>" 
                               class="hover:text-primary transition-colors">
                                <?= htmlspecialchars($product['name']) ?>
                            </a>
                        </h3>
                        
                        <?php if ($product['short_description']): ?>
                            <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                                <?= htmlspecialchars($product['short_description']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="flex items-center justify-between">
                            <!-- מחיר -->
                            <?php if ($settings['show_prices']): ?>
                                <div class="flex items-center gap-2">
                                    <span class="text-xl font-bold text-primary">
                                        ₪<?= number_format($product['price'] ?? 0, 2) ?>
                                    </span>
                                    <?php if ($product['compare_price'] && $product['compare_price'] > ($product['price'] ?? 0)): ?>
                                        <span class="text-sm text-gray-500 line-through">
                                            ₪<?= number_format($product['compare_price'] ?? 0, 2) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- כפתור הוספה לסל -->
                            <?php if ($settings['show_add_to_cart']): ?>
                                <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition-colors flex items-center gap-2 add-to-cart-btn"
                                        data-product-id="<?= $product['id'] ?>"
                                        title="הוסף לסל">
                                    <i class="ri-shopping-cart-line"></i>
                                    <span class="hidden sm:inline">הוסף לסל</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- כפתור צפה בכל המוצרים -->
        <div class="text-center mt-12">
            <a href="/category/all" 
               class="inline-flex items-center px-8 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-secondary transition-colors">
                <i class="ri-arrow-left-line ml-2"></i>
                צפה בכל המוצרים
            </a>
        </div>
        
    </div>
</section>
<?php endif; ?>

<style>
    .product-card {
        transition: transform 0.3s ease;
    }
    
    .product-card:hover {
        transform: translateY(-4px);
    }
    
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .add-to-cart-btn {
        transition: all 0.3s ease;
    }
    
    .add-to-cart-btn:hover {
        transform: scale(1.05);
    }
    
    @media (max-width: 640px) {
        .product-card {
            margin-bottom: 1rem;
        }
    }
</style>

<script>
    // הוספת מוצר לסל
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            addToCart(productId);
        });
    });
    
    function addToCart(productId) {
        // כאן תהיה הלוגיקה של הוספה לסל
        console.log('Adding product to cart:', productId);
        
        // הצגת הודעת הצלחה
        showNotification('המוצר נוסף לסל בהצלחה!', 'success');
    }
    
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transition-all duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="${type === 'success' ? 'ri-check-circle-line' : 'ri-information-line'} ml-2"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
</script> 