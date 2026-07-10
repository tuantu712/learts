<?php
// cart.php - Shopping Cart Page
require_once 'includes/header.php';

// Retrieve cart items
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = 0;
foreach ($cart as $id => $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Shipping calculation (free over $59, else $5.00)
$shipping_threshold = 59.00;
$shipping_fee = ($subtotal >= $shipping_threshold || $subtotal == 0) ? 0.00 : 5.00;
$grand_total = $subtotal + $shipping_fee;
?>

    <!-- Page Title/Header Start -->
    <div class="page-title-section section bg-light py-4" style="border-bottom: 1px solid #f0f0f0;">
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="page-title text-center">
                        <h1 class="title" style="font-family: 'Marcellus', sans-serif;">Shopping Cart</h1>
                        <ul class="breadcrumb justify-content-center p-0 m-0 bg-transparent">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Shopping Cart</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Title/Header End -->

    <!-- Shopping Cart Section Start -->
    <div class="section section-padding pt-5">
        <div class="container">
            
            <!-- Flash Message Alerts -->
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_message']['type']) ?> alert-dismissible fade show mb-4" role="alert">
                    <?= $_SESSION['flash_message']['text'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>

            <?php if (empty($cart)): ?>
                <div class="row">
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-shopping-basket text-muted mb-4" style="font-size: 72px;"></i>
                        <h3 class="mb-3 text-muted">Your Shopping Cart is Empty</h3>
                        <p class="mb-4">Add some handcrafted products to your cart and make them yours!</p>
                        <a href="shop.php" class="btn btn-dark px-4 py-3 btn-hover-primary" style="font-family: 'Marcellus', sans-serif; text-transform: uppercase;">Back to Shop</a>
                    </div>
                </div>
            <?php else: ?>
                <form action="api/cart_actions.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    
                    <div class="row">
                        <!-- Cart Table -->
                        <div class="col-lg-8 col-12 mb-4">
                            <div class="table-responsive">
                                <table class="table align-middle" style="border: 1px solid #eee;">
                                    <thead class="table-light">
                                        <tr class="text-uppercase" style="font-family: 'Marcellus', sans-serif; font-size: 13px; letter-spacing: 1px;">
                                            <th scope="col" class="border-0 py-3">Product</th>
                                            <th scope="col" class="border-0 py-3 text-center">Price</th>
                                            <th scope="col" class="border-0 py-3 text-center" style="width: 150px;">Quantity</th>
                                            <th scope="col" class="border-0 py-3 text-end">Total</th>
                                            <th scope="col" class="border-0 py-3 text-center">Remove</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cart as $id => $item): ?>
                                            <tr>
                                                <td class="py-3">
                                                    <div class="d-flex align-items-center">
                                                        <a href="product-detail.php?id=<?= $id ?>" class="me-3" style="width: 70px; flex-shrink: 0;">
                                                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="img-fluid" style="border: 1px solid #eee; border-radius: 4px;">
                                                        </a>
                                                        <div>
                                                            <a href="product-detail.php?id=<?= $id ?>" class="text-dark font-weight-500 hover-primary" style="font-size: 15px; font-family: 'Marcellus', sans-serif;">
                                                                <?= htmlspecialchars($item['name']) ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 text-center text-muted" style="font-family: 'Futura', sans-serif;">
                                                    $<?= number_format($item['price'], 2) ?>
                                                </td>
                                                <td class="py-3 text-center">
                                                    <div class="d-inline-flex align-items-center" style="border: 1px solid #ddd; border-radius: 4px; overflow: hidden; background: #fff;">
                                                        <button type="button" class="btn btn-sm px-2 py-1 border-0 bg-transparent text-secondary" onclick="stepQuantity(<?= $id ?>, -1)">&minus;</button>
                                                        <input type="number" id="qty-<?= $id ?>" name="quantities[<?= $id ?>]" value="<?= $item['quantity'] ?>" min="1" class="text-center border-0 font-weight-bold" style="width: 50px; outline: none;" readonly>
                                                        <button type="button" class="btn btn-sm px-2 py-1 border-0 bg-transparent text-secondary" onclick="stepQuantity(<?= $id ?>, 1)">&plus;</button>
                                                    </div>
                                                </td>
                                                <td class="py-3 text-end font-weight-500" style="font-family: 'Futura', sans-serif;">
                                                    $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                                </td>
                                                <td class="py-3 text-center">
                                                    <a href="api/cart_actions.php?action=remove&id=<?= $id ?>" class="text-danger" style="font-size: 18px;" onclick="return confirm('Remove this product?');">
                                                        <i class="far fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Cart Buttons -->
                            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                                <a href="shop.php" class="btn btn-outline-dark px-4 py-2 btn-hover-dark mb-2" style="font-family: 'Marcellus', sans-serif; text-transform: uppercase; font-size: 13px;">Continue Shopping</a>
                                <div>
                                    <a href="api/cart_actions.php?action=clear" class="btn btn-outline-danger px-4 py-2 me-2 mb-2" style="font-family: 'Marcellus', sans-serif; text-transform: uppercase; font-size: 13px;" onclick="return confirm('Are you sure you want to clear your entire cart?');">Clear Cart</a>
                                    <button type="submit" class="btn btn-dark px-4 py-2 mb-2" style="font-family: 'Marcellus', sans-serif; text-transform: uppercase; font-size: 13px;">Update Cart</button>
                                </div>
                            </div>
                        </div>

                        <!-- Cart Totals Sidebar -->
                        <div class="col-lg-4 col-12 mb-4">
                            <div class="card bg-light border-0 p-4" style="border-radius: 4px;">
                                <h4 class="card-title mb-4 pb-2 border-bottom text-uppercase" style="font-family: 'Marcellus', sans-serif; font-size: 18px; letter-spacing: 1px;">Cart Totals</h4>
                                
                                <div class="d-flex justify-content-between mb-3" style="font-size: 15px;">
                                    <span class="text-muted">Subtotal</span>
                                    <span class="font-weight-500" style="font-family: 'Futura', sans-serif;">$<?= number_format($subtotal, 2) ?></span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-3" style="font-size: 15px;">
                                    <span class="text-muted">Shipping</span>
                                    <span class="font-weight-500" style="font-family: 'Futura', sans-serif;">
                                        <?php if ($shipping_fee == 0.00): ?>
                                            <span class="text-success font-weight-bold">Free Shipping</span>
                                        <?php else: ?>
                                            $<?= number_format($shipping_fee, 2) ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                
                                <?php if ($shipping_fee > 0.00): ?>
                                    <p class="text-muted mb-4" style="font-size: 12px; line-height: 1.4;">Add <strong>$<?= number_format($shipping_threshold - $subtotal, 2) ?></strong> more to qualify for FREE shipping!</p>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between mb-4 pt-3 border-top" style="font-size: 18px; font-weight: bold; color: #222;">
                                    <span>Total</span>
                                    <span style="font-family: 'Futura', sans-serif;">$<?= number_format($grand_total, 2) ?></span>
                                </div>
                                
                                <a href="checkout.php" class="btn btn-dark w-100 py-3 text-uppercase btn-hover-primary" style="font-family: 'Marcellus', sans-serif; font-size: 14px; letter-spacing: 1px;">
                                    Proceed to Checkout
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <!-- Shopping Cart Section End -->

    <script>
        // Quantity adjustment function
        function stepQuantity(id, amount) {
            const input = document.getElementById('qty-' + id);
            let val = parseInt(input.value);
            val += amount;
            if (val < 1) val = 1;
            input.value = val;
        }
    </script>

<?php require_once 'includes/footer.php'; ?>
