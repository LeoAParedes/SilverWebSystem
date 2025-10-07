
<?php
// /app/ajax/designs.php
session_start();
require_once '../connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'] ?? $_SESSION['id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

header('Content-Type: application/json');

try {
    switch($action) {
        case 'add':
            handleAddDesign($pdo, $userId);
            break;
            
        case 'edit':
            handleEditDesign($pdo);
            break;
            
        case 'delete':
            handleDeleteDesign($pdo);
            break;
            
        case 'get':
            handleGetDesign($pdo);
            break;
            
        case 'toggle_featured':
            handleToggleFeatured($pdo, $userId);
            break;
            
        case 'get_featured':
            handleGetFeatured($pdo);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}




function handleAddDesign($pdo, $userId) {
    $uploadDir = dirname(__DIR__) . '/assets/img/';
    $imagePath = null;
    $imageName = null;
    $uploadError = null;
    
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
            return;
        }
    }
    
    if (!is_writable($uploadDir)) {
        echo json_encode(['success' => false, 'message' => 'Upload directory is not writable. Check permissions.']);
        return;
    }
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['image']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP allowed.']);
            return;
        }
        
        if ($_FILES['image']['size'] > 10 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 10MB.']);
            return;
        }
        
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $baseFileName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($_FILES['image']['name'], PATHINFO_FILENAME));
        $fileName = $baseFileName . '_' . time() . '.' . $extension;
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = '/app/assets/img/' . $fileName;
            $imageName = $_POST['name'];
            
            if (!file_exists($targetFile)) {
                echo json_encode(['success' => false, 'message' => 'File upload failed - file not found after move']);
                return;
            }
        } else {
            $error = error_get_last();
            $uploadError = 'Failed to move uploaded file: ' . ($error['message'] ?? 'Unknown error');
            error_log('Upload error: ' . $uploadError);
            echo json_encode(['success' => false, 'message' => $uploadError]);
            return;
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE in form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        
        $errorCode = $_FILES['image']['error'];
        $errorMsg = $errorMessages[$errorCode] ?? 'Unknown upload error';
        echo json_encode(['success' => false, 'message' => 'Upload failed: ' . $errorMsg]);
        return;
    }

    $pdo->beginTransaction();
    
    try {
        $sql = "INSERT INTO design (sku, barcode, name, creation_date, description, details, edition, 
                unit_launch_price, category, size, stock_quantity, is_featured, is_active) 
                VALUES (:sku, :barcode, :name, :creation_date, :description, :details, :edition, 
                :price, :category, :size, :stock, :featured, :active)";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':sku' => $_POST['sku'] ?? 'AUTO-' . time(),
            ':barcode' => null,
            ':name' => $_POST['name'],
            ':creation_date' => date('Y-m-d H:i:s'),
            ':description' => $_POST['description'] ?? '',
            ':details' => $_POST['details'] ?? null,
            ':edition' => $_POST['edition'] ?? '1',
            ':price' => $_POST['unit_launch_price'] ?? 300,
            ':category' => $_POST['category'] ?? 'parche',
            ':size' => $_POST['size'] ?? '6cm',
            ':stock' => $_POST['stock_quantity'] ?? 100,
            ':featured' => isset($_POST['is_featured']) ? 1 : 0,
            ':active' => isset($_POST['is_active']) ? 1 : 0
        ]);
        
        if (!$result) {
            throw new Exception('Failed to insert design into database');
        }
        
        $designId = $pdo->lastInsertId();
        
        if ($imagePath) {
            $imgSql = "INSERT INTO images (image_path, name, designid) VALUES (:path, :name, :id)";
            $imgStmt = $pdo->prepare($imgSql);
            $imgResult = $imgStmt->execute([
                ':path' => $imagePath,
                ':name' => $imageName,
                ':id' => $designId
            ]);
            
            if (!$imgResult) {
                throw new Exception('Failed to save image record in database');
            }
        }
        
        if (isset($_POST['is_featured'])) {
            addToFeatured($pdo, $designId, $userId);
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Design added successfully!',
            'design_id' => $designId,
            'image_path' => $imagePath
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        
        if ($imagePath && isset($targetFile) && file_exists($targetFile)) {
            unlink($targetFile);
        }
        
        error_log('Error adding design: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}












// Edit Design Function - FIXED
function handleEditDesign($pdo) {
    $designId = $_POST['designid'];
    $uploadDir = '../assets/img/';
    
    // Get current design data
    $currentSql = "SELECT d.*, i.image_path 
                   FROM design d 
                   LEFT JOIN images i ON d.designid = i.designid 
                   WHERE d.designid = :id";
    $currentStmt = $pdo->prepare($currentSql);
    $currentStmt->execute([':id' => $designId]);
    $current = $currentStmt->fetch(PDO::FETCH_ASSOC);
    
    $imagePath = $current['image_path'];
    
    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $fileName = basename($_FILES['image']['name']);
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            // Store ABSOLUTE path from root
            $imagePath = '/app/assets/img/' . $fileName;
            
            // Delete old image file if exists
            if ($current['image_path']) {
                $oldFileName = basename($current['image_path']);
                $oldFile = $uploadDir . $oldFileName;
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
        }
    }

    // Update design table
    $sql = "UPDATE design SET 
            sku = :sku,
            name = :name,
            description = :description,
            details = :details,
            edition = :edition,
            unit_launch_price = :price,
            category = :category,
            size = :size,
            stock_quantity = :stock,
            is_featured = :featured,
            is_active = :active
            WHERE designid = :id";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':sku' => $_POST['sku'],
        ':name' => $_POST['name'],
        ':description' => $_POST['description'] ?? '',
        ':details' => $_POST['details'] ?? null,
        ':edition' => $_POST['edition'] ?? '1',
        ':price' => $_POST['unit_launch_price'],
        ':category' => $_POST['category'] ?? 'parche',
        ':size' => $_POST['size'] ?? '6cm',
        ':stock' => $_POST['stock_quantity'] ?? 100,
        ':featured' => isset($_POST['is_featured']) ? 1 : 0,
        ':active' => isset($_POST['is_active']) ? 1 : 0,
        ':id' => $designId
    ]);
    
    if ($result) {
        // Update or insert image record if image changed
        if ($imagePath != $current['image_path']) {
            if ($current['image_path']) {
                // Update existing image record
                $imgSql = "UPDATE images SET image_path = :path, name = :name WHERE designid = :id";
            } else {
                // Insert new image record
                $imgSql = "INSERT INTO images (image_path, name, designid) VALUES (:path, :name, :id)";
            }
            $imgStmt = $pdo->prepare($imgSql);
            $imgStmt->execute([
                ':path' => $imagePath, 
                ':name' => $_POST['name'],
                ':id' => $designId
            ]);
        }
        
        // Handle featured status - FIXED FOR PROPER TOGGLE
        $wasFeatured = $current['is_featured'] == 1;
        $nowFeatured = isset($_POST['is_featured']);
        
        if ($nowFeatured && !$wasFeatured) {
            addToFeatured($pdo, $designId, $userId);
        } elseif (!$nowFeatured && $wasFeatured) {
            removeFromFeatured($pdo, $designId);
        }
        
        echo json_encode(['success' => true, 'message' => 'Design updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update design']);
    }
}

// Delete Design Function
function handleDeleteDesign($pdo) {
    $designId = $_POST['id'] ?? $_GET['id'];
    
    // Get image path before deletion
    $sql = "SELECT i.image_path FROM images i WHERE i.designid = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $designId]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Delete from images table first (foreign key constraint)
    $deleteImgSql = "DELETE FROM images WHERE designid = :id";
    $deleteImgStmt = $pdo->prepare($deleteImgSql);
    $deleteImgStmt->execute([':id' => $designId]);
    
    // Delete from featured_designs if exists
    $deleteFeaturedSql = "DELETE FROM featured_designs WHERE design_id = :id";
    $deleteFeaturedStmt = $pdo->prepare($deleteFeaturedSql);
    $deleteFeaturedStmt->execute([':id' => $designId]);
    
    // Delete from design table
    $deleteSql = "DELETE FROM design WHERE designid = :id";
    $deleteStmt = $pdo->prepare($deleteSql);
    $result = $deleteStmt->execute([':id' => $designId]);
    
    if ($result) {
        // Delete image file
        if ($image && $image['image_path']) {
            $fileName = basename($image['image_path']);
            $filePath = '../assets/img/' . $fileName;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Design deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete design']);
    }
}

// Get Design Function - FIXED
function handleGetDesign($pdo) {
    $designId = $_GET['id'];
    
    $sql = "SELECT d.*, i.image_path 
            FROM design d
            LEFT JOIN images i ON d.designid = i.designid
            WHERE d.designid = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $designId]);
    $design = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($design) {
        // Convert NULL values to appropriate defaults
        $design['details'] = $design['details'] ?? '';
        $design['size'] = $design['size'] ?? '6cm';
        $design['stock_quantity'] = $design['stock_quantity'] ?? 100;
        
        echo json_encode(['success' => true, 'data' => $design]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Design not found']);
    }
}

// Toggle Featured Function - FIXED FOR PROPER TOGGLE
function handleToggleFeatured($pdo, $userId) {
    $designId = $_POST['id'];
    
    // Check current status
    $sql = "SELECT is_featured FROM design WHERE designid = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $designId]);
    $design = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($design) {
        $newStatus = $design['is_featured'] == 0 ? 1 : 0; // Proper toggle
        
        // Update design table
        $updateSql = "UPDATE design SET is_featured = :status WHERE designid = :id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([':status' => $newStatus, ':id' => $designId]);
        
        if ($newStatus == 1) {
            addToFeatured($pdo, $designId, $userId);
            $message = 'Design featured successfully';
        } else {
            removeFromFeatured($pdo, $designId);
            $message = 'Design removed from featured';
        }
        
        echo json_encode(['success' => true, 'message' => $message, 'featured' => $newStatus]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Design not found']);
    }
}

// Get Featured Designs Function
function handleGetFeatured($pdo) {
    $sql = "SELECT fd.*, d.name, d.description, d.unit_launch_price, i.image_path
            FROM featured_designs fd
            JOIN design d ON fd.design_id = d.designid
            LEFT JOIN images i ON d.designid = i.designid
            WHERE fd.is_active = 1 AND d.is_featured = 1
            ORDER BY fd.featured_order ASC, fd.created_at DESC";
    
    $stmt = $pdo->query($sql);
    $featured = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $featured]);
}

// Helper: Add to Featured
function addToFeatured($pdo, $designId, $userId) {
    // Check if already exists
    $checkSql = "SELECT featured_id, is_active FROM featured_designs WHERE design_id = :design_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':design_id' => $designId]);
    $existing = $checkStmt->fetch();
    
    if ($existing) {
        // Update existing to active
        $sql = "UPDATE featured_designs SET is_active = 1 WHERE design_id = :design_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':design_id' => $designId]);
    } else {
        // Insert new
        $sql = "INSERT INTO featured_designs (design_id, featured_section, is_active, created_by) 
                VALUES (:design_id, 'featured', 1, :user_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':design_id' => $designId, ':user_id' => $userId]);
    }
}

// Helper: Remove from Featured
function removeFromFeatured($pdo, $designId) {
    // Set to inactive instead of deleting
    $sql = "UPDATE featured_designs SET is_active = 0 WHERE design_id = :design_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':design_id' => $designId]);
}
?>

