<!-- Typography Section -->
<div class="bg-gray-50 rounded-lg p-4">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <i class="ri-font-size text-pink-600"></i>
            <h4 class="font-medium text-gray-900">טיפוגרפיה</h4>
        </div>
        <br> <br>
        <!-- Responsive Mode Switcher for Typography -->
        <div class="flex items-center gap-1 bg-white rounded-md p-1 border border-gray-200">
            <button type="button" class="typography-mode-btn px-2 py-1 text-xs rounded flex items-center gap-1 transition-colors" data-mode="desktop" title="מחשב">
                <i class="ri-computer-line"></i>
                <span class="hidden sm:inline">מחשב</span>
            </button>
            <button type="button" class="typography-mode-btn px-2 py-1 text-xs rounded flex items-center gap-1 transition-colors" data-mode="tablet" title="טאבלט">
                <i class="ri-tablet-line"></i>
                <span class="hidden sm:inline">טאבלט</span>
            </button>
            <button type="button" class="typography-mode-btn px-2 py-1 text-xs rounded flex items-center gap-1 transition-colors" data-mode="mobile" title="מובייל">
                <i class="ri-smartphone-line"></i>
                <span class="hidden sm:inline">מובייל</span>
            </button>
        </div>
    </div>
    
    <div class="space-y-4">
        <!-- Title Typography -->
        <div class="bg-white rounded-md p-3">
            <h5 class="text-sm font-medium text-gray-900 mb-3">כותרת</h5>
            
            <!-- HTML Tag Selection -->
            <div class="mb-3">
                <label class="block text-xs text-gray-600 mb-1">תג HTML</label>
                <select id="<?php echo $sectionType; ?>TitleTag" name="titleTag" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="h1" <?php echo (isset($defaultData['titleTag']) && $defaultData['titleTag'] === 'h1') ? 'selected' : ''; ?>>H1</option>
                    <option value="h2" <?php echo (!isset($defaultData['titleTag']) || $defaultData['titleTag'] === 'h2') ? 'selected' : ''; ?>>H2 (ברירת מחדל)</option>
                    <option value="h3" <?php echo (isset($defaultData['titleTag']) && $defaultData['titleTag'] === 'h3') ? 'selected' : ''; ?>>H3</option>
                    <option value="h4" <?php echo (isset($defaultData['titleTag']) && $defaultData['titleTag'] === 'h4') ? 'selected' : ''; ?>>H4</option>
                    <option value="h5" <?php echo (isset($defaultData['titleTag']) && $defaultData['titleTag'] === 'h5') ? 'selected' : ''; ?>>H5</option>
                    <option value="h6" <?php echo (isset($defaultData['titleTag']) && $defaultData['titleTag'] === 'h6') ? 'selected' : ''; ?>>H6</option>
                    <option value="p" <?php echo (isset($defaultData['titleTag']) && $defaultData['titleTag'] === 'p') ? 'selected' : ''; ?>>P</option>
                    <option value="span" <?php echo (isset($defaultData['titleTag']) && $defaultData['titleTag'] === 'span') ? 'selected' : ''; ?>>SPAN</option>
                    <option value="a" <?php echo (isset($defaultData['titleTag']) && $defaultData['titleTag'] === 'a') ? 'selected' : ''; ?>>A</option>
                </select>
            </div>
            
            <!-- Font Size + Style Buttons -->
            <div class="flex items-center gap-3 mb-3">
                <div class="flex-1">
                    <label class="block text-xs text-gray-600 mb-1">גודל פונט (px)</label>
                    <input type="number" id="<?php echo $sectionType; ?>TitleFontSize" name="titleFontSize" 
                           value="<?php echo $defaultData['titleFontSize'] ?? '36'; ?>" 
                           class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                
                <!-- Style Buttons -->
                <div class="flex gap-1">
                    <button type="button" 
                            id="<?php echo $sectionType; ?>TitleBold" 
                            data-style="bold"
                            class="style-toggle-btn w-8 h-8 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-50 transition-colors <?php echo (isset($defaultData['titleFontWeight']) && $defaultData['titleFontWeight'] === 'bold') ? 'active bg-blue-500 text-white' : 'bg-white text-gray-600'; ?>"
                            title="הדגשה">
                        <i class="ri-bold text-sm"></i>
                    </button>
                    
                    <button type="button" 
                            id="<?php echo $sectionType; ?>TitleItalic" 
                            data-style="italic"
                            class="style-toggle-btn w-8 h-8 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-50 transition-colors <?php echo (isset($defaultData['titleFontStyle']) && $defaultData['titleFontStyle'] === 'italic') ? 'active bg-blue-500 text-white' : 'bg-white text-gray-600'; ?>"
                            title="קו נטוי">
                        <i class="ri-italic text-sm"></i>
                    </button>
                    
                    <button type="button" 
                            id="<?php echo $sectionType; ?>TitleUnderline" 
                            data-style="underline"
                            class="style-toggle-btn w-8 h-8 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-50 transition-colors <?php echo (isset($defaultData['titleTextDecoration']) && $defaultData['titleTextDecoration'] === 'underline') ? 'active bg-blue-500 text-white' : 'bg-white text-gray-600'; ?>"
                            title="קו תחתון">
                        <i class="ri-underline text-sm"></i>
                    </button>
                </div>
            </div>
            
            <!-- Font Family -->
            <div class="mb-3">
                <label class="block text-xs text-gray-600 mb-1">סוג פונט</label>
                <select id="<?php echo $sectionType; ?>TitleFontFamily" name="titleFontFamily" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="'Noto Sans Hebrew', sans-serif" <?php echo (!isset($defaultData['titleFontFamily']) || $defaultData['titleFontFamily'] === "'Noto Sans Hebrew', sans-serif") ? 'selected' : ''; ?>>נוטו סאנס עברית</option>
                    <option value="'Heebo', sans-serif" <?php echo (isset($defaultData['titleFontFamily']) && $defaultData['titleFontFamily'] === "'Heebo', sans-serif") ? 'selected' : ''; ?>>היבו</option>
                    <option value="'Open Sans Hebrew', 'Noto Sans Hebrew', sans-serif" <?php echo (isset($defaultData['titleFontFamily']) && $defaultData['titleFontFamily'] === "'Open Sans Hebrew', 'Noto Sans Hebrew', sans-serif") ? 'selected' : ''; ?>>אופן סאנס עברית</option>
                    <option value="'Assistant', sans-serif" <?php echo (isset($defaultData['titleFontFamily']) && $defaultData['titleFontFamily'] === "'Assistant', sans-serif") ? 'selected' : ''; ?>>אססיטנט</option>
                    <option value="'Varela Round', sans-serif" <?php echo (isset($defaultData['titleFontFamily']) && $defaultData['titleFontFamily'] === "'Varela Round', sans-serif") ? 'selected' : ''; ?>>וורלה ראונד</option>
                    <option value="'Poppins', sans-serif" <?php echo (isset($defaultData['titleFontFamily']) && $defaultData['titleFontFamily'] === "'Poppins', sans-serif") ? 'selected' : ''; ?>>Poppins</option>
                    <option value="'Montserrat', sans-serif" <?php echo (isset($defaultData['titleFontFamily']) && $defaultData['titleFontFamily'] === "'Montserrat', sans-serif") ? 'selected' : ''; ?>>Montserrat</option>
                </select>
            </div>

            <!-- Line Height and Text Transform -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">גובה שורה</label>
                    <input type="number" step="0.1" min="0.5" max="3" id="<?php echo $sectionType; ?>TitleLineHeight" name="titleLineHeight" 
                           value="<?php echo $defaultData['titleLineHeight'] ?? '1.2'; ?>" 
                           class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">אותיות גדולות</label>
                    <select id="<?php echo $sectionType; ?>TitleTextTransform" name="titleTextTransform" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="none" <?php echo (!isset($defaultData['titleTextTransform']) || $defaultData['titleTextTransform'] === 'none') ? 'selected' : ''; ?>>רגיל</option>
                        <option value="uppercase" <?php echo (isset($defaultData['titleTextTransform']) && $defaultData['titleTextTransform'] === 'uppercase') ? 'selected' : ''; ?>>אותיות גדולות</option>
                        <option value="lowercase" <?php echo (isset($defaultData['titleTextTransform']) && $defaultData['titleTextTransform'] === 'lowercase') ? 'selected' : ''; ?>>אותיות קטנות</option>
                        <option value="capitalize" <?php echo (isset($defaultData['titleTextTransform']) && $defaultData['titleTextTransform'] === 'capitalize') ? 'selected' : ''; ?>>אות גדולה בתחילת מילה</option>
                    </select>
                </div>
            </div>
            
            <!-- Hidden inputs for style values -->
            <input type="hidden" id="<?php echo $sectionType; ?>TitleFontWeight" name="titleFontWeight" value="<?php echo $defaultData['titleFontWeight'] ?? 'normal'; ?>">
            <input type="hidden" id="<?php echo $sectionType; ?>TitleFontStyle" name="titleFontStyle" value="<?php echo $defaultData['titleFontStyle'] ?? 'normal'; ?>">
            <input type="hidden" id="<?php echo $sectionType; ?>TitleTextDecoration" name="titleTextDecoration" value="<?php echo $defaultData['titleTextDecoration'] ?? 'none'; ?>">
        </div>
        
        <!-- Content Typography -->
        <div class="bg-white rounded-md p-3">
            <h5 class="text-sm font-medium text-gray-900 mb-3">תוכן</h5>
            
            <!-- HTML Tag Selection -->
            <div class="mb-3">
                <label class="block text-xs text-gray-600 mb-1">תג HTML</label>
                <select id="<?php echo $sectionType; ?>SubtitleTag" name="subtitleTag" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="h1" <?php echo (isset($defaultData['subtitleTag']) && $defaultData['subtitleTag'] === 'h1') ? 'selected' : ''; ?>>H1</option>
                    <option value="h2" <?php echo (isset($defaultData['subtitleTag']) && $defaultData['subtitleTag'] === 'h2') ? 'selected' : ''; ?>>H2</option>
                    <option value="h3" <?php echo (isset($defaultData['subtitleTag']) && $defaultData['subtitleTag'] === 'h3') ? 'selected' : ''; ?>>H3</option>
                    <option value="h4" <?php echo (isset($defaultData['subtitleTag']) && $defaultData['subtitleTag'] === 'h4') ? 'selected' : ''; ?>>H4</option>
                    <option value="h5" <?php echo (isset($defaultData['subtitleTag']) && $defaultData['subtitleTag'] === 'h5') ? 'selected' : ''; ?>>H5</option>
                    <option value="h6" <?php echo (isset($defaultData['subtitleTag']) && $defaultData['subtitleTag'] === 'h6') ? 'selected' : ''; ?>>H6</option>
                    <option value="p" <?php echo (!isset($defaultData['subtitleTag']) || $defaultData['subtitleTag'] === 'p') ? 'selected' : ''; ?>>P (ברירת מחדל)</option>
                    <option value="span" <?php echo (isset($defaultData['subtitleTag']) && $defaultData['subtitleTag'] === 'span') ? 'selected' : ''; ?>>SPAN</option>
                    <option value="a" <?php echo (isset($defaultData['subtitleTag']) && $defaultData['subtitleTag'] === 'a') ? 'selected' : ''; ?>>A</option>
                </select>
            </div>
            
            <!-- Font Size + Style Buttons -->
            <div class="flex items-center gap-3 mb-3">
                <div class="flex-1">
                    <label class="block text-xs text-gray-600 mb-1">גודל פונט (px)</label>
                    <input type="number" id="<?php echo $sectionType; ?>SubtitleFontSize" name="subtitleFontSize" 
                           value="<?php echo $defaultData['subtitleFontSize'] ?? '18'; ?>" 
                           class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                
                <!-- Style Buttons -->
                <div class="flex gap-1">
                    <button type="button" 
                            id="<?php echo $sectionType; ?>SubtitleBold" 
                            data-style="bold"
                            class="style-toggle-btn w-8 h-8 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-50 transition-colors <?php echo (isset($defaultData['subtitleFontWeight']) && $defaultData['subtitleFontWeight'] === 'bold') ? 'active bg-blue-500 text-white' : 'bg-white text-gray-600'; ?>"
                            title="הדגשה">
                        <i class="ri-bold text-sm"></i>
                    </button>
                    
                    <button type="button" 
                            id="<?php echo $sectionType; ?>SubtitleItalic" 
                            data-style="italic"
                            class="style-toggle-btn w-8 h-8 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-50 transition-colors <?php echo (isset($defaultData['subtitleFontStyle']) && $defaultData['subtitleFontStyle'] === 'italic') ? 'active bg-blue-500 text-white' : 'bg-white text-gray-600'; ?>"
                            title="קו נטוי">
                        <i class="ri-italic text-sm"></i>
                    </button>
                    
                    <button type="button" 
                            id="<?php echo $sectionType; ?>SubtitleUnderline" 
                            data-style="underline"
                            class="style-toggle-btn w-8 h-8 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-50 transition-colors <?php echo (isset($defaultData['subtitleTextDecoration']) && $defaultData['subtitleTextDecoration'] === 'underline') ? 'active bg-blue-500 text-white' : 'bg-white text-gray-600'; ?>"
                            title="קו תחתון">
                        <i class="ri-underline text-sm"></i>
                    </button>
                </div>
            </div>
            
            <!-- Font Family -->
            <div class="mb-3">
                <label class="block text-xs text-gray-600 mb-1">סוג פונט</label>
                <select id="<?php echo $sectionType; ?>SubtitleFontFamily" name="subtitleFontFamily" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="'Noto Sans Hebrew', sans-serif" <?php echo (!isset($defaultData['subtitleFontFamily']) || $defaultData['subtitleFontFamily'] === "'Noto Sans Hebrew', sans-serif") ? 'selected' : ''; ?>>נוטו סאנס עברית</option>
                    <option value="'Heebo', sans-serif" <?php echo (isset($defaultData['subtitleFontFamily']) && $defaultData['subtitleFontFamily'] === "'Heebo', sans-serif") ? 'selected' : ''; ?>>היבו</option>
                    <option value="'Open Sans Hebrew', 'Noto Sans Hebrew', sans-serif" <?php echo (isset($defaultData['subtitleFontFamily']) && $defaultData['subtitleFontFamily'] === "'Open Sans Hebrew', 'Noto Sans Hebrew', sans-serif") ? 'selected' : ''; ?>>אופן סאנס עברית</option>
                    <option value="'Assistant', sans-serif" <?php echo (isset($defaultData['subtitleFontFamily']) && $defaultData['subtitleFontFamily'] === "'Assistant', sans-serif") ? 'selected' : ''; ?>>אססיטנט</option>
                    <option value="'Varela Round', sans-serif" <?php echo (isset($defaultData['subtitleFontFamily']) && $defaultData['subtitleFontFamily'] === "'Varela Round', sans-serif") ? 'selected' : ''; ?>>וורלה ראונד</option>
                    <option value="'Poppins', sans-serif" <?php echo (isset($defaultData['subtitleFontFamily']) && $defaultData['subtitleFontFamily'] === "'Poppins', sans-serif") ? 'selected' : ''; ?>>Poppins</option>
                    <option value="'Montserrat', sans-serif" <?php echo (isset($defaultData['subtitleFontFamily']) && $defaultData['subtitleFontFamily'] === "'Montserrat', sans-serif") ? 'selected' : ''; ?>>Montserrat</option>
                </select>
            </div>

            <!-- Line Height and Text Transform -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">גובה שורה</label>
                    <input type="number" step="0.1" min="0.5" max="3" id="<?php echo $sectionType; ?>SubtitleLineHeight" name="subtitleLineHeight" 
                           value="<?php echo $defaultData['subtitleLineHeight'] ?? '1.5'; ?>" 
                           class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">אותיות גדולות</label>
                    <select id="<?php echo $sectionType; ?>SubtitleTextTransform" name="subtitleTextTransform" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="none" <?php echo (!isset($defaultData['subtitleTextTransform']) || $defaultData['subtitleTextTransform'] === 'none') ? 'selected' : ''; ?>>רגיל</option>
                        <option value="uppercase" <?php echo (isset($defaultData['subtitleTextTransform']) && $defaultData['subtitleTextTransform'] === 'uppercase') ? 'selected' : ''; ?>>אותיות גדולות</option>
                        <option value="lowercase" <?php echo (isset($defaultData['subtitleTextTransform']) && $defaultData['subtitleTextTransform'] === 'lowercase') ? 'selected' : ''; ?>>אותיות קטנות</option>
                        <option value="capitalize" <?php echo (isset($defaultData['subtitleTextTransform']) && $defaultData['subtitleTextTransform'] === 'capitalize') ? 'selected' : ''; ?>>אות גדולה בתחילת מילה</option>
                    </select>
                </div>
            </div>
            
            <!-- Hidden inputs for style values -->
            <input type="hidden" id="<?php echo $sectionType; ?>SubtitleFontWeight" name="subtitleFontWeight" value="<?php echo $defaultData['subtitleFontWeight'] ?? 'normal'; ?>">
            <input type="hidden" id="<?php echo $sectionType; ?>SubtitleFontStyle" name="subtitleFontStyle" value="<?php echo $defaultData['subtitleFontStyle'] ?? 'normal'; ?>">
            <input type="hidden" id="<?php echo $sectionType; ?>SubtitleTextDecoration" name="subtitleTextDecoration" value="<?php echo $defaultData['subtitleTextDecoration'] ?? 'none'; ?>">
        </div>
        
        <!-- Button Typography -->
        <div class="bg-white rounded-md p-3">
            <h5 class="text-sm font-medium text-gray-900 mb-3">כפתור</h5>
            <div class="space-y-3">
                <!-- Font Size + Style Buttons -->
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-600 mb-1">גודל פונט (px)</label>
                        <input type="number" id="<?php echo $sectionType; ?>ButtonFontSize" name="buttonFontSize" 
                               value="<?php echo $defaultData['buttonFontSize'] ?? '16'; ?>" 
                               class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    
                    <!-- Style Buttons -->
                    <div class="flex gap-1">
                        <button type="button" 
                                id="<?php echo $sectionType; ?>ButtonBold" 
                                data-style="bold"
                                class="style-toggle-btn w-8 h-8 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-50 transition-colors <?php echo (isset($defaultData['buttonFontWeight']) && $defaultData['buttonFontWeight'] === 'bold') ? 'active bg-blue-500 text-white' : 'bg-white text-gray-600'; ?>"
                                title="הדגשה">
                            <i class="ri-bold text-sm"></i>
                        </button>
                        
                        <button type="button" 
                                id="<?php echo $sectionType; ?>ButtonItalic" 
                                data-style="italic"
                                class="style-toggle-btn w-8 h-8 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-50 transition-colors <?php echo (isset($defaultData['buttonFontStyle']) && $defaultData['buttonFontStyle'] === 'italic') ? 'active bg-blue-500 text-white' : 'bg-white text-gray-600'; ?>"
                                title="קו נטוי">
                            <i class="ri-italic text-sm"></i>
                        </button>
                        
                        <button type="button" 
                                id="<?php echo $sectionType; ?>ButtonUnderline" 
                                data-style="underline"
                                class="style-toggle-btn w-8 h-8 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-50 transition-colors <?php echo (isset($defaultData['buttonTextDecoration']) && $defaultData['buttonTextDecoration'] === 'underline') ? 'active bg-blue-500 text-white' : 'bg-white text-gray-600'; ?>"
                                title="קו תחתון">
                            <i class="ri-underline text-sm"></i>
                        </button>
                        
                        <button type="button" 
                                id="<?php echo $sectionType; ?>ButtonShadow" 
                                data-style="shadow"
                                class="style-toggle-btn w-8 h-8 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-50 transition-colors <?php echo (isset($defaultData['buttonShadow']) && $defaultData['buttonShadow'] === 'true') ? 'active bg-blue-500 text-white' : 'bg-white text-gray-600'; ?>"
                                title="הצללה">
                            <i class="ri-shadow-line text-sm"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Font Family -->
                <div>
                    <label class="block text-xs text-gray-600 mb-1">סוג פונט</label>
                    <select id="<?php echo $sectionType; ?>ButtonFontFamily" name="buttonFontFamily" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="'Noto Sans Hebrew', sans-serif" <?php echo (!isset($defaultData['buttonFontFamily']) || $defaultData['buttonFontFamily'] === "'Noto Sans Hebrew', sans-serif") ? 'selected' : ''; ?>>נוטו סאנס עברית</option>
                        <option value="'Heebo', sans-serif" <?php echo (isset($defaultData['buttonFontFamily']) && $defaultData['buttonFontFamily'] === "'Heebo', sans-serif") ? 'selected' : ''; ?>>היבו</option>
                        <option value="'Open Sans Hebrew', 'Noto Sans Hebrew', sans-serif" <?php echo (isset($defaultData['buttonFontFamily']) && $defaultData['buttonFontFamily'] === "'Open Sans Hebrew', 'Noto Sans Hebrew', sans-serif") ? 'selected' : ''; ?>>אופן סאנס עברית</option>
                        <option value="'Assistant', sans-serif" <?php echo (isset($defaultData['buttonFontFamily']) && $defaultData['buttonFontFamily'] === "'Assistant', sans-serif") ? 'selected' : ''; ?>>אססיטנט</option>
                        <option value="'Varela Round', sans-serif" <?php echo (isset($defaultData['buttonFontFamily']) && $defaultData['buttonFontFamily'] === "'Varela Round', sans-serif") ? 'selected' : ''; ?>>וורלה ראונד</option>
                        <option value="'Poppins', sans-serif" <?php echo (isset($defaultData['buttonFontFamily']) && $defaultData['buttonFontFamily'] === "'Poppins', sans-serif") ? 'selected' : ''; ?>>Poppins</option>
                        <option value="'Montserrat', sans-serif" <?php echo (isset($defaultData['buttonFontFamily']) && $defaultData['buttonFontFamily'] === "'Montserrat', sans-serif") ? 'selected' : ''; ?>>Montserrat</option>
                    </select>
                </div>
                
                <!-- Hidden inputs for style values -->
                <input type="hidden" id="<?php echo $sectionType; ?>ButtonFontWeight" name="buttonFontWeight" value="<?php echo $defaultData['buttonFontWeight'] ?? 'normal'; ?>">
                <input type="hidden" id="<?php echo $sectionType; ?>ButtonFontStyle" name="buttonFontStyle" value="<?php echo $defaultData['buttonFontStyle'] ?? 'normal'; ?>">
                <input type="hidden" id="<?php echo $sectionType; ?>ButtonTextDecoration" name="buttonTextDecoration" value="<?php echo $defaultData['buttonTextDecoration'] ?? 'none'; ?>">
                <input type="hidden" id="<?php echo $sectionType; ?>ButtonShadow" name="buttonShadow" value="<?php echo $defaultData['buttonShadow'] ?? 'false'; ?>">
            </div>
        </div>
    </div>
</div>

<style>
/* Typography Style Toggle Buttons */
.style-toggle-btn {
    border: 1px solid #d1d5db;
    transition: all 0.2s ease;
}

.style-toggle-btn:hover {
    background-color: #f9fafb;
    border-color: #9ca3af;
}

.style-toggle-btn.active {
    background-color: #3b82f6 !important;
    color: white !important;
    border-color: #3b82f6 !important;
}

.style-toggle-btn.active:hover {
    background-color: #2563eb !important;
}

/* Typography Mode Switcher */
.typography-mode-btn.active {
    background-color: #3b82f6;
    color: white;
}

.typography-mode-btn:not(.active) {
    color: #6b7280;
}

.typography-mode-btn:not(.active):hover {
    background-color: #f3f4f6;
}
</style>

<script>
// Typography Style Buttons Handler - Handled by Hero.js now
// The Hero section will manage these buttons to avoid conflicts with dynamic loading
console.log('Typography component loaded - buttons will be handled by Hero.js');

// Function to update placeholders based on desktop values
function updateTypographyPlaceholders() {
    const currentMode = window.currentResponsiveMode || 'desktop';
    
    if (currentMode === 'desktop') {
        // Clear placeholders for desktop mode
        document.querySelectorAll('#heroTitleFontSize, #heroTitleFontFamily, #heroSubtitleFontSize, #heroSubtitleFontFamily, #heroButtonFontSize, #heroButtonFontFamily').forEach(input => {
            input.placeholder = '';
        });
        return;
    }
    
    // Get desktop values for placeholders
    const heroData = window.currentSection && window.currentSection.data ? window.currentSection.data : {};
    
    // Title placeholders
    const titleFontSize = document.getElementById('heroTitleFontSize');
    const titleFontFamily = document.getElementById('heroTitleFontFamily');
    if (titleFontSize) titleFontSize.placeholder = heroData.titleFontSize || '36';
    if (titleFontFamily) titleFontFamily.placeholder = heroData.titleFontFamily || 'Noto Sans Hebrew';
    
    // Subtitle placeholders
    const subtitleFontSize = document.getElementById('heroSubtitleFontSize');
    const subtitleFontFamily = document.getElementById('heroSubtitleFontFamily');
    if (subtitleFontSize) subtitleFontSize.placeholder = heroData.subtitleFontSize || '18';
    if (subtitleFontFamily) subtitleFontFamily.placeholder = heroData.subtitleFontFamily || 'Noto Sans Hebrew';
    
    // Button placeholders
    const buttonFontSize = document.getElementById('heroButtonFontSize');
    const buttonFontFamily = document.getElementById('heroButtonFontFamily');
    
    if (buttonFontSize) buttonFontSize.placeholder = heroData.buttonFontSize || '16';
    if (buttonFontFamily) buttonFontFamily.placeholder = heroData.buttonFontFamily || 'Noto Sans Hebrew';
}

// Export function for Hero.js to use
window.updateTypographyPlaceholders = updateTypographyPlaceholders;
</script>