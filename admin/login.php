<?php
// admin/login.php - Admin Login Page
require_once __DIR__ . '/../config/database.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $errors[] = "Please fill in all fields.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Success! Set session
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = "Invalid username or password.";
            }
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learts Admin - Login</title>
    <link rel="stylesheet" href="../assets/css/vendor/bootstrap.min.css">
    <style>
        body {
            background-color: #12151c; /* Deep dark background matching theme */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .login-card {
            background-color: #1e2229;
            border: 1px solid #2d323e;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            padding: 40px 30px;
            color: #e3e4e6;
        }
        .login-card .brand-title {
            text-align: center;
            margin-bottom: 30px;
            font-family: Georgia, serif;
            font-style: italic;
            font-size: 28px;
            color: #fff;
        }
        .login-card .brand-title span {
            color: #d5b85a; /* Gold accent color */
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
        .login-card a {
            color: #d5b85a;
            text-decoration: none;
        }
        .login-card a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h2 class="brand-title">Learts <span>Admin</span></h2>
    
    <?php if (isset($_SESSION['admin_flash'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['admin_flash']['type']) ?> py-2 text-center" style="font-size: 14px;">
            <?= $_SESSION['admin_flash']['text'] ?>
        </div>
        <?php unset($_SESSION['admin_flash']); ?>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger py-2" style="font-size: 14px;">
            <ul class="mb-0 pl-3">
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <input type="hidden" name="login" value="1">
        
        <div class="mb-3">
            <label for="username" class="form-label text-muted small">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required autofocus>
        </div>
        
        <div class="mb-4">
            <label for="password" class="form-label text-muted small">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-gold w-100 py-2 mb-3">Sign In</button>
        
        <div class="text-center small text-muted">
            Need an account? <a href="register.php">Register Admin</a>
        </div>
        <div class="text-center mt-3 small">
            <a href="../index.php">&larr; Back to Client Store</a>
        </div>
    </form>
</div>

</body>
</html>
