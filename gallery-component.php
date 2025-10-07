<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'app/connect.php';
require_once 'app/assets/utils/order-processing.php';

$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['id']);
$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

// Pagination settings
$itemsPerPage = 9; // 3x3 grid
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Filter settings
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch wishlist items
$wishlistItems = [];
if ($isLoggedIn && $userId) {
    $wishStmt = $pdo->prepare("SELECT designid FROM userwishlist WHERE user_id = ? AND wishlist = 1");
    $wishStmt->execute([$userId]);
    $wishlistItems = $wishStmt->fetchAll(PDO::FETCH_COLUMN);
}


// Fetch featured products from featured_designs table
$featuredQuery = "SELECT 
    d.*,
    i.image_path,
    d.unit_launch_price as price,
    (d.stock_quantity > 0) as in_stock,
    fd.featured_order,
    fd.featured_section
FROM featured_designs fd
INNER JOIN design d ON fd.design_id = d.designid
LEFT JOIN images i ON d.designid = i.designid
WHERE fd.is_active = 1 
    AND d.is_active = 1 
    AND i.image_path IS NOT NULL
GROUP BY d.designid
ORDER BY fd.featured_order ASC, fd.featured_from DESC
LIMIT 3";

$featuredStmt = $pdo->query($featuredQuery);
$featuredProducts = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);

// If no featured designs are marked, fallback to newest designs
if (empty($featuredProducts)) {
    $fallbackQuery = "SELECT 
        d.*,
        i.image_path,
        d.unit_launch_price as price,
        (d.stock_quantity > 0) as in_stock
    FROM design d
    LEFT JOIN images i ON d.designid = i.designid
    WHERE d.is_active = 1 AND i.image_path IS NOT NULL
    GROUP BY d.designid
    ORDER BY d.launch_date DESC
    LIMIT 3";
    
    $featuredStmt = $pdo->query($fallbackQuery);
    $featuredProducts = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Initialize where conditions array
$whereConditions = ['d.is_active = 1'];

// Add filter conditions
if ($filter === 'in-stock') {
    $whereConditions[] = 'd.stock_quantity > 0';
} elseif ($filter === 'out-stock') {
    $whereConditions[] = 'd.stock_quantity = 0';
} elseif ($filter === 'new') {
    $whereConditions[] = "d.launch_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
}

// Add search conditions
if (!empty($search)) {
    $whereConditions[] = "(d.name LIKE :search OR d.description LIKE :search OR d.sku LIKE :search)";
}

$whereClause = implode(' AND ', $whereConditions);

// Count total products
$countQuery = "SELECT COUNT(DISTINCT d.designid) as total FROM design d WHERE $whereClause";
$countStmt = $pdo->prepare($countQuery);
if (!empty($search)) {
    $searchParam = '%' . $search . '%';
    $countStmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
}
$countStmt->execute();
$totalProducts = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalProducts / $itemsPerPage);





// Fetch all products with pagination
$productsQuery = "SELECT 
    d.*,
    i.image_path,
    d.unit_launch_price as price,
    (d.stock_quantity > 0) as in_stock,
    CASE 
        WHEN d.launch_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 
        ELSE 0 
    END as is_new
FROM design d
LEFT JOIN images i ON d.designid = i.designid
WHERE $whereClause
GROUP BY d.designid
ORDER BY d.launch_date DESC
LIMIT :limit OFFSET :offset";

$productsStmt = $pdo->prepare($productsQuery);
$productsStmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
$productsStmt->bindParam(':offset', $offset, PDO::PARAM_INT);
if (!empty($search)) {
    $productsStmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
}



$productsStmt->execute();
$products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);

function fixImagePath($path) {
    if (!$path) return 'app/assets/img/placeholder.png';
    
    // Remove leading slash for relative path
    if (strpos($path, '/app/') === 0) {
        return substr($path, 1);
    }
    // Handle old format
    if (strpos($path, '/../../app/assets/') === 0) {
        return str_replace('/../../', '', $path);
    }
    return $path;
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">



<style>
/* Clean Modal Styles */
.product-modal .modal-body {
    padding: .4rem !important;
}

.product-modal-image {
    display: block !important;
    width: 100% !important;
    height: auto !important;
    max-width: 100% !important;
    margin: 0 auto !important;
}

/* Fix any spacing issues */
.modal-body > .row {
    margin-left: 0 !important;
    margin-right: 0 !important;
}

.modal-body .col-12 {
    padding-left: 0 !important;
    padding-right: 0 !important;
}
</style>


    
    <!-- CSS Files -->
    <link href="app/assets/css/output.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Gallery Component CSS (Separated) -->
    <link href="app/assets/css/gallery-component.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="gallery-wrapper">
        
        <!-- Gallery Header -->
        <section class="text-center mb-5">
            <h1 class="gallery-golden-title">Silver Web System Gallery</h1>
            <p class="gallery-golden-subtitle">
                Discover our exclusive collection of patches designed with golden proportions
            </p>
        </section>
        
<section class="container mb-5">
    <div class="featured-header">
        <h2 class="section-title">
            <i class="fas fa-star text-warning"></i> Featured Golden Collection
        </h2>
        <p class="text-muted text-center mb-4">
            Handpicked designs showcasing the essence of golden proportions
        </p>
    </div>
    
    <div class="featured-grid">
        <?php foreach($featuredProducts as $index => $product): 
            $isInWishlist = in_array($product['designid'], $wishlistItems);
            $imagePath = fixImagePath($product['image_path']);
            $animationDelay = ($index + 1) * 0.382; // Golden ratio animation
        ?>
        <div class="product-card featured-card" style="animation-delay: <?= $animationDelay ?>s;">
            <!-- Featured Badge -->
            <div class="featured-crown-badge">
                <i class="fas fa-crown"></i>
            </div>
            
            <div class="product-image-container">
                <img src="<?= htmlspecialchars($imagePath) ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>" 
                     class="product-image"
                     loading="lazy">
                
                <?php if($isLoggedIn): ?>
                <button class="wishlist-btn <?= $isInWishlist ? 'active' : '' ?>" 
                        onclick="toggleWishlist(<?= $product['designid'] ?>)">
                    <i class="<?= $isInWishlist ? 'fas' : 'far' ?> fa-heart text-danger"></i>
                </button>
                <?php endif; ?>
                
                <!-- Quick View Overlay -->
                <div class="featured-overlay">
                    <button class="btn btn-light btn-sm" 
                            data-bs-toggle="modal" 
                            data-bs-target="#productModal<?= $product['designid'] ?>">
                        <i class="fas fa-search-plus"></i> Quick View
                    </button>
                </div>
            </div>
            
            <div class="product-info">
                <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                <p class="product-sku text-muted small">SKU: <?= htmlspecialchars($product['sku']) ?></p>
                <p class="product-price">$<?= number_format($product['price'], 2) ?> MXN</p>
                
                <?php if($product['in_stock']): ?>
                    <span class="badge bg-success mb-2">In Stock</span>
                <?php else: ?>
                    <span class="badge bg-danger mb-2">Out of Stock</span>
                <?php endif; ?>
                
                <button class="btn w-100 btn-add-cart" 
                        data-bs-toggle="modal" 
                        data-bs-target="#orderModal<?= $product['designid'] ?>"
                        <?= !$product['in_stock'] ? 'disabled' : '' ?>>
                    <i class="fas fa-shopping-cart"></i>
                    <?= $product['in_stock'] ? 'Add to Cart' : 'Out of Stock' ?>
                </button>
            </div>
        </div>
        <?php renderOrderModal($product); ?>
        <?php endforeach; ?>
    </div>
    
    <?php if(empty($featuredProducts)): ?>
    <div class="text-center py-5">
        <i class="fas fa-star fa-3x text-muted mb-3"></i>
        <p class="text-muted">No featured products available at the moment</p>
    </div>
    <?php endif; ?>
</section>



        <!-- All Products Section with Pagination -->
        <section class="container">
            <div class="all-products-section">
                <h2 class="section-title">All Products</h2>
                
                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <button class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>" 
                                    onclick="applyFilter('all')">
                                <i class="fas fa-th"></i> All Products
                            </button>
                            <button class="filter-btn <?= $filter === 'in-stock' ? 'active' : '' ?>" 
                                    onclick="applyFilter('in-stock')">
                                <i class="fas fa-check-circle"></i> In Stock
                            </button>
                            <button class="filter-btn <?= $filter === 'out-stock' ? 'active' : '' ?>" 
                                    onclick="applyFilter('out-stock')">
                                <i class="fas fa-times-circle"></i> Out of Stock
                            </button>
                            <button class="filter-btn <?= $filter === 'new' ? 'active' : '' ?>" 
                                    onclick="applyFilter('new')">
                                <i class="fas fa-star"></i> New Arrivals
                            </button>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchInput" 
                                       placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                                <button class="btn btn-outline-secondary" onclick="searchProducts()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
         

       <!-- Results Counter -->
                <div class="results-counter">
                    Showing <?= $offset + 1 ?>-<?= min($offset + $itemsPerPage, $totalProducts) ?> 
                    of <?= $totalProducts ?> products
                </div>
                
                <!-- Products Grid 3x3 -->
                <div class="row g-4">
                    <?php foreach($products as $product): 
                        $isInWishlist = in_array($product['designid'], $wishlistItems);
                        $imagePath = fixImagePath($product['image_path']);
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="product-grid-card">
                            <!-- Stock Badge -->
                            <?php if($product['in_stock']): ?>
                                <span class="stock-badge in-stock">In Stock</span>
                            <?php else: ?>
                                <span class="stock-badge out-stock">Out of Stock</span>
                            <?php endif; ?>
                            
                            <!-- New Badge -->
                            <?php if($product['is_new']): ?>
                                <span class="badge bg-warning text-dark new-badge">NEW</span>
                            <?php endif; ?>
                            
                            <!-- Wishlist Button -->
                            <?php if($isLoggedIn): ?>
                            <button class="wishlist-btn <?= $isInWishlist ? 'active' : '' ?>" 
                                    onclick="toggleWishlist(<?= $product['designid'] ?>)">
                                <i class="<?= $isInWishlist ? 'fas' : 'far' ?> fa-heart text-danger"></i>
                            </button>
                            <?php endif; ?>
                            
                            <!-- Product Image -->
                            <div class="product-img-wrapper">
                                <img src="<?= htmlspecialchars($imagePath) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>" 
                                     class="product-img">
                            </div>
                            
                            <!-- Product Info -->
                            <div class="product-info-section">
                                <h3 class="product-name-grid"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="product-price-grid">$<?= number_format($product['price'], 2) ?> MXN</p>
                                
                                <!-- Action Buttons -->
                                <div class="product-actions">
                                    <button class="btn btn-expand" 
                                            onclick="expandProduct(<?= $product['designid'] ?>)"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#productModal<?= $product['designid'] ?>">
                                        <i class="fas fa-expand"></i>
                                    </button>
                                    <button class="btn btn-add-cart" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#orderModal<?= $product['designid'] ?>"
                                            <?= !$product['in_stock'] ? 'disabled' : '' ?>>
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
			
			<div class="modal fade product-modal" id="productModal<?= $product['designid'] ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><?= htmlspecialchars($product['name']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Image container with proper Bootstrap grid -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <img src="<?= htmlspecialchars($imagePath) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="product-modal-image w-100"
                             style="max-height: 400px; object-fit: contain;">
                    </div>
                </div>
                
                <!-- Product details in proper grid -->
                <div class="row">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">SKU: <?= htmlspecialchars($product['sku']) ?></p>
                        <p class="product-modal-price mb-3">$<?= number_format($product['price'], 2) ?> MXN</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="mb-2">
                            <span class="badge bg-<?= $product['in_stock'] ? 'success' : 'danger' ?> p-2">
                                <?= $product['in_stock'] ? 'In Stock: ' . $product['stock_quantity'] : 'Out of Stock' ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <?php if($product['description']): ?>
                <div class="mt-4">
                    <h6 class="fw-bold">Description</h6>
                    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <button class="btn btn-add-cart w-100 py-3" 
                            data-bs-toggle="modal" 
                            data-bs-target="#orderModal<?= $product['designid'] ?>"
                            data-bs-dismiss="modal"
                            <?= !$product['in_stock'] ? 'disabled' : '' ?>>
                        <i class="fas fa-shopping-cart"></i> 
                        <?= $product['in_stock'] ? 'Add to Cart' : 'Out of Stock' ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>                    



                    
                    <!-- Order Modal -->
                    <?php renderOrderModal($product); ?>
                    
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if($totalPages > 1): ?>
                <div class="pagination-container">
                    <nav>
                        <ul class="pagination">
                            <!-- Previous Button -->
                            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $currentPage - 1 ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            
                            <!-- Page Numbers -->
                            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if($i == 1 || $i == $totalPages || ($i >= $currentPage - 2 && $i <= $currentPage + 2)): ?>
                                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php elseif($i == $currentPage - 3 || $i == $currentPage + 3): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <!-- Next Button -->
                            <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $currentPage + 1 ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
                
                <!-- No Products Message -->
                <?php if(empty($products)): ?>
                <div class="no-products">
                    <i class="fas fa-box-open fa-4x"></i>
                    <h3>No products found</h3>
                    <p class="text-muted">Try adjusting your filters or search terms</p>
                    <button class="btn btn-add-cart" onclick="window.location.href='?'">
                        View All Products
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Wishlist Handler -->
    <script src="app/assets/js/wishlist-handler.js"></script>
    
    <!-- Gallery Scripts -->
    <script>
    // Pass wishlist items to JavaScript
    window.initialWishlistItems = <?= json_encode($wishlistItems) ?>;
    
    // Filter function
    function applyFilter(filterType) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('filter', filterType);
        urlParams.set('page', '1');
        window.location.href = '?' + urlParams.toString();
    }
    
    // Search function
    function searchProducts() {
        const searchTerm = document.getElementById('searchInput').value;
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('search', searchTerm);
        urlParams.set('page', '1');
        window.location.href = '?' + urlParams.toString();
    }
    
    // Enter key for search
    document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchProducts();
        }
    });
    
    // Expand product
    function expandProduct(designId) {
        console.log('Expanding product:', designId);
    }
    
    // Golden ratio animation on load
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.product-grid-card, .product-card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.618s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 50);
            }, index * 61.8);
        });
    });
    </script>


<script>
// Cart Functions
function adjustQty(productId, change) {
    const input = document.getElementById('qty' + productId);
    const max = parseInt(input.max);
    const newVal = parseInt(input.value) + change;
    
    if (newVal >= 1 && newVal <= max) {
        input.value = newVal;
        updateSubtotal(productId);
    }
}

function updateSubtotal(productId) {
    const qty = document.getElementById('qty' + productId).value;
    const priceElement = document.getElementById('subtotal' + productId);
    const basePrice = parseFloat(priceElement.dataset.price);
    const subtotal = (qty * basePrice).toFixed(2);
    priceElement.textContent = '$' + parseFloat(subtotal).toLocaleString('en-US', {minimumFractionDigits: 2});
}

function addToCart(productId) {
    const form = document.getElementById('cartForm' + productId);
    const formData = new FormData(form);
    
    // Show loading
    Swal.fire({
        title: 'Adding to cart...',
        didOpen: () => {
            Swal.showLoading();
        },
        allowOutsideClick: false
    });
    
    // Send AJAX request to order-processing.php
    fetch('app/assets/utils/order-processing.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Added to Cart!',
                text: data.message,
                showConfirmButton: true,
                confirmButtonText: 'View Cart',
                confirmButtonColor: '#138a36',
                showCancelButton: true,
                cancelButtonText: 'Continue Shopping',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to cart page
                    window.location.href = 'app/pages/orderprocessing/cart.php';
                } else {
                    // Update cart count in navbar if exists
                    updateCartCount(data.cart_count);
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('orderModal' + productId));
                    if (modal) {
                        modal.hide();
                    }
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to add to cart'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Something went wrong. Please try again.'
        });
    });
}

// Update cart count in navbar
function updateCartCount(count) {
    // Update all cart count badges
    document.querySelectorAll('.cart-count, .badge').forEach(el => {
        if (el.closest('a[href*="cart.php"]')) {
            el.textContent = count;
            el.style.display = count > 0 ? 'inline-block' : 'none';
        }
    });
}

// Initialize cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    // Get current cart count from session
    fetch('app/assets/utils/cart-session.php?action=get_count')
        .then(response => response.json())
        .then(data => {
            if (data.count !== undefined) {
                updateCartCount(data.count);
            }
        })
        .catch(error => console.log('Could not load cart count'));
});
</script>

</body>
</html>

