<?php
session_start();
include("../../connect.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $designName = $_POST['designName'];
    $checked = isset($_POST['checked']) ? 1 : 0;
    $timestamp = date('Y-m-d H:i:s');
    $wishlistData = json_encode([
        'designName' => $designName,
        'checked' => $checked,
        'timestamp' => $timestamp
    ]);
    $stmt = $pdo->prepare("UPDATE userwishlist SET wishlist = ? WHERE user_id = ? AND JSON_UNQUOTE(JSON_EXTRACT(wishlist, '$.designName')) = ?");
    $stmt->execute([$wishlistData, $_SESSION['user_id'], $id]);
    header("Location: index.php");
    exit();
}
?>