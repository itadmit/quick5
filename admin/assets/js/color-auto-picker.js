// Color Auto Picker - זיהוי אוטומטי של צבעים מוכרים
// QuickShop5 - יצירת מוצר עם וריאציות צבע

// מילון צבעים מוכרים בעברית
const KNOWN_COLORS = {
    // צבעים בסיסיים
    'אדום': '#DC2626',
    'כחול': '#2563EB', 
    'ירוק': '#16A34A',
    'צהוב': '#EAB308',
    'כתום': '#EA580C',
    'סגול': '#9333EA',
    'ורוד': '#EC4899',
    'חום': '#A16207',
    'שחור': '#000000',
    'לבן': '#FFFFFF',
    
    // גוונים נוספים
    'כחול בהיר': '#3B82F6',
    'כחול כהה': '#1E3A8A',
    'ירוק בהיר': '#22C55E',
    'ירוק כהה': '#15803D',
    'אדום בהיר': '#EF4444',
    'אדום כהה': '#B91C1C',
    'אפור': '#6B7280',
    'אפור בהיר': '#D1D5DB',
    'אפור כהה': '#374151',
    'זהב': '#F59E0B'
};

// רשימת צבעים נוספים באנגלית (אופציונלי)
const ENGLISH_COLORS = {
    'red': '#DC2626',
    'blue': '#2563EB',
    'green': '#16A34A',
    'yellow': '#EAB308',
    'orange': '#EA580C',
    'purple': '#9333EA',
    'pink': '#EC4899',
    'brown': '#A16207',
    'black': '#000000',
    'white': '#FFFFFF'
};

// פונקציה לניקוי טקסט (הסרת רווחים וכו')
function cleanColorText(text) {
    return text.trim().toLowerCase().replace(/\s+/g, ' ');
}

// פונקציה לחיפוש צבע לפי שם
function findColorByName(colorName) {
    const cleaned = cleanColorText(colorName);
    
    // חיפוש בעברית
    for (const [name, color] of Object.entries(KNOWN_COLORS)) {
        if (cleanColorText(name) === cleaned) {
            return color;
        }
    }
    
    // חיפוש באנגלית
    for (const [name, color] of Object.entries(ENGLISH_COLORS)) {
        if (cleanColorText(name) === cleaned) {
            return color;
        }
    }
    
    // חיפוש חלקי (מכיל את השם)
    for (const [name, color] of Object.entries(KNOWN_COLORS)) {
        if (cleanColorText(name).includes(cleaned) || cleaned.includes(cleanColorText(name))) {
            return color;
        }
    }
    
    return null;
}

// פונקציה לעדכון קולור פיקר אוטומטי
function autoUpdateColorPicker(valueInput, colorInput) {
    const colorName = valueInput.value;
    if (!colorName || !colorInput) return;
    
    const foundColor = findColorByName(colorName);
    if (foundColor) {
        colorInput.value = foundColor;
        
        // עדכון החזותי של הקולור פיקר
        if (colorInput.style) {
            colorInput.style.backgroundColor = foundColor;
        }
        
        // הצגת הודעה קטנה (אופציונלי)
        showColorUpdateNotification(valueInput, colorName, foundColor);
    }
}

// הצגת הודעה קטנה על עדכון צבע
function showColorUpdateNotification(input, colorName, colorHex) {
    // הסרת הודעות קודמות
    const existingNotification = input.parentNode.querySelector('.color-auto-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // יצירת הודעה חדשה
    const notification = document.createElement('div');
    notification.className = 'color-auto-notification absolute top-full left-0 mt-1 px-2 py-1 bg-green-100 text-green-800 text-xs rounded border border-green-200 z-10';
    notification.dir = 'rtl';
    notification.innerHTML = `
        <i class="ri-palette-line ml-1"></i>
        זוהה צבע: ${colorName} ← ${colorHex}
    `;
    
    // הוספה למיקום יחסי
    input.parentNode.style.position = 'relative';
    input.parentNode.appendChild(notification);
    
    // הסרה אחרי 3 שניות
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// פונקציה להטמעת האוטו-פיקר במאפיינים קיימים
function initAutoColorPicker() {
    // מאזין לשינויים במאפיינים
    document.addEventListener('input', function(e) {
        // בדיקה אם זה שדה ערך במאפיין צבע
        if (e.target.matches('input[name*="[values]"][name*="[value]"]')) {
            const valueInput = e.target;
            const attributeContainer = valueInput.closest('.attribute-item');
            
            if (attributeContainer) {
                const typeSelect = attributeContainer.querySelector('select[name*="[type]"]');
                
                // בדיקה אם המאפיין הוא מסוג צבע
                if (typeSelect && typeSelect.value === 'color') {
                    const colorInput = valueInput.parentNode.querySelector('input[type="color"]');
                    
                    if (colorInput) {
                        // עיכוב קטן כדי לתת למשתמש לסיים להקליד
                        clearTimeout(valueInput.colorTimeout);
                        valueInput.colorTimeout = setTimeout(() => {
                            autoUpdateColorPicker(valueInput, colorInput);
                        }, 500);
                    }
                }
            }
        }
    });
    
    // מאזין לשינוי סוג מאפיין לצבע
    document.addEventListener('change', function(e) {
        if (e.target.matches('select[name*="[type]"]') && e.target.value === 'color') {
            const attributeContainer = e.target.closest('.attribute-item');
            const valueInputs = attributeContainer.querySelectorAll('input[name*="[values]"][name*="[value]"]');
            
            // עדכון כל הערכים הקיימים
            valueInputs.forEach(valueInput => {
                if (valueInput.value) {
                    const colorInput = valueInput.parentNode.querySelector('input[type="color"]');
                    if (colorInput) {
                        autoUpdateColorPicker(valueInput, colorInput);
                    }
                }
            });
        }
    });
}

// הפעלת הסקריפט כשהעמוד נטען
document.addEventListener('DOMContentLoaded', initAutoColorPicker);

// פונקציה לטריגר זיהוי צבע ידני (לשימוש פנימי)
function triggerColorAutoDetection(valueInput) {
    const attributeContainer = valueInput.closest('.attribute-item');
    if (attributeContainer) {
        const typeSelect = attributeContainer.querySelector('select[name*="[type]"]');
        if (typeSelect && typeSelect.value === 'color') {
            const colorInput = valueInput.parentNode.querySelector('input[type="color"]');
            if (colorInput) {
                autoUpdateColorPicker(valueInput, colorInput);
            }
        }
    }
}

// ייצוא הפונקציות לשימוש חיצוני
window.ColorAutoPicker = {
    findColorByName,
    autoUpdateColorPicker,
    initAutoColorPicker,
    triggerColorAutoDetection,
    KNOWN_COLORS,
    ENGLISH_COLORS
};

// גישה גלובלית לפונקציית הטריגר
window.triggerColorAutoDetection = triggerColorAutoDetection; 