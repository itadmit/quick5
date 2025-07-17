/**
 * Button Features Extension
 * הרחבות מתקדמות לריפיטר כפתורים
 */

class ButtonFeatures {
    constructor() {
        this.iconModal = null;
        this.currentButtonIndex = null;
        this.setupGlobalFeatures();
        this.loadRemixIcons();
    }

    /**
     * הגדרות גלובליות
     */
    setupGlobalFeatures() {
        // הוספת סגנונות CSS
        this.addCustomStyles();
        
        // הוספת מודל אייקונים
        this.createIconModal();
    }

    /**
     * הוספת סגנונות CSS מותאמים
     */
    addCustomStyles() {
        const style = document.createElement('style');
        style.textContent = `
            /* תיקון hover על כפתורי סגנון נבחרים */
            .button-style-option.selected:hover {
                background-color: #3b82f6 !important;
                color: white !important;
                border-color: #3b82f6 !important;
            }
            
            /* פיצ'רים של כפתורים */
            .button-features {
                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
                gap: 8px;
                margin-top: 12px;
                padding-top: 12px;
                border-top: 1px solid #e5e7eb;
            }
            
            .feature-toggle {
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 8px;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                cursor: pointer;
                transition: all 0.2s;
                background: white;
            }
            
            .feature-toggle:hover {
                border-color: #9ca3af;
                background-color: #f9fafb;
            }
            
            .feature-toggle.active {
                background-color: #3b82f6;
                border-color: #3b82f6;
                color: white;
            }
            
            .feature-toggle i {
                font-size: 18px;
                margin-bottom: 4px;
            }
            
            .feature-toggle span {
                font-size: 10px;
                text-align: center;
                line-height: 1.2;
            }
            
            .feature-toggle.active span {
                color: white;
            }
            
            /* מודל אייקונים */
            .icon-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 10000;
            }
            
            .icon-modal-content {
                background: white;
                border-radius: 12px;
                padding: 24px;
                max-width: 600px;
                max-height: 80vh;
                overflow-y: auto;
                direction: rtl;
            }
            
            .icon-search {
                width: 100%;
                padding: 12px;
                border: 1px solid #d1d5db;
                border-radius: 8px;
                margin-bottom: 16px;
                font-size: 14px;
            }
            
            .icon-grid {
                display: grid;
                grid-template-columns: repeat(8, 1fr);
                gap: 8px;
                max-height: 400px;
                overflow-y: auto;
            }
            
            .icon-item {
                width: 48px;
                height: 48px;
                border: 1px solid #e5e7eb;
                border-radius: 6px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s;
                background: white;
            }
            
            .icon-item:hover {
                border-color: #3b82f6;
                background-color: #eff6ff;
            }
            
            .icon-item i {
                font-size: 20px;
                color: #374151;
            }
            
            /* תצוגה במובייל */
            .mobile-display-setting {
                background: #f8fafc;
                border-radius: 8px;
                padding: 16px;
                margin-top: 16px;
                border-top: 2px solid #e2e8f0;
            }
            
            .mobile-display-setting label {
                font-weight: 500;
                color: #374151;
                margin-bottom: 8px;
                display: block;
            }
            
            .mobile-display-options {
                display: flex;
                gap: 8px;
                direction: rtl;
            }
            
            .mobile-display-option {
                flex: 1;
                padding: 8px 12px;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                text-align: center;
                cursor: pointer;
                transition: all 0.2s;
                background: white;
                font-size: 13px;
            }
            
            .mobile-display-option:hover {
                border-color: #9ca3af;
            }
            
            .mobile-display-option.active {
                background-color: #3b82f6;
                border-color: #3b82f6;
                color: white;
            }
            
            /* רמות פינות מעוגלות */
            .rounded-level-0 {
                background-color: #6b7280;
                border-color: #6b7280;
                color: white;
            }
            
            .rounded-level-1 {
                background-color: #10b981;
                border-color: #10b981;
                color: white;
            }
            
            .rounded-level-2 {
                background-color: #3b82f6;
                border-color: #3b82f6;
                color: white;
            }
            
            .rounded-level-3 {
                background-color: #8b5cf6;
                border-color: #8b5cf6;
                color: white;
            }
            
            .rounded-level-4 {
                background-color: #f59e0b;
                border-color: #f59e0b;
                color: white;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * יצירת מודל בחירת אייקונים
     */
    createIconModal() {
        const modal = document.createElement('div');
        modal.className = 'icon-modal';
        modal.innerHTML = `
            <div class="icon-modal-content">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">בחירת אייקון</h3>
                    <button type="button" class="close-icon-modal text-gray-500 hover:text-gray-700">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>
                
                <input type="text" class="icon-search" placeholder="חיפוש אייקון..." />
                
                <!-- No Icon Option -->
                <div class="mb-4">
                    <div class="no-icon-option" style="padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; text-align: center; cursor: pointer; background: #f9fafb; transition: all 0.2s;">
                        <i class="ri-close-line text-gray-500 text-lg"></i>
                        <span style="display: block; margin-top: 4px; font-size: 13px; color: #6b7280;">ללא אייקון</span>
                    </div>
                </div>
                
                <div class="icon-grid">
                    <!-- אייקונים יטענו כאן -->
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        this.iconModal = modal;
        
        // הוספת מאזינים
        modal.querySelector('.close-icon-modal').addEventListener('click', () => {
            this.hideIconModal();
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.hideIconModal();
            }
        });
        
        // חיפוש אייקונים
        modal.querySelector('.icon-search').addEventListener('input', (e) => {
            this.filterIcons(e.target.value);
        });
        
        // לחיצה על "ללא אייקון"
        modal.querySelector('.no-icon-option').addEventListener('click', () => {
            this.selectIcon(''); // Empty string for no icon
        });
    }

    /**
     * טעינת אייקוני Remix
     */
    loadRemixIcons() {
        // רשימה של אייקונים פופולריים מ-Remix
        this.popularIcons = [
            'ri-star-line', 'ri-heart-line', 'ri-thumb-up-line', 'ri-fire-line',
            'ri-gift-line', 'ri-shopping-cart-line', 'ri-shopping-bag-line', 'ri-money-dollar-circle-line',
            'ri-phone-line', 'ri-mail-line', 'ri-send-plane-line', 'ri-chat-3-line',
            'ri-home-line', 'ri-building-line', 'ri-car-line', 'ri-truck-line',
            'ri-user-line', 'ri-team-line', 'ri-vip-crown-line', 'ri-shield-check-line',
            'ri-time-line', 'ri-calendar-line', 'ri-notification-line', 'ri-alarm-line',
            'ri-search-line', 'ri-filter-line', 'ri-settings-line', 'ri-tools-line',
            'ri-download-line', 'ri-upload-line', 'ri-share-line', 'ri-external-link-line',
            'ri-arrow-left-line', 'ri-arrow-right-line', 'ri-arrow-up-line', 'ri-arrow-down-line',
            'ri-check-line', 'ri-close-line', 'ri-add-line', 'ri-subtract-line',
            'ri-eye-line', 'ri-eye-off-line', 'ri-edit-line', 'ri-delete-bin-line',
            'ri-image-line', 'ri-video-line', 'ri-music-line', 'ri-file-line',
            'ri-folder-line', 'ri-bookmark-line', 'ri-flag-line', 'ri-map-pin-line'
        ];
        
        this.allIcons = [...this.popularIcons];
        this.renderIcons(this.allIcons);
    }

    /**
     * רנדור אייקונים
     */
    renderIcons(icons) {
        const grid = this.iconModal.querySelector('.icon-grid');
        grid.innerHTML = '';
        
        icons.forEach(iconClass => {
            const iconItem = document.createElement('div');
            iconItem.className = 'icon-item';
            iconItem.innerHTML = `<i class="${iconClass}"></i>`;
            iconItem.addEventListener('click', () => {
                this.selectIcon(iconClass);
            });
            grid.appendChild(iconItem);
        });
    }

    /**
     * סינון אייקונים
     */
    filterIcons(searchTerm) {
        if (!searchTerm.trim()) {
            this.renderIcons(this.allIcons);
            return;
        }
        
        const filtered = this.allIcons.filter(icon => 
            icon.toLowerCase().includes(searchTerm.toLowerCase())
        );
        this.renderIcons(filtered);
    }

    /**
     * בחירת אייקון
     */
    selectIcon(iconClass) {
        if (this.currentButtonIndex !== null && window.buttonsRepeater) {
            // עדכון הנתונים ברפיטר - empty string means no icon
            window.buttonsRepeater.updateButton(this.currentButtonIndex, 'icon', iconClass);
            
            // עדכון התצוגה המקדימה ושמירה
            this.updateButtonIcon(this.currentButtonIndex, iconClass);
            
            // עדכון מיידי של האייקון בטוגל עם timeout קטן
            setTimeout(() => {
                this.updateIconDisplay(this.currentButtonIndex, iconClass);
            }, 50);
            
            this.hideIconModal();
        }
    }
    
    /**
     * עדכון תצוגת האייקון בטוגל
     */
    updateIconDisplay(buttonIndex, iconClass) {
        console.log('Updating icon display for button:', buttonIndex, 'with icon:', iconClass);
        
        // חיפוש הטוגל הספציפי
        const iconToggle = document.querySelector(`[data-feature="icon"][data-index="${buttonIndex}"]`);
        console.log('Found icon toggle:', iconToggle);
        
        if (iconToggle) {
            const iconElement = iconToggle.querySelector('i');
            console.log('Found icon element:', iconElement);
            
            if (iconElement) {
                const oldClass = iconElement.className;
                // If no icon is selected, show default "no icon" display
                if (!iconClass || iconClass.trim() === '') {
                    iconElement.className = 'ri-close-line';
                    iconElement.style.color = '#9ca3af'; // Gray color for no icon
                } else {
                    iconElement.className = iconClass;
                    iconElement.style.color = ''; // Reset color
                }
                console.log('Updated icon class from', oldClass, 'to', iconElement.className);
            }
        }
    }

    /**
     * עדכון אייקון בתצוגה המקדימה
     */
    updateButtonIcon(buttonIndex, iconClass) {
        // עדכון בתצוגה המקדימה
        if (window.currentSection) {
            window.currentSection.updateButtonIcon(buttonIndex, iconClass);
            
            // גם עדכון מיידי של הנתונים בזיכרון
            if (window.currentSection.data.buttons && window.currentSection.data.buttons[buttonIndex]) {
                window.currentSection.data.buttons[buttonIndex].icon = iconClass;
            }
        }
    }
    
    /**
     * עדכון סגנון כפתור בתצוגה המקדימה
     */
    updateButtonStyle(buttonIndex, property, value) {
        if (window.currentSection) {
            // עדכון הנתונים בהירו סקשן
            if (window.currentSection.data.buttons && window.currentSection.data.buttons[buttonIndex]) {
                window.currentSection.data.buttons[buttonIndex][property] = value;
                // שליחה לתצוגה המקדימה
                window.currentSection.builder.sendToIframe('updateHero', window.currentSection.data);
                // שמירה אוטומטית
                window.currentSection.builder.markAsChanged();
            }
        }
    }

    /**
     * הצגת מודל אייקונים
     */
    showIconModal(buttonIndex) {
        this.currentButtonIndex = buttonIndex;
        this.iconModal.style.display = 'flex';
        this.iconModal.querySelector('.icon-search').value = '';
        this.renderIcons(this.allIcons);
    }

    /**
     * הסתרת מודל אייקונים
     */
    hideIconModal() {
        this.iconModal.style.display = 'none';
        this.currentButtonIndex = null;
    }

    /**
     * הוספת פיצ'רים לכפתור בריפיטר
     */
    enhanceButtonElement(buttonElement, button, index) {
        // הוספת פיצ'רים מתחת לפדינג
        const paddingSection = buttonElement.querySelector('.text-xs.text-gray-500.mt-1').parentNode;
        
        const featuresDiv = document.createElement('div');
        featuresDiv.className = 'button-features';
        featuresDiv.innerHTML = `
            <div style="display: flex; flex-direction: column; align-items: center; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; background: white;">
                <label style="font-size: 10px; margin-bottom: 4px; text-align: center; line-height: 1.2;">פינות<br/>מעוגלות (px)</label>
                <input type="number" min="0" max="50" value="${button.rounded || 0}" data-feature="rounded-input" data-index="${index}" 
                       style="width: 60px; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; text-align: center; font-size: 12px;">
            </div>
            
            <div class="feature-toggle ${button.fullWidth ? 'active' : ''}" data-feature="fullWidth" data-index="${index}">
                <i class="ri-fullscreen-line"></i>
                <span>רוחב<br/>מלא</span>
            </div>
            
            <div class="feature-toggle" data-feature="icon" data-index="${index}">
                <i class="${button.icon || 'ri-close-line'}" style="${button.icon ? '' : 'color: #9ca3af;'}"></i>
                <span>אייקון</span>
            </div>
        `;
        
        paddingSection.appendChild(featuresDiv);
        
        // הוספת מאזינים
        featuresDiv.addEventListener('click', (e) => {
            const toggle = e.target.closest('.feature-toggle');
            if (!toggle) return;
            
            const feature = toggle.dataset.feature;
            const buttonIndex = parseInt(toggle.dataset.index);
            
            this.handleFeatureToggle(feature, buttonIndex, toggle);
        });
        
        // מאזין לאינפוט פינות מעוגלות
        const roundedInput = featuresDiv.querySelector('[data-feature="rounded-input"]');
        if (roundedInput) {
            roundedInput.addEventListener('input', (e) => {
                const buttonIndex = parseInt(e.target.dataset.index);
                const value = parseInt(e.target.value) || 0;
                
                if (window.buttonsRepeater) {
                    window.buttonsRepeater.updateButton(buttonIndex, 'rounded', value);
                }
                
                // עדכון התצוגה המקדימה ישירות
                this.updateButtonStyle(buttonIndex, 'rounded', value);
            });
        }
        
        // עדכון המצב בהתבסס על הנתונים השמורים
        this.updateFeatureStates(buttonElement, button, index);
    }
    
    /**
     * עדכון מצבי הפיצ'רים על בסיס הנתונים השמורים
     */
    updateFeatureStates(buttonElement, button, index) {
        // עדכון ערך פינות מעוגלות באינפוט המספר
        const roundedInput = buttonElement.querySelector(`[data-feature="rounded-input"][data-index="${index}"]`);
        if (roundedInput) {
            roundedInput.value = button.rounded || 0;
        }
        
        // עדכון מצב רוחב מלא
        const fullWidthToggle = buttonElement.querySelector(`[data-feature="fullWidth"][data-index="${index}"]`);
        if (fullWidthToggle) {
            fullWidthToggle.classList.toggle('active', button.fullWidth === true);
        }
        
        // עדכון אייקון
        const iconToggle = buttonElement.querySelector(`[data-feature="icon"][data-index="${index}"]`);
        if (iconToggle && button.icon) {
            const iconElement = iconToggle.querySelector('i');
            if (iconElement) {
                iconElement.className = button.icon;
            }
        }
    }

    /**
     * טיפול בלחיצה על פיצ'ר
     */
    handleFeatureToggle(feature, buttonIndex, toggleElement) {
        switch (feature) {
            case 'fullWidth':
                this.toggleFullWidth(buttonIndex, toggleElement);
                break;
            case 'icon':
                this.showIconModal(buttonIndex);
                break;
        }
    }



    /**
     * החלפת רוחב מלא
     */
    toggleFullWidth(buttonIndex, toggleElement) {
        const isActive = toggleElement.classList.contains('active');
        const newValue = !isActive;
        
        toggleElement.classList.toggle('active', newValue);
        
        if (window.buttonsRepeater) {
            window.buttonsRepeater.updateButton(buttonIndex, 'fullWidth', newValue);
        }
        
        // עדכון התצוגה המקדימה ישירות
        this.updateButtonStyle(buttonIndex, 'fullWidth', newValue);
    }

    /**
     * הוספת הגדרות תצוגה במובייל
     */
    addMobileDisplaySetting(container) {
        console.log('ButtonFeatures: Adding mobile display setting');
        
        // קבלת המצב הנוכחי מהנתונים
        const currentDisplay = (window.currentSection && window.currentSection.data) ? 
            window.currentSection.data.buttonsMobileDisplay || 'horizontal' : 'horizontal';
            
        console.log('ButtonFeatures: Current mobile display mode:', currentDisplay);
        
        const mobileDiv = document.createElement('div');
        mobileDiv.className = 'mobile-display-setting';
        mobileDiv.innerHTML = `
            <label>תצוגת כפתורים במובייל</label>
            <div class="mobile-display-options">
                <div class="mobile-display-option ${currentDisplay === 'horizontal' ? 'active' : ''}" data-display="horizontal">
                    <i class="ri-arrow-left-right-line"></i>
                    אחד ליד השני
                </div>
                <div class="mobile-display-option ${currentDisplay === 'vertical' ? 'active' : ''}" data-display="vertical">
                    <i class="ri-arrow-up-down-line"></i>
                    אחד מתחת לשני
                </div>
            </div>
        `;
        
        container.appendChild(mobileDiv);
        
        // מאזין לשינוי
        mobileDiv.addEventListener('click', (e) => {
            console.log('ButtonFeatures: Click detected on mobile display setting');
            const option = e.target.closest('.mobile-display-option');
            if (!option) {
                console.log('ButtonFeatures: Click was not on a mobile-display-option');
                return;
            }
            
            const displayType = option.dataset.display;
            console.log('ButtonFeatures: Selected display type:', displayType);
            
            // עדכון UI
            mobileDiv.querySelectorAll('.mobile-display-option').forEach(opt => {
                opt.classList.remove('active');
            });
            option.classList.add('active');
            console.log('ButtonFeatures: UI updated, active class set on:', displayType);
            
            // שמירת הגדרה
            this.updateMobileDisplay(displayType);
        });
    }

    /**
     * עדכון תצוגה במובייל
     */
    updateMobileDisplay(displayType) {
        console.log('ButtonFeatures: Updating mobile display to:', displayType);
        if (window.currentSection) {
            console.log('ButtonFeatures: Current hero data before update:', window.currentSection.data.buttonsMobileDisplay);
            window.currentSection.updateProperty('buttonsMobileDisplay', displayType);
            console.log('ButtonFeatures: Current hero data after update:', window.currentSection.data.buttonsMobileDisplay);
            
            // עדכון השדה הנסתר בטופס
            const hiddenInput = document.getElementById('heroButtonsMobileDisplay');
            if (hiddenInput) {
                hiddenInput.value = displayType;
                console.log('ButtonFeatures: Updated hidden input value to:', displayType);
            } else {
                console.warn('ButtonFeatures: Hidden input heroButtonsMobileDisplay not found');
            }
            
            // שמירה אוטומטית
            window.currentSection.builder.markAsChanged();
            console.log('ButtonFeatures: Marked as changed for saving');
        } else {
            console.error('ButtonFeatures: window.currentSection not found');
        }
    }
}

// יצירת instance גלובלי
window.buttonFeatures = new ButtonFeatures(); 