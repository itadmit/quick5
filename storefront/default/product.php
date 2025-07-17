<?php
// קבלת מידע המוצר מהגלובלים
$product = $GLOBALS['CURRENT_PRODUCT'] ?? null;
$store = $GLOBALS['CURRENT_STORE'] ?? null;

if (!$product || !$store) {
    header('HTTP/1.1 404 Not Found');
    exit('Product not found');
}

// הגדרת משתנים לדף
$currentPage = 'product';
$pageTitle = $product['seo_title'] ?: $product['name'];
$pageDescription = $product['seo_description'] ?: $product['short_description'];

// קבלת תמונות המוצר
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT * FROM product_media 
        WHERE product_id = ? 
        ORDER BY is_primary DESC, sort_order ASC
    ");
    $stmt->execute([$product['id']]);
    $media = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $media = [];
}

// קבלת מוצרים קשורים
try {
    $stmt = $db->prepare("
        SELECT p.*, pm.url as image 
        FROM products p
        LEFT JOIN product_media pm ON p.id = pm.product_id AND pm.is_primary = 1
        WHERE p.store_id = ? AND p.status = 'active' AND p.id != ?
        ORDER BY RAND() 
        LIMIT 4
    ");
    $stmt->execute([$store['id'], $product['id']]);
    $relatedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $relatedProducts = [];
}

// CSS נוסף לגלריה ומוצר מתקדם
$additionalCSS = '
<style>
    .product-gallery img {
        transition: transform 0.3s ease;
    }
    .product-gallery img:hover {
        transform: scale(1.05);
    }
    .thumbnail {
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .thumbnail:hover {
        opacity: 0.8;
    }
    .thumbnail.active {
        border-color: var(--primary-color) !important;
    }
    .product-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        padding: 4px 8px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 9999px;
        z-index: 10;
    }
    .swiper-button-next, .swiper-button-prev {
        color: var(--primary-color) !important;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 50%;
        width: 40px !important;
        height: 40px !important;
        margin-top: -20px !important;
    }
    .swiper-pagination-bullet-active {
        background: var(--primary-color) !important;
    }
    .variant-selector {
        transition: all 0.2s ease;
    }
    .variant-selector:hover {
        transform: scale(1.05);
    }
    .variant-selector.selected {
        ring: 2px solid var(--primary-color);
        ring-offset: 2px;
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
    .product-zoom {
        cursor: zoom-in;
    }
    .quantity-input::-webkit-outer-spin-button,
    .quantity-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .quantity-input[type=number] {
        -moz-appearance: textfield;
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
';

// Include header
include __DIR__ . '/header.php';
?>

    <!-- Breadcrumbs -->
    <nav class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <ol class="flex items-center space-x-2 space-x-reverse text-sm text-gray-500">
                <li><a href="/" class="hover:text-primary">בית</a></li>
                <li><i class="ri-arrow-left-s-line"></i></li>
                <li><a href="/category/all" class="hover:text-primary">מוצרים</a></li>
                <li><i class="ri-arrow-left-s-line"></i></li>
                <li class="text-gray-900"><?= htmlspecialchars($product['name']) ?></li>
            </ol>
        </div>
    </nav>

    <!-- Main Product Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            <!-- Product Images -->
            <div class="space-y-4 product-gallery">
                <!-- Main Image Carousel -->
                <div class="relative">
                    <div class="swiper product-gallery-main">
                        <div class="swiper-wrapper">
                            <?php if (!empty($media)): ?>
                                <?php foreach ($media as $mediaItem): ?>
                                    <div class="swiper-slide">
                                        <div class="relative bg-white rounded-lg overflow-hidden">
                                            <img src="<?= htmlspecialchars($mediaItem['url']) ?>" 
                                                 alt="<?= htmlspecialchars($mediaItem['alt_text'] ?: $product['name']) ?>"
                                                 class="w-full h-96 lg:h-[600px] object-cover product-zoom">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="swiper-slide">
                                    <div class="bg-gray-200 rounded-lg h-96 lg:h-[600px] flex items-center justify-center">
                                        <i class="ri-image-line text-6xl text-gray-400"></i>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="swiper-pagination"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>
                </div>
                
                <!-- Thumbnail Gallery -->
                <?php if (!empty($media) && count($media) > 1): ?>
                <div class="swiper product-gallery-thumbs">
                    <div class="swiper-wrapper">
                        <?php foreach ($media as $mediaItem): ?>
                            <div class="swiper-slide">
                                <img src="<?= htmlspecialchars($mediaItem['url']) ?>" 
                                     alt="<?= htmlspecialchars($mediaItem['alt_text'] ?: $product['name']) ?>"
                                     class="w-full h-20 object-cover rounded cursor-pointer border-2 border-transparent hover:border-primary transition-colors thumbnail">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="space-y-6">
                <!-- Product Title & Price -->
                <div class="space-y-4">
                    <div class="flex items-start justify-between">
                        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">
                            <?= htmlspecialchars($product['name']) ?>
                        </h1>
                        <button class="p-2 text-gray-400 hover:text-red-500 transition-colors" 
                                data-add-to-wishlist
                                data-product-id="<?= $product['id'] ?>">
                            <i class="ri-heart-line text-xl"></i>
                        </button>
                    </div>
                    
                    <!-- SKU & Vendor -->
                    <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                        <?php if ($product['sku']): ?>
                            <span>קוד מוצר: <span class="font-medium"><?= htmlspecialchars($product['sku']) ?></span></span>
                        <?php endif; ?>
                        <?php if ($product['vendor']): ?>
                            <span>יצרן: <span class="font-medium"><?= htmlspecialchars($product['vendor']) ?></span></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Price -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-4">
                            <span class="text-3xl font-bold text-primary" id="current-price">
                                                                        ₪<?= number_format($product['price'] ?? 0, 2) ?>
                            </span>
                            <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                                <span class="text-xl text-gray-500 line-through" id="compare-price">
                                    ₪<?= number_format($product['compare_price'] ?? 0, 2) ?>
                                </span>
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm font-medium">
                                    חסכון <?= round((($product['compare_price'] - $product['price']) / $product['compare_price']) * 100) ?>%
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm text-gray-600">כולל מע"מ</p>
                    </div>
                    
                    <!-- Short Description -->
                    <?php if ($product['short_description']): ?>
                        <p class="text-gray-700 leading-relaxed">
                            <?= nl2br(htmlspecialchars($product['short_description'])) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Stock Status & Quantity -->
                <div class="space-y-4">
                    <!-- Stock Status -->
                    <div class="flex items-center gap-2 text-sm">
                        <?php if ($product['track_inventory']): ?>
                            <?php if ($product['inventory_quantity'] > 0): ?>
                                <i class="ri-checkbox-circle-fill text-green-500"></i>
                                <span class="text-green-700">במלאי (<?= $product['inventory_quantity'] ?> יחידות)</span>
                            <?php else: ?>
                                <i class="ri-close-circle-fill text-red-500"></i>
                                <span class="text-red-700">אזל מהמלאי</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <i class="ri-checkbox-circle-fill text-green-500"></i>
                            <span class="text-green-700">זמין</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quantity Selector -->
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-medium text-gray-900">כמות:</label>
                        <div class="flex items-center border border-gray-300 rounded-md">
                            <button type="button" 
                                    class="p-2 hover:bg-gray-100 transition-colors"
                                    onclick="updateQuantity(-1)">
                                <i class="ri-subtract-line"></i>
                            </button>
                            <input type="number" 
                                   id="quantity" 
                                   value="1" 
                                   min="1" 
                                   class="quantity-input w-16 text-center border-0 focus:ring-0 focus:outline-none">
                            <button type="button" 
                                    class="p-2 hover:bg-gray-100 transition-colors"
                                    onclick="updateQuantity(1)">
                                <i class="ri-add-line"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Add to Cart Button -->
                    <div class="flex gap-3">
                        <button type="button" 
                                id="add-to-cart-btn"
                                class="flex-1 btn-primary text-white px-6 py-3 rounded-lg font-medium hover:opacity-90 transition-opacity flex items-center justify-center gap-2"
                                onclick="addToCart(<?= $product['id'] ?>)">
                            <i class="ri-shopping-cart-line"></i>
                            הוסף לעגלה
                        </button>
                        
                        <button type="button" 
                                class="p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                data-add-to-wishlist
                                data-product-id="<?= $product['id'] ?>">
                            <i class="ri-heart-line text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Shipping & Returns Info -->
                <div class="border-t pt-6 space-y-3">
                    <div class="flex items-center gap-3 text-sm text-gray-600">
                        <i class="ri-truck-line text-primary"></i>
                        <span>משלוח חינם מעל ₪199 | משלוח רגיל ₪25</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-600">
                        <i class="ri-time-line text-primary"></i>
                        <span>זמן אספקה: 2-4 ימי עסקים</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-600">
                        <i class="ri-arrow-go-back-line text-primary"></i>
                        <span>החזרה עד 14 ימים | החזרת כספים מלאה</span>
                    </div>
                </div>
                
                <!-- Tags -->
                <?php if ($product['tags']): ?>
                    <div class="border-t pt-6">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">תגיות:</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach (explode(',', $product['tags']) as $tag): ?>
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">
                                    <?= htmlspecialchars(trim($tag)) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Product Details Tabs -->
        <div class="mt-16">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 space-x-reverse" role="tablist">
                    <button class="tab-button py-4 px-1 border-b-2 border-primary text-primary font-medium text-sm active"
                            data-tab="description">
                        תיאור המוצר
                    </button>
                    <button class="tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm"
                            data-tab="specifications">
                        מפרט טכני
                    </button>
                    <button class="tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm"
                            data-tab="shipping">
                        משלוחים והחזרות
                    </button>
                </nav>
            </div>
            
            <div class="mt-8">
                <!-- Description Tab -->
                <div class="tab-content active" id="description-tab">
                    <div class="prose max-w-none">
                        <?php if ($product['description']): ?>
                            <div class="text-gray-700 leading-relaxed">
                                <?= nl2br(htmlspecialchars($product['description'])) ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500">אין תיאור זמין למוצר זה.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Specifications Tab -->
                <div class="tab-content" id="specifications-tab">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php if ($product['sku']): ?>
                            <div class="border-b border-gray-200 pb-4">
                                <dt class="font-medium text-gray-900">קוד מוצר:</dt>
                                <dd class="text-gray-700 mt-1"><?= htmlspecialchars($product['sku']) ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if ($product['vendor']): ?>
                            <div class="border-b border-gray-200 pb-4">
                                <dt class="font-medium text-gray-900">יצרן:</dt>
                                <dd class="text-gray-700 mt-1"><?= htmlspecialchars($product['vendor']) ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if ($product['product_type']): ?>
                            <div class="border-b border-gray-200 pb-4">
                                <dt class="font-medium text-gray-900">סוג מוצר:</dt>
                                <dd class="text-gray-700 mt-1"><?= htmlspecialchars($product['product_type']) ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if ($product['weight']): ?>
                            <div class="border-b border-gray-200 pb-4">
                                <dt class="font-medium text-gray-900">משקל:</dt>
                                <dd class="text-gray-700 mt-1"><?= htmlspecialchars($product['weight']) ?> גרם</dd>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Shipping Tab -->
                <div class="tab-content" id="shipping-tab">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="font-semibold text-lg mb-4">משלוחים</h3>
                            <ul class="space-y-2 text-gray-700">
                                <li class="flex items-center gap-2">
                                    <i class="ri-check-line text-green-500"></i>
                                    משלוח חינם מעל ₪199
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="ri-check-line text-green-500"></i>
                                    משלוח רגיל ₪25
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="ri-check-line text-green-500"></i>
                                    זמן אספקה: 2-4 ימי עסקים
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="ri-check-line text-green-500"></i>
                                    איסוף עצמי זמין
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg mb-4">החזרות</h3>
                            <ul class="space-y-2 text-gray-700">
                                <li class="flex items-center gap-2">
                                    <i class="ri-check-line text-green-500"></i>
                                    החזרה עד 14 ימים
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="ri-check-line text-green-500"></i>
                                    החזרת כספים מלאה
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="ri-check-line text-green-500"></i>
                                    המוצר חייב להיות במצב מקורי
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="ri-check-line text-green-500"></i>
                                    תהליך החזרה פשוט ומהיר
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <div class="mt-16">
            <h2 class="text-2xl font-bold mb-8">מוצרים קשורים</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <div class="group">
                        <div class="relative bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <a href="/product/<?= htmlspecialchars($relatedProduct['slug']) ?>">
                                <?php if ($relatedProduct['image']): ?>
                                    <img src="<?= htmlspecialchars($relatedProduct['image']) ?>" 
                                         alt="<?= htmlspecialchars($relatedProduct['name']) ?>"
                                         class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                                <?php else: ?>
                                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                        <i class="ri-image-line text-4xl text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="p-4">
                                <h3 class="font-medium text-sm mb-2 line-clamp-2">
                                    <a href="/product/<?= htmlspecialchars($relatedProduct['slug']) ?>" 
                                       class="text-gray-900 hover:text-primary">
                                        <?= htmlspecialchars($relatedProduct['name']) ?>
                                    </a>
                                </h3>
                                <p class="text-primary font-semibold">₪<?= number_format($relatedProduct['price'] ?? 0, 2) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

<?php
$additionalJS = "
<script src=\"https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js\"></script>
<script>
// Product data for JavaScript
const productData = {
    id: " . $product['id'] . ",
    name: " . json_encode($product['name'], JSON_UNESCAPED_UNICODE) . ",
    price: " . $product['price'] . ",
    comparePrice: " . ($product['compare_price'] ?: 'null') . ",
    trackInventory: " . ($product['track_inventory'] ? 'true' : 'false') . ",
    inventoryQuantity: " . $product['inventory_quantity'] . "
};

// Initialize Swiper
let galleryThumbs = null;
let galleryMain = null;

// Check if thumbnail gallery exists
const thumbnailEl = document.querySelector('.product-gallery-thumbs');
if (thumbnailEl) {
    galleryThumbs = new Swiper('.product-gallery-thumbs', {
        spaceBetween: 10,
        slidesPerView: 4,
        freeMode: true,
        watchSlidesProgress: true,
    });
}

// Initialize main gallery
const mainGalleryEl = document.querySelector('.product-gallery-main');
if (mainGalleryEl) {
    galleryMain = new Swiper('.product-gallery-main', {
        spaceBetween: 10,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        thumbs: galleryThumbs ? {
            swiper: galleryThumbs,
        } : undefined,
    });
}

// Quantity functions
function updateQuantity(change) {
    const quantityInput = document.getElementById('quantity');
    let currentQuantity = parseInt(quantityInput.value) || 1;
    let newQuantity = currentQuantity + change;
    
    if (newQuantity < 1) newQuantity = 1;
    
    quantityInput.value = newQuantity;
}

// Enhanced add to cart function
function addToCart(productId) {
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    
    // Use the global cartManager
    if (typeof window.cartManager !== 'undefined') {
        window.cartManager.addToCart(productId, null, quantity)
            .then(response => {
                if (response.success) {
                    window.cartManager.showMessage('המוצר נוסף לעגלה בהצלחה!', 'success');
                } else {
                    window.cartManager.showMessage('שגיאה בהוספת המוצר לעגלה', 'error');
                }
            })
            .catch(error => {
                console.error('Error adding to cart:', error);
                window.cartManager.showMessage('שגיאה בהוספת המוצר לעגלה', 'error');
            });
    } else {
        // Fallback if cartManager is not available yet
        setTimeout(() => {
            if (typeof window.cartManager !== 'undefined') {
                addToCart(productId);
            } else {
                alert('מערכת העגלה לא זמינה כרגע');
            }
        }, 100);
    }
}

// Tab functionality
document.querySelectorAll('.tab-button').forEach(button => {
    button.addEventListener('click', function() {
        const tabId = this.dataset.tab;
        
        // Remove active class from all tabs and contents
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active', 'border-primary', 'text-primary');
            btn.classList.add('border-transparent', 'text-gray-500');
        });
        
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        
        // Add active class to current tab
        this.classList.add('active', 'border-primary', 'text-primary');
        this.classList.remove('border-transparent', 'text-gray-500');
        
        // Show current content
        document.getElementById(tabId + '-tab').classList.add('active');
    });
});

// Initialize quantity in add to cart button
document.addEventListener('DOMContentLoaded', function() {
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    if (addToCartBtn) {
        addToCartBtn.dataset.quantity = '1';
    }
});
</script>
";

// Include footer
include __DIR__ . '/footer.php';
?> 