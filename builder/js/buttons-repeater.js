/**
 * Buttons Repeater - ריפיטר כללי לכפתורים
 */
window.ButtonsRepeater = class ButtonsRepeater {
    constructor(containerId, inputId, buttons = [], options = {}) {
        this.containerId = containerId;
        this.inputId = inputId;
        this.buttons = buttons;
        this.options = {
            minButtons: 1,
            maxButtons: 10,
            showIcons: true,
            showHover: true,
            ...options
        };
        
        this.container = document.getElementById(containerId);
        this.hiddenInput = document.getElementById(inputId);
        this.addButton = document.querySelector(`[data-add="${containerId}"]`);
        
        if (!this.container || !this.hiddenInput) {
            console.error('ButtonsRepeater: Required elements not found');
            return;
        }
        
        this.init();
    }
    
    init() {
        // אתחול כפתורים אם לא קיימים
        if (!this.buttons || this.buttons.length === 0) {
            this.buttons = [{
                text: 'כפתור חדש',
                link: '#',
                newTab: false,
                style: 'filled',
                paddingTop: '',
                paddingBottom: '',
                paddingRight: '',
                paddingLeft: '',
                marginTop: '',
                marginBottom: '',
                rounded: 0,
                fullWidth: false,
                icon: ''
            }];
        }
        
        // Event listeners
        if (this.addButton) {
            this.addButton.addEventListener('click', () => this.addNewButton());
        }
        
        // רינדור ראשוני
        this.render();
    }
    
    addNewButton() {
        if (this.buttons.length >= this.options.maxButtons) {
            alert(`ניתן להוסיף עד ${this.options.maxButtons} כפתורים`);
            return;
        }
        
        const newButton = {
            text: 'כפתור חדש',
            link: '#',
            newTab: false,
            style: 'filled',
            paddingTop: '',
            paddingBottom: '',
            paddingRight: '',
            paddingLeft: '',
            marginTop: '',
            marginBottom: '',
            rounded: 0,
            fullWidth: false,
            icon: ''
        };
        
        this.buttons.push(newButton);
        this.render();
        this.updateHiddenInput();
        this.triggerChange();
    }
    
    deleteButton(index) {
        if (this.buttons.length <= this.options.minButtons) {
            alert(`חייב להיות לפחות ${this.options.minButtons} כפתור`);
            return;
        }
        
        this.buttons.splice(index, 1);
        this.render();
        this.updateHiddenInput();
        this.triggerChange();
    }
    
    updateButton(index, field, value) {
        if (this.buttons[index]) {
            this.buttons[index][field] = value;
            
            // עדכון חלק של השדה הספציפי במקום רינדור מחדש
            this.updateSpecificField(index, field, value);
            
            this.updateHiddenInput();
            this.triggerChange();
        }
    }
    
    updateSpecificField(index, field, value) {
        const buttonElement = this.container.children[index];
        if (!buttonElement) return;
        
        switch(field) {
            case 'text':
                const textInput = buttonElement.querySelector('input[placeholder="טקסט הכפתור"]');
                if (textInput && textInput.value !== value) {
                    textInput.value = value;
                }
                break;
                
            case 'link':
                const linkInput = buttonElement.querySelector('input[placeholder="קישור"]');
                if (linkInput && linkInput.value !== value) {
                    linkInput.value = value;
                }
                break;
                
            case 'newTab':
                const newTabInput = buttonElement.querySelector('input[type="checkbox"]');
                if (newTabInput && newTabInput.checked !== value) {
                    newTabInput.checked = value;
                }
                break;
                
            case 'style':
                const styleButtons = buttonElement.querySelectorAll('.btn-style-option');
                styleButtons.forEach(btn => {
                    if (btn.dataset.style === value) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
                break;
        }
    }
    
    render() {
        this.container.innerHTML = '';
        
        this.buttons.forEach((button, index) => {
            const buttonHtml = this.createButtonHtml(button, index);
            this.container.insertAdjacentHTML('beforeend', buttonHtml);
        });
        
        // הוספת event listeners לכפתורים החדשים
        this.setupButtonListeners();
    }
    
    createButtonHtml(button, index) {
        const iconOptions = this.options.showIcons ? this.getIconOptions(button.icon) : '';
        const hoverSettings = this.options.showHover ? this.getHoverSettings(button, index) : '';
        
        return `
            <div class="border border-gray-200 rounded-lg p-4 bg-white" data-button-index="${index}">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="font-medium text-gray-700">כפתור ${index + 1}</h4>
                    <button type="button" class="text-red-500 hover:text-red-700 delete-button" data-index="${index}">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 gap-3">
                    <!-- טקסט וקישור -->
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" value="${button.text}" placeholder="טקסט הכפתור" 
                               class="border border-gray-300 rounded px-3 py-2 text-sm"
                               data-field="text" data-index="${index}">
                        <input type="text" value="${button.link}" placeholder="קישור" 
                               class="border border-gray-300 rounded px-3 py-2 text-sm"
                               data-field="link" data-index="${index}">
                    </div>
                    
                    <!-- אפשרויות -->
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" ${button.newTab ? 'checked' : ''} 
                                   data-field="newTab" data-index="${index}">
                            פתח בחלון חדש
                        </label>
                        
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" ${button.fullWidth ? 'checked' : ''} 
                                   data-field="fullWidth" data-index="${index}">
                            רוחב מלא
                        </label>
                    </div>
                    
                    <!-- סגנון כפתור -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">סגנון</label>
                        <div class="flex gap-2">
                            <button type="button" class="btn-style-option px-3 py-1 text-xs border rounded ${button.style === 'filled' ? 'active bg-blue-100 border-blue-300' : 'bg-gray-50 border-gray-300'}" 
                                    data-style="filled" data-index="${index}">מלא</button>
                            <button type="button" class="btn-style-option px-3 py-1 text-xs border rounded ${button.style === 'outline' ? 'active bg-blue-100 border-blue-300' : 'bg-gray-50 border-gray-300'}" 
                                    data-style="outline" data-index="${index}">מסגרת</button>
                            <button type="button" class="btn-style-option px-3 py-1 text-xs border rounded ${button.style === 'ghost' ? 'active bg-blue-100 border-blue-300' : 'bg-gray-50 border-gray-300'}" 
                                    data-style="ghost" data-index="${index}">שקוף</button>
                        </div>
                    </div>
                    
                    ${iconOptions}
                    
                    <!-- מרווחים -->
                    <div class="grid grid-cols-4 gap-2">
                        <input type="number" min="0" value="${button.paddingTop}" placeholder="Padding Top" 
                               class="border border-gray-300 rounded px-2 py-1 text-xs"
                               data-field="paddingTop" data-index="${index}">
                        <input type="number" min="0" value="${button.paddingBottom}" placeholder="Padding Bottom" 
                               class="border border-gray-300 rounded px-2 py-1 text-xs"
                               data-field="paddingBottom" data-index="${index}">
                        <input type="number" min="0" value="${button.paddingRight}" placeholder="Padding Right" 
                               class="border border-gray-300 rounded px-2 py-1 text-xs"
                               data-field="paddingRight" data-index="${index}">
                        <input type="number" min="0" value="${button.paddingLeft}" placeholder="Padding Left" 
                               class="border border-gray-300 rounded px-2 py-1 text-xs"
                               data-field="paddingLeft" data-index="${index}">
                    </div>
                    
                    <div class="grid grid-cols-3 gap-2">
                        <input type="number" min="0" value="${button.marginTop}" placeholder="Margin Top" 
                               class="border border-gray-300 rounded px-2 py-1 text-xs"
                               data-field="marginTop" data-index="${index}">
                        <input type="number" min="0" value="${button.marginBottom}" placeholder="Margin Bottom" 
                               class="border border-gray-300 rounded px-2 py-1 text-xs"
                               data-field="marginBottom" data-index="${index}">
                        <input type="number" min="0" max="50" value="${button.rounded || 0}" placeholder="עיגול פינות" 
                               class="border border-gray-300 rounded px-2 py-1 text-xs"
                               data-field="rounded" data-index="${index}">
                    </div>
                    
                    ${hoverSettings}
                </div>
            </div>
        `;
    }
    
    getIconOptions(selectedIcon) {
        return `
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">אייקון</label>
                <select class="border border-gray-300 rounded px-3 py-2 text-sm w-full" 
                        data-field="icon" data-index="${index}">
                    <option value="">ללא אייקון</option>
                    <option value="ri-shopping-cart-line" ${selectedIcon === 'ri-shopping-cart-line' ? 'selected' : ''}>עגלת קניות</option>
                    <option value="ri-heart-line" ${selectedIcon === 'ri-heart-line' ? 'selected' : ''}>לב</option>
                    <option value="ri-star-line" ${selectedIcon === 'ri-star-line' ? 'selected' : ''}>כוכב</option>
                    <option value="ri-arrow-left-line" ${selectedIcon === 'ri-arrow-left-line' ? 'selected' : ''}>חץ שמאל</option>
                    <option value="ri-arrow-right-line" ${selectedIcon === 'ri-arrow-right-line' ? 'selected' : ''}>חץ ימין</option>
                    <option value="ri-download-line" ${selectedIcon === 'ri-download-line' ? 'selected' : ''}>הורדה</option>
                    <option value="ri-phone-line" ${selectedIcon === 'ri-phone-line' ? 'selected' : ''}>טלפון</option>
                    <option value="ri-mail-line" ${selectedIcon === 'ri-mail-line' ? 'selected' : ''}>אימייל</option>
                </select>
            </div>
        `;
    }
    
    getHoverSettings(button, index) {
        return `
            <div class="border-t pt-3 mt-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">הגדרות Hover</label>
                <div class="grid grid-cols-3 gap-2">
                    <input type="text" value="${button.hoverBgColor || ''}" placeholder="צבע רקע" 
                           class="border border-gray-300 rounded px-2 py-1 text-xs"
                           data-field="hoverBgColor" data-index="${index}">
                    <input type="text" value="${button.hoverTextColor || ''}" placeholder="צבע טקסט" 
                           class="border border-gray-300 rounded px-2 py-1 text-xs"
                           data-field="hoverTextColor" data-index="${index}">
                    <input type="text" value="${button.hoverBorderColor || ''}" placeholder="צבע מסגרת" 
                           class="border border-gray-300 rounded px-2 py-1 text-xs"
                           data-field="hoverBorderColor" data-index="${index}">
                </div>
            </div>
        `;
    }
    
    setupButtonListeners() {
        // מחיקת כפתורים
        this.container.querySelectorAll('.delete-button').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.target.closest('.delete-button').dataset.index);
                this.deleteButton(index);
            });
        });
        
        // עדכון שדות
        this.container.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('input', (e) => {
                const index = parseInt(e.target.dataset.index);
                const field = e.target.dataset.field;
                let value = e.target.value;
                
                if (e.target.type === 'checkbox') {
                    value = e.target.checked;
                }
                
                this.updateButton(index, field, value);
            });
        });
        
        // כפתורי סגנון
        this.container.querySelectorAll('.btn-style-option').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.target.dataset.index);
                const style = e.target.dataset.style;
                this.updateButton(index, 'style', style);
            });
        });
    }
    
    updateHiddenInput() {
        this.hiddenInput.value = JSON.stringify(this.buttons);
    }
    
    triggerChange() {
        const event = new Event('change', { bubbles: true });
        this.hiddenInput.dispatchEvent(event);
    }
    
    getButtons() {
        return this.buttons;
    }
    
    setButtons(buttons) {
        this.buttons = buttons;
        this.render();
        this.updateHiddenInput();
    }
}; 