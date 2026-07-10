<?php
// api/products.php - REST API endpoint for products (GET listing + Admin CRUD operations)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // --- GET LISTING ---
    $category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'default';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
    
    if ($page < 1) $page = 1;
    if ($limit < 1 || $limit > 50) $limit = 8;
    $offset = ($page - 1) * $limit;
    
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
    
    $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";
    
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
    
    try {
        // Count total matching
        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM products p $where_sql");
        $count_stmt->execute($params);
        $total_items = (int)$count_stmt->fetchColumn();
        
        $total_pages = ceil($total_items / $limit);
        
        // Fetch products
        $sql = "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id $where_sql $order_sql LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'message' => 'Products fetched successfully',
            'data' => [
                'products' => $products,
                'pagination' => [
                    'total_items' => $total_items,
                    'total_pages' => $total_pages,
                    'current_page' => $page,
                    'limit' => $limit
                ]
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to retrieve products: ' . $e->getMessage()
        ]);
    }
    exit;
}

// --- ADMIN CRUD ACTIONS (POST, PUT, DELETE) ---
// Require active admin session
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Admin session required.'
    ]);
    exit;
}

// Helper to validate product input
function validateProductData($data) {
    $errors = [];
    if (empty($data['name'])) $errors[] = "Product name is required.";
    if (empty($data['description'])) $errors[] = "Description is required.";
    if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] < 0) {
        $errors[] = "Price must be a valid non-negative number.";
    }
    if (!isset($data['stock']) || !is_numeric($data['stock']) || $data['stock'] < 0) {
        $errors[] = "Stock must be a valid non-negative integer.";
    }
    if (empty($data['category_id']) || (int)$data['category_id'] <= 0) {
        $errors[] = "A valid category_id is required.";
    }
    if (empty($data['image_url'])) {
        $errors[] = "Product image_url is required.";
    }
    return $errors;
}

// Get JSON raw body data
$raw_input = file_get_contents('php://input');
$input_data = json_decode($raw_input, true) ?: $_POST;

switch ($method) {
    case 'POST':
        // --- CREATE PRODUCT ---
        $errors = validateProductData($input_data);
        if (count($errors) > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category_id, image_url) VALUES (:name, :description, :price, :stock, :category_id, :image_url)");
            $stmt->execute([
                'name' => trim($input_data['name']),
                'description' => trim($input_data['description']),
                'price' => (float)$input_data['price'],
                'stock' => (int)$input_data['stock'],
                'category_id' => (int)$input_data['category_id'],
                'image_url' => trim($input_data['image_url'])
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => ['id' => $pdo->lastInsertId()]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create product: ' . $e->getMessage()]);
        }
        break;
        
    case 'PUT':
        // --- UPDATE PRODUCT ---
        $product_id = isset($input_data['id']) ? (int)$input_data['id'] : 0;
        if ($product_id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'A valid product ID is required.']);
            exit;
        }
        
        $errors = validateProductData($input_data);
        if (count($errors) > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE products SET name = :name, description = :description, price = :price, stock = :stock, category_id = :category_id, image_url = :image_url WHERE id = :id");
            $result = $stmt->execute([
                'name' => trim($input_data['name']),
                'description' => trim($input_data['description']),
                'price' => (float)$input_data['price'],
                'stock' => (int)$input_data['stock'],
                'category_id' => (int)$input_data['category_id'],
                'image_url' => trim($input_data['image_url']),
                'id' => $product_id
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Product updated successfully'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update product: ' . $e->getMessage()]);
        }
        break;
        
    case 'DELETE':
        // --- DELETE PRODUCT ---
        $product_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($input_data['id']) ? (int)$input_data['id'] : 0);
        
        if ($product_id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'A valid product ID is required.']);
            exit;
        }
        
        try {
            // Check if product exists
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE id = :id");
            $check_stmt->execute(['id' => $product_id]);
            if ($check_stmt->fetchColumn() == 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Product not found.']);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
            $stmt->execute(['id' => $product_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete product: ' . $e->getMessage()]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
        break;
}
