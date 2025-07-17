<?php
/**
 * עמוד מוצרים - QuickShop5 Admin
 */

require_once __DIR__ . '/../../includes/auth.php';

$auth = new Authentication();
$auth->requireLogin(); // הגנה על העמוד

$currentUser = $auth->getCurrentUser();

// אם אין משתמש מחובר, הפנה להתחברות
if (!$currentUser) {
    header('Location: /admin/login.php');
    exit;
}

// קבלת נתוני החנות
require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM stores WHERE user_id = ? LIMIT 1");
$stmt->execute([$currentUser['id']]);
$store = $stmt->fetch();

// אם אין חנות, הפנה לדשבורד
if (!$store) {
    header('Location: /admin/');
    exit;
}

// מידע על pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// פילטרים
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// בניית שאילתת SQL עם פילטרים
$whereConditions = ['p.store_id = ?'];
$params = [$store['id']];

if (!empty($search)) {
    $whereConditions[] = '(p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($status)) {
    $whereConditions[] = 'p.status = ?';
    $params[] = $status;
}

if (!empty($category)) {
    $whereConditions[] = 'EXISTS (SELECT 1 FROM product_categories pc WHERE pc.product_id = p.id AND pc.category_id = ?)';
    $params[] = $category;
}

$whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

// סיווג תוצאות
$orderBy = match($sort) {
    'name_asc' => 'ORDER BY p.name ASC',
    'name_desc' => 'ORDER BY p.name DESC',
    'price_asc' => 'ORDER BY p.price ASC',
    'price_desc' => 'ORDER BY p.price DESC',
    'oldest' => 'ORDER BY p.created_at ASC',
    default => 'ORDER BY p.created_at DESC'
};

// קבלת מוצרים
$sql = "
    SELECT p.*, 
           (SELECT pm.url FROM product_media pm WHERE pm.product_id = p.id AND pm.is_primary = TRUE LIMIT 1) as primary_image,
           (SELECT COUNT(*) FROM product_categories pc WHERE pc.product_id = p.id) as category_count,
           (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') 
            FROM categories c 
            JOIN product_categories pc ON c.id = pc.category_id 
            WHERE pc.product_id = p.id 
            LIMIT 3) as category_names
    FROM products p
    $whereClause
    $orderBy
    LIMIT $limit OFFSET $offset
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// ספירת סה"כ תוצאות
$countSql = "SELECT COUNT(*) FROM products p $whereClause";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$totalProducts = $stmt->fetchColumn();
$totalPages = ceil($totalProducts / $limit);

// קבלת קטגוריות לפילטר
$stmt = $db->prepare("SELECT * FROM categories WHERE store_id = ? ORDER BY name ASC");
$stmt->execute([$store['id']]);
$categories = $stmt->fetchAll();

// סטטיסטיקות מוצרים
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
        SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived,
        SUM(CASE WHEN inventory_quantity <= 5 AND track_inventory = 1 THEN 1 ELSE 0 END) as low_stock
    FROM products WHERE store_id = ?
");
$stmt->execute([$store['id']]);
$stats = $stmt->fetch();

$pageTitle = 'מוצרים';
?>

<?php include '../templates/header.php'; ?>
    
    <?php include '../templates/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="lg:pr-64">
        
        <?php include '../templates/navbar.php'; ?>

        <!-- Products Content -->
        <main class="py-8" style="background: #e9f0f3;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Success/Error Messages -->
                <?php if (isset($_GET['created'])): ?>
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="ri-check-circle-line text-green-500 ml-3"></i>
                            <span class="text-green-700">המוצר נוצר בהצלחה!</span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['updated'])): ?>
                    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="ri-check-circle-line text-blue-500 ml-3"></i>
                            <span class="text-blue-700">המוצר עודכן בהצלחה!</span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['deleted'])): ?>
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="ri-check-circle-line text-green-500 ml-3"></i>
                            <span class="text-green-700"><?= htmlspecialchars($_GET['message'] ?? 'המוצר נמחק בהצלחה!') ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="ri-error-warning-line text-red-500 ml-3"></i>
                            <span class="text-red-700">
                                <?php
                                $errors = [
                                    'product_not_found' => 'המוצר לא נמצא',
                                    'invalid_product' => 'מזהה מוצר לא תקין'
                                ];
                                echo $errors[$_GET['error']] ?? 'שגיאה לא ידועה';
                                ?>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Header Section -->
                <div class="mb-8">
                    <div class="bg-white shadow-xl card-rounded p-6">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 mb-2">ניהול מוצרים</h1>
                                <p class="text-gray-600">נהל את כל המוצרים בחנות שלך</p>
                            </div>
                            <div class="flex items-center gap-3 mt-4 lg:mt-0">
                                <a href="/admin/products/new.php" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-medium transition-colors flex items-center gap-2">
                                    <i class="ri-add-line"></i>
                                    הוסף מוצר חדש
                                </a>
                                <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl font-medium transition-colors flex items-center gap-2">
                                    <i class="ri-download-line"></i>
                                    ייצא
                                </button>
                            </div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                            <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl">
                                <div class="text-2xl font-bold text-blue-600"><?= $stats['total'] ?></div>
                                <div class="text-sm text-blue-700">סה״כ מוצרים</div>
                            </div>
                            <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl">
                                <div class="text-2xl font-bold text-green-600"><?= $stats['active'] ?></div>
                                <div class="text-sm text-green-700">פעילים</div>
                            </div>
                            <div class="text-center p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl">
                                <div class="text-2xl font-bold text-yellow-600"><?= $stats['draft'] ?></div>
                                <div class="text-sm text-yellow-700">טיוטות</div>
                            </div>
                            <div class="text-center p-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl">
                                <div class="text-2xl font-bold text-gray-600"><?= $stats['archived'] ?></div>
                                <div class="text-sm text-gray-700">בארכיון</div>
                            </div>
                            <div class="text-center p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-xl">
                                <div class="text-2xl font-bold text-red-600"><?= $stats['low_stock'] ?></div>
                                <div class="text-sm text-red-700">מלאי נמוך</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters Section -->
                <div class="mb-6">
                    <div class="bg-white shadow-xl card-rounded p-6">
                        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            
                            <!-- Search -->
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       value="<?= htmlspecialchars($search) ?>"
                                       placeholder="חיפוש מוצרים..." 
                                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <i class="ri-search-line absolute left-3 top-2.5 text-gray-400"></i>
                            </div>

                            <!-- Status Filter -->
                            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">כל הסטטוסים</option>
                                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>פעיל</option>
                                <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>טיוטה</option>
                                <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>בארכיון</option>
                            </select>

                            <!-- Category Filter -->
                            <select name="category" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">כל הקטגוריות</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <!-- Sort -->
                            <select name="sort" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>הכי חדשים</option>
                                <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>הכי ישנים</option>
                                <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>שם א-ת</option>
                                <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>שם ת-א</option>
                                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>מחיר נמוך-גבוה</option>
                                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>מחיר גבוה-נמוך</option>
                            </select>

                            <!-- Filter Button -->
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                                <i class="ri-filter-line"></i>
                                סנן
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="bg-white shadow-xl card-rounded">
                    <div class="px-6 py-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-medium text-gray-900">
                                מוצרים (<?= number_format($totalProducts) ?>)
                            </h3>
                            
                            <!-- Bulk Actions & View Toggle -->
                            <div class="flex items-center gap-4">
                                <!-- Bulk Delete Button (hidden by default) -->
                                <button id="bulkDeleteBtn" 
                                        onclick="bulkDeleteProducts()" 
                                        class="hidden bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                                    <i class="ri-delete-bin-line"></i>
                                    מחק נבחרים (<span id="selectedCount">0</span>)
                                </button>
                                
                                <!-- View Toggle -->
                                <div class="flex items-center gap-2">
                                    <button class="p-2 text-blue-600 bg-blue-100 rounded-lg">
                                        <i class="ri-table-line"></i>
                                    </button>
                                    <button class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                                        <i class="ri-grid-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <?php if (empty($products)): ?>
                            <!-- Empty State -->
                            <div class="text-center py-12">
                                <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="ri-shopping-bag-line text-4xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">אין מוצרים</h3>
                                <p class="text-gray-500 mb-6">
                                    <?php if (!empty($search) || !empty($status) || !empty($category)): ?>
                                        לא נמצאו מוצרים התואמים לחיפוש שלך
                                    <?php else: ?>
                                        עדיין לא הוספת מוצרים לחנות שלך
                                    <?php endif; ?>
                                </p>
                                <a href="/admin/products/new.php" 
                                   class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    <i class="ri-add-line"></i>
                                    הוסף מוצר ראשון
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Products Table -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="border-b border-gray-200">
                                            <th class="text-center py-3 px-4 text-sm font-medium text-gray-500 w-12">
                                                <input type="checkbox" 
                                                       id="selectAll" 
                                                       onchange="toggleSelectAll()" 
                                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            </th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">מוצר</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">סטטוס</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">מלאי</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">מחיר</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">קטגוריות</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">תאריך יצירה</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">פעולות</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <?php foreach ($products as $product): ?>
                                            <tr class="hover:bg-gray-50">
                                                <!-- Checkbox -->
                                                <td class="py-4 px-4 text-center">
                                                    <input type="checkbox" 
                                                           name="selected_products[]" 
                                                           value="<?= $product['id'] ?>" 
                                                           onchange="updateBulkActions()" 
                                                           class="product-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                </td>
                                                
                                                <!-- Product Info -->
                                                <td class="py-4 px-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-12 h-12 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                                            <?php if (!empty($product['primary_image'])): ?>
                                                                <img src="<?= htmlspecialchars($product['primary_image']) ?>" 
                                                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                                                     class="w-full h-full object-cover">
                                                            <?php else: ?>
                                                                <div class="w-full h-full flex items-center justify-center">
                                                                    <i class="ri-image-line text-gray-400"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <div class="text-sm font-medium text-gray-900 truncate">
                                                                <?= htmlspecialchars($product['name']) ?>
                                                            </div>
                                                            <div class="text-xs text-gray-500">
                                                                SKU: <?= htmlspecialchars($product['sku'] ?: 'ללא') ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Status -->
                                                <td class="py-4 px-4">
                                                    <?php 
                                                    $statusConfig = [
                                                        'active' => ['text' => 'פעיל', 'class' => 'bg-green-100 text-green-800'],
                                                        'draft' => ['text' => 'טיוטה', 'class' => 'bg-yellow-100 text-yellow-800'],
                                                        'archived' => ['text' => 'בארכיון', 'class' => 'bg-gray-100 text-gray-800']
                                                    ];
                                                    $status = $statusConfig[$product['status']] ?? ['text' => $product['status'], 'class' => 'bg-gray-100 text-gray-800'];
                                                    ?>
                                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?= $status['class'] ?>">
                                                        <?= $status['text'] ?>
                                                    </span>
                                                </td>

                                                <!-- Inventory -->
                                                <td class="py-4 px-4">
                                                    <?php if ($product['track_inventory']): ?>
                                                        <div class="text-sm text-gray-900"><?= number_format($product['inventory_quantity'] ?? 0) ?></div>
                                                        <?php if (($product['inventory_quantity'] ?? 0) <= 5): ?>
                                                            <div class="text-xs text-red-600">מלאי נמוך</div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-xs text-gray-500">לא נעקב</span>
                                                    <?php endif; ?>
                                                </td>

                                                <!-- Price -->
                                                <td class="py-4 px-4">
                                                    <?php if ($product['price'] !== null): ?>
                                                        <div class="text-sm font-medium text-gray-900">
                                                            ₪<?= number_format($product['price'] ?? 0, 2) ?>
                                                        </div>
                                                        <?php if (($product['compare_price'] ?? 0) > ($product['price'] ?? 0)): ?>
                                                            <div class="text-xs text-gray-500 line-through">
                                                                ₪<?= number_format($product['compare_price'] ?? 0, 2) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <div class="text-sm text-gray-500">יש וריאציות</div>
                                                    <?php endif; ?>
                                                </td>

                                                <!-- Categories -->
                                                <td class="py-4 px-4">
                                                    <div class="text-sm text-gray-900 truncate max-w-32" title="<?= htmlspecialchars($product['category_names'] ?: 'ללא קטגוריה') ?>">
                                                        <?= htmlspecialchars($product['category_names'] ?: 'ללא קטגוריה') ?>
                                                    </div>
                                                </td>

                                                <!-- Created Date -->
                                                <td class="py-4 px-4">
                                                    <div class="text-sm text-gray-900">
                                                        <?= date('d/m/Y', strtotime($product['created_at'])) ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        <?= date('H:i', strtotime($product['created_at'])) ?>
                                                    </div>
                                                </td>

                                                <!-- Actions -->
                                                <td class="py-4 px-4">
                                                    <div class="flex items-center gap-2">
                                                        <a href="/admin/products/edit.php?id=<?= $product['id'] ?>" 
                                                           class="text-blue-600 hover:text-blue-800 p-1">
                                                            <i class="ri-edit-line"></i>
                                                        </a>
                                                                                                <a href="#" onclick="window.open('http://<?= $store['slug'] ?>.localhost:8000/product/<?= rawurlencode($product['slug']) ?>', '_blank'); return false;" 
                                           class="text-green-600 hover:text-green-800 p-1">
                                            <i class="ri-external-link-line"></i>
                                        </a>
                                                        <button onclick="deleteProduct(<?= $product['id'] ?>)" 
                                                                class="text-red-600 hover:text-red-800 p-1">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-6">
                                    <div class="flex flex-1 justify-between sm:hidden">
                                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => max(1, $page - 1)])) ?>"
                                           class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 <?= $page <= 1 ? 'opacity-50 pointer-events-none' : '' ?>">
                                            הקודם
                                        </a>
                                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => min($totalPages, $page + 1)])) ?>"
                                           class="relative mr-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 <?= $page >= $totalPages ? 'opacity-50 pointer-events-none' : '' ?>">
                                            הבא
                                        </a>
                                    </div>
                                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm text-gray-700">
                                                מציג 
                                                <span class="font-medium"><?= $offset + 1 ?></span>
                                                עד
                                                <span class="font-medium"><?= min($offset + $limit, $totalProducts) ?></span>
                                                מתוך
                                                <span class="font-medium"><?= number_format($totalProducts) ?></span>
                                                תוצאות
                                            </p>
                                        </div>
                                        <div>
                                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => max(1, $page - 1)])) ?>"
                                                   class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 <?= $page <= 1 ? 'opacity-50 pointer-events-none' : '' ?>">
                                                    <i class="ri-arrow-right-s-line"></i>
                                                </a>
                                                
                                                <?php
                                                $start = max(1, $page - 2);
                                                $end = min($totalPages, $page + 2);
                                                
                                                if ($start > 1): ?>
                                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>"
                                                       class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">1</a>
                                                    <?php if ($start > 2): ?>
                                                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300">...</span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                
                                                <?php for ($i = $start; $i <= $end; $i++): ?>
                                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                                                       class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?= $i === $page ? 'bg-blue-600 text-white' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50' ?>"><?= $i ?></a>
                                                <?php endfor; ?>
                                                
                                                <?php if ($end < $totalPages): ?>
                                                    <?php if ($end < $totalPages - 1): ?>
                                                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300">...</span>
                                                    <?php endif; ?>
                                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>"
                                                       class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"><?= $totalPages ?></a>
                                                <?php endif; ?>
                                                
                                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => min($totalPages, $page + 1)])) ?>"
                                                   class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 <?= $page >= $totalPages ? 'opacity-50 pointer-events-none' : '' ?>">
                                                    <i class="ri-arrow-left-s-line"></i>
                                                </a>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        function deleteProduct(productId) {
            if (confirm('האם אתה בטוח שברצונך למחוק את המוצר? פעולה זו לא ניתנת לביטול.')) {
                // כאן נוסיף את לוגיקת המחיקה
                window.location.href = `/admin/products/delete.php?id=${productId}`;
            }
        }

        // Bulk actions functionality
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const productCheckboxes = document.querySelectorAll('.product-checkbox');
            
            productCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            
            updateBulkActions();
        }

        function updateBulkActions() {
            const productCheckboxes = document.querySelectorAll('.product-checkbox');
            const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            const selectAllCheckbox = document.getElementById('selectAll');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            const selectedCount = document.getElementById('selectedCount');
            
            // Update select all checkbox state
            if (selectedCheckboxes.length === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (selectedCheckboxes.length === productCheckboxes.length) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
                selectAllCheckbox.checked = false;
            }
            
            // Show/hide bulk actions
            if (selectedCheckboxes.length > 0) {
                bulkDeleteBtn.classList.remove('hidden');
                selectedCount.textContent = selectedCheckboxes.length;
            } else {
                bulkDeleteBtn.classList.add('hidden');
            }
        }

        function bulkDeleteProducts() {
            const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
            
            if (selectedIds.length === 0) {
                alert('לא נבחרו מוצרים למחיקה');
                return;
            }
            
            const confirmMessage = selectedIds.length === 1 
                ? 'האם אתה בטוח שברצונך למחוק את המוצר הנבחר?'
                : `האם אתה בטוח שברצונך למחוק ${selectedIds.length} מוצרים נבחרים?`;
            
            if (confirm(confirmMessage + '\nפעולה זו לא ניתנת לביטול.')) {
                // Show loading state
                const originalText = document.getElementById('bulkDeleteBtn').innerHTML;
                document.getElementById('bulkDeleteBtn').innerHTML = '<i class="ri-loader-4-line animate-spin"></i> מוחק...';
                document.getElementById('bulkDeleteBtn').disabled = true;
                
                // Send bulk delete request
                fetch('/admin/api/bulk-delete-products.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_ids: selectedIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect with success message
                        const message = selectedIds.length === 1 
                            ? 'המוצר נמחק בהצלחה'
                            : `${data.deleted_count} מוצרים נמחקו בהצלחה`;
                        window.location.href = `?deleted=1&message=${encodeURIComponent(message)}`;
                    } else {
                        alert('שגיאה במחיקת המוצרים: ' + (data.message || 'שגיאה לא ידועה'));
                        // Restore button
                        document.getElementById('bulkDeleteBtn').innerHTML = originalText;
                        document.getElementById('bulkDeleteBtn').disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('שגיאה במחיקת המוצרים');
                    // Restore button
                    document.getElementById('bulkDeleteBtn').innerHTML = originalText;
                    document.getElementById('bulkDeleteBtn').disabled = false;
                });
            }
        }
    </script>

<?php include '../templates/footer.php'; ?> 