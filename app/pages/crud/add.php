<?php
ob_start();
require 'Designs.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $creation_date = $_POST['creation_date'];
    $description = $_POST['description'];
    $size = $_POST['size'];
    $category = $_POST['category'];
    $edition = (int)$_POST['edition'];
    $unit_launch_price = $_POST['unit_launch_price'];

    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $targetDirectory = __DIR__ . '/../../assets/img/';
        
        $imageFileName = basename($_FILES['image']['name']);
        $imagePath = $targetDirectory . $imageFileName;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            // Image uploaded successfully
        } else {
            echo "Error uploading the image.";
            exit;
        }
    }

    // Insert the new design into the database
    $stmt = $pdo->prepare("INSERT INTO design (name, creation_date, description, size, category, edition, unit_launch_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $creation_date, $description, $size, $category, $edition, $unit_launch_price]);
    
    // Get the last inserted design ID
    $designId = $pdo->lastInsertId();

    // If an image was uploaded, insert it into the images table
    if ($imagePath) {
        $imageFileDirectory = '/app/assets/img/' . $imageFileName; // Relative path for web access
        $stmt = $pdo->prepare("INSERT INTO images (image_path, name, designid) VALUES (?, ?, ?)");
        $stmt->execute([$imageFileDirectory, $name, $designId]);
    }

    header("Location: designs.php?message=Design added successfully");
    exit;
}

ob_end_flush();
?>