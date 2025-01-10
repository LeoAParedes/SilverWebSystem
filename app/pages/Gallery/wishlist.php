<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("../../connect.php"); // Ensure this path is correct
session_start();

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$userId = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['designid'])) {
    echo json_encode(['success' => false, 'message' => 'Design ID not received.']);
    exit;
}

$designid = $data['designid'];

try {
    // Check if the record already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM userwishlist WHERE user_id = ? AND designid = ?");
    $stmt->execute([$userId, $designid]);
    $exists = $stmt->fetchColumn() > 0;

    if ($exists) {
        // Update the wishlist field to true if it already exists
        $stmt = $pdo->prepare("UPDATE userwishlist SET wishlist = TRUE WHERE user_id = ? AND designid = ?");
        $stmt->execute([$userId, $designid]);
        $response = ['success' => true, 'message' => 'Wishlist updated successfully.'];
    } else {
        // Insert a new record with wishlist set to true
        $stmt = $pdo->prepare("INSERT INTO userwishlist (user_id, designid, wishlist) VALUES (?, ?, TRUE)");
        $stmt->execute([$userId, $designid]);
        $response = ['success' => true, 'message' => 'Item added to wishlist successfully.'];
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
}

// Return the response as JSON
echo json_encode($response);
?>