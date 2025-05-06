<?php
class Settings {
    // Database connection and table name
    private $conn;
    private $table_name = "settings";
    
    // Object properties
    public $id;
    public $setting_key;
    public $setting_value;
    public $created_at;
    public $updated_at;
    
    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Read all settings
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY setting_key ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read single setting by key
    public function readByKey($key) {
        if (!$this->conn) {
            return false;
        }
        
        $query = "SELECT * FROM " . $this->table_name . " WHERE setting_key = ? LIMIT 0,1";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $key);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if($row) {
                $this->id = $row['id'];
                $this->setting_key = $row['setting_key'];
                $this->setting_value = $row['setting_value'];
                $this->created_at = $row['created_at'];
                $this->updated_at = $row['updated_at'];
                return true;
            }
        } catch(PDOException $e) {
            // Handle database error gracefully
            error_log("Database error in Settings::readByKey: " . $e->getMessage());
        }
        
        return false;
    }
    
    // Get value by key
    public function getValue($key, $default = '') {
        if ($this->readByKey($key)) {
            return $this->setting_value;
        }
        
        return $default;
    }
    
    // Create or update setting
    public function set($key, $value) {
        // Check if setting exists
        $this->setting_key = $key;
        if ($this->readByKey($key)) {
            // Update existing setting
            $query = "UPDATE " . $this->table_name . " 
                    SET 
                        setting_value = :setting_value, 
                        updated_at = :updated_at 
                    WHERE 
                        setting_key = :setting_key";
            
            $stmt = $this->conn->prepare($query);
            
            // Sanitize inputs
            $this->setting_value = htmlspecialchars(strip_tags($value));
            $this->updated_at = date('Y-m-d H:i:s');
            
            // Bind parameters
            $stmt->bindParam(':setting_value', $this->setting_value);
            $stmt->bindParam(':updated_at', $this->updated_at);
            $stmt->bindParam(':setting_key', $this->setting_key);
            
            // Execute the query
            if($stmt->execute()) {
                return true;
            }
            
            return false;
        } else {
            // Create new setting
            $query = "INSERT INTO " . $this->table_name . " 
                    SET 
                        setting_key = :setting_key, 
                        setting_value = :setting_value, 
                        created_at = :created_at, 
                        updated_at = :updated_at";
            
            $stmt = $this->conn->prepare($query);
            
            // Sanitize inputs
            $this->setting_key = htmlspecialchars(strip_tags($key));
            $this->setting_value = htmlspecialchars(strip_tags($value));
            $this->created_at = date('Y-m-d H:i:s');
            $this->updated_at = date('Y-m-d H:i:s');
            
            // Bind parameters
            $stmt->bindParam(':setting_key', $this->setting_key);
            $stmt->bindParam(':setting_value', $this->setting_value);
            $stmt->bindParam(':created_at', $this->created_at);
            $stmt->bindParam(':updated_at', $this->updated_at);
            
            // Execute the query
            if($stmt->execute()) {
                return true;
            }
            
            return false;
        }
    }
    
    // Delete setting
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Load settings as array
    public function loadSettings() {
        $settings = [];
        $stmt = $this->read();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }
    
    // Get site colors
    public function getSiteColors() {
        $colors = [
            'primary_color' => $this->getValue('primary_color', '#ff6b6b'),
            'secondary_color' => $this->getValue('secondary_color', '#4ecdc4'),
            'text_color' => $this->getValue('text_color', '#333333'),
            'background_color' => $this->getValue('background_color', '#ffffff'),
            'footer_color' => $this->getValue('footer_color', '#292b2c')
        ];
        
        return $colors;
    }
}
?>
