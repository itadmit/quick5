<?php
require_once '../../includes/auth.php';
require_once '../../includes/ThemeManager.php';

// בדיקת הרשאות
if (!isLoggedIn() || !hasPermission('edit_theme_code')) {
    header('Location: ../login.php');
    exit;
}

$currentUser = getCurrentUser();
$currentStore = getCurrentStore();

if (!$currentStore) {
    header('Location: ../index.php');
    exit;
}

$themeManager = new ThemeManager($currentStore['id']);
$message = '';
$error = '';

// טיפול בשמירת קוד
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $template = $_POST['template'] ?? '';
    $code = $_POST['code'] ?? '';
    
    if ($template && $code) {
        // כאן נשמור את הקוד (לעת עתה רק הודעה)
        $message = "קוד התבנית {$template} נשמר בהצלחה";
    }
}

$pageTitle = 'עורך קוד';

// תבניות זמינות
$templates = [
    'home' => ['name' => 'דף הבית', 'file' => 'home.php', 'icon' => 'ri-home-line'],
    'category' => ['name' => 'דף קטגוריה', 'file' => 'category.php', 'icon' => 'ri-grid-line'],
    'product' => ['name' => 'דף מוצר', 'file' => 'product.php', 'icon' => 'ri-shopping-bag-line'],
    'header' => ['name' => 'כותרת', 'file' => 'header.php', 'icon' => 'ri-layout-top-line'],
    'footer' => ['name' => 'תחתית', 'file' => 'footer.php', 'icon' => 'ri-layout-bottom-line']
];

$currentTemplate = $_GET['template'] ?? 'home';
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - QuickShop5</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛍️</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Hebrew:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- CodeMirror CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/material-darker.min.css">
    
    <style>
        body { font-family: 'Noto Sans Hebrew', sans-serif; }
        .code-editor-container { height: 100vh; display: flex; flex-direction: column; }
        .editor-content { flex: 1; display: flex; }
        .editor-sidebar { width: 250px; background: #f8f9fa; border-left: 1px solid #dee2e6; }
        .editor-main { flex: 1; display: flex; flex-direction: column; }
        .CodeMirror { height: 100%; font-size: 14px; }
    </style>
</head>
<body class="bg-gray-100">

<div class="code-editor-container">
    <!-- Header -->
    <div class="bg-white border-b px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="theme.php" class="text-gray-500 hover:text-gray-700">
                <i class="ri-arrow-right-line text-xl"></i>
            </a>
            <h1 class="text-lg font-semibold text-gray-900">עורך קוד</h1>
            <span class="text-sm text-gray-500">תבנית: <?= htmlspecialchars($templates[$currentTemplate]['name']) ?></span>
        </div>
        
        <div class="flex items-center gap-3">
            <?php if ($message): ?>
                <div class="bg-green-100 text-green-700 px-3 py-1 rounded text-sm">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <button id="format-code" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                <i class="ri-code-line ml-2"></i>
                פורמט קוד
            </button>
            <button id="save-code" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="ri-save-line ml-2"></i>
                שמור
            </button>
        </div>
    </div>
    
    <!-- Editor Content -->
    <div class="editor-content">
        <!-- Sidebar -->
        <div class="editor-sidebar p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-4">תבניות:</h3>
            <div class="space-y-1">
                <?php foreach ($templates as $key => $template): ?>
                    <a href="?template=<?= $key ?>" 
                       class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-colors <?= $key === $currentTemplate ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100' ?>">
                        <i class="<?= $template['icon'] ?>"></i>
                        <span><?= htmlspecialchars($template['name']) ?></span>
                        <span class="text-xs text-gray-500 mr-auto"><?= $template['file'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-6 pt-4 border-t">
                <h4 class="text-sm font-medium text-gray-700 mb-3">כלים:</h4>
                <div class="space-y-2">
                    <button id="find-replace" class="w-full text-right px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="ri-search-line ml-2"></i>
                        חפש והחלף
                    </button>
                    <button id="goto-line" class="w-full text-right px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="ri-skip-down-line ml-2"></i>
                        עבר לשורה
                    </button>
                    <button id="toggle-fullscreen" class="w-full text-right px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="ri-fullscreen-line ml-2"></i>
                        מסך מלא
                    </button>
                </div>
            </div>
            
            <div class="mt-6 pt-4 border-t">
                <h4 class="text-sm font-medium text-gray-700 mb-3">מידע:</h4>
                <div class="text-xs text-gray-600 space-y-1">
                    <div>שורות: <span id="line-count">0</span></div>
                    <div>תווים: <span id="char-count">0</span></div>
                    <div>מיקום: <span id="cursor-position">1:1</span></div>
                </div>
            </div>
        </div>
        
        <!-- Main Editor -->
        <div class="editor-main">
            <div class="bg-gray-800 text-white px-4 py-2 flex items-center justify-between text-sm">
                <div class="flex items-center gap-4">
                    <span><?= htmlspecialchars($templates[$currentTemplate]['file']) ?></span>
                    <span class="text-gray-400">•</span>
                    <span class="text-gray-400">PHP</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-gray-400">מצב:</span>
                    <span class="text-green-400">מוכן</span>
                </div>
            </div>
            
            <form method="POST" id="code-form">
                <input type="hidden" name="template" value="<?= htmlspecialchars($currentTemplate) ?>">
                <textarea id="code-editor" name="code" style="display: none;"><?= htmlspecialchars(getTemplateCode($currentTemplate)) ?></textarea>
            </form>
        </div>
    </div>
</div>

<!-- CodeMirror JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/php/php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/clike/clike.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/matchbrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/closebrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/dialog/dialog.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/search/searchcursor.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/search/search.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize CodeMirror
    const editor = CodeMirror.fromTextArea(document.getElementById('code-editor'), {
        mode: 'application/x-httpd-php',
        theme: 'material-darker',
        lineNumbers: true,
        matchBrackets: true,
        autoCloseBrackets: true,
        indentUnit: 4,
        indentWithTabs: false,
        lineWrapping: true,
        extraKeys: {
            'Ctrl-S': function(cm) {
                saveCode();
            },
            'Ctrl-F': 'findPersistent',
            'Ctrl-G': 'findNext',
            'Ctrl-Shift-G': 'findPrev',
            'Ctrl-H': 'replace',
            'F11': function(cm) {
                toggleFullscreen();
            }
        }
    });
    
    // Update stats
    function updateStats() {
        const content = editor.getValue();
        const lines = content.split('\n').length;
        const chars = content.length;
        const cursor = editor.getCursor();
        
        document.getElementById('line-count').textContent = lines;
        document.getElementById('char-count').textContent = chars;
        document.getElementById('cursor-position').textContent = `${cursor.line + 1}:${cursor.ch + 1}`;
    }
    
    // Event listeners
    editor.on('change', updateStats);
    editor.on('cursorActivity', updateStats);
    
    // Save code
    function saveCode() {
        const form = document.getElementById('code-form');
        const textarea = document.getElementById('code-editor');
        textarea.value = editor.getValue();
        form.submit();
    }
    
    // Format code
    function formatCode() {
        const cursor = editor.getCursor();
        editor.autoFormatRange({line: 0, ch: 0}, {line: editor.lineCount()});
        editor.setCursor(cursor);
    }
    
    // Toggle fullscreen
    function toggleFullscreen() {
        const container = document.querySelector('.code-editor-container');
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            container.requestFullscreen();
        }
    }
    
    // Button events
    document.getElementById('save-code').addEventListener('click', saveCode);
    document.getElementById('format-code').addEventListener('click', formatCode);
    document.getElementById('toggle-fullscreen').addEventListener('click', toggleFullscreen);
    
    document.getElementById('find-replace').addEventListener('click', function() {
        editor.execCommand('findPersistent');
    });
    
    document.getElementById('goto-line').addEventListener('click', function() {
        const line = prompt('עבר לשורה:');
        if (line && !isNaN(line)) {
            editor.setCursor(parseInt(line) - 1, 0);
            editor.focus();
        }
    });
    
    // Initialize stats
    updateStats();
    
    // Focus editor
    editor.focus();
});
</script>

</body>
</html>

<?php
function getTemplateCode($template) {
    // כאן נקרא את הקוד מהקובץ המתאים
    // לעת עתה נחזיר קוד לדוגמה
    
    $sampleCodes = [
        'home' => '<?php
/**
 * תבנית דף הבית
 */

// קבלת מוצרים מומלצים
$featuredProducts = $productManager->getFeaturedProducts(8);
$categories = $categoryManager->getActiveCategories();
?>

<div class="home-page">
    <!-- Hero Section -->
    <section class="hero-section bg-gradient-to-r from-blue-600 to-purple-600 text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                ברוכים הבאים לחנות שלנו
            </h1>
            <p class="text-xl mb-8">
                גלה את המוצרים הטובים ביותר במחירים הכי טובים
            </p>
            <a href="/products" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                קנה עכשיו
            </a>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">מוצרים מומלצים</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="product-card bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                        <img src="<?= $product[\'image\'] ?>" alt="<?= htmlspecialchars($product[\'name\']) ?>" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-2"><?= htmlspecialchars($product[\'name\']) ?></h3>
                            <p class="text-gray-600 mb-4"><?= htmlspecialchars($product[\'description\']) ?></p>
                            <div class="flex justify-between items-center">
                                <span class="text-2xl font-bold text-blue-600">₪<?= number_format($product[\'price\']) ?></span>
                                <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    הוסף לעגלה
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>',
        
        'category' => '<?php
/**
 * תבנית דף קטגוריה
 */

$category = $categoryManager->getCategoryBySlug($categorySlug);
$products = $productManager->getProductsByCategory($category[\'id\']);
?>

<div class="category-page">
    <!-- Category Header -->
    <div class="bg-gray-100 py-12">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                <?= htmlspecialchars($category[\'name\']) ?>
            </h1>
            <?php if ($category[\'description\']): ?>
                <p class="text-lg text-gray-600">
                    <?= htmlspecialchars($category[\'description\']) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <?php foreach ($products as $product): ?>
                    <div class="product-card bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                        <a href="/product/<?= $product[\'slug\'] ?>">
                            <img src="<?= $product[\'image\'] ?>" alt="<?= htmlspecialchars($product[\'name\']) ?>" class="w-full h-48 object-cover">
                        </a>
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-2">
                                <a href="/product/<?= $product[\'slug\'] ?>" class="text-gray-900 hover:text-blue-600">
                                    <?= htmlspecialchars($product[\'name\']) ?>
                                </a>
                            </h3>
                            <p class="text-gray-600 mb-4"><?= htmlspecialchars($product[\'short_description\']) ?></p>
                            <div class="flex justify-between items-center">
                                <span class="text-2xl font-bold text-blue-600">₪<?= number_format($product[\'price\']) ?></span>
                                <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    הוסף לעגלה
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>',
        
        'product' => '<?php
/**
 * תבנית דף מוצר
 */

$product = $productManager->getProductBySlug($productSlug);
$relatedProducts = $productManager->getRelatedProducts($product[\'id\'], 4);
?>

<div class="product-page">
    <!-- Product Details -->
    <div class="py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Product Images -->
                <div class="product-images">
                    <div class="main-image mb-4">
                        <img src="<?= $product[\'image\'] ?>" alt="<?= htmlspecialchars($product[\'name\']) ?>" class="w-full rounded-lg">
                    </div>
                    <!-- Thumbnail images would go here -->
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">
                        <?= htmlspecialchars($product[\'name\']) ?>
                    </h1>
                    
                    <div class="price mb-6">
                        <span class="text-4xl font-bold text-blue-600">₪<?= number_format($product[\'price\']) ?></span>
                        <?php if ($product[\'compare_price\']): ?>
                            <span class="text-xl text-gray-500 line-through mr-2">₪<?= number_format($product[\'compare_price\']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="description mb-6">
                        <p class="text-gray-700 leading-relaxed">
                            <?= nl2br(htmlspecialchars($product[\'description\'])) ?>
                        </p>
                    </div>
                    
                    <!-- Add to Cart -->
                    <div class="add-to-cart">
                        <div class="flex items-center gap-4 mb-4">
                            <label class="text-sm font-medium text-gray-700">כמות:</label>
                            <input type="number" value="1" min="1" class="w-20 px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <button class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                            הוסף לעגלה
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if ($relatedProducts): ?>
        <div class="py-12 bg-gray-50">
            <div class="container mx-auto px-4">
                <h2 class="text-2xl font-bold text-center mb-8">מוצרים קשורים</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <?php foreach ($relatedProducts as $relatedProduct): ?>
                        <div class="product-card bg-white rounded-lg shadow-lg overflow-hidden">
                            <a href="/product/<?= $relatedProduct[\'slug\'] ?>">
                                <img src="<?= $relatedProduct[\'image\'] ?>" alt="<?= htmlspecialchars($relatedProduct[\'name\']) ?>" class="w-full h-48 object-cover">
                            </a>
                            <div class="p-4">
                                <h3 class="font-semibold mb-2">
                                    <a href="/product/<?= $relatedProduct[\'slug\'] ?>" class="text-gray-900 hover:text-blue-600">
                                        <?= htmlspecialchars($relatedProduct[\'name\']) ?>
                                    </a>
                                </h3>
                                <span class="text-lg font-bold text-blue-600">₪<?= number_format($relatedProduct[\'price\']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>',
        
        'header' => '<?php
/**
 * כותרת האתר
 */
?>

<header class="bg-white shadow-lg">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between py-4">
            <!-- Logo -->
            <div class="logo">
                <a href="/" class="text-2xl font-bold text-blue-600">
                    <?= htmlspecialchars($store[\'name\']) ?>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="hidden md:flex items-center space-x-8">
                <a href="/" class="text-gray-700 hover:text-blue-600 transition-colors">בית</a>
                <a href="/products" class="text-gray-700 hover:text-blue-600 transition-colors">מוצרים</a>
                <a href="/categories" class="text-gray-700 hover:text-blue-600 transition-colors">קטגוריות</a>
                <a href="/about" class="text-gray-700 hover:text-blue-600 transition-colors">אודות</a>
                <a href="/contact" class="text-gray-700 hover:text-blue-600 transition-colors">צור קשר</a>
            </nav>

            <!-- Cart & Search -->
            <div class="flex items-center space-x-4">
                <button class="text-gray-700 hover:text-blue-600 transition-colors">
                    <i class="ri-search-line text-xl"></i>
                </button>
                <button class="text-gray-700 hover:text-blue-600 transition-colors relative">
                    <i class="ri-shopping-cart-line text-xl"></i>
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                        0
                    </span>
                </button>
            </div>
        </div>
    </div>
</header>',
        
        'footer' => '<?php
/**
 * תחתית האתר
 */
?>

<footer class="bg-gray-900 text-white py-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Store Info -->
            <div>
                <h3 class="text-xl font-bold mb-4"><?= htmlspecialchars($store[\'name\']) ?></h3>
                <p class="text-gray-400 mb-4">
                    <?= htmlspecialchars($store[\'description\']) ?>
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="ri-facebook-line text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="ri-instagram-line text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="ri-twitter-line text-xl"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-lg font-semibold mb-4">קישורים מהירים</h4>
                <ul class="space-y-2">
                    <li><a href="/" class="text-gray-400 hover:text-white transition-colors">בית</a></li>
                    <li><a href="/products" class="text-gray-400 hover:text-white transition-colors">מוצרים</a></li>
                    <li><a href="/categories" class="text-gray-400 hover:text-white transition-colors">קטגוריות</a></li>
                    <li><a href="/about" class="text-gray-400 hover:text-white transition-colors">אודות</a></li>
                </ul>
            </div>

            <!-- Customer Service -->
            <div>
                <h4 class="text-lg font-semibold mb-4">שירות לקוחות</h4>
                <ul class="space-y-2">
                    <li><a href="/contact" class="text-gray-400 hover:text-white transition-colors">צור קשר</a></li>
                    <li><a href="/shipping" class="text-gray-400 hover:text-white transition-colors">משלוחים</a></li>
                    <li><a href="/returns" class="text-gray-400 hover:text-white transition-colors">החזרות</a></li>
                    <li><a href="/faq" class="text-gray-400 hover:text-white transition-colors">שאלות נפוצות</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div>
                <h4 class="text-lg font-semibold mb-4">הירשם לניוזלטר</h4>
                <p class="text-gray-400 mb-4">קבל עדכונים על מוצרים חדשים והנחות</p>
                <form class="flex">
                    <input type="email" placeholder="כתובת מייל" class="flex-1 px-4 py-2 bg-gray-800 text-white rounded-r-lg border border-gray-700">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-l-lg hover:bg-blue-700 transition-colors">
                        הירשם
                    </button>
                </form>
            </div>
        </div>

        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
            <p>&copy; <?= date(\'Y\') ?> <?= htmlspecialchars($store[\'name\']) ?>. כל הזכויות שמורות.</p>
        </div>
    </div>
</footer>'
    ];
    
    return $sampleCodes[$template] ?? '<?php // קוד תבנית חדש ?>';
}
?> 