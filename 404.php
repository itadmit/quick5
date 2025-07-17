<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>עמוד לא נמצא - 404 | QuickShop</title>
    
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
        <!-- 404 Icon -->
        <div class="mb-8">
            <div class="w-32 h-32 mx-auto bg-blue-100 rounded-full flex items-center justify-center">
                <i class="ri-error-warning-line text-6xl text-blue-600"></i>
            </div>
        </div>
        
        <!-- Error Message -->
        <div class="space-y-4">
            <h1 class="text-6xl font-bold text-gray-900">404</h1>
            <h2 class="text-2xl font-semibold text-gray-800">עמוד לא נמצא</h2>
            <p class="text-gray-600 leading-relaxed">
                מצטערים, העמוד שחיפשת לא קיים או הועבר למיקום אחר.
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
                <button onclick="history.back()" 
                        class="text-blue-600 hover:text-blue-700 font-medium">
                    חזור לעמוד הקודם
                </button>
            </div>
        </div>
        
        <!-- Search -->
        <div class="mt-8 border-t pt-8">
            <p class="text-gray-600 mb-4">אולי תמצא את מה שאתה מחפש כאן:</p>
            <form action="/search" method="GET" class="flex">
                <input type="text" 
                       name="q" 
                       placeholder="חפש מוצרים..."
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <button type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-l-lg hover:bg-blue-700 transition-colors">
                    <i class="ri-search-line"></i>
                </button>
            </form>
        </div>
        
        <!-- Contact Info -->
        <div class="mt-8 text-sm text-gray-500">
            אם הבעיה נמשכת, 
            <a href="/contact" class="text-blue-600 hover:text-blue-700">צור קשר</a>
            עם התמיכה שלנו
        </div>
    </div>
</body>
</html> 