/**
 * מחלקה לניהול עגלת קניות - צד קליינט
 */
class CartManager {
    constructor() {
        this.apiUrl = '/api/cart.php';
        this.cartCount = 0;
        this.cartTotal = 0;
        this.init();
    }
    
    /**
     * אתחול
     */
    init() {
        this.loadCartCount();
        this.bindEvents();
    }
    
    /**
     * קישור אירועים
     */
    bindEvents() {
        // כפתורי הוספה לעגלה
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-add-to-cart]')) {
                e.preventDefault();
                this.handleAddToCart(e.target);
            }
            
            // עדכון כמות בעגלה
            if (e.target.matches('[data-update-quantity]')) {
                this.handleUpdateQuantity(e.target);
            }
            
            // הסרה מהעגלה
            if (e.target.matches('[data-remove-item]')) {
                e.preventDefault();
                this.handleRemoveItem(e.target);
            }
            
            // ניקוי עגלה
            if (e.target.matches('[data-clear-cart]')) {
                e.preventDefault();
                this.handleClearCart();
            }
        });
        
        // עדכון כמות בשדות input
        document.addEventListener('change', (e) => {
            if (e.target.matches('[data-quantity-input]')) {
                this.handleQuantityInput(e.target);
            }
        });
    }
    
    /**
     * הוספת מוצר לעגלה
     */
    async addToCart(productId, variantId = null, quantity = 1) {
        try {
            const response = await fetch(`${this.apiUrl}?action=add`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    variant_id: variantId,
                    quantity: quantity
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.updateCartUI(result.cart);
                this.showMessage(result.message, 'success');
                this.triggerCartUpdate();
            } else {
                this.showMessage(result.message, 'error');
            }
            
            return result;
            
        } catch (error) {
            console.error('Cart Error:', error);
            this.showMessage('שגיאה בהוספה לעגלה', 'error');
            return { success: false, message: 'שגיאת רשת' };
        }
    }
    
    /**
     * עדכון כמות פריט
     */
    async updateQuantity(itemKey, quantity) {
        try {
            const response = await fetch(`${this.apiUrl}?action=update`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    item_key: itemKey,
                    quantity: quantity
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.updateCartUI(result.cart);
                this.triggerCartUpdate();
            } else {
                this.showMessage(result.message, 'error');
            }
            
            return result;
            
        } catch (error) {
            console.error('Cart Error:', error);
            this.showMessage('שגיאה בעדכון העגלה', 'error');
            return { success: false, message: 'שגיאת רשת' };
        }
    }
    
    /**
     * הסרת פריט מהעגלה
     */
    async removeItem(itemKey) {
        try {
            const response = await fetch(`${this.apiUrl}?action=remove&item_key=${encodeURIComponent(itemKey)}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.updateCartUI(result.cart);
                this.showMessage(result.message, 'success');
                this.triggerCartUpdate();
            } else {
                this.showMessage(result.message, 'error');
            }
            
            return result;
            
        } catch (error) {
            console.error('Cart Error:', error);
            this.showMessage('שגיאה בהסרת הפריט', 'error');
            return { success: false, message: 'שגיאת רשת' };
        }
    }
    
    /**
     * ניקוי העגלה
     */
    async clearCart() {
        if (!confirm('האם אתה בטוח שברצונך לנקות את העגלה?')) {
            return;
        }
        
        try {
            const response = await fetch(`${this.apiUrl}?action=clear`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.updateCartUI({ items: [], totals: { subtotal: 0, shipping: 0, tax: 0, total: 0 } });
                this.showMessage(result.message, 'success');
                this.triggerCartUpdate();
            }
            
            return result;
            
        } catch (error) {
            console.error('Cart Error:', error);
            this.showMessage('שגיאה בניקוי העגלה', 'error');
            return { success: false, message: 'שגיאת רשת' };
        }
    }
    
    /**
     * קבלת עגלה מהשרת
     */
    async getCart() {
        try {
            const response = await fetch(`${this.apiUrl}?action=get`);
            const result = await response.json();
            
            if (result.success) {
                this.updateCartUI(result.cart);
                return result.cart;
            }
            
            return null;
            
        } catch (error) {
            console.error('Cart Error:', error);
            return null;
        }
    }
    
    /**
     * קבלת מספר פריטים בעגלה
     */
    async loadCartCount() {
        try {
            const response = await fetch(`${this.apiUrl}?action=count`);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const text = await response.text();
            
            // בדיקה אם התשובה היא JSON תקין
            if (!text.trim().startsWith('{')) {
                console.error('Non-JSON response from cart API:', text);
                return;
            }
            
            const result = JSON.parse(text);
            
            if (result.success) {
                this.cartCount = result.count;
                this.cartTotal = result.total;
                this.updateCartBadge();
            }
            
        } catch (error) {
            console.error('Cart Count Error:', error);
            // אתחול ברירת מחדל במקרה של שגיאה
            this.cartCount = 0;
            this.cartTotal = 0;
            this.updateCartBadge();
        }
    }
    
    /**
     * טיפול בכפתור הוספה לעגלה
     */
    async handleAddToCart(button) {
        const productId = button.dataset.productId;
        const variantId = button.dataset.variantId || null;
        const quantity = parseInt(button.dataset.quantity) || 1;
        
        // בדיקה אם יש וריאציות שצריך לבחור
        if (button.dataset.hasVariants === 'true' && !variantId) {
            this.showMessage('אנא בחר את מאפייני המוצר', 'warning');
            return;
        }
        
        // השבתת הכפתור זמנית
        button.disabled = true;
        const originalText = button.textContent;
        button.textContent = 'מוסיף...';
        
        const result = await this.addToCart(productId, variantId, quantity);
        
        // החזרת הכפתור למצב רגיל
        button.disabled = false;
        button.textContent = originalText;
    }
    
    /**
     * טיפול בעדכון כמות
     */
    async handleUpdateQuantity(button) {
        const itemKey = button.dataset.itemKey;
        const action = button.dataset.action; // 'increase' או 'decrease'
        const currentQuantity = parseInt(button.dataset.currentQuantity) || 1;
        
        let newQuantity = currentQuantity;
        if (action === 'increase') {
            newQuantity++;
        } else if (action === 'decrease' && currentQuantity > 1) {
            newQuantity--;
        }
        
        if (newQuantity !== currentQuantity) {
            await this.updateQuantity(itemKey, newQuantity);
        }
    }
    
    /**
     * טיפול בשדה כמות
     */
    async handleQuantityInput(input) {
        const itemKey = input.dataset.itemKey;
        const quantity = parseInt(input.value) || 1;
        
        if (quantity < 1) {
            input.value = 1;
            return;
        }
        
        await this.updateQuantity(itemKey, quantity);
    }
    
    /**
     * טיפול בהסרת פריט
     */
    async handleRemoveItem(button) {
        const itemKey = button.dataset.itemKey;
        await this.removeItem(itemKey);
    }
    
    /**
     * טיפול בניקוי עגלה
     */
    async handleClearCart() {
        await this.clearCart();
    }
    
    /**
     * עדכון ממשק המשתמש של העגלה
     */
    updateCartUI(cart) {
        this.cartCount = cart.items.reduce((sum, item) => sum + item.quantity, 0);
        this.cartTotal = cart.totals.total;
        
        // עדכון תג הכמות
        this.updateCartBadge();
        
        // עדכון תוכן העגלה אם הדף פתוח
        this.updateCartContent(cart);
        
        // עדכון סכומים
        this.updateCartTotals(cart.totals);
    }
    
    /**
     * עדכון תג כמות בעגלה
     */
    updateCartBadge() {
        const badges = document.querySelectorAll('[data-cart-count]');
        badges.forEach(badge => {
            badge.textContent = this.cartCount;
            badge.style.display = this.cartCount > 0 ? 'inline-block' : 'none';
        });
        
        const totals = document.querySelectorAll('[data-cart-total]');
        totals.forEach(total => {
            total.textContent = `₪${this.cartTotal.toFixed(2)}`;
        });
    }
    
    /**
     * עדכון תוכן העגלה
     */
    updateCartContent(cart) {
        const cartContainer = document.getElementById('cart-items');
        if (!cartContainer) return;
        
        if (cart.items.length === 0) {
            cartContainer.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="ri-shopping-cart-line text-4xl mb-4"></i>
                    <p>העגלה ריקה</p>
                </div>
            `;
            return;
        }
        
        cartContainer.innerHTML = cart.items.map(item => this.renderCartItem(item)).join('');
    }
    
    /**
     * רנדור פריט בעגלה
     */
    renderCartItem(item) {
        const attributesText = Object.entries(item.attributes || {})
            .map(([key, value]) => `${key}: ${value}`)
            .join(', ');
        
        return `
            <div class="flex items-center gap-4 p-4 border-b" data-item-key="${item.item_key}">
                <img src="${item.image || '/assets/images/placeholder.jpg'}" 
                     alt="${item.name}" 
                     class="w-16 h-16 object-cover rounded">
                
                <div class="flex-1">
                    <h4 class="font-medium">${item.name}</h4>
                    <p class="text-sm text-gray-600">${item.sku}</p>
                    ${attributesText ? `<p class="text-sm text-gray-500">${attributesText}</p>` : ''}
                    <p class="font-semibold text-blue-600">₪${item.price}</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <button type="button" 
                            class="w-8 h-8 bg-gray-200 rounded text-sm"
                            data-update-quantity
                            data-action="decrease"
                            data-item-key="${item.item_key}"
                            data-current-quantity="${item.quantity}">
                        <i class="ri-subtract-line"></i>
                    </button>
                    
                    <input type="number" 
                           value="${item.quantity}" 
                           min="1" 
                           class="w-16 text-center border rounded"
                           data-quantity-input
                           data-item-key="${item.item_key}">
                    
                    <button type="button" 
                            class="w-8 h-8 bg-gray-200 rounded text-sm"
                            data-update-quantity
                            data-action="increase"
                            data-item-key="${item.item_key}"
                            data-current-quantity="${item.quantity}">
                        <i class="ri-add-line"></i>
                    </button>
                </div>
                
                <button type="button" 
                        class="text-red-500 hover:text-red-700"
                        data-remove-item
                        data-item-key="${item.item_key}">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        `;
    }
    
    /**
     * עדכון סכומי העגלה
     */
    updateCartTotals(totals) {
        const elements = {
            subtotal: document.querySelectorAll('[data-cart-subtotal]'),
            shipping: document.querySelectorAll('[data-cart-shipping]'),
            tax: document.querySelectorAll('[data-cart-tax]'),
            total: document.querySelectorAll('[data-cart-total]')
        };
        
        Object.entries(elements).forEach(([key, nodeList]) => {
            nodeList.forEach(element => {
                element.textContent = `₪${totals[key].toFixed(2)}`;
            });
        });
    }
    
    /**
     * הצגת הודעה למשתמש
     */
    showMessage(message, type = 'info') {
        // יצירת אלמנט הודעה
        const messageEl = document.createElement('div');
        messageEl.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm`;
        
        const colors = {
            success: 'bg-green-500 text-white',
            error: 'bg-red-500 text-white',
            warning: 'bg-yellow-500 text-white',
            info: 'bg-blue-500 text-white'
        };
        
        messageEl.className += ` ${colors[type] || colors.info}`;
        messageEl.innerHTML = `
            <div class="flex items-center gap-2">
                <span>${message}</span>
                <button class="mr-2 hover:bg-black hover:bg-opacity-20 rounded p-1" onclick="this.parentElement.parentElement.remove()">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(messageEl);
        
        // הסרה אוטומטית אחרי 3 שניות
        setTimeout(() => {
            if (messageEl.parentNode) {
                messageEl.remove();
            }
        }, 3000);
    }
    
    /**
     * הפעלת אירוע עדכון עגלה
     */
    triggerCartUpdate() {
        const event = new CustomEvent('cartUpdated', {
            detail: {
                count: this.cartCount,
                total: this.cartTotal
            }
        });
        document.dispatchEvent(event);
    }
}

// אתחול מנהל העגלה
const cartManager = new CartManager();

// חשיפה גלובלית לשימוש
window.cartManager = cartManager; 