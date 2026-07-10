<?php
// api/orders.php - REST API endpoint for orders (Customer placement + Admin retrieval/updating)
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Get JSON raw body data
$raw_input = file_get_contents('php://input');
$input_data = json_decode($raw_input, true) ?: $_POST;

if ($method === 'POST') {
    // --- POST: CREATE ORDER ---
    $customer_name = isset($input_data['customer_name']) ? trim($input_data['customer_name']) : '';
    $customer_phone = isset($input_data['customer_phone']) ? trim($input_data['customer_phone']) : '';
    $customer_email = isset($input_data['customer_email']) ? trim($input_data['customer_email']) : '';
    $customer_address = isset($input_data['customer_address']) ? trim($input_data['customer_address']) : '';
    $items = isset($input_data['items']) ? $input_data['items'] : []; // Array of ['product_id' => X, 'quantity' => Y]
    
    $errors = [];
    
    // Strict Validation
    if (empty($customer_name)) $errors[] = "customer_name is required.";
    if (empty($customer_phone)) {
        $errors[] = "customer_phone is required.";
    } elseif (!preg_match('/^[0-9]{10}$/', $customer_phone)) {
        $errors[] = "customer_phone must be exactly 10 digits.";
    }
    
    if (empty($customer_email)) {
        $errors[] = "customer_email is required.";
    } elseif (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "customer_email must be a valid email format.";
    }
    
    if (empty($customer_address)) $errors[] = "customer_address is required.";
    if (empty($items) || !is_array($items)) {
        $errors[] = "A non-empty list of items is required.";
    }
    
    if (count($errors) > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        $total_amount = 0.00;
        $validated_items = [];
        
        // Lock and check stock for all items
        foreach ($items as $item) {
            $prod_id = isset($item['product_id']) ? (int)$item['product_id'] : 0;
            $qty = isset($item['quantity']) ? (int)$item['quantity'] : 0;
            
            if ($prod_id <= 0 || $qty <= 0) {
                throw new Exception("Invalid product_id or quantity in items list.");
            }
            
            // SELECT FOR UPDATE locks the product rows
            $stmt = $pdo->prepare("SELECT price, stock, name FROM products WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $prod_id]);
            $prod_db = $stmt->fetch();
            
            if (!$prod_db) {
                throw new Exception("Product ID {$prod_id} does not exist.");
            }
            
            if ($prod_db['stock'] < $qty) {
                throw new Exception("Insufficient stock for '{$prod_db['name']}'. In stock: {$prod_db['stock']}, requested: {$qty}.");
            }
            
            $line_total = $prod_db['price'] * $qty;
            $total_amount += $line_total;
            
            $validated_items[] = [
                'id' => $prod_id,
                'name' => $prod_db['name'],
                'price' => $prod_db['price'],
                'qty' => $qty
            ];
        }
        
        // Shipping calculation (free over $59, else $5.00)
        $shipping_fee = ($total_amount >= 59.00) ? 0.00 : 5.00;
        $grand_total = $total_amount + $shipping_fee;
        
        // Insert Order
        $order_stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_phone, customer_email, customer_address, total_amount, status) VALUES (:name, :phone, :email, :address, :total, 'Pending')");
        $order_stmt->execute([
            'name' => $customer_name,
            'phone' => $customer_phone,
            'email' => $customer_email,
            'address' => $customer_address,
            'total' => $grand_total
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Deduct inventory and insert items
        $deduct_stmt = $pdo->prepare("UPDATE products SET stock = stock - :qty WHERE id = :id");
        $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (:order_id, :product_id, :product_name, :price, :qty)");
        
        foreach ($validated_items as $v_item) {
            $deduct_stmt->execute(['qty' => $v_item['qty'], 'id' => $v_item['id']]);
            $item_stmt->execute([
                'order_id' => $order_id,
                'product_id' => $v_item['id'],
                'product_name' => $v_item['name'],
                'price' => $v_item['price'],
                'qty' => $v_item['qty']
            ]);
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully.',
            'data' => [
                'order_id' => (int)$order_id,
                'total_amount' => $grand_total,
                'shipping_fee' => $shipping_fee
            ]
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to place order: ' . $e->getMessage()
        ]);
    }
    exit;
}

// --- ADMIN ACCESS ONLY (GET, PUT) ---
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Admin session required.'
    ]);
    exit;
}

if ($method === 'GET') {
    // --- GET: FETCH ORDERS ---
    $order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    try {
        if ($order_id > 0) {
            // Fetch single order details with items
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id");
            $stmt->execute(['id' => $order_id]);
            $order = $stmt->fetch();
            
            if (!$order) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Order not found.']);
                exit;
            }
            
            $items_stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
            $items_stmt->execute(['order_id' => $order_id]);
            $order['items'] = $items_stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'message' => 'Order retrieved successfully',
                'data' => $order
            ]);
        } else {
            // Fetch all orders
            $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
            $orders = $stmt->fetchAll();
            
            // Optionally merge items for all orders (to make the API super rich!)
            $items_stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
            foreach ($orders as &$o) {
                $items_stmt->execute(['order_id' => $o['id']]);
                $o['items'] = $items_stmt->fetchAll();
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => $orders
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch orders: ' . $e->getMessage()
        ]);
    }
    exit;
}

if ($method === 'PUT') {
    // --- PUT: UPDATE ORDER STATUS ---
    $order_id = isset($input_data['id']) ? (int)$input_data['id'] : 0;
    $new_status = isset($input_data['status']) ? trim($input_data['status']) : '';
    
    $allowed_statuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];
    
    if ($order_id <= 0 || !in_array($new_status, $allowed_statuses)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'A valid order ID and a status (Pending, Processing, Completed, Cancelled) are required.'
        ]);
        exit;
    }
    
    try {
        // Verify order exists
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE id = :id");
        $check_stmt->execute(['id' => $order_id]);
        if ($check_stmt->fetchColumn() == 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Order not found.']);
            exit;
        }
        
        $update_stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $update_stmt->execute([
            'status' => $new_status,
            'id' => $order_id
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => "Order #{$order_id} status updated to '{$new_status}' successfully."
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update order status: ' . $e->getMessage()
        ]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
