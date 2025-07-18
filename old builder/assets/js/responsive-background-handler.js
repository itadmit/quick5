/**
 * Responsive Background Component Handler
 * טיפול בקומפוננט רקע responsive עם device switcher ואפשרויות מתקדמות
 */

class ResponsiveBackgroundHandler {
    constructor() {
        this.components = new Map();
        this.initialized = false;
        
        console.log('🎨 ResponsiveBackgroundHandler initialized');
        
        // התחל את האתחול
        this.init();
    }
    
    /**
     * אתחול הhandler
     */
    init() {
        if (this.initialized) return;
        
        // מאזין למאזין הטענת קומפוננטים חדשים
        this.startComponentWatcher();
        
        // טען קומפוננטים קיימים
        this.loadExistingComponents();
        
        this.initialized = true;
        console.log('✅ ResponsiveBackgroundHandler fully initialized');
    }
    
    /**
     * צופה קומפוננטים חדשים
     */
    startComponentWatcher() {
        // MutationObserver לזיהוי קומפוננטים חדשים
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        // בדוק אם זה קומפוננט רקע
                        if (node.classList && node.classList.contains('responsive-background-component')) {
                            this.initializeComponent(node);
                        }
                        
                        // בדוק קומפוננטים בתוך הnode
                        const components = node.querySelectorAll && node.querySelectorAll('.responsive-background-component');
                        if (components) {
                            components.forEach(comp => this.initializeComponent(comp));
                        }
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        console.log('👀 Component watcher started');
    }
    
    /**
     * טען קומפוננטים קיימים
     */
    loadExistingComponents() {
        const components = document.querySelectorAll('.responsive-background-component');
        console.log(`🔍 Found ${components.length} existing background components`);
        
        components.forEach(component => {
            this.initializeComponent(component);
        });
    }
    
    /**
     * אתחול קומפוננט בודד
     */
    initializeComponent(componentElement) {
        const componentId = componentElement.dataset.component;
        
        if (!componentId) {
            console.error('❌ Background component missing data-component attribute');
            return;
        }
        
        if (this.components.has(componentId)) {
            console.log(`🔄 Component ${componentId} already initialized`);
            return;
        }
        
        console.log(`🎨 Initializing background component: ${componentId}`);
        
        const component = new BackgroundComponent(componentElement, componentId);
        this.components.set(componentId, component);
        
        console.log(`✅ Background component ${componentId} initialized successfully`);
    }
}

/**
 * קלאס לקומפוננט בודד
 */
class BackgroundComponent {
    constructor(element, id) {
        this.element = element;
        this.id = id;
        this.basePath = element.dataset.basePath || 'styles';
        this.currentDevice = 'desktop';
        
        this.init();
    }
    
    /**
     * אתחול הקומפוננט
     */
    init() {
        this.setupDeviceSwitcher();
        this.setupBackgroundTypes();
        this.setupRangeSliders();
        this.setupColorInputs();
        this.setupImagePreviews();
        this.setupClearButtons();
        
        // הגדר סוג רקע צבע כברירת מחדל
        this.initializeDefaultState();
        
        console.log(`🎨 Component ${this.id} fully configured`);
    }
    
    /**
     * הגדרת device switcher
     */
    setupDeviceSwitcher() {
        const deviceBtns = this.element.querySelectorAll('.device-btn');
        
        deviceBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchDevice(btn.dataset.device);
            });
        });
        
        console.log(`📱 Device switcher setup complete (${deviceBtns.length} buttons)`);
    }
    
    /**
     * החלפת מצב מחשב/מובייל
     */
    switchDevice(device) {
        this.currentDevice = device;
        
        // עדכן כפתורים
        this.element.querySelectorAll('.device-btn').forEach(btn => {
            btn.classList.remove('active', 'bg-white', 'shadow-sm');
            btn.classList.add('hover:bg-white');
            
            if (btn.dataset.device === device) {
                btn.classList.add('active', 'bg-white', 'shadow-sm');
                btn.classList.remove('hover:bg-white');
            }
        });
        
        // הצג/הסתר הגדרות
        this.element.querySelectorAll('.device-settings').forEach(settings => {
            settings.style.display = settings.dataset.device === device ? 'block' : 'none';
        });
        
        console.log(`📱 Switched to device: ${device}`);
    }
    
    /**
     * הגדרת כפתורי סוג רקע
     */
    setupBackgroundTypes() {
        const bgTypeBtns = this.element.querySelectorAll('.bg-type-btn');
        
        bgTypeBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.selectBackgroundType(btn);
            });
        });
        
        console.log(`🎨 Background type setup complete (${bgTypeBtns.length} buttons)`);
    }
    
    /**
     * בחירת סוג רקע
     */
    selectBackgroundType(button) {
        const container = button.closest('.device-settings');
        const type = button.dataset.type;
        
        if (!container) {
            console.error('❌ Container not found for background type button');
            return;
        }
        
        // הסר active state מכל הכפתורים בcontainer הזה
        container.querySelectorAll('.bg-type-btn').forEach(btn => {
            btn.classList.remove('border-purple-500', 'bg-purple-50');
        });
        
        // הוסף active state לכפתור שנלחץ
        button.classList.add('border-purple-500', 'bg-purple-50');
        
        // הסתר כל הpanels
        container.querySelectorAll('.bg-setting').forEach(panel => {
            panel.style.display = 'none';
        });
        
        // הצג panel רלוונטי
        const targetPanel = container.querySelector(`.bg-setting[data-type="${type}"]`);
        if (targetPanel) {
            targetPanel.style.display = 'block';
            console.log(`👀 Showing panel for type: ${type}`);
        } else {
            console.error(`❌ Panel not found for type: ${type}`);
        }
        
        // עדכן input נסתר עבור סוג רקע
        this.updateBackgroundTypeInput(container, button.dataset.path, type);
        
        console.log(`🎨 Background type changed to: ${type}`);
    }
    
    /**
     * עדכון input נסתר לסוג רקע
     */
    updateBackgroundTypeInput(container, path, type) {
        if (!path) return;
        
        let hiddenInput = container.querySelector(`input[data-path="${path}"]`);
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.dataset.path = path;
            hiddenInput.dataset.responsive = 'true';
            container.appendChild(hiddenInput);
        }
        
        hiddenInput.value = type;
        
        // שלח event רגיל במקום לקרוא ישירות לsettings manager
        hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    
    /**
     * הגדרת range sliders
     */
    setupRangeSliders() {
        const rangeInputs = this.element.querySelectorAll('input[type="range"]');
        
        rangeInputs.forEach(rangeInput => {
            const updateValue = () => {
                const valueSpan = rangeInput.parentElement.querySelector('.overlay-value');
                if (valueSpan) {
                    valueSpan.textContent = rangeInput.value + '%';
                }
            };
            
            rangeInput.addEventListener('input', updateValue);
            // Event כבר נשלח אוטומטית על ידי הelement
            
            updateValue(); // אתחול
        });
        
        console.log(`📊 Range sliders setup complete (${rangeInputs.length} sliders)`);
    }
    
    /**
     * הגדרת color inputs
     */
    setupColorInputs() {
        const colorInputs = this.element.querySelectorAll('input[type="color"]');
        
        colorInputs.forEach(colorInput => {
            // Events נשלחים אוטומטית על ידי העצמאות
            // אין צורך בקריאה ישירה לsettings manager
        });
        
        console.log(`🎨 Color inputs setup complete (${colorInputs.length} inputs)`);
    }
    
    /**
     * הגדרת תצוגה מקדימה לתמונות
     */
    setupImagePreviews() {
        const imageInputs = this.element.querySelectorAll('input[data-path*="background-image"]');
        
        imageInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                const preview = input.closest('.bg-setting').querySelector('.image-preview');
                const img = preview ? preview.querySelector('img') : null;
                
                if (input.value && img) {
                    img.src = input.value;
                    preview.classList.remove('hidden');
                } else if (preview) {
                    preview.classList.add('hidden');
                }
                
                // Event נשלח אוטומטית על ידי הinput
            });
        });
        
        console.log(`🖼️ Image previews setup complete (${imageInputs.length} inputs)`);
    }
    
    /**
     * הגדרת כפתורי ניקוי
     */
    setupClearButtons() {
        const clearBtns = this.element.querySelectorAll('.clear-btn');
        
        clearBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                
                const targetSelector = btn.dataset.target;
                if (targetSelector) {
                    const targetInput = this.element.querySelector(targetSelector);
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
        // המתן קצת לוודא שהכל נטען
        setTimeout(() => {
            this.element.querySelectorAll('.device-settings').forEach(deviceSettings => {
                const colorBtn = deviceSettings.querySelector('.bg-type-btn[data-type="color"]');
                if (colorBtn) {
                    console.log(`🎨 Auto-clicking color type for device settings`);
                    colorBtn.click();
                }
            });
        }, 100);
    }
}

// אתחול האנדלר כשהDOM מוכן
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.responsiveBackgroundHandler = new ResponsiveBackgroundHandler();
    });
} else {
    // אם הDOM כבר נטען
    window.responsiveBackgroundHandler = new ResponsiveBackgroundHandler();
}

// גם וודא שזה רץ אחרי setTimeout לבטחון
setTimeout(() => {
    if (!window.responsiveBackgroundHandler) {
        console.log('🔄 Fallback initialization of ResponsiveBackgroundHandler');
        window.responsiveBackgroundHandler = new ResponsiveBackgroundHandler();
    }
}, 500);

console.log('📁 responsive-background-handler.js loaded'); 