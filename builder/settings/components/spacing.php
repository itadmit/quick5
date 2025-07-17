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
                    <input type="number" id="<?php echo $sectionType; ?>PaddingTop" name="paddingTop" 
                           value="<?php echo $defaultData['paddingTop'] ?? '80'; ?>" placeholder="למעלה" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <input type="number" id="<?php echo $sectionType; ?>PaddingBottom" name="paddingBottom" 
                           value="<?php echo $defaultData['paddingBottom'] ?? '80'; ?>" placeholder="למטה" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <input type="number" id="<?php echo $sectionType; ?>PaddingRight" name="paddingRight" 
                           value="<?php echo $defaultData['paddingRight'] ?? '20'; ?>" placeholder="ימין" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <input type="number" id="<?php echo $sectionType; ?>PaddingLeft" name="paddingLeft" 
                           value="<?php echo $defaultData['paddingLeft'] ?? '20'; ?>" placeholder="שמאל" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
        
        <!-- Margin -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">מרגין (px)</label>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <input type="number" id="<?php echo $sectionType; ?>MarginTop" name="marginTop" 
                           value="<?php echo $defaultData['marginTop'] ?? '0'; ?>" placeholder="למעלה" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <input type="number" id="<?php echo $sectionType; ?>MarginBottom" name="marginBottom" 
                           value="<?php echo $defaultData['marginBottom'] ?? '0'; ?>" placeholder="למטה" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <input type="number" id="<?php echo $sectionType; ?>MarginRight" name="marginRight" 
                           value="<?php echo $defaultData['marginRight'] ?? '0'; ?>" placeholder="ימין" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <input type="number" id="<?php echo $sectionType; ?>MarginLeft" name="marginLeft" 
                           value="<?php echo $defaultData['marginLeft'] ?? '0'; ?>" placeholder="שמאל" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
    </div>
</div> 