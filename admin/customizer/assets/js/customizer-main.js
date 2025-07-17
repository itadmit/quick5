/**
 * קסטומיזר ראשי - מנהל את כל הפונקציונליות
 */
class Customizer {
    constructor() {
        this.settings = {};
        this.previewFrame = null;
        this.currentTab = 'design';
        this.isLoading = false;
        this.debounceTimer = null;
        this.isMobileView = false;
        this.sectionsManager = null;
    }

    init() {
        console.log('Customizer.init() called');
        this.previewFrame = document.getElementById('preview-frame');
        this.sectionsManager = new SectionsManager(this);
        this.setupEventListeners();
        this.loadSettings();
        this.setupDeviceToggle();
        this.setupModals();
        this.setupColorPickers();
        this.setupRangeSliders();
        this.setupFileUploads();
        this.setupFormHandlers();
        // this.setupAutoSave(); // מבוטל - שמירה אוטומטית
        
        // טעינת הסקשנים
        console.log('About to call loadSections from customizer-main.js');
        this.sectionsManager.loadSections();
    }

    setupEventListeners() {
        // Main save button (לשמירת סקשנים)
        const mainSaveBtn = document.getElementById('save-changes');
        if (mainSaveBtn) {
            mainSaveBtn.addEventListener('click', () => {
                this.sectionsManager.saveToDatabase();
            });
        }

        // Theme settings save button (לשמירת הגדרות עיצוב)
        const themeBtn = document.getElementById('save-theme-changes');
        if (themeBtn) {
            themeBtn.addEventListener('click', () => {
                this.saveThemeSettings();
            });
        }

        // Cancel button
        const cancelBtn = document.getElementById('cancel-theme-changes');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                this.sectionsManager.cancelChanges();
            });
        }

        // Device toggle
        document.getElementById('desktop-view').addEventListener('click', () => {
            this.setDeviceView('desktop');
        });

        document.getElementById('mobile-view').addEventListener('click', () => {
            this.setDeviceView('mobile');
        });

        // Real-time preview updates
        this.setupRealTimeUpdates();

        // התראה לפני יציאה מהדף עם שינויים לא שמורים
        window.addEventListener('beforeunload', (e) => {
            if (this.sectionsManager.hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = 'יש לך שינויים לא שמורים. האם אתה בטוח שברצונך לצאת?';
                return e.returnValue;
            }
        });
    }

    setupDeviceToggle() {
        const desktopBtn = document.getElementById('desktop-view');
        const mobileBtn = document.getElementById('mobile-view');
        
        if (desktopBtn && mobileBtn) {
            desktopBtn.addEventListener('click', () => this.setDeviceView('desktop'));
            mobileBtn.addEventListener('click', () => this.setDeviceView('mobile'));
        }
    }

    setDeviceView(device) {
        this.isMobileView = device === 'mobile';
        
        // Update button states
        document.getElementById('desktop-view').classList.toggle('bg-blue-600', !this.isMobileView);
        document.getElementById('desktop-view').classList.toggle('text-white', !this.isMobileView);
        document.getElementById('mobile-view').classList.toggle('bg-blue-600', this.isMobileView);
        document.getElementById('mobile-view').classList.toggle('text-white', this.isMobileView);
        
        // Update preview frame
        const previewContainer = document.getElementById('preview-frame-container');
        if (previewContainer) {
            if (this.isMobileView) {
                previewContainer.style.width = '375px';
                previewContainer.style.height = '667px';
                previewContainer.style.margin = '0 auto';
                previewContainer.style.borderRadius = '20px';
                previewContainer.style.overflow = 'hidden';
            } else {
                previewContainer.style.width = '100%';
                previewContainer.style.height = '100%';
                previewContainer.style.margin = '0';
                previewContainer.style.borderRadius = '8px';
                previewContainer.style.overflow = 'visible';
            }
        }
    }

    setupModals() {
        // Theme settings modal
        const themeSettingsBtn = document.getElementById('theme-settings-btn');
        const themeSettingsModal = document.getElementById('theme-settings-modal');
        const closeThemeSettingsModal = document.getElementById('close-theme-settings-modal');

        if (themeSettingsBtn && themeSettingsModal) {
            themeSettingsBtn.addEventListener('click', () => {
                themeSettingsModal.classList.remove('hidden');
            });
        }

        if (closeThemeSettingsModal && themeSettingsModal) {
            closeThemeSettingsModal.addEventListener('click', () => {
                themeSettingsModal.classList.add('hidden');
            });
        }

        // Theme settings tabs
        document.querySelectorAll('.theme-tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const tabId = e.target.dataset.tab;
                this.switchThemeTab(tabId);
            });
        });
    }

    switchThemeTab(tabId) {
        // Update button states
        document.querySelectorAll('.theme-tab-btn').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white');
            btn.classList.add('text-gray-600');
        });
        
        const activeBtn = document.querySelector(`[data-tab="${tabId}"]`);
        if (activeBtn) {
            activeBtn.classList.add('bg-blue-600', 'text-white');
            activeBtn.classList.remove('text-gray-600');
        }

        // Update content
        document.querySelectorAll('.theme-tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        const activeContent = document.getElementById(`${tabId}-tab`);
        if (activeContent) {
            activeContent.classList.remove('hidden');
        }
    }

    setupColorPickers() {
        document.querySelectorAll('input[type="color"]').forEach(input => {
            input.addEventListener('change', (e) => {
                const setting = e.target.dataset.setting;
                const value = e.target.value;
                this.updateSetting(setting, value);
            });
        });
    }

    setupRangeSliders() {
        document.querySelectorAll('input[type="range"]').forEach(input => {
            input.addEventListener('input', (e) => {
                const setting = e.target.dataset.setting;
                const value = e.target.value;
                this.updateSetting(setting, value);
            });
        });
    }

    setupFileUploads() {
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', (e) => {
                this.handleFileUpload(e);
            });
        });
    }

    setupFormHandlers() {
        document.querySelectorAll('input, select, textarea').forEach(input => {
            if (input.type !== 'file' && input.type !== 'color' && input.type !== 'range') {
                input.addEventListener('input', (e) => {
                    const setting = e.target.dataset.setting;
                    const value = e.target.value;
                    if (setting) {
                        this.updateSetting(setting, value);
                    }
                });
            }
        });
    }

    setupRealTimeUpdates() {
        // Listen for changes in the preview frame
        window.addEventListener('message', (event) => {
            if (event.data.type === 'preview-ready') {
                this.updatePreviewSettings();
            }
        });
    }

    setupAutoSave() {
        // מבוטל - שמירה אוטומטית
        // setInterval(() => {
        //     this.autoSave();
        // }, 30000); // Auto-save every 30 seconds
    }

    updateSetting(setting, value) {
        this.settings[setting] = value;
        this.updatePreviewSetting(setting, value);
        // this.debouncedSave(); // מבוטל - שמירה אוטומטית
    }

    updatePreviewSetting(setting, value) {
        if (this.previewFrame) {
            this.previewFrame.contentWindow.postMessage({
                type: 'customizer-update',
                setting: setting,
                value: value
            }, '*');
        }
    }

    updatePreviewSettings() {
        if (this.previewFrame) {
            Object.keys(this.settings).forEach(setting => {
                this.previewFrame.contentWindow.postMessage({
                    type: 'customizer-update',
                    setting: setting,
                    value: this.settings[setting]
                }, '*');
            });
        }
    }

    /**
     * עדכון כללי של התצוגה המקדימה
     */
    updatePreview() {
        if (this.previewFrame) {
            try {
                // שליחת הודעה לעדכון דינמי במקום רענון
                this.previewFrame.contentWindow.postMessage({
                    type: 'update-sections',
                    action: 'refresh'
                }, '*');
            } catch (e) {
                // אם יש בעיית CORS, נטען מחדש את ה-src עם עיכוב קצר
                console.log('CORS error, reloading frame src');
                setTimeout(() => {
                    const currentSrc = this.previewFrame.src;
                    // הוספת timestamp כדי לכפות רענון
                    const separator = currentSrc.includes('?') ? '&' : '?';
                    this.previewFrame.src = currentSrc + separator + 'refresh=' + Date.now();
                }, 100);
            }
        }
    }

    /**
     * רענון מלא של התצוגה המקדימה
     */
    refreshPreview() {
        if (this.previewFrame) {
            const currentSrc = this.previewFrame.src;
            // הוספת timestamp כדי לכפות רענון
            const separator = currentSrc.includes('?') ? '&' : '?';
            this.previewFrame.src = currentSrc + separator + 'refresh=' + Date.now();
        }
    }

    debouncedSave() {
        // מבוטל - שמירה אוטומטית
        // clearTimeout(this.debounceTimer);
        // this.debounceTimer = setTimeout(() => {
        //     this.autoSave();
        // }, 1000);
    }

    async autoSave() {
        if (this.isLoading) return;
        
        try {
            this.isLoading = true;
            this.showAutoSaveIndicator();
            
            const response = await fetch('../api/auto-save-theme.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(this.settings)
            });

            if (response.ok) {
                this.showAutoSaveSuccess();
            } else {
                throw new Error('Auto-save failed');
            }
        } catch (error) {
            console.error('Auto-save error:', error);
            this.showAutoSaveError();
        } finally {
            this.isLoading = false;
            this.hideAutoSaveIndicator();
        }
    }

    async saveSettings() {
        if (this.isLoading) return;
        
        try {
            this.isLoading = true;
            this.showSaveIndicator();
            
            const response = await fetch('../api/save-theme-settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(this.settings)
            });

            if (response.ok) {
                this.showNotification('השינויים נשמרו בהצלחה', 'success');
            } else {
                throw new Error('Save failed');
            }
        } catch (error) {
            console.error('Save error:', error);
            this.showNotification('שגיאה בשמירת השינויים', 'error');
        } finally {
            this.isLoading = false;
            this.hideSaveIndicator();
        }
    }

    async loadSettings() {
        try {
            const response = await fetch('../api/get-theme-settings.php');
            if (response.ok) {
                const data = await response.json();
                this.settings = data.settings || {};
                this.populateForm();
            }
        } catch (error) {
            console.error('Load settings error:', error);
        }
    }

    populateForm() {
        Object.keys(this.settings).forEach(setting => {
            const input = document.querySelector(`[data-setting="${setting}"]`);
            if (input) {
                input.value = this.settings[setting];
            }
        });
    }

    handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);
        formData.append('type', 'theme');

        fetch('../api/upload-image.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const setting = event.target.dataset.setting;
                this.updateSetting(setting, data.url);
                this.showNotification('התמונה הועלתה בהצלחה', 'success');
            } else {
                throw new Error(data.error || 'Upload failed');
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            this.showNotification('שגיאה בהעלאת התמונה', 'error');
        });
    }

    showSaveIndicator() {
        const saveBtn = document.getElementById('save-changes');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="ri-loader-4-line animate-spin ml-2"></i>שומר סקשנים...';
        }
    }

    hideSaveIndicator() {
        const saveBtn = document.getElementById('save-changes');
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="ri-save-line ml-2"></i>שמור סקשנים';
        }
    }

    showAutoSaveIndicator() {
        const indicator = document.getElementById('auto-save-indicator');
        if (indicator) {
            indicator.classList.remove('hidden');
            indicator.innerHTML = '<i class="ri-loader-4-line animate-spin ml-1"></i>שומר...';
        }
    }

    showAutoSaveSuccess() {
        const indicator = document.getElementById('auto-save-indicator');
        if (indicator) {
            indicator.innerHTML = '<i class="ri-check-line ml-1"></i>נשמר';
            setTimeout(() => {
                indicator.classList.add('hidden');
            }, 2000);
        }
    }

    showAutoSaveError() {
        const indicator = document.getElementById('auto-save-indicator');
        if (indicator) {
            indicator.innerHTML = '<i class="ri-error-warning-line ml-1"></i>שגיאה';
            indicator.classList.add('text-red-600');
            setTimeout(() => {
                indicator.classList.add('hidden');
                indicator.classList.remove('text-red-600');
            }, 3000);
        }
    }

    hideAutoSaveIndicator() {
        setTimeout(() => {
            const indicator = document.getElementById('auto-save-indicator');
            if (indicator) {
                indicator.classList.add('hidden');
            }
        }, 1000);
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transition-all duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="${
                    type === 'success' ? 'ri-check-circle-line' :
                    type === 'error' ? 'ri-error-warning-line' :
                    'ri-information-line'
                } ml-2"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    /**
     * שמירת הגדרות עיצוב התבנית
     */
    async saveThemeSettings() {
        console.log('Saving theme settings...');
        
        // כאן תהיה הלוגיקה לשמירת הגדרות העיצוב
        // לדוגמה: צבעי תבנית, גופנים, הגדרות גלובליות וכו'
        
        try {
            // הצגת אינדיקטור שמירה
            const saveBtn = document.getElementById('save-theme-changes');
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="ri-loader-4-line animate-spin ml-2"></i>שומר...';
            }
            
            // כאן תהיה קריאה ל-API לשמירת הגדרות התבנית
            // await fetch('/api/save-theme-settings', {...});
            
            // סגירת המודל
            const modal = document.getElementById('theme-settings-modal');
            if (modal) {
                modal.classList.add('hidden');
            }
            
            // הצגת הודעת הצלחה
            this.showNotification('הגדרות העיצוב נשמרו בהצלחה', 'success');
            
        } catch (error) {
            console.error('Error saving theme settings:', error);
            this.showNotification('שגיאה בשמירת הגדרות העיצוב', 'error');
        } finally {
            // החזרת הכפתור למצב רגיל
            const saveBtn = document.getElementById('save-theme-changes');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="ri-save-line ml-2"></i>שמור שינויים';
            }
        }
    }
}

// Initialize customizer when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const customizer = new Customizer();
    customizer.init();
}); 