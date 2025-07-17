<?php
/**
 * Button Repeater Component - רכיב ריפיטר כפתורים
 */

// Helper functions for escaping
if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
    }
}

/**
 * רינדור כפתור בודד
 */
function renderSingleButton($index, $button = []) {
    $defaultButton = [
        'text' => '',
        'url' => '',
        'openInNewTab' => false,
        'style' => 'primary',
        'paddingTop' => '12',
        'paddingBottom' => '12',
        'paddingLeft' => '24',
        'paddingRight' => '24',
        'marginTop' => '0',
        'marginBottom' => '8',
        'marginLeft' => '4',
        'marginRight' => '4',
        'bgColor' => '#3B82F6',
        'textColor' => '#FFFFFF',
        'borderColor' => '#3B82F6',
        'hoverBgColor' => '#2563EB',
        'hoverTextColor' => '#FFFFFF',
        'hoverBorderColor' => '#2563EB'
    ];
    
    $button = array_merge($defaultButton, $button);
    ?>
    
    <div class="button-item border border-gray-200 rounded-lg p-4 bg-gray-50" data-index="<?php echo $index; ?>">
        <div class="flex items-center justify-between mb-3">
            <h6 class="text-sm font-medium text-gray-700">כפתור <?php echo $index + 1; ?></h6>
            <button type="button" class="remove-button-btn text-red-500 hover:text-red-700" data-index="<?php echo $index; ?>">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
        
        <div class="space-y-4">
            <!-- Button Text & URL -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">טקסט הכפתור</label>
                    <input type="text" 
                           name="buttons[<?php echo $index; ?>][text]"
                           value="<?php echo esc_attr($button['text']); ?>"
                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                           placeholder="טקסט הכפתור">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        <i class="ri-external-link-line mr-1"></i>
                        קישור
                    </label>
                    <input type="url" 
                           name="buttons[<?php echo $index; ?>][url]"
                           value="<?php echo esc_attr($button['url']); ?>"
                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                           placeholder="https://...">
                </div>
            </div>
            
            <!-- New Tab & Style -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="buttons[<?php echo $index; ?>][openInNewTab]"
                               <?php echo $button['openInNewTab'] ? 'checked' : ''; ?>
                               class="mr-2">
                        <span class="text-xs font-medium text-gray-600">פתח בכרטיסה חדשה</span>
                    </label>
                </div>
                
                <div>
                    <!-- Hover state toggle -->
                    <label class="flex items-center">
                        <input type="checkbox" 
                               id="hoverToggle<?php echo $index; ?>"
                               class="hover-toggle mr-2">
                        <span class="text-xs font-medium text-gray-600">מצב הובר</span>
                    </label>
                </div>
            </div>
            
            <hr class="border-gray-200">
            
            <!-- Button Style Selection -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-2">סוג כפתור</label>
                <div class="grid grid-cols-4 gap-2">
                    <button type="button" 
                            class="style-btn flex flex-col items-center p-2 border border-gray-300 rounded text-xs hover:bg-gray-50 transition-colors <?php echo $button['style'] === 'primary' ? 'bg-blue-100 border-blue-500' : ''; ?>" 
                            data-style="primary">
                        <div class="w-6 h-6 bg-blue-600 rounded mb-1"></div>
                        <span>ראשי</span>
                    </button>
                    <button type="button" 
                            class="style-btn flex flex-col items-center p-2 border border-gray-300 rounded text-xs hover:bg-gray-50 transition-colors <?php echo $button['style'] === 'secondary' ? 'bg-blue-100 border-blue-500' : ''; ?>" 
                            data-style="secondary">
                        <div class="w-6 h-6 bg-gray-600 rounded mb-1"></div>
                        <span>משני</span>
                    </button>
                    <button type="button" 
                            class="style-btn flex flex-col items-center p-2 border border-gray-300 rounded text-xs hover:bg-gray-50 transition-colors <?php echo $button['style'] === 'outline' ? 'bg-blue-100 border-blue-500' : ''; ?>" 
                            data-style="outline">
                        <div class="w-6 h-6 border-2 border-gray-600 rounded mb-1"></div>
                        <span>מתאר</span>
                    </button>
                    <button type="button" 
                            class="style-btn flex flex-col items-center p-2 border border-gray-300 rounded text-xs hover:bg-gray-50 transition-colors <?php echo $button['style'] === 'custom' ? 'bg-blue-100 border-blue-500' : ''; ?>" 
                            data-style="custom">
                        <div class="w-6 h-6 bg-gradient-to-r from-purple-500 to-pink-500 rounded mb-1"></div>
                        <span>מותאם</span>
                    </button>
                </div>
                <input type="hidden" name="buttons[<?php echo $index; ?>][style]" value="<?php echo esc_attr($button['style']); ?>">
            </div>
            
            <hr class="border-gray-200">
            
            <!-- Color Controls -->
            <div class="color-controls">
                <div class="normal-colors" data-state="normal">
                    <h6 class="text-xs font-medium text-gray-600 mb-2">צבעים רגילים</h6>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">צבע רקע</label>
                            <div class="flex items-center gap-2">
                                <input type="color" 
                                       name="buttons[<?php echo $index; ?>][bgColor]"
                                       value="<?php echo esc_attr($button['bgColor']); ?>"
                                       class="w-8 h-8 border border-gray-300 rounded cursor-pointer">
                                <input type="text" 
                                       value="<?php echo esc_attr($button['bgColor']); ?>"
                                       class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                       placeholder="#FFFFFF">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">צבע טקסט</label>
                            <div class="flex items-center gap-2">
                                <input type="color" 
                                       name="buttons[<?php echo $index; ?>][textColor]"
                                       value="<?php echo esc_attr($button['textColor']); ?>"
                                       class="w-8 h-8 border border-gray-300 rounded cursor-pointer">
                                <input type="text" 
                                       value="<?php echo esc_attr($button['textColor']); ?>"
                                       class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                       placeholder="#000000">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">צבע מסגרת</label>
                            <div class="flex items-center gap-2">
                                <input type="color" 
                                       name="buttons[<?php echo $index; ?>][borderColor]"
                                       value="<?php echo esc_attr($button['borderColor']); ?>"
                                       class="w-8 h-8 border border-gray-300 rounded cursor-pointer">
                                <input type="text" 
                                       value="<?php echo esc_attr($button['borderColor']); ?>"
                                       class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                       placeholder="#000000">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="hover-colors hidden" data-state="hover">
                    <h6 class="text-xs font-medium text-gray-600 mb-2">צבעי הובר</h6>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">צבע רקע</label>
                            <div class="flex items-center gap-2">
                                <input type="color" 
                                       name="buttons[<?php echo $index; ?>][hoverBgColor]"
                                       value="<?php echo esc_attr($button['hoverBgColor']); ?>"
                                       class="w-8 h-8 border border-gray-300 rounded cursor-pointer">
                                <input type="text" 
                                       value="<?php echo esc_attr($button['hoverBgColor']); ?>"
                                       class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                       placeholder="#FFFFFF">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">צבע טקסט</label>
                            <div class="flex items-center gap-2">
                                <input type="color" 
                                       name="buttons[<?php echo $index; ?>][hoverTextColor]"
                                       value="<?php echo esc_attr($button['hoverTextColor']); ?>"
                                       class="w-8 h-8 border border-gray-300 rounded cursor-pointer">
                                <input type="text" 
                                       value="<?php echo esc_attr($button['hoverTextColor']); ?>"
                                       class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                       placeholder="#000000">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">צבע מסגרת</label>
                            <div class="flex items-center gap-2">
                                <input type="color" 
                                       name="buttons[<?php echo $index; ?>][hoverBorderColor]"
                                       value="<?php echo esc_attr($button['hoverBorderColor']); ?>"
                                       class="w-8 h-8 border border-gray-300 rounded cursor-pointer">
                                <input type="text" 
                                       value="<?php echo esc_attr($button['hoverBorderColor']); ?>"
                                       class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                       placeholder="#000000">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr class="border-gray-200">
            
            <!-- Padding -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-2">פדינג פנימי (px)</label>
                <div class="grid grid-cols-4 gap-2">
                    <div>
                        <input type="number" 
                               name="buttons[<?php echo $index; ?>][paddingTop]"
                               value="<?php echo esc_attr($button['paddingTop']); ?>"
                               min="0" max="100"
                               class="w-full px-1 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                               placeholder="למעלה">
                        <label class="text-xs text-gray-500 block text-center mt-1">למעלה</label>
                    </div>
                    <div>
                        <input type="number" 
                               name="buttons[<?php echo $index; ?>][paddingBottom]"
                               value="<?php echo esc_attr($button['paddingBottom']); ?>"
                               min="0" max="100"
                               class="w-full px-1 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                               placeholder="למטה">
                        <label class="text-xs text-gray-500 block text-center mt-1">למטה</label>
                    </div>
                    <div>
                        <input type="number" 
                               name="buttons[<?php echo $index; ?>][paddingLeft]"
                               value="<?php echo esc_attr($button['paddingLeft']); ?>"
                               min="0" max="100"
                               class="w-full px-1 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                               placeholder="שמאל">
                        <label class="text-xs text-gray-500 block text-center mt-1">שמאל</label>
                    </div>
                    <div>
                        <input type="number" 
                               name="buttons[<?php echo $index; ?>][paddingRight]"
                               value="<?php echo esc_attr($button['paddingRight']); ?>"
                               min="0" max="100"
                               class="w-full px-1 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                               placeholder="ימין">
                        <label class="text-xs text-gray-500 block text-center mt-1">ימין</label>
                    </div>
                </div>
            </div>
            
            <!-- Margin -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-2">מרגין חיצוני (px)</label>
                <div class="grid grid-cols-4 gap-2">
                    <div>
                        <input type="number" 
                               name="buttons[<?php echo $index; ?>][marginTop]"
                               value="<?php echo esc_attr($button['marginTop']); ?>"
                               min="0" max="100"
                               class="w-full px-1 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                               placeholder="למעלה">
                        <label class="text-xs text-gray-500 block text-center mt-1">למעלה</label>
                    </div>
                    <div>
                        <input type="number" 
                               name="buttons[<?php echo $index; ?>][marginBottom]"
                               value="<?php echo esc_attr($button['marginBottom']); ?>"
                               min="0" max="100"
                               class="w-full px-1 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                               placeholder="למטה">
                        <label class="text-xs text-gray-500 block text-center mt-1">למטה</label>
                    </div>
                    <div>
                        <input type="number" 
                               name="buttons[<?php echo $index; ?>][marginLeft]"
                               value="<?php echo esc_attr($button['marginLeft']); ?>"
                               min="0" max="100"
                               class="w-full px-1 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                               placeholder="שמאל">
                        <label class="text-xs text-gray-500 block text-center mt-1">שמאל</label>
                    </div>
                    <div>
                        <input type="number" 
                               name="buttons[<?php echo $index; ?>][marginRight]"
                               value="<?php echo esc_attr($button['marginRight']); ?>"
                               min="0" max="100"
                               class="w-full px-1 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                               placeholder="ימין">
                        <label class="text-xs text-gray-500 block text-center mt-1">ימין</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    (function() {
        const buttonItem = document.querySelector('[data-index="<?php echo $index; ?>"]');
        
        // Style button selection
        const styleBtns = buttonItem.querySelectorAll('.style-btn');
        const styleInput = buttonItem.querySelector('input[name*="[style]"]');
        
        styleBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const style = this.dataset.style;
                
                // Update active button
                styleBtns.forEach(b => b.classList.remove('bg-blue-100', 'border-blue-500'));
                this.classList.add('bg-blue-100', 'border-blue-500');
                
                // Update hidden input
                if (styleInput) {
                    styleInput.value = style;
                    styleInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        });
        
        // Hover toggle
        const hoverToggle = buttonItem.querySelector('.hover-toggle');
        const normalColors = buttonItem.querySelector('.normal-colors');
        const hoverColors = buttonItem.querySelector('.hover-colors');
        
        if (hoverToggle && normalColors && hoverColors) {
            hoverToggle.addEventListener('change', function() {
                if (this.checked) {
                    normalColors.classList.add('hidden');
                    hoverColors.classList.remove('hidden');
                } else {
                    normalColors.classList.remove('hidden');
                    hoverColors.classList.add('hidden');
                }
            });
        }
        
        // Color input sync
        const colorInputs = buttonItem.querySelectorAll('input[type="color"]');
        colorInputs.forEach(colorInput => {
            const textInput = colorInput.nextElementSibling;
            
            colorInput.addEventListener('change', function() {
                textInput.value = this.value;
                textInput.dispatchEvent(new Event('input', { bubbles: true }));
            });
            
            textInput.addEventListener('input', function() {
                if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                    colorInput.value = this.value;
                }
            });
        });
        
    })();
    </script>
    
    <?php
}

/**
 * רינדור ריפיטר כפתורים
 */
function renderButtonsRepeater($buttons = []) {
    if (empty($buttons)) {
        $buttons = [
            [
                'text' => 'קנה עכשיו',
                'url' => '#',
                'style' => 'primary'
            ]
        ];
    }
    ?>
    
    <div class="buttons-repeater-container">
        <div id="buttonsContainer" class="space-y-4">
            <?php foreach ($buttons as $index => $button): ?>
                <?php renderSingleButton($index, $button); ?>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-4">
            <button type="button" 
                    id="addButtonBtn" 
                    class="w-full px-3 py-2 text-sm border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-colors">
                <i class="ri-add-line mr-1"></i>
                הוסף כפתור
            </button>
        </div>
    </div>
    
    <script>
    // Button repeater functionality
    (function() {
        let buttonIndex = <?php echo count($buttons); ?>;
        
        // Add button
        document.getElementById('addButtonBtn').addEventListener('click', function() {
            const container = document.getElementById('buttonsContainer');
            const newButtonHtml = createButtonTemplate(buttonIndex);
            
            container.insertAdjacentHTML('beforeend', newButtonHtml);
            
            // Attach events to new button
            attachButtonEvents(buttonIndex);
            
            buttonIndex++;
            updateButtonNumbers();
            
            // Trigger change event for live preview
            triggerSettingsUpdate();
        });
        
        // Create button template
        function createButtonTemplate(index) {
            return `
                <div class="button-item border border-gray-200 rounded-lg p-4 bg-gray-50" data-index="${index}">
                    <div class="flex items-center justify-between mb-3">
                        <h6 class="text-sm font-medium text-gray-700">כפתור ${index + 1}</h6>
                        <button type="button" class="remove-button-btn text-red-500 hover:text-red-700" data-index="${index}">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- Button Text & URL -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">טקסט הכפתור</label>
                                <input type="text" 
                                       name="buttons[${index}][text]"
                                       value=""
                                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                       placeholder="טקסט הכפתור">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    <i class="ri-external-link-line mr-1"></i>
                                    קישור
                                </label>
                                <input type="url" 
                                       name="buttons[${index}][url]"
                                       value=""
                                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                       placeholder="https://...">
                            </div>
                        </div>
                        
                        <!-- New Tab & Hover Toggle -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="buttons[${index}][openInNewTab]"
                                           class="mr-2">
                                    <span class="text-xs font-medium text-gray-600">פתח בכרטיסה חדשה</span>
                                </label>
                            </div>
                            
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           class="hover-toggle mr-2">
                                    <span class="text-xs font-medium text-gray-600">מצב הובר</span>
                                </label>
                            </div>
                        </div>
                        
                        <hr class="border-gray-200">
                        
                        <!-- Button Style Selection -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-2">סוג כפתור</label>
                            <div class="grid grid-cols-4 gap-2">
                                <button type="button" class="style-btn flex flex-col items-center p-2 border border-gray-300 rounded text-xs hover:bg-gray-50 transition-colors bg-blue-100 border-blue-500" data-style="primary">
                                    <div class="w-6 h-6 bg-blue-600 rounded mb-1"></div>
                                    <span>ראשי</span>
                                </button>
                                <button type="button" class="style-btn flex flex-col items-center p-2 border border-gray-300 rounded text-xs hover:bg-gray-50 transition-colors" data-style="secondary">
                                    <div class="w-6 h-6 bg-gray-600 rounded mb-1"></div>
                                    <span>משני</span>
                                </button>
                                <button type="button" class="style-btn flex flex-col items-center p-2 border border-gray-300 rounded text-xs hover:bg-gray-50 transition-colors" data-style="outline">
                                    <div class="w-6 h-6 border-2 border-gray-600 rounded mb-1"></div>
                                    <span>מתאר</span>
                                </button>
                                <button type="button" class="style-btn flex flex-col items-center p-2 border border-gray-300 rounded text-xs hover:bg-gray-50 transition-colors" data-style="custom">
                                    <div class="w-6 h-6 bg-gradient-to-r from-purple-500 to-pink-500 rounded mb-1"></div>
                                    <span>מותאם</span>
                                </button>
                            </div>
                            <input type="hidden" name="buttons[${index}][style]" value="primary">
                        </div>
                        
                        <hr class="border-gray-200">
                        
                        <!-- Color Controls -->
                        <div class="color-controls">
                            <div class="normal-colors" data-state="normal">
                                <h6 class="text-xs font-medium text-gray-600 mb-2">צבעים רגילים</h6>
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">צבע רקע</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" name="buttons[${index}][bgColor]" value="#3B82F6" class="w-8 h-8 border border-gray-300 rounded cursor-pointer">
                                            <input type="text" value="#3B82F6" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="#FFFFFF">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">צבע טקסט</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" name="buttons[${index}][textColor]" value="#FFFFFF" class="w-8 h-8 border border-gray-300 rounded cursor-pointer">
                                            <input type="text" value="#FFFFFF" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="#000000">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">צבע מסגרת</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" name="buttons[${index}][borderColor]" value="#3B82F6" class="w-8 h-8 border border-gray-300 rounded cursor-pointer">
                                            <input type="text" value="#3B82F6" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="#000000">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="hover-colors hidden" data-state="hover">
                                <h6 class="text-xs font-medium text-gray-600 mb-2">צבעי הובר</h6>
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">צבע רקע</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" name="buttons[${index}][hoverBgColor]" value="#2563EB" class="w-8 h-8 border border-gray-300 rounded cursor-pointer">
                                            <input type="text" value="#2563EB" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="#FFFFFF">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">צבע טקסט</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" name="buttons[${index}][hoverTextColor]" value="#FFFFFF" class="w-8 h-8 border border-gray-300 rounded cursor-pointer">
                                            <input type="text" value="#FFFFFF" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="#000000">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">צבע מסגרת</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" name="buttons[${index}][hoverBorderColor]" value="#2563EB" class="w-8 h-8 border border-gray-300 rounded cursor-pointer">
                                            <input type="text" value="#2563EB" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="#000000">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="border-gray-200">
                        
                        <!-- Padding & Margin ... -->
                    </div>
                </div>
            `;
        }
        
        // Attach events to buttons
        function attachButtonEvents(index) {
            const buttonItem = document.querySelector(`[data-index="${index}"]`);
            
            // Remove button
            const removeBtn = buttonItem.querySelector('.remove-button-btn');
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    if (document.querySelectorAll('.button-item').length > 1) {
                        buttonItem.remove();
                        updateButtonNumbers();
                        triggerSettingsUpdate();
                    }
                });
            }
            
            // Style selection
            const styleBtns = buttonItem.querySelectorAll('.style-btn');
            const styleInput = buttonItem.querySelector('input[name*="[style]"]');
            
            styleBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    styleBtns.forEach(b => b.classList.remove('bg-blue-100', 'border-blue-500'));
                    this.classList.add('bg-blue-100', 'border-blue-500');
                    
                    if (styleInput) {
                        styleInput.value = this.dataset.style;
                        triggerSettingsUpdate();
                    }
                });
            });
            
            // Hover toggle
            const hoverToggle = buttonItem.querySelector('.hover-toggle');
            const normalColors = buttonItem.querySelector('.normal-colors');
            const hoverColors = buttonItem.querySelector('.hover-colors');
            
            if (hoverToggle) {
                hoverToggle.addEventListener('change', function() {
                    if (this.checked) {
                        normalColors.classList.add('hidden');
                        hoverColors.classList.remove('hidden');
                    } else {
                        normalColors.classList.remove('hidden');
                        hoverColors.classList.add('hidden');
                    }
                });
            }
            
            // Color sync
            const colorInputs = buttonItem.querySelectorAll('input[type="color"]');
            colorInputs.forEach(colorInput => {
                const textInput = colorInput.nextElementSibling;
                
                colorInput.addEventListener('change', function() {
                    textInput.value = this.value;
                    triggerSettingsUpdate();
                });
                
                textInput.addEventListener('input', function() {
                    if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                        colorInput.value = this.value;
                        triggerSettingsUpdate();
                    }
                });
            });
            
            // All input changes
            buttonItem.querySelectorAll('input, select').forEach(input => {
                input.addEventListener('input', triggerSettingsUpdate);
                input.addEventListener('change', triggerSettingsUpdate);
            });
        }
        
        // Attach events to existing buttons
        document.querySelectorAll('.button-item').forEach((item, index) => {
            attachButtonEvents(index);
        });
        
        // Update button numbers
        function updateButtonNumbers() {
            document.querySelectorAll('.button-item').forEach((item, index) => {
                const title = item.querySelector('h6');
                if (title) {
                    title.textContent = `כפתור ${index + 1}`;
                }
                item.setAttribute('data-index', index);
                
                // Update input names
                item.querySelectorAll('input, select').forEach(input => {
                    const name = input.getAttribute('name');
                    if (name && name.includes('buttons[')) {
                        const newName = name.replace(/buttons\[\d+\]/, `buttons[${index}]`);
                        input.setAttribute('name', newName);
                    }
                });
            });
        }
        
        // Trigger settings update for live preview
        function triggerSettingsUpdate() {
            const anyInput = document.querySelector('#sectionSettings input, #sectionSettings textarea, #sectionSettings select');
            if (anyInput) {
                const event = new Event('input', { bubbles: true });
                anyInput.dispatchEvent(event);
            }
        }
        
    })();
    </script>
    
    <?php
}
?> 
?> 