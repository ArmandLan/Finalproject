<?php
/**
 * SoleMate - Cart API
 * Handles all cart operations: add, update, remove, get, clear, apply coupon
 */

require_once '../includes/auth.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$conn = getConnection();
$response = ['success' => false, 'message' => ''];

// Get or create session ID for non-logged-in users
if (!isset($_COOKIE['session_id'])) {
    $session_id = session_id() . '_' . uniqid();
    setcookie('session_id', $session_id, time() + 86400 * 30, '/');
} else {
    $session_id = $_COOKIE['session_id'];
}

// Get or create cart
function getCartId($conn, $user_id, $session_id) {
    if ($user_id) {
        $stmt = $conn->prepare("SELECT cart_id FROM carts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart = $stmt->fetch();
        if ($cart) return $cart['cart_id'];
        
        // Create cart for user
        $stmt = $conn->prepare("INSERT INTO carts (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        return $conn->lastInsertId();
    } else {
        $stmt = $conn->prepare("SELECT cart_id FROM carts WHERE session_id = ?");
        $stmt->execute([$session_id]);
        $cart = $stmt->fetch();
        if ($cart) return $cart['cart_id'];
        
        // Create cart for session
        $stmt = $conn->prepare("INSERT INTO carts (session_id) VALUES (?)");
        $stmt->execute([$session_id]);
        return $conn->lastInsertId();
    }
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$cart_id = getCartId($conn, $user_id, $session_id);

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$action = isset($_GET['action']) ? $_GET['action'] : ($input['action'] ?? '');

switch ($action) {
    case 'add':
        $product_id = (int)($input['product_id'] ?? 0);
        $size = trim($input['size'] ?? '');
        $quantity = (int)($input['quantity'] ?? 1);
        
        if (!$product_id || !$size) {
            $response['message'] = 'Product ID and size are required';
            break;
        }
        
        // Check if item exists in cart
        $stmt = $conn->prepare("
            SELECT cart_item_id, quantity FROM cart_items 
            WHERE cart_id = ? AND product_id = ? AND size = ?
        ");
        $stmt->execute([$cart_id, $product_id, $size]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update quantity
            $new_quantity = $existing['quantity'] + $quantity;
            $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
            $stmt->execute([$new_quantity, $existing['cart_item_id']]);
        } else {
            // Insert new item
            $stmt = $conn->prepare("
                INSERT INTO cart_items (cart_id, product_id, size, quantity) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$cart_id, $product_id, $size, $quantity]);
        }
        
        // Get updated cart count
        $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart_items WHERE cart_id = ?");
        $stmt->execute([$cart_id]);
        $count = $stmt->fetch()['count'] ?? 0;
        
        $response['success'] = true;
        $response['message'] = 'Product added to cart';
        $response['cart_count'] = $count;
        break;
        
    case 'update':
        $cart_item_id = (int)($input['cart_item_id'] ?? 0);
        $quantity = (int)($input['quantity'] ?? 1);
        
        if ($quantity <= 0) {
            // Remove item
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ? AND cart_id = ?");
            $stmt->execute([$cart_item_id, $cart_id]);
        } else {
            $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ? AND cart_id = ?");
            $stmt->execute([$quantity, $cart_item_id, $cart_id]);
        }
        
        $response['success'] = true;
        break;
        
    case 'remove':
        $cart_item_id = (int)($input['cart_item_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ? AND cart_id = ?");
        $stmt->execute([$cart_item_id, $cart_id]);
        $response['success'] = true;
        break;
        
    case 'clear':
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $stmt->execute([$cart_id]);
        $response['success'] = true;
        break;
        
    case 'get':
        $stmt = $conn->prepare("
            SELECT ci.*, p.name, p.sku, p.price, p.sale_price, p.image_main, p.brand
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.product_id
            WHERE ci.cart_id = ?
        ");
        $stmt->execute([$cart_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response['success'] = true;
        $response['cart'] = $items;
        break;
        
    case 'getCount':
        $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart_items WHERE cart_id = ?");
        $stmt->execute([$cart_id]);
        $count = $stmt->fetch()['count'] ?? 0;
        $response['success'] = true;
        $response['count'] = $count;
        break;
        
    case 'apply_coupon':
        $coupon_code = trim($input['coupon_code'] ?? '');
        // Simple coupon validation (expand as needed)
        $coupons = [
            'SAVE10' => ['discount' => 10, 'type' => 'percent'],
            'SAVE20' => ['discount' => 20, 'type' => 'percent'],
            'FREESHIP' => ['discount' => 0, 'type' => 'shipping']
        ];
        
        if (isset($coupons[$coupon_code])) {
            $_SESSION['coupon'] = $coupons[$coupon_code];
            $response['success'] = true;
            $response['message'] = 'Coupon applied';
            $response['discount'] = $coupons[$coupon_code]['discount'];
        } else {
            $response['message'] = 'Invalid coupon code';
        }
        break;
        
    default:
        $response['message'] = 'Unknown action';
}

echo json_encode($response);
?>
