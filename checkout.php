<?php
// Include database connection (starts session via DB handler)
require_once __DIR__ . '/config/database.php';

// If cart is empty and no order was just completed, redirect to shop
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$order_completed = isset($_SESSION['last_order_id']);

if (empty($cart) && !$order_completed) {
    header('Location: shop.php');
    exit;
}

$subtotal = 0;
foreach ($cart as $id => $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping_fee = ($subtotal >= 59.00 || $subtotal == 0) ? 0.00 : 5.00;
$grand_total = $subtotal + $shipping_fee;

$errors = [];
$customer_name = '';
$customer_phone = '';
$customer_email = '';
$customer_address = '';

// Process checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Sanitize inputs
    $customer_name = trim($_POST['customer_name']);
    $customer_phone = trim($_POST['customer_phone']);
    $customer_email = trim($_POST['customer_email']);
    $customer_address = trim($_POST['customer_address']);
    
    // Strict Validation
    if (empty($customer_name)) {
        $errors[] = "Full Name is required.";
    }
    
    if (empty($customer_phone)) {
        $errors[] = "Phone Number is required.";
    } elseif (!preg_match('/^[0-9]{10}$/', $customer_phone)) {
        $errors[] = "Phone Number must be exactly 10 digits.";
    }
    
    if (empty($customer_email)) {
        $errors[] = "Email Address is required.";
    } elseif (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email Address must be a valid format.";
    }
    
    if (empty($customer_address)) {
        $errors[] = "Shipping Address is required.";
    }
    
    // If no validation errors, proceed with Order creation and Stock checks
    if (empty($errors)) {
        try {
            // Start Transaction to guarantee atomicity and avoid race conditions
            $pdo->beginTransaction();
            
            // Re-verify stock and check for modifications
            foreach ($cart as $prod_id => $cart_item) {
                // Select for update locks the row so other requests wait until transaction commits
                $stock_stmt = $pdo->prepare("SELECT stock, name FROM products WHERE id = :id FOR UPDATE");
                $stock_stmt->execute(['id' => $prod_id]);
                $product_db = $stock_stmt->fetch();
                
                if (!$product_db) {
                    throw new Exception("Product '{$cart_item['name']}' no longer exists in the store.");
                }
                
                if ($product_db['stock'] < $cart_item['quantity']) {
                    throw new Exception("Not enough stock for '{$product_db['name']}'. Available: {$product_db['stock']} units, requested: {$cart_item['quantity']}. Please update your cart.");
                }
            }
            
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
            
            // Deduct stock and insert Order Items
            $deduct_stmt = $pdo->prepare("UPDATE products SET stock = stock - :qty WHERE id = :id");
            $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (:order_id, :product_id, :product_name, :price, :qty)");
            
            foreach ($cart as $prod_id => $cart_item) {
                // Deduct stock
                $deduct_stmt->execute([
                    'qty' => $cart_item['quantity'],
                    'id' => $prod_id
                ]);
                
                // Add order item details
                $item_stmt->execute([
                    'order_id' => $order_id,
                    'product_id' => $prod_id,
                    'product_name' => $cart_item['name'],
                    'price' => $cart_item['price'],
                    'qty' => $cart_item['quantity']
                ]);
            }
            
            // Commit Transaction
            $pdo->commit();
            
            // Clear Cart and Save Order ID in session to display success page
            $_SESSION['cart'] = [];
            $_SESSION['last_order_id'] = $order_id;
            $_SESSION['last_order_total'] = $grand_total;
            
            // Redirect to refresh and prevent double submit
            header('Location: checkout.php');
            exit;
            
        } catch (Exception $e) {
            // Roll back everything if any stock check or database query fails
            $pdo->rollBack();
            $errors[] = "Order Placement Failed: " . $e->getMessage();
        }
    }
}

// Check if displaying the success page
if ($order_completed) {
    $success_order_id = $_SESSION['last_order_id'];
    $success_order_total = $_SESSION['last_order_total'];
    
    // Clear session records for order confirmation
    unset($_SESSION['last_order_id']);
    unset($_SESSION['last_order_total']);
}

// Include header after processing redirection logic
require_once 'includes/header.php';
?>

    <!-- Page Title/Header Start -->
    <div class="page-title-section section bg-light py-4" style="border-bottom: 1px solid #f0f0f0;">
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="page-title text-center">
                        <h1 class="title" style="font-family: 'Marcellus', sans-serif;">Checkout</h1>
                        <ul class="breadcrumb justify-content-center p-0 m-0 bg-transparent">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Checkout</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Title/Header End -->

    <!-- Checkout Section Start -->
    <div class="section section-padding pt-5">
        <div class="container">
            
            <?php if (isset($success_order_id)): ?>
                <!-- Success view -->
                <div class="row justify-content-center">
                    <div class="col-md-8 col-12 text-center py-5" style="border: 1px solid #e0e0e0; border-radius: 4px; background-color: #fff;">
                        <i class="far fa-check-circle text-success mb-4" style="font-size: 80px;"></i>
                        <h2 class="mb-3" style="font-family: 'Marcellus', sans-serif;">Order Placed Successfully!</h2>
                        <p class="lead mb-4 text-muted">Thank you for your purchase. We have received your order details.</p>
                        
                        <div class="card bg-light border-0 p-4 mb-4 mx-auto" style="max-width: 400px; text-align: left;">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Order Number:</span>
                                <strong class="text-dark">#<?= sprintf('%06d', $success_order_id) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Paid:</span>
                                <strong class="text-dark">$<?= number_format($success_order_total, 2) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Payment Mode:</span>
                                <strong class="text-success">Cash on Delivery</strong>
                            </div>
                        </div>
                        
                        <p class="mb-4 text-muted">A confirmation email has been sent. We will contact you soon for shipping confirmation.</p>
                        <a href="shop.php" class="btn btn-dark px-4 py-3 btn-hover-primary" style="font-family: 'Marcellus', sans-serif; text-transform: uppercase;">Continue Shopping</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Checkout Form view -->
                <div class="row">
                    <div class="col-12">
                        <!-- Validation Errors -->
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i> Please correct the following errors:</h5>
                                <ul class="mb-0 pl-3">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <form action="checkout.php" method="POST" class="col-12">
                        <div class="row">
                            <!-- Billing Details Form -->
                            <div class="col-lg-7 col-12 mb-4">
                                <h3 class="mb-4 text-uppercase" style="font-family: 'Marcellus', sans-serif; font-size: 20px; letter-spacing: 1px;">Billing Details</h3>
                                
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="customer_name" class="form-label font-weight-500">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?= htmlspecialchars($customer_name) ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 col-12 mb-3">
                                        <label for="customer_phone" class="form-label font-weight-500">Phone Number (10 digits) <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="customer_phone" name="customer_phone" placeholder="e.g. 0912345678" value="<?= htmlspecialchars($customer_phone) ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 col-12 mb-3">
                                        <label for="customer_email" class="form-label font-weight-500">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="customer_email" name="customer_email" placeholder="e.g. name@domain.com" value="<?= htmlspecialchars($customer_email) ?>" required>
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <label for="customer_address" class="form-label font-weight-500">Shipping Address <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="customer_address" name="customer_address" rows="4" placeholder="Street Address, Ward, District, City" required><?= htmlspecialchars($customer_address) ?></textarea>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-group mb-0">
                                            <label class="form-label font-weight-500">Payment Method</label>
                                            <div class="form-check p-3" style="border: 1px solid #ddd; border-radius: 4px; background: #fff;">
                                                <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                                                <label class="form-check-label font-weight-bold" for="cod">
                                                    Cash on Delivery (COD)
                                                </label>
                                                <div class="form-text text-muted mt-1">Pay with cash upon delivery to your doorstep. Safe and convenient.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Summary -->
                            <div class="col-lg-5 col-12 mb-4">
                                <h3 class="mb-4 text-uppercase" style="font-family: 'Marcellus', sans-serif; font-size: 20px; letter-spacing: 1px;">Your Order</h3>
                                
                                <div class="card border-0 p-4 bg-light" style="border-radius: 4px;">
                                    <table class="table table-borderless align-middle mb-4">
                                        <thead>
                                            <tr class="border-bottom font-weight-bold text-dark" style="font-family: 'Marcellus', sans-serif; font-size: 14px;">
                                                <th class="pb-2">Product</th>
                                                <th class="pb-2 text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($cart as $id => $item): ?>
                                                <tr class="border-bottom" style="font-size: 14px;">
                                                    <td class="py-2 text-muted">
                                                        <?= htmlspecialchars($item['name']) ?> <strong class="text-dark">&times; <?= $item['quantity'] ?></strong>
                                                    </td>
                                                    <td class="py-2 text-end" style="font-family: 'Futura', sans-serif;">
                                                        $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            
                                            <tr style="font-size: 14px;">
                                                <td class="pt-3 text-muted">Subtotal</td>
                                                <td class="pt-3 text-end font-weight-500" style="font-family: 'Futura', sans-serif;">$<?= number_format($subtotal, 2) ?></td>
                                            </tr>
                                            
                                            <tr class="border-bottom" style="font-size: 14px;">
                                                <td class="pb-3 text-muted">Shipping</td>
                                                <td class="pb-3 text-end font-weight-500" style="font-family: 'Futura', sans-serif;">
                                                    <?php if ($shipping_fee == 0.00): ?>
                                                        <span class="text-success font-weight-bold">Free</span>
                                                    <?php else: ?>
                                                        $<?= number_format($shipping_fee, 2) ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            
                                            <tr style="font-size: 18px; font-weight: bold; color: #222;">
                                                <td class="pt-3">Total</td>
                                                <td class="pt-3 text-end" style="font-family: 'Futura', sans-serif;">$<?= number_format($grand_total, 2) ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    
                                    <button type="submit" name="place_order" class="btn btn-dark w-100 py-3 text-uppercase btn-hover-primary" style="font-family: 'Marcellus', sans-serif; font-size: 14px; letter-spacing: 1px;">
                                        Place Order
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

        </div>
    </div>
    <!-- Checkout Section End -->

<?php require_once 'includes/footer.php'; ?>
