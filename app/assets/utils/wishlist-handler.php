<?php

session_start();
require_once '../../connect.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache');

$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $designId = intval($_POST['design_id'] ?? 0);
    
    if ($action === 'toggle_wishlist' && $designId > 0) {
        try {
          
            $checkStmt = $pdo->prepare("
                SELECT id FROM userwishlist 
                WHERE user_id = ? AND designid = ?
            ");
            $checkStmt->execute([$userId, $designId]);
            $exists = $checkStmt->fetch();
            
            if ($exists) {
          
                $deleteStmt = $pdo->prepare("
                    DELETE FROM userwishlist 
                    WHERE user_id = ? AND designid = ?
                ");
                $deleteStmt->execute([$userId, $designId]);
                
                echo json_encode([
                    'success' => true,
                    'action' => 'removed',
                    'message' => 'Removed from wishlist'
                ]);
            } else {
              
                $insertStmt = $pdo->prepare("
                    INSERT INTO userwishlist (user_id, designid, wishlist, created_at) 
                    VALUES (?, ?, 1, NOW())
                ");
                $insertStmt->execute([$userId, $designId]);
                
                echo json_encode([
                    'success' => true,
                    'action' => 'added',
                    'message' => 'Added to wishlist'
                ]);
            }
        } catch (PDOException $e) {
            error_log('Wishlist error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Database error occurred'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request'
        ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'get_wishlist') {
        // Get all wishlist items for current user
        $stmt = $pdo->prepare("
            SELECT designid 
            FROM userwishlist 
            WHERE user_id = ? AND wishlist = 1
        ");
        $stmt->execute([$userId]);
        $wishlist = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode([
            'success' => true,
            'wishlist' => $wishlist
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>
