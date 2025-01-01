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

<section>
<div class="container mt-5 Edit-form" id="editBtn">
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