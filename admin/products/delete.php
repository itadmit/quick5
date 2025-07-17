<?php
/**
 * מחיקת מוצר - QuickShop5 Admin
 */

require_once __DIR__ . '/../../includes/auth.php';

$auth = new Authentication();
$auth->requireLogin();

$currentUser = $auth->getCurrentUser();
if (!$currentUser) {
    header('Location: /admin/login.php');
    exit;
}

// קבלת ID המוצר
$productId = (int)($_GET['id'] ?? 0);
if (!$productId) {
    header('Location: /admin/products/?error=invalid_product');
    exit;
}

// קבלת מידע החנות
require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM stores WHERE user_id = ? LIMIT 1");
$stmt->execute([$currentUser['id']]);
$store = $stmt->fetch();

if (!$store) {
    header('Location: /admin/');
    exit;
}

// קבלת פרטי המוצר
$stmt = $db->prepare("
    SELECT * FROM products 
    WHERE id = ? AND store_id = ? 
    LIMIT 1
");
$stmt->execute([$productId, $store['id']]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: /admin/products/?error=product_not_found');
    exit;
}

// טיפול במחיקה
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $db->beginTransaction();
        
        // מחיקת נתונים קשורים
        $stmt = $db->prepare("DELETE FROM product_categories WHERE product_id = ?");
        $stmt->execute([$productId]);
        
        $stmt = $db->prepare("DELETE FROM product_media WHERE product_id = ?");
        $stmt->execute([$productId]);
        
        $stmt = $db->prepare("DELETE FROM product_attributes WHERE product_id = ?");
        $stmt->execute([$productId]);
        
        $stmt = $db->prepare("DELETE FROM product_variants WHERE product_id = ?");
        $stmt->execute([$productId]);
        
        $stmt = $db->prepare("DELETE FROM product_badges WHERE product_id = ?");
        $stmt->execute([$productId]);
        
        // אם יש טבלות נוספות מbeta features
        $tables = ['product_accordions', 'product_custom_fields', 'product_relationships', 'bundle_products'];
        foreach ($tables as $table) {
            try {
                $stmt = $db->prepare("DELETE FROM $table WHERE product_id = ?");
                $stmt->execute([$productId]);
            } catch (Exception $e) {
                // טבלה לא קיימת - בסדר
            }
        }
        
        // מחיקת המוצר עצמו
        $stmt = $db->prepare("DELETE FROM products WHERE id = ? AND store_id = ?");
        $stmt->execute([$productId, $store['id']]);
        
        $db->commit();
        
        header('Location: /admin/products/?deleted=1&message=' . urlencode('המוצר נמחק בהצלחה'));
        exit;
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'שגיאה במחיקת המוצר: ' . $e->getMessage();
    }
}

$pageTitle = 'מחיקת מוצר - ' . $product['name'];
?>

<?php include '../templates/header.php'; ?>
    
    <?php include '../templates/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="lg:pr-64">
        
        <?php include '../templates/navbar.php'; ?>

        <!-- Delete Product Content -->
        <main class="py-8" style="background: #e9f0f3;">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Back Button -->
                <div class="mb-6">
                    <a href="/admin/products/" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                        <i class="ri-arrow-right-line"></i>
                        חזור לרשימת מוצרים
                    </a>
                </div>

                <!-- Error Message -->
                <?php if (isset($error)): ?>
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="ri-error-warning-line text-red-500 ml-3"></i>
                            <span class="text-red-700"><?= htmlspecialchars($error) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Delete Confirmation -->
                <div class="bg-white shadow-xl card-rounded">
                    <div class="px-6 py-6">
                        
                        <!-- Warning Header -->
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center ml-4">
                                <i class="ri-error-warning-line text-2xl text-red-600"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-900">מחיקת מוצר</h1>
                                <p class="text-gray-600">פעולה זו אינה ניתנת לביטול</p>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                    <?php
                                    // קבלת תמונה ראשית של המוצר
                                    $stmt = $db->prepare("SELECT url FROM product_media WHERE product_id = ? AND is_primary = TRUE LIMIT 1");
                                    $stmt->execute([$productId]);
                                    $primaryImage = $stmt->fetchColumn();
                                    ?>
                                    <?php if ($primaryImage): ?>
                                        <img src="<?= htmlspecialchars($primaryImage) ?>" 
                                             alt="<?= htmlspecialchars($product['name']) ?>"
                                             class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center">
                                            <i class="ri-image-line text-gray-400 text-xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($product['name']) ?></h3>
                                    <div class="text-sm text-gray-600 space-y-1">
                                        <div>מק״ט: <?= htmlspecialchars($product['sku'] ?: 'ללא') ?></div>
                                        <div>מחיר: ₪<?= number_format($product['price'] ?? 0, 2) ?></div>
                                        <div>
                                            סטטוס: 
                                            <?php 
                                            $statusText = [
                                                'active' => 'פעיל',
                                                'draft' => 'טיוטה',
                                                'archived' => 'בארכיון'
                                            ];
                                            echo $statusText[$product['status']] ?? $product['status'];
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Warning Text -->
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                            <h3 class="font-medium text-red-800 mb-2">אזהרה חשובה!</h3>
                            <ul class="text-sm text-red-700 space-y-1">
                                <li>• המוצר יימחק לצמיתות מהמערכת</li>
                                <li>• כל הנתונים הקשורים יימחקו (תמונות, וריאציות, קטגוריות)</li>
                                <li>• המוצר יוסר מכל ההזמנות הקיימות</li>
                                <li>• הקישורים למוצר יפסיקו לעבוד</li>
                                <li>• פעולה זו לא ניתנת לביטול!</li>
                            </ul>
                        </div>

                        <!-- Statistics -->
                        <?php
                        // סטטיסטיקות שיימחקו
                        $stmt = $db->prepare("SELECT COUNT(*) FROM product_categories WHERE product_id = ?");
                        $stmt->execute([$productId]);
                        $categoriesCount = $stmt->fetchColumn();
                        
                        $stmt = $db->prepare("SELECT COUNT(*) FROM product_media WHERE product_id = ?");
                        $stmt->execute([$productId]);
                        $mediaCount = $stmt->fetchColumn();
                        
                        $stmt = $db->prepare("SELECT COUNT(*) FROM product_variants WHERE product_id = ?");
                        $stmt->execute([$productId]);
                        $variantsCount = $stmt->fetchColumn();
                        ?>

                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-lg font-bold text-gray-700"><?= $categoriesCount ?></div>
                                <div class="text-xs text-gray-500">קטגוריות</div>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-lg font-bold text-gray-700"><?= $mediaCount ?></div>
                                <div class="text-xs text-gray-500">קבצי מדיה</div>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-lg font-bold text-gray-700"><?= $variantsCount ?></div>
                                <div class="text-xs text-gray-500">וריאציות</div>
                            </div>
                        </div>

                        <!-- Confirmation Form -->
                        <form method="POST" class="space-y-4">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" id="confirm_understanding" required
                                       class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <label for="confirm_understanding" class="text-sm text-gray-700">
                                    אני מבין/ה שפעולה זו תמחק את המוצר לצמיתות ולא ניתן יהיה לשחזר אותו
                                </label>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <input type="checkbox" id="confirm_backup" required
                                       class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <label for="confirm_backup" class="text-sm text-gray-700">
                                    ביצעתי גיבוי של הנתונים החשובים לי (אם נדרש)
                                </label>
                            </div>

                            <div class="border-t border-gray-200 pt-6 mt-6">
                                <div class="flex items-center justify-between">
                                    <a href="/admin/products/" 
                                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                        בטל
                                    </a>
                                    
                                    <button type="submit" name="confirm_delete" value="1"
                                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        <i class="ri-delete-bin-line ml-1"></i>
                                        מחק מוצר לצמיתות
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </main>
    </div>

<?php include '../templates/footer.php'; ?> 