<div class="p-6">
    <h2 class="text-lg font-medium text-gray-900 mb-4">סקשנים</h2>
    
    <!-- Sections List -->
    <div id="sectionsList" class="space-y-3">
        <!-- Hero Section -->
        <div class="section-item border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors relative" data-section="hero">
            <!-- Main Info Row -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="ri-image-line text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">Hero</h3>
                        <p class="text-sm text-gray-500">סקשן פתיחה ראשי</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <!-- Toggle Actions Button -->
                    <button class="toggle-actions-btn w-6 h-6 bg-gray-50 hover:bg-gray-100 rounded flex items-center justify-center transition-all text-xs" 
                            data-section="hero" title="פעולות">
                        <i class="ri-arrow-down-s-line text-gray-500 transition-transform"></i>
                    </button>
                    
                    <!-- Settings Button -->
                    <button class="settings-btn w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition-colors" 
                            data-section="hero" title="הגדרות">
                        <i class="ri-settings-3-line text-gray-600"></i>
                    </button>
                </div>
            </div>
            
            <!-- Action Buttons Row (Collapsible) -->
            <div class="section-actions max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                <div class="flex items-center justify-center gap-2 pt-3 mt-3 border-t border-gray-100">
                    <button class="action-btn move-up-btn w-8 h-7 bg-gray-50 hover:bg-blue-100 rounded flex items-center justify-center transition-colors text-xs" 
                            data-section="hero" data-action="move-up" title="הזז למעלה">
                        <i class="ri-arrow-up-line text-gray-600"></i>
                    </button>
                    <button class="action-btn move-down-btn w-8 h-7 bg-gray-50 hover:bg-blue-100 rounded flex items-center justify-center transition-colors text-xs" 
                            data-section="hero" data-action="move-down" title="הזז למטה">
                        <i class="ri-arrow-down-line text-gray-600"></i>
                    </button>
                    <button class="action-btn duplicate-btn w-8 h-7 bg-gray-50 hover:bg-green-100 rounded flex items-center justify-center transition-colors text-xs" 
                            data-section="hero" data-action="duplicate" title="שכפל">
                        <i class="ri-file-copy-line text-gray-600"></i>
                    </button>
                    <button class="action-btn hide-btn w-8 h-7 bg-gray-50 hover:bg-yellow-100 rounded flex items-center justify-center transition-colors text-xs" 
                            data-section="hero" data-action="hide" title="הסתר">
                        <i class="ri-eye-off-line text-gray-600"></i>
                    </button>
                    <button class="action-btn delete-btn w-8 h-7 bg-gray-50 hover:bg-red-100 rounded flex items-center justify-center transition-colors text-xs" 
                            data-section="hero" data-action="delete" title="מחק">
                        <i class="ri-delete-bin-line text-red-600"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Featured Products Section (דוגמה לעתיד) -->
        <div class="section-item border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors relative" data-section="featured-products">
            <!-- Main Info Row -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="ri-star-line text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">מוצרים מומלצים</h3>
                        <p class="text-sm text-gray-500">הצגת מוצרים נבחרים</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <!-- Toggle Actions Button -->
                    <button class="toggle-actions-btn w-6 h-6 bg-gray-50 hover:bg-gray-100 rounded flex items-center justify-center transition-all text-xs" 
                            data-section="featured-products" title="פעולות">
                        <i class="ri-arrow-down-s-line text-gray-500 transition-transform"></i>
                    </button>
                    
                    <!-- Settings Button -->
                    <button class="settings-btn w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition-colors" 
                            data-section="featured-products" title="הגדרות">
                        <i class="ri-settings-3-line text-gray-600"></i>
                    </button>
                </div>
            </div>
            
            <!-- Action Buttons Row (Collapsible) -->
            <div class="section-actions max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                <div class="flex items-center justify-center gap-2 pt-3 mt-3 border-t border-gray-100">
                    <button class="action-btn move-up-btn w-8 h-7 bg-gray-50 hover:bg-blue-100 rounded flex items-center justify-center transition-colors text-xs" 
                            data-section="featured-products" data-action="move-up" title="הזז למעלה">
                        <i class="ri-arrow-up-line text-gray-600"></i>
                    </button>
                    <button class="action-btn move-down-btn w-8 h-7 bg-gray-50 hover:bg-blue-100 rounded flex items-center justify-center transition-colors text-xs" 
                            data-section="featured-products" data-action="move-down" title="הזז למטה">
                        <i class="ri-arrow-down-line text-gray-600"></i>
                    </button>
                    <button class="action-btn duplicate-btn w-8 h-7 bg-gray-50 hover:bg-green-100 rounded flex items-center justify-center transition-colors text-xs" 
                            data-section="featured-products" data-action="duplicate" title="שכפל">
                        <i class="ri-file-copy-line text-gray-600"></i>
                    </button>
                    <button class="action-btn hide-btn w-8 h-7 bg-gray-50 hover:bg-yellow-100 rounded flex items-center justify-center transition-colors text-xs" 
                            data-section="featured-products" data-action="hide" title="הסתר">
                        <i class="ri-eye-off-line text-gray-600"></i>
                    </button>
                    <button class="action-btn delete-btn w-8 h-7 bg-gray-50 hover:bg-red-100 rounded flex items-center justify-center transition-colors text-xs" 
                            data-section="featured-products" data-action="delete" title="מחק">
                        <i class="ri-delete-bin-line text-red-600"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Section Button -->
    <div class="mt-6">
        <button class="add-section-btn w-full border-2 border-dashed border-gray-300 rounded-lg p-6 text-gray-500 hover:border-gray-400 hover:text-gray-600 transition-colors flex items-center justify-center gap-2">
            <i class="ri-add-line text-xl"></i>
            <span class="font-medium">הוסף סקשן</span>
        </button>
    </div>
</div> 