<!-- Content Section -->
<div class="bg-gray-50 rounded-lg p-4">
    <div class="flex items-center gap-2 mb-3">
        <i class="ri-text text-green-600"></i>
        <h4 class="font-medium text-gray-900">תוכן</h4>
    </div>
    
    <div class="space-y-4">
        <!-- Title -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">כותרת</label>
            <input type="text" id="<?php echo $sectionType; ?>Title" name="title" 
                   value="<?php echo $defaultData['title'] ?? 'ברוכים הבאים לחנות שלנו'; ?>" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <!-- Content -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">תוכן</label>
            <textarea id="<?php echo $sectionType; ?>Subtitle" name="subtitle" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="הכנס את התוכן כאן... תוכל להשתמש ב-&lt;br&gt; לשורה חדשה ו-&lt;strong&gt; להדגשה"><?php echo $defaultData['subtitle'] ?? 'גלה את המוצרים הטובים ביותר במחירים הכי טובים'; ?></textarea>
            <p class="text-xs text-gray-500 mt-1">
                <strong>עצות:</strong> 
                השתמש ב-&lt;br&gt; לשורה חדשה, ב-&lt;strong&gt;טקסט&lt;/strong&gt; להדגשה, וב-&lt;em&gt;טקסט&lt;/em&gt; לקו נטוי
            </p>
        </div>
        
        <!-- Hidden inputs for backward compatibility -->
        <input type="hidden" id="<?php echo $sectionType; ?>ButtonText" name="buttonText" value="">
        <input type="hidden" id="<?php echo $sectionType; ?>ButtonLink" name="buttonLink" value="">
        <input type="hidden" id="<?php echo $sectionType; ?>ButtonNewTab" name="buttonNewTab" value="">
    </div>
</div>

<!-- Buttons Repeater -->
<?php include 'buttons-repeater.php'; ?> 