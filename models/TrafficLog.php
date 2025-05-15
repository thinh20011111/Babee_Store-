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
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            visit_count INT DEFAULT 1,
            visit_date DATE,
            last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_session_date (session_id, visit_date)
        )";
        
        try {
            $this->conn->exec($query);
            error_log("Traffic logs table initialized successfully at " . date('Y-m-d H:i:s'));
        } catch (PDOException $e) {
            error_log("Error creating traffic logs table: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
            throw new Exception("Could not initialize traffic logs table");
        }
    }
    
    public function logAccess() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
            session_regenerate_id(true);
        }
        
        $ip_address = $this->getIpAddress();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $page_url = $_SERVER['REQUEST_URI'] ?? '/';
        $referer_url = $_SERVER['HTTP_REFERER'] ?? '';
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $session_id = session_id();
        $current_date = date('Y-m-d');
        
        if (empty($session_id)) {
            error_log("Session ID is empty at " . date('Y-m-d H:i:s'));
            return false;
        }
        
        error_log("Logging access: session_id=$session_id, ip=$ip_address, page=$page_url, user_id=" . ($user_id ?? 'null') . " at " . date('Y-m-d H:i:s'));
        
        // Kiểm tra xem session_id đã tồn tại trong ngày hiện tại chưa
        $check_query = "SELECT id, COALESCE(visit_count, 1) as visit_count FROM " . $this->table_name . "
                       WHERE session_id = :session_id AND visit_date = :current_date";
        
        try {
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':session_id', $session_id);
            $check_stmt->bindParam(':current_date', $current_date);
            $check_stmt->execute();
            $existing_record = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_record) {
                // Tăng visit_count nếu đã tồn tại
                $new_visit_count = $existing_record['visit_count'] + 1;
                $update_query = "UPDATE " . $this->table_name . "
                               SET visit_count = :visit_count, page_url = :page_url, last_updated = CURRENT_TIMESTAMP
                               WHERE id = :id";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(':visit_count', $new_visit_count, PDO::PARAM_INT);
                $update_stmt->bindParam(':page_url', $page_url);
                $update_stmt->bindParam(':id', $existing_record['id'], PDO::PARAM_INT);
                
                $result = $update_stmt->execute();
                if ($result) {
                    error_log("Updated visit_count to $new_visit_count for session_id: $session_id on $current_date at " . date('Y-m-d H:i:s'));
                } else {
                    error_log("Failed to update visit_count for session_id: $session_id - " . print_r($update_stmt->errorInfo(), true));
                }
                return $result;
            } else {
                // Thêm bản ghi mới với visit_count = 1
                $insert_query = "INSERT INTO " . $this->table_name . " 
                               (ip_address, user_agent, page_url, referer_url, user_id, session_id, visit_date, visit_count)
                               VALUES (:ip_address, :user_agent, :page_url, :referer_url, :user_id, :session_id, :visit_date, :visit_count)";
                
                $stmt = $this->conn->prepare($insert_query);
                $stmt->bindParam(':ip_address', $ip_address);
                $stmt->bindParam(':user_agent', $user_agent);
                $stmt->bindParam(':page_url', $page_url);
                $stmt->bindParam(':referer_url', $referer_url);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':session_id', $session_id);
                $stmt->bindParam(':visit_date', $current_date);
                $visit_count = 1; // Đảm bảo giá trị mặc định là 1
                $stmt->bindParam(':visit_count', $visit_count, PDO::PARAM_INT);
                
                $result = $stmt->execute();
                if ($result) {
                    error_log("Successfully inserted record for session_id: $session_id with visit_count=1 at " . date('Y-m-d H:i:s'));
                } else {
                    error_log("Failed to insert record for session_id: $session_id - " . print_r($stmt->errorInfo(), true));
                }
                return $result;
            }
        } catch (PDOException $e) {
            error_log("Error logging traffic: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
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
        $query = "SELECT COALESCE(SUM(visit_count), 0) as total FROM " . $this->table_name;
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = (int) ($row['total'] ?? 0);
            error_log("Total visits fetched: $total at " . date('Y-m-d H:i:s'));
            return $total;
        } catch (PDOException $e) {
            error_log("Error getting total visits: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
            return 0;
        }
    }
    
    public function getTodayVisits() {
        $query = "SELECT COALESCE(SUM(visit_count), 0) as total FROM " . $this->table_name . "
                 WHERE visit_date = :today";
        
        try {
            $stmt = $this->conn->prepare($query);
            $today = date('Y-m-d');
            $stmt->bindParam(':today', $today);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = (int) ($row['total'] ?? 0);
            error_log("Today visits fetched for $today: $total at " . date('Y-m-d H:i:s'));
            return $total;
        } catch (PDOException $e) {
            error_log("Error getting today's visits: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
            return 0;
        }
    }
    
    public function getStatsRange($start_date, $end_date, $interval = 'day') {
        if ($interval == 'month') {
            $group_by = "DATE_FORMAT(created_at, '%Y-%m')";
        } else {
            $group_by = "visit_date";
        }
        
        $query = "SELECT " . $group_by . " as period, COALESCE(SUM(visit_count), 0) as count 
                FROM " . $this->table_name . "
                WHERE visit_date BETWEEN :start_date AND :end_date
                GROUP BY " . $group_by . "
                ORDER BY period ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Raw stats range fetched for $start_date to $end_date: " . json_encode($result) . " at " . date('Y-m-d H:i:s'));
            
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
                        $filled_result[] = ['period' => $date_str, 'count' => (int)$row['count']];
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $filled_result[] = ['period' => $date_str, 'count' => 0];
                }
            }
            
            error_log("Filled stats range: " . json_encode($filled_result) . " at " . date('Y-m-d H:i:s'));
            return $filled_result;
        } catch (PDOException $e) {
            error_log("Error getting statistics range: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
            return [];
        }
    }
    
    public function getUniqueVisitors($start_date, $end_date) {
        $query = "SELECT COUNT(DISTINCT session_id) as unique_count 
                FROM " . $this->table_name . "
                WHERE visit_date BETWEEN :start_date AND :end_date";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $unique_count = (int) ($row['unique_count'] ?? 0);
            error_log("Unique visitors fetched for $start_date to $end_date: $unique_count at " . date('Y-m-d H:i:s'));
            return $unique_count;
        } catch (PDOException $e) {
            error_log("Error getting unique visitors: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
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
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Top pages fetched (limit $limit): " . json_encode($result) . " at " . date('Y-m-d H:i:s'));
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting top pages: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
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
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Referring sources fetched (limit $limit): " . json_encode($result) . " at " . date('Y-m-d H:i:s'));
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting referring sources: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
            return [];
        }
    }
}
?>