/**
 * Simple Background Handler - מטפל בקומפוננט רקע פשוט ללא מחשב/מובייל
 */

class SimpleBackgroundHandler {
    constructor() {
        this.init();
        console.log('🎨 SimpleBackgroundHandler initialized');
    }

    init() {
        this.startWatcher();
        this.initializeExistingComponents();
        console.log('✅ SimpleBackgroundHandler fully initialized');
    }

    /**
     * מתחיל לפקח על יצירת קומפוננטים חדשים
     */
    startWatcher() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        // חפש קומפוננטים חדשים
                        const components = node.querySelectorAll ? 
                            node.querySelectorAll('.simple-bg-settings-area') : [];
                        
                        // אם הnode עצמו הוא קומפוננט
                        if (node.classList && node.classList.contains('simple-bg-settings-area')) {
                            this.initializeComponent(node);
                        }
                        
                        // אתחל כל הקומפוננטים שנמצאו
                        components.forEach(component => {
                            this.initializeComponent(component);
                        });
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        console.log('👀 Simple background component watcher started');
    }

    /**
     * אתחול קומפוננטים קיימים
     */
    initializeExistingComponents() {
        const existingComponents = document.querySelectorAll('.simple-bg-settings-area');
        console.log(`🔍 Found ${existingComponents.length} existing simple background components`);
        
        existingComponents.forEach(component => {
            this.initializeComponent(component);
        });
    }

    /**
     * אתחול קומפוננט יחיד
     */
    initializeComponent(element) {
        // יצירת ID ייחודי לקומפוננט
        const componentId = 'simple_bg_' + Math.random().toString(36).substring(2, 15);
        element.id = componentId;
        
        console.log(`🎨 Initializing simple background component: ${componentId}`);
        console.log('🔍 Element:', element);
        console.log('🔍 Element HTML (first 300 chars):', element.outerHTML?.substring(0, 300));
        console.log('🔍 Parent element:', element.parentElement);
        console.log('🔍 Parent HTML (first 300 chars):', element.parentElement?.outerHTML?.substring(0, 300));
        
        const component = new SimpleBackgroundComponent(element);
        console.log(`✅ Simple background component ${componentId} initialized successfully`);
    }
}

/**
 * קלאס לקומפוננט רקע פשוט יחיד
 */
class SimpleBackgroundComponent {
    constructor(element) {
        this.element = element;
        this.container = element.parentElement || element.closest('.settings-group') || element.closest('div') || document; // האלמנט שמכיל את כל הקומפוננט
        this.init();
    }

    init() {
        // אתחל מערך ערכים שמורים
        this.savedValues = {};
        
        this.setupBackgroundTypes();
        this.setupRangeSliders();
        this.setupColorInputs();
        this.setupClearButtons();
        this.connectToSettingsManager();
        this.initializeDefaultState();
        
        // הוסף ID לקומפוננט
        this.componentId = this.element.id;
        
        console.log(`🎨 Component ${this.element.id} fully configured`);
    }
    
    /**
     * חיבור לsettings-manager הקיים
     */
    connectToSettingsManager() {
        // חכה לsettings-manager להיות מוכן
        setTimeout(() => {
            if (window.builderMain?.components?.settingsManager) {
                console.log('🔗 Connecting simple background to settings-manager');
                
                // מצא את כל הinputs ותוודא שהם מחוברים
                const allInputs = this.container.querySelectorAll('input, select, textarea').length > 0 ? 
                                 this.container.querySelectorAll('input, select, textarea') : 
                                 this.element.querySelectorAll('input, select, textarea');
                
                allInputs.forEach(input => {
                    if (input.dataset.path && !input._settingsEventsBound) {
                        console.log(`🔗 Connecting input: ${input.dataset.path}`);
                        
                        // שלח דרך settings-manager
                        const eventType = input.type === 'color' || input.type === 'range' ? 'input' : 'change';
                        
                        input.addEventListener(eventType, (e) => {
                            const section = window.builderCore?.getSection(window.builderMain.components.settingsManager.currentSectionId);
                            if (section) {
                                console.log(`🔧 Simple background input changed: ${input.dataset.path} = ${input.value}`);
                                window.builderMain.components.settingsManager.handleSettingChange(input, section);
                            }
                        });
                        
                        input._settingsEventsBound = true;
                    }
                });
                
                console.log(`🔗 Connected ${allInputs.length} inputs to settings-manager`);
            } else {
                console.warn('⚠️ Settings-manager not found, retrying...');
                // נסה שוב אחרי 500ms
                setTimeout(() => this.connectToSettingsManager(), 500);
            }
        }, 200);
    }

    /**
     * הגדרת כפתורי סוג רקע
     */
    setupBackgroundTypes() {
        console.log('🔍 Looking for buttons in container:', this.container);
        console.log('🔍 Container HTML:', this.container?.outerHTML?.substring(0, 200));
        
        const typeButtons = this.container.querySelectorAll('.simple-bg-type-btn');
        console.log('🔍 Found buttons:', typeButtons);
        
        // נסיון נוסף - חפש בכל המסמך
        const allButtons = document.querySelectorAll('.simple-bg-type-btn');
        console.log('🔍 All buttons in document:', allButtons);
        
        // נסיון נוסף - חפש בcontainer עם selector רחב יותר
        const allInContainer = this.container.querySelectorAll('*');
        console.log('🔍 All elements in container:', allInContainer.length);
        
        // נסיון נוסף - חפש בelement עצמו
        const buttonsInElement = this.element.querySelectorAll('.simple-bg-type-btn');
        console.log('🔍 Buttons in element itself:', buttonsInElement);
        
        // אם לא מצאנו כפתורים בcontainer, נחפש בכל המסמך ונקח אלה שמתייחסים לקומפוננט הזה
        let buttonsToUse = typeButtons;
        if (typeButtons.length === 0 && allButtons.length > 0) {
            console.log('🔧 No buttons in container, using document-wide search');
            buttonsToUse = allButtons;
        }
        
        buttonsToUse.forEach(button => {
            console.log('🔍 Setting up button:', button.dataset.type);
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.selectBackgroundType(button);
            });
        });
        
        console.log(`🎨 Background type setup complete (${buttonsToUse.length} buttons)`);
    }

    /**
     * בחירת סוג רקע
     */
    selectBackgroundType(button) {
        const type = button.dataset.type;
        
        console.log(`🎨 Simple background type button clicked: ${type}`);
        
        // שמור ערכים נוכחיים לפני החלפה
        this.saveCurrentValues();
        
        // הסר active state מכל הכפתורים
        const allTypeButtons = this.container.querySelectorAll('.simple-bg-type-btn').length > 0 ? 
                              this.container.querySelectorAll('.simple-bg-type-btn') : 
                              document.querySelectorAll('.simple-bg-type-btn');
        allTypeButtons.forEach(btn => {
            btn.classList.remove('border-purple-500', 'bg-purple-50');
        });
        
        // הוסף active state לכפתור שנלחץ
        button.classList.add('border-purple-500', 'bg-purple-50');
        
        // הסתר כל הpanels
        const allPanels = this.container.querySelectorAll('.simple-bg-setting').length > 0 ? 
                         this.container.querySelectorAll('.simple-bg-setting') : 
                         this.element.querySelectorAll('.simple-bg-setting');
        allPanels.forEach(panel => {
            panel.style.display = 'none';
        });
        
        // הצג panel רלוונטי
        const targetPanel = this.container.querySelector(`.simple-bg-setting[data-type="${type}"]`) || 
                           this.element.querySelector(`.simple-bg-setting[data-type="${type}"]`);
        if (targetPanel) {
            targetPanel.style.display = 'block';
            console.log(`👀 Showing panel for type: ${type}`);
        } else {
            console.error(`❌ Panel not found for type: ${type}`);
        }
        
        // שחזר ערכים שמורים לפני עדכון הsetting
        setTimeout(() => {
            this.restoreSavedValues();
        }, 50);
        
        // עדכן input נסתר עבור סוג רקע (ללא ניקוי ערכים!)
        this.updateBackgroundTypeInput(button.dataset.path, type);
        
        console.log(`🎨 Background type changed to: ${type} (Simple Background)`);
    }

    /**
     * ניקוי ערכי רקע של סוגים אחרים (שימוש זהיר - רק כשצריך באמת!)
     */
    clearOtherBackgroundTypes(selectedType) {
        // רשימת כל סוגי הרקע ושדותיהם
        const backgroundTypes = {
            'color': [],
            'gradient': ['gradient-color1', 'gradient-color2', 'gradient-direction'],
            'image': ['background-image', 'background-image-mobile', 'background-size', 'background-repeat', 'image-overlay-opacity'],
            'video': ['background-video', 'background-video-mobile', 'video-overlay-opacity', 'video-muted', 'video-loop']
        };
        
        // נקה את השדות של כל הסוגים האחרים
        Object.keys(backgroundTypes).forEach(type => {
            if (type !== selectedType) {
                backgroundTypes[type].forEach(field => {
                    const input = this.container.querySelector(`input[data-path*="${field}"], select[data-path*="${field}"]`) ||
                                 this.element.querySelector(`input[data-path*="${field}"], select[data-path*="${field}"]`);
                    if (input && (input.value || input.checked)) {
                        console.log(`🧹 Clearing ${field}="${input.value || input.checked}" (switching to ${selectedType})`);
                        
                        if (input.type === 'checkbox') {
                            input.checked = false;
                        } else if (input.type === 'color') {
                            // לcolor inputs, השתמש בdefault value במקום ריק
                            input.value = input.getAttribute('value') || '#000000';
                        } else {
                            input.value = '';
                        }
                        
                        // שלח דרך settings-manager
                        if (window.builderMain?.components?.settingsManager) {
                            const section = window.builderCore?.getSection(window.builderMain.components.settingsManager.currentSectionId);
                            if (section) {
                                window.builderMain.components.settingsManager.handleSettingChange(input, section);
                            }
                        } else {
                            // fallback
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                });
            }
        });
    }

    /**
     * שמירת ערכים קיימים של כל סוגי הרקע
     */
    saveCurrentValues() {
        if (!this.savedValues) {
            this.savedValues = {};
        }
        
        const allInputs = this.container.querySelectorAll('input[data-path], select[data-path]');
        allInputs.forEach(input => {
            const path = input.dataset.path;
            if (path) {
                if (input.type === 'checkbox') {
                    this.savedValues[path] = input.checked;
                } else {
                    this.savedValues[path] = input.value;
                }
            }
        });
        
        console.log('💾 Saved current values:', this.savedValues);
    }
    
    /**
     * שחזור ערכים שמורים
     */
    restoreSavedValues() {
        if (!this.savedValues) return;
        
        Object.keys(this.savedValues).forEach(path => {
            const input = this.container.querySelector(`input[data-path="${path}"], select[data-path="${path}"]`);
            if (input) {
                const savedValue = this.savedValues[path];
                if (input.type === 'checkbox') {
                    input.checked = savedValue;
                } else if (savedValue !== undefined && savedValue !== null && savedValue !== '') {
                    input.value = savedValue;
                }
            }
        });
        
        console.log('🔄 Restored saved values');
    }

    /**
     * עדכון input נסתר לסוג רקע
     */
    updateBackgroundTypeInput(path, type) {
        if (!path) return;
        
        let hiddenInput = this.container.querySelector(`input[data-path="${path}"]`);
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.dataset.path = path;
            this.container.appendChild(hiddenInput);
        }
        
        hiddenInput.value = type;
        hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
        
        // התחבר לsettings-manager אם קיים
        if (window.builderMain?.components?.settingsManager) {
            const section = window.builderCore?.getSection(window.builderMain.components.settingsManager.currentSectionId);
            if (section) {
                window.builderMain.components.settingsManager.handleSettingChange(hiddenInput, section);
            }
        }
    }

    /**
     * הגדרת range sliders
     */
    setupRangeSliders() {
        const rangeInputs = this.container.querySelectorAll('input[type="range"]').length > 0 ? 
                           this.container.querySelectorAll('input[type="range"]') : 
                           this.element.querySelectorAll('input[type="range"]');
        
        rangeInputs.forEach(rangeInput => {
            const updateValue = () => {
                const valueSpan = rangeInput.parentElement.querySelector('.overlay-value');
                if (valueSpan) {
                    valueSpan.textContent = rangeInput.value + '%';
                }
            };
            
            rangeInput.addEventListener('input', updateValue);
            updateValue(); // אתחול
        });
        
        console.log(`📊 Range sliders setup complete (${rangeInputs.length} sliders)`);
    }

    /**
     * הגדרת color inputs
     */
    setupColorInputs() {
        const colorInputs = this.container.querySelectorAll('input[type="color"]').length > 0 ? 
                           this.container.querySelectorAll('input[type="color"]') : 
                           this.element.querySelectorAll('input[type="color"]');
        
        // Events נשלחים אוטומטית על ידי הinputs
        console.log(`🎨 Color inputs setup complete (${colorInputs.length} inputs)`);
    }

    /**
     * הגדרת כפתורי ניקוי
     */
    setupClearButtons() {
        const clearBtns = this.container.querySelectorAll('.clear-btn').length > 0 ? 
                         this.container.querySelectorAll('.clear-btn') : 
                         this.element.querySelectorAll('.clear-btn');
        
        clearBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                
                const targetSelector = btn.dataset.target;
                if (targetSelector) {
                    const targetInput = this.container.querySelector(targetSelector) ||
                                       this.element.querySelector(targetSelector);
                    if (targetInput) {
                        targetInput.value = '';
                        targetInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
            });
        });
        
        console.log(`🗑️ Clear buttons setup complete (${clearBtns.length} buttons)`);
    }

    /**
     * אתחול מצב ברירת מחדל
     */
    initializeDefaultState() {
        setTimeout(() => {
            console.log('🎨 Initializing simple background default state...');
            
            // חפש input עם background-type קיים
            const backgroundTypeInput = this.container.querySelector(`input[data-path*="background-type"]`) ||
                                       this.element.querySelector(`input[data-path*="background-type"]`);
            let currentType = 'color'; // ברירת מחדל
            
            console.log('🔍 Background type input:', backgroundTypeInput, 'value:', backgroundTypeInput?.value);
            
            if (backgroundTypeInput && backgroundTypeInput.value) {
                currentType = backgroundTypeInput.value;
                console.log(`🎨 Found existing background type: ${currentType}`);
            } else {
                // אם אין background-type, נבדוק אם יש נתונים לסוגים אחרים
                const videoInput = this.container.querySelector(`input[data-path*="background-video"]`) ||
                                  this.element.querySelector(`input[data-path*="background-video"]`);
                const imageInput = this.container.querySelector(`input[data-path*="background-image"]`) ||
                                  this.element.querySelector(`input[data-path*="background-image"]`);
                const gradientInput1 = this.container.querySelector(`input[data-path*="gradient-color1"]`) ||
                                      this.element.querySelector(`input[data-path*="gradient-color1"]`);
                const gradientInput2 = this.container.querySelector(`input[data-path*="gradient-color2"]`) ||
                                      this.element.querySelector(`input[data-path*="gradient-color2"]`);
                
                console.log('🔍 Other inputs - video:', videoInput?.value, 'image:', imageInput?.value, 'gradient1:', gradientInput1?.value, 'gradient2:', gradientInput2?.value);
                
                if (videoInput && videoInput.value) {
                    currentType = 'video';
                    console.log(`🎨 Detected video background`);
                } else if (imageInput && imageInput.value) {
                    currentType = 'image';
                    console.log(`🎨 Detected image background`);
                } else if ((gradientInput1 && gradientInput1.value) || (gradientInput2 && gradientInput2.value)) {
                    currentType = 'gradient';
                    console.log(`🎨 Detected gradient background`);
                }
            }
            
            // בחר את הכפתור הנכון
            const targetBtn = this.container.querySelector(`.simple-bg-type-btn[data-type="${currentType}"]`) ||
                              document.querySelector(`.simple-bg-type-btn[data-type="${currentType}"]`);
            console.log('🔍 Target button for type', currentType, ':', targetBtn);
            
            if (targetBtn) {
                console.log(`🎨 Auto-clicking ${currentType} type`);
                targetBtn.click();
            } else {
                // fallback לצבע
                const colorBtn = this.container.querySelector('.simple-bg-type-btn[data-type="color"]') ||
                                document.querySelector('.simple-bg-type-btn[data-type="color"]');
                if (colorBtn) {
                    console.log(`🎨 Fallback to color type`);
                    colorBtn.click();
                }
            }
        }, 500); // זמן יותר ארוך כדי לוודא שהכל נטען
    }
}

// אתחול האנדלר כשהDOM מוכן
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.simpleBackgroundHandler = new SimpleBackgroundHandler();
    });
} else {
    window.simpleBackgroundHandler = new SimpleBackgroundHandler();
}

console.log('📁 simple-background-handler.js loaded'); 