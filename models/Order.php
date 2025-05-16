<?php
class Order {
    // Database connection and table name
    private $conn;
    private $table_name = "orders";
    
    // Object properties
    public $id;
    public $user_id;
    public $order_number;
    public $total_amount;
    public $status;
    public $payment_method;
    public $shipping_address;
    public $shipping_city;
    public $shipping_phone;
    public $customer_email;
    public $shipping_name;
    public $notes;
    public $created_at;
    public $updated_at;
    
    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Read all orders
    public function read($limit = null) {
        $query = "SELECT o.*, u.username, u.email, u.full_name
                FROM " . $this->table_name . " o
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT " . $limit;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read user orders
    public function readUserOrders($user_id) {
        $query = "SELECT o.*
                FROM " . $this->table_name . " o
                WHERE o.user_id = ?
                ORDER BY o.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read single order
    public function readOne() {
        $query = "SELECT o.*, u.username, u.email, u.full_name
                FROM " . $this->table_name . " o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = ?
                LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->user_id = $row['user_id'];
            $this->order_number = $row['order_number'];
            $this->total_amount = $row['total_amount'];
            $this->status = $row['status'];
            $this->payment_method = $row['payment_method'];
            $this->shipping_address = $row['shipping_address'];
            $this->shipping_city = $row['shipping_city'];
            $this->shipping_phone = $row['shipping_phone'];
            $this->customer_email = $row['customer_email'];
            $this->shipping_name = $row['shipping_name'];
            $this->notes = $row['notes'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }
    
    // Create order
    public function create() {
        try {
            $this->order_number = $this->generateOrderNumber();
            
            $query = "INSERT INTO " . $this->table_name . " 
                    SET 
                        user_id = :user_id, 
                        order_number = :order_number, 
                        total_amount = :total_amount, 
                        status = :status, 
                        payment_method = :payment_method, 
                        shipping_address = :shipping_address, 
                        shipping_city = :shipping_city, 
                        shipping_phone = :shipping_phone, 
                        customer_email = :customer_email, 
                        shipping_name = :shipping_name, 
                        notes = :notes, 
                        created_at = :created_at, 
                        updated_at = :updated_at";
            
            $stmt = $this->conn->prepare($query);
            
            $this->user_id = htmlspecialchars(strip_tags($this->user_id));
            $this->order_number = htmlspecialchars(strip_tags($this->order_number));
            $this->total_amount = htmlspecialchars(strip_tags($this->total_amount));
            $this->status = htmlspecialchars(strip_tags($this->status));
            $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));
            $this->shipping_address = htmlspecialchars(strip_tags($this->shipping_address));
            $this->shipping_city = htmlspecialchars(strip_tags($this->shipping_city));
            $this->shipping_phone = htmlspecialchars(strip_tags($this->shipping_phone));
            $this->customer_email = htmlspecialchars(strip_tags($this->customer_email));
            $this->shipping_name = htmlspecialchars(strip_tags($this->shipping_name));
            $this->notes = htmlspecialchars(strip_tags($this->notes));
            $this->created_at = date('Y-m-d H:i:s');
            $this->updated_at = date('Y-m-d H:i:s');
            
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->bindParam(':order_number', $this->order_number);
            $stmt->bindParam(':total_amount', $this->total_amount);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':payment_method', $this->payment_method);
            $stmt->bindParam(':shipping_address', $this->shipping_address);
            $stmt->bindParam(':shipping_city', $this->shipping_city);
            $stmt->bindParam(':shipping_phone', $this->shipping_phone);
            $stmt->bindParam(':customer_email', $this->customer_email);
            $stmt->bindParam(':shipping_name', $this->shipping_name);
            $stmt->bindParam(':notes', $this->notes);
            $stmt->bindParam(':created_at', $this->created_at);
            $stmt->bindParam(':updated_at', $this->updated_at);
            
            $stmt->execute();
            
            $this->id = $this->conn->lastInsertId();
            
            return $this->id;
        } catch (Exception $e) {
            error_log("Error creating order: " . $e->getMessage(), 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
            throw $e;
        }
    }
    
    // Update order
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET 
                    status = :status, 
                    payment_method = :payment_method, 
                    shipping_address = :shipping_address, 
                    shipping_city = :shipping_city, 
                    shipping_phone = :shipping_phone, 
                    customer_email = :customer_email, 
                    shipping_name = :shipping_name, 
                    notes = :notes, 
                    updated_at = :updated_at 
                WHERE 
                    id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));
        $this->shipping_address = htmlspecialchars(strip_tags($this->shipping_address));
        $this->shipping_city = htmlspecialchars(strip_tags($this->shipping_city));
        $this->shipping_phone = htmlspecialchars(strip_tags($this->shipping_phone));
        $this->customer_email = htmlspecialchars(strip_tags($this->customer_email));
        $this->shipping_name = htmlspecialchars(strip_tags($this->shipping_name));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        $this->updated_at = date('Y-m-d H:i:s');
        
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':payment_method', $this->payment_method);
        $stmt->bindParam(':shipping_address', $this->shipping_address);
        $stmt->bindParam(':shipping_city', $this->shipping_city);
        $stmt->bindParam(':shipping_phone', $this->shipping_phone);
        $stmt->bindParam(':customer_email', $this->customer_email);
        $stmt->bindParam(':shipping_name', $this->shipping_name);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Update order status
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " 
                SET 
                    status = :status, 
                    updated_at = :updated_at 
                WHERE 
                    id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->updated_at = date('Y-m-d H:i:s');
        
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Delete order
    public function delete() {
        try {
            $query = "DELETE FROM order_items WHERE order_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->execute();
            
            $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->execute();
            
            return true;
        } catch (Exception $e) {
            error_log("Error deleting order: " . $e->getMessage(), 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
            throw $e;
        }
    }
    
    // Add order details
    public function addOrderDetails($product_id, $quantity, $price, $variant_id) {
        $query = "INSERT INTO order_items
                SET 
                    order_id = :order_id, 
                    product_id = :product_id, 
                    quantity = :quantity, 
                    price = :price,
                    variant_id = :variant_id";
        
        $stmt = $this->conn->prepare($query);
        
        $product_id = htmlspecialchars(strip_tags($product_id));
        $quantity = htmlspecialchars(strip_tags($quantity));
        $price = htmlspecialchars(strip_tags($price));
        $variant_id = htmlspecialchars(strip_tags($variant_id));
        
        $stmt->bindParam(':order_id', $this->id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':variant_id', $variant_id);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Get order details
    public function getOrderDetails() {
        $query = "SELECT oi.*, p.name as product_name, p.image
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Generate unique order number
    private function generateOrderNumber() {
        return 'BB-' . date('Ymd') . rand(1000, 9999);
    }
    
    // Get revenue by date range
    public function getRevenue($start_date, $end_date) {
        $query = "SELECT DATE(created_at) as order_date, SUM(total_amount) as revenue
                FROM " . $this->table_name . "
                WHERE created_at BETWEEN ? AND ?
                AND status != 'cancelled'
                GROUP BY DATE(created_at)
                ORDER BY order_date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $start_date);
        $stmt->bindParam(2, $end_date);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get monthly revenue for last 12 months
    public function getMonthlyRevenue() {
        $query = "SELECT YEAR(created_at) as year, MONTH(created_at) as month, SUM(total_amount) as revenue
                FROM " . $this->table_name . "
                WHERE status != 'cancelled'
                GROUP BY YEAR(created_at), MONTH(created_at)
                ORDER BY year DESC, month DESC
                LIMIT 12";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Count orders by status
    public function countByStatus($status) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $status);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)($row['total'] ?? 0);
    }
    
    // Count all orders
    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)($row['total'] ?? 0);
    }
    
    // Get total revenue
    public function getTotalRevenue() {
        $query = "SELECT SUM(total_amount) as total FROM " . $this->table_name . " WHERE status != 'cancelled'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (float)($row['total'] ?? 0);
    }
    
    // Get recent orders
    public function getRecentOrders($limit = 5) {
        $query = "SELECT o.*, u.username, u.email, u.full_name
                FROM " . $this->table_name . " o
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }
}
?>