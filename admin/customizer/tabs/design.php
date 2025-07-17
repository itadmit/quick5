<div class="space-y-6">
    <!-- Colors -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="font-medium text-gray-900 mb-4">צבעים</h3>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">צבע ראשי</label>
                <div class="flex items-center gap-3">
                    <input type="color" value="#3B82F6" class="w-10 h-10 rounded border border-gray-300" id="primary-color">
                    <input type="text" value="#3B82F6" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" id="primary-color-text">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">צבע משני</label>
                <div class="flex items-center gap-3">
                    <input type="color" value="#10B981" class="w-10 h-10 rounded border border-gray-300" id="secondary-color">
                    <input type="text" value="#10B981" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" id="secondary-color-text">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">צבע רקע</label>
                <div class="flex items-center gap-3">
                    <input type="color" value="#FFFFFF" class="w-10 h-10 rounded border border-gray-300" id="background-color">
                    <input type="text" value="#FFFFFF" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" id="background-color-text">
                </div>
            </div>
        </div>
    </div>

    <!-- Typography -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="font-medium text-gray-900 mb-4">טיפוגרפיה</h3>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">גופן ראשי</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="primary-font">
                    <option value="Noto Sans Hebrew" selected>Noto Sans Hebrew</option>
                    <option value="Assistant">Assistant</option>
                    <option value="Heebo">Heebo</option>
                    <option value="Rubik">Rubik</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">גודל גופן בסיסי</label>
                <input type="range" min="12" max="20" value="16" class="w-full" id="base-font-size">
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>12px</span>
                    <span id="font-size-value">16px</span>
                    <span>20px</span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">משקל גופן</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="font-weight">
                    <option value="300">דק</option>
                    <option value="400" selected>רגיל</option>
                    <option value="500">בינוני</option>
                    <option value="600">בולט</option>
                    <option value="700">עבה</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="font-medium text-gray-900 mb-4">כותרת עליונה</h3>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">רקע כותרת</label>
                <div class="flex items-center gap-3">
                    <input type="color" value="#FFFFFF" class="w-10 h-10 rounded border border-gray-300" id="header-bg">
                    <input type="text" value="#FFFFFF" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" id="header-bg-text">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">גובה כותרת</label>
                <input type="range" min="60" max="120" value="80" class="w-full" id="header-height">
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>60px</span>
                    <span id="header-height-value">80px</span>
                    <span>120px</span>
                </div>
            </div>
            <div class="space-y-2">
                <label class="flex items-center">
                    <input type="checkbox" class="ml-2" id="show-logo" checked>
                    <span class="text-sm">הצג לוגו</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" class="ml-2" id="show-navigation" checked>
                    <span class="text-sm">הצג תפריט ניווט</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" class="ml-2" id="show-search" checked>
                    <span class="text-sm">הצג חיפוש</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" class="ml-2" id="show-cart" checked>
                    <span class="text-sm">הצג עגלת קניות</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Buttons -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="font-medium text-gray-900 mb-4">כפתורים</h3>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">סגנון כפתורים</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="button-style">
                    <option value="rounded-full">מעוגל</option>
                    <option value="rounded-none">מרובע</option>
                    <option value="rounded-lg" selected>מעוגל חלקית</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">גודל כפתורים</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="button-size">
                    <option value="small">קטן</option>
                    <option value="medium" selected>בינוני</option>
                    <option value="large">גדול</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">צבע כפתורים</label>
                <div class="flex items-center gap-3">
                    <input type="color" value="#3B82F6" class="w-10 h-10 rounded border border-gray-300" id="button-color">
                    <input type="text" value="#3B82F6" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" id="button-color-text">
                </div>
            </div>
        </div>
    </div>

    <!-- Layout -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="font-medium text-gray-900 mb-4">פריסה</h3>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">רוחב מקסימלי</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="max-width">
                    <option value="1200px">1200px</option>
                    <option value="1400px" selected>1400px</option>
                    <option value="1600px">1600px</option>
                    <option value="100%">ללא הגבלה</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">רווח פנימי</label>
                <input type="range" min="0" max="40" value="20" class="w-full" id="container-padding">
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>0px</span>
                    <span id="padding-value">20px</span>
                    <span>40px</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Animation -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="font-medium text-gray-900 mb-4">אנימציות</h3>
        <div class="space-y-3">
            <div>
                <label class="flex items-center">
                    <input type="checkbox" class="ml-2" id="enable-animations" checked>
                    <span class="text-sm">הפעל אנימציות</span>
                </label>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">מהירות אנימציה</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" id="animation-speed">
                    <option value="fast">מהיר</option>
                    <option value="medium" selected>בינוני</option>
                    <option value="slow">איטי</option>
                </select>
            </div>
        </div>
    </div>
</div> 