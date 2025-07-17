<?php
/**
 * דשבורד ראשי - QuickShop5 Admin
 */

require_once '../includes/auth.php';

$auth = new Authentication();
$auth->requireLogin(); // הגנה על העמוד

$currentUser = $auth->getCurrentUser();

// אם אין משתמש מחובר, הפנה להתחברות
if (!$currentUser) {
    header('Location: /admin/login.php');
    exit;
}

// קבלת נתוני החנות
require_once '../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM stores WHERE user_id = ? LIMIT 1");
$stmt->execute([$currentUser['id']]);
$store = $stmt->fetch();

// אם אין חנות, צור אחת ברירת מחדל
if (!$store) {
    $store = [
        'name' => 'החנות שלי',
        'domain' => 'store'
    ];
}

// נתונים סטטיסטיים בסיסיים
$stats = [
    'products' => 0,
    'orders' => 0,
    'customers' => 0,
    'revenue' => 0
];

$pageTitle = 'דשבורד';
?>

<?php include 'templates/header.php'; ?>
    
    <?php include 'templates/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="lg:pr-64">
        
        <?php include 'templates/navbar.php'; ?>

        <!-- Dashboard Content -->
        <main class="py-8" style="background: #e9f0f3;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Welcome Message for New Users -->
                <?php if (isset($_GET['welcome']) && $_GET['welcome'] == '1'): ?>
                    <div class="mb-8">
                        <div class="bg-gradient-to-r from-green-400 to-blue-500 rounded-lg shadow-xl p-6 text-white">
                            <div class="flex items-center">
                                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center ml-4">
                                    <i class="ri-rocket-line text-3xl text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <h2 class="text-2xl font-bold mb-2">🎉 ברוכים הבאים ל-QuickShop5!</h2>
                                    <p class="text-white/90 mb-4">החנות שלך נוצרה בהצלחה וזמינה בכתובת:</p>
                                    <?php if (isset($_GET['store_url'])): ?>
                                        <div class="bg-white/20 rounded-lg p-3 mb-4">
                                            <div class="font-mono text-lg" style="direction: ltr; text-align: left;">
                                                <?= htmlspecialchars($_GET['store_url']) ?>
                                            </div>
                </div>
                                        <div class="flex flex-wrap gap-3">
                                            <a href="http://<?= htmlspecialchars($_GET['store_url']) ?>" target="_blank" 
                                               class="bg-white text-green-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                                                <i class="ri-external-link-line ml-1"></i>
                                                צפה בחנות שלך
                                            </a>
                                            <a href="/admin/products/new.php" 
                                               class="bg-white/20 text-white px-4 py-2 rounded-lg font-medium hover:bg-white/30 transition-colors">
                                                <i class="ri-add-line ml-1"></i>
                                                הוסף מוצר ראשון
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button onclick="this.parentElement.parentElement.parentElement.style.display='none'" 
                                        class="text-white/70 hover:text-white transition-colors">
                                    <i class="ri-close-line text-xl"></i>
                                </button>
                        </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Trial Notice (if applicable) -->
                <?php if (isset($currentUser['subscription_status']) && $currentUser['subscription_status'] === 'trial'): ?>
                    <div class="mb-6">
                        <div class="card-rounded p-6 shadow-xl" style="background: linear-gradient(135deg, #ff9a56, #ff6b6b); color: white;">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                                    <i class="ri-time-line text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium">אתה בתקופת הניסיון</p>
                                    <p class="text-white/90 text-sm">תקופת הניסיון מסתיימת ב: <?php echo isset($currentUser['trial_ends_at']) ? date('d/m/Y', strtotime($currentUser['trial_ends_at'])) : 'לא ידוע'; ?></p>
                                </div>
                                <a href="/admin/billing/" class="bg-white text-orange-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                                    שדרג עכשיו
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    
                    <!-- Products -->
                    <div class="bg-white overflow-hidden shadow-xl card-rounded">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                        <i class="ri-shopping-bag-line text-xl text-white"></i>
                                    </div>
                                </div>
                                <div class="mr-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-600 truncate">מוצרים</dt>
                                        <dd class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['products']); ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-100">
                            <div class="text-sm">
                                <a href="/admin/products/new.php" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">הוסף מוצר חדש</a>
                            </div>
                        </div>
                    </div>

                    <!-- Orders -->
                    <div class="bg-white overflow-hidden shadow-xl card-rounded">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                                        <i class="ri-shopping-cart-line text-xl text-white"></i>
                                    </div>
                                </div>
                                <div class="mr-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-600 truncate">הזמנות היום</dt>
                                        <dd class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['orders']); ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-100">
                            <div class="text-sm">
                                <a href="/admin/orders/" class="font-medium text-green-600 hover:text-green-500 transition-colors">צפה בכל ההזמנות</a>
                            </div>
                        </div>
                    </div>

                    <!-- Customers -->
                    <div class="bg-white overflow-hidden shadow-xl card-rounded">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #fa709a, #fee140);">
                                        <i class="ri-user-line text-xl text-white"></i>
                                    </div>
                                </div>
                                <div class="mr-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-600 truncate">לקוחות</dt>
                                        <dd class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['customers']); ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-100">
                            <div class="text-sm">
                                <a href="/admin/customers/" class="font-medium text-purple-600 hover:text-purple-500 transition-colors">נהל לקוחות</a>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue -->
                    <div class="bg-white overflow-hidden shadow-xl card-rounded">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ff9a9e, #fad0c4);">
                                        <i class="ri-money-dollar-circle-line text-xl text-white"></i>
                                    </div>
                                </div>
                                <div class="mr-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-600 truncate">הכנסות החודש</dt>
                                        <dd class="text-2xl font-bold text-gray-900">₪<?php echo number_format($stats['revenue']); ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-100">
                            <div class="text-sm">
                                <a href="/admin/analytics/" class="font-medium text-orange-600 hover:text-orange-500 transition-colors">צפה בדוחות</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    
                    <!-- Sales Chart -->
                    <div class="bg-white shadow-xl card-rounded">
                        <div class="px-6 py-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">עסקאות שבועיות</h3>
                                <span class="text-sm text-gray-500">7 ימים אחרונים</span>
                            </div>
                            <div class="relative h-64">
                                <canvas id="salesChart" class="w-full h-full"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Chart -->
                    <div class="bg-white shadow-xl card-rounded">
                        <div class="px-6 py-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">מכירות שבועיות</h3>
                                <span class="text-sm text-gray-500">7 ימים אחרונים</span>
                            </div>
                            <div class="relative h-64">
                                <canvas id="revenueChart" class="w-full h-full"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="mb-8">
                    <div class="bg-white shadow-xl card-rounded">
                        <div class="px-6 py-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-medium text-gray-900">הזמנות אחרונות</h3>
                                <a href="/admin/orders/" class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                                    צפייה בכל ההזמנות
                                    <i class="ri-arrow-left-line mr-1"></i>
                                </a>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="border-b border-gray-200">
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">פעילות</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">תאריך</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">סטטוס</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">סכום</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">לקוח</th>
                                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500">מספר הזמנה</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 px-4">
                                                <i class="ri-eye-line text-blue-500"></i>
                                            </td>
                                            <td class="py-3 px-4 text-sm text-gray-900">16:21 09/07/2025</td>
                                            <td class="py-3 px-4">
                                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">שולם</span>
                                            </td>
                                            <td class="py-3 px-4 text-sm font-medium text-gray-900">₪2,375.00</td>
                                            <td class="py-3 px-4 text-sm text-gray-900">שירון שם טוב</td>
                                            <td class="py-3 px-4 text-sm text-gray-900">#14901</td>
                                        </tr>
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 px-4">
                                                <i class="ri-eye-line text-blue-500"></i>
                                            </td>
                                            <td class="py-3 px-4 text-sm text-gray-900">15:45 09/07/2025</td>
                                            <td class="py-3 px-4">
                                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">בטיפול</span>
                                            </td>
                                            <td class="py-3 px-4 text-sm font-medium text-gray-900">₪1,250.00</td>
                                            <td class="py-3 px-4 text-sm text-gray-900">דני כהן</td>
                                            <td class="py-3 px-4 text-sm text-gray-900">#14900</td>
                                        </tr>
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 px-4">
                                                <i class="ri-eye-line text-blue-500"></i>
                                            </td>
                                            <td class="py-3 px-4 text-sm text-gray-900">14:30 09/07/2025</td>
                                            <td class="py-3 px-4">
                                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">שולם</span>
                                            </td>
                                            <td class="py-3 px-4 text-sm font-medium text-gray-900">₪895.00</td>
                                            <td class="py-3 px-4 text-sm text-gray-900">רונית לוי</td>
                                            <td class="py-3 px-4 text-sm text-gray-900">#14899</td>
                                        </tr>
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 px-4">
                                                <i class="ri-eye-line text-blue-500"></i>
                                            </td>
                                            <td class="py-3 px-4 text-sm text-gray-900">13:15 09/07/2025</td>
                                            <td class="py-3 px-4">
                                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">בוטל</span>
                                            </td>
                                            <td class="py-3 px-4 text-sm font-medium text-gray-900">₪450.00</td>
                                            <td class="py-3 px-4 text-sm text-gray-900">מיכל דוד</td>
                                            <td class="py-3 px-4 text-sm text-gray-900">#14898</td>
                                        </tr>
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 px-4">
                                                <i class="ri-eye-line text-blue-500"></i>
                                            </td>
                                            <td class="py-3 px-4 text-sm text-gray-900">12:00 09/07/2025</td>
                                            <td class="py-3 px-4">
                                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">שולם</span>
                                            </td>
                                            <td class="py-3 px-4 text-sm font-medium text-gray-900">₪1,680.00</td>
                                            <td class="py-3 px-4 text-sm text-gray-900">אבי ישראל</td>
                                            <td class="py-3 px-4 text-sm text-gray-900">#14897</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Quick Actions Card -->
                    <div class="bg-white shadow-xl card-rounded">
                        <div class="px-6 py-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">פעולות מהירות</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <a href="/admin/products/new.php" class="p-4 border border-gray-200 rounded-xl hover:border-blue-300 hover:shadow-md transition-all hover:scale-105">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-3" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                        <i class="ri-add-circle-line text-lg text-white"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">הוסף מוצר</p>
                                </a>
                                <a href="/admin/orders/" class="p-4 border border-gray-200 rounded-xl hover:border-green-300 hover:shadow-md transition-all hover:scale-105">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-3" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                                        <i class="ri-shopping-cart-line text-lg text-white"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">הזמנות חדשות</p>
                                </a>
                                <a href="/admin/customers/" class="p-4 border border-gray-200 rounded-xl hover:border-purple-300 hover:shadow-md transition-all hover:scale-105">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-3" style="background: linear-gradient(135deg, #fa709a, #fee140);">
                                        <i class="ri-user-add-line text-lg text-white"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">הוסף לקוח</p>
                                </a>
                                <a href="/admin/coupons/" class="p-4 border border-gray-200 rounded-xl hover:border-orange-300 hover:shadow-md transition-all hover:scale-105">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-3" style="background: linear-gradient(135deg, #ff9a9e, #fad0c4);">
                                        <i class="ri-coupon-line text-lg text-white"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">צור קופון</p>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Getting Started -->
                    <div class="bg-white shadow-xl card-rounded">
                        <div class="px-6 py-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">בואו נתחיל</h3>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center ml-3" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                                        <i class="ri-check-line text-xs text-white"></i>
                                    </div>
                                    <span class="text-sm text-gray-600">החשבון שלך נוצר בהצלחה</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-300 ml-3"></div>
                                    <span class="text-sm text-gray-600">הוסף את המוצר הראשון שלך</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-300 ml-3"></div>
                                    <span class="text-sm text-gray-600">התאם את עיצוב החנות</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-300 ml-3"></div>
                                    <span class="text-sm text-gray-600">הגדר את אמצעי התשלום</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-300 ml-3"></div>
                                    <span class="text-sm text-gray-600">שתף את החנות שלך</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include 'templates/footer.php'; ?>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Sales Chart (Bar Chart)
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: ['03/07', '04/07', '05/07', '06/07', '07/07', '08/07', '09/07'],
                datasets: [{
                    label: 'עסקאות',
                    data: [7, 2, 1, 6, 7, 3, 7],
                    backgroundColor: '#4FD1C7',
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 8,
                        grid: {
                            display: true,
                            color: '#f3f4f6'
                        },
                        ticks: {
                            color: '#6b7280'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b7280'
                        }
                    }
                }
            }
        });

        // Revenue Chart (Line Chart)
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['03/07', '04/07', '05/07', '06/07', '07/07', '08/07', '09/07'],
                datasets: [{
                    label: 'מכירות',
                    data: [32000, 5000, 5000, 29000, 23000, 8000, 27000],
                    borderColor: '#4FD1C7',
                    backgroundColor: 'rgba(79, 209, 199, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#4FD1C7',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 35000,
                        grid: {
                            display: true,
                            color: '#f3f4f6'
                        },
                        ticks: {
                            color: '#6b7280',
                            callback: function(value) {
                                return (value / 1000) + 'K';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b7280'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 