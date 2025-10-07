<?php
// app/ajax/load-products.php - AJAX endpoint for loading more products
session_start();
require_once '../connect.php';

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$productsPerPage = 12;
$offset = ($page - 1) * $productsPerPage;

// Get products with images
$query = "SELECT 
    d.*,
    i.image_path,
    ROUND(d.unit_launch_price * d.golden_price_multiplier, 2) as golden_price,
    (d.stock_quantity > 0) as in_stock
FROM design d
LEFT JOIN images i ON d.designid = i.designid
WHERE d.is_active = 1
GROUP BY d.designid
ORDER BY d.launch_date DESC
LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':limit', $productsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check wishlist
$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
$wishlistItems = [];
if ($userId) {
    $wishStmt = $pdo->prepare("SELECT designid FROM userwishlist WHERE user_id = ? AND wishlist = 1");
    $wishStmt->execute([$userId]);
    $wishlistItems = $wishStmt->fetchAll(PDO::FETCH_COLUMN);
}

// Generate HTML
ob_start();
foreach($products as $product): 
    $isInWishlist = in_array($product['designid'], $wishlistItems);
    $imagePath = $product['image_path'] ?? '/assets/img/placeholder.png';
    if (strpos($imagePath, '/../../') === 0) {
        $imagePath = str_replace('/../../', '/', $imagePath);
    }
?>
<div class="product-card" data-product-id="<?php echo $product['designid']; ?>">
    <!-- Same product card HTML as in main component -->
    <!-- ... (copy the product card HTML from above) ... -->
</div>
<?php endforeach;

$html = ob_get_clean();
echo json_encode(['success' => true, 'html' => $html]);
?>

