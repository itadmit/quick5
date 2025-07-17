<?php
// קבלת מידע הקטגוריה מהגלובלים
$category = $GLOBALS['CURRENT_CATEGORY'] ?? null;
$store = $GLOBALS['CURRENT_STORE'] ?? null;

if (!$store) {
    header('HTTP/1.1 404 Not Found');
    exit('Store not found');
}

// הגדרת משתנים לדף
$currentPage = 'category';
$pageTitle = $category ? $category['name'] : 'כל המוצרים';
$pageDescription = $category ? ($category['description'] ?: 'רשימת מוצרים בקטגוריית ' . $category['name']) : 'כל המוצרים בחנות ' . $store['name'];

// פילטרים ומיון
$sortBy = $_GET['sort'] ?? 'newest';
$minPrice = $_GET['min_price'] ?? null;
$maxPrice = $_GET['max_price'] ?? null;
$selectedVariants = $_GET['variants'] ?? [];
$page = (int)($_GET['page'] ?? 1);
$limit = 12;
$offset = ($page - 1) * $limit;

// בניית שאילתה
$whereConditions = ["p.store_id = ? AND p.status = 'active'"];
$params = [$store['id']];

if ($category) {
    $whereConditions[] = "pc.category_id = ?";
    $params[] = $category['id'];
}

if ($minPrice) {
    $whereConditions[] = "p.price >= ?";
    $params[] = $minPrice;
}

if ($maxPrice) {
    $whereConditions[] = "p.price <= ?";
    $params[] = $maxPrice;
}

// פילטר וריאציות
if (!empty($selectedVariants)) {
    $variantConditions = [];
    foreach ($selectedVariants as $attributeName => $values) {
        if (!empty($values)) {
            $valuePlaceholders = str_repeat('?,', count($values) - 1) . '?';
            $variantConditions[] = "
                EXISTS (
                    SELECT 1 FROM product_variants pv
                    JOIN variant_attribute_values vav ON pv.id = vav.variant_id
                    JOIN attribute_values av ON vav.attribute_value_id = av.id
                    JOIN product_attributes pa ON av.attribute_id = pa.id
                    WHERE pv.product_id = p.id 
                    AND pa.name = ? 
                    AND av.value IN ($valuePlaceholders)
                    AND pv.is_active = 1
                )
            ";
            $params[] = $attributeName;
            $params = array_merge($params, $values);
        }
    }
    if (!empty($variantConditions)) {
        $whereConditions = array_merge($whereConditions, $variantConditions);
    }
}

$whereClause = implode(' AND ', $whereConditions);

// מיון
$orderBy = match($sortBy) {
    'price_low' => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'name' => 'p.name ASC',
    'oldest' => 'p.created_at ASC',
    default => 'p.created_at DESC'
};

// קבלת מוצרים
try {
    $db = Database::getInstance()->getConnection();
    
    $categoryJoin = $category ? "JOIN product_categories pc ON p.id = pc.product_id" : "";
    
    // ספירת סה"כ מוצרים
    $countQuery = "
        SELECT COUNT(DISTINCT p.id)
        FROM products p
        $categoryJoin
        WHERE $whereClause
    ";
    $stmt = $db->prepare($countQuery);
    $stmt->execute($params);
    $totalProducts = $stmt->fetchColumn();
    
    // קבלת המוצרים
    $query = "
        SELECT DISTINCT p.*, pm.url as image 
        FROM products p
        $categoryJoin
        LEFT JOIN product_media pm ON p.id = pm.product_id AND pm.is_primary = 1
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT $limit OFFSET $offset
    ";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalPages = ceil($totalProducts / $limit);
} catch (Exception $e) {
    $products = [];
    $totalProducts = 0;
    $totalPages = 0;
}

// קבלת כל הקטגוריות לפילטר
try {
    $stmt = $db->prepare("
        SELECT c.*, COUNT(pc.product_id) as product_count
        FROM categories c
        LEFT JOIN product_categories pc ON c.id = pc.category_id
        LEFT JOIN products p ON pc.product_id = p.id AND p.status = 'active'
        WHERE c.store_id = ?
        GROUP BY c.id
        HAVING product_count > 0
        ORDER BY c.name ASC
    ");
    $stmt->execute([$store['id']]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];
}

// קבלת מאפיינים זמינים לפילטרים
try {
    $variantAttributesQuery = "
        SELECT DISTINCT pa.name, pa.display_name, pa.type, av.value, av.display_value, av.color_hex, 
               COUNT(DISTINCT p.id) as product_count
        FROM product_attributes pa
        JOIN attribute_values av ON pa.id = av.attribute_id
        JOIN variant_attribute_values vav ON av.id = vav.attribute_value_id
        JOIN product_variants pv ON vav.variant_id = pv.id
        JOIN products p ON pv.product_id = p.id
        WHERE p.store_id = ? AND p.status = 'active' AND pv.is_active = 1
    ";
    
    if ($category) {
        $variantAttributesQuery .= " AND EXISTS (SELECT 1 FROM product_categories pc WHERE pc.product_id = p.id AND pc.category_id = ?)";
        $variantParams = [$store['id'], $category['id']];
    } else {
        $variantParams = [$store['id']];
    }
    
    $variantAttributesQuery .= "
        GROUP BY pa.name, av.value
        HAVING product_count > 0
        ORDER BY pa.name ASC, av.sort_order ASC, av.value ASC
    ";
    
    $stmt = $db->prepare($variantAttributesQuery);
    $stmt->execute($variantParams);
    $variantAttributesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ארגון המאפיינים לפי שם
    $variantAttributes = [];
    foreach ($variantAttributesData as $row) {
        $attributeName = $row['name'];
        if (!isset($variantAttributes[$attributeName])) {
            $variantAttributes[$attributeName] = [
                'name' => $row['name'],
                'display_name' => $row['display_name'],
                'type' => $row['type'],
                'values' => []
            ];
        }
        $variantAttributes[$attributeName]['values'][] = [
            'value' => $row['value'],
            'display_value' => $row['display_value'],
            'color_hex' => $row['color_hex'],
            'product_count' => $row['product_count']
        ];
    }
} catch (Exception $e) {
    $variantAttributes = [];
}

// CSS נוסף לפילטרים
$additionalCSS = '
<style>
/* סגנון מותאם לפילטרי הוריאציות */
.variant-filter-item {
    transition: all 0.2s ease-in-out;
}

.variant-filter-item:hover {
    background-color: rgba(59, 130, 246, 0.05);
    border-radius: 0.375rem;
}

/* סגנון למשבצות סימון */
.variant-filter-item input[type="checkbox"] {
    transition: all 0.2s ease-in-out;
}

.variant-filter-item input[type="checkbox"]:checked {
    background-color: var(--primary-color, #3B82F6);
    border-color: var(--primary-color, #3B82F6);
}

/* סגנון לצבעים */
.color-swatch {
    box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease-in-out;
}

.color-swatch:hover {
    transform: scale(1.1);
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

/* סגנון לתגיות פילטרים פעילים */
.active-filter-tag {
    animation: slideInFromTop 0.3s ease-out;
}

@keyframes slideInFromTop {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* סגנון לפילטרים בסרגל הצד */
.filters-sidebar {
    max-height: calc(100vh - 8rem);
    overflow-y: auto;
}

.filters-sidebar::-webkit-scrollbar {
    width: 4px;
}

.filters-sidebar::-webkit-scrollbar-track {
    background: #f1f5f9;
}

.filters-sidebar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

.filters-sidebar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* אנימציה לטעינת מוצרים */
.products-grid {
    opacity: 1;
    transition: opacity 0.3s ease-in-out;
}

.products-grid.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* רספונסיביות לפילטרים */
@media (max-width: 1024px) {
    .filters-sidebar {
        max-height: none;
        position: static;
    }
}
</style>
';

// Include header
include __DIR__ . '/header.php';
?>

    <!-- Category Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-4 space-x-reverse">
                <li>
                    <a href="/" class="text-gray-500 hover:text-gray-700">בית</a>
                </li>
                <li>
                    <i class="ri-arrow-left-s-line text-gray-400"></i>
                </li>
                <li class="text-gray-900 font-medium"><?= htmlspecialchars($pageTitle) ?></li>
            </ol>
        </nav>

        <!-- Category Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4"><?= htmlspecialchars($pageTitle) ?></h1>
            <?php if ($category && $category['description']): ?>
                <p class="text-gray-600 max-w-3xl"><?= htmlspecialchars($category['description']) ?></p>
            <?php endif; ?>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Filters -->
            <div class="lg:w-1/4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-24 filters-sidebar">
                    <h3 class="text-lg font-semibold mb-4">מסננים</h3>
                    
                    <form method="GET" id="filters-form">
                        <?php if (isset($_GET['sort'])): ?>
                            <input type="hidden" name="sort" value="<?= htmlspecialchars($_GET['sort']) ?>">
                        <?php endif; ?>
                        
                        <!-- Categories -->
                        <?php if ($categories && !$category): ?>
                            <div class="mb-6">
                                <h4 class="font-medium mb-3">קטגוריות</h4>
                                <div class="space-y-2">
                                    <?php foreach ($categories as $cat): ?>
                                        <a href="/category/<?= htmlspecialchars($cat['slug']) ?>" 
                                           class="flex items-center justify-between text-gray-600 hover:text-primary">
                                            <span><?= htmlspecialchars($cat['name']) ?></span>
                                            <span class="text-sm bg-gray-100 px-2 py-1 rounded"><?= $cat['product_count'] ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Variant Filters -->
                        <?php if (!empty($variantAttributes)): ?>
                            <?php foreach ($variantAttributes as $attribute): ?>
                                <div class="mb-6">
                                    <h4 class="font-medium mb-3"><?= htmlspecialchars($attribute['display_name']) ?></h4>
                                    <div class="space-y-2">
                                        <?php foreach ($attribute['values'] as $value): ?>
                                            <?php 
                                            $isSelected = isset($selectedVariants[$attribute['name']]) && 
                                                         in_array($value['value'], $selectedVariants[$attribute['name']]);
                                            $checkboxName = "variants[{$attribute['name']}][]";
                                            ?>
                                            <label class="flex items-center cursor-pointer group variant-filter-item p-2 -m-2">
                                                <input type="checkbox" 
                                                       name="<?= htmlspecialchars($checkboxName) ?>" 
                                                       value="<?= htmlspecialchars($value['value']) ?>"
                                                       <?= $isSelected ? 'checked' : '' ?>
                                                       class="rounded border-gray-300 text-primary focus:ring-primary focus:ring-offset-0 ml-2">
                                                
                                                <?php if ($attribute['type'] === 'color' && $value['color_hex']): ?>
                                                    <span class="w-4 h-4 rounded-full border border-gray-300 ml-2 color-swatch" 
                                                          style="background-color: <?= htmlspecialchars($value['color_hex']) ?>"></span>
                                                <?php endif; ?>
                                                
                                                <span class="flex-1 text-gray-700 group-hover:text-gray-900">
                                                    <?= htmlspecialchars($value['display_value']) ?>
                                                </span>
                                                
                                                <span class="text-sm text-gray-500">
                                                    (<?= $value['product_count'] ?>)
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Price Range -->
                        <div class="mb-6">
                            <h4 class="font-medium mb-3">טווח מחירים</h4>
                            <div class="flex space-x-2 space-x-reverse">
                                <input type="number" 
                                       name="min_price" 
                                       placeholder="מחיר מינימום"
                                       value="<?= htmlspecialchars($minPrice ?? '') ?>"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <input type="number" 
                                       name="max_price" 
                                       placeholder="מחיר מקסימום"
                                       value="<?= htmlspecialchars($maxPrice ?? '') ?>"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <!-- Filter Actions -->
                        <div class="flex space-x-3 space-x-reverse">
                            <button type="submit" class="flex-1 btn-primary text-white py-2 px-4 rounded-lg hover:opacity-90 transition-opacity">
                                סנן
                            </button>
                            <a href="<?= $category ? '/category/' . $category['slug'] : '/category/all' ?>" 
                               class="flex-1 text-center bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors">
                                נקה
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="lg:w-3/4">
                <!-- Active Filters -->
                <?php 
                $hasActiveFilters = !empty($selectedVariants) || !empty($minPrice) || !empty($maxPrice);
                if ($hasActiveFilters): 
                ?>
                    <div class="mb-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm text-gray-600 font-medium">פילטרים פעילים:</span>
                            
                            <!-- Variant Filters -->
                            <?php foreach ($selectedVariants as $attributeName => $values): ?>
                                <?php foreach ($values as $value): ?>
                                    <?php 
                                    // חיפוש display_name של המאפיין
                                    $attributeDisplayName = $attributeName;
                                    foreach ($variantAttributes as $attr) {
                                        if ($attr['name'] === $attributeName) {
                                            $attributeDisplayName = $attr['display_name'];
                                            break;
                                        }
                                    }
                                    ?>
                                    <span class="inline-flex items-center gap-1 bg-primary/10 text-primary px-3 py-1 rounded-full text-sm active-filter-tag">
                                        <?= htmlspecialchars($attributeDisplayName) ?>: <?= htmlspecialchars($value) ?>
                                        <button type="button" 
                                                onclick="removeVariantFilter('<?= htmlspecialchars($attributeName) ?>', '<?= htmlspecialchars($value) ?>')"
                                                class="hover:bg-primary/20 rounded-full p-0.5">
                                            <i class="ri-close-line text-xs"></i>
                                        </button>
                                    </span>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            
                            <!-- Price Filter -->
                            <?php if (!empty($minPrice) || !empty($maxPrice)): ?>
                                <span class="inline-flex items-center gap-1 bg-primary/10 text-primary px-3 py-1 rounded-full text-sm active-filter-tag">
                                    מחיר: 
                                    <?php if (!empty($minPrice)): ?>₪<?= number_format($minPrice) ?><?php endif; ?>
                                    <?php if (!empty($minPrice) && !empty($maxPrice)): ?> - <?php endif; ?>
                                    <?php if (!empty($maxPrice)): ?>₪<?= number_format($maxPrice) ?><?php endif; ?>
                                    <button type="button" 
                                            onclick="clearPriceFilter()"
                                            class="hover:bg-primary/20 rounded-full p-0.5">
                                        <i class="ri-close-line text-xs"></i>
                                    </button>
                                </span>
                            <?php endif; ?>
                            
                            <!-- Clear All -->
                            <a href="<?= $category ? '/category/' . $category['slug'] : '/category/all' ?>" 
                               class="text-sm text-gray-500 hover:text-gray-700 underline">
                                נקה הכל
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Sort and Results Count -->
                <div class="flex items-center justify-between mb-6">
                    <div class="text-gray-600">
                        <span class="font-medium"><?= number_format($totalProducts) ?></span> מוצרים נמצאו
                    </div>
                    
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <label for="sort" class="text-sm text-gray-600">מיין לפי:</label>
                        <select id="sort" name="sort" onchange="updateSort(this.value)"
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>החדשים ביותר</option>
                            <option value="oldest" <?= $sortBy === 'oldest' ? 'selected' : '' ?>>הישנים ביותר</option>
                            <option value="price_low" <?= $sortBy === 'price_low' ? 'selected' : '' ?>>מחיר: נמוך לגבוה</option>
                            <option value="price_high" <?= $sortBy === 'price_high' ? 'selected' : '' ?>>מחיר: גבוה לנמוך</option>
                            <option value="name" <?= $sortBy === 'name' ? 'selected' : '' ?>>שם (א-ת)</option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <?php if ($products): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8 products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                                <div class="aspect-w-1 aspect-h-1 w-full">
                                    <?php if ($product['image']): ?>
                                        <img src="<?= htmlspecialchars($product['image']) ?>" 
                                             alt="<?= htmlspecialchars($product['name']) ?>"
                                             class="w-full h-48 object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                            <i class="ri-image-line text-4xl text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </h3>
                                    
                                    <?php if ($product['short_description']): ?>
                                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                                            <?= htmlspecialchars($product['short_description']) ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="text-xl font-bold text-primary">
                                                ₪<?= number_format($product['price'] ?? 0, 2) ?>
                                            </span>
                                            <?php if ($product['compare_price'] && $product['compare_price'] > ($product['price'] ?? 0)): ?>
                                                <span class="text-sm text-gray-500 line-through block">
                                                    ₪<?= number_format($product['compare_price'] ?? 0, 2) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <a href="/product/<?= htmlspecialchars($product['slug']) ?>" 
                                           class="btn-primary text-white px-4 py-2 rounded-lg hover:opacity-90 transition-opacity">
                                            צפה במוצר
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="flex items-center justify-center space-x-2 space-x-reverse">
                            <?php if ($page > 1): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                                   class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                    <i class="ri-arrow-right-line"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                                   class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 <?= $i === $page ? 'bg-primary text-white border-primary' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" 
                                   class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                    <i class="ri-arrow-left-line"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- No Products Found -->
                    <div class="text-center py-16">
                        <i class="ri-search-line text-6xl text-gray-400 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">לא נמצאו מוצרים</h3>
                        <p class="text-gray-600 mb-6">נסה לשנות את הפילטרים או לחפש משהו אחר</p>
                        <a href="/category/all" class="btn-primary text-white px-6 py-3 rounded-lg hover:opacity-90 transition-opacity">
                            צפה בכל המוצרים
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php
$additionalJS = '
<script>
function updateSort(sortValue) {
    const url = new URL(window.location);
    url.searchParams.set("sort", sortValue);
    url.searchParams.delete("page"); // Reset to first page
    window.location.href = url.toString();
}

// התמודדות עם שינויים בפילטרי הוריאציות
document.addEventListener("DOMContentLoaded", function() {
    const filtersForm = document.getElementById("filters-form");
    const variantCheckboxes = filtersForm.querySelectorAll("input[type=\"checkbox\"][name*=\"variants\"]");
    const priceInputs = filtersForm.querySelectorAll("input[type=\"number\"]");
    
    // עדכון אוטומטי כשמסמנים checkbox של וריאציה
    variantCheckboxes.forEach(checkbox => {
        checkbox.addEventListener("change", function() {
            showLoadingState();
            setTimeout(() => {
                filtersForm.submit();
            }, 100);
        });
    });
    
    // עדכון אוטומטי עם דיליי למחירים
    let priceTimeout;
    priceInputs.forEach(input => {
        input.addEventListener("input", function() {
            clearTimeout(priceTimeout);
            priceTimeout = setTimeout(() => {
                showLoadingState();
                filtersForm.submit();
            }, 1000); // המתנה של שנייה אחרי הפסקת הקלדה
        });
    });
    
    // מניעת טעינה מחדש בעת לחיצה על הכפתורים בתוך הטופס
    const filterButtons = filtersForm.querySelectorAll("button[type=\"submit\"]");
    filterButtons.forEach(button => {
        button.addEventListener("click", function(e) {
            // אם זה כפתור הסינון הרגיל, תן לו לעבוד רגיל
            if (this.textContent.trim() === "סנן") {
                return;
            }
            e.preventDefault();
        });
    });
});

// פונקציה לניקוי פילטרים ספציפיים
function clearVariantFilter(attributeName) {
    const checkboxes = document.querySelectorAll(`input[name="variants[${attributeName}][]"]`);
    checkboxes.forEach(cb => cb.checked = false);
    document.getElementById("filters-form").submit();
}

function clearPriceFilter() {
    document.querySelector("input[name=\"min_price\"]").value = "";
    document.querySelector("input[name=\"max_price\"]").value = "";
    showLoadingState();
    document.getElementById("filters-form").submit();
}

// פונקציה להסרת פילטר וריאציה ספציפי
function removeVariantFilter(attributeName, value) {
    const checkbox = document.querySelector(`input[name="variants[${attributeName}][]"][value="${value}"]`);
    if (checkbox) {
        checkbox.checked = false;
        showLoadingState();
        document.getElementById("filters-form").submit();
    }
}

// פונקציה להצגת מצב טעינה
function showLoadingState() {
    const productsGrid = document.querySelector(".products-grid");
    if (productsGrid) {
        productsGrid.classList.add("loading");
    }
}
</script>
';

// Include footer
include __DIR__ . '/footer.php';
?> 