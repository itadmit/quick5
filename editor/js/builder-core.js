/**
 * Builder Core - ליבת הבילדר
 * פונקציות בסיסיות, נתונים גלובליים, ותקשורת עם iframe
 */

class BuilderCore {
    constructor() {
        this.sections = window.builderConfig?.sections || [];
        this.selectedSection = null;
        this.storeSlug = window.builderConfig?.storeSlug || '';
        this.storeId = window.builderConfig?.storeId || 0;
        this.isPublished = window.builderConfig?.isPublished || false;
        this.isDirty = false; // האם יש שינויים לא נשמרים
        
        // אם אין סקשנים, צור ברירת מחדל
        if (this.sections.length === 0) {
            console.log('📝 No sections found, creating default hero section');
            this.sections = [this.createDefaultSection('hero')];
            this.markDirty(); // סמן כדורש שמירה
        }
        
        this.init();
        
        // updatePreview יקרא רק כשiframe מוכן
    }
    
    /**
     * אתחול הליבה
     */
    init() {
        this.bindGlobalEvents();
        // this.startAutoSave(); // מבוטל - שמירה רק ידנית
        console.log('🚀 Builder Core initialized', {
            sections: this.sections.length,
            storeSlug: this.storeSlug
        });
    }
    
    /**
     * קישור אירועים גלובליים
     */
    bindGlobalEvents() {
        // שמירה עם Ctrl+S
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                this.savePage();
            }
        });
        
        // מניעת יציאה עם שינויים לא שמורים
        window.addEventListener('beforeunload', (e) => {
            if (this.isDirty) {
                e.preventDefault();
                e.returnValue = 'יש לך שינויים לא שמורים. האם אתה בטוח שברצונך לצאת?';
            }
        });
    }
    

    
    /**
     * הוספת סקשן חדש
     */
    addSection(type) {
        const newSection = this.createDefaultSection(type);
        this.sections.push(newSection);
        this.markDirty();
        this.updatePreview();
        
        console.log('➕ Section added:', newSection.id);
        return newSection;
    }
    
    /**
     * יצירת סקשן ברירת מחדל
     */
    createDefaultSection(type) {
        const id = `${type}_${Date.now()}`;
        
        const defaults = {
            hero: {
                id: id,
                type: 'hero',
                visible: true,
                styles: {
                    'background-type': 'gradient',
                    'gradient-color1': '#3b82f6',
                    'gradient-color2': '#1e40af',
                    'gradient-direction': 'to bottom',
                    'height': '100vh',
                    'padding-top': '60px',
                    'padding-bottom': '60px'
                },
                content: {
                    title: {
                        text: 'ברוכים הבאים לחנות שלנו',
                        styles: {
                            desktop: {
                                'font-size': '48px',
                                'font-weight': 'bold',
                                'color': '#FFFFFF',
                                'text-align': 'center',
                                'line-height': '1.2'
                            },
                            mobile: {
                                'font-size': '32px',
                                'font-weight': 'bold',
                                'color': '#FFFFFF',
                                'text-align': 'center',
                                'line-height': '1.2'
                            }
                        }
                    },
                    subtitle: {
                        text: 'גלו את המוצרים הטובים ביותר במחירים הטובים ביותר',
                        styles: {
                            desktop: {
                                'font-size': '18px',
                                'color': '#E5E7EB',
                                'text-align': 'center',
                                'line-height': '1.6'
                            },
                            mobile: {
                                'font-size': '16px',
                                'color': '#E5E7EB',
                                'text-align': 'center',
                                'line-height': '1.6'
                            }
                        }
                    },
                    buttons: [
                        {
                            id: 'btn_' + Date.now(),
                            text: 'קנה עכשיו',
                            url: '#',
                            style: 'primary',
                            styles: {
                                'background-color': '#ffffff',
                                'color': '#3b82f6',
                                'padding': '12px 24px',
                                'border-radius': '6px',
                                'font-weight': '500',
                                'text-decoration': 'none',
                                'display': 'inline-block'
                            }
                        }
                    ]
                }
            },
            text: {
                id: id,
                type: 'text',
                visible: true,
                styles: {
                    desktop: {
                        'padding': '60px 20px',
                        'background-color': 'white',
                        'text-align': 'center'
                    },
                    mobile: {
                        'padding': '40px 15px',
                        'background-color': 'white',
                        'text-align': 'center'
                    }
                },
                content: {
                    text: {
                        text: 'זה בלוק טקסט. ערוך אותי כרצונך!',
                        styles: {
                            desktop: {
                                'font-size': '16px',
                                'line-height': '1.6',
                                'color': '#374151'
                            },
                            mobile: {
                                'font-size': '14px',
                                'line-height': '1.6',
                                'color': '#374151'
                            }
                        }
                    }
                }
            }
        };
        
        return defaults[type] || defaults.text;
    }
    
    /**
     * עדכון סקשן
     */
    updateSection(sectionId, updates) {
        const sectionIndex = this.sections.findIndex(s => s.id === sectionId);
        if (sectionIndex === -1) return false;
        
        // Debug logging עבור visibility updates
        if (updates.visibility) {
            console.log('🔧 Visibility update received:', updates.visibility);
            console.log('🔧 Before update - section visibility:', this.sections[sectionIndex].visibility);
            console.log('🔧 Before update - FULL section:', this.sections[sectionIndex]);
        }
        
        // עדכון עמוק של הסקשן
        this.sections[sectionIndex] = this.deepMerge(this.sections[sectionIndex], updates);
        
        // Debug logging אחרי העדכון
        if (updates.visibility) {
            console.log('🔧 After update - section visibility:', this.sections[sectionIndex].visibility);
            console.log('🔧 After update - FULL section:', this.sections[sectionIndex]);
        }
        
        this.markDirty();
        this.updatePreview();
        
        console.log('🔧 Section updated:', sectionId);
        return true;
    }
    
    /**
     * מחיקת סקשן
     */
    deleteSection(sectionId) {
        const initialLength = this.sections.length;
        this.sections = this.sections.filter(s => s.id !== sectionId);
        
        if (this.sections.length < initialLength) {
            this.markDirty();
            this.updatePreview();
            console.log('🗑️ Section deleted:', sectionId);
            return true;
        }
        return false;
    }
    
    /**
     * הסתרה/הצגת סקשן
     */
    toggleSectionVisibility(sectionId) {
        const section = this.sections.find(s => s.id === sectionId);
        if (section) {
            section.visible = !section.visible;
            this.markDirty();
            this.updatePreview();
            console.log('👁️ Section visibility toggled:', sectionId, section.visible);
            return section.visible;
        }
        return false;
    }
    
    /**
     * קבלת סקשן לפי ID
     */
    getSection(sectionId) {
        return this.sections.find(s => s.id === sectionId);
    }
    
    /**
     * עדכון תצוגה מקדימה
     */
    updatePreview() {
        const iframe = document.getElementById('previewFrame');
        if (iframe && iframe.contentWindow) {
            console.log('📡 Sending updateSections to iframe:', this.sections.filter(s => s.visible !== false));
            iframe.contentWindow.postMessage({
                type: 'updateSections',
                sections: this.sections.filter(s => s.visible !== false)
            }, '*');
        }
    }
    
    /**
     * שמירה ופרסום בבת אחת
     */
    async savePage() {
        if (!this.isDirty) {
            this.showNotification('הדף כבר שמור ופורסם', 'info');
            return;
        }
        
        try {
            this.showLoading('שומר ומפרסם...');
            
            // שמירה עם פרסום אוטומטי
            const response = await fetch('../api/save-page.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    store_id: this.storeId,
                    page_type: 'home',
                    page_data: this.sections,
                    is_published: true
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.isDirty = false;
                this.isPublished = true;
                this.showNotification('הדף נשמר ופורסם בהצלחה!', 'success');
                console.log('💾 Page saved and published successfully');
            } else {
                throw new Error(result.message || 'שגיאה בשמירה');
            }
        } catch (error) {
            console.error('❌ Save error:', error);
            this.showNotification('שגיאה בשמירת הדף: ' + error.message, 'error');
        } finally {
            this.hideLoading();
        }
    }
    

    

    
    /**
     * סימון שיש שינויים
     */
    markDirty() {
        this.isDirty = true;
    }
    
    /**
     * מיזוג עמוק של אובייקטים
     */
    deepMerge(target, source) {
        const result = { ...target };
        
        for (const key in source) {
            if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
                result[key] = this.deepMerge(result[key] || {}, source[key]);
            } else {
                result[key] = source[key];
            }
        }
        
        return result;
    }
    
    /**
     * הצגת התראה
     */
    showNotification(message, type = 'info') {
        console.log(`🔔 ${type.toUpperCase()}: ${message}`);
        
        const container = document.getElementById('notificationContainer');
        if (!container) return;
        
        // יצירת הבועית
        const bubble = document.createElement('div');
        bubble.className = `notification-bubble ${type}`;
        
        // בחירת אייקון לפי סוג ההודעה
        let icon = '';
        switch (type) {
            case 'success':
                icon = 'ri-check-circle-fill';
                break;
            case 'error':
                icon = 'ri-error-warning-fill';
                break;
            case 'warning':
                icon = 'ri-alert-fill';
                break;
            default:
                icon = 'ri-information-fill';
        }
        
        bubble.innerHTML = `
            <i class="${icon} notification-icon"></i>
            <span class="notification-text">${message}</span>
        `;
        
        // הוספה לcontainer
        container.appendChild(bubble);
        
        // אנימציה של הופעה
        setTimeout(() => {
            bubble.classList.add('show');
        }, 100);
        
        // הסרה אוטומטית אחרי 4 שניות
        setTimeout(() => {
            bubble.classList.remove('show');
            setTimeout(() => {
                if (bubble.parentNode) {
                    bubble.parentNode.removeChild(bubble);
                }
            }, 400);
        }, 4000);
    }
    
    /**
     * הצגת מצב טעינה
     */
    showLoading(message) {
        console.log(`⏳ Loading: ${message}`);
        
        const container = document.getElementById('notificationContainer');
        if (!container) return;
        
        // הסרת הודעות טעינה קודמות
        this.hideLoading();
        
        // יצירת הודעת טעינה
        const bubble = document.createElement('div');
        bubble.className = 'notification-bubble loading';
        bubble.id = 'loadingNotification';
        
        bubble.innerHTML = `
            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
            <span class="notification-text">${message}</span>
        `;
        
        container.appendChild(bubble);
        
        setTimeout(() => {
            bubble.classList.add('show');
        }, 100);
    }
    
    /**
     * הסתרת מצב טעינה
     */
    hideLoading() {
        console.log('✅ Loading hidden');
        
        const loadingBubble = document.getElementById('loadingNotification');
        if (loadingBubble) {
            loadingBubble.classList.remove('show');
            setTimeout(() => {
                if (loadingBubble.parentNode) {
                    loadingBubble.parentNode.removeChild(loadingBubble);
                }
            }, 400);
        }
    }
    
    /**
     * ניקוי משאבים
     */
    destroy() {
        // אין משאבים לנקות יותר
        console.log('🔌 Builder Core destroyed');
    }
}

// יצירת instance גלובלי
window.builderCore = new BuilderCore(); 