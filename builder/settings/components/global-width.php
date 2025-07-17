<?php
/**
 * Global Width Settings Component
 * 
 * רכיב הגדרות רוחב גלובלי שיכול לשמש כל הסקשנים
 * כולל: קונטיינר, רוחב מלא, רוחב מותאם אישית
 */

// Load global width helper if not already loaded
if (!class_exists('SectionWidthHelper')) {
    require_once __DIR__ . '/../../includes/SectionWidthHelper.php';
}

$widthSettings = SectionWidthHelper::getWidthSettingsForJS();
?>

<div class="space-y-4">
    <h4 class="text-sm font-medium text-gray-900 mb-3">רוחב הסקשן</h4>
    
    <!-- Width Type Selection -->
    <div class="space-y-2">
        <label class="text-sm font-medium text-gray-700">סוג רוחב</label>
        <select id="<?php echo $sectionType ?? 'section'; ?>Width" name="width" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="container" <?php echo (isset($defaultData['width']) && $defaultData['width'] === 'container') ? 'selected' : ''; ?>>קונטיינר (1200px מקסימום)</option>
            <option value="full" <?php echo (isset($defaultData['width']) && $defaultData['width'] === 'full') ? 'selected' : ''; ?>>רוחב מלא</option>
            <option value="custom" <?php echo (isset($defaultData['width']) && $defaultData['width'] === 'custom') ? 'selected' : ''; ?>>רוחב מותאם אישית</option>
        </select>
    </div>
    
    <!-- Custom Width Settings -->
    <div id="customWidthSettings" class="space-y-3 <?php echo (!isset($defaultData['width']) || $defaultData['width'] !== 'custom') ? 'hidden' : ''; ?>">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-sm font-medium text-gray-700">רוחב</label>
                <input type="number" 
                       id="<?php echo $sectionType ?? 'section'; ?>CustomWidth" 
                       name="customWidth"
                       value="<?php echo $defaultData['customWidth'] ?? '800'; ?>"
                       min="200" 
                       max="2000" 
                       step="50"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="800">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">יחידה</label>
                <select id="<?php echo $sectionType ?? 'section'; ?>CustomWidthUnit" name="customWidthUnit" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="px" <?php echo (!isset($defaultData['customWidthUnit']) || $defaultData['customWidthUnit'] === 'px') ? 'selected' : ''; ?>>פיקסלים (px)</option>
                    <option value="%" <?php echo (isset($defaultData['customWidthUnit']) && $defaultData['customWidthUnit'] === '%') ? 'selected' : ''; ?>>אחוזים (%)</option>
                    <option value="vw" <?php echo (isset($defaultData['customWidthUnit']) && $defaultData['customWidthUnit'] === 'vw') ? 'selected' : ''; ?>>רוחב מסך (vw)</option>
                </select>
            </div>
        </div>
        
        <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded">
            <i class="ri-information-line mr-1"></i>
            רוחב מותאם אישית יחול רק על התוכן, הרקע יישאר ברוחב מלא
        </div>
    </div>
</div>

<script>
// Global Width Component JavaScript
(function() {
    // Get section type from PHP
    const sectionType = '<?php echo $sectionType ?? "section"; ?>';
    
    // Get elements with dynamic IDs
    const widthType = document.getElementById(sectionType + 'Width');
    const customWidthSettings = document.getElementById('customWidthSettings');
    const customWidth = document.getElementById(sectionType + 'CustomWidth');
    const customWidthUnit = document.getElementById(sectionType + 'CustomWidthUnit');
    
    console.log('Width component elements:', {
        sectionType,
        widthType: !!widthType,
        customWidthSettings: !!customWidthSettings,
        customWidth: !!customWidth,
        customWidthUnit: !!customWidthUnit
    });
    
    // Show/hide custom width settings
    function toggleCustomSettings() {
        if (!widthType || !customWidthSettings) return;
        
        if (widthType.value === 'custom') {
            customWidthSettings.classList.remove('hidden');
        } else {
            customWidthSettings.classList.add('hidden');
        }
    }
    
    // Setup event listeners if elements exist
    if (widthType && customWidthSettings && customWidth && customWidthUnit) {
        
        // Event listeners
        widthType.addEventListener('change', function() {
            toggleCustomSettings();
            
            // Trigger section-specific update if available
            if (window.currentSection && window.currentSection.updateProperty) {
                window.currentSection.updateProperty('width', this.value);
                window.currentSection.updateProperty('customWidth', customWidth.value || 800);
                window.currentSection.updateProperty('customWidthUnit', customWidthUnit.value || 'px');
            }
        });
        
        customWidth.addEventListener('input', function() {
            if (widthType.value === 'custom' && window.currentSection && window.currentSection.updateProperty) {
                window.currentSection.updateProperty('customWidth', this.value || 800);
            }
        });
        
        customWidthUnit.addEventListener('change', function() {
            if (widthType.value === 'custom' && window.currentSection && window.currentSection.updateProperty) {
                window.currentSection.updateProperty('customWidthUnit', this.value || 'px');
            }
        });
        
        // Initialize
        toggleCustomSettings();
        
        console.log('Width component initialized successfully');
    } else {
        console.warn('Width component elements not found:', {
            widthType: !!widthType,
            customWidthSettings: !!customWidthSettings,
            customWidth: !!customWidth,
            customWidthUnit: !!customWidthUnit
        });
    }
})();
</script> 