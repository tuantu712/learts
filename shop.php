<?php
// shop.php - Dynamic Shop Catalog Page
require_once 'includes/header.php';

// Pagination settings
$limit = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Filter and Sort settings
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'default';

// Construct queries dynamically
$where_clauses = [];
$params = [];

if ($category_id > 0) {
    $where_clauses[] = "p.category_id = :category_id";
    $params['category_id'] = $category_id;
}

if ($search !== '') {
    $where_clauses[] = "(p.name LIKE :search OR p.description LIKE :search)";
    $params['search'] = "%$search%";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Order by clause
switch ($sort) {
    case 'price':
        $order_sql = "ORDER BY p.price ASC";
        break;
    case 'price-desc':
        $order_sql = "ORDER BY p.price DESC";
        break;
    case 'date':
        $order_sql = "ORDER BY p.created_at DESC";
        break;
    default:
        $order_sql = "ORDER BY p.id ASC";
        break;
}

// Count total products for pagination
try {
    $count_sql = "SELECT COUNT(*) FROM products p $where_sql";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_products = $count_stmt->fetchColumn();
} catch (Exception $e) {
    $total_products = 0;
}

$total_pages = ceil($total_products / $limit);
if ($total_pages < 1) $total_pages = 1;
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $limit;

// Fetch products for current page
try {
    $products_sql = "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id $where_sql $order_sql LIMIT :limit OFFSET :offset";
    $products_stmt = $pdo->prepare($products_sql);
    
    // Bind parameters for LIMIT and OFFSET as integers (PDO standard)
    foreach ($params as $key => $val) {
        $products_stmt->bindValue($key, $val);
    }
    $products_stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $products_stmt->bindValue('offset', $offset, PDO::PARAM_INT);
    
    $products_stmt->execute();
    $products = $products_stmt->fetchAll();
} catch (Exception $e) {
    $products = [];
}

// Helper to keep query parameters in pagination links
function buildQueryUrl($changes) {
    $query = $_GET;
    foreach ($changes as $k => $v) {
        if ($v === null) {
            unset($query[$k]);
        } else {
            $query[$k] = $v;
        }
    }
    return 'shop.php?' . http_build_query($query);
}
?>

    <!-- Page Title/Header Start -->
    <div class="page-title-section section bg-light py-5" style="border-bottom: 1px solid #f0f0f0;">
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="page-title text-center">
                        <h1 class="title" style="font-family: 'Marcellus', sans-serif;">Shop</h1>
                        <ul class="breadcrumb justify-content-center bg-transparent p-0 m-0">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Products</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Title/Header End -->

    <!-- Shop Products Section Start -->
    <div class="section section-padding pt-5">
        <div class="container">
            
            <!-- Shop Toolbar Start -->
            <div class="shop-toolbar border-bottom pb-3 mb-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-md-auto col-12 mb-3 mb-md-0">
                        <div class="category-filters">
                            <a href="<?= buildQueryUrl(['category' => null, 'page' => 1]) ?>" class="btn btn-sm <?= ($category_id == 0) ? 'btn-dark' : 'btn-outline-dark' ?> mr-2 mb-1">All</a>
                            <?php foreach ($nav_categories as $cat): ?>
                                <a href="<?= buildQueryUrl(['category' => $cat['id'], 'page' => 1]) ?>" class="btn btn-sm <?= ($category_id == $cat['id']) ? 'btn-dark' : 'btn-outline-dark' ?> mr-2 mb-1">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-auto col-12">
                        <div class="d-flex align-items-center justify-content-md-end">
                            <!-- Search query indicator -->
                            <?php if ($search !== ''): ?>
                                <span class="mr-3 font-italic">Search results for: "<?= htmlspecialchars($search) ?>" <a href="<?= buildQueryUrl(['search' => null, 'page' => 1]) ?>" class="text-danger ml-1">&times;</a></span>
                            <?php endif; ?>
                            
                            <!-- Sort selector -->
                            <div class="product-sorting mr-3">
                                <select class="form-control form-control-sm" onchange="location = this.value;">
                                    <option value="<?= buildQueryUrl(['sort' => 'default']) ?>" <?= ($sort == 'default') ? 'selected' : '' ?>>Default sorting</option>
                                    <option value="<?= buildQueryUrl(['sort' => 'price']) ?>" <?= ($sort == 'price') ? 'selected' : '' ?>>Price: low to high</option>
                                    <option value="<?= buildQueryUrl(['sort' => 'price-desc']) ?>" <?= ($sort == 'price-desc') ? 'selected' : '' ?>>Price: high to low</option>
                                    <option value="<?= buildQueryUrl(['sort' => 'date']) ?>" <?= ($sort == 'date') ? 'selected' : '' ?>>Latest</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Shop Toolbar End -->

            <!-- Products Grid Start -->
            <div class="products row row-cols-xl-4 row-cols-lg-3 row-cols-md-2 row-cols-1">
                <?php if (empty($products)): ?>
                    <div class="col-12 text-center py-5">
                        <h4 class="text-muted">No products found matching your criteria.</h4>
                        <a href="shop.php" class="btn btn-dark mt-3">Reset Filters</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col mb-4">
                            <div class="product">
                                <div class="product-thumb">
                                    <?php if ($product['stock'] <= 0): ?>
                                        <span class="product-badges">
                                            <span class="outofstock"><i class="far fa-frown"></i> Out Of Stock</span>
                                        </span>
                                    <?php elseif ($product['id'] == 1): ?>
                                        <span class="product-badges">
                                            <span class="onsale">-10%</span>
                                        </span>
                                    <?php endif; ?>
                                    <a href="product-detail.php?id=<?= $product['id'] ?>" class="image">
                                        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                        <?php 
                                            $hover_img = str_replace('.webp', '-hover.webp', $product['image_url']);
                                            if (file_exists(__DIR__ . '/' . $hover_img)): 
                                        ?>
                                            <img class="image-hover" src="<?= htmlspecialchars($hover_img) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                        <?php endif; ?>
                                    </a>
                                </div>
                                <div class="product-info">
                                    <span class="category" style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #999;">
                                        <?= htmlspecialchars($product['category_name']) ?>
                                    </span>
                                    <h6 class="title" style="margin-top: 5px;">
                                        <a href="product-detail.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a>
                                    </h6>
                                    <span class="price">
                                        $<?= number_format($product['price'], 2) ?>
                                    </span>
                                    <div class="product-buttons">
                                        <a href="product-detail.php?id=<?= $product['id'] ?>" class="product-button hintT-top" data-hint="View Details"><i class="fas fa-search"></i></a>
                                        <?php if ($product['stock'] > 0): ?>
                                            <a href="api/cart_actions.php?action=add&id=<?= $product['id'] ?>&quantity=1" class="product-button hintT-top" data-hint="Add to Cart"><i class="fas fa-shopping-cart"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <!-- Products Grid End -->

            <!-- Pagination Start -->
            <?php if ($total_pages > 1): ?>
                <div class="row mt-5">
                    <div class="col-12">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link btn btn-outline-dark mx-1" href="<?= buildQueryUrl(['page' => $page - 1]) ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                        <a class="page-link btn <?= ($i == $page) ? 'btn-dark' : 'btn-outline-dark' ?> mx-1" href="<?= buildQueryUrl(['page' => $i]) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link btn btn-outline-dark mx-1" href="<?= buildQueryUrl(['page' => $page + 1]) ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>
            <!-- Pagination End -->

        </div>
    </div>
    <!-- Shop Products Section End -->

<?php require_once 'includes/footer.php'; ?>
