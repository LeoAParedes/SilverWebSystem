<?php
ob_start();
require 'Designs.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['designid'];
    $name = $_POST['name'];
    $creation_date = $_POST['creation_date'];
    $description = $_POST['description'];
    $size = $_POST['size'];
    $category = $_POST['category'];
    $edition = (int)$_POST['edition'];
    $unit_launch_price = $_POST['unit_launch_price'];

    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $targetDirectory = __DIR__.'/../../assets/img/';
        $imageFileName = basename($_FILES['image']['name']);
        $imagePath = $targetDirectory . $imageFileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
        } else {
            echo "Error uploading the image.";
            exit;
        }
    }

    $stmt = $pdo->prepare("UPDATE design SET name=?, creation_date=?, description=?, size=?, category=?, edition=?, unit_launch_price=? WHERE designid=?");
    $stmt->execute([$name, $creation_date, $description, $size, $category, $edition, $unit_launch_price, $id]);
   
    if ($imagePath) {
        $stmt = $pdo->prepare("INSERT INTO images (image_path, name, designid) VALUES (?, ?, ?)");
        $stmt->execute([$imagePath, $name, $id]);
    }

    header("Location: designs.php?message=Design updated successfully");
    exit;
}

$id = $_GET['designid'];
$stmt = $pdo->prepare("SELECT * FROM design WHERE designid=?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
ob_end_flush(); 
