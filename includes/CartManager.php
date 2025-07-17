<?php
class CartManager {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->initializeCart();
    }
    
    /**
     * אתחול עגלה ריקה אם לא קיימת
     */
    private function initializeCart() {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [
                'items' => [],
                'totals' => [
                    'subtotal' => 0.00,
                    'shipping' => 0.00,
                    'tax' => 0.00,
                    'total' => 0.00
                ],
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * הוספת מוצר לעגלה
     */
    public function addItem($productId, $variantId = null, $quantity = 1) {
        try {
            // קבלת פרטי המוצר
            $product = $this->getProductDetails($productId, $variantId);
            if (!$product) {
                return ['success' => false, 'message' => 'מוצר לא נמצא'];
            }
            
            // בדיקת מלאי
            if (!$this->checkInventory($product, $quantity)) {
                return ['success' => false, 'message' => 'אין מספיק מלאי'];
            }
            
            // יצירת מפתח ייחודי לפריט
            $itemKey = $this->generateItemKey($productId, $variantId);
            
            // בדיקה אם הפריט כבר קיים בעגלה
            $existingIndex = $this->findExistingItem($itemKey);
            
            if ($existingIndex !== false) {
                // עדכון כמות פריט קיים
                $_SESSION['cart']['items'][$existingIndex]['quantity'] += $quantity;
            } else {
                // הוספת פריט חדש
                $_SESSION['cart']['items'][] = [
                    'item_key' => $itemKey,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'sku' => $product['sku'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'quantity' => $quantity,
                    'image' => $product['image'],
                    'attributes' => $product['attributes'] ?? []
                ];
            }
            
            $this->updateTotals();
            $this->updateTimestamp();
            
            return [
                'success' => true, 
                'message' => 'המוצר נוסף לעגלה',
                'cart' => $this->getCart()
            ];
            
        } catch (Exception $e) {
            error_log("Cart Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'שגיאה בהוספה לעגלה'];
        }
    }
    
    /**
     * עדכון כמות פריט בעגלה
     */
    public function updateQuantity($itemKey, $quantity) {
        try {
            $index = $this->findExistingItem($itemKey);
            if ($index === false) {
                return ['success' => false, 'message' => 'פריט לא נמצא בעגלה'];
            }
            
            if ($quantity <= 0) {
                return $this->removeItem($itemKey);
            }
            
            $item = $_SESSION['cart']['items'][$index];
            
            // בדיקת מלאי
            $product = $this->getProductDetails($item['product_id'], $item['variant_id']);
            if (!$this->checkInventory($product, $quantity)) {
                return ['success' => false, 'message' => 'אין מספיק מלאי'];
            }
            
            $_SESSION['cart']['items'][$index]['quantity'] = $quantity;
            $this->updateTotals();
            $this->updateTimestamp();
            
            return [
                'success' => true,
                'message' => 'הכמות עודכנה',
                'cart' => $this->getCart()
            ];
            
        } catch (Exception $e) {
            error_log("Cart Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'שגיאה בעדכון העגלה'];
        }
    }
    
    /**
     * הסרת פריט מהעגלה
     */
    public function removeItem($itemKey) {
        try {
            $index = $this->findExistingItem($itemKey);
            if ($index === false) {
                return ['success' => false, 'message' => 'פריט לא נמצא בעגלה'];
            }
            
            array_splice($_SESSION['cart']['items'], $index, 1);
            $this->updateTotals();
            $this->updateTimestamp();
            
            return [
                'success' => true,
                'message' => 'הפריט הוסר מהעגלה',
                'cart' => $this->getCart()
            ];
            
        } catch (Exception $e) {
            error_log("Cart Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'שגיאה בהסרת הפריט'];
        }
    }
    
    /**
     * ניקוי העגלה
     */
    public function clearCart() {
        $_SESSION['cart'] = [
            'items' => [],
            'totals' => [
                'subtotal' => 0.00,
                'shipping' => 0.00,
                'tax' => 0.00,
                'total' => 0.00
            ],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return ['success' => true, 'message' => 'העגלה נוקתה'];
    }
    
    /**
     * קבלת העגלה המלאה
     */
    public function getCart() {
        return $_SESSION['cart'];
    }
    
    /**
     * קבלת מספר פריטים בעגלה
     */
    public function getItemCount() {
        return array_sum(array_column($_SESSION['cart']['items'], 'quantity'));
    }
    
    /**
     * קבלת סכום כולל
     */
    public function getTotal() {
        return $_SESSION['cart']['totals']['total'];
    }
    
    /**
     * פרטי מוצר לעגלה
     */
    private function getProductDetails($productId, $variantId = null) {
        try {
            if ($variantId) {
                // קבלת פרטי וריאציה
                $sql = "SELECT 
                    p.id, p.name, p.slug,
                    v.id as variant_id, v.sku, v.price, v.inventory_quantity,
                    p.track_inventory, p.allow_backorders,
                    pm.url as image,
                    GROUP_CONCAT(CONCAT(pa.name, ':', av.value) SEPARATOR ',') as attributes_str
                FROM products p
                JOIN product_variants v ON p.id = v.product_id
                LEFT JOIN product_media pm ON p.id = pm.product_id AND pm.is_primary = 1
                LEFT JOIN variant_attribute_values vav ON v.id = vav.variant_id
                LEFT JOIN attribute_values av ON vav.attribute_value_id = av.id
                LEFT JOIN product_attributes pa ON av.attribute_id = pa.id
                WHERE p.id = ? AND v.id = ? AND v.is_active = 1
                GROUP BY v.id";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$productId, $variantId]);
            } else {
                // קבלת פרטי מוצר רגיל
                $sql = "SELECT 
                    p.id, p.name, p.slug, p.sku, p.price, p.inventory_quantity,
                    p.track_inventory, p.allow_backorders,
                    pm.url as image
                FROM products p
                LEFT JOIN product_media pm ON p.id = pm.product_id AND pm.is_primary = 1
                WHERE p.id = ? AND p.status = 'active'";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$productId]);
            }
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$product) return null;
            
            // עיבוד מאפיינים
            if (isset($product['attributes_str']) && $product['attributes_str']) {
                $attributePairs = explode(',', $product['attributes_str']);
                $attributes = [];
                foreach ($attributePairs as $pair) {
                    $parts = explode(':', $pair);
                    if (count($parts) == 2) {
                        $attributes[trim($parts[0])] = trim($parts[1]);
                    }
                }
                $product['attributes'] = $attributes;
            }
            
            return $product;
            
        } catch (Exception $e) {
            error_log("Get Product Details Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * בדיקת מלאי
     */
    private function checkInventory($product, $requestedQuantity) {
        if (!$product['track_inventory']) {
            return true; // אין מעקב מלאי
        }
        
        $availableQuantity = $product['inventory_quantity'];
        
        if ($availableQuantity >= $requestedQuantity) {
            return true; // יש מלאי
        }
        
        if ($product['allow_backorders']) {
            return true; // מותר להזמין מראש
        }
        
        return false; // אין מלאי
    }
    
    /**
     * יצירת מפתח ייחודי לפריט
     */
    private function generateItemKey($productId, $variantId = null) {
        return $variantId ? "p{$productId}_v{$variantId}" : "p{$productId}";
    }
    
    /**
     * חיפוש פריט קיים בעגלה
     */
    private function findExistingItem($itemKey) {
        foreach ($_SESSION['cart']['items'] as $index => $item) {
            if ($item['item_key'] === $itemKey) {
                return $index;
            }
        }
        return false;
    }
    
    /**
     * עדכון סכומי העגלה
     */
    private function updateTotals() {
        $subtotal = 0;
        
        foreach ($_SESSION['cart']['items'] as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        $shipping = $this->calculateShipping($subtotal);
        $tax = $subtotal * 0.18; // מעמ 18%
        $total = $subtotal + $shipping + $tax;
        
        $_SESSION['cart']['totals'] = [
            'subtotal' => round($subtotal, 2),
            'shipping' => round($shipping, 2),
            'tax' => round($tax, 2),
            'total' => round($total, 2)
        ];
    }
    
    /**
     * חישוב משלוח
     */
    private function calculateShipping($subtotal) {
        // משלוח חינם מעל 200 שקל
        return $subtotal >= 200 ? 0 : 25;
    }
    
    /**
     * עדכון זמן עדכון אחרון
     */
    private function updateTimestamp() {
        $_SESSION['cart']['updated_at'] = date('Y-m-d H:i:s');
    }
    
    /**
     * בדיקה אם העגלה ריקה
     */
    public function isEmpty() {
        return empty($_SESSION['cart']['items']);
    }
    
    /**
     * קבלת עגלה בפורמט JSON
     */
    public function getCartJson() {
        return json_encode($_SESSION['cart'], JSON_UNESCAPED_UNICODE);
    }
}
?> 