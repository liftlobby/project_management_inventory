<?php

class AuditLogger {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        require_once 'db_connect.php';
        $this->conn = $GLOBALS['connect'];
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Log user activity
     * 
     * @param string $action The action being performed (e.g., 'create', 'update', 'delete')
     * @param string $tableName The table being modified (e.g., 'users', 'products')
     * @param int $recordId The ID of the record being modified
     * @param array|null $oldValues Previous values before change (for updates)
     * @param array|null $newValues New values after change (for updates/creates)
     * @return bool Whether the logging was successful
     */
    public function log($action, $tableName, $recordId, $oldValues = null, $newValues = null) {
        try {
            // Get current user info from session
            if (!isset($_SESSION['userId']) || !isset($_SESSION['username'])) {
                error_log("No user session found for audit logging");
                return false;
            }
            
            $userId = $_SESSION['userId'];
            $username = $_SESSION['username'];
            
            // Convert arrays to JSON for storage
            $oldValuesJson = $oldValues ? json_encode($oldValues) : null;
            $newValuesJson = $newValues ? json_encode($newValues) : null;
            
            // Get IP address
            $ipAddress = $this->getClientIP();
            
            // Prepare and execute query
            $sql = "INSERT INTO audit_logs (user_id, username, action, table_name, record_id, old_values, new_values, ip_address) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                error_log("Failed to prepare audit log statement: " . $this->conn->error);
                return false;
            }
            
            $stmt->bind_param("isssisss", 
                $userId,
                $username,
                $action,
                $tableName,
                $recordId,
                $oldValuesJson,
                $newValuesJson,
                $ipAddress
            );
            
            $result = $stmt->execute();
            if (!$result) {
                error_log("Failed to execute audit log statement: " . $stmt->error);
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error in audit logging: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Get audit logs with filtering and pagination
     * 
     * @param array $filters Associative array of filters (user_id, action, table_name, date_from, date_to)
     * @param int $page Current page number
     * @param int $perPage Number of items per page
     * @return array Array of audit log entries
     */
    public function getAuditLogs($filters = [], $page = 1, $perPage = 20) {
        try {
            $conditions = [];
            $params = [];
            $types = "";
            
            // Build filter conditions
            if (!empty($filters['user_id'])) {
                $conditions[] = "user_id = ?";
                $params[] = $filters['user_id'];
                $types .= "i";
            }
            
            if (!empty($filters['action'])) {
                $conditions[] = "action = ?";
                $params[] = $filters['action'];
                $types .= "s";
            }
            
            if (!empty($filters['table_name'])) {
                $conditions[] = "table_name = ?";
                $params[] = $filters['table_name'];
                $types .= "s";
            }
            
            if (!empty($filters['date_from'])) {
                $conditions[] = "created_at >= ?";
                $params[] = $filters['date_from'] . ' 00:00:00';
                $types .= "s";
            }
            
            if (!empty($filters['date_to'])) {
                $conditions[] = "created_at <= ?";
                $params[] = $filters['date_to'] . ' 23:59:59';
                $types .= "s";
            }
            
            // Build query
            $sql = "SELECT * FROM audit_logs";
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            
            // Add ordering
            $sql .= " ORDER BY created_at DESC";
            
            // Add pagination
            $offset = ($page - 1) * $perPage;
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;
            $types .= "ii";
            
            // Execute query
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                error_log("Failed to prepare getAuditLogs statement: " . $this->conn->error);
                return [];
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                error_log("Failed to execute getAuditLogs statement: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            
            $logs = [];
            while ($row = $result->fetch_assoc()) {
                // Convert JSON strings back to arrays
                if ($row['old_values']) {
                    $row['old_values'] = json_decode($row['old_values'], true);
                }
                if ($row['new_values']) {
                    $row['new_values'] = json_decode($row['new_values'], true);
                }
                $logs[] = $row;
            }
            
            return $logs;
            
        } catch (Exception $e) {
            error_log("Error retrieving audit logs: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [];
        }
    }
    
    /**
     * Get total count of audit logs for pagination
     * 
     * @param array $filters Associative array of filters
     * @return int Total number of matching logs
     */
    public function getTotalLogsCount($filters = []) {
        try {
            $conditions = [];
            $params = [];
            $types = "";
            
            // Build filter conditions (same as getAuditLogs)
            if (!empty($filters['user_id'])) {
                $conditions[] = "user_id = ?";
                $params[] = $filters['user_id'];
                $types .= "i";
            }
            
            if (!empty($filters['action'])) {
                $conditions[] = "action = ?";
                $params[] = $filters['action'];
                $types .= "s";
            }
            
            if (!empty($filters['table_name'])) {
                $conditions[] = "table_name = ?";
                $params[] = $filters['table_name'];
                $types .= "s";
            }
            
            if (!empty($filters['date_from'])) {
                $conditions[] = "created_at >= ?";
                $params[] = $filters['date_from'] . ' 00:00:00';
                $types .= "s";
            }
            
            if (!empty($filters['date_to'])) {
                $conditions[] = "created_at <= ?";
                $params[] = $filters['date_to'] . ' 23:59:59';
                $types .= "s";
            }
            
            $sql = "SELECT COUNT(*) as total FROM audit_logs";
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                error_log("Failed to prepare getTotalLogsCount statement: " . $this->conn->error);
                return 0;
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                error_log("Failed to execute getTotalLogsCount statement: " . $stmt->error);
                return 0;
            }
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int)$row['total'];
            
        } catch (Exception $e) {
            error_log("Error counting audit logs: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return 0;
        }
    }
    
    /**
     * Get client's real IP address
     */
    private function getClientIP() {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return 'Unknown';
    }
}
