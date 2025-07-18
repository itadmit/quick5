/**
 * Device Sync Manager - מנהל סינכרון מצבי מחשב/מובייל בכל הקומפוננטים
 */

class DeviceSyncManager {
    constructor() {
        this.currentDevice = 'desktop';
        this.components = [];
        this.init();
    }

    init() {
        console.log('📱 Initializing Device Sync Manager');
        
        // האזן לכל לחיצות על device switchers (כולל header)
        document.addEventListener('click', (e) => {
            console.log('🖱️ Click detected on:', e.target);
            
            // בדוק אם זה כפתור device (גם בheader וגם בקומפוננטים)
            const button = e.target.classList.contains('device-btn') ? e.target : e.target.closest('.device-btn');
            
            // תמיכה מיוחדת לכפתורי הheader
            const isHeaderDesktop = e.target.id === 'desktopView' || e.target.closest('#desktopView');
            const isHeaderMobile = e.target.id === 'mobileView' || e.target.closest('#mobileView');
            
            let device = null;
            
            if (button && button.dataset.device) {
                device = button.dataset.device;
                console.log(`📱 Device button clicked: ${device} (from device-btn class)`);
            } else if (isHeaderDesktop) {
                device = 'desktop';
                console.log(`📱 Header desktop button clicked`);
            } else if (isHeaderMobile) {
                device = 'mobile';
                console.log(`📱 Header mobile button clicked`);
            }
            
            if (device && device !== this.currentDevice) {
                console.log(`📱 Switching from ${this.currentDevice} to ${device}`);
                e.preventDefault(); // מנע פעולות נוספות
                this.switchAllDevices(device);
            }
        });
        
        // צפה לswitchers חדשים שמתווספים
        this.observeNewSwitchers();
        
        // תקן header switcher בטעינה הראשונית
        setTimeout(() => {
            this.syncMainHeaderSwitcher(this.currentDevice);
        }, 100);
        
        console.log('✅ Device Sync Manager initialized');
    }

    /**
     * החלפת כל הקומפוננטים למכשיר חדש
     */
    switchAllDevices(device) {
        console.log(`📱 Switching ALL components to ${device} mode`);
        
        this.currentDevice = device;
        
        // עדכן את כל הswitchers
        this.syncAllSwitchers(device);
        
        // עדכן את כל הקומפוננטים הרשומים
        this.components.forEach(component => {
            if (component && typeof component.switchDevice === 'function') {
                component.currentDevice = device;
                component.updateDeviceSettings(device);
            }
        });
        
        // עדכן את התצוגה המקדימה במכשיר החדש
        this.updatePreviewDevice(device);
        
        console.log(`✅ All components switched to ${device}`);
    }

    /**
     * סינכרון כל הswitchers בעמוד
     */
    syncAllSwitchers(device) {
        // עדכון switchers רגילים (בקומפוננטים) - לא כולל הראשי שבהדר
        const allSwitchers = document.querySelectorAll('.device-switcher');
        
        allSwitchers.forEach(switcher => {
            const buttons = switcher.querySelectorAll('.device-btn');
            
            buttons.forEach(btn => {
                btn.classList.remove('active', 'bg-white', 'shadow-sm');
                btn.classList.add('hover:bg-white');
                
                if (btn.dataset.device === device) {
                    btn.classList.add('active', 'bg-white', 'shadow-sm');
                    btn.classList.remove('hover:bg-white');
                }
            });
        });
        
        // עדכון הסוויצר הראשי בהדר - בטיפול מיוחד
        this.syncMainHeaderSwitcher(device);
        
        // עדכן את כל הsettings panels
        const allSettings = document.querySelectorAll('.device-settings');
        allSettings.forEach(settings => {
            settings.style.display = settings.dataset.device === device ? 'block' : 'none';
        });
        
        console.log(`📱 Synced ${allSwitchers.length} switchers + main header to ${device}`);
    }
    
    /**
     * סינכרון הסוויצר הראשי בהדר - מחזק ואמין
     */
    syncMainHeaderSwitcher(device) {
        const desktopBtn = document.getElementById('desktopView');
        const mobileBtn = document.getElementById('mobileView');
        
        console.log(`📱 Syncing header switcher to ${device}`);
        console.log(`📱 Desktop button found:`, !!desktopBtn);
        console.log(`📱 Mobile button found:`, !!mobileBtn);
        
        if (desktopBtn && mobileBtn) {
            // הוסף את הקלאסים הנכונים לכל כפתור בכל מקרה (בלי להרוס הקיימים!)
            desktopBtn.classList.add('device-btn');
            mobileBtn.classList.add('device-btn');
            desktopBtn.dataset.device = 'desktop';
            mobileBtn.dataset.device = 'mobile';
            
            if (device === 'mobile') {
                // איפוס כפתור מחשב - רק מסיר/מוסיף classes רלוונטיים
                desktopBtn.classList.remove('figma-button', 'bg-white', 'text-gray-700');
                desktopBtn.classList.add('text-gray-600', 'hover:text-gray-900');
                
                // הפעלת כפתור מובייל - רק מוסיף classes רלוונטיים
                mobileBtn.classList.remove('text-gray-600', 'hover:text-gray-900');
                mobileBtn.classList.add('figma-button', 'bg-white', 'text-gray-700');
            } else {
                // הפעלת כפתור מחשב - רק מוסיף classes רלוונטיים
                desktopBtn.classList.remove('text-gray-600', 'hover:text-gray-900');
                desktopBtn.classList.add('figma-button', 'bg-white', 'text-gray-700');
                
                // איפוס כפתור מובייל - רק מסיר/מוסיף classes רלוונטיים
                mobileBtn.classList.remove('figma-button', 'bg-white', 'text-gray-700');
                mobileBtn.classList.add('text-gray-600', 'hover:text-gray-900');
            }
            
            console.log(`📱 ✅ Header switcher successfully synced to ${device}`);
        }
    }

    /**
     * עדכון התצוגה המקדימה למכשיר חדש
     */
    updatePreviewDevice(device) {
        const previewFrame = document.getElementById('previewFrame');
        if (previewFrame && previewFrame.contentWindow) {
            // שלח הודעה לiframe לעדכון תצוגת מכשיר
            previewFrame.contentWindow.postMessage({
                type: 'deviceChanged',
                device: device
            }, '*');
            
            console.log(`📱 Sent device change to preview: ${device}`);
        }
        
        // עדכן את ה-builder UI למכשיר הנוכחי
        document.documentElement.setAttribute('data-preview-device', device);
        
        // עדכן את ה-iframe view mode (גודל התצוגה)
        this.updateIframeViewMode(device);
    }
    
    /**
     * עדכון view mode של ה-iframe
     */
    updateIframeViewMode(device) {
        const iframe = document.getElementById('previewFrame');
        if (!iframe) return;
        
        if (device === 'mobile') {
            iframe.style.width = '375px';
            iframe.style.margin = '0 auto';
            iframe.style.border = '1px solid #ccc';
            iframe.style.borderRadius = '10px';
        } else {
            iframe.style.width = '100%';
            iframe.style.margin = '0';
            iframe.style.border = 'none';
            iframe.style.borderRadius = '0';
        }
        
        // שלח גם הודעת viewModeChanged
        iframe.contentWindow?.postMessage({
            type: 'viewModeChanged',
            mode: device
        }, '*');
        
        console.log(`📱 Updated iframe view mode to ${device}`);
    }

    /**
     * רישום קומפוננט למעקב
     */
    registerComponent(component) {
        if (component && !this.components.includes(component)) {
            this.components.push(component);
            
            // ודא שהקומפוננט מתחיל במכשיר הנוכחי
            if (typeof component.switchDevice === 'function') {
                component.currentDevice = this.currentDevice;
                component.updateDeviceSettings?.(this.currentDevice);
            }
            
            console.log(`📱 Registered component for device sync:`, component.componentId || 'unknown');
        }
    }

    /**
     * ביטול רישום קומפוננט
     */
    unregisterComponent(component) {
        const index = this.components.indexOf(component);
        if (index > -1) {
            this.components.splice(index, 1);
            console.log(`📱 Unregistered component from device sync`);
        }
    }

    /**
     * צפייה לswitchers חדשים
     */
    observeNewSwitchers() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        const newSwitchers = node.querySelectorAll ? 
                                           node.querySelectorAll('.device-switcher') : [];
                        
                        if (newSwitchers.length > 0) {
                            // סנכרן switchers חדשים למצב הנוכחי
                            this.syncAllSwitchers(this.currentDevice);
                            console.log(`📱 Synced ${newSwitchers.length} new switchers`);
                        }
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * קבלת המכשיר הנוכחי
     */
    getCurrentDevice() {
        return this.currentDevice;
    }
}

// יצירת instance גלובלי
window.deviceSyncManager = new DeviceSyncManager();

console.log('📁 device-sync-manager.js loaded');
console.log('📱 Header device switcher should now work with sync mechanism!'); 