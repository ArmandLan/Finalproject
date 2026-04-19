<?php
/**
 * SoleMate - Wishlist API
 * Handles wishlist operations: add, remove, get
 */

require_once '../includes/auth.php';
require_once '../config/database.php';

header('Content-Type: application/json');

// Require login for wishlist
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to use wishlist']);
    exit;
}

$conn = getConnection();
$response = ['success' => false, 'message' => ''];
$user_id = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'add':
        $product_id = (int)($input['product_id'] ?? 0);
        
        if (!$product_id) {
            $response['message'] = 'Product ID required';
            break;
        }
        
        // Check if already in wishlist
        $stmt = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        
        if (!$stmt->fetch()) {
            $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $product_id]);
            $response['message'] = 'Added to wishlist';
        } else {
            $response['message'] = 'Product already in wishlist';
        }
        $response['success'] = true;
        break;
        
    case 'remove':
        $product_id = (int)($input['product_id'] ?? 0);
        
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        
        $response['success'] = true;
        $response['message'] = 'Removed from wishlist';
        break;
        
    case 'get':
        $stmt = $conn->prepare("
            SELECT w.*, p.name, p.sku, p.price, p.sale_price, p.image_main, p.brand
            FROM wishlist w
            JOIN products p ON w.product_id = p.product_id
            WHERE w.user_id = ?
            ORDER BY w.added_at DESC
        ");
        $stmt->execute([$user_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response['success'] = true;
        $response['wishlist'] = $items;
        break;
        
    default:
        $response['message'] = 'Unknown action';
}

echo json_encode($response);
?>
