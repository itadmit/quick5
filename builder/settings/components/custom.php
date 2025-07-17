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
            <input type="text" id="<?php echo $sectionType; ?>CustomClass" name="customClass" 
                   value="<?php echo $defaultData['customClass'] ?? ''; ?>"
                   placeholder="my-custom-class" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <!-- Custom ID -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">מזהה מותאם (ID)</label>
            <input type="text" id="<?php echo $sectionType; ?>CustomId" name="customId" 
                   value="<?php echo $defaultData['customId'] ?? ''; ?>"
                   placeholder="my-custom-id" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>
</div> 