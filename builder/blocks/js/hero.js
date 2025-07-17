/**
 * Hero Section - מחלקה מודולרית להגדרות Hero
 */
window.HeroSection = class HeroSection {
    constructor(builder) {
        this.builder = builder;
        this.data = this.getDefaultData();
        this.isActive = false;
        this.updateTimer = null;
        this.buttonsRepeater = null;
        this._buttonsRepeaterSetup = false;
        this._buttonsRepeaterInitialized = false;
    }
    
    /**
     * נתוני ברירת מחדל עבור Hero
     */
    getDefaultData() {
        return {
            // Content
            title: 'ברוכים הבאים לחנות שלנו',
            subtitle: 'גלה את המוצרים הטובים ביותר במחירים הכי טובים',
            buttons: [{
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
            }],
            
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
            bgVideo: '',
            bgMobileVideo: '',
            bgTabletVideo: '',
            bgOverlay: false,
            bgOverlayColor: '#000000',
            bgOverlayOpacity: 0.4,
            
            // Layout
            containerType: 'container',
            paddingType: 'default',
            widthType: 'full',
            heightType: 'auto',
            heightValue: 500,
            minHeight: '',
            maxHeight: '',
            
            // Content alignment
            textAlign: 'center',
            verticalAlign: 'center',
            contentPosition: 'center-center',
            
            // Typography
            ...window.TypographyManager.getDefaultValues(),
            
            // Colors
            titleColor: '#1F2937',
            subtitleColor: '#6B7280',
            buttonBgColor: '#3B82F6',
            buttonTextColor: '#FFFFFF',
            buttonBorderColor: '#3B82F6',
            
            // Spacing
            titleMarginBottom: 16,
            subtitleMarginBottom: 24,
            buttonsMarginTop: 32,
            
            // Effects
            parallax: false,
            parallaxSpeed: 0.5,
            animation: 'none',
            animationDelay: 0,
            
            // Responsive
            hideMobile: false,
            hideTablet: false,
            hideDesktop: false
        };
    }
    
    /**
     * טעינת נתוני הסקשן
     */
    async loadData() {
        try {
            const response = await fetch('api/pages.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'load',
                    page_type: 'home'
                })
            });
            
            const result = await response.json();
            console.log('Hero loadData response:', result);
            
            if (result.success && result.data && result.data.sections) {
                const heroSection = result.data.sections.find(section => section.type === 'hero');
                if (heroSection && heroSection.data) {
                    this.data = { ...this.getDefaultData(), ...heroSection.data };
                    console.log('Hero data loaded:', this.data);
                } else {
                    console.log('No hero section found, using defaults');
                }
            } else {
                console.log('No page data found, using defaults');
            }
        } catch (error) {
            console.error('Failed to load hero data:', error);
        }
    }
    
    /**
     * שמירת נתוני הסקשן
     */
    async saveData() {
        try {
            const response = await fetch('api/pages.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'save',
                    page_type: 'home',
                    sections: [{
                        type: 'hero',
                        id: 'hero_1',
                        order: 1,
                        data: this.data
                    }]
                })
            });
            
            const result = await response.json();
            if (result.success) {
                console.log('Hero data saved successfully');
                return true;
            } else {
                console.error('Failed to save hero data:', result.error);
                return false;
            }
        } catch (error) {
            console.error('Failed to save hero data:', error);
            return false;
        }
    }
    
    /**
     * פתיחת הגדרות Hero
     */
    async onOpen() {
        console.log('Opening Hero settings');
        
        this.isActive = true;
        window.currentSection = this;
        
        await this.loadData();
        
        setTimeout(() => {
            this.populateForm();
            this.setupEventListeners();
            this.builder.sendToIframe('updateHero', this.data);
        }, 150);
    }
    
    /**
     * סגירת הגדרות Hero
     */
    async onClose() {
        console.log('Closing Hero settings');
        
        this.isActive = false;
        this._buttonsRepeaterSetup = false;
        this._buttonsRepeaterInitialized = false;
        
        // Reset responsive mode to desktop when closing
        if (window.setResponsiveMode) {
            window.setResponsiveMode('desktop');
        }
        
        window.currentSection = null;
        
        this.removeEventListeners();
        this.builder.slideToSections();
    }
    
    /**
     * מילוי הטופס בנתונים
     */
    populateForm() {
        const form = document.getElementById('heroForm');
        if (!form) return;
        
        // Content fields
        this.setFieldValue('heroTitle', this.data.title);
        this.setFieldValue('heroSubtitle', this.data.subtitle);
        
        // Background
        this.setFieldValue('heroBgType', this.data.bgType);
        this.setFieldValue('heroBgColor', this.data.bgColor);
        this.setFieldValue('heroBgGradient1', this.data.bgGradient1);
        this.setFieldValue('heroBgGradient2', this.data.bgGradient2);
        this.setFieldValue('heroBgGradientDirection', this.data.bgGradientDirection);
        this.setFieldValue('heroBgImage', this.data.bgImage);
        this.setFieldValue('heroBgVideo', this.data.bgVideo);
        this.setFieldValue('heroBgMobileVideo', this.data.bgMobileVideo);
        this.setFieldValue('heroBgTabletVideo', this.data.bgTabletVideo);
        
        // Typography
        this.setFieldValue('heroTitleFontSize', this.data.titleFontSize);
        this.setFieldValue('heroTitleFontFamily', this.data.titleFontFamily);
        this.setFieldValue('heroTitleFontWeight', this.data.titleFontWeight);
        this.setFieldValue('heroSubtitleFontSize', this.data.subtitleFontSize);
        this.setFieldValue('heroSubtitleFontFamily', this.data.subtitleFontFamily);
        this.setFieldValue('heroSubtitleFontWeight', this.data.subtitleFontWeight);
        
        // Layout
        this.setFieldValue('heroContentPosition', this.data.contentPosition);
        this.setFieldValue('heroHeightType', this.data.heightType);
        this.setFieldValue('heroHeightValue', this.data.heightValue);
        
        // Colors
        this.setFieldValue('heroTitleColor', this.data.titleColor);
        this.setFieldValue('heroSubtitleColor', this.data.subtitleColor);
        
        // Buttons
        this.setupButtonsRepeater();
        
        // עדכון כפתורים פעילים
        this.updateActiveButtons();
    }
    
    /**
     * הגדרת ערך לשדה
     */
    setFieldValue(fieldId, value) {
        const field = document.getElementById(fieldId);
        if (field) {
            if (field.type === 'checkbox') {
                field.checked = value;
            } else {
                field.value = value || '';
            }
        }
    }
    
    /**
     * הגדרת Event Listeners
     */
    setupEventListeners() {
        if (!this.isActive) return;
        
        const form = document.getElementById('heroForm');
        if (!form) return;
        
        // Input fields
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.removeEventListener('input', this.handleInputChange);
            input.removeEventListener('change', this.handleInputChange);
            input.addEventListener('input', this.handleInputChange.bind(this));
            input.addEventListener('change', this.handleInputChange.bind(this));
        });
        
        // Position buttons
        this.setupPositionButtons();
        
        // Background type buttons
        this.setupBackgroundTypeButtons();
        
        // Height controls
        this.setupHeightControls();
        
        // Buttons repeater
        if (!this._buttonsRepeaterSetup) {
            this.setupButtonsRepeater();
            this._buttonsRepeaterSetup = true;
        }
        
        console.log('Hero event listeners setup completed');
    }
    
    /**
     * טיפול בשינוי שדות
     */
    handleInputChange(event) {
        const field = event.target;
        const property = this.getPropertyFromFieldId(field.id);
        let value = field.type === 'checkbox' ? field.checked : field.value;
        
        if (property) {
            this.updatePropertyDebounced(property, value);
        }
    }
    
    /**
     * קבלת שם מאפיין מזהה שדה
     */
    getPropertyFromFieldId(fieldId) {
        const fieldMap = {
            'heroTitle': 'title',
            'heroSubtitle': 'subtitle',
            'heroBgType': 'bgType',
            'heroBgColor': 'bgColor',
            'heroBgGradient1': 'bgGradient1',
            'heroBgGradient2': 'bgGradient2',
            'heroBgGradientDirection': 'bgGradientDirection',
            'heroBgImage': 'bgImage',
            'heroBgVideo': 'bgVideo',
            'heroBgMobileVideo': 'bgMobileVideo',
            'heroBgTabletVideo': 'bgTabletVideo',
            'heroTitleFontSize': 'titleFontSize',
            'heroTitleFontFamily': 'titleFontFamily',
            'heroTitleFontWeight': 'titleFontWeight',
            'heroSubtitleFontSize': 'subtitleFontSize',
            'heroSubtitleFontFamily': 'subtitleFontFamily',
            'heroSubtitleFontWeight': 'subtitleFontWeight',
            'heroContentPosition': 'contentPosition',
            'heroHeightType': 'heightType',
            'heroHeightValue': 'heightValue',
            'heroTitleColor': 'titleColor',
            'heroSubtitleColor': 'subtitleColor'
        };
        
        return fieldMap[fieldId] || null;
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
    
    handlePositionClick(event) {
        const position = event.target.dataset.position;
        if (position) {
            this.updateProperty('contentPosition', position);
            this.updateActiveButtons();
        }
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
    
    handleBgTypeClick(event) {
        const bgType = event.target.dataset.bgType;
        if (bgType) {
            this.updateProperty('bgType', bgType);
            this.updateActiveButtons();
        }
    }
    
    /**
     * הגדרת בקרי גובה
     */
    setupHeightControls() {
        const heightTypeRadios = document.querySelectorAll('input[name="heroHeightType"]');
        heightTypeRadios.forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.updateProperty('heightType', e.target.value);
            });
        });
    }
    
    /**
     * הגדרת ריפיטר כפתורים
     */
    setupButtonsRepeater() {
        if (this._buttonsRepeaterInitialized) return;
        
        try {
            this.buttonsRepeater = new window.ButtonsRepeater(
                'buttonsContainer',
                'heroButtons',
                this.data.buttons || [],
                {
                    minButtons: 1,
                    maxButtons: 5,
                    showIcons: true,
                    showHover: true
                }
            );
            
            // Listen for buttons changes
            const buttonsInput = document.getElementById('heroButtons');
            if (buttonsInput) {
                buttonsInput.addEventListener('change', () => {
                    this.data.buttons = this.buttonsRepeater.getButtons();
                    this.builder.sendToIframe('updateHero', this.data);
                    this.builder.markAsChanged();
                });
            }
            
            this._buttonsRepeaterInitialized = true;
            console.log('Buttons repeater initialized');
            
        } catch (error) {
            console.error('Failed to initialize buttons repeater:', error);
        }
    }
    
    /**
     * עדכון כפתורים פעילים
     */
    updateActiveButtons() {
        // Position buttons
        document.querySelectorAll('.position-btn').forEach(btn => {
            if (btn.dataset.position === this.data.contentPosition) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Background type buttons
        document.querySelectorAll('.bg-type-btn').forEach(btn => {
            if (btn.dataset.bgType === this.data.bgType) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
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
    }
    
    /**
     * עדכון מאפיין עם debounce
     */
    updatePropertyDebounced(property, value, delay = 100) {
        if (this.updateTimer) {
            clearTimeout(this.updateTimer);
        }
        
        this.updatePropertyImmediate(property, value);
        
        this.updateTimer = setTimeout(() => {
            this.builder.sendToIframe('updateHero', this.data);
            this.builder.markAsChanged();
        }, delay);
    }
    
    /**
     * עדכון מיידי של מאפיין
     */
    updatePropertyImmediate(property, value) {
        const currentMode = window.currentResponsiveMode || 'desktop';
        
        if (currentMode !== 'desktop' && window.TypographyManager && window.TypographyManager.isTypographyProperty(property)) {
            this.data = window.TypographyManager.updateProperty(this.data, property, value, currentMode);
        } else {
            this.data[property] = value;
        }
    }
    
    /**
     * עדכון מאפיין
     */
    updateProperty(property, value) {
        const currentMode = window.currentResponsiveMode || 'desktop';
        
        if (currentMode !== 'desktop' && window.TypographyManager && window.TypographyManager.isTypographyProperty(property)) {
            this.data = window.TypographyManager.updateProperty(this.data, property, value, currentMode);
        } else {
            this.data[property] = value;
        }
        
        this.builder.sendToIframe('updateHero', this.data);
        this.builder.markAsChanged();
    }
    
    /**
     * עדכון placeholders רספונסיביים
     */
    updatePlaceholders(mode) {
        if (window.TypographyManager) {
            window.TypographyManager.updatePlaceholders(mode, 'hero');
        }
    }
    
    /**
     * קבלת נתוני הסקשן
     */
    getData() {
        return this.data;
    }
    
    /**
     * הגדרת נתוני הסקשן
     */
    setData(data) {
        this.data = { ...this.getDefaultData(), ...data };
        if (this.isActive) {
            this.populateForm();
        }
    }
}; 