// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            this.classList.toggle('active');
        });
    }
    
    // Active Navigation Highlight
    const currentPage = window.location.pathname;
    const navLinks = document.querySelectorAll('.main-nav a, .mobile-menu a');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });
});

// Add to Cart Function
function addToCart(productId, size, quantity = 1) {
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
            updateCartCount(data.cart_count);
            showNotification('Product added to cart!', 'success');
        } else {
            showNotification(data.message || 'Error adding to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

// Update Cart Count
function updateCartCount(count) {
    const cartBadge = document.getElementById('cartCount');
    if (cartBadge) {
        cartBadge.textContent = count;
        if (count === 0) {
            cartBadge.style.display = 'none';
        } else {
            cartBadge.style.display = 'inline-block';
        }
    }
}

// Show Notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
        <span>${message}</span>
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

// Product Filtering
function filterProducts() {
    const category = document.getElementById('categoryFilter')?.value || '';
    const brand = document.getElementById('brandFilter')?.value || '';
    const minPrice = document.getElementById('minPrice')?.value || '';
    const maxPrice = document.getElementById('maxPrice')?.value || '';
    const sort = document.getElementById('sortBy')?.value || '';
    
    const params = new URLSearchParams();
    if (category) params.append('category', category);
    if (brand) params.append('brand', brand);
    if (minPrice) params.append('min_price', minPrice);
    if (maxPrice) params.append('max_price', maxPrice);
    if (sort) params.append('sort', sort);
    
    window.location.href = `/pages/dynamic/products.php?${params.toString()}`;
}

// Interactive Map (Store Locator)
function initStoreMap() {
    const mapContainer = document.getElementById('storeMap');
    if (!mapContainer) return;
    
    const map = L.map('storeMap').setView([42.3149, -83.0364], 13); // Windsor coordinates
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Store locations
    const stores = [
        { lat: 42.3149, lng: -83.0364, name: 'SoleMate Downtown', address: '123 Main St, Windsor, ON' },
        { lat: 42.2850, lng: -83.0010, name: 'SoleMate East', address: '456 Tecumseh Rd, Windsor, ON' }
    ];
    
    stores.forEach(store => {
        L.marker([store.lat, store.lng])
            .bindPopup(`<b>${store.name}</b><br>${store.address}`)
            .addTo(map);
    });
}

// Price Trend Chart
function createPriceTrendChart(elementId, data) {
    const ctx = document.getElementById(elementId);
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Average Price',
                data: data.prices,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
}

// Size Guide Interactive Tool
function initSizeGuide() {
    const sizeSelect = document.getElementById('shoeSize');
    const footLength = document.getElementById('footLength');
    
    if (sizeSelect && footLength) {
        sizeSelect.addEventListener('change', function() {
            const sizeMap = {
                '6': '9.25',
                '7': '9.625',
                '8': '10.0',
                '9': '10.375',
                '10': '10.75',
                '11': '11.125'
            };
            footLength.textContent = sizeMap[this.value] || 'Select size';
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initStoreMap();
    initSizeGuide();
    
    // Price range slider
    const priceSlider = document.getElementById('priceRange');
    if (priceSlider) {
        priceSlider.addEventListener('input', function() {
            document.getElementById('priceValue').textContent = `$${this.value}`;
        });
    }
});
