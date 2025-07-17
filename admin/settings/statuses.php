<?php
require_once '../../includes/auth.php';
require_once '../../includes/OrderStatusManager.php';

$auth = new Authentication();
$auth->requireLogin(); // הגנה על העמוד

$currentUser = $auth->getCurrentUser();

// אם אין משתמש מחובר, הפנה להתחברות
if (!$currentUser) {
    header('Location: ../login.php');
    exit;
}

// קבלת נתוני החנות
require_once '../../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM stores WHERE user_id = ? LIMIT 1");
$stmt->execute([$currentUser['id']]);
$store = $stmt->fetch();

// אם אין חנות, הפנה לדשבורד
if (!$store) {
    header('Location: ../');
    exit;
}

$statusManager = new OrderStatusManager();
$storeId = $store['id'];

// קבלת סטטוסים
$orderStatuses = $statusManager->getOrderStatuses($storeId, false);
$paymentStatuses = $statusManager->getPaymentStatuses($storeId, false);

$pageTitle = 'ניהול סטטוסים';

include '../templates/header.php';
include '../templates/sidebar.php';
?>

<!-- Main Content -->
<div class="lg:pr-64">
    <?php include '../templates/navbar.php'; ?>

    <!-- Content -->
    <main class="py-8" style="background: #e9f0f3;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header Section -->
            <div class="mb-8">
                <div class="bg-white shadow-xl rounded-3xl p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 mb-2">ניהול סטטוסים</h1>
                            <p class="text-gray-600">נהל סטטוסי הזמנות ותשלום מותאמים אישית</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="mb-6">
                <div class="bg-white shadow-xl rounded-3xl">
                    <div class="border-b border-gray-200">
                        <nav class="flex space-x-8 px-6 py-4" aria-label="Tabs">
                            <button onclick="switchTab('order')" id="order-tab" 
                                    class="tab-button border-b-2 border-blue-500 text-blue-600 py-2 px-1 text-sm font-medium">
                                <i class="ri-shopping-bag-line ml-2"></i>
                                סטטוסי הזמנות
                            </button>
                            <button onclick="switchTab('payment')" id="payment-tab" 
                                    class="tab-button border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-2 px-1 text-sm font-medium">
                                <i class="ri-money-dollar-circle-line ml-2"></i>
                                סטטוסי תשלום
                            </button>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Order Statuses Tab -->
                <div id="order-content" class="tab-content lg:col-span-2">
                    <div class="bg-white shadow-xl rounded-3xl">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">סטטוסי הזמנות</h3>
                                <button onclick="openStatusModal('order')" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-medium transition-colors flex items-center gap-2">
                                    <i class="ri-add-line"></i>
                                    הוסף סטטוס
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            <div id="order-statuses-list" class="space-y-4">
                                <?php foreach ($orderStatuses as $status): ?>
                                <div class="status-item bg-gray-50 rounded-xl p-4 flex items-center justify-between group hover:bg-gray-100 transition-colors" 
                                     data-id="<?= $status['id'] ?>" data-type="order">
                                    <div class="flex items-center gap-4">
                                        <div class="drag-handle cursor-move text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <i class="ri-drag-move-2-line text-lg"></i>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <?php if ($status['is_system']): ?>
                                            <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded-full text-xs font-medium">מערכת</span>
                                            <?php endif; ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium gap-2" 
                                                  style="color: <?= $status['color'] ?>; background-color: <?= $status['background_color'] ?>;">
                                                <i class="<?= $status['icon'] ?>"></i>
                                                <?= htmlspecialchars($status['display_name']) ?>
                                            </span>
                                            <?php if ($status['is_default']): ?>
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">ברירת מחדל</span>
                                            <?php endif; ?>
                                            <?php if (!$status['is_active']): ?>
                                            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs font-medium">לא פעיל</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button onclick="editStatus('order', <?= $status['id'] ?>)" 
                                                class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50 transition-colors">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <?php if (!$status['is_system']): ?>
                                        <button onclick="deleteStatus('order', <?= $status['id'] ?>)" 
                                                class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50 transition-colors">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Statuses Tab -->
                <div id="payment-content" class="tab-content hidden lg:col-span-2">
                    <div class="bg-white shadow-xl rounded-3xl">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">סטטוסי תשלום</h3>
                                <button onclick="openStatusModal('payment')" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-medium transition-colors flex items-center gap-2">
                                    <i class="ri-add-line"></i>
                                    הוסף סטטוס
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            <div id="payment-statuses-list" class="space-y-4">
                                <?php foreach ($paymentStatuses as $status): ?>
                                <div class="status-item bg-gray-50 rounded-xl p-4 flex items-center justify-between group hover:bg-gray-100 transition-colors" 
                                     data-id="<?= $status['id'] ?>" data-type="payment">
                                    <div class="flex items-center gap-4">
                                        <div class="drag-handle cursor-move text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <i class="ri-drag-move-2-line text-lg"></i>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <?php if ($status['is_system']): ?>
                                            <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded-full text-xs font-medium">מערכת</span>
                                            <?php endif; ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium gap-2" 
                                                  style="color: <?= $status['color'] ?>; background-color: <?= $status['background_color'] ?>;">
                                                <i class="<?= $status['icon'] ?>"></i>
                                                <?= htmlspecialchars($status['display_name']) ?>
                                            </span>
                                            <?php if ($status['is_default']): ?>
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">ברירת מחדל</span>
                                            <?php endif; ?>
                                            <?php if (!$status['is_active']): ?>
                                            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs font-medium">לא פעיל</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button onclick="editStatus('payment', <?= $status['id'] ?>)" 
                                                class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50 transition-colors">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <?php if (!$status['is_system']): ?>
                                        <button onclick="deleteStatus('payment', <?= $status['id'] ?>)" 
                                                class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50 transition-colors">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white shadow-xl rounded-3xl p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">מידע שימושי</h4>
                        <div id="order-info" class="info-content">
                            <ul class="space-y-3 text-sm text-gray-600">
                                <li class="flex items-start gap-3">
                                    <i class="ri-information-line text-blue-500 mt-0.5 flex-shrink-0"></i>
                                    <span><strong>סטטוסי מערכת:</strong> לא ניתן למחוק או לשנות שם</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <i class="ri-drag-move-line text-blue-500 mt-0.5 flex-shrink-0"></i>
                                    <span><strong>גרירה:</strong> ניתן לשנות סדר הצגה</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <i class="ri-palette-line text-blue-500 mt-0.5 flex-shrink-0"></i>
                                    <span><strong>צבעים:</strong> משפיעים על הצגה בממשק</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <i class="ri-settings-line text-blue-500 mt-0.5 flex-shrink-0"></i>
                                    <span><strong>הגדרות מלאי:</strong> קובעות מתי להוריד/לשחרר מלאי</span>
                                </li>
                            </ul>
                        </div>
                        <div id="payment-info" class="info-content hidden">
                            <ul class="space-y-3 text-sm text-gray-600">
                                <li class="flex items-start gap-3">
                                    <i class="ri-check-line text-green-500 mt-0.5 flex-shrink-0"></i>
                                    <span><strong>תשלום מושלם:</strong> מסמן שהתשלום בוצע</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <i class="ri-refund-line text-yellow-500 mt-0.5 flex-shrink-0"></i>
                                    <span><strong>אפשרות החזר:</strong> האם ניתן להחזיר כסף</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <i class="ri-truck-line text-blue-500 mt-0.5 flex-shrink-0"></i>
                                    <span><strong>מילוי אוטומטי:</strong> האם להמשיך לעיבוד הזמנה</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Status Modal -->
<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white rounded-t-3xl border-b border-gray-200 px-6 py-4">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-900" id="statusModalTitle">הוסף סטטוס</h3>
                <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="ri-close-line text-2xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6">
            <form id="statusForm" class="space-y-6">
                <input type="hidden" id="statusId" name="id">
                <input type="hidden" id="statusType" name="type">
                
                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="statusName" class="block text-sm font-medium text-gray-700 mb-2">שם פנימי *</label>
                        <input type="text" id="statusName" name="name" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <p class="text-xs text-gray-500 mt-1">לשימוש במערכת (באנגלית)</p>
                    </div>
                    <div>
                        <label for="statusDisplayName" class="block text-sm font-medium text-gray-700 mb-2">שם תצוגה *</label>
                        <input type="text" id="statusDisplayName" name="display_name" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <p class="text-xs text-gray-500 mt-1">יוצג ללקוחות ובממשק</p>
                    </div>
                </div>
                
                <!-- Description -->
                <div>
                    <label for="statusDescription" class="block text-sm font-medium text-gray-700 mb-2">תיאור</label>
                    <textarea id="statusDescription" name="description" rows="3" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"></textarea>
                </div>
                
                <!-- Colors and Icon -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="statusColor" class="block text-sm font-medium text-gray-700 mb-2">צבע טקסט *</label>
                        <div class="relative">
                            <input type="color" id="statusColor" name="color" value="#6B7280" 
                                   class="w-full h-12 border border-gray-300 rounded-xl cursor-pointer">
                        </div>
                    </div>
                    <div>
                        <label for="statusBgColor" class="block text-sm font-medium text-gray-700 mb-2">צבע רקע</label>
                        <div class="relative">
                            <input type="color" id="statusBgColor" name="background_color" value="#F3F4F6" 
                                   class="w-full h-12 border border-gray-300 rounded-xl cursor-pointer">
                        </div>
                    </div>
                    <div>
                        <label for="statusIcon" class="block text-sm font-medium text-gray-700 mb-2">אייקון</label>
                        <input type="text" id="statusIcon" name="icon" placeholder="ri-circle-line" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <p class="text-xs text-gray-500 mt-1">RemixIcon class</p>
                    </div>
                </div>
                
                <!-- Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="checkbox" id="statusActive" name="is_active" checked 
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="text-sm font-medium text-gray-700">פעיל</span>
                        </label>
                    </div>
                    <div>
                        <label for="statusSortOrder" class="block text-sm font-medium text-gray-700 mb-2">סדר הצגה</label>
                        <input type="number" id="statusSortOrder" name="sort_order" min="0" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                </div>
                
                <!-- Order Status Options -->
                <div id="orderStatusOptions" class="hidden">
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">הגדרות התנהגות</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-3">
                                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" id="allowEdit" name="allow_edit" checked 
                                           class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">אפשר עריכת הזמנה</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" id="autoCompletePayment" name="auto_complete_payment" 
                                           class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">השלם תשלום אוטומטי</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" id="sendEmail" name="send_email_notification" checked 
                                           class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">שלח התראת מייל</span>
                                </label>
                            </div>
                            <div class="space-y-3">
                                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" id="sendSms" name="send_sms_notification" 
                                           class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">שלח SMS</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" id="reduceStock" name="reduce_stock" 
                                           class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">הורד מהמלאי</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" id="releaseStock" name="release_stock" 
                                           class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">שחרר מלאי</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Status Options -->
                <div id="paymentStatusOptions" class="hidden">
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">הגדרות תשלום</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="checkbox" id="isPaid" name="is_paid" 
                                       class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-sm text-gray-700">תשלום מושלם</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="checkbox" id="allowRefund" name="allow_refund" 
                                       class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-sm text-gray-700">אפשר החזר</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="checkbox" id="autoFulfill" name="auto_fulfill" 
                                       class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-sm text-gray-700">מילוי אוטומטי</span>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Modal Footer -->
        <div class="sticky bottom-0 bg-white rounded-b-3xl border-t border-gray-200 px-6 py-4">
            <div class="flex justify-end gap-3">
                <button onclick="closeStatusModal()" 
                        class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors font-medium">
                    ביטול
                </button>
                <button onclick="saveStatus()" 
                        class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium">
                    שמור
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
let currentTab = 'order';

// הגדרת גרירה
document.addEventListener('DOMContentLoaded', function() {
    // גרירה לסטטוסי הזמנות
    new Sortable(document.getElementById('order-statuses-list'), {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'opacity-50',
        chosenClass: 'ring-2 ring-blue-500',
        onEnd: function(evt) {
            updateStatusOrder('order');
        }
    });
    
    // גרירה לסטטוסי תשלום
    new Sortable(document.getElementById('payment-statuses-list'), {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'opacity-50',
        chosenClass: 'ring-2 ring-blue-500',
        onEnd: function(evt) {
            updateStatusOrder('payment');
        }
    });
});

// החלפת טאבים
function switchTab(tab) {
    currentTab = tab;
    
    // עדכון הטאבים
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('border-blue-500', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    
    document.getElementById(tab + '-tab').classList.add('border-blue-500', 'text-blue-600');
    document.getElementById(tab + '-tab').classList.remove('border-transparent', 'text-gray-500');
    
    // עדכון התוכן
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    document.getElementById(tab + '-content').classList.remove('hidden');
    
    // עדכון המידע בצד
    document.querySelectorAll('.info-content').forEach(info => {
        info.classList.add('hidden');
    });
    document.getElementById(tab + '-info').classList.remove('hidden');
}

// פתיחת מודל יצירת סטטוס
function openStatusModal(type) {
    document.getElementById('statusModalTitle').textContent = 'הוסף סטטוס';
    document.getElementById('statusForm').reset();
    document.getElementById('statusId').value = '';
    document.getElementById('statusType').value = type;
    
    // הצגת אפשרויות רלוונטיות
    document.getElementById('orderStatusOptions').classList.toggle('hidden', type !== 'order');
    document.getElementById('paymentStatusOptions').classList.toggle('hidden', type !== 'payment');
    
    // הגדרת אייקון ברירת מחדל
    document.getElementById('statusIcon').value = type === 'payment' ? 'ri-money-dollar-circle-line' : 'ri-circle-line';
    
    document.getElementById('statusModal').classList.remove('hidden');
}

// סגירת מודל
function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

// עריכת סטטוס
async function editStatus(type, id) {
    try {
        const response = await fetch(`../api/order-statuses.php?type=${type}&id=${id}`);
        const status = await response.json();
        
        if (response.ok) {
            // מילוי הטופס
            document.getElementById('statusModalTitle').textContent = 'ערוך סטטוס';
            document.getElementById('statusId').value = status.id;
            document.getElementById('statusType').value = type;
            document.getElementById('statusName').value = status.name;
            document.getElementById('statusDisplayName').value = status.display_name;
            document.getElementById('statusDescription').value = status.description || '';
            document.getElementById('statusColor').value = status.color;
            document.getElementById('statusBgColor').value = status.background_color;
            document.getElementById('statusIcon').value = status.icon;
            document.getElementById('statusActive').checked = status.is_active == 1;
            document.getElementById('statusSortOrder').value = status.sort_order;
            
            // הגדרות מתקדמות
            if (type === 'order') {
                document.getElementById('allowEdit').checked = status.allow_edit == 1;
                document.getElementById('autoCompletePayment').checked = status.auto_complete_payment == 1;
                document.getElementById('sendEmail').checked = status.send_email_notification == 1;
                document.getElementById('sendSms').checked = status.send_sms_notification == 1;
                document.getElementById('reduceStock').checked = status.reduce_stock == 1;
                document.getElementById('releaseStock').checked = status.release_stock == 1;
            } else {
                document.getElementById('isPaid').checked = status.is_paid == 1;
                document.getElementById('allowRefund').checked = status.allow_refund == 1;
                document.getElementById('autoFulfill').checked = status.auto_fulfill == 1;
            }
            
            // הצגת אפשרויות רלוונטיות
            document.getElementById('orderStatusOptions').classList.toggle('hidden', type !== 'order');
            document.getElementById('paymentStatusOptions').classList.toggle('hidden', type !== 'payment');
            
            // השבתת שדות עבור סטטוסי מערכת
            if (status.is_system == 1) {
                document.getElementById('statusName').disabled = true;
                document.getElementById('statusName').classList.add('bg-gray-100', 'cursor-not-allowed');
            }
            
            document.getElementById('statusModal').classList.remove('hidden');
        } else {
            alert('שגיאה בטעינת הסטטוס');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('שגיאה בטעינת הסטטוס');
    }
}

// שמירת סטטוס
async function saveStatus() {
    const form = document.getElementById('statusForm');
    const formData = new FormData(form);
    
    // המרה ל-JSON
    const data = {};
    for (let [key, value] of formData.entries()) {
        if (form.querySelector(`[name="${key}"]`).type === 'checkbox') {
            data[key] = form.querySelector(`[name="${key}"]`).checked;
        } else {
            data[key] = value;
        }
    }
    
    const isEdit = data.id !== '';
    const method = isEdit ? 'PUT' : 'POST';
    
    try {
        const response = await fetch('../api/order-statuses.php', {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.error || 'שגיאה בשמירת הסטטוס');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('שגיאה בשמירת הסטטוס');
    }
}

// מחיקת סטטוס
async function deleteStatus(type, id) {
    if (!confirm('האם אתה בטוח שברצונך למחוק את הסטטוס?')) {
        return;
    }
    
    try {
        const response = await fetch('../api/order-statuses.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id, type: type })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.error || 'שגיאה במחיקת הסטטוס');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('שגיאה במחיקת הסטטוס');
    }
}

// עדכון סדר סטטוסים
async function updateStatusOrder(type) {
    const listId = type === 'order' ? 'order-statuses-list' : 'payment-statuses-list';
    const items = document.querySelectorAll(`#${listId} .status-item`);
    
    const updates = [];
    items.forEach((item, index) => {
        updates.push({
            id: parseInt(item.dataset.id),
            type: type,
            sort_order: index + 1
        });
    });
    
    // שליחת עדכונים
    for (const update of updates) {
        try {
            await fetch('../api/order-statuses.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(update)
            });
        } catch (error) {
            console.error('Error updating order:', error);
        }
    }
}

// סגירת מודל בלחיצה מחוץ לתוכן
document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeStatusModal();
    }
});

// ESC key להסרת מודל
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('statusModal').classList.contains('hidden')) {
        closeStatusModal();
    }
});
</script>

<?php include '../templates/footer.php'; ?> 