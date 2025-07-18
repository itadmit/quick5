/**
 * Responsive Height Handler - מנהל קומפוננט גובה responsive
 * מטפל בהחלפת מכשירים ועדכון ערכים עבור גובה הסקשן
 */

class ResponsiveHeightHandler {
    constructor() {
        this.components = [];
        this.init();
    }

    init() {
        console.log('📐 Initializing Responsive Height Handler');
        
        // צפה לקומפוננטים חדשים
        this.watchForNewComponents();
        
        // אתחל קומפוננטים קיימים
        this.initializeExistingComponents();
        
        console.log('✅ ResponsiveHeightHandler fully initialized');
    }

    /**
     * צפייה לקומפוננטים חדשים
     */
    watchForNewComponents() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        const heightComponents = node.querySelectorAll('.device-switcher[data-component-id^="height_"]');
                        heightComponents.forEach(component => {
                            const componentId = component.dataset.componentId;
                            if (!this.components.find(c => c.id === componentId)) {
                                this.initializeComponent(componentId, component);
                            }
                        });
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        console.log('👀 Height component watcher started');
    }

    /**
     * אתחול קומפוננטים קיימים
     */
    initializeExistingComponents() {
        const existingComponents = document.querySelectorAll('.device-switcher[data-component-id^="height_"]');
        console.log(`🔍 Found ${existingComponents.length} existing height components`);
        
        existingComponents.forEach(component => {
            const componentId = component.dataset.componentId;
            this.initializeComponent(componentId, component);
        });
    }

    /**
     * אתחול קומפוננט יחיד
     */
    initializeComponent(componentId, switcherElement) {
        console.log('📐 Initializing height component:', componentId);
        
        const container = switcherElement.closest('.settings-group');
        if (!container) {
            console.error('❌ Container not found for height component');
            return;
        }

        const component = {
            id: componentId,
            container: container,
            switcher: switcherElement,
            currentDevice: 'desktop'
        };

        // הגדרת device switcher
        this.setupDeviceSwitcher(component);

        // רישום עבור device sync
        if (window.deviceSyncManager) {
            window.deviceSyncManager.registerComponent(component);
        }

        this.components.push(component);
        
        console.log('✅ Height component', componentId, 'initialized successfully');
    }

    /**
     * הגדרת device switcher
     */
    setupDeviceSwitcher(component) {
        const buttons = component.switcher.querySelectorAll('.device-btn');
        
        buttons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const device = button.dataset.device;
                
                if (device && device !== component.currentDevice) {
                    this.switchDevice(component, device);
                }
            });
        });

        console.log('📱 Device switcher setup complete (2 buttons)');
        
        // התחלה במצב desktop
        this.updateDeviceSettings(component, 'desktop');
    }

    /**
     * החלפת מכשיר
     */
    switchDevice(component, device) {
        console.log(`📱 Switching height component to ${device} mode`);
        
        component.currentDevice = device;
        
        // עדכון כפתורי הswitcher
        const buttons = component.switcher.querySelectorAll('.device-btn');
        buttons.forEach(btn => {
            btn.classList.remove('bg-white', 'shadow-sm');
            btn.classList.add('hover:bg-white');
            
            if (btn.dataset.device === device) {
                btn.classList.add('bg-white', 'shadow-sm');
                btn.classList.remove('hover:bg-white');
            }
        });
        
        // עדכון הגדרות המכשיר
        this.updateDeviceSettings(component, device);
        
        console.log(`📱 Height component switched to ${device}`);
    }

    /**
     * עדכון הגדרות המכשיר
     */
    updateDeviceSettings(component, device) {
        const settingsPanels = component.container.querySelectorAll('.device-settings');
        
        settingsPanels.forEach(panel => {
            const panelDevice = panel.dataset.device;
            panel.style.display = panelDevice === device ? 'block' : 'none';
        });
        
        console.log(`📱 Updated device settings to ${device}`);
    }

    /**
     * קבלת קומפוננט לפי ID
     */
    getComponent(componentId) {
        return this.components.find(c => c.id === componentId);
    }

    /**
     * טיפול בשינוי שדות הגובה - חיבור value+unit
     */
    handleHeightChange(input) {
        const path = input.dataset.path;
        
        // זהה אם זה שדה value או unit
        if (path.endsWith('.value') || path.endsWith('.unit')) {
            const basePath = path.replace(/\.(value|unit)$/, '');
            const container = input.closest('.device-settings');
            
            if (container) {
                // מצא את שני השדות
                const valueInput = container.querySelector(`[data-path="${basePath}.value"]`);
                const unitSelect = container.querySelector(`[data-path="${basePath}.unit"]`);
                
                if (valueInput && unitSelect) {
                    const value = valueInput.value;
                    const unit = unitSelect.value;
                    
                    // חבר את הערכים
                    let combinedValue = '';
                    if (value && unit !== 'auto') {
                        combinedValue = value + unit;
                    } else if (unit === 'auto') {
                        combinedValue = 'auto';
                    }
                    
                    console.log(`📐 Height changed: ${basePath} = ${combinedValue}`);
                    
                    // הsettings manager כבר מטפל אוטומטית בעדכון הערך דרך event listeners
                    // אין צורך בקריאה ידנית
                }
            }
        }
    }

    /**
     * פיצול ערך מורכב (כמו "100vh") לvalue+unit
     */
    parseHeightValue(heightValue) {
        if (!heightValue || heightValue === 'auto') {
            return { value: '', unit: 'auto' };
        }
        
        // חיפוש יחידה בסוף הערך
        const match = heightValue.match(/^(\d+(?:\.\d+)?)(vh|px|%)$/);
        
        if (match) {
            return {
                value: match[1],
                unit: match[2] === '%' ? 'vh' : match[2] // המרת % ל-vh
            };
        }
        
        // ברירת מחדל אם הפרמט לא מזוהה
        return { value: heightValue, unit: 'vh' };
    }

    /**
     * אכלוס ערכים קיימים לשדות הפרדיים
     */
    populateHeightInputs(container, basePath, currentValue) {
        const valueInput = container.querySelector(`[data-path="${basePath}.value"]`);
        const unitSelect = container.querySelector(`[data-path="${basePath}.unit"]`);
        
        if (valueInput && unitSelect && currentValue) {
            let parsed;
            
            // בדוק אם זה כבר אובייקט {value, unit} או string שצריך לפרק
            if (typeof currentValue === 'object' && currentValue.value && currentValue.unit) {
                // פורמט חדש - כבר מופרד
                parsed = currentValue;
            } else {
                // פורמט ישן - string שצריך לפרק
                parsed = this.parseHeightValue(currentValue);
            }
            
            valueInput.value = parsed.value || '';
            unitSelect.value = parsed.unit || 'vh';
            
            console.log(`📐 Populated height: ${basePath} = ${JSON.stringify(currentValue)} -> value=${parsed.value}, unit=${parsed.unit}`);
        }
    }

    /**
     * חיבור לsettings-manager
     */
    connectToSettingsManager() {
        console.log('🔗 Connecting height components to settings-manager');
        
        this.components.forEach(component => {
            // מצא את כל הinputs בקומפוננט
            const inputs = component.container.querySelectorAll('[data-path][data-responsive]');
            
            inputs.forEach(input => {
                const path = input.dataset.path;
                if (path && window.builderMain?.components?.settingsManager) {
                    console.log('🔗 Connecting input:', path);
                    
                    // הוסף event listener מיוחד לשדות גובה
                    input.addEventListener('input', () => {
                        this.handleHeightChange(input);
                    });
                    
                    input.addEventListener('change', () => {
                        this.handleHeightChange(input);
                    });
                    
                    // קשור את האירוע לsettings-manager
                    const eventType = input.type === 'range' ? 'input' : 'change';
                    input.addEventListener(eventType, (e) => {
                        const section = window.builderCore?.getSection(
                            window.builderMain.components.settingsManager.currentSectionId
                        );
                        if (section) {
                            window.builderMain.components.settingsManager.handleSettingChange(input, section);
                        }
                    });
                }
            });
            
            console.log(`🔗 Connected ${inputs.length} inputs to settings-manager`);
        });
        
        // האזן לאירועי הגדרות כדי לטפל באכלוס נתונים
        if (window.settingsManager) {
            // האזן לאירוע הצגת הגדרות
            const originalShowSectionSettings = window.settingsManager.showSectionSettings;
            window.settingsManager.showSectionSettings = function(section) {
                const result = originalShowSectionSettings.call(this, section);
                
                // אכלס נתונים לרכיבי גובה
                setTimeout(() => {
                    window.responsiveHeightHandler.populateExistingHeightData(section);
                }, 100);
                
                return result;
            };
        }
    }

    /**
     * אכלוס נתונים קיימים לשדות גובה
     */
    populateExistingHeightData(section) {
        console.log('📐 Populating existing height data', section);
        
        this.components.forEach(component => {
            // מצא containers של desktop ו-mobile
            const desktopContainer = component.container.querySelector('.device-settings[data-device="desktop"]');
            const mobileContainer = component.container.querySelector('.device-settings[data-device="mobile"]');
            
            if (desktopContainer) {
                const desktopHeight = this.getNestedValue(section, 'styles.height.desktop', '');
                if (desktopHeight) {
                    this.populateHeightInputs(desktopContainer, 'styles.height.desktop', desktopHeight);
                }
            }
            
            if (mobileContainer) {
                const mobileHeight = this.getNestedValue(section, 'styles.height.mobile', '');
                if (mobileHeight) {
                    this.populateHeightInputs(mobileContainer, 'styles.height.mobile', mobileHeight);
                }
            }
        });
        
        console.log('📐 Completed populating height data');
    }

    /**
     * עזר לקבלת ערך מאוביקט עמוק
     */
    getNestedValue(obj, path, defaultValue = null) {
        const keys = path.split('.');
        let current = obj;
        
        for (const key of keys) {
            if (current && typeof current === 'object' && key in current) {
                current = current[key];
            } else {
                return defaultValue;
            }
        }
        
        return current;
    }
}

// יצירת instance גלובלי
window.responsiveHeightHandler = new ResponsiveHeightHandler();

// חיבור לsettings-manager כשהוא זמין
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        if (window.responsiveHeightHandler && window.builderMain?.components?.settingsManager) {
            window.responsiveHeightHandler.connectToSettingsManager();
        }
    }, 500);
});

console.log('📁 responsive-height-handler.js loaded'); 