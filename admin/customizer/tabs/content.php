<div class="space-y-6">
    <!-- Logo -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="font-medium text-gray-900 mb-4">לוגו</h3>
        <div class="space-y-3">
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                <i class="ri-image-line text-3xl text-gray-400 mb-2"></i>
                <p class="text-sm text-gray-600 mb-3">גרור תמונה או לחץ להעלאה</p>
                <input type="file" id="logo-upload" accept="image/*" class="hidden">
                <button onclick="document.getElementById('logo-upload').click()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                    בחר קובץ
                </button>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">רוחב לוגו</label>
                <input type="range" min="100" max="300" value="150" class="w-full" id="logo-width">
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>100px</span>
                    <span id="logo-width-value">150px</span>
                    <span>300px</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="font-medium text-gray-900 mb-4">סקציית גיבור</h3>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">כותרת ראשית</label>
                <input type="text" value="ברוכים הבאים לחנות שלנו" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="hero-title">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">תת כותרת</label>
                <textarea rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="hero-subtitle">גלה את המוצרים הטובים ביותר במחירים הכי טובים</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">טקסט כפתור</label>
                <input type="text" value="קנה עכשיו" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="hero-button-text">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">קישור כפתור</label>
                <input type="url" value="/products" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="hero-button-link">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">תמונת רקע</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                    <i class="ri-image-line text-2xl text-gray-400 mb-2"></i>
                    <p class="text-sm text-gray-600 mb-3">העלה תמונת רקע</p>
                    <input type="file" id="hero-bg-upload" accept="image/*" class="hidden">
                    <button onclick="document.getElementById('hero-bg-upload').click()" class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                        בחר תמונה
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="font-medium text-gray-900 mb-4">תפריט ניווט</h3>
        <div class="space-y-3">
            <div id="menu-items">
                <div class="flex items-center gap-2 mb-2">
                    <input type="text" value="בית" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="שם הקישור">
                    <input type="text" value="/" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="URL">
                    <button class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
                <div class="flex items-center gap-2 mb-2">
                    <input type="text" value="מוצרים" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="שם הקישור">
                    <input type="text" value="/products" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="URL">
                    <button class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
                <div class="flex items-center gap-2 mb-2">
                    <input type="text" value="אודות" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="שם הקישור">
                    <input type="text" value="/about" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="URL">
                    <button class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </div>
            <button id="add-menu-item" class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                <i class="ri-add-line ml-2"></i>
                הוסף פריט תפריט
            </button>
        </div>
    </div>

    <!-- Featured Products -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="font-medium text-gray-900 mb-4">מוצרים מומלצים</h3>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">כותרת סקציה</label>
                <input type="text" value="המוצרים שלנו" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="featured-title">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">תיאור</label>
                <textarea rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="featured-description">גלה את המוצרים הטובים ביותר שלנו</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">מספר מוצרים להצגה</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="featured-count">
                    <option value="4">4 מוצרים</option>
                    <option value="6">6 מוצרים</option>
                    <option value="8" selected>8 מוצרים</option>
                    <option value="12">12 מוצרים</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">סגנון תצוגה</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="featured-layout">
                    <option value="grid" selected>רשת</option>
                    <option value="carousel">קרוסלה</option>
                    <option value="list">רשימה</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="font-medium text-gray-900 mb-4">תחתית</h3>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">טקסט זכויות יוצרים</label>
                <input type="text" value="© 2024 החנות שלי. כל הזכויות שמורות." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="footer-copyright">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">קישורים נוספים</label>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <input type="text" value="מדיניות פרטיות" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="שם הקישור">
                        <input type="text" value="/privacy" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="URL">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" value="תנאי שימוש" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="שם הקישור">
                        <input type="text" value="/terms" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="URL">
                    </div>
                </div>
            </div>
            <div>
                <label class="flex items-center">
                    <input type="checkbox" class="ml-2" id="show-social-links" checked>
                    <span class="text-sm">הצג קישורים לרשתות חברתיות</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Social Media -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="font-medium text-gray-900 mb-4">רשתות חברתיות</h3>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">פייסבוק</label>
                <input type="url" placeholder="https://facebook.com/yourpage" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="social-facebook">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">אינסטגרם</label>
                <input type="url" placeholder="https://instagram.com/yourpage" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="social-instagram">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">טוויטר</label>
                <input type="url" placeholder="https://twitter.com/yourpage" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="social-twitter">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">יוטיוב</label>
                <input type="url" placeholder="https://youtube.com/yourchannel" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="social-youtube">
            </div>
        </div>
    </div>
</div> 