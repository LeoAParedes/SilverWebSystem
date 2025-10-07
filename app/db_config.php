


$environment = 'production';
if ($environment === 'production') {
    // Production credentials
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'production');
    define('DB_PASSWORD', 'Goldenpass321#');
    define('DB_NAME', 'silverwebsystem');
} else {
    // Development credentials
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'silverweb');
    define('DB_PASSWORD', 'goldenleon#');
    define('DB_NAME', 'silverwebsystem');
}

define('DB_CHARSET', 'utf8mb4');
function getConnection() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return false;
    }
    
    $conn->set_charset(DB_CHARSET);
    return $conn;
}

/**
 * Get PDO Connection
 */
function getPDOConnection() {
    try {
        $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];
        
        return new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
    } catch(PDOException $e) {
        error_log("PDO Connection failed: " . $e->getMessage());
        return false;
    }
}
?>

