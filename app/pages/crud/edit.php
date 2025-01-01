<?php
// Include the database connection
require 'addDesigns.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $creation_date = $_POST['creation_date'];
    $description = $_POST['description'];
    $details = $_POST['details'];
    $edition = (int)$_POST['edition']; // Cast to integer
    $unit_launch_price = $_POST['unit_launch_price'];

    $stmt = $pdo->prepare("UPDATE designs SET name=?, creation_date=?, description=?, details=?, edition=?, unit_launch_price=? WHERE id=?");
    $stmt->execute([$name, $creation_date, $description, $details, $edition, $unit_launch_price, $id]);

    header("Location: addDesigns.php");
    exit;
}
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM designs WHERE id=?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
?>
