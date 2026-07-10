<?php
// api/index.php - Router for Vercel Serverless PHP deployment

// Set include path to project root so that relative requires/includes resolve correctly
set_include_path(dirname(__DIR__) . PATH_SEPARATOR . get_include_path());

$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
// Remove query string
$path = parse_url($request_uri, PHP_URL_PATH);

// Clean up path (remove leading/trailing slashes)
$path = trim($path, '/');

// Route to the appropriate root-level PHP file
switch ($path) {
    case '':
    case 'index':
        require_once 'index.php';
        break;
    case 'cart':
        require_once 'cart.php';
        break;
    case 'checkout':
        require_once 'checkout.php';
        break;
    case 'shop':
        require_once 'shop.php';
        break;
    case 'product-detail':
        require_once 'product-detail.php';
        break;
    case 'admin':
    case 'admin/dashboard':
        require_once 'admin/dashboard.php';
        break;
    case 'admin/login':
        require_once 'admin/login.php';
        break;
    case 'admin/logout':
        require_once 'admin/logout.php';
        break;
    case 'admin/orders':
        require_once 'admin/orders.php';
        break;
    case 'admin/products':
        require_once 'admin/products.php';
        break;
    case 'admin/product-add':
    case 'admin/product_add':
        require_once 'admin/product_add.php';
        break;
    case 'admin/product-edit':
    case 'admin/product_edit':
        require_once 'admin/product_edit.php';
        break;
    case 'admin/register':
        require_once 'admin/register.php';
        break;
    default:
        // Fallback for direct .php requests or subdirectories
        if (preg_match('/\.php$/', $path)) {
            if (file_exists(dirname(__DIR__) . '/' . $path)) {
                require_once $path;
                exit;
            }
        }
        
        // If it's a subdirectory path but without .php, check if the .php file exists
        if (file_exists(dirname(__DIR__) . '/' . $path . '.php')) {
            require_once $path . '.php';
            exit;
        }

        http_response_code(404);
        echo "404 Not Found";
        break;
}
