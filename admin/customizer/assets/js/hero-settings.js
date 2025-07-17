/**
 * מנהל הגדרות Hero - אחראי על יצירת ממשק הגדרות מתקדם לסקשן Hero
 */
class HeroSettingsManager {
    constructor() {
        this.buttonCounter = 0;
    }

    /**
     * יצירת טופס הגדרות Hero מתקדם
     */
    generateHeroSettingsForm(settings) {
        const buttons = settings.buttons || [{ label: 'צפה במוצרים', link: '#products', style: 'solid' }];
        
        return `
            <div class="space-y-6">
                <!-- Content Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-medium text-gray-900 mb-4">תוכן</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">כותרת ראשית</label>
                            <input type="text" id="hero-title" value="${settings.title || ''}" 
                                   placeholder="הזן כותרת ראשית"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">תת כותרת</label>
                            <input type="text" id="hero-subtitle" value="${settings.subtitle || ''}" 
                                   placeholder="הזן תת כותרת"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">תוכן טקסט (תומך HTML)</label>
                            <textarea id="hero-content" rows="4" 
                                      placeholder="הזן תוכן טקסט (תומך HTML ואנטר)"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">${settings.content || ''}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Buttons Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-medium text-gray-900">כפתורים</h3>
                        <button type="button" id="add-hero-button" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                            <i class="ri-add-line ml-1"></i>הוסף כפתור
                        </button>
                    </div>
                    
                    <div id="hero-buttons-list" class="space-y-3">
                        ${buttons.map((button, index) => this.generateButtonField(button, index)).join('')}
                    </div>
                </div>

                <!-- Background Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-medium text-gray-900 mb-4">רקע</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">סוג רקע</label>
                            <select id="hero-bg-type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="color" ${(settings.bg_type || 'color') === 'color' ? 'selected' : ''}>צבע אחיד</option>
                                <option value="gradient" ${settings.bg_type === 'gradient' ? 'selected' : ''}>גרדיאנט</option>
                                <option value="image" ${settings.bg_type === 'image' ? 'selected' : ''}>תמונה</option>
                                <option value="video" ${settings.bg_type === 'video' ? 'selected' : ''}>סרטון</option>
                            </select>
                        </div>
                        
                        <div id="hero-bg-color-field" class="bg-type-field" style="display: ${(settings.bg_type || 'color') === 'color' ? 'block' : 'none'}">
                            <label class="block text-sm font-medium text-gray-700 mb-2">צבע רקע</label>
                            <input type="color" id="hero-bg-color" value="${settings.bg_color || '#1e40af'}" 
                                   class="w-full h-10 border border-gray-300 rounded-md">
                        </div>
                        
                        <div id="hero-bg-gradient-field" class="bg-type-field" style="display: ${settings.bg_type === 'gradient' ? 'block' : 'none'}">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">צבע התחלה</label>
                                    <input type="color" id="hero-gradient-start" value="${settings.gradient_start || '#1e40af'}" 
                                           class="w-full h-10 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">צבע סיום</label>
                                    <input type="color" id="hero-gradient-end" value="${settings.gradient_end || '#3b82f6'}" 
                                           class="w-full h-10 border border-gray-300 rounded-md">
                                </div>
                            </div>
                            <div class="mt-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">כיוון</label>
                                <select id="hero-gradient-direction" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="to right" ${settings.gradient_direction === 'to right' ? 'selected' : ''}>שמאל לימין</option>
                                    <option value="to left" ${settings.gradient_direction === 'to left' ? 'selected' : ''}>ימין לשמאל</option>
                                    <option value="to bottom" ${(settings.gradient_direction || 'to bottom') === 'to bottom' ? 'selected' : ''}>למטה</option>
                                    <option value="to top" ${settings.gradient_direction === 'to top' ? 'selected' : ''}>למעלה</option>
                                    <option value="45deg" ${settings.gradient_direction === '45deg' ? 'selected' : ''}>אלכסוני</option>
                                </select>
                            </div>
                        </div>
                        
                        <div id="hero-bg-image-field" class="bg-type-field" style="display: ${settings.bg_type === 'image' ? 'block' : 'none'}">
                            <label class="block text-sm font-medium text-gray-700 mb-2">תמונת רקע</label>
                            <input type="file" id="hero-bg-image" accept="image/*" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            ${settings.bg_image ? `<img src="${settings.bg_image}" class="mt-2 h-20 object-cover rounded">` : ''}
                            <div class="mt-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">גודל תמונה</label>
                                <select id="hero-bg-image-size" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="cover" ${(settings.bg_image_size || 'cover') === 'cover' ? 'selected' : ''}>כיסוי מלא</option>
                                    <option value="contain" ${settings.bg_image_size === 'contain' ? 'selected' : ''}>התאמה</option>
                                    <option value="auto" ${settings.bg_image_size === 'auto' ? 'selected' : ''}>גודל מקורי</option>
                                </select>
                            </div>
                        </div>
                        
                        <div id="hero-bg-video-field" class="bg-type-field" style="display: ${settings.bg_type === 'video' ? 'block' : 'none'}">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">סוג סרטון</label>
                                    <select id="hero-video-type" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                        <option value="file" ${(settings.video_type || 'file') === 'file' ? 'selected' : ''}>העלאת קובץ</option>
                                        <option value="url" ${settings.video_type === 'url' ? 'selected' : ''}>קישור לסרטון</option>
                                    </select>
                                </div>
                                
                                <div id="hero-video-file-field" style="display: ${(settings.video_type || 'file') === 'file' ? 'block' : 'none'}">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">העלאת סרטון</label>
                                    <input type="file" id="hero-bg-video-file" accept="video/*" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    ${settings.bg_video_file ? `<video src="${settings.bg_video_file}" class="mt-2 h-20 object-cover rounded" muted></video>` : ''}
                                </div>
                                
                                <div id="hero-video-url-field" style="display: ${settings.video_type === 'url' ? 'block' : 'none'}">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">קישור לסרטון</label>
                                    <input type="url" id="hero-bg-video-url" value="${settings.bg_video_url || ''}" 
                                           placeholder="https://example.com/video.mp4"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <p class="text-xs text-gray-500 mt-1">תומך ב-MP4, WebM, YouTube, Vimeo</p>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="flex items-center">
                                            <input type="checkbox" id="hero-video-autoplay" ${settings.video_autoplay !== false ? 'checked' : ''} 
                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="mr-2 text-sm text-gray-700">הפעלה אוטומטית</span>
                                        </label>
                                    </div>
                                    <div>
                                        <label class="flex items-center">
                                            <input type="checkbox" id="hero-video-loop" ${settings.video_loop !== false ? 'checked' : ''} 
                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="mr-2 text-sm text-gray-700">חזרה בלופ</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="flex items-center">
                                            <input type="checkbox" id="hero-video-muted" ${settings.video_muted !== false ? 'checked' : ''} 
                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="mr-2 text-sm text-gray-700">השתקה</span>
                                        </label>
                                    </div>
                                    <div>
                                        <label class="flex items-center">
                                            <input type="checkbox" id="hero-video-overlay" ${settings.video_overlay !== false ? 'checked' : ''} 
                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="mr-2 text-sm text-gray-700">שכבת כיסוי</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div id="hero-video-overlay-color-field" style="display: ${settings.video_overlay !== false ? 'block' : 'none'}">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">צבע שכבת כיסוי</label>
                                    <input type="color" id="hero-video-overlay-color" value="${this.convertRgbaToHex(settings.video_overlay_color) || '#000000'}" 
                                           class="w-full h-10 border border-gray-300 rounded-lg">
                                    <div class="mt-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">שקיפות (0-100%)</label>
                                        <input type="range" id="hero-video-overlay-opacity" min="0" max="100" 
                                               value="${(parseFloat(settings.video_overlay_color?.split(',')[3]?.replace(')', '') || 0.4) * 100)}" 
                                               class="w-full">
                                        <div class="flex justify-between text-xs text-gray-500">
                                            <span>שקוף</span>
                                            <span>אטום</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Typography Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-medium text-gray-900 mb-4">עיצוב טקסט</h3>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">צבע כותרת</label>
                                <input type="color" id="hero-title-color" value="${settings.title_color || '#ffffff'}" 
                                       class="w-full h-10 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">גודל כותרת (px)</label>
                                <input type="number" id="hero-title-size" value="${settings.title_size || 48}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">צבע תת כותרת</label>
                                <input type="color" id="hero-subtitle-color" value="${settings.subtitle_color || '#e5e7eb'}" 
                                       class="w-full h-10 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">גודל תת כותרת (px)</label>
                                <input type="number" id="hero-subtitle-size" value="${settings.subtitle_size || 24}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">צבע תוכן</label>
                                <input type="color" id="hero-content-color" value="${settings.content_color || '#d1d5db'}" 
                                       class="w-full h-10 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">גודל תוכן (px)</label>
                                <input type="number" id="hero-content-size" value="${settings.content_size || 16}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Button Styling Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-medium text-gray-900 mb-4">עיצוב כפתורים</h3>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">צבע רקע כפתור</label>
                                <input type="color" id="hero-button-bg-color" value="${settings.button_bg_color || '#f59e0b'}" 
                                       class="w-full h-10 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">צבע טקסט כפתור</label>
                                <input type="color" id="hero-button-text-color" value="${settings.button_text_color || '#ffffff'}" 
                                       class="w-full h-10 border border-gray-300 rounded-md">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">גודל פונט כפתור (px)</label>
                            <input type="number" id="hero-button-font-size" value="${settings.button_font_size || 16}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>

                <!-- Spacing Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-medium text-gray-900 mb-4">מרווחים</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">פדינג (px)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" id="hero-padding-top" value="${settings.padding_top || 80}" 
                                       placeholder="למעלה" class="px-3 py-2 border border-gray-300 rounded-md">
                                <input type="number" id="hero-padding-bottom" value="${settings.padding_bottom || 80}" 
                                       placeholder="למטה" class="px-3 py-2 border border-gray-300 rounded-md">
                                <input type="number" id="hero-padding-left" value="${settings.padding_left || 20}" 
                                       placeholder="שמאל" class="px-3 py-2 border border-gray-300 rounded-md">
                                <input type="number" id="hero-padding-right" value="${settings.padding_right || 20}" 
                                       placeholder="ימין" class="px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">מרגין (px)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" id="hero-margin-top" value="${settings.margin_top || 0}" 
                                       placeholder="למעלה" class="px-3 py-2 border border-gray-300 rounded-md">
                                <input type="number" id="hero-margin-bottom" value="${settings.margin_bottom || 0}" 
                                       placeholder="למטה" class="px-3 py-2 border border-gray-300 rounded-md">
                                <input type="number" id="hero-margin-left" value="${settings.margin_left || 0}" 
                                       placeholder="שמאל" class="px-3 py-2 border border-gray-300 rounded-md">
                                <input type="number" id="hero-margin-right" value="${settings.margin_right || 0}" 
                                       placeholder="ימין" class="px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom CSS Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-medium text-gray-900 mb-4">class מותאם אישית</h3>
                    
                    <textarea id="hero-custom-css" rows="6" 
                              placeholder="הזן class מותאם אישית..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm">${settings.custom_css || ''}</textarea>
                </div>
            </div>
        `;
    }

    /**
     * יצירת שדה כפתור
     */
    generateButtonField(button, index) {
        return `
            <div class="button-field border border-gray-200 rounded-lg p-3" data-index="${index}">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-700">כפתור ${index + 1}</span>
                    <div class="flex items-center gap-2">
                        <button type="button" class="edit-button-btn p-1 text-blue-600 hover:text-blue-800">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button type="button" class="delete-button-btn p-1 text-red-600 hover:text-red-800">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">תווית</label>
                        <input type="text" class="button-label w-full px-3 py-2 border border-gray-300 rounded-md" 
                               value="${button.label || ''}" placeholder="טקסט הכפתור">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">קישור</label>
                        <input type="url" class="button-link w-full px-3 py-2 border border-gray-300 rounded-md" 
                               value="${button.link || ''}" placeholder="https://example.com">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">סגנון</label>
                        <select class="button-style w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="solid" ${(button.style || 'solid') === 'solid' ? 'selected' : ''}>צבע מלא</option>
                            <option value="outline" ${button.style === 'outline' ? 'selected' : ''}>מתאר</option>
                            <option value="underline" ${button.style === 'underline' ? 'selected' : ''}>קו תחתון</option>
                            <option value="text" ${button.style === 'text' ? 'selected' : ''}>טקסט בלבד</option>
                        </select>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * הגדרת מאזינים לאירועים
     */
    setupEventListeners() {
        // מאזין לשינוי סוג רקע
        document.getElementById('hero-bg-type')?.addEventListener('change', (e) => {
            this.toggleBackgroundFields(e.target.value);
        });

        // מאזין להוספת כפתור
        document.getElementById('add-hero-button')?.addEventListener('click', () => {
            this.addButton();
        });

        // מאזינים לכפתורי עריכה ומחיקה
        document.addEventListener('click', (e) => {
            if (e.target.closest('.delete-button-btn')) {
                this.deleteButton(e.target.closest('.button-field'));
            }
        });

        // מאזינים לשינויים בשדות
        this.setupFieldListeners();
        
        // מאזין לשינוי סוג סרטון
        const videoTypeSelect = document.getElementById('hero-video-type');
        if (videoTypeSelect) {
            videoTypeSelect.addEventListener('change', (e) => {
                this.toggleVideoFields(e.target.value);
            });
        }
        
        // מאזין לשינוי שכבת כיסוי
        const videoOverlayCheckbox = document.getElementById('hero-video-overlay');
        if (videoOverlayCheckbox) {
            videoOverlayCheckbox.addEventListener('change', (e) => {
                this.toggleVideoOverlay(e.target.checked);
            });
        }
        
        // מאזין להעלאת סרטון
        const videoFileInput = document.getElementById('hero-bg-video-file');
        if (videoFileInput) {
            videoFileInput.addEventListener('change', (e) => {
                this.handleVideoUpload(e.target.files[0]);
            });
        }
    }

    /**
     * החלפת שדות רקע לפי סוג
     */
    toggleBackgroundFields(type) {
        const fields = document.querySelectorAll('.bg-type-field');
        fields.forEach(field => field.style.display = 'none');
        
        const activeField = document.getElementById(`hero-bg-${type}-field`);
        if (activeField) {
            activeField.style.display = 'block';
        }
    }

    /**
     * החלפת שדות סרטון לפי סוג
     */
    toggleVideoFields(type) {
        const fileField = document.getElementById('hero-video-file-field');
        const urlField = document.getElementById('hero-video-url-field');
        
        if (fileField && urlField) {
            fileField.style.display = type === 'file' ? 'block' : 'none';
            urlField.style.display = type === 'url' ? 'block' : 'none';
        }
    }

    /**
     * החלפת שדה שכבת כיסוי
     */
    toggleVideoOverlay(show) {
        const overlayField = document.getElementById('hero-video-overlay-color-field');
        if (overlayField) {
            overlayField.style.display = show ? 'block' : 'none';
        }
    }

    /**
     * הוספת כפתור חדש
     */
    addButton() {
        const buttonsList = document.getElementById('hero-buttons-list');
        if (!buttonsList) return;

        const newButton = { label: '', link: '', style: 'solid' };
        const newIndex = buttonsList.children.length;
        
        const buttonHtml = this.generateButtonField(newButton, newIndex);
        buttonsList.insertAdjacentHTML('beforeend', buttonHtml);
    }

    /**
     * מחיקת כפתור
     */
    deleteButton(buttonField) {
        if (confirm('האם אתה בטוח שברצונך למחוק את הכפתור?')) {
            buttonField.remove();
        }
    }

    /**
     * הגדרת מאזינים לשדות
     */
    setupFieldListeners() {
        // מאזינים לשינויים בזמן אמת
        const fields = [
            'hero-title', 'hero-subtitle', 'hero-content',
            'hero-title-color', 'hero-title-size',
            'hero-subtitle-color', 'hero-subtitle-size',
            'hero-content-color', 'hero-content-size',
            'hero-bg-color', 'hero-gradient-start', 'hero-gradient-end',
            'hero-bg-video-url', 'hero-video-type',
            'hero-video-autoplay', 'hero-video-loop', 'hero-video-muted', 'hero-video-overlay',
            'hero-video-overlay-color', 'hero-video-overlay-opacity',
            'hero-button-bg-color', 'hero-button-text-color', 'hero-button-font-size',
            'hero-padding-top', 'hero-padding-bottom', 'hero-padding-left', 'hero-padding-right',
            'hero-margin-top', 'hero-margin-bottom', 'hero-margin-left', 'hero-margin-right',
            'hero-custom-css'
        ];

        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', () => {
                    this.updatePreview();
                });
            }
        });
    }

    /**
     * עדכון תצוגה מקדימה
     */
    updatePreview() {
        // כאן נוסיף את הלוגיקה לעדכון התצוגה המקדימה
        if (window.sectionsManager) {
            window.sectionsManager.updateSectionSettings(window.sectionsManager.currentSection);
        }
    }

    /**
     * איסוף הגדרות מהטופס
     */
    collectSettings() {
        const settings = {
            // תוכן
            title: document.getElementById('hero-title')?.value || '',
            subtitle: document.getElementById('hero-subtitle')?.value || '',
            content: document.getElementById('hero-content')?.value || '',
            
            // רקע
            bg_type: document.getElementById('hero-bg-type')?.value || 'color',
            bg_color: document.getElementById('hero-bg-color')?.value || '#1e40af',
            gradient_start: document.getElementById('hero-gradient-start')?.value || '#1e40af',
            gradient_end: document.getElementById('hero-gradient-end')?.value || '#3b82f6',
            gradient_direction: document.getElementById('hero-gradient-direction')?.value || 'to bottom',
            bg_image_size: document.getElementById('hero-bg-image-size')?.value || 'cover',
            
            // סרטון
            video_type: document.getElementById('hero-video-type')?.value || 'file',
            bg_video_url: document.getElementById('hero-bg-video-url')?.value || '',
            video_autoplay: document.getElementById('hero-video-autoplay')?.checked || false,
            video_loop: document.getElementById('hero-video-loop')?.checked || false,
            video_muted: document.getElementById('hero-video-muted')?.checked || false,
            video_overlay: document.getElementById('hero-video-overlay')?.checked || false,
            video_overlay_color: this.getVideoOverlayColor(),
            
            // טיפוגרפיה
            title_color: document.getElementById('hero-title-color')?.value || '#ffffff',
            title_size: parseInt(document.getElementById('hero-title-size')?.value) || 48,
            subtitle_color: document.getElementById('hero-subtitle-color')?.value || '#e5e7eb',
            subtitle_size: parseInt(document.getElementById('hero-subtitle-size')?.value) || 24,
            content_color: document.getElementById('hero-content-color')?.value || '#d1d5db',
            content_size: parseInt(document.getElementById('hero-content-size')?.value) || 16,
            
            // כפתורים
            button_bg_color: document.getElementById('hero-button-bg-color')?.value || '#f59e0b',
            button_text_color: document.getElementById('hero-button-text-color')?.value || '#ffffff',
            button_font_size: parseInt(document.getElementById('hero-button-font-size')?.value) || 16,
            
            // מרווחים
            padding_top: parseInt(document.getElementById('hero-padding-top')?.value) || 80,
            padding_bottom: parseInt(document.getElementById('hero-padding-bottom')?.value) || 80,
            padding_left: parseInt(document.getElementById('hero-padding-left')?.value) || 20,
            padding_right: parseInt(document.getElementById('hero-padding-right')?.value) || 20,
            margin_top: parseInt(document.getElementById('hero-margin-top')?.value) || 0,
            margin_bottom: parseInt(document.getElementById('hero-margin-bottom')?.value) || 0,
            margin_left: parseInt(document.getElementById('hero-margin-left')?.value) || 0,
            margin_right: parseInt(document.getElementById('hero-margin-right')?.value) || 0,
            
            // CSS מותאם
            custom_css: document.getElementById('hero-custom-css')?.value || '',
            
            // כפתורים
            buttons: this.collectButtons()
        };

        // הסרת שדות ריקים
        Object.keys(settings).forEach(key => {
            if (settings[key] === '' || settings[key] === null || settings[key] === undefined) {
                delete settings[key];
            }
        });

        return settings;
    }

    /**
     * איסוף נתוני כפתורים
     */
    collectButtons() {
        const buttons = [];
        const buttonFields = document.querySelectorAll('.button-field');
        
        buttonFields.forEach(field => {
            const label = field.querySelector('.button-label')?.value || '';
            const link = field.querySelector('.button-link')?.value || '';
            const style = field.querySelector('.button-style')?.value || 'solid';
            
            // הוספת כפתור רק אם יש תווית
            if (label.trim()) {
                buttons.push({ label, link, style });
            }
        });
        
        return buttons;
    }

    /**
     * המרת צבע rgba לפורמט hex
     */
    convertRgbaToHex(color) {
        if (!color || !color.includes('rgba')) {
            return color;
        }
        
        // חילוץ הערכים מ-rgba(r,g,b,a)
        const match = color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*[\d.]+)?\)/);
        if (!match) return '#000000';
        
        const r = parseInt(match[1]);
        const g = parseInt(match[2]);
        const b = parseInt(match[3]);
        
        // המרה להקס
        const toHex = (n) => {
            const hex = n.toString(16);
            return hex.length === 1 ? '0' + hex : hex;
        };
        
        return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
    }

    /**
     * טיפול בהעלאת סרטון
     */
    async handleVideoUpload(file) {
        if (!file) return;

        // בדיקת סוג הקובץ
        const allowedTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/avi', 'video/mov'];
        if (!allowedTypes.includes(file.type)) {
            alert('סוג קובץ לא נתמך. נתמכים: MP4, WebM, OGG, AVI, MOV');
            return;
        }

        // בדיקת גודל הקובץ (100MB)
        const maxSize = 100 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('הקובץ גדול מדי. גודל מקסימלי: 100MB');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('video', file);

            const response = await fetch('../api/upload-video.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('שגיאה בהעלאת הסרטון');
            }

            const result = await response.json();
            
            if (result.success) {
                // עדכון השדה עם ה-URL החדש
                const videoUrlInput = document.getElementById('hero-bg-video-url');
                if (videoUrlInput) {
                    videoUrlInput.value = result.url;
                }
                
                // עדכון התצוגה המקדימה
                this.updatePreview();
                
                alert('הסרטון הועלה בהצלחה!');
            } else {
                throw new Error(result.error || 'שגיאה בהעלאת הסרטון');
            }
        } catch (error) {
            console.error('Upload error:', error);
            alert('שגיאה בהעלאת הסרטון: ' + error.message);
        }
    }

    // פונקציה לקבלת צבע הכיסוי של הסרטון
    getVideoOverlayColor() {
        try {
            const colorInput = document.getElementById('hero-video-overlay-color');
            const opacityInput = document.getElementById('hero-video-overlay-opacity');
            
            if (colorInput && colorInput.value) {
                const hexColor = colorInput.value;
                const opacity = opacityInput && opacityInput.value ? (parseInt(opacityInput.value) / 100) : 0.5;
                
                // המרת hex ל-rgba
                const r = parseInt(hexColor.slice(1, 3), 16);
                const g = parseInt(hexColor.slice(3, 5), 16);
                const b = parseInt(hexColor.slice(5, 7), 16);
                
                return `rgba(${r}, ${g}, ${b}, ${opacity})`;
            }
        } catch (error) {
            console.warn('Error getting video overlay color:', error);
        }
        
        return 'rgba(0, 0, 0, 0.5)'; // ברירת מחדל
    }
}

// יצירת אינסטנס גלובלי
window.heroSettingsManager = new HeroSettingsManager(); 