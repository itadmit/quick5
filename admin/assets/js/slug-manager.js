class SlugManager {
    constructor(nameInputId, slugInputId, checkUrl) {
        this.nameInput = document.getElementById(nameInputId);
        this.slugInput = document.getElementById(slugInputId);
        this.checkUrl = checkUrl;
        this.isManualEdit = false;
        this.checkTimeout = null;
        
        this.init();
    }
    
    init() {
        // יצירה אוטומטית של slug כשמקלידים שם מוצר
        this.nameInput.addEventListener('input', (e) => {
            if (!this.isManualEdit) {
                const slug = this.generateSlug(e.target.value);
                this.slugInput.value = slug;
                this.updateSlugPreview(slug);
                this.checkSlugAvailability(slug);
            }
        });
        
        // כשמעדכנים ידנית את הslug
        this.slugInput.addEventListener('input', (e) => {
            this.isManualEdit = true;
            const slug = this.generateSlug(e.target.value);
            this.slugInput.value = slug;
            this.updateSlugPreview(slug);
            this.checkSlugAvailability(slug);
        });
        
        // כשיוצאים מהשדה slug, מפסיקים עריכה ידנית
        this.slugInput.addEventListener('blur', () => {
            setTimeout(() => {
                this.isManualEdit = false;
            }, 1000);
        });
    }
    
    generateSlug(text) {
        if (!text) return '';
        
        return text.trim()
            .toLowerCase()
            .replace(/[^\u05D0-\u05EA\u0590-\u05FF\w\s-]/g, '') // השאר עברית, אנגלית ומספרים
            .replace(/\s+/g, '-') // החלף רווחים במקפים
            .replace(/-+/g, '-') // החלף מקפים כפולים במקף יחיד
            .replace(/^-+|-+$/g, ''); // הסר מקפים מהתחלה ומהסוף
    }
    
    updateSlugPreview(slug) {
        const slugText = document.getElementById('slug-text');
        if (slugText) {
            slugText.textContent = slug;
        }
    }
    
    checkSlugAvailability(slug) {
        if (!slug) {
            this.hideAllIndicators();
            return;
        }
        
        // בטל בדיקה קודמת
        if (this.checkTimeout) {
            clearTimeout(this.checkTimeout);
        }
        
        // הצג אינדיקטור טעינה
        this.showIndicator('checking');
        
        // בדיקה עם עיכוב קצר כדי לא לעשות יותר מדי בקשות
        this.checkTimeout = setTimeout(() => {
            this.performSlugCheck(slug);
        }, 500);
    }
    
    performSlugCheck(slug) {
        fetch(this.checkUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ slug: slug })
        })
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                this.showIndicator('available');
            } else {
                this.showIndicator('taken');
            }
        })
        .catch(error => {
            console.error('Error checking slug:', error);
            this.hideAllIndicators();
        });
    }
    
    showIndicator(type) {
        this.hideAllIndicators();
        
        const indicator = document.getElementById('slug-indicator');
        const specificIndicator = document.getElementById(`slug-${type}`);
        
        if (indicator && specificIndicator) {
            indicator.classList.remove('hidden');
            specificIndicator.classList.remove('hidden');
        }
    }
    
    hideAllIndicators() {
        const indicator = document.getElementById('slug-indicator');
        const available = document.getElementById('slug-available');
        const taken = document.getElementById('slug-taken');
        const checking = document.getElementById('slug-checking');
        
        if (indicator) indicator.classList.add('hidden');
        if (available) available.classList.add('hidden');
        if (taken) taken.classList.add('hidden');
        if (checking) checking.classList.add('hidden');
    }
}

// אתחול כשהעמוד נטען
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('name') && document.getElementById('slug')) {
        new SlugManager('name', 'slug', '/admin/api/check_slug.php');
    }
}); 