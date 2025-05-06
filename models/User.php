<?php
class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";
    
    // Object properties
    public $id;
    public $username;
    public $email;
    public $password;
    public $full_name;
    public $phone;
    public $address;
    public $role;
    public $created_at;
    public $updated_at;
    
    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Read all users
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read single user
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->full_name = $row['full_name'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->role = $row['role'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }
    
    // Read by email
    public function readByEmail() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->password = $row['password'];
            $this->full_name = $row['full_name'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->role = $row['role'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }
    
    // Create user
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET 
                    username = :username, 
                    email = :email, 
                    password = :password, 
                    full_name = :full_name, 
                    phone = :phone, 
                    address = :address, 
                    role = :role, 
                    created_at = :created_at, 
                    updated_at = :updated_at";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT); // Hash the password
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
        
        // Bind parameters
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':created_at', $this->created_at);
        $stmt->bindParam(':updated_at', $this->updated_at);
        
        // Execute the query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }
    
    // Update user
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET 
                    username = :username, 
                    email = :email, 
                    full_name = :full_name, 
                    phone = :phone, 
                    address = :address, 
                    role = :role, 
                    updated_at = :updated_at
                WHERE 
                    id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->updated_at = date('Y-m-d H:i:s');
        
        // Bind parameters
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':id', $this->id);
        
        // Execute the query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Update password
    public function updatePassword() {
        $query = "UPDATE " . $this->table_name . " 
                SET 
                    password = :password,
                    updated_at = :updated_at
                WHERE 
                    id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash the password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->updated_at = date('Y-m-d H:i:s');
        
        // Bind parameters
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':id', $this->id);
        
        // Execute the query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Delete user
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Search users
    public function search($keywords) {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE 
                    username LIKE ? OR 
                    email LIKE ? OR 
                    full_name LIKE ? 
                ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize keywords
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";
        
        // Bind parameters
        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Check if email exists
    public function emailExists() {
        $query = "SELECT id, username, password, role FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->password = $row['password'];
            $this->role = $row['role'];
            return true;
        }
        
        return false;
    }
    
    // Login verification
    public function verifyPassword($password) {
        // Verify password
        return password_verify($password, $this->password);
    }
    
    // Count users
    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
}
?>
