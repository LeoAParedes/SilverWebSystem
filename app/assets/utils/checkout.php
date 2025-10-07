<?php
session_start();
require_once 'app/connect.php';
require_once 'app/asseets/utils/cart-session.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header('Location: gallery.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM customers WHERE user_id = ?");
$stmt->execute([$userId]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingData = [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'external_number' => $_POST['external_number'],
        'internal_number' => $_POST['internal_number'] ?? '',
        'neighborhood' => $_POST['neighborhood'],
        'city' => $_POST['city'],
        'state' => $_POST['state'],
        'postal_code' => $_POST['postal_code'],
        'country' => 'Mexico',
        'special_instructions' => $_POST['special_instructions'] ?? ''
    ];
    
    $orderStmt = $pdo->prepare("
        INSERT INTO orders (
            user_id, customer_id, order_number, order_date, 
            subtotal, tax, shipping, total, 
            shipping_address, billing_address, 
            status, payment_status
        ) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, 'pending', 'pending')
    ");
    
    $orderNumber = 'ORD-' . date('Ymd') . '-' . uniqid();
    $subtotal = getCartTotal();
    $tax = $subtotal * 0.16;
    $shipping = 150;
    $total = $subtotal + $tax + $shipping;
    
    $shippingAddress = json_encode($shippingData);
    
    $orderStmt->execute([
        $userId,
        $customer['customer_id'] ?? null,
        $orderNumber,
        $subtotal,
        $tax,
        $shipping,
        $total,
        $shippingAddress,
        $shippingAddress
    ]);
    
    $orderId = $pdo->lastInsertId();
    
    foreach ($cart as $item) {
        $itemStmt = $pdo->prepare("
            INSERT INTO order_items (
                order_id, design_id, quantity, unit_price, 
                subtotal, design_details, custom_notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $itemStmt->execute([
            $orderId,
            $item['design_id'],
            $item['quantity'],
            $item['price'],
            $item['price'] * $item['quantity'],
            $item['design_details'] ?? '',
            $item['custom_notes'] ?? ''
        ]);
    }
    
    clearCart($userId);
    
    header("Location: order-success.php?order=$orderNumber");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Silver Web System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="output.css" rel="stylesheet">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .checkout-step {
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 0.618rem;
            background: var(--honeydew);
        }
        
        .form-label {
            color: var(--black-olive);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .form-control:focus {
            border-color: var(--forest-green);
            box-shadow: 0 0 0 0.2rem rgba(19, 138, 54, 0.25);
        }
        
        .btn-checkout {
            background: linear-gradient(135deg, var(--forest-green) 0%, var(--spring-green) 100%);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.382s;
        }
        
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(19, 138, 54, 0.3);
        }
    </style>
</head>
<body>
    <div class="checkout-container py-5">
        <h1 class="text-center mb-5" style="color: var(--forest-green);">
            <i class="fas fa-lock"></i> Secure Checkout
        </h1>
        
        <form method="POST" id="checkoutForm">
            <div class="row">
                <div class="col-lg-8">
                    <div class="checkout-step">
                        <h3 class="mb-4" style="color: var(--black-olive);">
                            <i class="fas fa-shipping-fast"></i> Shipping Information
                        </h3>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name *</label>
                                <input type="text" class="form-control" name="first_name" 
                                       value="<?= $customer['first_name'] ?? '' ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Last Name *</label>
                                <input type="text" class="form-control" name="last_name" 
                                       value="<?= $customer['last_name'] ?? '' ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?= $customer['email'] ?? '' ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Phone *</label>
                                <input type="tel" class="form-control" name="phone" 
                                       placeholder="+52 XXX XXX XXXX" required>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Street Address *</label>
                                <input type="text" class="form-control" name="address" required 
                                       placeholder="Street name">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">External Number *</label>
                                <input type="text" class="form-control" name="external_number" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Internal Number</label>
                                <input type="text" class="form-control" name="internal_number">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Neighborhood *</label>
                                <input type="text" class="form-control" name="neighborhood" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">City *</label>
                                <input type="text" class="form-control" name="city" 
                                       value="Mexicali" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">State *</label>
                                <select class="form-control" name="state" required>
                                    <option value="">Select State</option>
                                    <option value="BC" selected>Baja California</option>
                                    <option value="BCS">Baja California Sur</option>
                                    <option value="SON">Sonora</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Postal Code *</label>
                                <input type="text" class="form-control" name="postal_code" 
                                       pattern="[0-9]{5}" maxlength="5" required>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Special Instructions</label>
                                <textarea class="form-control" name="special_instructions" rows="3" 
                                          placeholder="Any special delivery instructions..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="checkout-step" style="position: sticky; top: 20px;">
                        <h3 class="mb-4" style="color: var(--black-olive);">
                            <i class="fas fa-shopping-cart"></i> Order Summary
                        </h3>
                        
                        <div class="order-items mb-3">
                            <?php foreach ($cart as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <strong><?= htmlspecialchars($item['name']) ?></strong>
                                    <small class="text-muted d-block">Qty: <?= $item['quantity'] ?></small>
                                </div>
                                <span>$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>$<?= number_format(getCartTotal(), 2) ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>IVA (16%):</span>
                            <span>$<?= number_format(getCartTotal() * 0.16, 2) ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span>Shipping:</span>
                            <span>$150.00</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <h4>Total:</h4>
                            <h4 class="text-success">
                                $<?= number_format(getCartTotal() * 1.16 + 150, 2) ?> MXN
                            </h4>
                        </div>
                        
                        <button type="submit" class="btn btn-checkout w-100">
                            <i class="fas fa-lock"></i> Complete Order
                        </button>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt"></i> Secure SSL Encryption
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = this.querySelector('.btn-checkout');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        btn.disabled = true;
        
        setTimeout(() => {
            this.submit();
        }, 600);
    });
    </script>
</body>
</html>

