<?php
/**
 * דוגמה לשימוש ב-API של סטטוסים מותאמים אישית
 */

require_once 'includes/OrderStatusManager.php';
require_once 'config/database.php';

echo "=== דוגמה לשימוש במערכת הסטטוסים המותאמים ===\n\n";

try {
    $statusManager = new OrderStatusManager();
    $storeId = 1; // ID של החנות
    
    echo "1. קבלת סטטוסי הזמנה קיימים:\n";
    $orderStatuses = $statusManager->getOrderStatuses($storeId);
    foreach ($orderStatuses as $status) {
        echo "   - {$status['display_name']} ({$status['name']}) - צבע: {$status['color']}\n";
    }
    
    echo "\n2. הוספת סטטוס הזמנה מותאם אישית:\n";
    $customOrderStatus = [
        'name' => 'urgent_processing',
        'slug' => 'urgent_processing',
        'display_name' => 'טיפול דחוף',
        'description' => 'הזמנות הדורשות טיפול מיידי ודחוף',
        'color' => '#FF5722',
        'background_color' => '#FFEBEE',
        'icon' => 'ri-alarm-warning-line',
        'is_active' => true,
        'sort_order' => 999,
        'allow_edit' => true,
        'send_email_notification' => true,
        'send_sms_notification' => true, // שליחת SMS לסטטוס דחוף
        'reduce_stock' => true
    ];
    
    $result = $statusManager->createOrderStatus($storeId, $customOrderStatus);
    if ($result) {
        echo "   ✓ סטטוס 'טיפול דחוף' נוצר בהצלחה!\n";
    } else {
        echo "   ✗ שגיאה ביצירת הסטטוס\n";
    }
    
    echo "\n3. הוספת סטטוס תשלום מותאם אישית:\n";
    $customPaymentStatus = [
        'name' => 'installments',
        'slug' => 'installments',
        'display_name' => 'תשלומים',
        'description' => 'תשלום בתשלומים עם כרטיס אשראי',
        'color' => '#FF9800',
        'background_color' => '#FFF3E0',
        'icon' => 'ri-bank-card-line',
        'is_active' => true,
        'sort_order' => 999,
        'is_paid' => true, // נחשב כמו תשלום מושלם
        'allow_refund' => true,
        'auto_fulfill' => true
    ];
    
    $result = $statusManager->createPaymentStatus($storeId, $customPaymentStatus);
    if ($result) {
        echo "   ✓ סטטוס תשלום 'תשלומים' נוצר בהצלחה!\n";
    } else {
        echo "   ✗ שגיאה ביצירת סטטוס התשלום\n";
    }
    
    echo "\n4. קבלת סטטוסי תשלום עם הסטטוס החדש:\n";
    $paymentStatuses = $statusManager->getPaymentStatuses($storeId);
    foreach ($paymentStatuses as $status) {
        $paidText = $status['is_paid'] ? ' (שולם)' : '';
        echo "   - {$status['display_name']} ({$status['name']}){$paidText}\n";
    }
    
    echo "\n=== הדגמת שימוש ב-API ===\n";
    echo "כדי להשתמש ב-API מהקליינט, שלח בקשות ל:\n";
    echo "GET    /admin/api/order-statuses.php?type=order     - קבלת סטטוסי הזמנות\n";
    echo "GET    /admin/api/order-statuses.php?type=payment   - קבלת סטטוסי תשלום\n";
    echo "POST   /admin/api/order-statuses.php               - יצירת סטטוס חדש\n";
    echo "PUT    /admin/api/order-statuses.php               - עדכון סטטוס קיים\n";
    echo "DELETE /admin/api/order-statuses.php               - מחיקת סטטוס\n";
    
    echo "\nדוגמת JSON ליצירת סטטוס חדש:\n";
    echo json_encode([
        'type' => 'order',
        'name' => 'express_delivery',
        'display_name' => 'משלוח מהיר',
        'color' => '#4CAF50',
        'description' => 'משלוח מהיר תוך 24 שעות',
        'send_sms_notification' => true
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    echo "\n\n✅ כל הדוגמאות בוצעו בהצלחה!\n";
    echo "כעת תוכל לגשת לממשק הניהול ב: /admin/settings/statuses.php\n";
    
} catch (Exception $e) {
    echo "❌ שגיאה: " . $e->getMessage() . "\n";
}
?> 