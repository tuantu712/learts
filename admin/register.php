<?php
// admin/register.php - Admin Registration Page
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $errors[] = "Please fill in all fields.";
    } else {
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }
        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long.";
        }
        
        // Check if username is already taken
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = :username");
                $stmt->execute(['username' => $username]);
                if ($stmt->fetchColumn() > 0) {
                    $errors[] = "Username is already taken.";
                }
            } catch (Exception $e) {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
        
        // Register the new admin
        if (empty($errors)) {
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (:username, :password)");
                $insert_stmt->execute([
                    'username' => $username,
                    'password' => $hashed_password
                ]);
                
                $_SESSION['admin_flash'] = [
                    'type' => 'success',
                    'text' => 'Admin registered successfully! You can now log in.'
                ];
                header('Location: login.php');
                exit;
            } catch (Exception $e) {
                $errors[] = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learts Admin - Register</title>
    <link rel="stylesheet" href="../assets/css/vendor/bootstrap.min.css">
    <style>
        body {
            background-color: #12151c;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .register-card {
            background-color: #1e2229;
            border: 1px solid #2d323e;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            padding: 40px 30px;
            color: #e3e4e6;
        }
        .register-card .brand-title {
            text-align: center;
            margin-bottom: 30px;
            font-family: Georgia, serif;
            font-style: italic;
            font-size: 28px;
            color: #fff;
        }
        .register-card .brand-title span {
            color: #d5b85a;
        }
        .form-control {
            background-color: #12151c;
            border: 1px solid #2d323e;
            color: #fff;
        }
        .form-control:focus {
            background-color: #12151c;
            border-color: #d5b85a;
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(213, 184, 90, 0.25);
        }
        .btn-gold {
            background-color: #d5b85a;
            color: #12151c;
            font-weight: 600;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-gold:hover {
            background-color: #e0c570;
            color: #12151c;
        }
        .register-card a {
            color: #d5b85a;
            text-decoration: none;
        }
        .register-card a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="register-card">
    <h2 class="brand-title">Learts <span>Admin</span></h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger py-2" style="font-size: 14px;">
            <ul class="mb-0 pl-3">
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <input type="hidden" name="register" value="1">
        
        <div class="mb-3">
            <label for="username" class="form-label text-muted small">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required autofocus>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label text-muted small">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="mb-4">
            <label for="confirm_password" class="form-label text-muted small">Confirm Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        
        <button type="submit" class="btn btn-gold w-100 py-2 mb-3">Register Admin</button>
        
        <div class="text-center small text-muted">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </form>
</div>

</body>
</html>
