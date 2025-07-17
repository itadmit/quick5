<?php
/**
 * Product Grid Block - QuickShop Evening Theme
 * בלוק הצגת מוצרים ברשת
 */

function renderProductGridBlock($settings, $store) {
    // הגדרות ברירת מחדל
    $defaults = [
        'title' => 'מוצרים מומלצים',
        'subtitle' => 'הבחירה שלנו עבורך',
        'products_count' => 8,
        'columns' => 4,
        'show_price' => true,
        'show_add_to_cart' => true,
        'show_compare_price' => true,
        'filter_type' => 'featured', // featured, latest, category, manual
        'category_id' => null,
        'background_color' => '#FFFFFF',
        'text_color' => 'text-gray-900',
        'button_style' => 'primary',
        'show_view_all' => true,
        'view_all_text' => 'צפה בכל המוצרים',
        'view_all_link' => '/category/all',
        'animation' => 'fade-in'
    ];
    
    $settings = array_merge($defaults, $settings);
    
    // קבלת מוצרים מהדטאבייס
    $products = [];
    if ($store) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // בניית שאילתה לפי סוג הפילטר
            $whereClause = "p.store_id = ? AND p.status = 'active'";
            $orderBy = "p.created_at DESC";
            $params = [$store['id']];
            
            switch ($settings['filter_type']) {
                case 'featured':
                    $orderBy = "p.featured DESC, p.created_at DESC";
                    break;
                case 'latest':
                    $orderBy = "p.created_at DESC";
                    break;
                case 'category':
                    if ($settings['category_id']) {
                        $whereClause .= " AND EXISTS (SELECT 1 FROM product_categories pc WHERE pc.product_id = p.id AND pc.category_id = ?)";
                        $params[] = $settings['category_id'];
                    }
                    break;
                case 'price_low':
                    $orderBy = "p.price ASC";
                    break;
                case 'price_high':
                    $orderBy = "p.price DESC";
                    break;
            }
            
            $query = "
                SELECT p.*, pm.url as image, pm.thumbnail_url
                FROM products p
                LEFT JOIN product_media pm ON p.id = pm.product_id AND pm.is_primary = 1
                WHERE {$whereClause}
                ORDER BY {$orderBy}
                LIMIT ?
            ";
            
            $params[] = $settings['products_count'];
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Product Grid Block error: " . $e->getMessage());
        }
    }
    
    // הגדרת מחלקות CSS לעמודות
    $gridCols = match((int)$settings['columns']) {
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 md:grid-cols-2',
        3 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
        5 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5',
        6 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6',
        default => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4'
    };
    
    // הגדרת סגנון כפתור
    $buttonClasses = match($settings['button_style']) {
        'primary' => 'bg-primary text-white hover:bg-secondary',
        'secondary' => 'bg-gray-800 text-white hover:bg-gray-700',
        'outline' => 'border-2 border-primary text-primary hover:bg-primary hover:text-white',
        'minimal' => 'text-primary hover:text-secondary underline',
        default => 'bg-primary text-white hover:bg-secondary'
    };
    
    ?>
    
    <section class="py-16" 
             style="background-color: <?= $settings['background_color'] ?>"
             data-block="product-grid"
             data-animation="<?= $settings['animation'] ?>">
        <div class="container-custom mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <?php if (!empty($settings['title']) || !empty($settings['subtitle'])): ?>
            <div class="text-center mb-12">
                <?php if (!empty($settings['title'])): ?>
                    <h2 class="text-3xl md:text-4xl font-bold <?= $settings['text_color'] ?> mb-4">
                        <?= htmlspecialchars($settings['title']) ?>
                    </h2>
                <?php endif; ?>
                
                <?php if (!empty($settings['subtitle'])): ?>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        <?= htmlspecialchars($settings['subtitle']) ?>
                    </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Products Grid -->
            <?php if (!empty($products)): ?>
            <div class="grid <?= $gridCols ?> gap-6 mb-12">
                <?php foreach ($products as $product): ?>
                    <div class="group bg-white rounded-lg shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-200 transform hover:scale-105">
                        
                        <!-- Product Image -->
                        <div class="aspect-w-1 aspect-h-1 bg-gray-200 overflow-hidden relative">
                            <a href="/product/<?= htmlspecialchars($product['slug']) ?>">
                                <?php if ($product['image']): ?>
                                    <img src="<?= htmlspecialchars($product['thumbnail_url'] ?: $product['image']) ?>" 
                                         alt="<?= htmlspecialchars($product['name']) ?>"
                                         class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-300"
                                         loading="lazy">
                                <?php else: ?>
                                    <div class="w-full h-64 bg-gray-100 flex items-center justify-center">
                                        <i class="ri-image-line text-4xl text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                            </a>
                            
                            <!-- Product Badges -->
                            <?php if ($product['featured']): ?>
                                <div class="absolute top-2 right-2 bg-accent text-white px-2 py-1 rounded-full text-xs font-medium">
                                    מומלץ
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($settings['show_compare_price'] && $product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                                <div class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                                    <?= round((($product['compare_price'] - $product['price']) / $product['compare_price']) * 100) ?>% הנחה
                                </div>
                            <?php endif; ?>
                            
                            <!-- Quick Actions -->
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                                <div class="flex gap-2">
                                    <button class="bg-white text-gray-900 p-2 rounded-full hover:bg-gray-100 transition-colors"
                                            onclick="quickView(<?= $product['id'] ?>)"
                                            title="צפייה מהירה">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    <button class="bg-white text-gray-900 p-2 rounded-full hover:bg-gray-100 transition-colors"
                                            onclick="addToWishlist(<?= $product['id'] ?>)"
                                            title="הוסף לרשימת משאלות">
                                        <i class="ri-heart-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
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
                            
                            <!-- Price and Actions -->
                            <div class="flex items-center justify-between">
                                <?php if ($settings['show_price']): ?>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xl font-bold text-primary">
                                            ₪<?= number_format($product['price'] ?? 0, 2) ?>
                                        </span>
                                        <?php if ($settings['show_compare_price'] && $product['compare_price'] && $product['compare_price'] > ($product['price'] ?? 0)): ?>
                                            <span class="text-sm text-gray-500 line-through">
                                                ₪<?= number_format($product['compare_price'] ?? 0, 2) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($settings['show_add_to_cart']): ?>
                                    <button class="<?= $buttonClasses ?> px-4 py-2 rounded-lg transition-all duration-300 flex items-center gap-2 font-medium"
                                            onclick="addToCart(<?= $product['id'] ?>)">
                                        <i class="ri-shopping-cart-line"></i>
                                        <span class="hidden sm:inline">הוסף לסל</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Stock Status -->
                            <?php if ($product['track_inventory']): ?>
                                <div class="mt-2 text-xs">
                                    <?php if ($product['inventory_quantity'] > 0): ?>
                                        <span class="text-green-600 flex items-center">
                                            <i class="ri-checkbox-circle-line ml-1"></i>
                                            במלאי (<?= $product['inventory_quantity'] ?>)
                                        </span>
                                    <?php elseif ($product['allow_backorders']): ?>
                                        <span class="text-yellow-600 flex items-center">
                                            <i class="ri-time-line ml-1"></i>
                                            זמין להזמנה
                                        </span>
                                    <?php else: ?>
                                        <span class="text-red-600 flex items-center">
                                            <i class="ri-close-circle-line ml-1"></i>
                                            אזל המלאי
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- View All Button -->
            <?php if ($settings['show_view_all']): ?>
                <div class="text-center">
                    <a href="<?= htmlspecialchars($settings['view_all_link']) ?>" 
                       class="inline-flex items-center px-8 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-secondary transition-all duration-300 transform hover:scale-105">
                        <i class="ri-arrow-left-line ml-2"></i>
                        <?= htmlspecialchars($settings['view_all_text']) ?>
                    </a>
                </div>
            <?php endif; ?>
            
            <?php else: ?>
                <!-- No Products Message -->
                <div class="text-center py-12">
                    <i class="ri-shopping-bag-line text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">אין מוצרים להצגה</h3>
                    <p class="text-gray-500">נסה לשנות את הפילטרים או חזור מאוחר יותר</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php
}

// קבלת הגדרות ברירת מחדל לבלוק
function getProductGridBlockDefaults() {
    return [
        'title' => 'מוצרים מומלצים',
        'subtitle' => 'הבחירה שלנו עבורך',
        'products_count' => 8,
        'columns' => 4,
        'show_price' => true,
        'show_add_to_cart' => true,
        'show_compare_price' => true,
        'filter_type' => 'featured',
        'category_id' => null,
        'background_color' => '#FFFFFF',
        'text_color' => 'text-gray-900',
        'button_style' => 'primary',
        'show_view_all' => true,
        'view_all_text' => 'צפה בכל המוצרים',
        'view_all_link' => '/category/all',
        'animation' => 'fade-in'
    ];
}

// קבלת הגדרות השדות לקסטומיזר
function getProductGridBlockFields() {
    return [
        'title' => [
            'type' => 'text',
            'label' => 'כותרת'
        ],
        'subtitle' => [
            'type' => 'textarea',
            'label' => 'כותרת משנה',
            'rows' => 2
        ],
        'products_count' => [
            'type' => 'number',
            'label' => 'מספר מוצרים',
            'min' => 1,
            'max' => 50
        ],
        'columns' => [
            'type' => 'select',
            'label' => 'מספר עמודות',
            'options' => [
                2 => '2 עמודות',
                3 => '3 עמודות',
                4 => '4 עמודות',
                5 => '5 עמודות',
                6 => '6 עמודות'
            ]
        ],
        'filter_type' => [
            'type' => 'select',
            'label' => 'סוג פילטר',
            'options' => [
                'featured' => 'מוצרים מומלצים',
                'latest' => 'מוצרים חדשים',
                'category' => 'לפי קטגוריה',
                'price_low' => 'מחיר נמוך לגבוה',
                'price_high' => 'מחיר גבוה לנמוך'
            ]
        ],
        'show_price' => [
            'type' => 'checkbox',
            'label' => 'הצג מחיר'
        ],
        'show_add_to_cart' => [
            'type' => 'checkbox',
            'label' => 'הצג כפתור הוספה לסל'
        ],
        'show_compare_price' => [
            'type' => 'checkbox',
            'label' => 'הצג מחיר לפני הנחה'
        ],
        'button_style' => [
            'type' => 'select',
            'label' => 'סגנון כפתור',
            'options' => [
                'primary' => 'ראשי',
                'secondary' => 'משני',
                'outline' => 'מסגרת',
                'minimal' => 'מינימלי'
            ]
        ],
        'show_view_all' => [
            'type' => 'checkbox',
            'label' => 'הצג כפתור צפה בכל'
        ],
        'view_all_text' => [
            'type' => 'text',
            'label' => 'טקסט כפתור צפה בכל'
        ],
        'view_all_link' => [
            'type' => 'text',
            'label' => 'קישור צפה בכל'
        ],
        'background_color' => [
            'type' => 'color',
            'label' => 'צבע רקע'
        ]
    ];
}
?> 