<?php
require_once 'config/database.php';
require_once 'includes/CsvImporter.php';

// קריאת הקובץ CSV לבדיקה
$csv_file = 'uploads/imports/import_6878f6c4b35a30.93184070.csv';

if (!file_exists($csv_file)) {
    die("קובץ CSV לא נמצא: $csv_file");
}

echo "<h2>בדיקת פרסור CSV</h2>\n";

// יצירת importer
$importer = new CsvImporter(1, 'test');

// קריאת הנתונים
$csv_content = file_get_contents($csv_file);
$lines = str_getcsv($csv_content, "\n");
$headers = str_getcsv($lines[0]);

$csv_data = [];
for ($i = 1; $i < count($lines); $i++) {
    $row = str_getcsv($lines[$i]);
    if (count($row) == count($headers)) {
        $csv_data[] = array_combine($headers, $row);
    }
}

echo "<h3>נתוני CSV שנקראו:</h3>";
echo "<strong>סך שורות:</strong> " . count($csv_data) . "<br>";

$configurable_products = [];
$simple_products = [];

foreach ($csv_data as $row) {
    $product_type = trim($row['product_type'] ?? '');
    $sku = trim($row['sku'] ?? '');
    
    if ($product_type === 'configurable') {
        $configurable_products[$sku] = $row;
        echo "<div style='background: #e7f3ff; padding: 10px; margin: 10px;'>";
        echo "<strong>Configurable Product:</strong> $sku<br>";
        echo "<strong>שם:</strong> " . htmlspecialchars($row['name']) . "<br>";
        echo "<strong>Variations:</strong> " . htmlspecialchars($row['configurable_variations']) . "<br>";
        echo "<strong>Labels:</strong> " . htmlspecialchars($row['configurable_variation_labels']) . "<br>";
        echo "</div>";
    } elseif ($product_type === 'simple') {
        $simple_products[$sku] = $row;
        echo "<div style='background: #f0f0f0; padding: 5px; margin: 2px;'>";
        echo "<strong>Simple Product:</strong> $sku - " . htmlspecialchars($row['name']) . "<br>";
        echo "</div>";
    }
}

echo "<h3>עיבוד המוצר הקבוצתי:</h3>";

if (!empty($configurable_products)) {
    foreach ($configurable_products as $parent_sku => $parent_data) {
        echo "<div style='border: 2px solid #007cba; padding: 15px; margin: 10px;'>";
        echo "<h4>עיבוד: $parent_sku</h4>";
        
        // פרסור וריאציות
        $variations_string = $parent_data['configurable_variations'] ?? '';
        if (!empty($variations_string)) {
            $variant_strings = explode('|', $variations_string);
            
            echo "<strong>וריאציות שנמצאו:</strong><br>";
            foreach ($variant_strings as $variant_string) {
                $variant = [];
                $pairs = explode(',', $variant_string);
                
                foreach ($pairs as $pair) {
                    $parts = explode('=', $pair, 2);
                    if (count($parts) === 2) {
                        $variant[trim($parts[0])] = trim($parts[1]);
                    }
                }
                
                if (!empty($variant)) {
                    $variant_sku = $variant['sku'] ?? 'N/A';
                    echo "- SKU: $variant_sku";
                    
                    // בדיקה אם הוריאציה קיימת ב-simple products
                    if (isset($simple_products[$variant_sku])) {
                        echo " ✅ נמצא במוצרים פשוטים";
                        echo " - שם: " . htmlspecialchars($simple_products[$variant_sku]['name']);
                    } else {
                        echo " ❌ לא נמצא במוצרים פשוטים";
                    }
                    
                    foreach ($variant as $key => $value) {
                        if ($key !== 'sku') {
                            echo ", $key=$value";
                        }
                    }
                    echo "<br>";
                }
            }
            
            echo "<div style='background: #d4edda; padding: 10px; margin-top: 10px;'>";
            echo "<strong>סיכום:</strong><br>";
            echo "מוצר ראשי: $parent_sku<br>";
            echo "מספר וריאציות: " . count($variant_strings) . "<br>";
            echo "צריך ליצור: מוצר אחד עם " . count($variant_strings) . " וריאציות<br>";
            echo "</div>";
        }
        
        echo "</div>";
    }
} else {
    echo "<div style='color: red;'>לא נמצאו מוצרים קבוצתיים!</div>";
}

echo "<h3>המסקנה:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7;'>";
echo "<strong>הבעיה:</strong> המערכת יוצרת 9 מוצרים נפרדים במקום אחד עם וריאציות<br>";
echo "<strong>הפתרון:</strong> לוודא שהמוצר הקבוצתי יוצר אחד מוצר ראשי ומחבר אליו את כל הוריאציות<br>";
echo "<strong>מה שצריך לקרות:</strong> מוצר 1230527050 עם 8 product_variants במסד הנתונים<br>";
echo "</div>";
?> 