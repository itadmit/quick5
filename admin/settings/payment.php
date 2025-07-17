<?php
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = 'הגדרות תשלום';
$currentPage = 'settings';

$db = Database::getInstance()->getConnection();
$currentStore = getCurrentStore();

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update_provider':
                    $settings = [
                        'api_key' => $_POST['api_key'] ?? '',
                        'api_secret' => $_POST['api_secret'] ?? '',
                        'webhook_url' => $_POST['webhook_url'] ?? '',
                        'currency' => $_POST['currency'] ?? 'ILS',
                        'commission_rate' => $_POST['commission_rate'] ?? 0
                    ];
                    
                    $stmt = $db->prepare("
                        INSERT INTO payment_providers (store_id, provider_name, display_name, is_active, is_test_mode, settings, sort_order)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                        display_name = VALUES(display_name),
                        is_active = VALUES(is_active),
                        is_test_mode = VALUES(is_test_mode),
                        settings = VALUES(settings),
                        sort_order = VALUES(sort_order)
                    ");
                    
                    $stmt->execute([
                        $currentStore['id'],
                        $_POST['provider_name'],
                        $_POST['display_name'],
                        isset($_POST['is_active']) ? 1 : 0,
                        isset($_POST['is_test_mode']) ? 1 : 0,
                        json_encode($settings),
                        $_POST['sort_order'] ?? 0
                    ]);
                    
                    $success = 'הגדרות ספק התשלום עודכנו בהצלחה!';
                    break;
                    
                case 'delete_provider':
                    $stmt = $db->prepare("DELETE FROM payment_providers WHERE id = ? AND store_id = ?");
                    $stmt->execute([$_POST['provider_id'], $currentStore['id']]);
                    $success = 'ספק התשלום נמחק בהצלחה!';
                    break;
            }
        }
    } catch (Exception $e) {
        $error = 'שגיאה: ' . $e->getMessage();
    }
}

// Get all payment providers
$stmt = $db->prepare("SELECT * FROM payment_providers WHERE store_id = ? ORDER BY sort_order ASC, is_active DESC");
$stmt->execute([$currentStore['id']]);
$providers = $stmt->fetchAll();

// Available providers with their configurations
$availableProviders = [
    'paypal' => [
        'name' => 'PayPal',
        'icon' => 'ri-paypal-line',
        'color' => 'text-blue-600',
        'fields' => ['client_id' => 'Client ID', 'client_secret' => 'Client Secret']
    ],
    'stripe' => [
        'name' => 'Stripe',
        'icon' => 'ri-bank-card-line',
        'color' => 'text-purple-600',
        'fields' => ['publishable_key' => 'Publishable Key', 'secret_key' => 'Secret Key']
    ],
    'credit_guard' => [
        'name' => 'Credit Guard',
        'icon' => 'ri-shield-check-line',
        'color' => 'text-green-600',
        'fields' => ['terminal_number' => 'מספר טרמינל', 'username' => 'שם משתמש', 'password' => 'סיסמה']
    ],
    'tranzila' => [
        'name' => 'Tranzila',
        'icon' => 'ri-bank-line',
        'color' => 'text-orange-600',
        'fields' => ['supplier' => 'ספק', 'username' => 'שם משתמש', 'password' => 'סיסמה']
    ],
    'bit' => [
        'name' => 'Bit',
        'icon' => 'ri-smartphone-line',
        'color' => 'text-blue-500',
        'fields' => ['terminal_id' => 'מזהה טרמינל', 'username' => 'שם משתמש']
    ],
    'cash_on_delivery' => [
        'name' => 'תשלום במזומן',
        'icon' => 'ri-money-dollar-circle-line',
        'color' => 'text-green-500',
        'fields' => []
    ],
    'bank_transfer' => [
        'name' => 'העברה בנקאית',
        'icon' => 'ri-bank-line',
        'color' => 'text-gray-600',
        'fields' => ['bank_name' => 'שם הבנק', 'account_number' => 'מספר חשבון', 'account_name' => 'שם בעל החשבון']
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
                        <p class="text-gray-600 mt-2">ניהול ספקי תשלום ואמצעי תשלום לחנות</p>
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

            <!-- Payment Providers Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php foreach ($availableProviders as $key => $providerInfo): ?>
                    <?php
                    $existingProvider = null;
                    foreach ($providers as $provider) {
                        if ($provider['provider_name'] === $key) {
                            $existingProvider = $provider;
                            break;
                        }
                    }
                    $settings = $existingProvider ? json_decode($existingProvider['settings'], true) : [];
                    ?>
                    
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center">
                                    <i class="<?php echo $providerInfo['icon']; ?> <?php echo $providerInfo['color']; ?> text-3xl ml-4"></i>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900"><?php echo $providerInfo['name']; ?></h3>
                                        <p class="text-sm text-gray-500">
                                            <?php if ($existingProvider && $existingProvider['is_active']): ?>
                                                <span class="text-green-600">פעיל</span>
                                            <?php else: ?>
                                                <span class="text-gray-400">לא מוגדר</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <?php if ($existingProvider && $existingProvider['is_active']): ?>
                                    <span class="bg-green-100 text-green-800 text-xs px-3 py-1 rounded-full font-medium">
                                        פעיל
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="action" value="update_provider">
                                <input type="hidden" name="provider_name" value="<?php echo $key; ?>">
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">שם תצוגה</label>
                                    <input type="text" name="display_name" 
                                           value="<?php echo htmlspecialchars($existingProvider['display_name'] ?? $providerInfo['name']); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <?php if (!empty($providerInfo['fields'])): ?>
                                    <?php foreach ($providerInfo['fields'] as $fieldKey => $fieldLabel): ?>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo $fieldLabel; ?></label>
                                            <input type="<?php echo in_array($fieldKey, ['password', 'secret_key', 'client_secret']) ? 'password' : 'text'; ?>" 
                                                   name="<?php echo $fieldKey; ?>" 
                                                   value="<?php echo htmlspecialchars($settings[$fieldKey] ?? ''); ?>"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">עמלה (%)</label>
                                        <input type="number" step="0.01" name="commission_rate" 
                                               value="<?php echo $settings['commission_rate'] ?? '0'; ?>"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">סדר תצוגה</label>
                                        <input type="number" name="sort_order" 
                                               value="<?php echo $existingProvider['sort_order'] ?? '0'; ?>"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-6">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_active" 
                                               <?php echo ($existingProvider && $existingProvider['is_active']) ? 'checked' : ''; ?>
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded ml-3">
                                        <span class="text-sm font-medium text-gray-700">פעיל</span>
                                    </label>
                                    
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_test_mode" 
                                               <?php echo (!$existingProvider || $existingProvider['is_test_mode']) ? 'checked' : ''; ?>
                                               class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded ml-3">
                                        <span class="text-sm font-medium text-gray-700">מצב בדיקה</span>
                                    </label>
                                </div>
                                
                                <div class="flex gap-3 pt-4">
                                    <button type="submit" 
                                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-medium transition-colors">
                                        <i class="ri-save-line ml-2"></i>
                                        שמור הגדרות
                                    </button>
                                    
                                    <?php if ($existingProvider): ?>
                                        <button type="button" onclick="deleteProvider(<?php echo $existingProvider['id']; ?>, '<?php echo htmlspecialchars($providerInfo['name']); ?>')"
                                                class="bg-red-50 hover:bg-red-100 text-red-600 py-3 px-4 rounded-lg font-medium transition-colors">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Payment Settings Summary -->
            <div class="mt-8 bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">
                    <i class="ri-settings-3-line ml-2"></i>
                    סיכום הגדרות תשלום
                </h2>
                
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="text-center p-4 bg-blue-50 rounded-xl">
                        <i class="ri-bank-card-line text-3xl text-blue-600 mb-2"></i>
                        <h3 class="font-semibold text-gray-900">ספקי תשלום פעילים</h3>
                        <p class="text-2xl font-bold text-blue-600">
                            <?php echo count(array_filter($providers, function($p) { return $p['is_active']; })); ?>
                        </p>
                    </div>
                    
                    <div class="text-center p-4 bg-green-50 rounded-xl">
                        <i class="ri-shield-check-line text-3xl text-green-600 mb-2"></i>
                        <h3 class="font-semibold text-gray-900">מצב אבטחה</h3>
                        <p class="text-sm font-medium text-green-600">SSL מופעל</p>
                    </div>
                    
                    <div class="text-center p-4 bg-orange-50 rounded-xl">
                        <i class="ri-test-tube-line text-3xl text-orange-600 mb-2"></i>
                        <h3 class="font-semibold text-gray-900">מצב בדיקה</h3>
                        <p class="text-sm font-medium text-orange-600">
                            <?php
                            $testModeCount = count(array_filter($providers, function($p) { return $p['is_test_mode'] && $p['is_active']; }));
                            echo $testModeCount > 0 ? "$testModeCount ספקים" : "לא פעיל";
                            ?>
                        </p>
                    </div>
                </div>
                
                <div class="mt-6 p-4 bg-yellow-50 border-l-4 border-yellow-400">
                    <div class="flex">
                        <i class="ri-information-line text-yellow-600 ml-2 mt-1"></i>
                        <div>
                            <h4 class="font-medium text-yellow-800">הערות חשובות</h4>
                            <ul class="text-sm text-yellow-700 mt-2 space-y-1">
                                <li>• ודא שכל הגדרות ה-API מוגדרות נכון לפני הפעלת הספק</li>
                                <li>• השתמש במצב בדיקה לפני פרסום החנות ללקוחות</li>
                                <li>• עמלות מוצגות למידע בלבד ולא משפיעות על החישוב האמיתי</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function deleteProvider(id, name) {
    if (confirm(`האם אתה בטוח שברצונך למחוק את ספק התשלום "${name}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_provider">
            <input type="hidden" name="provider_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Show/hide password fields
document.querySelectorAll('input[type="password"]').forEach(input => {
    const wrapper = input.parentElement;
    const toggleBtn = document.createElement('button');
    toggleBtn.type = 'button';
    toggleBtn.className = 'absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600';
    toggleBtn.innerHTML = '<i class="ri-eye-line"></i>';
    
    wrapper.classList.add('relative');
    wrapper.appendChild(toggleBtn);
    
    toggleBtn.addEventListener('click', function() {
        if (input.type === 'password') {
            input.type = 'text';
            toggleBtn.innerHTML = '<i class="ri-eye-off-line"></i>';
        } else {
            input.type = 'password';
            toggleBtn.innerHTML = '<i class="ri-eye-line"></i>';
        }
    });
});
</script>

<?php include '../../admin/templates/footer.php'; ?> 