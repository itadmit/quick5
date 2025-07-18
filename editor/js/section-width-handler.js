/**
 * Section Width Handler
 * מטפל באפשרויות רוחב הסקשן ומידות נוספות
 */

class SectionWidthHandler {
    constructor() {
        console.log('📐 Initializing Section Width Handler');
        this.init();
        this.watchForNewComponents();
        this.setupExistingComponents();
        console.log('✅ SectionWidthHandler fully initialized');
    }

    init() {
        // הגדרת event listeners גלובליים
        document.addEventListener('change', (e) => {
            if (e.target.matches('[data-path="styles.section-width"]')) {
                this.handleWidthChange(e.target);
            }
        });
    }

    watchForNewComponents() {
        console.log('👀 Section width component watcher started');
        
        // observer לגילוי רכיבים חדשים
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        const widthSelects = node.querySelectorAll('[data-path="styles.section-width"]');
                        widthSelects.forEach(select => {
                            console.log('📐 Found new section width component');
                            this.setupWidthComponent(select);
                        });
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    setupExistingComponents() {
        console.log('🔍 Looking for existing section width components');
        const existingSelects = document.querySelectorAll('[data-path="styles.section-width"]');
        console.log(`🔍 Found ${existingSelects.length} existing section width components`);
        
        existingSelects.forEach(select => {
            this.setupWidthComponent(select);
        });
    }

    setupWidthComponent(select) {
        console.log('📐 Setting up section width component');
        
        // מצא את האלמנט המכיל
        const container = select.closest('.settings-group');
        if (!container) {
            console.warn('⚠️ Could not find settings group container');
            return;
        }

        // מצא את השדה המותאם אישית
        const customWidthDiv = container.querySelector('.custom-width-settings');
        if (!customWidthDiv) {
            console.warn('⚠️ Could not find custom width settings div');
            return;
        }

        // הגדר את המצב הראשוני
        this.updateCustomWidthVisibility(select, customWidthDiv);

        console.log('✅ Section width component setup complete');
    }

    handleWidthChange(select) {
        console.log('📐 Section width changed:', select.value);
        
        const container = select.closest('.settings-group');
        if (!container) return;

        const customWidthDiv = container.querySelector('.custom-width-settings');
        if (!customWidthDiv) return;

        this.updateCustomWidthVisibility(select, customWidthDiv);

        // הsettings manager כבר מטפל אוטומטית בעדכון הערך דרך event listeners
        // אין צורך בקריאה ידנית
    }

    updateCustomWidthVisibility(select, customWidthDiv) {
        if (select.value === 'custom') {
            customWidthDiv.style.display = 'block';
            console.log('📐 Custom width settings shown');
        } else {
            customWidthDiv.style.display = 'none';
            console.log('📐 Custom width settings hidden');
        }
    }

    // חיבור לsettings manager
    connectToSettingsManager() {
        console.log('🔗 Connecting section width to settings-manager');
        
        const inputs = document.querySelectorAll([
            '[data-path="styles.section-width"]',
            '[data-path="styles.max-width"]',
            '[data-path="styles.min-height"]',
            '[data-path="styles.max-height"]'
        ].join(', '));

        inputs.forEach(input => {
            console.log('🔗 Connecting input:', input.dataset.path);
        });

        console.log(`🔗 Connected ${inputs.length} inputs to settings-manager`);
        return inputs.length;
    }
}

// יצירת instance גלובלי
window.sectionWidthHandler = new SectionWidthHandler();

// חיבור לsettings-manager כשהוא זמין
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        if (window.sectionWidthHandler && window.builderMain?.components?.settingsManager) {
            window.sectionWidthHandler.connectToSettingsManager();
        }
    }, 500);
});

console.log('📁 section-width-handler.js loaded'); 