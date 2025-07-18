# איך להוסיף בלוק חדש לבילדר

המערכת מודולרית לחלוטין - כל בלוק עצמאי עם 4 קבצים בלבד.

## רכיבים משותפים זמינים:
- **📁 `builder/settings/components/content.php`** - תוכן (כותרת, תת-כותרת, כפתור עם קישור ואפשרות פתיחה בכרטיסיה חדשה)
- **📁 `builder/settings/components/background.php`** - רקע (צבע, גרדיאנט, תמונה, סרטון)
- **📁 `builder/settings/components/colors.php`** - צבעים (כותרת, תת-כותרת, כפתור)
- **📁 `builder/settings/components/layout.php`** - פריסה (רוחב, מיקום תוכן, גובה הסקשן)
- **📁 `builder/settings/components/spacing.php`** - מרווחים (פדינג ומרגין)
- **📁 `builder/settings/components/typography.php`** - טיפוגרפיה (גודל פונט וסוג פונט לכותרת, תת-כותרת וכפתור)
- **📁 `builder/settings/components/custom.php`** - התאמה אישית (CSS class ו-ID)

## שלבים להוספת בלוק חדש (למשל "featured-products"):

### 1. בלוק ה-PHP (תצוגה וקריאת נתונים)
**📁 `builder/blocks/featured-products.php`**
```php
<?php
// טעינת נתונים מקובץ JSON
function getFeaturedProductsData() {
    $dataFile = __DIR__ . '/../data/featured-products.json';
    $defaultData = [
        'title' => 'מוצרים מומלצים',
        'showCount' => 6,
        'layout' => 'grid'
    ];
    
    if (file_exists($dataFile)) {
        $saved = json_decode(file_get_contents($dataFile), true);
        return array_merge($defaultData, $saved);
    }
    return $defaultData;
}

$data = getFeaturedProductsData();
?>

<section id="featuredProducts" data-builder-block="featured-products">
    <!-- תוכן הבלוק כאן -->
</section>
```

### 2. מנהל JavaScript (אינטראקציה בזמן אמת)
**📁 `builder/assets/js/sections/featured-products.js`**
```javascript
window.FeaturedProductsSection = class FeaturedProductsSection {
    constructor(builder) {
        this.builder = builder;
        this.data = this.getDefaultData();
    }
    
    getDefaultData() {
        return {
            title: 'מוצרים מומלצים',
            showCount: 6,
            layout: 'grid'
        };
    }
    
    async onOpen() {
        // פתיחת הגדרות
        this.builder.slideToSettings();
        await this.loadData();
        this.setupEventListeners();
        this.populateForm();
    }
    
    async onClose() {
        // סגירת הגדרות
        this.removeEventListeners();
        this.builder.slideToSections();
    }
    
    async loadData() {
        const response = await fetch('api/load-featured-products.php');
        const result = await response.json();
        if (result.success) this.data = result.data;
    }
    
    async saveData() {
        const response = await fetch('api/save-featured-products.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(this.data)
        });
        return response.json();
    }
    
    setupEventListeners() {
        // הגדרת מאזינים לשינויים
    }
    
    getData() {
        return this.data;
    }
};
```

### 3. טופס הגדרות (UI) - **עם שימוש ברכיבים משותפים**
**📁 `builder/settings/featured-products-settings.php`**
```php
<?php
$sectionType = 'featuredProducts';
$defaultData = [
    'title' => 'מוצרים מומלצים',
    'subtitle' => 'הקולקציה החדשה שלנו',
    'buttonText' => 'צפה בכל המוצרים',
    'buttonLink' => '/products',
    'buttonNewTab' => false,
    'bgType' => 'color',
    'bgColor' => '#f8f9fa',
    // ... שאר הנתונים
];
?>

<div class="p-6 h-full overflow-y-auto">
    <div class="flex items-center gap-3 mb-6">
        <button id="backButton" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center">
            <i class="ri-arrow-right-line text-gray-600"></i>
        </button>
        <h3 class="text-lg font-medium">הגדרות מוצרים מומלצים</h3>
    </div>
    
    <form id="featuredProductsForm" class="space-y-6">
        <?php include 'components/content.php'; ?>
        <?php include 'components/background.php'; ?>
        <?php include 'components/colors.php'; ?>
        <?php include 'components/layout.php'; ?>
        <?php include 'components/spacing.php'; ?>
        <?php include 'components/typography.php'; ?>
        <?php include 'components/custom.php'; ?>
        
        <!-- שדות מיוחדים לבלוק הזה -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="font-medium text-gray-900 mb-3">הגדרות מוצרים</h4>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">מספר מוצרים להצגה</label>
                    <input type="number" id="featuredProductsShowCount" name="showCount" 
                           value="6" min="1" max="20"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
            </div>
        </div>
    </form>
</div>
```

### 4. קבצי API (שמירה וטעינה)
**📁 `builder/api/save-featured-products.php`**
```php
<?php
header('Content-Type: application/json');
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'לא מחובר']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$cleanData = [
    'title' => strip_tags($input['title']),
    'showCount' => (int)$input['showCount'],
    'layout' => $input['layout']
];

$dataFile = __DIR__ . '/../data/featured-products.json';
file_put_contents($dataFile, json_encode($cleanData, JSON_PRETTY_PRINT));

echo json_encode(['success' => true]);
?>
```

**📁 `builder/api/load-featured-products.php`**
```php
<?php
header('Content-Type: application/json');
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

$dataFile = __DIR__ . '/../data/featured-products.json';
$defaultData = ['title' => 'מוצרים מומלצים', 'showCount' => 6];

$data = file_exists($dataFile) 
    ? array_merge($defaultData, json_decode(file_get_contents($dataFile), true))
    : $defaultData;

echo json_encode(['success' => true, 'data' => $data]);
?>
```

### 5. הוספה לרשימת הסקשנים
**📁 `builder/sections-list/sections.php`** - הוספת הקוד:
```php
<div class="section-item border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors relative group" data-section="featured-products">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="ri-star-line text-green-600"></i>
            </div>
            <div>
                <h3 class="font-medium text-gray-900">מוצרים מומלצים</h3>
                <p class="text-sm text-gray-500">הצגת מוצרים נבחרים</p>
            </div>
        </div>
        <!-- כפתורי פעולה -->
    </div>
</div>
```

## ✅ זהו! הבלוק החדש מוכן

**יתרונות המבנה המודולרי:**
- 🔧 כל בלוק עצמאי לחלוטין
- 📦 קבצים קטנים וניתנים לתחזוקה
- 🚀 קל להוסיף בלוקים חדשים
- 👥 מתאים לעבודת צוות
- 🔒 לא משפיע על בלוקים אחרים

**קבצי ה-API נפרדים מבטיחים:**
- כל בלוק עם validation משלו
- אין קונפליקטים בין בלוקים
- ביצועים טובים יותר
- קלות בדיבוג ותחזוקה

## פונטים זמינים במערכת:
- **נוטו סאנס עברית** - `'Noto Sans Hebrew', sans-serif` (ברירת מחדל)
- **היבו** - `'Heebo', sans-serif`
- **אופן סאנס עברית** - `'Open Sans Hebrew', sans-serif`
- **אססיטנט** - `'Assistant', sans-serif`
- **וורלה ראונד** - `'Varela Round', sans-serif`
- **Poppins** - `'Poppins', sans-serif`
- **Montserrat** - `'Montserrat', sans-serif`

## תכונות חדשות במערכת:
- **תוכן** - כולל אייקון קישור ואפשרות לפתוח בכרטיסיה חדשה
- **גובה סקשן** - ברירת מחדל, פיקסלים, אחוזים או vh
- **טיפוגרפיה** - גודל פונט וסוג פונט נפרד לכותרת, תת-כותרת וכפתור
- **תצוגה מקדימה בזמן אמת** - כל השינויים נראים מיד ללא שמירה 