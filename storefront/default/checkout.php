<?php
// קבלת מידע החנות מהגלובלים
$store = $GLOBALS['CURRENT_STORE'] ?? null;

if (!$store) {
    header('HTTP/1.1 404 Not Found');
    exit('Store not found');
}

// הגדרת משתנים לדף
$currentPage = 'checkout';
$pageTitle = 'תשלום';
$pageDescription = 'השלם את הרכישה - תהליך תשלום מאובטח';

// Include header
include __DIR__ . '/header.php';
?>

    <!-- Checkout Content -->
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
                <li>
                    <a href="/cart" class="text-gray-500 hover:text-gray-700">עגלת קניות</a>
                </li>
                <li>
                    <i class="ri-arrow-left-s-line text-gray-400"></i>
                </li>
                <li class="text-gray-900 font-medium">תשלום</li>
            </ol>
        </nav>

        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-center space-x-8 space-x-reverse">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                        <i class="ri-check-line"></i>
                    </div>
                    <span class="mr-2 text-sm font-medium text-green-600">עגלת קניות</span>
                </div>
                
                <div class="flex-1 h-px bg-green-500"></div>
                
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center text-sm font-medium">
                        2
                    </div>
                    <span class="mr-2 text-sm font-medium text-primary">פרטי משלוח</span>
                </div>
                
                <div class="flex-1 h-px bg-gray-300"></div>
                
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">
                        3
                    </div>
                    <span class="mr-2 text-sm font-medium text-gray-600">תשלום</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Checkout Form -->
            <div class="lg:col-span-2">
                <form id="checkout-form" class="space-y-8">
                    <!-- Contact Information -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6">פרטי הזמנה</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">שם פרטי *</label>
                                <input type="text" id="first_name" name="first_name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">שם משפחה *</label>
                                <input type="text" id="last_name" name="last_name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">כתובת אימייל *</label>
                                <input type="email" id="email" name="email" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">מספר טלפון *</label>
                                <input type="tel" id="phone" name="phone" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="050-1234567">
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6">כתובת משלוח</h2>
                        
                        <div class="space-y-6">
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">רחוב ומספר בית *</label>
                                <input type="text" id="address" name="address" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="רחוב הרצל 123">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">עיר *</label>
                                    <input type="text" id="city" name="city" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="תל אביב">
                                </div>
                                
                                <div>
                                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">מיקוד</label>
                                    <input type="text" id="postal_code" name="postal_code"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="1234567">
                                </div>
                            </div>
                            
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">הערות למשלוח</label>
                                <textarea id="notes" name="notes" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="הערות נוספות למשלוח (קומה, דירה, הוראות מיוחדות)"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6">אמצעי תשלום</h2>
                        
                        <div class="space-y-4">
                            <div class="flex items-center p-4 border border-gray-200 rounded-lg">
                                <input type="radio" id="credit_card" name="payment_method" value="credit_card" checked
                                       class="h-4 w-4 text-primary focus:ring-primary border-gray-300">
                                <label for="credit_card" class="mr-3 flex items-center flex-1 cursor-pointer">
                                    <div class="flex items-center">
                                        <i class="ri-bank-card-line text-xl text-gray-600 ml-3"></i>
                                        <span class="text-sm font-medium text-gray-900">כרטיס אשראי</span>
                                    </div>
                                    <div class="flex space-x-2 space-x-reverse">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Visa.svg" alt="Visa" class="h-6">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" class="h-6">
                                    </div>
                                </label>
                            </div>
                            
                            <div class="flex items-center p-4 border border-gray-200 rounded-lg">
                                <input type="radio" id="paypal" name="payment_method" value="paypal"
                                       class="h-4 w-4 text-primary focus:ring-primary border-gray-300">
                                <label for="paypal" class="mr-3 flex items-center cursor-pointer">
                                    <i class="ri-paypal-line text-xl text-blue-600 ml-3"></i>
                                    <span class="text-sm font-medium text-gray-900">PayPal</span>
                                </label>
                            </div>
                            
                            <div class="flex items-center p-4 border border-gray-200 rounded-lg">
                                <input type="radio" id="bank_transfer" name="payment_method" value="bank_transfer"
                                       class="h-4 w-4 text-primary focus:ring-primary border-gray-300">
                                <label for="bank_transfer" class="mr-3 flex items-center cursor-pointer">
                                    <i class="ri-bank-line text-xl text-gray-600 ml-3"></i>
                                    <span class="text-sm font-medium text-gray-900">העברה בנקאית</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Credit Card Fields -->
                        <div id="credit-card-fields" class="mt-6 space-y-4">
                            <div>
                                <label for="card_number" class="block text-sm font-medium text-gray-700 mb-2">מספר כרטיס אשראי</label>
                                <input type="text" id="card_number" name="card_number"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="1234 5678 9012 3456"
                                       maxlength="19">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-2">תוקף</label>
                                    <input type="text" id="expiry_date" name="expiry_date"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="MM/YY"
                                           maxlength="5">
                                </div>
                                
                                <div>
                                    <label for="cvv" class="block text-sm font-medium text-gray-700 mb-2">CVV</label>
                                    <input type="text" id="cvv" name="cvv"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="123"
                                           maxlength="4">
                                </div>
                            </div>
                            
                            <div>
                                <label for="card_holder" class="block text-sm font-medium text-gray-700 mb-2">שם בעל הכרטיס</label>
                                <input type="text" id="card_holder" name="card_holder"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="שם כפי שמופיע על הכרטיס">
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-start">
                            <input type="checkbox" id="terms" name="terms" required
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded mt-1">
                            <label for="terms" class="mr-3 text-sm text-gray-700">
                                אני מסכים ל<a href="/terms" class="text-primary hover:underline" target="_blank">תנאי השימוש</a>
                                ול<a href="/privacy" class="text-primary hover:underline" target="_blank">מדיניות הפרטיות</a>
                            </label>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 sticky top-24">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">סיכום הזמנה</h2>
                    </div>
                    
                    <div id="checkout-summary" class="p-6">
                        <!-- Order summary will be loaded here -->
                        <div class="text-center py-4">
                            <i class="ri-loader-4-line text-2xl text-gray-400 animate-spin"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
$additionalJS = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    loadCheckoutSummary();
    initializePaymentMethods();
    initializeCardFormatting();
});

function loadCheckoutSummary() {
    if (typeof cartManager !== "undefined") {
        cartManager.getItems()
            .then(response => {
                if (response.success && response.items && response.items.length > 0) {
                    displayCheckoutSummary(response);
                } else {
                    // Redirect to cart if empty
                    window.location.href = "/cart";
                }
            })
            .catch(error => {
                console.error("Error loading cart:", error);
                window.location.href = "/cart";
            });
    }
}

function displayCheckoutSummary(cartData) {
    const container = document.getElementById("checkout-summary");
    const subtotal = parseFloat(cartData.total);
    const shipping = subtotal >= 200 ? 0 : 25;
    const total = subtotal + shipping;
    
    let itemsHtml = "";
    cartData.items.forEach(item => {
        itemsHtml += `
            <div class="flex items-center justify-between py-2">
                <div class="flex items-center space-x-3 space-x-reverse">
                    <div class="w-12 h-12 bg-gray-100 rounded flex-shrink-0">
                        ${item.image ? 
                            `<img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover rounded">` :
                            `<div class="w-full h-full flex items-center justify-center">
                                <i class="ri-image-line text-gray-400"></i>
                             </div>`
                        }
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">${item.name}</p>
                        <p class="text-sm text-gray-500">כמות: ${item.quantity}</p>
                    </div>
                </div>
                <span class="text-sm font-medium">₪${(parseFloat(item.price) * item.quantity).toFixed(2)}</span>
            </div>
        `;
    });
    
    container.innerHTML = `
        <div class="space-y-4">
            <div class="space-y-2">
                ${itemsHtml}
            </div>
            
            <div class="border-t pt-4 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">סה"כ מוצרים:</span>
                    <span>₪${subtotal.toFixed(2)}</span>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">משלוח:</span>
                    <span>${shipping === 0 ? "חינם" : "₪" + shipping.toFixed(2)}</span>
                </div>
                
                <div class="flex items-center justify-between text-lg font-semibold border-t pt-2">
                    <span>סה"כ לתשלום:</span>
                    <span class="text-primary">₪${total.toFixed(2)}</span>
                </div>
            </div>
            
            <button onclick="submitOrder()" 
                    class="w-full btn-primary text-white py-3 px-6 rounded-lg font-semibold hover:opacity-90 transition-opacity">
                השלם הזמנה
            </button>
            
            <div class="text-center">
                <div class="flex items-center justify-center space-x-2 space-x-reverse text-sm text-gray-500">
                    <i class="ri-shield-check-line"></i>
                    <span>תשלום מאובטח 256-bit SSL</span>
                </div>
            </div>
        </div>
    `;
}

function initializePaymentMethods() {
    const paymentMethods = document.querySelectorAll("input[name=\"payment_method\"]");
    const creditCardFields = document.getElementById("credit-card-fields");
    
    paymentMethods.forEach(method => {
        method.addEventListener("change", function() {
            if (this.value === "credit_card") {
                creditCardFields.style.display = "block";
            } else {
                creditCardFields.style.display = "none";
            }
        });
    });
}

function initializeCardFormatting() {
    const cardNumber = document.getElementById("card_number");
    const expiryDate = document.getElementById("expiry_date");
    
    cardNumber.addEventListener("input", function(e) {
        let value = e.target.value.replace(/\s+/g, "").replace(/[^0-9]/gi, "");
        let formattedValue = value.match(/.{1,4}/g)?.join(" ") || value;
        e.target.value = formattedValue;
    });
    
    expiryDate.addEventListener("input", function(e) {
        let value = e.target.value.replace(/\D/g, "");
        if (value.length >= 2) {
            value = value.substring(0, 2) + "/" + value.substring(2, 4);
        }
        e.target.value = value;
    });
}

function submitOrder() {
    const form = document.getElementById("checkout-form");
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const paymentMethod = formData.get("payment_method");
    
    // Show loading state
    const submitButton = event.target;
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = "<i class=\"ri-loader-4-line animate-spin ml-2\"></i>מעבד הזמנה...";
    submitButton.disabled = true;
    
    // Simulate order processing
    setTimeout(() => {
        alert("ההזמנה נשלחה בהצלחה! תקבל אישור במייל בקרוב.");
        
        // Clear cart and redirect
        if (typeof cartManager !== "undefined") {
            cartManager.clearCart();
        }
        
        window.location.href = "/";
    }, 2000);
}
</script>
';

// Include footer
include __DIR__ . '/footer.php';
?> 