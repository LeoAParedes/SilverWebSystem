<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fix the path to connect.php
require_once dirname(__DIR__) . '/../connect.php';
require_once 'cart-session.php';

$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['id']);
$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'add_to_cart') {
        $designId = intval($_POST['design_id']);
        $quantity = intval($_POST['quantity']);
        $designDetails = $_POST['design_details'] ?? 'standard';
        $customNotes = $_POST['custom_notes'] ?? '';
        
        // Get product details from database
        $stmt = $pdo->prepare("
            SELECT d.*, d.unit_launch_price as price, i.image_path
            FROM design d 
            LEFT JOIN images i ON d.designid = i.designid
            WHERE d.designid = ? AND d.is_active = 1
        ");
        $stmt->execute([$designId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            // Initialize cart if not exists
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            // Check if item already in cart
            if (isset($_SESSION['cart'][$designId])) {
                $_SESSION['cart'][$designId]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$designId] = [
                    'design_id' => $designId,
                    'name' => $product['name'],
                    'quantity' => $quantity,
                    'price' => $product['price'],
                    'design_details' => $designDetails,
                    'custom_notes' => $customNotes,
                    'image_path' => $product['image_path'] ?? '',
                    'added_at' => date('Y-m-d H:i:s')
                ];
            }
            
            // Save to database if logged in
            if ($isLoggedIn && $userId) {
                saveCartToDatabase($userId, $_SESSION['cart']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Item added to cart successfully!',
                'cart_count' => getCartItemCount(),
                'cart_total' => getCartTotal()
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Product not found'
            ]);
        }
        exit;
    }
}

// Fixed renderOrderModal function
function renderOrderModal($product) {
    ?>
    <div class="modal fade" id="orderModal<?= $product['designid'] ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 0.618rem;">
                <div class="modal-header" style="background: linear-gradient(135deg, #138a36 0%, #18ff6d 100%);">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-shopping-cart"></i> Add to Cart - <?= htmlspecialchars($product['name']) ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <form id="cartForm<?= $product['designid'] ?>">
                        <input type="hidden" name="design_id" value="<?= $product['designid'] ?>">
                        <input type="hidden" name="action" value="add_to_cart">
                        
                        <div class="row g-4">
                            <div class="col-md-5">
                                <?php 
                                // Fix image path for display
                                $imagePath = $product['image_path'] ?? '';
                                if (strpos($imagePath, '/app/') === 0) {
                                    $imagePath = substr($imagePath, 1);
                                }
                                ?>
                                <img src="<?= htmlspecialchars($imagePath) ?>" 
                                     class="img-fluid rounded shadow-lg"
                                     alt="<?= htmlspecialchars($product['name']) ?>">
                                <div class="mt-3 p-3 rounded" style="background: #e4f2e9;">
                                    <h6 style="color: #138a36;">Price</h6>
                                    <h3 class="text-success">$<?= number_format($product['unit_launch_price'] ?? $product['price'], 2) ?> MXN</h3>
                                </div>
                            </div>
                            
                            <div class="col-md-7">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Quantity</label>
                                    <div class="input-group" style="max-width: 200px;">
                                        <button class="btn btn-outline-success" type="button" 
                                                onclick="adjustQty(<?= $product['designid'] ?>, -1)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" class="form-control text-center" 
                                               id="qty<?= $product['designid'] ?>" 
                                               name="quantity" value="1" min="1" 
                                               max="<?= $product['stock_quantity'] ?? 100 ?>">
                                        <button class="btn btn-outline-success" type="button" 
                                                onclick="adjustQty(<?= $product['designid'] ?>, 1)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Design Details</label>
                                    <select class="form-select" name="design_details">
                                        <option value="standard">Standard Design</option>
                                        <option value="custom_size">Custom Size</option>
                                        <option value="engraving">With Engraving</option>
                                        <option value="premium_finish">Premium Finish</option>
                                    </select>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Custom Requirements (Optional)</label>
                                    <textarea class="form-control" name="custom_notes" rows="3" 
                                              placeholder="Describe any specific customizations..."></textarea>
                                    <small class="text-warning mt-2">
                                        <i class="fas fa-info-circle"></i> 
                                        Additional customizations may affect the final price
                                    </small>
                                </div>
                                
                                <div class="p-3 bg-light rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">Subtotal:</span>
                                        <span class="h5 text-success mb-0" id="subtotal<?= $product['designid'] ?>" 
                                              data-price="<?= $product['unit_launch_price'] ?? $product['price'] ?>">
                                            $<?= number_format($product['unit_launch_price'] ?? $product['price'], 2) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="modal-footer" style="background: #e4f2e9;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-success px-4" 
                            onclick="addToCart(<?= $product['designid'] ?>)"
                            style="background: linear-gradient(135deg, #138a36 0%, #18ff6d 100%); border: none;">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>

