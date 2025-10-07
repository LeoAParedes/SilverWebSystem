<?php
session_start();
include("../../connect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
    header("Location: ../../login.php");
    exit();
}

if (!isset($_SESSION['id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header('Location: ../../../index.php');
    exit('Access denied. Super Admin only.');
}


// Get designs with their images
$stmt = $pdo->query("
    SELECT d.*, i.image_path 
    FROM design d 
    LEFT JOIN images i ON d.designid = i.designid 
    ORDER BY d.designid DESC
");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Designs - Silver Web System</title>
    
    <!-- Bootstrap CSS (MUST BE FIRST) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Forest Green Design System CSS -->
    <link rel="stylesheet" href="../../assets/css/output.css">
    
    <style>
    /* ================================================
       Designs Management Styles - Forest Green Theme
       ================================================ */

    /* Page Container */
    .designs-container {
        padding-top: 90px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f9fcfa 0%, #e4f2e9 100%);
    }

    /* Main Container */
    .container.main-content {
        margin-top: 100px; /* Account for fixed navbar */
        padding: 2rem;
    }

    /* Page Title */
    .page-title {
        color: #34403a;
        font-size: 2.618rem;
        font-weight: 700;
        margin-bottom: 2rem;
        position: relative;
        padding-bottom: 1rem;
    }

    .page-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100px;
        height: 3px;
        background: linear-gradient(90deg, #138a36, #18ff6d);
    }

    /* Action Buttons Container */
    .action-buttons {
        margin-bottom: 2rem;
    }

    /* Add Design Button */
    #addDesignBtn {
        background: linear-gradient(135deg, #18ff6d 0%, #138a36 100%);
        color: white;
        padding: 0.75rem 2rem;
        font-size: 1.1rem;
        border-radius: 50px;
        border: none;
        box-shadow: 0 4px 15px rgba(24, 255, 109, 0.3);
        transition: all 0.382s ease;
    }

    #addDesignBtn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 25px rgba(24, 255, 109, 0.4);
    }

    /* Table Styles */
    .designs-table {
        background: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(52, 64, 58, 0.1);
    }

    .designs-table thead {
        background: linear-gradient(135deg, #34403a 0%, #34403a 100%);
        color: #e4f2e9;
    }

    .designs-table thead th {
        padding: 1.2rem 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.875rem;
        border: none;
    }

    .designs-table tbody tr {
        border-bottom: 1px solid #e4f2e9;
        transition: all 0.382s ease;
    }

    .designs-table tbody tr:hover {
        background: linear-gradient(135deg, rgba(228, 242, 233, 0.3) 0%, rgba(228, 242, 233, 0.1) 100%);
    }

    .designs-table tbody td {
        padding: 1rem;
        vertical-align: middle;
        color: #34403a;
    }

    /* Image Thumbnail */
    .design-thumb {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 0.5rem;
        border: 2px solid #e4f2e9;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .design-thumb:hover {
        border-color: #18ff6d;
        transform: scale(1.1);
    }

    /* Action Buttons */
    .btn-action {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        margin: 0 0.125rem;
    }

    /* Price Display */
    .price-display {
        font-weight: 600;
        color: #138a36;
    }

    /* Stock Badge */
    .stock-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .stock-badge.in-stock {
        background-color: rgba(24, 255, 109, 0.2);
        color: #138a36;
    }

    .stock-badge.low-stock {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ff9800;
    }

    /* Featured Star */
    .toggle-featured-btn {
        transition: all 0.3s ease;
    }

    .toggle-featured-btn.active {
        color: #ffc107;
    }

    /* Loading Spinner Override */
    .spinner-border {
        border-width: 3px;
    }

    /* Responsive Table */
    @media (max-width: 768px) {
        .designs-table {
            font-size: 0.875rem;
        }
        
        .designs-table thead {
            display: none;
        }
        
        .designs-table tbody td {
            display: block;
            padding: 0.5rem;
            text-align: right;
        }
        
        .designs-table tbody td:before {
            content: attr(data-label);
            float: left;
            font-weight: 600;
            color: #34403a;
        }
        
        .designs-table tbody tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid #e4f2e9;
            border-radius: 0.5rem;
            padding: 0.5rem;
        }
    }
    </style>
</head>
<body>
    <!-- Include Navigation Menu -->
    <?php include("../../assets/navmenu/navmenu.php"); ?>
    
    <!-- Main Content Container -->
    <div class="container main-content">
        <h1 class="page-title">
            <i class="fas fa-palette"></i> Design Management
        </h1>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <button id="addDesignBtn" class="btn">
                <i class="fas fa-plus-circle"></i> Add New Design
            </button>
        </div>
        
        <!-- Designs Table -->
        <div class="table-responsive">
            <table class="table designs-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>



			<?php foreach ($items as $item): ?>
    <?php
   
    $imagePath = '';
    $imageUrl = '';
    
    if (!empty($item['image_path'])) {
        // Database has /app/assets/img/filename.png format
        $filename = basename($item['image_path']);
        
        // From /app/pages/crud/ to /app/assets/img/ = ../../assets/img/
        $imageUrl = '../../assets/img/' . $filename;
        
        // Verify file exists
        $fullPath = __DIR__ . '/../../assets/img/' . $filename;
        
        if (!file_exists($fullPath)) {
            // File doesn't exist, clear the URL
            $imageUrl = '';
        }
    }


 
    // Determine stock status
    $stockClass = 'in-stock';
    if ($item['stock_quantity'] <= 10) {
        $stockClass = 'low-stock';
    }
    ?>
    <tr>
        <td data-label="Image">
            <?php if($imageUrl): ?>
                <img src="<?= htmlspecialchars($imageUrl) ?>" 
                     alt="<?= htmlspecialchars($item['name']) ?>"
                     class="design-thumb view-image-btn"
                     data-image="<?= htmlspecialchars($imageUrl) ?>"
                     data-name="<?= htmlspecialchars($item['name']) ?>"
                     style="cursor: pointer;"
                     onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI1MCIgaGVpZ2h0PSI1MCI+PHJlY3Qgd2lkdGg9IjUwIiBoZWlnaHQ9IjUwIiBmaWxsPSIjZTRmMmU5Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJzYW5zLXNlcmlmIiBmb250LXNpemU9IjEyIiBmaWxsPSIjMzQ0MDNhIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+Tm8gSW1hZ2U8L3RleHQ+PC9zdmc+';">
            <?php else: ?>
                <div class="design-thumb" style="background: #e4f2e9; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-image text-muted"></i>
                </div>
            <?php endif; ?>
        </td>
        <td data-label="ID"><?= htmlspecialchars($item['designid']) ?></td>
        <td data-label="Name">
            <strong><?= htmlspecialchars($item['name']) ?></strong>
        </td>
        <td data-label="SKU"><?= htmlspecialchars($item['sku']) ?></td>
        <td data-label="Category">
            <span class="badge bg-secondary"><?= htmlspecialchars($item['category'] ?? 'Parche') ?></span>
        </td>
        <td data-label="Price">
            <span class="price-display">$<?= number_format($item['unit_launch_price'], 2) ?></span>
        </td>
        <td data-label="Stock">
            <span class="stock-badge <?= $stockClass ?>">
                <?= $item['stock_quantity'] ?> units
            </span>
        </td>
        <td data-label="Featured">
            <button class="btn btn-sm <?= $item['is_featured'] ? 'btn-warning' : 'btn-outline-warning' ?> toggle-featured-btn btn-action" 
                    data-id="<?= $item['designid'] ?>"
                    title="Toggle Featured">
                <i class="<?= $item['is_featured'] ? 'fas' : 'far' ?> fa-star"></i>
            </button>
        </td>
        <td data-label="Actions">
            <?php if($imageUrl): ?>
                <button class="btn btn-sm btn-info view-image-btn btn-action" 
                        data-image="<?= htmlspecialchars($imageUrl) ?>"
                        data-name="<?= htmlspecialchars($item['name']) ?>"
                        title="View Image">
                    <i class="fas fa-eye"></i>
                </button>
            <?php else: ?>
                <button class="btn btn-sm btn-secondary btn-action" disabled title="No Image">
                    <i class="fas fa-eye-slash"></i>
                </button>
            <?php endif; ?>
            
            <button class="btn btn-sm btn-warning edit-design-btn btn-action" 
                    data-id="<?= $item['designid'] ?>"
                    title="Edit Design">
                <i class="fas fa-edit"></i>
            </button>
            
            <button class="btn btn-sm btn-danger delete-design-btn btn-action" 
                    data-id="<?= $item['designid'] ?>"
                    data-name="<?= htmlspecialchars($item['name']) ?>"
                    title="Delete Design">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
<?php endforeach; ?>




                </tbody>
            </table>
        </div>
    </div>
    
    <!-- jQuery (Required for Bootstrap and Gallery Functions) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS Bundle (MUST BE BEFORE galleryfunctions.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Gallery Functions (MUST BE AFTER Bootstrap) -->
    <script src="../../assets/js/galleryfunctions.js"></script>
    
    <!-- Page Specific Scripts -->
    <script>
    // Additional page-specific functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Show success/error messages if any
        const urlParams = new URLSearchParams(window.location.search);
        const success = urlParams.get('success');
        
        if (success === 'created') {
            GalleryManager.showNotification('Design created successfully!', 'success');
        } else if (success === 'updated') {
            GalleryManager.showNotification('Design updated successfully!', 'success');
        } else if (success === 'deleted') {
            GalleryManager.showNotification('Design deleted successfully!', 'success');
        }
        
        // Clean URL after showing message
        if (success) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
    </script>
</body>
</html>

