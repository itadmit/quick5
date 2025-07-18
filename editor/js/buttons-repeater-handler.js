/**
 * Buttons Repeater Handler - מהיר ואופטימי ⚡
 * טיפול במערכת כפתורים - קריאת כפתורים קיימים ועריכה מלאה
 * 
 * אופטימיזציות ביצועים:
 * ⚡ שמירה מיידית (50ms במקום 500ms)
 * ⚡ הסרת confirm מחיקה (חוויה מהירה)
 * ⚡ רינדור חכם של כפתורים חדשים
 * ⚡ עדכון מיידי בתצוגה מקדימה
 */

class ButtonsRepeaterHandler {
    constructor() {
        this.components = [];
        this.init();
    }

    init() {
        console.log('🔘 Initializing Buttons Repeater Handler');
        
        // צפה לרכיבים חדשים
        this.watchForNewComponents();
        
        // אתחל רכיבים קיימים
        this.initializeExistingComponents();
        
        console.log('✅ ButtonsRepeaterHandler fully initialized');
    }

    /**
     * צפייה לרכיבים חדשים
     */
    watchForNewComponents() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        const buttonsComponents = node.querySelectorAll ? 
                                                 node.querySelectorAll('.buttons-repeater-component') : [];
                        
                        buttonsComponents.forEach(component => {
                            if (!component.dataset.initialized) {
                                this.initializeComponent(component);
                            }
                        });
                        
                        // אם הnode עצמו הוא buttons component
                        if (node.classList && node.classList.contains('buttons-repeater-component') && !node.dataset.initialized) {
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

        console.log('👀 Buttons component watcher started');
    }

    /**
     * אתחול רכיבים קיימים
     */
    initializeExistingComponents() {
        const existing = document.querySelectorAll('.buttons-repeater-component:not([data-initialized])');
        console.log(`🔍 Found ${existing.length} existing buttons components`);
        
        existing.forEach(component => {
            this.initializeComponent(component);
        });
    }

    /**
     * אתחול רכיב יחיד
     */
    initializeComponent(element) {
        if (element.dataset.initialized) return;
        
        const componentId = element.dataset.component || 'buttons_' + Date.now();
        console.log(`🔘 Initializing buttons component: ${componentId}`);
        
        element.dataset.initialized = 'true';
        
        const component = new ButtonsRepeaterComponent(element);
        this.components.push(component);
        
        console.log(`✅ Buttons component ${componentId} initialized successfully`);
    }
}

/**
 * קלאס לרכיב כפתורים יחיד
 */
class ButtonsRepeaterComponent {
    constructor(element) {
        this.element = element;
        this.componentId = element.dataset.component || 'buttons_' + Date.now();
        this.basePath = element.dataset.path || 'content.buttons';
        this.buttonsList = element.querySelector('.buttons-list');
        this.template = element.querySelector('template');
        this.buttonsData = [];
        this.init();
    }

    init() {
        console.log(`🔘 Initializing buttons component: ${this.componentId}`);
        
        this.setupAddButton();
        this.connectToSettingsManager();
        this.loadExistingButtons();
        
        console.log(`🔘 Component ${this.componentId} fully configured`);
    }

    /**
     * הגדרת כפתור הוספה
     */
    setupAddButton() {
        const addBtn = this.element.querySelector('.add-button-btn');
        if (!addBtn) return;

        addBtn.addEventListener('click', (e) => {
            e.preventDefault();
            this.addNewButton();
        });

        console.log('🔘 Add button setup complete');
    }

    /**
     * טעינת כפתורים קיימים
     */
    loadExistingButtons() {
        // חכה לsettings-manager להיות זמין
        setTimeout(() => {
            if (window.builderMain?.components?.settingsManager) {
                const section = window.builderCore?.getSection(window.builderMain.components.settingsManager.currentSectionId);
                if (section) {
                    const buttons = this.getNestedValue(section, this.basePath, []);
                    console.log(`🔘 Loading ${buttons.length} existing buttons:`, buttons);
                    
                    if (Array.isArray(buttons) && buttons.length > 0) {
                        this.buttonsData = buttons;
                        this.renderAllButtons();
                    } else {
                        console.log('🔘 No existing buttons found');
                    }
                }
            }
        }, 200);
    }

    /**
     * קבלת ערך מתוך object עמוק
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

    /**
     * רינדור כל הכפתורים
     */
    renderAllButtons() {
        this.buttonsList.innerHTML = '';
        
        this.buttonsData.forEach((button, index) => {
            this.renderButton(button, index);
        });

        console.log(`🔘 Rendered ${this.buttonsData.length} buttons`);
    }

    /**
     * רינדור כפתור יחיד
     */
    renderButton(buttonData, index) {
        if (!this.template) {
            console.error('❌ Button template not found');
            return;
        }

        // שכפל את הtemplate
        const clone = this.template.content.cloneNode(true);
        const buttonElement = clone.querySelector('.button-item');
        
        if (!buttonElement) {
            console.error('❌ Button item not found in template');
            return;
        }

        // עדכן index ו-placeholders
        buttonElement.dataset.buttonIndex = index;
        
        // החלף placeholders
        buttonElement.innerHTML = buttonElement.innerHTML.replace(/{{INDEX}}/g, index + 1);
        
        // מלא את הנתונים
        this.populateButtonData(buttonElement, buttonData, index);
        
        // הגדר event listeners
        this.setupButtonEvents(buttonElement, index);
        
        // הוסף לרשימה
        this.buttonsList.appendChild(buttonElement);
    }

    /**
     * מילוי נתוני כפתור
     */
    populateButtonData(buttonElement, buttonData, index) {
        // טקסט
        const textInput = buttonElement.querySelector('.button-text-input');
        if (textInput && buttonData.text) {
            textInput.value = buttonData.text;
        }
        
        // URL
        const urlInput = buttonElement.querySelector('.button-url-input');
        if (urlInput && buttonData.url) {
            urlInput.value = buttonData.url;
        }
        
        // אייקון
        const iconInput = buttonElement.querySelector('.button-icon-input');
        if (iconInput) {
            iconInput.value = buttonData.icon || '';
            
            // עדכון התצוגה של בחירת האייקון
            const display = buttonElement.querySelector('.selected-icon-display');
            if (display) {
                if (buttonData.icon) {
                    const iconPrefix = this.getIconPrefix(buttonData.icon);
                    display.innerHTML = `<i class="${iconPrefix}${buttonData.icon} mr-2"></i>${buttonData.icon}`;
                    display.classList.remove('text-gray-500');
                    display.classList.add('text-gray-900');
                } else {
                    display.textContent = 'בחר אייקון';
                    display.classList.remove('text-gray-900');
                    display.classList.add('text-gray-500');
                }
            }
        }
        
        // סוג כפתור - תמיכה בפורמטים שונים
        const typeSelect = buttonElement.querySelector('.button-type-select');
        console.log(`🔘 Button ${index + 1} type select element:`, typeSelect);
        console.log(`🔘 Button ${index + 1} original style:`, buttonData.style);
        
        if (typeSelect && buttonData.style) {
            // המרה מפורמט ישן לחדש
            let styleValue = buttonData.style;
            if (styleValue === 'primary') styleValue = 'solid';
            if (styleValue === 'secondary') styleValue = 'outline';
            
            typeSelect.value = styleValue;
            console.log(`🔘 Set button type select to: ${styleValue} (original: ${buttonData.style})`);
            
            // בדיקת האפשרויות הזמינות בselect
            const options = Array.from(typeSelect.options).map(opt => opt.value);
            console.log(`🔘 Available options in select:`, options);
        } else {
            console.warn(`🔘 Button ${index + 1} - typeSelect not found or no style in data`);
        }
        
        // צבע רקע
        const bgColorInput = buttonElement.querySelector('.button-bg-color');
        if (bgColorInput && buttonData.styles && buttonData.styles['background-color']) {
            bgColorInput.value = buttonData.styles['background-color'];
        }
        
        // צבע טקסט
        const textColorInput = buttonElement.querySelector('.button-text-color');
        if (textColorInput && buttonData.styles && buttonData.styles.color) {
            textColorInput.value = buttonData.styles.color;
        }
        
        // עגול פינות
        const borderRadiusSelect = buttonElement.querySelector('.button-border-radius');
        if (borderRadiusSelect && buttonData.styles && buttonData.styles['border-radius']) {
            borderRadiusSelect.value = buttonData.styles['border-radius'];
        }

        console.log(`🔘 Populated button ${index + 1} data:`, buttonData);
        console.log(`🔘 Button ${index + 1} style mapping: ${buttonData.style} -> ${typeSelect?.value || 'not found'}`);
    }

    /**
     * הגדרת events לכפתור
     */
    setupButtonEvents(buttonElement, index) {
        // עריכת שדות
        const inputs = buttonElement.querySelectorAll('input, select');
        console.log(`🔘 Setting up events for button ${index + 1}, found ${inputs.length} inputs`);
        
        inputs.forEach(input => {
            const eventType = input.type === 'color' ? 'input' : 'change';
            console.log(`🔘 Binding ${eventType} event to ${input.dataset.field} for button ${index + 1}`);
            
            input.addEventListener(eventType, () => {
                console.log(`🔘 Event triggered on button ${index + 1}, field: ${input.dataset.field}, value: ${input.value}`);
                this.updateButtonData(index, input);
            });
        });

        // כפתורי פעולה
        const removeBtn = buttonElement.querySelector('.remove-button-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => this.removeButton(index));
        }

        const moveUpBtn = buttonElement.querySelector('.move-up-btn');
        if (moveUpBtn) {
            moveUpBtn.addEventListener('click', () => this.moveButton(index, -1));
        }

        const moveDownBtn = buttonElement.querySelector('.move-down-btn');
        if (moveDownBtn) {
            moveDownBtn.addEventListener('click', () => this.moveButton(index, 1));
        }
        
        // כפתור בחירת אייקון
        const iconPickerBtn = buttonElement.querySelector('.icon-picker-btn');
        if (iconPickerBtn) {
            iconPickerBtn.addEventListener('click', () => this.openIconPicker(buttonElement, index));
        }
    }

    /**
     * עדכון נתוני כפתור
     */
    updateButtonData(index, input) {
        const field = input.dataset.field;
        const value = input.value;
        
        if (!this.buttonsData[index]) {
            this.buttonsData[index] = { text: '', url: '', styles: {} };
        }
        
        if (field.includes('.')) {
            // nested field כמו styles.background-color
            const [parent, child] = field.split('.');
            if (!this.buttonsData[index][parent]) {
                this.buttonsData[index][parent] = {};
            }
            this.buttonsData[index][parent][child] = value;
        } else {
            // simple field כמו text, url
            this.buttonsData[index][field] = value;
        }
        
        console.log(`🔘 Updated button ${index + 1} field ${field}:`, value);
        console.log(`🔘 Current buttons data:`, this.buttonsData);
        
        // עדכון מיידי של הכפתור ב-DOM בלבד - ללא רינדור מלא
        this.updateButtonInPreview(index);
        
        // שמירה מהירה
        console.log(`🔘 Saving immediately...`);
        this.saveToSection();
    }
    
    /**
     * עדכון מיידי ומהיר של כפתור בתצוגה מקדימה
     */
    updateButtonInPreview(index) {
        try {
            const iframe = document.getElementById('previewFrame');
            if (iframe && iframe.contentWindow) {
                const buttonData = this.buttonsData[index];
                if (buttonData) {
                    // שליחה מיידית ללא עיכוב
                    iframe.contentWindow.postMessage({
                        type: 'updateButton',
                        buttonIndex: index,
                        buttonData: buttonData
                    }, '*');
                    console.log(`🔘 ⚡ Instant button update sent to preview:`, buttonData);
                }
            }
        } catch (error) {
            console.warn('⚠️ Could not update button in preview:', error);
        }
    }

    /**
     * הוספת כפתור חדש (אופטימיזציה לביצועים)
     */
    addNewButton() {
        if (this.buttonsData.length >= 5) {
            alert('ניתן להוסיף מקסימום 5 כפתורים');
            return;
        }

        const newButton = {
            text: 'כפתור חדש',
            url: '#',
            icon: '',
            style: 'solid',
            styles: {
                'background-color': '#3b82f6',
                'color': '#ffffff',
                'border-radius': '6px'
            }
        };

        // הוסף לנתונים
        this.buttonsData.push(newButton);
        
        // רינדור מיידי רק של הכפתור החדש (לא כל הכפתורים!)
        const newIndex = this.buttonsData.length - 1;
        this.renderButton(newButton, newIndex);
        
        // שמירה מיידית
        this.saveToSection();

        console.log('🔘 Added new button instantly:', newButton);
    }

    /**
     * הסרת כפתור (מהיר ללא confirm)
     */
    removeButton(index) {
        // מחיקה מיידית ללא confirm לחוויה מהירה
        this.buttonsData.splice(index, 1);
        
        // רינדור מהיר של כל הכפתורים (נדרש לעדכון אינדקסים)
        this.renderAllButtons();
        
        // שמירה מיידית
        this.saveToSection();
        
        console.log(`🔘 Removed button ${index + 1} instantly`);
    }

    /**
     * הזזת כפתור (אופטימיזציה לביצועים)
     */
    moveButton(index, direction) {
        const newIndex = index + direction;
        
        if (newIndex < 0 || newIndex >= this.buttonsData.length) {
            return; // לא ניתן להזיז
        }
        
        // החלף מקומות
        [this.buttonsData[index], this.buttonsData[newIndex]] = [this.buttonsData[newIndex], this.buttonsData[index]];
        
        // רינדור מהיר
        this.renderAllButtons();
        
        // שמירה מיידית
        this.saveToSection();
        
        console.log(`🔘 Moved button from ${index + 1} to ${newIndex + 1} instantly`);
    }

    /**
     * שמירה מיידית לsection (הסרת delay מיותר)
     */
    saveToSection() {
        // ביטול timeout קיים אם יש
        if (this.saveTimeout) {
            clearTimeout(this.saveTimeout);
        }
        
        // שמירה מיידית - רק דחיית 50ms להתמצות עדכונים מהירים
        this.saveTimeout = setTimeout(() => {
            if (window.builderMain?.components?.settingsManager) {
                const settingsManager = window.builderMain.components.settingsManager;
                
                console.log('🔘 Saving buttons to section (immediate):', this.basePath, this.buttonsData);
                
                // שלח עדכון ישירות לsettings-manager
                settingsManager.updateSetting(this.basePath, this.buttonsData);
                
                console.log(`🔘 Saved ${this.buttonsData.length} buttons to section:`, this.buttonsData);
            } else {
                console.warn('⚠️ Settings-manager not available for buttons save');
            }
        }, 50); // רק 50ms במקום 500ms!
    }

    /**
     * הגדרת ערך בobject עמוק
     */
    setNestedValue(obj, path, value) {
        const keys = path.split('.');
        let current = obj;
        
        for (let i = 0; i < keys.length - 1; i++) {
            const key = keys[i];
            if (!(key in current) || typeof current[key] !== 'object') {
                current[key] = {};
            }
            current = current[key];
        }
        
        current[keys[keys.length - 1]] = value;
    }

    /**
     * חיבור לsettings-manager
     */
    connectToSettingsManager() {
        // הדרך הזו מוודאת שנקבל עדכונים כששדות אחרים משתנים
        setTimeout(() => {
            if (window.builderMain?.components?.settingsManager) {
                console.log('🔗 Connected buttons repeater to settings-manager');
            } else {
                console.warn('⚠️ Settings-manager not found');
            }
        }, 100);
    }
    
    /**
     * פתיחת מודל בחירת אייקון
     */
    openIconPicker(buttonElement, index) {
        console.log('🎯 Opening icon picker for button', index + 1);
        
        this.currentButtonElement = buttonElement;
        this.currentButtonIndex = index;
        
        const modal = document.getElementById('iconPickerModal');
        console.log('🔍 Modal element:', modal);
        console.log('🔍 Modal exists?', !!modal);
        
        if (modal) {
            console.log('✅ Modal found, showing it');
            modal.classList.remove('hidden');
            
            // הגדרת event listeners למודל אם עוד לא הוגדרו
            if (!modal.dataset.eventsSet) {
                console.log('🔧 Setting up modal events');
                this.setupIconPickerEvents(modal);
                modal.dataset.eventsSet = 'true';
            }
        } else {
            console.error('❌ Modal not found! Creating it now...');
            this.createModal();
            
            // עכשיו נסה שוב לפתוח
            setTimeout(() => {
                const newModal = document.getElementById('iconPickerModal');
                if (newModal) {
                    console.log('✅ Modal created and ready, opening it');
                    // הגדרת האירועים למודל החדש
                    this.setupIconPickerEvents(newModal);
                    newModal.dataset.eventsSet = 'true';
                    // פתיחת המודל
                    newModal.classList.remove('hidden');
                } else {
                    console.error('❌ Failed to create modal');
                }
            }, 100);
        }
    }
    
    /**
     * הגדרת events למודל בחירת אייקונים
     */
    setupIconPickerEvents(modal) {
        // כפתור סגירה
        const closeBtn = modal.querySelector('.close-modal');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.closeIconPicker());
        }
        
        // לחיצה על רקע המודל לסגירה
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.closeIconPicker();
            }
        });
        
        // כפתורי בחירת אייקון
        const iconOptions = modal.querySelectorAll('.icon-option');
        iconOptions.forEach(option => {
            option.addEventListener('click', () => {
                const selectedIcon = option.dataset.icon;
                this.selectIcon(selectedIcon);
            });
        });
        
        // ESC key לסגירת המודל
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                this.closeIconPicker();
            }
        });
    }
    
    /**
     * בחירת אייקון
     */
    selectIcon(iconValue) {
        console.log('🎯 Selected icon:', iconValue || 'no icon');
        
        if (this.currentButtonElement) {
            // עדכון האינפוט הנסתר
            const hiddenInput = this.currentButtonElement.querySelector('.button-icon-input');
            if (hiddenInput) {
                hiddenInput.value = iconValue;
                
                // עדכון הנתונים
                this.updateButtonData(this.currentButtonIndex, hiddenInput);
            }
            
            // עדכון התצוגה של הכפתור
            const display = this.currentButtonElement.querySelector('.selected-icon-display');
            if (display) {
                if (iconValue) {
                    // הצגת האייקון הנבחר
                    const iconPrefix = this.getIconPrefix(iconValue);
                    display.innerHTML = `<i class="${iconPrefix}${iconValue} mr-2"></i>${iconValue}`;
                    display.classList.remove('text-gray-500');
                    display.classList.add('text-gray-900');
                } else {
                    // אין אייקון
                    display.textContent = 'בחר אייקון';
                    display.classList.remove('text-gray-900');
                    display.classList.add('text-gray-500');
                }
            }
        }
        
        this.closeIconPicker();
    }
    
    /**
     * סגירת מודל בחירת אייקונים
     */
    closeIconPicker() {
        const modal = document.getElementById('iconPickerModal');
        if (modal) {
            modal.classList.add('hidden');
        }
        
        this.currentButtonElement = null;
        this.currentButtonIndex = null;
    }
    
    /**
     * קבלת prefix עבור אייקון
     */
    getIconPrefix(icon) {
        if (!icon) return '';
        
        if (icon.startsWith('fa-') || icon.startsWith('fas ') || icon.startsWith('fab ')) {
            return ''; // Font Awesome כבר יש לו prefix
        }
        
        return 'ri-'; // Remix Icons
    }
    
    /**
     * יצירת מודל בחירת אייקונים דינמית
     */
    createModal() {
        console.log('🔧 Creating icon picker modal dynamically');
        
        // וודא שהמודל לא קיים כבר
        if (document.getElementById('iconPickerModal')) {
            console.log('⚠️ Modal already exists, skipping creation');
            return;
        }
        
        const modalHTML = `
            <div id="iconPickerModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-lg max-w-2xl w-full max-h-96 overflow-hidden">
                        <div class="flex items-center justify-between p-4 border-b">
                            <h3 class="text-lg font-semibold">בחר אייקון</h3>
                            <button type="button" class="close-modal text-gray-400 hover:text-gray-600">
                                <i class="ri-close-line text-xl"></i>
                            </button>
                        </div>
                        <div class="p-4 overflow-y-auto max-h-80">
                            <div class="grid grid-cols-8 gap-3 mb-4">
                                <button type="button" class="icon-option p-3 border border-gray-300 rounded hover:bg-blue-50 hover:border-blue-300 transition-colors text-center" data-icon="">
                                    <div class="text-2xl text-gray-400 mb-1">×</div>
                                    <div class="text-xs text-gray-600">אין אייקון</div>
                                </button>
                                ${this.generateIconButtons()}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // הוסף את המודל לbody
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        console.log('✅ Modal HTML added to body');
    }
    
    /**
     * יצירת כפתורי האייקונים
     */
    generateIconButtons() {
        const popularIcons = [
            'arrow-right-line:חץ ימין',
            'arrow-left-line:חץ שמאל', 
            'arrow-up-line:חץ למעלה',
            'arrow-down-line:חץ למטה',
            'home-line:בית',
            'user-line:משתמש',
            'mail-line:מייל',
            'phone-line:טלפון',
            'shopping-cart-line:עגלת קניות',
            'heart-line:לב',
            'star-line:כוכב',
            'download-line:הורדה',
            'upload-line:העלאה',
            'share-line:שיתוף',
            'search-line:חיפוש',
            'settings-line:הגדרות',
            'edit-line:עריכה',
            'delete-line:מחיקה',
            'add-line:הוספה',
            'check-line:אישור',
            'close-line:סגירה',
            'menu-line:תפריט',
            'eye-line:עין',
            'lock-line:נעילה',
            'unlock-line:פתיחה',
            'calendar-line:לוח שנה',
            'time-line:שעון',
            'map-pin-line:מיקום',
            'camera-line:מצלמה',
            'image-line:תמונה',
            'video-line:וידאו',
            'music-line:מוזיקה',
            'file-line:קובץ',
            'folder-line:תיקייה',
            'external-link-line:קישור חיצוני',
            'information-line:מידע',
            'error-warning-line:אזהרה',
            'question-line:שאלה',
            'thumb-up-line:לייק',
            'thumb-down-line:דיסלייק',
            'facebook-line:פייסבוק',
            'instagram-line:אינסטגרם',
            'twitter-line:טוויטר',
            'linkedin-line:לינקדאין',
            'whatsapp-line:ווטסאפ',
            'telegram-line:טלגרם',
            'youtube-line:יוטיוב'
        ];
        
        return popularIcons.map(iconData => {
            const [icon, name] = iconData.split(':');
            return `
                <button type="button" class="icon-option p-3 border border-gray-300 rounded hover:bg-blue-50 hover:border-blue-300 transition-colors text-center" data-icon="${icon}">
                    <i class="ri-${icon} text-2xl text-gray-600 block mb-1"></i>
                    <div class="text-xs text-gray-600">${name}</div>
                </button>
            `;
        }).join('');
    }
}

// אתחול כשהDOM מוכן
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.buttonsRepeaterHandler = new ButtonsRepeaterHandler();
    });
} else {
    window.buttonsRepeaterHandler = new ButtonsRepeaterHandler();
}

console.log('📁 buttons-repeater-handler.js loaded'); 