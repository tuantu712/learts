<?php
// admin/dashboard.php - Admin Control Panel Dashboard
require_once __DIR__ . '/includes/header.php';

// Calculate summary stats
try {
    // 1. Total revenue (from Completed orders)
    $rev_stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'Completed'");
    $total_revenue = (float)$rev_stmt->fetchColumn();
    
    // 2. Total orders count
    $orders_stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $total_orders = (int)$orders_stmt->fetchColumn();
    
    // 3. Total products count
    $prod_stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $total_products = (int)$prod_stmt->fetchColumn();
    
    // 4. Out of stock products count
    $oos_stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 0");
    $out_of_stock = (int)$oos_stmt->fetchColumn();
    
    // 5. Fetch 5 most recent orders
    $recent_orders_stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
    $recent_orders = $recent_orders_stmt->fetchAll();
    
    // 6. Fetch products with low stock (<= 3 units)
    $low_stock_stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.stock <= 3 ORDER BY p.stock ASC LIMIT 5");
    $low_stock_items = $low_stock_stmt->fetchAll();
    
} catch (Exception $e) {
    $total_revenue = 0.00;
    $total_orders = 0;
    $total_products = 0;
    $out_of_stock = 0;
    $recent_orders = [];
    $low_stock_items = [];
}
?>

<!-- Statistics Blocks -->
<div class="row mb-4">
    <!-- Total Revenue -->
    <div class="col-xl-3 col-md-6 col-12 mb-3">
        <div class="card border-0 shadow-sm p-4 bg-white" style="border-radius: 6px; border-left: 4px solid #28a745 !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase mb-2" style="font-size: 12px; letter-spacing: 1px;">Total Revenue</h6>
                    <h3 class="mb-0" style="font-weight: bold; color: #333;">$<?= number_format($total_revenue, 2) ?></h3>
                </div>
                <div class="bg-success-light p-3 rounded" style="background-color: #d4edda; color: #28a745;">
                    <i class="fas fa-dollar-sign fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Orders -->
    <div class="col-xl-3 col-md-6 col-12 mb-3">
        <div class="card border-0 shadow-sm p-4 bg-white" style="border-radius: 6px; border-left: 4px solid #007bff !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase mb-2" style="font-size: 12px; letter-spacing: 1px;">Total Orders</h6>
                    <h3 class="mb-0" style="font-weight: bold; color: #333;"><?= $total_orders ?></h3>
                </div>
                <div class="bg-primary-light p-3 rounded" style="background-color: #cce5ff; color: #007bff;">
                    <i class="fas fa-shopping-bag fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Products -->
    <div class="col-xl-3 col-md-6 col-12 mb-3">
        <div class="card border-0 shadow-sm p-4 bg-white" style="border-radius: 6px; border-left: 4px solid #17a2b8 !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase mb-2" style="font-size: 12px; letter-spacing: 1px;">Active Products</h6>
                    <h3 class="mb-0" style="font-weight: bold; color: #333;"><?= $total_products ?></h3>
                </div>
                <div class="bg-info-light p-3 rounded" style="background-color: #d1ecf1; color: #17a2b8;">
                    <i class="fas fa-boxes fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Out of Stock -->
    <div class="col-xl-3 col-md-6 col-12 mb-3">
        <div class="card border-0 shadow-sm p-4 bg-white" style="border-radius: 6px; border-left: 4px solid #dc3545 !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase mb-2" style="font-size: 12px; letter-spacing: 1px;">Out of Stock</h6>
                    <h3 class="mb-0" style="font-weight: bold; color: #333;"><?= $out_of_stock ?></h3>
                </div>
                <div class="bg-danger-light p-3 rounded" style="background-color: #f8d7da; color: #dc3545;">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders Table -->
    <div class="col-lg-8 col-12 mb-4">
        <div class="main-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0" style="font-weight: 600; color: #333;">Recent Orders</h5>
                <a href="orders.php" class="btn btn-sm btn-outline-primary">View All Orders</a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_orders)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td><strong>#<?= sprintf('%06d', $order['id']) ?></strong></td>
                                    <td>
                                        <div><?= htmlspecialchars($order['customer_name']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($order['customer_email']) ?></div>
                                    </td>
                                    <td><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></td>
                                    <td><strong>$<?= number_format($order['total_amount'], 2) ?></strong></td>
                                    <td>
                                        <?php 
                                        $badge_class = 'badge-pending';
                                        if ($order['status'] == 'Processing') $badge_class = 'badge-processing';
                                        elseif ($order['status'] == 'Completed') $badge_class = 'badge-completed';
                                        elseif ($order['status'] == 'Cancelled') $badge_class = 'badge-cancelled';
                                        ?>
                                        <span class="badge <?= $badge_class ?> rounded-pill px-3 py-2"><?= $order['status'] ?></span>
                                    </td>
                                    <td>
                                        <a href="orders.php" class="btn btn-sm btn-light">Manage</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Alert Sidebar -->
    <div class="col-lg-4 col-12 mb-4">
        <div class="main-card">
            <h5 class="mb-3" style="font-weight: 600; color: #333;">Low Stock Alert</h5>
            
            <?php if (empty($low_stock_items)): ?>
                <div class="alert alert-success py-3 mb-0 text-center">
                    <i class="fas fa-check-circle me-1"></i> All products have sufficient stock.
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($low_stock_items as $item): ?>
                        <div class="list-group-item px-0 py-3 border-0 border-bottom d-flex align-items-center">
                            <img src="../<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 45px; height: 45px; object-fit: cover; border-radius: 4px;" class="me-3">
                            <div class="flex-grow-1">
                                <div class="font-weight-500 text-dark small text-truncate" style="max-width: 180px;" title="<?= htmlspecialchars($item['name']) ?>">
                                    <?= htmlspecialchars($item['name']) ?>
                                </div>
                                <div class="text-muted small"><?= htmlspecialchars($item['category_name']) ?></div>
                            </div>
                            <div>
                                <?php if ($item['stock'] <= 0): ?>
                                    <span class="badge bg-danger">Sold Out</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><?= $item['stock'] ?> left</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="products.php" class="btn btn-sm btn-outline-secondary w-100">Restock Products</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
