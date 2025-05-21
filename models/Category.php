<?php
class Category {
    private $conn;
    private $table_name = "categories";
    
    public $id;
    public $name;
    public $description;
    public $image;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
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
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':created_at', $this->created_at);
        $stmt->bindParam(':updated_at', $this->updated_at);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
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
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->updated_at = date('Y-m-d H:i:s');
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':id', $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Other methods (delete, getNameById, countProducts, countAll) remain unchanged
}
?>