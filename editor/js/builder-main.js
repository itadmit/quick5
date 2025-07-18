/**
 * Builder Main - הקובץ הראשי שמחבר את כל הרכיבים
 * אחראי על תזמון, קישור אירועים, וניהול המצב הכללי
 */

class BuilderMain {
    constructor() {
        this.isInitialized = false;
        this.components = {
            core: null,
            sectionsManager: null,
            settingsManager: null
        };
        
        this.init();
    }
    
    /**
     * אתחול הבילדר
     */
    init() {
        // המתנה לטעינת DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.start();
            });
        } else {
            this.start();
        }
    }
    
    /**
     * התחלת הבילדר
     */
    start() {
        try {
            // וידוא שכל הרכיבים נטענו
            this.waitForComponents()
                .then(() => {
                    this.initializeComponents();
                    this.bindGlobalEvents();
                    this.setupIframe();
                    
                    // קביעת מצב תצוגה ראשוני
                    this.setViewMode('desktop');
                    
                    this.isInitialized = true;
                    
                    console.log('🚀 Builder fully initialized and ready!');
                    
                    // הפעלת אירוע מותאם
                    this.dispatchEvent('builderReady');
                })
                .catch(error => {
                    console.error('❌ Failed to initialize builder:', error);
                    this.showError('שגיאה באתחול הבילדר');
                });
        } catch (error) {
            console.error('❌ Critical error during builder startup:', error);
        }
    }
    
    /**
     * המתנה לרכיבים
     */
    waitForComponents() {
        return new Promise((resolve, reject) => {
            let attempts = 0;
            const maxAttempts = 50; // 5 שניות
            
            const checkComponents = () => {
                attempts++;
                
                // בדיקה שכל הרכיבים קיימים
                const coreExists = window.builderCore && typeof window.builderCore === 'object';
                const sectionsExists = window.sectionsManager && typeof window.sectionsManager === 'object';
                const settingsExists = window.settingsManager && typeof window.settingsManager === 'object';
                
                if (coreExists && sectionsExists && settingsExists) {
                    this.components.core = window.builderCore;
                    this.components.sectionsManager = window.sectionsManager;
                    this.components.settingsManager = window.settingsManager;
                    
                    resolve();
                } else if (attempts >= maxAttempts) {
                    reject(new Error('Components not loaded in time'));
                } else {
                    setTimeout(checkComponents, 100);
                }
            };
            
            checkComponents();
        });
    }
    
    /**
     * אתחול קישורים בין רכיבים
     */
    initializeComponents() {
        // קישור בין sections manager ל-core
        this.setupSectionsManagerIntegration();
        
        // קישור בין settings manager ל-core
        this.setupSettingsManagerIntegration();
        
        // הגדרת תקשורת עם iframe
        this.setupIframeIntegration();
    }
    
    /**
     * קישור sections manager
     */
    setupSectionsManagerIntegration() {
        // עדכון רשימת סקשנים כשהליבה משתנה
        const originalUpdateSection = this.components.core.updateSection;
        this.components.core.updateSection = (...args) => {
            const result = originalUpdateSection.apply(this.components.core, args);
            if (result) {
                this.components.sectionsManager.render();
            }
            return result;
        };
        
        const originalDeleteSection = this.components.core.deleteSection;
        this.components.core.deleteSection = (...args) => {
            const result = originalDeleteSection.apply(this.components.core, args);
            if (result) {
                this.components.sectionsManager.render();
            }
            return result;
        };
    }
    
    /**
     * קישור settings manager
     */
    setupSettingsManagerIntegration() {
        // סגירת הגדרות כשמוחקים סקשן
        const originalDeleteSection = this.components.core.deleteSection;
        this.components.core.deleteSection = (...args) => {
            const sectionId = args[0];
            if (this.components.settingsManager.currentSectionId === sectionId) {
                this.components.settingsManager.closeSettings();
            }
            return originalDeleteSection.apply(this.components.core, args);
        };
    }
    
    /**
     * הגדרת iframe
     */
    setupIframe() {
        const iframe = document.getElementById('previewFrame');
        if (!iframe) return;
        
        let iframeLoaded = false;
        
        const handleIframeLoad = () => {
            if (iframeLoaded) return;
            iframeLoaded = true;
            this.onIframeLoad();
        };
        
        // המתנה לטעינת iframe
        iframe.addEventListener('load', handleIframeLoad);
        
        // אם הiframe כבר נטען
        if (iframe.contentDocument && iframe.contentDocument.readyState === 'complete') {
            handleIframeLoad();
        }
    }
    
    /**
     * iframe נטען
     */
    onIframeLoad() {
        console.log('📱 Iframe loaded');
        
        // שליחת סקשנים נוכחיים
        this.components.core.updatePreview();
        
        // הגדרת תקשורת דו-כיוונית
        this.setupIframeIntegration();
    }
    
    /**
     * תקשורת עם iframe
     */
    setupIframeIntegration() {
        // הסרת event listener קיים למניעת כפילות
        if (this.messageHandler) {
            window.removeEventListener('message', this.messageHandler);
        }
        
        // יצירת handler חדש
        this.messageHandler = (event) => {
            this.handleIframeMessage(event);
        };
        
        // האזנה להודעות מהiframe
        window.addEventListener('message', this.messageHandler);
    }
    
    /**
     * טיפול בהודעות מiframe
     */
    handleIframeMessage(event) {
        // בדיקת מקור
        if (event.origin !== window.location.origin) return;
        
        const { type, data } = event.data;
        
        switch (type) {
            case 'sectionClicked':
                this.handleSectionClick(data.sectionId);
                break;
                
            case 'sectionHover':
                this.handleSectionHover(data.sectionId, data.isHovering);
                break;
                
            case 'previewReady':
                console.log('✅ Preview is ready');
                break;
                
            default:
                console.log('📨 Unknown iframe message:', type, data);
        }
    }
    
    /**
     * לחיצה על סקשן ב-iframe
     */
    handleSectionClick(sectionId) {
        if (this.components.sectionsManager) {
            this.components.sectionsManager.selectSection(sectionId);
        }
    }
    
    /**
     * ריחוף על סקשן ב-iframe
     */
    handleSectionHover(sectionId, isHovering) {
        // הדגשת הסקשן ברשימה
        const sectionElement = document.querySelector(`[data-section-id="${sectionId}"]`);
        if (sectionElement) {
            if (isHovering) {
                sectionElement.classList.add('hover-highlight');
            } else {
                sectionElement.classList.remove('hover-highlight');
            }
        }
    }
    
    /**
     * קישור אירועים גלובליים
     */
    bindGlobalEvents() {
        // כפתורי הbartoolbar
        this.bindToolbarEvents();
        
        // קיצורי מקלדת נוספים
        this.bindKeyboardShortcuts();
        
        // אירועי חלון
        this.bindWindowEvents();
    }
    
    /**
     * אירועי toolbar
     */
    bindToolbarEvents() {
        // כפתור שמירה ופרסום
        const saveBtn = document.getElementById('saveBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                this.components.core.savePage();
            });
        }
        
        // מצבי תצוגה - הDevice Sync Manager יטפל בסינכרון
        // נשאיר רק לצורך תאימות לאחור
        const desktopView = document.getElementById('desktopView');
        const mobileView = document.getElementById('mobileView');
        
        if (desktopView && mobileView) {
            // הDevice Sync Manager כבר מטפל בכל הסינכרון
            // אלה נשארים רק כ-fallback
            desktopView.addEventListener('click', (e) => {
                // אם Device Sync Manager לא זמין - fallback
                if (!window.deviceSyncManager) {
                    this.setViewMode('desktop');
                }
            });
            
            mobileView.addEventListener('click', (e) => {
                // אם Device Sync Manager לא זמין - fallback
                if (!window.deviceSyncManager) {
                    this.setViewMode('mobile');
                }
            });
        }
    }
    
    /**
     * קיצורי מקלדת
     */
    bindKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Escape - סגירת הגדרות
            if (e.key === 'Escape') {
                if (this.components.settingsManager.isSettingsOpen()) {
                    this.components.settingsManager.closeSettings();
                    e.preventDefault();
                }
            }
            
            // Delete - מחיקת סקשן נבחר (רק אם לא בתוך input)
            if (e.key === 'Delete' || e.key === 'Backspace') {
                // בדוק שהמקש לא נלחץ בתוך input/textarea/contenteditable
                const activeElement = document.activeElement;
                const isInInput = activeElement && (
                    activeElement.tagName === 'INPUT' ||
                    activeElement.tagName === 'TEXTAREA' ||
                    activeElement.contentEditable === 'true' ||
                    activeElement.isContentEditable
                );
                
                if (!isInInput && this.components.sectionsManager.selectedSectionId) {
                    this.components.sectionsManager.deleteSection(
                        this.components.sectionsManager.selectedSectionId
                    );
                    e.preventDefault();
                }
            }
            
            // Ctrl+D - שכפול סקשן
            if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
                if (this.components.sectionsManager.selectedSectionId) {
                    this.duplicateSection(this.components.sectionsManager.selectedSectionId);
                    e.preventDefault();
                }
            }
        });
    }
    
    /**
     * אירועי חלון
     */
    bindWindowEvents() {
        // התאמת גובה iframe
        window.addEventListener('resize', () => {
            this.adjustIframeHeight();
        });
        
        // התראה לפני יציאה עם שינויים לא שמורים
        window.addEventListener('beforeunload', (e) => {
            if (this.components.core.isDirty) {
                e.preventDefault();
                e.returnValue = 'יש שינויים לא שמורים. האם לצאת?';
            }
        });
    }
    
    /**
     * שינוי מצב תצוגה
     */
    setViewMode(mode) {
        const iframe = document.getElementById('previewFrame');
        const desktopBtn = document.getElementById('desktopView');
        const mobileBtn = document.getElementById('mobileView');
        
        if (mode === 'mobile') {
            iframe.style.width = '375px';
            iframe.style.margin = '0 auto';
            iframe.style.border = '1px solid #ccc';
            iframe.style.borderRadius = '10px';
            
            // איפוס כפתור מחשב לסטיילינג לא פעיל
            desktopBtn.className = 'text-gray-600 px-3 py-2 text-sm hover:text-gray-900';
            desktopBtn.innerHTML = '<i class="ri-computer-line"></i>\nמחשב';
            
            // הפעלת כפתור מובייל
            mobileBtn.className = 'figma-button bg-white text-gray-700 text-sm';
            mobileBtn.innerHTML = '<i class="ri-smartphone-line"></i>\nמובייל';
        } else {
            iframe.style.width = '100%';
            iframe.style.margin = '0';
            iframe.style.border = 'none';
            iframe.style.borderRadius = '0';
            
            // הפעלת כפתור מחשב 
            desktopBtn.className = 'figma-button bg-white text-gray-700 text-sm';
            desktopBtn.innerHTML = '<i class="ri-computer-line"></i>\nמחשב';
            
            // איפוס כפתור מובייל לסטיילינג לא פעיל
            mobileBtn.className = 'text-gray-600 px-3 py-2 text-sm hover:text-gray-900';
            mobileBtn.innerHTML = '<i class="ri-smartphone-line"></i>\nמובייל';
        }
        
        // עדכון iframe על מצב התצוגה
        iframe.contentWindow?.postMessage({
            type: 'viewModeChanged',
            mode: mode
        }, '*');
    }
    
    /**
     * שכפול סקשן
     */
    duplicateSection(sectionId) {
        const section = this.components.core.getSection(sectionId);
        if (!section) return;
        
        const duplicated = JSON.parse(JSON.stringify(section));
        duplicated.id = section.type + '_' + Date.now();
        
        this.components.core.sections.push(duplicated);
        this.components.core.markDirty();
        this.components.core.updatePreview();
        this.components.sectionsManager.render();
        
        // בחירת הסקשן החדש
        this.components.sectionsManager.selectSection(duplicated.id);
    }
    
    /**
     * התאמת גובה iframe
     */
    adjustIframeHeight() {
        const iframe = document.getElementById('previewFrame');
        if (iframe) {
            const windowHeight = window.innerHeight;
            const toolbarHeight = document.querySelector('.toolbar').offsetHeight;
            iframe.style.height = (windowHeight - toolbarHeight) + 'px';
        }
    }
    
    /**
     * הצגת שגיאה
     */
    showError(message) {
        // TODO: הצגת modal או toast עם שגיאה
        alert(message);
    }
    
    /**
     * שליחת אירוע מותאם
     */
    dispatchEvent(eventName, data = null) {
        const event = new CustomEvent(eventName, { detail: data });
        document.dispatchEvent(event);
    }
    
    /**
     * נקיון כשעוזבים
     */
    destroy() {
        // הסרת event listener
        if (this.messageHandler) {
            window.removeEventListener('message', this.messageHandler);
            this.messageHandler = null;
        }
        
        if (this.components.core) {
            this.components.core.destroy();
        }
        
        this.isInitialized = false;
        console.log('🔌 Builder destroyed');
    }
}

// יצירת instance גלובלי
window.builderMain = new BuilderMain();

// Export for modules if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BuilderMain;
} 