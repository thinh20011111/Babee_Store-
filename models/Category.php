<?php
class Category {
    // Database connection and table name
    private $conn;
    private $table_name = "categories";
    
    // Object properties
    public $id;
    public $name;
    public $description;
    public $image;
    public $created_at;
    public $updated_at;
    
    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Read all categories
    public function read() {
        if (!$this->conn) {
            error_log("Lỗi trong Category::read: Kết nối cơ sở dữ liệu là null");
            return false;
        }
        
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch(PDOException $e) {
            error_log("Lỗi cơ sở dữ liệu trong Category::read: " . $e->getMessage());
            return false;
        }
    }
    
    // Read single category
    public function readOne() {
        if (!$this->conn) {
            error_log("Lỗi trong Category::readOne: Kết nối cơ sở dữ liệu là null");
            return false;
        }

        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->image = $row['image'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }
    
    // Create category
    public function create() {
        if (!$this->conn) {
            error_log("Lỗi trong Category::create: Kết nối cơ sở dữ liệu là null");
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " 
                SET 
                    name = :name, 
                    description = :description, 
                    image = :image, 
                    created_at = :created_at, 
                    updated_at = :updated_at";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
        
        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':created_at', $this->created_at);
        $stmt->bindParam(':updated_at', $this->updated_at);
        
        // Execute the query
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    // Update category
    public function update() {
        if (!$this->conn) {
            error_log("Lỗi trong Category::update: Kết nối cơ sở dữ liệu là null");
            return false;
        }

        $query = "UPDATE " . $this->table_name . " 
                SET 
                    name = :name, 
                    description = :description, 
                    image = :image, 
                    updated_at = :updated_at 
                WHERE 
                    id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->updated_at = date('Y-m-d H:i:s');
        
        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':id', $this->id);
        
        // Execute the query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Delete category
    public function delete() {
        if (!$this->conn) {
            error_log("Lỗi trong Category::delete: Kết nối cơ sở dữ liệu là null");
            return false;
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Get category name by ID
    public function getNameById($id) {
        if (!$this->conn) {
            error_log("Lỗi trong Category::getNameById: Kết nối cơ sở dữ liệu là null");
            return "";
        }

        $query = "SELECT name FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? $row['name'] : "";
    }
    
    // Count products in category
    public function countProducts() {
        if (!$this->conn) {
            error_log("Lỗi trong Category::countProducts: Kết nối cơ sở dữ liệu là null");
            return 0;
        }

        $query = "SELECT COUNT(*) as total FROM products WHERE category_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
    
    // Count all categories
    public function countAll() {
        if (!$this->conn) {
            error_log("Lỗi trong Category::countAll: Kết nối cơ sở dữ liệu là null");
            return 0;
        }

        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
}
?>