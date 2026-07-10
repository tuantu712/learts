<?php
// admin/includes/header.php - Admin Header & Navigation

// Include database connection (starts session via DB handler)
require_once __DIR__ . '/../../config/database.php';

// Enforce authentication
require_once __DIR__ . '/../../includes/auth_check.php';

// Get current page name for active states
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learts Admin - Control Panel</title>
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/vendor/fontawesome.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #d5b85a; /* Gold Accent */
            --dark-bg: #1e2229;
            --darker-bg: #12151c;
            --text-color: #e3e4e6;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f5f6f8;
            margin: 0;
            padding: 0;
        }
        #admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar Styles */
        #admin-sidebar {
            width: var(--sidebar-width);
            background-color: var(--dark-bg);
            color: var(--text-color);
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        #admin-sidebar .brand {
            padding: 20px;
            background-color: var(--darker-bg);
            text-align: center;
            border-bottom: 1px solid #2d323e;
        }
        #admin-sidebar .brand h4 {
            margin: 0;
            color: #fff;
            font-family: Georgia, serif;
            font-style: italic;
            letter-spacing: 1px;
        }
        #admin-sidebar .nav-menu {
            list-style: none;
            padding: 20px 0;
            margin: 0;
            flex-grow: 1;
        }
        #admin-sidebar .nav-menu li a {
            display: block;
            padding: 12px 25px;
            color: #a0a5b5;
            text-decoration: none;
            font-size: 15px;
            transition: all 0.2s ease;
        }
        #admin-sidebar .nav-menu li a:hover,
        #admin-sidebar .nav-menu li.active a {
            color: #fff;
            background-color: #292f3b;
            border-left: 4px solid var(--primary-color);
            padding-left: 21px;
        }
        #admin-sidebar .nav-menu li a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }
        #admin-sidebar .user-info {
            padding: 15px 25px;
            background-color: var(--darker-bg);
            font-size: 14px;
            border-top: 1px solid #2d323e;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        #admin-sidebar .user-info .username {
            color: #fff;
            font-weight: bold;
        }
        /* Content Area Styles */
        #admin-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        #admin-navbar {
            background-color: #fff;
            padding: 15px 30px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #eef0f3;
        }
        #admin-navbar .page-title {
            margin: 0;
            font-size: 20px;
            color: #333;
            font-weight: 600;
        }
        .main-card {
            background-color: #fff;
            border: 1px solid #eef0f3;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            padding: 25px;
            margin-bottom: 30px;
        }
        /* Form Styles */
        .form-label {
            font-weight: 500;
            color: #495057;
            font-size: 14px;
        }
        /* Status Badge Colors */
        .badge-pending { background-color: #ffeeba; color: #856404; }
        .badge-processing { background-color: #b8daff; color: #004085; }
        .badge-completed { background-color: #c3e6cb; color: #155724; }
        .badge-cancelled { background-color: #f5c6cb; color: #721c24; }
    </style>
</head>
<body>

<div id="admin-wrapper">

    <!-- Sidebar Start -->
    <div id="admin-sidebar">
        <div class="brand">
            <h4>Learts Admin</h4>
        </div>
        <ul class="nav-menu">
            <li class="<?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
                <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
            </li>
            <li class="<?= ($current_page == 'products.php' || $current_page == 'product_add.php' || $current_page == 'product_edit.php') ? 'active' : '' ?>">
                <a href="products.php"><i class="fas fa-boxes"></i> Products</a>
            </li>
            <li class="<?= ($current_page == 'orders.php') ? 'active' : '' ?>">
                <a href="orders.php"><i class="fas fa-shopping-bag"></i> Orders</a>
            </li>
            <li>
                <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Store</a>
            </li>
        </ul>
        <div class="user-info">
            <div>
                <i class="far fa-user-circle me-1"></i>
                <span class="username"><?= htmlspecialchars($_SESSION['admin_username']) ?></span>
            </div>
            <a href="logout.php" class="text-danger text-decoration-none" title="Logout"><i class="fas fa-power-off me-1"></i> Đăng xuất</a>
        </div>
    </div>
    <!-- Sidebar End -->

    <!-- Content Wrapper Start -->
    <div id="admin-content">
        <!-- Top Navbar -->
        <div id="admin-navbar">
            <h2 class="page-title">
                <?php
                if ($current_page == 'dashboard.php') echo 'Dashboard Overview';
                elseif ($current_page == 'products.php') echo 'Product Catalog Management';
                elseif ($current_page == 'product_add.php') echo 'Add New Handmade Product';
                elseif ($current_page == 'product_edit.php') echo 'Edit Product Details';
                elseif ($current_page == 'orders.php') echo 'Manage Shop Orders';
                else echo 'Admin Panel';
                ?>
            </h2>
            <div>
                <span class="text-muted small">Server Time: <?= date('Y-m-d H:i') ?></span>
            </div>
        </div>
        
        <!-- Inner container -->
        <div class="container-fluid p-4">
            
            <!-- Global Flash Alerts -->
            <?php if (isset($_SESSION['admin_flash'])): ?>
                <div class="alert alert-<?= htmlspecialchars($_SESSION['admin_flash']['type']) ?> alert-dismissible fade show mb-4" role="alert">
                    <?= $_SESSION['admin_flash']['text'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['admin_flash']); ?>
            <?php endif; ?>
