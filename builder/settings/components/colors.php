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
            <input type="color" id="<?php echo $sectionType; ?>TitleColor" name="titleColor" 
                   value="<?php echo $defaultData['titleColor'] ?? '#FFFFFF'; ?>" 
                   class="w-full h-10 border border-gray-300 rounded-md">
        </div>
        
        <!-- Subtitle Color -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">צבע תת כותרת</label>
            <input type="color" id="<?php echo $sectionType; ?>SubtitleColor" name="subtitleColor" 
                   value="<?php echo $defaultData['subtitleColor'] ?? '#E5E7EB'; ?>" 
                   class="w-full h-10 border border-gray-300 rounded-md">
        </div>
        
        <!-- Button Colors with State Switcher -->
        <div class="col-span-2">
            <div class="bg-white rounded-md p-3">
                <div class="flex items-center justify-between mb-3">
                    <h5 class="text-sm font-medium text-gray-900">צבעי כפתורים</h5>
                    
                    <!-- Button State Switcher -->
                    <div class="flex items-center gap-1 bg-gray-100 rounded-md p-1">
                        <button type="button" class="button-state-btn px-3 py-1 text-xs rounded active" data-state="normal">רגיל</button>
                        <button type="button" class="button-state-btn px-3 py-1 text-xs rounded" data-state="hover">Hover</button>
                        </div>
</div>

<style>
.button-state-btn.active {
    background-color: #3b82f6;
    color: white;
}

.button-state-btn:not(.active) {
    color: #6b7280;
}

.button-state-btn:not(.active):hover {
    background-color: #f3f4f6;
}
</style>

                <!-- Normal State -->
                <div id="buttonNormalState" class="button-state-panel">
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">רקע</label>
                            <input type="color" id="<?php echo $sectionType; ?>ButtonBgColor" name="buttonBgColor" 
                                   value="<?php echo $defaultData['buttonBgColor'] ?? '#F59E0B'; ?>" 
                                   class="w-full h-8 border border-gray-300 rounded">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">מסגרת</label>
                            <input type="color" id="<?php echo $sectionType; ?>ButtonBorderColor" name="buttonBorderColor" 
                                   value="<?php echo $defaultData['buttonBorderColor'] ?? '#F59E0B'; ?>" 
                                   class="w-full h-8 border border-gray-300 rounded">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">טקסט</label>
                            <input type="color" id="<?php echo $sectionType; ?>ButtonTextColor" name="buttonTextColor" 
                                   value="<?php echo $defaultData['buttonTextColor'] ?? '#FFFFFF'; ?>" 
                                   class="w-full h-8 border border-gray-300 rounded">
                        </div>
                    </div>
                </div>
                
                <!-- Hover State -->
                <div id="buttonHoverState" class="button-state-panel" style="display: none;">
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">רקע</label>
                            <input type="color" id="<?php echo $sectionType; ?>ButtonBgColorHover" name="buttonBgColorHover" 
                                   value="<?php echo $defaultData['buttonBgColorHover'] ?? '#E5A712'; ?>" 
                                   class="w-full h-8 border border-gray-300 rounded">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">מסגרת</label>
                            <input type="color" id="<?php echo $sectionType; ?>ButtonBorderColorHover" name="buttonBorderColorHover" 
                                   value="<?php echo $defaultData['buttonBorderColorHover'] ?? '#E5A712'; ?>" 
                                   class="w-full h-8 border border-gray-300 rounded">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">טקסט</label>
                            <input type="color" id="<?php echo $sectionType; ?>ButtonTextColorHover" name="buttonTextColorHover" 
                                   value="<?php echo $defaultData['buttonTextColorHover'] ?? '#FFFFFF'; ?>" 
                                   class="w-full h-8 border border-gray-300 rounded">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Button state switcher functionality - execute immediately
(function() {
    function initButtonStateSwitcher() {
        const stateButtons = document.querySelectorAll('.button-state-btn');
        const normalPanel = document.getElementById('buttonNormalState');
        const hoverPanel = document.getElementById('buttonHoverState');
        
        console.log('Color state buttons found:', stateButtons.length);
        console.log('Normal panel:', normalPanel);
        console.log('Hover panel:', hoverPanel);
        
        if (stateButtons.length > 0 && normalPanel && hoverPanel) {
            stateButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const state = this.dataset.state;
                    console.log('Button state clicked:', state);
                    
                    // Update active button
                    stateButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show/hide panels
                    if (state === 'normal') {
                        normalPanel.style.display = 'block';
                        hoverPanel.style.display = 'none';
                    } else if (state === 'hover') {
                        normalPanel.style.display = 'none';
                        hoverPanel.style.display = 'block';
                    }
                });
            });
            console.log('Button state switcher initialized successfully');
        } else {
            console.error('Button state elements not found - retrying in 100ms');
            // Retry after a short delay if elements not found
            setTimeout(initButtonStateSwitcher, 100);
        }
    }
    
    // Try to initialize immediately
    initButtonStateSwitcher();
})();
</script> 