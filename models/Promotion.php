<?php
class Promotion {
    // Database connection and table name
    private $conn;
    private $table_name = "promotions";
    
    // Object properties
    public $id;
    public $name;
    public $code;
    public $discount_type;
    public $discount_value;
    public $start_date;
    public $end_date;
    public $is_active;
    public $min_purchase;
    public $usage_limit;
    public $usage_count;
    public $created_at;
    public $updated_at;
    
    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Read all promotions
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read active promotions
    public function readActive() {
        $today = date('Y-m-d');
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE is_active = 1 
                AND start_date <= ? 
                AND (end_date >= ? OR end_date IS NULL)
                ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $today);
        $stmt->bindParam(2, $today);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read single promotion
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->name = $row['name'];
            $this->code = $row['code'];
            $this->discount_type = $row['discount_type'];
            $this->discount_value = $row['discount_value'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->is_active = $row['is_active'];
            $this->min_purchase = $row['min_purchase'];
            $this->usage_limit = $row['usage_limit'];
            $this->usage_count = $row['usage_count'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }
    
    // Read by code
    public function readByCode() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE code = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->code);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->discount_type = $row['discount_type'];
            $this->discount_value = $row['discount_value'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->is_active = $row['is_active'];
            $this->min_purchase = $row['min_purchase'];
            $this->usage_limit = $row['usage_limit'];
            $this->usage_count = $row['usage_count'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }
    
    // Create promotion
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET 
                    name = :name, 
                    code = :code, 
                    discount_type = :discount_type, 
                    discount_value = :discount_value, 
                    start_date = :start_date, 
                    end_date = :end_date, 
                    is_active = :is_active, 
                    min_purchase = :min_purchase, 
                    usage_limit = :usage_limit, 
                    usage_count = :usage_count, 
                    created_at = :created_at, 
                    updated_at = :updated_at";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->code = htmlspecialchars(strip_tags($this->code));
        $this->discount_type = htmlspecialchars(strip_tags($this->discount_type));
        $this->discount_value = htmlspecialchars(strip_tags($this->discount_value));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->end_date = $this->end_date ? htmlspecialchars(strip_tags($this->end_date)) : null;
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));
        $this->min_purchase = htmlspecialchars(strip_tags($this->min_purchase));
        $this->usage_limit = htmlspecialchars(strip_tags($this->usage_limit));
        $this->usage_count = 0; // New promotion starts with 0 uses
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
        
        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':discount_type', $this->discount_type);
        $stmt->bindParam(':discount_value', $this->discount_value);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':min_purchase', $this->min_purchase);
        $stmt->bindParam(':usage_limit', $this->usage_limit);
        $stmt->bindParam(':usage_count', $this->usage_count);
        $stmt->bindParam(':created_at', $this->created_at);
        $stmt->bindParam(':updated_at', $this->updated_at);
        
        // Execute the query
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    // Update promotion
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET 
                    name = :name, 
                    code = :code, 
                    discount_type = :discount_type, 
                    discount_value = :discount_value, 
                    start_date = :start_date, 
                    end_date = :end_date, 
                    is_active = :is_active, 
                    min_purchase = :min_purchase, 
                    usage_limit = :usage_limit, 
                    updated_at = :updated_at 
                WHERE 
                    id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->code = htmlspecialchars(strip_tags($this->code));
        $this->discount_type = htmlspecialchars(strip_tags($this->discount_type));
        $this->discount_value = htmlspecialchars(strip_tags($this->discount_value));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->end_date = $this->end_date ? htmlspecialchars(strip_tags($this->end_date)) : null;
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));
        $this->min_purchase = htmlspecialchars(strip_tags($this->min_purchase));
        $this->usage_limit = htmlspecialchars(strip_tags($this->usage_limit));
        $this->updated_at = date('Y-m-d H:i:s');
        
        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':discount_type', $this->discount_type);
        $stmt->bindParam(':discount_value', $this->discount_value);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':min_purchase', $this->min_purchase);
        $stmt->bindParam(':usage_limit', $this->usage_limit);
        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':id', $this->id);
        
        // Execute the query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Delete promotion
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Validate promotion code
    public function validateCode($total_amount = 0) {
        // Check if promotion exists
        if (!$this->code || !$this->readByCode()) {
            return [
                'valid' => false,
                'message' => 'Invalid promotion code.'
            ];
        }
        
        // Check if promotion is active
        if ($this->is_active != 1) {
            return [
                'valid' => false,
                'message' => 'This promotion is not active.'
            ];
        }
        
        // Check dates
        $today = date('Y-m-d');
        if ($this->start_date > $today) {
            return [
                'valid' => false,
                'message' => 'This promotion is not yet active.'
            ];
        }
        
        if ($this->end_date && $this->end_date < $today) {
            return [
                'valid' => false,
                'message' => 'This promotion has expired.'
            ];
        }
        
        // Check usage limit
        if ($this->usage_limit > 0 && $this->usage_count >= $this->usage_limit) {
            return [
                'valid' => false,
                'message' => 'This promotion has reached its usage limit.'
            ];
        }
        
        // Check minimum purchase amount
        if ($this->min_purchase > 0 && $total_amount < $this->min_purchase) {
            return [
                'valid' => false,
                'message' => 'Minimum purchase amount of ' . CURRENCY . number_format($this->min_purchase) . ' required.'
            ];
        }
        
        // Promotion is valid
        return [
            'valid' => true,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'message' => 'Promotion applied successfully.'
        ];
    }
    
    // Increment usage count
    public function incrementUsage() {
        $query = "UPDATE " . $this->table_name . " 
                SET 
                    usage_count = usage_count + 1, 
                    updated_at = NOW() 
                WHERE 
                    id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Calculate discount amount
    public function calculateDiscount($total_amount) {
        if ($this->discount_type == 'percentage') {
            return ($total_amount * $this->discount_value) / 100;
        } else { // fixed amount
            return $this->discount_value;
        }
    }
    
    // Count active promotions
    public function countActive() {
        $today = date('Y-m-d');
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                WHERE is_active = 1 
                AND start_date <= ? 
                AND (end_date >= ? OR end_date IS NULL)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $today);
        $stmt->bindParam(2, $today);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
}
?>
