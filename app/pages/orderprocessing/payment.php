<?php
session_start();
require_once '../../connect.php';

// Load Composer autoloader for Stripe
require_once '../../vendor/autoload.php';

\Stripe\Stripe::setApiKey(''); 

$orderData = $_SESSION['pending_order'] ?? null;

if (!$orderData) {
    header('Location: cart.php');
    exit;
}

// Process Stripe payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stripeToken'])) {
    try {
        // Create Stripe charge     
        $charge = \Stripe\Charge::create([
            'amount' => round($orderData['total'] * 100), // Amount in cents
            'currency' => 'mxn',
            'description' => 'Order #' . $orderData['order_number'] ?? uniqid(),
            'source' => $_POST['stripeToken'],
            'metadata' => [
                'order_id' => $orderData['order_number'] ?? '',
                'customer_email' => $orderData['customer_email'] ?? ''
            ]
        ]);
        
        // Payment successful - save order to database
        $orderNumber = 'ORD-' . date('Ymd') . '-' . uniqid();
        
        $pdo->beginTransaction();
        
        // Insert order
        $stmt = $pdo->prepare("
            INSERT INTO orders (
                order_number, user_id, customer_id, order_date,
                subtotal, tax, shipping, total,
                shipping_address, billing_address,
                payment_method, payment_status, status,
                stripe_charge_id
            ) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, 'stripe', 'paid', 'processing', ?)
        ");
        
        $stmt->execute([
            $orderNumber,
            $orderData['user_id'] ?? null,
            $orderData['customer_id'] ?? null,
            $orderData['subtotal'],
            $orderData['tax'],
            $orderData['shipping_cost'] ?? 150,
            $orderData['total'],
            json_encode($orderData['shipping'] ?? $orderData),
            json_encode($orderData['shipping'] ?? $orderData),
            $charge->id
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Insert order items
        foreach ($orderData['items'] as $item) {
            $itemStmt = $pdo->prepare("
                INSERT INTO order_items (
                    order_id, design_id, quantity, unit_price, subtotal
                ) VALUES (?, ?, ?, ?, ?)
            ");
            
            $itemStmt->execute([
                $orderId,
                $item['design_id'],
                $item['quantity'],
                $item['price'],
                $item['price'] * $item['quantity']
            ]);
        }
        
        $pdo->commit();


 // CLEAR CART FROM DATABASE if user is logged in
    if (isset($_SESSION['user_id']) || isset($_SESSION['id'])) {
        $userId = $_SESSION['user_id'] ?? $_SESSION['id'];
        
        // Delete from user_cart table
        $clearCartStmt = $pdo->prepare("DELETE FROM user_cart WHERE user_id = ?");
        $clearCartStmt->execute([$userId]);
    }
    
    // Clear pending order from session
    unset($_SESSION['pending_order']);



        header("Location: order-success.php?order=" . $orderNumber);
        exit();
        
    } catch(\Stripe\Exception\CardException $e) {
        $error = $e->getMessage();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Payment failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Silver Web System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Stripe JS -->
    <script src="https://js.stripe.com/v3/"></script>
    
    <style>
        :root {
            --forest-green: #138a36;
            --spring-green: #18ff6d;
        }
        
        body {
            background: linear-gradient(135deg, #f9fcfa 0%, #e4f2e9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .payment-container {
            max-width: 600px;
            margin: 3rem auto;
        }
        
        .payment-card {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .btn-complete {
            background: linear-gradient(135deg, var(--forest-green) 0%, var(--spring-green) 100%);
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 50px;
            width: 100%;
            font-weight: 600;
        }
        
        #card-element {
            background: #f8f9fa;
            padding: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
        }
        
        .StripeElement--focus {
            border-color: var(--forest-green);
        }
        
        #card-errors {
            color: #dc3545;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-card">
            <h2 class="text-center mb-4">
                <i class="fas fa-credit-card"></i> Secure Payment
            </h2>
            
            <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Order Total: 
                <strong>$<?= number_format($orderData['total'], 2) ?> MXN</strong>
            </div>
            
            <form id="payment-form" method="POST">
                <div class="mb-4">
                    <label class="form-label">Card Information</label>
                    <div id="card-element">
                        <!-- Stripe Card Element will be inserted here -->
                    </div>
                    <div id="card-errors" role="alert"></div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Cardholder Name</label>
                    <input type="text" class="form-control" id="cardholder-name" required>
                </div>
                
                <button type="submit" class="btn btn-complete" id="submit-payment">
                    <i class="fas fa-lock"></i> Pay $<?= number_format($orderData['total'], 2) ?> MXN
                </button>
            </form>
            
            <div class="text-center mt-4">
                <img src="https://cdn.brandfolder.io/KGT2DTA4/at/8vbr8k4mr5fwvspk36kffbqh/Powered_by_Stripe_-_blurple.svg" 
                     alt="Powered by Stripe" height="40">
                <p class="text-muted mt-2">
                    <i class="fas fa-shield-alt"></i> Your payment information is secure and encrypted
                </p>
            </div>
                    </div>
    </div>
    
    <script>
    // Create Stripe instance
    var stripe = Stripe(''); 
    var elements = stripe.elements();
    
    // Create card element
    var cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        }
    });
    
    // Mount card element
    cardElement.mount('#card-element');
    
    // Handle real-time validation errors
    cardElement.addEventListener('change', function(event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
    
    // Handle form submission
    var form = document.getElementById('payment-form');
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        var submitBtn = document.getElementById('submit-payment');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        stripe.createToken(cardElement).then(function(result) {
            if (result.error) {
                // Show error
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-lock"></i> Pay $<?= number_format($orderData['total'], 2) ?> MXN';
            } else {
                // Send token to server
                var hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'stripeToken');
                hiddenInput.setAttribute('value', result.token.id);
                form.appendChild(hiddenInput);
                
                // Submit form
                form.submit();
            }
        });
    });
    </script>
</body>
</html>

