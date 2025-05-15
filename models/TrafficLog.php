<?php
/**
 * Model for tracking and retrieving website traffic statistics
 */
class TrafficLog {
    private $conn;
    private $table_name = "traffic_logs";
    
    public function __construct($db) {
        $this->conn = $db;
        $this->initializeTable();
    }
    
    private function initializeTable() {
        $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45),
            user_agent TEXT,
            page_url VARCHAR(255),
            referer_url VARCHAR(255),
            user_id INT NULL,
            session_id VARCHAR(100),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        try {
            $this->conn->exec($query);
        } catch (PDOException $e) {
            error_log("Error creating traffic logs table: " . $e->getMessage());
            throw new Exception("Could not initialize traffic logs table");
        }
    }
    
    public function logAccess() {
        $ip_address = $this->getIpAddress();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $page_url = $_SERVER['REQUEST_URI'] ?? '/';
        $referer_url = $_SERVER['HTTP_REFERER'] ?? '';
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $session_id = session_id();
        
        $query = "INSERT INTO " . $this->table_name . " 
                (ip_address, user_agent, page_url, referer_url, user_id, session_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$ip_address, $user_agent, $page_url, $referer_url, $user_id, $session_id]);
        } catch (PDOException $e) {
            error_log("Error logging traffic: " . $e->getMessage());
            return false;
        }
    }
    
    private function getIpAddress() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }
    
    public function getTotalVisits() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $row['total'];
        } catch (PDOException $e) {
            error_log("Error getting total visits: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getTodayVisits() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . "
                 WHERE DATE(created_at) = CURDATE()";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $row['total'];
        } catch (PDOException $e) {
            error_log("Error getting today's visits: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getStatsRange($start_date, $end_date, $interval = 'day') {
        if ($interval == 'month') {
            $group_by = "DATE_FORMAT(created_at, '%Y-%m')";
        } else {
            $group_by = "DATE(created_at)";
        }
        
        $query = "SELECT " . $group_by . " as period, COUNT(*) as count 
                FROM " . $this->table_name . "
                WHERE DATE(created_at) BETWEEN :start_date AND :end_date
                GROUP BY " . $group_by . "
                ORDER BY period ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Fill missing days with 0 count
            $current_date = new DateTime($start_date);
            $end = new DateTime($end_date);
            $interval_obj = new DateInterval('P1D');
            $date_range = new DatePeriod($current_date, $interval_obj, $end->modify('+1 day'));
            $filled_result = [];
            
            foreach ($date_range as $date) {
                $date_str = $date->format('Y-m-d');
                $found = false;
                foreach ($result as $row) {
                    if ($row['period'] === $date_str) {
                        $filled_result[] = $row;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $filled_result[] = ['period' => $date_str, 'count' => 0];
                }
            }
            
            return $filled_result;
        } catch (PDOException $e) {
            error_log("Error getting statistics range: " . $e->getMessage());
            return [];
        }
    }
    
    public function getUniqueVisitors($start_date, $end_date) {
        $query = "SELECT COUNT(DISTINCT session_id) as unique_count 
                FROM " . $this->table_name . "
                WHERE DATE(created_at) BETWEEN :start_date AND :end_date";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $row['unique_count'];
        } catch (PDOException $e) {
            error_log("Error getting unique visitors: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getTopPages($limit = 10) {
        $query = "SELECT page_url, COUNT(*) as count 
                FROM " . $this->table_name . "
                GROUP BY page_url
                ORDER BY count DESC
                LIMIT :limit";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting top pages: " . $e->getMessage());
            return [];
        }
    }
    
    public function getReferringSources($limit = 10) {
        $query = "SELECT 
                    CASE 
                        WHEN referer_url = '' THEN 'Direct' 
                        ELSE referer_url 
                    END as source, 
                    COUNT(*) as count 
                FROM " . $this->table_name . "
                GROUP BY source
                ORDER BY count DESC
                LIMIT :limit";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting referring sources: " . $e->getMessage());
            return [];
        }
    }
}
?>