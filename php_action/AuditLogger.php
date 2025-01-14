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
     * @param string $action The action being performed (e.g., 'login', 'logout', 'create', 'update', 'delete')
     * @param string $entityType The type of entity being acted upon (e.g., 'user', 'product', 'order')
     * @param string|null $entityId The ID of the entity (if applicable)
     * @param array|null $oldValues Previous values before change (for updates)
     * @param array|null $newValues New values after change (for updates/creates)
     * @return bool Whether the logging was successful
     */
    public function log($action, $entityType = null, $entityId = null, $oldValues = null, $newValues = null) {
        try {
            // Get current user info
            $userId = isset($_SESSION['userId']) ? $_SESSION['userId'] : null;
            $username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
            
            // Get request details
            $ipAddress = $this->getClientIP();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            // Prepare old and new values for storage
            $oldValuesJson = $oldValues ? json_encode($oldValues) : null;
            $newValuesJson = $newValues ? json_encode($newValues) : null;
            
            // Prepare and execute query
            $sql = "INSERT INTO audit_logs (user_id, username, action, entity_type, entity_id, 
                    old_values, new_values, ip_address, user_agent) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("issssssss", 
                $userId,
                $username,
                $action,
                $entityType,
                $entityId,
                $oldValuesJson,
                $newValuesJson,
                $ipAddress,
                $userAgent
            );
            
            $result = $stmt->execute();
            if (!$result) {
                error_log("Failed to log audit entry: " . $stmt->error);
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error in audit logging: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get client's real IP address
     */
    private function getClientIP() {
        $ipAddress = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ipAddress;
    }
    
    /**
     * Get audit logs with filtering and pagination
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
            if (!empty($filters['entity_type'])) {
                $conditions[] = "entity_type = ?";
                $params[] = $filters['entity_type'];
                $types .= "s";
            }
            if (!empty($filters['date_from'])) {
                $conditions[] = "created_at >= ?";
                $params[] = $filters['date_from'];
                $types .= "s";
            }
            if (!empty($filters['date_to'])) {
                $conditions[] = "created_at <= ?";
                $params[] = $filters['date_to'];
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
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
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
            return [];
        }
    }
    
    /**
     * Get total count of audit logs for pagination
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
            // ... add other filters similarly
            
            $sql = "SELECT COUNT(*) as total FROM audit_logs";
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $stmt = $this->conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int)$row['total'];
        } catch (Exception $e) {
            error_log("Error counting audit logs: " . $e->getMessage());
            return 0;
        }
    }
}
