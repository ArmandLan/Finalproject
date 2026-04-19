/**
 * SoleMate - Admin Dashboard Scripts
 * Handles admin panel functionality: product management, user management, analytics
 */

// ============================================
// ADMIN VARIABLES
// ============================================
let currentPage = 1;
let itemsPerPage = 20;
let currentChart = null;

// ============================================
// ADMIN INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    initAdminDashboard();
    initProductManagement();
    initOrderManagement();
    initUserManagement();
    initTemplateSwitcher();
    initSystemMonitoring();
});

// Initialize admin dashboard
function initAdminDashboard() {
    // Load dashboard stats
    loadDashboardStats();
    
    // Load recent activity
    loadRecentActivity();
    
    // Initialize charts
    initSalesChart();
    initCategoryChart();
}

// Load dashboard statistics
function loadDashboardStats() {
    fetch('/admin/api/stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalProducts')?.textContent = data.total_products || 0;
                document.getElementById('totalOrders')?.textContent = data.total_orders || 0;
                document.getElementById('totalUsers')?.textContent = data.total_users || 0;
                document.getElementById('totalRevenue')?.textContent = `$${formatNumber(data.total_revenue || 0)}`;
                document.getElementById('pendingOrders')?.textContent = data.pending_orders || 0;
                document.getElementById('lowStock')?.textContent = data.low_stock || 0;
            }
        })
        .catch(error => console.error('Error loading stats:', error));
}

// Load recent activity
function loadRecentActivity() {
    fetch('/admin/api/recent-activity.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recentActivity');
            if (container && data.activities) {
                container.innerHTML = data.activities.map(activity => `
                    <div class="activity-item">
                        <div class="activity-icon ${activity.type}">
                            <i class="fas ${getActivityIcon(activity.type)}"></i>
                        </div>
                        <div class="activity-details">
                            <p>${escapeHtml(activity.message)}</p>
                            <span class="activity-time">${formatTimeAgo(activity.created_at)}</span>
                        </div>
                    </div>
                `).join('');
            }
        })
        .catch(error => console.error('Error loading activity:', error));
}

// Initialize sales chart
function initSalesChart() {
    const canvas = document.getElementById('salesChart');
    if (!canvas) return;
    
    fetch('/admin/api/sales-data.php')
        .then(response => response.json())
        .then(data => {
            const ctx = canvas.getContext('2d');
            if (currentChart) currentChart.destroy();
            
            currentChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Sales',
                        data: data.sales || [0, 0, 0, 0, 0, 0],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { callbacks: { label: (ctx) => `$${ctx.raw.toFixed(2)}` } }
                    }
                }
            });
        })
        .catch(error => console.error('Error loading sales chart:', error));
}

// Initialize category chart
function initCategoryChart() {
    const canvas = document.getElementById('categoryChart');
    if (!canvas) return;
    
    fetch('/admin/api/category-data.php')
        .then(response => response.json())
        .then(data => {
            const ctx = canvas.getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        data: data.values || [],
                        backgroundColor: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        })
        .catch(error => console.error('Error loading category chart:', error));
}

// ============================================
// PRODUCT MANAGEMENT
// ============================================
function initProductManagement() {
    // Load product list
    loadProductList();
    
    // Bind product form submission
    const productForm = document.getElementById('productForm');
    if (productForm) {
        productForm.addEventListener('submit', saveProduct);
    }
    
    // Bind delete buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-product')) {
            const productId = e.target.closest('.delete-product').dataset.id;
            deleteProduct(productId);
        }
    });
}

// Load product list with pagination
function loadProductList(page = 1) {
    const container = document.getElementById('productList');
    if (!container) return;
    
    fetch(`/admin/api/products.php?page=${page}&limit=${itemsPerPage}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderProductTable(data.products);
                renderPagination(data.total_pages, data.current_page);
            }
        })
        .catch(error => console.error('Error loading products:', error));
}

// Render product table
function renderProductTable(products) {
    const container = document.getElementById('productList');
    if (!container) return;
    
    if (!products || products.length === 0) {
        container.innerHTML = '<tr><td colspan="7" class="text-center">No products found</td></tr>';
        return;
    }
    
    container.innerHTML = products.map(product => `
        <tr>
            <td><img src="${product.image_main || '/assets/images/placeholder.jpg'}" alt="${escapeHtml(product.name)}" style="width: 50px; height: 50px; object-fit: cover;"></td>
            <td>${escapeHtml(product.name)}</td>
            <td>${escapeHtml(product.sku)}</td>
            <td>$${parseFloat(product.price).toFixed(2)}</td>
            <td>${product.sale_price ? '$' + parseFloat(product.sale_price).toFixed(2) : '-'}</td>
            <td><span class="stock-badge ${product.total_stock > 10 ? 'in-stock' : 'low-stock'}">${product.total_stock || 0}</span></td>
            <td>
                <button class="btn-icon edit-product" data-id="${product.product_id}" onclick="editProduct(${product.product_id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon delete-product" data-id="${product.product_id}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Edit product
function editProduct(productId) {
    window.location.href = `/admin/products/edit.php?id=${productId}`;
}

// Save product (add/edit)
function saveProduct(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    fetch('/admin/api/save-product.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product saved successfully!', 'success');
            setTimeout(() => {
                window.location.href = '/admin/products/list.php';
            }, 1500);
        } else {
            showNotification(data.message || 'Error saving product', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

// Delete product
function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        fetch('/admin/api/delete-product.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Product deleted', 'success');
                loadProductList(currentPage);
            } else {
                showNotification(data.message || 'Delete failed', 'error');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

// ============================================
// ORDER MANAGEMENT
// ============================================
function initOrderManagement() {
    loadOrderList();
}

function loadOrderList(status = 'all', page = 1) {
    const container = document.getElementById('orderList');
    if (!container) return;
    
    fetch(`/admin/api/orders.php?status=${status}&page=${page}&limit=${itemsPerPage}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderOrderTable(data.orders);
            }
        })
        .catch(error => console.error('Error loading orders:', error));
}

function renderOrderTable(orders) {
    const container = document.getElementById('orderList');
    if (!container) return;
    
    if (!orders || orders.length === 0) {
        container.innerHTML = '<tr><td colspan="6" class="text-center">No orders found</td></tr>';
        return;
    }
    
    container.innerHTML = orders.map(order => `
        <tr>
            <td>${escapeHtml(order.order_number)}</td>
            <td>${formatDate(order.created_at)}</td>
            <td>${escapeHtml(order.customer_name)}</td>
            <td>$${parseFloat(order.total_amount).toFixed(2)}</td>
            <td><span class="status-badge status-${order.status}">${order.status}</span></td>
            <td>
                <button class="btn-icon view-order" onclick="viewOrder(${order.order_id})">
                    <i class="fas fa-eye"></i>
                </button>
                <select class="order-status-select" onchange="updateOrderStatus(${order.order_id}, this.value)">
                    <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                    <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Processing</option>
                    <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>Shipped</option>
                    <option value="delivered" ${order.status === 'delivered' ? 'selected' : ''}>Delivered</option>
                    <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                </select>
            </td>
        </tr>
    `).join('');
}

function viewOrder(orderId) {
    window.location.href = `/admin/orders/view.php?id=${orderId}`;
}

function updateOrderStatus(orderId, status) {
    fetch('/admin/api/update-order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: orderId, status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Order status updated', 'success');
        } else {
            showNotification(data.message || 'Update failed', 'error');
        }
    })
    .catch(error => console.error('Error:', error));
}

// ============================================
// USER MANAGEMENT
// ============================================
function initUserManagement() {
    loadUserList();
}

function loadUserList(page = 1) {
    const container = document.getElementById('userList');
    if (!container) return;
    
    fetch(`/admin/api/users.php?page=${page}&limit=${itemsPerPage}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderUserTable(data.users);
            }
        })
        .catch(error => console.error('Error loading users:', error));
}

function renderUserTable(users) {
    const container = document.getElementById('userList');
    if (!container) return;
    
    if (!users || users.length === 0) {
        container.innerHTML = '<tr><td colspan="6" class="text-center">No users found</td></tr>';
        return;
    }
    
    container.innerHTML = users.map(user => `
        <tr>
            <td>${escapeHtml(user.full_name)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${user.role}</td>
            <td><span class="status-badge ${user.status === 'active' ? 'status-active' : 'status-disabled'}">${user.status}</span></td>
            <td>${formatDate(user.created_at)}</td>
            <td>
                <button class="btn-icon" onclick="editUser(${user.user_id})"><i class="fas fa-edit"></i></button>
                <button class="btn-icon" onclick="toggleUserStatus(${user.user_id}, '${user.status}')">
                    <i class="fas ${user.status === 'active' ? 'fa-ban' : 'fa-check-circle'}"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function editUser(userId) {
    window.location.href = `/admin/users/edit.php?id=${userId}`;
}

function toggleUserStatus(userId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'disabled' : 'active';
    const action = newStatus === 'active' ? 'enable' : 'disable';
    
    if (confirm(`Are you sure you want to ${action} this user?`)) {
        fetch('/admin/api/toggle-user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(`User ${action}d successfully`, 'success');
                loadUserList();
            } else {
                showNotification(data.message || 'Action failed', 'error');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

// ============================================
// TEMPLATE SWITCHER (3 themes)
// ============================================
function initTemplateSwitcher() {
    // Load saved theme
    const savedTheme = localStorage.getItem('admin_theme') || 'light';
    applyTheme(savedTheme);
    
    // Bind theme buttons
    document.querySelectorAll('.theme-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const theme = this.dataset.theme;
            applyTheme(theme);
            saveThemeSettings(theme);
        });
    });
}

function applyTheme(theme) {
    document.body.classList.remove('light-theme', 'dark-theme', 'autumn-theme');
    
    if (theme === 'dark') {
        document.body.classList.add('dark-theme');
        document.documentElement.setAttribute('data-theme', 'dark');
    } else if (theme === 'autumn') {
        document.body.classList.add('autumn-theme');
        document.documentElement.setAttribute('data-theme', 'autumn');
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
    }
    
    localStorage.setItem('admin_theme', theme);
}

function saveThemeSettings(theme) {
    fetch('/admin/api/save-theme.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ theme: theme })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Theme saved', 'success');
        }
    })
    .catch(error => console.error('Error saving theme:', error));
}

// ============================================
// SYSTEM MONITORING
// ============================================
function initSystemMonitoring() {
    loadSystemStatus();
    setInterval(loadSystemStatus, 30000); // Refresh every 30 seconds
}

function loadSystemStatus() {
    fetch('/admin/api/system-status.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateSystemStatus(data.status);
            }
        })
        .catch(error => console.error('Error loading system status:', error));
}

function updateSystemStatus(status) {
    // Database status
    const dbStatus = document.getElementById('dbStatus');
    if (dbStatus) {
        dbStatus.className = `status-indicator ${status.database ? 'online' : 'offline'}`;
        dbStatus.querySelector('span').textContent = status.database ? 'Online' : 'Offline';
    }
    
    // API status
    const apiStatus = document.getElementById('apiStatus');
    if (apiStatus) {
        apiStatus.className = `status-indicator ${status.api ? 'online' : 'offline'}`;
        apiStatus.querySelector('span').textContent = status.api ? 'Online' : 'Offline';
    }
    
    // Server load
    const serverLoad = document.getElementById('serverLoad');
    if (serverLoad && status.server_load) {
        const loadPercent = Math.min(status.server_load * 10, 100);
        serverLoad.style.width = `${loadPercent}%`;
        serverLoad.className = `load-bar ${loadPercent > 80 ? 'high' : loadPercent > 50 ? 'medium' : 'low'}`;
        document.getElementById('loadValue').textContent = `${status.server_load.toFixed(2)}`;
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return `${seconds} seconds ago`;
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes} minutes ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} hours ago`;
    const days = Math.floor(hours / 24);
    if (days < 7) return `${days} days ago`;
    return formatDate(dateString);
}

function formatNumber(num) {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num.toString();
}

function getActivityIcon(type) {
    const icons = {
        'order': 'fa-shopping-cart',
        'user': 'fa-user',
        'product': 'fa-box',
        'review': 'fa-star'
    };
    return icons[type] || 'fa-bell';
}

function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i><span>${message}</span>`;
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 100);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function renderPagination(totalPages, currentPage) {
    const container = document.getElementById('pagination');
    if (!container) return;
    
    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '';
    for (let i = 1; i <= Math.min(totalPages, 10); i++) {
        html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="loadProductList(${i})">${i}</button>`;
    }
    container.innerHTML = html;
}

// Add admin styles
const adminStyles = document.createElement('style');
adminStyles.textContent = `
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .status-pending { background: #fef3c7; color: #d97706; }
    .status-processing { background: #dbeafe; color: #2563eb; }
    .status-shipped { background: #e0e7ff; color: #4338ca; }
    .status-delivered { background: #d1fae5; color: #059669; }
    .status-cancelled { background: #fee2e2; color: #dc2626; }
    .status-active { background: #d1fae5; color: #059669; }
    .status-disabled { background: #fee2e2; color: #dc2626; }
    .stock-badge { padding: 2px 6px; border-radius: 4px; font-size: 12px; }
    .in-stock { background: #d1fae5; color: #059669; }
    .low-stock { background: #fee2e2; color: #dc2626; }
    .btn-icon {
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px;
        margin: 0 2px;
        color: #64748b;
    }
    .btn-icon:hover { color: #3b82f6; }
    .activity-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 10px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .activity-icon {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .activity-icon.order { background: #dbeafe; color: #2563eb; }
    .activity-icon.user { background: #d1fae5; color: #059669; }
    .activity-icon.product { background: #fef3c7; color: #d97706; }
    .order-status-select {
        padding: 5px;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
    }
`;
document.head.appendChild(adminStyles);
