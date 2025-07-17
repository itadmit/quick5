<?php
session_start();
require_once '../../includes/auth.php';
require_once '../../config/database.php';

// בדיקת הרשאות מנהל
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$db = Database::getInstance();
$pdo = $db->getConnection();

// קבלת מידע על החנות
$stmt = $pdo->prepare("SELECT * FROM stores WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$store = $stmt->fetch();

if (!$store) {
    die('חנות לא נמצאה');
}

$store_id = $store['id'];
$page_title = 'ייבוא מוצרים מ-CSV';
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - QuickShop5</title>
    <link href="../assets/css/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Hebrew:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Noto Sans Hebrew', sans-serif; }
        .progress-bar {
            background: linear-gradient(-45deg, #ee7724, #d8363a, #dd3675, #b44593);
            background-size: 400% 400%;
            animation: gradient 2s ease infinite;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .upload-area {
            border: 2px dashed #cbd5e0;
            transition: all 0.3s ease;
        }
        .upload-area.drag-over {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include '../templates/header.php'; ?>
    
    <div class="flex">
        <?php include '../templates/sidebar.php'; ?>
        
        <main class="flex-1 p-6">
            <div class="max-w-6xl mx-auto">
                <!-- כותרת עמוד -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="ri-upload-cloud-2-line text-3xl text-blue-600 ml-3"></i>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900"><?= $page_title ?></h1>
                                <p class="text-gray-600">העלה קובץ CSV וייבא מוצרים לחנות שלך</p>
                            </div>
                        </div>
                        <a href="../products/" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors">
                            <i class="ri-arrow-right-line ml-2"></i>
                            חזרה למוצרים
                        </a>
                    </div>
                </div>

                <!-- הוראות וטיפים -->
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h3 class="font-semibold text-blue-900 mb-3 flex items-center">
                            <i class="ri-information-line ml-2"></i>
                            מידע חשוב לייבוא
                        </h3>
                        <ul class="text-blue-800 space-y-2 text-sm">
                            <li>• הקובץ חייב להיות בפורמט CSV</li>
                            <li>• תומך במוצרים עם וריאציות (מגנטו)</li>
                            <li>• תמונות יורדו אוטומטית מ-URL חיצוני</li>
                            <li>• ניתן לעבד עד 1000 מוצרים בפעם אחת</li>
                        </ul>
                    </div>
                    
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                        <h3 class="font-semibold text-green-900 mb-3 flex items-center">
                            <i class="ri-shield-check-line ml-2"></i>
                            הגדרות ייבוא
                        </h3>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" id="skip_existing" class="ml-2" checked>
                                <span class="text-sm text-green-800">דלג על מוצרים קיימים (לפי SKU)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" id="download_images" class="ml-2" checked>
                                <span class="text-sm text-green-800">הורד תמונות אוטומטית</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" id="create_categories" class="ml-2" checked>
                                <span class="text-sm text-green-800">צור קטגוריות חדשות אם נדרש</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- אזור העלאת קובץ -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="upload-area rounded-lg p-8 text-center" 
                         ondrop="handleDrop(event)" 
                         ondragover="handleDragOver(event)" 
                         ondragenter="handleDragEnter(event)" 
                         ondragleave="handleDragLeave(event)">
                        
                        <i class="ri-upload-cloud-2-line text-6xl text-gray-400 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">גרור קובץ CSV לכאן או לחץ לבחירה</h3>
                        <p class="text-gray-600 mb-4">קבצים נתמכים: .csv (עד 10MB)</p>
                        
                        <input type="file" id="csv_file" accept=".csv" class="hidden" onchange="handleFileSelect(event)">
                        <button onclick="document.getElementById('csv_file').click()" 
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="ri-folder-open-line ml-2"></i>
                            בחר קובץ CSV
                        </button>
                    </div>

                    <!-- מידע על הקובץ הנבחר -->
                    <div id="file_info" class="hidden mt-4 p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="ri-file-text-line text-2xl text-green-600 ml-3"></i>
                                <div>
                                    <div id="file_name" class="font-semibold text-gray-900"></div>
                                    <div id="file_size" class="text-sm text-gray-600"></div>
                                </div>
                            </div>
                            <button onclick="clearFile()" class="text-red-600 hover:text-red-700">
                                <i class="ri-close-line text-xl"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- הגדרות תמונות -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="ri-image-line ml-2"></i>
                        הגדרות תמונות
                    </h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">דומיין התמונות</label>
                            <input type="text" id="image_domain" value="https://www.studiopasha.co.il/media/catalog/product" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="https://example.com/media/catalog/product">
                            <p class="text-xs text-gray-500 mt-1">הדומיין שיתווסף לנתיבי התמונות</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">איכות תמונה</label>
                            <select id="image_quality" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="original">מקורי (איכות מלאה)</option>
                                <option value="high" selected>גבוהה (80%)</option>
                                <option value="medium">בינונית (60%)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- כפתור ייבוא -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900">מוכן לייבוא?</h3>
                            <p class="text-gray-600 text-sm">לחץ על הכפתור כדי להתחיל בתהליך הייבוא</p>
                        </div>
                        <button id="import_btn" onclick="startImport()" disabled 
                                class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <i class="ri-download-line ml-2"></i>
                            התחל ייבוא
                        </button>
                    </div>
                </div>

                <!-- אזור התקדמות -->
                <div id="progress_area" class="hidden mt-6 bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">מתקדמות ייבוא</h3>
                    
                    <!-- פס התקדמות -->
                    <div class="mb-4">
                        <div class="flex justify-between text-sm text-gray-600 mb-2">
                            <span id="progress_text">מתחיל...</span>
                            <span id="progress_percent">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progress_bar" class="progress-bar h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- סטטיסטיקות -->
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div class="text-center p-3 bg-blue-50 rounded">
                            <div id="total_products" class="text-2xl font-bold text-blue-600">0</div>
                            <div class="text-sm text-gray-600">סה"כ מוצרים</div>
                        </div>
                        <div class="text-center p-3 bg-green-50 rounded">
                            <div id="imported_products" class="text-2xl font-bold text-green-600">0</div>
                            <div class="text-sm text-gray-600">יובאו בהצלחה</div>
                        </div>
                        <div class="text-center p-3 bg-red-50 rounded">
                            <div id="failed_products" class="text-2xl font-bold text-red-600">0</div>
                            <div class="text-sm text-gray-600">נכשלו</div>
                        </div>
                    </div>

                    <!-- לוג שגיאות -->
                    <div id="error_log" class="hidden">
                        <h4 class="font-semibold text-red-900 mb-2">שגיאות:</h4>
                        <div id="error_list" class="bg-red-50 border border-red-200 rounded p-3 max-h-32 overflow-y-auto text-sm"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let selectedFile = null;
        let importInProgress = false;

        // טיפול בגרירה ושחרור
        function handleDragOver(e) {
            e.preventDefault();
            e.currentTarget.classList.add('drag-over');
        }

        function handleDragEnter(e) {
            e.preventDefault();
        }

        function handleDragLeave(e) {
            e.currentTarget.classList.remove('drag-over');
        }

        function handleDrop(e) {
            e.preventDefault();
            e.currentTarget.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFile(files[0]);
            }
        }

        // טיפול בבחירת קובץ
        function handleFileSelect(e) {
            const file = e.target.files[0];
            if (file) {
                handleFile(file);
            }
        }

        function handleFile(file) {
            // בדיקת סוג קובץ
            if (!file.name.toLowerCase().endsWith('.csv')) {
                alert('אנא בחר קובץ CSV בלבד');
                return;
            }

            // בדיקת גודל קובץ (10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert('הקובץ גדול מדי. אנא בחר קובץ עד 10MB');
                return;
            }

            selectedFile = file;
            
            // הצגת מידע על הקובץ
            document.getElementById('file_info').classList.remove('hidden');
            document.getElementById('file_name').textContent = file.name;
            document.getElementById('file_size').textContent = formatFileSize(file.size);
            document.getElementById('import_btn').disabled = false;
        }

        function clearFile() {
            selectedFile = null;
            document.getElementById('file_info').classList.add('hidden');
            document.getElementById('csv_file').value = '';
            document.getElementById('import_btn').disabled = true;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // התחלת ייבוא
        function startImport() {
            if (!selectedFile || importInProgress) return;

            importInProgress = true;
            document.getElementById('import_btn').disabled = true;
            document.getElementById('progress_area').classList.remove('hidden');
            
            // איפוס סטטיסטיקות
            updateProgress(0, 'מתחיל ייבוא...');
            document.getElementById('total_products').textContent = '0';
            document.getElementById('imported_products').textContent = '0';
            document.getElementById('failed_products').textContent = '0';
            document.getElementById('error_log').classList.add('hidden');

            // יצירת FormData
            const formData = new FormData();
            formData.append('csv_file', selectedFile);
            formData.append('store_id', '<?= $store_id ?>');
            formData.append('skip_existing', document.getElementById('skip_existing').checked);
            formData.append('download_images', document.getElementById('download_images').checked);
            formData.append('create_categories', document.getElementById('create_categories').checked);
            formData.append('image_domain', document.getElementById('image_domain').value);
            formData.append('image_quality', document.getElementById('image_quality').value);

            // שליחת בקשה לשרת
            fetch('../api/import-csv.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    processImport(data.import_id);
                } else {
                    showError('שגיאה בהתחלת הייבוא: ' + data.message);
                    importInProgress = false;
                    document.getElementById('import_btn').disabled = false;
                }
            })
            .catch(error => {
                showError('שגיאת רשת: ' + error.message);
                importInProgress = false;
                document.getElementById('import_btn').disabled = false;
            });
        }

        // עיבוד ייבוא
        function processImport(importId) {
            const interval = setInterval(() => {
                fetch(`../api/import-status.php?import_id=${importId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateProgress(data.progress, data.status);
                        document.getElementById('total_products').textContent = data.total || '0';
                        document.getElementById('imported_products').textContent = data.imported || '0';
                        document.getElementById('failed_products').textContent = data.failed || '0';

                        if (data.errors && data.errors.length > 0) {
                            showErrors(data.errors);
                        }

                        if (data.completed) {
                            clearInterval(interval);
                            importInProgress = false;
                            document.getElementById('import_btn').disabled = false;
                            updateProgress(100, 'ייבוא הושלם בהצלחה!');
                            
                            // הצגת הודעת הצלחה
                            setTimeout(() => {
                                if (confirm('הייבוא הושלם! האם תרצה לעבור לעמוד המוצרים?')) {
                                    window.location.href = '../products/';
                                }
                            }, 2000);
                        }
                    }
                })
                .catch(error => {
                    console.error('שגיאה בבדיקת סטטוס:', error);
                });
            }, 1000);
        }

        function updateProgress(percent, text) {
            document.getElementById('progress_bar').style.width = percent + '%';
            document.getElementById('progress_percent').textContent = Math.round(percent) + '%';
            document.getElementById('progress_text').textContent = text;
        }

        function showError(message) {
            alert(message);
        }

        function showErrors(errors) {
            document.getElementById('error_log').classList.remove('hidden');
            const errorList = document.getElementById('error_list');
            errorList.innerHTML = errors.map(error => `<div class="mb-1">${error}</div>`).join('');
        }
    </script>
</body>
</html> 