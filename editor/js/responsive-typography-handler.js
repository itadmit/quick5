/**
 * Responsive Typography Handler
 * טיפול בטיפוגרפיה responsive עם device switcher ויישור
 */

class ResponsiveTypographyHandler {
    constructor() {
        this.components = [];
        this.init();
    }

    init() {
        console.log('📝 Initializing Responsive Typography Handler');
        
        // צפה לרכיבים חדשים
        this.watchForNewComponents();
        
        // אתחל רכיבים קיימים
        this.initializeExistingComponents();
        
        console.log('✅ ResponsiveTypographyHandler fully initialized');
    }

    /**
     * צפייה לרכיבים חדשים
     */
    watchForNewComponents() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        const typographyComponents = node.querySelectorAll ? 
                                                   node.querySelectorAll('.responsive-typography-component') : [];
                        
                        typographyComponents.forEach(component => {
                            if (!component.dataset.initialized) {
                                this.initializeComponent(component);
                            }
                        });
                        
                        // אם הnode עצמו הוא typography component
                        if (node.classList && node.classList.contains('responsive-typography-component') && !node.dataset.initialized) {
                            this.initializeComponent(node);
                        }
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        console.log('👀 Typography component watcher started');
    }

    /**
     * אתחול רכיבים קיימים
     */
    initializeExistingComponents() {
        const existing = document.querySelectorAll('.responsive-typography-component:not([data-initialized])');
        console.log(`🔍 Found ${existing.length} existing typography components`);
        
        existing.forEach(component => {
            this.initializeComponent(component);
        });
    }

    /**
     * אתחול רכיב יחיד
     */
    initializeComponent(element) {
        if (element.dataset.initialized) return;
        
        const componentId = element.dataset.component || 'typo_' + Date.now();
        console.log(`📝 Initializing typography component: ${componentId}`);
        
        element.dataset.initialized = 'true';
        
        const component = new ResponsiveTypographyComponent(element);
        this.components.push(component);
        
        console.log(`✅ Typography component ${componentId} initialized successfully`);
    }
}

/**
 * קלאס לרכיב טיפוגרפיה יחיד
 */
class ResponsiveTypographyComponent {
    constructor(element) {
        this.element = element;
        this.componentId = element.dataset.component || 'typo_' + Date.now();
        this.currentDevice = 'desktop';
        this.init();
    }

    init() {
        console.log(`📝 Initializing typography component: ${this.componentId}`);
        
        this.setupDeviceSwitcher();
        this.setupAlignmentButtons();
        this.setupColorInputs();
        this.setupClearButtons();
        this.connectToSettingsManager();
        
        // אתחול הודעות ירושה
        this.updateInheritanceMessages(this.currentDevice);
        
        // רישום למנהל הסינכרון הגלובלי
        if (window.deviceSyncManager) {
            window.deviceSyncManager.registerComponent(this);
        }
        
        console.log(`📝 Component ${this.componentId} fully configured`);
    }

    /**
     * הגדרת device switcher
     */
    setupDeviceSwitcher() {
        const switcher = this.element.querySelector('.device-switcher');
        if (!switcher) return;

        const buttons = switcher.querySelectorAll('.device-btn');
        
        buttons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                
                const device = button.dataset.device;
                if (device) {
                    // אל תקרא ישירות - תן למנהל הגלובלי לטפל
                    // המנהל יסנכרן את כל הקומפוננטים
                    if (window.deviceSyncManager) {
                        // המנהל יטפל בהכל
                        return;
                    } else {
                        // fallback אם המנהל לא זמין
                        this.switchDevice(device, button);
                    }
                }
            });
        });

        console.log(`📱 Device switcher setup complete (${buttons.length} buttons)`);
    }

    /**
     * החלפת מכשיר
     */
    switchDevice(device, activeButton = null) {
        console.log(`📱 Switching to ${device} mode`);
        
        this.currentDevice = device;
        
        // עדכון כפתורים (רק אם לא מגיע מהמנהל הגלובלי)
        if (activeButton) {
            const allButtons = this.element.querySelectorAll('.device-btn');
            allButtons.forEach(btn => {
                btn.classList.remove('active', 'bg-white', 'shadow-sm');
                btn.classList.add('hover:bg-white');
            });
            
            activeButton.classList.add('active', 'bg-white', 'shadow-sm');
            activeButton.classList.remove('hover:bg-white');
        }
        
        // הצגת/הסתרת הגדרות
        const allSettings = this.element.querySelectorAll('.device-settings');
        allSettings.forEach(settings => {
            settings.style.display = settings.dataset.device === device ? 'block' : 'none';
        });
        
        // עדכון הודעות ירושה וכפתורי נקה
        this.updateInheritanceMessages(device);
        
        console.log(`📱 Device switched to: ${device}`);
    }

    /**
     * עדכון הגדרות המכשיר (פשוט - בלי ירושה)
     */
    updateInheritanceMessages(device) {
        // כבר אין הודעות ירושה - הצבע זהה בכל המכשירים
        console.log(`📱 Device switched to: ${device} (shared colors)`);
    }

    /**
     * עדכון הגדרות מכשיר (נקרא מהמנהל הגלובלי)
     */
    updateDeviceSettings(device) {
        this.switchDevice(device, null);
    }



    /**
     * הגדרת כפתורי יישור
     */
    setupAlignmentButtons() {
        const alignButtons = this.element.querySelectorAll('.align-btn');
        
        alignButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleAlignmentClick(button);
            });
        });

        console.log(`🎯 Alignment buttons setup complete (${alignButtons.length} buttons)`);
    }

    /**
     * טיפול בלחיצה על כפתור יישור
     */
    handleAlignmentClick(button) {
        const container = button.closest('.device-settings');
        const alignment = button.dataset.align;
        const path = button.dataset.path;
        
        console.log(`🎯 Alignment clicked: ${alignment} for path: ${path}`);
        
        // נקה בחירות קודמות באותו container
        container.querySelectorAll('.align-btn').forEach(btn => {
            btn.classList.remove('bg-blue-100', 'bg-blue-50');
        });
        
        // הוסף בחירה לכפתור שנלחץ
        button.classList.add('bg-blue-100');
        
        // צור/עדכן hidden input
        let hiddenInput = container.querySelector(`input[data-path="${path}"]`);
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.dataset.path = path;
            hiddenInput.dataset.responsive = 'true';
            container.appendChild(hiddenInput);
        }
        
        hiddenInput.value = alignment;
        
        // שלח דרך settings-manager
        if (window.builderMain?.components?.settingsManager) {
            const section = window.builderCore?.getSection(window.builderMain.components.settingsManager.currentSectionId);
            if (section) {
                window.builderMain.components.settingsManager.handleSettingChange(hiddenInput, section);
            }
        } else {
            // fallback
            hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
        
        console.log(`🎯 Alignment set to: ${alignment}`);
    }

    /**
     * הגדרת color inputs
     */
    setupColorInputs() {
        const colorInputs = this.element.querySelectorAll('input[type="color"]');
        
        colorInputs.forEach(input => {
            // רק אם אין value, תן ברירת מחדל
            if (!input.value) {
                input.value = '#000000';
            }
            
            input.addEventListener('input', (e) => {
                console.log(`🎨 Color changed: ${e.target.value} for ${e.target.dataset.path}`);
                
                // עדכן אינדיקטור צבע מחשב אם אנחנו במצב מובייל
                if (this.currentDevice === 'mobile') {
                    setTimeout(() => this.updateDesktopColorIndicator(), 10);
                }
                
                // שלח דרך settings-manager
                if (window.builderMain?.components?.settingsManager) {
                    const section = window.builderCore?.getSection(window.builderMain.components.settingsManager.currentSectionId);
                    if (section) {
                        window.builderMain.components.settingsManager.handleSettingChange(e.target, section);
                    }
                }
            });
        });

        console.log(`🎨 Color inputs setup complete (${colorInputs.length} inputs)`);
    }

    /**
     * הגדרת כפתורי ניקוי
     */
    setupClearButtons() {
        const clearButtons = this.element.querySelectorAll('.clear-color-btn');
        
        clearButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const targetSelector = button.dataset.target;
                const targetInput = this.element.querySelector(targetSelector);
                
                if (targetInput) {
                    // פשוט - אפס לצבע ברירת מחדל (שחור)
                    targetInput.value = '#000000';
                    targetInput.dispatchEvent(new Event('input', { bubbles: true }));
                    console.log(`🗑️ Reset color to default: ${targetSelector}`);
                }
            });
        });

        console.log(`🗑️ Clear buttons setup complete (${clearButtons.length} buttons)`);
    }

    /**
     * חיבור לsettings-manager
     */
    connectToSettingsManager() {
        setTimeout(() => {
            if (window.builderMain?.components?.settingsManager) {
                console.log('🔗 Connecting typography to settings-manager');
                
                // מצא את כל הinputs ותוודא שהם מחוברים
                const allInputs = this.element.querySelectorAll('input, select, textarea');
                
                let connectedCount = 0;
                allInputs.forEach(input => {
                    if (input.dataset.path && !input._typographyEventsBound) {
                        console.log(`🔗 Connecting input: ${input.dataset.path}`);
                        
                        // שלח דרך settings-manager במקום להוסיף event listeners
                        const eventType = input.type === 'color' ? 'input' : 'change';
                        
                        input.addEventListener(eventType, (e) => {
                            if (window.builderMain?.components?.settingsManager) {
                                const section = window.builderCore?.getSection(window.builderMain.components.settingsManager.currentSectionId);
                                if (section) {
                                    window.builderMain.components.settingsManager.handleSettingChange(e.target, section);
                                }
                            }
                        });
                        
                        input._typographyEventsBound = true;
                        connectedCount++;
                    }
                });
                
                console.log(`🔗 Connected ${connectedCount} inputs to settings-manager`);
            } else {
                console.warn('⚠️ Settings-manager not found, typography events may not work');
            }
        }, 100);
    }
}

// אתחול כשהDOM מוכן
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.responsiveTypographyHandler = new ResponsiveTypographyHandler();
    });
} else {
    window.responsiveTypographyHandler = new ResponsiveTypographyHandler();
}

console.log('📁 responsive-typography-handler.js loaded'); 