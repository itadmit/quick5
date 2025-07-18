# Page Builder - ארכיטקטורה מודולרית מתקדמת

## 🏗️ מבנה המערכת החדש

```
builder/
├── index.php                       # דף ראשי - קונטיינר בלבד
├── sections-list/
│   └── sections.php                # רשימת כל הסקשנים
├── settings/
│   ├── hero-settings.php          # HTML הגדרות Hero
│   └── [section]-settings.php     # HTML הגדרות סקשנים נוספים
├── assets/
│   ├── css/builder.css
│   └── js/
│       ├── builder.js              # מנהל כללי + iframe
│       ├── section-manager.js      # טעינה דינמית של סקשנים
│       └── sections/
│           ├── hero.js             # לוגיקה מלאה של Hero
│           └── [section].js        # לוגיקה של סקשנים נוספים
└── api/
    ├── save.php
    └── load.php
```

## 🎯 ארכיטקטורה מתקדמת

### 1. **Builder.js** - המנהל הכללי
```javascript
// תפקידים:
- ניהול iframe
- תקשורת כללית
- אנימציות slider
- כפתור שמירה
- הודעות סטטוס
```

### 2. **SectionManager.js** - מנהל הסקשנים
```javascript
// תפקידים:
- טעינה דינמית של קבצי JS
- ניהול מחזור חיים של סקשנים
- מעבר בין הגדרות
- ניהול זיכרון
```

### 3. **Hero.js** - סקשן מודולרי
```javascript
// תפקידים:
- הגדרת Event Listeners
- ניהול נתונים ספציפיים
- תקשורת עם iframe
- שמירה/טעינה
```

## ➕ איך להוסיף סקשן חדש

### שלב 1: יצירת HTML הגדרות
צור קובץ: `settings/[section-name]-settings.php`

### שלב 2: יצירת קובץ JS
צור קובץ: `assets/js/sections/[section-name].js`

```javascript
class [SectionName]Section {
    constructor(builder) {
        this.builder = builder;
        this.data = this.getDefaultData();
        this.isActive = false;
    }
    
    getDefaultData() {
        return {
            // נתוני ברירת מחדל
        };
    }
    
    async onOpen() {
        // פתיחת הגדרות
        this.isActive = true;
        this.builder.slideToSettings();
        await this.loadData();
        setTimeout(() => {
            this.setupEventListeners();
            this.populateForm();
        }, 150);
    }
    
    async onClose() {
        // סגירת הגדרות
        this.isActive = false;
        this.removeEventListeners();
        this.builder.slideToSections();
    }
    
    setupEventListeners() {
        // הגדרת Event Listeners
    }
    
    removeEventListeners() {
        // הסרת Event Listeners
    }
    
    async loadData() {
        // טעינת נתונים
    }
    
    async saveData() {
        // שמירת נתונים
    }
    
    getData() {
        return this.data;
    }
}
```

### שלב 3: הוספה לרשימת הסקשנים
ערוך את `sections-list/sections.php`

### שלב 4: עדכון API (אופציונלי)
הוסף תמיכה בסקשן החדש ב-`api/save.php` ו-`api/load.php`

## 🔥 יתרונות הארכיטקטורה החדשה

### ✅ **מודולריות מלאה**
- כל סקשן עצמאי לחלוטין
- אין תלויות בין סקשנים
- קל להוסיף/להסיר סקשנים

### ⚡ **ביצועים מעולים**
- טעינה דינמית - רק מה שצריך
- ניהול זיכרון אופטימלי
- אין עומס מיותר

### 👥 **עבודת צוות**
- מפתחים שונים = סקשנים שונים
- אין קונפליקטים בקוד
- בדיקות עצמאיות

### 🧹 **תחזוקה קלה**
- שינוי בסקשן אחד לא משפיע על אחרים
- דיבוג פשוט יותר
- קוד נקי ומסודר

## 🎛️ API של מחלקת הסקשן

### פונקציות חובה:
```javascript
constructor(builder)    // יצירת אינסטנס
async onOpen()         // פתיחת הגדרות
async onClose()        // סגירת הגדרות
getData()              // קבלת נתונים
```

### פונקציות אופציונליות:
```javascript
async loadData()       // טעינת נתונים
async saveData()       // שמירת נתונים
getDefaultData()       // נתוני ברירת מחדל
setupEventListeners()  // הגדרת Events
removeEventListeners() // הסרת Events
populateForm()         // מילוי טופס
```

## 🚀 דוגמאות להרחבה

### סקשן מוצרים מומלצים:
```javascript
class FeaturedProductsSection {
    getDefaultData() {
        return {
            title: 'מוצרים מומלצים',
            count: 8,
            layout: 'grid',
            showPrices: true
        };
    }
    // ...
}
```

### סקשן Newsletter:
```javascript
class NewsletterSection {
    getDefaultData() {
        return {
            title: 'הצטרפו לרשימת התפוצה',
            subtitle: 'קבלו עדכונים על מבצעים',
            buttonText: 'הצטרפו'
        };
    }
    // ...
}
```

## 🔧 הגדרות מתקדמות

### Debug Mode:
```javascript
const DEBUG_MODE = true; // ב-builder.js
```

### טעינה אסינכרונית:
המערכת טוענת סקשנים רק כשצריך, וזוכרת אותם לשימוש חוזר.

### ניהול שגיאות:
כל שגיאה בטעינת סקשן מוצגת למשתמש עם הודעה ברורה.

---

**המערכת מוכנה להרחבה אינסופית! 🎊** 