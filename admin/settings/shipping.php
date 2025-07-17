<?php
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = 'הגדרות משלוח';
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
                case 'add_zone':
                    $stmt = $db->prepare("
                        INSERT INTO shipping_zones (store_id, name, countries, states, postcodes, is_active) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $currentStore['id'],
                        $_POST['zone_name'],
                        json_encode(explode(',', $_POST['countries'] ?? '')),
                        json_encode(explode(',', $_POST['states'] ?? '')),
                        json_encode(explode(',', $_POST['postcodes'] ?? '')),
                        isset($_POST['is_active']) ? 1 : 0
                    ]);
                    $success = 'אזור משלוח נוסף בהצלחה!';
                    break;
                    
                case 'update_zone':
                    $stmt = $db->prepare("
                        UPDATE shipping_zones 
                        SET name = ?, countries = ?, states = ?, postcodes = ?, is_active = ?
                        WHERE id = ? AND store_id = ?
                    ");
                    $stmt->execute([
                        $_POST['zone_name'],
                        json_encode(explode(',', $_POST['countries'] ?? '')),
                        json_encode(explode(',', $_POST['states'] ?? '')),
                        json_encode(explode(',', $_POST['postcodes'] ?? '')),
                        isset($_POST['is_active']) ? 1 : 0,
                        $_POST['zone_id'],
                        $currentStore['id']
                    ]);
                    $success = 'אזור משלוח עודכן בהצלחה!';
                    break;
                    
                case 'delete_zone':
                    $stmt = $db->prepare("DELETE FROM shipping_zones WHERE id = ? AND store_id = ?");
                    $stmt->execute([$_POST['zone_id'], $currentStore['id']]);
                    $success = 'אזור משלוח נמחק בהצלחה!';
                    break;
                    
                case 'add_method':
                    $stmt = $db->prepare("
                        INSERT INTO shipping_methods (store_id, zone_id, name, description, method_type, cost, min_order_amount, max_weight, estimated_delivery_days, is_active, sort_order) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $currentStore['id'],
                        $_POST['zone_id'],
                        $_POST['method_name'],
                        $_POST['description'],
                        $_POST['method_type'],
                        $_POST['cost'] ?? 0,
                        $_POST['min_order_amount'] ?? null,
                        $_POST['max_weight'] ?? null,
                        $_POST['estimated_delivery_days'] ?? null,
                        isset($_POST['is_active']) ? 1 : 0,
                        $_POST['sort_order'] ?? 0
                    ]);
                    $success = 'שיטת משלוח נוספה בהצלחה!';
                    break;
                    
                case 'update_method':
                    $stmt = $db->prepare("
                        UPDATE shipping_methods 
                        SET name = ?, description = ?, method_type = ?, cost = ?, min_order_amount = ?, max_weight = ?, estimated_delivery_days = ?, is_active = ?, sort_order = ?
                        WHERE id = ? AND store_id = ?
                    ");
                    $stmt->execute([
                        $_POST['method_name'],
                        $_POST['description'],
                        $_POST['method_type'],
                        $_POST['cost'] ?? 0,
                        $_POST['min_order_amount'] ?? null,
                        $_POST['max_weight'] ?? null,
                        $_POST['estimated_delivery_days'] ?? null,
                        isset($_POST['is_active']) ? 1 : 0,
                        $_POST['sort_order'] ?? 0,
                        $_POST['method_id'],
                        $currentStore['id']
                    ]);
                    $success = 'שיטת משלוח עודכנה בהצלחה!';
                    break;
                    
                case 'delete_method':
                    $stmt = $db->prepare("DELETE FROM shipping_methods WHERE id = ? AND store_id = ?");
                    $stmt->execute([$_POST['method_id'], $currentStore['id']]);
                    $success = 'שיטת משלוח נמחקה בהצלחה!';
                    break;
            }
        }
    } catch (Exception $e) {
        $error = 'שגיאה: ' . $e->getMessage();
    }
}

// Get all zones and methods
$stmt = $db->prepare("
    SELECT z.*, 
           COUNT(m.id) as methods_count,
           COUNT(CASE WHEN m.is_active = 1 THEN 1 END) as active_methods_count
    FROM shipping_zones z 
    LEFT JOIN shipping_methods m ON z.id = m.zone_id 
    WHERE z.store_id = ? 
    GROUP BY z.id 
    ORDER BY z.created_at DESC
");
$stmt->execute([$currentStore['id']]);
$zones = $stmt->fetchAll();

$stmt = $db->prepare("
    SELECT m.*, z.name as zone_name 
    FROM shipping_methods m 
    JOIN shipping_zones z ON m.zone_id = z.id 
    WHERE m.store_id = ? 
    ORDER BY z.name, m.sort_order, m.name
");
$stmt->execute([$currentStore['id']]);
$methods = $stmt->fetchAll();

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
                        <p class="text-gray-600 mt-2">הגדרת אזורי משלוח ושיטות משלוח לחנות</p>
                    </div>
                    <button onclick="openZoneModal()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-medium transition-colors shadow-lg">
                        <i class="ri-add-line ml-2"></i>
                        הוסף אזור משלוח
                    </button>
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

            <!-- Shipping Zones -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">אזורי משלוח</h2>
                
                <?php if (empty($zones)): ?>
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 text-center">
                        <i class="ri-map-2-line text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-500 mb-2">אין אזורי משלוח</h3>
                        <p class="text-gray-400 mb-6">התחל בהוספת אזור משלוח ראשון</p>
                        <button onclick="openZoneModal()" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-medium transition-colors">
                            <i class="ri-add-line ml-2"></i>
                            הוסף אזור ראשון
                        </button>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($zones as $zone): ?>
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="font-bold text-lg text-gray-900"><?php echo htmlspecialchars($zone['name']); ?></h3>
                                        <?php if ($zone['is_active']): ?>
                                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">פעיל</span>
                                        <?php else: ?>
                                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">לא פעיל</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="space-y-2 mb-4">
                                        <?php
                                        $countries = json_decode($zone['countries'], true);
                                        $states = json_decode($zone['states'], true);
                                        $postcodes = json_decode($zone['postcodes'], true);
                                        ?>
                                        
                                        <?php if (!empty($countries[0])): ?>
                                            <p class="text-sm text-gray-600">
                                                <i class="ri-global-line ml-1"></i>
                                                מדינות: <?php echo implode(', ', array_filter($countries)); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($states[0])): ?>
                                            <p class="text-sm text-gray-600">
                                                <i class="ri-map-pin-line ml-1"></i>
                                                אזורים: <?php echo implode(', ', array_filter($states)); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($postcodes[0])): ?>
                                            <p class="text-sm text-gray-600">
                                                <i class="ri-mail-line ml-1"></i>
                                                מיקודים: <?php echo implode(', ', array_filter($postcodes)); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="bg-gray-50 p-3 rounded-lg mb-4">
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium"><?php echo $zone['methods_count']; ?></span> שיטות משלוח
                                            (<?php echo $zone['active_methods_count']; ?> פעילות)
                                        </p>
                                    </div>
                                    
                                    <div class="flex gap-2">
                                        <button onclick="editZone(<?php echo htmlspecialchars(json_encode($zone)); ?>)" 
                                                class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-600 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                            <i class="ri-edit-line ml-1"></i>
                                            עריכה
                                        </button>
                                        <button onclick="openMethodModal(<?php echo $zone['id']; ?>, '<?php echo htmlspecialchars($zone['name']); ?>')" 
                                                class="flex-1 bg-green-50 hover:bg-green-100 text-green-600 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                            <i class="ri-add-line ml-1"></i>
                                            שיטה
                                        </button>
                                        <button onclick="deleteZone(<?php echo $zone['id']; ?>, '<?php echo htmlspecialchars($zone['name']); ?>')" 
                                                class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Shipping Methods -->
            <?php if (!empty($methods)): ?>
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">שיטות משלוח</h2>
                    
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">שיטה</th>
                                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">אזור</th>
                                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">עלות</th>
                                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">זמן משלוח</th>
                                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">סטטוס</th>
                                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">פעולות</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($methods as $method): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <?php
                                                    $methodIcons = [
                                                        'flat_rate' => 'ri-price-tag-3-line text-blue-500',
                                                        'free_shipping' => 'ri-gift-line text-green-500',
                                                        'local_pickup' => 'ri-store-2-line text-orange-500',
                                                        'calculated' => 'ri-calculator-line text-purple-500'
                                                    ];
                                                    $icon = $methodIcons[$method['method_type']] ?? 'ri-truck-line text-gray-500';
                                                    ?>
                                                    <i class="<?php echo $icon; ?> ml-3"></i>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($method['name']); ?></div>
                                                        <?php if ($method['description']): ?>
                                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($method['description']); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($method['zone_name']); ?></td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <?php if ($method['method_type'] === 'free_shipping'): ?>
                                                    <span class="text-green-600 font-medium">חינם</span>
                                                <?php else: ?>
                                                    ₪<?php echo number_format($method['cost'], 2); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <?php echo $method['estimated_delivery_days'] ? $method['estimated_delivery_days'] . ' ימים' : 'לא צוין'; ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php if ($method['is_active']): ?>
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">פעיל</span>
                                                <?php else: ?>
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">לא פעיל</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm font-medium">
                                                <div class="flex gap-2">
                                                    <button onclick="editMethod(<?php echo htmlspecialchars(json_encode($method)); ?>)"
                                                            class="text-blue-600 hover:text-blue-900">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    <button onclick="deleteMethod(<?php echo $method['id']; ?>, '<?php echo htmlspecialchars($method['name']); ?>')"
                                                            class="text-red-600 hover:text-red-900">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Zone Modal -->
<div id="zoneModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 id="zoneModalTitle" class="text-xl font-bold text-gray-900">הוסף אזור משלוח</h3>
                    <button onclick="closeZoneModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="ri-close-line text-2xl"></i>
                    </button>
                </div>
                
                <form id="zoneForm" method="POST">
                    <input type="hidden" name="action" id="zoneAction" value="add_zone">
                    <input type="hidden" name="zone_id" id="zoneIdEdit">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">שם האזור</label>
                            <input type="text" name="zone_name" id="zoneName" required 
                                   placeholder="למשל: מרכז הארץ"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">מדינות (מופרדות בפסיק)</label>
                            <input type="text" name="countries" id="zoneCountries" 
                                   placeholder="ישראל, פלסטין"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">אזורים/מחוזות (מופרדים בפסיק)</label>
                            <input type="text" name="states" id="zoneStates" 
                                   placeholder="תל אביב, מרכז, ירושלים"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">מיקודים (מופרדים בפסיק)</label>
                            <input type="text" name="postcodes" id="zonePostcodes" 
                                   placeholder="1000-9999, 5000-5999"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="zoneActive" checked 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded ml-3">
                            <label for="zoneActive" class="text-sm font-medium text-gray-700">אזור פעיל</label>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6">
                        <button type="submit" 
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-medium transition-colors">
                            <span id="zoneSubmitText">הוסף אזור</span>
                        </button>
                        <button type="button" onclick="closeZoneModal()" 
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-4 rounded-lg font-medium transition-colors">
                            ביטול
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Method Modal -->
<div id="methodModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 id="methodModalTitle" class="text-xl font-bold text-gray-900">הוסף שיטת משלוח</h3>
                    <button onclick="closeMethodModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="ri-close-line text-2xl"></i>
                    </button>
                </div>
                
                <form id="methodForm" method="POST">
                    <input type="hidden" name="action" id="methodAction" value="add_method">
                    <input type="hidden" name="method_id" id="methodIdEdit">
                    <input type="hidden" name="zone_id" id="methodZoneId">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">שם השיטה</label>
                            <input type="text" name="method_name" id="methodName" required 
                                   placeholder="למשל: משלוח רגיל"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">תיאור</label>
                            <textarea name="description" id="methodDescription" rows="2"
                                      placeholder="תיאור קצר של שיטת המשלוח"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">סוג שיטה</label>
                            <select name="method_type" id="methodType" required onchange="toggleCostField()"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="flat_rate">מחיר קבוע</option>
                                <option value="free_shipping">משלוח חינם</option>
                                <option value="local_pickup">איסוף עצמי</option>
                                <option value="calculated">מחושב</option>
                            </select>
                        </div>
                        
                        <div id="costField">
                            <label class="block text-sm font-medium text-gray-700 mb-2">עלות (₪)</label>
                            <input type="number" step="0.01" name="cost" id="methodCost" 
                                   placeholder="0.00"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">הזמנה מינימלית (₪)</label>
                                <input type="number" step="0.01" name="min_order_amount" id="methodMinOrder" 
                                       placeholder="אופציונלי"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">משקל מקסימלי (ק״ג)</label>
                                <input type="number" step="0.1" name="max_weight" id="methodMaxWeight" 
                                       placeholder="אופציונלי"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">זמן משלוח (ימים)</label>
                                <input type="number" name="estimated_delivery_days" id="methodDeliveryDays" 
                                       placeholder="3"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">סדר תצוגה</label>
                                <input type="number" name="sort_order" id="methodSortOrder" 
                                       placeholder="0"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="methodActive" checked 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded ml-3">
                            <label for="methodActive" class="text-sm font-medium text-gray-700">שיטה פעילה</label>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6">
                        <button type="submit" 
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-medium transition-colors">
                            <span id="methodSubmitText">הוסף שיטה</span>
                        </button>
                        <button type="button" onclick="closeMethodModal()" 
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-4 rounded-lg font-medium transition-colors">
                            ביטול
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Zone Modal Functions
function openZoneModal() {
    document.getElementById('zoneModalTitle').textContent = 'הוסף אזור משלוח';
    document.getElementById('zoneAction').value = 'add_zone';
    document.getElementById('zoneSubmitText').textContent = 'הוסף אזור';
    document.getElementById('zoneForm').reset();
    document.getElementById('zoneActive').checked = true;
    document.getElementById('zoneModal').classList.remove('hidden');
}

function editZone(zone) {
    document.getElementById('zoneModalTitle').textContent = 'עריכת אזור משלוח';
    document.getElementById('zoneAction').value = 'update_zone';
    document.getElementById('zoneSubmitText').textContent = 'עדכן אזור';
    document.getElementById('zoneIdEdit').value = zone.id;
    document.getElementById('zoneName').value = zone.name;
    
    const countries = JSON.parse(zone.countries || '[]');
    const states = JSON.parse(zone.states || '[]');
    const postcodes = JSON.parse(zone.postcodes || '[]');
    
    document.getElementById('zoneCountries').value = countries.filter(c => c).join(', ');
    document.getElementById('zoneStates').value = states.filter(s => s).join(', ');
    document.getElementById('zonePostcodes').value = postcodes.filter(p => p).join(', ');
    document.getElementById('zoneActive').checked = zone.is_active == 1;
    document.getElementById('zoneModal').classList.remove('hidden');
}

function closeZoneModal() {
    document.getElementById('zoneModal').classList.add('hidden');
}

function deleteZone(id, name) {
    if (confirm(`האם אתה בטוח שברצונך למחוק את אזור המשלוח "${name}"?\nכל שיטות המשלוח באזור זה יימחקו גם כן.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_zone">
            <input type="hidden" name="zone_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Method Modal Functions
function openMethodModal(zoneId, zoneName) {
    document.getElementById('methodModalTitle').textContent = `הוסף שיטת משלוח - ${zoneName}`;
    document.getElementById('methodAction').value = 'add_method';
    document.getElementById('methodSubmitText').textContent = 'הוסף שיטה';
    document.getElementById('methodZoneId').value = zoneId;
    document.getElementById('methodForm').reset();
    document.getElementById('methodActive').checked = true;
    toggleCostField();
    document.getElementById('methodModal').classList.remove('hidden');
}

function editMethod(method) {
    document.getElementById('methodModalTitle').textContent = 'עריכת שיטת משלוח';
    document.getElementById('methodAction').value = 'update_method';
    document.getElementById('methodSubmitText').textContent = 'עדכן שיטה';
    document.getElementById('methodIdEdit').value = method.id;
    document.getElementById('methodZoneId').value = method.zone_id;
    document.getElementById('methodName').value = method.name;
    document.getElementById('methodDescription').value = method.description || '';
    document.getElementById('methodType').value = method.method_type;
    document.getElementById('methodCost').value = method.cost || '';
    document.getElementById('methodMinOrder').value = method.min_order_amount || '';
    document.getElementById('methodMaxWeight').value = method.max_weight || '';
    document.getElementById('methodDeliveryDays').value = method.estimated_delivery_days || '';
    document.getElementById('methodSortOrder').value = method.sort_order || '0';
    document.getElementById('methodActive').checked = method.is_active == 1;
    toggleCostField();
    document.getElementById('methodModal').classList.remove('hidden');
}

function closeMethodModal() {
    document.getElementById('methodModal').classList.add('hidden');
}

function deleteMethod(id, name) {
    if (confirm(`האם אתה בטוח שברצונך למחוק את שיטת המשלוח "${name}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_method">
            <input type="hidden" name="method_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function toggleCostField() {
    const methodType = document.getElementById('methodType').value;
    const costField = document.getElementById('costField');
    
    if (methodType === 'free_shipping' || methodType === 'local_pickup') {
        costField.style.display = 'none';
        document.getElementById('methodCost').value = '0';
    } else {
        costField.style.display = 'block';
    }
}

// Close modals on outside click
document.getElementById('zoneModal').addEventListener('click', function(e) {
    if (e.target === this) closeZoneModal();
});

document.getElementById('methodModal').addEventListener('click', function(e) {
    if (e.target === this) closeMethodModal();
});
</script>

<?php include '../../admin/templates/footer.php'; ?> 