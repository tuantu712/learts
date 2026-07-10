<?php
// admin/products.php - Manage Product Catalog (List and Delete)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $delete_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($delete_id > 0) {
        try {
            // Fetch product first to delete its image if it's not a sample image (optional, but let's keep it safe)
            $select_stmt = $pdo->prepare("SELECT name FROM products WHERE id = :id");
            $select_stmt->execute(['id' => $delete_id]);
            $prod_name = $select_stmt->fetchColumn();
            
            if ($prod_name) {
                $delete_stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
                $delete_stmt->execute(['id' => $delete_id]);
                
                $_SESSION['admin_flash'] = [
                    'type' => 'success',
                    'text' => "Product '{$prod_name}' has been deleted successfully."
                ];
            } else {
                $_SESSION['admin_flash'] = [
                    'type' => 'danger',
                    'text' => "Product not found or already deleted."
                ];
            }
        } catch (Exception $e) {
            $_SESSION['admin_flash'] = [
                'type' => 'danger',
                'text' => "Failed to delete product: " . $e->getMessage()
            ];
        }
    }
    
    header('Location: products.php');
    exit;
}

// Fetch all products
try {
    $stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
    $products = $stmt->fetchAll();
} catch (Exception $e) {
    $products = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0" style="font-weight: 600; color: #333;">Product List</h4>
    <a href="product_add.php" class="btn btn-dark" style="background-color: var(--dark-bg); font-size: 14px;">
        <i class="fas fa-plus me-1"></i> Add Product
    </a>
</div>

<div class="main-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-muted small text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">
                    <th>ID</th>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock Status</th>
                    <th>Date Added</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No products in catalog. Click 'Add Product' to get started.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $prod): ?>
                        <tr>
                            <td>#<?= $prod['id'] ?></td>
                            <td>
                                <img src="../<?= htmlspecialchars($prod['image_url']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #eee;">
                            </td>
                            <td>
                                <div class="font-weight-bold text-dark" style="font-size: 15px;"><?= htmlspecialchars($prod['name']) ?></div>
                                <div class="text-muted small text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($prod['description']) ?>">
                                    <?= htmlspecialchars($prod['description']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($prod['category_name']) ?></span>
                            </td>
                            <td class="font-weight-bold">$<?= number_format($prod['price'], 2) ?></td>
                            <td>
                                <?php if ($prod['stock'] <= 0): ?>
                                    <span class="text-danger font-weight-bold"><i class="fas fa-times-circle me-1"></i> Out of stock</span>
                                <?php elseif ($prod['stock'] <= 3): ?>
                                    <span class="text-warning font-weight-bold"><i class="fas fa-exclamation-circle me-1"></i> Low stock (<?= $prod['stock'] ?>)</span>
                                <?php else: ?>
                                    <span class="text-success"><i class="fas fa-check-circle me-1"></i> <?= $prod['stock'] ?> units</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= date('Y-m-d', strtotime($prod['created_at'])) ?></td>
                            <td class="text-end">
                                <a href="product_edit.php?id=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit Product">
                                    <i class="far fa-edit"></i> Edit
                                </a>
                                <a href="products.php?action=delete&id=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete Product" onclick="return confirm('Are you sure you want to delete this product? All corresponding order histories will be affected.');">
                                    <i class="far fa-trash-alt"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
