
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['action']) && $_GET['action'] === 'get_count') {
    header('Content-Type: application/json');
    echo json_encode(['count' => getCartItemCount()]);
    exit;
}

// Fix the require path
require_once dirname(__DIR__) . '/../connect.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function loadCartFromDatabase($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT cart_data FROM user_cart 
        WHERE user_id = ? 
        ORDER BY updated_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && $result['cart_data']) {
        $_SESSION['cart'] = json_decode($result['cart_data'], true);
        return true;
    }
    return false;
}

function saveCartToDatabase($userId, $cartData) {
    global $pdo;
    
    $cartJson = json_encode($cartData);
    $itemCount = 0;
    foreach($cartData as $item) {
        $itemCount += $item['quantity'];
    }
    
    $checkStmt = $pdo->prepare("SELECT id FROM user_cart WHERE user_id = ?");
    $checkStmt->execute([$userId]);
    $exists = $checkStmt->fetch();
    
    if ($exists) {
        $updateStmt = $pdo->prepare("
            UPDATE user_cart 
            SET cart_data = ?, item_count = ?, updated_at = NOW() 
            WHERE user_id = ?
        ");
        $updateStmt->execute([$cartJson, $itemCount, $userId]);
    } else {
        $insertStmt = $pdo->prepare("
            INSERT INTO user_cart (user_id, cart_data, item_count, created_at, updated_at) 
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        $insertStmt->execute([$userId, $cartJson, $itemCount]);
    }
}

function clearCart($userId = null) {
    $_SESSION['cart'] = [];
    
    if ($userId) {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM user_cart WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
}

function getCartTotal() {
    $total = 0;
    foreach ($_SESSION['cart'] ?? [] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return round($total, 2);
}

function getCartItemCount() {
    $count = 0;
    foreach ($_SESSION['cart'] ?? [] as $item) {
        $count += $item['quantity'];
    }
    return $count;
}
?>

