<?php
/**
 * Categories Section Settings - הגדרות סקשן גריד קטגוריות
 */

// טעינת קומפוננטים משותפים
require_once '../../shared/components/text-input.php';
require_once '../../shared/components/responsive-typography.php';
require_once '../../shared/components/simple-background.php';
require_once '../../shared/components/select.php';

?>
<div class="categories-settings space-y-6">
    
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
    
    <!-- הגדרות גריד הקטגוריות -->
    <div class="settings-group border border-gray-200 rounded-lg p-4">
        <h3 class="font-medium text-sm text-gray-900 mb-4 flex items-center gap-2">
            <i class="ri-grid-line text-orange-600"></i>
            הגדרות גריד הקטגוריות
        </h3>
        
        <div class="grid grid-cols-1 gap-4">
            <!-- מספר עמודות בדסקטופ -->
            <div class="mb-4">
                <label class="settings-label text-xs font-medium text-gray-700 block mb-2">עמודות במחשב</label>
                <select class="w-full border border-gray-300 rounded px-3 py-2 text-sm" data-path="content.grid.columns.desktop">
                    <option value="2">2 עמודות</option>
                    <option value="3">3 עמודות</option>
                    <option value="4" selected>4 עמודות</option>
                    <option value="5">5 עמודות</option>
                    <option value="6">6 עמודות</option>
                </select>
            </div>

            <!-- מספר עמודות במובייל -->
            <div class="mb-4">
                <label class="settings-label text-xs font-medium text-gray-700 block mb-2">עמודות במובייל</label>
                <select class="w-full border border-gray-300 rounded px-3 py-2 text-sm" data-path="content.grid.columns.mobile">
                    <option value="1">עמודה אחת</option>
                    <option value="2" selected>2 עמודות</option>
                    <option value="3">3 עמודות</option>
                </select>
            </div>

            <!-- רווח בין קטגוריות -->
            <div class="mb-4">
                <label class="settings-label text-xs font-medium text-gray-700 block mb-2">רווח בין קטגוריות</label>
                <select class="w-full border border-gray-300 rounded px-3 py-2 text-sm" data-path="content.grid.gap">
                    <option value="16px">רווח קטן (16px)</option>
                    <option value="24px" selected>רווח בינוני (24px)</option>
                    <option value="32px">רווח גדול (32px)</option>
                    <option value="48px">רווח גדול מאוד (48px)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- ניהול קטגוריות -->
    <div class="settings-group border border-gray-200 rounded-lg p-4">
        <h3 class="font-medium text-sm text-gray-900 mb-4 flex items-center gap-2">
            <i class="ri-folder-line text-blue-600"></i>
            ניהול קטגוריות
        </h3>
        
        <div id="categories-list" class="space-y-4">
            <!-- קטגוריות יתווספו דינמית בJavaScript -->
        </div>
        
        <button type="button" class="add-category-btn w-full mt-4 px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-colors">
            <i class="ri-add-line mr-2"></i>
            הוסף קטגוריה
        </button>
    </div>
    
    <!-- הגדרות כפתורים -->
    <?php 
    // טען את הקומפוננט החדש
    require_once __DIR__ . '/../../shared/components/buttons-repeater.php';
    
    echo renderButtonsRepeater([
        'title' => 'כפתורי פעולה',
        'basePath' => 'content.buttons',
        'maxButtons' => 3
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
                    'colorPresets' => ['#1f2937', '#374151', '#000000', '#ffffff', '#3b82f6', '#ef4444'],
                    'hideDeviceSwitcher' => true  // נסתיר את הסוויצ'ר הפנימי
                ]); ?>
                
                <!-- טיפוגרפיה תוכן -->
                <?php echo renderResponsiveTypography([
                    'title' => 'טיפוגרפיית תת-כותרת',
                    'basePath' => 'content.subtitle.styles',
                    'showSize' => true,
                    'showWeight' => true,
                    'showColor' => true,
                    'showAlign' => true,
                    'showLineHeight' => true,
                    'colorPresets' => ['#6b7280', '#9ca3af', '#374151', '#000000', '#ffffff', '#3b82f6'],
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
    function renderHtmlAttributes($options = []) {
        $defaults = [
            'title' => 'מאפייני HTML',
            'basePath' => 'attributes',
            'showId' => true,
            'showClass' => true
        ];
        
        $opts = array_merge($defaults, $options);
        
        // יצירת ID ייחודי לאקורדיון
        $accordionId = 'accordion_' . uniqid();
        
        $html = '<div class="html-attributes-component border border-gray-200 rounded-lg mb-4">';
        
        // כותרת האקורדיון - לחיצה
        $html .= '<button type="button" class="accordion-header w-full p-4 flex items-center justify-between text-right transition-colors hover:bg-gray-50" ';
        $html .= 'onclick="toggleAccordion(\'' . $accordionId . '\')">';
        
        // צד ימין - אייקון וטקסט
        $html .= '<div class="flex items-center gap-2">';
        $html .= '<i class="ri-code-line text-purple-600"></i>';
        $html .= '<span class="font-medium text-sm text-gray-900">' . htmlspecialchars($opts['title']) . '</span>';
        $html .= '</div>';
        
        // צד שמאל - חץ
        $html .= '<i class="ri-arrow-down-s-line text-gray-500 transition-transform duration-200" id="arrow_' . $accordionId . '"></i>';
        $html .= '</button>';
        
        // תוכן האקורדיון
        $html .= '<div id="' . $accordionId . '" class="accordion-content" style="display: none;">';
        $html .= '<div class="p-4 border-t border-gray-200">';
        
        if ($opts['showId']) {
            $html .= '<div class="mb-4">';
            $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">ID מותאם אישית</label>';
            $html .= '<input type="text" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" ';
            $html .= 'placeholder="my-custom-id" data-path="' . $opts['basePath'] . '.id">';
            $html .= '</div>';
        }
        
        if ($opts['showClass']) {
            $html .= '<div class="mb-4">';
            $html .= '<label class="settings-label text-xs font-medium text-gray-700 block mb-2">כיתות CSS נוספות</label>';
            $html .= '<input type="text" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" ';
            $html .= 'placeholder="class1 class2" data-path="' . $opts['basePath'] . '.class">';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    echo renderHtmlAttributes([
        'title' => 'מאפייני HTML',
        'basePath' => 'attributes',
        'showId' => true,
        'showClass' => true
    ]);
    ?>
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
 * ניהול קטגוריות דינמי
 */
class CategoriesManager {
    constructor() {
        this.categories = [];
        this.categoriesList = document.getElementById('categories-list');
        this.addCategoryBtn = document.querySelector('.add-category-btn');
        
        this.init();
    }
    
    init() {
        this.addCategoryBtn.addEventListener('click', () => {
            this.addNewCategory();
        });
        
        // טעינת קטגוריות קיימות אם יש
        this.loadExistingCategories();
    }
    
    addNewCategory() {
        const categoryId = 'cat_' + Date.now();
        const category = {
            id: categoryId,
            name: 'קטגוריה חדשה',
            image: 'https://via.placeholder.com/300x200?text=קטגוריה+חדשה',
            url: '/category/new'
        };
        
        this.categories.push(category);
        this.renderCategory(category);
        this.updateCategoriesData();
    }
    
    renderCategory(category) {
        const categoryHtml = `
            <div class="category-item border border-gray-200 rounded p-3" data-category-id="${category.id}">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium">קטגוריה ${this.categories.length}</span>
                    <button type="button" class="remove-category text-red-500 hover:text-red-700">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 gap-3">
                    <div>
                        <label class="settings-label text-xs">שם הקטגוריה</label>
                        <input type="text" class="settings-input text-xs category-name" 
                               value="${category.name}" 
                               data-path="content.grid.categories.${category.id}.name">
                    </div>
                    
                    <div>
                        <label class="settings-label text-xs">קישור URL</label>
                        <input type="url" class="settings-input text-xs category-url" 
                               value="${category.url}" 
                               data-path="content.grid.categories.${category.id}.url">
                    </div>
                    
                    <div>
                        <label class="settings-label text-xs">תמונה</label>
                        <input type="url" class="settings-input text-xs category-image" 
                               value="${category.image}" 
                               data-path="content.grid.categories.${category.id}.image">
                    </div>
                </div>
            </div>
        `;
        
        this.categoriesList.insertAdjacentHTML('beforeend', categoryHtml);
        
        // קישור אירועים
        const categoryElement = this.categoriesList.lastElementChild;
        this.bindCategoryEvents(categoryElement, category);
    }
    
    bindCategoryEvents(element, category) {
        // מחיקת קטגוריה
        element.querySelector('.remove-category').addEventListener('click', () => {
            this.removeCategory(category.id);
        });
        
        // שינויים בשדות
        element.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', () => {
                this.updateCategoriesData();
            });
        });
    }
    
    removeCategory(categoryId) {
        this.categories = this.categories.filter(cat => cat.id !== categoryId);
        const element = document.querySelector(`[data-category-id="${categoryId}"]`);
        if (element) {
            element.remove();
        }
        this.updateCategoriesData();
    }
    
    updateCategoriesData() {
        // עדכון הנתונים מהממשק
        this.categories.forEach(category => {
            const element = document.querySelector(`[data-category-id="${category.id}"]`);
            if (element) {
                category.name = element.querySelector('.category-name').value;
                category.url = element.querySelector('.category-url').value;
                category.image = element.querySelector('.category-image').value;
            }
        });
        
        // יצירת hidden input עם הנתונים
        let hiddenInput = document.querySelector('input[data-path="content.grid.categories"]');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.dataset.path = 'content.grid.categories';
            document.querySelector('.categories-settings').appendChild(hiddenInput);
        }
        
        hiddenInput.value = JSON.stringify(this.categories);
        hiddenInput.dispatchEvent(new Event('change'));
    }
    
    loadExistingCategories() {
        // TODO: טעינת קטגוריות קיימות מהנתונים
    }
}

// אתחול מנהל הקטגוריות
document.addEventListener('DOMContentLoaded', () => {
    new CategoriesManager();
});
</script> 