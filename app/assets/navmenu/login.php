<?php
session_start();
require_once 'app/connect.php';

// Redirect if already logged in
if (isset($_SESSION['user_id']) || isset($_SESSION['id'])) {
    header("Location: app/dashboard.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT id, username, password, email, role_id FROM users WHERE username = ? OR email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role_id'] = $user['role_id'];
        
        // Redirect based on role or return URL
        $redirect = $_GET['redirect'] ?? '';
        if ($redirect === 'checkout') {
            header("Location: app/pages/orderprocessing/checkout.php");
        } else {
            header("Location: app/dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Silver Web System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #34403a 0%, #138a36 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .btn-forest {
            background: linear-gradient(135deg, #138a36 0%, #18ff6d 100%);
            color: white;
            border: none;
        }
        .btn-forest:hover {
            background: linear-gradient(135deg, #138a36 0%, #138a36 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <i class="fas fa-user-circle fa-4x text-success"></i>
            <h2 class="mt-3">Silver Web System</h2>
        </div>
        
        <?php if($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username or Email</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-forest w-100">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <hr class="my-4">
        
        <div class="text-center">
            <p>Don't have an account? <a href="register.php">Register</a></p>
            <a href="index.php">Back to Home</a>
        </div>
    </div>
</body>
</html>

