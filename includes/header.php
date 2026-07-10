<?php
// includes/header.php - Dynamic Client Header

// Include database connection (starts session via DB handler)
require_once __DIR__ . '/../config/database.php';

// Fetch categories for navbar
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $nav_categories = $stmt->fetchAll();
} catch (Exception $e) {
    $nav_categories = [];
}

// Initialize and calculate Cart counts
$cart_count = 0;
$cart_subtotal = 0;
$cart_items = [];
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $item) {
        $cart_count += $item['quantity'];
        $cart_subtotal += $item['price'] * $item['quantity'];
        $cart_items[$id] = $item;
    }
}
?>
<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Learts – Handmade Shop eCommerce</title>
    <meta name="description" content="Learts - Handmade Store - Made for you - Making & creating">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.webp">

    <!-- CSS
	============================================ -->
    <!-- Vendor CSS (Bootstrap & Icon Font) -->
    <link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/vendor/fontawesome.min.css">
    <link rel="stylesheet" href="assets/css/vendor/themify-icons.css">
    <link rel="stylesheet" href="assets/css/vendor/customFonts.css">

    <!-- Plugins CSS (All Plugins Files) -->
    <link rel="stylesheet" href="assets/css/plugins/select2.min.css">
    <link rel="stylesheet" href="assets/css/plugins/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/css/plugins/swiper.min.css">
    <link rel="stylesheet" href="assets/css/plugins/nice-select.css">
    <link rel="stylesheet" href="assets/css/plugins/ion.rangeSlider.min.css">
    <link rel="stylesheet" href="assets/css/plugins/photoswipe.css">
    <link rel="stylesheet" href="assets/css/plugins/photoswipe-default-skin.css">
    <link rel="stylesheet" href="assets/css/plugins/magnific-popup.css">
    <link rel="stylesheet" href="assets/css/plugins/slick.css">

    <!-- Main Style CSS -->
    <link rel="stylesheet" href="assets/css/style.min.css">
    <style>
        .home1-slide-item.swiper-slide {
            background-color: #f4ece1;
            background-position: center center;
            background-size: cover;
        }
        .header-logo img {
            max-height: 40px;
        }
        .minicart-product-list {
            padding: 0;
            margin: 0;
            list-style: none;
        }
        .minicart-product-list li {
            display: flex;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .minicart-product-list li .image {
            width: 70px;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .minicart-product-list li .image img {
            width: 100%;
        }
        .minicart-product-list li .content {
            flex-grow: 1;
        }
        .minicart-product-list li .content .title {
            display: block;
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .minicart-product-list li .content .quantity-price {
            font-size: 13px;
            color: #999;
        }
        .minicart-product-list li .content .remove {
            color: #ff5c5c;
            font-size: 18px;
            float: right;
            cursor: pointer;
        }
        /* Admin button styles in client header */
        .admin-entry-btn {
            background-color: #222;
            color: #fff;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 15px;
            font-family: 'Marcellus', sans-serif;
            text-transform: uppercase;
        }
        .admin-entry-btn:hover {
            background-color: #444;
            color: #fff;
        }
    </style>
</head>

<body>

    <!-- Topbar Section Start -->
    <div class="topbar-section section bg-primary2">
        <div class="container">
            <div class="row justify-content-between align-items-center">
                <div class="col-md-auto col-12">
                    <p class="text-white text-center text-md-left my-2">Free shipping for orders over $59 !</p>
                </div>
                <div class="col-auto d-none d-md-block">
                    <div class="topbar-menu">
                        <ul>
                            <li><a href="#" class="text-white"><i class="fa fa-map-marker-alt"></i>Store Location</a></li>
                            <li><a href="#" class="text-white"><i class="fa fa-truck"></i>Order Status</a></li>
                            <?php if (isset($_SESSION['admin_id'])): ?>
                                <li><a href="admin/dashboard.php" class="text-white"><i class="fa fa-tachometer-alt"></i>Admin Dashboard</a></li>
                            <?php else: ?>
                                <li><a href="admin/login.php" class="text-white"><i class="fa fa-lock"></i>Admin Login</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar Section End -->

    <!-- Header Section Start -->
    <div class="header-section section bg-white d-none d-xl-block">
        <div class="container">
            <div class="row row-cols-lg-3 align-items-center">

                <!-- Header Language & Currency Start -->
                <div class="col">
                    <ul class="header-lan-curr">
                        <li><a href="#">English</a>
                            <ul class="curr-lan-sub-menu">
                                <li><a href="#">Français</a></li>
                                <li><a href="#">Deutsch</a></li>
                            </ul>
                        </li>
                        <li><a href="#">USD</a>
                            <ul class="curr-lan-sub-menu">
                                <li><a href="#">EUR</a></li>
                                <li><a href="#">GBP</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <!-- Header Language & Currency End -->

                <!-- Header Logo Start -->
                <div class="col">
                    <div class="header-logo justify-content-center">
                        <a href="index.php"><img src="assets/images/logo/logo.webp" alt="Learts Logo"></a>
                    </div>
                </div>
                <!-- Header Logo End -->

                <!-- Header Tools Start -->
                <div class="col">
                    <div class="header-tools justify-content-end">
                        <div class="header-login">
                            <?php if (isset($_SESSION['admin_id'])): ?>
                                <a href="admin/dashboard.php" title="Admin Area"><i class="far fa-user"></i> Admin</a>
                            <?php else: ?>
                                <a href="admin/login.php" title="Admin Login"><i class="far fa-user"></i></a>
                            <?php endif; ?>
                        </div>
                        <div class="header-search">
                            <a href="#offcanvas-search" class="offcanvas-toggle"><i class="fas fa-search"></i></a>
                        </div>
                        <div class="header-wishlist">
                            <a href="#offcanvas-wishlist" class="offcanvas-toggle"><span class="wishlist-count">0</span><i class="far fa-heart"></i></a>
                        </div>
                        <div class="header-cart">
                            <a href="#offcanvas-cart" class="offcanvas-toggle"><span class="cart-count"><?= $cart_count ?></span><i class="fas fa-shopping-cart"></i></a>
                        </div>
                    </div>
                </div>
                <!-- Header Tools End -->

            </div>
        </div>

        <!-- Site Menu Section Start -->
        <div class="site-menu-section section">
            <div class="container">
                <nav class="site-main-menu justify-content-center">
                    <ul>
                        <li><a href="index.php"><span class="menu-text">Home</span></a></li>
                        <li><a href="shop.php"><span class="menu-text">Shop</span></a>
                            <ul class="sub-menu">
                                <li><a href="shop.php">All Products</a></li>
                                <?php foreach ($nav_categories as $cat): ?>
                                    <li><a href="shop.php?category=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <li><a href="cart.php"><span class="menu-text">Cart</span></a></li>
                        <li><a href="checkout.php"><span class="menu-text">Checkout</span></a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <!-- Site Menu Section End -->
    </div>
    <!-- Header Section End -->

    <!-- Header Sticky Section Start -->
    <div class="sticky-header header-menu-center section bg-white d-none d-xl-block">
        <div class="container">
            <div class="row align-items-center">
                <!-- Header Logo Start -->
                <div class="col">
                    <div class="header-logo">
                        <a href="index.php"><img src="assets/images/logo/logo-2.webp" alt="Learts Logo"></a>
                    </div>
                </div>
                <!-- Header Logo End -->

                <!-- Nav Menu Start -->
                <div class="col d-none d-xl-block">
                    <nav class="site-main-menu justify-content-center">
                        <ul>
                            <li><a href="index.php"><span class="menu-text">Home</span></a></li>
                            <li><a href="shop.php"><span class="menu-text">Shop</span></a>
                                <ul class="sub-menu">
                                    <li><a href="shop.php">All Products</a></li>
                                    <?php foreach ($nav_categories as $cat): ?>
                                        <li><a href="shop.php?category=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                            <li><a href="cart.php"><span class="menu-text">Cart</span></a></li>
                            <li><a href="checkout.php"><span class="menu-text">Checkout</span></a></li>
                        </ul>
                    </nav>
                </div>
                <!-- Nav Menu End -->

                <!-- Header Tools Start -->
                <div class="col-auto">
                    <div class="header-tools justify-content-end">
                        <div class="header-login">
                            <?php if (isset($_SESSION['admin_id'])): ?>
                                <a href="admin/dashboard.php" title="Admin Area"><i class="far fa-user"></i> Admin</a>
                            <?php else: ?>
                                <a href="admin/login.php" title="Admin Login"><i class="far fa-user"></i></a>
                            <?php endif; ?>
                        </div>
                        <div class="header-search d-none d-sm-block">
                            <a href="#offcanvas-search" class="offcanvas-toggle"><i class="fas fa-search"></i></a>
                        </div>
                        <div class="header-wishlist">
                            <a href="#offcanvas-wishlist" class="offcanvas-toggle"><span class="wishlist-count">0</span><i class="far fa-heart"></i></a>
                        </div>
                        <div class="header-cart">
                            <a href="#offcanvas-cart" class="offcanvas-toggle"><span class="cart-count"><?= $cart_count ?></span><i class="fas fa-shopping-cart"></i></a>
                        </div>
                    </div>
                </div>
                <!-- Header Tools End -->
            </div>
        </div>
    </div>
    <!-- Header Sticky Section End -->

    <!-- Mobile Header Section Start -->
    <div class="mobile-header bg-white section d-xl-none">
        <div class="container">
            <div class="row align-items-center">
                <!-- Header Logo Start -->
                <div class="col">
                    <div class="header-logo">
                        <a href="index.php"><img src="assets/images/logo/logo-2.webp" alt="Learts Logo"></a>
                    </div>
                </div>
                <!-- Header Logo End -->

                <!-- Header Tools Start -->
                <div class="col-auto">
                    <div class="header-tools justify-content-end">
                        <div class="header-login">
                            <?php if (isset($_SESSION['admin_id'])): ?>
                                <a href="admin/dashboard.php" title="Admin Area"><i class="far fa-user"></i> Admin</a>
                            <?php else: ?>
                                <a href="admin/login.php" title="Admin Login"><i class="far fa-user"></i></a>
                            <?php endif; ?>
                        </div>
                        <div class="header-search">
                            <a href="#offcanvas-search" class="offcanvas-toggle"><i class="fas fa-search"></i></a>
                        </div>
                        <div class="header-wishlist d-none d-sm-block">
                            <a href="#offcanvas-wishlist" class="offcanvas-toggle"><span class="wishlist-count">0</span><i class="far fa-heart"></i></a>
                        </div>
                        <div class="header-cart">
                            <a href="#offcanvas-cart" class="offcanvas-toggle"><span class="cart-count"><?= $cart_count ?></span><i class="fas fa-shopping-cart"></i></a>
                        </div>
                        <div class="mobile-menu-toggle">
                            <a href="#offcanvas-mobile-menu" class="offcanvas-toggle">
                                <svg viewBox="0 0 800 600">
                                    <path d="M300,220 C300,220 520,220 540,220 C740,220 640,540 520,420 C440,340 300,200 300,200" class="top"></path>
                                    <path d="M300,320 L540,320" class="middle"></path>
                                    <path d="M300,210 C300,210 520,210 540,210 C740,210 640,530 520,410 C440,330 300,190 300,190" class="bottom" transform="translate(480, 320) scale(1, -1) translate(-480, -318) "></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Header Tools End -->
            </div>
        </div>
    </div>
    <!-- Mobile Header Section End -->

    <!-- OffCanvas Search Start -->
    <div id="offcanvas-search" class="offcanvas offcanvas-search">
        <div class="inner">
            <div class="offcanvas-search-form">
                <button class="offcanvas-close">×</button>
                <form action="shop.php" method="GET">
                    <div class="row mb-n3">
                        <div class="col-lg-8 col-12 mb-3">
                            <input type="text" name="search" placeholder="Search Products...">
                        </div>
                        <div class="col-lg-4 col-12 mb-3">
                            <select class="search-select select2-basic" name="category">
                                <option value="0">All Categories</option>
                                <?php foreach ($nav_categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <p class="search-description text-body-light mt-2"> 
                <span># Type keyword and hit enter to search</span> 
                <span># Hit ESC or click X to close</span>
            </p>
        </div>
    </div>
    <!-- OffCanvas Search End -->

    <!-- OffCanvas Wishlist Start -->
    <div id="offcanvas-wishlist" class="offcanvas offcanvas-wishlist">
        <div class="inner">
            <div class="head">
                <span class="title">Wishlist</span>
                <button class="offcanvas-close">×</button>
            </div>
            <div class="body customScroll">
                <p class="text-center py-4">Your wishlist is currently empty.</p>
            </div>
        </div>
    </div>
    <!-- OffCanvas Wishlist End -->

    <!-- OffCanvas Cart Start (Dynamic!) -->
    <div id="offcanvas-cart" class="offcanvas offcanvas-cart">
        <div class="inner">
            <div class="head">
                <span class="title">Cart</span>
                <button class="offcanvas-close">×</button>
            </div>
            <div class="body customScroll">
                <?php if (empty($cart_items)): ?>
                    <p class="text-center py-4">Your shopping cart is empty.</p>
                <?php else: ?>
                    <ul class="minicart-product-list">
                        <?php foreach ($cart_items as $id => $item): ?>
                            <li>
                                <a href="product-detail.php?id=<?= $id ?>" class="image">
                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                </a>
                                <div class="content">
                                    <a href="product-detail.php?id=<?= $id ?>" class="title"><?= htmlspecialchars($item['name']) ?></a>
                                    <span class="quantity-price"><?= $item['quantity'] ?> x <span class="amount">$<?= number_format($item['price'], 2) ?></span></span>
                                    <a href="api/cart_actions.php?action=remove&id=<?= $id ?>" class="remove" onclick="return removeMiniCartItem(event, <?= $id ?>)">×</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="foot">
                <div class="sub-total">
                    <strong>Subtotal :</strong>
                    <span class="amount">$<?= number_format($cart_subtotal, 2) ?></span>
                </div>
                <div class="buttons">
                    <a href="cart.php" class="btn btn-dark btn-hover-primary">view cart</a>
                    <a href="checkout.php" class="btn btn-outline-dark">checkout</a>
                </div>
                <p class="minicart-message">Free Shipping on All Orders Over $59!</p>
            </div>
        </div>
    </div>
    <!-- OffCanvas Cart End -->

    <!-- OffCanvas Mobile Menu Start -->
    <div id="offcanvas-mobile-menu" class="offcanvas offcanvas-mobile-menu">
        <div class="inner customScroll">
            <div class="offcanvas-menu-search-form">
                <form action="shop.php" method="GET">
                    <input type="text" name="search" placeholder="Search...">
                    <button><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="offcanvas-menu">
                <ul>
                    <li><a href="index.php"><span class="menu-text">Home</span></a></li>
                    <li><a href="shop.php"><span class="menu-text">Shop</span></a>
                        <ul class="sub-menu">
                            <li><a href="shop.php">All Products</a></li>
                            <?php foreach ($nav_categories as $cat): ?>
                                <li><a href="shop.php?category=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li><a href="cart.php"><span class="menu-text">Cart</span></a></li>
                    <li><a href="checkout.php"><span class="menu-text">Checkout</span></a></li>
                    <?php if (isset($_SESSION['admin_id'])): ?>
                        <li><a href="admin/dashboard.php"><span class="menu-text">Admin Dashboard</span></a></li>
                    <?php else: ?>
                        <li><a href="admin/login.php"><span class="menu-text">Admin Login</span></a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="offcanvas-social">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>
    <!-- OffCanvas Mobile Menu End -->

    <div class="offcanvas-overlay"></div>
