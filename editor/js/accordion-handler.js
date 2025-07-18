/**
 * Accordion Handler
 * מטפל באקורדיון פשוט למאפייני HTML
 */

class AccordionHandler {
    constructor() {
        console.log('🎯 Initializing Accordion Handler');
        this.init();
        console.log('✅ AccordionHandler fully initialized');
    }

    init() {
        // וידוא שהפונקציה זמינה גלובלית
        window.toggleAccordion = this.toggleAccordion.bind(this);
        
        // קביעת מצב ראשוני נכון לכל החצים
        setTimeout(() => {
            this.initializeArrowStates();
        }, 100);
    }

    /**
     * קביעת מצב ראשוני נכון לכל החצים
     */
    initializeArrowStates() {
        const accordionContents = document.querySelectorAll('.accordion-content');
        
        accordionContents.forEach(content => {
            const accordionId = content.id;
            const arrow = document.getElementById('arrow_' + accordionId);
            const header = content.previousElementSibling;
            
            if (arrow && content && header) {
                const computedStyle = window.getComputedStyle(content);
                const isOpen = computedStyle.display !== 'none' && content.offsetHeight > 0;
                
                if (isOpen) {
                    arrow.style.transform = 'rotate(180deg)';
                    header.classList.add('open');
                } else {
                    arrow.style.transform = 'rotate(0deg)';
                    header.classList.remove('open');
                }
                
                console.log(`🎯 Arrow and header initialized for ${accordionId}: ${isOpen ? 'open' : 'closed'}`);
            }
        });
    }

    /**
     * הפעלת/כיבוי אקורדיון
     */
    toggleAccordion(accordionId) {
        const content = document.getElementById(accordionId);
        const arrow = document.getElementById('arrow_' + accordionId);
        
        if (!content || !arrow) {
            console.warn('⚠️ Accordion elements not found:', accordionId);
            return;
        }
        
        // בדיקה אמיתית של המצב הויזואלי של האלמנט
        const computedStyle = window.getComputedStyle(content);
        const isOpen = computedStyle.display !== 'none' && content.offsetHeight > 0;
        
        console.log(`🔧 Toggling accordion ${accordionId}, currently ${isOpen ? 'open' : 'closed'}`);
        
        // מצא את הheader כדי לעדכן את הכיתה
        const header = content.previousElementSibling;
        
        if (isOpen) {
            // סגירה עם אנימציה חלקה
            content.style.maxHeight = content.scrollHeight + 'px';
            content.style.transition = 'max-height 0.3s ease, opacity 0.3s ease';
            content.style.overflow = 'hidden';
            
            setTimeout(() => {
                content.style.maxHeight = '0px';
                content.style.opacity = '0';
            }, 10);
            
            setTimeout(() => {
                content.style.display = 'none';
                content.style.maxHeight = '';
                content.style.opacity = '';
                content.style.transition = '';
                content.style.overflow = '';
                
                // הסר את כיתת open מהheader
                if (header) {
                    header.classList.remove('open');
                }
            }, 300);
            
            arrow.style.transform = 'rotate(0deg)';
            console.log('🔻 Accordion closed:', accordionId);
        } else {
            // פתיחה עם אנימציה חלקה
            content.style.display = 'block';
            content.style.maxHeight = '0px';
            content.style.opacity = '0';
            content.style.transition = 'max-height 0.3s ease, opacity 0.3s ease';
            content.style.overflow = 'hidden';
            
            // הוסף את כיתת open לheader מיד
            if (header) {
                header.classList.add('open');
            }
            
            setTimeout(() => {
                content.style.maxHeight = content.scrollHeight + 'px';
                content.style.opacity = '1';
            }, 10);
            
            setTimeout(() => {
                content.style.maxHeight = '';
                content.style.transition = '';
                content.style.overflow = '';
            }, 300);
            
            arrow.style.transform = 'rotate(180deg)';
            console.log('🔺 Accordion opened:', accordionId);
        }
    }
}

// יצירת instance גלובלי
document.addEventListener('DOMContentLoaded', () => {
    if (!window.accordionHandler) {
        window.accordionHandler = new AccordionHandler();
    }
});

console.log('📁 accordion-handler.js loaded'); 