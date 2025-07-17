<?php
/**
 * מחלקה לייבוא מוצרים מקובץ CSV של מגנטו
 * תומכת במוצרים עם וריאציות, תמונות והורדה אוטומטית
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/ImageUploader.php';

class CsvImporter {
    private $store_id;
    private $import_id;
    private $pdo;
    private $options;
    private $errors = [];
    private $stats = [
        'total_rows' => 0,
        'processed_rows' => 0,
        'imported_products' => 0,
        'failed_products' => 0
    ];

    public function __construct($store_id, $import_id) {
        $this->store_id = $store_id;
        $this->import_id = $import_id;
        
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
        
        // אפשרויות ברירת מחדל
        $this->options = [
            'skip_existing' => true,
            'download_images' => true,
            'create_categories' => true,
            'image_domain' => '',
            'image_quality' => 'high'
        ];
        
        // המיפוי יילקח מהקובץ עצמו, לא נקבע מראש
    }

    public function setOptions($options) {
        $this->options = array_merge($this->options, $options);
    }
    
    // המיפוי יבוא מהקובץ עצמו דרך parseConfigurableVariationLabels

    public function import($csv_file_path) {
        try {
            $this->updateProgress(0, 'קורא קובץ CSV...');
            
            // קריאת קובץ CSV
            $csv_data = $this->readCsvFile($csv_file_path);
            $this->stats['total_rows'] = count($csv_data);
            
            if (empty($csv_data)) {
                throw new Exception('קובץ CSV ריק או לא תקין');
            }

            $this->updateProgress(10, 'מעבד נתונים...');

            // עיבוד הנתונים
            $products_data = $this->processCsvData($csv_data);
            
            $this->updateProgress(20, 'מתחיל ייבוא מוצרים...');

            // ייבוא המוצרים
            $this->importProducts($products_data);

            $this->updateProgress(100, 'הייבוא הושלם בהצלחה');

            return [
                'success' => true,
                'total_rows' => $this->stats['total_rows'],
                'processed_rows' => $this->stats['processed_rows'],
                'imported_products' => $this->stats['imported_products'],
                'failed_products' => $this->stats['failed_products'],
                'error_log' => implode("\n", $this->errors)
            ];

        } catch (Exception $e) {
            $this->addError('שגיאה כללית: ' . $e->getMessage());
            
            return [
                'success' => false,
                'total_rows' => $this->stats['total_rows'],
                'processed_rows' => $this->stats['processed_rows'],
                'imported_products' => $this->stats['imported_products'],
                'failed_products' => $this->stats['failed_products'],
                'error_log' => implode("\n", $this->errors)
            ];
        }
    }

    private function readCsvFile($file_path) {
        if (!file_exists($file_path)) {
            throw new Exception('קובץ CSV לא נמצא');
        }

        $csv_data = [];
        $headers = [];
        
        if (($handle = fopen($file_path, 'r')) !== FALSE) {
            $row_number = 0;
            
            while (($data = fgetcsv($handle, 0, ',')) !== FALSE) {
                $row_number++;
                
                if ($row_number === 1) {
                    // שורת כותרות
                    $headers = $data;
                    continue;
                }
                
                if (count($data) !== count($headers)) {
                    $this->addError("שורה {$row_number}: מספר עמודות לא תואם לכותרות");
                    continue;
                }
                
                // יצירת מערך אסוציאטיבי
                $row_data = array_combine($headers, $data);
                if ($row_data) {
                    $csv_data[] = $row_data;
                }
            }
            
            fclose($handle);
        } else {
            throw new Exception('לא ניתן לפתוח את קובץ ה-CSV');
        }

        return $csv_data;
    }

    private function processCsvData($csv_data) {
        $products = [];
        $configurable_products = [];
        $simple_products = [];

        // הפרדה בין מוצרים ראשיים לוריאציות
        foreach ($csv_data as $row) {
            $product_type = trim($row['product_type'] ?? '');
            $sku = trim($row['sku'] ?? '');

            if (empty($sku)) {
                $this->addError('שורה ללא SKU - מדלג');
                continue;
            }

            if ($product_type === 'configurable') {
                $configurable_products[$sku] = $row;
                error_log("Found configurable product: $sku with variations: " . ($row['configurable_variations'] ?? 'none'));
            } elseif ($product_type === 'simple') {
                $simple_products[$sku] = $row;
                error_log("Found simple product: $sku - " . ($row['name'] ?? 'no name'));
            }
        }
        
        error_log("Total: " . count($configurable_products) . " configurable, " . count($simple_products) . " simple products");

        // עיבוד מוצרים עם וריאציות
        foreach ($configurable_products as $parent_sku => $parent_data) {
            error_log("Processing configurable product: $parent_sku");
            $product = $this->processConfigurableProduct($parent_data, $simple_products);
            if ($product) {
                error_log("Successfully processed configurable product: $parent_sku with " . count($product['variants']) . " variants");
                $products[] = $product;
            } else {
                error_log("Failed to process configurable product: $parent_sku");
            }
        }

        // עיבוד מוצרים פשוטים שלא שייכים למוצר ראשי
        foreach ($simple_products as $simple_sku => $simple_data) {
            error_log("Checking simple product: $simple_sku");
            // בדיקה אם המוצר לא שייך למוצר ראשי
            $is_variant = false;
            foreach ($configurable_products as $parent_sku => $parent_data) {
                $variations = $this->parseConfigurableVariations($parent_data['configurable_variations'] ?? '');
                error_log("Checking against configurable: $parent_sku with " . count($variations) . " variations");
                foreach ($variations as $variant) {
                    $variant_sku = trim($variant['sku'] ?? '');
                    $trimmed_simple_sku = trim($simple_sku);
                    error_log("Comparing: Variant SKU: '$variant_sku' vs Simple SKU: '$trimmed_simple_sku'");
                    error_log("SKU lengths: Variant=" . strlen($variant_sku) . ", Simple=" . strlen($trimmed_simple_sku));
                    error_log("Are equal: " . ($variant_sku === $trimmed_simple_sku ? 'YES' : 'NO'));
                    
                    if (!empty($variant_sku) && $variant_sku === $trimmed_simple_sku) {
                        $is_variant = true;
                        error_log("✓ $simple_sku is a variant of $parent_sku - skipping standalone creation");
                        break 2;
                    }
                }
            }

            if (!$is_variant) {
                error_log("✗ $simple_sku is NOT a variant - creating as standalone product");
                $product = $this->processSimpleProduct($simple_data);
                if ($product) {
                    $products[] = $product;
                }
            } else {
                error_log("✓ $simple_sku is a variant - NOT creating standalone product");
            }
        }

        return $products;
    }

    private function processConfigurableProduct($parent_data, $simple_products) {
        $sku = trim($parent_data['sku']);
        
        // ניתוח וריאציות
        $variations = $this->parseConfigurableVariations($parent_data['configurable_variations'] ?? '');
        
        if (empty($variations)) {
            $this->addError("מוצר {$sku}: לא נמצאו וריאציות");
            return null;
        }

        // בניית מבנה המוצר
        $product = [
            'type' => 'configurable',
            'sku' => $sku,
            'name' => trim($parent_data['name'] ?? ''),
            'description' => trim($parent_data['description'] ?? ''),
            'short_description' => trim($parent_data['short_description'] ?? ''),
            'categories' => $this->parseCategories($parent_data['categories'] ?? ''),
            'price' => null, // המחיר יילקח מהוריאציה הראשונה
            'images' => $this->parseImages($parent_data),
            'additional_attributes' => $this->parseAdditionalAttributes($parent_data['additional_attributes'] ?? ''),
            'attributes' => [],
            'variants' => []
        ];

        // ניתוח מאפיינים מהוריאציות
        $attribute_labels = $this->parseConfigurableVariationLabels($parent_data['configurable_variation_labels'] ?? '');
        
        foreach ($variations as $variant_data) {
            $variant_sku = $variant_data['sku'];
            
            if (!isset($simple_products[$variant_sku])) {
                $this->addError("וריאציה {$variant_sku} לא נמצאה במוצרים הפשוטים");
                continue;
            }

            $simple_data = $simple_products[$variant_sku];
            $variant = $this->processVariant($simple_data, $variant_data);
            
            if ($variant) {
                $product['variants'][] = $variant;
                
                // הוספת מאפיינים למוצר הראשי עם תרגום
                foreach ($variant_data as $attr_key => $attr_value) {
                    if (in_array($attr_key, ['sku'])) continue;
                    
                    $attr_name = $attribute_labels[$attr_key] ?? $this->attributeLabels[$attr_key] ?? $attr_key;
                    
                    // תרגום הערך
                    $translated_value = $attr_value;
                    if ($attr_key === 'color' && isset($this->colorMapping[$attr_value])) {
                        $translated_value = $this->colorMapping[$attr_value]['name'];
                    } elseif ($attr_key === 'size' && isset($this->sizeMapping[$attr_value])) {
                        $translated_value = $this->sizeMapping[$attr_value]['name'];
                    }
                    
                    if (!isset($product['attributes'][$attr_key])) {
                        $product['attributes'][$attr_key] = [
                            'name' => $attr_key,
                            'display_name' => $attr_name,
                            'type' => $this->detectAttributeType($attr_value),
                            'values' => []
                        ];
                    }
                    
                    if (!in_array($translated_value, $product['attributes'][$attr_key]['values'])) {
                        $product['attributes'][$attr_key]['values'][] = $translated_value;
                    }
                }
            }
        }

        // קביעת מחיר ברירת מחדל מהוריאציה הראשונה
        if (!empty($product['variants'])) {
            $product['price'] = $product['variants'][0]['price'];
        }

        return $product;
    }

    private function processSimpleProduct($data) {
        $sku = trim($data['sku']);
        
        return [
            'type' => 'simple',
            'sku' => $sku,
            'name' => trim($data['name'] ?? ''),
            'description' => trim($data['description'] ?? ''),
            'short_description' => trim($data['short_description'] ?? ''),
            'price' => floatval($data['price'] ?? 0),
            'categories' => $this->parseCategories($data['categories'] ?? ''),
            'images' => $this->parseImages($data),
            'additional_attributes' => $this->parseAdditionalAttributes($data['additional_attributes'] ?? ''),
            'weight' => floatval($data['weight'] ?? 0),
            'status' => $this->parseStatus($data['product_online'] ?? '1')
        ];
    }

    private function processVariant($simple_data, $variant_data) {
        // תרגום מאפיינים לשמות אמיתיים
        $translated_attributes = $this->translateVariantAttributes($variant_data);
        
        return [
            'sku' => trim($simple_data['sku']),
            'name' => trim($simple_data['name'] ?? ''),
            'price' => floatval($simple_data['price'] ?? 0),
            'weight' => floatval($simple_data['weight'] ?? 0),
            'images' => $this->parseImages($simple_data),
            'attributes' => $translated_attributes,
            'raw_attributes' => $variant_data // שמירת הערכים המקוריים למקרה הצורך
        ];
    }
    
    private function translateVariantAttributes($variant_data) {
        $translated = [];
        
        foreach ($variant_data as $attr_key => $attr_value) {
            if ($attr_key === 'sku') {
                continue; // לא מתרגמים SKU
            }
            
            $translated_key = $this->attributeLabels[$attr_key] ?? $attr_key;
            
            // תרגום ערכים ספציפיים
            if ($attr_key === 'color' && isset($this->colorMapping[$attr_value])) {
                $translated[$translated_key] = $this->colorMapping[$attr_value]['name'];
                $translated[$translated_key . '_code'] = $this->colorMapping[$attr_value]['code'];
            } elseif ($attr_key === 'size' && isset($this->sizeMapping[$attr_value])) {
                $translated[$translated_key] = $this->sizeMapping[$attr_value]['name'];
                $translated[$translated_key . '_numeric'] = $this->sizeMapping[$attr_value]['size'];
            } else {
                $translated[$translated_key] = $attr_value;
            }
        }
        
        return $translated;
    }

    private function parseConfigurableVariations($variations_string) {
        $variations = [];
        
        if (empty($variations_string)) {
            error_log("Empty variations string");
            return $variations;
        }

        error_log("Parsing variations: $variations_string");
        
        // פורמט: sku=12305270500212,color=12,size=02|sku=12305270500412,color=12,size=04
        $variant_strings = explode('|', $variations_string);
        error_log("Split into " . count($variant_strings) . " variant strings");
        
        foreach ($variant_strings as $i => $variant_string) {
            $variant = [];
            $pairs = explode(',', $variant_string);
            
            foreach ($pairs as $pair) {
                $parts = explode('=', $pair, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    $variant[$key] = $value;
                    error_log("Variant $i: $key = $value");
                }
            }
            
            if (!empty($variant)) {
                $variations[] = $variant;
                error_log("Added variant $i with SKU: " . ($variant['sku'] ?? 'missing'));
            }
        }

        error_log("Total variations parsed: " . count($variations));
        return $variations;
    }

    private function parseConfigurableVariationLabels($labels_string) {
        $labels = [];
        
        if (empty($labels_string)) {
            return $labels;
        }

        // פורמט: color=Color,size=Size
        $pairs = explode(',', $labels_string);
        
        foreach ($pairs as $pair) {
            $parts = explode('=', $pair, 2);
            if (count($parts) === 2) {
                $labels[trim($parts[0])] = trim($parts[1]);
            }
        }

        return $labels;
    }

    private function parseCategories($categories_string) {
        if (empty($categories_string)) {
            return [];
        }

        // פורמט: "Default Category/חצאיות ,Default Category/מכנסיים"
        $categories = explode(',', $categories_string);
        $result = [];

        foreach ($categories as $category) {
            $category = trim($category);
            if (!empty($category)) {
                // הסרת "Default Category/" אם קיים
                $category = preg_replace('/^Default Category\//', '', $category);
                $result[] = trim($category);
            }
        }

        return array_unique($result);
    }

    private function parseImages($data) {
        $images = [];
        
        // תמונה ראשית
        $base_image = trim($data['base_image'] ?? '');
        if (!empty($base_image)) {
            $images[] = [
                'url' => $this->buildImageUrl($base_image),
                'is_primary' => true,
                'alt_text' => trim($data['base_image_label'] ?? '')
            ];
        }

        // תמונות נוספות
        $additional_images = trim($data['additional_images'] ?? '');
        if (!empty($additional_images)) {
            $image_paths = explode(',', $additional_images);
            foreach ($image_paths as $image_path) {
                $image_path = trim($image_path);
                if (!empty($image_path)) {
                    $images[] = [
                        'url' => $this->buildImageUrl($image_path),
                        'is_primary' => false,
                        'alt_text' => ''
                    ];
                }
            }
        }

        return $images;
    }

    private function buildImageUrl($image_path) {
        $image_path = trim($image_path);
        
        if (empty($image_path)) {
            return '';
        }

        // אם כבר כתובת מלאה
        if (preg_match('/^https?:\/\//', $image_path)) {
            return $image_path;
        }

        // בניית כתובת מלאה
        $domain = rtrim($this->options['image_domain'], '/');
        $path = ltrim($image_path, '/');
        
        return $domain . '/' . $path;
    }

    private function parseAdditionalAttributes($attributes_string) {
        $attributes = [];
        
        if (empty($attributes_string)) {
            return $attributes;
        }

        // פורמט: attr1=value1,attr2=value2
        $pairs = explode(',', $attributes_string);
        
        foreach ($pairs as $pair) {
            $parts = explode('=', $pair, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                if (!empty($key) && !empty($value)) {
                    $attributes[$key] = $value;
                }
            }
        }

        return $attributes;
    }

    private function detectAttributeType($value) {
        // זיהוי צבע (hex)
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
            return 'color';
        }
        
        // זיהוי מספרים (מידות)
        if (is_numeric($value)) {
            return 'text';
        }

        return 'text';
    }

    private function parseStatus($status) {
        return in_array($status, ['1', 'true', 'yes']) ? 'active' : 'draft';
    }

    private function importProducts($products_data) {
        $total_products = count($products_data);
        $processed = 0;

        foreach ($products_data as $product_data) {
            try {
                $this->importSingleProduct($product_data);
                $this->stats['imported_products']++;
            } catch (Exception $e) {
                $this->addError("שגיאה בייבוא מוצר {$product_data['sku']}: " . $e->getMessage());
                $this->stats['failed_products']++;
            }

            $processed++;
            $progress = 20 + (($processed / $total_products) * 70); // 20-90%
            $this->updateProgress($progress, "מעבד מוצר {$processed} מתוך {$total_products}");
            $this->stats['processed_rows'] = $processed;
        }

        $this->updateProgress(90, 'מסיים ייבוא...');
    }

    private function importSingleProduct($product_data) {
        // בדיקת קיום מוצר
        if ($this->options['skip_existing'] && $this->productExists($product_data['sku'])) {
            return;
        }

        $this->pdo->beginTransaction();

        try {
            // יצירת מוצר ראשי
            $product_id = $this->createProduct($product_data);

            // יצירת קטגוריות וקישור
            if (!empty($product_data['categories'])) {
                $this->linkProductToCategories($product_id, $product_data['categories']);
            }

            // טיפול בתמונות
            if (!empty($product_data['images']) && $this->options['download_images']) {
                $this->handleProductImages($product_id, $product_data['images']);
            }

            // יצירת וריאציות אם קיימות
            if ($product_data['type'] === 'configurable' && !empty($product_data['variants'])) {
                $this->createProductVariants($product_id, $product_data);
            }

            $this->pdo->commit();

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function productExists($sku) {
        $stmt = $this->pdo->prepare("SELECT id FROM products WHERE sku = ? AND store_id = ?");
        $stmt->execute([$sku, $this->store_id]);
        return $stmt->fetch() !== false;
    }

    private function createProduct($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO products (
                store_id, name, slug, description, short_description, sku, 
                price, weight, status, has_variants, vendor, product_type,
                created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $slug = $this->generateSlug($data['name']);
        $has_variants = $data['type'] === 'configurable' ? 1 : 0;
        $vendor = $data['additional_attributes']['brands'] ?? '';
        $product_type = $data['type']; // 'configurable' או 'simple'

        $stmt->execute([
            $this->store_id,
            $data['name'],
            $slug,
            $data['description'],
            $data['short_description'],
            $data['sku'],
            $data['price'],
            $data['weight'] ?? 0,
            $data['status'] ?? 'active',
            $has_variants,
            $vendor,
            $product_type
        ]);

        return $this->pdo->lastInsertId();
    }

    private function generateSlug($name) {
        if (!$name) {
            return 'product-' . uniqid();
        }
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9\s\-א-ת]/', '', $slug);
        $slug = preg_replace('/[\s\-]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // בדיקת ייחודיות
        $original_slug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug)) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists($slug) {
        $stmt = $this->pdo->prepare("SELECT id FROM products WHERE slug = ? AND store_id = ?");
        $stmt->execute([$slug, $this->store_id]);
        return $stmt->fetch() !== false;
    }

    private function linkProductToCategories($product_id, $categories) {
        foreach ($categories as $category_path) {
            $category_id = $this->findOrCreateCategory($category_path);
            if ($category_id) {
                $stmt = $this->pdo->prepare("
                    INSERT IGNORE INTO product_categories (product_id, category_id) 
                    VALUES (?, ?)
                ");
                $stmt->execute([$product_id, $category_id]);
            }
        }
    }

    private function findOrCreateCategory($category_path) {
        // פיצול נתיב הקטגוריה
        $parts = explode('/', $category_path);
        $parent_id = null;

        foreach ($parts as $category_name) {
            $category_name = trim($category_name);
            if (empty($category_name)) continue;

            // בדיקת קיום קטגוריה
            $stmt = $this->pdo->prepare("
                SELECT id FROM categories 
                WHERE store_id = ? AND name = ? AND parent_id " . 
                ($parent_id ? "= ?" : "IS NULL")
            );
            
            $params = [$this->store_id, $category_name];
            if ($parent_id) {
                $params[] = $parent_id;
            }
            
            $stmt->execute($params);
            $category = $stmt->fetch();

            if ($category) {
                $parent_id = $category['id'];
            } elseif ($this->options['create_categories']) {
                // יצירת קטגוריה חדשה
                $slug = $this->generateCategorySlug($category_name);
                
                $stmt = $this->pdo->prepare("
                    INSERT INTO categories (
                        store_id, name, slug, parent_id, 
                        is_active, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, 1, NOW(), NOW())
                ");
                
                $stmt->execute([
                    $this->store_id, 
                    $category_name, 
                    $slug, 
                    $parent_id
                ]);
                
                $parent_id = $this->pdo->lastInsertId();
            } else {
                return null;
            }
        }

        return $parent_id;
    }

    private function generateCategorySlug($name) {
        if (!$name) {
            return 'category-' . uniqid();
        }
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9\s\-א-ת]/', '', $slug);
        $slug = preg_replace('/[\s\-]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        $original_slug = $slug;
        $counter = 1;
        
        while ($this->categorySlugExists($slug)) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function categorySlugExists($slug) {
        $stmt = $this->pdo->prepare("SELECT id FROM categories WHERE slug = ? AND store_id = ?");
        $stmt->execute([$slug, $this->store_id]);
        return $stmt->fetch() !== false;
    }

    private function handleProductImages($product_id, $images) {
        $imageUploader = new ImageUploader();
        $sort_order = 0;

        foreach ($images as $image_data) {
            try {
                if (empty($image_data['url'])) continue;

                error_log("Attempting to download image: " . $image_data['url']);

                // הורדת התמונה
                $uploaded_image = $imageUploader->downloadAndUpload(
                    $image_data['url'], 
                    $this->store_id,
                    'products'
                );

                if ($uploaded_image) {
                    error_log("Image downloaded successfully: " . $uploaded_image['url']);
                } else {
                    error_log("Failed to download image: " . $image_data['url']);
                    $stmt = $this->pdo->prepare("
                        INSERT INTO product_media (
                            product_id, type, url, thumbnail_url, alt_text,
                            is_primary, sort_order, created_at
                        ) VALUES (?, 'image', ?, ?, ?, ?, ?, NOW())
                    ");

                    $stmt->execute([
                        $product_id,
                        $uploaded_image['url'],
                        $uploaded_image['thumbnail_url'],
                        $image_data['alt_text'],
                        $image_data['is_primary'] ? 1 : 0,
                        $sort_order++
                    ]);
                }

            } catch (Exception $e) {
                error_log("Image processing error for {$image_data['url']}: " . $e->getMessage());
                $this->addError("שגיאה בהעלאת תמונה {$image_data['url']}: " . $e->getMessage());
            }
        }
    }

    private function createProductVariants($product_id, $product_data) {
        // יצירת מאפיינים
        $attribute_mapping = [];
        foreach ($product_data['attributes'] as $attr_key => $attr_data) {
            $attribute_id = $this->createProductAttribute($product_id, $attr_data);
            $attribute_mapping[$attr_key] = [
                'id' => $attribute_id,
                'values' => []
            ];

            // יצירת ערכי מאפיינים
            foreach ($attr_data['values'] as $value) {
                $value_id = $this->createAttributeValue($attribute_id, $value);
                $attribute_mapping[$attr_key]['values'][$value] = $value_id;
            }
        }

        // יצירת וריאציות
        foreach ($product_data['variants'] as $variant_data) {
            $variant_id = $this->createProductVariant($product_id, $variant_data);

            // קישור לערכי מאפיינים
            foreach ($variant_data['attributes'] as $attr_key => $attr_value) {
                if (isset($attribute_mapping[$attr_key]['values'][$attr_value])) {
                    $value_id = $attribute_mapping[$attr_key]['values'][$attr_value];
                    
                    $stmt = $this->pdo->prepare("
                        INSERT INTO variant_attribute_values (variant_id, attribute_value_id)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$variant_id, $value_id]);
                }
            }

            // טיפול בתמונות וריאציה
            if (!empty($variant_data['images']) && $this->options['download_images']) {
                $this->handleVariantImages($product_id, $variant_id, $variant_data['images'], $variant_data['attributes']);
            }
        }
    }

    private function createProductAttribute($product_id, $attr_data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO product_attributes (
                product_id, name, display_name, type, sort_order
            ) VALUES (?, ?, ?, ?, 0)
        ");

        $stmt->execute([
            $product_id,
            $attr_data['name'],
            $attr_data['display_name'],
            $attr_data['type']
        ]);

        return $this->pdo->lastInsertId();
    }

    private function createAttributeValue($attribute_id, $value) {
        // זיהוי צבע
        $color_hex = null;
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
            $color_hex = $value;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO attribute_values (
                attribute_id, value, display_value, color_hex, sort_order
            ) VALUES (?, ?, ?, ?, 0)
        ");

        $stmt->execute([
            $attribute_id,
            $value,
            $value,
            $color_hex
        ]);

        return $this->pdo->lastInsertId();
    }

    private function createProductVariant($product_id, $variant_data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO product_variants (
                product_id, sku, price, weight, is_active, 
                created_at, updated_at
            ) VALUES (?, ?, ?, ?, 1, NOW(), NOW())
        ");

        $stmt->execute([
            $product_id,
            $variant_data['sku'],
            $variant_data['price'],
            $variant_data['weight'] ?? 0
        ]);

        return $this->pdo->lastInsertId();
    }

    private function handleVariantImages($product_id, $variant_id, $images, $attributes) {
        // קביעת gallery_value למיון תמונות לפי מאפיינים
        $gallery_value = '';
        foreach ($attributes as $key => $value) {
            if (in_array($key, ['color', 'צבע'])) {
                $gallery_value = "{$key}: {$value}";
                break;
            }
        }

        $imageUploader = new ImageUploader();
        $sort_order = 1000; // וריאציות מתחילות מסדר גבוה

        foreach ($images as $image_data) {
            try {
                if (empty($image_data['url'])) continue;

                $uploaded_image = $imageUploader->downloadAndUpload(
                    $image_data['url'], 
                    $this->store_id,
                    'products'
                );

                if ($uploaded_image) {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO product_media (
                            product_id, type, url, thumbnail_url, alt_text,
                            gallery_value, is_primary, sort_order, created_at
                        ) VALUES (?, 'image', ?, ?, ?, ?, ?, ?, NOW())
                    ");

                    $stmt->execute([
                        $product_id,
                        $uploaded_image['url'],
                        $uploaded_image['thumbnail_url'],
                        $gallery_value,
                        $gallery_value,
                        0, // וריאציות לא ראשיות
                        $sort_order++
                    ]);
                }

            } catch (Exception $e) {
                $this->addError("שגיאה בהעלאת תמונת וריאציה: " . $e->getMessage());
            }
        }
    }

    private function updateProgress($percent, $step) {
        $stmt = $this->pdo->prepare("
            UPDATE import_jobs 
            SET progress_percent = ?, current_step = ?
            WHERE import_id = ?
        ");
        $stmt->execute([$percent, $step, $this->import_id]);
    }

    private function addError($error) {
        $this->errors[] = $error;
    }
}
?> 