
<?php
include("../../header.php");
include ("../../connect.php");


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $designName = $_POST['designName'];
    $checked = isset($_POST['checked']) ? 1 : 0;
    $timestamp = date('Y-m-d H:i:s');

    $wishlistData = json_encode([
        'designName' => $designName,
        'checked' => $checked,
        'timestamp' => $timestamp
    ]);

    // Prepare the insert statement
    $stmt = $pdo->prepare("INSERT INTO userwishlist (user_id, wishlist) VALUES (?, ?)");
    $stmt->execute([$_SESSION['id'], $wishlistData]);

    header("Location: userwishlist.php");
    exit();
}


// Fetch all wishlist records
$stmt = $pdo->prepare("SELECT * FROM userwishlist WHERE user_id = ?");
$stmt->execute([$_SESSION['id']]);
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

<section>
<div class="container mt-5 Create-wish" >
    <h2>Create New Wishlist Item</h2>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="designName" class="form-label">Design Name</label>
            <input type="text" class="form-control" id="designName" name="designName" required>
        </div>
        <div class="mb-3">
            <label for="checked" class="form-label">Checked</label>
            <input type="checkbox" id="checked" name="checked">
        </div>
        <button type="submit"  class="btn btn-primary">Add to Wishlist</button>
    </form>
</div>
</section>

<div class="container mb-5">
<section>
    <h2 class="mt-5">Wishlist Items</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Wishlist Data</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['id']) ?></td>
                    <td><?= htmlspecialchars($item['wishlist']) ?></td>
                    <td>
                        <a href="deletewish.php?id=<?= $item['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/../../app/scripts.js"></script>

</body>
</html>