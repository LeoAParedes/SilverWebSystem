<?php
// app/ajax/create-order.php - Create order and prepare Stripe payment
session_start();
require_once '../connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit;
}

$userId = $_SESSION['user_id'] ?? $_SESSION['id'];

// Get form data
$productId = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);
$price = floatval($_POST['price']);
$shippingAddress = $_POST['shipping_address'] ?? '';
$phone = $_POST['phone'] ?? '';

// Validate product and stock
$stmt = $pdo->prepare("SELECT * FROM design WHERE designid = ? AND is_active = 1");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

if ($product['stock_quantity'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
    exit;
}

// Calculate totals
$subtotal = $price * $quantity;
$shipping = 100.00; // Default shipping cost
$tax = $subtotal * 0.16; // 16% IVA
$total = $subtotal + $shipping + $tax;

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Create order (you'll need to create an orders table)
    $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    
    // For now, store in session (implement orders table)
    $_SESSION['pending_order'] = [
        'order_number' => $orderNumber,
        'user_id' => $userId,
        'product_id' => $productId,
        'quantity' => $quantity,
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total,
        'shipping_address' => $shippingAddress,
        'phone' => $phone,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Update stock
    $stmt = $pdo->prepare("UPDATE design SET stock_quantity = stock_quantity - ? WHERE designid = ?");
    $stmt->execute([$quantity, $productId]);
    
    // Create inventory transaction
    $stmt = $pdo->prepare("INSERT INTO inventory_transactions 
        (product_id, transaction_type, quantity, unit_cost, total_cost, created_by) 
        VALUES (?, 'sale', ?, ?, ?, ?)");
    $stmt->execute([$productId, -$quantity, $price, $total, $userId]);
    
    $pdo->commit();
    
    // Stripe integration scaffold
    // Uncomment and configure when you have Stripe keys
    /*
    require_once 'stripe-php/init.php';
    \Stripe\Stripe::setApiKey('your-stripe-secret-key');
    
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'mxn',
                'product_data' => [
                    'name' => $product['name'],
                    'description' => $product['description'],
                ],
                'unit_amount' => $price * 100, // Stripe uses cents
            ],
            'quantity' => $quantity,
        ]],
        'mode' => 'payment',
        'success_url' => 'https://silverwebsystem.com/success?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'https://silverwebsystem.com/cancel',
    ]);
    
    $stripe_url = $checkout_session->url;
    */
    
    echo json_encode([
        'success' => true,
        'order_id' => $orderNumber,
        'message' => 'Order created successfully',
        // 'stripe_url' => $stripe_url ?? null
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error processing order: ' . $e->getMessage()]);
}
?>

