/**
 * Hero Section - מחלקה מודולרית להגדרות Hero
 */

// ודא שהמחלקה זמינה גלובלית
window.HeroSection = class HeroSection {
    constructor(builder) {
        this.builder = builder;
        this.data = this.getDefaultData();
        this.isActive = false;
        this.updateTimer = null; // Debounce timer for smooth updates
    }
    
    /**
     * נתוני ברירת מחדל עבור Hero
     */
    getDefaultData() {
        return {
            // Content
            title: 'ברוכים הבאים לחנות שלנו',
            subtitle: 'גלה את המוצרים הטובים ביותר במחירים הכי טובים',
            buttons: [
                {
                    text: 'קנה עכשיו',
                    link: '/products',
                    newTab: false,
                    style: 'filled',
                    paddingTop: '',
                    paddingBottom: '',
                    paddingRight: '',
                    paddingLeft: '',
                    marginTop: '',
                    marginBottom: ''
                }
            ],
            // Legacy fields for backward compatibility
            buttonText: 'קנה עכשיו',
            buttonLink: '/products',
            buttonNewTab: false,
            
            // Background
            bgType: 'color',
            bgColor: '#3B82F6',
            bgGradient1: '#3B82F6',
            bgGradient2: '#1E40AF',
            bgGradientDirection: 'to-b',
            bgImage: '',
            bgImageSize: 'cover',
            bgImagePosition: 'center',
            bgImageRepeat: 'no-repeat',
            bgImage_mobile: '',
            bgVideo: '',
            bgVideo_mobile: '',
            bgVideoOverlay: 30,
            
            // Colors
            titleColor: '#FFFFFF',
            subtitleColor: '#E5E7EB',
                    buttonBgColor: '#F59E0B',
        buttonTextColor: '#FFFFFF',
        buttonBorderColor: '#F59E0B',
        buttonBgColorHover: '#E5A712',
        buttonTextColorHover: '#FFFFFF',
        buttonBorderColorHover: '#E5A712',
            
            // Layout
            width: 'container',
            customWidth: 800,
            customWidthUnit: 'px',
            contentPosition: 'center-center',
            heightType: 'auto',
            heightValue: 500,
            heightType_tablet: '',
            heightValue_tablet: '',
            heightType_mobile: '',
            heightValue_mobile: '',
            
            // Spacing
            paddingTop: 80,
            paddingBottom: 80,
            paddingRight: 20,
            paddingLeft: 20,
            marginTop: 0,
            marginBottom: 0,
            marginRight: 0,
            marginLeft: 0,
            
            // Typography - Desktop
            titleFontSize: 36,
            titleFontFamily: "'Noto Sans Hebrew', sans-serif",
            titleFontWeight: 'normal',
            titleFontStyle: 'normal',
            titleTextDecoration: 'none',
            subtitleFontSize: 18,
            subtitleFontFamily: "'Noto Sans Hebrew', sans-serif",
            subtitleFontWeight: 'normal',
            subtitleFontStyle: 'normal',
            subtitleTextDecoration: 'none',
            buttonFontSize: 16,
            buttonFontFamily: "'Noto Sans Hebrew', sans-serif",
            buttonFontWeight: 'normal',
            buttonFontStyle: 'normal',
            buttonTextDecoration: 'none',
            buttonPaddingTop: 12,
            buttonPaddingBottom: 12,
            buttonPaddingRight: 32,
            buttonPaddingLeft: 32,
            buttonMarginTop: 0,
            buttonMarginBottom: 0,
            buttonShadow: 'false',
            
            // Typography - Tablet (will fallback to desktop if empty)
            titleFontSize_tablet: '',
            titleFontFamily_tablet: '',
            titleFontWeight_tablet: '',
            titleFontStyle_tablet: '',
            titleTextDecoration_tablet: '',
            subtitleFontSize_tablet: '',
            subtitleFontFamily_tablet: '',
            subtitleFontWeight_tablet: '',
            subtitleFontStyle_tablet: '',
            subtitleTextDecoration_tablet: '',
            buttonFontSize_tablet: '',
            buttonFontFamily_tablet: '',
            buttonFontWeight_tablet: '',
            buttonFontStyle_tablet: '',
            buttonTextDecoration_tablet: '',
            buttonPaddingTop_tablet: '',
            buttonPaddingBottom_tablet: '',
            buttonPaddingRight_tablet: '',
            buttonPaddingLeft_tablet: '',
            buttonMarginTop_tablet: '',
            buttonMarginBottom_tablet: '',
            buttonShadow_tablet: '',
            
            // Typography - Mobile (will fallback to desktop if empty)
            titleFontSize_mobile: '',
            titleFontFamily_mobile: '',
            titleFontWeight_mobile: '',
            titleFontStyle_mobile: '',
            titleTextDecoration_mobile: '',
            subtitleFontSize_mobile: '',
            subtitleFontFamily_mobile: '',
            subtitleFontWeight_mobile: '',
            subtitleFontStyle_mobile: '',
            subtitleTextDecoration_mobile: '',
            buttonFontSize_mobile: '',
            buttonFontFamily_mobile: '',
            buttonFontWeight_mobile: '',
            buttonFontStyle_mobile: '',
            buttonTextDecoration_mobile: '',
            buttonPaddingTop_mobile: '',
            buttonPaddingBottom_mobile: '',
            buttonPaddingRight_mobile: '',
            buttonPaddingLeft_mobile: '',
            buttonMarginTop_mobile: '',
            buttonMarginBottom_mobile: '',
            buttonShadow_mobile: '',
            
            // Custom
            customClass: '',
            customId: ''
        };
    }
    
    /**
     * פתיחת הגדרות Hero
     */
    async onOpen() {
        debugLog('Opening Hero settings');
        
        this.isActive = true;
        
        // הגדרה גלובלית לגישה מכפתורים וממערכת רספונסיבית
        window.heroSection = this;
        window.currentSection = this;
        
        // עדכון כותרת
        document.getElementById('settingsTitle').textContent = 'הגדרות Hero';
        
        // הצגת האנימציה
        this.builder.slideToSettings();
        
        // טעינת נתונים
        await this.loadData();
        
        // הגדרת event listeners
        setTimeout(() => {
            this.setupEventListeners();
            this.populateForm();
            this.initializeButtonsRepeater();
            this.setupTypographyButtons();
            this.setupTypographyModeSwitcher();
            
            // Load forms for current responsive mode
            const currentMode = window.currentResponsiveMode || 'desktop';
            this.populateTypographyForms(currentMode);
            this.populateHeightForms(currentMode);
            this.updateResponsiveTabsUI(currentMode);
        }, 150);
    }
    
    /**
     * סגירת הגדרות Hero
     */
    async onClose() {
        debugLog('Closing Hero settings');
        
        this.isActive = false;
        this._buttonsRepeaterSetup = false; // Reset buttons repeater setup flag
        this._buttonsRepeaterInitialized = false; // Reset buttons repeater initialization flag
        
        // Reset responsive mode to desktop when closing
        if (window.setResponsiveMode) {
            window.setResponsiveMode('desktop');
        }
        
        // נקה הגדרות גלובליות
        window.currentSection = null;
        
        this.removeEventListeners();
        this.builder.slideToSections();
    }
    
    /**
     * הגדרת Event Listeners
     */
    setupEventListeners() {
        if (!this.isActive) return;
        
        const form = document.getElementById('heroForm');
        if (!form) return;
        
        // אינפוטים רגילים
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.removeEventListener('input', this.handleInputChange);
            input.removeEventListener('change', this.handleInputChange);
            input.addEventListener('input', this.handleInputChange.bind(this));
            input.addEventListener('change', this.handleInputChange.bind(this));
        });
        
        // כפתורי מיקום
        this.setupPositionButtons();
        
        // כפתורי סוג רקע
        this.setupBackgroundTypeButtons();
        
        // הגדרת גובה הסקשן
        this.setupHeightControls();
        
        // הגדרות רוחב מתבצעות דרך global-width component
        
        // הגדרת ריפיטר כפתורים - יקרא רק אם עדיין לא הוקם
        if (!this._buttonsRepeaterSetup) {
            this.setupButtonsRepeater();
            this._buttonsRepeaterSetup = true;
        }
        
        debugLog('Hero event listeners setup completed');
    }
    
    /**
     * הסרת Event Listeners
     */
    removeEventListeners() {
        const form = document.getElementById('heroForm');
        if (!form) return;
        
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.removeEventListener('input', this.handleInputChange);
            input.removeEventListener('change', this.handleInputChange);
        });
        
        const positionButtons = document.querySelectorAll('.position-btn');
        positionButtons.forEach(btn => {
            btn.removeEventListener('click', this.handlePositionClick);
        });
        
        const bgTypeButtons = document.querySelectorAll('.bg-type-btn');
        bgTypeButtons.forEach(btn => {
            btn.removeEventListener('click', this.handleBgTypeClick);
        });
        
        // Remove buttons repeater listeners
        const buttonsInput = document.getElementById('heroButtons');
        if (buttonsInput) {
            const oldChangeHandler = buttonsInput._heroChangeHandler;
            const oldInputHandler = buttonsInput._heroInputHandler;
            
            if (oldChangeHandler) {
                buttonsInput.removeEventListener('change', oldChangeHandler);
                delete buttonsInput._heroChangeHandler;
            }
            if (oldInputHandler) {
                buttonsInput.removeEventListener('input', oldInputHandler);
                delete buttonsInput._heroInputHandler;
            }
        }
        
        // Remove typography button listeners
        document.querySelectorAll('.style-toggle-btn').forEach(button => {
            button.removeEventListener('click', this.handleTypographyButtonClick);
        });
        
        // Remove typography mode switcher listeners
        const modeButtons = document.querySelectorAll('.typography-mode-btn');
        modeButtons.forEach(button => {
            button.removeEventListener('click', this.handleTypographyModeChange);
        });
    }
    
    /**
     * הגדרת כפתורי מיקום
     */
    setupPositionButtons() {
        const positionButtons = document.querySelectorAll('.position-btn');
        
        positionButtons.forEach(btn => {
            btn.removeEventListener('click', this.handlePositionClick);
            btn.addEventListener('click', this.handlePositionClick.bind(this));
        });
    }
    
    /**
     * הגדרת כפתורי סוג רקע
     */
    setupBackgroundTypeButtons() {
        const bgTypeButtons = document.querySelectorAll('.bg-type-btn');
        
        bgTypeButtons.forEach(btn => {
            btn.removeEventListener('click', this.handleBgTypeClick);
            btn.addEventListener('click', this.handleBgTypeClick.bind(this));
        });
    }
    
    /**
     * הגדרת בקרי גובה הסקשן
     */
    setupHeightControls() {
        // Desktop height controls
        const heightTypeSelect = document.getElementById('heroHeightType');
        const heightValueInput = document.getElementById('heroHeightValue');
        
        if (heightTypeSelect) {
            heightTypeSelect.addEventListener('change', this.handleHeightTypeChange.bind(this));
        }
        
        if (heightValueInput) {
            heightValueInput.addEventListener('input', this.handleInputChange.bind(this));
        }
        
        // Tablet height controls
        const heightTypeSelectTablet = document.getElementById('heroHeightType_tablet');
        const heightValueInputTablet = document.getElementById('heroHeightValue_tablet');
        
        if (heightTypeSelectTablet) {
            heightTypeSelectTablet.addEventListener('change', this.handleHeightTypeChangeTablet.bind(this));
        }
        
        if (heightValueInputTablet) {
            heightValueInputTablet.addEventListener('input', this.handleInputChange.bind(this));
        }
        
        // Mobile height controls
        const heightTypeSelectMobile = document.getElementById('heroHeightType_mobile');
        const heightValueInputMobile = document.getElementById('heroHeightValue_mobile');
        
        if (heightTypeSelectMobile) {
            heightTypeSelectMobile.addEventListener('change', this.handleHeightTypeChangeMobile.bind(this));
        }
        
        if (heightValueInputMobile) {
            heightValueInputMobile.addEventListener('input', this.handleInputChange.bind(this));
        }
    }
    
    // setupWidthControls removed - now handled by global-width component
    
    /**
     * עדכון מצב קומפוננט הרוחב
     */
    updateWidthComponentState() {
        // הצגה/הסתרה של שדות רוחב מותאם אישית
        const customWidthSettings = document.getElementById('customWidthSettings');
        const widthSelect = document.querySelector('[name="width"]');
        
        console.log('Width component state update:', {
            customWidthSettings: !!customWidthSettings,
            widthSelect: !!widthSelect,
            currentWidth: this.data.width,
            widthSelectValue: widthSelect ? widthSelect.value : 'not found'
        });
        
        if (customWidthSettings && widthSelect) {
            if (this.data.width === 'custom') {
                customWidthSettings.classList.remove('hidden');
                console.log('Showing custom width settings');
            } else {
                customWidthSettings.classList.add('hidden');
                console.log('Hiding custom width settings');
            }
        }
        
        debugLog('Width component state updated');
    }
    
    /**
     * טיפול בשינוי אינפוט
     */
    handleInputChange = (e) => {
        if (!this.isActive) return;
        
        const { name, value } = e.target;
        this.updateProperty(name, value);
        this.builder.markAsChanged();
    }
    
    /**
     * טיפול בלחיצה על כפתור מיקום
     */
    handlePositionClick = (e) => {
        e.preventDefault();
        if (!this.isActive) return;
        
        // עדכון UI
        document.querySelectorAll('.position-btn').forEach(btn => {
            btn.classList.remove('bg-blue-50', 'border-blue-300');
        });
        e.target.closest('.position-btn').classList.add('bg-blue-50', 'border-blue-300');
        
        // עדכון הנתונים
        const position = e.target.closest('.position-btn').dataset.position;
        document.getElementById('heroContentPosition').value = position;
        this.updateProperty('contentPosition', position);
        this.builder.markAsChanged();
    }
    
    /**
     * טיפול בלחיצה על כפתור סוג רקע
     */
    handleBgTypeClick = (e) => {
        e.preventDefault();
        if (!this.isActive) return;
        
        // עדכון UI
        document.querySelectorAll('.bg-type-btn').forEach(btn => {
            btn.classList.remove('bg-blue-50', 'border-blue-300');
        });
        e.target.closest('.bg-type-btn').classList.add('bg-blue-50', 'border-blue-300');
        
        // עדכון הנתונים
        const bgType = e.target.closest('.bg-type-btn').dataset.bgType;
        document.getElementById('heroBgType').value = bgType;
        
        // הצגה/הסתרה של סקשנים רלוונטיים
        this.toggleBackgroundSections(bgType);
        
        this.updateProperty('bgType', bgType);
        this.builder.markAsChanged();
    }
    
    /**
     * טיפול בשינוי סוג הגובה
     */
    handleHeightTypeChange = (e) => {
        if (!this.isActive) return;
        
        const heightType = e.target.value;
        const heightValueInput = document.getElementById('heroHeightValue');
        
        if (heightValueInput) {
            if (heightType === 'auto') {
                heightValueInput.classList.add('opacity-50', 'bg-gray-100');
                heightValueInput.disabled = true;
            } else {
                heightValueInput.classList.remove('opacity-50', 'bg-gray-100');
                heightValueInput.disabled = false;
            }
        }
        
        this.updateProperty('heightType', heightType);
        this.builder.markAsChanged();
    }
    
    /**
     * טיפול בשינוי סוג הגובה לטאבלט
     */
    handleHeightTypeChangeTablet = (e) => {
        if (!this.isActive) return;
        
        const heightType = e.target.value;
        const heightValueInput = document.getElementById('heroHeightValue_tablet');
        
        if (heightValueInput) {
            if (heightType === 'auto' || heightType === '') {
                heightValueInput.classList.add('opacity-50', 'bg-gray-100');
                heightValueInput.disabled = true;
            } else {
                heightValueInput.classList.remove('opacity-50', 'bg-gray-100');
                heightValueInput.disabled = false;
            }
        }
        
        this.updateProperty('heightType_tablet', heightType);
        this.builder.markAsChanged();
    }
    
    /**
     * טיפול בשינוי סוג הגובה למובייל
     */
    handleHeightTypeChangeMobile = (e) => {
        if (!this.isActive) return;
        
        const heightType = e.target.value;
        const heightValueInput = document.getElementById('heroHeightValue_mobile');
        
        if (heightValueInput) {
            if (heightType === 'auto' || heightType === '') {
                heightValueInput.classList.add('opacity-50', 'bg-gray-100');
                heightValueInput.disabled = true;
            } else {
                heightValueInput.classList.remove('opacity-50', 'bg-gray-100');
                heightValueInput.disabled = false;
            }
        }
        
        this.updateProperty('heightType_mobile', heightType);
        this.builder.markAsChanged();
    }
    

    
    /**
     * הצגה/הסתרה של סקשני רקע
     */
    toggleBackgroundSections(bgType) {
        // הסתרת כל הסקשנים
        document.getElementById('bgColorSection').style.display = 'none';
        document.getElementById('bgGradientSection').classList.add('hidden');
        document.getElementById('bgImageSection').classList.add('hidden');
        document.getElementById('bgVideoSection').classList.add('hidden');
        
        // הצגת הסקשן הרלוונטי
        switch (bgType) {
            case 'color':
                document.getElementById('bgColorSection').style.display = 'block';
                break;
            case 'gradient':
                document.getElementById('bgGradientSection').classList.remove('hidden');
                break;
            case 'image':
                document.getElementById('bgImageSection').classList.remove('hidden');
                break;
            case 'video':
                document.getElementById('bgVideoSection').classList.remove('hidden');
                break;
        }
    }
    
    /**
     * עדכון מאפיין
     */
    updateProperty(property, value) {
        if (property === 'buttonsMobileDisplay') {
            console.log('Hero updateProperty: Updating buttonsMobileDisplay from', this.data[property], 'to', value);
        }
        
        this.data[property] = value;
        
        if (property === 'buttonsMobileDisplay') {
            console.log('Hero updateProperty: buttonsMobileDisplay updated in data:', this.data[property]);
        }
        
        // שליחה ל-iframe
        this.builder.sendToIframe('updateHero', this.data);
        
        // שמירה אוטומטית
        this.builder.markAsChanged();
        
        debugLog(`Updated Hero ${property} to:`, value);
    }
    
    /**
     * עדכון אייקון כפתור
     */
    updateButtonIcon(index, iconClass) {
        if (this.data.buttons && this.data.buttons[index]) {
            this.data.buttons[index].icon = iconClass;
            this.builder.sendToIframe('updateHero', this.data);
            // שמירה אוטומטית
            this.builder.markAsChanged();
        }
    }
    
    /**
     * מילוי הטופס
     */
    populateForm() {
        Object.keys(this.data).forEach(key => {
            const input = document.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = this.data[key];
            }
        });
        
        // עדכון UI של כפתורי מיקום
        const positionValue = this.data.contentPosition || 'center-center';
        document.querySelectorAll('.position-btn').forEach(btn => {
            btn.classList.remove('bg-blue-50', 'border-blue-300');
            if (btn.dataset.position === positionValue) {
                btn.classList.add('bg-blue-50', 'border-blue-300');
            }
        });
        
        // עדכון UI של כפתורי סוג רקע
        const bgTypeValue = this.data.bgType || 'color';
        document.querySelectorAll('.bg-type-btn').forEach(btn => {
            btn.classList.remove('bg-blue-50', 'border-blue-300');
            if (btn.dataset.bgType === bgTypeValue) {
                btn.classList.add('bg-blue-50', 'border-blue-300');
            }
        });
        
        // הצגת הסקשן הנכון של הרקע
        this.toggleBackgroundSections(bgTypeValue);
        
        // עדכון כפתורי הטיפוגרפיה
        this.updateTypographyButtons();
        
        // עדכון נתוני הכפתורים בריפטר
        if (this.data.buttons && window.buttonsRepeater) {
            console.log('Hero: Updating buttons repeater with data:', this.data.buttons);
            window.buttonsRepeater.setButtons(this.data.buttons);
        }
        
        // עדכון placeholders לפי המצב הנוכחי
        const currentMode = window.currentResponsiveMode || 'desktop';
        this.updatePlaceholders(currentMode);
        
        // עדכון מצב קומפוננט הרוחב
        this.updateWidthComponentState();
        
        debugLog('Hero form populated');
    }
    
    /**
     * טעינת נתונים - מהמסד או מדמו
     */
    async loadData(pageType = 'home') {
        try {
            const response = await fetch(`api/pages.php?page_type=${pageType}`);
            const result = await response.json();
            
            if (result.success && result.page_data) {
                console.log('Page loaded from:', result.source); // database או demo
                
                // חיפוש ה-hero section בדף
                const heroSection = result.page_data.sections?.find(section => section.type === 'hero');
                
                if (heroSection && heroSection.data) {
                    this.data = { ...this.getDefaultData(), ...heroSection.data };
                } else {
                    // אם אין hero section, השתמש בברירת מחדל
                    console.log('No hero section found, using defaults');
                    this.data = this.getDefaultData();
                }
                
                // Ensure buttons data is properly formatted
                if (!this.data.buttons || !Array.isArray(this.data.buttons)) {
                    this.data.buttons = this.getDefaultData().buttons;
                }
                
                console.log('Hero data loaded successfully:', this.data);
                debugLog('Hero data loaded successfully');
            } else {
                throw new Error(result.message || 'Failed to load page data');
            }
        } catch (error) {
            console.error('Error loading hero data:', error);
            // Fallback to default data
            this.data = this.getDefaultData();
        }
    }
    
    /**
     * שמירת נתונים
     */
    async saveData() {
        try {
            const response = await fetch('api/save-hero.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    hero: this.data
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                debugLog('Hero data saved successfully');
                return true;
            } else {
                throw new Error(result.message || 'שגיאה בשמירה');
            }
        } catch (error) {
            console.error('Error saving hero data:', error);
            throw error;
        }
    }
    
    /**
     * אתחול רכיב הכפתורים
     */
    initializeButtonsRepeater() {
        console.log('Hero: Initializing buttons repeater');
        
        if (this._buttonsRepeaterInitialized) {
            console.log('Hero: Buttons repeater already initialized');
            return;
        }
        
        // Create our own buttons repeater directly
        this._buttonsRepeaterInitialized = true;
        
        // Initialize the buttons repeater
        this.createButtonsRepeater();
        
        // Setup buttons repeater after initialization
        setTimeout(() => {
            if (!this._buttonsRepeaterSetup) {
                this.setupButtonsRepeater();
                this._buttonsRepeaterSetup = true;
            }
        }, 100);
    }
    
    /**
     * יצירת ריפטר כפתורים
     */
    createButtonsRepeater() {
        console.log('Hero: Creating buttons repeater');
        
        if (window.buttonsRepeater) {
            console.log('Hero: Buttons repeater already exists');
            return;
        }
        
        // Create buttons repeater instance
        window.buttonsRepeater = new this.ButtonsRepeater('hero', this);
        console.log('Hero: Buttons repeater created');
    }
    
    /**
     * מחלקת ריפיטר כפתורים
     */
    ButtonsRepeater = class {
        constructor(sectionType, heroSection) {
            this.sectionType = sectionType;
            this.heroSection = heroSection;
            this.container = document.getElementById('buttonsContainer');
            this.hiddenInput = document.getElementById(`${sectionType}Buttons`);
            this.addButton = document.getElementById('addButton');
            this.buttons = [];
            
            console.log('ButtonsRepeater constructor:', {
                sectionType,
                container: !!this.container,
                hiddenInput: !!this.hiddenInput,
                addButton: !!this.addButton
            });
            
            if (!this.container || !this.hiddenInput) {
                console.error('ButtonsRepeater: Required elements not found');
                return;
            }
            
            this.init();
        }
        
        init() {
            console.log('ButtonsRepeater: Starting initialization');
            
            if (!this.container || !this.hiddenInput) {
                console.error('ButtonsRepeater: Cannot initialize - missing required elements');
                return;
            }
            
            // Load existing buttons
            try {
                this.buttons = JSON.parse(this.hiddenInput.value || '[]');
                console.log('ButtonsRepeater: Loaded buttons:', this.buttons);
            } catch (e) {
                console.log('ButtonsRepeater: Error parsing buttons, using default');
                this.buttons = [{
                    text: 'קנה עכשיו',
                    link: '/products',
                    newTab: false,
                    style: 'filled',
                    paddingTop: '',
                    paddingBottom: '',
                    paddingRight: '',
                    paddingLeft: '',
                    rounded: 0,
                    fullWidth: false,
                    icon: ''
                }];
            }
            
            // Ensure we have at least one button
            if (!this.buttons || this.buttons.length === 0) {
                this.buttons = [{
                    text: 'קנה עכשיו',
                    link: '/products',
                    newTab: false,
                    style: 'filled',
                    paddingTop: '',
                    paddingBottom: '',
                    paddingRight: '',
                    paddingLeft: '',
                    rounded: 0,
                    fullWidth: false,
                    icon: ''
                }];
            }
            
            // Event listeners
            if (this.addButton) {
                this.addButton.addEventListener('click', () => this.addNewButton());
                console.log('ButtonsRepeater: Add button event listener added');
            } else {
                console.warn('ButtonsRepeater: Add button not found, continuing without it');
            }
            
            // Render initial buttons
            this.render();
            console.log('ButtonsRepeater: Initialization complete');
        }
        
        addNewButton() {
            const newButton = {
                text: 'כפתור חדש',
                link: '#',
                newTab: false,
                style: 'filled',
                paddingTop: '',
                paddingBottom: '',
                paddingRight: '',
                paddingLeft: '',
                marginTop: '',
                marginBottom: ''
            };
            
            this.buttons.push(newButton);
            this.render();
            this.updateHiddenInput();
            this.triggerChange();
        }
        
        deleteButton(index) {
            if (this.buttons.length > 1) { // Keep at least one button
                this.buttons.splice(index, 1);
                this.render();
                this.updateHiddenInput();
                this.triggerChange();
            } else {
                alert('חייב להיות לפחות כפתור אחד');
            }
        }
        
        updateButton(index, field, value) {
            if (this.buttons[index]) {
                this.buttons[index][field] = value;
                
                // עדכון חלק של השדה הספציפי במקום רינדור מחדש
                this.updateSpecificField(index, field, value);
                
                this.updateHiddenInput();
                this.triggerChange();
            }
        }
        
        /**
         * עדכון שדה ספציפי בכפתור ללא רינדור מחדש
         */
        updateSpecificField(index, field, value) {
            const buttonElement = this.container.children[index];
            if (!buttonElement) return;
            
            switch(field) {
                case 'text':
                    const textInput = buttonElement.querySelector('input[placeholder="טקסט הכפתור"]');
                    if (textInput && textInput.value !== value) {
                        textInput.value = value;
                    }
                    break;
                    
                case 'link':
                    const linkInput = buttonElement.querySelector('input[placeholder="https://example.com"]');
                    if (linkInput && linkInput.value !== value) {
                        linkInput.value = value;
                    }
                    break;
                    
                case 'newTab':
                    const checkbox = buttonElement.querySelector(`#newTab_${index}`);
                    if (checkbox && checkbox.checked !== value) {
                        checkbox.checked = value;
                    }
                    break;
                    
                case 'style':
                    this.updateStyleSelection(buttonElement, value);
                    break;
                    
                case 'paddingTop':
                case 'paddingBottom':
                case 'paddingRight':
                case 'paddingLeft':
                    this.updatePaddingField(buttonElement, field, value);
                    break;
                    
                case 'marginTop':
                case 'marginBottom':
                    this.updateMarginField(buttonElement, field, value);
                    break;
            }
        }
        
        /**
         * עדכון בחירת סגנון הכפתור
         */
        updateStyleSelection(buttonElement, selectedStyle) {
            const styleOptions = buttonElement.querySelectorAll('.button-style-option');
            styleOptions.forEach(option => {
                const isSelected = option.textContent.trim() === this.getStyleLabel(selectedStyle);
                
                if (isSelected) {
                    option.classList.add('selected', 'bg-blue-500', 'text-white', 'border-blue-500');
                } else {
                    option.classList.remove('selected', 'bg-blue-500', 'text-white', 'border-blue-500');
                }
            });
        }
        
        /**
         * עדכון שדה padding
         */
        updatePaddingField(buttonElement, field, value) {
            const paddingInput = buttonElement.querySelector(`input[data-padding="${field}"]`);
            
            if (paddingInput && paddingInput.value !== value) {
                paddingInput.value = value;
            }
        }
        
        /**
         * עדכון שדה margin
         */
        updateMarginField(buttonElement, field, value) {
            const marginInput = buttonElement.querySelector(`input[data-margin="${field}"]`);
            
            if (marginInput && marginInput.value !== value) {
                marginInput.value = value;
            }
        }
        
        /**
         * קבלת תווית סגנון
         */
        getStyleLabel(style) {
            const styleLabels = {
                'filled': 'צבע מלא',
                'outline': 'מתאר',
                'white': 'לבן',
                'black': 'שחור',
                'text': 'קו בלבד'
            };
            return styleLabels[style] || '';
        }
        
        updateHiddenInput() {
            this.hiddenInput.value = JSON.stringify(this.buttons);
        }
        
        triggerChange() {
            // Use debounced update for smoother experience
            if (window.heroSection && window.heroSection.updatePropertyDebounced) {
                window.heroSection.updatePropertyDebounced('buttons', this.buttons, 150);
            } else {
                // Fallback to regular method
                const changeEvent = new Event('change', { bubbles: true });
                this.hiddenInput.dispatchEvent(changeEvent);
                
                const inputEvent = new Event('input', { bubbles: true });
                this.hiddenInput.dispatchEvent(inputEvent);
            }
        }
        
        render() {
            this.container.innerHTML = '';
            
            this.buttons.forEach((button, index) => {
                const buttonElement = this.createButtonElement(button, index);
                this.container.appendChild(buttonElement);
            });
            
            // הוספת הגדרות תצוגה במובייל בסוף הריפיטר
            if (window.buttonFeatures) {
                window.buttonFeatures.addMobileDisplaySetting(this.container);
            }
        }
        
        createButtonElement(button, index) {
            const div = document.createElement('div');
            div.className = 'button-item bg-white border border-gray-200 rounded-lg p-4 transition-all hover:border-gray-300 hover:shadow-sm';
            div.innerHTML = `
                <div class="flex items-center justify-between mb-3">
                    <h5 class="text-sm font-medium text-gray-900">כפתור ${index + 1}</h5>
                    <button type="button" 
                            class="delete-button w-6 h-6 text-red-500 hover:text-red-700 flex items-center justify-center opacity-60 hover:opacity-100 transition-opacity"
                            onclick="window.buttonsRepeater.deleteButton(${index})"
                            title="מחק כפתור">
                        <i class="ri-delete-bin-line text-sm"></i>
                    </button>
                </div>
                
                <div class="space-y-3">
                    <!-- Button Text -->
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">טקסט כפתור</label>
                        <input type="text" 
                               value="${button.text}" 
                               placeholder="טקסט הכפתור"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               onchange="window.buttonsRepeater.updateButton(${index}, 'text', this.value)">
                    </div>
                    
                    <!-- Button Link -->
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">קישור כפתור</label>
                        <div class="flex items-center gap-2">
                            <input type="text" 
                                   value="${button.link}" 
                                   placeholder="https://example.com"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   onchange="window.buttonsRepeater.updateButton(${index}, 'link', this.value)">
                            <button type="button" 
                                    class="w-8 h-8 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-50 transition-colors"
                                    title="אייקון לינק">
                                <i class="ri-external-link-line text-sm text-gray-600"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- New Tab Checkbox -->
                    <div class="flex items-center gap-2">
                        <input type="checkbox" 
                               id="newTab_${index}" 
                               ${button.newTab ? 'checked' : ''}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                               onchange="window.buttonsRepeater.updateButton(${index}, 'newTab', this.checked)">
                        <label for="newTab_${index}" class="text-sm text-gray-700">פתח בכרטיסיה חדשה</label>
                    </div>
                    
                    <!-- Button Style -->
                    <div>
                        <label class="block text-xs text-gray-600 mb-2">סגנון כפתור</label>
                        <div class="grid grid-cols-5 gap-2">
                            ${this.createStyleOption('filled', 'צבע מלא', button.style === 'filled', index)}
                            ${this.createStyleOption('outline', 'מתאר', button.style === 'outline', index)}
                            ${this.createStyleOption('white', 'לבן', button.style === 'white', index)}
                            ${this.createStyleOption('black', 'שחור', button.style === 'black', index)}
                            ${this.createStyleOption('text', 'קו בלבד', button.style === 'text', index)}
                        </div>
                    </div>
                    
                    <!-- Button Padding -->
                    <div>
                        <label class="block text-xs text-gray-600 mb-2">ריווח פנימי (Padding)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">עליון</label>
                                <input type="number" 
                                       value="${button.paddingTop || ''}" 
                                       placeholder="12"
                                       min="0"
                                       data-padding="paddingTop"
                                       class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 transition-all duration-200"
                                       onchange="window.buttonsRepeater.updateButton(${index}, 'paddingTop', this.value)">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">תחתון</label>
                                <input type="number" 
                                       value="${button.paddingBottom || ''}" 
                                       placeholder="12"
                                       min="0"
                                       data-padding="paddingBottom"
                                       class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 transition-all duration-200"
                                       onchange="window.buttonsRepeater.updateButton(${index}, 'paddingBottom', this.value)">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">ימין</label>
                                <input type="number" 
                                       value="${button.paddingRight || ''}" 
                                       placeholder="32"
                                       min="0"
                                       data-padding="paddingRight"
                                       class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 transition-all duration-200"
                                       onchange="window.buttonsRepeater.updateButton(${index}, 'paddingRight', this.value)">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">שמאל</label>
                                <input type="number" 
                                       value="${button.paddingLeft || ''}" 
                                       placeholder="32"
                                       min="0"
                                       data-padding="paddingLeft"
                                       class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 transition-all duration-200"
                                       onchange="window.buttonsRepeater.updateButton(${index}, 'paddingLeft', this.value)">
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">השאר ריק לשימוש בברירת מחדל</p>
                    </div>
                    
                    <!-- Button Margin -->
                    <div>
                        <label class="block text-xs text-gray-600 mb-2">ריווח עליון ותחתון (Margin)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">עליון</label>
                                <input type="number" 
                                       value="${button.marginTop || ''}" 
                                       placeholder="0"
                                       min="0"
                                       data-margin="marginTop"
                                       class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 transition-all duration-200"
                                       onchange="window.buttonsRepeater.updateButton(${index}, 'marginTop', this.value)">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">תחתון</label>
                                <input type="number" 
                                       value="${button.marginBottom || ''}" 
                                       placeholder="0"
                                       min="0"
                                       data-margin="marginBottom"
                                       class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 transition-all duration-200"
                                       onchange="window.buttonsRepeater.updateButton(${index}, 'marginBottom', this.value)">
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">השאר ריק לשימוש בברירת מחדל</p>
                    </div>
                </div>
            `;
            
            // הוספת פיצ'רים מתקדמים
            if (window.buttonFeatures) {
                window.buttonFeatures.enhanceButtonElement(div, button, index);
            }
            
            return div;
        }
        
        createStyleOption(style, label, selected, index) {
            return `
                <div class="button-style-option ${selected ? 'selected' : ''} p-2 border border-gray-300 rounded-md cursor-pointer text-center text-xs transition-all hover:bg-gray-50 flex items-center justify-center ${selected ? 'bg-blue-500 text-white border-blue-500' : ''}" 
                     onclick="window.buttonsRepeater.selectStyle(${index}, '${style}', this)">
                    ${label}
                </div>
            `;
        }
        
        selectStyle(index, style, element) {
            // Remove selected class from siblings
            const parent = element.parentNode;
            parent.querySelectorAll('.button-style-option').forEach(option => {
                option.classList.remove('selected', 'bg-blue-500', 'text-white', 'border-blue-500');
            });
            
            // Add selected class to clicked element
            element.classList.add('selected', 'bg-blue-500', 'text-white', 'border-blue-500');
            
            // Update button data directly to avoid double UI updates
            if (this.buttons[index]) {
                this.buttons[index]['style'] = style;
                this.updateHiddenInput();
                this.triggerChange();
            }
        }
        
        // Public method to get buttons data
        getButtons() {
            return this.buttons;
        }
        
        // Public method to set buttons data
        setButtons(buttons) {
            this.buttons = buttons || [];
            this.render();
            this.updateHiddenInput();
        }
    }
    
    /**
     * הגדרת ריפיטר כפתורים
     */
    setupButtonsRepeater() {
        console.log('Hero: Setting up buttons repeater');
        
        // Wait for buttonsRepeater to be available and set initial data
        const setupRepeaterInterval = setInterval(() => {
            if (window.buttonsRepeater && this.data && this.data.buttons) {
                console.log('Hero: Setting initial buttons data');
                clearInterval(setupRepeaterInterval);
                window.buttonsRepeater.setButtons(this.data.buttons);
            }
        }, 100);
        
        // Clear interval after 5 seconds
        setTimeout(() => {
            clearInterval(setupRepeaterInterval);
        }, 5000);
        
        // Listen for buttons updates
        const buttonsInput = document.getElementById('heroButtons');
        if (buttonsInput) {
            // Remove existing listeners to avoid duplicates
            const oldChangeHandler = buttonsInput._heroChangeHandler;
            const oldInputHandler = buttonsInput._heroInputHandler;
            
            if (oldChangeHandler) {
                buttonsInput.removeEventListener('change', oldChangeHandler);
            }
            if (oldInputHandler) {
                buttonsInput.removeEventListener('input', oldInputHandler);
            }
            
            // Create new handlers with debouncing
            let updateTimeout;
            const updateButtons = (buttonsData) => {
                clearTimeout(updateTimeout);
                updateTimeout = setTimeout(() => {
                    if (!this.isActive) return;
                    
                    try {
                        this.updateProperty('buttons', buttonsData);
                        
                        // Update legacy fields for backward compatibility
                        if (buttonsData.length > 0) {
                            this.updateProperty('buttonText', buttonsData[0].text || '');
                            this.updateProperty('buttonLink', buttonsData[0].link || '');
                            this.updateProperty('buttonNewTab', buttonsData[0].newTab || false);
                        }
                        
                        this.builder.markAsChanged();
                    } catch (error) {
                        console.error('Error processing buttons data:', error);
                    }
                }, 100); // 100ms debounce
            };
            
            const changeHandler = (e) => {
                try {
                    const buttons = JSON.parse(e.target.value || '[]');
                    updateButtons(buttons);
                } catch (error) {
                    console.error('Error parsing buttons data:', error);
                }
            };
            
            // Only use change handler, not input
            const inputHandler = changeHandler;
            
            // Add new listeners and store references
            buttonsInput.addEventListener('change', changeHandler);
            buttonsInput.addEventListener('input', inputHandler);
            buttonsInput._heroChangeHandler = changeHandler;
            buttonsInput._heroInputHandler = inputHandler;
            
            console.log('Hero: Buttons input event listeners added');
        } else {
            console.error('Hero: Buttons input not found');
        }
    }
    
    /**
     * עדכון מראה כפתורי טיפוגרפיה
     */
    updateTypographyButtons() {
        console.log('Hero: Updating typography buttons state');
        
        // Title buttons
        this.updateTypographyButtonState('heroTitleBold', this.data.titleFontWeight === 'bold');
        this.updateTypographyButtonState('heroTitleItalic', this.data.titleFontStyle === 'italic');
        this.updateTypographyButtonState('heroTitleUnderline', this.data.titleTextDecoration === 'underline');
        
        // Subtitle buttons
        this.updateTypographyButtonState('heroSubtitleBold', this.data.subtitleFontWeight === 'bold');
        this.updateTypographyButtonState('heroSubtitleItalic', this.data.subtitleFontStyle === 'italic');
        this.updateTypographyButtonState('heroSubtitleUnderline', this.data.subtitleTextDecoration === 'underline');
        
        // Button buttons
        this.updateTypographyButtonState('heroButtonBold', this.data.buttonFontWeight === 'bold');
        this.updateTypographyButtonState('heroButtonItalic', this.data.buttonFontStyle === 'italic');
        this.updateTypographyButtonState('heroButtonUnderline', this.data.buttonTextDecoration === 'underline');
        this.updateTypographyButtonState('heroButtonShadow', this.data.buttonShadow === 'true');
    }
    
    /**
     * עדכון מצב כפתור טיפוגרפיה בודד
     */
    updateTypographyButtonState(buttonId, isActive) {
        const button = document.getElementById(buttonId);
        if (button) {
            if (isActive) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        }
    }
    
    /**
     * הגדרת כפתורי טיפוגרפיה
     */
    setupTypographyButtons() {
        console.log('Hero: Setting up typography buttons');
        
        // Handle style toggle buttons for all elements
        document.querySelectorAll('.style-toggle-btn').forEach(button => {
            // Remove existing listeners to avoid duplicates
            button.removeEventListener('click', this.handleTypographyButtonClick);
            button.addEventListener('click', this.handleTypographyButtonClick.bind(this));
        });
    }
    
    /**
     * טיפול בלחיצה על כפתור טיפוגרפיה
     */
    handleTypographyButtonClick = (e) => {
        e.preventDefault();
        
        if (!this.isActive) return;
        
        const button = e.target.closest('.style-toggle-btn');
        const buttonId = button.id;
        const style = button.getAttribute('data-style');
        
        console.log('Typography button clicked:', buttonId, style);
        
        // Toggle active state
        button.classList.toggle('active');
        
        // Update hidden input based on button ID and style
        let hiddenInputId;
        let value;
        
        if (buttonId.includes('Bold')) {
            hiddenInputId = buttonId.replace('Bold', 'FontWeight');
            value = button.classList.contains('active') ? 'bold' : 'normal';
        } else if (buttonId.includes('Italic')) {
            hiddenInputId = buttonId.replace('Italic', 'FontStyle');
            value = button.classList.contains('active') ? 'italic' : 'normal';
        } else if (buttonId.includes('Underline')) {
            hiddenInputId = buttonId.replace('Underline', 'TextDecoration');
            value = button.classList.contains('active') ? 'underline' : 'none';
        } else if (buttonId.includes('Shadow')) {
            hiddenInputId = buttonId.replace('Shadow', 'Shadow');
            value = button.classList.contains('active') ? 'true' : 'false';
        }
        
        console.log('Updating:', hiddenInputId, 'to:', value);
        
        // Update hidden input
        const hiddenInput = document.getElementById(hiddenInputId);
        if (hiddenInput) {
            hiddenInput.value = value;
            
            // Update data property
            const propertyName = hiddenInput.name;
            this.updateProperty(propertyName, value);
            
            console.log('Updated property:', propertyName, 'to:', value);
            
            this.builder.markAsChanged();
        } else {
            console.error('Hidden input not found:', hiddenInputId);
        }
    }
    
    /**
     * הגדרת סוויצ'ר מצבי טיפוגרפיה
     */
    setupTypographyModeSwitcher() {
        console.log('Hero: Setting up typography mode switcher');
        
        const modeButtons = document.querySelectorAll('.typography-mode-btn');
        
        modeButtons.forEach(button => {
            button.removeEventListener('click', this.handleTypographyModeChange);
            button.addEventListener('click', this.handleTypographyModeChange.bind(this));
        });
        
        // Set initial mode based on global responsive mode
        const currentMode = window.currentResponsiveMode || 'desktop';
        console.log('Setting initial typography mode to:', currentMode);
        this.updateTypographyModeUI(currentMode);
    }
    
    /**
     * טיפול בשינוי מצב טיפוגרפיה
     */
    handleTypographyModeChange = (e) => {
        if (!this.isActive) return;
        
        const mode = e.target.closest('.typography-mode-btn').getAttribute('data-mode');
        
        // Update global responsive mode
        if (window.setResponsiveMode) {
            window.setResponsiveMode(mode);
        }
        
        // Force update the forms after a brief delay to ensure everything is set
        setTimeout(() => {
            this.populateTypographyForms(mode);
        }, 20);
    }
    
    /**
     * עדכון UI של סוויצ'ר מצבי טיפוגרפיה
     */
    updateTypographyModeUI(mode) {
        const modeButtons = document.querySelectorAll('.typography-mode-btn');
        
        modeButtons.forEach(btn => {
            btn.classList.remove('active', 'bg-blue-500', 'text-white');
            btn.classList.add('text-gray-600');
            if (btn.getAttribute('data-mode') === mode) {
                btn.classList.add('active', 'bg-blue-500', 'text-white');
                btn.classList.remove('text-gray-600');
            }
        });
    }
    
    /**
     * עדכון UI של כל הסוויצ'רים הרספונסיביים
     */
    updateResponsiveTabsUI(mode) {
        // Update responsive content visibility
        const allResponsiveContent = document.querySelectorAll('.responsive-content');
        allResponsiveContent.forEach(content => {
            content.classList.add('hidden');
        });
        
        const targetContent = document.querySelector(`.${mode}-content`);
        if (targetContent) {
            targetContent.classList.remove('hidden');
        }
    }
    
    /**
     * מילוי טפסי גובה עבור מצב מסוים
     */
    populateHeightForms(mode) {
        const heightTypeInput = document.getElementById(`heroHeightType${mode === 'desktop' ? '' : '_' + mode}`);
        const heightValueInput = document.getElementById(`heroHeightValue${mode === 'desktop' ? '' : '_' + mode}`);
        
        if (heightTypeInput) {
            const heightTypeValue = this.getResponsiveValueLocal('heightType', mode);
            heightTypeInput.value = heightTypeValue || (mode === 'desktop' ? 'auto' : '');
            
            // Handle input state based on height type
            if (heightValueInput) {
                const heightValueValue = this.getResponsiveValueLocal('heightValue', mode);
                heightValueInput.value = heightValueValue || '';
                
                if (heightTypeInput.value === 'auto' || heightTypeInput.value === '') {
                    heightValueInput.classList.add('opacity-50', 'bg-gray-100');
                    heightValueInput.disabled = true;
                } else {
                    heightValueInput.classList.remove('opacity-50', 'bg-gray-100');
                    heightValueInput.disabled = false;
                }
            }
        }
    }
    
    /**
     * מקבל ערך רספונסיבי עם fallback למחשב
     */
    getResponsiveValueLocal(baseName, mode = null) {
        const currentMode = mode || window.currentResponsiveMode || 'desktop';
        
        if (currentMode === 'desktop') {
            return this.data[baseName];
        }
        
        const responsiveProperty = `${baseName}_${currentMode}`;
        const responsiveValue = this.data[responsiveProperty];
        
        // If responsive value exists and is not empty, use it
        // Check for meaningful values (not empty, null, undefined, or string "0")
        if (responsiveValue !== undefined && responsiveValue !== '' && responsiveValue !== null && responsiveValue !== "0") {
            return responsiveValue;
        }
        
        // Otherwise, fallback to desktop value
        return this.data[baseName];
    }

    /**
     * מילוי טפסי טיפוגרפיה עבור מצב מסוים
     */
    populateTypographyForms(mode) {
        // Typography elements to update
        const elements = ['title', 'subtitle', 'button'];
        const properties = ['FontSize', 'FontFamily', 'FontWeight', 'FontStyle', 'TextDecoration'];
        
        elements.forEach(element => {
            properties.forEach(property => {
                const responsiveValue = this.getResponsiveValueLocal(`${element}${property}`, mode);
                const inputId = `hero${element.charAt(0).toUpperCase() + element.slice(1)}${property}`;
                const input = document.getElementById(inputId);
                
                if (input) {
                    input.value = responsiveValue || '';
                }
            });
        });
        
        // Button padding
        ['Top', 'Bottom', 'Right', 'Left'].forEach(side => {
            const responsiveValue = this.getResponsiveValueLocal(`buttonPadding${side}`, mode);
            const inputId = `heroButtonPadding${side}`;
            const input = document.getElementById(inputId);
            
            if (input) {
                input.value = responsiveValue || '';
            }
        });
        
        // Button margin
        ['Top', 'Bottom'].forEach(side => {
            const responsiveValue = this.getResponsiveValueLocal(`buttonMargin${side}`, mode);
            const inputId = `heroButtonMargin${side}`;
            const input = document.getElementById(inputId);
            
            if (input) {
                input.value = responsiveValue || '';
            }
        });
        
        // Button shadow
        const responsiveValue = this.getResponsiveValueLocal('buttonShadow', mode);
        const shadowInput = document.getElementById('heroButtonShadow');
        if (shadowInput) {
            shadowInput.value = responsiveValue || 'false';
        }
        
        // Update typography buttons state
        this.updateTypographyButtons();
    }
    
    /**
     * מעבר למצב רספונסיבי חדש (נקרא מהמערכת הגלובלית)
     */
    onResponsiveModeChange(mode) {
        if (!this.isActive) return;
        
        // Update responsive tabs UI
        this.updateResponsiveTabsUI(mode);
        
        // Update typography mode switcher
        this.updateTypographyModeUI(mode);
        
        // Update forms (with slight delay to ensure UI is updated)
        setTimeout(() => {
            this.populateTypographyForms(mode);
            this.populateHeightForms(mode);
        }, 10);
        
        // Update placeholders for responsive inputs
        this.updatePlaceholders(mode);
        
        // Update preview
        this.builder.sendToIframe('updateHero', this.data);
        
        // Double-check: ensure forms are populated
        setTimeout(() => {
            this.populateTypographyForms(mode);
            this.populateHeightForms(mode);
        }, 50);
    }
    
    /**
     * עדכון placeholders עבור שדות רספונסיביים
     */
    updatePlaceholders(mode) {
        if (mode === 'desktop') {
            // Clear placeholders for desktop mode
            this.clearPlaceholders();
            return;
        }
        
        // Set placeholders to show desktop values
        this.setResponsivePlaceholders(mode);
        
        // Call global function if available
        if (window.updateTypographyPlaceholders) {
            window.updateTypographyPlaceholders();
        }
    }
    
    /**
     * ניקוי placeholders במצב מחשב
     */
    clearPlaceholders() {
        const inputs = [
            'heroTitleFontSize', 'heroTitleFontFamily', 
            'heroSubtitleFontSize', 'heroSubtitleFontFamily',
            'heroButtonFontSize', 'heroButtonFontFamily',
            'heroButtonPaddingTop', 'heroButtonPaddingBottom',
            'heroButtonPaddingRight', 'heroButtonPaddingLeft',
            'heroButtonMarginTop', 'heroButtonMarginBottom',
            'heroHeightType_tablet', 'heroHeightValue_tablet',
            'heroHeightType_mobile', 'heroHeightValue_mobile'
        ];
        
        inputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.placeholder = '';
            }
        });
    }
    
    /**
     * הגדרת placeholders רספונסיביים
     */
    setResponsivePlaceholders(mode) {
        const placeholderData = {
            'heroTitleFontSize': this.data.titleFontSize || '36',
            'heroTitleFontFamily': this.data.titleFontFamily || 'Noto Sans Hebrew',
            'heroSubtitleFontSize': this.data.subtitleFontSize || '18',
            'heroSubtitleFontFamily': this.data.subtitleFontFamily || 'Noto Sans Hebrew',
            'heroButtonFontSize': this.data.buttonFontSize || '16',
            'heroButtonFontFamily': this.data.buttonFontFamily || 'Noto Sans Hebrew',
            'heroButtonPaddingTop': this.data.buttonPaddingTop || '12',
            'heroButtonPaddingBottom': this.data.buttonPaddingBottom || '12',
            'heroButtonPaddingRight': this.data.buttonPaddingRight || '32',
            'heroButtonPaddingLeft': this.data.buttonPaddingLeft || '32',
            'heroButtonMarginTop': this.data.buttonMarginTop || '0',
            'heroButtonMarginBottom': this.data.buttonMarginBottom || '0',
            'heroHeightType_tablet': this.data.heightType || 'auto',
            'heroHeightValue_tablet': this.data.heightValue || '500',
            'heroHeightType_mobile': this.data.heightType || 'auto',
            'heroHeightValue_mobile': this.data.heightValue || '500'
        };
        
        Object.keys(placeholderData).forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.placeholder = `ברירת מחדל: ${placeholderData[id]}`;
            }
        });
    }
    
    /**
     * עדכון מוכן עם debounce למניעת ריצוד
     */
    updatePropertyDebounced(property, value, delay = 100) {
        // Cancel previous timer
        if (this.updateTimer) {
            clearTimeout(this.updateTimer);
        }
        
        // Update data immediately for UI responsiveness
        this.updatePropertyImmediate(property, value);
        
        // Debounce iframe update
        this.updateTimer = setTimeout(() => {
            this.builder.sendToIframe('updateHero', this.data);
            this.builder.markAsChanged();
        }, delay);
    }
    
    /**
     * עדכון מיידי של הנתונים ללא שליחה ל-iframe
     */
    updatePropertyImmediate(property, value) {
        const currentMode = window.currentResponsiveMode || 'desktop';
        
        if (currentMode !== 'desktop' && this.isTypographyProperty(property)) {
            const responsiveProperty = `${property}_${currentMode}`;
            
            if (value === '' || value === '0' || value === 0) {
                this.data[responsiveProperty] = '';
            } else {
                this.data[responsiveProperty] = value;
            }
        } else {
            this.data[property] = value;
        }
    }
    
    /**
     * עדכון מאפיין עם תמיכה במצבים רספונסיביים
     */
    updateProperty(property, value) {
        // Check if we're in responsive mode and this is a typography property
        const currentMode = window.currentResponsiveMode || 'desktop';
        
        if (currentMode !== 'desktop' && this.isTypographyProperty(property)) {
            // Update responsive property
            const responsiveProperty = `${property}_${currentMode}`;
            
            // Don't save empty values as "0" - leave them empty for fallback
            if (value === '' || value === '0' || value === 0) {
                this.data[responsiveProperty] = '';
            } else {
                this.data[responsiveProperty] = value;
            }
            console.log(`Updated responsive property ${responsiveProperty} to:`, this.data[responsiveProperty]);
        } else {
            // Update regular property
            this.data[property] = value;
            console.log(`Updated property ${property} to:`, value);
        }
        
        // שליחה ל-iframe
        this.builder.sendToIframe('updateHero', this.data);
        
        // שמירה אוטומטית
        this.builder.markAsChanged();
    }
    
    /**
     * בדיקה אם מאפיין הוא של טיפוגרפיה
     */
    isTypographyProperty(property) {
        const typographyProperties = [
            'titleFontSize', 'titleFontFamily', 'titleFontWeight', 'titleFontStyle', 'titleTextDecoration',
            'subtitleFontSize', 'subtitleFontFamily', 'subtitleFontWeight', 'subtitleFontStyle', 'subtitleTextDecoration',
            'buttonFontSize', 'buttonFontFamily', 'buttonFontWeight', 'buttonFontStyle', 'buttonTextDecoration', 
            'buttonPaddingTop', 'buttonPaddingBottom', 'buttonPaddingRight', 'buttonPaddingLeft',
            'buttonMarginTop', 'buttonMarginBottom', 'buttonShadow',
            'heightType', 'heightValue'
        ];
        
        return typographyProperties.includes(property);
    }
    
    /**
     * קבלת נתוני הסקשן
     */
    getData() {
        return this.data;
    }
}; // סוגר המחלקה הגלובלית