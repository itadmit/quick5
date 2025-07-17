/**
 * מנהל סקשנים - פונקציונליות בסיסית
 */
class SectionsManager {
    constructor(customizer) {
        console.log('SectionsManager constructor called');
        this.customizer = customizer;
        this.sections = [];
        this.currentSection = null;
        this.updateTimer = null;
        this.hasUnsavedChanges = false;
        this.isLoading = false; // flag למניעת טעינה כפולה
        this.database = new SectionsDatabase(this);
        this.sectionsUI = new SectionsSettingsUI(this);
        this.setupEventListeners();
    }

    /**
     * הגדרת מאזינים לאירועים
     */
    setupEventListeners() {
        // כפתור הוספת סקשן
        const addSectionBtn = document.getElementById('add-section-btn');
        const addSectionMain = document.getElementById('add-section-main');
        const addSectionModal = document.getElementById('add-section-modal');
        const closeAddSectionModal = document.getElementById('close-add-section-modal');

        [addSectionBtn, addSectionMain].forEach(btn => {
            if (btn) {
                btn.addEventListener('click', () => {
                    this.showAddSectionModal();
                });
            }
        });

        if (closeAddSectionModal) {
            closeAddSectionModal.addEventListener('click', () => {
                this.hideAddSectionModal();
            });
        }

        // כפתורי הוספת סקשן ספציפי
        document.querySelectorAll('.section-template').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const sectionType = e.currentTarget.dataset.section;
                console.log('Adding section:', sectionType);
                this.addSection(sectionType);
                this.hideAddSectionModal();
            });
        });
    }

    /**
     * הצגת מודל הוספת סקשן
     */
    showAddSectionModal() {
        const modal = document.getElementById('add-section-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    /**
     * הסתרת מודל הוספת סקשן
     */
    hideAddSectionModal() {
        const modal = document.getElementById('add-section-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    /**
     * הוספת סקשן חדש
     */
    async addSection(sectionType) {
        console.log('addSection called with:', sectionType);
        try {
            // יצירת ID ייחודי
            const sectionId = `${sectionType}-${Date.now()}`;
            
            // יצירת נתוני הסקשן
            const sectionData = {
                id: sectionId,
                type: sectionType,
                settings: this.getDefaultSectionSettings(sectionType),
                order: this.getNextOrder()
            };
            
            console.log('Section data:', sectionData);

            // הוספה לרשימה המקומית
            this.sections.push(sectionData);

            // עדכון הממשק
            this.renderSectionInList(sectionData);
            
            // רענון מלא של התצוגה המקדימה
            this.customizer.refreshPreview();

            // בחירת הסקשן החדש
            this.selectSection(sectionId);

            // סימון שיש שינויים לא שמורים
            this.markUnsavedChanges();

            this.customizer.showNotification('הסקשן נוסף (לא נשמר עדיין)', 'info');
        } catch (error) {
            console.error('Error adding section:', error);
            this.customizer.showNotification('שגיאה בהוספת הסקשן: ' + error.message, 'error');
        }
    }

    /**
     * מחיקת סקשן
     */
    async deleteSection(sectionId) {
        console.log('Deleting section:', sectionId);
        
        if (!confirm('האם אתה בטוח שברצונך למחוק את הסקשן?')) {
            return;
        }

        try {
            // מחיקה מהרשימה המקומית
            this.sections = this.sections.filter(section => section.id !== sectionId);

            // מחיקה מהממשק
            const sectionElement = document.querySelector(`[data-section="${sectionId}"]`);
            if (sectionElement) {
                sectionElement.remove();
            }

            // עדכון התצוגה המקדימה
            this.customizer.refreshPreview();

            // סימון שיש שינויים לא שמורים
            this.markUnsavedChanges();

            this.customizer.showNotification('הסקשן נמחק (לא נשמר עדיין)', 'info');
        } catch (error) {
            console.error('Error deleting section:', error);
            this.customizer.showNotification('שגיאה במחיקת הסקשן: ' + error.message, 'error');
        }
    }

    /**
     * בחירת סקשן
     */
    selectSection(sectionId) {
        console.log('Selecting section:', sectionId);
        
        // הסרת בחירה קודמת
        document.querySelectorAll('.section-item').forEach(item => {
            item.classList.remove('bg-blue-50', 'border-blue-300');
            item.classList.add('bg-gray-50', 'border-gray-200');
        });

        // הוספת בחירה חדשה
        const sectionElement = document.querySelector(`[data-section="${sectionId}"]`);
        if (sectionElement) {
            sectionElement.classList.remove('bg-gray-50', 'border-gray-200');
            sectionElement.classList.add('bg-blue-50', 'border-blue-300');
        }

        // גלילה לסקשן בתצוגה המקדימה
        this.scrollToSection(sectionId);
        this.highlightSectionInPreview(sectionId);

        // שמירת הסקשן הנוכחי
        this.currentSection = this.sections.find(s => s.id === sectionId);
    }

    /**
     * קבלת הגדרות ברירת מחדל לסקשן
     */
    getDefaultSectionSettings(sectionType) {
        const defaultSettings = {
            'hero': {
                title: 'ברוכים הבאים',
                subtitle: 'גלה את המוצרים הטובים ביותר',
                content: '',
                bg_type: 'color',
                bg_color: '#1e40af',
                gradient_start: '#1e40af',
                gradient_end: '#3b82f6',
                gradient_direction: 'to bottom',
                bg_image: '',
                bg_image_size: 'cover',
                bg_video_url: '',
                bg_video_file: '',
                video_type: 'file',
                video_autoplay: true,
                video_loop: true,
                video_muted: true,
                video_overlay: true,
                video_overlay_color: '#000000',
                title_color: '#ffffff',
                title_size: 48,
                subtitle_color: '#e5e7eb',
                subtitle_size: 24,
                content_color: '#d1d5db',
                content_size: 16,
                button_text: 'קנה עכשיו',
                button_link: '/products',
                button_bg_color: '#f59e0b',
                button_text_color: '#ffffff',
                button_font_size: 16,
                padding_top: 80,
                padding_bottom: 80,
                padding_left: 20,
                padding_right: 20,
                margin_top: 0,
                margin_bottom: 0,
                margin_left: 0,
                margin_right: 0,
                custom_css: '',
                buttons: []
            },
            'header': {
                store_name: 'החנות שלי',
                show_search: true,
                show_cart: true
            },
            'footer': {
                copyright: '© 2024 כל הזכויות שמורות',
                show_social: true,
                show_contact: true
            },
            'featured_products': {
                title: 'מוצרים מומלצים',
                count: 6,
                columns: 3,
                show_prices: true
            },
            'text': {
                title: 'כותרת',
                content: 'תוכן הטקסט כאן...',
                text_align: 'center',
                text_color: '#1f2937',
                background_color: '#ffffff'
            },
            'image': {
                image_url: '',
                alt_text: '',
                width: '100%',
                alignment: 'center'
            },
            'two-columns': {
                column1_title: 'עמודה ראשונה',
                column1_content: 'תוכן העמודה הראשונה',
                column2_title: 'עמודה שנייה',
                column2_content: 'תוכן העמודה השנייה',
                gap: '2rem'
            },
            'three-columns': {
                column1_title: 'עמודה 1',
                column1_content: 'תוכן עמודה 1',
                column2_title: 'עמודה 2',
                column2_content: 'תוכן עמודה 2',
                column3_title: 'עמודה 3',
                column3_content: 'תוכן עמודה 3',
                gap: '1.5rem'
            },
            'four-columns': {
                column1_title: 'עמודה 1',
                column1_content: 'תוכן עמודה 1',
                column2_title: 'עמודה 2',
                column2_content: 'תוכן עמודה 2',
                column3_title: 'עמודה 3',
                column3_content: 'תוכן עמודה 3',
                column4_title: 'עמודה 4',
                column4_content: 'תוכן עמודה 4',
                gap: '1rem'
            }
        };

        return defaultSettings[sectionType] || {};
    }

    /**
     * קבלת ברירות המחדל לסוג סקשן
     */
    getDefaultSettings(sectionType) {
        const defaultSettings = {
            'hero': {
                title: 'ברוכים הבאים',
                subtitle: 'גלה את המוצרים הטובים ביותר',
                content: '',
                bg_type: 'color',
                bg_color: '#1e40af',
                gradient_start: '#1e40af',
                gradient_end: '#3b82f6',
                gradient_direction: 'to bottom',
                bg_image: '',
                bg_image_size: 'cover',
                bg_video_url: '',
                bg_video_file: '',
                video_type: 'file',
                video_autoplay: true,
                video_loop: true,
                video_muted: true,
                video_overlay: true,
                video_overlay_color: '#000000',
                title_color: '#ffffff',
                title_size: 48,
                subtitle_color: '#e5e7eb',
                subtitle_size: 24,
                content_color: '#d1d5db',
                content_size: 16,
                button_text: 'קנה עכשיו',
                button_link: '/products',
                button_bg_color: '#f59e0b',
                button_text_color: '#ffffff',
                button_font_size: 16,
                padding_top: 80,
                padding_bottom: 80,
                padding_left: 20,
                padding_right: 20,
                margin_top: 0,
                margin_bottom: 0,
                margin_left: 0,
                margin_right: 0,
                custom_css: '',
                buttons: []
            },
            'header': {
                store_name: 'החנות שלי',
                show_search: true,
                show_cart: true,
                background_color: '#ffffff',
                text_color: '#1f2937'
            },
            'footer': {
                copyright: '© 2024 כל הזכויות שמורות',
                show_social: true,
                show_contact: true,
                background_color: '#1f2937',
                text_color: '#ffffff'
            },
            'featured-products': {
                title: 'מוצרים מומלצים',
                products_count: 4,
                columns: 4,
                show_price: true,
                show_add_to_cart: true
            }
        };
        
        return defaultSettings[sectionType] || {};
    }

    /**
     * קבלת הסדר הבא לסקשן
     */
    getNextOrder() {
        if (this.sections.length === 0) return 1;
        return Math.max(...this.sections.map(s => s.order || 0)) + 1;
    }

    /**
     * רינדור סקשן ברשימה
     */
    renderSectionInList(sectionData) {
        const sectionsList = document.getElementById('sections-list');
        if (!sectionsList) return;

        const sectionElement = this.createSectionElement(sectionData);
        
        // הוספה לפני הפוטר אם קיים
        const footerSection = sectionsList.querySelector('[data-section="footer"]');
        if (footerSection) {
            sectionsList.insertBefore(sectionElement, footerSection);
        } else {
            sectionsList.appendChild(sectionElement);
        }

        this.setupSectionEventListeners(sectionElement);
    }

    /**
     * יצירת אלמנט סקשן
     */
    createSectionElement(sectionData) {
        const sectionInfo = this.getSectionInfo(sectionData.type);
        
        const sectionElement = document.createElement('div');
        sectionElement.className = 'section-item bg-gray-50 border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-100 transition-colors';
        sectionElement.dataset.section = sectionData.id;
        
        sectionElement.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="${sectionInfo.icon} text-gray-600 ml-3"></i>
                    <div>
                        <h3 class="font-medium text-gray-900">${sectionInfo.title}</h3>
                        <p class="text-sm text-gray-500">${sectionInfo.description}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button class="section-settings-btn p-1 text-gray-400 hover:text-gray-600" title="הגדרות">
                        <i class="ri-settings-3-line"></i>
                    </button>
                    <button class="section-delete-btn p-1 text-gray-400 hover:text-red-600" title="מחק">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </div>
        `;
        
        return sectionElement;
    }

    /**
     * קבלת מידע על סוג הסקשן
     */
    getSectionInfo(sectionType) {
        const sectionTypes = {
            'hero': { title: 'Hero', description: 'תמונת פתיחה עם טקסט', icon: 'ri-image-line' },
            'header': { title: 'Header', description: 'כותרת האתר', icon: 'ri-layout-top-line' },
            'footer': { title: 'Footer', description: 'תחתית האתר', icon: 'ri-layout-bottom-line' },
            'featured_products': { title: 'מוצרים מומלצים', description: 'תצוגת מוצרים', icon: 'ri-star-line' },
            'text': { title: 'טקסט', description: 'בלוק טקסט פשוט', icon: 'ri-text' },
            'image': { title: 'תמונה', description: 'תמונה בודדת', icon: 'ri-image-2-line' },
            'two-columns': { title: 'שתי עמודות', description: 'פריסה של שתי עמודות', icon: 'ri-layout-column-line' },
            'three-columns': { title: 'שלוש עמודות', description: 'פריסה של שלוש עמודות', icon: 'ri-layout-3-line' },
            'four-columns': { title: 'ארבע עמודות', description: 'פריסה של ארבע עמודות', icon: 'ri-layout-4-line' }
        };
        
        return sectionTypes[sectionType] || { title: sectionType, description: 'סקשן מותאם', icon: 'ri-layout-line' };
    }

    /**
     * הגדרת מאזינים לסקשן
     */
    setupSectionEventListeners(sectionElement) {
        const sectionId = sectionElement.dataset.section;

        // לחיצה על הסקשן
        sectionElement.addEventListener('click', (e) => {
            if (!e.target.closest('button')) {
                this.selectSection(sectionId);
            }
        });

        // כפתור הגדרות
        const settingsBtn = sectionElement.querySelector('.section-settings-btn');
        if (settingsBtn) {
            settingsBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.showSectionSettings(sectionId);
            });
        }

        // כפתור מחיקה
        const deleteBtn = sectionElement.querySelector('.section-delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.deleteSection(sectionId);
            });
        }
    }

    /**
     * גלילה אל הסקשן בעמוד
     */
    scrollToSection(sectionId) {
        const previewFrame = document.getElementById('preview-frame');
        if (!previewFrame) {
            console.log('Preview frame not found');
            return;
        }

        try {
            const previewDoc = previewFrame.contentDocument || previewFrame.contentWindow.document;
            let targetElement = null;

            // חיפוש הסקשן בעמוד
            if (sectionId === 'header') {
                targetElement = previewDoc.querySelector('header') || previewDoc.querySelector('.header');
            } else if (sectionId === 'footer') {
                targetElement = previewDoc.querySelector('footer') || previewDoc.querySelector('.footer');
            } else {
                targetElement = previewDoc.querySelector(`[data-section="${sectionId}"]`) || 
                               previewDoc.querySelector(`#${sectionId}`) ||
                               previewDoc.querySelector(`.${sectionId}`);
            }

            console.log('Scrolling to section:', sectionId, 'Element found:', !!targetElement);

            if (targetElement) {
                targetElement.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }
        } catch (e) {
            console.log('CORS error in scrollToSection, using postMessage instead');
            // שליחת הודעה לגלילה
            previewFrame.contentWindow.postMessage({
                type: 'scroll-to-section',
                sectionId: sectionId
            }, '*');
        }
    }

    /**
     * הדגשת הסקשן בתצוגה המקדימה
     */
    highlightSectionInPreview(sectionId) {
        const previewFrame = document.getElementById('preview-frame');
        if (!previewFrame) return;

        try {
            const previewDoc = previewFrame.contentDocument || previewFrame.contentWindow.document;
            
            // הסרת הדגשה קודמת
            previewDoc.querySelectorAll('.section-highlight').forEach(el => {
                el.classList.remove('section-highlight');
            });

            // הוספת הדגשה חדשה
            let targetElement = null;
            if (sectionId === 'header') {
                targetElement = previewDoc.querySelector('header') || previewDoc.querySelector('.header');
            } else if (sectionId === 'footer') {
                targetElement = previewDoc.querySelector('footer') || previewDoc.querySelector('.footer');
            } else {
                targetElement = previewDoc.querySelector(`[data-section="${sectionId}"]`) || 
                               previewDoc.querySelector(`#${sectionId}`) ||
                               previewDoc.querySelector(`.${sectionId}`);
            }

            console.log('Highlighting section:', sectionId, 'Element found:', !!targetElement);

            if (targetElement) {
                targetElement.classList.add('section-highlight');
                
                // הסרת ההדגשה אחרי 3 שניות
                setTimeout(() => {
                    targetElement.classList.remove('section-highlight');
                }, 3000);
            }
        } catch (e) {
            console.log('CORS error in highlightSectionInPreview, using postMessage instead');
            // שליחת הודעה להדגשה
            previewFrame.contentWindow.postMessage({
                type: 'highlight-section',
                sectionId: sectionId
            }, '*');
        }
    }

    /**
     * סימון שיש שינויים לא שמורים
     */
    markUnsavedChanges() {
        this.hasUnsavedChanges = true;
        const notice = document.getElementById('unsaved-changes-notice');
        if (notice) {
            notice.classList.remove('hidden');
        }
    }

    /**
     * סימון שאין שינויים לא שמורים
     */
    markChangesSaved() {
        this.hasUnsavedChanges = false;
        const notice = document.getElementById('unsaved-changes-notice');
        if (notice) {
            notice.classList.add('hidden');
        }
        }

    /**
     * הצגת הגדרות הסקשן
     */
    showSectionSettings(sectionId) {
        console.log('Showing section settings for:', sectionId);
        // הפונקציה תמומש בקובץ נפרד
        if (this.sectionsUI) {
            this.sectionsUI.showSectionSettings(sectionId);
        }
    }

    /**
     * טעינת סקשנים מהדטאבייס
     */
    async loadSections() {
        // מניעת טעינה כפולה
        if (this.isLoading) {
            console.log('Sections already loading, skipping...');
            return;
        }
        
        this.isLoading = true;
        console.log('Loading sections...');
        console.trace('loadSections called from:');
        
        try {
            // טעינה מהדטאבייס
            const response = await fetch('../api/get-sections.php');
            console.log('Response status:', response.status);
            if (response.ok) {
                const data = await response.json();
                console.log('Loaded sections from database:', data);
                this.sections = data.sections || [];
                this.renderAllSections();
            } else {
                console.error('Failed to load sections:', response.status);
            }
        } catch (error) {
            console.error('Error loading sections:', error);
        } finally {
            this.isLoading = false;
        }
    }

    /**
     * רינדור כל הסקשנים
     */
    renderAllSections() {
        console.log('Rendering all sections:', this.sections);
        const sectionsList = document.getElementById('sections-list');
        if (!sectionsList) {
            console.error('sections-list element not found');
            return;
        }

        // ניקוי הרשימה
        sectionsList.innerHTML = '';

        // הוספת הסקשנים
        this.sections.forEach(section => {
            // הנתונים כבר בפורמט הנכון מה-API
            this.renderSectionInList(section);
        });
    }

    /**
     * שמירת כל הסקשנים למסד הנתונים
     */
    async saveToDatabase() {
        return await this.database.saveAllSections();
    }

    /**
     * ביטול שינויים
     */
    async cancelChanges() {
        return await this.database.cancelChanges();
    }

    /**
     * הצגת רשימת הסקשנים
     */
    showSectionsList() {
        // החזרת הקונטיינר לתצוגה הרגילה
        const container = document.getElementById('sections-container');
        if (container) {
            container.innerHTML = `
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-900">סקשנים</h2>
                        <button id="add-section-btn" class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="ri-add-line"></i>
                        </button>
                    </div>

                    <!-- Status Message -->
                    <div id="unsaved-changes-notice" class="hidden mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="ri-information-line text-yellow-600 ml-2"></i>
                            <span class="text-sm text-yellow-800">יש לך שינויים לא שמורים. לחץ על "שמור שינויים" כדי לשמור אותם.</span>
                        </div>
                    </div>

                    <!-- Sections List -->
                    <div id="sections-list" class="space-y-3">
                        <!-- הסקשנים ייטענו דינמית מהדטאבייס -->
                    </div>
                </div>
            `;
            
            // הוספת מאזין לכפתור הוספת סקשן
            const addBtn = document.getElementById('add-section-btn');
            if (addBtn) {
                addBtn.addEventListener('click', () => {
                    this.showAddSectionModal();
                });
            }
        }
        
        this.renderAllSections();
    }

    /**
     * עדכון סקשן בתצוגה המקדימה
     */
    updateSectionInPreview(section) {
        // עדכון הסקשן בתצוגה המקדימה
        const previewFrame = document.getElementById('preview-frame');
        if (previewFrame && previewFrame.contentWindow) {
            previewFrame.contentWindow.postMessage({
                type: 'updateSection',
                section: section
            }, '*');
        }
    }

    /**
     * סימון שיש שינויים שלא נשמרו
     */
    markAsChanged() {
        this.hasUnsavedChanges = true;
        const notice = document.getElementById('unsaved-changes-notice');
        if (notice) {
            notice.classList.remove('hidden');
        }
    }
}