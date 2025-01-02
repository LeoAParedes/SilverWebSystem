
<?php
session_start();
include("../../connect.php");

if (isset($_GET['designName'])) {
    $designName = $_GET['designName'];

   
    $stmt = $pdo->prepare("DELETE FROM userwishlist WHERE user_id = ? AND JSON_UNQUOTE(JSON_EXTRACT(wishlist, '$.designName')) = ?");
    $stmt->execute([$_SESSION['id'], $designName]);


    header("Location: userwishlist.php");
    exit();
} else {
  
    header("Location: userwishlist.php");
    exit();
}
?>