<?php
class Product {
    // Database connection and table name
    private $conn;
    private $table_name = "products";
    
    // Object properties
    public $id;
    public $name;
    public $description;
    public $price;
    public $sale_price;
    public $category_id;
    public $image;
    public $is_featured;
    public $is_sale;
    public $created_at;
    public $updated_at;
    public $variants; // Added to store product variants
    
    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Read all products with pagination
    public function read($items_per_page = null, $page = 1) {
        $query = "SELECT p.*, c.name as category_name
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY p.created_at DESC";
        
        // Add pagination if items_per_page is specified
        if ($items_per_page) {
            $start = ($page - 1) * $items_per_page;
            $query .= " LIMIT ?, ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $start, PDO::PARAM_INT);
            $stmt->bindParam(2, $items_per_page, PDO::PARAM_INT);
        } else {
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read featured products
    public function readFeatured($limit = 8) {
        if (!$this->conn) {
            return false;
        }
        
        try {
            $query = "SELECT p.*, c.name as category_name
                    FROM " . $this->table_name . " p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.is_featured = 1
                    ORDER BY p.created_at DESC
                    LIMIT ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt;
        } catch(PDOException $e) {
            error_log("Database error in Product::readFeatured: " . $e->getMessage());
            return false;
        }
    }
    
    // Read all sale products (simple version)
    public function readAllSale($limit = 8) {
        if (!$this->conn) {
            return false;
        }
        
        try {
            $query = "SELECT p.*, c.name as category_name
                    FROM " . $this->table_name . " p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.is_sale = 1
                    ORDER BY p.created_at DESC
                    LIMIT ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt;
        } catch(PDOException $e) {
            error_log("Database error in Product::readAllSale: " . $e->getMessage());
            return false;
        }
    }
    
    // Read products by category
    public function readByCategory($category_id, $page = 1, $items_per_page = ITEMS_PER_PAGE) {
        // Calculate the starting row
        $start = ($page - 1) * $items_per_page;
        
        $query = "SELECT p.*, c.name as category_name
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.category_id = ?
                ORDER BY p.created_at DESC
                LIMIT ?, ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $category_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $start, PDO::PARAM_INT);
        $stmt->bindParam(3, $items_per_page, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read related products (same category, exclude current product)
    public function readRelatedProducts($product_id, $category_id, $limit = 4) {
        if (!$this->conn) {
            return false;
        }
        
        try {
            $query = "SELECT p.*, c.name as category_name
                    FROM " . $this->table_name . " p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.category_id = ? AND p.id != ?
                    ORDER BY RAND()
                    LIMIT ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $category_id, PDO::PARAM_INT);
            $stmt->bindParam(2, $product_id, PDO::PARAM_INT);
            $stmt->bindParam(3, $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt;
        } catch(PDOException $e) {
            error_log("Database error in Product::readRelatedProducts: " . $e->getMessage());
            return false;
        }
    }
    
    // Read sale products with pagination
    public function readSale($limit = 8, $offset = 0) {
        if (!$this->conn) {
            return false;
        }
        
        try {
            $query = "SELECT p.*, c.name as category_name
                    FROM " . $this->table_name . " p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.is_sale = 1
                    ORDER BY p.created_at DESC
                    LIMIT ?, ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $offset, PDO::PARAM_INT);
            $stmt->bindParam(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt;
        } catch(PDOException $e) {
            error_log("Database error in Product::readSale with pagination: " . $e->getMessage());
            return false;
        }
    }
    
    // Count sale products
    public function countSale() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE is_sale = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
    
    // Count products by category
    public function countByCategory($category_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE category_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $category_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
    
    // Read single product
    public function readOne() {
        $query = "SELECT p.*, c.name as category_name
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = ?
                LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->price = $row['price'];
            $this->sale_price = $row['sale_price'];
            $this->category_id = $row['category_id'];
            $this->image = $row['image'];
            $this->is_featured = $row['is_featured'];
            $this->is_sale = $row['is_sale'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            $this->variants = $this->getVariants(); // Load variants
            return true;
        }
        
        return false;
    }
    
    // Get product variants
    public function getVariants() {
        $query = "SELECT id, product_id, color, size, price, stock FROM product_variants WHERE product_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Variants for product ID {$this->id}: " . json_encode($variants));
        return $variants;
    }
    
    // Get total stock from variants
    public function getTotalStock() {
        $query = "SELECT SUM(stock) as total_stock FROM product_variants WHERE product_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("Total stock for product ID {$this->id}: " . ($row['total_stock'] ?? 0));
        return $row['total_stock'] ?? 0;
    }
    
    // Create product
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET 
                    name = :name, 
                    description = :description, 
                    price = :price, 
                    sale_price = :sale_price, 
                    category_id = :category_id, 
                    image = :image, 
                    is_featured = :is_featured, 
                    is_sale = :is_sale, 
                    created_at = :created_at, 
                    updated_at = :updated_at";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->sale_price = htmlspecialchars(strip_tags($this->sale_price));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->is_featured = htmlspecialchars(strip_tags($this->is_featured));
        $this->is_sale = htmlspecialchars(strip_tags($this->is_sale));
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
        
        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':sale_price', $this->sale_price);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':is_featured', $this->is_featured);
        $stmt->bindParam(':is_sale', $this->is_sale);
        $stmt->bindParam(':created_at', $this->created_at);
        $stmt->bindParam(':updated_at', $this->updated_at);
        
        // Execute the query
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    // Update product
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET 
                    name = :name, 
                    description = :description, 
                    price = :price, 
                    sale_price = :sale_price, 
                    category_id = :category_id, 
                    image = :image, 
                    is_featured = :is_featured, 
                    is_sale = :is_sale, 
                    updated_at = :updated_at 
                WHERE 
                    id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->sale_price = htmlspecialchars(strip_tags($this->sale_price));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->is_featured = htmlspecialchars(strip_tags($this->is_featured));
        $this->is_sale = htmlspecialchars(strip_tags($this->is_sale));
        $this->updated_at = date('Y-m-d H:i:s');
        
        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':sale_price', $this->sale_price);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':is_featured', $this->is_featured);
        $stmt->bindParam(':is_sale', $this->is_sale);
        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':id', $this->id);
        
        // Execute the query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Delete product
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Search products
    public function search($keywords, $page = 1, $items_per_page = ITEMS_PER_PAGE) {
        // Calculate the starting row
        $start = ($page - 1) * $items_per_page;
        
        $query = "SELECT p.*, c.name as category_name
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE 
                    p.name LIKE ? OR 
                    p.description LIKE ? OR 
                    c.name LIKE ? 
                ORDER BY p.created_at DESC
                LIMIT ?, ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize keywords
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";
        
        // Bind parameters
        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);
        $stmt->bindParam(4, $start, PDO::PARAM_INT);
        $stmt->bindParam(5, $items_per_page, PDO::PARAM_INT);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Count search results
    public function countSearch($keywords) {
        $query = "SELECT COUNT(*) as total
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE 
                    p.name LIKE ? OR 
                    p.description LIKE ? OR 
                    c.name LIKE ?";
        
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
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
    
    // Get bestselling products
    public function getBestsellers($limit = 5) {
        $query = "SELECT p.*, c.name as category_name, 
                    SUM(oi.quantity) as total_sold
                FROM " . $this->table_name . " p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN order_items oi ON p.id = oi.product_id
                GROUP BY p.id
                ORDER BY total_sold DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Update stock for a specific variant
    public function updateVariantStock($variant_id, $quantity) {
        $query = "UPDATE product_variants 
                SET 
                    stock = stock - ?, 
                    updated_at = NOW() 
                WHERE 
                    id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $quantity, PDO::PARAM_INT);
        $stmt->bindParam(2, $variant_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Count all products
    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
    
    // Save product variants
    public function saveVariants($variants) {
        try {
            // Xóa các biến thể cũ của sản phẩm
            $query = "DELETE FROM product_variants WHERE product_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
            $stmt->execute();
            error_log("Deleted old variants for product ID: " . $this->id);

            // Thêm các biến thể mới
            $query = "INSERT INTO product_variants (product_id, color, size, price, stock) 
                      VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);

            foreach ($variants as $variant) {
                $color = htmlspecialchars(strip_tags($variant['color'] ?? ''));
                $size = htmlspecialchars(strip_tags($variant['size'] ?? ''));
                $price = floatval($variant['price'] ?? 0);
                $stock = intval($variant['stock'] ?? 0);

                $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
                $stmt->bindParam(2, $color);
                $stmt->bindParam(3, $size);
                $stmt->bindParam(4, $price);
                $stmt->bindParam(5, $stock, PDO::PARAM_INT);

                $stmt->execute();
                error_log("Inserted variant for product ID: " . $this->id . ", color: " . $color . ", size: " . $size);
            }

            return true;
        } catch (PDOException $e) {
            error_log("Error saving variants for product ID: " . $this->id . " - " . $e->getMessage());
            throw new Exception("Error saving variants: " . $e->getMessage());
        }
    }
}
?>