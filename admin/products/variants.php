<?php
/**
 * Variants & Attributes Component
 * משותף בין new.php ו-edit.php
 */

// פונקציה לגילוי צבע מהשם
function getColorFromNamePHP($colorName) {
    $knownColors = [
        'אדום' => '#DC2626',
        'כחול' => '#2563EB', 
        'ירוק' => '#16A34A',
        'צהוב' => '#EAB308',
        'כתום' => '#EA580C',
        'סגול' => '#9333EA',
        'ורוד' => '#EC4899',
        'חום' => '#A16207',
        'שחור' => '#000000',
        'לבן' => '#FFFFFF',
        'כחול בהיר' => '#3B82F6',
        'כחול כהה' => '#1E3A8A',
        'ירוק בהיר' => '#22C55E',
        'ירוק כהה' => '#15803D',
        'אדום בהיר' => '#EF4444',
        'אדום כהה' => '#B91C1C',
        'אפור' => '#6B7280',
        'אפור בהיר' => '#D1D5DB',
        'אפור כהה' => '#374151',
        'זהב' => '#F59E0B'
    ];
    
    $cleanName = trim(strtolower($colorName));
    
    // חיפוש ישיר
    foreach ($knownColors as $name => $color) {
        if (strtolower($name) === $cleanName) {
            return $color;
        }
    }
    
    // חיפוש חלקי
    foreach ($knownColors as $name => $color) {
        if (strpos($cleanName, strtolower($name)) !== false || strpos(strtolower($name), $cleanName) !== false) {
            return $color;
        }
    }
    
    // אם לא נמצא, החזר צבע ברירת מחדל
    return '#6B7280'; // אפור
}

// קבלת נתונים קיימים אם זה מוצר לעריכה
$hasVariants = $product['has_variants'] ?? false;
$existingAttributes = $attributes ?? [];
$existingVariants = [];

// קבלת וריאציות קיימות אם יש
if (isset($product) && $product['id']) {
    $stmt = Database::getInstance()->getConnection()->prepare("
        SELECT v.*, 
               GROUP_CONCAT(CONCAT(pa.name, ':', av.value) SEPARATOR ',') as attributes_str
        FROM product_variants v
        LEFT JOIN variant_attribute_values vav ON v.id = vav.variant_id
        LEFT JOIN attribute_values av ON vav.attribute_value_id = av.id
        LEFT JOIN product_attributes pa ON av.attribute_id = pa.id
        WHERE v.product_id = ?
        GROUP BY v.id
        ORDER BY v.is_default DESC, v.id ASC
    ");
    $stmt->execute([$product['id']]);
    $existingVariants = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!-- Variants & Attributes Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h2 class="text-lg font-medium text-gray-900 mb-4">וריאציות ומאפיינים</h2>
    
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">למוצר יש וריאציות (מידות, צבעים וכו')</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="has-variants" name="has_variants" value="1" 
                       <?= $hasVariants ? 'checked' : '' ?>
                       class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
        </div>
        
        <div id="variants-section" class="<?= $hasVariants ? '' : 'hidden' ?> space-y-4">
            
            <div id="attributes-container">
                <?php if (!empty($existingAttributes)): ?>
                    <?php foreach ($existingAttributes as $index => $attribute): ?>
                        <div class="attribute-item border border-gray-200 rounded-lg p-4 space-y-4" data-attribute-id="<?= $attribute['id'] ?>">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">שם המאפיין</label>
                                        <input type="text" name="attributes[<?= $index ?>][name]" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                            placeholder="למשל: צבע, מידה" value="<?= htmlspecialchars($attribute['name']) ?>">
                                        <input type="hidden" name="attributes[<?= $index ?>][id]" value="<?= $attribute['id'] ?>">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">סוג</label>
                                        <select name="attributes[<?= $index ?>][type]" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                            <option value="text" <?= $attribute['type'] === 'text' ? 'selected' : '' ?>>טקסט</option>
                                            <option value="color" <?= $attribute['type'] === 'color' ? 'selected' : '' ?>>צבע</option>
                                            <option value="image" <?= $attribute['type'] === 'image' ? 'selected' : '' ?>>תמונה</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="button" onclick="this.closest('.attribute-item').remove(); updateVariantsTable();" 
                                    class="text-red-500 hover:text-red-700 p-2 mr-4 mt-6 flex items-center justify-center">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                            
                            <div>
                                <div class="flex items-center gap-3 mb-3">
                                    <input type="checkbox" name="attributes[<?= $index ?>][is_variant]" value="1"
                                        id="is_variant_<?= $index ?>" checked
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                    <label for="is_variant_<?= $index ?>" class="text-sm text-gray-700">
                                        משפיע על וריאציות (יוצר מוצרים נפרדים)
                                    </label>
                                </div>
                            </div>
                            
                            <div class="attribute-values space-y-2">
                                <label class="block text-sm font-medium text-gray-700">ערכים</label>
                                <div class="values-container space-y-2">
                                    <?php if (!empty($attribute['values'])): ?>
                                        <?php foreach ($attribute['values'] as $valueIndex => $value): ?>
                                            <div class="flex items-center gap-2">
                                                <input type="text" name="attributes[<?= $index ?>][values][<?= $valueIndex ?>][value]" 
                                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                                    placeholder="ערך (למשל: אדום, XL)" 
                                                    value="<?= htmlspecialchars($value['value']) ?>">
                                                <input type="hidden" name="attributes[<?= $index ?>][values][<?= $valueIndex ?>][id]" 
                                                    value="<?= $value['id'] ?>">
                                                <input type="color" name="attributes[<?= $index ?>][values][<?= $valueIndex ?>][color]" 
                                                    class="w-8 h-8 border border-gray-300 rounded attribute-color <?= $attribute['type'] === 'color' ? '' : 'hidden' ?>"
                                                    value="<?= !empty($value['color_hex']) && $value['color_hex'] !== '' ? $value['color_hex'] : '#000000' ?>">
                                                <button type="button" class="attribute-image <?= $attribute['type'] === 'image' ? '' : 'hidden' ?> px-2 py-1 text-sm bg-blue-50 text-blue-600 border border-blue-200 rounded hover:bg-blue-100 transition-colors"
                                                    onclick="uploadAttributeImage(this, <?= $index ?>, <?= $valueIndex ?>)">
                                                    <i class="ri-image-add-line"></i>
                                                </button>
                                                <input type="hidden" name="attributes[<?= $index ?>][values][<?= $valueIndex ?>][image]" 
                                                    class="attribute-image-input" value="<?= $value['image'] ?? '' ?>">
                                                <button type="button" onclick="removeAttributeValue(this)" 
                                                    class="text-red-500 hover:text-red-700 p-1 flex items-center justify-center">
                                                    <i class="ri-close-line"></i>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <button type="button" onclick="addAttributeValue(this, <?= $index ?>)" 
                                    class="text-sm text-blue-600 hover:text-blue-700 flex items-center gap-2">
                                    <div class="w-4 h-4 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="ri-add-line text-blue-600 text-xs"></i>
                                    </div>
                                    הוסף ערך
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <button type="button" id="add-attribute" 
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <div class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="ri-add-line text-blue-600 text-xs"></i>
                </div>
                הוסף מאפיין
            </button>
            
            <!-- Variants Table Container - יתווסף על ידי JavaScript -->
            <?php if (!empty($existingVariants)): ?>
                <div class="variants-table-container mt-6 bg-gray-50 rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="ri-grid-line text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">טבלת וריאציות</h3>
                            <p class="text-sm text-gray-600"><?= count($existingVariants) ?> וריאציות קיימות</p>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full bg-white rounded-lg shadow-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <?php
                                    // בניית עמודות המאפיינים
                                    $attributeNames = [];
                                    foreach ($existingAttributes as $attr) {
                                        if (!empty($attr['values'])) {
                                            $attributeNames[] = $attr['name'];
                                            echo '<th class="px-4 py-3 text-right text-sm font-medium text-gray-700">' . htmlspecialchars($attr['name']) . '</th>';
                                        }
                                    }
                                    ?>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">SKU</th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">מחיר</th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">מחיר עלות</th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">מלאי</th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">ברירת מחדל</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($existingVariants as $index => $variant): ?>
                                    <tr class="hover:bg-gray-50">
                                        <?php
                                        // פירוק מחרוזת המאפיינים בצורה טובה יותר
                                        $variantAttributes = [];
                                        if (!empty($variant['attributes_str'])) {
                                            // ניסוי לפרסם JSON אם יש
                                            $decoded = json_decode($variant['attributes_str'], true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                $variantAttributes = $decoded;
                                            } else {
                                                // פירוק מחרוזת רגילה
                                                $pairs = explode(',', trim($variant['attributes_str']));
                                                foreach ($pairs as $pair) {
                                                    $parts = explode(':', trim($pair), 2); // limit to 2 parts
                                                    if (count($parts) == 2) {
                                                        $key = trim($parts[0]);
                                                        $value = trim($parts[1]);
                                                        if (!empty($key) && !empty($value)) {
                                                            $variantAttributes[$key] = $value;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        

                                        
                                        // הצגת ערכי המאפיינים
                                        foreach ($attributeNames as $attrName) {
                                            $value = $variantAttributes[$attrName] ?? '';
                                            
                                            // בדיקה אם המאפיין הוא צבע
                                            $isColorAttribute = false;
                                            $colorValue = null;
                                            
                                            foreach ($existingAttributes as $attr) {
                                                if ($attr['name'] === $attrName && $attr['type'] === 'color') {
                                                    $isColorAttribute = true;
                                                    
                                                    // חיפוש הצבע המתאים
                                                    foreach ($attr['values'] as $attrValue) {
                                                        if ($attrValue['value'] === $value) {
                                                            $colorValue = $attrValue['color_hex'] ?? null;
                                                            break;
                                                        }
                                                    }
                                                    
                                                    // אם לא נמצא צבע, נסה לגלות מהשם
                                                    if (!$colorValue) {
                                                        $colorValue = getColorFromNamePHP($value);
                                                    }
                                                    break;
                                                }
                                            }
                                            
                                            // הצגה עם או בלי עיגול צבע
                                            if ($isColorAttribute && $colorValue) {
                                                echo '<td class="px-4 py-3 text-sm text-gray-900">';
                                                echo '<div class="flex items-center gap-2">';
                                                echo '<div class="w-5 h-5 rounded-full border-2 border-gray-300 shadow-sm flex-shrink-0" style="background-color: ' . htmlspecialchars($colorValue) . ';" title="' . htmlspecialchars($value) . '"></div>';
                                                echo '<span>' . htmlspecialchars($value) . '</span>';
                                                echo '</div>';
                                                echo '</td>';
                                            } else {
                                                echo '<td class="px-4 py-3 text-sm text-gray-900">' . htmlspecialchars($value) . '</td>';
                                            }
                                            
                                            echo '<input type="hidden" name="variants[' . $index . '][attributes][' . htmlspecialchars($attrName) . ']" value="' . htmlspecialchars($value) . '">';
                                        }
                                        ?>
                                        <td class="px-4 py-3">
                                            <input type="text" name="variants[<?= $index ?>][sku]" 
                                                class="w-full px-2 py-1 text-sm border border-gray-300 rounded"
                                                placeholder="SKU" value="<?= htmlspecialchars($variant['sku'] ?? '') ?>">
                                            <input type="hidden" name="variants[<?= $index ?>][id]" value="<?= $variant['id'] ?>">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" name="variants[<?= $index ?>][price]" step="0.01"
                                                class="w-20 px-2 py-1 text-sm border border-gray-300 rounded"
                                                placeholder="0.00" value="<?= $variant['price'] ? (floor($variant['price']) == $variant['price'] ? number_format($variant['price'], 0) : number_format($variant['price'], 2, '.', '')) : '' ?>">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" name="variants[<?= $index ?>][cost_price]" step="0.01"
                                                class="w-20 px-2 py-1 text-sm border border-gray-300 rounded"
                                                placeholder="0.00" value="<?= $variant['cost_price'] ? (floor($variant['cost_price']) == $variant['cost_price'] ? number_format($variant['cost_price'], 0) : number_format($variant['cost_price'], 2, '.', '')) : '' ?>">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" name="variants[<?= $index ?>][inventory_quantity]"
                                                class="w-16 px-2 py-1 text-sm border border-gray-300 rounded"
                                                placeholder="0" value="<?= $variant['inventory_quantity'] ?? 0 ?>">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="radio" name="default_variant" value="<?= $index ?>"
                                                <?= $variant['is_default'] ? 'checked' : '' ?>
                                                class="w-4 h-4 text-blue-600">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="bulkEditVariants('price')" 
                                class="px-3 py-1.5 text-sm bg-blue-50 text-blue-600 border border-blue-200 rounded hover:bg-blue-100 transition-colors">
                                <i class="ri-edit-line ml-1"></i>
                                עריכה קבוצתית - מחיר
                            </button>
                            <button type="button" onclick="bulkEditVariants('inventory_quantity')" 
                                class="px-3 py-1.5 text-sm bg-green-50 text-green-600 border border-green-200 rounded hover:bg-green-100 transition-colors">
                                <i class="ri-edit-line ml-1"></i>
                                עריכה קבוצתית - מלאי
                            </button>
                            <button type="button" onclick="bulkEditVariants('sku')" 
                                class="px-3 py-1.5 text-sm bg-purple-50 text-purple-600 border border-purple-200 rounded hover:bg-purple-100 transition-colors">
                                <i class="ri-edit-line ml-1"></i>
                                עריכה קבוצתית - SKU
                            </button>
                        </div>
                        <div class="text-sm text-gray-500">
                            <?= count($existingVariants) ?> וריאציות
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
        // עדכון מונה המאפיינים לפי הנתונים הקיימים - רק פעם אחת
        if (typeof window.productForm !== 'undefined' && !window.attributeCountSet) {
            window.productForm.attributeCount = <?= count($existingAttributes) ?>;
            window.attributeCountSet = true;
        }

        // טעינת טבלת וריאציות בעמוד עריכה - רק פעם אחת
        document.addEventListener('DOMContentLoaded', function() {
            // מניעת טעינה כפולה
            if (window.variantsInitialized) return;
            window.variantsInitialized = true;
            
            <?php if ($hasVariants): ?>
                // אם יש וריאציות, נפעיל את המצב הנכון
                const hasVariantsCheckbox = document.getElementById('has-variants');
                if (hasVariantsCheckbox && !hasVariantsCheckbox.checked) {
                    hasVariantsCheckbox.checked = true;
                    hasVariantsCheckbox.dispatchEvent(new Event('change'));
                }
                
                <?php if (!empty($existingAttributes)): ?>
                    // אם יש מאפיינים קיימים, נעדכן את הטבלה - רק אם עדיין לא נטען
                    setTimeout(() => {
                        // בדוק אם כבר יש מאפיינים ב-DOM
                        const container = document.getElementById('variants-section');
                        const attributesContainer = document.getElementById('attributes-container');
                        
                        if (container && attributesContainer) {
                            const existingAttributeItems = attributesContainer.querySelectorAll('.attribute-item');
                            
                            // רק אם יש מאפיינים ב-HTML אבל עדיין אין טבלה
                            if (existingAttributeItems.length > 0) {
                                const existingTables = container.querySelectorAll('.variants-table-container');
                                
                                // אם יש טבלה קיימת מה-PHP אבל לא כפולה, לא נעשה כלום
                                if (existingTables.length <= 1 && typeof updateVariantsTable === 'function') {
                                    updateVariantsTable();
                                }
                            }
                        }
                    }, 1000);
                <?php endif; ?>
            <?php endif; ?>
        });
</script> 