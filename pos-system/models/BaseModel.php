<?php
require_once __DIR__ . '/../config/database.php';

class BaseModel {
    protected $conn;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function findById($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
            $result = $this->query($sql, [$id]);
            return mysqli_fetch_assoc($result);
        } catch (Exception $e) {
            throw new Exception("Failed to find record: " . $e->getMessage());
        }
    }

    public function findOne($conditions) {
        try {
            $where = $this->buildWhereClause($conditions);
            $sql = "SELECT * FROM {$this->table} {$where['where']} LIMIT 1";
            $result = $this->query($sql, $where['params']);
            return mysqli_fetch_assoc($result);
        } catch (Exception $e) {
            throw new Exception("Failed to find record: " . $e->getMessage());
        }
    }

    public function findAll($conditions = [], $orderBy = '', $limit = null, $offset = null) {
        try {
            $where = $this->buildWhereClause($conditions);
            $sql = "SELECT * FROM {$this->table} {$where['where']}";
            
            if ($orderBy) {
                $sql .= " ORDER BY $orderBy";
            }
            
            if ($limit !== null) {
                $sql .= " LIMIT " . (int)$limit;
                if ($offset !== null) {
                    $sql .= " OFFSET " . (int)$offset;
                }
            }

            $result = $this->query($sql, $where['params']);
            $records = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $records[] = $row;
            }
            return $records;
        } catch (Exception $e) {
            throw new Exception("Failed to find records: " . $e->getMessage());
        }
    }

    public function create($data) {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = str_repeat('?,', count($data) - 1) . '?';
            
            $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
            $result = $this->query($sql, array_values($data));
            
            if ($result) {
                return Database::getInstance()->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            throw new Exception("Failed to create record: " . $e->getMessage());
        }
    }

    public function update($id, $data) {
        try {
            $set = $this->buildSetClause($data);
            $sql = "UPDATE {$this->table} SET {$set['set']} WHERE {$this->primaryKey} = ?";
            
            // Add id to params array
            $params = array_merge($set['params'], [$id]);
            
            $result = $this->query($sql, $params);
            return mysqli_affected_rows($this->conn);
        } catch (Exception $e) {
            throw new Exception("Failed to update record: " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
            $result = $this->query($sql, [$id]);
            return mysqli_affected_rows($this->conn);
        } catch (Exception $e) {
            throw new Exception("Failed to delete record: " . $e->getMessage());
        }
    }

    public function count($conditions = []) {
        try {
            $where = $this->buildWhereClause($conditions);
            $sql = "SELECT COUNT(*) as count FROM {$this->table} {$where['where']}";
            $result = $this->query($sql, $where['params']);
            $row = mysqli_fetch_assoc($result);
            return (int)$row['count'];
        } catch (Exception $e) {
            throw new Exception("Failed to count records: " . $e->getMessage());
        }
    }

    public function exists($conditions) {
        try {
            $where = $this->buildWhereClause($conditions);
            $sql = "SELECT EXISTS(SELECT 1 FROM {$this->table} {$where['where']}) as exist";
            $result = $this->query($sql, $where['params']);
            $row = mysqli_fetch_assoc($result);
            return (bool)$row['exist'];
        } catch (Exception $e) {
            throw new Exception("Failed to check existence: " . $e->getMessage());
        }
    }

    protected function query($sql, $params = []) {
        return Database::getInstance()->query($sql, $params);
    }

    protected function buildWhereClause($conditions) {
        return Database::getInstance()->buildWhereClause($conditions);
    }

    protected function buildSetClause($data) {
        return Database::getInstance()->buildSetClause($data);
    }

    protected function validateTableName($table) {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new Exception("Invalid table name");
        }
        return $table;
    }

    protected function validateColumnNames($columns) {
        if (is_array($columns)) {
            foreach ($columns as $column) {
                if (!preg_match('/^[a-zA-Z0-9_\.]+$/', $column)) {
                    throw new Exception("Invalid column name: $column");
                }
            }
            return implode(', ', $columns);
        }
        return $columns === '*' ? '*' : throw new Exception("Invalid columns parameter");
    }

    protected function now() {
        return date('Y-m-d H:i:s');
    }

    protected function escapeString($value) {
        return Database::getInstance()->escapeString($value);
    }
}
?>
