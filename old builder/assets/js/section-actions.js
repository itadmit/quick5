/**
 * Section Actions Manager - ניהול פעולות על סקשנים
 */

class SectionActionsManager {
    constructor(builder) {
        this.builder = builder;
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        // Listen for all action button clicks
        document.addEventListener('click', (e) => {
            if (e.target.closest('.action-btn')) {
                e.preventDefault();
                e.stopPropagation();
                
                const btn = e.target.closest('.action-btn');
                const section = btn.dataset.section;
                const action = btn.dataset.action;
                
                this.handleAction(action, section, btn);
            }
            
            // Listen for toggle actions button clicks
            if (e.target.closest('.toggle-actions-btn')) {
                e.preventDefault();
                e.stopPropagation();
                
                const btn = e.target.closest('.toggle-actions-btn');
                const section = btn.dataset.section;
                
                this.toggleSectionActions(section);
            }
        });
    }
    
    async handleAction(action, sectionType, button) {
        debugLog(`Handling action: ${action} for section: ${sectionType}`);
        
        switch (action) {
            case 'move-up':
                this.moveSection(sectionType, 'up');
                break;
            case 'move-down':
                this.moveSection(sectionType, 'down');
                break;
            case 'duplicate':
                await this.duplicateSection(sectionType);
                break;
            case 'hide':
                this.toggleSectionVisibility(sectionType, button);
                break;
            case 'delete':
                this.deleteSection(sectionType);
                break;
        }
    }
    
    /**
     * הזזת סקשן למעלה או למטה
     */
    moveSection(sectionType, direction) {
        const sectionEl = document.querySelector(`[data-section="${sectionType}"]`);
        if (!sectionEl) return;
        
        const parent = sectionEl.parentElement;
        const sections = Array.from(parent.children).filter(el => 
            el.classList.contains('section-item'));
        
        const currentIndex = sections.indexOf(sectionEl);
        
        if (direction === 'up' && currentIndex > 0) {
            parent.insertBefore(sectionEl, sections[currentIndex - 1]);
            this.showSuccessMessage('הסקשן הועבר למעלה');
        } else if (direction === 'down' && currentIndex < sections.length - 1) {
            parent.insertBefore(sectionEl, sections[currentIndex + 2] || null);
            this.showSuccessMessage('הסקשן הועבר למטה');
        }
        
        this.updateSectionOrder();
    }
    
    /**
     * שכפול סקשן
     */
    async duplicateSection(sectionType) {
        try {
            const sectionEl = document.querySelector(`[data-section="${sectionType}"]`);
            if (!sectionEl) return;
            
            // Clone the section element
            const clone = sectionEl.cloneNode(true);
            
            // Generate new unique ID
            const newSectionId = `${sectionType}-${Date.now()}`;
            clone.setAttribute('data-section', newSectionId);
            
            // Update all data attributes in clone
            const buttons = clone.querySelectorAll('[data-section]');
            buttons.forEach(btn => {
                btn.setAttribute('data-section', newSectionId);
            });
            
            // Add duplicate indicator to title
            const titleEl = clone.querySelector('h3');
            if (titleEl) {
                titleEl.textContent += ' (עותק)';
            }
            
            // Insert after original
            sectionEl.insertAdjacentElement('afterend', clone);
            
            this.showSuccessMessage('הסקשן שוכפל בהצלחה');
            this.updateSectionOrder();
            
        } catch (error) {
            console.error('Error duplicating section:', error);
            this.showErrorMessage('שגיאה בשכפול הסקשן');
        }
    }
    
    /**
     * החלפת מצב הצגה/הסתרה של סקשן
     */
    toggleSectionVisibility(sectionType, button) {
        const sectionEl = document.querySelector(`[data-section="${sectionType}"]`);
        if (!sectionEl) return;
        
        const isHidden = sectionEl.classList.contains('section-hidden');
        const icon = button.querySelector('i');
        
        if (isHidden) {
            // Show section
            sectionEl.classList.remove('section-hidden', 'opacity-50');
            icon.className = 'ri-eye-off-line text-xs text-gray-600';
            button.title = 'הסתר';
            this.showSuccessMessage('הסקשן מוצג');
        } else {
            // Hide section
            sectionEl.classList.add('section-hidden', 'opacity-50');
            icon.className = 'ri-eye-line text-xs text-yellow-600';
            button.title = 'הצג';
            this.showSuccessMessage('הסקשן הוסתר');
        }
        
        this.updateSectionVisibility(sectionType, !isHidden);
    }
    
    /**
     * מחיקת סקשן
     */
    deleteSection(sectionType) {
        const sectionEl = document.querySelector(`[data-section="${sectionType}"]`);
        if (!sectionEl) return;
        
        const sectionName = sectionEl.querySelector('h3').textContent;
        
        if (confirm(`האם אתה בטוח שברצונך למחוק את הסקשן "${sectionName}"?`)) {
            // Add deletion animation
            sectionEl.style.transition = 'all 0.3s ease';
            sectionEl.style.transform = 'translateX(-100%)';
            sectionEl.style.opacity = '0';
            
            setTimeout(() => {
                sectionEl.remove();
                this.showSuccessMessage('הסקשן נמחק');
                this.updateSectionOrder();
            }, 300);
        }
    }
    
    /**
     * פתיחה/סגירה של כפתורי הפעולות
     */
    toggleSectionActions(sectionType) {
        const sectionEl = document.querySelector(`[data-section="${sectionType}"]`);
        if (!sectionEl) return;
        
        const actionsEl = sectionEl.querySelector('.section-actions');
        const toggleBtn = sectionEl.querySelector('.toggle-actions-btn i');
        
        if (!actionsEl || !toggleBtn) return;
        
        const isOpen = actionsEl.style.maxHeight && actionsEl.style.maxHeight !== '0px';
        
        if (isOpen) {
            // Close
            actionsEl.style.maxHeight = '0px';
            toggleBtn.style.transform = 'rotate(0deg)';
        } else {
            // Open
            actionsEl.style.maxHeight = actionsEl.scrollHeight + 'px';
            toggleBtn.style.transform = 'rotate(180deg)';
        }
        
        debugLog(`Toggled actions for section: ${sectionType}, isOpen: ${!isOpen}`);
    }
    
    /**
     * עדכון סדר הסקשנים במסד הנתונים
     */
    updateSectionOrder() {
        const sections = Array.from(document.querySelectorAll('.section-item')).map((el, index) => ({
            id: el.dataset.section,
            order: index
        }));
        
        debugLog('Updated section order:', sections);
        // TODO: Send to API to save order
    }
    
    /**
     * עדכון מצב הצגה של סקשן במסד הנתונים
     */
    updateSectionVisibility(sectionType, isVisible) {
        debugLog(`Section ${sectionType} visibility changed to:`, isVisible);
        // TODO: Send to API to save visibility state
    }
    
    /**
     * הצגת הודעת הצלחה
     */
    showSuccessMessage(message) {
        if (this.builder && this.builder.showStatusMessage) {
            this.builder.showStatusMessage(message, 'success');
        }
    }
    
    /**
     * הצגת הודעת שגיאה
     */
    showErrorMessage(message) {
        if (this.builder && this.builder.showStatusMessage) {
            this.builder.showStatusMessage(message, 'error');
        }
    }
} 