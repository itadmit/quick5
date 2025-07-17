// Product Form Management Class
class ProductForm {
    constructor() {
        this.mediaCount = 0;
        this.attributeCount = 1;
        this.accordionCount = 1;
        this.badgeCount = 0;
        this.imageUploader = null;
        this.searchCache = new Map(); // cache לחיפוש מוצרים
        
        // הפיכת ה-instance לגלובלי
        window.ProductFormInstance = this;
        
        this.init();
    }
    
    init() {
        this.initImageUploader();
        this.setupEventListeners();
        this.setupMediaUpload();
        this.setupVariantsToggle();
        this.setupInventoryToggle();
        this.setupColorPickers();
        this.setupTagsInput();
        
        // Initialize existing product searches (for edit page)
        this.initExistingProductSearches();
    }
    
    initImageUploader() {
        // אתחול מערכת העלאת תמונות
        this.imageUploader = initImageUploader({
            storeId: window.storeId || 1,
            onSuccess: (result) => {
                console.log('תמונה הועלתה בהצלחה:', result);
                showNotification('התמונה הועלתה בהצלחה!', 'success');
            },
            onError: (message) => {
                console.error('שגיאה בהעלאת תמונה:', message);
                showNotification('שגיאה בהעלאת התמונה: ' + message, 'error');
            }
        });
    }
    
    setupEventListeners() {
        // Media upload
        const mediaUpload = document.getElementById('media-upload');
        const mediaUploadArea = document.getElementById('media-upload-area');
        
        if (mediaUpload && mediaUploadArea) {
            // Click handler for upload area and button
            mediaUploadArea.addEventListener('click', () => mediaUpload.click());
            
            // File input change handler  
            mediaUpload.addEventListener('change', (e) => this.handleMediaUpload(e.target.files));
        }
        
        // Listen for changes in attribute names to update galleries
        document.addEventListener('input', (e) => {
            if (e.target.matches('input[name*="[name]"]') || e.target.matches('input[name*="[value]"]')) {
                if (typeof updateGalleryAttributeDropdown === 'function') {
                    setTimeout(updateGalleryAttributeDropdown, 100); // Small delay to ensure value is updated
                }
            }
        });
        
        // Add buttons
        const addAccordionBtn = document.getElementById('add-accordion');
        const addAttributeBtn = document.getElementById('add-attribute');
        const addBadgeBtn = document.getElementById('add-badge');
        const addRelatedBtn = document.getElementById('add-related-product');
        const addUpsellBtn = document.getElementById('add-upsell-product');
        const addBundleBtn = document.getElementById('add-product-bundle');
        
        if (addAccordionBtn) addAccordionBtn.addEventListener('click', () => this.addAccordion());
        if (addAttributeBtn) addAttributeBtn.addEventListener('click', () => this.addAttribute());
        if (addBadgeBtn) addBadgeBtn.addEventListener('click', () => this.addBadge());
        if (addRelatedBtn) addRelatedBtn.addEventListener('click', () => this.addRelatedProduct());
        if (addUpsellBtn) addUpsellBtn.addEventListener('click', () => this.addUpsellProduct());
        if (addBundleBtn) addBundleBtn.addEventListener('click', () => this.addProductBundle());
    }
    
    setupMediaUpload() {
        const mediaUploadArea = document.getElementById('media-upload-area');
        if (!mediaUploadArea) return;
        
        // Drag and drop functionality
        mediaUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            mediaUploadArea.classList.add('bg-blue-50', 'border-blue-300');
        });
        
        mediaUploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            mediaUploadArea.classList.remove('bg-blue-50', 'border-blue-300');
        });
        
        mediaUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            mediaUploadArea.classList.remove('bg-blue-50', 'border-blue-300');
            this.handleMediaUpload(e.dataTransfer.files);
        });
    }
    
    setupVariantsToggle() {
        const hasVariantsToggle = document.getElementById('has-variants');
        const variantsSection = document.getElementById('variants-section');
        
        if (hasVariantsToggle && variantsSection) {
            hasVariantsToggle.addEventListener('change', (e) => {
                if (e.target.checked) {
                    variantsSection.classList.remove('hidden');
                    this.hidePricingSection();
                    
                    // Check if there are no attributes yet and add one automatically
                    const attributesContainer = document.getElementById('attributes-container');
                    const existingAttributes = attributesContainer.querySelectorAll('.attribute-item');
                    
                    if (existingAttributes.length === 0) {
                        // Add the first attribute with default values
                        this.addAttributeWithDefaults();
                    }
                } else {
                    variantsSection.classList.add('hidden');
                    this.showPricingSection();
                    // Clear ALL variants tables
                    const existingTables = variantsSection.querySelectorAll('.variants-table-container');
                    existingTables.forEach(table => table.remove());
                }
            });
        }
    }
    
    setupInventoryToggle() {
        const trackInventoryToggle = document.getElementById('track_inventory');
        const inventoryFields = document.getElementById('inventory-fields');
        
        if (trackInventoryToggle && inventoryFields) {
            trackInventoryToggle.addEventListener('change', (e) => {
                if (e.target.checked) {
                    inventoryFields.style.opacity = '1';
                    inventoryFields.querySelectorAll('input, select').forEach(input => {
                        input.disabled = false;
                    });
                } else {
                    inventoryFields.style.opacity = '0.5';
                    inventoryFields.querySelectorAll('input, select').forEach(input => {
                        input.disabled = true;
                        if (input.type === 'number') {
                            input.value = '0';
                        }
                    });
                }
            });
        }
    }
    
    hidePricingSection() {
        // מצא את חלק התמחור
        const pricingSection = document.getElementById('pricing-section') || 
            Array.from(document.querySelectorAll('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6'))
            .find(section => section.querySelector('h2')?.textContent?.includes('תמחור'));
        
        if (pricingSection) {
            pricingSection.style.opacity = '0.5';
            const inputs = pricingSection.querySelectorAll('input');
            inputs.forEach(input => {
                input.disabled = true;
                input.style.backgroundColor = '#f9fafb';
                // הוסף data attribute כדי לזכור שזה מושבת בגלל וריאציות
                input.setAttribute('data-disabled-for-variants', 'true');
                // שמור את ה-required המקורי והסר אותו
                if (input.hasAttribute('required')) {
                    input.setAttribute('data-was-required', 'true');
                    input.removeAttribute('required');
                }
            });
            
            // הסתר את ה-required indicator
            const requiredIndicator = document.getElementById('price-required-indicator');
            if (requiredIndicator) {
                requiredIndicator.style.display = 'none';
            }
            
            // הוסף הודעה
            let warningDiv = pricingSection.querySelector('.variants-pricing-warning');
            if (!warningDiv) {
                warningDiv = document.createElement('div');
                warningDiv.className = 'variants-pricing-warning mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg';
                warningDiv.innerHTML = `
                    <div class="flex items-center gap-2">
                        <i class="ri-information-line text-blue-600"></i>
                        <span class="text-sm text-blue-800 font-medium">התמחור נקבע לפי וריאציות</span>
                    </div>
                    <p class="text-xs text-blue-700 mt-1">כאשר למוצר יש וריאציות, המחיר נקבע בטבלת הוריאציות למטה</p>
                `;
                pricingSection.appendChild(warningDiv);
            }
        }
        
        // גם מסתיר את חלק המלאי הכללי
        const inventorySection = Array.from(document.querySelectorAll('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6'))
            .find(section => section.querySelector('h3')?.textContent?.includes('מלאי'));
        
        if (inventorySection) {
            const inventoryQuantityContainer = inventorySection.querySelector('#inventory-quantity-container');
            const skuContainer = inventorySection.querySelector('input[name="sku"]')?.closest('div');
            
            if (inventoryQuantityContainer) {
                inventoryQuantityContainer.style.opacity = '0.5';
                const input = inventoryQuantityContainer.querySelector('input');
                if (input) {
                    input.disabled = true;
                    input.style.backgroundColor = '#f9fafb';
                    input.setAttribute('data-disabled-for-variants', 'true');
                }
            }
            
            if (skuContainer) {
                skuContainer.style.opacity = '0.5';
                const input = skuContainer.querySelector('input');
                if (input) {
                    input.disabled = true;
                    input.style.backgroundColor = '#f9fafb';
                    input.setAttribute('data-disabled-for-variants', 'true');
                }
            }
        }
    }
    
    showPricingSection() {
        // מצא את חלק התמחור
        const pricingSection = document.getElementById('pricing-section') || 
            Array.from(document.querySelectorAll('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6'))
            .find(section => section.querySelector('h2')?.textContent?.includes('תמחור'));
        
        if (pricingSection) {
            pricingSection.style.opacity = '1';
            const inputs = pricingSection.querySelectorAll('input[data-disabled-for-variants]');
            inputs.forEach(input => {
                input.disabled = false;
                input.style.backgroundColor = '';
                input.removeAttribute('data-disabled-for-variants');
                // החזר את ה-required אם היה
                if (input.hasAttribute('data-was-required')) {
                    input.setAttribute('required', '');
                    input.removeAttribute('data-was-required');
                }
            });
            
            // הצג את ה-required indicator
            const requiredIndicator = document.getElementById('price-required-indicator');
            if (requiredIndicator) {
                requiredIndicator.style.display = 'inline';
            }
            
            // הסר את ההודעה
            const warningDiv = pricingSection.querySelector('.variants-pricing-warning');
            if (warningDiv) {
                warningDiv.remove();
            }
        }
        
        // גם מחזיר את חלק המלאי הכללי
        const inventorySection = Array.from(document.querySelectorAll('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6'))
            .find(section => section.querySelector('h3')?.textContent?.includes('מלאי'));
        
        if (inventorySection) {
            const inventoryQuantityContainer = inventorySection.querySelector('#inventory-quantity-container');
            const skuContainer = inventorySection.querySelector('input[name="sku"]')?.closest('div');
            
            if (inventoryQuantityContainer) {
                inventoryQuantityContainer.style.opacity = '1';
                const input = inventoryQuantityContainer.querySelector('input');
                if (input && input.hasAttribute('data-disabled-for-variants')) {
                    input.disabled = false;
                    input.style.backgroundColor = '';
                    input.removeAttribute('data-disabled-for-variants');
                }
            }
            
            if (skuContainer) {
                skuContainer.style.opacity = '1';
                const input = skuContainer.querySelector('input');
                if (input && input.hasAttribute('data-disabled-for-variants')) {
                    input.disabled = false;
                    input.style.backgroundColor = '';
                    input.removeAttribute('data-disabled-for-variants');
                }
            }
        }
    }
    
    async handleMediaUpload(files) {
        const mediaPreview = document.getElementById('media-preview');
        
        for (let file of files) {
            if (file.type.startsWith('image/') || file.type.startsWith('video/')) {
                // העלאה דרך API החדש
                if (file.type.startsWith('image/') && this.imageUploader) {
                    await this.uploadAndAddMedia(file);
                } else {
                    // לוידאו - נשאיר את הדרך הישנה בינתיים
                    this.addMediaPreview(file);
                }
            }
        }
        
        if (mediaPreview.children.length > 0) {
            mediaPreview.classList.remove('hidden');
        }
    }
    
    async uploadAndAddMedia(file) {
        const mediaPreview = document.getElementById('media-preview');
        const mediaItem = document.createElement('div');
        mediaItem.className = 'relative group border border-gray-200 rounded-lg overflow-hidden';
        
        // הצג מצב טעינה
        mediaItem.innerHTML = `
            <div class="aspect-square bg-gray-100 flex items-center justify-center">
                <div class="flex flex-col items-center gap-2 text-gray-400">
                    <i class="ri-loader-4-line text-2xl animate-spin"></i>
                    <span class="text-sm">מעלה תמונה...</span>
                </div>
            </div>
        `;
        
        mediaPreview.appendChild(mediaItem);
        mediaPreview.classList.remove('hidden');
        
        try {
            // העלאה דרך API
            const result = await this.imageUploader.uploadFile(file, 'products');
            
            if (result && result.success) {
                // עדכן את הDOM עם התמונה שהועלתה
                mediaItem.innerHTML = `
                    <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden rounded-lg">
                        <img src="${result.thumbnail_url || result.url}" alt="${file.name}" class="w-full h-full object-cover">
                    </div>
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-200"></div>
                    
                    <!-- אייקון מחיקה - שמאל מעלה -->
                    <button type="button" onclick="removeGalleryImage(this);" 
                        class="absolute top-2 left-2 w-8 h-8 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-red-600 flex items-center justify-center shadow-lg">
                        <i class="ri-delete-bin-line text-sm"></i>
                    </button>
                    
                    <!-- אייקון תמונה ראשית - ימין מעלה -->
                    <button type="button" onclick="setPrimaryImage(this);" 
                        class="absolute top-2 right-2 w-8 h-8 ${this.mediaCount === 0 ? 'bg-yellow-500 text-white' : 'bg-white text-gray-600'} rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-yellow-500 hover:text-white flex items-center justify-center shadow-lg">
                        <i class="ri-star-${this.mediaCount === 0 ? 'fill' : 'line'} text-sm"></i>
                    </button>
                    
                    <!-- מספר סידורי -->
                    <div class="absolute bottom-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-all duration-200">
                        ${this.mediaCount + 1}
                    </div>
                    
                    <input type="hidden" name="media[${this.mediaCount}][url]" value="${result.url}">
                    <input type="hidden" name="media[${this.mediaCount}][thumbnail_url]" value="${result.thumbnail_url || result.url}">
                    <input type="hidden" name="media[${this.mediaCount}][type]" value="image">
                    <input type="hidden" name="media[${this.mediaCount}][alt_text]" value="${file.name}">
                    <input type="hidden" name="media[${this.mediaCount}][filename]" value="${result.filename}">
                    <input type="hidden" name="media[${this.mediaCount}][is_primary]" value="${this.mediaCount === 0 ? 1 : 0}">
                    <input type="hidden" name="media[${this.mediaCount}][sort_order]" value="${this.mediaCount}">
                `;
                
                this.mediaCount++;
            } else {
                // הסרת התמונה במקרה של שגיאה
                mediaItem.remove();
                showNotification('שגיאה בהעלאת התמונה', 'error');
            }
        } catch (error) {
            console.error('שגיאה בהעלאת תמונה:', error);
            mediaItem.remove();
            showNotification('שגיאה בהעלאת התמונה: ' + error.message, 'error');
        }
    }
    
    addMediaPreview(file) {
        const mediaPreview = document.getElementById('media-preview');
        const mediaItem = document.createElement('div');
        mediaItem.className = 'relative group border border-gray-200 rounded-lg overflow-hidden';
        
        // Show preview container immediately
        mediaPreview.classList.remove('hidden');
        
        // Add to DOM first, then load content
        mediaPreview.appendChild(mediaItem);
        
        const reader = new FileReader();
        reader.onload = (e) => {
            const isVideo = file.type.startsWith('video/');
            
            mediaItem.innerHTML = `
                <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden rounded-lg">
                    ${isVideo ? 
                        `<video src="${e.target.result}" class="w-full h-full object-cover" controls></video>` :
                        `<img src="${e.target.result}" alt="${file.name}" class="w-full h-full object-cover">`
                    }
                </div>
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-200"></div>
                
                <!-- אייקון מחיקה - שמאל מעלה -->
                <button type="button" onclick="removeGalleryImage(this);" 
                    class="absolute top-2 left-2 w-8 h-8 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-red-600 flex items-center justify-center shadow-lg">
                    <i class="ri-delete-bin-line text-sm"></i>
                </button>
                
                <!-- אייקון תמונה ראשית - ימין מעלה -->
                <button type="button" onclick="setPrimaryImage(this);" 
                    class="absolute top-2 right-2 w-8 h-8 ${this.mediaCount === 0 ? 'bg-yellow-500 text-white' : 'bg-white text-gray-600'} rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-yellow-500 hover:text-white flex items-center justify-center shadow-lg">
                    <i class="ri-star-${this.mediaCount === 0 ? 'fill' : 'line'} text-sm"></i>
                </button>
                
                <!-- מספר סידורי -->
                <div class="absolute bottom-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-all duration-200">
                    ${this.mediaCount + 1}
                </div>
                
                <input type="hidden" name="media[${this.mediaCount}][url]" value="${e.target.result}">
                <input type="hidden" name="media[${this.mediaCount}][type]" value="${isVideo ? 'video' : 'image'}">
                <input type="hidden" name="media[${this.mediaCount}][alt_text]" value="${file.name}">
                <input type="hidden" name="media[${this.mediaCount}][is_primary]" value="${this.mediaCount === 0 ? 1 : 0}">
                <input type="hidden" name="media[${this.mediaCount}][sort_order]" value="${this.mediaCount}">
            `;
        };
        
        // Show loading state
        mediaItem.innerHTML = `
            <div class="aspect-square bg-gray-100 flex items-center justify-center">
                <div class="flex flex-col items-center gap-2 text-gray-400">
                    <i class="ri-loader-4-line text-2xl animate-spin"></i>
                    <span class="text-sm">טוען...</span>
                </div>
            </div>
        `;
        
        this.mediaCount++;
        reader.readAsDataURL(file);
    }
    
    addAccordion() {
        const container = document.getElementById('accordions-container');
        if (!container) {
            console.error('Accordions container not found');
            return;
        }
        
        // קבל את המספר הבא לאקורדיון
        const nextIndex = this.getNextAccordionIndex();
        
        const accordionItem = document.createElement('div');
        accordionItem.className = 'accordion-item border border-gray-200 rounded-lg';
        
        accordionItem.innerHTML = `
            <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <input type="text" name="accordions[${nextIndex}][title]" 
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm ml-3"
                    placeholder="כותרת השדה (למשל: הוראות שימוש, מידע טכני)">
                <button type="button" onclick="this.closest('.accordion-item').remove(); window.ProductFormInstance.updateAccordionCount();" 
                    class="text-red-500 hover:text-red-700 p-1">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
            <div class="p-4">
                <textarea name="accordions[${nextIndex}][content]" rows="3" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    placeholder="תוכן השדה המותאם..."></textarea>
            </div>
        `;
        
        container.appendChild(accordionItem);
        this.accordionCount = Math.max(this.accordionCount, nextIndex + 1);
    }
    
    getNextAccordionIndex() {
        const container = document.getElementById('accordions-container');
        if (!container) return 0;
        
        const existingAccordions = container.querySelectorAll('.accordion-item');
        const usedIndices = [];
        
        existingAccordions.forEach(accordion => {
            const titleInput = accordion.querySelector('input[name*="[title]"]');
            if (titleInput) {
                const match = titleInput.name.match(/accordions\[(\d+)\]\[title\]/);
                if (match) {
                    usedIndices.push(parseInt(match[1]));
                }
            }
        });
        
        // מצא את המספר הבא הזמין
        let nextIndex = 0;
        while (usedIndices.includes(nextIndex)) {
            nextIndex++;
        }
        
        return nextIndex;
    }
    
    updateAccordionCount() {
        const container = document.getElementById('accordions-container');
        if (container) {
            const existingAccordions = container.querySelectorAll('.accordion-item');
            this.accordionCount = existingAccordions.length;
        }
    }
    
    addAttributeWithDefaults() {
        // Add attribute with empty defaults - user fills everything
        this.addAttribute('', 'text', []);
    }
    
    addAttribute(defaultName = '', defaultType = 'text', defaultValues = []) {
        const container = document.getElementById('attributes-container');
        const attributeItem = document.createElement('div');
        attributeItem.className = 'attribute-item border border-gray-200 rounded-lg p-4 space-y-4';
        
        // Generate options for the select based on default type
        const typeOptions = ['text', 'color', 'image'].map(type => 
            `<option value="${type}" ${type === defaultType ? 'selected' : ''}>${
                type === 'text' ? 'טקסט' : 
                type === 'color' ? 'צבע' : 'תמונה'
            }</option>`
        ).join('');
        
        // Generate default values HTML
        let valuesHTML = '';
        if (defaultValues.length > 0) {
            valuesHTML = defaultValues.map((val, index) => `
                <div class="flex items-center gap-2">
                    <input type="text" name="attributes[${this.attributeCount}][values][${index}][value]" 
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="ערך (למשל: אדום, XL)" value="${val.value || ''}">
                    <input type="color" name="attributes[${this.attributeCount}][values][${index}][color]" 
                        class="w-8 h-8 border border-gray-300 rounded attribute-color ${defaultType === 'color' ? '' : 'hidden'}"
                        value="${val.color || '#000000'}">
                    <button type="button" class="attribute-image ${defaultType === 'image' ? '' : 'hidden'} px-2 py-1 text-sm bg-blue-50 text-blue-600 border border-blue-200 rounded hover:bg-blue-100 transition-colors"
                        onclick="uploadAttributeImage(this, ${this.attributeCount}, ${index})">
                        <i class="ri-image-add-line"></i>
                    </button>
                    <input type="hidden" name="attributes[${this.attributeCount}][values][${index}][image]" class="attribute-image-input" value="${val.image || ''}">
                    <button type="button" onclick="removeAttributeValue(this)" 
                        class="text-red-500 hover:text-red-700 p-1 flex items-center justify-center">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            `).join('');
        } else {
            valuesHTML = `
                <div class="flex items-center gap-2">
                    <input type="text" name="attributes[${this.attributeCount}][values][0][value]" 
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="ערך (למשל: אדום, XL)">
                    <input type="color" name="attributes[${this.attributeCount}][values][0][color]" 
                        class="w-8 h-8 border border-gray-300 rounded attribute-color ${defaultType === 'color' ? '' : 'hidden'}"
                        value="#000000">
                    <button type="button" class="attribute-image ${defaultType === 'image' ? '' : 'hidden'} px-2 py-1 text-sm bg-blue-50 text-blue-600 border border-blue-200 rounded hover:bg-blue-100 transition-colors"
                        onclick="uploadAttributeImage(this, ${this.attributeCount}, 0)">
                        <i class="ri-image-add-line"></i>
                    </button>
                    <input type="hidden" name="attributes[${this.attributeCount}][values][0][image]" class="attribute-image-input">
                    <button type="button" onclick="removeAttributeValue(this)" 
                        class="text-red-500 hover:text-red-700 p-1 flex items-center justify-center">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            `;
        }
        
        attributeItem.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex-1 grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">שם המאפיין</label>
                        <input type="text" name="attributes[${this.attributeCount}][name]" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                            placeholder="למשל: צבע, מידה" value="${defaultName}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">סוג</label>
                        <select name="attributes[${this.attributeCount}][type]" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            ${typeOptions}
                        </select>
                    </div>
                </div>
                <button type="button" onclick="this.closest('.attribute-item').remove()" 
                    class="text-red-500 hover:text-red-700 p-2 mr-4 mt-6 flex items-center justify-center">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
            
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <input type="checkbox" name="attributes[${this.attributeCount}][is_variant]" value="1"
                        id="is_variant_${this.attributeCount}" checked
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                    <label for="is_variant_${this.attributeCount}" class="text-sm font-medium text-gray-700">
                        משפיע על וריאציות (יוצר מוצרים נפרדים)
                    </label>
                </div>
            </div>
            
            <div class="attribute-values space-y-2">
                <label class="block text-sm font-medium text-gray-700">ערכים</label>
                <div class="values-container space-y-2">
                    ${valuesHTML}
                </div>
                <button type="button" onclick="addAttributeValue(this, ${this.attributeCount})" 
                    class="text-sm text-blue-600 hover:text-blue-700 flex items-center gap-2">
                    <div class="w-4 h-4 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="ri-add-line text-blue-600 text-xs"></i>
                    </div>
                    הוסף ערך
                </button>
            </div>
        `;
        
        container.appendChild(attributeItem);
        
        // Setup type change handler
        const typeSelect = attributeItem.querySelector('select[name*="[type]"]');
        typeSelect.addEventListener('change', (e) => {
            const colorInputs = attributeItem.querySelectorAll('.attribute-color');
            const imageInputs = attributeItem.querySelectorAll('.attribute-image');
            
            if (e.target.value === 'color') {
                colorInputs.forEach(input => input.classList.remove('hidden'));
                imageInputs.forEach(input => input.classList.add('hidden'));
            } else if (e.target.value === 'image') {
                colorInputs.forEach(input => input.classList.add('hidden'));
                imageInputs.forEach(input => input.classList.remove('hidden'));
            } else {
                colorInputs.forEach(input => input.classList.add('hidden'));
                imageInputs.forEach(input => input.classList.add('hidden'));
            }
        });
        
        this.attributeCount++;
        
        // עדכן את הגלרייה לפי מאפיינים אחרי הוספת מאפיין חדש
        if (typeof updateGalleryAttributeDropdown === 'function') {
            setTimeout(updateGalleryAttributeDropdown, 100);
        }
    }
    
    addBadge() {
        const container = document.getElementById('badges-container');
        const badgeItem = document.createElement('div');
        badgeItem.className = 'badge-item border border-gray-200 rounded-lg p-4 space-y-3';
        
        badgeItem.innerHTML = `
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium text-gray-700">מדבקה חדשה</h4>
                <button type="button" onclick="this.closest('.badge-item').remove()" 
                    class="text-red-500 hover:text-red-700 p-1">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">טקסט</label>
                    <input type="text" name="badges[${this.badgeCount}][text]" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="למשל: חדש, מבצע">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">מיקום</label>
                    <select name="badges[${this.badgeCount}][position]" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="top-right">מעלה ימין</option>
                        <option value="top-left">מעלה שמאל</option>
                        <option value="bottom-right">מטה ימין</option>
                        <option value="bottom-left">מטה שמאל</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">צבע טקסט</label>
                    <div class="relative">
                        <input type="color" name="badges[${this.badgeCount}][color]" value="#ffffff"
                            class="sr-only color-input" id="badge-text-color-${this.badgeCount}">
                        <button type="button" class="w-full h-12 border-2 border-gray-200 rounded-xl flex items-center justify-between px-4 hover:border-gray-300 transition-colors color-preview-btn"
                            data-target="badge-text-color-${this.badgeCount}" style="background-color: #ffffff;">
                            <span class="text-sm font-medium text-gray-700">בחר צבע</span>
                            <div class="w-6 h-6 rounded-full border-2 border-gray-300 shadow-sm" style="background-color: #ffffff;"></div>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">צבע רקע</label>
                    <div class="relative">
                        <input type="color" name="badges[${this.badgeCount}][background_color]" value="#3b82f6"
                            class="sr-only color-input" id="badge-bg-color-${this.badgeCount}">
                        <button type="button" class="w-full h-12 border-2 border-gray-200 rounded-xl flex items-center justify-between px-4 hover:border-gray-300 transition-colors color-preview-btn"
                            data-target="badge-bg-color-${this.badgeCount}" style="background-color: #3b82f6;">
                            <span class="text-sm font-medium text-white">בחר צבע</span>
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow-sm" style="background-color: #3b82f6;"></div>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        container.appendChild(badgeItem);
        this.badgeCount++;
    }
    
    // Add existing badge to container (for loading saved badges)
    addBadgeToContainer(text, textColor, backgroundColor, container, position = 'top-right') {
        const badgeItem = document.createElement('div');
        badgeItem.className = 'badge-item border border-gray-200 rounded-lg p-4 space-y-3';
        
        badgeItem.innerHTML = `
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium text-gray-700">מדבקה קיימת</h4>
                <button type="button" onclick="this.closest('.badge-item').remove()" 
                    class="text-red-500 hover:text-red-700 p-1">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">טקסט</label>
                    <input type="text" name="badges[${this.badgeCount}][text]" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="למשל: חדש, מבצע" value="${text}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">מיקום</label>
                    <select name="badges[${this.badgeCount}][position]" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="top-right" ${position === 'top-right' ? 'selected' : ''}>מעלה ימין</option>
                        <option value="top-left" ${position === 'top-left' ? 'selected' : ''}>מעלה שמאל</option>
                        <option value="bottom-right" ${position === 'bottom-right' ? 'selected' : ''}>מטה ימין</option>
                        <option value="bottom-left" ${position === 'bottom-left' ? 'selected' : ''}>מטה שמאל</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">צבע טקסט</label>
                    <div class="relative">
                        <input type="color" name="badges[${this.badgeCount}][color]" value="${textColor || '#ffffff'}"
                            class="sr-only color-input" id="badge-text-color-${this.badgeCount}">
                        <button type="button" class="w-full h-12 border-2 border-gray-200 rounded-xl flex items-center justify-between px-4 hover:border-gray-300 transition-colors color-preview-btn"
                            data-target="badge-text-color-${this.badgeCount}" style="background-color: ${textColor || '#ffffff'};">
                            <span class="text-sm font-medium" style="color: ${textColor === '#ffffff' || !textColor ? '#374151' : '#ffffff'};">בחר צבע</span>
                            <div class="w-6 h-6 rounded-full border-2 border-gray-300 shadow-sm" style="background-color: ${textColor || '#ffffff'};"></div>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">צבע רקע</label>
                    <div class="relative">
                        <input type="color" name="badges[${this.badgeCount}][background_color]" value="${backgroundColor || '#3b82f6'}"
                            class="sr-only color-input" id="badge-bg-color-${this.badgeCount}">
                        <button type="button" class="w-full h-12 border-2 border-gray-200 rounded-xl flex items-center justify-between px-4 hover:border-gray-300 transition-colors color-preview-btn"
                            data-target="badge-bg-color-${this.badgeCount}" style="background-color: ${backgroundColor || '#3b82f6'};">
                            <span class="text-sm font-medium text-white">בחר צבע</span>
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow-sm" style="background-color: ${backgroundColor || '#3b82f6'};"></div>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        container.appendChild(badgeItem);
        this.badgeCount++;
    }
    
    // Setup modern color pickers
    setupColorPickers() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('color-preview-btn') || e.target.closest('.color-preview-btn')) {
                const btn = e.target.classList.contains('color-preview-btn') ? e.target : e.target.closest('.color-preview-btn');
                const targetId = btn.getAttribute('data-target');
                const hiddenInput = document.getElementById(targetId);
                if (hiddenInput) {
                    hiddenInput.click();
                }
            }
        });
        
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('color-input')) {
                const previewBtn = document.querySelector(`[data-target="${e.target.id}"]`);
                const previewCircle = previewBtn?.querySelector('.rounded-full:last-child');
                if (previewBtn && previewCircle) {
                    previewBtn.style.backgroundColor = e.target.value;
                    previewCircle.style.backgroundColor = e.target.value;
                    // Update text color based on brightness
                    const span = previewBtn.querySelector('span');
                    if (span) {
                        const brightness = this.getColorBrightness(e.target.value);
                        span.style.color = brightness > 128 ? '#374151' : '#ffffff';
                    }
                }
            }
        });
    }
    
    // Get color brightness for text contrast
    getColorBrightness(hex) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return ((r * 299) + (g * 587) + (b * 114)) / 1000;
    }
    
    // Tags input functionality
    setupTagsInput() {
        const tagInput = document.getElementById('tag-input');
        const tagsContainer = document.getElementById('tags-container');
        const hiddenTagsInput = document.getElementById('tags');
        
        if (!tagInput || !tagsContainer || !hiddenTagsInput) return;
        
        let tags = [];
        
        const updateHiddenInput = () => {
            hiddenTagsInput.value = tags.join(',');
        };
        
        const createTagBubble = (tagText) => {
            const tagBubble = document.createElement('div');
            tagBubble.className = 'inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full border border-blue-200 hover:bg-blue-200 transition-colors';
            tagBubble.innerHTML = `
                <span>${tagText}</span>
                <button type="button" class="text-blue-600 hover:text-blue-800 hover:bg-blue-300 rounded-full w-4 h-4 flex items-center justify-center text-xs font-bold transition-colors" 
                    onclick="this.closest('div').remove(); window.productForm.removeTag('${tagText}')">
                    ×
                </button>
            `;
            return tagBubble;
        };
        
        const addTag = (tagText) => {
            tagText = tagText.trim();
            if (tagText && !tags.includes(tagText)) {
                tags.push(tagText);
                const tagBubble = createTagBubble(tagText);
                tagsContainer.appendChild(tagBubble);
                updateHiddenInput();
            }
        };
        
        this.removeTag = (tagText) => {
            tags = tags.filter(tag => tag !== tagText);
            updateHiddenInput();
        };
        
        // Handle input events
        tagInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                addTag(tagInput.value);
                tagInput.value = '';
            }
        });
        
        tagInput.addEventListener('blur', () => {
            if (tagInput.value.trim()) {
                addTag(tagInput.value);
                tagInput.value = '';
            }
        });
        
        // Handle paste events to support multiple tags
        tagInput.addEventListener('paste', (e) => {
            setTimeout(() => {
                const pastedText = tagInput.value;
                const newTags = pastedText.split(/[,\n\r]/).filter(tag => tag.trim());
                
                if (newTags.length > 1) {
                    newTags.forEach(tag => addTag(tag));
                    tagInput.value = '';
                }
            }, 0);
        });
    }
    
    // מוצרים מומלצים
    addRecommendedProduct() {
        const container = document.getElementById('recommended-products');
        if (!container) {
            console.error('Recommended products container not found');
            return;
        }
        
        const div = document.createElement('div');
        div.className = 'flex items-center gap-3 p-3 border border-gray-200 rounded-lg';
        div.innerHTML = `
            <input type="text" name="related_products[]" 
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm product-search"
                placeholder="חפש מוצר מומלץ... (התחל להקליד)">
            <input type="hidden" name="related_types[]" value="recommended">
            <button type="button" onclick="this.closest('.flex').remove()" 
                class="text-red-500 hover:text-red-700 p-1">
                <i class="ri-close-line"></i>
            </button>
        `;
        container.appendChild(div);
        
        // הוסף חיפוש מוצרים לשדה החדש
        this.setupProductSearch(div.querySelector('.product-search'));
    }

    // לקוחות שרכשו גם
    addFrequentlyBoughtProduct() {
        const container = document.getElementById('frequently-bought-products');
        if (!container) {
            console.error('Frequently bought products container not found');
            return;
        }
        
        const div = document.createElement('div');
        div.className = 'flex items-center gap-3 p-3 border border-gray-200 rounded-lg';
        div.innerHTML = `
            <input type="text" name="related_products[]" 
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm product-search"
                placeholder="חפש מוצר שנרכש יחד... (התחל להקליד)">
            <input type="hidden" name="related_types[]" value="frequently_bought">
            <button type="button" onclick="this.closest('.flex').remove()" 
                class="text-red-500 hover:text-red-700 p-1">
                <i class="ri-close-line"></i>
            </button>
        `;
        container.appendChild(div);
        
        // הוסף חיפוש מוצרים לשדה החדש
        this.setupProductSearch(div.querySelector('.product-search'));
    }

    // השלם את הלוק
    addCompleteLookProduct() {
        const container = document.getElementById('complete-look-products');
        if (!container) {
            console.error('Complete look products container not found');
            return;
        }
        
        const div = document.createElement('div');
        div.className = 'flex items-center gap-3 p-3 border border-gray-200 rounded-lg';
        div.innerHTML = `
            <input type="text" name="related_products[]" 
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm product-search"
                placeholder="חפש מוצר להשלמת הלוק... (התחל להקליד)">
            <input type="hidden" name="related_types[]" value="complete_look">
            <button type="button" onclick="this.closest('.flex').remove()" 
                class="text-red-500 hover:text-red-700 p-1">
                <i class="ri-close-line"></i>
            </button>
        `;
        container.appendChild(div);
        
        // הוסף חיפוש מוצרים לשדה החדש
        this.setupProductSearch(div.querySelector('.product-search'));
    }
    
    // פונקציה לאתחול חיפוש מוצרים בשדות קיימים
    initExistingProductSearches() {
        const productSearchInputs = document.querySelectorAll('.product-search');
        console.log('Found', productSearchInputs.length, 'product search inputs'); // דיבוג
        productSearchInputs.forEach(input => {
            console.log('Setting up search for input:', input); // דיבוג
            this.setupProductSearch(input);
        });
    }
    
    // פונקציה לחיפוש מוצרים
    setupProductSearch(input) {
        if (!input) return;
        
        const self = this;
        let timeout;
        
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                const existingList = this.parentNode.querySelector('.search-results');
                if (existingList) existingList.remove();
                return;
            }
            
            timeout = setTimeout(function() {
                console.log('Starting search for query:', query); // דיבוג
                self.searchProducts(query, input);
            }, 300);
        });
        
        // הוסף אירוע focus לשדה החיפוש
        input.addEventListener('focus', function() {
            if (this.value.trim().length >= 2) {
                self.searchProducts(this.value.trim(), input);
            }
        });
        
        // הוסף אירוע Enter לחיפוש מיידי
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = this.value.trim();
                if (query.length >= 2) {
                    clearTimeout(timeout);
                    self.searchProducts(query, input);
                }
            }
            
            // אם לחצו על חץ למטה, עבור לתוצאה הראשונה
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                const firstResult = input.parentNode.querySelector('.search-results > div:first-child');
                if (firstResult) {
                    firstResult.click();
                }
            }
        });
    }
    
    // חיפוש מוצרים מהשרת
    searchProducts(query, input) {
        const self = this;
        
        // בדיקה אם יש תוצאה ב-cache
        if (this.searchCache.has(query)) {
            const cachedResults = this.searchCache.get(query);
            self.showProductSearchResults(input, cachedResults);
            return;
        }
        
        // הצג הודעת loading
        self.showLoadingResults(input);
        
        fetch(`/admin/api/search_products.php?q=${encodeURIComponent(query)}&limit=8`)
            .then(response => response.json())
            .then(data => {
                console.log('Search API response:', data); // דיבוג
                
                if (data.success && data.products) {
                    const results = data.products.map(product => ({
                        id: product.id,
                        name: product.name,
                        display_name: product.display_name,
                        sku: product.sku,
                        price: product.price,
                        status: product.status
                    }));
                    
                    console.log('Formatted results:', results); // דיבוג
                    
                    // שמור ב-cache
                    this.searchCache.set(query, results);
                    
                    // נקה cache אם הוא גדול מדי
                    if (this.searchCache.size > 50) {
                        const firstKey = this.searchCache.keys().next().value;
                        this.searchCache.delete(firstKey);
                    }
                    
                    self.showProductSearchResults(input, results);
                } else {
                    console.log('No products found or API error:', data); // דיבוג
                    self.showProductSearchResults(input, []);
                }
            })
            .catch(error => {
                console.error('Error searching products:', error);
                self.showErrorResults(input, 'שגיאה בחיפוש מוצרים');
            });
    }
    
    // הצג הודעת loading
    showLoadingResults(input) {
        const existingList = input.parentNode.querySelector('.search-results');
        if (existingList) existingList.remove();
        
        const loadingList = document.createElement('div');
        loadingList.className = 'search-results absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-b-lg shadow-lg z-10';
        loadingList.innerHTML = `
            <div class="px-3 py-2 text-gray-500 text-sm flex items-center gap-2">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                מחפש מוצרים...
            </div>
        `;
        
        input.parentNode.style.position = 'relative';
        input.parentNode.appendChild(loadingList);
    }
    
    // הצג הודעת שגיאה
    showErrorResults(input, message) {
        const existingList = input.parentNode.querySelector('.search-results');
        if (existingList) existingList.remove();
        
        const errorList = document.createElement('div');
        errorList.className = 'search-results absolute top-full left-0 right-0 bg-white border border-red-300 rounded-b-lg shadow-lg z-10';
        errorList.innerHTML = `
            <div class="px-3 py-2 text-red-600 text-sm flex items-center gap-2">
                <i class="ri-error-warning-line"></i>
                ${message}
            </div>
        `;
        
        input.parentNode.style.position = 'relative';
        input.parentNode.appendChild(errorList);
        
        // הסר את הודעת השגיאה אחרי 3 שניות
        setTimeout(() => {
            if (errorList.parentNode) {
                errorList.remove();
            }
        }, 3000);
    }
    
    showProductSearchResults(input, results) {
        const existingList = input.parentNode.querySelector('.search-results');
        if (existingList) existingList.remove();
        
        if (results.length === 0) {
            // הצג הודעה אם אין תוצאות
            const noResultsList = document.createElement('div');
            noResultsList.className = 'search-results absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-b-lg shadow-lg z-10';
            noResultsList.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">לא נמצאו מוצרים</div>';
            
            input.parentNode.style.position = 'relative';
            input.parentNode.appendChild(noResultsList);
            
            setTimeout(() => {
                if (noResultsList.parentNode) {
                    noResultsList.remove();
                }
            }, 2000);
            
            return;
        }
        
        const resultsList = document.createElement('div');
        resultsList.className = 'search-results absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-b-lg shadow-lg z-10 max-h-80 overflow-y-auto';
        
        results.forEach(function(product) {
            const item = document.createElement('div');
            item.className = 'px-3 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0 flex items-center gap-3';
            
            // תמונת המוצר
            const imageContainer = document.createElement('div');
            imageContainer.className = 'flex-shrink-0 w-12 h-12 bg-gray-100 rounded-lg overflow-hidden';
            
            if (product.image) {
                const productImage = document.createElement('img');
                productImage.src = product.image;
                productImage.alt = product.name;
                productImage.className = 'w-full h-full object-cover';
                productImage.onerror = function() {
                    this.style.display = 'none';
                    const placeholder = document.createElement('div');
                    placeholder.className = 'w-full h-full bg-gray-200 flex items-center justify-center';
                    placeholder.innerHTML = '<i class="ri-image-line text-gray-400"></i>';
                    imageContainer.appendChild(placeholder);
                };
                imageContainer.appendChild(productImage);
            } else {
                const placeholder = document.createElement('div');
                placeholder.className = 'w-full h-full bg-gray-200 flex items-center justify-center';
                placeholder.innerHTML = '<i class="ri-image-line text-gray-400"></i>';
                imageContainer.appendChild(placeholder);
            }
            
            item.appendChild(imageContainer);
            
            // מידע המוצר
            const productInfo = document.createElement('div');
            productInfo.className = 'flex-1 min-w-0';
            
            // שם המוצר
            const productName = document.createElement('div');
            productName.className = 'font-medium text-gray-900 truncate';
            productName.textContent = product.name;
            productInfo.appendChild(productName);
            
            // פרטים נוספים
            const productDetails = document.createElement('div');
            productDetails.className = 'flex items-center gap-2 mt-1 text-sm text-gray-500';
            
            if (product.sku) {
                const skuSpan = document.createElement('span');
                skuSpan.textContent = `SKU: ${product.sku}`;
                productDetails.appendChild(skuSpan);
            }
            
            if (product.category_name) {
                if (product.sku) {
                    const separator = document.createElement('span');
                    separator.textContent = '•';
                    productDetails.appendChild(separator);
                }
                const categorySpan = document.createElement('span');
                categorySpan.textContent = product.category_name;
                productDetails.appendChild(categorySpan);
            }
            
            productInfo.appendChild(productDetails);
            
            // תיאור קצר
            if (product.description) {
                const descriptionDiv = document.createElement('div');
                descriptionDiv.className = 'text-xs text-gray-400 mt-1 truncate';
                descriptionDiv.textContent = product.description;
                productInfo.appendChild(descriptionDiv);
            }
            
            item.appendChild(productInfo);
            
            // מחיר וסטטוס
            const priceStatusContainer = document.createElement('div');
            priceStatusContainer.className = 'flex-shrink-0 text-left';
            
            if (product.price) {
                const productPrice = document.createElement('div');
                productPrice.className = 'text-sm font-medium text-blue-600';
                productPrice.textContent = `₪${product.price}`;
                priceStatusContainer.appendChild(productPrice);
            }
            
            // סטטוס מלאי
            if (product.stock_status === 'out_of_stock') {
                const stockBadge = document.createElement('span');
                stockBadge.className = 'inline-block mt-1 px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full';
                stockBadge.textContent = 'אזל מהמלאי';
                priceStatusContainer.appendChild(stockBadge);
            } else if (product.stock_status === 'low_stock') {
                const stockBadge = document.createElement('span');
                stockBadge.className = 'inline-block mt-1 px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full';
                stockBadge.textContent = 'מלאי נמוך';
                priceStatusContainer.appendChild(stockBadge);
            }
            
            // הוסף סטטוס אם המוצר בטיוטה
            if (product.status === 'draft') {
                const statusBadge = document.createElement('span');
                statusBadge.className = 'inline-block mt-1 px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full';
                statusBadge.textContent = 'טיוטה';
                priceStatusContainer.appendChild(statusBadge);
            }
            
            item.appendChild(priceStatusContainer);
            
            item.addEventListener('click', function() {
                input.value = product.name;
                resultsList.remove();
            });
            resultsList.appendChild(item);
        });
        
        input.parentNode.style.position = 'relative';
        input.parentNode.appendChild(resultsList);
        
        const hideResults = function(e) {
            if (!resultsList.contains(e.target) && e.target !== input) {
                resultsList.remove();
                document.removeEventListener('click', hideResults);
            }
        };
        document.addEventListener('click', hideResults);
    }
    
    // Upsell Products
    addUpsellProduct() {
        const container = document.getElementById('upsell-products');
        if (!container) {
            console.error('Upsell products container not found');
            return;
        }
        
        const div = document.createElement('div');
        div.className = 'flex items-center gap-3 p-3 border border-gray-200 rounded-lg';
        div.innerHTML = `
            <input type="text" name="upsell_products[]" 
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm product-search"
                placeholder="חפש מוצר לשדרוג...">
            <input type="text" name="upsell_descriptions[]" 
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                placeholder="סיבה לשדרוג">
            <button type="button" onclick="this.closest('.flex').remove()" 
                class="text-red-500 hover:text-red-700 p-1">
                <div class="w-5 h-5 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="ri-close-line text-red-500 text-xs"></i>
                </div>
            </button>
        `;
        container.appendChild(div);
        
        // הוסף חיפוש מוצרים לשדה החדש
        this.setupProductSearch(div.querySelector('.product-search'));
    }
    
    // Product Bundles
    addProductBundle() {
        const container = document.getElementById('product-bundles');
        if (!container) {
            console.error('Product bundles container not found');
            return;
        }
        
        const bundleIndex = container.children.length;
        const div = document.createElement('div');
        div.className = 'border border-gray-200 rounded-lg p-4 bundle-item';
        div.innerHTML = `
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-medium text-gray-800">חבילה #${bundleIndex + 1}</h4>
                <button type="button" onclick="this.closest('.bundle-item').remove()" 
                    class="text-red-500 hover:text-red-700 p-1">
                    <i class="ri-close-line"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">שם החבילה</label>
                    <input type="text" name="bundles[${bundleIndex}][title]" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="שם החבילה">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">מחיר החבילה</label>
                    <input type="number" name="bundles[${bundleIndex}][price]" step="0.01" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="0.00">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">תיאור החבילה</label>
                <textarea name="bundles[${bundleIndex}][description]" rows="2" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    placeholder="תיאור קצר של החבילה..."></textarea>
            </div>
            
            <div class="border-t border-gray-200 pt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">מוצרים בחבילה</label>
                <div class="bundle-products-${bundleIndex} space-y-2">
                    <div class="flex items-center gap-3">
                        <input type="text" name="bundles[${bundleIndex}][products][]" 
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm product-search"
                            placeholder="חפש מוצר להוספה...">
                        <input type="number" name="bundles[${bundleIndex}][quantities][]" min="1" value="1" 
                            class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                            placeholder="כמות">
                        <button type="button" onclick="this.closest('.flex').remove()" 
                            class="text-red-500 hover:text-red-700 p-1">
                            <div class="w-5 h-5 bg-red-100 rounded-full flex items-center justify-center">
                                <i class="ri-close-line text-red-500 text-xs"></i>
                            </div>
                        </button>
                    </div>
                </div>
                <button type="button" onclick="window.productForm.addBundleProduct(${bundleIndex})" 
                    class="mt-2 text-sm text-purple-600 hover:text-purple-700 flex items-center gap-2">
                    <div class="w-4 h-4 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="ri-add-line text-purple-600 text-xs"></i>
                    </div>
                    הוסף מוצר נוסף
                </button>
            </div>
        `;
        container.appendChild(div);
        
        // הוסף חיפוש מוצרים לשדה הראשון בחבילה
        this.setupProductSearch(div.querySelector('.product-search'));
    }
    
    addBundleProduct(bundleIndex) {
        const container = document.querySelector(`.bundle-products-${bundleIndex}`);
        if (!container) {
            console.error(`Bundle products container for bundle ${bundleIndex} not found`);
            return;
        }
        
        const div = document.createElement('div');
        div.className = 'flex items-center gap-3';
        div.innerHTML = `
            <input type="text" name="bundles[${bundleIndex}][products][]" 
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm product-search"
                placeholder="חפש מוצר להוספה...">
            <input type="number" name="bundles[${bundleIndex}][quantities][]" min="1" value="1" 
                class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                placeholder="כמות">
            <button type="button" onclick="this.closest('.flex').remove()" 
                class="text-red-500 hover:text-red-700 p-1">
                <div class="w-5 h-5 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="ri-close-line text-red-500 text-xs"></i>
                </div>
            </button>
        `;
        container.appendChild(div);
        
        // הוסף חיפוש מוצרים לשדה החדש
        this.setupProductSearch(div.querySelector('.product-search'));
    }
}

// Helper function for adding attribute values
function addAttributeValue(button, attributeIndex) {
    const container = button.previousElementSibling;
    const valueCount = container.children.length;
    const newValue = document.createElement('div');
    newValue.className = 'flex items-center gap-2';
    
    newValue.innerHTML = `
        <input type="text" name="attributes[${attributeIndex}][values][${valueCount}][value]" 
            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
            placeholder="ערך (למשל: אדום, XL)">
        <input type="color" name="attributes[${attributeIndex}][values][${valueCount}][color]" 
            class="w-8 h-8 border border-gray-300 rounded attribute-color hidden"
            value="#000000">
        <button type="button" class="attribute-image hidden px-2 py-1 text-sm bg-blue-50 text-blue-600 border border-blue-200 rounded hover:bg-blue-100 transition-colors"
            onclick="uploadAttributeImage(this, ${attributeIndex}, ${valueCount})">
            <i class="ri-image-add-line"></i>
        </button>
        <input type="hidden" name="attributes[${attributeIndex}][values][${valueCount}][image]" class="attribute-image-input">
        <button type="button" onclick="removeAttributeValue(this)" 
            class="text-red-500 hover:text-red-700 p-1 flex items-center justify-center">
            <div class="w-5 h-5 bg-red-100 rounded-full flex items-center justify-center">
                <i class="ri-close-line text-red-500 text-xs"></i>
            </div>
        </button>
    `;
    
    container.appendChild(newValue);
    
    // Show appropriate inputs based on type
    const attributeItem = button.closest('.attribute-item');
    const typeSelect = attributeItem.querySelector('select[name*="[type]"]');
    if (typeSelect) {
        const colorInputs = newValue.querySelectorAll('.attribute-color');
        const imageInputs = newValue.querySelectorAll('.attribute-image');
        
        if (typeSelect.value === 'color') {
            colorInputs.forEach(input => input.classList.remove('hidden'));
        } else if (typeSelect.value === 'image') {
            imageInputs.forEach(input => input.classList.remove('hidden'));
        }
    }
    
    // Update gallery attributes after adding new value
    if (typeof updateGalleryAttributeDropdown === 'function') {
        setTimeout(updateGalleryAttributeDropdown, 100);
    }
}

// Global functions for backward compatibility
window.addExistingMediaToPreview = function(media, container) {
    const mediaItem = document.createElement('div');
    mediaItem.className = 'relative group border border-gray-200 rounded-lg overflow-hidden';
    
    // קבלת מספר הקרן לפי כמות הילדים הקיימים
    const currentIndex = container.children.length;
    
    const isPrimary = media.is_primary && media.is_primary == 1;
    
    mediaItem.innerHTML = `
        <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden rounded-lg">
            <img src="${media.thumbnail_url || media.url}" alt="${media.alt_text || ''}" class="w-full h-full object-cover">
        </div>
        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-200"></div>
        
        <!-- אייקון מחיקה - שמאל מעלה -->
        <button type="button" onclick="removeGalleryImage(this);" 
            class="absolute top-2 left-2 w-8 h-8 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-red-600 flex items-center justify-center shadow-lg">
            <i class="ri-delete-bin-line text-sm"></i>
        </button>
        
        <!-- אייקון תמונה ראשית - ימין מעלה -->
        <button type="button" onclick="setPrimaryImage(this);" 
            class="absolute top-2 right-2 w-8 h-8 ${isPrimary ? 'bg-yellow-500 text-white' : 'bg-white text-gray-600'} rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-yellow-500 hover:text-white flex items-center justify-center shadow-lg">
            <i class="ri-star-${isPrimary ? 'fill' : 'line'} text-sm"></i>
        </button>
        
        <!-- מספר סידורי -->
        <div class="absolute bottom-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-all duration-200">
            ${currentIndex + 1}
        </div>
        
        <input type="hidden" name="media[${currentIndex}][url]" value="${media.url}">
        <input type="hidden" name="media[${currentIndex}][thumbnail_url]" value="${media.thumbnail_url || media.url}">
        <input type="hidden" name="media[${currentIndex}][type]" value="${media.type || 'image'}">
        <input type="hidden" name="media[${currentIndex}][alt_text]" value="${media.alt_text || ''}">
        <input type="hidden" name="media[${currentIndex}][sort_order]" value="${media.sort_order || 0}">
        <input type="hidden" name="media[${currentIndex}][is_primary]" value="${media.is_primary || 0}">
    `;
    
    container.appendChild(mediaItem);
    
    // עדכון מספר המדיה אם יש instance
    if (window.ProductFormInstance) {
        window.ProductFormInstance.mediaCount = Math.max(window.ProductFormInstance.mediaCount, currentIndex + 1);
    }
};

// פונקציות לניהול גלרייה
window.removeGalleryImage = function(button) {
    const mediaItem = button.closest('.relative');
    const container = mediaItem.parentNode;
    
    // Remove the image
    mediaItem.remove();
    
    // Hide container if empty
    if (container && container.children.length === 0) {
        container.classList.add('hidden');
    }
    
    // Update image numbers
    updateImageNumbers(container);
};

window.setPrimaryImage = function(button) {
    const container = button.closest('.relative').parentNode;
    
    // Remove primary status from all images
    container.querySelectorAll('.relative').forEach((item, index) => {
        const starBtn = item.querySelector('[onclick*="setPrimaryImage"]');
        const hiddenInput = item.querySelector('input[name*="[is_primary]"]');
        
        if (starBtn && hiddenInput) {
            starBtn.className = 'absolute top-2 right-2 w-8 h-8 bg-white text-gray-600 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-yellow-500 hover:text-white flex items-center justify-center shadow-lg';
            starBtn.innerHTML = '<i class="ri-star-line text-sm"></i>';
            hiddenInput.value = '0';
        }
    });
    
    // Set clicked image as primary
    const currentItem = button.closest('.relative');
    const currentHiddenInput = currentItem.querySelector('input[name*="[is_primary]"]');
    
    if (currentHiddenInput) {
        button.className = 'absolute top-2 right-2 w-8 h-8 bg-yellow-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-yellow-500 hover:text-white flex items-center justify-center shadow-lg';
        button.innerHTML = '<i class="ri-star-fill text-sm"></i>';
        currentHiddenInput.value = '1';
    }
};

function updateImageNumbers(container) {
    if (!container) return;
    
    container.querySelectorAll('.relative').forEach((item, index) => {
        const numberDiv = item.querySelector('.absolute.bottom-2.left-2');
        if (numberDiv) {
            numberDiv.textContent = index + 1;
        }
    });
}

// פונקציות נוספות לתאימות לאחור
window.addBadgeToContainer = function(text, textColor, backgroundColor, container, position = 'top-right') {
    if (window.ProductFormInstance) {
        // יצירת badge element
        const badgeItem = document.createElement('div');
        badgeItem.className = 'badge-item border border-gray-200 rounded-lg p-4 space-y-3';
        
        badgeItem.innerHTML = `
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium text-gray-700">מדבקה קיימת</h4>
                <button type="button" onclick="this.closest('.badge-item').remove()" 
                    class="text-red-500 hover:text-red-700 p-1">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">טקסט</label>
                    <input type="text" name="badges[${window.ProductFormInstance.badgeCount}][text]" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="למשל: חדש, מבצע" value="${text}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">מיקום</label>
                    <select name="badges[${window.ProductFormInstance.badgeCount}][position]" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="top-right" ${position === 'top-right' ? 'selected' : ''}>מעלה ימין</option>
                        <option value="top-left" ${position === 'top-left' ? 'selected' : ''}>מעלה שמאל</option>
                        <option value="bottom-right" ${position === 'bottom-right' ? 'selected' : ''}>מטה ימין</option>
                        <option value="bottom-left" ${position === 'bottom-left' ? 'selected' : ''}>מטה שמאל</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">צבע טקסט</label>
                    <div class="relative">
                        <input type="color" name="badges[${window.ProductFormInstance.badgeCount}][color]" value="${textColor || '#ffffff'}"
                            class="sr-only color-input" id="badge-text-color-${window.ProductFormInstance.badgeCount}">
                        <button type="button" class="w-full h-12 border-2 border-gray-200 rounded-xl flex items-center justify-between px-4 hover:border-gray-300 transition-colors color-preview-btn"
                            data-target="badge-text-color-${window.ProductFormInstance.badgeCount}" style="background-color: ${textColor || '#ffffff'};">
                            <span class="text-sm font-medium" style="color: ${textColor === '#ffffff' || !textColor ? '#374151' : '#ffffff'};">בחר צבע</span>
                            <div class="w-6 h-6 rounded-full border-2 border-gray-300 shadow-sm" style="background-color: ${textColor || '#ffffff'};"></div>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">צבע רקע</label>
                    <div class="relative">
                        <input type="color" name="badges[${window.ProductFormInstance.badgeCount}][background_color]" value="${backgroundColor || '#3b82f6'}"
                            class="sr-only color-input" id="badge-bg-color-${window.ProductFormInstance.badgeCount}">
                        <button type="button" class="w-full h-12 border-2 border-gray-200 rounded-xl flex items-center justify-between px-4 hover:border-gray-300 transition-colors color-preview-btn"
                            data-target="badge-bg-color-${window.ProductFormInstance.badgeCount}" style="background-color: ${backgroundColor || '#3b82f6'};">
                            <span class="text-sm font-medium text-white">בחר צבע</span>
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow-sm" style="background-color: ${backgroundColor || '#3b82f6'};"></div>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        container.appendChild(badgeItem);
        window.ProductFormInstance.badgeCount++;
    }
};

window.addAccordionToContainer = function(title, content, container) {
    if (window.ProductFormInstance && container) {
        window.ProductFormInstance.addAccordion();
        
        // עדכון הערכים של האקורדיון שנוסף
        const accordionItems = container.querySelectorAll('.accordion-item');
        const lastAccordion = accordionItems[accordionItems.length - 1];
        
        if (lastAccordion) {
            const titleInput = lastAccordion.querySelector('input[name*="[title]"]');
            const contentInput = lastAccordion.querySelector('textarea[name*="[content]"]');
            
            if (titleInput) titleInput.value = title;
            if (contentInput) contentInput.value = content;
        }
    }
};

window.addAttributeToContainer = function(name, type, values, container) {
    if (window.ProductFormInstance) {
        window.ProductFormInstance.addAttribute(name, type, values);
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // יצירת instance רק אם לא קיים כבר
    if (!window.productForm && !window.ProductFormInstance) {
        window.productForm = new ProductForm();
    }
});

// Form validation before submit
document.getElementById('product-form')?.addEventListener('submit', (e) => {
    // Remove empty accordions before submit
    const accordionItems = document.querySelectorAll('.accordion-item');
    accordionItems.forEach(item => {
        const titleInput = item.querySelector('input[name*="[title]"]');
        const contentInput = item.querySelector('textarea[name*="[content]"]');
        
        if (titleInput && contentInput) {
            const title = titleInput.value.trim();
            const content = contentInput.value.trim();
            
            // Remove accordion if both title and content are empty
            if (!title && !content) {
                item.remove();
            }
        }
    });
    
    // Continue with original validation
    const name = document.getElementById('name').value.trim();
    const price = document.getElementById('price').value;
    
    if (!name) {
        e.preventDefault();
        alert('שם המוצר הוא שדה חובה');
        document.getElementById('name').focus();
        return;
    }
    
    if (!price || price <= 0) {
        e.preventDefault();
        alert('יש להזין מחיר תקין');
        document.getElementById('price').focus();
        return;
    }
});

// Accordion toggle function
function toggleAccordion(id) {
    // Try to find the content element with or without "-content" suffix
    let content = document.getElementById(id);
    if (!content) {
        content = document.getElementById(id + '-content');
    }
    
    // Try to find the icon element with different possible suffixes
    let icon = document.getElementById(id + '-icon');
    if (!icon) {
        icon = document.getElementById(id + '-arrow');
    }
    
    // Only proceed if we found the content element
    if (content) {
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            if (icon) icon.style.transform = 'rotate(180deg)';
        } else {
            content.classList.add('hidden');
            if (icon) icon.style.transform = 'rotate(0deg)';
        }
    } else {
        console.warn('Accordion element not found:', id);
    }
}

// הפונקציה צריכה להיות זמינה גלובלית
window.toggleAccordion = toggleAccordion;

// Category Modal Functions
function openNewCategoryModal() {
    document.getElementById('newCategoryModal').classList.remove('hidden');
    document.getElementById('new_category_name').focus();
}

function closeNewCategoryModal() {
    document.getElementById('newCategoryModal').classList.add('hidden');
    document.getElementById('newCategoryForm').reset();
    // Reset loading states
    const submitBtn = document.querySelector('#newCategoryForm button[type="submit"]');
    submitBtn.disabled = false;
    submitBtn.querySelector('.submit-text').classList.remove('hidden');
    submitBtn.querySelector('.loading-text').classList.add('hidden');
}

// Handle new category form submission
document.addEventListener('DOMContentLoaded', function() {
    const newCategoryForm = document.getElementById('newCategoryForm');
    if (newCategoryForm) {
        newCategoryForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const submitText = submitBtn.querySelector('.submit-text');
            const loadingText = submitBtn.querySelector('.loading-text');
            
            // Show loading state
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            loadingText.classList.remove('hidden');
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('/admin/api/create_category.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        name: formData.get('name'),
                        description: formData.get('description')
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Add new category to the list
                    const categoriesContainer = document.querySelector('.space-y-2.max-h-48');
                    const newCategoryDiv = document.createElement('div');
                    newCategoryDiv.className = 'flex items-center gap-3';
                    newCategoryDiv.innerHTML = `
                        <input type="checkbox" id="category_${result.category.id}" 
                            name="categories[]" value="${result.category.id}" checked
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <label for="category_${result.category.id}" class="text-sm text-gray-700">
                            ${result.category.name}
                        </label>
                    `;
                    categoriesContainer.appendChild(newCategoryDiv);
                    
                    // Close modal
                    closeNewCategoryModal();
                    
                    // Show success message
                    showNotification('הקטגוריה נוצרה בהצלחה!', 'success');
                } else {
                    throw new Error(result.message || 'שגיאה ביצירת הקטגוריה');
                }
            } catch (error) {
                console.error('Error creating category:', error);
                showNotification('שגיאה ביצירת הקטגוריה: ' + error.message, 'error');
            } finally {
                // Reset loading state
                submitBtn.disabled = false;
                submitText.classList.remove('hidden');
                loadingText.classList.add('hidden');
            }
        });
    }
});

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white transition-all transform translate-x-full ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        'bg-blue-500'
    }`;
    notification.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="ri-${type === 'success' ? 'check' : type === 'error' ? 'error-warning' : 'information'}-line"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
} 