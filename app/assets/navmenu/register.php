<?php
session_start();
require_once 'app/connect.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: app/dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        // Check if username or email exists
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $checkStmt->execute([$username, $email]);
        
        if ($checkStmt->fetch()) {
            $error = "Username or email already exists";
        } else {
            // Insert new user
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $insertStmt = $pdo->prepare("INSERT INTO users (username, password, email, role_id, created_at) VALUES (?, ?, ?, 5, NOW())");
            
            if ($insertStmt->execute([$username, $hashedPassword, $email])) {
                $userId = $pdo->lastInsertId();
                
                // Auto-login after registration
                $_SESSION['user_id'] = $userId;
                $_SESSION['id'] = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['role_id'] = 5; // Default user role
                
                // Check if redirecting to checkout
                $redirect = $_GET['redirect'] ?? '';
                if ($redirect === 'checkout') {
                    header("Location: app/pages/orderprocessing/checkout.php");
                } else {
                    header("Location: app/dashboard.php");
                }
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Silver Web System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #34403a 0%, #138a36 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .register-card {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
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
    <div class="register-card">
        <div class="text-center mb-4">
            <i class="fas fa-user-plus fa-4x text-success"></i>
            <h2 class="mt-3">Create Account</h2>
        </div>
        
        <?php if($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required 
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-forest w-100">
                <i class="fas fa-user-plus"></i> Register
            </button>
        </form>
        
        <hr class="my-4">
        
        <div class="text-center">
            <p>Already have an account? <a href="login.php">Login</a></p>
            <a href="index.php">Back to Home</a>
        </div>
    </div>
</body>
</html>

