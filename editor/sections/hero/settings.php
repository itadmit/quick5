<?php
/**
 * Hero Section Settings - הגדרות מתקדמות עם Responsive Design
 */

// טעינת קומפוננטים משותפים
require_once '../../shared/components/text-input.php';
require_once '../../shared/components/responsive-typography.php';
require_once '../../shared/components/simple-background.php';
require_once '../../shared/components/select.php';

?>
<div class="hero-settings space-y-6">
    
    <!-- הגדרות כלליות -->
    <div class="settings-group border border-gray-200 rounded-lg p-4">
        <h3 class="font-medium text-sm text-gray-900 mb-4 flex items-center gap-2">
            <i class="ri-settings-3-line text-gray-600"></i>
            הגדרות כלליות
        </h3>
        
        <div class="grid grid-cols-1 gap-4">
            <!-- כותרת -->
            <?php echo renderTextInput([
                'label' => 'כותרת ראשית',
                'path' => 'content.title.text',
                'placeholder' => 'הכנס כותרת...',
                'required' => true,
                'maxlength' => 100
            ]); ?>
            
            <!-- תת-כותרת -->
            <?php echo renderTextInput([
                'label' => 'תת-כותרת',
                'path' => 'content.subtitle.text',
                'placeholder' => 'הכנס תת-כותרת...' . "\n" . 'ניתן לרדת שורה עם Enter',
                'maxlength' => 500,
                'type' => 'textarea',
                'rows' => 3
            ]); ?>
        </div>
    </div>
    
    <!-- הגדרות כפתורים -->
    <?php 
    // טען את הקומפוננט החדש
    require_once __DIR__ . '/../../shared/components/buttons-repeater.php';
    
    echo renderButtonsRepeater([
        'title' => 'כפתורי פעולה',
        'basePath' => 'content.buttons',
        'maxButtons' => 5
    ]); 
    ?>
    
    <!-- רקע פשוט -->
    <?php
    $backgroundAccordionId = 'background_accordion_' . uniqid();
    ?>
    <div class="settings-group border border-gray-200 rounded-lg mb-4">
        <!-- כותרת האקורדיון - לחיצה -->
        <button type="button" class="accordion-header w-full p-4 flex items-center justify-between text-right transition-colors hover:bg-gray-50" 
                onclick="toggleAccordion('<?php echo $backgroundAccordionId; ?>')">
            
            <!-- צד ימין - אייקון וטקסט -->
            <div class="flex items-center gap-2">
                <i class="ri-image-line text-purple-600"></i>
                <span class="font-medium text-sm text-gray-900">רקע הסקשן</span>
            </div>
            
            <!-- צד שמאל - חץ -->
            <i class="ri-arrow-up-s-line text-gray-500 transition-transform duration-200" id="arrow_<?php echo $backgroundAccordionId; ?>" style="transform: rotate(180deg);"></i>
        </button>
        
        <!-- תוכן האקורדיון - פתוח בדיפולט -->
        <div id="<?php echo $backgroundAccordionId; ?>" class="accordion-content">
            <div class="p-4 border-t border-gray-200">
        <?php 
        echo renderSimpleBackground([
            'label' => '',
            'basePath' => 'styles',
            'showVideo' => true,
            'showMobileFields' => true
        ]);
        ?>
            </div>
        </div>
    </div>

    <!-- מידות וגדלים -->
    <?php
    $accordionId = 'accordion_' . uniqid();
    ?>
    <div class="settings-group border border-gray-200 rounded-lg mb-4">
        <!-- כותרת האקורדיון - לחיצה -->
        <button type="button" class="accordion-header w-full p-4 flex items-center justify-between text-right transition-colors hover:bg-gray-50" 
                onclick="toggleAccordion('<?php echo $accordionId; ?>')">
            
            <!-- צד ימין - אייקון וטקסט -->
            <div class="flex items-center gap-2">
                <i class="ri-ruler-2-line text-emerald-600"></i>
                <span class="font-medium text-sm text-gray-900">מידות וגדלים</span>
            </div>
            
            <!-- צד שמאל - חץ -->
            <i class="ri-arrow-down-s-line text-gray-500 transition-transform duration-200" id="arrow_<?php echo $accordionId; ?>"></i>
        </button>
        
        <!-- תוכן האקורדיון -->
        <div id="<?php echo $accordionId; ?>" class="accordion-content" style="display: none;">
            <div class="p-4 border-t border-gray-200">
        
        <div class="grid grid-cols-1 gap-4">
            <!-- רוחב הסקשן -->
            <div class="mb-4">
                <label class="settings-label text-xs font-medium text-gray-700 block mb-2">רוחב הסקשן</label>
                <select class="w-full border border-gray-300 rounded px-3 py-2 text-sm" data-path="styles.section-width">
                    <option value="full">רוחב מלא</option>
                    <option value="container">קונטיינר (מוגבל)</option>
                    <option value="custom">מותאם אישית</option>
                </select>
            </div>

            <!-- Max Width (מופיע רק כשבוחרים מותאם אישית) -->
            <div class="custom-width-settings" style="display: none;">
                <label class="settings-label text-xs font-medium text-gray-700 block mb-2">רוחב מקסימלי</label>
                <input type="text" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" 
                       placeholder="1200px" data-path="styles.max-width">
            </div>

            <!-- גובה responsive -->
            <?php 
            require_once __DIR__ . '/../../shared/components/responsive-height.php';
            echo renderResponsiveHeight([
                'title' => 'גובה הסקשן',
                'basePath' => 'styles.height'
            ]); 
            ?>
            
            <!-- Min Height -->
            <div class="mb-4">
                <label class="settings-label text-xs font-medium text-gray-700 block mb-2">גובה מינימלי</label>
                <input type="text" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" 
                       placeholder="300px" data-path="styles.min-height">
            </div>

            <!-- Max Height -->
            <div class="mb-4">
                <label class="settings-label text-xs font-medium text-gray-700 block mb-2">גובה מקסימלי</label>
                <input type="text" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" 
                       placeholder="900px" data-path="styles.max-height">
            </div>
            </div> <!-- סיום grid -->
            </div> <!-- סיום padding container -->
        </div> <!-- סיום accordion-content -->
    </div> <!-- סיום component -->
    
    <!-- טיפוגרפיה -->
    <?php
    $typographyAccordionId = 'typography_accordion_' . uniqid();
    ?>
    <div class="settings-group border border-gray-200 rounded-lg mb-4">
        <!-- כותרת האקורדיון - לחיצה -->
        <button type="button" class="accordion-header w-full p-4 flex items-center justify-between text-right transition-colors hover:bg-gray-50" 
                onclick="toggleAccordion('<?php echo $typographyAccordionId; ?>')">
            
            <!-- צד ימין - אייקון וטקסט -->
            <div class="flex items-center gap-2">
                <i class="ri-text text-indigo-600"></i>
                <span class="font-medium text-sm text-gray-900">טיפוגרפיה</span>
            </div>
            
            <!-- צד שמאל - חץ -->
            <i class="ri-arrow-up-s-line text-gray-500 transition-transform duration-200" id="arrow_<?php echo $typographyAccordionId; ?>" style="transform: rotate(180deg);"></i>
        </button>
        
        <!-- תוכן האקורדיון - פתוח בדיפולט -->
        <div id="<?php echo $typographyAccordionId; ?>" class="accordion-content">
            <div class="p-4 border-t border-gray-200">
                
                <!-- Device Mode Switcher -->
                <div class="flex items-center justify-center mb-4">
                    <div class="device-switcher flex bg-gray-100 rounded-lg p-1" data-component-id="typography_global">
                        <button type="button" class="device-btn active px-3 py-1 text-xs rounded transition-colors bg-white shadow-sm" data-device="desktop">
                            <i class="ri-computer-line mr-1"></i>מחשב
                        </button>
                        <button type="button" class="device-btn px-3 py-1 text-xs rounded transition-colors hover:bg-white" data-device="mobile">
                            <i class="ri-smartphone-line mr-1"></i>מובייל
                        </button>
                    </div>
                </div>

                <!-- טיפוגרפיה כותרת -->
                <?php echo renderResponsiveTypography([
                    'title' => 'טיפוגרפיית כותרת',
                    'basePath' => 'content.title.styles',
                    'showSize' => true,
                    'showWeight' => true,
                    'showColor' => true,
                    'showAlign' => true,
                    'showLineHeight' => true,
                    'colorPresets' => ['#ffffff', '#000000', '#374151', '#f3f4f6', '#ef4444', '#3b82f6'],
                    'hideDeviceSwitcher' => true  // נסתיר את הסוויצ'ר הפנימי
                ]); ?>
                
                <!-- טיפוגרפיה תוכן -->
                <?php echo renderResponsiveTypography([
                    'title' => 'טיפוגרפיית תוכן',
                    'basePath' => 'content.subtitle.styles',
                    'showSize' => true,
                    'showWeight' => true,
                    'showColor' => true,
                    'showAlign' => true,
                    'showLineHeight' => true,
                    'colorPresets' => ['#e5e7eb', '#d1d5db', '#9ca3af', '#6b7280', '#ffffff', '#000000'],
                    'hideDeviceSwitcher' => true  // נסתיר את הסוויצ'ר הפנימי
                ]); ?>
                
            </div>
        </div>
    </div>
    
    <!-- הגדרות מתקדמות -->
    <?php 
    // טען את הקומפוננטים החדשים
    require_once __DIR__ . '/../../shared/components/spacing-control.php';
    require_once __DIR__ . '/../../shared/components/visibility-control.php';
    
    // הסתרה לפי מכשיר - הסתרה במובייל/מחשב
    echo renderVisibilityControl([
        'title' => 'הסתרה לפי מכשיר',
        'basePath' => 'visibility',
        'icon' => 'ri-eye-line',
        'iconColor' => 'text-green-600'
    ]);
    
    // מרווחים עם 4 כיוונים
    echo renderSpacingControl([
        'title' => 'מרווחים',
        'basePath' => 'styles',
        'showPadding' => true,
        'showMargin' => true,
        'icon' => 'ri-expand-diagonal-line',
        'iconColor' => 'text-blue-600'
    ]);
    
    // מאפייני HTML
    echo renderHtmlAttributes([
        'title' => 'מאפייני HTML',
        'basePath' => 'attributes',
        'showId' => true,
        'showClass' => true
    ]);
    ?>
    
    <!-- אנימציה -->
    <?php
    $animationAccordionId = 'animation_accordion_' . uniqid();
    ?>
    <div class="settings-group border border-gray-200 rounded-lg mb-4">
        <!-- כותרת האקורדיון - לחיצה -->
        <button type="button" class="accordion-header w-full p-4 flex items-center justify-between text-right transition-colors hover:bg-gray-50" 
                onclick="toggleAccordion('<?php echo $animationAccordionId; ?>')">
            
            <!-- צד ימין - אייקון וטקסט -->
            <div class="flex items-center gap-2">
                <i class="ri-magic-line text-purple-600"></i>
                <span class="font-medium text-sm text-gray-900">אנימציה</span>
            </div>
            
            <!-- צד שמאל - חץ -->
            <i class="ri-arrow-down-s-line text-gray-500 transition-transform duration-200" id="arrow_<?php echo $animationAccordionId; ?>"></i>
        </button>
        
        <!-- תוכן האקורדיון -->
        <div id="<?php echo $animationAccordionId; ?>" class="accordion-content" style="display: none;">
            <div class="p-4 border-t border-gray-200">
        
        <?php echo renderSelect([
            'label' => 'אנימציה בכניסה',
            'path' => 'styles.animation',
            'options' => [
                'none' => 'ללא אנימציה',
                'fade-in' => 'הופעה הדרגתית',
                'slide-up' => 'החלקה מלמטה',
                'zoom-in' => 'זום פנימה'
            ]
        ]); ?>
        
            </div>
        </div>
    </div>
</div>

<script>
// הוספת תמיכה בסוויצ'ר טיפוגרפיה גלובלי
document.addEventListener('DOMContentLoaded', function() {
    // מצא את הסוויצ'ר הגלובלי של הטיפוגרפיה
    const globalSwitcher = document.querySelector('[data-component-id="typography_global"]');
    
    if (globalSwitcher) {
        const globalButtons = globalSwitcher.querySelectorAll('.device-btn');
        
        globalButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetDevice = this.dataset.device;
                
                // עדכן את המצב של הכפתורים הגלובליים
                globalButtons.forEach(btn => btn.classList.remove('active', 'bg-white', 'shadow-sm'));
                this.classList.add('active', 'bg-white', 'shadow-sm');
                
                // מצא את כל הרכיבי טיפוגרפיה ועדכן אותם
                const typographyComponents = document.querySelectorAll('.responsive-typography-component');
                
                typographyComponents.forEach(component => {
                    // עדכן את הסוויצ'רים הפנימיים
                    const internalSwitchers = component.querySelectorAll('.device-switcher .device-btn');
                    internalSwitchers.forEach(btn => {
                        btn.classList.remove('active', 'bg-white', 'shadow-sm');
                        if (btn.dataset.device === targetDevice) {
                            btn.classList.add('active', 'bg-white', 'shadow-sm');
                        }
                    });
                    
                    // הצג/הסתר את האזורים המתאימים
                    const deviceSettings = component.querySelectorAll('.device-settings');
                    deviceSettings.forEach(setting => {
                        if (setting.dataset.device === targetDevice) {
                            setting.style.display = 'block';
                        } else {
                            setting.style.display = 'none';
                        }
                    });
                });
                
                console.log('📱 Global typography device switched to:', targetDevice);
            });
        });
    }
});

/**
 * ניהול כפתורים דינמי
 */
class ButtonsManager {
    constructor() {
        this.buttons = [];
        this.buttonsList = document.getElementById('buttons-list');
        this.addButton = document.querySelector('.add-button-btn');
        
        this.init();
    }
    
    init() {
        this.addButton.addEventListener('click', () => {
            this.addNewButton();
        });
        
        // טעינת כפתורים קיימים אם יש
        this.loadExistingButtons();
    }
    
    addNewButton() {
        const buttonId = 'btn_' + Date.now();
        const button = {
            id: buttonId,
            text: 'כפתור חדש',
            url: '#',
            style: 'primary',
            styles: {
                'background-color': '#3b82f6',
                'color': '#ffffff',
                'padding': '12px 24px',
                'border-radius': '6px',
                'font-weight': '500'
            }
        };
        
        this.buttons.push(button);
        this.renderButton(button);
        this.updateButtonsData();
    }
    
    renderButton(button) {
        const buttonHtml = `
            <div class="button-item border border-gray-200 rounded p-3" data-button-id="${button.id}">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium">כפתור ${this.buttons.length}</span>
                    <button type="button" class="remove-button text-red-500 hover:text-red-700">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="settings-label text-xs">טקסט כפתור</label>
                        <input type="text" class="settings-input text-xs button-text" 
                               value="${button.text}" 
                               data-path="content.buttons.${button.id}.text">
                    </div>
                    
                    <div>
                        <label class="settings-label text-xs">קישור</label>
                        <input type="url" class="settings-input text-xs button-url" 
                               value="${button.url}" 
                               data-path="content.buttons.${button.id}.url">
                    </div>
                </div>
                
                <div class="mt-3">
                    <label class="settings-label text-xs">סגנון כפתור</label>
                    <select class="settings-input text-xs button-style" data-path="content.buttons.${button.id}.style">
                        <option value="primary" ${button.style === 'primary' ? 'selected' : ''}>ראשי (כחול)</option>
                        <option value="secondary" ${button.style === 'secondary' ? 'selected' : ''}>משני (אפור)</option>
                        <option value="success" ${button.style === 'success' ? 'selected' : ''}>הצלחה (ירוק)</option>
                        <option value="outline" ${button.style === 'outline' ? 'selected' : ''}>מתאר</option>
                        <option value="custom" ${button.style === 'custom' ? 'selected' : ''}>מותאם אישית</option>
                    </select>
                </div>
                
                <div class="custom-styles mt-3" style="display: ${button.style === 'custom' ? 'block' : 'none'};">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="settings-label text-xs">צבע רקע</label>
                            <input type="color" class="w-full h-8 border border-gray-300 rounded" 
                                   value="${button.styles['background-color']}"
                                   data-path="content.buttons.${button.id}.styles.background-color">
                        </div>
                        <div>
                            <label class="settings-label text-xs">צבע טקסט</label>
                            <input type="color" class="w-full h-8 border border-gray-300 rounded" 
                                   value="${button.styles.color}"
                                   data-path="content.buttons.${button.id}.styles.color">
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        this.buttonsList.insertAdjacentHTML('beforeend', buttonHtml);
        
        // קישור אירועים
        const buttonElement = this.buttonsList.lastElementChild;
        this.bindButtonEvents(buttonElement, button);
    }
    
    bindButtonEvents(element, button) {
        // מחיקת כפתור
        element.querySelector('.remove-button').addEventListener('click', () => {
            this.removeButton(button.id);
        });
        
        // שינוי סגנון
        element.querySelector('.button-style').addEventListener('change', (e) => {
            const customStyles = element.querySelector('.custom-styles');
            customStyles.style.display = e.target.value === 'custom' ? 'block' : 'none';
            this.updateButtonsData();
        });
        
        // שינויים בשדות
        element.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('input', () => {
                this.updateButtonsData();
            });
        });
    }
    
    removeButton(buttonId) {
        this.buttons = this.buttons.filter(btn => btn.id !== buttonId);
        const element = document.querySelector(`[data-button-id="${buttonId}"]`);
        if (element) {
            element.remove();
        }
        this.updateButtonsData();
    }
    
    updateButtonsData() {
        // עדכון הנתונים מהממשק
        this.buttons.forEach(button => {
            const element = document.querySelector(`[data-button-id="${button.id}"]`);
            if (element) {
                button.text = element.querySelector('.button-text').value;
                button.url = element.querySelector('.button-url').value;
                button.style = element.querySelector('.button-style').value;
                
                if (button.style === 'custom') {
                    const bgColor = element.querySelector('input[data-path*="background-color"]');
                    const textColor = element.querySelector('input[data-path*="color"]');
                    
                    if (bgColor) button.styles['background-color'] = bgColor.value;
                    if (textColor) button.styles.color = textColor.value;
                }
            }
        });
        
        // יצירת hidden input עם הנתונים
        let hiddenInput = document.querySelector('input[data-path="content.buttons"]');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.dataset.path = 'content.buttons';
            document.querySelector('.hero-settings').appendChild(hiddenInput);
        }
        
        hiddenInput.value = JSON.stringify(this.buttons);
        hiddenInput.dispatchEvent(new Event('change'));
    }
    
    loadExistingButtons() {
        // TODO: טעינת כפתורים קיימים מהנתונים
    }
}

// אתחול מנהל הכפתורים
document.addEventListener('DOMContentLoaded', () => {
    new ButtonsManager();
});
</script> 