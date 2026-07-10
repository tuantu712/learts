<?php
// api/cart_actions.php - Handle shopping cart session operations
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prepare standard JSON response or Redirect helper
function respond($success, $message, $redirectUrl = '../cart.php') {
    if (isset($_GET['ajax']) || isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message
        ]);
        exit;
    } else {
        // Set flash message in session if desired
        $_SESSION['flash_message'] = [
            'type' => $success ? 'success' : 'danger',
            'text' => $message
        ];
        header('Location: ' . $redirectUrl);
        exit;
    }
}

// Ensure cart is initialized in session
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'add':
        $product_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
        $qty = isset($_GET['quantity']) ? (int)$_GET['quantity'] : (isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1);
        
        if ($product_id <= 0 || $qty <= 0) {
            respond(false, 'Invalid product or quantity.', '../shop.php');
        }
        
        // Check product in database
        try {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
            $stmt->execute(['id' => $product_id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                respond(false, 'Product not found.', '../shop.php');
            }
            
            // Check stock
            $current_in_cart = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id]['quantity'] : 0;
            $new_qty = $current_in_cart + $qty;
            
            if ($new_qty > $product['stock']) {
                respond(false, "Cannot add more. Only {$product['stock']} units are in stock, and you already have {$current_in_cart} in your cart.", "../product-detail.php?id=$product_id");
            }
            
            // Add or update cart
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => (float)$product['price'],
                'image_url' => $product['image_url'],
                'quantity' => $new_qty
            ];
            
            respond(true, "Added '{$product['name']}' to cart successfully!", '../cart.php');
            
        } catch (Exception $e) {
            respond(false, 'Database error occurred: ' . $e->getMessage(), '../shop.php');
        }
        break;
        
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respond(false, 'Invalid request method.', '../cart.php');
        }
        
        $quantities = isset($_POST['quantities']) ? $_POST['quantities'] : [];
        if (!is_array($quantities)) {
            respond(false, 'Invalid data submitted.', '../cart.php');
        }
        
        $errors = [];
        $updates = [];
        
        // Validate all updates first before applying
        foreach ($quantities as $id => $qty) {
            $id = (int)$id;
            $qty = (int)$qty;
            
            if (!isset($_SESSION['cart'][$id])) {
                continue; // Item not in cart
            }
            
            if ($qty <= 0) {
                $updates[$id] = 0; // Will be removed
                continue;
            }
            
            try {
                $stmt = $pdo->prepare("SELECT stock, name FROM products WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $product = $stmt->fetch();
                
                if (!$product) {
                    $updates[$id] = 0; // Remove non-existent products
                } elseif ($qty > $product['stock']) {
                    $errors[] = "Requested quantity for '{$product['name']}' ({$qty}) exceeds available stock ({$product['stock']}).";
                } else {
                    $updates[$id] = $qty;
                }
            } catch (Exception $e) {
                $errors[] = "Error checking stock: " . $e->getMessage();
            }
        }
        
        if (count($errors) > 0) {
            respond(false, implode('<br>', $errors), '../cart.php');
        }
        
        // Apply updates
        foreach ($updates as $id => $qty) {
            if ($qty <= 0) {
                unset($_SESSION['cart'][$id]);
            } else {
                $_SESSION['cart'][$id]['quantity'] = $qty;
            }
        }
        
        respond(true, 'Cart updated successfully!', '../cart.php');
        break;
        
    case 'remove':
        $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (isset($_SESSION['cart'][$product_id])) {
            $name = $_SESSION['cart'][$product_id]['name'];
            unset($_SESSION['cart'][$product_id]);
            respond(true, "Removed '{$name}' from cart.", '../cart.php');
        } else {
            respond(false, 'Item not found in cart.', '../cart.php');
        }
        break;
        
    case 'clear':
        $_SESSION['cart'] = [];
        respond(true, 'Cart cleared successfully.', '../cart.php');
        break;
        
    default:
        respond(false, 'Invalid action.', '../cart.php');
        break;
}
