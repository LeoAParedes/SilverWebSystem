<?php

require 'Designs.php';


// Handle form submission for creating a new record
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $creation_date = $_POST['creation_date'];
    $description = $_POST['description'];
    $details = $_POST['details'];
    $edition = $_POST['edition'];
    $unit_launch_price = $_POST['unit_launch_price'];

    $stmt = $pdo->prepare("INSERT INTO design (name, creation_date, description, details, edition, unit_launch_price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $creation_date, $description, $details, $edition, $unit_launch_price]);
   
}

// Fetch all records

?>