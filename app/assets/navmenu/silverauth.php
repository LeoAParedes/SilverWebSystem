<?php
/**
 * SilverWebSystem Authentication Handler
 * Uses existing connect.php for database connection
 */

// Start output buffering first
ob_start();

// Suppress all errors/warnings to prevent JSON corruption
error_reporting(0);
ini_set('display_errors', 0);

// Start session
session_start();

// Include the existing database connection
require_once '../../connect.php';

// Initialize response
$response = [
    'success' => false,
    'message' => ''
];

// Get action from request
$action = isset($_GET['action']) ? $_GET['action'] : '';

/**
 * Database Wrapper Class using existing PDO connection
 */
class SilverDatabase {
    private $pdo;
    
    public function __construct() {
        // Use the global $pdo from connect.php
        global $pdo;
        if (!$pdo) {
            throw new Exception("Database connection not available");
        }
        $this->pdo = $pdo;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function executeQuery($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            error_log("Query execution failed: " . $e->getMessage());
            throw new Exception("Query execution failed");
        }
    }
    
    public function fetchOne($sql, $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetch();
    }
    
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        return $this->executeQuery($sql, $data);
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $setClause = [];
        foreach(array_keys($data) as $key) {
            $setClause[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge($data, $whereParams);
        
        return $this->executeQuery($sql, $params);
    }
}

/**
 * Password Manager with Golden Ratio Security
 */
class GoldenPasswordManager {
    // Using golden ratio for security timing
    private const GOLDEN_RATIO = 1.618;
    
    public static function hashPassword($password) {
        // Use PHP's built-in password hashing (more secure than custom)
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        if ($hash === false) {
            throw new Exception("Password hashing failed");
        }
        
        return $hash;
    }
    
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public static function validatePasswordStrength($password) {
        if (strlen($password) < 8) {
            return "Password must be at least 8 characters long";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return "Password must contain at least one uppercase letter";
        }
        if (!preg_match('/[a-z]/', $password)) {
            return "Password must contain at least one lowercase letter";
        }
        if (!preg_match('/[0-9]/', $password)) {
            return "Password must contain at least one number";
        }
        return true;
    }
}

// Process LOGIN
if ($action === 'login') {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $response['message'] = 'Username and password are required';
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    try {
        $db = new SilverDatabase();
        
        // Get user from database with all needed fields
        $user = $db->fetchOne(
            "SELECT id, username, password, email, role_id FROM users WHERE username = ? OR email = ?",
            [$username, $username]
        );
        
        if (!$user) {
            $response['message'] = 'Invalid username or password';
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        // Verify password
        if (GoldenPasswordManager::verifyPassword($password, $user['password'])) {
            // Set ALL session variables for compatibility
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['id'] = $user['id'];  // CRITICAL for navmenu.php compatibility
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'] ?? '';
            $_SESSION['role_id'] = $user['role_id'] ?? 6; // Default to viewer if not set
            $_SESSION['login_time'] = time();
            $_SESSION['golden_ratio'] = 1.618; // For theme calculations
            
            // NOTE: Removed last_login update as column doesn't exist in database
            // If you want to add it later, first add column to database:
            // ALTER TABLE users ADD COLUMN last_login DATETIME NULL AFTER role_id;
            
            $response['success'] = true;
            $response['message'] = 'Login successful! Welcome to SilverWebSystem';
            
            // Check for checkout redirect
            if (isset($_POST['return_to']) && $_POST['return_to'] === 'checkout') {
                $response['redirect'] = 'app/pages/orderprocessing/checkout.php';
            } else {
                $response['redirect'] = null;
            }
            
            // Add customer record if not exists
            try {
                $customer = $db->fetchOne(
                    "SELECT customer_id FROM customers WHERE user_id = ?",
                    [$user['id']]
                );
                
                if (!$customer) {
                    $customerEmail = $user['email'] ?? ($user['username'] . '@silverwebsystem.com');
                    $db->insert('customers', [
                        'user_id' => $user['id'],
                        'first_name' => $user['username'],
                        'last_name' => '',
                        'email' => $customerEmail,
                        'customer_type' => 'individual',
                        'golden_ratio_tier' => 1,
                        'is_active' => 1,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            } catch (Exception $e) {
                // Don't fail login if customer creation fails
                error_log("Customer creation failed: " . $e->getMessage());
            }
        } else {
            $response['message'] = 'Invalid username or password';
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $response['message'] = 'An error occurred. Please try again.';
    }
    
    // Clean output and send JSON response
    ob_end_clean();
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    echo json_encode($response);
    exit;
}

// Process SIGNUP
if ($action === 'signup') {
    // Check all required fields
    if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['passwordrpt']) || empty($_POST['email'])) {
        $response['message'] = 'All fields are required';
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Validate email
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $response['message'] = 'Invalid email address';
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $passwordrpt = $_POST['passwordrpt'];
    
    // Validate passwords match
    if ($password !== $passwordrpt) {
        $response['message'] = 'Passwords do not match';
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Validate username format
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $response['message'] = 'Username must be 3-20 characters (letters, numbers, underscore only)';
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Validate password strength
    $passwordValidation = GoldenPasswordManager::validatePasswordStrength($password);
    if ($passwordValidation !== true) {
        $response['message'] = $passwordValidation;
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    try {
        $db = new SilverDatabase();
        
        // Check if username exists
        $existingUser = $db->fetchOne(
            "SELECT id FROM users WHERE username = ?",
            [$username]
        );
        
        if ($existingUser) {
            $response['message'] = 'Username already exists';
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        // Check if email already exists
        $existingEmail = $db->fetchOne(
            "SELECT id FROM users WHERE email = ?",
            [$email]
        );
        
        if ($existingEmail) {
            $response['message'] = 'Email already registered';
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        // Hash password
        $hashedPassword = GoldenPasswordManager::hashPassword($password);
        
        // Insert new user with email and viewer role
        $userData = [
            'username' => $username,
            'password' => $hashedPassword,
            'email' => $email,              // Email is required
            'role_id' => 6,                 // Viewer role for all new signups
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $db->insert('users', $userData);
        
        // Get the new user ID
        $newUserId = $db->getConnection()->lastInsertId();
        
        // Create customer record
        if ($newUserId) {
            try {
                $db->insert('customers', [
                    'user_id' => $newUserId,
                    'first_name' => $username,
                    'last_name' => '',
                    'email' => $email,
                    'customer_type' => 'individual',
                    'golden_ratio_tier' => 1,
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } catch (Exception $e) {
                // Log but don't fail signup
                error_log("Customer creation failed: " . $e->getMessage());
            }
            
            // Add to user_roles table
            try {
                $db->insert('user_roles', [
                    'user_id' => $newUserId,
                    'role_id' => 6,
                    'assigned_at' => date('Y-m-d H:i:s'),
                    'assigned_by' => $newUserId
                ]);
            } catch (Exception $e) {
                // Log but don't fail signup
                error_log("User role assignment failed: " . $e->getMessage());
            }
        }
        
        $response['success'] = true;
        $response['message'] = 'Account created successfully! Please login.';
        
    } catch (Exception $e) {
        error_log("Signup error: " . $e->getMessage());
        $response['message'] = 'An error occurred during registration. Please try again.';
    }
    
    // Clean output and send JSON response
    ob_end_clean();
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    echo json_encode($response);
    exit;
}

// Process LOGOUT
if ($action === 'logout') {
    // Clear all session data
    $_SESSION = [];
    
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to index
    header('Location: ../../../index.php');
    exit;
}

// Invalid action - clean output and return error
ob_end_clean();
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
?>

