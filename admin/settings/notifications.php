<?php
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = 'הגדרות התראות';
$currentPage = 'settings';

$db = Database::getInstance()->getConnection();
$currentStore = getCurrentStore();

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'update_notifications') {
            foreach ($_POST['notifications'] as $type => $settings) {
                $recipients = [];
                if (!empty($settings['email_recipients'])) {
                    $recipients['email'] = array_map('trim', explode(',', $settings['email_recipients']));
                }
                if (!empty($settings['sms_recipients'])) {
                    $recipients['sms'] = array_map('trim', explode(',', $settings['sms_recipients']));
                }
                if (!empty($settings['webhook_url'])) {
                    $recipients['webhook'] = $settings['webhook_url'];
                }
                
                $additionalSettings = [
                    'template' => $settings['template'] ?? '',
                    'delay_minutes' => $settings['delay_minutes'] ?? 0,
                    'conditions' => $settings['conditions'] ?? ''
                ];
                
                $stmt = $db->prepare("
                    INSERT INTO notification_settings (store_id, notification_type, is_enabled, email_enabled, sms_enabled, slack_enabled, webhook_enabled, recipients, settings)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    is_enabled = VALUES(is_enabled),
                    email_enabled = VALUES(email_enabled),
                    sms_enabled = VALUES(sms_enabled),
                    slack_enabled = VALUES(slack_enabled),
                    webhook_enabled = VALUES(webhook_enabled),
                    recipients = VALUES(recipients),
                    settings = VALUES(settings)
                ");
                
                $stmt->execute([
                    $currentStore['id'],
                    $type,
                    isset($settings['is_enabled']) ? 1 : 0,
                    isset($settings['email_enabled']) ? 1 : 0,
                    isset($settings['sms_enabled']) ? 1 : 0,
                    isset($settings['slack_enabled']) ? 1 : 0,
                    isset($settings['webhook_enabled']) ? 1 : 0,
                    json_encode($recipients),
                    json_encode($additionalSettings)
                ]);
            }
            
            $success = 'הגדרות התראות עודכנו בהצלחה!';
        }
    } catch (Exception $e) {
        $error = 'שגיאה: ' . $e->getMessage();
    }
}

// Get current notification settings
$stmt = $db->prepare("SELECT * FROM notification_settings WHERE store_id = ?");
$stmt->execute([$currentStore['id']]);
$currentSettings = [];
foreach ($stmt->fetchAll() as $setting) {
    $currentSettings[$setting['notification_type']] = $setting;
}

// Available notification types
$notificationTypes = [
    'new_order' => [
        'name' => 'הזמנה חדשה',
        'description' => 'התראה כאשר מתקבלת הזמנה חדשה',
        'icon' => 'ri-shopping-cart-line',
        'color' => 'text-green-600'
    ],
    'low_stock' => [
        'name' => 'מלאי נמוך',
        'description' => 'התראה כאשר מלאי מוצר מתחת לסף המינימלי',
        'icon' => 'ri-alert-line',
        'color' => 'text-orange-600'
    ],
    'order_shipped' => [
        'name' => 'הזמנה נשלחה',
        'description' => 'התראה כאשר הזמנה יוצאת למשלוח',
        'icon' => 'ri-truck-line',
        'color' => 'text-blue-600'
    ],
    'payment_received' => [
        'name' => 'תשלום התקבל',
        'description' => 'התראה כאשר מתקבל תשלום עבור הזמנה',
        'icon' => 'ri-money-dollar-circle-line',
        'color' => 'text-purple-600'
    ],
    'customer_registered' => [
        'name' => 'לקוח נרשם',
        'description' => 'התראה כאשר לקוח חדש נרשם לחנות',
        'icon' => 'ri-user-add-line',
        'color' => 'text-cyan-600'
    ]
];

include '../../admin/templates/header.php';
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../admin/templates/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php include '../../admin/templates/navbar.php'; ?>
        
        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center text-sm text-gray-500 mb-4">
                    <a href="../" class="hover:text-gray-700">ניהול</a>
                    <i class="ri-arrow-left-s-line mx-2"></i>
                    <a href="./" class="hover:text-gray-700">הגדרות</a>
                    <i class="ri-arrow-left-s-line mx-2"></i>
                    <span class="text-gray-900"><?php echo $pageTitle; ?></span>
                </div>
                
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900"><?php echo $pageTitle; ?></h1>
                        <p class="text-gray-600 mt-2">הגדרת התראות אימייל, SMS ווהוקים עבור אירועי החנות</p>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                    <i class="ri-check-line ml-2"></i><?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                    <i class="ri-error-warning-line ml-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Notification Settings Form -->
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="update_notifications">
                
                <?php foreach ($notificationTypes as $type => $info): ?>
                    <?php 
                    $settings = $currentSettings[$type] ?? null;
                    $recipients = $settings ? json_decode($settings['recipients'], true) : [];
                    $additionalSettings = $settings ? json_decode($settings['settings'], true) : [];
                    ?>
                    
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center">
                                    <i class="<?php echo $info['icon']; ?> <?php echo $info['color']; ?> text-3xl ml-4"></i>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900"><?php echo $info['name']; ?></h3>
                                        <p class="text-sm text-gray-500"><?php echo $info['description']; ?></p>
                                    </div>
                                </div>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" name="notifications[<?php echo $type; ?>][is_enabled]" 
                                           <?php echo ($settings && $settings['is_enabled']) ? 'checked' : ''; ?>
                                           class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded ml-3">
                                    <span class="text-sm font-medium text-gray-700">פעיל</span>
                                </label>
                            </div>
                            
                            <!-- Notification Channels -->
                            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                                <!-- Email -->
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="notifications[<?php echo $type; ?>][email_enabled]" 
                                               <?php echo ($settings && $settings['email_enabled']) ? 'checked' : ''; ?>
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded ml-3">
                                        <i class="ri-mail-line text-blue-600 ml-2"></i>
                                        <span class="text-sm font-medium text-gray-700">אימייל</span>
                                    </label>
                                    <textarea name="notifications[<?php echo $type; ?>][email_recipients]" 
                                              placeholder="email1@example.com, email2@example.com"
                                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                              rows="2"><?php echo htmlspecialchars(implode(', ', $recipients['email'] ?? [])); ?></textarea>
                                </div>
                                
                                <!-- SMS -->
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="notifications[<?php echo $type; ?>][sms_enabled]" 
                                               <?php echo ($settings && $settings['sms_enabled']) ? 'checked' : ''; ?>
                                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded ml-3">
                                        <i class="ri-message-2-line text-green-600 ml-2"></i>
                                        <span class="text-sm font-medium text-gray-700">SMS</span>
                                    </label>
                                    <textarea name="notifications[<?php echo $type; ?>][sms_recipients]" 
                                              placeholder="0501234567, 0507654321"
                                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                              rows="2"><?php echo htmlspecialchars(implode(', ', $recipients['sms'] ?? [])); ?></textarea>
                                </div>
                                
                                <!-- Slack -->
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="notifications[<?php echo $type; ?>][slack_enabled]" 
                                               <?php echo ($settings && $settings['slack_enabled']) ? 'checked' : ''; ?>
                                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded ml-3">
                                        <i class="ri-slack-line text-purple-600 ml-2"></i>
                                        <span class="text-sm font-medium text-gray-700">Slack</span>
                                    </label>
                                    <input type="text" name="notifications[<?php echo $type; ?>][slack_webhook]" 
                                           placeholder="https://hooks.slack.com/..."
                                           value="<?php echo htmlspecialchars($recipients['slack'] ?? ''); ?>"
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                </div>
                                
                                <!-- Webhook -->
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="notifications[<?php echo $type; ?>][webhook_enabled]" 
                                               <?php echo ($settings && $settings['webhook_enabled']) ? 'checked' : ''; ?>
                                               class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded ml-3">
                                        <i class="ri-webhook-line text-orange-600 ml-2"></i>
                                        <span class="text-sm font-medium text-gray-700">Webhook</span>
                                    </label>
                                    <input type="url" name="notifications[<?php echo $type; ?>][webhook_url]" 
                                           placeholder="https://your-webhook-url.com"
                                           value="<?php echo htmlspecialchars($recipients['webhook'] ?? ''); ?>"
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                </div>
                            </div>
                            
                            <!-- Advanced Settings -->
                            <div class="border-t pt-4">
                                <button type="button" onclick="toggleAdvanced('<?php echo $type; ?>')" 
                                        class="flex items-center text-sm text-gray-600 hover:text-gray-900 mb-3">
                                    <i class="ri-arrow-down-s-line ml-1 transition-transform" id="arrow-<?php echo $type; ?>"></i>
                                    הגדרות מתקדמות
                                </button>
                                
                                <div id="advanced-<?php echo $type; ?>" class="hidden space-y-4">
                                    <div class="grid md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">עיכוב (דקות)</label>
                                            <input type="number" name="notifications[<?php echo $type; ?>][delay_minutes]" 
                                                   value="<?php echo htmlspecialchars($additionalSettings['delay_minutes'] ?? '0'); ?>"
                                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">תבנית הודעה</label>
                                            <select name="notifications[<?php echo $type; ?>][template]" 
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <option value="default" <?php echo ($additionalSettings['template'] ?? '') === 'default' ? 'selected' : ''; ?>>ברירת מחדל</option>
                                                <option value="detailed" <?php echo ($additionalSettings['template'] ?? '') === 'detailed' ? 'selected' : ''; ?>>מפורט</option>
                                                <option value="minimal" <?php echo ($additionalSettings['template'] ?? '') === 'minimal' ? 'selected' : ''; ?>>מינימלי</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">תנאים נוספים</label>
                                        <textarea name="notifications[<?php echo $type; ?>][conditions]" 
                                                  placeholder="תנאים מיוחדים להפעלת ההתראה (אופציונלי)"
                                                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                  rows="2"><?php echo htmlspecialchars($additionalSettings['conditions'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Submit Button -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 px-6 rounded-xl font-medium transition-colors shadow-lg">
                        <i class="ri-save-line ml-2"></i>
                        שמור הגדרות התראות
                    </button>
                </div>
            </form>

            <!-- Test Notifications -->
            <div class="mt-8 bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="ri-test-tube-line ml-2"></i>
                    בדיקת התראות
                </h2>
                
                <p class="text-gray-600 mb-6">שלח התראת בדיקה כדי לוודא שההגדרות עובדות כראוי</p>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($notificationTypes as $type => $info): ?>
                        <button onclick="testNotification('<?php echo $type; ?>')" 
                                class="flex items-center justify-center p-4 border-2 border-gray-200 rounded-xl hover:border-blue-300 hover:bg-blue-50 transition-colors">
                            <i class="<?php echo $info['icon']; ?> <?php echo $info['color']; ?> text-xl ml-2"></i>
                            <span class="text-sm font-medium text-gray-700">בדוק <?php echo $info['name']; ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Summary -->
            <div class="mt-8 bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl border border-blue-200 p-6">
                <div class="grid md:grid-cols-4 gap-6 text-center">
                    <div>
                        <i class="ri-notification-3-line text-3xl text-blue-600 mb-2"></i>
                        <h3 class="font-semibold text-gray-900">התראות פעילות</h3>
                        <p class="text-2xl font-bold text-blue-600">
                            <?php echo count(array_filter($currentSettings, function($s) { return $s['is_enabled']; })); ?>
                        </p>
                    </div>
                    
                    <div>
                        <i class="ri-mail-line text-3xl text-green-600 mb-2"></i>
                        <h3 class="font-semibold text-gray-900">אימייל מופעל</h3>
                        <p class="text-2xl font-bold text-green-600">
                            <?php echo count(array_filter($currentSettings, function($s) { return $s['email_enabled']; })); ?>
                        </p>
                    </div>
                    
                    <div>
                        <i class="ri-message-2-line text-3xl text-orange-600 mb-2"></i>
                        <h3 class="font-semibold text-gray-900">SMS מופעל</h3>
                        <p class="text-2xl font-bold text-orange-600">
                            <?php echo count(array_filter($currentSettings, function($s) { return $s['sms_enabled']; })); ?>
                        </p>
                    </div>
                    
                    <div>
                        <i class="ri-webhook-line text-3xl text-purple-600 mb-2"></i>
                        <h3 class="font-semibold text-gray-900">Webhooks מופעלים</h3>
                        <p class="text-2xl font-bold text-purple-600">
                            <?php echo count(array_filter($currentSettings, function($s) { return $s['webhook_enabled']; })); ?>
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function toggleAdvanced(type) {
    const advanced = document.getElementById(`advanced-${type}`);
    const arrow = document.getElementById(`arrow-${type}`);
    
    if (advanced.classList.contains('hidden')) {
        advanced.classList.remove('hidden');
        arrow.style.transform = 'rotate(180deg)';
    } else {
        advanced.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
    }
}

function testNotification(type) {
    // This would typically send an AJAX request to test the notification
    alert(`בדיקת התראה עבור "${type}" - פונקציונליות זו תיושם בהמשך`);
}

// Auto-save functionality (optional)
let saveTimeout;
document.querySelectorAll('input, textarea, select').forEach(element => {
    element.addEventListener('change', function() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            // Auto-save indication
            const indicator = document.createElement('div');
            indicator.className = 'fixed top-4 left-4 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm z-50';
            indicator.textContent = 'שומר שינויים...';
            document.body.appendChild(indicator);
            
            setTimeout(() => {
                indicator.remove();
            }, 2000);
        }, 1000);
    });
});
</script>

<?php include '../../admin/templates/footer.php'; ?> 