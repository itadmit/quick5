/**
 * Settings Manager - מנהל הגדרות כללי
 * טוען דינמית הגדרות לכל סקשן מהתיקיות המתאימות + תמיכה responsive
 */

class SettingsManager {
    constructor() {
        this.elements = {
            settingsPanel: document.getElementById('settingsPanel'),
            settingsContent: document.getElementById('settingsContent'),
            closeSettings: document.getElementById('closeSettings')
        };
        
        this.currentSectionId = null;
        this.currentSectionType = null;
        this.settingsCache = new Map(); // קאש להגדרות שנטענו
        
        this.init();
    }
    
    /**
     * אתחול מנהל ההגדרות
     */
    init() {
        this.bindEvents();
        console.log('⚙️ Settings Manager initialized');
    }
    
    /**
     * קישור אירועים
     */
    bindEvents() {
        // סגירת הגדרות
        this.elements.closeSettings?.addEventListener('click', () => {
            this.closeSettings();
        });
        
        // סגירה עם ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isSettingsOpen()) {
                this.closeSettings();
            }
        });
    }
    
    /**
     * הצגת הגדרות לסקשן
     */
    async showSectionSettings(sectionId) {
        const section = window.builderCore.getSection(sectionId);
        if (!section) {
            console.error('❌ Section not found:', sectionId);
            return;
        }
        
        this.currentSectionId = sectionId;
        this.currentSectionType = section.type;
        
        try {
            // טעינת הגדרות הסקשן
            const settingsHTML = await this.loadSectionSettings(section.type);
            
            // הצגת ההגדרות
            this.elements.settingsContent.innerHTML = settingsHTML;
            this.elements.settingsPanel.style.display = 'block';
            
            // המתנה קצרה לאתחול הDOM
            setTimeout(() => {
                // מילוי ערכים נוכחיים
                this.populateCurrentValues(section);
                
                // קישור אירועי עדכון
                this.bindSettingsEvents(section);
                
                console.log('⚙️ Settings shown for:', section.type, sectionId, section);
            }, 100);
            
        } catch (error) {
            console.error('❌ Failed to load settings:', error);
            this.showError('שגיאה בטעינת ההגדרות');
        }
    }
    
    /**
     * טעינת הגדרות מהשרת
     */
    async loadSectionSettings(sectionType) {
        // בדיקה בקאש
        if (this.settingsCache.has(sectionType)) {
            return this.settingsCache.get(sectionType);
        }
        
        try {
            const response = await fetch(`sections/${sectionType}/settings.php`);
            
            if (!response.ok) {
                throw new Error(`Failed to load settings for ${sectionType}: ${response.status}`);
            }
            
            const settingsHTML = await response.text();
            
            // שמירה בקאש
            this.settingsCache.set(sectionType, settingsHTML);
            
            return settingsHTML;
            
        } catch (error) {
            console.error('❌ Error loading section settings:', error);
            
            // fallback להגדרות בסיסיות
            return this.getBasicSettings(sectionType);
        }
    }
    
    /**
     * מילוי ערכים נוכחיים בהגדרות - תמיכה RESPONSIVE
     */
    populateCurrentValues(section) {
        console.log('🔧 Populating values for section:', section);
        
        // מילוי שדות טקסט כלליים
        this.populateTextInputs(section);
        
        // מילוי בוחרי צבעים
        this.populateColorInputs(section);
        
        // מילוי בוחרי רשימה
        this.populateSelectInputs(section);
        
        // מילוי checkbox/radio
        this.populateCheckboxInputs(section);
        
        // מילוי רכיבים responsive
        this.populateResponsiveComponents(section);
        
        // מילוי שדות משותפים (צבעים זהים במובייל ומחשב)
        this.populateSharedComponents(section);
        
        // מילוי רכיבים פשוטים (simple background)
        this.populateSimpleComponents(section);
    }
    
    /**
     * מילוי רכיבי responsive
     */
    populateResponsiveComponents(section) {
        // מילוי כל הinputs עם data-responsive="true"
        const responsiveInputs = this.elements.settingsContent.querySelectorAll('[data-responsive="true"]');
        
        responsiveInputs.forEach(input => {
            const path = input.dataset.path;
            if (path) {
                const value = this.getValueByPath(section, path);
                console.log('🔧 Populating responsive input:', path, '=', value);
                
                if (value !== undefined && value !== null) {
                    if (input.type === 'checkbox') {
                        input.checked = Boolean(value);
                    } else if (input.type === 'range') {
                        input.value = value;
                        // עדכון תצוגת הערך עבור sliders
                        const valueSpan = input.parentElement.querySelector('.overlay-value');
                        if (valueSpan) {
                            valueSpan.textContent = value + '%';
                        }
                    } else {
                        input.value = value;
                        
                        // אם זה שדה תמונה - הראה תצוגה מקדימה
                        if (value && input.dataset.path && input.dataset.path.includes('background-image')) {
                            const preview = input.closest('.bg-setting')?.querySelector('.image-preview');
                            const img = preview?.querySelector('img');
                            if (img && preview) {
                                img.src = value;
                                preview.classList.remove('hidden');
                            }
                        }
                    }
                    
                    // אם זה אלמנט alignment - הדגש את הכפתור הנכון
                    if (input.dataset.align) {
                        const container = input.closest('.device-settings');
                        if (container) {
                            container.querySelectorAll('.align-btn').forEach(btn => {
                                btn.classList.remove('bg-blue-100');
                            });
                            
                            if (input.value === input.dataset.align) {
                                input.classList.add('bg-blue-100');
                            }
                        }
                    }
                }
            }
        });
        
        // עדכון כפתורי background type
        this.updateBackgroundTypeButtons(section);
        
        // עדכון device switchers
        this.updateDeviceSwitchers();
    }
    
    /**
     * עדכון כפתורי סוג רקע
     */
    updateBackgroundTypeButtons(section) {
        const bgTypeButtons = this.elements.settingsContent.querySelectorAll('.bg-type-btn');
        
        bgTypeButtons.forEach(btn => {
            const path = btn.dataset.path;
            if (path) {
                const value = this.getValueByPath(section, path);
                const btnType = btn.dataset.type;
                
                if (value === btnType || (!value && btnType === 'color')) {
                    btn.classList.add('border-purple-500', 'bg-purple-50');
                    
                    // הצג את הpanel המתאים
                    const container = btn.closest('.device-settings');
                    if (container) {
                        container.querySelectorAll('.bg-setting').forEach(panel => {
                            panel.style.display = 'none';
                        });
                        
                        const targetPanel = container.querySelector(`.bg-setting[data-type="${btnType}"]`);
                        if (targetPanel) {
                            targetPanel.style.display = 'block';
                        }
                    }
                }
            }
        });
    }
    
    /**
     * עדכון device switchers
     */
    updateDeviceSwitchers() {
        // וודא שמצב desktop פעיל כברירת מחדל
        const deviceSwitchers = this.elements.settingsContent.querySelectorAll('.device-switcher');
        
        deviceSwitchers.forEach(switcher => {
            const desktopBtn = switcher.querySelector('[data-device="desktop"]');
            const mobileBtn = switcher.querySelector('[data-device="mobile"]');
            
            if (desktopBtn && !desktopBtn.classList.contains('active')) {
                desktopBtn.click();
            }
        });
    }
    
    /**
     * מילוי שדות טקסט
     */
    populateTextInputs(section) {
        const textInputs = this.elements.settingsContent.querySelectorAll('input[type="text"], input[type="url"], textarea');
        
        textInputs.forEach(input => {
            const path = input.dataset.path;
            if (path && !input.dataset.responsive) { // רק שדות לא-responsive
                const value = this.getValueByPath(section, path);
                if (value !== undefined) {
                    input.value = value;
                    console.log('🔧 Populated text input:', path, '=', value);
                }
            }
        });
    }
    
    /**
     * מילוי שדות משותפים (צבעים זהים במובייל ומחשב)
     */
    populateSharedComponents(section) {
        const sharedInputs = this.elements.settingsContent.querySelectorAll('input[data-shared="true"], select[data-shared="true"], textarea[data-shared="true"]');
        console.log(`🔧 Found ${sharedInputs.length} shared component inputs`);
        
        sharedInputs.forEach(input => {
            const path = input.dataset.path;
            if (path) {
                // קח את הערך מהמחשב (ערך מרכזי)
                const desktopPath = path.replace('.color', '.desktop.color');
                const value = this.getValueByPath(section, desktopPath);
                console.log(`🔧 Populating shared input: ${path} = ${value} (from ${desktopPath})`);
                
                if (value !== undefined) {
                    if (input.type === 'checkbox') {
                        input.checked = !!value;
                    } else {
                        input.value = value;
                    }
                }
            }
        });
    }

    /**
     * מילוי רכיבים פשוטים (simple background ו-inputs אחרים ללא data-responsive)
     */
    populateSimpleComponents(section) {
        // מילוי כל הinputs עם data-path אבל ללא data-responsive ו-ללא data-shared
        const simpleInputs = this.elements.settingsContent.querySelectorAll('[data-path]:not([data-responsive]):not([data-shared])');
        
        console.log(`🔧 Found ${simpleInputs.length} simple component inputs`);
        
        simpleInputs.forEach(input => {
            const path = input.dataset.path;
            if (path) {
                const value = this.getValueByPath(section, path);
                console.log('🔧 Populating simple input:', path, '=', value);
                
                if (input.type === 'checkbox') {
                    // עבור checkboxes - תמיד עדכן, false/undefined/null = לא מסומן
                    input.checked = Boolean(value);
                    console.log('🔧 Populated checkbox:', path, '=', value, '-> checked:', input.checked);
                } else if (value !== undefined && value !== null) {
                    if (input.type === 'range') {
                        input.value = value;
                        // עדכון תצוגת הערך עבור sliders
                        const valueSpan = input.parentElement.querySelector('.overlay-value');
                        if (valueSpan) {
                            valueSpan.textContent = value + '%';
                        }
                    } else {
                        input.value = value;
                    }
                }
            }
        });
    }
    
    /**
     * מילוי בוחרי צבעים
     */
    populateColorInputs(section) {
        const colorInputs = this.elements.settingsContent.querySelectorAll('input[type="color"]');
        
        colorInputs.forEach(input => {
            const path = input.dataset.path;
            if (path && !input.dataset.responsive) { // רק שדות לא-responsive
                const value = this.getValueByPath(section, path);
                if (value) {
                    input.value = value;
                    console.log('🔧 Populated color input:', path, '=', value);
                }
            }
        });
    }
    
    /**
     * מילוי בוחרי רשימה
     */
    populateSelectInputs(section) {
        const selects = this.elements.settingsContent.querySelectorAll('select');
        
        selects.forEach(select => {
            const path = select.dataset.path;
            if (path && !select.dataset.responsive) { // רק שדות לא-responsive
                const value = this.getValueByPath(section, path);
                if (value !== undefined) {
                    select.value = value;
                    console.log('🔧 Populated select:', path, '=', value);
                }
            }
        });
    }
    
    /**
     * מילוי checkbox/radio
     */
    populateCheckboxInputs(section) {
        const checkboxes = this.elements.settingsContent.querySelectorAll('input[type="checkbox"], input[type="radio"]');
        
        checkboxes.forEach(input => {
            const path = input.dataset.path;
            if (path && !input.dataset.responsive) { // רק שדות לא-responsive
                const value = this.getValueByPath(section, path);
                input.checked = value === input.value || value === true;
                console.log('🔧 Populated checkbox:', path, '=', value);
            }
        });
    }
    
    /**
     * קישור אירועי עדכון
     */
    bindSettingsEvents(section) {
        // הסרת event listeners קיימים למניעת כפילות
        const inputs = this.elements.settingsContent.querySelectorAll('input, select, textarea');
        
        console.log(`🔧 Binding events to ${inputs.length} total inputs`);
        
        inputs.forEach(input => {
            // הסרת listeners קיימים
            if (input._settingsEventsBound) {
                console.log(`🔧 Input already bound: ${input.dataset.path}`);
                return;
            }
            
            // רק inputs עם data-path
            if (!input.dataset.path) {
                console.log(`🔧 Skipping input without data-path:`, input);
                return;
            }
            
            // עבור צבעים ו-range - האזנה רק ל-input עבור עדכון בזמן אמת
            let eventType;
            if (input.type === 'color' || input.type === 'range') {
                eventType = 'input';
            } else if (input.type === 'text' || input.type === 'textarea') {
                eventType = 'input';
            } else {
                eventType = 'change';
            }
            
            console.log(`🔧 Binding ${eventType} event to: ${input.dataset.path}`);
            
            input.addEventListener(eventType, (e) => {
                this.handleSettingChange(e.target, section);
            });
            
            // סימון שהevent listeners הוגדרו
            input._settingsEventsBound = true;
        });
        
        console.log('🔧 Bound events to', inputs.length, 'inputs');
    }
    
    /**
     * טיפול בשינוי הגדרה - תמיכה RESPONSIVE
     */
    handleSettingChange(input, section) {
        const path = input.dataset.path;
        if (!path) return;
        
        let value;
        
        // קבלת הערך לפי סוג השדה
        switch(input.type) {
            case 'checkbox':
                value = input.checked;
                console.log('🔧 Checkbox change detected:', path, '-> checked:', input.checked, 'value:', value);
                console.log('🔧 Checkbox element:', input);
                console.log('🔧 Checkbox current section before comparison:', section);
                break;
            case 'radio':
                value = input.checked ? input.value : undefined;
                break;
            case 'number':
                value = parseFloat(input.value) || 0;
                break;
            default:
                value = input.value;
        }
        
        if (value !== undefined) {
            // בדיקה אם הערך באמת השתנה
            const currentValue = this.getValueByPath(section, path);
            const isTextField = input.type === 'text' || input.type === 'textarea';
            const isCheckboxField = input.type === 'checkbox';
            const isSharedField = input.dataset.shared === 'true';
            
            // התמודדות מיוחדת עם checkboxes
            let valuesAreEqual = false;
            if (isCheckboxField) {
                // עבור checkboxes - השווה בדיוק את הערכים הבוליאניים
                // undefined/null נחשבים כ-false, אבל true חייב להיות true במפורש
                const normalizedCurrent = Boolean(currentValue);
                const normalizedNew = Boolean(value);
                valuesAreEqual = normalizedCurrent === normalizedNew;
                
                console.log(`🔧 Checkbox comparison: current=${currentValue} (${normalizedCurrent}) vs new=${value} (${normalizedNew}) => equal=${valuesAreEqual}`);
            } else {
                valuesAreEqual = currentValue === value;
            }
            
            // עבור שדות טקסט - תמיד עדכן (למקרה ששדה נמחק וחוזר)
            // עבור שדות משותפים - תמיד עדכן
            // עבור checkboxes - תמיד עדכן (כדי לטפל בבעיות בזמן מציאת הערך הנוכחי)
            // עבור שדות אחרים - רק אם הערך השתנה
            if (!isTextField && !isSharedField && !isCheckboxField && valuesAreEqual) {
                console.log('🔧 Value unchanged, skipping update:', path, '=', value, '(current:', currentValue, ')');
                return;
            }
            
            // הודעה מיוחדת עבור checkboxes
            if (isCheckboxField) {
                console.log('🔧 Checkbox update (always allowed):', path, '=', value, '(current:', currentValue, ')');
            }
            
            // עבור שדות משותפים - עדכן גם במחשב וגם במובייל
            if (isSharedField) {
                const updates = this.createSharedUpdateObject(path, value);
                console.log('🔧 Shared setting change:', path, '=', value, 'updates:', updates);
                window.builderCore.updateSection(section.id, updates);
            } else {
                // עדכון רגיל
                const updates = this.createUpdateObject(path, value);
                console.log('🔧 Setting change:', path, '=', value, 'updates:', updates);
                window.builderCore.updateSection(section.id, updates);
            }
        }
    }
    
    /**
     * עדכון תצוגה מקדימה מיידי
     */
    updatePreviewImmediately() {
        if (window.builderCore && window.builderCore.updatePreview) {
            window.builderCore.updatePreview();
        }
    }
    
    /**
     * עדכון הגדרה ישירות (לשימוש קומפוננטים)
     */
    updateSetting(path, value) {
        if (!this.currentSectionId || !path) return;
        
        const section = window.builderCore.getSection(this.currentSectionId);
        if (!section) return;
        
        // עדכון הסקשן
        const updates = this.createUpdateObject(path, value);
        
        console.log('🔧 Direct setting update:', path, '=', value, 'updates:', updates);
        
        // עדכון מיידי - updateSection כבר קורא ל-updatePreview פנימית
        window.builderCore.updateSection(section.id, updates);
    }
    
    /**
     * יצירת אובייקט עדכון לפי נתיב
     */
    createUpdateObject(path, value) {
        const parts = path.split('.');
        const result = {};
        
        let current = result;
        for (let i = 0; i < parts.length - 1; i++) {
            current[parts[i]] = {};
            current = current[parts[i]];
        }
        
        // אם הערך null - מחק את הkey, אם false - שמור כ-false (חשוב לcheckboxes!)
        if (value === null) {
            current[parts[parts.length - 1]] = undefined;
        } else {
            current[parts[parts.length - 1]] = value;
        }
        
        // Debug logging עבור visibility updates
        if (path.includes('visibility')) {
            console.log('🔧 Creating update object for visibility:', path, '=', value);
            console.log('🔧 Update object parts:', parts);
            console.log('🔧 Final update object result:', result);
        }
        
        return result;
    }

    /**
     * יצירת אובייקט עדכון משותף (גם מחשב וגם מובייל)
     */
    createSharedUpdateObject(path, value) {
        const parts = path.split('.');
        const result = {};
        
        // יצירת נתיבים גם למחשב וגם למובייל
        const desktopPath = [...parts];
        const mobilePath = [...parts];
        
        // הוספת desktop ו-mobile למיקום הנכון
        desktopPath.splice(-1, 0, 'desktop');
        mobilePath.splice(-1, 0, 'mobile');
        
        // בניית המבנה למחשב
        let current = result;
        for (let i = 0; i < desktopPath.length - 1; i++) {
            if (!current[desktopPath[i]]) {
                current[desktopPath[i]] = {};
            }
            current = current[desktopPath[i]];
        }
        current[desktopPath[desktopPath.length - 1]] = value;
        
        // בניית המבנה למובייל
        current = result;
        for (let i = 0; i < mobilePath.length - 1; i++) {
            if (!current[mobilePath[i]]) {
                current[mobilePath[i]] = {};
            }
            current = current[mobilePath[i]];
        }
        current[mobilePath[mobilePath.length - 1]] = value;
        
        return result;
    }
    
    /**
     * קבלת ערך לפי נתיב
     */
    getValueByPath(obj, path) {
        if (!obj || !path) return undefined;
        
        const parts = path.split('.');
        let current = obj;
        
        // Debug logging עבור visibility paths
        if (path.includes('visibility')) {
            console.log(`🔧 getValueByPath: path=${path}, obj=`, obj);
        }
        
        for (const part of parts) {
            if (current && typeof current === 'object' && part in current) {
                current = current[part];
                if (path.includes('visibility')) {
                    console.log(`🔧 getValueByPath: step ${part} = `, current);
                }
            } else {
                if (path.includes('visibility')) {
                    console.log(`🔧 getValueByPath: ${part} not found in`, current);
                }
                return undefined;
            }
        }
        
        if (path.includes('visibility')) {
            console.log(`🔧 getValueByPath: final result for ${path} = `, current);
        }
        
        return current;
    }
    
    /**
     * סגירת הגדרות
     */
    closeSettings() {
        // ניקוי סימוני event listeners
        const inputs = this.elements.settingsContent.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            delete input._settingsEventsBound;
        });
        
        this.elements.settingsPanel.style.display = 'none';
        this.currentSectionId = null;
        this.currentSectionType = null;
        
        console.log('⚙️ Settings closed');
    }
    
    /**
     * בדיקה אם הגדרות פתוחות
     */
    isSettingsOpen() {
        return this.elements.settingsPanel.style.display === 'block';
    }
    
    /**
     * הצגת שגיאה
     */
    showError(message) {
        if (this.elements.settingsContent) {
            this.elements.settingsContent.innerHTML = `
                <div class="p-4 text-center">
                    <div class="text-red-600 mb-2">
                        <i class="ri-error-warning-line text-2xl"></i>
                    </div>
                    <p class="text-gray-600">${message}</p>
                </div>
            `;
        }
    }
    
    /**
     * הגדרות בסיסיות כfallback
     */
    getBasicSettings(sectionType) {
        return `
            <div class="p-4 text-center">
                <h3 class="text-lg font-medium mb-4">הגדרות ${sectionType}</h3>
                <p class="text-gray-600 text-sm">לא נמצאו הגדרות מתקדמות לסקשן זה</p>
            </div>
        `;
    }
}

// יצירת instance גלובלי
window.settingsManager = new SettingsManager(); 