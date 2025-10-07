<?php
session_start();



$orderNumber = $_GET['order'] ?? 'DEMO-' . date('YmdHis');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful - Silver Web System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f9fcfa 0%, #e4f2e9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .success-card {
            background: white;
            padding: 3rem;
            border-radius: 1rem;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #138a36 0%, #18ff6d 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s ease;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        .btn-forest {
            background: linear-gradient(135deg, #138a36 0%, #18ff6d 100%);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 50px;
        }
        
        .btn-forest:hover {
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check text-white fa-3x"></i>
        </div>
        
        <h2 class="mb-3">Order Successful!</h2>
        <p class="text-muted mb-4">
            Thank you for your order. We've received your payment and will process your order shortly.
        </p>
        
        <div class="alert alert-light">
            <strong>Order Number:</strong><br>
            <span class="h5"><?= htmlspecialchars($orderNumber) ?></span>
        </div>
                <div class="d-grid gap-2">
            <a href="../../../index.php" class="btn btn-forest">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <?php if(isset($_SESSION['user_id'])): ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

