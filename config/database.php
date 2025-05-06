<?php
class Database {
    // Database credentials for InfinityFree
    private $host = "sql202.infinityfree.com";
    private $db_name = "if0_38706403_babee_store";
    private $username = "if0_38706403";
    private $password = "Haiyen2308";
    private $conn;
    
    // Get database connection
    public function getConnection() {
        $this->conn = null;
        
        try {
            // For local development, use SQLite instead of remote MySQL
            if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '0.0.0.0' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
                $this->conn = new PDO('sqlite:./database.sqlite');
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->createLocalTables();
            } else {
                // Original remote connection for production
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                    $this->username, 
                    $this->password
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->exec("set names utf8");
            }
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
    
    // Create local database tables for development
    private function createLocalTables() {
        // Create users table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            full_name TEXT,
            phone TEXT,
            address TEXT,
            role TEXT DEFAULT 'customer',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Create categories table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            parent_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Insert default categories if not exist
        $check = $this->conn->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        if ($check == 0) {
            $this->conn->exec("INSERT INTO categories (name) VALUES 
                ('Boys Clothing'),
                ('Girls Clothing'),
                ('Unisex Clothing'),
                ('Accessories'),
                ('Seasonal Items')
            ");
        }
        
        // Create products table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            sale_price REAL,
            image TEXT,
            category_id INTEGER,
            stock INTEGER DEFAULT 0,
            is_featured INTEGER DEFAULT 0,
            is_sale INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Create orders table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_number TEXT NOT NULL,
            user_id INTEGER,
            total_amount REAL NOT NULL,
            status TEXT DEFAULT 'pending',
            payment_method TEXT,
            shipping_address TEXT,
            shipping_city TEXT,
            shipping_phone TEXT,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Create order_items table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL,
            price REAL NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Create promotions table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS promotions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            code TEXT NOT NULL UNIQUE,
            discount_type TEXT NOT NULL,
            discount_value REAL NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE,
            is_active INTEGER DEFAULT 1,
            usage_limit INTEGER DEFAULT 0,
            usage_count INTEGER DEFAULT 0,
            min_purchase REAL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Create banners table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS banners (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            subtitle TEXT,
            image TEXT,
            link TEXT,
            position INTEGER DEFAULT 1,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Create settings table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key TEXT NOT NULL UNIQUE,
            setting_value TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Insert default settings if not exist
        $check = $this->conn->query("SELECT COUNT(*) FROM settings")->fetchColumn();
        if ($check == 0) {
            $this->conn->exec("INSERT INTO settings (setting_key, setting_value) VALUES 
                ('primary_color', '#ff6b6b'),
                ('secondary_color', '#4ecdc4'),
                ('text_color', '#333333'),
                ('background_color', '#ffffff'),
                ('footer_color', '#292b2c'),
                ('site_name', 'Babee Store'),
                ('site_description', 'Quality Baby Clothing at Affordable Prices'),
                ('contact_email', 'contact@babeestore.com'),
                ('contact_phone', '+84 123 456 789'),
                ('contact_address', 'Hanoi, Vietnam')
            ");
        }
        
        // Add sample admin user if no users exist
        $check = $this->conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($check == 0) {
            // Default password: admin123
            $this->conn->exec("INSERT INTO users (username, email, password, role) VALUES 
                ('admin', 'admin@example.com', '$2y$10$8gF5Tcz8ZZi4ZKpzjXHgWOzxKCBXCQGUnkmlAWV7PZkWvpUwQ5wXC', 'admin')
            ");
        }
        
        // Add sample products if no products exist
        $check = $this->conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
        if ($check == 0) {
            $this->conn->exec("INSERT INTO products (name, description, price, category_id, stock, is_featured) VALUES 
                ('Baby T-Shirt', 'Comfortable cotton t-shirt for babies', 150000, 3, 25, 1),
                ('Girls Pink Dress', 'Adorable pink dress for baby girls', 250000, 2, 15, 1),
                ('Boys Blue Shorts', 'Comfortable shorts for baby boys', 180000, 1, 20, 1),
                ('Baby Hat', 'Protective sun hat for babies', 100000, 4, 30, 0),
                ('Winter Onesie', 'Warm onesie for cold weather', 220000, 5, 18, 1)
            ");
        }
    }
}
?>
