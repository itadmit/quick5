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
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3">
                                <i class="ri-settings-line text-gray-700"></i>
                                הגדרות חנות
                            </h1>
                            <p class="text-gray-600">נהל את כל ההגדרות והתצורות של החנות שלך</p>
                        </div>
                        <div class="mt-4 lg:mt-0">
                            <nav class="text-sm text-gray-500 flex items-center gap-2">
                                <span>הגדרות חנות</span>
                                <i class="ri-arrow-left-s-line"></i>
                                <span class="text-blue-600 font-medium">דשבורד</span>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <!-- Row 1 -->
                <!-- הטמעות פיקסלים -->
                <div class="bg-white shadow-xl rounded-3xl p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer group" onclick="location.href='analytics.php'">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-purple-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="ri-pie-chart-line text-2xl text-purple-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">הטמעות פיקסלים</h3>
                        <p class="text-sm text-gray-600">Google Analytics, Facebook Pixel ועוד</p>
                    </div>
                </div>

                <!-- הגדרות תשלום -->
                <div class="bg-white shadow-xl rounded-3xl p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer group" onclick="location.href='payment.php'">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-green-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="ri-bank-card-line text-2xl text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">הגדרות תשלום</h3>
                        <p class="text-sm text-gray-600">נהל את הגדרת מספק התשלום שלך</p>
                    </div>
                </div>

                <!-- משלוחים -->
                <div class="bg-white shadow-xl rounded-3xl p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer group" onclick="location.href='shipping.php'">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-blue-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="ri-truck-line text-2xl text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">משלוחים</h3>
                        <p class="text-sm text-gray-600">הגדר את אזורי הרוקלח שלך</p>
                    </div>
                </div>

                <!-- ניהול החנות -->
                <div class="bg-white shadow-xl rounded-3xl p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer group" onclick="location.href='store.php'">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-cyan-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="ri-store-line text-2xl text-cyan-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">ניהול החנות</h3>
                        <p class="text-sm text-gray-600">ניהול הדף, לוגו, מפרייטים ועוד</p>
                    </div>
                </div>

                <!-- Row 2 -->
                <!-- התראות -->
                <div class="bg-white shadow-xl rounded-3xl p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer group" onclick="location.href='notifications.php'">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-yellow-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="ri-notification-line text-2xl text-yellow-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">התראות</h3>
                        <p class="text-sm text-gray-600">נהל את ההתראות בקבלת הזמנות</p>
                    </div>
                </div>

                <!-- אקורדיונים גלובליים -->
                <div class="bg-white shadow-xl rounded-3xl p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer group" onclick="location.href='accordions.php'">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-cyan-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="ri-layout-row-line text-2xl text-cyan-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">אקורדיונים גלובליים</h3>
                        <p class="text-sm text-gray-600">הגדר אקורדיונים שיופיעו בכל מוצרי החנות</p>
                    </div>
                </div>

                <!-- קוד מעקב -->
                <div class="bg-white shadow-xl rounded-3xl p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer group" onclick="location.href='tracking.php'">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-indigo-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="ri-code-line text-2xl text-indigo-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">קוד מעקב</h3>
                        <p class="text-sm text-gray-600">נהל את קוד המעקב שלך</p>
                    </div>
                </div>

                <!-- אפשרויות מוצר -->
                <div class="bg-white shadow-xl rounded-3xl p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer group" onclick="location.href='product-options.php'">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-orange-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="ri-settings-3-line text-2xl text-orange-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">אפשרויות מוצר</h3>
                        <p class="text-sm text-gray-600">הגדיר אפשרויות למוצרים שלך</p>
                    </div>
                </div>

                <!-- Row 3 -->
                <!-- אוטומציות -->
                <div class="bg-white shadow-xl rounded-3xl p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer group" onclick="location.href='automations.php'">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-purple-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="ri-settings-2-line text-2xl text-purple-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">אוטומציות</h3>
                        <p class="text-sm text-gray-600">הגדרת וניהול לאחר הזמנה</p>
                    </div>
                </div>

                <!-- בחירת חברת משלוחים -->
                <div class="bg-white shadow-xl rounded-3xl p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer group" onclick="location.href='shipping-companies.php'">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-blue-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="ri-truck-line text-2xl text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">בחירת חברת משלוחים</h3>
                        <p class="text-sm text-gray-600">הגדרת חברת המשלוחים שלך</p>
                    </div>
                </div>

                <!-- דומיין מותאם אישי -->
                <div class="bg-white shadow-xl rounded-3xl p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer group" onclick="location.href='custom-domain.php'">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-green-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="ri-global-line text-2xl text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">דומיין מותאם אישי</h3>
                        <p class="text-sm text-gray-600">חבר את הדומיין שלך לחנות</p>
                    </div>
                </div>

                <!-- הגדרות SMS -->
                <div class="bg-white shadow-xl rounded-3xl p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer group" onclick="location.href='sms.php'">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-yellow-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="ri-message-line text-2xl text-yellow-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">הגדרות SMS</h3>
                        <p class="text-sm text-gray-600">ניהול הודעות SMS והתראות</p>
                    </div>
                </div>

                <!-- Row 4 -->
                <!-- שיתוף גישה -->
                <div class="bg-white shadow-xl rounded-3xl p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer group" onclick="location.href='access.php'">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-red-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="ri-group-line text-2xl text-red-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">שיתוף גישה</h3>
                        <p class="text-sm text-gray-600">הוסף משתמשים ונהל הרשאות</p>
                    </div>
                </div>

                <!-- חיובי מערכת -->
                <div class="bg-white shadow-xl rounded-3xl p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer group" onclick="location.href='billing.php'">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-indigo-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="ri-bill-line text-2xl text-indigo-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">חיובי מערכת</h3>
                        <p class="text-sm text-gray-600">מעקב תשלומים וחשבוניות</p>
                    </div>
                </div>

                <!-- קוד מותאם אישית -->
                <div class="bg-white shadow-xl rounded-3xl p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer group" onclick="location.href='custom-code.php'">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="ri-code-s-slash-line text-2xl text-gray-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">קוד מותאם אישית</h3>
                        <p class="text-sm text-gray-600">CSS, JavaScript ו-HTML</p>
                    </div>
                </div>

                <!-- הגדרות עוגיות GDPR -->
                <div class="bg-white shadow-xl rounded-3xl p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer group" onclick="location.href='gdpr.php'">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-pink-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="ri-shield-user-line text-2xl text-pink-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">הגדרות עוגיות GDPR</h3>
                        <p class="text-sm text-gray-600">מדיניות פרטיות ועוגיות</p>
                    </div>
                </div>

                <!-- Row 5 -->
                <!-- API מפתחות -->
                <div class="bg-white shadow-xl rounded-3xl p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer group" onclick="location.href='api.php'">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="ri-key-line text-2xl text-emerald-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">API מפתחות</h3>
                        <p class="text-sm text-gray-600">יצירה וניהול מפתחות API</p>
                    </div>
                </div>

            </div>

        </div>
    </main>
</div>

<?php include '../templates/footer.php'; ?> 
<?php include '../templates/footer.php'; ?> 