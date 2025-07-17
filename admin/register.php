<?php
/**
 * עמוד הרשמה למערכת QuickShop5
 */

require_once '../includes/auth.php';

$auth = new Authentication();
$message = '';
$messageType = '';

// אם כבר מחובר - העברה לדשבורד
if ($auth->isLoggedIn()) {
    header('Location: /admin/');
    exit;
}

// טיפול בהרשמה
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $storeName = trim($_POST['store_name'] ?? '');
    $storeSlug = trim($_POST['store_slug'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($storeName) || empty($storeSlug) || empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = 'נא למלא את כל השדות החובה';
        $messageType = 'error';
    } elseif ($password !== $confirm_password) {
        $message = 'הסיסמאות אינן תואמות';
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'הסיסמה חייבת להכיל לפחות 6 תווים';
        $messageType = 'error';
    } elseif (!preg_match('/^[a-z0-9\-]+$/', $storeSlug)) {
        $message = 'סלאג החנות יכול להכיל רק אותיות באנגלית קטנות, מספרים ומקפים';
        $messageType = 'error';
    } elseif (strlen($storeSlug) < 3) {
        $message = 'סלאג החנות חייב להכיל לפחות 3 תווים';
        $messageType = 'error';
    } else {
        // בדיקת זמינות הסלאג
        require_once '../includes/StoreResolver.php';
        if (!StoreResolver::isSlugAvailable($storeSlug)) {
            $message = 'סלאג החנות כבר תפוס, אנא בחר סלאג אחר';
            $messageType = 'error';
        } else {
            $result = $auth->register($email, $password, $firstName, $lastName, $phone);
            
            if ($result['success']) {
                // יצירת החנות לאחר יצירת המשתמש
                $storeResult = $auth->createDefaultStore($result['user_id'], $storeName, $storeSlug);
                
                if ($storeResult['success']) {
                    // התחברות אוטומטית אחרי הרשמה מוצלחת
                    session_start();
                    $_SESSION['user_id'] = $result['user_id'];
                    $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['is_logged_in'] = true;
                    
                    // הפניה לדשבורד עם הודעת הצלחה
                    header('Location: /admin/?welcome=1&store_url=' . urlencode($storeSlug . '.quick-shop.co.il'));
                    exit;
                } else {
                    $message = 'המשתמש נוצר אך נכשלה יצירת החנות: ' . $storeResult['message'];
                    $messageType = 'warning';
                }
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>הרשמה - QuickShop5</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛍️</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Hebrew:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { font-family: 'Noto Sans Hebrew', sans-serif; }
        .animate-spin {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    
    <!-- Header -->
    <div class="flex min-h-screen">
        <!-- Right Panel - Form -->
        <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-20 xl:px-24">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                
                <!-- Logo & Title -->
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-blue-600 mb-2">
                        <i class="ri-shopping-bag-3-line ml-2"></i>
                        QuickShop5
                    </h1>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">צור את החנות שלך</h2>
                    <p class="text-sm text-gray-600">
                        הצטרף לאלפי בעלי עסקים שבחרו ב-QuickShop5
                    </p>
                </div>

                <!-- Alert Message -->
                <?php if ($message): ?>
                    <div class="mt-6 rounded-md p-4 <?php echo $messageType === 'success' ? 'bg-green-50 border border-green-200' : ($messageType === 'warning' ? 'bg-yellow-50 border border-yellow-200' : 'bg-red-50 border border-red-200'); ?>">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="<?php echo $messageType === 'success' ? 'ri-checkbox-circle-line text-green-400' : ($messageType === 'warning' ? 'ri-alert-line text-yellow-400' : 'ri-error-warning-line text-red-400'); ?> text-xl"></i>
                            </div>
                            <div class="mr-3">
                                <p class="text-sm <?php echo $messageType === 'success' ? 'text-green-700' : ($messageType === 'warning' ? 'text-yellow-700' : 'text-red-700'); ?>">
                                    <?php echo htmlspecialchars($message); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Registration Form -->
                <form class="mt-8 space-y-6" method="POST">
                    <div class="space-y-4">
                        
                        <!-- Store Name & Slug -->
                        <div class="space-y-4">
                            <div>
                                <label for="store_name" class="block text-sm font-medium text-gray-700">שם החנות</label>
                                <div class="mt-1 relative">
                                    <input type="text" id="store_name" name="store_name" required
                                           class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="רוני בוטיק"
                                           value="<?php echo htmlspecialchars($_POST['store_name'] ?? ''); ?>"
                                           oninput="generateSlug()">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="ri-store-2-line text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label for="store_slug" class="block text-sm font-medium text-gray-700">כתובת האתר שלך</label>
                                <div class="mt-1">
                                    <div class="flex rounded-md shadow-sm">
                                           <span class="inline-flex items-center px-3 rounded-r-md border border-r-1 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                            .quick-shop.co.il
                                        </span>
                                        <input type="text" id="store_slug" name="store_slug" required
                                               class="flex-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-l-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="my-store"
                                               pattern="[a-z0-9\-]+"
                                               title="רק אותיות באנגלית (קטנות), מספרים ומקפים"
                                               value="<?php echo htmlspecialchars($_POST['store_slug'] ?? ''); ?>"
                                               oninput="validateSlug(this)"
                                               style="direction: ltr; text-align: left;">
                                     
                                    </div>

                                    <div id="slug-status" class="mt-1 text-sm hidden">
                                        <span id="slug-available" class="text-green-600 hidden">
                                            <i class="ri-checkbox-circle-line ml-1"></i>
                                            הכתובת זמינה!
                                        </span>
                                        <span id="slug-taken" class="text-red-600 hidden">
                                            <i class="ri-close-circle-line ml-1"></i>
                                            הכתובת תפוסה, נסה משהו אחר
                                        </span>
                                        <span id="slug-checking" class="text-blue-600 hidden">
                                            <i class="ri-loader-4-line animate-spin ml-1"></i>
                                            בודק זמינות...
                                        </span>
                                    </div>
                                    <div class="mt-3 p-4 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg border border-green-200">
                                        <div class="flex items-center mb-3">
                                            <i class="ri-lightbulb-line text-green-600 text-lg ml-2"></i>
                                            <span class="text-sm font-medium text-green-800">טיפים לכתובת מושלמת:</span>
                                        </div>
                                        <div class="space-y-2 mr-6">
                                            <div class="flex items-center text-sm text-green-700">
                                                <span class="w-2 h-2 bg-green-400 rounded-full ml-2"></span>
                                                השתמש באותיות באנגלית קטנות (a-z)
                                            </div>
                                            <div class="flex items-center text-sm text-green-700">
                                                <span class="w-2 h-2 bg-green-400 rounded-full ml-2"></span>
                                                מספרים (0-9) ומקפים (-) גם בסדר
                                            </div>
                                            <div class="flex items-center text-sm text-blue-700">
                                                <span class="w-2 h-2 bg-blue-400 rounded-full ml-2"></span>
                                                לדוגמה: my-store או shop123
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                        <p class="text-sm text-gray-700 mb-1">כתובת החנות שלך תהיה:</p>
                                        <div class="font-medium text-blue-600" id="store-url-preview" style="direction: ltr; text-align: left;">
                                            my-store.quick-shop.co.il
                                        </div>
                                    </div>
                                   
                                </div>
                            </div>
                        </div>
                        
                        <!-- Personal Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700">שם פרטי</label>
                                <input type="text" id="first_name" name="first_name" required autocomplete="given-name"
                                       class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="דני"
                                       value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700">שם משפחה</label>
                                <input type="text" id="last_name" name="last_name" required autocomplete="family-name"
                                       class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="כהן"
                                       value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">כתובת מייל</label>
                            <div class="mt-1 relative">
                                <input type="email" id="email" name="email" required autocomplete="email"
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="dani@example.com"
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="ri-mail-line text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">טלפון (אופציונלי)</label>
                            <div class="mt-1 relative">
                                <input type="tel" id="phone" name="phone" autocomplete="tel"
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="050-1234567"
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="ri-phone-line text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">סיסמה</label>
                            <div class="mt-1 relative">
                                                        <input type="password" id="password" name="password" required autocomplete="new-password"
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="לפחות 6 תווים">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="ri-lock-line text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">אישור סיסמה</label>
                            <div class="mt-1 relative">
                                                        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password"
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="הקלד שוב את הסיסמה">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="ri-lock-2-line text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="ri-rocket-line text-blue-500 group-hover:text-blue-400 ml-1"></i>
                            </span>
                            התחל את תקופת הניסיון בחינם
                        </button>
                    </div>

                    <!-- Login Link -->
                    <div class="text-center">
                        <p class="text-sm text-gray-600">
                            כבר יש לך חשבון?
                            <a href="/admin/login.php" class="font-medium text-blue-600 hover:text-blue-500">התחבר כאן</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Left Panel - Features -->
        <div class="hidden lg:block relative w-0 flex-1">
            <div class="absolute inset-0 h-full w-full bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center">
                <div class="text-center text-white p-8">
                    <h3 class="text-3xl font-bold mb-6">למה QuickShop5?</h3>
                    
                    <div class="space-y-6 text-right">
                        <div class="flex items-center">
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold">ניהול מוצרים מתקדם</h4>
                                <p class="text-blue-100">וריאציות, גלריות וכל מה שצריך</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-shopping-bag-3-line text-3xl text-blue-200"></i>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold">תקופת ניסיון 14 יום</h4>
                                <p class="text-blue-100">ללא מחויבות, ללא דמי הפעלה</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-calendar-check-line text-3xl text-blue-200"></i>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold">תמיכה בעברית</h4>
                                <p class="text-blue-100">ממשק מלא בעברית עם תמיכה מקומית</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-customer-service-2-line text-3xl text-blue-200"></i>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold">עיצוב מותאם</h4>
                                <p class="text-blue-100">חנות יפה ומותאמת לנייד</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-palette-line text-3xl text-blue-200"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // בדיקת תאימות סיסמאות
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function validatePasswords() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('הסיסמאות אינן תואמות');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
        
        password.addEventListener('change', validatePasswords);
        confirmPassword.addEventListener('keyup', validatePasswords);

        // טיפול בslug
        const storeNameInput = document.getElementById('store_name');
        const storeSlugInput = document.getElementById('store_slug');
        const storeUrlPreview = document.getElementById('store-url-preview');
        let slugTimeout;

        // יצירת slug רק מתווי אנגלית
        function generateSlug() {
            const storeName = storeNameInput.value;
            
            // ניקוי וקביעה שרק אותיות אנגלית, מספרים ורווחים יישארו
            let slug = storeName.toLowerCase()
                              .replace(/[^a-z0-9\s\-]/g, '')  // הסרת כל מה שלא אנגלית/מספרים/רווחים/מקפים
                              .replace(/\s+/g, '-')           // החלפת רווחים במקפים
                              .replace(/-+/g, '-')            // איחוד מקפים כפולים
                              .replace(/^-|-$/g, '');         // הסרת מקפים מההתחלה והסוף

            // עדכון השדה רק אם הוא ריק או אם המשתמש לא ערך אותו ידנית
            if (!storeSlugInput.dataset.userModified) {
                storeSlugInput.value = slug;
                updateUrlPreview(slug);
                if (slug && slug.length >= 3) {
                    checkSlugAvailability(slug);
                }
            }
        }

        // בדיקת תקינות slug
        function validateSlug(input) {
            input.dataset.userModified = 'true';
            let value = input.value.toLowerCase();
            
            // הסרת תווים לא חוקיים
            value = value.replace(/[^a-z0-9\-]/g, '');
            
            if (value !== input.value) {
                input.value = value;
            }

            updateUrlPreview(value);
            
            // איפוס timeout קודם
            clearTimeout(slugTimeout);
            
            // בדיקת זמינות עם עיכוב
            if (value.length >= 3) {
                slugTimeout = setTimeout(() => {
                    checkSlugAvailability(value);
                }, 500);
            } else {
                hideSlugStatus();
            }
        }

        // עדכון תצוגת URL
        function updateUrlPreview(slug) {
            if (slug) {
                storeUrlPreview.textContent = slug + '.quick-shop.co.il';
            } else {
                storeUrlPreview.textContent = 'my-store.quick-shop.co.il';
            }
        }

        // בדיקת זמינות slug
        function checkSlugAvailability(slug) {
            showSlugChecking();
            
            fetch('../api/check-slug.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ slug: slug })
            })
            .then(response => response.json())
            .then(data => {
                if (data.available) {
                    showSlugAvailable();
                } else {
                    showSlugTaken();
                }
            })
            .catch(() => {
                hideSlugStatus();
            });
        }

        // הצגת סטטוסי slug
        function showSlugChecking() {
            document.getElementById('slug-status').classList.remove('hidden');
            document.getElementById('slug-checking').classList.remove('hidden');
            document.getElementById('slug-available').classList.add('hidden');
            document.getElementById('slug-taken').classList.add('hidden');
        }

        function showSlugAvailable() {
            document.getElementById('slug-checking').classList.add('hidden');
            document.getElementById('slug-available').classList.remove('hidden');
            document.getElementById('slug-taken').classList.add('hidden');
        }

        function showSlugTaken() {
            document.getElementById('slug-checking').classList.add('hidden');
            document.getElementById('slug-available').classList.add('hidden');
            document.getElementById('slug-taken').classList.remove('hidden');
        }

        function hideSlugStatus() {
            document.getElementById('slug-status').classList.add('hidden');
        }

        // איפוס מצב עריכה ידנית כשמנקים את השדה
        storeSlugInput.addEventListener('input', function() {
            if (this.value === '') {
                delete this.dataset.userModified;
            }
        });

        // יצירת slug אוטומטי בהתחלה אם יש שם חנות
        if (storeNameInput.value) {
            generateSlug();
        }

        // יצירת slug אוטומטי כשמקלידים שם חנות
        storeNameInput.addEventListener('input', generateSlug);
    </script>
</body>
</html> 