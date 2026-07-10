<?php
// admin/product_add.php - Add New Product with Image Upload support
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Fetch categories for dropdown
try {
    $cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $cat_stmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

$errors = [];
$name = '';
$description = '';
$price = '';
$stock = '';
$category_id = '';
$image_url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);
    $category_id = (int)$_POST['category_id'];
    $image_url = trim($_POST['image_url']);
    
    // Server-side validation
    if (empty($name)) $errors[] = "Product Name is required.";
    if (empty($description)) $errors[] = "Description is required.";
    if (empty($price) || !is_numeric($price) || $price < 0) $errors[] = "Price must be a valid non-negative number.";
    if (empty($stock) && $stock !== '0' || !is_numeric($stock) || $stock < 0) $errors[] = "Stock must be a valid non-negative integer.";
    if ($category_id <= 0) $errors[] = "Please select a valid Category.";
    
    // Handle file upload
    $file_uploaded = false;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['product_image']['name'];
        $file_size = $_FILES['product_image']['size'];
        $file_tmp  = $_FILES['product_image']['tmp_name'];
        $file_type = $_FILES['product_image']['type'];
        
        $temp = explode('.', $file_name);
        $file_ext = strtolower(end($temp));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_ext, $allowed_exts)) {
            // Generate safe filename
            $new_filename = uniqid('prod_', true) . '.' . $file_ext;
            $upload_dir = __DIR__ . '/../assets/images/product/s328/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $dest_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $dest_path)) {
                $image_url = 'assets/images/product/s328/' . $new_filename;
                $file_uploaded = true;
            } else {
                $errors[] = "Failed to move uploaded file. Check permissions on assets directory.";
            }
        } else {
            $errors[] = "Invalid file extension. Allowed types: jpg, jpeg, png, gif, webp.";
        }
    }
    
    // If no file uploaded and no image path text specified
    if (!$file_uploaded && empty($image_url)) {
        $errors[] = "Product Image is required (either upload a file or enter an image path).";
    }
    
    // Insert if no errors
    if (empty($errors)) {
        try {
            $insert_stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category_id, image_url) VALUES (:name, :description, :price, :stock, :category_id, :image_url)");
            $insert_stmt->execute([
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'stock' => (int)$stock,
                'category_id' => $category_id,
                'image_url' => $image_url
            ]);
            
            $_SESSION['admin_flash'] = [
                'type' => 'success',
                'text' => "Product '{$name}' created successfully!"
            ];
            header('Location: products.php');
            exit;
        } catch (Exception $e) {
            $errors[] = "Failed to save product: " . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="mb-4">
    <a href="products.php" class="btn btn-sm btn-outline-secondary">&larr; Back to Catalog</a>
</div>

<div class="main-card col-lg-8 col-12 mx-auto">
    <h5 class="mb-4" style="font-weight: 600; color: #333;">Add New Product Details</h5>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger py-2 mb-4" style="font-size: 14px;">
            <ul class="mb-0 pl-3">
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="product_add.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="add_product" value="1">
        
        <div class="row">
            <!-- Name -->
            <div class="col-12 mb-3">
                <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
            </div>
            
            <!-- Category -->
            <div class="col-md-6 col-12 mb-3">
                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($category_id == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Price -->
            <div class="col-md-3 col-6 mb-3">
                <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="<?= htmlspecialchars($price) ?>" required>
            </div>
            
            <!-- Stock -->
            <div class="col-md-3 col-6 mb-3">
                <label for="stock" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                <input type="number" min="0" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars($stock) ?>" required>
            </div>
            
            <!-- Image File Upload -->
            <div class="col-12 mb-3">
                <label for="product_image" class="form-label">Upload Product Image (Allowed: jpg, png, webp)</label>
                <input type="file" class="form-control" id="product_image" name="product_image" accept="image/*">
                <div class="form-text text-muted">Upload a high-quality photo of your handmade item.</div>
            </div>
            
            <!-- Image Path alternative -->
            <div class="col-12 mb-4">
                <label for="image_url" class="form-label">Or specify existing image path (Alternative)</label>
                <input type="text" class="form-control" id="image_url" name="image_url" placeholder="e.g. assets/images/product/s328/product-1.webp" value="<?= htmlspecialchars($image_url) ?>">
                <div class="form-text text-muted">Use this option if you want to reference a template image that is already on the server.</div>
            </div>
            
            <!-- Description -->
            <div class="col-12 mb-4">
                <label for="description" class="form-label">Product Description <span class="text-danger">*</span></label>
                <textarea class="form-control" id="description" name="description" rows="6" required><?= htmlspecialchars($description) ?></textarea>
            </div>
            
            <!-- Submit -->
            <div class="col-12">
                <button type="submit" class="btn btn-dark w-100 py-2" style="background-color: var(--dark-bg);">
                    Create Product
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
