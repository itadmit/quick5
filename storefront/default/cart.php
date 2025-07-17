<?php
// קבלת מידע החנות מהגלובלים
$store = $GLOBALS['CURRENT_STORE'] ?? null;

if (!$store) {
    header('HTTP/1.1 404 Not Found');
    exit('Store not found');
}

// הגדרת משתנים לדף
$currentPage = 'cart';
$pageTitle = 'עגלת קניות';
$pageDescription = 'עגלת הקניות שלך - סקור את המוצרים והמשך לתשלום';

// Include header
include __DIR__ . '/header.php';
?>

    <!-- Cart Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-4 space-x-reverse">
                <li>
                    <a href="/" class="text-gray-500 hover:text-gray-700">בית</a>
                </li>
                <li>
                    <i class="ri-arrow-left-s-line text-gray-400"></i>
                </li>
                <li class="text-gray-900 font-medium">עגלת קניות</li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h1 class="text-2xl font-bold text-gray-900">עגלת הקניות שלך</h1>
                    </div>
                    
                    <div id="cart-items-container">
                        <!-- Cart items will be loaded here by JavaScript -->
                        <div class="p-6">
                            <div class="text-center py-8">
                                <i class="ri-loader-4-line text-4xl text-gray-400 animate-spin mb-4"></i>
                                <p class="text-gray-600">טוען עגלת קניות...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 sticky top-24">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">סיכום הזמנה</h2>
                    </div>
                    
                    <div class="p-6" id="order-summary">
                        <!-- Order summary will be loaded here by JavaScript -->
                        <div class="text-center py-4">
                            <i class="ri-loader-4-line text-2xl text-gray-400 animate-spin"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Continue Shopping -->
        <div class="mt-8 text-center">
            <a href="/category/all" class="inline-flex items-center text-primary hover:text-secondary transition-colors">
                <i class="ri-arrow-right-line ml-2"></i>
                המשך קניות
            </a>
        </div>
    </div>

<?php
$additionalJS = "
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.cartManager !== 'undefined') {
        loadCartItems();
    } else {
        console.error('Cart manager not found');
        showEmptyCart();
    }
});

function loadCartItems() {
    window.cartManager.getCart()
        .then(cart => {
            if (cart && cart.items && cart.items.length > 0) {
                displayCartItems(cart.items, cart);
                displayOrderSummary(cart);
            } else {
                showEmptyCart();
            }
        })
        .catch(error => {
            console.error('Error loading cart items:', error);
            showEmptyCart();
        });
}

function displayCartItems(items, cartData) {
    const container = document.getElementById('cart-items-container');
    
    let html = '<div class=\"divide-y divide-gray-200\">';
    
    items.forEach(item => {
        const imageHtml = item.image ? 
            '<img src=\"' + item.image + '\" alt=\"' + item.name + '\" class=\"w-full h-full object-cover rounded-lg\">' :
            '<div class=\"w-full h-full bg-gray-200 rounded-lg flex items-center justify-center\"><i class=\"ri-image-line text-gray-400\"></i></div>';
            
        html += '<div class=\"p-6 flex items-center space-x-4 space-x-reverse\" data-item-id=\"' + item.product_id + '\">' +
            '<div class=\"flex-shrink-0 w-20 h-20\">' + imageHtml + '</div>' +
            '<div class=\"flex-1 min-w-0\">' +
                '<h3 class=\"text-lg font-medium text-gray-900 truncate\">' + item.name + '</h3>' +
                '<p class=\"text-gray-600\">₪' + parseFloat(item.price).toFixed(2) + '</p>' +
            '</div>' +
            '<div class=\"flex items-center space-x-3 space-x-reverse\">' +
                '<button onclick=\"updateQuantity(' + item.product_id + ', ' + (item.quantity - 1) + ')\" class=\"w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50\">' +
                    '<i class=\"ri-subtract-line\"></i>' +
                '</button>' +
                '<span class=\"w-8 text-center font-medium\">' + item.quantity + '</span>' +
                '<button onclick=\"updateQuantity(' + item.product_id + ', ' + (item.quantity + 1) + ')\" class=\"w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50\">' +
                    '<i class=\"ri-add-line\"></i>' +
                '</button>' +
            '</div>' +
            '<div class=\"text-lg font-semibold text-gray-900\">₪' + (parseFloat(item.price) * item.quantity).toFixed(2) + '</div>' +
            '<button onclick=\"removeItem(' + item.product_id + ')\" class=\"text-red-500 hover:text-red-700 p-2\">' +
                '<i class=\"ri-delete-bin-line\"></i>' +
            '</button>' +
        '</div>';
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function displayOrderSummary(cart) {
    const container = document.getElementById('order-summary');
    const subtotal = cart.totals ? cart.totals.subtotal : cart.total;
    const shipping = subtotal >= 200 ? 0 : 25;
    const total = subtotal + shipping;
    
    let shippingText = shipping === 0 ? 'חינם' : '₪' + shipping.toFixed(2);
    let freeShippingNotice = shipping === 0 ? '' : 
        '<div class=\"text-sm text-green-600 bg-green-50 p-3 rounded-lg\">' +
            '<i class=\"ri-truck-line ml-1\"></i>' +
            'הוסף עוד ₪' + (200 - subtotal).toFixed(2) + ' למשלוח חינם!' +
        '</div>';
    
    container.innerHTML = 
        '<div class=\"space-y-4\">' +
            '<div class=\"flex items-center justify-between\">' +
                '<span class=\"text-gray-600\">סה\"כ מוצרים:</span>' +
                '<span class=\"font-medium\">₪' + subtotal.toFixed(2) + '</span>' +
            '</div>' +
            '<div class=\"flex items-center justify-between\">' +
                '<span class=\"text-gray-600\">משלוח:</span>' +
                '<span class=\"font-medium\">' + shippingText + '</span>' +
            '</div>' +
            freeShippingNotice +
            '<div class=\"border-t pt-4\">' +
                '<div class=\"flex items-center justify-between text-lg font-semibold\">' +
                    '<span>סה\"כ לתשלום:</span>' +
                    '<span class=\"text-primary\">₪' + total.toFixed(2) + '</span>' +
                '</div>' +
            '</div>' +
            '<button onclick=\"proceedToCheckout()\" class=\"w-full btn-primary text-white py-3 px-6 rounded-lg font-semibold hover:opacity-90 transition-opacity\">' +
                'מעבר לתשלום' +
            '</button>' +
            '<div class=\"text-center\">' +
                '<div class=\"flex items-center justify-center space-x-2 space-x-reverse text-sm text-gray-500\">' +
                    '<i class=\"ri-shield-check-line\"></i>' +
                    '<span>תשלום מאובטח ובטוח</span>' +
                '</div>' +
            '</div>' +
        '</div>';
}

function showEmptyCart() {
    const container = document.getElementById('cart-items-container');
    container.innerHTML = 
        '<div class=\"text-center py-16\">' +
            '<i class=\"ri-shopping-cart-line text-6xl text-gray-400 mb-4\"></i>' +
            '<h3 class=\"text-xl font-semibold text-gray-900 mb-2\">העגלה שלך ריקה</h3>' +
            '<p class=\"text-gray-600 mb-6\">הוסף מוצרים לעגלה כדי להתחיל קניות</p>' +
            '<a href=\"/category/all\" class=\"btn-primary text-white px-6 py-3 rounded-lg hover:opacity-90 transition-opacity\">' +
                'התחל קניות' +
            '</a>' +
        '</div>';
    
    document.getElementById('order-summary').innerHTML = 
        '<div class=\"text-center py-8 text-gray-500\">' +
            '<p>אין פריטים בעגלה</p>' +
        '</div>';
}

function updateQuantity(productId, newQuantity) {
    if (newQuantity <= 0) {
        removeItem(productId);
        return;
    }
    
    window.cartManager.updateQuantity(productId, newQuantity)
        .then(response => {
            if (response.success) {
                loadCartItems();
            } else {
                alert('שגיאה בעדכון הכמות');
            }
        })
        .catch(error => {
            console.error('Error updating quantity:', error);
            alert('שגיאה בעדכון הכמות');
        });
}

function removeItem(productId) {
    if (confirm('האם אתה בטוח שברצונך להסיר את הפריט מהעגלה?')) {
        window.cartManager.removeItem(productId)
            .then(response => {
                if (response.success) {
                    loadCartItems();
                } else {
                    alert('שגיאה בהסרת הפריט');
                }
            })
            .catch(error => {
                console.error('Error removing item:', error);
                alert('שגיאה בהסרת הפריט');
            });
    }
}

function proceedToCheckout() {
    window.location.href = '/checkout';
}
</script>
";

// Include footer
include __DIR__ . '/footer.php';
?> 