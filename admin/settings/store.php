<?php
require_once '../../includes/auth.php';

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

$pageTitle = 'הגדרות חנות';

// טיפול בשמירת הגדרות
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $storeName = trim($_POST['store_name'] ?? '');
    $storeDescription = trim($_POST['store_description'] ?? '');
    $storeEmail = trim($_POST['store_email'] ?? '');
    $storePhone = trim($_POST['store_phone'] ?? '');
    $storeAddress = trim($_POST['store_address'] ?? '');
    $storeDomain = trim($_POST['store_domain'] ?? '');
    
    if (!empty($storeName)) {
        try {
            $stmt = $db->prepare("UPDATE stores SET 
                name = ?, 
                description = ?, 
                email = ?, 
                phone = ?, 
                address = ?, 
                domain = ? 
                WHERE id = ?");
            $stmt->execute([
                $storeName, 
                $storeDescription, 
                $storeEmail, 
                $storePhone, 
                $storeAddress, 
                $storeDomain, 
                $store['id']
            ]);
            
            $successMessage = 'הגדרות החנות נשמרו בהצלחה!';
            
            // עדכון המידע בזיכרון
            $store['name'] = $storeName;
            $store['description'] = $storeDescription;
            $store['email'] = $storeEmail;
            $store['phone'] = $storePhone;
            $store['address'] = $storeAddress;
            $store['domain'] = $storeDomain;
            
        } catch (Exception $e) {
            $errorMessage = 'שגיאה בשמירת ההגדרות: ' . $e->getMessage();
        }
    } else {
        $errorMessage = 'שם החנות הוא שדה חובה';
    }
}

include '../templates/header.php';
include '../templates/sidebar.php';
?>

<!-- Main Content -->
<div class="lg:pr-64">
    <?php include '../templates/navbar.php'; ?>

    <!-- Content -->
    <main class="py-8" style="background: #e9f0f3;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header Section -->
            <div class="mb-8">
                <div class="bg-white shadow-xl rounded-3xl p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <nav class="text-sm text-gray-500 mb-2 flex items-center gap-2">
                                <a href="../settings/" class="hover:text-blue-600">הגדרות</a>
                                <i class="ri-arrow-left-s-line"></i>
                                <span class="text-blue-600 font-medium">ניהול החנות</span>
                            </nav>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3">
                                <i class="ri-store-line text-cyan-600"></i>
                                ניהול החנות
                            </h1>
                            <p class="text-gray-600">עדכן את פרטי החנות, לוגו ומידע בסיסי</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if (isset($successMessage)): ?>
                <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4">
                    <div class="flex items-center">
                        <i class="ri-check-circle-line text-green-500 ml-3"></i>
                        <span class="text-green-700"><?= htmlspecialchars($successMessage) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($errorMessage)): ?>
                <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="flex items-center">
                        <i class="ri-error-warning-line text-red-500 ml-3"></i>
                        <span class="text-red-700"><?= htmlspecialchars($errorMessage) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Store Settings Form -->
            <div class="bg-white shadow-xl rounded-3xl">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">פרטי החנות</h3>
                    <p class="text-gray-600 mt-1">עדכן את המידע הבסיסי של החנות שלך</p>
                </div>
                
                <form method="POST" class="p-6">
                    <div class="space-y-6">
                        
                        <!-- Store Name and Domain -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="store_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    שם החנות *
                                </label>
                                <input type="text" id="store_name" name="store_name" required
                                       value="<?= htmlspecialchars($store['name'] ?? '') ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <p class="text-xs text-gray-500 mt-1">השם שיוצג ללקוחות בחנות</p>
                            </div>
                            
                            <div>
                                <label for="store_domain" class="block text-sm font-medium text-gray-700 mb-2">
                                    דומיין חנות
                                </label>
                                <div class="relative">
                                    <input type="text" id="store_domain" name="store_domain"
                                           value="<?= htmlspecialchars($store['domain'] ?? '') ?>"
                                           class="w-full px-4 py-3 pr-32 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-500">
                                        .quickshop5.co.il
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">כתובת האתר של החנות</p>
                            </div>
                        </div>

                        <!-- Store Description -->
                        <div>
                            <label for="store_description" class="block text-sm font-medium text-gray-700 mb-2">
                                תיאור החנות
                            </label>
                            <textarea id="store_description" name="store_description" rows="4"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                                      placeholder="ספר ללקוחות על החנות שלך..."><?= htmlspecialchars($store['description'] ?? '') ?></textarea>
                            <p class="text-xs text-gray-500 mt-1">התיאור יוצג בדף הבית ובחיפוש</p>
                        </div>

                        <!-- Contact Information -->
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">פרטי יצירת קשר</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="store_email" class="block text-sm font-medium text-gray-700 mb-2">
                                        אימייל החנות
                                    </label>
                                    <input type="email" id="store_email" name="store_email"
                                           value="<?= htmlspecialchars($store['email'] ?? '') ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <p class="text-xs text-gray-500 mt-1">יוצג ללקוחות ליצירת קשר</p>
                                </div>
                                
                                <div>
                                    <label for="store_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                        טלפון החנות
                                    </label>
                                    <input type="tel" id="store_phone" name="store_phone"
                                           value="<?= htmlspecialchars($store['phone'] ?? '') ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <p class="text-xs text-gray-500 mt-1">מספר טלפון ליצירת קשר</p>
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="store_address" class="block text-sm font-medium text-gray-700 mb-2">
                                כתובת החנות
                            </label>
                            <textarea id="store_address" name="store_address" rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                                      placeholder="רחוב, עיר, מיקוד..."><?= htmlspecialchars($store['address'] ?? '') ?></textarea>
                            <p class="text-xs text-gray-500 mt-1">כתובת פיזית של החנות</p>
                        </div>

                        <!-- Logo Upload Section -->
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">לוגו החנות</h4>
                            
                            <div class="flex items-center gap-6">
                                <div class="w-24 h-24 bg-gray-100 rounded-xl flex items-center justify-center border-2 border-dashed border-gray-300">
                                    <?php if (!empty($store['logo'])): ?>
                                        <img src="<?= htmlspecialchars($store['logo']) ?>" alt="לוגו החנות" class="w-full h-full object-cover rounded-xl">
                                    <?php else: ?>
                                        <i class="ri-image-line text-3xl text-gray-400"></i>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex-1">
                                    <div class="flex gap-3">
                                        <button type="button" onclick="document.getElementById('logo-upload').click()" 
                                                class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition-colors flex items-center gap-2">
                                            <i class="ri-upload-line"></i>
                                            העלה לוגו
                                        </button>
                                        <?php if (!empty($store['logo'])): ?>
                                        <button type="button" onclick="removeLogo()" 
                                                class="bg-red-100 text-red-700 px-6 py-3 rounded-xl hover:bg-red-200 transition-colors flex items-center gap-2">
                                            <i class="ri-delete-bin-line"></i>
                                            הסר לוגו
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">מומלץ: 200x200 פיקסלים, פורמט PNG או JPG</p>
                                    <input type="file" id="logo-upload" accept="image/*" class="hidden" onchange="previewLogo(this)">
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            <!-- Additional Settings -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                
                <!-- Store Status -->
                <div class="bg-white shadow-xl rounded-3xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
                        <i class="ri-toggle-line text-green-600"></i>
                        סטטוס החנות
                    </h4>
                    
                    <div class="space-y-4">
                        <label class="flex items-center justify-between p-4 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                            <div>
                                <div class="font-medium text-gray-900">חנות פעילה</div>
                                <div class="text-sm text-gray-500">הלקוחות יכולים לגלוש ולהזמין</div>
                            </div>
                            <input type="checkbox" checked class="w-6 h-6 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        </label>
                        
                        <label class="flex items-center justify-between p-4 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                            <div>
                                <div class="font-medium text-gray-900">מצב תחזוקה</div>
                                <div class="text-sm text-gray-500">הצג הודעת תחזוקה למבקרים</div>
                            </div>
                            <input type="checkbox" class="w-6 h-6 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        </label>
                    </div>
                </div>

                <!-- SEO Settings -->
                <div class="bg-white shadow-xl rounded-3xl p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
                        <i class="ri-search-line text-purple-600"></i>
                        הגדרות SEO
                    </h4>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   placeholder="כותרת לחיפוש גוגל">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                            <textarea rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" 
                                      placeholder="תיאור קצר לחיפוש גוגל"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="mt-8">
                <div class="flex justify-between items-center bg-white shadow-xl rounded-3xl p-6">
                    <div>
                        <p class="text-sm text-gray-600">שינוי ההגדרות ישפיע על החנות מיידית</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="window.location.href='../settings/'" 
                                class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors font-medium">
                            ביטול
                        </button>
                        <button type="submit" form="storeForm" 
                                class="px-8 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium flex items-center gap-2">
                            <i class="ri-save-line"></i>
                            שמור שינויים
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
// Preview logo before upload
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.querySelector('.w-24.h-24 img, .w-24.h-24 i').parentElement;
            preview.innerHTML = `<img src="${e.target.result}" alt="לוגו החנות" class="w-full h-full object-cover rounded-xl">`;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Remove logo
function removeLogo() {
    if (confirm('האם אתה בטוח שברצונך להסיר את הלוגו?')) {
        const preview = document.querySelector('.w-24.h-24 img, .w-24.h-24 i').parentElement;
        preview.innerHTML = '<i class="ri-image-line text-3xl text-gray-400"></i>';
    }
}

// Make form submission work with the save button
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[method="POST"]');
    const saveButton = document.querySelector('button[form="storeForm"]');
    
    if (form && saveButton) {
        form.id = 'storeForm';
        saveButton.addEventListener('click', function(e) {
            e.preventDefault();
            form.submit();
        });
    }
});
</script>

<?php include '../templates/footer.php'; ?> 