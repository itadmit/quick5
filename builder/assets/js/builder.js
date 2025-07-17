/**
 * Builder JavaScript - מנהל כללי עם ארכיטקטורה מודולרית
 */

// Debug mode - שנה ל-true כדי לראות לוגים מפורטים
const DEBUG_MODE = false;

// פונקציית debug
const debugLog = (...args) => {
    if (DEBUG_MODE) {
        console.log('[Builder Debug]', ...args);
    }
};

class SimpleBuilder {
    constructor() {
        this.iframe = null;
        this.isFrameReady = false;
        this.hasChanges = false;
        this.sectionManager = null;
        
        this.init();
    }
    
    init() {
        // Get iframe reference
        this.iframe = document.getElementById('previewFrame');
        
        if (!this.iframe) {
            console.error('Preview iframe not found!');
            return;
        }
        
        debugLog('Builder initialized');
        
        // יצירת מנהל הסקשנים
        this.sectionManager = new SectionManager(this);
        
        // יצירת מנהל פעולות הסקשנים
        this.actionsManager = new SectionActionsManager(this);
        
        // Setup iframe communication
        this.setupIframeCommunication();
        
        // Setup global listeners
        this.setupGlobalListeners();
        
        // Setup save button
        this.setupSaveButton();
    }
    
    setupIframeCommunication() {
        // Listen for iframe load
        this.iframe.addEventListener('load', () => {
            this.isFrameReady = true;
            debugLog('Iframe loaded successfully');
            
            // Wait a bit for the iframe to fully initialize
            setTimeout(() => {
                // שליחת נתונים ראשוניים אם יש סקשן פעיל
                const currentSection = this.sectionManager.getCurrentSection();
                if (currentSection && currentSection.getData) {
                    this.sendToIframe('updateHero', currentSection.getData());
                }
            }, 500);
        });
        
        // Listen for messages from iframe
        window.addEventListener('message', (event) => {
            if (event.data.source === 'builderPreview') {
                this.handleIframeMessage(event.data);
            }
        });
    }
    
    setupGlobalListeners() {
        // Setup click listeners for sections management
        document.addEventListener('click', (e) => {
            // Settings button click
            if (e.target.closest('.settings-btn')) {
                const sectionType = e.target.closest('.settings-btn').dataset.section;
                this.openSectionSettings(sectionType);
            }
            
            // Back button click
            if (e.target.closest('#backButton')) {
                this.closeSectionSettings();
            }
        });
        
        debugLog('Global listeners setup completed');
    }
    
    setupSaveButton() {
        document.getElementById('saveBtn').addEventListener('click', () => {
            this.saveCurrentSection();
        });
    }
    
    /**
     * פתיחת הגדרות סקשן
     */
    async openSectionSettings(sectionType) {
        debugLog(`Opening settings for section: ${sectionType}`);
        
        try {
            await this.sectionManager.openSectionSettings(sectionType);
            
            // Highlight active section
            this.highlightActiveSection(sectionType);
            
        } catch (error) {
            console.error(`Error opening section ${sectionType}:`, error);
            this.showStatusMessage(`שגיאה בטעינת הסקשן: ${sectionType}`, 'error');
        }
    }
    
    /**
     * סגירת הגדרות סקשן
     */
    async closeSectionSettings() {
        debugLog('Closing section settings');
        
        await this.sectionManager.closeSectionSettings();
        
        // Remove highlights
        this.removeHighlights();
    }
    
    /**
     * הדגשת הסקשן הפעיל
     */
    highlightActiveSection(sectionType) {
        document.querySelectorAll('.section-item').forEach(item => {
            item.classList.remove('active');
        });
        
        const activeSection = document.querySelector(`[data-section="${sectionType}"]`);
        if (activeSection) {
            activeSection.classList.add('active');
        }
    }
    
    /**
     * הסרת הדגשות
     */
    removeHighlights() {
        document.querySelectorAll('.section-item').forEach(item => {
            item.classList.remove('active');
        });
    }
    
    /**
     * אנימציה להגדרות
     */
    slideToSettings() {
        const slidingContainer = document.getElementById('slidingContainer');
        slidingContainer.style.transform = 'translateX(50%)';
    }
    
    /**
     * אנימציה לסקשנים
     */
    slideToSections() {
        const slidingContainer = document.getElementById('slidingContainer');
        slidingContainer.style.transform = 'translateX(0%)';
    }
    
    /**
     * שליחת נתונים ל-iframe
     */
    sendToIframe(action, data) {
        if (!this.isFrameReady || !this.iframe.contentWindow) {
            debugLog('Iframe not ready, skipping message');
            return;
        }
        
        const message = {
            source: 'pageBuilder',
            action: action,
            data: data,
            responsiveMode: window.currentResponsiveMode || 'desktop'
        };
        
        debugLog('Sending to iframe:', action, 'with responsive mode:', message.responsiveMode);
        this.iframe.contentWindow.postMessage(message, '*');
    }
    
    /**
     * טיפול בהודעות מ-iframe
     */
    handleIframeMessage(message) {
        debugLog('Received from iframe:', message.action);
        
        switch (message.action) {
            case 'ready':
                debugLog('Iframe is ready, sending initial data');
                // שליחת נתונים ראשוניים אם יש סקשן פעיל
                const currentSection = this.sectionManager.getCurrentSection();
                if (currentSection && currentSection.getData) {
                    this.sendToIframe('updateHero', currentSection.getData());
                }
                break;
                
            case 'heroClicked':
                debugLog('Hero section was clicked');
                break;
        }
    }
    
    /**
     * שמירת הסקשן הנוכחי
     */
    async saveCurrentSection() {
        const currentSection = this.sectionManager.getCurrentSection();
        
        if (!currentSection || !currentSection.saveData) {
            debugLog('No active section to save');
            return;
        }
        
        const saveBtn = document.getElementById('saveBtn');
        
        // Update button state - keep icon, change text
        saveBtn.innerHTML = '<i class="ri-save-line"></i> שומר...';
        saveBtn.className = 'px-4 py-2 bg-gray-600 text-white rounded-lg saving flex items-center gap-2';
        saveBtn.disabled = true;
        
        try {
            await currentSection.saveData();
            this.markAsSaved();
            debugLog('Section data saved successfully');
            
        } catch (error) {
            console.error('Error saving section data:', error);
            this.showStatusMessage('שגיאה בשמירה: ' + error.message, 'error');
        } finally {
            // Reset button
            saveBtn.innerHTML = '<i class="ri-save-line"></i> שמור';
            saveBtn.className = 'px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2';
            saveBtn.disabled = false;
        }
    }
    
    /**
     * סימון שינויים
     */
    markAsChanged() {
        this.hasChanges = true;
        const saveBtn = document.getElementById('saveBtn');
        if (!saveBtn.disabled) {
            saveBtn.className = 'px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors flex items-center gap-2';
            saveBtn.innerHTML = '<i class="ri-save-line"></i> שמור שינויים';
        }
    }
    
    /**
     * סימון נשמר
     */
    markAsSaved() {
        this.hasChanges = false;
        const saveBtn = document.getElementById('saveBtn');
        saveBtn.className = 'px-4 py-2 bg-green-600 text-white rounded-lg saved';
        saveBtn.innerHTML = '<i class="ri-check-line"></i> נשמר';
        
        // Show success message
        this.showStatusMessage('שינויים נשמרו בהצלחה!', 'success');
        
        setTimeout(() => {
            saveBtn.className = 'px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2';
            saveBtn.innerHTML = '<i class="ri-save-line"></i> שמור';
        }, 2000);
    }
    
    /**
     * הצגת הודעת סטטוס
     */
    showStatusMessage(message, type = 'info') {
        // Remove existing message
        const existing = document.querySelector('.status-message');
        if (existing) {
            existing.remove();
        }
        
        // Create new message
        const statusEl = document.createElement('div');
        statusEl.className = `status-message ${type}`;
        statusEl.textContent = message;
        document.body.appendChild(statusEl);
        
        // Show message
        setTimeout(() => statusEl.classList.add('show'), 100);
        
        // Hide message after 3 seconds
        setTimeout(() => {
            statusEl.classList.remove('show');
            setTimeout(() => statusEl.remove(), 300);
        }, 3000);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.builder = new SimpleBuilder();
});

// Warn before leaving if unsaved changes
window.addEventListener('beforeunload', (e) => {
    if (window.builder && window.builder.hasChanges) {
        e.preventDefault();
        e.returnValue = 'יש לך שינויים שלא נשמרו. האם אתה בטוח שברצונך לעזוב?';
    }
}); 