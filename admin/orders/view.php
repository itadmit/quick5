<?php
/**
 * צפייה בהזמנה - QuickShop5 Admin
 */

require_once __DIR__ . '/../../includes/auth.php';

$auth = new Authentication();
$auth->requireLogin();

$currentUser = $auth->getCurrentUser();

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

if (!$store) {
    header('Location: /admin/');
    exit;
}

// קבלת מזהה ההזמנה
$orderId = (int)($_GET['id'] ?? 0);

if (!$orderId) {
    header('Location: /admin/orders/?error=invalid_order');
    exit;
}

// קבלת פרטי ההזמנה
$stmt = $db->prepare("
    SELECT * FROM orders 
    WHERE id = ? AND store_id = ?
");
$stmt->execute([$orderId, $store['id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: /admin/orders/?error=order_not_found');
    exit;
}

// קבלת פריטי ההזמנה
$stmt = $db->prepare("
    SELECT oi.*, p.name as product_name, p.slug as product_slug
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
    ORDER BY oi.id ASC
");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll();

// קבלת היסטוריית סטטוס
$stmt = $db->prepare("
    SELECT * FROM order_status_history 
    WHERE order_id = ?
    ORDER BY changed_at DESC
");
$stmt->execute([$orderId]);
$statusHistory = $stmt->fetchAll();

$pageTitle = 'הזמנה #' . $order['order_number'];
?>

<?php include '../templates/header.php'; ?>
    
<?php include '../templates/sidebar.php'; ?>

<!-- Main Content -->
<div class="lg:pr-64">
    
    <?php include '../templates/navbar.php'; ?>

    <!-- Order Details Content -->
    <main class="py-8" style="background: #e9f0f3;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="mb-8">
                <div class="bg-white shadow-xl card-rounded p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <a href="/admin/orders/" class="text-gray-500 hover:text-gray-700">
                                    <i class="ri-arrow-right-line"></i>
                                </a>
                                <h1 class="text-2xl font-bold text-gray-900">הזמנה #<?= htmlspecialchars($order['order_number']) ?></h1>
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
                                <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full <?= $statusInfo['class'] ?>">
                                    <?= $statusInfo['text'] ?>
                                </span>
                            </div>
                            <p class="text-gray-600">הוזמן ב-<?= date('d/m/Y בשעה H:i', strtotime($order['order_date'])) ?></p>
                        </div>
                        <div class="flex items-center gap-3 mt-4 lg:mt-0">
                            <a href="/admin/orders/edit.php?id=<?= $order['id'] ?>" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-medium transition-colors flex items-center gap-2">
                                <i class="ri-edit-line"></i>
                                עדכן הזמנה
                            </a>
                            <button onclick="window.print()" 
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl font-medium transition-colors flex items-center gap-2">
                                <i class="ri-printer-line"></i>
                                הדפס
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Order Details -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Order Items -->
                    <div class="bg-white shadow-xl card-rounded p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">פריטי ההזמנה</h3>
                        
                        <div class="space-y-4">
                            <?php foreach ($orderItems as $item): ?>
                                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <i class="ri-shopping-bag-line text-gray-400"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">
                                                <?= htmlspecialchars($item['product_name']) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                SKU: <?= htmlspecialchars($item['product_sku'] ?: 'ללא') ?>
                                            </div>
                                            <?php if ($item['variant_attributes']): ?>
                                                <div class="text-sm text-gray-500">
                                                    <?= htmlspecialchars($item['variant_attributes']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="text-left">
                                        <div class="font-medium text-gray-900">
                                            ₪<?= number_format($item['total_price'], 2) ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= $item['quantity'] ?> × ₪<?= number_format($item['unit_price'], 2) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Order Summary -->
                        <div class="border-t border-gray-200 mt-6 pt-6">
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">סכום ביניים:</span>
                                    <span class="text-gray-900">₪<?= number_format($order['subtotal'], 2) ?></span>
                                </div>
                                <?php if ($order['discount_amount'] > 0): ?>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">הנחה:</span>
                                        <span class="text-red-600">-₪<?= number_format($order['discount_amount'], 2) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($order['shipping_amount'] > 0): ?>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">משלוח:</span>
                                        <span class="text-gray-900">₪<?= number_format($order['shipping_amount'], 2) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($order['tax_amount'] > 0): ?>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">מע"מ:</span>
                                        <span class="text-gray-900">₪<?= number_format($order['tax_amount'], 2) ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="flex justify-between text-lg font-medium border-t border-gray-200 pt-2">
                                    <span class="text-gray-900">סה"כ לתשלום:</span>
                                    <span class="text-gray-900">₪<?= number_format($order['total_amount'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status History -->
                    <?php if (!empty($statusHistory)): ?>
                        <div class="bg-white shadow-xl card-rounded p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">היסטוריית סטטוס</h3>
                            
                            <div class="space-y-3">
                                <?php foreach ($statusHistory as $history): ?>
                                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="ri-time-line text-blue-600 text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900">
                                                סטטוס שונה ל: <?= $statusConfig[$history['new_status']]['text'] ?? $history['new_status'] ?>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <?= date('d/m/Y H:i', strtotime($history['changed_at'])) ?>
                                                <?php if ($history['changed_by']): ?>
                                                    על ידי <?= htmlspecialchars($history['changed_by']) ?>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($history['notes']): ?>
                                                <div class="text-sm text-gray-600 mt-1">
                                                    <?= htmlspecialchars($history['notes']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    
                    <!-- Customer Info -->
                    <div class="bg-white shadow-xl card-rounded p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">פרטי לקוח</h3>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm font-medium text-gray-500">שם מלא</label>
                                <div class="text-gray-900"><?= htmlspecialchars($order['customer_name']) ?></div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500">אימייל</label>
                                <div class="text-gray-900">
                                    <a href="mailto:<?= htmlspecialchars($order['customer_email']) ?>" 
                                       class="text-blue-600 hover:text-blue-800">
                                        <?= htmlspecialchars($order['customer_email']) ?>
                                    </a>
                                </div>
                            </div>
                            
                            <?php if ($order['customer_phone']): ?>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">טלפון</label>
                                    <div class="text-gray-900">
                                        <a href="tel:<?= htmlspecialchars($order['customer_phone']) ?>" 
                                           class="text-blue-600 hover:text-blue-800">
                                            <?= htmlspecialchars($order['customer_phone']) ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div class="bg-white shadow-xl card-rounded p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">פרטי תשלום</h3>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm font-medium text-gray-500">סטטוס תשלום</label>
                                <div>
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
                                </div>
                            </div>
                            
                            <?php if ($order['payment_method']): ?>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">אמצעי תשלום</label>
                                    <div class="text-gray-900"><?= htmlspecialchars($order['payment_method']) ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($order['payment_id']): ?>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">מזהה תשלום</label>
                                    <div class="text-gray-900 text-sm font-mono"><?= htmlspecialchars($order['payment_id']) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Shipping Info -->
                    <div class="bg-white shadow-xl card-rounded p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">פרטי משלוח</h3>
                        
                        <div class="space-y-3">
                            <?php if ($order['shipping_method']): ?>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">אמצעי משלוח</label>
                                    <div class="text-gray-900"><?= htmlspecialchars($order['shipping_method']) ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($order['tracking_number']): ?>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">מספר מעקב</label>
                                    <div class="text-gray-900 text-sm font-mono"><?= htmlspecialchars($order['tracking_number']) ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($order['shipping_address']): ?>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">כתובת משלוח</label>
                                    <div class="text-gray-900 whitespace-pre-line"><?= htmlspecialchars($order['shipping_address']) ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($order['shipped_at']): ?>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">נשלח ב</label>
                                    <div class="text-gray-900"><?= date('d/m/Y H:i', strtotime($order['shipped_at'])) ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($order['delivered_at']): ?>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">נמסר ב</label>
                                    <div class="text-gray-900"><?= date('d/m/Y H:i', strtotime($order['delivered_at'])) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Notes -->
                    <?php if ($order['notes'] || $order['internal_notes']): ?>
                        <div class="bg-white shadow-xl card-rounded p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">הערות</h3>
                            
                            <div class="space-y-3">
                                <?php if ($order['notes']): ?>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">הערות לקוח</label>
                                        <div class="text-gray-900 whitespace-pre-line"><?= htmlspecialchars($order['notes']) ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($order['internal_notes']): ?>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">הערות פנימיות</label>
                                        <div class="text-gray-900 whitespace-pre-line"><?= htmlspecialchars($order['internal_notes']) ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../templates/footer.php'; ?> 