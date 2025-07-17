<div class="p-6">
    <div class="flex items-center gap-3 mb-4">
        <button id="backButton" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition-colors">
            <i class="ri-arrow-right-line text-gray-600"></i>
        </button>
        <h3 id="settingsTitle" class="text-lg font-medium text-gray-900">הגדרות מוצרים מומלצים</h3>
    </div>
    
    <!-- Featured Products Settings Form -->
    <form id="featuredProductsForm" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">כותרת הסקשן</label>
            <input type="text" id="featuredTitle" name="title" 
                   value="המוצרים הכי מומלצים שלנו" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">תת כותרת</label>
            <textarea id="featuredSubtitle" name="subtitle" rows="2"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">גלה את המוצרים שהלקוחות שלנו הכי אוהבים</textarea>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">מספר מוצרים להצגה</label>
            <select id="featuredCount" name="productCount" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="4">4 מוצרים</option>
                <option value="8" selected>8 מוצרים</option>
                <option value="12">12 מוצרים</option>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">סגנון תצוגה</label>
            <select id="featuredLayout" name="layout" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="grid" selected>רשת</option>
                <option value="carousel">קרוסלה</option>
                <option value="list">רשימה</option>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">צבע רקע הסקשן</label>
            <input type="color" id="featuredBgColor" name="bgColor" 
                   value="#F9FAFB" 
                   class="w-full h-10 border border-gray-300 rounded-md">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">צבע כותרת</label>
            <input type="color" id="featuredTitleColor" name="titleColor" 
                   value="#111827" 
                   class="w-full h-10 border border-gray-300 rounded-md">
        </div>
        
        <div class="pt-4 border-t border-gray-200">
            <label class="flex items-center">
                <input type="checkbox" id="showPrices" name="showPrices" checked 
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="mr-2 text-sm text-gray-700">הצג מחירים</span>
            </label>
        </div>
        
        <div>
            <label class="flex items-center">
                <input type="checkbox" id="showRatings" name="showRatings" checked 
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="mr-2 text-sm text-gray-700">הצג דירוגים</span>
            </label>
        </div>
    </form>
</div> 