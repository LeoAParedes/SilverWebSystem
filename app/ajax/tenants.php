<?php
session_start();
require_once '../connect.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
header('Content-Type: application/json');

function testDatabaseConnection($dbName) {
    try {
        $testPdo = new PDO(
            "mysql:host=localhost;dbname=$dbName;charset=utf8mb4",
            'silverweb',
            'goldenleon#',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 2
            ]
        );
        
        $tables = $testPdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        return [
            'success' => true,
            'accessible' => true,
            'table_count' => count($tables),
            'tables' => $tables
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'accessible' => false,
            'error' => 'Cannot connect to database. Ensure it exists and silverweb has access.'
        ];
    }
}

switch($action) {
    case 'getTenants':
        try {
            $stmt = $pdo->query("
                SELECT t.*, u.username as owner_name, u.email as owner_email,
                       (SELECT COUNT(*) FROM tenant_users WHERE tenant_id = t.tenant_id) as user_count
                FROM tenants t
                LEFT JOIN users u ON t.owner_user_id = u.id
                ORDER BY t.created_at DESC
            ");
            $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($tenants as &$tenant) {
                if (!empty($tenant['database_name'])) {
                    $dbTest = testDatabaseConnection($tenant['database_name']);
                    $tenant['database_accessible'] = $dbTest['accessible'];
                    $tenant['table_count'] = $dbTest['table_count'] ?? 0;
                } else {
                    $tenant['database_accessible'] = false;
                    $tenant['table_count'] = 0;
                }
            }
            
            echo json_encode(['success' => true, 'tenants' => $tenants]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
case 'validateDatabase':
    $database_name = $_GET['database_name'] ?? '';
    
    if (empty($database_name)) {
        echo json_encode(['exists' => false, 'accessible' => false, 'message' => 'Database name is empty']);
        exit;
    }
    
    try {
        // Create a NEW connection to test the database
        $testPdo = new PDO(
            "mysql:host=localhost;charset=utf8mb4",
            'silverweb',
            'goldenleon#',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Check if database exists
        $stmt = $testPdo->prepare("SHOW DATABASES LIKE ?");
        $stmt->execute([$database_name]);
        $exists = $stmt->rowCount() > 0;
        
        if (!$exists) {
            echo json_encode([
                'exists' => false, 
                'accessible' => false, 
                'message' => 'Database does not exist'
            ]);
            exit;
        }
        
        // Try to use the database
        $testPdo->exec("USE `$database_name`");
        
        // Get table count
        $stmt = $testPdo->query("SHOW TABLES");
        $table_count = $stmt->rowCount();
        
        // Test write permissions by creating and dropping a test table
        $testTableName = 'test_permission_' . time();
        $testPdo->exec("CREATE TABLE IF NOT EXISTS `$testTableName` (id INT PRIMARY KEY)");
        $testPdo->exec("DROP TABLE IF EXISTS `$testTableName`");
        
        echo json_encode([
            'exists' => true,
            'accessible' => true,
            'table_count' => $table_count,
            'message' => 'Database is accessible'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'exists' => false,
            'accessible' => false,
            'message' => 'Database error: ' . $e->getMessage(),
            'error_code' => $e->getCode()
        ]);
    }
    break;        
    



    case 'createUser':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$data['username'], $data['email']]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
                exit;
            }
            
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password, role_id, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $data['username'],
                $data['email'],
                $hashedPassword,
                $data['role_id'] ?? 3
            ]);
            
            $userId = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id, assigned_by, assigned_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$userId, $data['role_id'] ?? 3, $_SESSION['id']]);
            
            echo json_encode(['success' => true, 'user_id' => $userId]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
        
    case 'createTenant':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $dbName = $data['database_name'] ?? '';
        
        if (!empty($dbName)) {
            $connectionTest = testDatabaseConnection($dbName);
            if (!$connectionTest['accessible']) {
                echo json_encode([
                    'success' => false, 
                    'message' => "Cannot connect to database '$dbName'. Please verify it exists and silverweb user has access."
                ]);
                exit;
            }
        }
        
        $pdo->beginTransaction();
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO tenants (tenant_name, tenant_code, database_name, owner_user_id, 
                                   subscription_type, domain, max_users, storage_limit, max_databases, 
                                   is_active, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
            ");
            $stmt->execute([
                $data['name'],
                $data['code'],
                $dbName ?: null,
                $data['owner_id'],
                $data['subscription'],
                $data['domain'] ?? null,
                $data['max_users'] ?? 5,
                $data['max_storage'] ?? 1,
                $data['max_databases'] ?? 1
            ]);
            
            $tenantId = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("
                INSERT INTO tenant_users (tenant_id, user_id, tenant_role, joined_at) 
                VALUES (?, ?, 'owner', NOW())
            ");
            $stmt->execute([$tenantId, $data['owner_id']]);
            
            $pdo->commit();
            
            $response = [
                'success' => true,
                'tenant_id' => $tenantId,
                'message' => 'Tenant created successfully!'
            ];
            
            if (!empty($dbName) && $connectionTest['accessible']) {
                $response['database_info'] = [
                    'database_name' => $dbName,
                    'accessible' => true,
                    'table_count' => $connectionTest['table_count'] ?? 0
                ];
            }
            
            echo json_encode($response);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
        
    case 'updateDatabase':
        $data = json_decode(file_get_contents('php://input'), true);
        $tenantId = $data['tenant_id'] ?? 0;
        $dbName = $data['database_name'] ?? '';
        
        if (empty($dbName)) {
            echo json_encode(['success' => false, 'message' => 'Database name required']);
            exit;
        }
        
        $connectionTest = testDatabaseConnection($dbName);
        if (!$connectionTest['accessible']) {
            echo json_encode([
                'success' => false, 
                'message' => "Cannot connect to database '$dbName'. Please verify it exists and silverweb user has access."
            ]);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE tenants SET database_name = ? WHERE tenant_id = ?");
            $stmt->execute([$dbName, $tenantId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Database configuration updated successfully',
                'database_info' => [
                    'database_name' => $dbName,
                    'accessible' => true,
                    'table_count' => $connectionTest['table_count'] ?? 0
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
        
    case 'deleteTenant':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $pdo->beginTransaction();
        
        try {
            $stmt = $pdo->prepare("SELECT database_name FROM tenants WHERE tenant_id = ?");
            $stmt->execute([$data['tenant_id']]);
            $tenant = $stmt->fetch();
            
            $stmt = $pdo->prepare("DELETE FROM tenant_users WHERE tenant_id = ?");
            $stmt->execute([$data['tenant_id']]);
            
            $stmt = $pdo->prepare("DELETE FROM tenants WHERE tenant_id = ?");
            $stmt->execute([$data['tenant_id']]);
            
            $pdo->commit();
            
            $message = 'Tenant deleted successfully';
            if (!empty($tenant['database_name'])) {
                $message .= ". Database '{$tenant['database_name']}' configuration removed (database itself not deleted).";
            }
            
            echo json_encode(['success' => true, 'message' => $message]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
        
    case 'getUsers':
        try {
            $stmt = $pdo->query("SELECT id, username, email FROM users ORDER BY username");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'users' => $users]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>

