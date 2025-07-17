/**
 * Builder Main - קובץ JS ראשי של הבילדר
 * מחבר בין כל הקומפוננטים ומנהל את המצב הכללי
 */

class Builder {
    constructor() {
        this.core = null;
        this.renderIframe = null;
        this.isInitialized = false;
        
        this.init();
    }
    
    /**
     * אתחול הבילדר
     */
    init() {
        // המתנה לטעינת כל הקומפוננטים
        this.waitForComponents();
    }
    
    /**
     * המתנה לטעינת כל הקומפוננטים
     */
    waitForComponents() {
        const checkComponents = () => {
            if (window.builderCore && window.renderIframe) {
                this.core = window.builderCore;
                this.renderIframe = window.renderIframe;
                this.connectComponents();
                this.isInitialized = true;
            } else {
                setTimeout(checkComponents, 100);
            }
        };
        
        checkComponents();
    }
    
    /**
     * חיבור בין הקומפוננטים
     */
    connectComponents() {
        // חיבור הליבה לרינדר
        this.core.renderIframe = this.renderIframe;
        this.renderIframe.core = this.core;
        
        // Override של פונקציית עדכון התצוגה בליבה
        const originalUpdatePreview = this.core.updatePreview.bind(this.core);
        this.core.updatePreview = () => {
            this.renderIframe.updateSections(this.core.sections);
            originalUpdatePreview();
        };
        
        // Override של פונקציית החלפת מכשיר
        const originalSwitchDevice = this.core.switchDevice.bind(this.core);
        this.core.switchDevice = (device) => {
            originalSwitchDevice(device);
            this.renderIframe.setDevice(device);
        };
        
        // הגדרת אירועים נוספים
        this.setupGlobalEvents();
        
        console.log('Builder initialized successfully');
    }
    
    /**
     * הגדרת אירועים גלובליים
     */
    setupGlobalEvents() {
        // קיצורי מקלדת
        document.addEventListener('keydown', (e) => {
            this.handleKeyboardShortcuts(e);
        });
        
        // מניעת רענון מקרי
        window.addEventListener('beforeunload', (e) => {
            if (this.core.sections.length > 0) {
                e.preventDefault();
                e.returnValue = 'יש לך שינויים שלא נשמרו. האם אתה בטוח שברצונך לעזוב?';
            }
        });
        
        // טיפול בשגיאות
        window.addEventListener('error', (e) => {
            console.error('Builder error:', e.error);
            this.showErrorNotification('שגיאה בבילדר: ' + e.error.message);
        });
        
        // טיפול בשגיאות Promise
        window.addEventListener('unhandledrejection', (e) => {
            console.error('Builder promise error:', e.reason);
            this.showErrorNotification('שגיאה בבילדר: ' + e.reason);
        });
    }
    
    /**
     * טיפול בקיצורי מקלדת
     */
    handleKeyboardShortcuts(e) {
        // Ctrl+S / Cmd+S - שמירה
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            this.core.savePage();
        }
        
        // Ctrl+Shift+P / Cmd+Shift+P - פרסום
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'P') {
            e.preventDefault();
            this.core.publishPage();
        }
        
        // Escape - ביטול בחירת סקשן
        if (e.key === 'Escape') {
            this.core.currentSection = null;
            document.querySelectorAll('.section-item').forEach(item => {
                item.classList.remove('ring-2', 'ring-blue-500');
            });
        }
        
        // Delete - מחיקת סקשן נבחר
        if (e.key === 'Delete' && this.core.currentSection) {
            this.core.deleteSection(this.core.currentSection.id);
        }
    }
    
    /**
     * הצגת התראת שגיאה
     */
    showErrorNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 z-50 px-4 py-3 rounded shadow-lg bg-red-500 text-white max-w-sm';
        notification.innerHTML = `
            <div class="flex items-center gap-2">
                <i class="ri-error-warning-line"></i>
                <span class="text-sm">${message}</span>
                <button class="ml-auto hover:bg-red-600 rounded p-1">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        `;
        
        const closeBtn = notification.querySelector('button');
        closeBtn.addEventListener('click', () => {
            notification.remove();
        });
        
        document.body.appendChild(notification);
        
        // הסרה אוטומטית אחרי 5 שניות
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
    
    /**
     * קבלת נתוני הבילדר הנוכחיים
     */
    getBuilderData() {
        return {
            sections: this.core.sections,
            currentSection: this.core.currentSection,
            currentDevice: this.core.currentDevice,
            isPublished: this.core.isPublished,
            storeId: this.core.storeId,
            pageId: this.core.pageId
        };
    }
    
    /**
     * ייצוא הגדרות
     */
    exportSettings() {
        const data = this.getBuilderData();
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = `builder-settings-${Date.now()}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
    
    /**
     * ייבוא הגדרות
     */
    importSettings(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                try {
                    const data = JSON.parse(e.target.result);
                    
                    if (data.sections) {
                        this.core.sections = data.sections;
                        this.core.loadExistingSections();
                        this.core.updatePreview();
                        this.core.showNotification('הגדרות יובאו בהצלחה', 'success');
                        resolve(data);
                    } else {
                        throw new Error('קובץ לא תקין');
                    }
                } catch (error) {
                    reject(error);
                    this.core.showNotification('שגיאה בייבוא הגדרות', 'error');
                }
            };
            
            reader.onerror = () => {
                reject(new Error('שגיאה בקריאת הקובץ'));
            };
            
            reader.readAsText(file);
        });
    }
    
    /**
     * איפוס הבילדר
     */
    reset() {
        if (!confirm('האם אתה בטוח שברצונך לאפס את כל ההגדרות?')) {
            return;
        }
        
        this.core.sections = [];
        this.core.currentSection = null;
        this.core.loadExistingSections();
        this.core.updatePreview();
        
        // ניקוי הגדרות
        document.getElementById('sectionSettings').innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <i class="ri-settings-3-line text-2xl mb-2 block"></i>
                <p class="text-sm">בחר סקשן לעריכה</p>
            </div>
        `;
        
        this.core.showNotification('הבילדר אופס בהצלחה', 'success');
    }
    
    /**
     * בדיקת תקינות נתונים
     */
    validateData() {
        const errors = [];
        
        this.core.sections.forEach((section, index) => {
            if (!section.id) {
                errors.push(`סקשן ${index + 1}: חסר מזהה`);
            }
            
            if (!section.type) {
                errors.push(`סקשן ${index + 1}: חסר סוג`);
            }
            
            if (section.type === 'hero') {
                if (!section.settings.title) {
                    errors.push(`סקשן הירו: חסרה כותרת`);
                }
            }
        });
        
        return errors;
    }
    
    /**
     * שמירה מתקדמת עם ולידציה
     */
    async saveWithValidation() {
        const errors = this.validateData();
        
        if (errors.length > 0) {
            const errorMessage = 'נמצאו שגיאות:\n' + errors.join('\n');
            if (!confirm(errorMessage + '\n\nהאם לשמור בכל זאת?')) {
                return false;
            }
        }
        
        return await this.core.savePage();
    }
}

// יצירת instance גלובלי
window.builder = new Builder();

// Export לשימוש במקומות אחרים
window.Builder = Builder; 