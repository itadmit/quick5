<!-- Layout Section -->
<div class="bg-gray-50 rounded-lg p-4">
    <div class="flex items-center gap-2 mb-3">
        <i class="ri-layout-line text-blue-600"></i>
        <h4 class="font-medium text-gray-900">פריסה</h4>
    </div>
    
    <div class="space-y-4">
        <!-- Global Width Component -->
        <?php include 'global-width.php'; ?>
        
        <!-- Section Height -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-gray-700">גובה הסקשן</label>
                <div class="responsive-switcher flex bg-gray-100 rounded-md overflow-hidden text-xs">
                    <button type="button" class="mode-btn responsive-tab px-3 py-1 bg-blue-500 text-white" data-mode="desktop">
                        <i class="ri-computer-line"></i>
                    </button>
                    <button type="button" class="mode-btn responsive-tab px-3 py-1 hover:bg-gray-200" data-mode="tablet">
                        <i class="ri-tablet-line"></i>
                    </button>
                    <button type="button" class="mode-btn responsive-tab px-3 py-1 hover:bg-gray-200" data-mode="mobile">
                        <i class="ri-smartphone-line"></i>
                    </button>
                </div>
            </div>
            
            <!-- Desktop Height -->
            <div class="responsive-content desktop-content">
                <div class="grid grid-cols-2 gap-3">
                    <select id="<?php echo $sectionType; ?>HeightType" name="heightType" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="auto" <?php echo (!isset($defaultData['heightType']) || $defaultData['heightType'] === 'auto') ? 'selected' : ''; ?>>ברירת מחדל</option>
                        <option value="px" <?php echo (isset($defaultData['heightType']) && $defaultData['heightType'] === 'px') ? 'selected' : ''; ?>>פיקסלים</option>
                        <option value="vh" <?php echo (isset($defaultData['heightType']) && $defaultData['heightType'] === 'vh') ? 'selected' : ''; ?>>גובה מסך (vh)</option>
                    </select>
                    <input type="number" id="<?php echo $sectionType; ?>HeightValue" name="heightValue" 
                           value="<?php echo $defaultData['heightValue'] ?? '500'; ?>" 
                           placeholder="500" 
                           class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 <?php echo (!isset($defaultData['heightType']) || $defaultData['heightType'] === 'auto') ? 'opacity-50 bg-gray-100' : ''; ?>">
                </div>
            </div>
            
            <!-- Tablet Height -->
            <div class="responsive-content tablet-content hidden">
                <div class="grid grid-cols-2 gap-3">
                    <select id="<?php echo $sectionType; ?>HeightType_tablet" name="heightType_tablet" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="" <?php echo (!isset($defaultData['heightType_tablet']) || $defaultData['heightType_tablet'] === '') ? 'selected' : ''; ?>>כמו מחשב</option>
                        <option value="auto" <?php echo (isset($defaultData['heightType_tablet']) && $defaultData['heightType_tablet'] === 'auto') ? 'selected' : ''; ?>>ברירת מחדל</option>
                        <option value="px" <?php echo (isset($defaultData['heightType_tablet']) && $defaultData['heightType_tablet'] === 'px') ? 'selected' : ''; ?>>פיקסלים</option>
                        <option value="vh" <?php echo (isset($defaultData['heightType_tablet']) && $defaultData['heightType_tablet'] === 'vh') ? 'selected' : ''; ?>>גובה מסך (vh)</option>
                    </select>
                    <input type="number" id="<?php echo $sectionType; ?>HeightValue_tablet" name="heightValue_tablet" 
                           value="<?php echo $defaultData['heightValue_tablet'] ?? ''; ?>" 
                           placeholder="כמו מחשב" 
                           class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 <?php echo (!isset($defaultData['heightType_tablet']) || $defaultData['heightType_tablet'] === '' || $defaultData['heightType_tablet'] === 'auto') ? 'opacity-50 bg-gray-100' : ''; ?>">
                </div>
            </div>
            
            <!-- Mobile Height -->
            <div class="responsive-content mobile-content hidden">
                <div class="grid grid-cols-2 gap-3">
                    <select id="<?php echo $sectionType; ?>HeightType_mobile" name="heightType_mobile" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="" <?php echo (!isset($defaultData['heightType_mobile']) || $defaultData['heightType_mobile'] === '') ? 'selected' : ''; ?>>כמו מחשב</option>
                        <option value="auto" <?php echo (isset($defaultData['heightType_mobile']) && $defaultData['heightType_mobile'] === 'auto') ? 'selected' : ''; ?>>ברירת מחדל</option>
                        <option value="px" <?php echo (isset($defaultData['heightType_mobile']) && $defaultData['heightType_mobile'] === 'px') ? 'selected' : ''; ?>>פיקסלים</option>
                        <option value="vh" <?php echo (isset($defaultData['heightType_mobile']) && $defaultData['heightType_mobile'] === 'vh') ? 'selected' : ''; ?>>גובה מסך (vh)</option>
                    </select>
                    <input type="number" id="<?php echo $sectionType; ?>HeightValue_mobile" name="heightValue_mobile" 
                           value="<?php echo $defaultData['heightValue_mobile'] ?? ''; ?>" 
                           placeholder="כמו מחשב" 
                           class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 <?php echo (!isset($defaultData['heightType_mobile']) || $defaultData['heightType_mobile'] === '' || $defaultData['heightType_mobile'] === 'auto') ? 'opacity-50 bg-gray-100' : ''; ?>">
                        </div>
    </div>
</div>

<script>
// Layout responsive switcher functionality
(function() {
    function initLayoutResponsiveSwitcher() {
        const switcher = document.querySelector('.responsive-switcher');
        if (!switcher) {
            setTimeout(initLayoutResponsiveSwitcher, 100);
            return;
        }
        
        const responsiveTabs = switcher.querySelectorAll('.responsive-tab');
        const responsiveContents = document.querySelectorAll('.responsive-content');
        
        console.log('Layout responsive tabs found:', responsiveTabs.length);
        console.log('Layout responsive contents found:', responsiveContents.length);
        
        if (responsiveTabs.length > 0 && responsiveContents.length > 0) {
            responsiveTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const mode = this.dataset.mode;
                    console.log('Layout responsive tab clicked:', mode);
                    
                    // Update tab states
                    responsiveTabs.forEach(t => {
                        t.classList.remove('bg-blue-500', 'text-white');
                        t.classList.add('hover:bg-gray-200');
                    });
                    this.classList.add('bg-blue-500', 'text-white');
                    this.classList.remove('hover:bg-gray-200');
                    
                    // Show/hide content panels
                    responsiveContents.forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    const targetContent = document.querySelector(`.${mode}-content`);
                    if (targetContent) {
                        targetContent.classList.remove('hidden');
                    }
                    
                    // Update global responsive mode
                    if (window.setResponsiveMode) {
                        window.setResponsiveMode(mode);
                    }
                });
            });
            
            // Set initial state based on current mode
            const currentMode = window.currentResponsiveMode || 'desktop';
            const currentTab = switcher.querySelector(`[data-mode="${currentMode}"]`);
            if (currentTab) {
                currentTab.click();
            }
            
            console.log('Layout responsive switcher initialized successfully');
        } else {
            console.error('Layout responsive elements not found - retrying in 100ms');
            setTimeout(initLayoutResponsiveSwitcher, 100);
        }
    }
    
    // Try to initialize immediately
    initLayoutResponsiveSwitcher();
})();
</script>
        
        <!-- Content Position -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">מיקום תוכן</label>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors flex flex-col items-center justify-center text-center <?php echo (isset($defaultData['contentPosition']) && $defaultData['contentPosition'] === 'top-right') ? 'bg-blue-50 border-blue-300' : ''; ?>" data-position="top-right">
                    <i class="ri-align-top text-lg"></i>
                    <div class="text-xs mt-1">ימין למעלה</div>
                </button>
                <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors flex flex-col items-center justify-center text-center <?php echo (isset($defaultData['contentPosition']) && $defaultData['contentPosition'] === 'top-center') ? 'bg-blue-50 border-blue-300' : ''; ?>" data-position="top-center">
                    <i class="ri-align-top text-lg"></i>
                    <div class="text-xs mt-1">מרכז למעלה</div>
                </button>
                <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors flex flex-col items-center justify-center text-center <?php echo (isset($defaultData['contentPosition']) && $defaultData['contentPosition'] === 'top-left') ? 'bg-blue-50 border-blue-300' : ''; ?>" data-position="top-left">
                    <i class="ri-align-top text-lg"></i>
                    <div class="text-xs mt-1">שמאל למעלה</div>
                </button>
                <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors flex flex-col items-center justify-center text-center <?php echo (isset($defaultData['contentPosition']) && $defaultData['contentPosition'] === 'center-right') ? 'bg-blue-50 border-blue-300' : ''; ?>" data-position="center-right">
                    <i class="ri-align-center text-lg"></i>
                    <div class="text-xs mt-1">ימין מרכז</div>
                </button>
                <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors flex flex-col items-center justify-center text-center <?php echo (!isset($defaultData['contentPosition']) || $defaultData['contentPosition'] === 'center-center') ? 'bg-blue-50 border-blue-300' : ''; ?>" data-position="center-center">
                    <i class="ri-align-center text-lg"></i>
                    <div class="text-xs mt-1">מרכז מרכז</div>
                </button>
                <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors flex flex-col items-center justify-center text-center <?php echo (isset($defaultData['contentPosition']) && $defaultData['contentPosition'] === 'center-left') ? 'bg-blue-50 border-blue-300' : ''; ?>" data-position="center-left">
                    <i class="ri-align-center text-lg"></i>
                    <div class="text-xs mt-1">שמאל מרכז</div>
                </button>
                <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors flex flex-col items-center justify-center text-center <?php echo (isset($defaultData['contentPosition']) && $defaultData['contentPosition'] === 'bottom-right') ? 'bg-blue-50 border-blue-300' : ''; ?>" data-position="bottom-right">
                    <i class="ri-align-bottom text-lg"></i>
                    <div class="text-xs mt-1">ימין למטה</div>
                </button>
                <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors flex flex-col items-center justify-center text-center <?php echo (isset($defaultData['contentPosition']) && $defaultData['contentPosition'] === 'bottom-center') ? 'bg-blue-50 border-blue-300' : ''; ?>" data-position="bottom-center">
                    <i class="ri-align-bottom text-lg"></i>
                    <div class="text-xs mt-1">מרכז למטה</div>
                </button>
                <button type="button" class="position-btn p-3 border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-colors flex flex-col items-center justify-center text-center <?php echo (isset($defaultData['contentPosition']) && $defaultData['contentPosition'] === 'bottom-left') ? 'bg-blue-50 border-blue-300' : ''; ?>" data-position="bottom-left">
                    <i class="ri-align-bottom text-lg"></i>
                    <div class="text-xs mt-1">שמאל למטה</div>
                </button>
            </div>
            <input type="hidden" id="<?php echo $sectionType; ?>ContentPosition" name="contentPosition" value="<?php echo $defaultData['contentPosition'] ?? 'center-center'; ?>">
        </div>
    </div>
</div> 