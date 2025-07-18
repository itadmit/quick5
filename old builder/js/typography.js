/**
 * Typography Manager - ניהול טיפוגרפיה כלליות
 */
window.TypographyManager = {
    
    /**
     * עדכון placeholders עבור שדות טיפוגרפיה רספונסיביים
     */
    updatePlaceholders(mode, sectionName = '') {
        if (mode === 'desktop') {
            this.clearPlaceholders(sectionName);
            return;
        }
        
        this.setResponsivePlaceholders(mode, sectionName);
    },
    
    /**
     * ניקוי placeholders במצב מחשב
     */
    clearPlaceholders(sectionName = '') {
        const selector = sectionName ? `[id*="${sectionName}"]` : 'input, select';
        const inputs = document.querySelectorAll(selector);
        
        inputs.forEach(input => {
            if (this.isTypographyField(input.id)) {
                input.placeholder = '';
            }
        });
    },
    
    /**
     * הגדרת placeholders רספונסיביים
     */
    setResponsivePlaceholders(mode, sectionName = '') {
        const selector = sectionName ? `[id*="${sectionName}"]` : 'input, select';
        const inputs = document.querySelectorAll(selector);
        
        inputs.forEach(input => {
            if (this.isTypographyField(input.id)) {
                const desktopValue = this.getDesktopValue(input.id);
                if (desktopValue) {
                    input.placeholder = `ברירת מחדל: ${desktopValue}`;
                }
            }
        });
    },
    
    /**
     * בדיקה אם שדה הוא של טיפוגרפיה
     */
    isTypographyField(fieldId) {
        const typographyFields = [
            'FontSize', 'FontFamily', 'FontWeight', 'FontStyle', 'TextDecoration',
            'LineHeight', 'TextTransform', 'LetterSpacing', 'WordSpacing'
        ];
        
        return typographyFields.some(field => fieldId.includes(field));
    },
    
    /**
     * קבלת ערך מחשב לשדה
     */
    getDesktopValue(fieldId) {
        const input = document.getElementById(fieldId);
        if (!input) return null;
        
        // נסה לקבל את הערך מהאינפוט או מנתוני הסקשן
        return input.value || input.getAttribute('data-desktop-value') || '';
    },
    
    /**
     * עדכון מאפיין טיפוגרפיה עם תמיכה רספונסיבית
     */
    updateProperty(sectionData, property, value, currentMode = 'desktop') {
        if (currentMode !== 'desktop' && this.isTypographyProperty(property)) {
            const responsiveProperty = `${property}_${currentMode}`;
            
            if (value === '' || value === '0' || value === 0) {
                sectionData[responsiveProperty] = '';
            } else {
                sectionData[responsiveProperty] = value;
            }
        } else {
            sectionData[property] = value;
        }
        
        return sectionData;
    },
    
    /**
     * בדיקה אם מאפיין הוא של טיפוגרפיה
     */
    isTypographyProperty(property) {
        const typographyProperties = [
            'titleFontSize', 'titleFontFamily', 'titleFontWeight', 'titleFontStyle', 'titleTextDecoration',
            'titleLineHeight', 'titleTextTransform', 'titleLetterSpacing',
            'subtitleFontSize', 'subtitleFontFamily', 'subtitleFontWeight', 'subtitleFontStyle', 'subtitleTextDecoration',
            'subtitleLineHeight', 'subtitleTextTransform', 'subtitleLetterSpacing',
            'buttonFontSize', 'buttonFontFamily', 'buttonFontWeight', 'buttonFontStyle', 'buttonTextDecoration',
            'buttonLineHeight', 'buttonTextTransform', 'buttonLetterSpacing',
            'textFontSize', 'textFontFamily', 'textFontWeight', 'textFontStyle', 'textTextDecoration',
            'textLineHeight', 'textTextTransform', 'textLetterSpacing'
        ];
        
        return typographyProperties.includes(property);
    },
    
    /**
     * קבלת ערכי טיפוגרפיה רספונסיביים
     */
    getResponsiveValue(sectionData, property, mode = 'desktop') {
        if (mode === 'desktop') {
            return sectionData[property] || '';
        }
        
        const responsiveProperty = `${property}_${mode}`;
        return sectionData[responsiveProperty] || sectionData[property] || '';
    },
    
    /**
     * הגדרת ערכי ברירת מחדל לטיפוגרפיה
     */
    getDefaultValues() {
        return {
            titleFontSize: '36',
            titleFontFamily: 'Noto Sans Hebrew',
            titleFontWeight: '700',
            titleFontStyle: 'normal',
            titleTextDecoration: 'none',
            titleLineHeight: '1.2',
            titleTextTransform: 'none',
            titleLetterSpacing: 'normal',
            
            subtitleFontSize: '18',
            subtitleFontFamily: 'Noto Sans Hebrew',
            subtitleFontWeight: '400',
            subtitleFontStyle: 'normal',
            subtitleTextDecoration: 'none',
            subtitleLineHeight: '1.5',
            subtitleTextTransform: 'none',
            subtitleLetterSpacing: 'normal',
            
            buttonFontSize: '16',
            buttonFontFamily: 'Noto Sans Hebrew',
            buttonFontWeight: '500',
            buttonFontStyle: 'normal',
            buttonTextDecoration: 'none',
            buttonLineHeight: '1.4',
            buttonTextTransform: 'none',
            buttonLetterSpacing: 'normal'
        };
    }
}; 