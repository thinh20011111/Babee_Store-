<?php
class Banner {
    // Database connection and table name
    private $conn;
    private $table_name = "banners";
    
    // Object properties
    public $id;
    public $title;
    public $subtitle;
    public $image;
    public $link;
    public $position;
    public $is_active;
    public $created_at;
    public $updated_at;
    
    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Read all banners
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY position ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read active banners
    public function readActive() {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE is_active = 1 
                ORDER BY position ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read single banner
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->title = $row['title'];
            $this->subtitle = $row['subtitle'];
            $this->image = $row['image'];
            $this->link = $row['link'];
            $this->position = $row['position'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }
    
    // Create banner
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET 
                    title = :title, 
                    subtitle = :subtitle, 
                    image = :image, 
                    link = :link, 
                    position = :position, 
                    is_active = :is_active, 
                    created_at = :created_at, 
                    updated_at = :updated_at";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->subtitle = htmlspecialchars(strip_tags($this->subtitle));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->link = htmlspecialchars(strip_tags($this->link));
        $this->position = htmlspecialchars(strip_tags($this->position));
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
        
        // Bind parameters
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':subtitle', $this->subtitle);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':link', $this->link);
        $stmt->bindParam(':position', $this->position);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':created_at', $this->created_at);
        $stmt->bindParam(':updated_at', $this->updated_at);
        
        // Execute the query
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    // Update banner
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET 
                    title = :title, 
                    subtitle = :subtitle, 
                    image = :image, 
                    link = :link, 
                    position = :position, 
                    is_active = :is_active, 
                    updated_at = :updated_at 
                WHERE 
                    id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->subtitle = htmlspecialchars(strip_tags($this->subtitle));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->link = htmlspecialchars(strip_tags($this->link));
        $this->position = htmlspecialchars(strip_tags($this->position));
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));
        $this->updated_at = date('Y-m-d H:i:s');
        
        // Bind parameters
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':subtitle', $this->subtitle);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':link', $this->link);
        $stmt->bindParam(':position', $this->position);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':id', $this->id);
        
        // Execute the query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Delete banner
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Get next position
    public function getNextPosition() {
        $query = "SELECT MAX(position) as max_position FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ($row['max_position'] ?? 0) + 1;
    }
    
    // Count active banners
    public function countActive() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
}
?>
