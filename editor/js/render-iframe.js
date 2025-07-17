/**
 * Render Iframe - ניהול iframe והתצוגה המקדימה
 * מנהל את התקשורת עם ה-iframe ועדכון התצוגה בזמן אמת
 */

class RenderIframe {
    constructor() {
        this.iframe = document.getElementById('previewFrame');
        this.currentSections = [];
        this.isLoaded = false;
        
        this.init();
    }
    
    /**
     * אתחול הרינדר
     */
    init() {
        if (!this.iframe) return;
        
        this.bindEvents();
        this.waitForIframeLoad();
    }
    
    /**
     * קישור אירועים
     */
    bindEvents() {
        // האזנה להודעות מה-iframe
        window.addEventListener('message', (event) => {
            this.handleMessage(event);
        });
        
        // האזנה לטעינת ה-iframe
        this.iframe.addEventListener('load', () => {
            this.onIframeLoad();
        });
    }
    
    /**
     * המתנה לטעינת ה-iframe
     */
    waitForIframeLoad() {
        const checkLoad = () => {
            try {
                if (this.iframe.contentDocument && this.iframe.contentDocument.readyState === 'complete') {
                    this.onIframeLoad();
                } else {
                    setTimeout(checkLoad, 100);
                }
            } catch (e) {
                // Cross-origin error, wait for load event
                setTimeout(checkLoad, 100);
            }
        };
        
        checkLoad();
    }
    
    /**
     * טיפול בטעינת ה-iframe
     */
    onIframeLoad() {
        this.isLoaded = true;
        this.injectBuilderStyles();
        this.setupBuilderOverlay();
        
        // עדכון סקשנים ראשוני
        if (this.currentSections.length > 0) {
            this.updateSections(this.currentSections);
        }
    }
    
    /**
     * טיפול בהודעות מה-iframe
     */
    handleMessage(event) {
        // וידוא שההודעה מגיעה מה-iframe שלנו
        if (event.source !== this.iframe.contentWindow) return;
        
        const { type, data } = event.data;
        
        switch (type) {
            case 'sectionClick':
                this.handleSectionClick(data.sectionId);
                break;
                
            case 'sectionHover':
                this.handleSectionHover(data.sectionId, data.isHovering);
                break;
                
            case 'ready':
                this.onIframeReady();
                break;
        }
    }
    
    /**
     * ה-iframe מוכן לקבלת נתונים
     */
    onIframeReady() {
        this.isLoaded = true;
        
        // שליחת סקשנים נוכחיים
        if (this.currentSections.length > 0) {
            this.updateSections(this.currentSections);
        }
    }
    
    /**
     * עדכון סקשנים ב-iframe
     */
    updateSections(sections) {
        this.currentSections = sections;
        
        if (!this.isLoaded) return;
        
        this.iframe.contentWindow?.postMessage({
            type: 'updateSections',
            sections: sections
        }, '*');
    }
    
    /**
     * הוספת סטיילים לבילדר ב-iframe
     */
    injectBuilderStyles() {
        if (!this.iframe.contentDocument) return;
        
        const styles = `
            <style id="builder-styles">
                .builder-section-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(59, 130, 246, 0.1);
                    border: 2px dashed #3B82F6;
                    pointer-events: none;
                    opacity: 0;
                    transition: opacity 0.2s;
                    z-index: 1000;
                }
                
                .builder-section-overlay.visible {
                    opacity: 1;
                }
                
                .builder-section-overlay.selected {
                    background: rgba(59, 130, 246, 0.2);
                    border: 2px solid #3B82F6;
                }
                
                .builder-section-label {
                    position: absolute;
                    top: -30px;
                    left: 0;
                    background: #3B82F6;
                    color: white;
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 12px;
                    font-weight: 500;
                    white-space: nowrap;
                }
                
                .builder-section {
                    position: relative;
                    cursor: pointer;
                }
                
                .builder-section:hover .builder-section-overlay {
                    opacity: 1;
                }
                
                /* Hide scrollbars but keep functionality */
                body {
                    overflow-x: hidden;
                }
                
                /* Builder mode indicators */
                body.builder-mode::before {
                    content: "מצב עריכה";
                    position: fixed;
                    top: 10px;
                    left: 10px;
                    background: rgba(0, 0, 0, 0.8);
                    color: white;
                    padding: 5px 10px;
                    border-radius: 4px;
                    font-size: 12px;
                    z-index: 9999;
                }
            </style>
        `;
        
        // הוספת הסטיילים ל-head
        const head = this.iframe.contentDocument.head;
        if (head && !head.querySelector('#builder-styles')) {
            head.insertAdjacentHTML('beforeend', styles);
        }
    }
    
    /**
     * הגדרת overlay לבילדר
     */
    setupBuilderOverlay() {
        if (!this.iframe.contentDocument) return;
        
        const body = this.iframe.contentDocument.body;
        body.classList.add('builder-mode');
        
        // הוספת event listeners לסקשנים
        this.addSectionListeners();
    }
    
    /**
     * הוספת event listeners לסקשנים
     */
    addSectionListeners() {
        if (!this.iframe.contentDocument) return;
        
        const sections = this.iframe.contentDocument.querySelectorAll('[id*="hero"], [id*="section"]');
        
        sections.forEach(section => {
            // הוספת כיתה builder-section
            section.classList.add('builder-section');
            
            // יצירת overlay
            const overlay = document.createElement('div');
            overlay.className = 'builder-section-overlay';
            overlay.innerHTML = `<div class="builder-section-label">${this.getSectionDisplayName(section.id)}</div>`;
            
            section.style.position = 'relative';
            section.appendChild(overlay);
            
            // קישור אירועים
            section.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.selectSection(section.id);
            });
            
            section.addEventListener('mouseenter', () => {
                overlay.classList.add('visible');
                this.hoverSection(section.id, true);
            });
            
            section.addEventListener('mouseleave', () => {
                overlay.classList.remove('visible');
                this.hoverSection(section.id, false);
            });
        });
    }
    
    /**
     * בחירת סקשן
     */
    selectSection(sectionId) {
        // עדכון הסקשן הנבחר ב-iframe
        const sections = this.iframe.contentDocument.querySelectorAll('.builder-section');
        sections.forEach(section => {
            const overlay = section.querySelector('.builder-section-overlay');
            if (overlay) {
                overlay.classList.remove('selected');
            }
        });
        
        const selectedSection = this.iframe.contentDocument.getElementById(sectionId);
        if (selectedSection) {
            const overlay = selectedSection.querySelector('.builder-section-overlay');
            if (overlay) {
                overlay.classList.add('selected');
            }
        }
        
        // שליחת הודעה לבילדר הראשי
        if (window.builderCore) {
            window.builderCore.selectSection(sectionId);
        }
    }
    
    /**
     * הוור על סקשן
     */
    hoverSection(sectionId, isHovering) {
        // שליחת הודעה לבילדר הראשי
        window.parent.postMessage({
            type: 'sectionHover',
            data: { sectionId, isHovering }
        }, '*');
    }
    
    /**
     * טיפול בלחיצה על סקשן מהבילדר הראשי
     */
    handleSectionClick(sectionId) {
        this.selectSection(sectionId);
    }
    
    /**
     * טיפול בהוור על סקשן מהבילדר הראשי
     */
    handleSectionHover(sectionId, isHovering) {
        const section = this.iframe.contentDocument?.getElementById(sectionId);
        if (!section) return;
        
        const overlay = section.querySelector('.builder-section-overlay');
        if (overlay) {
            if (isHovering) {
                overlay.classList.add('visible');
            } else {
                overlay.classList.remove('visible');
            }
        }
    }
    
    /**
     * קבלת שם תצוגה לסקשן
     */
    getSectionDisplayName(sectionId) {
        if (sectionId.includes('hero')) return 'הירו';
        if (sectionId.includes('category')) return 'קטגוריות';
        if (sectionId.includes('product')) return 'מוצרים';
        if (sectionId.includes('text')) return 'טקסט';
        
        return 'סקשן';
    }
    
    /**
     * רענון ה-iframe
     */
    refresh() {
        if (this.iframe) {
            this.iframe.src = this.iframe.src;
        }
    }
    
    /**
     * שינוי גודל ה-iframe (לצפייה responsive)
     */
    setDevice(device) {
        if (!this.iframe) return;
        
        const container = this.iframe.parentElement;
        
        switch (device) {
            case 'mobile':
                container.style.width = '375px';
                container.style.height = '667px';
                container.style.margin = '20px auto';
                container.style.border = '1px solid #ccc';
                container.style.borderRadius = '20px';
                break;
                
            case 'tablet':
                container.style.width = '768px';
                container.style.height = '1024px';
                container.style.margin = '20px auto';
                container.style.border = '1px solid #ccc';
                container.style.borderRadius = '10px';
                break;
                
            default: // desktop
                container.style.width = '100%';
                container.style.height = '100%';
                container.style.margin = '0';
                container.style.border = 'none';
                container.style.borderRadius = '0';
                break;
        }
    }
    
    /**
     * הפעלת/כיבוי מצב עריכה
     */
    setEditMode(enabled) {
        if (!this.iframe.contentDocument) return;
        
        const body = this.iframe.contentDocument.body;
        
        if (enabled) {
            body.classList.add('builder-mode');
            this.addSectionListeners();
        } else {
            body.classList.remove('builder-mode');
            
            // הסרת overlays
            const overlays = this.iframe.contentDocument.querySelectorAll('.builder-section-overlay');
            overlays.forEach(overlay => overlay.remove());
        }
    }
}

// אתחול הרינדר כשהדף נטען
document.addEventListener('DOMContentLoaded', () => {
    window.renderIframe = new RenderIframe();
    
    // קישור לבילדר הראשי
    if (window.builderCore) {
        window.builderCore.renderIframe = window.renderIframe;
    }
}); 