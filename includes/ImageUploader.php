<?php
/**
 * מחלקת העלאת תמונות
 * תומכת במערכת קבצים מקומית עם הכנה למעבר ל-S3
 */

class ImageUploader {
    private $uploadPath;
    private $baseUrl;
    private $allowedTypes = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    private $maxFileSize = 10 * 1024 * 1024; // 10MB
    private $useS3 = false; // בעתיד נשנה ל-true
    
    public function __construct($storeSlug = null) {
        $this->uploadPath = __DIR__ . '/../uploads/';
        
        // זיהוי דינמי של הפורט והפרוטוקול
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        
        // אם זה localhost, וודא שאנחנו משתמשים בפורט הנכון
        if (strpos($host, 'localhost') !== false) {
            // אם לא מצוין פורט ב-HTTP_HOST, נסה לגלות מ-SERVER_PORT
            if (!strpos($host, ':')) {
                $port = $_SERVER['SERVER_PORT'] ?? '8000';
                $host = 'localhost:' . $port;
            }
        }
        
        $this->baseUrl = $scheme . '://' . $host . '/uploads/';
        
        // יצירת תקיות לחנות ספציפית
        if ($storeSlug) {
            $this->uploadPath .= "stores/{$storeSlug}/";
            $this->baseUrl .= "stores/{$storeSlug}/";
            $this->ensureDirectoryExists($this->uploadPath);
        }
    }
    
    /**
     * העלאת תמונה מ-base64
     */
    public function uploadFromBase64($base64Data, $filename = null, $folder = 'products') {
        try {
            // פענוח base64
            if (strpos($base64Data, ',') !== false) {
                list($type, $data) = explode(',', $base64Data);
                $data = base64_decode($data);
                
                // זיהוי סוג קובץ
                preg_match('/data:image\/([a-zA-Z0-9]+);/', $type, $matches);
                $extension = $matches[1] ?? 'jpg';
            } else {
                $data = base64_decode($base64Data);
                $extension = 'jpg';
            }
            
            if (!$data) {
                throw new Exception('נתוני תמונה לא תקינים');
            }
            
            // בדיקת גודל
            if (strlen($data) > $this->maxFileSize) {
                throw new Exception('קובץ גדול מדי (מקסימום 10MB)');
            }
            
            // יצירת שם קובץ ייחודי
            $filename = $filename ?: $this->generateUniqueFilename($extension);
            
            // העלאה
            return $this->saveFile($data, $filename, $folder);
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * העלאת תמונה מ-FormData
     */
    public function uploadFromFormData($fileData, $folder = 'products') {
        try {
            // בדיקת שגיאות העלאה
            if ($fileData['error'] !== UPLOAD_ERR_OK) {
                throw new Exception($this->getUploadErrorMessage($fileData['error']));
            }
            
            // בדיקת סוג קובץ
            $extension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $this->allowedTypes)) {
                throw new Exception('סוג קובץ לא נתמך. מותרים: ' . implode(', ', $this->allowedTypes));
            }
            
            // בדיקת גודל
            if ($fileData['size'] > $this->maxFileSize) {
                throw new Exception('קובץ גדול מדי (מקסימום 10MB)');
            }
            
            // קריאת נתוני הקובץ
            $data = file_get_contents($fileData['tmp_name']);
            
            // יצירת שם קובץ ייחודי
            $filename = $this->generateUniqueFilename($extension);
            
            // העלאה
            return $this->saveFile($data, $filename, $folder);
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * שמירת קובץ (מקומי או S3)
     */
    private function saveFile($data, $filename, $folder) {
        if ($this->useS3) {
            return $this->saveToS3($data, $filename, $folder);
        } else {
            return $this->saveLocal($data, $filename, $folder);
        }
    }
    
    /**
     * שמירה מקומית
     */
    private function saveLocal($data, $filename, $folder) {
        // יצירת תקיית יעד
        $folderPath = $this->uploadPath . $folder . '/';
        $this->ensureDirectoryExists($folderPath);
        
        $filePath = $folderPath . $filename;
        $url = $this->baseUrl . $folder . '/' . $filename;
        
        // שמירת הקובץ
        if (file_put_contents($filePath, $data) === false) {
            throw new Exception('שגיאה בשמירת הקובץ');
        }
        
        // יצירת thumbnail
        $thumbnailUrl = $this->createThumbnail($filePath, $folderPath, $filename);
        
        return [
            'success' => true,
            'url' => $url,
            'thumbnail_url' => $thumbnailUrl,
            'filename' => $filename,
            'size' => strlen($data),
            'storage' => 'local'
        ];
    }
    
    /**
     * שמירה ל-S3 (להכנה לעתיד)
     */
    private function saveToS3($data, $filename, $folder) {
        // TODO: הטמעת S3
        // $s3Client = new S3Client([...]);
        // $result = $s3Client->putObject([...]);
        
        throw new Exception('S3 לא מוכן עדיין');
    }
    
    /**
     * יצירת thumbnail
     */
    private function createThumbnail($originalPath, $folderPath, $originalFilename) {
        try {
            $thumbnailSize = 300;
            $pathInfo = pathinfo($originalFilename);
            $thumbnailFilename = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
            $thumbnailPath = $folderPath . $thumbnailFilename;
            
            // טעינת התמונה לפי סוג
            $extension = strtolower($pathInfo['extension']);
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($originalPath);
                    break;
                case 'png':
                    $image = imagecreatefrompng($originalPath);
                    break;
                case 'webp':
                    $image = imagecreatefromwebp($originalPath);
                    break;
                case 'gif':
                    $image = imagecreatefromgif($originalPath);
                    break;
                default:
                    return null;
            }
            
            if (!$image) return null;
            
            // קבלת מידות מקוריות
            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);
            
            // חישוב מידות חדשות (שמירה על יחס)
            if ($originalWidth > $originalHeight) {
                $newWidth = $thumbnailSize;
                $newHeight = ($originalHeight * $thumbnailSize) / $originalWidth;
            } else {
                $newHeight = $thumbnailSize;
                $newWidth = ($originalWidth * $thumbnailSize) / $originalHeight;
            }
            
            // יצירת תמונה חדשה
            $thumbnail = imagecreatetruecolor((int)$newWidth, (int)$newHeight);
            
            // שמירה על שקיפות עבור PNG
            if ($extension === 'png') {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
                $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
                imagefill($thumbnail, 0, 0, $transparent);
            }
            
            // שינוי גודל
            imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, (int)$newWidth, (int)$newHeight, $originalWidth, $originalHeight);
            
            // שמירת thumbnail
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($thumbnail, $thumbnailPath, 85);
                    break;
                case 'png':
                    imagepng($thumbnail, $thumbnailPath, 8);
                    break;
                case 'webp':
                    imagewebp($thumbnail, $thumbnailPath, 85);
                    break;
                case 'gif':
                    imagegif($thumbnail, $thumbnailPath);
                    break;
            }
            
            // ניקוי זיכרון
            imagedestroy($image);
            imagedestroy($thumbnail);
            
            return str_replace($this->uploadPath, $this->baseUrl, $thumbnailPath);
            
        } catch (Exception $e) {
            error_log("Thumbnail creation failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * מחיקת תמונה
     */
    public function deleteImage($url) {
        if ($this->useS3) {
            return $this->deleteFromS3($url);
        } else {
            return $this->deleteLocal($url);
        }
    }
    
    /**
     * מחיקה מקומית
     */
    private function deleteLocal($url) {
        $filePath = str_replace($this->baseUrl, $this->uploadPath, $url);
        
        if (file_exists($filePath)) {
            unlink($filePath);
            
            // מחיקת thumbnail אם קיים
            $pathInfo = pathinfo($filePath);
            $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * מחיקה מ-S3 (להכנה לעתיד)
     */
    private function deleteFromS3($url) {
        // TODO: הטמעת מחיקה מ-S3
        return false;
    }
    
    /**
     * יצירת שם קובץ ייחודי
     */
    private function generateUniqueFilename($extension) {
        return uniqid() . '_' . time() . '.' . $extension;
    }
    
    /**
     * וידוא קיום תקייה
     */
    private function ensureDirectoryExists($path) {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
    
    /**
     * הודעות שגיאה להעלאת קבצים
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'הקובץ גדול מדי';
            case UPLOAD_ERR_PARTIAL:
                return 'הקובץ הועלה חלקית בלבד';
            case UPLOAD_ERR_NO_FILE:
                return 'לא נבחר קובץ להעלאה';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'תקיית temp לא נמצאה';
            case UPLOAD_ERR_CANT_WRITE:
                return 'שגיאה בכתיבת הקובץ';
            case UPLOAD_ERR_EXTENSION:
                return 'העלאה נחסמה על ידי הרחבה';
            default:
                return 'שגיאה לא ידועה בהעלאה';
        }
    }
    
    /**
     * קבלת מידע על חנות לצורך יצירת תקיות
     */
    public static function getStoreInfo($storeId) {
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT slug FROM stores WHERE id = ?");
            $stmt->execute([$storeId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * הורדת תמונה מ-URL חיצוני והעלאתה למערכת
     */
    public function downloadAndUpload($imageUrl, $storeId, $folder = 'products') {
        try {
            // אימות URL
            if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                throw new Exception('כתובת תמונה לא תקינה');
            }

            // הכנת הקשר להורדה
            $context = stream_context_create([
                'http' => [
                    'timeout' => 30,
                    'user_agent' => 'Mozilla/5.0 (compatible; QuickShop5 Image Downloader)',
                    'follow_location' => true,
                    'max_redirects' => 3
                ]
            ]);

            // הורדת התמונה
            $imageData = file_get_contents($imageUrl, false, $context);
            
            if ($imageData === false) {
                throw new Exception('לא ניתן להוריד את התמונה');
            }

            // זיהוי סוג התמונה
            $imageInfo = getimagesizefromstring($imageData);
            if (!$imageInfo) {
                throw new Exception('הקובץ שהורד אינו תמונה תקינה');
            }

            // קביעת סיומת הקובץ
            $mimeType = $imageInfo['mime'];
            $extension = '';
            
            switch ($mimeType) {
                case 'image/jpeg':
                    $extension = 'jpg';
                    break;
                case 'image/png':
                    $extension = 'png';
                    break;
                case 'image/webp':
                    $extension = 'webp';
                    break;
                case 'image/gif':
                    $extension = 'gif';
                    break;
                default:
                    throw new Exception('סוג תמונה לא נתמך');
            }

            // בדיקת גודל הקובץ
            $fileSize = strlen($imageData);
            if ($fileSize > $this->maxFileSize) {
                throw new Exception('התמונה גדולה מדי');
            }

            // קבלת מידע על החנות
            $storeInfo = self::getStoreInfo($storeId);
            if (!$storeInfo) {
                throw new Exception('חנות לא נמצאה');
            }

            // הכנת נתיבי שמירה
            $storeSlug = $storeInfo['slug'];
            $uploadDir = $this->uploadPath . "stores/{$storeSlug}/{$folder}/";
            $baseUrl = $this->baseUrl . "stores/{$storeSlug}/{$folder}/";
            
            $this->ensureDirectoryExists($uploadDir);

            // יצירת שמות קבצים ייחודיים
            $filename = $this->generateUniqueFilename($extension);
            $thumbnailFilename = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.' . $extension;

            $fullPath = $uploadDir . $filename;
            $thumbnailPath = $uploadDir . $thumbnailFilename;

            // שמירת התמונה המקורית
            if (file_put_contents($fullPath, $imageData) === false) {
                throw new Exception('שגיאה בשמירת התמונה');
            }

            // יצירת thumbnail
            $thumbnailUrl = $this->createThumbnail($fullPath, $uploadDir, $filename);
            
            // קביעת שם קובץ thumbnail
            $pathInfo = pathinfo($filename);
            $thumbnailFilename = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];

            // החזרת פרטי הקובץ
            return [
                'success' => true,
                'filename' => $filename,
                'url' => $baseUrl . $filename,
                'thumbnail_filename' => $thumbnailFilename,
                'thumbnail_url' => $thumbnailUrl ?: $baseUrl . $thumbnailFilename,
                'size' => $fileSize,
                'width' => $imageInfo[0],
                'height' => $imageInfo[1]
            ];

        } catch (Exception $e) {
            throw new Exception('שגיאה בהורדת התמונה: ' . $e->getMessage());
        }
    }



    /**
     * הכנה למעבר ל-S3
     */
    public function enableS3($config = []) {
        $this->useS3 = true;
        // TODO: הגדרת התצורה ל-S3
        // $this->s3Config = $config;
    }
}
?> 