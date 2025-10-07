<?php
session_start();
require_once '../../connect.php';
require_once '../../assets/utils/cart-session.php';

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header('Location: ../../../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process guest checkout
    $orderData = [
        'order_number' => 'ORD-' . date('Ymd') . '-' . uniqid(),
        'customer_email' => $_POST['email'],
        'customer_name' => $_POST['first_name'] . ' ' . $_POST['last_name'],
        'shipping_address' => json_encode([
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'address' => $_POST['address'],
            'city' => $_POST['city'],
            'state' => $_POST['state'],
            'postal_code' => $_POST['postal_code'],
            'country' => 'Mexico'
        ]),
        'payment_method' => $_POST['payment_method'] ?? 'pending',
        'subtotal' => getCartTotal(),
        'tax' => getCartTotal() * 0.16,
        'shipping' => 150,
        'total' => getCartTotal() * 1.16 + 150,
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Store order in session for payment processing
    $_SESSION['pending_order'] = $orderData;
    $_SESSION['pending_order']['items'] = $cart;
    
    // Redirect to payment
    header('Location: payment.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Checkout - Silver Web System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --forest-green: #138a36;
            --spring-green: #18ff6d;
            --honeydew: #e4f2e9;
        }
        
        body {
            background: linear-gradient(135deg, #f9fcfa 0%, #e4f2e9 100%);
        }
        
        .checkout-header {
            background: linear-gradient(135deg, var(--forest-green) 0%, var(--spring-green) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .btn-checkout {
            background: linear-gradient(135deg, var(--forest-green) 0%, var(--spring-green) 100%);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            font-weight: 600;
        }
        
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(19, 138, 54, 0.3);
        }
    </style>
</head>
<body>
    <div class="checkout-header">
        <h1><i class="fas fa-user"></i> Guest Checkout</h1>
        <p>Complete your order without creating an account</p>
    </div>
    
    <div class="container mt-4">
        <form method="POST" id="guestCheckoutForm">
            <div class="row">
                <div class="col-lg-8">
                    <div class="form-section">
                        <h3 class="mb-4"><i class="fas fa-user"></i> Contact Information</h3>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name *</label>
                                <input type="text" class="form-control" name="first_name" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Last Name *</label>
                                <input type="text" class="form-control" name="last_name" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Phone *</label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="mb-4"><i class="fas fa-shipping-fast"></i> Shipping Address</h3>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Street Address *</label>
                                <input type="text" class="form-control" name="address" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">City *</label>
                                <input type="text" class="form-control" name="city" value="Mexicali" required>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">State *</label>
                                <select class="form-control" name="state" required>
                                    <option value="BC">Baja California</option>
                                    <option value="BCS">Baja California Sur</option>
                                    <option value="SON">Sonora</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Postal Code *</label>
                                <input type="text" class="form-control" name="postal_code" maxlength="5" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="createAccount">
                            <label class="form-check-label" for="createAccount">
                                Create an account for faster checkout next time
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="form-section">
                        <h4 class="mb-3">Order Summary</h4>
                        
                        <?php foreach ($cart as $item): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <small><?= htmlspecialchars($item['name']) ?> x<?= $item['quantity'] ?></small>
                            <small>$<?= number_format($item['price'] * $item['quantity'], 2) ?></small>
                        </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong class="text-success">$<?= number_format(getCartTotal() * 1.16 + 150, 2) ?> MXN</strong>
                        </div>
                        
                        <button type="submit" class="btn btn-checkout w-100 mt-4">
                            <i class="fas fa-arrow-right"></i> Continue to Payment
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

