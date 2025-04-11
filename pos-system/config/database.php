<?php
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            // Database configuration
            $host = getenv('DB_HOST') ?: 'localhost';
            $dbname = getenv('DB_NAME') ?: 'pos_system';
            $username = getenv('DB_USER') ?: 'root';
            $password = getenv('DB_PASS') ?: '';

            // Create connection
            $this->conn = mysqli_connect($host, $username, $password, $dbname);

            // Check connection
            if (!$this->conn) {
                throw new Exception("Connection failed: " . mysqli_connect_error());
            }

            // Set charset
            mysqli_set_charset($this->conn, "utf8mb4");
            
        } catch (Exception $e) {
            throw new Exception("Connection failed: " . $e->getMessage());
        }
    }

    // Get database instance (Singleton pattern)
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Get database connection
    public function getConnection() {
        return $this->conn;
    }

    // Begin transaction
    public function beginTransaction() {
        return mysqli_begin_transaction($this->conn);
    }

    // Commit transaction
    public function commit() {
        return mysqli_commit($this->conn);
    }

    // Rollback transaction
    public function rollBack() {
        return mysqli_rollback($this->conn);
    }

    // Execute a query
    public function query($sql, $params = []) {
        try {
            // If there are parameters, prepare and bind them
            if (!empty($params)) {
                $stmt = mysqli_prepare($this->conn, $sql);
                if (!$stmt) {
                    throw new Exception("Query preparation failed: " . mysqli_error($this->conn));
                }

                // Build types string for bind_param
                $types = '';
                $bindParams = [];
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } else {
                        $types .= 's';
                    }
                    $bindParams[] = $param;
                }

                // Create array of references for bind_param
                $bindRefs = [];
                $bindRefs[] = $types;
                foreach ($bindParams as $key => $value) {
                    $bindRefs[] = &$bindParams[$key];
                }

                // Bind parameters
                call_user_func_array([$stmt, 'bind_param'], $bindRefs);

                // Execute statement
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Query execution failed: " . mysqli_stmt_error($stmt));
                }

                $result = mysqli_stmt_get_result($stmt);
                mysqli_stmt_close($stmt);
                return $result;
            } else {
                // Execute simple query without parameters
                $result = mysqli_query($this->conn, $sql);
                if ($result === false) {
                    throw new Exception("Query failed: " . mysqli_error($this->conn));
                }
                return $result;
            }
        } catch (Exception $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    // Get last inserted ID
    public function lastInsertId() {
        return mysqli_insert_id($this->conn);
    }

    // Escape string
    public function escapeString($value) {
        return mysqli_real_escape_string($this->conn, $value);
    }

    // Helper method to build WHERE clause from conditions array
    public function buildWhereClause($conditions) {
        if (empty($conditions)) {
            return ['where' => '', 'params' => []];
        }

        $where = [];
        $params = [];

        foreach ($conditions as $key => $value) {
            if (is_numeric($key)) {
                // Raw condition
                $where[] = $value;
            } else {
                // Simple equality condition
                $where[] = "$key = ?";
                $params[] = $value;
            }
        }

        return [
            'where' => 'WHERE ' . implode(' AND ', $where),
            'params' => $params
        ];
    }

    // Helper method to build SET clause for UPDATE queries
    public function buildSetClause($data) {
        $set = [];
        $params = [];

        foreach ($data as $key => $value) {
            $set[] = "$key = ?";
            $params[] = $value;
        }

        return [
            'set' => implode(', ', $set),
            'params' => $params
        ];
    }

    // Close connection
    public function close() {
        if ($this->conn) {
            mysqli_close($this->conn);
        }
    }

    // Prevent cloning of the instance (Singleton pattern)
    private function __clone() {}

    // Prevent unserializing of the instance (Singleton pattern)
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
?>
