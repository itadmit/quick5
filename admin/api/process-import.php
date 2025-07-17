<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/CsvImporter.php';

// הגדרת זמן ריצה ללא הגבלה
set_time_limit(0);
ini_set('memory_limit', '512M');

// הסתרת warnings לפלט נקי
if (php_sapi_name() !== 'cli') {
    ini_set('display_errors', 0);
    error_reporting(E_ERROR | E_PARSE);
    ob_start();
}

// קבלת מזהה ייבוא
$import_id = $_GET['import_id'] ?? $argv[1] ?? '';
if (empty($import_id)) {
    if (php_sapi_name() === 'cli') {
        exit('מזהה ייבוא חסר');
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'מזהה ייבוא חסר']);
        exit;
    }
}

try {
    // הוספת לוג התחלה
    error_log("Process Import Started for ID: $import_id");
    
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    error_log("Database connection established");
    
    // שליפת פרטי הייבוא
    $stmt = $pdo->prepare("
        SELECT * FROM import_jobs 
        WHERE import_id = ? AND status = 'pending'
    ");
    $stmt->execute([$import_id]);
    $job = $stmt->fetch();
    
    error_log("Job query executed, found: " . ($job ? 'yes' : 'no'));
    
    if (!$job) {
        $error_msg = 'עבודת ייבוא לא נמצאה או שכבר מעובדת';
        error_log("Import job not found: $import_id");
        if (php_sapi_name() === 'cli') {
            exit($error_msg);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => $error_msg]);
            exit;
        }
    }

    error_log("Found job: " . $job['filename'] . " at path: " . $job['file_path']);

    // עדכון סטטוס להתחלה
    $stmt = $pdo->prepare("
        UPDATE import_jobs 
        SET status = 'processing', started_at = NOW(), current_step = 'מתחיל עיבוד...'
        WHERE import_id = ?
    ");
    $stmt->execute([$import_id]);
    
    error_log("Status updated to processing");

    // בדיקת קיום הקובץ
    $file_path = $job['file_path'];
    error_log("Checking file existence: $file_path");
    
    if (!file_exists($file_path)) {
        // נסיון לתקן נתיב יחסי
        $absolute_path = __DIR__ . '/../../' . $file_path;
        error_log("Trying absolute path: $absolute_path");
        
        if (file_exists($absolute_path)) {
            $file_path = $absolute_path;
            error_log("File found at absolute path");
        } else {
            throw new Exception("קובץ CSV לא נמצא בנתיב: $file_path או $absolute_path");
        }
    }

    // יצירת אובייקט ייבוא
    $importer = new CsvImporter($job['store_id'], $import_id);
    $importer->setOptions([
        'skip_existing' => $job['skip_existing'],
        'download_images' => $job['download_images'],
        'create_categories' => $job['create_categories'],
        'image_domain' => $job['image_domain'],
        'image_quality' => $job['image_quality']
    ]);

    // ביצוע הייבוא
    error_log("Starting import with file: $file_path");
    $result = $importer->import($file_path);

    // עדכון תוצאה סופית
    $status = $result['success'] ? 'completed' : 'failed';
    $stmt = $pdo->prepare("
        UPDATE import_jobs 
        SET 
            status = ?,
            total_rows = ?,
            processed_rows = ?,
            imported_products = ?,
            failed_products = ?,
            progress_percent = 100,
            current_step = ?,
            error_log = ?,
            completed_at = NOW()
        WHERE import_id = ?
    ");
    
    $stmt->execute([
        $status,
        $result['total_rows'] ?? 0,
        $result['processed_rows'] ?? 0,
        $result['imported_products'] ?? 0,
        $result['failed_products'] ?? 0,
        $result['success'] ? 'הייבוא הושלם בהצלחה' : 'הייבוא נכשל',
        $result['error_log'] ?? '',
        $import_id
    ]);

    // ניקוי קובץ זמני
    if (file_exists($file_path)) {
        unlink($file_path);
        error_log("Cleaned up temporary file: $file_path");
    }

    error_log("Import completed with result: " . ($result['success'] ? 'success' : 'failure'));
    
    if (php_sapi_name() === 'cli') {
        echo $result['success'] ? "ייבוא הושלם בהצלחה\n" : "ייבוא נכשל\n";
    } else {
        // ניקוי פלט קודם
        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => $result['success'],
            'message' => $result['success'] ? 'ייבוא הושלם בהצלחה' : 'ייבוא נכשל',
            'result' => $result
        ]);
    }

} catch (Exception $e) {
    error_log("Import failed with error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // עדכון שגיאה
    try {
        $stmt = $pdo->prepare("
            UPDATE import_jobs 
            SET 
                status = 'failed',
                current_step = 'שגיאה בעיבוד',
                error_log = ?,
                completed_at = NOW()
            WHERE import_id = ?
        ");
        $stmt->execute([$e->getMessage(), $import_id]);
        error_log("Updated job status to failed");
    } catch (Exception $dbError) {
        error_log("Failed to update job status: " . $dbError->getMessage());
    }
    
    if (php_sapi_name() === 'cli') {
        echo "שגיאה: " . $e->getMessage() . "\n";
    } else {
        // ניקוי פלט קודם
        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'שגיאה: ' . $e->getMessage()
        ]);
    }
}
?> 