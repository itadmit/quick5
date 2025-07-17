# Page Builder - מבנה מודולרי

## 🏗️ מבנה הקבצים

```
builder/
├── index.php                 # דף ראשי - קונטיינר בלבד
├── sections-list/
│   └── sections.php          # רשימת כל הסקשנים
├── settings/
│   ├── hero-settings.php          # הגדרות Hero
│   ├── featured-products-settings.php  # הגדרות מוצרים מומלצים
│   └── [section]-settings.php     # הגדרות סקשנים נוספים
├── assets/
│   ├── css/builder.css
│   └── js/builder.js
└── api/
    ├── save.php
    └── load.php
```

## ➕ איך להוסיף סקשן חדש

### שלב 1: יצירת קובץ הגדרות
יצר קובץ חדש: `settings/[section-name]-settings.php`

```php
<div class="p-6">
    <div class="flex items-center gap-3 mb-4">
        <button id="backButton" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition-colors">
            <i class="ri-arrow-right-line text-gray-600"></i>
        </button>
        <h3 id="settingsTitle" class="text-lg font-medium text-gray-900">הגדרות [שם הסקשן]</h3>
    </div>
    
    <form id="[sectionName]Form" class="space-y-4">
        <!-- הגדרות הסקשן כאן -->
    </form>
</div>
```

### שלב 2: הוספה לרשימת הסקשנים
ערוך את `sections-list/sections.php` והוסף:

```html
<div class="section-item border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors" data-section="section-name">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-[color]-100 rounded-lg flex items-center justify-center">
                <i class="ri-[icon]-line text-[color]-600"></i>
            </div>
            <div>
                <h3 class="font-medium text-gray-900">[שם הסקשן]</h3>
                <p class="text-sm text-gray-500">[תיאור הסקשן]</p>
            </div>
        </div>
        <button class="settings-btn w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition-colors" 
                data-section="section-name" title="הגדרות">
            <i class="ri-settings-3-line text-gray-600"></i>
        </button>
    </div>
</div>
```

### שלב 3: עדכון JavaScript
ב-`builder.js`, הוסף לוגיקה לסקשן החדש:

1. ב-`openSectionSettings()` הוסף case חדש
2. ב-`handleInputChange()` הוסף טיפול בסקשן החדש
3. צור פונקציות חדשות עבור הסקשן

### שלב 4: יצירת API endpoints
יצר קבצי API חדשים או עדכן את הקיימים לטפל בנתוני הסקשן החדש.

## 🎯 יתרונות המבנה המודולרי

- **קוד נקי**: כל סקשן מנוהל בנפרד
- **קל להוסיף**: סקשן חדש = קובץ חדש
- **תחזוקה קלה**: שינוי בסקשן לא משפיע על האחרים
- **עבודת צוות**: מפתחים שונים יכולים לעבוד על סקשנים שונים
- **ביצועים**: טעינה דינמית של רק מה שנדרש

## 📝 דוגמאות קיימות

1. **Hero Section** - `settings/hero-settings.php`
   - צבעים, טקסטים, כפתורים
   
2. **Featured Products** - `settings/featured-products-settings.php`
   - מספר מוצרים, סגנון תצוגה, הגדרות מראה

## 🔧 פיתוח עתידי

המבנה מוכן להרחבות כמו:
- Categories Grid
- Newsletter Signup  
- Testimonials
- FAQ Section
- Contact Form
- ועוד...

כל סקשן חדש פשוט מתווסף כקובץ נפרד בתקיית `settings/`! 