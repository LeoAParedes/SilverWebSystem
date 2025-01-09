<?php

require 'Designs.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['designid'])) {
    $designid = $_POST['designid'];

    // First, fetch the image path associated with the design
    $stmt = $pdo->prepare("SELECT image_path FROM images WHERE designid = ?");
    $stmt->execute([$designid]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    // Delete the design from the design table
    $stmt = $pdo->prepare("DELETE FROM design WHERE designid = ?");
    $stmt->execute([$designid]);

    // If an image exists, delete it from the file system
    if ($image) {
        $imagePath = $image['image_path'];
        if (file_exists($imagePath)) {
            unlink($imagePath); // Delete the image file
        }
    }

    // Optionally, delete the image record from the images table
    $stmt = $pdo->prepare("DELETE FROM images WHERE designid = ?");
    $stmt->execute([$designid]);

    // Redirect or show a success message
    header("Location: designs.php?message=Design deleted successfully");
    exit();
}
?>