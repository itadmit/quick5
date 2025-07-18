/**
 * Sections Manager - ניהול רשימת סקשנים
 * אחראי על רינדור הרשימה, פעולות על סקשנים, ועדכון ממשק המשתמש
 */

class SectionsManager {
    constructor() {
        this.selectedSectionId = null;
        this.elements = {
            sectionsList: document.getElementById('sectionsList'),
            emptySections: document.getElementById('emptySections'),
            addSectionBtns: document.querySelectorAll('.add-section-btn')
        };
        
        this.init();
    }
    
    /**
     * אתחול מנהל הסקשנים
     */
    init() {
        this.bindEvents();
        this.render();
        console.log('📋 Sections Manager initialized');
    }
    
    /**
     * קישור אירועים
     */
    bindEvents() {
        // כפתור פתיחת תפריט הוספת סקשן
        const addSectionTrigger = document.querySelector('.add-section-trigger .figma-button');
        const addSectionMenu = document.querySelector('.add-section-menu');
        
        if (addSectionTrigger && addSectionMenu) {
            addSectionTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const isVisible = !addSectionMenu.classList.contains('hidden');
                
                if (isVisible) {
                    addSectionMenu.classList.add('hidden');
                } else {
                    // בדיקת מיקום התפריט בחלון
                    const triggerRect = addSectionTrigger.getBoundingClientRect();
                    const menuWidth = 192; // w-48 = 192px
                    const windowWidth = window.innerWidth;
                    
                    // אם התפריט יוצא מהמסך בצד שמאל, פתח אותו לצד ימין
                    if (triggerRect.left - menuWidth < 0) {
                        addSectionMenu.classList.remove('left-0');
                        addSectionMenu.classList.add('right-0');
                    } else {
                        addSectionMenu.classList.remove('right-0');
                        addSectionMenu.classList.add('left-0');
                    }
                    
                    addSectionMenu.classList.remove('hidden');
                }
            });
            
            // סגירת התפריט בלחיצה מחוץ לו
            document.addEventListener('click', (e) => {
                if (!addSectionTrigger.contains(e.target) && !addSectionMenu.contains(e.target)) {
                    addSectionMenu.classList.add('hidden');
                }
            });
        }
        
        // הוספת סקשן
        this.elements.addSectionBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const type = e.currentTarget.dataset.type;
                this.addSection(type);
                
                // סגירת התפריט
                const menu = e.currentTarget.closest('.add-section-menu');
                if (menu) {
                    menu.classList.add('hidden');
                }
            });
        });
        
        // פעולות על סקשנים
        document.addEventListener('click', (e) => {
            const actionBtn = e.target.closest('.section-action-btn');
            if (actionBtn) {
                e.stopPropagation();
                const action = actionBtn.dataset.action;
                const sectionItem = actionBtn.closest('.section-item');
                const sectionId = sectionItem.dataset.sectionId;
                
                this.handleSectionAction(action, sectionId);
                return;
            }
            
            // לחיצה על סקשן - בחירה
            const sectionItem = e.target.closest('.section-item');
            if (sectionItem) {
                const sectionId = sectionItem.dataset.sectionId;
                this.selectSection(sectionId);
            }
        });
        
        // האזנה לעדכונים מהליבה
        document.addEventListener('sectionsUpdated', () => {
            this.render();
        });
    }
    
    /**
     * רינדור רשימת הסקשנים
     */
    render() {
        const sections = window.builderCore.sections;
        
        if (sections.length === 0) {
            this.elements.sectionsList.innerHTML = '';
            this.elements.emptySections.style.display = 'block';
            return;
        }
        
        this.elements.emptySections.style.display = 'none';
        
        this.elements.sectionsList.innerHTML = sections.map((section, index) => 
            this.renderSectionItem(section, index)
        ).join('');
    }
    
    /**
     * רינדור פריט סקשן בודד
     */
    renderSectionItem(section, index) {
        const isSelected = this.selectedSectionId === section.id;
        const isHidden = section.visible === false;
        
        return `
            <div class="section-item ${isSelected ? 'active' : ''} ${isHidden ? 'hidden' : ''}" 
                 data-section-id="${section.id}" data-index="${index}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="${this.getSectionIcon(section.type)} text-${this.getSectionColor(section.type)}-600"></i>
                        <div>
                            <div class="font-medium text-sm">${this.getSectionName(section.type)}</div>
                            <div class="text-xs text-gray-500">${this.getSectionPreview(section)}</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-1">
                        <button class="section-action-btn p-1 text-gray-400 hover:text-blue-600" 
                                data-action="settings" title="הגדרות">
                            <i class="ri-settings-3-line"></i>
                        </button>
                        <button class="section-action-btn p-1 text-gray-400 hover:text-yellow-600" 
                                data-action="hide" title="${isHidden ? 'הצג' : 'הסתר'}">
                            <i class="ri-eye-${isHidden ? '' : 'off-'}line"></i>
                        </button>
                        <button class="section-action-btn p-1 text-gray-400 hover:text-red-600" 
                                data-action="delete" title="מחק">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * הוספת סקשן חדש
     */
    addSection(type) {
        const newSection = window.builderCore.addSection(type);
        this.render();
        this.selectSection(newSection.id);
        
        // אנימציה לפריט החדש
        setTimeout(() => {
            const newItem = document.querySelector(`[data-section-id="${newSection.id}"]`);
            if (newItem) {
                newItem.classList.add('slide-in');
            }
        }, 50);
    }
    
    /**
     * טיפול בפעולות על סקשן
     */
    handleSectionAction(action, sectionId) {
        switch(action) {
            case 'settings':
                this.selectSection(sectionId);
                break;
                
            case 'hide':
                this.toggleSectionVisibility(sectionId);
                break;
                
            case 'delete':
                this.deleteSection(sectionId);
                break;
        }
    }
    
    /**
     * בחירת סקשן
     */
    selectSection(sectionId) {
        this.selectedSectionId = sectionId;
        this.render();
        
        // הפעלת מנהל ההגדרות
        if (window.settingsManager) {
            window.settingsManager.showSectionSettings(sectionId);
        }
        
        // הדגשת הסקשן ב-iframe
        this.highlightSectionInPreview(sectionId);
    }
    
    /**
     * הסתרה/הצגת סקשן
     */
    toggleSectionVisibility(sectionId) {
        const isVisible = window.builderCore.toggleSectionVisibility(sectionId);
        this.render();
        
        // הודעה
        const message = isVisible ? 'הסקשן מוצג כעת' : 'הסקשן הוסתר';
        window.builderCore.showNotification(message, 'info');
    }
    
    /**
     * מחיקת סקשן
     */
    deleteSection(sectionId) {
        const section = window.builderCore.getSection(sectionId);
        if (!section) return;
        
        const sectionName = this.getSectionName(section.type);
        const confirmed = confirm(`האם אתה בטוח שברצונך למחוק את ${sectionName}?`);
        
        if (confirmed) {
            window.builderCore.deleteSection(sectionId);
            this.render();
            
            // אם זה הסקשן שנבחר, סגור הגדרות
            if (this.selectedSectionId === sectionId) {
                this.selectedSectionId = null;
                if (window.settingsManager) {
                    window.settingsManager.closeSettings();
                }
            }
        }
    }
    
    /**
     * הדגשת סקשן ב-iframe
     */
    highlightSectionInPreview(sectionId) {
        const iframe = document.getElementById('previewFrame');
        if (iframe && iframe.contentWindow) {
            iframe.contentWindow.postMessage({
                type: 'highlightSection',
                sectionId: sectionId
            }, '*');
        }
    }
    
    /**
     * ביטול בחירת סקשן
     */
    deselectSection() {
        this.selectedSectionId = null;
        this.render();
    }
    
    /**
     * קבלת אייקון לפי סוג סקשן
     */
    getSectionIcon(type) {
        const icons = {
            hero: 'ri-image-line',
            text: 'ri-text',
            products: 'ri-shopping-bag-line',
            categories: 'ri-grid-line',
            gallery: 'ri-gallery-line',
            contact: 'ri-phone-line',
            map: 'ri-map-pin-line'
        };
        return icons[type] || 'ri-layout-line';
    }
    
    /**
     * קבלת צבע לפי סוג סקשן
     */
    getSectionColor(type) {
        const colors = {
            hero: 'blue',
            text: 'green', 
            products: 'purple',
            categories: 'orange',
            gallery: 'pink',
            contact: 'indigo',
            map: 'red'
        };
        return colors[type] || 'gray';
    }
    
    /**
     * קבלת שם לפי סוג סקשן
     */
    getSectionName(type) {
        const names = {
            hero: 'סקשן הירו',
            text: 'בלוק טקסט',
            products: 'רשת מוצרים', 
            categories: 'רשת קטגוריות',
            gallery: 'גלריית תמונות',
            contact: 'פרטי קשר',
            map: 'מפה'
        };
        return names[type] || 'סקשן';
    }
    
    /**
     * קבלת תצוגה מקדימה של תוכן הסקשן
     */
    getSectionPreview(section) {
        switch(section.type) {
            case 'hero':
                return section.content?.title?.text || 'ללא כותרת';
            case 'text':
                const text = section.content?.text?.text || 'ללא תוכן';
                return text.length > 30 ? text.substring(0, 30) + '...' : text;
            case 'products':
                return 'מוצרים נבחרים';
            case 'categories':
                return 'קטגוריות ראשיות';
            default:
                return section.id;
        }
    }
    
    /**
     * קבלת כמות הסקשנים
     */
    getSectionsCount() {
        return window.builderCore.sections.length;
    }
    
    /**
     * קבלת כמות הסקשנים הנראים
     */
    getVisibleSectionsCount() {
        return window.builderCore.sections.filter(s => s.visible !== false).length;
    }
}

// יצירת instance גלובלי
window.sectionsManager = new SectionsManager(); 