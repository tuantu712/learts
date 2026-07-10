<?php
// api/categories.php - REST API endpoint for retrieving product categories
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'message' => 'Categories fetched successfully',
        'data' => $categories
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch categories: ' . $e->getMessage(),
        'data' => []
    ]);
}
