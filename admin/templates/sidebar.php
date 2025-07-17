<!-- Sidebar -->
<div class="fixed inset-y-0 right-0 z-50 w-64 bg-white shadow-xl flex flex-col" id="sidebar">
    
    <!-- Header -->
    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200">
        <div class="flex items-center">
            <h1 class="text-xl font-bold text-gray-800">
                <i class="ri-shopping-bag-3-line ml-2"></i>
                QuickShop5
            </h1>
        </div>
        <button class="lg:hidden" onclick="toggleSidebar()">
            <i class="ri-close-line text-xl text-gray-600"></i>
        </button>
    </div>

    <!-- User Info -->
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-white" style="background: #1a2a42;">
                <?php echo strtoupper(substr($currentUser['first_name'] ?? 'U', 0, 1)); ?>
            </div>
            <div class="mr-3">
                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')); ?></p>
                <p class="text-xs text-gray-500"><?php echo htmlspecialchars(isset($store['name']) ? $store['name'] : 'החנות שלי'); ?></p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="mt-4 px-2 flex-1 overflow-y-auto" id="sidebar-nav">
        <!-- Scroll progress indicator -->
        <div class="fixed left-0 top-0 w-1 bg-gray-200 h-full z-50" id="scroll-progress-bg" style="display: none;">
            <div class="bg-blue-500 w-full transition-all duration-100" id="scroll-progress" style="height: 0%;"></div>
        </div>
        
        <!-- Scroll buttons -->
        <div class="fixed bottom-20 left-4 z-60 flex flex-col gap-2" id="scroll-buttons" style="display: none;">
            <button onclick="scrollToTop()" class="bg-green-500 hover:bg-green-600 text-white p-3 rounded-full shadow-lg transition-all duration-200 opacity-90 hover:opacity-100 hover:shadow-xl" id="scroll-to-top" title="גלול למעלה">
                <i class="ri-arrow-up-line text-lg"></i>
            </button>
            <button onclick="scrollToBottom()" class="bg-blue-500 hover:bg-blue-600 text-white p-3 rounded-full shadow-lg transition-all duration-200 opacity-90 hover:opacity-100 hover:shadow-xl" id="scroll-to-bottom" title="גלול למטה">
                <i class="ri-arrow-down-line text-lg"></i>
            </button>
        </div>
        <ul class="space-y-1">
            
            <!-- דשבורד -->
            <li>
                <a href="/admin/" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg text-gray-800" style="background: #dcf3f3;">
                    <i class="ri-dashboard-line ml-3 text-gray-800"></i>
                    דשבורד
                </a>
            </li>

            <!-- Divider -->
            <li class="py-3">
                <div class="border-t border-gray-200"></div>
            </li>

            <!-- מוצרים -->
            <li class="pt-2">
                <p class="px-4 text-xs font-semibold uppercase tracking-wider text-gray-500">ניהול מוצרים</p>
            </li>
            <li class="pt-1">
                <a href="/admin/products/" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-gray-700 hover:bg-gray-50">
                    <i class="ri-shopping-bag-line ml-3 text-gray-400"></i>
                    מוצרים
                </a>
            </li>
            <li>
                <a href="/admin/products/bulk-edit.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-gray-700 hover:bg-gray-50">
                    <i class="ri-edit-2-line ml-3 text-gray-400"></i>
                    עריכה קבוצתית
                </a>
            </li>
            <li>
                <a href="/admin/products/new.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-gray-700 hover:bg-gray-50">
                    <i class="ri-add-circle-line ml-3 text-gray-400"></i>
                    מוצר חדש
                </a>
            </li>
            <li>
                <a href="/admin/categories/" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-gray-700 hover:bg-gray-50">
                    <i class="ri-folder-line ml-3 text-gray-400"></i>
                    קטגוריות
                </a>
            </li>
            <li>
                <a href="/admin/import/" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-gray-700 hover:bg-gray-50">
                    <i class="ri-upload-cloud-2-line ml-3 text-gray-400"></i>
                    ייבוא מוצרים
                </a>
            </li>

            <!-- Divider -->
            <li class="py-3">
                <div class="border-t border-gray-200"></div>
            </li>

            <!-- הזמנות -->
            <li class="pt-2">
                <p class="px-4 text-xs font-semibold uppercase tracking-wider text-gray-500">מכירות</p>
            </li>
            <li class="pt-1">
                <a href="/admin/orders/" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-gray-700 hover:bg-gray-50">
                    <i class="ri-shopping-cart-line ml-3 text-gray-400"></i>
                    הזמנות
                    <span class="mr-auto text-white text-xs rounded-full px-2 py-1" style="background: #ff6b6b;">177</span>
                </a>
            </li>
            <li>
                <a href="/admin/abandoned-carts/" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-gray-700 hover:bg-gray-50">
                    <i class="ri-shopping-cart-2-line ml-3 text-gray-400"></i>
                    שחזור עגלות
                </a>
            </li>
            <li>
                <a href="/admin/customers/" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-gray-700 hover:bg-gray-50">
                    <i class="ri-user-line ml-3 text-gray-400"></i>
                    לקוחות
                </a>
            </li>
            <li>
                <a href="/admin/coupons/" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-gray-700 hover:bg-gray-50">
                    <i class="ri-coupon-line ml-3 text-gray-400"></i>
                    קופונים
                </a>
            </li>

            <!-- Divider -->
            <li class="py-3">
                <div class="border-t border-gray-200"></div>
            </li>

            <!-- אנליטיקס -->
            <li class="pt-2">
                <p class="px-4 text-xs font-semibold uppercase tracking-wider text-gray-500">ניתוח נתונים</p>
            </li>
            <li class="pt-1">
                <a href="/admin/analytics/" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-gray-700 hover:bg-gray-50">
                    <i class="ri-line-chart-line ml-3 text-gray-400"></i>
                    דוחות ונתונים
                </a>
            </li>

            <!-- Divider -->
            <li class="py-3">
                <div class="border-t border-gray-200"></div>
            </li>

            <!-- אתר -->
            <li class="pt-2">
                <p class="px-4 text-xs font-semibold uppercase tracking-wider text-gray-500">עיצוב האתר</p>
            </li>
            <li class="pt-1">
                <a href="/admin/settings/theme.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-gray-700 hover:bg-gray-50">
                    <i class="ri-brush-line ml-3 text-gray-400"></i>
                    עיצוב והתאמה אישית
                </a>
            </li>
            <li>
                <a href="/admin/pages/" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-gray-700 hover:bg-gray-50">
                    <i class="ri-file-text-line ml-3 text-gray-400"></i>
                    עמודים
                </a>
            </li>
            <li>
                <a href="/admin/menus/" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-gray-700 hover:bg-gray-50">
                    <i class="ri-menu-line ml-3 text-gray-400"></i>
                    תפריטים
                </a>
            </li>
            <li>
                <a href="/admin/popups/" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-gray-700 hover:bg-gray-50">
                    <i class="ri-notification-line ml-3 text-gray-400"></i>
                    פופאפים
                </a>
            </li>
            <li>
                <a href="/admin/media/" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-gray-700 hover:bg-gray-50">
                    <i class="ri-image-line ml-3 text-gray-400"></i>
                    מדיה
                </a>
            </li>

            <!-- Divider -->
            <li class="py-3">
                <div class="border-t border-gray-200"></div>
            </li>

            <!-- הגדרות -->
            <li class="pt-2">
                <p class="px-4 text-xs font-semibold uppercase tracking-wider text-gray-500">מערכת</p>
            </li>
            <li class="pt-1">
                <a href="/admin/settings/" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-gray-700 hover:bg-gray-50">
                    <i class="ri-settings-line ml-3 text-gray-400"></i>
                    הגדרות
                </a>
            </li>

            <li>
                <a href="/admin/settings/statuses.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-gray-700 hover:bg-gray-50">
                    <i class="ri-settings-3-line ml-3 text-gray-400"></i>
                    ניהול סטטוסים
                </a>
            </li>
        </ul>

        <!-- Logout -->
        <div class="border-t border-gray-200 mt-6 pt-6" id="sidebar-bottom">
            <a href="/logout.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all text-red-600 hover:bg-red-50">
                <i class="ri-logout-box-line ml-3 text-red-600"></i>
                התנתק
            </a>
        </div>
    </nav>
</div>

<!-- Overlay for mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden lg:hidden z-40" onclick="toggleSidebar()"></div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    sidebar.classList.toggle('translate-x-full');
    overlay.classList.toggle('hidden');
}

// Scroll to bottom function
function scrollToBottom() {
    const sidebarNav = document.getElementById('sidebar-nav');
    sidebarNav.scrollTo({
        top: sidebarNav.scrollHeight,
        behavior: 'smooth'
    });
}

// Scroll to top function
function scrollToTop() {
    const sidebarNav = document.getElementById('sidebar-nav');
    sidebarNav.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Show/hide scroll buttons based on scroll position
function updateScrollButtons() {
    const sidebarNav = document.getElementById('sidebar-nav');
    const scrollToTopButton = document.getElementById('scroll-to-top');
    const scrollToBottomButton = document.getElementById('scroll-to-bottom');
    const scrollButtons = document.getElementById('scroll-buttons');
    const scrollProgressBg = document.getElementById('scroll-progress-bg');
    const scrollProgress = document.getElementById('scroll-progress');
    
    const isScrollable = sidebarNav.scrollHeight > sidebarNav.clientHeight;
    const isAtTop = sidebarNav.scrollTop <= 10;
    const isAtBottom = sidebarNav.scrollTop + sidebarNav.clientHeight >= sidebarNav.scrollHeight - 10;
    
    if (!isScrollable) {
        // אם אין צורך בגלילה, הסתר את הכפתורים ואת האינדיקטור
        scrollButtons.style.display = 'none';
        scrollProgressBg.style.display = 'none';
    } else {
        scrollButtons.style.display = 'flex';
        scrollProgressBg.style.display = 'block';
        
        // עדכון אינדיקטור ההתקדמות
        const scrollPercentage = (sidebarNav.scrollTop / (sidebarNav.scrollHeight - sidebarNav.clientHeight)) * 100;
        scrollProgress.style.height = scrollPercentage + '%';
        
        // הצג/הסתר כפתור למעלה
        if (isAtTop) {
            scrollToTopButton.style.display = 'none';
        } else {
            scrollToTopButton.style.display = 'block';
        }
        
        // הצג/הסתר כפתור למטה
        if (isAtBottom) {
            scrollToBottomButton.style.display = 'none';
        } else {
            scrollToBottomButton.style.display = 'block';
        }
    }
}

// Close sidebar when screen becomes large
window.addEventListener('resize', function() {
    if (window.innerWidth >= 1024) {
        document.getElementById('sidebar').classList.remove('translate-x-full');
        document.getElementById('sidebar-overlay').classList.add('hidden');
    }
});

// Initialize scroll buttons behavior
document.addEventListener('DOMContentLoaded', function() {
    const sidebarNav = document.getElementById('sidebar-nav');
    
    // Add scroll event listener
    sidebarNav.addEventListener('scroll', updateScrollButtons);
    
    // Initial check
    updateScrollButtons();
    
    // Add hover effects for better UX
    const scrollToTopButton = document.getElementById('scroll-to-top');
    const scrollToBottomButton = document.getElementById('scroll-to-bottom');
    
    [scrollToTopButton, scrollToBottomButton].forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Update buttons on window resize
    window.addEventListener('resize', updateScrollButtons);
});
</script> 