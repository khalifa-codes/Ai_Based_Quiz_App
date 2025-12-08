<?php
/**
 * Database Configuration and Connection
 * Singleton pattern for database connection management
 * 
 * @version 1.0
 * @author QuizAura System
 */

// Prevent direct access
if (!defined('DB_INITIALIZED')) {
    define('DB_INITIALIZED', true);
}

class Database {
    private static $instance = null;
    private $connection = null;
    private static $config = null;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        // Load configuration
        self::loadConfig();
        
        $host = self::$config['host'];
        $dbname = self::$config['dbname'];
        $username = self::$config['username'];
        $password = self::$config['password'];
        $charset = self::$config['charset'] ?? 'utf8mb4';
        
        // Build DSN with port support
        $port = self::$config['port'] ?? 3307;
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
        
        // PDO options for security and performance
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false, // Use native prepared statements
            PDO::ATTR_PERSISTENT => false // Don't use persistent connections
        ];
        
        try {
            $this->connection = new PDO($dsn, $username, $password, $options);
            
            // Set timezone to UTC for consistency
            $this->connection->exec("SET time_zone = '+00:00'");
            
        } catch (PDOException $e) {
            // Log error securely (don't expose credentials)
            error_log("Database connection failed: " . $e->getMessage());
            
            // In production, show generic error to user
            if (self::$config['show_errors'] ?? false) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            } else {
                throw new Exception("Database connection failed. Please contact the administrator.");
            }
        }
    }
    
    /**
     * Load database configuration from environment variables or defaults
     */
    private static function loadConfig() {
        if (self::$config !== null) {
            return; // Already loaded
        }
        
        // Try to load from environment variables first (for production)
        self::$config = [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => intval(getenv('DB_PORT') ?: '3307'),
            'dbname' => getenv('DB_NAME') ?: 'quiz_system',
            'username' => getenv('DB_USER') ?: 'root',
            'password' => getenv('DB_PASS') ?: '',
            'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
            'show_errors' => filter_var(getenv('DB_SHOW_ERRORS') ?: 'false', FILTER_VALIDATE_BOOLEAN)
        ];
        
        // Allow override via config file if exists
        $configFile = __DIR__ . '/database_config.php';
        if (file_exists($configFile)) {
            $customConfig = require $configFile;
            self::$config = array_merge(self::$config, $customConfig);
        }
    }
    
    /**
     * Get singleton instance
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     * 
     * @return PDO
     */
    public function getConnection() {
        if ($this->connection === null) {
            throw new Exception("Database connection is not initialized");
        }
        return $this->connection;
    }
    
    /**
     * Test database connection
     * 
     * @return bool
     */
    public function testConnection() {
        try {
            $this->connection->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get database configuration (without sensitive data)
     * 
     * @return array
     */
    public static function getConfig() {
        if (self::$config === null) {
            self::loadConfig();
        }
        
        $safeConfig = self::$config;
        // Don't expose password
        if (isset($safeConfig['password'])) {
            $safeConfig['password'] = '***';
        }
        
        return $safeConfig;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Helper function for easy database access
 * 
 * @return PDO
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

/**
 * Helper function to check if database is available
 * 
 * @return bool
 */
function isDatabaseAvailable() {
    try {
        $db = Database::getInstance();
        return $db->testConnection();
    } catch (Exception $e) {
        return false;
    }
}
