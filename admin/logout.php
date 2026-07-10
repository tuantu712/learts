<?php
// admin/logout.php - Destroy admin session and logout
require_once __DIR__ . '/../config/database.php';

// Unset all admin variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

// Optional: Destroy the entire session if no other data is needed
// session_destroy();

$_SESSION['admin_flash'] = [
    'type' => 'success',
    'text' => 'Logged out successfully!'
];

header('Location: login.php');
exit;
