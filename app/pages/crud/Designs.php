<?php

include("../../header.php");
include("../../connect.php");

$stmt = $pdo->query("SELECT * FROM design");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add designs</title>

</head>
<body>
<div class="overlay" id="overlay"></div>
<section>
<div class="container mt-5 Edit-form" id="editForm"  >
    <h2>Edit Design</h2>
    <form method="POST" action="edit.php">
        <input type="hidden" name="id"  value="<?php echo $item['designid']; ?>" id="editId">
        <div class="mb-3">
            <label for="name" class="form-label" value="<?php echo $item['name']; ?>" >Name</label>
            <input type="text" class="form-control" id="editName" name="name" required>
        </div>
        <div class="mb-3">
            <label for="creation_date" class="form-label">Creation Date</label>
            <input type="date" class="form-control" id="editCreationDate" value="<?php echo $item['creation_date']; ?>" name="creation_date" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="editDescription" name="description" required></textarea>
        </div>
        <div class="mb-3">
            <label for="details" class="form-label">Details</label>
            <textarea class="form-control" id="editDetails" name="details" required><?php echo $item['details']; ?></textarea>
        </div>
        <div class="mb-3">
            <label for="edition" class="form-label">Edition</label>
            <input type="number" class="form-control" id="editEdition" name="edition" required>
        </div>
        <div class="mb-3">
            <label for="unit_launch_price" class="form-label">Unit Launch Price</label>
            <input type="number" step="0.01" class="form-control" id="editUnitLaunchPrice" name="unit_launch_price" required>
        </div>
        <button type="submit"  href="edit.php?id=<?= $item['designid'] ?>" class="btn btn-primary">Update Design</button>
    </form>
</div>
</section>

<section>

<div class="container mt-5 Create-form" id="createForm">
    <h2>Create Design</h2>
    <form method="POST" action="add.php">
        <input type="hidden" name="id" id="editId">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text card" class="form-control" id="editName" name="name" required>
        </div>
        <div class="mb-3">
            <label for="creation_date" class="form-label">Creation Date</label>
            <input type="date" class="form-control" id="editCreationDate" name="creation_date" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="editDescription" name="description" required></textarea>
        </div>
        <div class="mb-3">
            <label for="details" class="form-label">Details</label>
            <textarea class="form-control" id="editDetails" name="details" required></textarea>
        </div>
        <div class="mb-3">
            <label for="edition" class="form-label">Edition</label>
            <input type="number" class="form-control" id="editEdition" name="edition" required>
        </div>
        <div class="mb-3">
            <label for="unit_launch_price" class="form-label">Unit Launch Price</label>
            <input type="number" step="0.01" class="form-control" id="editUnitLaunchPrice" name="unit_launch_price" required>
        </div>
        <button type="submit" class="btn btn-primary">Create Design</button>
    </form>
</div>
</section>

<div class="container mb-5">
<button id="createBtn" class=" btn btn-success btn-sm">Create</button>

<section>
<h2 class="mt-5">Items List</h2>
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
                    <td><?= htmlspecialchars($item['designid']) ?></td>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= htmlspecialchars($item['creation_date']) ?></td>
                    <td><?= htmlspecialchars($item['description']) ?></td>
                    <td><?= htmlspecialchars($item['details']) ?></td>
                    <td><?= htmlspecialchars($item['edition']) ?></td>
                    <td><?= htmlspecialchars($item['unit_launch_price']) ?></td>
                    <td>
                        <a class="btn btn-warning btn-sm editBtn"
                        id="editBtn"
                                type="button"
                                data-id="<?= $item['designid'] ?>" 
                                data-name="<?= htmlspecialchars($item['name']) ?>" 
                                data-creation-date="<?= htmlspecialchars($item['creation_date']) ?>" 
                                data-description="<?= htmlspecialchars($item['description']) ?>" 
                                data-details="<?= htmlspecialchars($item['details']) ?>" 
                                data-edition="<?= htmlspecialchars($item['edition']) ?>" 
                                data-unit-launch-price="<?= htmlspecialchars($item['unit_launch_price']) ?>">Edit</a>
                        <a href="delete.php?id=<?= $item['designid'] ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
</div>


<script src="edit.js"></script>
</body>
</html>