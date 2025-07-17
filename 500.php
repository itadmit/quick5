<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>שגיאת שרת - 500 | QuickShop</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Hebrew:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'hebrew': ['Noto Sans Hebrew', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        body { font-family: 'Noto Sans Hebrew', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 font-hebrew min-h-screen flex items-center justify-center">
    <div class="max-w-md mx-auto text-center px-4">
        <!-- 500 Icon -->
        <div class="mb-8">
            <div class="w-32 h-32 mx-auto bg-red-100 rounded-full flex items-center justify-center">
                <i class="ri-tools-line text-6xl text-red-600"></i>
            </div>
        </div>
        
        <!-- Error Message -->
        <div class="space-y-4">
            <h1 class="text-6xl font-bold text-gray-900">500</h1>
            <h2 class="text-2xl font-semibold text-gray-800">שגיאת שרת</h2>
            <p class="text-gray-600 leading-relaxed">
                מצטערים, התרחשה שגיאה בשרת. אנחנו עובדים על פתרון הבעיה.
            </p>
        </div>
        
        <!-- Actions -->
        <div class="mt-8 space-y-4">
            <a href="/" 
               class="inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                <i class="ri-home-line"></i>
                חזרה לעמוד הבית
            </a>
            
            <div class="text-sm text-gray-500">
                או
                <button onclick="window.location.reload()" 
                        class="text-blue-600 hover:text-blue-700 font-medium">
                    נסה שוב
                </button>
            </div>
        </div>
        
        <!-- Support Info -->
        <div class="mt-8 border-t pt-8">
            <p class="text-gray-600 mb-4">צריך עזרה?</p>
            <div class="space-y-2 text-sm">
                <div class="flex items-center justify-center gap-2 text-gray-600">
                    <i class="ri-mail-line"></i>
                    <a href="mailto:support@quickshop.co.il" class="text-blue-600 hover:text-blue-700">
                        support@quickshop.co.il
                    </a>
                </div>
                <div class="flex items-center justify-center gap-2 text-gray-600">
                    <i class="ri-phone-line"></i>
                    <span>03-123-4567</span>
                </div>
            </div>
        </div>
        
        <!-- Status -->
        <div class="mt-8 text-xs text-gray-400">
            קוד שגיאה: 500 | <?= date('H:i:s d/m/Y') ?>
        </div>
    </div>
</body>
</html> 