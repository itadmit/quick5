<?php
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = 'הגדרות אנליטיקס ופיקסלים';
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
                case 'add_pixel':
                    $stmt = $db->prepare("
                        INSERT INTO analytics_pixels (store_id, pixel_type, name, pixel_id, is_active) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $currentStore['id'],
                        $_POST['pixel_type'],
                        $_POST['name'],
                        $_POST['pixel_id'],
                        isset($_POST['is_active']) ? 1 : 0
                    ]);
                    $success = 'פיקסל נוסף בהצלחה!';
                    break;
                    
                case 'update_pixel':
                    $stmt = $db->prepare("
                        UPDATE analytics_pixels 
                        SET pixel_type = ?, name = ?, pixel_id = ?, is_active = ?
                        WHERE id = ? AND store_id = ?
                    ");
                    $stmt->execute([
                        $_POST['pixel_type'],
                        $_POST['name'],
                        $_POST['pixel_id'],
                        isset($_POST['is_active']) ? 1 : 0,
                        $_POST['pixel_id_edit'],
                        $currentStore['id']
                    ]);
                    $success = 'פיקסל עודכן בהצלחה!';
                    break;
                    
                case 'delete_pixel':
                    $stmt = $db->prepare("DELETE FROM analytics_pixels WHERE id = ? AND store_id = ?");
                    $stmt->execute([$_POST['pixel_id'], $currentStore['id']]);
                    $success = 'פיקסל נמחק בהצלחה!';
                    break;
            }
        }
    } catch (Exception $e) {
        $error = 'שגיאה: ' . $e->getMessage();
    }
}

// Get all pixels
$stmt = $db->prepare("SELECT * FROM analytics_pixels WHERE store_id = ? ORDER BY created_at DESC");
$stmt->execute([$currentStore['id']]);
$pixels = $stmt->fetchAll();

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
                        <p class="text-gray-600 mt-2">ניהול פיקסלי מעקב, Google Analytics ופלטפורמות אנליטיקס</p>
                    </div>
                    <button onclick="openAddPixelModal()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-medium transition-colors shadow-lg">
                        <i class="ri-add-line ml-2"></i>
                        הוסף פיקסל חדש
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

            <!-- Pixels Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php foreach ($pixels as $pixel): ?>
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <?php
                                    $iconMap = [
                                        'google_analytics' => 'ri-google-line text-orange-500',
                                        'facebook_pixel' => 'ri-facebook-line text-blue-600',
                                        'google_ads' => 'ri-google-line text-green-500',
                                        'tiktok_pixel' => 'ri-tiktok-line text-black',
                                        'custom' => 'ri-code-line text-purple-500'
                                    ];
                                    $icon = $iconMap[$pixel['pixel_type']] ?? 'ri-code-line text-gray-500';
                                    ?>
                                    <i class="<?php echo $icon; ?> text-2xl ml-3"></i>
                                    <div>
                                        <h3 class="font-bold text-lg text-gray-900"><?php echo htmlspecialchars($pixel['name']); ?></h3>
                                        <p class="text-sm text-gray-500">
                                            <?php
                                            $typeNames = [
                                                'google_analytics' => 'Google Analytics',
                                                'facebook_pixel' => 'Facebook Pixel',
                                                'google_ads' => 'Google Ads',
                                                'tiktok_pixel' => 'TikTok Pixel',
                                                'custom' => 'פיקסל מותאם'
                                            ];
                                            echo $typeNames[$pixel['pixel_type']] ?? $pixel['pixel_type'];
                                            ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <?php if ($pixel['is_active']): ?>
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">פעיל</span>
                                    <?php else: ?>
                                        <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">לא פעיל</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-1">מזהה פיקסל:</p>
                                <code class="text-sm bg-gray-100 px-3 py-1 rounded font-mono"><?php echo htmlspecialchars($pixel['pixel_id']); ?></code>
                            </div>
                            
                            <div class="flex gap-2">
                                <button onclick="editPixel(<?php echo htmlspecialchars(json_encode($pixel)); ?>)" 
                                        class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-600 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                    <i class="ri-edit-line ml-1"></i>
                                    עריכה
                                </button>
                                <button onclick="deletePixel(<?php echo $pixel['id']; ?>, '<?php echo htmlspecialchars($pixel['name']); ?>')" 
                                        class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                    <i class="ri-delete-bin-line ml-1"></i>
                                    מחיקה
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($pixels)): ?>
                    <div class="col-span-full">
                        <div class="text-center py-12">
                            <i class="ri-pie-chart-line text-6xl text-gray-300 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-500 mb-2">אין פיקסלי מעקב</h3>
                            <p class="text-gray-400 mb-6">התחל להוסיף פיקסלי מעקב כדי לנתח את התנועה באתר שלך</p>
                            <button onclick="openAddPixelModal()" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-medium transition-colors">
                                <i class="ri-add-line ml-2"></i>
                                הוסף פיקסל ראשון
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Guide Section -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="ri-book-open-line ml-2"></i>
                    מדריך הגדרת פיקסלים
                </h2>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h3 class="font-semibold text-gray-800">Google Analytics</h3>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <i class="ri-arrow-left-s-line text-blue-500 mt-1 ml-1"></i>
                                צור חשבון ב-Google Analytics
                            </li>
                            <li class="flex items-start">
                                <i class="ri-arrow-left-s-line text-blue-500 mt-1 ml-1"></i>
                                העתק את ה-Measurement ID (מתחיל ב-GA-)
                            </li>
                            <li class="flex items-start">
                                <i class="ri-arrow-left-s-line text-blue-500 mt-1 ml-1"></i>
                                הדבק כאן והפעל את הפיקסל
                            </li>
                        </ul>
                        
                        <h3 class="font-semibold text-gray-800 pt-4">Facebook Pixel</h3>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <i class="ri-arrow-left-s-line text-blue-500 mt-1 ml-1"></i>
                                היכנס ל-Facebook Business Manager
                            </li>
                            <li class="flex items-start">
                                <i class="ri-arrow-left-s-line text-blue-500 mt-1 ml-1"></i>
                                צור פיקסל חדש בסקציית Events Manager
                            </li>
                            <li class="flex items-start">
                                <i class="ri-arrow-left-s-line text-blue-500 mt-1 ml-1"></i>
                                העתק את מזהה הפיקסל (מספר 15-16 ספרות)
                            </li>
                        </ul>
                    </div>
                    
                    <div class="space-y-4">
                        <h3 class="font-semibold text-gray-800">TikTok Pixel</h3>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <i class="ri-arrow-left-s-line text-purple-500 mt-1 ml-1"></i>
                                היכנס ל-TikTok Ads Manager
                            </li>
                            <li class="flex items-start">
                                <i class="ri-arrow-left-s-line text-purple-500 mt-1 ml-1"></i>
                                בחר במסך Assets ולאחר מכן Events
                            </li>
                            <li class="flex items-start">
                                <i class="ri-arrow-left-s-line text-purple-500 mt-1 ml-1"></i>
                                צור פיקסל חדש והעתק את המזהה
                            </li>
                        </ul>
                        
                        <h3 class="font-semibold text-gray-800 pt-4">פיקסל מותאם</h3>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <i class="ri-arrow-left-s-line text-gray-500 mt-1 ml-1"></i>
                                עבור כל פיקסל או קוד מעקב אחר
                            </li>
                            <li class="flex items-start">
                                <i class="ri-arrow-left-s-line text-gray-500 mt-1 ml-1"></i>
                                הכנס את הקוד המלא או מזהה
                            </li>
                            <li class="flex items-start">
                                <i class="ri-arrow-left-s-line text-gray-500 mt-1 ml-1"></i>
                                השתמש בשדה "קוד מעקב" למקרים מורכבים
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add/Edit Pixel Modal -->
<div id="pixelModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 id="modalTitle" class="text-xl font-bold text-gray-900">הוסף פיקסל חדש</h3>
                    <button onclick="closePixelModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="ri-close-line text-2xl"></i>
                    </button>
                </div>
                
                <form id="pixelForm" method="POST">
                    <input type="hidden" name="action" id="formAction" value="add_pixel">
                    <input type="hidden" name="pixel_id_edit" id="pixelIdEdit">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">סוג פיקסל</label>
                            <select name="pixel_type" id="pixelType" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">בחר סוג פיקסל</option>
                                <option value="google_analytics">Google Analytics</option>
                                <option value="facebook_pixel">Facebook Pixel</option>
                                <option value="google_ads">Google Ads</option>
                                <option value="tiktok_pixel">TikTok Pixel</option>
                                <option value="custom">פיקסל מותאם</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">שם הפיקסל</label>
                            <input type="text" name="name" id="pixelName" required 
                                   placeholder="למשל: Google Analytics - אתר ראשי"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">מזהה פיקסל</label>
                            <input type="text" name="pixel_id" id="pixelIdValue" required 
                                   placeholder="למשל: GA-XXXXXXXXX-X"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono">
                            <p class="text-xs text-gray-500 mt-1">הכנס את מזהה הפיקסל שקיבלת מהפלטפורמה</p>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="pixelActive" checked 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded ml-3">
                            <label for="pixelActive" class="text-sm font-medium text-gray-700">פיקסל פעיל</label>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6">
                        <button type="submit" 
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-medium transition-colors">
                            <span id="submitText">הוסף פיקסל</span>
                        </button>
                        <button type="button" onclick="closePixelModal()" 
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
function openAddPixelModal() {
    document.getElementById('modalTitle').textContent = 'הוסף פיקסל חדש';
    document.getElementById('formAction').value = 'add_pixel';
    document.getElementById('submitText').textContent = 'הוסף פיקסל';
    document.getElementById('pixelForm').reset();
    document.getElementById('pixelActive').checked = true;
    document.getElementById('pixelModal').classList.remove('hidden');
}

function editPixel(pixel) {
    document.getElementById('modalTitle').textContent = 'עריכת פיקסל';
    document.getElementById('formAction').value = 'update_pixel';
    document.getElementById('submitText').textContent = 'עדכן פיקסל';
    document.getElementById('pixelIdEdit').value = pixel.id;
    document.getElementById('pixelType').value = pixel.pixel_type;
    document.getElementById('pixelName').value = pixel.name;
    document.getElementById('pixelIdValue').value = pixel.pixel_id;
    document.getElementById('pixelActive').checked = pixel.is_active == 1;
    document.getElementById('pixelModal').classList.remove('hidden');
}

function closePixelModal() {
    document.getElementById('pixelModal').classList.add('hidden');
}

function deletePixel(id, name) {
    if (confirm(`האם אתה בטוח שברצונך למחוק את הפיקסל "${name}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_pixel">
            <input type="hidden" name="pixel_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-format pixel ID based on type
document.getElementById('pixelType').addEventListener('change', function() {
    const pixelIdInput = document.getElementById('pixelIdValue');
    const nameInput = document.getElementById('pixelName');
    
    switch(this.value) {
        case 'google_analytics':
            pixelIdInput.placeholder = 'GA-XXXXXXXXX-X';
            nameInput.placeholder = 'Google Analytics - אתר ראשי';
            break;
        case 'facebook_pixel':
            pixelIdInput.placeholder = '123456789012345';
            nameInput.placeholder = 'Facebook Pixel - קמפיין ראשי';
            break;
        case 'google_ads':
            pixelIdInput.placeholder = 'AW-XXXXXXXXX';
            nameInput.placeholder = 'Google Ads - קמפיין ראשי';
            break;
        case 'tiktok_pixel':
            pixelIdInput.placeholder = 'ABCDEFGHIJKLMNOP';
            nameInput.placeholder = 'TikTok Pixel - קמפיין ראשי';
            break;
        case 'custom':
            pixelIdInput.placeholder = 'קוד או מזהה מותאם';
            nameInput.placeholder = 'פיקסל מותאם';
            break;
        default:
            pixelIdInput.placeholder = 'מזהה פיקסל';
            nameInput.placeholder = 'שם הפיקסל';
    }
});

// Close modal on outside click
document.getElementById('pixelModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePixelModal();
    }
});
</script>

<?php include '../../admin/templates/footer.php'; ?> 