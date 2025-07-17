<?php
require_once __DIR__ . '/../config/database.php';

class ProductManager {
    private $db;
    
    public function __construct($database = null) {
        if ($database) {
            $this->db = $database;
        } else {
            $database = Database::getInstance();
            $this->db = $database->getConnection();
        }
    }
    
    /**
     * יצירת מוצר חדש עם כל התכונות
     */
    public function createProduct($storeId, $productData) {
        try {
            $this->db->beginTransaction();
            
            // ניקוי ואילוצי נתונים
            $cleanData = $this->validateAndCleanProductData($productData);
            
            // בדיקה אם יש וריאציות - אם כן, לא שומרים מחיר כללי
            $hasVariants = (!empty($cleanData['attributes']) && !empty($cleanData['variants'])) || 
                          (!empty($productData['has_variants']) && $productData['has_variants']);
            
            // יצירת המוצר הבסיסי
            $stmt = $this->db->prepare("
                INSERT INTO products (
                    store_id, name, slug, description, short_description, price, compare_price, cost_price, 
                    sku, barcode, track_inventory, inventory_quantity, allow_backorders,
                    weight, gallery_attribute, featured,
                    seo_title, seo_description, seo_keywords,
                    status, vendor, product_type, tags, has_variants
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $storeId,
                $cleanData['name'],
                !empty($cleanData['slug']) ? $this->generateSlug($cleanData['slug'], $storeId) : $this->generateSlug($cleanData['name'], $storeId),
                $cleanData['description'],
                $cleanData['short_description'],
                $hasVariants ? null : $cleanData['price'],
                $hasVariants ? null : $cleanData['compare_price'],
                $hasVariants ? null : $cleanData['cost_price'],
                $hasVariants ? null : $cleanData['sku'],
                $cleanData['barcode'],
                $cleanData['track_inventory'],
                $hasVariants ? null : $cleanData['inventory_quantity'],
                $cleanData['allow_backorders'],
                $cleanData['weight'],
                $cleanData['gallery_attribute'],
                $cleanData['featured'],
                $cleanData['seo_title'] ?: $cleanData['name'],
                $cleanData['seo_description'],
                $cleanData['seo_keywords'],
                $cleanData['status'],
                $cleanData['vendor'],
                $cleanData['product_type'],
                $cleanData['tags'],
                $hasVariants ? 1 : 0
            ]);
            
            $productId = $this->db->lastInsertId();
            
            // שמירת קטגוריות
            if (!empty($cleanData['categories'])) {
                $this->saveProductCategories($productId, $cleanData['categories']);
            }
            
            // שמירת מדיה מנוקה
            if (!empty($cleanData['media'])) {
                $cleanMedia = $this->cleanMediaData($cleanData['media']);
                $this->saveProductMedia($productId, $cleanMedia);
            }
            
            // שמירת מדיה לפי מאפיינים מנוקה
            if (!empty($cleanData['attribute_media'])) {
                error_log("About to save attribute media in createProduct...");
                error_log("Raw attribute media: " . print_r($cleanData['attribute_media'], true));
                $this->saveAttributeMedia($productId, $cleanData['attribute_media']);
            }
            
            // שמירת מאפיינים ווריאציות מנוקים
            if (!empty($cleanData['attributes'])) {
                $cleanAttributes = $this->cleanAttributesData($cleanData['attributes']);
                $this->saveProductAttributes($productId, $cleanAttributes);
            }
            
            // שמירת וריאציות אם קיימות
            if (!empty($cleanData['variants'])) {
                $this->saveProductVariants($productId, $cleanData['variants']);
            }
            
            // שמירת אקורדיונים מנוקים
            if (!empty($cleanData['accordions'])) {
                $cleanAccordions = $this->cleanAccordionsData($cleanData['accordions']);
                $this->saveProductAccordions($productId, $cleanAccordions);
            }
            
            // שמירת שדות מותאמים
            if (!empty($cleanData['custom_fields'])) {
                $this->saveProductCustomFields($productId, $cleanData['custom_fields']);
            }
            
            // שמירת מדבקות מנוקות
            if (!empty($cleanData['badges'])) {
                $cleanBadges = $this->cleanBadgesData($cleanData['badges']);
                $this->saveProductBadges($productId, $cleanBadges);
            }
            
            // שמירת מוצרים קשורים
            if (!empty($cleanData['related_products'])) {
                $this->saveRelatedProducts($productId, $cleanData['related_products'], $cleanData['related_types']);
            }
            
            // שמירת מוצרי שדרוג
            if (!empty($cleanData['upsell_products'])) {
                $this->saveUpsellProducts($productId, $cleanData['upsell_products'], $cleanData['upsell_descriptions']);
            }
            
            // שמירת חבילות מוצרים
            if (!empty($cleanData['bundles'])) {
                $this->saveProductBundles($productId, $cleanData['bundles']);
            }
            
            // שמירת הצעות אוטומטיות
            $this->saveAutoSuggestions($productId, $cleanData);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'product_id' => $productId,
                'message' => 'המוצר נוצר בהצלחה'
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => 'שגיאה ביצירת המוצר: ' . $e->getMessage()
            ];
        }
    }

    /**
     * עדכון מוצר קיים עם כל התכונות
     */
    public function updateProduct($productId, $productData) {
        try {
            $this->db->beginTransaction();
            
            // Debug for accordion issue
            if (!empty($productData['accordions'])) {
                echo "<div style='background: #fff3cd; padding: 15px; margin: 15px; border: 1px solid #ffeaa7; direction: ltr;'>";
                echo "<h4>DEBUG: ProductManager received accordions</h4>";
                echo "<pre>";
                var_dump($productData['accordions']);
                echo "</pre>";
                echo "</div>";
            }
            
            // ניקוי ואילוצי נתונים
            $cleanData = $this->validateAndCleanProductData($productData);
            
            // Debug removed for production
            
            // בדיקה אם יש וריאציות - אם כן, לא שומרים מחיר כללי
            $hasVariants = (!empty($cleanData['attributes']) && !empty($cleanData['variants'])) || 
                          (!empty($productData['has_variants']) && $productData['has_variants']);
            
            // עדכון המוצר הבסיסי
            $stmt = $this->db->prepare("
                UPDATE products SET 
                    name = ?, slug = ?, description = ?, short_description = ?, 
                    price = ?, compare_price = ?, cost_price = ?, 
                    sku = ?, barcode = ?, track_inventory = ?, inventory_quantity = ?, allow_backorders = ?,
                    weight = ?, gallery_attribute = ?, featured = ?,
                    seo_title = ?, seo_description = ?, seo_keywords = ?,
                    status = ?, vendor = ?, product_type = ?, tags = ?, has_variants = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $cleanData['name'],
                $cleanData['slug'],
                $cleanData['description'],
                $cleanData['short_description'],
                $hasVariants ? null : $cleanData['price'],
                $hasVariants ? null : $cleanData['compare_price'],
                $hasVariants ? null : $cleanData['cost_price'],
                $hasVariants ? null : $cleanData['sku'],
                $cleanData['barcode'],
                $cleanData['track_inventory'],
                $hasVariants ? null : $cleanData['inventory_quantity'],
                $cleanData['allow_backorders'],
                $cleanData['weight'],
                $cleanData['gallery_attribute'],
                $cleanData['featured'],
                $cleanData['seo_title'] ?: $cleanData['name'],
                $cleanData['seo_description'],
                $cleanData['seo_keywords'],
                $cleanData['status'],
                $cleanData['vendor'],
                $cleanData['product_type'],
                $cleanData['tags'],
                $hasVariants ? 1 : 0,
                $productId
            ]);
            
            // מחיקת נתונים קיימים לפני עדכון
            $this->deleteProductRelatedData($productId);
            
            // שמירת קטגוריות
            if (!empty($cleanData['categories'])) {
                $this->saveProductCategories($productId, $cleanData['categories']);
            }
            
            // שמירת מדיה מנוקה
            if (!empty($cleanData['media'])) {
                $cleanMedia = $this->cleanMediaData($cleanData['media']);
                $this->saveProductMedia($productId, $cleanMedia);
            }
            
            // שמירת מדיה לפי מאפיינים מנוקה
            if (!empty($cleanData['attribute_media'])) {
                error_log("About to save attribute media in updateProduct...");
                error_log("Raw attribute media: " . print_r($cleanData['attribute_media'], true));
                $this->saveAttributeMedia($productId, $cleanData['attribute_media']);
            } else {
                error_log("No attribute_media in cleanData");
            }
            
            // שמירת מאפיינים ווריאציות מנוקים - הסדר חשוב!
            if (!empty($cleanData['attributes'])) {
                $cleanAttributes = $this->cleanAttributesData($cleanData['attributes']);
                $this->saveProductAttributes($productId, $cleanAttributes);
                
                // חכה רגע לוודא שהמאפיינים נשמרו לפני הווריאציות
                usleep(100000); // 0.1 שניה
            }
            
            // שמירת וריאציות אם קיימות - רק אחרי שהמאפיינים נשמרו
            if (!empty($cleanData['variants'])) {
                $this->saveProductVariants($productId, $cleanData['variants']);
            }
            
            // שמירת אקורדיונים מנוקים
            if (!empty($cleanData['accordions'])) {
                $cleanAccordions = $this->cleanAccordionsData($cleanData['accordions']);
                $this->saveProductAccordions($productId, $cleanAccordions);
            }
            
            // שמירת שדות מותאמים
            if (!empty($cleanData['custom_fields'])) {
                $this->saveProductCustomFields($productId, $cleanData['custom_fields']);
            }
            
            // שמירת מדבקות מנוקות
            if (!empty($cleanData['badges'])) {
                $cleanBadges = $this->cleanBadgesData($cleanData['badges']);
                $this->saveProductBadges($productId, $cleanBadges);
            }
            
            // שמירת מוצרים קשורים
            if (!empty($cleanData['related_products'])) {
                $this->saveRelatedProducts($productId, $cleanData['related_products'], $cleanData['related_types']);
            }
            
            // שמירת מוצרי שדרוג
            if (!empty($cleanData['upsell_products'])) {
                $this->saveUpsellProducts($productId, $cleanData['upsell_products'], $cleanData['upsell_descriptions']);
            }
            
            // שמירת חבילות מוצרים
            if (!empty($cleanData['bundles'])) {
                $this->saveProductBundles($productId, $cleanData['bundles']);
            }
            
            // שמירת הצעות אוטומטיות
            $this->saveAutoSuggestions($productId, $cleanData);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'product_id' => $productId,
                'message' => 'המוצר עודכן בהצלחה'
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => 'שגיאה בעדכון המוצר: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * קבלת מוצר לפי slug
     */
    public function getProductBySlug($slug, $storeId = null) {
        try {
            $whereClause = "WHERE p.slug = ? AND p.status = 'active'";
            $params = [$slug];
            
            if ($storeId) {
                $whereClause .= " AND p.store_id = ?";
                $params[] = $storeId;
            }
            
            $stmt = $this->db->prepare("
                SELECT p.*, s.name as store_name
                FROM products p
                LEFT JOIN stores s ON p.store_id = s.id
                $whereClause
            ");
            
            $stmt->execute($params);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                return null;
            }
            
            // קבלת תמונה ראשית
            $mediaStmt = $this->db->prepare("
                SELECT url FROM product_media 
                WHERE product_id = ? AND is_primary = 1 
                ORDER BY sort_order ASC LIMIT 1
            ");
            $mediaStmt->execute([$product['id']]);
            $primaryMedia = $mediaStmt->fetch(PDO::FETCH_ASSOC);
            $product['featured_image'] = $primaryMedia['url'] ?? null;
            
            // קבלת מדיה
            $product['media'] = $this->getProductMedia($product['id']);
            
            // קבלת מאפיינים ווריאציות
            $product['attributes'] = $this->getProductAttributes($product['id']);
            $product['variants'] = $this->getProductVariants($product['id']);
            
            // קבלת אקורדיונים
            $product['accordions'] = $this->getProductAccordions($product['id']);
            
            // קבלת שדות מותאמים
            $product['custom_fields'] = $this->getProductCustomFields($product['id']);
            
            // קבלת מדבקות
            $product['badges'] = $this->getProductBadges($product['id']);
            
            // קבלת קטגוריות
            $product['categories'] = $this->getProductCategories($product['id']);
            
            return $product;
            
        } catch (Exception $e) {
            error_log("Get Product By Slug Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * קבלת מוצר עם כל הפרטים
     */
    public function getProduct($productId, $storeId = null) {
        try {
            $whereClause = "WHERE p.id = ?";
            $params = [$productId];
            
            if ($storeId) {
                $whereClause .= " AND p.store_id = ?";
                $params[] = $storeId;
            }
            
            $stmt = $this->db->prepare("
                SELECT p.*, s.name as store_name
                FROM products p
                LEFT JOIN stores s ON p.store_id = s.id
                $whereClause
            ");
            
            $stmt->execute($params);
            $product = $stmt->fetch();
            
            if (!$product) {
                return ['success' => false, 'message' => 'המוצר לא נמצא'];
            }
            
            // קבלת מדיה
            $product['media'] = $this->getProductMedia($productId);
            
            // קבלת מאפיינים ווריאציות
            $product['attributes'] = $this->getProductAttributes($productId);
            $product['variants'] = $this->getProductVariants($productId);
            
            // קבלת אקורדיונים
            $product['accordions'] = $this->getProductAccordions($productId);
            
            // קבלת שדות מותאמים
            $product['custom_fields'] = $this->getProductCustomFields($productId);
            
            // קבלת מדבקות
            $product['badges'] = $this->getProductBadges($productId);
            
            // קבלת קטגוריות
            $product['categories'] = $this->getProductCategories($productId);
            
            return [
                'success' => true,
                'product' => $product
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'שגיאה בקבלת המוצר: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * שמירת מדיה למוצר
     */
    private function saveProductMedia($productId, $mediaData) {
        require_once __DIR__ . '/ImageUploader.php';
        
        // Debug
        error_log("saveProductMedia called with: " . json_encode($mediaData));
        
        // קבלת מידע החנות
        $stmt = $this->db->prepare("SELECT store_id FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) return;
        
        $storeInfo = ImageUploader::getStoreInfo($product['store_id']);
        if (!$storeInfo) return;
        
        $uploader = new ImageUploader($storeInfo['slug']);
        
        foreach ($mediaData as $media) {
            $url = $media['url'] ?? '';
            $thumbnailUrl = $media['thumbnail_url'] ?? null;
            
            // אם זה base64, נעלה את הקובץ
            if (strpos($url, 'data:image') === 0) {
                $uploadResult = $uploader->uploadFromBase64($url, null, 'products');
                
                if ($uploadResult['success']) {
                    $url = $uploadResult['url'];
                    $thumbnailUrl = $uploadResult['thumbnail_url'] ?? $url;
                }
            }
            
            if (empty($url)) continue;
            
            $stmt = $this->db->prepare("
                INSERT INTO product_media (
                    product_id, type, url, thumbnail_url, alt_text, 
                    gallery_value, is_primary, sort_order
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $productId,
                $media['type'] ?? 'image',
                $url,
                $thumbnailUrl,
                $media['alt_text'] ?? '',
                $media['gallery_value'] ?? null,
                $media['is_primary'] ?? 0,
                $media['sort_order'] ?? 0
            ]);
            
            error_log("Saved media: $url");
        }
    }
    
    /**
     * שמירת מדיה לפי מאפיינים
     */
    private function saveAttributeMedia($productId, $attributeMediaData) {
        error_log("saveAttributeMedia called for product $productId");
        error_log("Attribute media data: " . print_r($attributeMediaData, true));
        
        if (empty($attributeMediaData)) {
            error_log("No attribute media data provided");
            return;
        }
        
        require_once __DIR__ . '/ImageUploader.php';
        
        // קבלת מידע החנות
        $stmt = $this->db->prepare("SELECT store_id FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) {
            error_log("Product not found for ID: $productId");
            return;
        }
        
        $storeInfo = ImageUploader::getStoreInfo($product['store_id']);
        if (!$storeInfo) {
            error_log("Store info not found for store ID: " . $product['store_id']);
            return;
        }
        
        $uploader = new ImageUploader($storeInfo['slug']);
        $sortOrder = 1000; // התחלה מ-1000 כדי להבדיל מהמדיה הראשית
        
        // קבל את כל הערכים שנשלחו בטופס ובהם יש מדיה חדשה
        $valuesToDelete = [];
        foreach ($attributeMediaData as $attribute => $values) {
            foreach ($values as $value => $mediaList) {
                // מחק רק אם יש מדיה חדשה בערך הזה
                $hasNewMedia = false;
                foreach ($mediaList as $media) {
                    if (!empty($media)) {
                        $hasNewMedia = true;
                        break;
                    }
                }
                if ($hasNewMedia) {
                    $valuesToDelete[] = $value;
                }
            }
        }
        
        if (!empty($valuesToDelete)) {
            // מחיקת מדיית מאפיינים רק עבור הערכים שיש בהם מדיה חדשה
            $placeholders = str_repeat('?,', count($valuesToDelete) - 1) . '?';
            $deleteStmt = $this->db->prepare("
                DELETE FROM product_media 
                WHERE product_id = ? AND gallery_value IN ($placeholders)
            ");
            $deleteParams = array_merge([$productId], $valuesToDelete);
            $deleteStmt->execute($deleteParams);
            $deletedRows = $deleteStmt->rowCount();
            error_log("Deleted $deletedRows existing attribute media for values with new media: " . implode(', ', $valuesToDelete));
        }
        
        foreach ($attributeMediaData as $attribute => $values) {
            error_log("Processing attribute: $attribute");
            foreach ($values as $value => $mediaList) {
                error_log("Processing value: $value with " . count($mediaList) . " media items");
                foreach ($mediaList as $index => $mediaBase64) {
                    if (!empty($mediaBase64)) {
                        error_log("Processing media item $index for $attribute:$value, data length: " . strlen($mediaBase64));
                        
                        // העלאת התמונה באמצעות ImageUploader
                        $uploadResult = $uploader->uploadFromBase64($mediaBase64, null, 'attribute-media');
                        
                        if ($uploadResult['success']) {
                            error_log("Upload successful, saving to database...");
                            $stmt = $this->db->prepare("
                                INSERT INTO product_media (
                                    product_id, type, url, thumbnail_url, alt_text, sort_order, 
                                    gallery_value, is_primary
                                ) VALUES (?, 'image', ?, ?, ?, ?, ?, 0)
                            ");
                            
                            $stmt->execute([
                                $productId,
                                $uploadResult['url'],
                                $uploadResult['thumbnail_url'] ?? $uploadResult['url'],
                                $attribute . ': ' . $value,
                                $sortOrder++,
                                $value // זה מקשר את המדיה לערך המאפיין הספציפי
                            ]);
                            
                            $mediaId = $this->db->lastInsertId();
                            error_log("Saved media with ID: $mediaId, gallery_value: $value, URL: " . $uploadResult['url']);
                        } else {
                            error_log("Upload failed for $attribute:$value: " . ($uploadResult['message'] ?? 'Unknown error'));
                        }
                    } else {
                        error_log("Empty media data for $attribute:$value at index $index");
                    }
                }
            }
        }
    }
    
    /**
     * שמירת מאפיינים למוצר
     */
    private function saveProductAttributes($productId, $attributesData) {
        foreach ($attributesData as $attributeData) {
            // יצירת המאפיין
            $stmt = $this->db->prepare("
                INSERT INTO product_attributes (
                    product_id, name, display_name, type, sort_order
                ) VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $productId,
                $attributeData['name'],
                $attributeData['display_name'],
                $attributeData['type'] ?? 'text',
                $attributeData['sort_order'] ?? 0
            ]);
            
            $attributeId = $this->db->lastInsertId();
            
            // שמירת ערכי המאפיין
            if (!empty($attributeData['values'])) {
                foreach ($attributeData['values'] as $valueData) {
                    $stmt = $this->db->prepare("
                        INSERT INTO attribute_values (
                            attribute_id, value, display_value, color_hex, 
                            image_url, sort_order
                        ) VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([
                        $attributeId,
                        $valueData['value'],
                        $valueData['display_value'],
                        $valueData['color_hex'] ?? null,
                        $valueData['image_url'] ?? null,
                        $valueData['sort_order'] ?? 0
                    ]);
                }
            }
        }
    }
    
    /**
     * שמירת וריאציות של מוצר
     */
    private function saveProductVariants($productId, $variants) {
        foreach ($variants as $variantData) {
            // Clean numeric values - convert empty strings to null
            $cleanPrice = (!empty($variantData['price']) && is_numeric($variantData['price'])) ? $variantData['price'] : null;
            $cleanComparePrice = (!empty($variantData['compare_price']) && is_numeric($variantData['compare_price'])) ? $variantData['compare_price'] : null;
            $cleanCostPrice = (!empty($variantData['cost_price']) && is_numeric($variantData['cost_price'])) ? $variantData['cost_price'] : null;
            $cleanWeight = (!empty($variantData['weight']) && is_numeric($variantData['weight'])) ? $variantData['weight'] : null;
            
            // בדיקה אם זה עדכון של וריאציה קיימת או יצירת חדשה
            if (!empty($variantData['id']) && is_numeric($variantData['id'])) {
                // בדיקה שהוריאציה באמת קיימת ושייכת למוצר הזה
                $checkStmt = $this->db->prepare("
                    SELECT id FROM product_variants WHERE id = ? AND product_id = ?
                ");
                $checkStmt->execute([$variantData['id'], $productId]);
                $existingVariant = $checkStmt->fetch();
                
                if ($existingVariant) {
                    // עדכון וריאציה קיימת
                    $stmt = $this->db->prepare("
                        UPDATE product_variants SET 
                            sku = ?, price = ?, compare_price = ?, cost_price = ?, 
                            inventory_quantity = ?, weight = ?, is_default = ?, is_active = ?
                        WHERE id = ? AND product_id = ?
                    ");
                    
                    $stmt->execute([
                        $variantData['sku'] ?? null,
                        $cleanPrice,
                        $cleanComparePrice,
                        $cleanCostPrice,
                        $variantData['inventory_quantity'] ?? ($variantData['inventory'] ?? 0),
                        $cleanWeight,
                        $variantData['is_default'] ?? 0,
                        $variantData['is_active'] ?? 1,
                        $variantData['id'],
                        $productId
                    ]);
                    
                    $variantId = $variantData['id'];
                    
                    // מחיקת ערכי מאפיינים קיימים לפני עדכון
                    $deleteStmt = $this->db->prepare("DELETE FROM variant_attribute_values WHERE variant_id = ?");
                    $deleteStmt->execute([$variantId]);
                } else {
                    // הוריאציה לא קיימת, ניצור חדשה
                    $stmt = $this->db->prepare("
                        INSERT INTO product_variants (
                            product_id, sku, price, compare_price, cost_price, 
                            inventory_quantity, weight, is_default, is_active
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([
                        $productId,
                        $variantData['sku'] ?? null,
                        $cleanPrice,
                        $cleanComparePrice,
                        $cleanCostPrice,
                        $variantData['inventory_quantity'] ?? ($variantData['inventory'] ?? 0),
                        $cleanWeight,
                        $variantData['is_default'] ?? 0,
                        $variantData['is_active'] ?? 1
                    ]);
                    
                    $variantId = $this->db->lastInsertId();
                }
            } else {
                // יצירת וריאציה חדשה
                $stmt = $this->db->prepare("
                    INSERT INTO product_variants (
                        product_id, sku, price, compare_price, cost_price, 
                        inventory_quantity, weight, is_default, is_active
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $productId,
                    $variantData['sku'] ?? null,
                    $cleanPrice,
                    $cleanComparePrice,
                    $cleanCostPrice,
                    $variantData['inventory_quantity'] ?? ($variantData['inventory'] ?? 0),
                    $cleanWeight,
                    $variantData['is_default'] ?? 0,
                    $variantData['is_active'] ?? 1
                ]);
                
                $variantId = $this->db->lastInsertId();
            }
            
            // קישור וריאציה לערכי מאפיינים - תמיכה בשני פורמטים
            if (!empty($variantData['attribute_values'])) {
                // פורמט ישן - attribute_values כמערך של IDs
                foreach ($variantData['attribute_values'] as $attributeValueId) {
                    try {
                        $stmt = $this->db->prepare("
                            INSERT INTO variant_attribute_values (variant_id, attribute_value_id)
                            VALUES (?, ?)
                        ");
                        $stmt->execute([$variantId, $attributeValueId]);
                    } catch (Exception $e) {
                        error_log("Error linking variant $variantId to attribute_value $attributeValueId: " . $e->getMessage());
                    }
                }
            } elseif (!empty($variantData['attributes'])) {
                // פורמט חדש - attributes כמערך של name => value
                foreach ($variantData['attributes'] as $attributeName => $attributeValue) {
                    if (empty($attributeName) || empty($attributeValue)) continue;
                    
                    // מצא את ה-attribute_id
                    $attrStmt = $this->db->prepare("
                        SELECT id FROM product_attributes 
                        WHERE product_id = ? AND name = ?
                    ");
                    $attrStmt->execute([$productId, $attributeName]);
                    $attribute = $attrStmt->fetch();
                    
                    if (!$attribute) {
                        error_log("Attribute not found: $attributeName for product $productId");
                        continue;
                    }
                    
                    // מצא את ה-attribute_value_id
                    $valueStmt = $this->db->prepare("
                        SELECT id FROM attribute_values 
                        WHERE attribute_id = ? AND value = ?
                    ");
                    $valueStmt->execute([$attribute['id'], $attributeValue]);
                    $attributeValueRow = $valueStmt->fetch();
                    
                    if (!$attributeValueRow) {
                        error_log("Attribute value not found: $attributeValue for attribute $attributeName (id: {$attribute['id']})");
                        continue;
                    }
                    
                    // קישור הוריאציה לערך המאפיין
                    try {
                        $stmt = $this->db->prepare("
                            INSERT INTO variant_attribute_values (variant_id, attribute_value_id)
                            VALUES (?, ?)
                        ");
                        $stmt->execute([$variantId, $attributeValueRow['id']]);
                        error_log("Successfully linked variant $variantId to attribute_value {$attributeValueRow['id']} ($attributeName: $attributeValue)");
                    } catch (Exception $e) {
                        error_log("Error linking variant $variantId to attribute_value {$attributeValueRow['id']}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * שמירת אקורדיונים
     */
    private function saveProductAccordions($productId, $accordions) {
        foreach ($accordions as $index => $accordion) {
            $stmt = $this->db->prepare("
                INSERT INTO product_accordions (
                    product_id, title, content, icon, is_open_by_default, 
                    sort_order, is_active
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $productId,
                $accordion['title'],
                $accordion['content'],
                $accordion['icon'] ?? 'ri-file-text-line',
                $accordion['is_open_by_default'] ?? 0,
                $accordion['sort_order'] ?? $index,
                $accordion['is_active'] ?? 1
            ]);
        }
    }
    
    /**
     * שמירת מדבקות
     */
    private function saveProductBadges($productId, $badges) {
        foreach ($badges as $badge) {
            $stmt = $this->db->prepare("
                INSERT INTO product_badges (
                    product_id, text, color, background_color, 
                    position, is_active
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $productId,
                $badge['text'],
                $badge['color'] ?? '#ffffff',
                $badge['background_color'] ?? '#3b82f6',
                $badge['position'] ?? 'top-right',
                $badge['is_active'] ?? 1
            ]);
        }
    }
    
    /**
     * קבלת מדיה של מוצר
     */
    private function getProductMedia($productId) {
        $stmt = $this->db->prepare("
            SELECT * FROM product_media 
            WHERE product_id = ? 
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
    
    /**
     * קבלת מאפיינים של מוצר
     */
    private function getProductAttributes($productId) {
        $stmt = $this->db->prepare("
            SELECT * FROM product_attributes
            WHERE product_id = ?
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
    
    /**
     * יצירת slug מטקסט
     */
    private function generateSlug($text, $storeId = null) {
        require_once __DIR__ . '/StoreResolver.php';
        
        $text = trim($text);
        $baseSlug = StoreResolver::sanitizeSlug($text);
        
        // אם לא סופק store_id, נחזיר את ה-slug הבסיסי
        if (!$storeId) {
            return $baseSlug;
        }
        
        // בדיקה אם ה-slug כבר קיים
        $slug = $baseSlug;
        $counter = 1;
        
        while ($this->slugExists($slug, $storeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * בדיקה אם slug קיים
     */
    private function slugExists($slug, $storeId) {
        $stmt = $this->db->prepare("SELECT id FROM products WHERE slug = ? AND store_id = ?");
        $stmt->execute([$slug, $storeId]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * קבלת או יצירת מאפיין
     */
    private function getOrCreateAttribute($name, $type = 'text') {
        // בדיקה אם המאפיין קיים
        $stmt = $this->db->prepare("SELECT id FROM attributes WHERE name = ?");
        $stmt->execute([$name]);
        $attribute = $stmt->fetch();
        
        if ($attribute) {
            return $attribute['id'];
        }
        
        // יצירת מאפיין חדש
        $stmt = $this->db->prepare("
            INSERT INTO attributes (name, type, display_name)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$name, $type, $name]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * קבלת או יצירת ערך מאפיין
     */
    private function getOrCreateAttributeValue($attributeId, $value, $color = null) {
        // בדיקה אם הערך קיים
        $stmt = $this->db->prepare("
            SELECT id FROM attribute_values 
            WHERE attribute_id = ? AND value = ?
        ");
        $stmt->execute([$attributeId, $value]);
        $attributeValue = $stmt->fetch();
        
        if ($attributeValue) {
            return $attributeValue['id'];
        }
        
        // יצירת ערך חדש
        $stmt = $this->db->prepare("
            INSERT INTO attribute_values (attribute_id, value, color_code)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$attributeId, $value, $color]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * שמירת קטגוריות למוצר
     */
    private function saveProductCategories($productId, $categories) {
        foreach ($categories as $categoryId) {
            $stmt = $this->db->prepare("
                INSERT INTO product_categories (product_id, category_id)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE product_id = product_id
            ");
            $stmt->execute([$productId, $categoryId]);
        }
    }
    
    /**
     * שמירת שדות מותאמים
     */
    private function saveProductCustomFields($productId, $customFields) {
        foreach ($customFields as $field) {
            $stmt = $this->db->prepare("
                INSERT INTO product_custom_fields (
                    product_id, field_type_id, value
                ) VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $productId,
                $field['field_type_id'],
                $field['value']
            ]);
        }
    }
    
    /**
     * קבלת וריאציות של מוצר
     */
    private function getProductVariants($productId) {
        $stmt = $this->db->prepare("
            SELECT * FROM product_variants 
            WHERE product_id = ? 
            ORDER BY is_default DESC, id ASC
        ");
        $stmt->execute([$productId]);
        $variants = $stmt->fetchAll();
        
        // קבלת ערכי מאפיינים לכל וריאציה
        foreach ($variants as &$variant) {
            $stmt = $this->db->prepare("
                SELECT vav.*, av.value, av.color_hex
                FROM variant_attribute_values vav
                JOIN attribute_values av ON vav.attribute_value_id = av.id
                WHERE vav.variant_id = ?
            ");
            $stmt->execute([$variant['id']]);
            $variant['attribute_values'] = $stmt->fetchAll();
        }
        
        return $variants;
    }
    
    /**
     * קבלת אקורדיונים של מוצר
     */
    private function getProductAccordions($productId) {
        $stmt = $this->db->prepare("
            SELECT * FROM product_accordions 
            WHERE product_id = ? 
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
    
    /**
     * קבלת שדות מותאמים של מוצר
     */
    private function getProductCustomFields($productId) {
        $stmt = $this->db->prepare("
            SELECT pcf.*, cft.name, cft.label
            FROM product_custom_fields pcf
            JOIN custom_field_types cft ON pcf.field_type_id = cft.id
            WHERE pcf.product_id = ?
            ORDER BY cft.id ASC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
    
    /**
     * קבלת מדבקות של מוצר
     */
    private function getProductBadges($productId) {
        $stmt = $this->db->prepare("
            SELECT * FROM product_badges 
            WHERE product_id = ? AND is_active = 1
            ORDER BY id ASC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
    
    /**
     * קבלת קטגוריות של מוצר
     */
    private function getProductCategories($productId) {
        $stmt = $this->db->prepare("
            SELECT c.* FROM categories c
            JOIN product_categories pc ON c.id = pc.category_id
            WHERE pc.product_id = ?
            ORDER BY c.name ASC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
    
    /**
     * קבלת ערכי מאפיין
     */
    private function getAttributeValues($attributeId) {
        $stmt = $this->db->prepare("
            SELECT * FROM attribute_values 
            WHERE attribute_id = ? 
            ORDER BY value ASC
        ");
        $stmt->execute([$attributeId]);
        return $stmt->fetchAll();
    }
    
    /**
     * קבלת רשימת מוצרים עם פילטרים
     */
    public function getProducts($storeId, $filters = []) {
        $whereConditions = ["p.store_id = ?"];
        $params = [$storeId];
        
        // פילטר לפי סטטוס
        if (!empty($filters['status'])) {
            $whereConditions[] = "p.status = ?";
            $params[] = $filters['status'];
        }
        
        // פילטר לפי קטגוריה
        if (!empty($filters['category_id'])) {
            $whereConditions[] = "EXISTS (SELECT 1 FROM product_categories pc WHERE pc.product_id = p.id AND pc.category_id = ?)";
            $params[] = $filters['category_id'];
        }
        
        // חיפוש טקסט
        if (!empty($filters['search'])) {
            $whereConditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        $orderBy = $filters['order_by'] ?? 'p.id DESC';
        $limit = $filters['limit'] ?? 50;
        $offset = ($filters['page'] ?? 1 - 1) * $limit;
        
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   (SELECT url FROM product_media pm WHERE pm.product_id = p.id AND pm.is_primary = 1 LIMIT 1) as featured_image
            FROM products p
            WHERE $whereClause
            ORDER BY $orderBy
            LIMIT $limit OFFSET $offset
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * מחיקת מוצר
     */
    public function deleteProduct($productId, $storeId) {
        try {
            $this->db->beginTransaction();
            
            // בדיקה שהמוצר שייך לחנות
            $stmt = $this->db->prepare("SELECT id FROM products WHERE id = ? AND store_id = ?");
            $stmt->execute([$productId, $storeId]);
            if (!$stmt->fetch()) {
                throw new Exception('המוצר לא נמצא או שאינו שייך לחנות זו');
            }
            
            // מחיקת כל הנתונים הקשורים
            $tables = [
                'product_media',
                'product_attributes', 
                'product_variants',
                'variant_attribute_values' => 'variant_id IN (SELECT id FROM product_variants WHERE product_id = ?)',
                'product_accordions',
                'product_custom_fields',
                'product_badges',
                'product_categories'
            ];
            
            foreach ($tables as $table => $condition) {
                if (is_numeric($table)) {
                    $table = $condition;
                    $condition = "product_id = ?";
                }
                
                $stmt = $this->db->prepare("DELETE FROM $table WHERE $condition");
                $stmt->execute([$productId]);
            }
            
            // מחיקת המוצר עצמו
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            
            $this->db->commit();
            
            return ['success' => true, 'message' => 'המוצר נמחק בהצלחה'];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'שגיאה במחיקת המוצר: ' . $e->getMessage()];
        }
    }
    
    /**
     * שמירת מוצרים קשורים
     */
    private function saveRelatedProducts($productId, $relatedProducts, $relatedTypes) {
        error_log("saveRelatedProducts called with productId: $productId");
        error_log("relatedProducts: " . print_r($relatedProducts, true));
        error_log("relatedTypes: " . print_r($relatedTypes, true));
        
        foreach ($relatedProducts as $index => $relatedProductName) {
            if (empty($relatedProductName)) continue;
            
            // חיפוש המוצר לפי שם
            $relatedProductId = $this->findProductByName($relatedProductName);
            if (!$relatedProductId) {
                error_log("Product not found: $relatedProductName");
                continue;
            }
            
            $relationshipType = $relatedTypes[$index] ?? 'related';
            error_log("Inserting relationship: productId=$productId, relatedProductId=$relatedProductId, type=$relationshipType");
            
            $stmt = $this->db->prepare("
                INSERT INTO product_relationships (
                    main_product_id, related_product_id, relationship_type
                ) VALUES (?, ?, ?)
            ");
            
            try {
                $stmt->execute([
                    $productId,
                    $relatedProductId,
                    $relationshipType
                ]);
                error_log("Successfully inserted relationship");
            } catch (Exception $e) {
                error_log("Error inserting relationship: " . $e->getMessage());
                throw $e;
            }
        }
    }
    
    /**
     * שמירת מוצרי שדרוג
     */
    private function saveUpsellProducts($productId, $upsellProducts, $upsellDescriptions) {
        error_log("saveUpsellProducts called with productId: $productId");
        error_log("upsellProducts: " . print_r($upsellProducts, true));
        error_log("upsellDescriptions: " . print_r($upsellDescriptions, true));
        
        foreach ($upsellProducts as $index => $upsellProductName) {
            if (empty($upsellProductName)) continue;
            
            $upsellProductId = $this->findProductByName($upsellProductName);
            if (!$upsellProductId) {
                error_log("Upsell product not found: $upsellProductName");
                continue;
            }
            
            $description = $upsellDescriptions[$index] ?? '';
            error_log("Inserting upsell relationship: productId=$productId, upsellProductId=$upsellProductId, description=$description");
            
            $stmt = $this->db->prepare("
                INSERT INTO product_relationships (
                    main_product_id, related_product_id, relationship_type, 
                    description
                ) VALUES (?, ?, 'upsell', ?)
            ");
            
            try {
                $stmt->execute([
                    $productId,
                    $upsellProductId,
                    $description
                ]);
                error_log("Successfully inserted upsell relationship");
            } catch (Exception $e) {
                error_log("Error inserting upsell relationship: " . $e->getMessage());
                throw $e;
            }
        }
    }
    
    /**
     * שמירת חבילות מוצרים
     */
    private function saveProductBundles($productId, $bundles) {
        // קבלת store_id של המוצר
        $stmt = $this->db->prepare("SELECT store_id FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) return;
        
        $storeId = $product['store_id'];
        
        foreach ($bundles as $bundle) {
            if (empty($bundle['title']) || empty($bundle['products'])) continue;
            
            // יצירת החבילה
            $stmt = $this->db->prepare("
                INSERT INTO product_bundles (
                    store_id, name, description, bundle_price, 
                    discount_type, discount_value, is_active
                ) VALUES (?, ?, ?, ?, 'fixed', 0, 1)
            ");
            
            $stmt->execute([
                $storeId,
                $bundle['title'],
                $bundle['description'] ?? '',
                $bundle['price'] ?? 0
            ]);
            
            $bundleId = $this->db->lastInsertId();
            
            // הוספת מוצרים לחבילה
            foreach ($bundle['products'] as $index => $productName) {
                if (empty($productName)) continue;
                
                $bundleProductId = $this->findProductByName($productName);
                if (!$bundleProductId) continue;
                
                $stmt = $this->db->prepare("
                    INSERT INTO bundle_products (
                        bundle_id, product_id, quantity, sort_order
                    ) VALUES (?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $bundleId,
                    $bundleProductId,
                    $bundle['quantities'][$index] ?? 1,
                    $index
                ]);
            }
        }
    }
    
    /**
     * שמירת הצעות אוטומטיות
     */
    private function saveAutoSuggestions($productId, $productData) {
        error_log("saveAutoSuggestions called with productId: $productId");
        error_log("auto_suggest data: " . print_r([
            'category' => $productData['auto_suggest_category'] ?? 0,
            'price' => $productData['auto_suggest_price'] ?? 0,
            'vendor' => $productData['auto_suggest_vendor'] ?? 0,
            'tags' => $productData['auto_suggest_tags'] ?? 0
        ], true));
        
        // קבלת store_id של המוצר
        $stmt = $this->db->prepare("SELECT store_id FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) {
            error_log("Product not found for ID: $productId");
            return;
        }
        
        $storeId = $product['store_id'];
        error_log("Store ID: $storeId");
        
        // מחיקת הצעות קיימות של החנות
        $stmt = $this->db->prepare("DELETE FROM auto_suggestions WHERE store_id = ?");
        $stmt->execute([$storeId]);
        $deletedRows = $stmt->rowCount();
        error_log("Deleted $deletedRows existing auto suggestions");
        
        $suggestions = [];
        
        if (!empty($productData['auto_suggest_category'])) {
            $suggestions[] = [
                'name' => 'Auto Suggest by Category',
                'trigger_type' => 'product_category',
                'suggestion_type' => 'cross_sell'
            ];
            error_log("Added category auto suggestion");
        }
        
        if (!empty($productData['auto_suggest_price'])) {
            $suggestions[] = [
                'name' => 'Auto Suggest by Price Range',
                'trigger_type' => 'cart_value',
                'suggestion_type' => 'upsell'
            ];
            error_log("Added price auto suggestion");
        }
        
        if (!empty($productData['auto_suggest_vendor'])) {
            $suggestions[] = [
                'name' => 'Auto Suggest by Vendor',
                'trigger_type' => 'product_specific',
                'suggestion_type' => 'cross_sell'
            ];
            error_log("Added vendor auto suggestion");
        }
        
        if (!empty($productData['auto_suggest_tags'])) {
            $suggestions[] = [
                'name' => 'Auto Suggest by Tags',
                'trigger_type' => 'product_specific',
                'suggestion_type' => 'bundle'
            ];
            error_log("Added tags auto suggestion");
        }
        
        error_log("Total suggestions to save: " . count($suggestions));
        
        foreach ($suggestions as $suggestion) {
            $stmt = $this->db->prepare("
                INSERT INTO auto_suggestions (
                    store_id, name, trigger_type, trigger_condition, 
                    suggestion_type, target_products, is_active
                ) VALUES (?, ?, ?, '{}', ?, '[]', ?)
            ");
            
            try {
                $stmt->execute([
                    $storeId,
                    $suggestion['name'],
                    $suggestion['trigger_type'],
                    $suggestion['suggestion_type'],
                    1
                ]);
                error_log("Successfully saved auto suggestion: " . $suggestion['name']);
            } catch (Exception $e) {
                error_log("Error saving auto suggestion: " . $e->getMessage());
            }
        }
    }
    
    /**
     * חיפוש מוצר לפי שם
     */
    private function findProductByName($productName) {
        $stmt = $this->db->prepare("
            SELECT id FROM products 
            WHERE name LIKE ? 
            ORDER BY name ASC 
            LIMIT 1
        ");
        
        $stmt->execute(['%' . $productName . '%']);
        $result = $stmt->fetch();
        
        return $result ? $result['id'] : null;
    }
    
    /**
     * קבלת מוצרים קשורים
     */
    public function getRelatedProducts($productId, $limit = 6) {
        try {
            // קבלת מוצרים מאותה קטגוריה
            $stmt = $this->db->prepare("
                SELECT DISTINCT p.id, p.name, p.slug, p.price, p.compare_price,
                       pm.url as image
                FROM products p
                JOIN product_categories pc ON p.id = pc.product_id
                LEFT JOIN product_media pm ON p.id = pm.product_id AND pm.is_primary = 1
                WHERE pc.category_id IN (
                    SELECT category_id FROM product_categories WHERE product_id = ?
                )
                AND p.id != ?
                AND p.status = 'active'
                ORDER BY RAND()
                LIMIT ?
            ");
            
            $stmt->execute([$productId, $productId, $limit]);
            $relatedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // אם אין מספיק מוצרים מאותה קטגוריה, נקח מוצרים רנדומליים
            if (count($relatedProducts) < $limit) {
                $remainingLimit = $limit - count($relatedProducts);
                $excludeIds = array_column($relatedProducts, 'id');
                $excludeIds[] = $productId;
                
                $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
                
                $stmt = $this->db->prepare("
                    SELECT p.id, p.name, p.slug, p.price, p.compare_price,
                           pm.url as image
                    FROM products p
                    LEFT JOIN product_media pm ON p.id = pm.product_id AND pm.is_primary = 1
                    WHERE p.id NOT IN ($placeholders)
                    AND p.status = 'active'
                    ORDER BY RAND()
                    LIMIT ?
                ");
                
                $params = $excludeIds;
                $params[] = $remainingLimit;
                
                $stmt->execute($params);
                $randomProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $relatedProducts = array_merge($relatedProducts, $randomProducts);
            }
            
            return $relatedProducts;
            
        } catch (Exception $e) {
            error_log("Get Related Products Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * מחיקת נתונים קשורים למוצר לפני עדכון
     */
    private function deleteProductRelatedData($productId) {
        error_log("Deleting related data for product: $productId");
        
        // טבלאות עם עמודת product_id רגילה (ללא product_media)
        $tablesWithProductId = [
            'product_categories',
            'product_attributes',
            'product_badges',
            'product_accordions',
            'product_custom_fields'
        ];
        
        foreach ($tablesWithProductId as $table) {
            $stmt = $this->db->prepare("DELETE FROM $table WHERE product_id = ?");
            $stmt->execute([$productId]);
            $deletedRows = $stmt->rowCount();
            error_log("Deleted $deletedRows rows from $table");
        }
        
        // מחיקת מדיה רגילה בלבד (ללא gallery_value או עם gallery_value ריק)
        $stmt = $this->db->prepare("DELETE FROM product_media WHERE product_id = ? AND (gallery_value IS NULL OR gallery_value = '')");
        $stmt->execute([$productId]);
        $deletedRows = $stmt->rowCount();
        error_log("Deleted $deletedRows regular media rows from product_media");
        
        // טבלת product_relationships משתמשת ב-main_product_id
        $stmt = $this->db->prepare("DELETE FROM product_relationships WHERE main_product_id = ? OR related_product_id = ?");
        $stmt->execute([$productId, $productId]);
        
        // מחיקת וריאציות וערכי מאפיינים שלהן
        $stmt = $this->db->prepare("
            DELETE vav FROM variant_attribute_values vav
            JOIN product_variants pv ON vav.variant_id = pv.id
            WHERE pv.product_id = ?
        ");
        $stmt->execute([$productId]);
        
        $stmt = $this->db->prepare("DELETE FROM product_variants WHERE product_id = ?");
        $stmt->execute([$productId]);
        
        // מחיקת פריטים מחבילות מוצרים
        $stmt = $this->db->prepare("DELETE FROM bundle_products WHERE product_id = ?");
        $stmt->execute([$productId]);
        
        // מחיקת פריטים מאוספים 
        $stmt = $this->db->prepare("DELETE FROM product_collection_items WHERE product_id = ?");
        $stmt->execute([$productId]);
        
        // לא מוחקים auto_suggestions כי הם שייכים לחנות ולא למוצר ספציפי
    }

    /**
     * קיצור מחרוזת לאורך מקסימלי
     */
    private function truncateString($string, $maxLength) {
        if (empty($string)) return '';
        return mb_strlen($string) > $maxLength ? mb_substr($string, 0, $maxLength) : $string;
    }

    /**
     * ולידציה וניקוי נתוני מוצר
     */
    private function validateAndCleanProductData($productData) {
        // פונקציה עזר לניקוי ערכים מספריים
        $cleanNumeric = function($value) {
            if (empty($value) || $value === '' || !is_numeric($value)) {
                return null;
            }
            return $value;
        };
        
        // הגבלת אורכי מחרוזות לפי הגדרות הטבלה
        $cleanData = [
            'name' => $this->truncateString($productData['name'] ?? '', 255),
            'slug' => $this->truncateString($productData['slug'] ?? '', 255),
            'description' => $productData['description'] ?? '',
            'short_description' => $this->truncateString($productData['short_description'] ?? '', 500),
            'sku' => $this->truncateString($productData['sku'] ?? '', 100),
            'barcode' => $this->truncateString($productData['barcode'] ?? '', 100),
            'price' => $cleanNumeric($productData['price'] ?? ''),
            'compare_price' => $cleanNumeric($productData['compare_price'] ?? ''),
            'cost_price' => $cleanNumeric($productData['cost_price'] ?? ''),
            'track_inventory' => $productData['track_inventory'] ?? 1,
            'inventory_quantity' => $cleanNumeric($productData['inventory_quantity'] ?? '') ?? 0,
            'allow_backorders' => $productData['allow_backorders'] ?? 0,
            'weight' => $cleanNumeric($productData['weight'] ?? ''),
            'requires_shipping' => $productData['requires_shipping'] ?? 1,
            'is_physical' => $productData['is_physical'] ?? 1,
            'featured' => $productData['featured'] ?? 0,
            'vendor' => $this->truncateString($productData['vendor'] ?? '', 255),
            'product_type' => $this->truncateString($productData['product_type'] ?? '', 100),
            'tags' => $productData['tags'] ?? '',
            'gallery_attribute' => $this->truncateString($productData['gallery_attribute'] ?? '', 50),
            'status' => in_array($productData['status'] ?? '', ['draft', 'active', 'archived']) ? $productData['status'] : 'active',
            'seo_title' => $this->truncateString($productData['seo_title'] ?? '', 255),
            'seo_description' => $this->truncateString($productData['seo_description'] ?? '', 500),
            'seo_keywords' => $productData['seo_keywords'] ?? '',
            'categories' => $productData['categories'] ?? [],
            'media' => $productData['media'] ?? [],
            'attributes' => $productData['attributes'] ?? [],
            'variants' => $productData['variants'] ?? [],
            'accordions' => $productData['accordions'] ?? [],
            'badges' => $productData['badges'] ?? [],
            'related_products' => $productData['related_products'] ?? [],
            'related_types' => $productData['related_types'] ?? [],
            'upsell_products' => $productData['upsell_products'] ?? [],
            'upsell_descriptions' => $productData['upsell_descriptions'] ?? [],
            'bundles' => $productData['bundles'] ?? [],
            'auto_suggest_category' => $productData['auto_suggest_category'] ?? 0,
            'auto_suggest_price' => $productData['auto_suggest_price'] ?? 0,
            'auto_suggest_vendor' => $productData['auto_suggest_vendor'] ?? 0,
            'auto_suggest_tags' => $productData['auto_suggest_tags'] ?? 0,
            'attribute_media' => $productData['attribute_media'] ?? []
        ];

        return $cleanData;
    }

    /**
     * ניקוי נתוני מדיה
     */
    private function cleanMediaData($mediaData) {
        $cleanMedia = [];
        foreach ($mediaData as $media) {
            $cleanMedia[] = [
                'url' => $this->truncateString($media['url'] ?? '', 500),
                'thumbnail_url' => $this->truncateString($media['thumbnail_url'] ?? '', 500),
                'alt_text' => $this->truncateString($media['alt_text'] ?? '', 255),
                'gallery_value' => $this->truncateString($media['gallery_value'] ?? '', 255),
                'is_primary' => $media['is_primary'] ?? 0,
                'sort_order' => is_numeric($media['sort_order'] ?? '') ? intval($media['sort_order']) : 0,
                'type' => in_array($media['type'] ?? '', ['image', 'video']) ? $media['type'] : 'image'
            ];
        }
        return $cleanMedia;
    }

    /**
     * ניקוי נתוני מאפיינים
     */
    private function cleanAttributesData($attributesData) {
        $cleanAttributes = [];
        foreach ($attributesData as $attr) {
            $cleanAttr = [
                'name' => $this->truncateString($attr['name'] ?? '', 100),
                'display_name' => $this->truncateString($attr['display_name'] ?? '', 100),
                'type' => in_array($attr['type'] ?? '', ['text', 'color', 'image']) ? $attr['type'] : 'text',
                'sort_order' => is_numeric($attr['sort_order'] ?? '') ? intval($attr['sort_order']) : 0,
                'values' => []
            ];
            
            if (!empty($attr['values'])) {
                foreach ($attr['values'] as $value) {
                    $cleanAttr['values'][] = [
                        'value' => $this->truncateString($value['value'] ?? '', 255),
                        'display_value' => $this->truncateString($value['display_value'] ?? '', 255),
                        'color_hex' => $this->truncateString($value['color_hex'] ?? $value['color'] ?? '', 7),
                        'image_url' => $this->truncateString($value['image_url'] ?? '', 500),
                        'sort_order' => is_numeric($value['sort_order'] ?? '') ? intval($value['sort_order']) : 0
                    ];
                }
            }
            
            $cleanAttributes[] = $cleanAttr;
        }
        return $cleanAttributes;
    }

    /**
     * ניקוי נתוני אקורדיונים
     */
    private function cleanAccordionsData($accordionsData) {
        $cleanAccordions = [];
        
        // בדיקה אם הנתונים מגיעים בפורמט הישן (title[], content[]) או החדש
        if (isset($accordionsData['title']) && is_array($accordionsData['title'])) {
            // פורמט ישן: accordions[title][], accordions[content][]
            $titles = $accordionsData['title'] ?? [];
            $contents = $accordionsData['content'] ?? [];
            
            for ($i = 0; $i < count($titles); $i++) {
                if (!empty($titles[$i])) { // רק אם יש כותרת
                    $cleanAccordions[] = [
                        'title' => $this->truncateString($titles[$i], 255),
                        'content' => $contents[$i] ?? '',
                        'icon' => 'ri-file-text-line',
                        'is_open_by_default' => 0,
                        'sort_order' => $i,
                        'is_active' => 1
                    ];
                }
            }
        } else {
            // פורמט חדש: accordions[0][title], accordions[0][content]
            foreach ($accordionsData as $accordion) {
                if (!empty($accordion['title'])) { // רק אם יש כותרת
                    $cleanAccordions[] = [
                        'title' => $this->truncateString($accordion['title'] ?? '', 255),
                        'content' => $accordion['content'] ?? '',
                        'icon' => $this->truncateString($accordion['icon'] ?? '', 50),
                        'is_open_by_default' => $accordion['is_open_by_default'] ?? 0,
                        'sort_order' => is_numeric($accordion['sort_order'] ?? '') ? intval($accordion['sort_order']) : 0,
                        'is_active' => $accordion['is_active'] ?? 1
                    ];
                }
            }
        }
        
        return $cleanAccordions;
    }

    /**
     * ניקוי נתוני מדבקות
     */
    private function cleanBadgesData($badgesData) {
        $cleanBadges = [];
        foreach ($badgesData as $badge) {
            $cleanBadges[] = [
                'text' => $this->truncateString($badge['text'] ?? '', 50),
                'color' => $this->truncateString($badge['color'] ?? '', 7),
                'background_color' => $this->truncateString($badge['background_color'] ?? '', 7),
                'position' => in_array($badge['position'] ?? '', ['top-left', 'top-right', 'bottom-left', 'bottom-right']) ? $badge['position'] : 'top-right',
                'is_active' => $badge['is_active'] ?? 1
            ];
        }
        return $cleanBadges;
    }
} 