<?php
require_once 'includes/CsvImporter.php';

// קריאת הקובץ CSV וחיפוש דוגמת תמונות
$csvFile = './example-import/magento-1-product-variable.csv';

if (!file_exists($csvFile)) {
    echo "קובץ CSV לא נמצא: $csvFile\n";
    exit;
}

echo "בודק איך נבנות כתובות התמונות:\n\n";

// קריאת כמה שורות מה-CSV
$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle);
$configurable_row = fgetcsv($handle); // השורה הראשונה של המוצר הקבוצתי
$simple_row = fgetcsv($handle); // שורה ראשונה של מוצר פשוט

fclose($handle);

// בניית מערך נתונים
$configurable_data = array_combine($header, $configurable_row);
$simple_data = array_combine($header, $simple_row);

echo "=== מוצר קבוצתי (1230527050) ===\n";
echo "SKU: " . $configurable_data['sku'] . "\n";
echo "base_image: '" . ($configurable_data['base_image'] ?? 'ריק') . "'\n";
echo "additional_images: '" . ($configurable_data['additional_images'] ?? 'ריק') . "'\n";

echo "\n=== מוצר פשוט (12305270500212) ===\n";
echo "SKU: " . $simple_data['sku'] . "\n";
echo "base_image: '" . ($simple_data['base_image'] ?? 'ריק') . "'\n";
echo "additional_images: '" . ($simple_data['additional_images'] ?? 'ריק') . "'\n";

// בדיקת בניית URL
echo "\n=== בדיקת בניית URL ===\n";
$image_domain = 'https://www.studiopasha.co.il/media/catalog/product';
$image_path = $simple_data['base_image'] ?? '';

echo "Domain: $image_domain\n";
echo "Path מהCSV: '$image_path'\n";

// סימולציה של buildImageUrl
function buildImageUrl($image_path, $domain) {
    $image_path = trim($image_path);
    
    if (empty($image_path)) {
        return '';
    }

    // אם כבר כתובת מלאה
    if (preg_match('/^https?:\/\//', $image_path)) {
        return $image_path;
    }

    // בניית כתובת מלאה
    $domain = rtrim($domain, '/');
    $path = ltrim($image_path, '/');
    
    return $domain . '/' . $path;
}

$full_url = buildImageUrl($image_path, $image_domain);
echo "URL מלא: $full_url\n";

// בדיקת תקינות URL
$is_valid = filter_var($full_url, FILTER_VALIDATE_URL);
echo "URL תקין: " . ($is_valid ? 'כן' : 'לא') . "\n";

// בדיקת קיום התמונה
echo "\n=== בדיקת קיום התמונה ===\n";
if ($is_valid) {
    $headers = @get_headers($full_url, 1);
    if ($headers && strpos($headers[0], '200') !== false) {
        echo "התמונה קיימת! ✅\n";
        echo "Content-Type: " . ($headers['Content-Type'] ?? 'לא ידוע') . "\n";
    } else {
        echo "התמונה לא קיימת או לא נגישה ❌\n";
    }
} else {
    echo "לא ניתן לבדוק - URL לא תקין\n";
}

// בדיקת CsvImporter options
echo "\n=== בדיקת אפשרויות CsvImporter ===\n";
$importer = new CsvImporter(1, 1); // store_id=1, import_id=1

// הגדרת options ידנית
$reflection = new ReflectionClass($importer);
$optionsProperty = $reflection->getProperty('options');
$optionsProperty->setAccessible(true);
$options = $optionsProperty->getValue($importer);
$options['image_domain'] = $image_domain;
$optionsProperty->setValue($importer, $options);

echo "image_domain מוגדר: " . $options['image_domain'] . "\n";

// קריאה פרטית לפונקציה buildImageUrl
$method = $reflection->getMethod('buildImageUrl');
$method->setAccessible(true);

$result_url = $method->invoke($importer, $image_path);
echo "תוצאת buildImageUrl: $result_url\n";
?> 