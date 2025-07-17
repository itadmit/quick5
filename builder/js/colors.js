/**
 * Color Manager - ניהול צבעים כלליים
 */
window.ColorManager = {
    
    /**
     * המרת hex ל-RGB
     */
    hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    },
    
    /**
     * המרת RGB ל-hex
     */
    rgbToHex(r, g, b) {
        return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
    },
    
    /**
     * יצירת צבע עם שקיפות
     */
    addOpacity(color, opacity) {
        const rgb = this.hexToRgb(color);
        if (!rgb) return color;
        
        return `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${opacity})`;
    },
    
    /**
     * בדיקה אם צבע הוא בהיר או כהה
     */
    isLight(color) {
        const rgb = this.hexToRgb(color);
        if (!rgb) return true;
        
        // נוסחת luminance
        const luminance = (0.299 * rgb.r + 0.587 * rgb.g + 0.114 * rgb.b) / 255;
        return luminance > 0.5;
    },
    
    /**
     * קבלת צבע ניגודי
     */
    getContrastColor(bgColor) {
        return this.isLight(bgColor) ? '#000000' : '#FFFFFF';
    },
    
    /**
     * יצירת גרדיאנט CSS
     */
    createGradient(color1, color2, direction = 'to-b') {
        const directionMap = {
            'to-t': 'to top',
            'to-b': 'to bottom',
            'to-l': 'to left',
            'to-r': 'to right',
            'to-tl': 'to top left',
            'to-tr': 'to top right',
            'to-bl': 'to bottom left',
            'to-br': 'to bottom right'
        };
        
        const cssDirection = directionMap[direction] || 'to bottom';
        return `linear-gradient(${cssDirection}, ${color1}, ${color2})`;
    },
    
    /**
     * קבלת צבעי ברירת מחדל
     */
    getDefaultColors() {
        return {
            primary: '#3B82F6',
            secondary: '#6B7280',
            success: '#10B981',
            warning: '#F59E0B',
            danger: '#EF4444',
            info: '#3B82F6',
            light: '#F8FAFC',
            dark: '#1F2937',
            white: '#FFFFFF',
            black: '#000000'
        };
    },
    
    /**
     * אימות צבע hex חוקי
     */
    isValidHex(color) {
        return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(color);
    },
    
    /**
     * יצירת פלטת צבעים מצבע בסיס
     */
    generatePalette(baseColor) {
        const rgb = this.hexToRgb(baseColor);
        if (!rgb) return {};
        
        return {
            50: this.lighten(baseColor, 40),
            100: this.lighten(baseColor, 30),
            200: this.lighten(baseColor, 20),
            300: this.lighten(baseColor, 10),
            400: this.lighten(baseColor, 5),
            500: baseColor,
            600: this.darken(baseColor, 5),
            700: this.darken(baseColor, 10),
            800: this.darken(baseColor, 20),
            900: this.darken(baseColor, 30)
        };
    },
    
    /**
     * הבהרת צבע
     */
    lighten(color, percent) {
        const rgb = this.hexToRgb(color);
        if (!rgb) return color;
        
        const factor = percent / 100;
        const r = Math.min(255, Math.round(rgb.r + (255 - rgb.r) * factor));
        const g = Math.min(255, Math.round(rgb.g + (255 - rgb.g) * factor));
        const b = Math.min(255, Math.round(rgb.b + (255 - rgb.b) * factor));
        
        return this.rgbToHex(r, g, b);
    },
    
    /**
     * החשכת צבע
     */
    darken(color, percent) {
        const rgb = this.hexToRgb(color);
        if (!rgb) return color;
        
        const factor = percent / 100;
        const r = Math.max(0, Math.round(rgb.r * (1 - factor)));
        const g = Math.max(0, Math.round(rgb.g * (1 - factor)));
        const b = Math.max(0, Math.round(rgb.b * (1 - factor)));
        
        return this.rgbToHex(r, g, b);
    },
    
    /**
     * עדכון צבע ברכיב DOM
     */
    updateElementColor(element, property, color) {
        if (!element) return;
        
        switch(property) {
            case 'background':
                element.style.backgroundColor = color;
                break;
            case 'text':
                element.style.color = color;
                break;
            case 'border':
                element.style.borderColor = color;
                break;
            case 'shadow':
                element.style.boxShadow = `0 4px 6px ${this.addOpacity(color, 0.1)}`;
                break;
        }
    }
}; 