<?php

class OrderStatusManager {
    private $db;
    
    public function __construct($database = null) {
        if ($database) {
            $this->db = $database;
        } else {
            require_once __DIR__ . '/../config/database.php';
            $database = Database::getInstance();
            $this->db = $database->getConnection();
        }
    }

    /**
     * יצירת סטטוסי ברירת מחדל לחנות חדשה
     */
    public function createDefaultStatuses($storeId) {
        $this->createDefaultOrderStatuses($storeId);
        $this->createDefaultPaymentStatuses($storeId);
    }

    /**
     * יצירת סטטוסי הזמנה ברירת מחדל
     */
    private function createDefaultOrderStatuses($storeId) {
        $defaultStatuses = [
            [
                'name' => 'pending',
                'slug' => 'pending',
                'display_name' => 'ממתינה',
                'description' => 'הזמנה חדשה שממתינה לטיפול',
                'color' => '#F59E0B',
                'background_color' => '#FEF3C7',
                'icon' => 'ri-time-line',
                'is_default' => true,
                'is_system' => true,
                'sort_order' => 1,
                'allow_edit' => true,
                'send_email_notification' => true,
                'reduce_stock' => false
            ],
            [
                'name' => 'confirmed',
                'slug' => 'confirmed',
                'display_name' => 'אושרה',
                'description' => 'הזמנה אושרה ומוכנה לטיפול',
                'color' => '#3B82F6',
                'background_color' => '#DBEAFE',
                'icon' => 'ri-check-line',
                'is_default' => false,
                'is_system' => true,
                'sort_order' => 2,
                'allow_edit' => true,
                'send_email_notification' => true,
                'reduce_stock' => true
            ],
            [
                'name' => 'processing',
                'slug' => 'processing',
                'display_name' => 'בטיפול',
                'description' => 'הזמנה בתהליך הכנה ואריזה',
                'color' => '#8B5CF6',
                'background_color' => '#EDE9FE',
                'icon' => 'ri-settings-3-line',
                'is_default' => false,
                'is_system' => true,
                'sort_order' => 3,
                'allow_edit' => true,
                'send_email_notification' => true,
                'reduce_stock' => false
            ],
            [
                'name' => 'shipped',
                'slug' => 'shipped',
                'display_name' => 'נשלחה',
                'description' => 'הזמנה נשלחה ובדרך ללקוח',
                'color' => '#6366F1',
                'background_color' => '#E0E7FF',
                'icon' => 'ri-truck-line',
                'is_default' => false,
                'is_system' => true,
                'sort_order' => 4,
                'allow_edit' => false,
                'send_email_notification' => true,
                'reduce_stock' => false
            ],
            [
                'name' => 'delivered',
                'slug' => 'delivered',
                'display_name' => 'נמסרה',
                'description' => 'הזמנה נמסרה בהצלחה ללקוח',
                'color' => '#10B981',
                'background_color' => '#D1FAE5',
                'icon' => 'ri-check-double-line',
                'is_default' => false,
                'is_system' => true,
                'sort_order' => 5,
                'allow_edit' => false,
                'send_email_notification' => true,
                'reduce_stock' => false
            ],
            [
                'name' => 'cancelled',
                'slug' => 'cancelled',
                'display_name' => 'בוטלה',
                'description' => 'הזמנה בוטלה',
                'color' => '#EF4444',
                'background_color' => '#FEE2E2',
                'icon' => 'ri-close-circle-line',
                'is_default' => false,
                'is_system' => true,
                'sort_order' => 6,
                'allow_edit' => false,
                'send_email_notification' => true,
                'release_stock' => true
            ],
            [
                'name' => 'refunded',
                'slug' => 'refunded',
                'display_name' => 'הוחזרה',
                'description' => 'הזמנה הוחזרה ובוצע החזר כספי',
                'color' => '#6B7280',
                'background_color' => '#F3F4F6',
                'icon' => 'ri-refund-line',
                'is_default' => false,
                'is_system' => true,
                'sort_order' => 7,
                'allow_edit' => false,
                'send_email_notification' => true,
                'release_stock' => true
            ]
        ];

        foreach ($defaultStatuses as $status) {
            $this->createOrderStatus($storeId, $status);
        }
    }

    /**
     * יצירת סטטוסי תשלום ברירת מחדל
     */
    private function createDefaultPaymentStatuses($storeId) {
        $defaultStatuses = [
            [
                'name' => 'pending',
                'slug' => 'pending',
                'display_name' => 'ממתין לתשלום',
                'description' => 'תשלום עדיין לא בוצע',
                'color' => '#F59E0B',
                'background_color' => '#FEF3C7',
                'icon' => 'ri-time-line',
                'is_default' => true,
                'is_system' => true,
                'sort_order' => 1,
                'is_paid' => false,
                'allow_refund' => false,
                'auto_fulfill' => false
            ],
            [
                'name' => 'paid',
                'slug' => 'paid',
                'display_name' => 'שולם',
                'description' => 'תשלום בוצע בהצלחה',
                'color' => '#10B981',
                'background_color' => '#D1FAE5',
                'icon' => 'ri-check-line',
                'is_default' => false,
                'is_system' => true,
                'sort_order' => 2,
                'is_paid' => true,
                'allow_refund' => true,
                'auto_fulfill' => true
            ],
            [
                'name' => 'failed',
                'slug' => 'failed',
                'display_name' => 'נכשל',
                'description' => 'תשלום נכשל',
                'color' => '#EF4444',
                'background_color' => '#FEE2E2',
                'icon' => 'ri-close-line',
                'is_default' => false,
                'is_system' => true,
                'sort_order' => 3,
                'is_paid' => false,
                'allow_refund' => false,
                'auto_fulfill' => false
            ],
            [
                'name' => 'refunded',
                'slug' => 'refunded',
                'display_name' => 'הוחזר',
                'description' => 'בוצע החזר כספי',
                'color' => '#6B7280',
                'background_color' => '#F3F4F6',
                'icon' => 'ri-refund-line',
                'is_default' => false,
                'is_system' => true,
                'sort_order' => 4,
                'is_paid' => false,
                'allow_refund' => false,
                'auto_fulfill' => false
            ],
            [
                'name' => 'partial',
                'slug' => 'partial',
                'display_name' => 'חלקי',
                'description' => 'תשלום חלקי בוצע',
                'color' => '#F97316',
                'background_color' => '#FED7AA',
                'icon' => 'ri-funds-line',
                'is_default' => false,
                'is_system' => true,
                'sort_order' => 5,
                'is_paid' => false,
                'allow_refund' => true,
                'auto_fulfill' => false
            ]
        ];

        foreach ($defaultStatuses as $status) {
            $this->createPaymentStatus($storeId, $status);
        }
    }

    /**
     * יצירת סטטוס הזמנה חדש
     */
    public function createOrderStatus($storeId, $data) {
        $stmt = $this->db->prepare("
            INSERT INTO order_statuses (
                store_id, name, slug, display_name, description, color, background_color, 
                icon, is_default, is_system, is_active, sort_order, allow_edit, 
                auto_complete_payment, send_email_notification, send_sms_notification,
                reduce_stock, release_stock
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $storeId,
            $data['name'],
            $data['slug'],
            $data['display_name'],
            $data['description'] ?? '',
            $data['color'],
            $data['background_color'],
            $data['icon'],
            ($data['is_default'] ?? false) ? 1 : 0,
            ($data['is_system'] ?? false) ? 1 : 0,
            ($data['is_active'] ?? true) ? 1 : 0,
            $data['sort_order'] ?? 0,
            ($data['allow_edit'] ?? true) ? 1 : 0,
            ($data['auto_complete_payment'] ?? false) ? 1 : 0,
            ($data['send_email_notification'] ?? true) ? 1 : 0,
            ($data['send_sms_notification'] ?? false) ? 1 : 0,
            ($data['reduce_stock'] ?? false) ? 1 : 0,
            ($data['release_stock'] ?? false) ? 1 : 0
        ]);
    }

    /**
     * יצירת סטטוס תשלום חדש
     */
    public function createPaymentStatus($storeId, $data) {
        $stmt = $this->db->prepare("
            INSERT INTO payment_statuses (
                store_id, name, slug, display_name, description, color, background_color,
                icon, is_default, is_system, is_active, sort_order, is_paid, allow_refund, auto_fulfill
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $storeId,
            $data['name'],
            $data['slug'],
            $data['display_name'],
            $data['description'] ?? '',
            $data['color'],
            $data['background_color'],
            $data['icon'],
            ($data['is_default'] ?? false) ? 1 : 0,
            ($data['is_system'] ?? false) ? 1 : 0,
            ($data['is_active'] ?? true) ? 1 : 0,
            $data['sort_order'] ?? 0,
            ($data['is_paid'] ?? false) ? 1 : 0,
            ($data['allow_refund'] ?? false) ? 1 : 0,
            ($data['auto_fulfill'] ?? false) ? 1 : 0
        ]);
    }

    /**
     * קבלת סטטוסי הזמנה לחנות
     */
    public function getOrderStatuses($storeId, $activeOnly = true) {
        $sql = "SELECT * FROM order_statuses WHERE store_id = ?";
        $params = [$storeId];

        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }

        $sql .= " ORDER BY sort_order ASC, id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * קבלת סטטוסי תשלום לחנות
     */
    public function getPaymentStatuses($storeId, $activeOnly = true) {
        $sql = "SELECT * FROM payment_statuses WHERE store_id = ?";
        $params = [$storeId];

        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }

        $sql .= " ORDER BY sort_order ASC, id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * קבלת סטטוס הזמנה לפי ID
     */
    public function getOrderStatus($storeId, $statusId) {
        $stmt = $this->db->prepare("
            SELECT * FROM order_statuses 
            WHERE id = ? AND store_id = ?
        ");
        $stmt->execute([$statusId, $storeId]);
        return $stmt->fetch();
    }

    /**
     * קבלת סטטוס תשלום לפי ID
     */
    public function getPaymentStatus($storeId, $statusId) {
        $stmt = $this->db->prepare("
            SELECT * FROM payment_statuses 
            WHERE id = ? AND store_id = ?
        ");
        $stmt->execute([$statusId, $storeId]);
        return $stmt->fetch();
    }

    /**
     * עדכון סטטוס הזמנה
     */
    public function updateOrderStatus($storeId, $statusId, $data) {
        // בדיקה שהסטטוס שייך לחנות ולא מערכתי
        $status = $this->getOrderStatus($storeId, $statusId);
        if (!$status || $status['is_system']) {
            return false;
        }

        $updateFields = [];
        $params = [];

        $allowedFields = [
            'display_name', 'description', 'color', 'background_color', 'icon',
            'is_active', 'sort_order', 'allow_edit', 'auto_complete_payment',
            'send_email_notification', 'send_sms_notification', 'reduce_stock', 'release_stock'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updateFields)) {
            return false;
        }

        $params[] = $statusId;
        $params[] = $storeId;

        $sql = "UPDATE order_statuses SET " . implode(', ', $updateFields) . " WHERE id = ? AND store_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * עדכון סטטוס תשלום
     */
    public function updatePaymentStatus($storeId, $statusId, $data) {
        // בדיקה שהסטטוס שייך לחנות ולא מערכתי
        $status = $this->getPaymentStatus($storeId, $statusId);
        if (!$status || $status['is_system']) {
            return false;
        }

        $updateFields = [];
        $params = [];

        $allowedFields = [
            'display_name', 'description', 'color', 'background_color', 'icon',
            'is_active', 'sort_order', 'is_paid', 'allow_refund', 'auto_fulfill'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updateFields)) {
            return false;
        }

        $params[] = $statusId;
        $params[] = $storeId;

        $sql = "UPDATE payment_statuses SET " . implode(', ', $updateFields) . " WHERE id = ? AND store_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * מחיקת סטטוס הזמנה (רק לא מערכתיים)
     */
    public function deleteOrderStatus($storeId, $statusId) {
        $stmt = $this->db->prepare("
            DELETE FROM order_statuses 
            WHERE id = ? AND store_id = ? AND is_system = 0
        ");
        return $stmt->execute([$statusId, $storeId]);
    }

    /**
     * מחיקת סטטוס תשלום (רק לא מערכתיים)
     */
    public function deletePaymentStatus($storeId, $statusId) {
        $stmt = $this->db->prepare("
            DELETE FROM payment_statuses 
            WHERE id = ? AND store_id = ? AND is_system = 0
        ");
        return $stmt->execute([$statusId, $storeId]);
    }

    /**
     * קבלת סטטוס ברירת מחדל
     */
    public function getDefaultOrderStatus($storeId) {
        $stmt = $this->db->prepare("
            SELECT * FROM order_statuses 
            WHERE store_id = ? AND is_default = 1 
            LIMIT 1
        ");
        $stmt->execute([$storeId]);
        return $stmt->fetch();
    }

    /**
     * קבלת סטטוס תשלום ברירת מחדל
     */
    public function getDefaultPaymentStatus($storeId) {
        $stmt = $this->db->prepare("
            SELECT * FROM payment_statuses 
            WHERE store_id = ? AND is_default = 1 
            LIMIT 1
        ");
        $stmt->execute([$storeId]);
        return $stmt->fetch();
    }
}
?> 