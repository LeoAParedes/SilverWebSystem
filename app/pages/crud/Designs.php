<?php

include("../../header.php");
include("../../connect.php");

$stmt = $pdo->query("SELECT * FROM design");
$designs = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (isset($_GET['designid'])) {
    $id = $_GET['designid'];
    $stmt = $pdo->prepare("SELECT * FROM design WHERE designid=?");
    $stmt->execute([$id]);
    $design = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add designs</title>
    <script src="edit.js"></script>
</head>
<body>
<div class="overlay" id="overlay"></div>
<section>
<div class="container mt-5 Edit-form" id="editForm">
    <h2>Edit Design</h2>
    <form method="POST" action="edit.php" enctype="multipart/form-data">
        <input type="hidden" name="designid" id="editId" value="<?php echo isset($item['designid']) ? htmlspecialchars($item['designid']) : ''; ?>">
        
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="editName" name="name" value="<?php echo isset($item['name']) ? htmlspecialchars($item['name']) : ''; ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="creation_date" class="form-label">Creation Date</label>
            <input type="date" class="form-control" id="editCreationDate" name="creation_date" value="<?php echo isset($item['creation_date']) ? htmlspecialchars($item['creation_date']) : ''; ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="editDescription" name="description" required><?php echo isset($item['description']) ? htmlspecialchars($item['description']) : ''; ?></textarea>
        </div>
        
        <div class="mb-3">
            <label for="size" class="form-label">Size</label>
            <input type="text" class="form-control" id="size" name="size" value="<?php echo isset($item['size']) ? htmlspecialchars($item['size']) : ''; ?>">
        </div> 

        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <input type="text" class="form-control" id="category" name="category" value="<?php echo isset($item['category']) ? htmlspecialchars($item['category']) : ''; ?>">
        </div>

        <div class="mb-3">
            <label for="edition" class="form-label">Edition</label>
            <input type="number" class="form-control" id="edition" name="edition" value="<?php echo isset($item['edition']) ? htmlspecialchars($item['edition']) : ''; ?>" required>
        </div>

        <div class="mb-3">
            <label for="unit_launch_price" class="form-label">Unit Launch Price</label>
            <input type="text" class="form-control" id="editUnitLaunchPrice" name="unit_launch_price" value="<?php echo isset($item['unit_launch_price']) ? htmlspecialchars($item['unit_launch_price']) : ''; ?>" required>
        </div>

        <div class="mb-3">
            <label for="imageUpload" class="form-label">Upload Image</label>
            <input type="file" class="form-control" id="imageUpload" name="image" accept="image/*">
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
</section>
<section >
<div class="container justify-content-center col-3 " >
    <div class="<?= isset($_GET['id']) ? 'container my-5 show mx-4 ' : 'container my-5 d-none' ?>" id="deleteForm">
    <h2>Confirm Deletion</h2>
    <form method="POST" action="deleteDesign.php">
        <input type="hidden" name="designid" id="deleteId" value="<?= isset($_GET['id']) ? htmlspecialchars($_GET['id']) : ''; ?>">
        <p>Are you sure you want to delete this design?</p>
        <button type="submit" class="btn btn-danger">Delete</button>
        <button type="button" class="btn btn-secondary" onclick="$('#deleteForm').hide(); $('#overlay').hide();">Cancel</button>
    </form>
</div>
</div>
    </section>
    <section>
    <div class="container Create-form" id="createForm">
        <h2>Create Design</h2>
        <form method="POST" action="add.php" enctype="multipart/form-data" class="form">
            <input type="hidden" name="id" id="editId">

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="editName" name="name" required>
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
                <label for="size" class="form-label">Size</label>
                <input type="text" class="form-control" id="size" name="size" required>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <input type="text" class="form-control" id="category" name="category" required>
            </div>

            <div class="mb-3">
                <label for="edition" class="form-label">Edition</label>
                <input type="number" class="form-control" id="edition" name="edition" required>
            </div>

            <div class="mb-3">
                <label for="unit_launch_price" class="form-label">Unit Launch Price</label>
                <input type="number" step="0.01" class="form-control" id="editUnitLaunchPrice" name="unit_launch_price" required>
            </div>

            <div class="mb-3">
                <label for="imageUpload" class="form-label">Upload Image</label>
                <input type="file" class="form-control" id="imageUpload" name="image" accept="image/*" required>
            </div>

            <button type="submit" class="btn btn-primary">Create Design</button>
        </form>
    </div>

    <div class="container mb-5">
        <button id="createBtn" class="mt-5 btn btn-success btn-sm">Create</button>
    </div>
</section>

<div class="container">
    <h1>Designs</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Creation Date</th>
                <th>Description</th>
                <th>Size</th>
                <th>Category</th>
                <th>Edition</th>
                <th>Unit Launch Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($designs as $design): ?>
                <tr>
                    <td><?php echo htmlspecialchars($design['designid']); ?></td>
                    <td><?php echo htmlspecialchars($design['name']); ?></td>
                    <td><?php echo htmlspecialchars($design['creation_date']); ?></td>
                    <td><?php echo htmlspecialchars($design['description']); ?></td>
                    <td><?php echo htmlspecialchars($design['size']); ?></td>
                    <td><?php echo htmlspecialchars($design['category']); ?></td>
                    <td><?php echo htmlspecialchars($design['edition']); ?></td>
                    <td><?php echo htmlspecialchars($design['unit_launch_price']); ?></td>
                    <td>
                        <a class="editBtn btn btn-warning btn-sm " href="edit.php?id=<?= $item['designid'] ?>"
                            data-designid="<?php echo $design['designid']; ?>" 
                            data-name="<?php echo htmlspecialchars($design['name']); ?>" 
                            data-creation_date="<?php echo htmlspecialchars($design['creation_date']); ?>" 
                            data-description="<?php echo htmlspecialchars($design['description']); ?>" 
                            data-size="<?php echo htmlspecialchars($design['size']); ?>" 
                            data-category="<?php echo htmlspecialchars($design['category']); ?>" 
                            data-edition="<?php echo htmlspecialchars($design['edition']); ?>" 
                            data-unit_launch_price="<?php echo htmlspecialchars($design['unit_launch_price']); ?>">Edit</a>
                            <a href="deleteDesign.php?id=<?= $design['designid'] ?>"  class="btn btn-danger btn-sm deleteBtn">Delete</a>
                        
                        </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</section>
</div>


</body>
</html>

