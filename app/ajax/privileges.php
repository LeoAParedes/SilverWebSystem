<?php
session_start();
require_once '../connect.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
header('Content-Type: application/json');

switch($action) {
    case 'getRoles':
        $stmt = $pdo->query("SELECT * FROM roles ORDER BY role_name");
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'roles' => $roles]);
        break;
        
    case 'getPermissions':
        $stmt = $pdo->query("SELECT * FROM permissions ORDER BY module, permission_name");
        $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'permissions' => $permissions]);
        break;
        
    case 'getMatrix':
        $stmt = $pdo->query("
            SELECT rp.role_id, rp.permission_id 
            FROM role_permissions rp
        ");
        $matrix = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!isset($matrix[$row['role_id']])) {
                $matrix[$row['role_id']] = [];
            }
            $matrix[$row['role_id']][] = $row['permission_id'];
        }
        echo json_encode(['success' => true, 'matrix' => $matrix]);
        break;
        
    case 'updatePermission':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data['granted']) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        } else {
            $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?");
        }
        $stmt->execute([$data['role_id'], $data['permission_id']]);
        echo json_encode(['success' => true]);
        break;
        
    case 'createRole':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO roles (role_name, role_description) VALUES (?, ?)");
        $stmt->execute([$data['name'], $data['description']]);
        $roleId = $pdo->lastInsertId();
        
        foreach($data['permissions'] as $permId) {
            $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            $stmt->execute([$roleId, $permId]);
        }
        echo json_encode(['success' => true]);
        break;
        
    case 'assignRole':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("UPDATE users SET role_id = ? WHERE id = ?");
        $stmt->execute([$data['role_id'], $data['user_id']]);
        
        $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id, assigned_by) VALUES (?, ?, ?) 
                              ON DUPLICATE KEY UPDATE role_id = ?, assigned_at = NOW()");
        $stmt->execute([$data['user_id'], $data['role_id'], $_SESSION['id'], $data['role_id']]);
        echo json_encode(['success' => true]);
        break;
        
    case 'getUsers':
        $stmt = $pdo->query("SELECT id, username, email FROM users ORDER BY username");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'users' => $users]);
        break;
}
?>

