<?php
// product-detail.php - Product Detail Page
require_once 'includes/header.php';

// Validate ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: shop.php');
    exit;
}

// Fetch product details
try {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = :id");
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: shop.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: shop.php');
    exit;
}

// Fetch related products (same category, excluding current product)
try {
    $related_stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.category_id = :category_id AND p.id != :id ORDER BY p.id DESC LIMIT 4");
    $related_stmt->execute(['category_id' => $product['category_id'], 'id' => $id]);
    $related_products = $related_stmt->fetchAll();
} catch (Exception $e) {
    $related_products = [];
}
?>

    <!-- Page Title/Header Start -->
    <div class="page-title-section section bg-light py-4" style="border-bottom: 1px solid #f0f0f0;">
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="page-title">
                        <ul class="breadcrumb p-0 m-0 bg-transparent">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="shop.php">Shop</a></li>
                            <li class="breadcrumb-item"><a href="shop.php?category=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name']) ?></a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Title/Header End -->

    <!-- Single Product Section Start -->
    <div class="section section-padding pt-5">
        <div class="container">
            <div class="row">
                
                <!-- Product Images Start -->
                <div class="col-lg-6 col-12 mb-5 mb-lg-0">
                    <div class="product-images" style="border: 1px solid #f0f0f0; border-radius: 4px; padding: 15px; text-align: center; background-color: #fff;">
                        <img id="main-product-image" src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="max-width: 100%; height: auto;">
                    </div>
                </div>
                <!-- Product Images End -->

                <!-- Product Summary Start -->
                <div class="col-lg-6 col-12">
                    <div class="product-summery" style="padding-left: 20px;">
                        
                        <!-- Ratings -->
                        <div class="product-ratings mb-2">
                            <span class="star-rating">
                                <span class="rating-active" style="width: 100%;">ratings</span>
                            </span>
                            <a href="#reviews" class="review-link text-muted" style="font-size: 13px;">(3 customer reviews)</a>
                        </div>
                        
                        <!-- Title -->
                        <h2 class="product-title" style="font-family: 'Marcellus', sans-serif; font-size: 32px; margin-bottom: 15px;">
                            <?= htmlspecialchars($product['name']) ?>
                        </h2>
                        
                        <!-- Price -->
                        <div class="product-price mb-3" style="font-size: 24px; color: #222; font-family: 'Futura', sans-serif; font-weight: 500;">
                            $<?= number_format($product['price'], 2) ?>
                        </div>
                        
                        <!-- Stock Status -->
                        <div class="product-stock-status mb-4">
                            <?php if ($product['stock'] > 0): ?>
                                <span class="badge bg-success text-white" style="font-size: 13px; font-weight: normal; padding: 6px 12px;">In Stock (<?= $product['stock'] ?> units available)</span>
                            <?php else: ?>
                                <span class="badge bg-danger text-white" style="font-size: 13px; font-weight: normal; padding: 6px 12px;">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Description -->
                        <div class="product-description mb-4 text-muted" style="line-height: 1.8; font-size: 15px;">
                            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        </div>
                        
                        <!-- Add to Cart Form -->
                        <?php if ($product['stock'] > 0): ?>
                            <form action="api/cart_actions.php" method="GET" class="product-variations mb-5">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                
                                <div class="d-flex align-items-center mb-4">
                                    <div class="quantity-input-wrapper d-flex align-items-center mr-3" style="border: 1px solid #ddd; border-radius: 4px; overflow: hidden; background: #fff;">
                                        <button type="button" class="qty-btn btn px-3 py-2 border-0 bg-transparent text-secondary" onclick="changeQty(-1)">&minus;</button>
                                        <input type="number" id="purchase-qty" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" class="text-center border-0 py-2" style="width: 60px; font-weight: bold; outline: none;" readonly>
                                        <button type="button" class="qty-btn btn px-3 py-2 border-0 bg-transparent text-secondary" onclick="changeQty(1)">&plus;</button>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-dark px-4 py-3 btn-hover-primary" style="font-family: 'Marcellus', sans-serif; text-transform: uppercase; font-size: 14px; letter-spacing: 1px;">
                                        <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning mb-5">This product is currently sold out and cannot be ordered.</div>
                        <?php endif; ?>

                        <!-- Meta Info -->
                        <div class="product-meta pt-4" style="border-top: 1px solid #eee; font-size: 14px;">
                            <table class="table table-borderless table-sm">
                                <tbody>
                                    <tr>
                                        <td class="pl-0 text-muted" style="width: 100px;">Category:</td>
                                        <td class="font-weight-500"><a href="shop.php?category=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name']) ?></a></td>
                                    </tr>
                                    <tr>
                                        <td class="pl-0 text-muted">SKU:</td>
                                        <td class="font-weight-500">LR-<?= sprintf('%05d', $product['id']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="pl-0 text-muted">Tags:</td>
                                        <td class="font-weight-500">handmade, learts, eco-friendly, craft</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
                <!-- Product Summary End -->

            </div>
        </div>
    </div>
    <!-- Single Product Section End -->

    <!-- Related Products Section Start -->
    <?php if (!empty($related_products)): ?>
        <div class="section section-padding pt-0 border-top mt-5">
            <div class="container pt-5">
                <!-- Section Title Start -->
                <div class="section-title text-center mb-5">
                    <h3 class="sub-title">Related Products</h3>
                    <h2 class="title title-icon-both" style="font-family: 'Marcellus', sans-serif;">You might also like</h2>
                </div>
                <!-- Section Title End -->
                
                <div class="row row-cols-xl-4 row-cols-lg-3 row-cols-md-2 row-cols-1">
                    <?php foreach ($related_products as $rel): ?>
                        <div class="col mb-4">
                            <div class="product">
                                <div class="product-thumb">
                                    <a href="product-detail.php?id=<?= $rel['id'] ?>" class="image">
                                        <img src="<?= htmlspecialchars($rel['image_url']) ?>" alt="<?= htmlspecialchars($rel['name']) ?>">
                                    </a>
                                </div>
                                <div class="product-info">
                                    <span class="category" style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #999;">
                                        <?= htmlspecialchars($rel['category_name']) ?>
                                    </span>
                                    <h6 class="title" style="margin-top: 5px;">
                                        <a href="product-detail.php?id=<?= $rel['id'] ?>"><?= htmlspecialchars($rel['name']) ?></a>
                                    </h6>
                                    <span class="price">
                                        $<?= number_format($rel['price'], 2) ?>
                                    </span>
                                    <div class="product-buttons">
                                        <a href="product-detail.php?id=<?= $rel['id'] ?>" class="product-button hintT-top" data-hint="View Details"><i class="fas fa-search"></i></a>
                                        <?php if ($rel['stock'] > 0): ?>
                                            <a href="api/cart_actions.php?action=add&id=<?= $rel['id'] ?>&quantity=1" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- Related Products Section End -->

    <script>
        // Quantity change controller
        function changeQty(amount) {
            const input = document.getElementById('purchase-qty');
            let current = parseInt(input.value);
            const maxVal = parseInt(input.getAttribute('max'));
            
            current += amount;
            if (current < 1) current = 1;
            if (current > maxVal) current = maxVal;
            
            input.value = current;
        }
    </script>

<?php require_once 'includes/footer.php'; ?>
