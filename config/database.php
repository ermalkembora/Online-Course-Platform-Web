<?php
/**
 * Database Connection Class
 * 
 * Singleton pattern PDO database connection with helper methods.
 */

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $conn;
    private $stmt;

    /**
     * Private constructor - prevents direct instantiation
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (ENV === 'development') {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed. Please contact the administrator.");
            }
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
     * Get the PDO connection
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Prepare a SQL query
     * 
     * @param string $sql SQL query with placeholders
     * @return Database
     */
    public function query($sql) {
        $this->stmt = $this->conn->prepare($sql);
        return $this;
    }

    /**
     * Bind a parameter to the prepared statement
     * 
     * @param mixed $param Parameter name or position
     * @param mixed $value Parameter value
     * @param int $type PDO parameter type
     * @return Database
     */
    public function bind($param, $value, $type = null) {
        if ($type === null) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        
        $this->stmt->bindValue($param, $value, $type);
        return $this;
    }

    /**
     * Execute the prepared statement
     * 
     * @return bool
     */
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            if (ENV === 'development') {
                die("Query execution failed: " . $e->getMessage());
            } else {
                error_log("Database error: " . $e->getMessage());
                return false;
            }
        }
    }

    /**
     * Get all results as an array of associative arrays
     * 
     * @return array
     */
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    /**
     * Get a single row as an associative array
     * 
     * @return array|false
     */
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    /**
     * Get the number of rows affected by the last statement
     * 
     * @return int
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    /**
     * Get the ID of the last inserted row
     * 
     * @return string
     */
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }

    /**
     * Begin a transaction
     * 
     * @return bool
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    /**
     * Commit a transaction
     * 
     * @return bool
     */
    public function commit() {
        return $this->conn->commit();
    }

    /**
     * Rollback a transaction
     * 
     * @return bool
     */
    public function rollback() {
        return $this->conn->rollBack();
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Global helper function to get database instance
 * 
 * @return Database
 */
function getDB() {
    return Database::getInstance();
}
