/**
 * SoleMate - Cart Functionality
 * Handles shopping cart operations, quantity updates, and checkout
 */

// ============================================
// CART VARIABLES
// ============================================
let cartItems = [];
let cartTotal = 0;
let cartCount = 0;

// ============================================
// CART INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    loadCart();
    updateCartDisplay();
    bindCartEvents();
});

// Load cart from localStorage or session
function loadCart() {
    const savedCart = localStorage.getItem('solemate_cart');
    if (savedCart) {
        cartItems = JSON.parse(savedCart);
        recalculateCart();
    } else {
        // Try to fetch from server session
        fetchCartFromServer();
    }
}

// Fetch cart from server
function fetchCartFromServer() {
    fetch('/api/cart-api.php?action=get')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.cart) {
                cartItems = data.cart;
                recalculateCart();
                saveCartToLocal();
                updateCartDisplay();
            }
        })
        .catch(error => console.error('Error fetching cart:', error));
}

// Save cart to localStorage
function saveCartToLocal() {
    localStorage.setItem('solemate_cart', JSON.stringify(cartItems));
}

// Recalculate cart totals
function recalculateCart() {
    cartTotal = 0;
    cartCount = 0;
    
    cartItems.forEach(item => {
        const itemPrice = parseFloat(item.sale_price) || parseFloat(item.price);
        const itemTotal = itemPrice * item.quantity;
        cartTotal += itemTotal;
        cartCount += item.quantity;
    });
    
    cartTotal = Math.round(cartTotal * 100) / 100;
    
    // Update cart badge
    updateCartBadge(cartCount);
}

// Update cart badge in header
function updateCartBadge(count) {
    const badges = document.querySelectorAll('#cartCount');
    badges.forEach(badge => {
        if (badge) {
            badge.textContent = count;
            if (count === 0) {
                badge.style.display = 'none';
            } else {
                badge.style.display = 'inline-block';
            }
        }
    });
}

// ============================================
// ADD TO CART FUNCTION
// ============================================
function addToCart(productId, size, quantity = 1) {
    // Validate size selection
    if (!size || size === 'Select size') {
        showNotification('Please select a size', 'error');
        return false;
    }
    
    // Validate quantity
    if (quantity < 1 || quantity > 10) {
        showNotification('Quantity must be between 1 and 10', 'error');
        return false;
    }
    
    // Check if product already exists in cart
    const existingItem = cartItems.find(item => 
        item.product_id == productId && item.size === size
    );
    
    if (existingItem) {
        // Update quantity
        const newQuantity = existingItem.quantity + quantity;
        if (newQuantity > 10) {
            showNotification('Maximum quantity is 10', 'error');
            return false;
        }
        updateCartItemQuantity(existingItem.cart_item_id, newQuantity);
    } else {
        // Add new item to cart via API
        fetch('/api/cart-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productId,
                size: size,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh cart from server
                fetchCartFromServer();
                showNotification('Product added to cart!', 'success');
                
                // Animate cart icon
                animateCartIcon();
            } else {
                showNotification(data.message || 'Error adding to cart', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        });
    }
}

// Animate cart icon when item added
function animateCartIcon() {
    const cartIcon = document.querySelector('.cart-icon');
    if (cartIcon) {
        cartIcon.classList.add('cart-animation');
        setTimeout(() => {
            cartIcon.classList.remove('cart-animation');
        }, 500);
    }
}

// ============================================
// CART PAGE FUNCTIONS
// ============================================
// Update cart item quantity
function updateCartItemQuantity(cartItemId, newQuantity) {
    if (newQuantity < 1) {
        removeCartItem(cartItemId);
        return;
    }
    
    if (newQuantity > 10) {
        showNotification('Maximum quantity is 10', 'error');
        return;
    }
    
    fetch('/api/cart-api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'update',
            cart_item_id: cartItemId,
            quantity: newQuantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchCartFromServer();
            updateCartDisplay();
            showNotification('Cart updated', 'success');
        } else {
            showNotification(data.message || 'Update failed', 'error');
        }
    })
    .catch(error => console.error('Error:', error));
}

// Remove item from cart
function removeCartItem(cartItemId) {
    if (confirm('Remove this item from your cart?')) {
        fetch('/api/cart-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                cart_item_id: cartItemId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchCartFromServer();
                updateCartDisplay();
                showNotification('Item removed', 'success');
            } else {
                showNotification(data.message || 'Remove failed', 'error');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

// Clear entire cart
function clearCart() {
    if (confirm('Are you sure you want to clear your entire cart?')) {
        fetch('/api/cart-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'clear'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cartItems = [];
                recalculateCart();
                updateCartDisplay();
                showNotification('Cart cleared', 'success');
            } else {
                showNotification(data.message || 'Clear failed', 'error');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

// Update cart display on cart page
function updateCartDisplay() {
    const cartContainer = document.getElementById('cartItemsContainer');
    const subtotalElement = document.getElementById('cartSubtotal');
    const totalElement = document.getElementById('cartTotal');
    const shippingElement = document.getElementById('shippingCost');
    const taxElement = document.getElementById('taxAmount');
    const emptyCartMessage = document.getElementById('emptyCartMessage');
    const cartSummary = document.getElementById('cartSummary');
    
    if (!cartContainer) return;
    
    if (cartItems.length === 0) {
        if (emptyCartMessage) emptyCartMessage.style.display = 'block';
        if (cartContainer) cartContainer.innerHTML = '';
        if (cartSummary) cartSummary.style.display = 'none';
        return;
    }
    
    if (emptyCartMessage) emptyCartMessage.style.display = 'none';
    if (cartSummary) cartSummary.style.display = 'block';
    
    // Build cart items HTML
    let cartHtml = '';
    cartItems.forEach(item => {
        const itemPrice = parseFloat(item.sale_price) || parseFloat(item.price);
        const itemSubtotal = itemPrice * item.quantity;
        
        cartHtml += `
            <div class="cart-item" data-item-id="${item.cart_item_id}">
                <div class="cart-item-image">
                    <img src="${item.image_main || 'https://via.placeholder.com/80x80'}" alt="${escapeHtml(item.name)}">
                </div>
                <div class="cart-item-details">
                    <h4>${escapeHtml(item.name)}</h4>
                    <div class="cart-item-meta">
                        <span class="cart-item-brand">${escapeHtml(item.brand || 'SoleMate')}</span>
                        <span class="cart-item-size">Size: ${item.size}</span>
                    </div>
                    <div class="cart-item-price-mobile">$${itemPrice.toFixed(2)}</div>
                    <div class="cart-item-actions">
                        <div class="cart-item-quantity">
                            <button class="qty-btn qty-minus" onclick="updateCartItemQuantity(${item.cart_item_id}, ${item.quantity - 1})">-</button>
                            <input type="number" value="${item.quantity}" min="1" max="10" 
                                   onchange="updateCartItemQuantity(${item.cart_item_id}, this.value)">
                            <button class="qty-btn qty-plus" onclick="updateCartItemQuantity(${item.cart_item_id}, ${item.quantity + 1})">+</button>
                        </div>
                        <button class="cart-item-remove" onclick="removeCartItem(${item.cart_item_id})">
                            <i class="fas fa-trash-alt"></i> Remove
                        </button>
                    </div>
                </div>
                <div class="cart-item-price">
                    $${itemPrice.toFixed(2)}
                </div>
                <div class="cart-item-subtotal">
                    $${itemSubtotal.toFixed(2)}
                </div>
            </div>
        `;
    });
    
    cartContainer.innerHTML = cartHtml;
    
    // Calculate and update totals
    const subtotal = cartTotal;
    const shipping = subtotal > 50 ? 0 : 9.99;
    const tax = subtotal * 0.13;
    const total = subtotal + shipping + tax;
    
    if (subtotalElement) subtotalElement.textContent = `$${subtotal.toFixed(2)}`;
    if (shippingElement) shippingElement.textContent = shipping === 0 ? 'Free' : `$${shipping.toFixed(2)}`;
    if (taxElement) taxElement.textContent = `$${tax.toFixed(2)}`;
    if (totalElement) totalElement.textContent = `$${total.toFixed(2)}`;
}

// ============================================
// CHECKOUT FUNCTIONS
// ============================================
// Validate checkout form
function validateCheckoutForm() {
    const form = document.getElementById('checkoutForm');
    if (!form) return true;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    // Validate email
    const email = document.getElementById('email');
    if (email && email.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value)) {
            email.classList.add('error');
            isValid = false;
        }
    }
    
    // Validate phone
    const phone = document.getElementById('phone');
    if (phone && phone.value) {
        const phoneRegex = /^[\d\s\-\(\)\+]{10,}$/;
        if (!phoneRegex.test(phone.value)) {
            phone.classList.add('error');
            isValid = false;
        }
    }
    
    if (!isValid) {
        showNotification('Please fill in all required fields correctly', 'error');
    }
    
    return isValid;
}

// Process checkout
function processCheckout() {
    if (!validateCheckoutForm()) return false;
    
    const formData = {
        first_name: document.getElementById('first_name')?.value,
        last_name: document.getElementById('last_name')?.value,
        email: document.getElementById('email')?.value,
        phone: document.getElementById('phone')?.value,
        address: document.getElementById('address')?.value,
        city: document.getElementById('city')?.value,
        postal_code: document.getElementById('postal_code')?.value,
        payment_method: document.querySelector('input[name="payment_method"]:checked')?.value
    };
    
    showLoading(true);
    
    fetch('/api/checkout-api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'process',
            order_data: formData,
            cart_items: cartItems
        })
    })
    .then(response => response.json())
    .then(data => {
        showLoading(false);
        if (data.success) {
            // Clear local cart
            localStorage.removeItem('solemate_cart');
            cartItems = [];
            recalculateCart();
            
            // Redirect to order confirmation
            window.location.href = `/pages/dynamic/order-confirm.php?order_id=${data.order_id}`;
        } else {
            showNotification(data.message || 'Checkout failed', 'error');
        }
    })
    .catch(error => {
        showLoading(false);
        console.error('Error:', error);
        showNotification('An error occurred during checkout', 'error');
    });
}

// ============================================
// APPLY COUPON CODE
// ============================================
function applyCoupon() {
    const couponInput = document.getElementById('couponCode');
    const couponCode = couponInput?.value.trim();
    
    if (!couponCode) {
        showNotification('Please enter a coupon code', 'error');
        return;
    }
    
    fetch('/api/cart-api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'apply_coupon',
            coupon_code: couponCode
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Coupon applied! You saved $${data.discount.toFixed(2)}`, 'success');
            updateCartDisplay();
            if (couponInput) couponInput.value = '';
        } else {
            showNotification(data.message || 'Invalid coupon code', 'error');
        }
    })
    .catch(error => console.error('Error:', error));
}

// ============================================
// UTILITY FUNCTIONS
// ============================================
// Escape HTML to prevent XSS
function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Show notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
        <span>${escapeHtml(message)}</span>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Show/hide loading spinner
function showLoading(show) {
    let loader = document.getElementById('loadingSpinner');
    if (show) {
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'loadingSpinner';
            loader.className = 'loading-spinner';
            loader.innerHTML = '<div class="spinner"></div>';
            document.body.appendChild(loader);
        }
        loader.style.display = 'flex';
    } else if (loader) {
        loader.style.display = 'none';
    }
}

// Bind cart events
function bindCartEvents() {
    // Update quantity on input change
    document.addEventListener('change', function(e) {
        if (e.target.classList && e.target.classList.contains('cart-qty')) {
            const itemId = e.target.dataset.itemId;
            const newQty = parseInt(e.target.value);
            if (!isNaN(newQty)) {
                updateCartItemQuantity(itemId, newQty);
            }
        }
    });
}

// Add CSS for cart animations
const cartStyles = document.createElement('style');
cartStyles.textContent = `
    .cart-animation {
        animation: cartShake 0.5s ease-in-out;
    }
    
    @keyframes cartShake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    .cart-item {
        display: flex;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #e2e8f0;
        gap: 20px;
    }
    
    .cart-item-image img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .cart-item-details {
        flex: 2;
    }
    
    .cart-item-details h4 {
        margin-bottom: 5px;
    }
    
    .cart-item-meta {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 10px;
    }
    
    .cart-item-price,
    .cart-item-subtotal {
        width: 100px;
        text-align: right;
    }
    
    .cart-item-actions {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-top: 10px;
    }
    
    .cart-item-quantity {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .qty-btn {
        width: 30px;
        height: 30px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        cursor: pointer;
    }
    
    .cart-item-quantity input {
        width: 50px;
        height: 30px;
        text-align: center;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
    }
    
    .cart-item-remove {
        background: none;
        border: none;
        color: #ef4444;
        cursor: pointer;
        font-size: 13px;
    }
    
    .loading-spinner {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    
    .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #f3f4f6;
        border-top-color: #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    input.error {
        border-color: #ef4444 !important;
    }
    
    @media (max-width: 768px) {
        .cart-item {
            flex-wrap: wrap;
        }
        .cart-item-price,
        .cart-item-subtotal {
            display: none;
        }
        .cart-item-price-mobile {
            display: block;
            font-weight: bold;
        }
    }
`;
document.head.appendChild(cartStyles);
