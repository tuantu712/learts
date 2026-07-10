<?php
// index.php - Client Homepage
require_once 'includes/header.php';

// Fetch 10 featured products
try {
    $stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC LIMIT 10");
    $featured_products = $stmt->fetchAll();
} catch (Exception $e) {
    $featured_products = [];
}
?>

    <!-- Slider main container Start -->
    <div class="home1-slider swiper-container">
        <div class="swiper-wrapper">
            <!-- Slide 1 -->
            <div class="home1-slide-item swiper-slide" data-swiper-autoplay="5000" style="background-color: #f4ece1;">
                <div class="home1-slide1-content">
                    <span class="bg"></span>
                    <span class="slide-border"></span>
                    <span class="icon"><img src="assets/images/slider/home1/slide-1-1.webp" alt="Slide Icon"></span>
                    <h2 class="title">Handicraft Shop</h2>
                    <h3 class="sub-title">Just for you</h3>
                    <div class="link"><a href="shop.php">shop now</a></div>
                </div>
            </div>
            <!-- Slide 2 -->
            <div class="home1-slide-item swiper-slide" data-swiper-autoplay="5000" style="background-color: #eae1d8;">
                <div class="home1-slide2-content">
                    <span class="bg"></span>
                    <span class="slide-border"></span>
                    <span class="icon">
                        <img src="assets/images/slider/home1/slide-2-2.webp" alt="Slide Icon">
                        <img src="assets/images/slider/home1/slide-2-3.webp" alt="Slide Icon">
                    </span>
                    <h2 class="title">Newly arrived</h2>
                    <h3 class="sub-title">Sale up to <br>10%</h3>
                    <div class="link"><a href="shop.php">shop now</a></div>
                </div>
            </div>
            <!-- Slide 3 -->
            <div class="home1-slide-item swiper-slide" data-swiper-autoplay="5000" style="background-color: #e5dfd9;">
                <div class="home1-slide3-content">
                    <h2 class="title">Affectious gifts</h2>
                    <h3 class="sub-title">
                        <img class="left-icon " src="assets/images/slider/home1/slide-2-2.webp" alt="Slide Icon">
                        For friends & family
                        <img class="right-icon " src="assets/images/slider/home1/slide-2-3.webp" alt="Slide Icon">
                    </h3>
                    <div class="link"><a href="shop.php">shop now</a></div>
                </div>
            </div>
        </div>
        <div class="home1-slider-prev swiper-button-prev"><i class="ti-angle-left"></i></div>
        <div class="home1-slider-next swiper-button-next"><i class="ti-angle-right"></i></div>
    </div>
    <!-- Slider main container End -->

    <!-- Sale Banner Section Start -->
    <div class="section section-padding">
        <div class="container">
            <!-- Section Title Start -->
            <div class="section-title text-center">
                <h3 class="sub-title">Just for you</h3>
                <h2 class="title title-icon-both">Making & crafting</h2>
            </div>
            <!-- Section Title End -->

            <div class="row learts-mb-n40">
                <div class="col-lg-5 col-md-6 col-12 me-auto learts-mb-40">
                    <div class="sale-banner1" style="background-color: #f7ede2; padding: 40px; border-radius: 4px;">
                        <div class="inner">
                            <img src="assets/images/banner/sale/sale-banner1-1.1.webp" alt="Sale Banner Icon">
                            <span class="title">Spring sale</span>
                            <h2 class="sale-percent">
                                <span class="number">40</span> % <br> off
                            </h2>
                            <a href="shop.php" class="link">shop now</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-md-6 col-12 learts-mb-40">
                    <div class="sale-banner2">
                        <div class="inner">
                            <div class="image"><img src="assets/images/banner/sale/sale-banner2-1.webp" alt="Spring Sale"></div>
                            <div class="content row justify-content-between mb-n3">
                                <div class="col-auto mb-3">
                                    <h2 class="sale-percent">10% off</h2>
                                    <span class="text">YOUR NEXT PURCHASE</span>
                                </div>
                                <div class="col-auto mb-3">
                                    <a class="btn btn-hover-dark" href="shop.php">SHOP NOW</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Sale Banner Section End -->

    <!-- Category Banner Section Start -->
    <div class="section section-fluid section-padding pt-0">
        <div class="container">
            <div class="category-banner1-carousel">
                <div class="col">
                    <div class="category-banner1">
                        <div class="inner">
                            <a href="shop.php?category=1" class="image"><img src="assets/images/banner/category/banner-s1-1.webp" alt="Gift ideas"></a>
                            <div class="content">
                                <h3 class="title">
                                    <a href="shop.php?category=1">Gift ideas</a>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="category-banner1">
                        <div class="inner">
                            <a href="shop.php?category=2" class="image"><img src="assets/images/banner/category/banner-s1-2.webp" alt="Home Decor"></a>
                            <div class="content">
                                <h3 class="title">
                                    <a href="shop.php?category=2">Home Decor</a>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="category-banner1">
                        <div class="inner">
                            <a href="shop.php?category=3" class="image"><img src="assets/images/banner/category/banner-s1-3.webp" alt="Kids & Babies"></a>
                            <div class="content">
                                <h3 class="title">
                                    <a href="shop.php?category=3">Kids & Babies</a>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="category-banner1">
                        <div class="inner">
                            <a href="shop.php?category=4" class="image"><img src="assets/images/banner/category/banner-s1-4.webp" alt="Kitchen"></a>
                            <div class="content">
                                <h3 class="title">
                                    <a href="shop.php?category=4">Kitchen</a>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="category-banner1">
                        <div class="inner">
                            <a href="shop.php?category=5" class="image"><img src="assets/images/banner/category/banner-s1-5.webp" alt="Knitting & Sewing"></a>
                            <div class="content">
                                <h3 class="title">
                                    <a href="shop.php?category=5">Knitting & Sewing</a>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Category Banner Section End -->

    <!-- Product Section Start -->
    <div class="section section-fluid section-padding pt-0">
        <div class="container">
            <!-- Section Title Start -->
            <div class="section-title text-center">
                <h3 class="sub-title">Shop now</h3>
                <h2 class="title title-icon-both">Shop our best-sellers</h2>
            </div>
            <!-- Section Title End -->

            <!-- Products Start -->
            <div class="products row row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1">
                <?php if (empty($featured_products)): ?>
                    <div class="col-12 text-center">
                        <p>No products found.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($featured_products as $product): ?>
                        <div class="col">
                            <div class="product">
                                <div class="product-thumb">
                                    <?php if ($product['id'] == 1): ?>
                                        <span class="product-badges">
                                            <span class="onsale">-10%</span>
                                        </span>
                                    <?php elseif ($product['id'] == 3): ?>
                                        <span class="product-badges">
                                            <span class="hot">hot</span>
                                        </span>
                                    <?php endif; ?>
                                    <a href="product-detail.php?id=<?= $product['id'] ?>" class="image">
                                        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                        <?php 
                                            // Check if hover image exists
                                            $hover_img = str_replace('.webp', '-hover.webp', $product['image_url']);
                                            if (file_exists(__DIR__ . '/' . $hover_img)): 
                                        ?>
                                            <img class="image-hover" src="<?= htmlspecialchars($hover_img) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                        <?php endif; ?>
                                    </a>
                                    <a href="wishlist.html" class="add-to-wishlist hintT-left" data-hint="Add to wishlist"><i class="far fa-heart"></i></a>
                                </div>
                                <div class="product-info">
                                    <h6 class="title"><a href="product-detail.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a></h6>
                                    <span class="price">
                                        $<?= number_format($product['price'], 2) ?>
                                    </span>
                                    <div class="product-buttons">
                                        <a href="product-detail.php?id=<?= $product['id'] ?>" class="product-button hintT-top" data-hint="Quick View"><i class="fas fa-search"></i></a>
                                        <a href="api/cart_actions.php?action=add&id=<?= $product['id'] ?>&quantity=1" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <!-- Products End -->
        </div>
    </div>
    <!-- Product Section End -->

<?php require_once 'includes/footer.php'; ?>
