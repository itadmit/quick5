<?php
/**
 * Header Section - QuickShop Evening Theme
 * כותרת האתר עם ניווט, לוגו ועגלת קניות
 */

// קבלת נתוני החנות
$store = $GLOBALS['CURRENT_STORE'] ?? null;
$currentPage = $GLOBALS['PAGE_DATA']['template'] ?? 'home';

// קבלת קטגוריות לתפריט
$categories = [];
if ($store) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT c.*, COUNT(pc.product_id) as product_count
            FROM categories c
            LEFT JOIN product_categories pc ON c.id = pc.category_id
            LEFT JOIN products p ON pc.product_id = p.id AND p.status = 'active'
            WHERE c.store_id = ?
            GROUP BY c.id
            HAVING product_count > 0 OR c.id IS NOT NULL
            ORDER BY c.name ASC
            LIMIT 10
        ");
        $stmt->execute([$store['id']]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Header categories error: " . $e->getMessage());
    }
}

// פונקציה לבדיקת עמוד פעיל
if (!function_exists('isActivePage')) {
    function isActivePage($page, $current) {
        return $page === $current ? 'text-primary font-medium' : 'text-gray-700 hover:text-primary';
    }
}
?>

<div class="container-custom mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
        
        <!-- Logo Section -->
        <div class="flex items-center">
            <a href="/" class="flex items-center group">
                <?php if (!empty($store['logo'])): ?>
                    <img src="<?= htmlspecialchars($store['logo']) ?>" 
                         alt="<?= htmlspecialchars($store['name']) ?>" 
                         class="h-10 w-auto transition-transform group-hover:scale-105">
                <?php else: ?>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-primary to-secondary rounded-lg flex items-center justify-center ml-3">
                            <i class="ri-store-2-line text-white text-xl"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 group-hover:text-primary transition-colors">
                            <?= htmlspecialchars($store['name'] ?? 'QuickShop') ?>
                        </h1>
                    </div>
                <?php endif; ?>
            </a>
        </div>
        
        <!-- Desktop Navigation -->
        <nav class="hidden lg:flex items-center space-x-8 space-x-reverse" role="navigation" aria-label="תפריט ראשי">
            <a href="/" 
               class="flex items-center transition-colors font-medium <?= isActivePage('home', $currentPage) ?>">
                <i class="ri-home-line ml-2"></i>
                בית
            </a>
            
            <!-- Products Dropdown -->
            <div class="relative group">
                <button class="flex items-center transition-colors font-medium <?= isActivePage('category', $currentPage) ?> group-hover:text-primary">
                    <i class="ri-grid-line ml-2"></i>
                    מוצרים
                    <i class="ri-arrow-down-s-line mr-1 transition-transform group-hover:rotate-180"></i>
                </button>
                
                <?php if (!empty($categories)): ?>
                <div class="absolute top-full right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                    <div class="p-4">
                        <a href="/category/all" 
                           class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary rounded-lg transition-colors font-medium">
                            <i class="ri-apps-line ml-2"></i>
                            כל המוצרים
                        </a>
                        <hr class="my-2">
                        <?php foreach ($categories as $category): ?>
                            <a href="/category/<?= htmlspecialchars($category['slug']) ?>" 
                               class="block px-4 py-2 text-gray-600 hover:bg-gray-50 hover:text-primary rounded-lg transition-colors">
                                <?= htmlspecialchars($category['name']) ?>
                                <?php if ($category['product_count'] > 0): ?>
                                    <span class="text-xs text-gray-400 mr-1">(<?= $category['product_count'] ?>)</span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <a href="/about" 
               class="flex items-center transition-colors font-medium <?= isActivePage('about', $currentPage) ?>">
                <i class="ri-information-line ml-2"></i>
                אודות
            </a>
            
            <a href="/contact" 
               class="flex items-center transition-colors font-medium <?= isActivePage('contact', $currentPage) ?>">
                <i class="ri-phone-line ml-2"></i>
                צור קשר
            </a>
        </nav>
        
        <!-- Actions Section -->
        <div class="flex items-center space-x-3 space-x-reverse">
            
            <!-- Search Button -->
            <button class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors" 
                    id="search-toggle"
                    aria-label="פתח חיפוש">
                <i class="ri-search-line text-xl"></i>
            </button>
            
            <!-- Cart Button -->
            <a href="/cart" 
               class="relative p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors group" 
               id="cart-button"
               aria-label="עגלת קניות">
                <i class="ri-shopping-cart-line text-xl"></i>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium opacity-0 scale-0 transition-all duration-200" 
                      id="cart-count" 
                      data-cart-count="0">0</span>
            </a>
            
            <!-- User Account -->
            <div class="hidden md:block">
                <button class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors" 
                        id="user-menu-toggle"
                        aria-label="חשבון משתמש">
                    <i class="ri-user-line text-xl"></i>
                </button>
            </div>
            
            <!-- Mobile Menu Toggle -->
            <button class="lg:hidden p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors" 
                    id="mobile-menu-toggle"
                    aria-label="תפריט נייד">
                <i class="ri-menu-line text-xl"></i>
            </button>
        </div>
    </div>
    
    <!-- Search Bar (Hidden by default) -->
    <div id="search-bar" class="hidden border-t border-gray-200 py-4">
        <div class="relative max-w-md mx-auto">
            <input type="text" 
                   placeholder="חפש מוצרים..." 
                   class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                   id="search-input">
            <i class="ri-search-line absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        </div>
    </div>
</div>

<!-- Mobile Navigation Menu -->
<div id="mobile-menu" class="lg:hidden hidden bg-white border-t border-gray-200">
    <div class="px-4 py-2 space-y-1">
        <a href="/" 
           class="flex items-center py-3 px-3 text-gray-700 hover:text-primary hover:bg-gray-50 rounded-lg transition-colors font-medium">
            <i class="ri-home-line ml-3"></i>
            בית
        </a>
        
        <!-- Mobile Categories -->
        <div class="space-y-1">
            <a href="/category/all" 
               class="flex items-center py-3 px-3 text-gray-700 hover:text-primary hover:bg-gray-50 rounded-lg transition-colors font-medium">
                <i class="ri-grid-line ml-3"></i>
                כל המוצרים
            </a>
            
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <a href="/category/<?= htmlspecialchars($category['slug']) ?>" 
                       class="flex items-center py-2 px-6 text-gray-600 hover:text-primary hover:bg-gray-50 rounded-lg transition-colors">
                        <?= htmlspecialchars($category['name']) ?>
                        <?php if ($category['product_count'] > 0): ?>
                            <span class="text-xs text-gray-400 mr-2">(<?= $category['product_count'] ?>)</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <a href="/about" 
           class="flex items-center py-3 px-3 text-gray-700 hover:text-primary hover:bg-gray-50 rounded-lg transition-colors font-medium">
            <i class="ri-information-line ml-3"></i>
            אודות
        </a>
        
        <a href="/contact" 
           class="flex items-center py-3 px-3 text-gray-700 hover:text-primary hover:bg-gray-50 rounded-lg transition-colors font-medium">
            <i class="ri-phone-line ml-3"></i>
            צור קשר
        </a>
        
        <!-- Mobile User Actions -->
        <div class="border-t border-gray-200 pt-4 mt-4">
            <a href="/account" 
               class="flex items-center py-3 px-3 text-gray-700 hover:text-primary hover:bg-gray-50 rounded-lg transition-colors font-medium">
                <i class="ri-user-line ml-3"></i>
                החשבון שלי
            </a>
        </div>
    </div>
</div>

<!-- Header JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            
            // Toggle icon
            const icon = this.querySelector('i');
            if (mobileMenu.classList.contains('hidden')) {
                icon.className = 'ri-menu-line text-xl';
            } else {
                icon.className = 'ri-close-line text-xl';
            }
        });
    }
    
    // Search toggle
    const searchToggle = document.getElementById('search-toggle');
    const searchBar = document.getElementById('search-bar');
    const searchInput = document.getElementById('search-input');
    
    if (searchToggle && searchBar) {
        searchToggle.addEventListener('click', function() {
            searchBar.classList.toggle('hidden');
            if (!searchBar.classList.contains('hidden')) {
                searchInput.focus();
            }
        });
    }
    
    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    window.location.href = `/search?q=${encodeURIComponent(query)}`;
                }
            }
        });
    }
    
    // Cart count update
    function updateCartCount() {
        // This will be implemented with the cart system
        const cartCount = localStorage.getItem('cart_count') || 0;
        const cartCountElement = document.getElementById('cart-count');
        
        if (cartCountElement) {
            if (cartCount > 0) {
                cartCountElement.textContent = cartCount;
                cartCountElement.classList.remove('opacity-0', 'scale-0');
                cartCountElement.classList.add('opacity-100', 'scale-100');
            } else {
                cartCountElement.classList.add('opacity-0', 'scale-0');
                cartCountElement.classList.remove('opacity-100', 'scale-100');
            }
        }
    }
    
    // Initialize cart count
    updateCartCount();
    
    // Listen for cart updates
    window.addEventListener('cartUpdated', updateCartCount);
});
</script> 