# תבנית מהירה לבלוק חדש עם רכיבים משותפים

## החלף את {BLOCK_NAME} בשם הבלוק שלך (ללא מקפים)
## החלף את {block-name} בשם עם מקפים

**💡 הרכיבים המשותפים כוללים: תוכן, רקע, צבעים, פריסה, מרווחים, טיפוגרפיה והתאמה אישית**

### 1. PHP Block: `builder/blocks/{block-name}.php`
```php
<?php
function get{BLOCK_NAME}Data() {
    $dataFile = __DIR__ . '/../data/{block-name}.json';
    $defaultData = [
        'title' => 'כותרת ברירת מחדל'
    ];
    
    if (file_exists($dataFile)) {
        $saved = json_decode(file_get_contents($dataFile), true);
        return array_merge($defaultData, $saved);
    }
    return $defaultData;
}

$data = get{BLOCK_NAME}Data();
?>

<section id="{BLOCK_NAME}Section" data-builder-block="{block-name}">
    <h2><?php echo htmlspecialchars($data['title']); ?></h2>
</section>
```

### 2. JavaScript: `builder/assets/js/sections/{block-name}.js`
```javascript
window.{BLOCK_NAME}Section = class {BLOCK_NAME}Section {
    constructor(builder) {
        this.builder = builder;
        this.data = { title: 'כותרת ברירת מחדל' };
    }
    
    async onOpen() {
        this.builder.slideToSettings();
        await this.loadData();
        this.setupEventListeners();
    }
    
    async onClose() {
        this.builder.slideToSections();
    }
    
    async loadData() {
        const response = await fetch('api/load-{block-name}.php');
        const result = await response.json();
        if (result.success) this.data = result.data;
    }
    
    async saveData() {
        const response = await fetch('api/save-{block-name}.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(this.data)
        });
        return response.json();
    }
    
    setupEventListeners() {
        // הוסף event listeners כאן
    }
    
    getData() { return this.data; }
};
```

### 3. Settings: `builder/settings/{block-name}-settings.php`
```php
<?php
$sectionType = '{BLOCK_NAME}';
$defaultData = [
    // Content
    'title' => 'כותרת ברירת מחדל',
    'subtitle' => 'תת כותרת',
    'buttonText' => 'לחץ כאן',
    'buttonLink' => '#',
    'buttonNewTab' => false,
    
    // Background
    'bgType' => 'color',
    'bgColor' => '#ffffff',
    
    // Colors
    'titleColor' => '#000000',
    'subtitleColor' => '#666666',
    'buttonBgColor' => '#3B82F6',
    'buttonTextColor' => '#ffffff',
    
    // Layout
    'width' => 'full',
    'contentPosition' => 'center-center',
    'heightType' => 'auto',
    'heightValue' => 500,
    
    // Spacing
    'paddingTop' => 40,
    'paddingBottom' => 40,
    'paddingRight' => 20,
    'paddingLeft' => 20,
    'marginTop' => 0,
    'marginBottom' => 0,
    'marginRight' => 0,
    'marginLeft' => 0,
    
    // Typography
    'titleFontSize' => 24,
    'titleFontFamily' => "'Noto Sans Hebrew', sans-serif",
    'subtitleFontSize' => 16,
    'subtitleFontFamily' => "'Noto Sans Hebrew', sans-serif",
    'buttonFontSize' => 14,
    'buttonFontFamily' => "'Noto Sans Hebrew', sans-serif",
    
    // Custom
    'customClass' => '',
    'customId' => ''
];
?>

<div class="p-6 h-full overflow-y-auto">
    <div class="flex items-center gap-3 mb-6">
        <button id="backButton" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition-colors">
            <i class="ri-arrow-right-line text-gray-600"></i>
        </button>
        <h3 id="settingsTitle" class="text-lg font-medium text-gray-900">הגדרות {BLOCK_NAME}</h3>
    </div>
    
    <form id="{BLOCK_NAME}Form" class="space-y-6">
        
        <?php include 'components/content.php'; ?>
        <?php include 'components/background.php'; ?>
        <?php include 'components/colors.php'; ?>
        <?php include 'components/layout.php'; ?>
        <?php include 'components/spacing.php'; ?>
        <?php include 'components/typography.php'; ?>
        <?php include 'components/custom.php'; ?>
        
        <!-- שדות מיוחדים לבלוק הזה -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-3">
                <i class="ri-settings-line text-purple-600"></i>
                <h4 class="font-medium text-gray-900">הגדרות מיוחדות</h4>
            </div>
            <div class="space-y-4">
                <!-- הוסף כאן שדות ייחודיים לבלוק שלך -->
            </div>
        </div>
        
    </form>
</div>
```

### 4. Save API: `builder/api/save-{block-name}.php`
```php
<?php
header('Content-Type: application/json');
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$cleanData = ['title' => strip_tags($input['title'])];

file_put_contents(__DIR__ . '/../data/{block-name}.json', json_encode($cleanData));
echo json_encode(['success' => true]);
?>
```

### 5. Load API: `builder/api/load-{block-name}.php`
```php
<?php
header('Content-Type: application/json');
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

$dataFile = __DIR__ . '/../data/{block-name}.json';
$data = file_exists($dataFile) 
    ? json_decode(file_get_contents($dataFile), true)
    : ['title' => 'ברירת מחדל'];

echo json_encode(['success' => true, 'data' => $data]);
?>
```

### 6. הוספה לרשימה: `builder/sections-list/sections.php`
```php
<div class="section-item border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors relative" data-section="{block-name}">
    <!-- Main Info Row -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="ri-icon-name text-blue-600"></i>
            </div>
            <div>
                <h3 class="font-medium text-gray-900">{BLOCK_NAME}</h3>
                <p class="text-sm text-gray-500">תיאור הבלוק</p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <!-- Toggle Actions Button -->
            <button class="toggle-actions-btn w-6 h-6 bg-gray-50 hover:bg-gray-100 rounded flex items-center justify-center transition-all text-xs" 
                    data-section="{block-name}" title="פעולות">
                <i class="ri-arrow-down-s-line text-gray-500 transition-transform"></i>
            </button>
            
            <!-- Settings Button -->
            <button class="settings-btn w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition-colors" 
                    data-section="{block-name}" title="הגדרות">
                <i class="ri-settings-3-line text-gray-600"></i>
            </button>
        </div>
    </div>
    
    <!-- Action Buttons Row (Collapsible) -->
    <div class="section-actions max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
        <div class="flex items-center justify-center gap-2 pt-3 mt-3 border-t border-gray-100">
            <button class="action-btn move-up-btn w-8 h-7 bg-gray-50 hover:bg-blue-100 rounded flex items-center justify-center transition-colors text-xs" 
                    data-section="{block-name}" data-action="move-up" title="הזז למעלה">
                <i class="ri-arrow-up-line text-gray-600"></i>
            </button>
            <button class="action-btn move-down-btn w-8 h-7 bg-gray-50 hover:bg-blue-100 rounded flex items-center justify-center transition-colors text-xs" 
                    data-section="{block-name}" data-action="move-down" title="הזז למטה">
                <i class="ri-arrow-down-line text-gray-600"></i>
            </button>
            <button class="action-btn duplicate-btn w-8 h-7 bg-gray-50 hover:bg-green-100 rounded flex items-center justify-center transition-colors text-xs" 
                    data-section="{block-name}" data-action="duplicate" title="שכפל">
                <i class="ri-file-copy-line text-gray-600"></i>
            </button>
            <button class="action-btn hide-btn w-8 h-7 bg-gray-50 hover:bg-yellow-100 rounded flex items-center justify-center transition-colors text-xs" 
                    data-section="{block-name}" data-action="hide" title="הסתר">
                <i class="ri-eye-off-line text-gray-600"></i>
            </button>
            <button class="action-btn delete-btn w-8 h-7 bg-gray-50 hover:bg-red-100 rounded flex items-center justify-center transition-colors text-xs" 
                    data-section="{block-name}" data-action="delete" title="מחק">
                <i class="ri-delete-bin-line text-red-600"></i>
            </button>
        </div>
    </div>
</div>
``` 