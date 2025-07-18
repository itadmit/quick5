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
            console.log(`=== Opening section settings for: ${sectionType} ===`);
            
            // טעינת הגדרות ה-UI
            console.log('Step 1: Loading settings UI...');
            await this.loadSectionSettingsUI(sectionType);
            
            // הזזה לפאנל ההגדרות
            console.log('Step 2: Sliding to settings panel...');
            this.builder.slideToSettings();
            
            // טעינת הסקשן אם לא נטען
            console.log('Step 3: Loading section instance...');
            const section = await this.loadSection(sectionType);
            
            // סגירת הסקשן הקודם
            if (this.currentSection) {
                console.log('Step 4: Closing previous section...');
                await this.currentSection.onClose();
            }
            
            this.currentSection = section;
            window.currentSection = section; // Make it globally available
            
            // פתיחת ההגדרות
            console.log('Step 5: Opening section...');
            await section.onOpen();
            
            console.log(`=== Successfully opened settings for: ${sectionType} ===`);
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
            console.log(`Loading settings UI for ${sectionType}`);
            const response = await fetch(`settings/${sectionType}-settings.php`);
            console.log(`Response status: ${response.status}`);
            
            if (!response.ok) {
                throw new Error(`Failed to load settings UI for ${sectionType}: ${response.status}`);
            }
            
            const html = await response.text();
            console.log(`HTML loaded, length: ${html.length}`);
            
            const settingsContainer = document.getElementById('settingsContent');
            if (!settingsContainer) {
                throw new Error('Settings content container not found');
            }
            
            settingsContainer.innerHTML = html;
            console.log('HTML content set');
            
            // Execute any scripts in the loaded content
            this.executeScriptsInContent(settingsContainer);
            
            debugLog(`Loaded settings UI for: ${sectionType}`);
            
        } catch (error) {
            console.error(`Error loading settings UI for ${sectionType}:`, error);
            // Fallback to basic settings
            const settingsContainer = document.getElementById('settingsContent');
            if (settingsContainer) {
                settingsContainer.innerHTML = `
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <button id="backButton" class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                <i class="ri-arrow-right-line"></i>
                            </button>
                            <h3>הגדרות ${sectionType}</h3>
                        </div>
                        <p class="text-gray-500">שגיאה בטעינת הגדרות ${sectionType}: ${error.message}</p>
                    </div>
                `;
            }
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
                if (!script.parentNode) return; // Skip if no parent
                
                // Create new script element to ensure execution
                const newScript = document.createElement('script');
                
                if (script.src) {
                    // External script - load it
                    newScript.src = script.src;
                } else {
                    // Inline script - copy content and validate
                    const scriptContent = script.textContent || script.innerHTML;
                    if (scriptContent && scriptContent.trim()) {
                        // Basic validation - check for obvious syntax issues
                        if (scriptContent.includes('<') && !scriptContent.includes('<!--')) {
                            console.warn('Skipping script with HTML content:', scriptContent.substring(0, 100));
                            return;
                        }
                        newScript.textContent = scriptContent;
                    }
                }
                
                // Only replace if we have valid content
                if (newScript.src || newScript.textContent) {
                    script.parentNode.replaceChild(newScript, script);
                    debugLog('Executed script in dynamically loaded content');
                }
            } catch (error) {
                console.error('Error executing script:', error);
                // Remove the problematic script instead of crashing
                try {
                    if (script.parentNode) {
                        script.parentNode.removeChild(script);
                    }
                } catch (removeError) {
                    console.error('Error removing problematic script:', removeError);
                }
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
        
        // חזרה לפאנל הסקשנים
        this.builder.slideToSections();
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