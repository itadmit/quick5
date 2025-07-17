<!-- Top Bar -->
<div class="sticky top-0 z-40 bg-white shadow-lg">
    <div class="relative flex items-center justify-between px-4 sm:px-6 lg:px-8 py-3">
        
        <!-- Mobile menu button -->
        <button class="lg:hidden text-gray-600 hover:text-gray-800 transition-colors" onclick="toggleSidebar()">
            <i class="ri-menu-line text-xl"></i>
        </button>

        <!-- Title -->
        <div>
            <h1 class="text-xl font-bold text-gray-800">👋 ברוך הבא, <?php echo htmlspecialchars($currentUser['first_name'] ?? 'משתמש'); ?>!</h1>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <a href="/admin/products/new.php" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all bg-blue-600 text-white hover:bg-blue-700">
                <i class="ri-add-line ml-2"></i>
                מוצר חדש
            </a>
            
            <a href="<?php echo 'https://' . (isset($store['domain']) ? $store['domain'] : 'store') . '.quickshop5.co.il'; ?>" target="_blank" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all border border-gray-300 text-gray-700 hover:bg-gray-50">
                <i class="ri-external-link-line ml-2"></i>
                צפה בחנות
            </a>
            
            <!-- User Avatar -->
            <div class="flex items-center gap-2">
                <div class="text-right hidden sm:block">
                    <div class="font-medium text-sm text-gray-900"><?= htmlspecialchars(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')) ?></div>
                    <div class="text-xs text-gray-500"><?= htmlspecialchars($currentUser['email'] ?? '') ?></div>
                </div>
                <div class="w-8 h-8 rounded-full flex items-center justify-center bg-gray-100 border border-gray-200">
                    <i class="ri-user-line text-sm text-gray-600"></i>
                </div>
            </div>
        </div>
    </div>
</div> 