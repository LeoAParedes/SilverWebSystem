<?php
session_start();
require_once '../../connect.php';
require_once '../../assets/utils/cart-session.php';

$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['id']);
$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

// Load cart from database if logged in
if ($isLoggedIn && $userId) {
    loadCartFromDatabase($userId);
}

$cart = $_SESSION['cart'] ?? [];
$cartTotal = getCartTotal();
$cartCount = getCartItemCount();

// Handle AJAX cart operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch($_POST['action']) {
        case 'update_quantity':
            $designId = intval($_POST['design_id']);
            $quantity = intval($_POST['quantity']);
            
            if (isset($_SESSION['cart'][$designId])) {
                if ($quantity > 0) {
                    $_SESSION['cart'][$designId]['quantity'] = $quantity;
                } else {
                    unset($_SESSION['cart'][$designId]);
                }
                
                if ($isLoggedIn) {
                    saveCartToDatabase($userId, $_SESSION['cart']);
                }
                
                echo json_encode([
                    'success' => true,
                    'cart_total' => getCartTotal(),
                    'cart_count' => getCartItemCount()
                ]);
            }
            exit;
            
        case 'remove_item':
            $designId = intval($_POST['design_id']);
            
            if (isset($_SESSION['cart'][$designId])) {
                unset($_SESSION['cart'][$designId]);
                
                if ($isLoggedIn) {
                    saveCartToDatabase($userId, $_SESSION['cart']);
                }
                
                echo json_encode([
                    'success' => true,
                    'cart_total' => getCartTotal(),
                    'cart_count' => getCartItemCount()
                ]);
            }
            exit;
    }
}

// Get product images
foreach ($cart as $designId => &$item) {
    $imgStmt = $pdo->prepare("SELECT image_path FROM images WHERE designid = ?");
    $imgStmt->execute([$designId]);
    $image = $imgStmt->fetch(PDO::FETCH_ASSOC);
    $item['image_path'] = $image['image_path'] ?? '/app/assets/img/placeholder.png';
}

function fixImagePath($path) {
    if (!$path) return '../../assets/img/placeholder.png';
    if (strpos($path, '/app/') === 0) {
        return '../..' . $path;
    }
    return $path;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Silver Web System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../../assets/css/output.css" rel="stylesheet">
    <style>
        :root {
            --forest-green: #138a36;
            --spring-green: #18ff6d;
            --black-olive: #34403a;
            --ash-gray: #b4d0bf;
            --honeydew: #e4f2e9;
        }
        
        body {
            background: linear-gradient(135deg, #f9fcfa 0%, #e4f2e9 100%);
            min-height: 100vh;
            padding-top: 100px;
        }
        
        .cart-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .cart-header {
            background: linear-gradient(135deg, var(--forest-green) 0%, var(--spring-green) 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem 1rem 0 0;
            text-align: center;
        }
        
        .cart-item {
            background: white;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-radius: 0.618rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.382s ease;
        }
        
        .cart-item:hover {
            box-shadow: 0 4px 20px rgba(19, 138, 54, 0.1);
            transform: translateY(-2px);
        }
        
        .item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 0.618rem;
            border: 2px solid var(--honeydew);
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid var(--forest-green);
            background: white;
            color: var(--forest-green);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .quantity-btn:hover {
            background: var(--forest-green);
            color: white;
        }
        
        .cart-summary {
            background: white;
            padding: 2rem;
            border-radius: 0.618rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 20px;
        }
        
        .checkout-btn {
            background: linear-gradient(135deg, var(--forest-green) 0%, var(--spring-green) 100%);
            color: white;
            padding: 1rem;
            border-radius: 50px;
            border: none;
            width: 100%;
            font-weight: 600;
            transition: all 0.382s ease;
        }
        
        .checkout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(19, 138, 54, 0.3);
        }
        
        .empty-cart {
            text-align: center;
            padding: 4rem;
            background: white;
            border-radius: 1rem;
        }
        
        .remove-btn {
            color: #dc3545;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .remove-btn:hover {
            transform: scale(1.2);
        }
        
        .text-forest-green {
            color: var(--forest-green) !important;
        }
        
        .btn-forest {
            background: linear-gradient(135deg, #138a36 0%, #138a36 100%);
            color: white;
            border: none;
        }
        
        .btn-forest:hover {
            background: linear-gradient(135deg, #138a36 0%, #18ff6d 100%);
            color: white;
        }
    </style>
</head>
<body>
    <?php include('../../assets/navmenu/navmenu.php'); ?>
    
    <div class="cart-container">
        <div class="cart-header">
            <h1><i class="fas fa-shopping-cart"></i> Shopping Cart</h1>
            <p><?= $cartCount ?> items in your cart</p>
        </div>
        
        <?php if (empty($cart)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-basket fa-4x text-muted mb-3"></i>
            <h3>Your cart is empty</h3>
            <p class="text-muted">Add some amazing designs to get started!</p>
            <a href="../../../index.php#gallery" class="btn checkout-btn" style="width: auto; padding: 0.75rem 2rem;">
                <i class="fas fa-arrow-left"></i> Continue Shopping
            </a>
        </div>
        <?php else: ?>
        
        <div class="row mt-4">
            <div class="col-lg-8">
                <?php foreach ($cart as $designId => $item): 
                    $imagePath = fixImagePath($item['image_path']);
                ?>
                <div class="cart-item" id="cart-item-<?= $designId ?>">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <img src="<?= htmlspecialchars($imagePath) ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>" 
                                 class="item-image">
                        </div>
                        <div class="col-md-4">
                            <h5><?= htmlspecialchars($item['name']) ?></h5>
                            <?php if (!empty($item['design_details'])): ?>
                            <small class="text-muted">Details: <?= htmlspecialchars($item['design_details']) ?></small>
                            <?php endif; ?>
                            <?php if (!empty($item['custom_notes'])): ?>
                            <small class="text-muted d-block">Notes: <?= htmlspecialchars($item['custom_notes']) ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-2">
                            <div class="quantity-control">
                                <button class="quantity-btn" onclick="updateQuantity(<?= $designId ?>, -1)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="form-control text-center" 
                                       value="<?= $item['quantity'] ?>" 
                                       id="qty-<?= $designId ?>" 
                                       style="width: 60px;" readonly>
                                <button class="quantity-btn" onclick="updateQuantity(<?= $designId ?>, 1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <span class="h5">$<?= number_format($item['price'], 2) ?></span>
                            <small class="d-block text-muted">per unit</small>
                        </div>
                        <div class="col-md-1 text-center">
                            <span class="h5 text-success">$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        </div>
                        <div class="col-md-1 text-center">
                            <i class="fas fa-trash remove-btn" onclick="removeFromCart(<?= $designId ?>)"></i>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="cart-summary">
                    <h4 class="mb-4">Order Summary</h4>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span id="subtotal">$<?= number_format($cartTotal, 2) ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>IVA (16%):</span>
                        <span id="tax">$<?= number_format($cartTotal * 0.16, 2) ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span>Shipping:</span>
                        <span>$150.00</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <h5>Total:</h5>
                        <h5 class="text-success" id="total">
                            $<?= number_format($cartTotal * 1.16 + 150, 2) ?> MXN
                        </h5>
                    </div>
                    
                    <?php if ($isLoggedIn): ?>
                    <a href="checkout.php" class="btn checkout-btn">
                        <i class="fas fa-lock"></i> Proceed to Checkout
                    </a>
                    <?php else: ?>
                    <button class="btn checkout-btn" onclick="showCheckoutOptions()">
                        <i class="fas fa-lock"></i> Proceed to Checkout
                    </button>
                    <?php endif; ?>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt"></i> Secure SSL Checkout
                        </small>
                    </div>
                    
                    <hr class="my-4">
                    
<p class="mb-2">Already have an account?</p>
    <button type="button" class="btn btn-forest" onclick="saveReturnUrl(); window.location.href='../../../index.php#login';">
        <i class="fas fa-sign-in-alt"></i> Login
    </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
<script>
function saveReturnUrl() {
    // Save where to return after login
    sessionStorage.setItem('returnAfterLogin', 'checkout');
}
</script>


    
    <!-- Checkout Options Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Checkout Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card h-100 text-center">
                                <div class="card-body">
                                    <i class="fas fa-user-plus fa-3x text-success mb-3"></i>
                                    <h5>Create Account</h5>
                                    <p class="text-muted">Save your info for faster checkout</p>
                                    <a href="../../../register.php?redirect=checkout" class="btn btn-forest w-100">
                                        Sign Up & Checkout
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 text-center">
                                <div class="card-body">
                                    <i class="fas fa-user fa-3x text-secondary mb-3"></i>
                                    <h5>Guest Checkout</h5>
                                    <p class="text-muted">Checkout without creating account</p>
                                    <a href="guest-checkout.php" class="btn btn-outline-secondary w-100">
                                        Continue as Guest
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <p class="mb-2">Already have an account?</p>
                        <a href="../../../login.php?redirect=checkout" class="btn btn-forest">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    function updateQuantity(designId, change) {
        const qtyInput = document.getElementById('qty-' + designId);
        const newQty = parseInt(qtyInput.value) + change;
        
        if (newQty < 1) return;
        
        const formData = new FormData();
        formData.append('action', 'update_quantity');
        formData.append('design_id', designId);
        formData.append('quantity', newQty);
        
        fetch('cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
    
    function removeFromCart(designId) {
        Swal.fire({
            title: 'Remove from cart?',
            text: "This item will be removed from your cart",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#138a36',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, remove it'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'remove_item');
                formData.append('design_id', designId);
                
                fetch('cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        });
    }
    
    function showCheckoutOptions() {
        const modal = new bootstrap.Modal(document.getElementById('checkoutModal'));
        modal.show();
    }
    </script>
</body>
</html>

