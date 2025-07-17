// Variants and Attributes Management for Product Form

// Update variants table when attributes change
function updateVariantsTable() {
    // בדיקה שלא מתבצעת הפעלה מיותרת
    if (window.variantsTableUpdating) return;
    window.variantsTableUpdating = true;
    
    setTimeout(() => {
        window.variantsTableUpdating = false;
    }, 100);
    
    // Get all variant attributes (those with is_variant checked)
    const variantAttributes = [];
    const attributeItems = document.querySelectorAll('.attribute-item');
    
    // אם אין מאפיינים בכלל, צא מוקדם
    if (attributeItems.length === 0) {
        hideVariantsTable();
        return;
    }
    
    attributeItems.forEach((item, index) => {
        const isVariantCheckbox = item.querySelector('input[name*="[is_variant]"]');
        if (isVariantCheckbox && isVariantCheckbox.checked) {
            const nameInput = item.querySelector('input[name*="[name]"]');
            const valueInputs = item.querySelectorAll('input[name*="[values]"][name*="[value]"]');
            
            if (nameInput && nameInput.value.trim()) {
                const values = [];
                valueInputs.forEach(input => {
                    if (input.value.trim()) {
                        values.push(input.value.trim());
                    }
                });
                
                if (values.length > 0) {
                    variantAttributes.push({
                        name: nameInput.value.trim(),
                        values: values,
                        index: index
                    });
                }
            }
        }
    });
    
    // Generate combinations
    if (variantAttributes.length > 0) {
        generateVariantsTable(variantAttributes);
    } else {
        hideVariantsTable();
    }
}

function generateVariantsTable(attributes) {
    const combinations = getVariantCombinations(attributes);
    const container = document.getElementById('variants-section');
    
    // אם אין קומבינציות תקינות, לא נעשה כלום
    if (!combinations || combinations.length === 0) {
        hideVariantsTable();
        return;
    }
    
    // בדוק אם יש כבר טבלה עם אותו מספר שורות (למניעת כפילות)
    const existingTables = container.querySelectorAll('.variants-table-container');
    if (existingTables.length > 0) {
        const existingRows = container.querySelectorAll('tbody tr');
        if (existingRows.length === combinations.length) {
            // יש כבר טבלה עם אותו מספר וריאציות, לא נעשה כלום
            return;
        }
    }
    
    // Remove ALL existing tables if any - not just the first one
    existingTables.forEach(table => table.remove());
    
    // Create table
    const tableContainer = document.createElement('div');
    tableContainer.className = 'variants-table-container mt-6 bg-gray-50 rounded-xl p-6 border border-gray-200';
    
    tableContainer.innerHTML = `
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="ri-grid-line text-blue-600"></i>
            </div>
            <div>
                <h3 class="text-lg font-medium text-gray-900">טבלת וריאציות</h3>
                <p class="text-sm text-gray-600">${combinations.length} צירופים נמצאו</p>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full bg-white rounded-lg shadow-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        ${attributes.map(attr => `<th class="px-4 py-3 text-right text-sm font-medium text-gray-700">${attr.name}</th>`).join('')}
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">SKU</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">מחיר</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">מחיר עלות</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">מלאי</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">גלרייה</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    ${combinations.map((combo, index) => `
                        <tr class="hover:bg-gray-50">
                            ${combo.map((value, attrIndex) => {
                                // בדוק אם המאפיין הוא צבע כדי להציג עיגול צבע
                                const attributeName = attributes[attrIndex].name;
                                const isColorAttribute = getAttributeType(attributeName) === 'color';
                                const colorValue = isColorAttribute ? getColorForValue(attributeName, value) : null;
                                
                                if (isColorAttribute && colorValue) {
                                    return `<td class="px-4 py-3 text-sm text-gray-900">
                                        <div class="flex items-center gap-2">
                                            <div class="w-5 h-5 rounded-full border-2 border-gray-300 shadow-sm flex-shrink-0" 
                                                 style="background-color: ${colorValue};" 
                                                 title="${value}"></div>
                                            <span>${value}</span>
                                        </div>
                                    </td>`;
                                } else {
                                    return `<td class="px-4 py-3 text-sm text-gray-900">${value}</td>`;
                                }
                            }).join('')}
                            <td class="px-4 py-3">
                                <input type="text" name="variants[${index}][sku]" 
                                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded"
                                    placeholder="SKU">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" name="variants[${index}][price]" step="0.01"
                                    class="w-20 px-2 py-1 text-sm border border-gray-300 rounded"
                                    placeholder="0.00">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" name="variants[${index}][cost_price]" step="0.01"
                                    class="w-20 px-2 py-1 text-sm border border-gray-300 rounded"
                                    placeholder="0.00">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" name="variants[${index}][inventory]" 
                                    class="w-16 px-2 py-1 text-sm border border-gray-300 rounded"
                                    placeholder="0" value="0">
                            </td>
                            <td class="px-4 py-3">
                                <select name="variants[${index}][gallery_value]" 
                                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded">
                                    <option value="">בחר ערך לגלרייה</option>
                                    ${combo.map((value, attrIndex) => `
                                        <option value="${value}">${attributes[attrIndex].name}: ${value}</option>
                                    `).join('')}
                                </select>
                            </td>
                            ${combo.map((value, attrIndex) => `
                                <input type="hidden" name="variants[${index}][attributes][${attributes[attrIndex].name}]" value="${value}">
                            `).join('')}
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button type="button" onclick="bulkEditVariants('price')" 
                    class="px-3 py-1.5 text-sm bg-blue-50 text-blue-600 border border-blue-200 rounded hover:bg-blue-100 transition-colors">
                    <i class="ri-edit-line ml-1"></i>
                    עריכה קבוצתית - מחיר
                </button>
                <button type="button" onclick="bulkEditVariants('inventory')" 
                    class="px-3 py-1.5 text-sm bg-green-50 text-green-600 border border-green-200 rounded hover:bg-green-100 transition-colors">
                    <i class="ri-edit-line ml-1"></i>
                    עריכה קבוצתית - מלאי
                </button>
                <button type="button" onclick="bulkEditVariants('sku')" 
                    class="px-3 py-1.5 text-sm bg-purple-50 text-purple-600 border border-purple-200 rounded hover:bg-purple-100 transition-colors">
                    <i class="ri-edit-line ml-1"></i>
                    עריכה קבוצתית - SKU
                </button>
            </div>
            <div class="text-sm text-gray-500">
                ${combinations.length} וריאציות
            </div>
        </div>
    `;
    
    container.appendChild(tableContainer);
    
    // Hide general pricing when variants exist
    hideGeneralPricing();
}

function getVariantCombinations(attributes) {
    if (attributes.length === 0) return [];
    if (attributes.length === 1) return attributes[0].values.map(value => [value]);
    
    const result = [];
    const [first, ...rest] = attributes;
    const restCombinations = getVariantCombinations(rest);
    
    first.values.forEach(value => {
        if (restCombinations.length === 0) {
            result.push([value]);
        } else {
            restCombinations.forEach(combo => {
                result.push([value, ...combo]);
            });
        }
    });
    
    return result;
}

// פונקציות עזר להצגת צבעים בטבלת וריאציות
function getAttributeType(attributeName) {
    const attributeItems = document.querySelectorAll('.attribute-item');
    for (let item of attributeItems) {
        const nameInput = item.querySelector('input[name*="[name]"]');
        const typeSelect = item.querySelector('select[name*="[type]"]');
        
        if (nameInput && nameInput.value.trim() === attributeName && typeSelect) {
            return typeSelect.value;
        }
    }
    return 'text';
}

function getColorForValue(attributeName, value) {
    const attributeItems = document.querySelectorAll('.attribute-item');
    
    for (let item of attributeItems) {
        const nameInput = item.querySelector('input[name*="[name]"]');
        const typeSelect = item.querySelector('select[name*="[type]"]');
        
        if (nameInput && nameInput.value.trim() === attributeName && 
            typeSelect && typeSelect.value === 'color') {
            
            // חפש את הערך המתאים ואת הצבע שלו
            const valueInputs = item.querySelectorAll('input[name*="[values]"][name*="[value]"]');
            
            for (let valueInput of valueInputs) {
                if (valueInput.value.trim() === value) {
                    const colorInput = valueInput.parentNode.querySelector('input[type="color"]');
                    if (colorInput && colorInput.value && colorInput.value !== '#000000') {
                        return colorInput.value;
                    }
                    
                    // אם אין צבע נבחר, נסה לגלות מהשם
                    return getColorFromName(value);
                }
            }
        }
    }
    
    // fallback - נסה לגלות צבע מהשם
    return getColorFromName(value);
}

// פונקציה לגילוי צבע מהשם (משתמשת בקוד מ-color-auto-picker.js)
function getColorFromName(colorName) {
    const KNOWN_COLORS = {
        'אדום': '#DC2626',
        'כחול': '#2563EB', 
        'ירוק': '#16A34A',
        'צהוב': '#EAB308',
        'כתום': '#EA580C',
        'סגול': '#9333EA',
        'ורוד': '#EC4899',
        'חום': '#A16207',
        'שחור': '#000000',
        'לבן': '#FFFFFF',
        'כחול בהיר': '#3B82F6',
        'כחול כהה': '#1E3A8A',
        'ירוק בהיר': '#22C55E',
        'ירוק כהה': '#15803D',
        'אדום בהיר': '#EF4444',
        'אדום כהה': '#B91C1C',
        'אפור': '#6B7280',
        'אפור בהיר': '#D1D5DB',
        'אפור כהה': '#374151',
        'זהב': '#F59E0B'
    };
    
    const cleanName = colorName.trim().toLowerCase();
    
    // חיפוש ישיר
    for (let [name, color] of Object.entries(KNOWN_COLORS)) {
        if (name.toLowerCase() === cleanName) {
            return color;
        }
    }
    
    // חיפוש חלקי
    for (let [name, color] of Object.entries(KNOWN_COLORS)) {
        if (cleanName.includes(name.toLowerCase()) || name.toLowerCase().includes(cleanName)) {
            return color;
        }
    }
    
    // אם לא נמצא, החזר צבע ברירת מחדל
    return '#6B7280'; // אפור
}

function hideVariantsTable() {
    const container = document.getElementById('variants-section');
    const existingTables = container.querySelectorAll('.variants-table-container');
    existingTables.forEach(table => table.remove());
    
    // Show general pricing when no variants
    showGeneralPricing();
}

function showGeneralPricing() {
    const generalPricing = document.getElementById('general-pricing');
    const variantsNotice = document.getElementById('variants-pricing-notice');
    
    if (generalPricing) {
        generalPricing.style.display = 'grid';
    }
    if (variantsNotice) {
        variantsNotice.style.display = 'none';
    }
}

function hideGeneralPricing() {
    const generalPricing = document.getElementById('general-pricing');
    const variantsNotice = document.getElementById('variants-pricing-notice');
    
    if (generalPricing) {
        generalPricing.style.display = 'none';
    }
    if (variantsNotice) {
        variantsNotice.style.display = 'block';
    }
}

// Handle attribute value removal
function removeAttributeValue(button) {
    button.closest('.flex').remove();
    // Trigger update of variants table and gallery
    setTimeout(() => {
        updateVariantsTable();
        if (typeof updateGalleryAttributeDropdown === 'function') {
            updateGalleryAttributeDropdown();
        }
    }, 100);
}

// Handle attribute image upload
function uploadAttributeImage(button, attributeIndex, valueIndex) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    
    input.onchange = function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Update hidden input with image data
                const hiddenInput = button.parentNode.querySelector('.attribute-image-input');
                if (hiddenInput) {
                    hiddenInput.value = e.target.result;
                }
                
                // Update button appearance
                button.innerHTML = '<i class="ri-image-line"></i> תמונה נבחרה';
                button.classList.remove('bg-blue-50', 'text-blue-600', 'border-blue-200');
                button.classList.add('bg-green-50', 'text-green-600', 'border-green-200');
            };
            reader.readAsDataURL(file);
        }
    };
    
    input.click();
}

// Bulk edit variants function
function bulkEditVariants(field) {
    const fieldName = {
        'price': 'מחיר',
        'inventory': 'מלאי', 
        'sku': 'SKU'
    }[field];
    
    const value = prompt(`הזן ${fieldName} לכל הוריאציות:`);
    if (value !== null) {
        const inputs = document.querySelectorAll(`input[name*="[${field}]"]`);
        inputs.forEach(input => {
            if (field === 'sku' && value) {
                // For SKU, add a suffix based on the row index
                const match = input.name.match(/variants\[(\d+)\]/);
                const index = match ? parseInt(match[1]) + 1 : 1;
                input.value = `${value}-${index.toString().padStart(2, '0')}`;
            } else {
                input.value = value;
            }
        });
    }
}

// Update gallery attribute dropdown
function updateGalleryAttributeDropdown() {
    console.log('updateGalleryAttributeDropdown called');
    
    const gallerySelect = document.getElementById('gallery-attribute-select');
    if (!gallerySelect) {
        console.log('Gallery select not found, exiting');
        return;
    }
    
    // שמור את הערך הנוכחי
    const currentValue = gallerySelect.value;
    console.log('Current gallery attribute value:', currentValue);
    
    // Clear existing options (except first)
    while (gallerySelect.children.length > 1) {
        gallerySelect.removeChild(gallerySelect.lastChild);
    }
    
    // Get all attribute names
    const attributeItems = document.querySelectorAll('.attribute-item');
    let foundCurrentValue = false;
    
    console.log('Found', attributeItems.length, 'attribute items for dropdown');
    
    attributeItems.forEach((item) => {
        const nameInput = item.querySelector('input[name*="[name]"]');
        if (nameInput && nameInput.value.trim()) {
            console.log('Adding attribute to dropdown:', nameInput.value.trim());
            
            const option = document.createElement('option');
            option.value = nameInput.value.trim();
            option.textContent = nameInput.value.trim();
            gallerySelect.appendChild(option);
            
            // בדוק אם זה הערך שהיה בחור לפני העדכון
            if (option.value === currentValue) {
                foundCurrentValue = true;
                option.selected = true;
                console.log('Restored previous selection:', currentValue);
            }
        }
    });
    
    // אם הערך הקודם לא נמצא, נקה את הבחירה
    if (!foundCurrentValue && currentValue) {
        console.log('Previous value not found, clearing selection:', currentValue);
        gallerySelect.value = '';
    }
    
    console.log('Dropdown updated, calling updateAttributeGalleries');
    
    // Update attribute galleries
    updateAttributeGalleries();
}

// Add event listener for gallery attribute selection
document.addEventListener('DOMContentLoaded', function() {
    const galleryAttributeSelect = document.getElementById('gallery-attribute-select');
    if (galleryAttributeSelect) {
        galleryAttributeSelect.addEventListener('change', updateAttributeGalleries);
    }
});

// Update attribute galleries section
function updateAttributeGalleries() {
    console.log('updateAttributeGalleries called');
    
    const galleriesContainer = document.getElementById('attribute-galleries');
    const gallerySection = document.getElementById('gallery-per-attribute');
    const galleryAttributeSelect = document.getElementById('gallery-attribute-select');
    
    console.log('Gallery containers found:', {
        galleriesContainer: !!galleriesContainer,
        gallerySection: !!gallerySection,
        galleryAttributeSelect: !!galleryAttributeSelect
    });
    
    if (!galleriesContainer || !gallerySection || !galleryAttributeSelect) {
        console.log('Missing required gallery containers, exiting');
        return;
    }
    
    // Clear existing galleries
    galleriesContainer.innerHTML = '';
    
    // Only show if a gallery attribute is selected
    const selectedAttribute = galleryAttributeSelect.value;
    console.log('Selected gallery attribute:', selectedAttribute);
    
    if (!selectedAttribute) {
        gallerySection.classList.add('hidden');
        console.log('No attribute selected, hiding gallery section');
        return;
    }
    
    // Get all attributes with values
    const attributeItems = document.querySelectorAll('.attribute-item');
    let hasMatchingAttribute = false;
    
    console.log('Found', attributeItems.length, 'attribute items');
    
    attributeItems.forEach((item, attrIndex) => {
        const nameInput = item.querySelector('input[name*="[name]"]');
        const valueInputs = item.querySelectorAll('input[name*="[values]"][name*="[value]"]');
        
        if (nameInput) {
            console.log('Attribute', attrIndex, '- name:', nameInput.value.trim(), 'values count:', valueInputs.length);
        }
        
        // Only show galleries for the selected attribute
        if (nameInput && nameInput.value.trim() === selectedAttribute && valueInputs.length > 0) {
            hasMatchingAttribute = true;
            console.log('Found matching attribute:', selectedAttribute, 'with', valueInputs.length, 'values');
            
            const attributeGallery = document.createElement('div');
            attributeGallery.className = 'border border-gray-200 rounded-lg p-4';
            
            let valuesHtml = '';
            valueInputs.forEach((valueInput, valueIndex) => {
                if (valueInput.value.trim()) {
                    console.log('Adding gallery for value:', valueInput.value.trim());
                    valuesHtml += `
                        <div class="space-y-2">
                            <h6 class="text-sm font-medium text-gray-700">${nameInput.value.trim()}: ${valueInput.value.trim()}</h6>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-blue-400 transition-colors attribute-gallery-upload"
                                data-attribute="${nameInput.value.trim()}" data-value="${valueInput.value.trim()}">
                                <input type="file" multiple accept="image/*" class="hidden attribute-gallery-input">
                                <i class="ri-image-add-line text-2xl text-gray-400 mb-2"></i>
                                <p class="text-xs text-gray-500">העלה תמונות עבור ${valueInput.value.trim()}</p>
                            </div>
                            <div class="attribute-gallery-preview grid grid-cols-3 gap-2 hidden">
                                <!-- Preview images will be added here -->
                            </div>
                        </div>
                    `;
                }
            });
            
            attributeGallery.innerHTML = `
                <h5 class="font-medium text-gray-800 mb-3">${nameInput.value.trim()}</h5>
                <div class="space-y-4">
                    ${valuesHtml}
                </div>
            `;
            
            galleriesContainer.appendChild(attributeGallery);
            console.log('Added gallery section for attribute:', selectedAttribute);
        }
    });
    
    // Show/hide the gallery section
    if (hasMatchingAttribute) {
        gallerySection.classList.remove('hidden');
        console.log('Gallery section shown');
    } else {
        gallerySection.classList.add('hidden');
        console.log('No matching attributes found, hiding gallery section');
    }
    
    // Add event listeners for new upload areas
    setupAttributeGalleryListeners();
    console.log('Event listeners setup completed');
}

// Setup event listeners for attribute galleries
function setupAttributeGalleryListeners() {
    const uploadAreas = document.querySelectorAll('.attribute-gallery-upload');
    console.log('Setting up gallery listeners for', uploadAreas.length, 'upload areas');
    
    uploadAreas.forEach(area => {
        const input = area.querySelector('.attribute-gallery-input');
        const attribute = area.dataset.attribute;
        const value = area.dataset.value;
        
        console.log('Setting up listener for attribute:', attribute, 'value:', value);
        
        // Click to upload
        area.addEventListener('click', () => {
            console.log('Upload area clicked for:', attribute, value);
            input.click();
        });
        
        // File selection
        input.addEventListener('change', (e) => {
            const files = e.target.files;
            const preview = area.nextElementSibling;
            
            console.log('Files selected for', attribute, value, '- count:', files.length);
            
            for (let file of files) {
                if (file.type.startsWith('image/')) {
                    console.log('Processing image file:', file.name, 'size:', file.size);
                    addAttributeGalleryPreview(file, preview, attribute, value);
                } else {
                    console.log('Skipping non-image file:', file.name, 'type:', file.type);
                }
            }
            
            if (preview.children.length > 0) {
                preview.classList.remove('hidden');
                console.log('Preview container shown with', preview.children.length, 'images');
            }
        });
        
        // Drag and drop
        area.addEventListener('dragover', (e) => {
            e.preventDefault();
            area.classList.add('bg-blue-50', 'border-blue-300');
        });
        
        area.addEventListener('dragleave', (e) => {
            e.preventDefault();
            area.classList.remove('bg-blue-50', 'border-blue-300');
        });
        
        area.addEventListener('drop', (e) => {
            e.preventDefault();
            area.classList.remove('bg-blue-50', 'border-blue-300');
            
            const files = e.dataTransfer.files;
            const preview = area.nextElementSibling;
            
            console.log('Files dropped for', attribute, value, '- count:', files.length);
            
            for (let file of files) {
                if (file.type.startsWith('image/')) {
                    console.log('Processing dropped image file:', file.name, 'size:', file.size);
                    addAttributeGalleryPreview(file, preview, attribute, value);
                } else {
                    console.log('Skipping dropped non-image file:', file.name, 'type:', file.type);
                }
            }
            
            if (preview.children.length > 0) {
                preview.classList.remove('hidden');
                console.log('Preview container shown after drop with', preview.children.length, 'images');
            }
        });
    });
}

// Remove attribute gallery image
function removeAttributeGalleryImage(button) {
    const imageDiv = button.closest('.relative');
    const container = imageDiv.parentNode;
    
    // Remove the image div
    imageDiv.remove();
    
    // Check if container is now empty and hide if needed
    if (container && container.children.length === 0) {
        container.classList.add('hidden');
        
        // עדכן את סטטוס האפלוד
        const uploadArea = container.previousElementSibling;
        if (uploadArea && uploadArea.classList.contains('attribute-gallery-upload')) {
            uploadArea.style.borderColor = '';
            uploadArea.style.backgroundColor = '';
            
            const statusText = uploadArea.querySelector('.upload-status');
            if (statusText) {
                statusText.remove();
            }
        }
    } else if (container) {
        // עדכן את מספר התמונות בסטטוס
        const uploadArea = container.previousElementSibling;
        if (uploadArea && uploadArea.classList.contains('attribute-gallery-upload')) {
            const statusText = uploadArea.querySelector('.upload-status');
            if (statusText) {
                statusText.textContent = `${container.children.length} תמונות נבחרו`;
            }
        }
        
        // עדכן מספרים סידוריים
        updateAttributeImageNumbers(container);
    }
}

// Set primary image for attribute gallery
function setAttributePrimaryImage(button) {
    const container = button.closest('.relative').parentNode;
    
    // Remove primary status from all images in this attribute gallery
    container.querySelectorAll('.relative').forEach((item, index) => {
        const starBtn = item.querySelector('[onclick*="setAttributePrimaryImage"]');
        const hiddenInput = item.querySelector('input[name*="attribute_media_primary"]');
        
        if (starBtn && hiddenInput) {
            starBtn.className = 'absolute top-2 right-2 w-8 h-8 bg-white text-gray-600 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-yellow-500 hover:text-white flex items-center justify-center shadow-lg';
            starBtn.innerHTML = '<i class="ri-star-line text-sm"></i>';
            hiddenInput.value = '0';
        }
    });
    
    // Set clicked image as primary
    const currentItem = button.closest('.relative');
    const currentHiddenInput = currentItem.querySelector('input[name*="attribute_media_primary"]');
    
    if (currentHiddenInput) {
        button.className = 'absolute top-2 right-2 w-8 h-8 bg-yellow-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-yellow-500 hover:text-white flex items-center justify-center shadow-lg';
        button.innerHTML = '<i class="ri-star-fill text-sm"></i>';
        currentHiddenInput.value = '1';
    }
}

// Update image numbers for attribute gallery
function updateAttributeImageNumbers(container) {
    if (!container) return;
    
    container.querySelectorAll('.relative').forEach((item, index) => {
        const numberDiv = item.querySelector('.absolute.bottom-2.left-2');
        if (numberDiv) {
            numberDiv.textContent = index + 1;
        }
    });
}

// Add preview for attribute gallery image
function addAttributeGalleryPreview(file, container, attribute, value) {
    const reader = new FileReader();
    reader.onload = (e) => {
        console.log('✅ Gallery image added:', attribute, value, file.name);
        
        const imageDiv = document.createElement('div');
        imageDiv.className = 'relative group';
        
        // חישוב מספר התמונה הנוכחית
        const currentIndex = container.children.length;
        
        imageDiv.innerHTML = `
            <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden rounded-lg">
                <img src="${e.target.result}" alt="${file.name}" class="w-full h-full object-cover">
            </div>
            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-200"></div>
            
            <!-- אייקון מחיקה - שמאל מעלה -->
            <button type="button" onclick="removeAttributeGalleryImage(this);" 
                class="absolute top-2 left-2 w-8 h-8 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-red-600 flex items-center justify-center shadow-lg">
                <i class="ri-delete-bin-line text-sm"></i>
            </button>
            
            <!-- אייקון תמונה ראשית - ימין מעלה -->
            <button type="button" onclick="setAttributePrimaryImage(this);" 
                class="absolute top-2 right-2 w-8 h-8 ${currentIndex === 0 ? 'bg-yellow-500 text-white' : 'bg-white text-gray-600'} rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-yellow-500 hover:text-white flex items-center justify-center shadow-lg">
                <i class="ri-star-${currentIndex === 0 ? 'fill' : 'line'} text-sm"></i>
            </button>
            
            <!-- מספר סידורי -->
            <div class="absolute bottom-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-all duration-200">
                ${currentIndex + 1}
            </div>
            
            <input type="hidden" name="attribute_media[${attribute}][${value}][]" value="${e.target.result}">
            <input type="hidden" name="attribute_media_primary[${attribute}][${value}]" value="${currentIndex === 0 ? '1' : '0'}">
        `;
        
        container.appendChild(imageDiv);
        
        // Debug: הצג את השם של השדה החבוי שנוצר
        const hiddenInput = imageDiv.querySelector('input[type="hidden"]');
        if (hiddenInput) {
            console.log('🔗 Hidden input created:', hiddenInput.name);
        }
        
        // הסתר את ה-preview container
        container.classList.remove('hidden');
        
        // הוסף visual feedback
        const uploadArea = container.previousElementSibling;
        if (uploadArea && uploadArea.classList.contains('attribute-gallery-upload')) {
            uploadArea.style.borderColor = '#10b981';
            uploadArea.style.backgroundColor = '#f0fdf4';
            
            const statusText = uploadArea.querySelector('.upload-status');
            if (!statusText) {
                const statusDiv = document.createElement('div');
                statusDiv.className = 'upload-status text-xs text-green-600 mt-1';
                statusDiv.textContent = `${container.children.length} תמונות נבחרו`;
                uploadArea.appendChild(statusDiv);
            } else {
                statusText.textContent = `${container.children.length} תמונות נבחרו`;
            }
        }
    };
    reader.readAsDataURL(file);
}

// Add event listeners when DOM is loaded - only once
document.addEventListener('DOMContentLoaded', function() {
    // Prevent duplicate event listeners
    if (window.variantListenersAdded) return;
    window.variantListenersAdded = true;
    
    // Listen for changes in attribute values and variant checkboxes
    document.addEventListener('input', function(e) {
        if (e.target.name && (e.target.name.includes('[values]') || e.target.name.includes('[name]'))) {
            // Debounce the update
            clearTimeout(window.variantUpdateTimeout);
            window.variantUpdateTimeout = setTimeout(() => {
                updateVariantsTable();
                updateGalleryAttributeDropdown();
            }, 300);
        }
    });
    
    document.addEventListener('change', function(e) {
        if (e.target.name && e.target.name.includes('[is_variant]')) {
            updateVariantsTable();
        }
        
        // מאזין לשינויים בקולור פיקר כדי לעדכן את הטבלה
        if (e.target.type === 'color' && e.target.name && e.target.name.includes('[color]')) {
            // עיכוב קטן כדי לתת לדף להתעדכן
            setTimeout(() => {
                updateVariantsTable();
            }, 100);
        }
        
        // מאזין לשינויים בסוג מאפיין (אם הופך לצבע)
        if (e.target.name && e.target.name.includes('[type]')) {
            setTimeout(() => {
                updateVariantsTable();
            }, 100);
        }
    });
}); 