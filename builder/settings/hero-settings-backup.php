<div class="p-6 h-full overflow-y-auto">
    <div class="flex items-center gap-3 mb-6">
        <button id="backButton" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition-colors">
            <i class="ri-arrow-right-line text-gray-600"></i>
        </button>
        <h3 id="settingsTitle" class="text-lg font-medium text-gray-900">הגדרות Hero</h3>
    </div>
    
    <!-- Hero Settings Form -->
    <form id="heroForm" class="space-y-6">
        
        <!-- Layout Section -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-3">
                <i class="ri-layout-line text-blue-600"></i>
                <h4 class="font-medium text-gray-900">פריסה</h4>
            </div>
            
            <div class="space-y-4">
                <!-- Width -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">רוחב</label>
                    <select id="heroWidth" name="width" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="container">קונטיינר</option>
                        <option value="full" selected>רוחב מלא</option>
                        <option value="custom">מותאם אישית</option>
                    </select>
                </div>
                
                <!-- Content Position -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">מיקום תוכן</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors" data-position="top-right">
                            <i class="ri-align-top text-lg"></i>
                            <div class="text-xs mt-1">ימין למעלה</div>
                        </button>
                        <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors" data-position="top-center">
                            <i class="ri-align-top text-lg"></i>
                            <div class="text-xs mt-1">מרכז למעלה</div>
                        </button>
                        <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors" data-position="top-left">
                            <i class="ri-align-top text-lg"></i>
                            <div class="text-xs mt-1">שמאל למעלה</div>
                        </button>
                        <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors" data-position="center-right">
                            <i class="ri-align-center text-lg"></i>
                            <div class="text-xs mt-1">ימין מרכז</div>
                        </button>
                        <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors bg-blue-50 border-blue-300" data-position="center-center">
                            <i class="ri-align-center text-lg"></i>
                            <div class="text-xs mt-1">מרכז מרכז</div>
                        </button>
                        <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors" data-position="center-left">
                            <i class="ri-align-center text-lg"></i>
                            <div class="text-xs mt-1">שמאל מרכז</div>
                        </button>
                        <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors" data-position="bottom-right">
                            <i class="ri-align-bottom text-lg"></i>
                            <div class="text-xs mt-1">ימין למטה</div>
                        </button>
                        <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors" data-position="bottom-center">
                            <i class="ri-align-bottom text-lg"></i>
                            <div class="text-xs mt-1">מרכז למטה</div>
                        </button>
                        <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors" data-position="bottom-left">
                            <i class="ri-align-bottom text-lg"></i>
                            <div class="text-xs mt-1">שמאל למטה</div>
                        </button>
                    </div>
                    <input type="hidden" id="heroContentPosition" name="contentPosition" value="center-center">
                </div>
            </div>
        </div>

        <!-- Typography Section -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-3">
                <i class="ri-text text-green-600"></i>
                <h4 class="font-medium text-gray-900">טיפוגרפיה</h4>
            </div>
            
            <div class="space-y-4">
                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">כותרת</label>
                    <input type="text" id="heroTitle" name="title" 
                           value="ברוכים הבאים לחנות שלנו" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- Subtitle -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">תת כותרת</label>
                    <textarea id="heroSubtitle" name="subtitle" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">גלה את המוצרים הטובים ביותר במחירים הכי טובים</textarea>
                </div>
                
                <!-- Button Text -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">טקסט כפתור</label>
                    <input type="text" id="heroButtonText" name="buttonText" 
                           value="קנה עכשיו" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- Button Link -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">קישור כפתור</label>
                    <input type="text" id="heroButtonLink" name="buttonLink" 
                           value="/products" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Background Section -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-3">
                <i class="ri-image-fill text-purple-600"></i>
                <h4 class="font-medium text-gray-900">רקע</h4>
            </div>
            
            <div class="space-y-4">
                <!-- Background Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">סוג רקע</label>
                    <div class="grid grid-cols-4 gap-2">
                        <button type="button" class="bg-type-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors bg-blue-50 border-blue-300" data-bg-type="color">
                            <i class="ri-palette-line text-lg"></i>
                            <div class="text-xs mt-1">צבע</div>
                        </button>
                        <button type="button" class="bg-type-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors" data-bg-type="gradient">
                            <i class="ri-contrast-2-line text-lg"></i>
                            <div class="text-xs mt-1">גרדיאנט</div>
                        </button>
                        <button type="button" class="bg-type-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors" data-bg-type="image">
                            <i class="ri-image-line text-lg"></i>
                            <div class="text-xs mt-1">תמונה</div>
                        </button>
                        <button type="button" class="bg-type-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors" data-bg-type="video">
                            <i class="ri-video-line text-lg"></i>
                            <div class="text-xs mt-1">סרטון</div>
                        </button>
                    </div>
                    <input type="hidden" id="heroBgType" name="bgType" value="color">
                </div>
                
                <!-- Background Color -->
                <div id="bgColorSection">
                    <label class="block text-sm font-medium text-gray-700 mb-1">צבע רקע</label>
                    <input type="color" id="heroBgColor" name="bgColor" 
                           value="#3B82F6" 
                           class="w-full h-10 border border-gray-300 rounded-md">
                </div>
                
                <!-- Background Gradient -->
                <div id="bgGradientSection" class="hidden space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">צבע 1</label>
                            <input type="color" id="heroBgGradient1" name="bgGradient1" 
                                   value="#3B82F6" 
                                   class="w-full h-10 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">צבע 2</label>
                            <input type="color" id="heroBgGradient2" name="bgGradient2" 
                                   value="#1E40AF" 
                                   class="w-full h-10 border border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">כיוון</label>
                        <select id="heroBgGradientDirection" name="bgGradientDirection" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="to-r">שמאל לימין</option>
                            <option value="to-l">ימין לשמאל</option>
                            <option value="to-t">מטה למעלה</option>
                            <option value="to-b" selected>מעלה למטה</option>
                            <option value="to-br">אלכסון למטה-ימין</option>
                            <option value="to-bl">אלכסון למטה-שמאל</option>
                        </select>
                    </div>
                </div>
                
                <!-- Background Image -->
                <div id="bgImageSection" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">כתובת תמונה</label>
                    <input type="url" id="heroBgImage" name="bgImage" 
                           placeholder="https://example.com/image.jpg" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- Background Video -->
                <div id="bgVideoSection" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">כתובת סרטון (MP4)</label>
                    <input type="url" id="heroBgVideo" name="bgVideo" 
                           placeholder="https://example.com/video.mp4" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Colors Section -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-3">
                <i class="ri-palette-fill text-orange-600"></i>
                <h4 class="font-medium text-gray-900">צבעים</h4>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <!-- Title Color -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">צבע כותרת</label>
                    <input type="color" id="heroTitleColor" name="titleColor" 
                           value="#FFFFFF" 
                           class="w-full h-10 border border-gray-300 rounded-md">
                </div>
                
                <!-- Subtitle Color -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">צבע תת כותרת</label>
                    <input type="color" id="heroSubtitleColor" name="subtitleColor" 
                           value="#E5E7EB" 
                           class="w-full h-10 border border-gray-300 rounded-md">
                </div>
                
                <!-- Button Background Color -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">רקע כפתור</label>
                    <input type="color" id="heroButtonBgColor" name="buttonBgColor" 
                           value="#F59E0B" 
                           class="w-full h-10 border border-gray-300 rounded-md">
                </div>
                
                <!-- Button Text Color -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">טקסט כפתור</label>
                    <input type="color" id="heroButtonTextColor" name="buttonTextColor" 
                           value="#FFFFFF" 
                           class="w-full h-10 border border-gray-300 rounded-md">
                </div>
            </div>
        </div>

        <!-- Spacing Section -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-3">
                <i class="ri-space text-red-600"></i>
                <h4 class="font-medium text-gray-900">מרווחים</h4>
            </div>
            
            <div class="space-y-4">
                <!-- Padding -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">פדינג (px)</label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <input type="number" id="heroPaddingTop" name="paddingTop" 
                                   value="80" placeholder="למעלה" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <input type="number" id="heroPaddingBottom" name="paddingBottom" 
                                   value="80" placeholder="למטה" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <input type="number" id="heroPaddingRight" name="paddingRight" 
                                   value="20" placeholder="ימין" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <input type="number" id="heroPaddingLeft" name="paddingLeft" 
                                   value="20" placeholder="שמאל" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
                
                <!-- Margin -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">מרגין (px)</label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <input type="number" id="heroMarginTop" name="marginTop" 
                                   value="0" placeholder="למעלה" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <input type="number" id="heroMarginBottom" name="marginBottom" 
                                   value="0" placeholder="למטה" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <input type="number" id="heroMarginRight" name="marginRight" 
                                   value="0" placeholder="ימין" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <input type="number" id="heroMarginLeft" name="marginLeft" 
                                   value="0" placeholder="שמאל" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Section -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-3">
                <i class="ri-code-line text-indigo-600"></i>
                <h4 class="font-medium text-gray-900">התאמה אישית</h4>
            </div>
            
            <div class="space-y-4">
                <!-- Custom Class -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">מחלקה מותאמת (CSS Class)</label>
                    <input type="text" id="heroCustomClass" name="customClass" 
                           placeholder="my-custom-class" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- Custom ID -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">מזהה מותאם (ID)</label>
                    <input type="text" id="heroCustomId" name="customId" 
                           placeholder="my-custom-id" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
        
    </form>
</div> 