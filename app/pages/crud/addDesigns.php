<?php
include("../../header.php");
include ("../../connect.php");


// Handle form submission for creating a new record
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $creation_date = $_POST['creation_date'];
    $description = $_POST['description'];
    $details = $_POST['details'];
    $edition = $_POST['edition'];
    $unit_launch_price = $_POST['unit_launch_price'];

    $stmt = $pdo->prepare("INSERT INTO designs (name, creation_date, description, details, edition, unit_launch_price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $creation_date, $description, $details, $edition, $unit_launch_price]);
}

// Fetch all records
$stmt = $pdo->query("SELECT * FROM designs");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Application</title>
</head>
<body>
<div class="overlay" id="overlay"></div>

<section >
<div class="container mt-5 Edit-form" id="editForm">
    <h2>Edit Design</h2>
    <form method="POST" action="edit.php">
        <input type="hidden" name="id" value="<?= htmlspecialchars($item['id']) ?>">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="creation_date" class="form-label">Creation Date</label>
            <input type="date" class="form-control" id="creation_date" name="creation_date" value="<?= htmlspecialchars($item['creation_date']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description"><?= htmlspecialchars($item['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="details" class="form-label">Details</label>
            <textarea class="form-control" id="details" name="details"><?= htmlspecialchars($item['details']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="edition" class="form-label">Edition</label>
            <input type="number" class="form-control" id="edition" name="edition" value="<?= htmlspecialchars($item['edition']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="unit_launch_price" class="form-label">Unit Launch Price</label>
            <input type="number" step="0.01" class="form-control" id="unit_launch_price" name="unit_launch_price" value="<?= htmlspecialchars($item['unit_launch_price']) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Update Design</button>
    </form>
</div>
</section>


<section  >
<div class="container mt-5 Create-form" id="createForm">
    <h2>Create New Item</h2>
    <form method="POST" action="addDesigns.php">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="creation_date" class="form-label">Creation Date</label>
            <input type="date" class="form-control" id="creation_date" name="creation_date" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" required id="description" name="description"></textarea>
        </div>
        <div class="mb-3">
            <label for="details" class="form-label">Details</label>
            <textarea class="form-control" id="details" name="details"></textarea>
        </div>
        <div class="mb-3">
            <label for="edition" class="form-label">Edition</label>
            <input type="text" required class="form-control" id="edition" name="edition">
        </div>
        <div class="mb-3">
            <label for="unit_launch_price" class="form-label">Unit Launch Price</label>
            <input type="number" step="0.01" class="form-control" id="unit_launch_price" name="unit_launch_price">
        </div>
        <button type="submit"  class="btn btn-primary">Add Design</button>
    </form>

    
</div>
</section>

<div class="container mb-5">

<section><h2 class="mt-5">Items List</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Creation Date</th>
                <th>Description</th>
                <th>Details</th>
                <th>Edition</th>
                <th>Unit Launch Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['id']) ?></td>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= htmlspecialchars($item['creation_date']) ?></td>
                    <td><?= htmlspecialchars($item['description']) ?></td>
                    <td><?= htmlspecialchars($item['details']) ?></td>
                    <td><?= htmlspecialchars($item['edition']) ?></td>
                    <td><?= htmlspecialchars($item['unit_launch_price']) ?></td>
                    <td>
                        <a  id="editBtn" type="submit" class="btn btn-warning btn-sm">Edit</a>
                        <a href="delete.php?id=<?= $item['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table></section>

</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/../../app/scripts.js"></script>

</body>
</html>