/**
 * Builder Core - ליבת הבילדר
 * מנהל את הפונקציונליות הבסיסית של הבילדר
 */

class BuilderCore {
    constructor() {
        this.currentDevice = 'desktop';
        this.currentSection = null;
        this.sections = window.builderData?.sections || [];
        this.storeId = window.builderData?.storeId;
        this.pageId = window.builderData?.pageId;
        this.isPublished = window.builderData?.isPublished || false;
        
        this.init();
    }
    
    /**
     * אתחול הבילדר
     */
    init() {
        this.bindEvents();
        this.loadExistingSections();
        this.setupDeviceToggle();
        // this.setupAutoSave(); // מבוטל - שמירה אוטומטית
    }
    
    /**
     * קישור אירועים
     */
    bindEvents() {
        // כפתורי הוספת סקשן
        document.querySelectorAll('.add-section-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const sectionType = e.target.dataset.section;
                this.addSection(sectionType);
            });
        });
        
        // כפתורי שמירה
        document.getElementById('saveBtn')?.addEventListener('click', () => {
            this.savePage();
        });
        
        document.getElementById('publishBtn')?.addEventListener('click', () => {
            this.publishPage();
        });
        
        document.getElementById('previewBtn')?.addEventListener('click', () => {
            this.openPreview();
        });
        
        // טאבים
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.switchTab(e.target.dataset.tab);
            });
        });
    }
    
    /**
     * הוספת סקשן חדש
     */
    addSection(sectionType) {
        const sectionId = sectionType + '_' + Date.now();
        const newSection = {
            id: sectionId,
            type: sectionType,
            settings: this.getDefaultSettings(sectionType)
        };
        
        this.sections.push(newSection);
        this.renderSectionInList(newSection);
        this.updatePreview();
        this.selectSection(sectionId);
        
        // Auto-save מבוטל
        // this.autoSave();
    }
    
    /**
     * הגדרות ברירת מחדל לסקשן
     */
    getDefaultSettings(sectionType) {
        const defaults = {
            hero: {
                title: 'ברוכים הבאים לחנות שלנו',
                subtitle: 'גלו את המוצרים הטובים ביותר במחירים הטובים ביותר',
                titleColor: '#FFFFFF',
                subtitleColor: '#E5E7EB',
                bgColor: '#3B82F6',
                heightDesktop: '75vh',
                bgType: 'color',
                heroTitleTextType: 'h1',
                heroSubtitleTextType: 'p',
                buttons: [
                    {
                        text: 'קנה עכשיו',
                        url: '#',
                        style: 'primary',
                        openInNewTab: false,
                        paddingTop: '12',
                        paddingBottom: '12',
                        paddingLeft: '24',
                        paddingRight: '24',
                        marginTop: '0',
                        marginBottom: '8',
                        marginLeft: '4',
                        marginRight: '4'
                    }
                ]
            },
            'category-grid': {
                title: 'הקטגוריות שלנו',
                columns: 3,
                showTitle: true
            },
            'product-slider': {
                title: 'מוצרים מומלצים',
                productsCount: 8,
                showTitle: true
            },
            'text-block': {
                content: 'הכנס כאן את התוכן שלך',
                textAlign: 'right'
            }
        };
        
        return defaults[sectionType] || {};
    }
    
    /**
     * רינדור סקשן ברשימה
     */
    renderSectionInList(section) {
        const container = document.getElementById('existingSections');
        if (!container) return;
        
        const sectionElement = document.createElement('div');
        sectionElement.className = 'section-item p-3 bg-white border rounded cursor-pointer hover:bg-gray-50';
        sectionElement.dataset.sectionId = section.id;
        
        sectionElement.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="ri-drag-move-2-line text-gray-400"></i>
                    <span class="font-medium">${this.getSectionDisplayName(section.type)}</span>
                </div>
                <div class="flex items-center gap-1">
                    <button class="edit-section-btn p-1 text-blue-600 hover:bg-blue-100 rounded" data-section-id="${section.id}">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button class="delete-section-btn p-1 text-red-600 hover:bg-red-100 rounded" data-section-id="${section.id}">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </div>
        `;
        
        // קישור אירועים
        sectionElement.querySelector('.edit-section-btn').addEventListener('click', (e) => {
            e.stopPropagation();
            this.selectSection(section.id);
        });
        
        sectionElement.querySelector('.delete-section-btn').addEventListener('click', (e) => {
            e.stopPropagation();
            this.deleteSection(section.id);
        });
        
        sectionElement.addEventListener('click', () => {
            this.selectSection(section.id);
        });
        
        container.appendChild(sectionElement);
    }
    
    /**
     * קבלת שם תצוגה לסקשן
     */
    getSectionDisplayName(sectionType) {
        const names = {
            hero: 'הירו',
            'category-grid': 'גריד קטגוריות',
            'product-slider': 'סלידר מוצרים',
            'text-block': 'בלוק טקסט'
        };
        
        return names[sectionType] || sectionType;
    }
    
    /**
     * בחירת סקשן לעריכה
     */
    selectSection(sectionId) {
        const section = this.sections.find(s => s.id === sectionId);
        if (!section) return;
        
        this.currentSection = section;
        
        // עדכון UI
        document.querySelectorAll('.section-item').forEach(item => {
            item.classList.remove('ring-2', 'ring-blue-500');
        });
        
        const sectionElement = document.querySelector(`[data-section-id="${sectionId}"]`);
        if (sectionElement) {
            sectionElement.classList.add('ring-2', 'ring-blue-500');
        }
        
        // עדכון טאב הגדרות
        this.switchTab('settings');
        this.loadSectionSettings(section);
    }
    
    /**
     * טעינת הגדרות סקשן
     */
    loadSectionSettings(section) {
        const settingsContainer = document.getElementById('sectionSettings');
        if (!settingsContainer) return;
        
        // טעינת תוכן הגדרות לפי סוג הסקשן
        fetch(`settings/sections/${section.type}/settings.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                sectionId: section.id,
                settings: section.settings
            })
        })
        .then(response => response.text())
        .then(html => {
            settingsContainer.innerHTML = html;
            this.bindSettingsEvents();
        })
        .catch(error => {
            console.error('Error loading section settings:', error);
            settingsContainer.innerHTML = `
                <div class="text-center text-red-500 py-8">
                    <i class="ri-error-warning-line text-2xl mb-2 block"></i>
                    <p class="text-sm">שגיאה בטעינת הגדרות הסקשן</p>
                </div>
            `;
        });
    }
    
    /**
     * קישור אירועי הגדרות
     */
    bindSettingsEvents() {
        const settingsContainer = document.getElementById('sectionSettings');
        if (!settingsContainer) return;
        
        // קישור כל השדות לעדכון בזמן אמת
        settingsContainer.querySelectorAll('input, textarea, select').forEach(input => {
            input.addEventListener('input', () => {
                this.updateSectionSetting(input);
            });
        });
    }
    
    /**
     * עדכון הגדרת סקשן
     */
    updateSectionSetting(input) {
        if (!this.currentSection) return;
        
        console.log('🔧 DEBUG: Updating setting', input.name, input.value);
        
        const settingName = input.name;
        let settingValue = input.type === 'checkbox' ? input.checked : input.value;
        
        // Handle button repeater arrays
        if (settingName.includes('buttons[')) {
            // Parse button array structure
            const match = settingName.match(/buttons\[(\d+)\]\[(.+)\]/);
            if (match) {
                const buttonIndex = parseInt(match[1]);
                const buttonProperty = match[2];
                
                // Initialize buttons array if it doesn't exist
                if (!this.currentSection.settings.buttons) {
                    this.currentSection.settings.buttons = [];
                }
                
                // Initialize button object if it doesn't exist
                if (!this.currentSection.settings.buttons[buttonIndex]) {
                    this.currentSection.settings.buttons[buttonIndex] = {};
                }
                
                // Set the specific button property
                this.currentSection.settings.buttons[buttonIndex][buttonProperty] = settingValue;
                
                console.log('🔧 DEBUG: Updated button', buttonIndex, buttonProperty, settingValue);
            }
        } else {
            // Regular setting update
            this.currentSection.settings[settingName] = settingValue;
        }
        
        console.log('🔧 DEBUG: Current section after update', this.currentSection);
        
        // עדכון התצוגה המקדימה מייד
        this.updatePreview();
        
        // Auto-save מבוטל
        // this.autoSave();
    }
    
    /**
     * מחיקת סקשן
     */
    deleteSection(sectionId) {
        if (!confirm('האם אתה בטוח שברצונך למחוק את הסקשן?')) {
            return;
        }
        
        this.sections = this.sections.filter(s => s.id !== sectionId);
        
        // הסרה מה-UI
        const sectionElement = document.querySelector(`[data-section-id="${sectionId}"]`);
        if (sectionElement) {
            sectionElement.remove();
        }
        
        // ניקוי הגדרות אם זה הסקשן הנוכחי
        if (this.currentSection?.id === sectionId) {
            this.currentSection = null;
            document.getElementById('sectionSettings').innerHTML = `
                <div class="text-center text-gray-500 py-8">
                    <i class="ri-settings-3-line text-2xl mb-2 block"></i>
                    <p class="text-sm">בחר סקשן לעריכה</p>
                </div>
            `;
        }
        
        this.updatePreview();
        // this.autoSave(); // מבוטל - שמירה אוטומטית
    }
    
    /**
     * עדכון התצוגה המקדימה
     */
    updatePreview() {
        const iframe = document.getElementById('previewFrame');
        if (!iframe) return;
        
        // שליחת נתונים מעודכנים ל-iframe
        iframe.contentWindow?.postMessage({
            type: 'updateSections',
            sections: this.sections
        }, '*');
    }
    
    /**
     * החלפת טאב
     */
    switchTab(tabName) {
        // עדכון כפתורי טאב
        document.querySelectorAll('.tab-btn').forEach(btn => {
            if (btn.dataset.tab === tabName) {
                btn.classList.add('border-blue-500', 'text-blue-600', 'bg-blue-50');
                btn.classList.remove('border-transparent', 'text-gray-500');
            } else {
                btn.classList.remove('border-blue-500', 'text-blue-600', 'bg-blue-50');
                btn.classList.add('border-transparent', 'text-gray-500');
            }
        });
        
        // הצגת תוכן טאב
        document.querySelectorAll('.tab-panel').forEach(panel => {
            if (panel.id === tabName + 'Tab') {
                panel.classList.remove('hidden');
            } else {
                panel.classList.add('hidden');
            }
        });
    }
    
    /**
     * הגדרת החלפת מכשירים
     */
    setupDeviceToggle() {
        document.querySelectorAll('.device-toggle').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const device = e.target.dataset.device;
                this.switchDevice(device);
            });
        });
    }
    
    /**
     * החלפת מכשיר
     */
    switchDevice(device) {
        this.currentDevice = device;
        
        // עדכון כפתורים
        document.querySelectorAll('.device-toggle').forEach(btn => {
            if (btn.dataset.device === device) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // עדכון גודל iframe
        const iframe = document.getElementById('previewFrame');
        if (iframe) {
            const container = iframe.parentElement;
            
            switch (device) {
                case 'mobile':
                    container.style.width = '375px';
                    container.style.margin = '20px auto';
                    break;
                case 'tablet':
                    container.style.width = '768px';
                    container.style.margin = '20px auto';
                    break;
                default: // desktop
                    container.style.width = '100%';
                    container.style.margin = '0';
                    break;
            }
        }
    }
    
    /**
     * טעינת סקשנים קיימים
     */
    loadExistingSections() {
        const container = document.getElementById('existingSections');
        if (!container) return;
        
        container.innerHTML = '';
        
        this.sections.forEach(section => {
            this.renderSectionInList(section);
        });
    }
    
    /**
     * שמירת דף
     */
    async savePage() {
        console.log('🔧 DEBUG: Starting save...', {
            pageId: this.pageId,
            storeId: this.storeId,
            sectionsCount: this.sections.length,
            sections: this.sections
        });
        
        try {
            const requestData = {
                pageId: this.pageId,
                storeId: this.storeId,
                sections: this.sections
            };
            
            console.log('🔧 DEBUG: Sending request to api/save-page.php', requestData);
            
            const response = await fetch('api/save-page.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            });
            
            console.log('🔧 DEBUG: Response status:', response.status);
            console.log('🔧 DEBUG: Response headers:', Object.fromEntries(response.headers.entries()));
            
            const responseText = await response.text();
            console.log('🔧 DEBUG: Raw response:', responseText);
            
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (parseError) {
                console.error('🔧 DEBUG: JSON parse error:', parseError);
                console.error('🔧 DEBUG: Response was not valid JSON:', responseText);
                throw new Error('התגובה מהשרת לא תקינה');
            }
            
            console.log('🔧 DEBUG: Parsed result:', result);
            
            if (result.success) {
                this.showNotification('הדף נשמר בהצלחה', 'success');
                console.log('🔧 DEBUG: Save successful!');
            } else {
                this.showNotification('שגיאה בשמירת הדף: ' + (result.message || 'לא ידוע'), 'error');
                console.error('🔧 DEBUG: Save failed:', result);
            }
        } catch (error) {
            console.error('🔧 DEBUG: Save error:', error);
            this.showNotification('שגיאה בשמירת הדף: ' + error.message, 'error');
        }
    }
    
    /**
     * פרסום דף
     */
    async publishPage() {
        try {
            await this.savePage(); // שמירה קודם
            
            const response = await fetch('../api/publish-page.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    pageId: this.pageId,
                    storeId: this.storeId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.isPublished = true;
                this.showNotification('הדף פורסם בהצלחה', 'success');
                
                // עדכון כפתור
                const publishBtn = document.getElementById('publishBtn');
                if (publishBtn) {
                    publishBtn.innerHTML = '<i class="ri-check-line ml-1"></i>פורסם';
                    publishBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                    publishBtn.classList.add('bg-gray-600', 'hover:bg-gray-700');
                }
            } else {
                this.showNotification('שגיאה בפרסום הדף', 'error');
            }
        } catch (error) {
            console.error('Publish error:', error);
            this.showNotification('שגיאה בפרסום הדף', 'error');
        }
    }
    
    /**
     * פתיחת תצוגה מקדימה
     */
    openPreview() {
        const previewUrl = `../store-front/home.php?preview=1&store=${encodeURIComponent(window.builderData?.storeSlug || '')}`;
        window.open(previewUrl, '_blank');
    }
    
    /**
     * הגדרת שמירה אוטומטית
     */
    setupAutoSave() {
        setInterval(() => {
            this.autoSave();
        }, 30000); // כל 30 שניות
    }
    
    /**
     * שמירה אוטומטית
     */
    async autoSave() {
        if (this.sections.length === 0) return;
        
        try {
            await fetch('../api/auto-save-page.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    pageId: this.pageId,
                    storeId: this.storeId,
                    sections: this.sections
                })
            });
        } catch (error) {
            console.error('Auto-save error:', error);
        }
    }
    
    /**
     * הצגת התראה
     */
    showNotification(message, type = 'info') {
        // יצירת התראה זמנית
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded shadow-lg ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// אתחול הבילדר כשהדף נטען
document.addEventListener('DOMContentLoaded', () => {
    window.builderCore = new BuilderCore();
}); 