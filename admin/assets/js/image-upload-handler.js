/**
 * מטפל בהעלאת תמונות - מאוחד ליצירה ועריכה
 */

class ImageUploadHandler {
    constructor(options = {}) {
        this.apiUrl = options.apiUrl || '/api/upload-image.php';
        this.storeId = options.storeId || 1;
        this.maxFileSize = options.maxFileSize || 10 * 1024 * 1024; // 10MB
        this.allowedTypes = options.allowedTypes || ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        this.onSuccess = options.onSuccess || this.defaultSuccessHandler;
        this.onError = options.onError || this.defaultErrorHandler;
        this.onProgress = options.onProgress || null;
    }

    /**
     * העלאת תמונה מ-input file
     */
    async uploadFile(file, folder = 'products') {
        try {
            // בדיקות תקינות
            this.validateFile(file);

            const formData = new FormData();
            formData.append('image', file);
            formData.append('store_id', this.storeId);
            formData.append('folder', folder);

            return await this.sendRequest(formData);

        } catch (error) {
            this.onError(error.message);
            return null;
        }
    }

    /**
     * העלאת תמונה מ-base64
     */
    async uploadBase64(base64Data, folder = 'products', filename = null) {
        try {
            const formData = new FormData();
            formData.append('image_data', base64Data);
            formData.append('store_id', this.storeId);
            formData.append('folder', folder);
            
            if (filename) {
                formData.append('filename', filename);
            }

            return await this.sendRequest(formData);

        } catch (error) {
            this.onError(error.message);
            return null;
        }
    }

    /**
     * העלאת תמונה מ-drag & drop
     */
    async uploadFromDrop(dataTransfer, folder = 'products') {
        const files = Array.from(dataTransfer.files);
        const results = [];

        for (const file of files) {
            const result = await this.uploadFile(file, folder);
            if (result) {
                results.push(result);
            }
        }

        return results;
    }

    /**
     * שליחת בקשה לשרת
     */
    async sendRequest(formData) {
        const response = await fetch(this.apiUrl, {
            method: 'POST',
            body: formData
            // לא מגדירים Content-Type כדי שהדפדפן יעשה multipart/form-data אוטומטית
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'שגיאה בהעלאת התמונה');
        }

        this.onSuccess(result);
        return result;
    }

    /**
     * בדיקת תקינות קובץ
     */
    validateFile(file) {
        if (!file) {
            throw new Error('לא נבחר קובץ');
        }

        if (!this.allowedTypes.includes(file.type)) {
            throw new Error('סוג קובץ לא נתמך. מותרים: ' + this.allowedTypes.join(', '));
        }

        if (file.size > this.maxFileSize) {
            throw new Error('קובץ גדול מדי (מקסימום 10MB)');
        }
    }

    /**
     * הפיכת קובץ ל-base64
     */
    fileToBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    /**
     * callback ברירת מחדל להצלחה
     */
    defaultSuccessHandler(result) {
        console.log('תמונה הועלתה בהצלחה:', result);
    }

    /**
     * callback ברירת מחדל לשגיאה
     */
    defaultErrorHandler(message) {
        console.error('שגיאה בהעלאת תמונה:', message);
        alert('שגיאה בהעלאת תמונה: ' + message);
    }

    /**
     * הוספת drag & drop לאלמנט
     */
    addDragAndDrop(element, folder = 'products') {
        element.addEventListener('dragover', (e) => {
            e.preventDefault();
            element.classList.add('drag-over');
        });

        element.addEventListener('dragleave', () => {
            element.classList.remove('drag-over');
        });

        element.addEventListener('drop', async (e) => {
            e.preventDefault();
            element.classList.remove('drag-over');
            
            const results = await this.uploadFromDrop(e.dataTransfer, folder);
            return results;
        });
    }

    /**
     * יצירת input file חבוי
     */
    createFileInput(multiple = false, folder = 'products') {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = this.allowedTypes.join(',');
        input.multiple = multiple;
        input.style.display = 'none';

        input.addEventListener('change', async (e) => {
            const files = Array.from(e.target.files);
            const results = [];

            for (const file of files) {
                const result = await this.uploadFile(file, folder);
                if (result) {
                    results.push(result);
                }
            }

            return results;
        });

        return input;
    }
}

/**
 * פונקציות עזר גלובליות
 */

// יצירת instance גלובלי
window.ImageUploader = null;

// אתחול עם הגדרות ברירת מחדל
window.initImageUploader = function(options = {}) {
    window.ImageUploader = new ImageUploadHandler(options);
    return window.ImageUploader;
};

// עזר להוספת preview לתמונה
window.addImagePreview = function(container, imageUrl, onRemove = null, mediaData = null) {
    const previewDiv = document.createElement('div');
    previewDiv.className = 'image-preview relative inline-block m-2';
    
    const img = document.createElement('img');
    img.src = imageUrl;
    img.className = 'w-24 h-24 object-cover rounded border';
    
    if (onRemove) {
        const removeBtn = document.createElement('button');
        removeBtn.innerHTML = '×';
        removeBtn.className = 'absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 text-sm';
        removeBtn.onclick = () => {
            previewDiv.remove();
            if (onRemove) onRemove(imageUrl);
        };
        previewDiv.appendChild(removeBtn);
    }
    
    previewDiv.appendChild(img);
    
    // אם זה מדיה קיימת, הוסף שדות נסתרים
    if (mediaData) {
        // חשב אינדקס על בסיס המדיה הקיימת בcontainer
        const existingMediaItems = container.querySelectorAll('[name*="media["][name*="][url]"]');
        const currentIndex = existingMediaItems.length;
        const hiddenInputs = [
            { name: `media[${currentIndex}][url]`, value: mediaData.url || imageUrl },
            { name: `media[${currentIndex}][thumbnail_url]`, value: mediaData.thumbnail_url || imageUrl },
            { name: `media[${currentIndex}][type]`, value: mediaData.type || 'image' },
            { name: `media[${currentIndex}][alt_text]`, value: mediaData.alt_text || '' },
            { name: `media[${currentIndex}][sort_order]`, value: mediaData.sort_order || 0 },
            { name: `media[${currentIndex}][is_primary]`, value: mediaData.is_primary || 0 }
        ];
        
        hiddenInputs.forEach(input => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = input.name;
            hiddenInput.value = input.value;
            previewDiv.appendChild(hiddenInput);
        });
    }
    
    container.appendChild(previewDiv);
    
    return previewDiv;
};

// עזר ליצירת gallery widget
window.createGalleryWidget = function(container, existingImages = [], options = {}) {
    const galleryDiv = document.createElement('div');
    galleryDiv.className = 'gallery-widget border-2 border-dashed border-gray-300 rounded-lg p-4';
    
    const dropZone = document.createElement('div');
    dropZone.className = 'drop-zone text-center p-8';
    dropZone.innerHTML = `
        <div class="text-gray-500">
            <i class="fas fa-cloud-upload-alt text-3xl mb-2"></i>
            <p>גרור תמונות לכאן או לחץ לבחירה</p>
        </div>
    `;
    
    const previewContainer = document.createElement('div');
    previewContainer.className = 'preview-container flex flex-wrap mt-4';
    
    galleryDiv.appendChild(dropZone);
    galleryDiv.appendChild(previewContainer);
    container.appendChild(galleryDiv);
    
    // הוספת תמונות קיימות
    existingImages.forEach(imageUrl => {
        addImagePreview(previewContainer, imageUrl, options.onRemove);
    });
    
    // הוספת drag & drop
    if (window.ImageUploader) {
        window.ImageUploader.addDragAndDrop(dropZone, options.folder || 'products');
    }
    
    // לחיצה לבחירת קבצים
    const fileInput = window.ImageUploader ? 
        window.ImageUploader.createFileInput(true, options.folder || 'products') : 
        null;
    
    if (fileInput) {
        dropZone.addEventListener('click', () => fileInput.click());
        container.appendChild(fileInput);
    }
    
    return {
        container: galleryDiv,
        previewContainer: previewContainer,
        fileInput: fileInput
    };
};

// CSS להוספה
const style = document.createElement('style');
style.textContent = `
    .drag-over {
        border-color: #3B82F6 !important;
        background-color: #EFF6FF !important;
    }
    
    .image-preview {
        transition: transform 0.2s;
    }
    
    .image-preview:hover {
        transform: scale(1.05);
    }
    
    .drop-zone {
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .drop-zone:hover {
        background-color: #F9FAFB;
        border-color: #9CA3AF;
    }
`;
document.head.appendChild(style); 