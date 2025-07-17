# מערכת קסטומיזר QuickShop - גרסה היברידית

מערכת קסטומיזר מתקדמת לעיצוב והתאמה אישית של חנויות QuickShop עם טכנולוגיה היברידית יעילה.

## מבנה התיקיות

```
admin/customizer/
├── index.php                    # הקובץ הראשי של הקסטומיזר
├── assets/
│   ├── css/
│   │   └── customizer.css      # עיצוב הקסטומיזר
│   └── js/
│       ├── customizer-main.js         # הקסטומיזר הראשי
│       ├── sections-manager-core.js   # מנהל הסקשנים
│       ├── sections-settings.js       # הגדרות הסקשנים
│       ├── sections-database.js       # מנהל הדטאבייס
│       └── hero-settings.js           # הגדרות מתקדמות ל-Hero
├── tabs/
│   ├── design.php              # טאב עיצוב
│   └── content.php             # טאב תוכן
└── README.md                   # מסמך זה
```

## המערכת ההיברידית החדשה

### עקרונות יסוד:
- **ברירות מחדל בקוד**: כל סקשן יש לו הגדרות ברירת מחדל מוגדרות בקוד
- **שמירת שינויים בלבד**: רק הגדרות שהמשתמש שינה נשמרות בדטאבייס
- **מיזוג חכם**: המערכת מחברת ברירות מחדל עם שינויים מותאמים אישית

### יתרונות:
- **ביצועים מעולים**: פחות שאילתות, נתונים קטנים יותר
- **חיסכון במקום**: דטאבייס קטן ויעיל
- **גמישות**: קל לעדכן ברירות מחדל ולהוסיף תכונות חדשות
- **יציבות**: תמיד יש תוכן בסיסי להציג

## מבנה הדטאבייס

### טבלת `store_pages`
```sql
- id (מזהה)
- store_id (מזהה החנות)
- page_type (סוג העמוד - 'home', 'category', וכו')
- page_structure (מבנה העמוד - JSON)
- global_settings (הגדרות גלובליות - JSON)
```

### טבלת `custom_sections`
```sql
- id (מזהה)
- store_id (מזהה החנות)
- page_type (סוג העמוד)
- section_id (מזהה הסקשן)
- section_type (סוג הסקשן)
- custom_settings (הגדרות מותאמות אישית - JSON)
- section_order (סדר הסקשן)
```

## תכונות

### ניהול סקשנים
- **הוספת סקשנים**: הוספה דינמית של סקשנים חדשים
- **עריכת הגדרות**: עריכה מתקדמת של כל סקשן
- **סידור מחדש**: גרירה ושחרור לסידור הסקשנים
- **תצוגה מקדימה**: תצוגה בזמן אמת של השינויים

### סקשנים זמינים
- **Header**: כותרת האתר, תפריט ניווט
- **Hero**: סקשן פתיחה עם כותרת, תיאור וכפתור
- **Featured Products**: מוצרים מומלצים
- **Footer**: כותרת תחתונה עם קישורים

### הגדרות מתקדמות
- **צבעים**: צבע ראשי, משני ורקע
- **טיפוגרפיה**: בחירת גופן וגדלים
- **רווחים**: הגדרות מרווחים וגבולות
- **הגדרות גלובליות**: הגדרות המשפיעות על כל העמוד

## API

### נקודות קצה:
- `../api/get-sections.php` - קבלת הסקשנים (משתמש במערכת ההיברידית)
- `../api/save-section.php` - שמירת הגדרות סקשן (משתמש במערכת ההיברידית)

### דוגמת שימוש:
```javascript
// קבלת הסקשנים
const response = await fetch('../api/get-sections.php');
const data = await response.json();

// שמירת הגדרות
await fetch('../api/save-section.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        action: 'update_section',
        section: {
            id: 'hero-1',
            type: 'hero',
            settings: { title: 'כותרת חדשה' }
        }
    })
});
```

## התקנה ותחזוקה

### דרישות מערכת:
- PHP 7.4+
- MySQL 5.7+
- מחלקת `HybridPageManager` (נמצאת ב-`includes/HybridPageManager.php`)

### הגדרה ראשונית:
1. וודא שהטבלאות `store_pages` ו-`custom_sections` קיימות
2. הרץ את סקריפט ההעברה אם יש נתונים ישנים
3. בדוק שהקבצים `admin/api/get-sections.php` ו-`admin/api/save-section.php` עודכנו

## פתרון בעיות

### שגיאות נפוצות:
- **"Undefined array key 'visible'"**: וודא שהמערכת ההיברידית מחזירה את השדה `visible`
- **"HybridPageManager not found"**: וודא שהקובץ `includes/HybridPageManager.php` קיים
- **שגיאות API**: בדוק שקבצי ה-API עודכנו למערכת ההיברידית

### מצב דיבוג:
```javascript
// הפעלת מצב דיבוג
console.log('Loading sections...');
const sections = await hybridManager.getHomePage();
console.log('Loaded sections:', sections);
```

---

**מפותח על ידי צוות QuickShop**  
**גרסה היברידית - אופטימיזציה מלאה לביצועים ויעילות** 