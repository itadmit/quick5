<?php
/**
 * עמוד הזמנות - QuickShop5 Admin
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
$payment_status = $_GET['payment_status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// בניית שאילתת SQL עם פילטרים
$whereConditions = ['o.store_id = ?'];
$params = [$store['id']];

if (!empty($search)) {
    $whereConditions[] = '(o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($status)) {
    $whereConditions[] = 'o.status = ?';
    $params[] = $status;
}

if (!empty($payment_status)) {
    $whereConditions[] = 'o.payment_status = ?';
    $params[] = $payment_status;
}

if (!empty($date_from)) {
    $whereConditions[] = 'DATE(o.order_date) >= ?';
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $whereConditions[] = 'DATE(o.order_date) <= ?';
    $params[] = $date_to;
}

$whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

// סיווג תוצאות
$orderBy = match($sort) {
    'customer_asc' => 'ORDER BY o.customer_name ASC',
    'customer_desc' => 'ORDER BY o.customer_name DESC',
    'total_asc' => 'ORDER BY o.total_amount ASC',
    'total_desc' => 'ORDER BY o.total_amount DESC',
    'oldest' => 'ORDER BY o.order_date ASC',
    default => 'ORDER BY o.order_date DESC'
};

// קבלת הזמנות
$sql = "
    SELECT o.*, 
           COUNT(oi.id) as items_count,
           GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as product_names
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    $whereClause
    GROUP BY o.id
    $orderBy
    LIMIT $limit OFFSET $offset
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll() ?: [];

// ספירת סה"כ תוצאות
$countSql = "SELECT COUNT(DISTINCT o.id) FROM orders o $whereClause";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$totalOrders = (int)$stmt->fetchColumn();
$totalPages = ceil($totalOrders / $limit);

// סטטיסטיקות הזמנות
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
        SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as payment_pending,
        COALESCE(SUM(total_amount), 0) as total_revenue
    FROM orders WHERE store_id = ?
");
$stmt->execute([$store['id']]);
$stats = $stmt->fetch();

// וידוא שכל הערכים הם מספרים ולא null
$stats = array_map(function($value) {
    return $value === null ? 0 : $value;
}, $stats);

$pageTitle = 'הזמנות';
?>

<?php include '../templates/header.php'; ?>
    
    <?php include '../templates/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="lg:pr-64">
        
        <?php include '../templates/navbar.php'; ?>

        <!-- Orders Content -->
        <main class="py-8" style="background: #e9f0f3;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Success/Error Messages -->
                <?php if (isset($_GET['updated'])): ?>
                    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="ri-check-circle-line text-blue-500 ml-3"></i>
                            <span class="text-blue-700">ההזמנה עודכנה בהצלחה!</span>
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
                                    'order_not_found' => 'ההזמנה לא נמצאה',
                                    'invalid_order' => 'מזהה הזמנה לא תקין'
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
                                <h1 class="text-2xl font-bold text-gray-900 mb-2">ניהול הזמנות</h1>
                                <p class="text-gray-600">נהל את כל ההזמנות בחנות שלך</p>
                            </div>
                            <div class="flex items-center gap-3 mt-4 lg:mt-0">
                                <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl font-medium transition-colors flex items-center gap-2">
                                    <i class="ri-download-line"></i>
                                    ייצא להזמנות
                                </button>
                            </div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                            <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl">
                                <div class="text-2xl font-bold text-blue-600"><?= $stats['total'] ?? 0 ?></div>
                                <div class="text-sm text-blue-700">סה״כ הזמנות</div>
                            </div>
                            <div class="text-center p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl">
                                <div class="text-2xl font-bold text-yellow-600"><?= $stats['pending'] ?? 0 ?></div>
                                <div class="text-sm text-yellow-700">ממתינות</div>
                            </div>
                            <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl">
                                <div class="text-2xl font-bold text-green-600"><?= $stats['delivered'] ?? 0 ?></div>
                                <div class="text-sm text-green-700">נמסרו</div>
                            </div>
                            <div class="text-center p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-xl">
                                <div class="text-2xl font-bold text-red-600"><?= $stats['cancelled'] ?? 0 ?></div>
                                <div class="text-sm text-red-700">בוטלו</div>
                            </div>
                            <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl">
                                <div class="text-2xl font-bold text-purple-600">₪<?= number_format($stats['total_revenue'] ?? 0, 0) ?></div>
                                <div class="text-sm text-purple-700">סה״כ הכנסות</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters Section -->
                <div class="mb-6">
                    <div class="bg-white shadow-xl card-rounded p-6">
                        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                            
                            <!-- Search -->
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       value="<?= htmlspecialchars($search) ?>"
                                       placeholder="חיפוש הזמנות..." 
                                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <i class="ri-search-line absolute left-3 top-2.5 text-gray-400"></i>
                            </div>

                            <!-- Status Filter -->
                            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">כל הסטטוסים</option>
                                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>ממתינה</option>
                                <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>אושרה</option>
                                <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>בטיפול</option>
                                <option value="shipped" <?= $status === 'shipped' ? 'selected' : '' ?>>נשלחה</option>
                                <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>נמסרה</option>
                                <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>בוטלה</option>
                                <option value="refunded" <?= $status === 'refunded' ? 'selected' : '' ?>>הוחזרה</option>
                            </select>

                            <!-- Payment Status Filter -->
                            <select name="payment_status" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">כל סטטוסי התשלום</option>
                                <option value="pending" <?= $payment_status === 'pending' ? 'selected' : '' ?>>ממתין לתשלום</option>
                                <option value="paid" <?= $payment_status === 'paid' ? 'selected' : '' ?>>שולם</option>
                                <option value="failed" <?= $payment_status === 'failed' ? 'selected' : '' ?>>נכשל</option>
                                <option value="refunded" <?= $payment_status === 'refunded' ? 'selected' : '' ?>>הוחזר</option>
                            </select>

                            <!-- Date Range -->
                            <input type="date" 
                                   name="date_from" 
                                   value="<?= htmlspecialchars($date_from) ?>"
                                   placeholder="מתאריך"
                                   class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            
                            <input type="date" 
                                   name="date_to" 
                                   value="<?= htmlspecialchars($date_to) ?>"
                                   placeholder="עד תאריך"
                                   class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                            <!-- Sort -->
                            <select name="sort" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>הכי חדשות</option>
                                <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>הכי ישנות</option>
                                <option value="customer_asc" <?= $sort === 'customer_asc' ? 'selected' : '' ?>>לקוח א-ת</option>
                                <option value="customer_desc" <?= $sort === 'customer_desc' ? 'selected' : '' ?>>לקוח ת-א</option>
                                <option value="total_asc" <?= $sort === 'total_asc' ? 'selected' : '' ?>>סכום נמוך-גבוה</option>
                                <option value="total_desc" <?= $sort === 'total_desc' ? 'selected' : '' ?>>סכום גבוה-נמוך</option>
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

                <!-- Orders Table -->
                <div class="bg-white shadow-xl card-rounded">
                    <div class="px-6 py-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-medium text-gray-900">
                                הזמנות (<?= number_format($totalOrders) ?>)
                            </h3>
                        </div>

                        <?php if (empty($orders)): ?>
                            <!-- Empty State -->
                            <div class="text-center py-12">
                                <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="ri-shopping-cart-line text-4xl text-gray-400"></i>
                                </div>
                                                                            <h3 class="text-lg font-medium text-gray-900 mb-2">אין הזמנות</h3>
                                <p class="text-gray-500 mb-6">
                                    <?php if (!empty($search) || !empty($status) || !empty($payment_status) || !empty($date_from) || !empty($date_to)): ?>
                                        לא נמצאו הזמנות התואמות לחיפוש שלך
                                    <?php else: ?>
                                        עדיין לא התקבלו הזמנות בחנות שלך
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <!-- Orders Table -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="border-b border-gray-200">
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">מספר הזמנה</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">לקוח</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">סטטוס</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">תשלום</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">פריטים</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">סכום</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">תאריך</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">פעולות</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <?php foreach ($orders as $order): ?>
                                            <tr class="hover:bg-gray-50">
                                                <!-- Order Number -->
                                                <td class="py-4 px-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        #<?= htmlspecialchars($order['order_number']) ?>
                                                    </div>
                                                </td>

                                                <!-- Customer -->
                                                <td class="py-4 px-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?= htmlspecialchars($order['customer_name']) ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        <?= htmlspecialchars($order['customer_email']) ?>
                                                    </div>
                                                </td>

                                                <!-- Status -->
                                                <td class="py-4 px-4">
                                                    <?php 
                                                    $statusConfig = [
                                                        'pending' => ['text' => 'ממתינה', 'class' => 'bg-yellow-100 text-yellow-800'],
                                                        'confirmed' => ['text' => 'אושרה', 'class' => 'bg-blue-100 text-blue-800'],
                                                        'processing' => ['text' => 'בטיפול', 'class' => 'bg-purple-100 text-purple-800'],
                                                        'shipped' => ['text' => 'נשלחה', 'class' => 'bg-indigo-100 text-indigo-800'],
                                                        'delivered' => ['text' => 'נמסרה', 'class' => 'bg-green-100 text-green-800'],
                                                        'cancelled' => ['text' => 'בוטלה', 'class' => 'bg-red-100 text-red-800'],
                                                        'refunded' => ['text' => 'הוחזרה', 'class' => 'bg-gray-100 text-gray-800']
                                                    ];
                                                    $statusInfo = $statusConfig[$order['status']] ?? ['text' => $order['status'], 'class' => 'bg-gray-100 text-gray-800'];
                                                    ?>
                                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?= $statusInfo['class'] ?>">
                                                        <?= $statusInfo['text'] ?>
                                                    </span>
                                                </td>

                                                <!-- Payment Status -->
                                                <td class="py-4 px-4">
                                                    <?php 
                                                    $paymentConfig = [
                                                        'pending' => ['text' => 'ממתין', 'class' => 'bg-yellow-100 text-yellow-800'],
                                                        'paid' => ['text' => 'שולם', 'class' => 'bg-green-100 text-green-800'],
                                                        'failed' => ['text' => 'נכשל', 'class' => 'bg-red-100 text-red-800'],
                                                        'refunded' => ['text' => 'הוחזר', 'class' => 'bg-gray-100 text-gray-800'],
                                                        'partial' => ['text' => 'חלקי', 'class' => 'bg-orange-100 text-orange-800']
                                                    ];
                                                    $paymentInfo = $paymentConfig[$order['payment_status']] ?? ['text' => $order['payment_status'], 'class' => 'bg-gray-100 text-gray-800'];
                                                    ?>
                                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?= $paymentInfo['class'] ?>">
                                                        <?= $paymentInfo['text'] ?>
                                                    </span>
                                                </td>

                                                <!-- Items Count -->
                                                <td class="py-4 px-4">
                                                    <div class="text-sm text-gray-900"><?= $order['items_count'] ?> פריטים</div>
                                                    <div class="text-xs text-gray-500 truncate max-w-32" title="<?= htmlspecialchars($order['product_names'] ?: 'ללא מוצרים') ?>">
                                                        <?= htmlspecialchars($order['product_names'] ?: 'ללא מוצרים') ?>
                                                    </div>
                                                </td>

                                                <!-- Total Amount -->
                                                <td class="py-4 px-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        ₪<?= number_format($order['total_amount'], 2) ?>
                                                    </div>
                                                </td>

                                                <!-- Order Date -->
                                                <td class="py-4 px-4">
                                                    <div class="text-sm text-gray-900">
                                                        <?= date('d/m/Y', strtotime($order['order_date'])) ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        <?= date('H:i', strtotime($order['order_date'])) ?>
                                                    </div>
                                                </td>

                                                <!-- Actions -->
                                                <td class="py-4 px-4">
                                                    <div class="flex items-center gap-2">
                                                        <a href="/admin/orders/view.php?id=<?= $order['id'] ?>" 
                                                           class="text-blue-600 hover:text-blue-800 p-1" title="צפה בהזמנה">
                                                            <i class="ri-eye-line"></i>
                                                        </a>
                                                        <a href="/admin/orders/edit.php?id=<?= $order['id'] ?>" 
                                                           class="text-green-600 hover:text-green-800 p-1" title="עדכן הזמנה">
                                                            <i class="ri-edit-line"></i>
                                                        </a>
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
                                        <?php if ($page > 1): ?>
                                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                                               class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                                הקודם
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" 
                                               class="relative mr-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                                הבא
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm text-gray-700">
                                                מציג 
                                                <span class="font-medium"><?= $offset + 1 ?></span>
                                                עד 
                                                <span class="font-medium"><?= min($offset + $limit, $totalOrders) ?></span>
                                                מתוך 
                                                <span class="font-medium"><?= number_format($totalOrders) ?></span>
                                                תוצאות
                                            </p>
                                        </div>
                                        
                                        <div>
                                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                                <?php if ($page > 1): ?>
                                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                                                       class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                                        <i class="ri-arrow-right-s-line"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php
                                                $startPage = max(1, $page - 2);
                                                $endPage = min($totalPages, $page + 2);
                                                
                                                for ($i = $startPage; $i <= $endPage; $i++):
                                                ?>
                                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                                                       class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?= $i === $page ? 'bg-blue-600 text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0' ?>">
                                                        <?= $i ?>
                                                    </a>
                                                <?php endfor; ?>
                                                
                                                <?php if ($page < $totalPages): ?>
                                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" 
                                                       class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                                        <i class="ri-arrow-left-s-line"></i>
                                                    </a>
                                                <?php endif; ?>
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

<?php include '../templates/footer.php'; ?> 