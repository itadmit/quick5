# מערכת העלאת תמונות - QuickShop5

מערכת מקיפה להעלאת תמונות שמוכנה למעבר ל-S3 בעתיד.

## 🏗️ מבנה המערכת

### קבצים עיקריים
- `includes/ImageUploader.php` - מחלקת העלאת תמונות
- `api/upload-image.php` - API להעלאת תמונות
- `admin/assets/js/image-upload-handler.js` - מטפל JS

### מבנה תקיות
```
uploads/
└── stores/
    └── [store-slug]/
        ├── products/         # תמונות מוצרים
        ├── attribute-media/  # תמונות מאפיינים
        └── gallery/          # תמונות כלליות
```

## 🚀 שימוש ב-JavaScript

### אתחול
```javascript
const uploader = initImageUploader({
    storeId: 1,                    // מזהה החנות
    apiUrl: '/api/upload-image.php', // כתובת API (אופציונלי)
    onSuccess: (result) => {
        console.log('הצלחה:', result);
    },
    onError: (message) => {
        console.error('שגיאה:', message);
    }
});
```

### העלאת קובץ
```javascript
// מ-input file
const file = document.getElementById('fileInput').files[0];
const result = await uploader.uploadFile(file, 'products');

// מ-base64
const result = await uploader.uploadBase64(base64Data, 'products');

// מ-drag & drop
const results = await uploader.uploadFromDrop(event.dataTransfer, 'products');
```

### יצירת gallery widget
```javascript
const gallery = createGalleryWidget(
    document.getElementById('container'),
    ['existing-image-url.jpg'], // תמונות קיימות
    {
        folder: 'products',
        onRemove: (url) => {
            console.log('תמונה הוסרה:', url);
        }
    }
);
```

## 🔧 שימוש ב-PHP

### יצירת uploader
```php
require_once 'includes/ImageUploader.php';

// קבלת slug החנות
$storeInfo = ImageUploader::getStoreInfo($storeId);
$uploader = new ImageUploader($storeInfo['slug']);
```

### העלאת תמונה
```php
// מ-base64
$result = $uploader->uploadFromBase64($base64Data, $filename, $folder);

// מ-FormData ($_FILES)
$result = $uploader->uploadFromFormData($_FILES['image'], $folder);

if ($result['success']) {
    echo "URL: " . $result['url'];
    echo "Thumbnail: " . $result['thumbnail_url'];
} else {
    echo "שגיאה: " . $result['message'];
}
```

### מחיקת תמונה
```php
$success = $uploader->deleteImage($imageUrl);
```

## 📡 API Endpoints

### POST /api/upload-image.php

#### פרמטרים:
- `store_id` (int) - מזהה החנות
- `folder` (string) - תיקייה (ברירת מחדל: 'products')

#### אחד מהבאים:
- `image` (file) - קובץ תמונה
- `image_data` (string) - base64 של תמונה
- `filename` (string, אופציונלי) - שם קובץ מותאם

#### תגובה:
```json
{
    "success": true,
    "url": "http://localhost:8000/uploads/stores/yogev/products/image.jpg",
    "thumbnail_url": "http://localhost:8000/uploads/stores/yogev/products/image_thumb.jpg",
    "filename": "unique_filename.jpg",
    "size": 12345,
    "storage": "local",
    "folder": "products",
    "store_id": 1,
    "timestamp": 1752081199
}
```

## 🎯 אינטגרציה עם ProductManager

מערכת העלאת התמונות מתחברת אוטומטית ל-ProductManager:

```php
// בProductManager.php
private function saveProductMedia($productId, $mediaData) {
    // אם מגיע base64, המערכת מעלה אוטומטית לקובץ
    // ושומרת את ה-URL במסד הנתונים
}
```

## 🔧 הגדרות

### ImageUploader Options
```php
$uploader = new ImageUploader($storeSlug, [
    'maxFileSize' => 10 * 1024 * 1024, // 10MB
    'allowedTypes' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
    'thumbnailSize' => 300
]);
```

### הכנה ל-S3
```php
// בעתיד כשמעברים ל-S3:
$uploader->enableS3([
    'bucket' => 'my-bucket',
    'region' => 'us-east-1',
    'credentials' => [...]
]);
```

## 🗂️ סוגי תיקיות

- **products** - תמונות מוצרים עיקריות
- **attribute-media** - תמונות לערכי מאפיינים (צבעים, גדלים וכו')
- **gallery** - תמונות כלליות לגלריה
- **variants** - תמונות וריאציות מוצר
- **badges** - תמונות מדבקות ותוויות

## 🛡️ אבטחה

- בדיקת סוגי קבצים מותרים
- הגבלת גודל קובץ (10MB)
- שמות קבצים ייחודיים למניעת התנגשויות
- בדיקת הרשאות חנות

## 🚀 ביצועים

- יצירת thumbnails אוטומטית (300px)
- דחיסה אופטימלית לתמונות
- תמיכה בפורמטים מתקדמים (WebP)
- מוכן לCDN ו-S3

## 🔧 פתרון בעיות

### תמונה לא נשמרת
1. בדוק הרשאות תיקיית `uploads/` (755)
2. בדוק שהחנות קיימת במסד הנתונים
3. בדוק שגודל הקובץ לא חורג מהמותר

### שגיאת JavaScript
1. וודא שטען את `image-upload-handler.js`
2. בדוק שהאתחול עם `initImageUploader()` רץ
3. בדוק שה-API נגיש

### בעיות ביצועים
1. הפעל דחיסה בשרת (gzip)
2. השתמש ב-WebP במקום JPEG
3. עבור ל-S3 + CloudFront לייצור 