/**
 * מנהל הגדרות הסקשנים
 */
class SectionsSettingsUI {
    constructor(sectionsManager) {
        this.sectionsManager = sectionsManager;
        this.currentSection = null;
        this.originalSettings = {};
    }

    /**
     * הצגת הגדרות הסקשן
     */
    showSectionSettings(sectionId) {
        console.log('Showing settings for section:', sectionId);
        
        const section = this.sectionsManager.sections.find(s => s.id === sectionId);
        if (!section) {
            console.error('Section not found:', sectionId);
            console.log('Available sections:', this.sectionsManager.sections.map(s => s.id));
            return;
        }

        this.currentSection = section;
        this.originalSettings = JSON.parse(JSON.stringify(section.settings || {}));

        const container = document.getElementById('sections-container');
        if (!container) {
            console.error('Sections container not found');
            return;
        }

        // יצירת HTML להגדרות
        const settingsHTML = this.generateSettingsHTML(section);
        container.innerHTML = settingsHTML;

        // הוספת event listeners
        this.setupSettingsEventListeners(section);
    }

    /**
     * יצירת HTML להגדרות בהתאם לסוג הסקשן
     */
    generateSettingsHTML(section) {
        const settings = section.settings || {};
        const sectionType = section.type;

        let settingsForm = '';

        switch (sectionType) {
            case 'hero':
                settingsForm = this.generateHeroSettings(settings);
                break;
            case 'featured-products':
                settingsForm = this.generateFeaturedProductsSettings(settings);
                break;
            case 'header':
                settingsForm = this.generateHeaderSettings(settings);
                break;
            case 'footer':
                settingsForm = this.generateFooterSettings(settings);
                break;
            default:
                settingsForm = '<p class="text-gray-500">אין הגדרות זמינות עבור סקשן זה</p>';
        }

        return `
            <div class="p-6">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <button id="back-to-sections" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors ml-3">
                            <i class="ri-arrow-right-line"></i>
                        </button>
                        <h2 class="text-lg font-semibold text-gray-900">הגדרות ${this.getSectionTitle(sectionType)}</h2>
                    </div>
                </div>

                <!-- Settings Form -->
                <div class="space-y-6">
                    ${settingsForm}
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex gap-3">
                    <button id="cancel-settings" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        חזרה לרשימת סקשנים
                    </button>
                </div>
            </div>
        `;
    }

    /**
     * יצירת הגדרות עבור Hero
     */
    generateHeroSettings(settings) {
        // שימוש במנהל ההגדרות המתקדם של Hero
        const heroSettingsManager = new HeroSettingsManager();
        return heroSettingsManager.generateHeroSettingsForm(settings);
    }

    /**
     * יצירת הגדרות עבור Featured Products
     */
    generateFeaturedProductsSettings(settings) {
        return `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">כותרת הסקשן</label>
                    <input type="text" id="featured-title" value="${settings.title || 'מוצרים מומלצים'}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">מספר מוצרים להצגה</label>
                    <input type="number" id="featured-count" value="${settings.products_count || 4}" min="1" max="12"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">מספר עמודות</label>
                    <select id="featured-columns" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="2" ${settings.columns == 2 ? 'selected' : ''}>2 עמודות</option>
                        <option value="3" ${settings.columns == 3 ? 'selected' : ''}>3 עמודות</option>
                        <option value="4" ${settings.columns == 4 ? 'selected' : ''}>4 עמודות</option>
                    </select>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="featured-show-price" ${settings.show_price !== false ? 'checked' : ''} 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="featured-show-price" class="mr-2 block text-sm text-gray-900">הצג מחירים</label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="featured-show-cart" ${settings.show_add_to_cart !== false ? 'checked' : ''} 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="featured-show-cart" class="mr-2 block text-sm text-gray-900">הצג כפתור הוספה לעגלה</label>
                </div>
            </div>
        `;
    }

    /**
     * יצירת הגדרות עבור Header
     */
    generateHeaderSettings(settings) {
        return `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">שם החנות</label>
                    <input type="text" id="header-store-name" value="${settings.store_name || 'החנות שלי'}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="header-show-search" ${settings.show_search !== false ? 'checked' : ''} 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="header-show-search" class="mr-2 block text-sm text-gray-900">הצג חיפוש</label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="header-show-cart" ${settings.show_cart !== false ? 'checked' : ''} 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="header-show-cart" class="mr-2 block text-sm text-gray-900">הצג עגלת קניות</label>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">צבע רקע</label>
                        <input type="color" id="header-bg-color" value="${settings.background_color || '#ffffff'}" 
                               class="w-full h-10 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">צבע טקסט</label>
                        <input type="color" id="header-text-color" value="${settings.text_color || '#1f2937'}" 
                               class="w-full h-10 border border-gray-300 rounded-lg">
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * יצירת הגדרות עבור Footer
     */
    generateFooterSettings(settings) {
        return `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">טקסט זכויות יוצרים</label>
                    <input type="text" id="footer-copyright" value="${settings.copyright || '© 2024 החנות שלי. כל הזכויות שמורות.'}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">קישור פייסבוק</label>
                    <input type="url" id="footer-facebook" value="${settings.facebook_link || ''}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">קישור אינסטגרם</label>
                    <input type="url" id="footer-instagram" value="${settings.instagram_link || ''}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">אימייל ליצירת קשר</label>
                    <input type="email" id="footer-email" value="${settings.contact_email || ''}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">טלפון ליצירת קשר</label>
                    <input type="tel" id="footer-phone" value="${settings.contact_phone || ''}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">צבע רקע</label>
                        <input type="color" id="footer-bg-color" value="${settings.background_color || '#1f2937'}" 
                               class="w-full h-10 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">צבע טקסט</label>
                        <input type="color" id="footer-text-color" value="${settings.text_color || '#ffffff'}" 
                               class="w-full h-10 border border-gray-300 rounded-lg">
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * הגדרת event listeners להגדרות
     */
    setupSettingsEventListeners(section) {
        // כפתור חזרה
        const backBtn = document.getElementById('back-to-sections');
        if (backBtn) {
            backBtn.addEventListener('click', () => {
                this.sectionsManager.showSectionsList();
            });
        }

        // כפתור ביטול/חזרה
        const cancelBtn = document.getElementById('cancel-settings');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                this.cancelSettings();
            });
        }

        // הגדרת event listeners ספציפיים לסוג הסקשן
        if (section.type === 'hero') {
            // שימוש באינסטנס הגלובלי
            if (window.heroSettingsManager) {
                window.heroSettingsManager.setupEventListeners();
            }
        }
        
        // Real-time preview updates - אחרי יצירת השדות
        setTimeout(() => {
            this.setupRealTimePreview(section);
        }, 100);
    }

    /**
     * הגדרת תצוגה מקדימה בזמן אמת
     */
    setupRealTimePreview(section) {
        const inputs = document.querySelectorAll('input, select, textarea');
        
        // משתנה לשמירת timeout
        let updateTimeout;
        
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                // ביטול timeout קיים
                if (updateTimeout) {
                    clearTimeout(updateTimeout);
                }
                
                // הגדרת timeout חדש עם השהיה של 300ms
                updateTimeout = setTimeout(() => {
                    this.updatePreview();
                }, 300);
            });
        });
    }

    /**
     * החלת ההגדרות
     */
    async applySettings() {
        if (!this.currentSection) return;

        const newSettings = this.collectSettings();
        console.log('Applying settings:', newSettings);
        
        this.currentSection.settings = newSettings;

        // עדכון הדטאבייס
        this.sectionsManager.markAsChanged();
        
        // שמירה מיידית למסד הנתונים
        try {
            await this.sectionsManager.saveToDatabase();
            this.showSuccessMessage('הגדרות הסקשן נשמרו בהצלחה');
        } catch (error) {
            console.error('Error saving settings:', error);
            this.showErrorMessage('שגיאה בשמירת ההגדרות');
            return;
        }
        
        // חזרה לרשימת הסקשנים
        this.sectionsManager.showSectionsList();
    }

    /**
     * חזרה לרשימת הסקשנים (בלי שמירת שינויים)
     */
    cancelSettings() {
        // החזרה לרשימת הסקשנים בלי שמירת שינויים
        this.sectionsManager.showSectionsList();
    }

    /**
     * איסוף ההגדרות מהטופס
     */
    collectSettings() {
        const settings = {};
        const sectionType = this.currentSection.type;

        switch (sectionType) {
            case 'hero':
                // שימוש במנהל ההגדרות המתקדם של Hero
                if (window.heroSettingsManager) {
                    const heroSettings = window.heroSettingsManager.collectSettings();
                    return heroSettings;
                }
                return {};

            case 'featured-products':
                settings.title = document.getElementById('featured-title')?.value || '';
                settings.products_count = parseInt(document.getElementById('featured-count')?.value) || 4;
                settings.columns = parseInt(document.getElementById('featured-columns')?.value) || 4;
                settings.show_price = document.getElementById('featured-show-price')?.checked || false;
                settings.show_add_to_cart = document.getElementById('featured-show-cart')?.checked || false;
                break;

            case 'header':
                settings.store_name = document.getElementById('header-store-name')?.value || '';
                settings.show_search = document.getElementById('header-show-search')?.checked || false;
                settings.show_cart = document.getElementById('header-show-cart')?.checked || false;
                settings.background_color = document.getElementById('header-bg-color')?.value || '#ffffff';
                settings.text_color = document.getElementById('header-text-color')?.value || '#1f2937';
                break;

            case 'footer':
                settings.copyright = document.getElementById('footer-copyright')?.value || '';
                settings.facebook_link = document.getElementById('footer-facebook')?.value || '';
                settings.instagram_link = document.getElementById('footer-instagram')?.value || '';
                settings.contact_email = document.getElementById('footer-email')?.value || '';
                settings.contact_phone = document.getElementById('footer-phone')?.value || '';
                settings.background_color = document.getElementById('footer-bg-color')?.value || '#1f2937';
                settings.text_color = document.getElementById('footer-text-color')?.value || '#ffffff';
                break;
        }

        return settings;
    }

    /**
     * עדכון התצוגה המקדימה
     */
    updatePreview() {
        if (!this.currentSection) return;

        const newSettings = this.collectSettings();
        const tempSection = { ...this.currentSection, settings: newSettings };
        
        // עדכון הסקשן בתצוגה המקדימה (בלי שמירה למסד נתונים)
        this.sectionsManager.updateSectionInPreview(tempSection);
        
        // עדכון ההגדרות הזמניות בסקשן (בזיכרון בלבד)
        this.currentSection.settings = newSettings;
        
        // סימון שיש שינויים שלא נשמרו
        this.sectionsManager.markAsChanged();
    }

    /**
     * קבלת כותרת הסקשן
     */
    getSectionTitle(sectionType) {
        const titles = {
            'hero': 'Hero',
            'featured-products': 'מוצרים מומלצים',
            'header': 'Header',
            'footer': 'Footer'
        };
        return titles[sectionType] || sectionType;
    }

    /**
     * הצגת הודעת הצלחה
     */
    showSuccessMessage(message) {
        // יצירת הודעת הצלחה זמנית
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    /**
     * הצגת הודעת שגיאה
     */
    showErrorMessage(message) {
        // יצירת הודעת שגיאה זמנית
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
} 