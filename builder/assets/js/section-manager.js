/**
 * Section Manager - טעינה דינמית של סקשנים
 */

class SectionManager {
    constructor(builder) {
        this.builder = builder;
        this.loadedSections = new Map();
        this.currentSection = null;
    }
    
    /**
     * טעינת סקשן בצורה דינמית
     */
    async loadSection(sectionType) {
        // אם הסקשן כבר נטען, פשוט נשתמש בו
        if (this.loadedSections.has(sectionType)) {
            debugLog(`Section ${sectionType} already loaded`);
            return this.loadedSections.get(sectionType);
        }
        
        try {
            debugLog(`Loading section: ${sectionType}`);
            
            const sectionClassName = this.getSectionClassName(sectionType);
            
            // בדיקה אם המחלקה כבר קיימת
            if (!window[sectionClassName]) {
                // טעינת הקובץ JS של הסקשן
                const script = document.createElement('script');
                script.src = `blocks/js/${sectionType}.js?t=${Date.now()}`;
                
                // המתנה לטעינה
                await new Promise((resolve, reject) => {
                    script.onload = () => {
                        debugLog(`Script ${sectionType}.js loaded`);
                        resolve();
                    };
                    script.onerror = () => reject(new Error(`Failed to load ${sectionType}.js`));
                    document.head.appendChild(script);
                });
                
                // המתנה קצרה למחלקה להיטען
                await new Promise(resolve => setTimeout(resolve, 100));
            }
            
            // יצירת אינסטנס של הסקשן
            if (window[sectionClassName]) {
                const sectionInstance = new window[sectionClassName](this.builder);
                this.loadedSections.set(sectionType, sectionInstance);
                debugLog(`Section ${sectionType} loaded successfully`);
                return sectionInstance;
            } else {
                throw new Error(`Section class ${sectionClassName} not found after loading script`);
            }
            
        } catch (error) {
            console.error(`Error loading section ${sectionType}:`, error);
            throw error;
        }
    }
    
    /**
     * פתיחת הגדרות סקשן
     */
    async openSectionSettings(sectionType) {
        try {
            // טעינת הגדרות ה-UI
            await this.loadSectionSettingsUI(sectionType);
            
            // טעינת הסקשן אם לא נטען
            const section = await this.loadSection(sectionType);
            
            // סגירת הסקשן הקודם
            if (this.currentSection) {
                await this.currentSection.onClose();
            }
            
            this.currentSection = section;
            window.currentSection = section; // Make it globally available
            
            // פתיחת ההגדרות
            await section.onOpen();
            
            debugLog(`Opened settings for section: ${sectionType}`);
            
        } catch (error) {
            console.error(`Error opening section ${sectionType}:`, error);
            this.builder.showStatusMessage(`שגיאה בטעינת הסקשן: ${sectionType}`, 'error');
        }
    }
    
    /**
     * טעינת ממשק ההגדרות לסקשן
     */
    async loadSectionSettingsUI(sectionType) {
        try {
            const response = await fetch(`settings/${sectionType}-settings.php`);
            if (!response.ok) {
                throw new Error(`Failed to load settings UI for ${sectionType}`);
            }
            
            const html = await response.text();
            document.getElementById('settingsContent').innerHTML = html;
            
            // Execute any scripts in the loaded content
            this.executeScriptsInContent(document.getElementById('settingsContent'));
            
            debugLog(`Loaded settings UI for: ${sectionType}`);
            
        } catch (error) {
            console.error(`Error loading settings UI for ${sectionType}:`, error);
            // Fallback to basic settings
            document.getElementById('settingsContent').innerHTML = `
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <button id="backButton" class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="ri-arrow-right-line"></i>
                        </button>
                        <h3>הגדרות ${sectionType}</h3>
                    </div>
                    <p class="text-gray-500">הגדרות ${sectionType} לא נמצאו</p>
                </div>
            `;
        }
    }
    
    /**
     * ביצוע סקריפטים בתוכן שנטען דינמית
     */
    executeScriptsInContent(container) {
        if (!container) return;
        
        const scripts = container.querySelectorAll('script');
        scripts.forEach(script => {
            try {
                // Create new script element to ensure execution
                const newScript = document.createElement('script');
                
                if (script.src) {
                    // External script - load it
                    newScript.src = script.src;
                } else {
                    // Inline script - copy content
                    newScript.textContent = script.textContent;
                }
                
                // Replace old script with new one
                script.parentNode.replaceChild(newScript, script);
                
                debugLog('Executed script in dynamically loaded content');
            } catch (error) {
                console.error('Error executing script:', error);
            }
        });
    }
    
    /**
     * סגירת הגדרות הסקשן הנוכחי
     */
    async closeSectionSettings() {
        if (this.currentSection) {
            await this.currentSection.onClose();
            this.currentSection = null;
            window.currentSection = null; // Clear global reference
            debugLog('Closed current section settings');
        }
    }
    
    /**
     * קבלת שם המחלקה על בסיס סוג הסקשן
     */
    getSectionClassName(sectionType) {
        // המרה מ-kebab-case ל-PascalCase
        return sectionType
            .split('-')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join('') + 'Section';
    }
    
    /**
     * קבלת הסקשן הנוכחי
     */
    getCurrentSection() {
        return this.currentSection;
    }
    
    /**
     * בדיקה אם סקשן נטען
     */
    isSectionLoaded(sectionType) {
        return this.loadedSections.has(sectionType);
    }
} 