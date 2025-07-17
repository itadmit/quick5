<?php
/**
 * עמוד התחברות למערכת QuickShop5
 */

require_once '../includes/auth.php';

$auth = new Authentication();
$message = $_GET['message'] ?? '';
$messageType = $message ? 'success' : '';

// אם כבר מחובר - העברה לדשבורד
if ($auth->isLoggedIn()) {
    header('Location: /admin/');
    exit;
}

// טיפול בהתחברות
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $message = 'נא למלא את כל השדות';
        $messageType = 'error';
    } else {
        $result = $auth->login($email, $password);
        
        if ($result['success']) {
            header('Location: /admin/');
            exit;
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>התחברות - QuickShop5</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛍️</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Hebrew:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { font-family: 'Noto Sans Hebrew', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            
            <!-- Header -->
            <div class="text-center">
                <h1 class="text-4xl font-bold text-blue-600 mb-2">
                    <i class="ri-shopping-bag-3-line ml-2"></i>
                    QuickShop5
                </h1>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">ברוך הבא!</h2>
                <p class="text-gray-600">התחבר לחשבון שלך כדי לנהל את החנות</p>
            </div>

            <!-- Alert Message -->
            <?php if ($message): ?>
                <div class="rounded-md p-4 <?php echo $messageType === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?>">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="<?php echo $messageType === 'success' ? 'ri-checkbox-circle-line text-green-400' : 'ri-error-warning-line text-red-400'; ?> text-xl"></i>
                        </div>
                        <div class="mr-3">
                            <p class="text-sm <?php echo $messageType === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                                <?php echo htmlspecialchars($message); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form class="mt-8 space-y-6 bg-white p-8 rounded-lg shadow-md" method="POST">
                <div class="space-y-4">
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">כתובת מייל</label>
                        <div class="relative">
                            <input type="email" id="email" name="email" required autocomplete="email"
                                   class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="your@email.com"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="ri-mail-line text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">סיסמה</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required autocomplete="current-password"
                                   class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="הסיסמה שלך">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="ri-lock-line text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember_me" name="remember_me" type="checkbox" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="remember_me" class="mr-2 block text-sm text-gray-900">זכור אותי</label>
                        </div>
                        <div class="text-sm">
                            <a href="#" class="font-medium text-blue-600 hover:text-blue-500">שכחת סיסמה?</a>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="ri-login-circle-line text-blue-500 group-hover:text-blue-400 ml-1"></i>
                        </span>
                        התחבר למערכת
                    </button>
                </div>

                <!-- Register Link -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        עדיין אין לך חשבון?
                        <a href="/admin/register.php" class="font-medium text-blue-600 hover:text-blue-500">הירשם כאן</a>
                    </p>
                </div>
            </form>

            <!-- Features Preview -->
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">למה QuickShop5?</h3>
                <div class="grid grid-cols-2 gap-4 text-center">
                    <div>
                        <i class="ri-shield-check-line text-2xl text-green-600 mb-2"></i>
                        <p class="text-xs text-gray-600">בטוח ומוגן</p>
                    </div>
                    <div>
                        <i class="ri-speed-line text-2xl text-blue-600 mb-2"></i>
                        <p class="text-xs text-gray-600">מהיר וקל</p>
                    </div>
                    <div>
                        <i class="ri-customer-service-2-line text-2xl text-purple-600 mb-2"></i>
                        <p class="text-xs text-gray-600">תמיכה 24/7</p>
                    </div>
                    <div>
                        <i class="ri-smartphone-line text-2xl text-orange-600 mb-2"></i>
                        <p class="text-xs text-gray-600">מותאם לנייד</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Focus על שדה המייל בטעינה
        document.getElementById('email').focus();
    </script>
</body>
</html> 