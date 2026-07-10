<?php
// admin/orders.php - Order Management Dashboard
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $new_status = isset($_POST['status']) ? trim($_POST['status']) : '';
    
    $allowed_statuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];
    
    if ($order_id > 0 && in_array($new_status, $allowed_statuses)) {
        try {
            // Update order status in database
            $update_stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
            $update_stmt->execute([
                'status' => $new_status,
                'id' => $order_id
            ]);
            
            $_SESSION['admin_flash'] = [
                'type' => 'success',
                'text' => "Order #{$order_id} status updated to '{$new_status}' successfully."
            ];
        } catch (Exception $e) {
            $_SESSION['admin_flash'] = [
                'type' => 'danger',
                'text' => "Failed to update status: " . $e->getMessage()
            ];
        }
    }
    
    header('Location: orders.php');
    exit;
}

// Fetch all orders
try {
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
    $orders = $stmt->fetchAll();
} catch (Exception $e) {
    $orders = [];
}

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0" style="font-weight: 600; color: #333;">Order Management</h4>
</div>

<div class="main-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-muted small text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">
                    <th>Order Details</th>
                    <th>Customer Contact</th>
                    <th>Delivery Address</th>
                    <th>Order Items (Items &amp; Qty)</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No orders placed yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <!-- Order Info -->
                            <td>
                                <div class="font-weight-bold text-dark" style="font-size: 15px;">#<?= sprintf('%06d', $order['id']) ?></div>
                                <div class="text-muted small"><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></div>
                            </td>
                            
                            <!-- Customer Info -->
                            <td>
                                <div><strong><?= htmlspecialchars($order['customer_name']) ?></strong></div>
                                <div class="text-muted small"><i class="fas fa-phone me-1"></i> <?= htmlspecialchars($order['customer_phone']) ?></div>
                                <div class="text-muted small"><i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($order['customer_email']) ?></div>
                            </td>
                            
                            <!-- Address -->
                            <td>
                                <div class="small" style="max-width: 200px; line-height: 1.4;"><?= nl2br(htmlspecialchars($order['customer_address'])) ?></div>
                            </td>
                            
                            <!-- Order Items (Subquery for items details) -->
                            <td>
                                <div class="p-2 bg-light rounded" style="font-size: 13px; max-width: 280px; border: 1px solid #eef0f3;">
                                    <?php
                                    try {
                                        $items_stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
                                        $items_stmt->execute(['order_id' => $order['id']]);
                                        $items = $items_stmt->fetchAll();
                                        
                                        foreach ($items as $item) {
                                            echo '<div class="mb-1 border-bottom pb-1 last-border-none">';
                                            echo htmlspecialchars($item['product_name']) . ' <strong class="text-dark">&times; ' . $item['quantity'] . '</strong>';
                                            echo '<span class="float-end text-muted">$' . number_format($item['price'] * $item['quantity'], 2) . '</span>';
                                            echo '</div>';
                                        }
                                    } catch (Exception $e) {
                                        echo '<span class="text-danger">Failed to load items.</span>';
                                    }
                                    ?>
                                </div>
                            </td>
                            
                            <!-- Total Amount -->
                            <td class="font-weight-bold" style="font-size: 16px;">
                                $<?= number_format($order['total_amount'], 2) ?>
                            </td>
                            
                            <!-- Status Dropdown (Submits instantly on change!) -->
                            <td>
                                <form action="orders.php" method="POST" class="d-inline-block">
                                    <input type="hidden" name="update_status" value="1">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    
                                    <?php 
                                    $border_color = '#ffeeba'; // Default Pending
                                    if ($order['status'] == 'Processing') $border_color = '#b8daff';
                                    elseif ($order['status'] == 'Completed') $border_color = '#c3e6cb';
                                    elseif ($order['status'] == 'Cancelled') $border_color = '#f5c6cb';
                                    ?>
                                    
                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="border: 2px solid <?= $border_color ?>; font-weight: bold; width: 135px; border-radius: 4px;">
                                        <option value="Pending" <?= ($order['status'] == 'Pending') ? 'selected' : '' ?> class="text-warning font-weight-bold">Pending</option>
                                        <option value="Processing" <?= ($order['status'] == 'Processing') ? 'selected' : '' ?> class="text-primary font-weight-bold">Processing</option>
                                        <option value="Completed" <?= ($order['status'] == 'Completed') ? 'selected' : '' ?> class="text-success font-weight-bold">Completed</option>
                                        <option value="Cancelled" <?= ($order['status'] == 'Cancelled') ? 'selected' : '' ?> class="text-danger font-weight-bold">Cancelled</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    /* Styling to remove border on last item inside the cell list */
    .last-border-none:last-child {
        border-bottom: 0 !important;
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
    }
</style>

<?php require_once 'includes/footer.php'; ?>
