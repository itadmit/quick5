<?php
/**
 * עריכת הזמנה - QuickShop5 Admin
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

// עיבוד טופס
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // קבלת נתונים
    $status = $_POST['status'] ?? '';
    $payment_status = $_POST['payment_status'] ?? '';
    $tracking_number = $_POST['tracking_number'] ?? '';
    $internal_notes = $_POST['internal_notes'] ?? '';
    $shipping_address = $_POST['shipping_address'] ?? '';
    
    // וידוא נתונים
    $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
    $validPaymentStatuses = ['pending', 'paid', 'failed', 'refunded', 'partial'];
    
    if (!in_array($status, $validStatuses)) {
        $errors[] = 'סטטוס לא תקין';
    }
    
    if (!in_array($payment_status, $validPaymentStatuses)) {
        $errors[] = 'סטטוס תשלום לא תקין';
    }
    
    if (empty($errors)) {
        try {
            $db->beginTransaction();
            
            // עדכון ההזמנה
            $updateFields = [];
            $updateParams = [];
            
            if ($status !== $order['status']) {
                $updateFields[] = 'status = ?';
                $updateParams[] = $status;
                
                // הוספה להיסטוריית סטטוס
                $stmt = $db->prepare("
                    INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, notes)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $orderId,
                    $order['status'],
                    $status,
                    $currentUser['first_name'] . ' ' . $currentUser['last_name'],
                    'עודכן מממשק הניהול'
                ]);
                
                // עדכון תאריכי משלוח/מסירה
                if ($status === 'shipped' && !$order['shipped_at']) {
                    $updateFields[] = 'shipped_at = NOW()';
                }
                if ($status === 'delivered' && !$order['delivered_at']) {
                    $updateFields[] = 'delivered_at = NOW()';
                }
            }
            
            if ($payment_status !== $order['payment_status']) {
                $updateFields[] = 'payment_status = ?';
                $updateParams[] = $payment_status;
            }
            
            if ($tracking_number !== $order['tracking_number']) {
                $updateFields[] = 'tracking_number = ?';
                $updateParams[] = $tracking_number;
            }
            
            if ($internal_notes !== $order['internal_notes']) {
                $updateFields[] = 'internal_notes = ?';
                $updateParams[] = $internal_notes;
            }
            
            if ($shipping_address !== $order['shipping_address']) {
                $updateFields[] = 'shipping_address = ?';
                $updateParams[] = $shipping_address;
            }
            
            if (!empty($updateFields)) {
                $updateFields[] = 'updated_at = NOW()';
                $updateParams[] = $orderId;
                $updateParams[] = $store['id'];
                
                $sql = "UPDATE orders SET " . implode(', ', $updateFields) . " WHERE id = ? AND store_id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute($updateParams);
            }
            
            $db->commit();
            
            header('Location: /admin/orders/view.php?id=' . $orderId . '&updated=1');
            exit;
            
        } catch (Exception $e) {
            $db->rollback();
            $errors[] = 'שגיאה בעדכון ההזמנה: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'עריכת הזמנה #' . $order['order_number'];
?>

<?php include '../templates/header.php'; ?>
    
<?php include '../templates/sidebar.php'; ?>

<!-- Main Content -->
<div class="lg:pr-64">
    
    <?php include '../templates/navbar.php'; ?>

    <!-- Edit Order Content -->
    <main class="py-8" style="background: #e9f0f3;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="mb-8">
                <div class="bg-white shadow-xl card-rounded p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <a href="/admin/orders/view.php?id=<?= $order['id'] ?>" class="text-gray-500 hover:text-gray-700">
                            <i class="ri-arrow-right-line"></i>
                        </a>
                        <h1 class="text-2xl font-bold text-gray-900">עריכת הזמנה #<?= htmlspecialchars($order['order_number']) ?></h1>
                    </div>
                    <p class="text-gray-600">הוזמן ב-<?= date('d/m/Y בשעה H:i', strtotime($order['order_date'])) ?></p>
                </div>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <i class="ri-error-warning-line text-red-500 ml-3"></i>
                        <span class="text-red-700 font-medium">נמצאו שגיאות:</span>
                    </div>
                    <ul class="text-red-700 text-sm space-y-1">
                        <?php foreach ($errors as $error): ?>
                            <li>• <?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Edit Form -->
            <form method="POST" class="space-y-6">
                
                <!-- Order Status -->
                <div class="bg-white shadow-xl card-rounded p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">סטטוס הזמנה</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                סטטוס הזמנה
                            </label>
                            <select name="status" id="status" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>ממתינה</option>
                                <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>אושרה</option>
                                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>בטיפול</option>
                                <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>נשלחה</option>
                                <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>נמסרה</option>
                                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>בוטלה</option>
                                <option value="refunded" <?= $order['status'] === 'refunded' ? 'selected' : '' ?>>הוחזרה</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-2">
                                סטטוס תשלום
                            </label>
                            <select name="payment_status" id="payment_status" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="pending" <?= $order['payment_status'] === 'pending' ? 'selected' : '' ?>>ממתין</option>
                                <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>שולם</option>
                                <option value="failed" <?= $order['payment_status'] === 'failed' ? 'selected' : '' ?>>נכשל</option>
                                <option value="refunded" <?= $order['payment_status'] === 'refunded' ? 'selected' : '' ?>>הוחזר</option>
                                <option value="partial" <?= $order['payment_status'] === 'partial' ? 'selected' : '' ?>>חלקי</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Shipping Info -->
                <div class="bg-white shadow-xl card-rounded p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">פרטי משלוח</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="tracking_number" class="block text-sm font-medium text-gray-700 mb-2">
                                מספר מעקב
                            </label>
                            <input type="text" 
                                   name="tracking_number" 
                                   id="tracking_number"
                                   value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>"
                                   placeholder="הזן מספר מעקב..."
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-2">
                                כתובת משלוח
                            </label>
                            <textarea name="shipping_address" 
                                      id="shipping_address"
                                      rows="3"
                                      placeholder="הזן כתובת משלוח..."
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($order['shipping_address'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Internal Notes -->
                <div class="bg-white shadow-xl card-rounded p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">הערות פנימיות</h3>
                    
                    <div>
                        <textarea name="internal_notes" 
                                  id="internal_notes"
                                  rows="4"
                                  placeholder="הערות לצוות (לא נראות ללקוח)..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($order['internal_notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Customer Info (Read Only) -->
                <div class="bg-white shadow-xl card-rounded p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">פרטי לקוח</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">שם מלא</label>
                            <div class="text-gray-900"><?= htmlspecialchars($order['customer_name']) ?></div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">אימייל</label>
                            <div class="text-gray-900"><?= htmlspecialchars($order['customer_email']) ?></div>
                        </div>
                        
                        <?php if ($order['customer_phone']): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">טלפון</label>
                                <div class="text-gray-900"><?= htmlspecialchars($order['customer_phone']) ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">סכום הזמנה</label>
                            <div class="text-gray-900 font-medium">₪<?= number_format($order['total_amount'], 2) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between">
                    <a href="/admin/orders/view.php?id=<?= $order['id'] ?>" 
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-xl font-medium transition-colors">
                        ביטול
                    </a>
                    
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl font-medium transition-colors flex items-center gap-2">
                        <i class="ri-save-line"></i>
                        שמור שינויים
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<?php include '../templates/footer.php'; ?> 