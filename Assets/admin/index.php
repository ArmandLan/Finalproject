<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireAdmin();

$conn = getConnection();

// Get dashboard statistics
$stats = [];

// Total products
$stmt = $conn->query("SELECT COUNT(*) as count FROM products");
$stats['products'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total orders
$stmt = $conn->query("SELECT COUNT(*) as count FROM orders");
$stats['orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total users
$stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total revenue
$stmt = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'");
$stats['revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Recent orders
$stmt = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Low stock products
$stmt = $conn->query("
    SELECT p.product_id, p.name, ps.size_value, ps.quantity 
    FROM product_sizes ps 
    JOIN products p ON ps.product_id = p.product_id 
    WHERE ps.quantity < 10 
    ORDER BY ps.quantity ASC 
    LIMIT 10
");
$low_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Admin Dashboard';
?>

<?php include '../includes/header.php'; ?>

<div class="admin-container">
    <div class="admin-sidebar">
        <div class="admin-logo">
            <h2>SoleMate Admin</h2>
        </div>
        <nav class="admin-nav">
            <a href="/admin/" class="active"><i class="fas fa-dashboard"></i> Dashboard</a>
            <a href="/admin/products/list.php"><i class="fas fa-shoe-prints"></i> Products</a>
            <a href="/admin/orders/list.php"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="/admin/users/list.php"><i class="fas fa-users"></i> Users</a>
            <a href="/admin/categories/list.php"><i class="fas fa-tags"></i> Categories</a>
            <a href="/admin/reviews/moderate.php"><i class="fas fa-star"></i> Reviews</a>
            <a href="/admin/templates/settings.php"><i class="fas fa-palette"></i> Templates</a>
            <a href="/admin/monitor/status.php"><i class="fas fa-chart-line"></i> Monitor</a>
        </nav>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>Dashboard</h1>
            <div class="admin-user">
                <span>Welcome, <?php echo $_SESSION['user_name']; ?></span>
                <a href="/pages/dynamic/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shoe-prints"></i></div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['products']); ?></h3>
                    <p>Total Products</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['orders']); ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['users']); ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-info">
                    <h3>$<?php echo number_format($stats['revenue'], 2); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
        </div>
        
        <div class="admin-grid">
            <!-- Recent Orders -->
            <div class="admin-card">
                <div class="card-header">
                    <h3>Recent Orders</h3>
                    <a href="/admin/orders/list.php" class="btn-sm">View All</a>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td><?php echo $order['order_number']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                            <td><a href="/admin/orders/view.php?id=<?php echo $order['order_id']; ?>" class="btn-icon"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Low Stock Alerts -->
            <div class="admin-card">
                <div class="card-header">
                    <h3>Low Stock Alert</h3>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr><th>Product</th><th>Size</th><th>Quantity</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($low_stock as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo $item['size_value']; ?></td>
                            <td class="stock-low"><?php echo $item['quantity']; ?></td>
                            <td><a href="/admin/products/edit.php?id=<?php echo $item['product_id']; ?>" class="btn-icon"><i class="fas fa-edit"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.admin-container {
    display: flex;
    min-height: 100vh;
}

.admin-sidebar {
    width: 260px;
    background: var(--secondary-color);
    color: white;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
}

.admin-content {
    flex: 1;
    margin-left: 260px;
    padding: 20px;
    background: var(--bg-light);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: var(--shadow);
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: rgba(59, 130, 246, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: var(--primary-color);
}

.admin-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: var(--shadow);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th,
.admin-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.status-badge {
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

.stock-low { color: #dc2626; font-weight: bold; }
</style>

<?php include '../includes/footer.php'; ?>
