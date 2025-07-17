<?php
// קבלת מידע החנות מהגלובלים
$store = $GLOBALS['CURRENT_STORE'] ?? null;
$storeContext = $GLOBALS['STORE_CONTEXT'] ?? null;
$storeResolver = $GLOBALS['STORE_RESOLVER'] ?? null;

if (!$store) {
    header('HTTP/1.1 404 Not Found');
    exit('Store not found');
}
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?><?= htmlspecialchars($store['name']) ?> - חנות אונליין</title>
    <meta name="description" content="<?= isset($pageDescription) ? htmlspecialchars($pageDescription) : htmlspecialchars($store['description'] ?: 'חנות אונליין מתקדמת') ?>">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Hebrew:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Noto Sans Hebrew', sans-serif; }
        :root {
            --primary-color: <?= $store['primary_color'] ?: '#3B82F6' ?>;
            --secondary-color: <?= $store['secondary_color'] ?: '#1E40AF' ?>;
        }
        .btn-primary {
            background-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: var(--secondary-color);
        }
        .text-primary {
            color: var(--primary-color);
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
    
    <?php if (isset($additionalCSS)): ?>
        <?= $additionalCSS ?>
    <?php endif; ?>
</head>
<body class="bg-gray-50">

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="/">
                        <?php if ($store['logo']): ?>
                            <img src="<?= htmlspecialchars($store['logo']) ?>" 
                                 alt="<?= htmlspecialchars($store['name']) ?>" 
                                 class="h-10 w-auto">
                        <?php else: ?>
                            <h1 class="text-2xl font-bold text-primary">
                                <?= htmlspecialchars($store['name']) ?>
                            </h1>
                        <?php endif; ?>
                    </a>
                </div>
                
                <!-- Navigation -->
                <nav class="hidden md:flex space-x-8 space-x-reverse">
                    <a href="/" class="text-gray-700 hover:text-primary transition-colors <?= ($currentPage ?? '') === 'home' ? 'text-primary font-medium' : '' ?>">בית</a>
                    <a href="/category/all" class="text-gray-700 hover:text-primary transition-colors <?= ($currentPage ?? '') === 'category' ? 'text-primary font-medium' : '' ?>">מוצרים</a>
                    <a href="/about" class="text-gray-700 hover:text-primary transition-colors <?= ($currentPage ?? '') === 'about' ? 'text-primary font-medium' : '' ?>">אודות</a>
                    <a href="/contact" class="text-gray-700 hover:text-primary transition-colors <?= ($currentPage ?? '') === 'contact' ? 'text-primary font-medium' : '' ?>">צור קשר</a>
                </nav>
                
                <!-- Cart & Search -->
                <div class="flex items-center space-x-4 space-x-reverse">
                    <button class="p-2 text-gray-600 hover:text-primary transition-colors" id="search-btn">
                        <i class="ri-search-line text-xl"></i>
                    </button>
                    
                    <a href="/cart" class="p-2 text-gray-600 hover:text-primary transition-colors relative" id="cart-btn">
                        <i class="ri-shopping-cart-line text-xl"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" 
                              data-cart-count style="display: none;">0</span>
                    </a>
                    
                    <!-- Mobile menu button -->
                    <button class="md:hidden p-2 text-gray-600" id="mobile-menu-btn">
                        <i class="ri-menu-line text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Navigation -->
        <div class="md:hidden hidden bg-white border-t" id="mobile-menu">
            <div class="px-4 py-2 space-y-2">
                <a href="/" class="block py-2 text-gray-700 hover:text-primary <?= ($currentPage ?? '') === 'home' ? 'text-primary font-medium' : '' ?>">בית</a>
                <a href="/category/all" class="block py-2 text-gray-700 hover:text-primary <?= ($currentPage ?? '') === 'category' ? 'text-primary font-medium' : '' ?>">מוצרים</a>
                <a href="/about" class="block py-2 text-gray-700 hover:text-primary <?= ($currentPage ?? '') === 'about' ? 'text-primary font-medium' : '' ?>">אודות</a>
                <a href="/contact" class="block py-2 text-gray-700 hover:text-primary <?= ($currentPage ?? '') === 'contact' ? 'text-primary font-medium' : '' ?>">צור קשר</a>
            </div>
        </div>
    </header>

    <!-- Search Modal -->
    <div id="search-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-start justify-center pt-20 px-4">
            <div class="bg-white rounded-lg w-full max-w-md">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">חיפוש מוצרים</h3>
                        <button id="close-search" class="text-gray-400 hover:text-gray-600">
                            <i class="ri-close-line text-xl"></i>
                        </button>
                    </div>
                    <form action="/search" method="GET">
                        <div class="relative">
                            <input type="text" name="q" placeholder="מה אתה מחפש?" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <button type="submit" class="absolute left-3 top-3 text-gray-400 hover:text-gray-600">
                                <i class="ri-search-line text-xl"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        // Search modal
        document.getElementById('search-btn').addEventListener('click', function() {
            document.getElementById('search-modal').classList.remove('hidden');
            document.querySelector('#search-modal input').focus();
        });

        document.getElementById('close-search').addEventListener('click', function() {
            document.getElementById('search-modal').classList.add('hidden');
        });

        document.getElementById('search-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    </script> 