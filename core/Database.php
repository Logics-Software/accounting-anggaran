<?php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $config = require __DIR__ . '/../config/database.php';
        
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                
                // OPTIMIZATION: Persistent connection
                // Reuse connection untuk request berikutnya, mengurangi overhead create/close connection
                PDO::ATTR_PERSISTENT => true,
                
                // OPTIMIZATION: Connection timeout (5 seconds)
                PDO::ATTR_TIMEOUT => 5,
            ];
            
            $this->connection = new PDO($dsn, $config['username'], $config['password'], $options);
            
            // Set charset and collation
            $this->connection->exec("SET NAMES {$config['charset']} COLLATE {$config['collation']}");
            
            // OPTIMIZATION: Set connection character set explicitly
            $this->connection->exec("SET CHARACTER SET {$config['charset']}");
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            
            // Set error mode to exception to catch warnings as well
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt->execute($params);
            
            // Check for warnings
            $errorInfo = $stmt->errorInfo();
            if ($errorInfo[0] !== '00000' && $errorInfo[0] !== null) {
                // If it's a warning (01xxx), log it but don't throw
                if (substr($errorInfo[0], 0, 2) === '01') {
                    error_log("Database warning: " . ($errorInfo[2] ?? 'Unknown warning') . " | SQL: " . substr($sql, 0, 200));
                    // For data truncation warnings, throw exception
                    if (strpos($errorInfo[2] ?? '', 'Data truncated') !== false) {
                        throw new PDOException("Data truncated: " . ($errorInfo[2] ?? 'Unknown field'));
                    }
                } else {
                    // For other errors, throw exception
                    throw new PDOException($errorInfo[2] ?? 'Database error');
                }
            }
            
            return $stmt;
        } catch(PDOException $e) {
            error_log("Database query error: " . $e->getMessage() . " | SQL: " . substr($sql, 0, 200));
            throw $e;
        }
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Begin a database transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit the current transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback the current transaction
     */
    public function rollBack() {
        return $this->connection->rollBack();
    }
    
    /**
     * Check if currently in a transaction
     */
    public function inTransaction() {
        return $this->connection->inTransaction();
    }
    
    /**
     * Execute a callback within a transaction
     * Automatically handles commit/rollback
     * 
     * @param callable $callback Function to execute
     * @return mixed Return value of callback
     * @throws Exception If callback throws exception, transaction is rolled back
     */
    public function transaction(callable $callback) {
        $this->beginTransaction();
        
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        } catch (Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }
}

