<?php
/**
 * Model for tracking and retrieving website traffic statistics
 */
class TrafficLog {
    // Database connection and table name
    private $conn;
    private $table_name = "traffic_logs";
    
    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
        
        // Create the table if it doesn't exist
        $this->initializeTable();
    }
    
    /**
     * Initialize the traffic_logs table if it doesn't exist
     */
    private function initializeTable() {
        $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address TEXT,
            user_agent TEXT,
            page_url TEXT,
            referer_url TEXT,
            user_id INTEGER NULL,
            session_id TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        try {
            $this->conn->exec($query);
        } catch (PDOException $e) {
            error_log("Error creating traffic logs table: " . $e->getMessage());
            throw new Exception("Could not initialize traffic logs table");
        }
    }
    
    /**
     * Log a website access
     * @return boolean success/failure
     */
    public function logAccess() {
        // Get visitor information
        $ip_address = $this->getIpAddress();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $page_url = $_SERVER['REQUEST_URI'] ?? '/';
        $referer_url = $_SERVER['HTTP_REFERER'] ?? '';
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $session_id = session_id();
        
        // SQL query to insert the log
        $query = "INSERT INTO " . $this->table_name . " 
                (ip_address, user_agent, page_url, referer_url, user_id, session_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                $ip_address,
                $user_agent,
                $page_url,
                $referer_url,
                $user_id,
                $session_id
            ]);
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error logging traffic: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get the client's real IP address
     * @return string IP address
     */
    private function getIpAddress() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }
    
    /**
     * Get the total number of visits
     * @return int
     */
    public function getTotalVisits() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total'];
        } catch (PDOException $e) {
            error_log("Error getting total visits: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get the number of visits today
     * @return int
     */
    public function getTodayVisits() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . "
                 WHERE date(created_at) = date('now')";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total'];
        } catch (PDOException $e) {
            error_log("Error getting today's visits: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get statistics for a date range
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @param string $interval 'day' or 'month'
     * @return array
     */
    public function getStatsRange($start_date, $end_date, $interval = 'day') {
        // Format for grouping
        if ($interval == 'month') {
            $format = "%Y-%m";
            $label = "substr(created_at, 1, 7)"; // YYYY-MM
        } else {
            $format = "%Y-%m-%d";
            $label = "date(created_at)"; // YYYY-MM-DD
        }
        
        $query = "SELECT " . $label . " as period, COUNT(*) as count 
                FROM " . $this->table_name . "
                WHERE date(created_at) BETWEEN :start_date AND :end_date
                GROUP BY " . $label . "
                ORDER BY period ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            
            $result = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $row;
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting statistics range: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get unique visitor count for a date range
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @return int
     */
    public function getUniqueVisitors($start_date, $end_date) {
        $query = "SELECT COUNT(DISTINCT session_id) as unique_count 
                FROM " . $this->table_name . "
                WHERE date(created_at) BETWEEN :start_date AND :end_date";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['unique_count'];
        } catch (PDOException $e) {
            error_log("Error getting unique visitors: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get most visited pages
     * @param int $limit Number of results to return
     * @return array
     */
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
            
            $result = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $row;
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting top pages: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get traffic stats by referring sources
     * @param int $limit Number of results to return
     * @return array
     */
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
            
            $result = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $row;
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting referring sources: " . $e->getMessage());
            return [];
        }
    }
}
?>