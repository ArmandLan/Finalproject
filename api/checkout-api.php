<?php
/**
 * SoleMate - Checkout API
 * Processes orders and creates order records
 */

require_once '../includes/auth.php';
require_once '../config/database.php';

header('Content-Type: application/json');

// Require login for checkout
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to checkout']);
    exit;
}

$conn = getConnection();
$response = ['success' => false, 'message' => ''];

$user_id = $_SESSION['user_id'];

// Get cart items
$stmt = $conn->prepare("
    SELECT cart_id FROM carts WHERE user_id = ?
");
$stmt->execute([$user_id]);
$cart = $stmt->fetch();

if (!$cart) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

$cart_id = $cart['cart_id'];

// Get cart items
$stmt = $conn->prepare("
    SELECT ci.*, p.name, p.price, p.sale_price, p.sku
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.product_id
    WHERE ci.cart_id = ?
");
$stmt->execute([$cart_id]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $price = $item['sale_price'] ?: $item['price'];
    $subtotal += $price * $item['quantity'];
}
$shipping = $subtotal > 50 ? 0 : 9.99;
$tax = $subtotal * 0.13;
$total = $subtotal + $shipping + $tax;

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($input['first_name'] ?? '');
    $last_name = trim($input['last_name'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $address = trim($input['address'] ?? '');
    $city = trim($input['city'] ?? '');
    $postal_code = trim($input['postal_code'] ?? '');
    $province = trim($input['province'] ?? '');
    $payment_method = trim($input['payment_method'] ?? 'credit_card');
    $notes = trim($input['notes'] ?? '');
    
    // Validate
    $errors = [];
    if (empty($first_name)) $errors[] = 'First name required';
    if (empty($last_name)) $errors[] = 'Last name required';
    if (empty($email)) $errors[] = 'Email required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email';
    if (empty($address)) $errors[] = 'Address required';
    if (empty($city)) $errors[] = 'City required';
    if (empty($postal_code)) $errors[] = 'Postal code required';
    
    if (!empty($errors)) {
        $response['message'] = implode(', ', $errors);
        echo json_encode($response);
        exit;
    }
    
    $full_address = "$address, $city, $province $postal_code";
    
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Generate order number
        $order_number = 'SOLE-' . strtoupper(uniqid());
        
        // Insert order
        $stmt = $conn->prepare("
            INSERT INTO orders (
                user_id, order_number, subtotal, shipping_cost, tax_amount, total_amount,
                status, shipping_address, shipping_city, shipping_postal_code,
                payment_method, notes, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $user_id, $order_number, $subtotal, $shipping, $tax, $total,
            $full_address, $city, $postal_code, $payment_method, $notes
        ]);
        $order_id = $conn->lastInsertId();
        
        // Insert order items
        foreach ($cart_items as $item) {
            $price = $item['sale_price'] ?: $item['price'];
            $stmt = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, size, quantity, price)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $order_id, $item['product_id'], $item['name'], 
                $item['size'], $item['quantity'], $price
            ]);
            
            // Update inventory
            $stmt = $conn->prepare("
                UPDATE product_sizes 
                SET quantity = quantity - ? 
                WHERE product_id = ? AND size_value = ?
            ");
            $stmt->execute([$item['quantity'], $item['product_id'], $item['size']]);
        }
        
        // Clear cart
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $stmt->execute([$cart_id]);
        
        // Commit transaction
        $conn->commit();
        
        $response['success'] = true;
        $response['order_id'] = $order_id;
        $response['order_number'] = $order_number;
        $response['message'] = 'Order placed successfully';
        
    } catch (Exception $e) {
        $conn->rollBack();
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>
