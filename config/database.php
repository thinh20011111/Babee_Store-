<?php
// Hàm lấy kết nối database
function getConnection() {
    $conn = null;
    
    try {
        // Sử dụng SQLite cho môi trường phát triển
        $conn = new PDO('sqlite:./database.sqlite');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        createLocalTables($conn);
    } catch(PDOException $exception) {
        die("Connection error: " . $exception->getMessage());
    }
    
    return $conn;
}

// Class Database cũ - giữ lại để tương thích
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
        return createLocalTables($this->conn);
    }
}

// Hàm tạo bảng cho SQLite
function createLocalTables($conn) {
        // Create users table
        $conn->exec("CREATE TABLE IF NOT EXISTS users (
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
        $conn->exec("CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            parent_id INTEGER,
            image TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Insert default categories if not exist
        $check = $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        if ($check == 0) {
            $conn->exec("INSERT INTO categories (name, description) VALUES 
                ('Tops', 'T-shirts, Shirts, Tanktops, Hoodies'),
                ('Bottoms', 'Pants, Shorts, Jeans, Skirts'),
                ('Outerwear', 'Jackets, Coats, Bombers'),
                ('Accessories', 'Caps, Bags, Jewelry'),
                ('Footwear', 'Sneakers, Boots, Sandals')
            ");
        }
        
        // Create products table
        $conn->exec("CREATE TABLE IF NOT EXISTS products (
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
        $conn->exec("CREATE TABLE IF NOT EXISTS orders (
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
        $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL,
            price REAL NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Create promotions table
        $conn->exec("CREATE TABLE IF NOT EXISTS promotions (
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
        $conn->exec("CREATE TABLE IF NOT EXISTS banners (
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
        $conn->exec("CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key TEXT NOT NULL UNIQUE,
            setting_value TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Insert default settings if not exist
        $check = $conn->query("SELECT COUNT(*) FROM settings")->fetchColumn();
        if ($check == 0) {
            $conn->exec("INSERT INTO settings (setting_key, setting_value) VALUES 
                ('primary_color', '#FF2D55'),
                ('secondary_color', '#4A00E0'),
                ('accent_color', '#FFCC00'),
                ('text_color', '#121212'),
                ('background_color', '#FFFFFF'),
                ('dark_bg_color', '#1A1A1A'),
                ('light_bg_color', '#F7F7F7'),
                ('footer_color', '#0D0D0D'),
                ('site_name', 'StreetStyle'),
                ('site_description', 'Thời trang đường phố dành cho giới trẻ - Bold & Colorful'),
                ('contact_email', 'contact@streetstyle.com'),
                ('contact_phone', '+84 123 456 789'),
                ('contact_address', 'Hanoi, Vietnam')
            ");
        }
        
        // Add sample admin user if no users exist
        $check = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($check == 0) {
            // Default password: admin123
            $conn->exec("INSERT INTO users (username, email, password, role) VALUES 
                ('admin', 'admin@example.com', '$2y$10$8gF5Tcz8ZZi4ZKpzjXHgWOzxKCBXCQGUnkmlAWV7PZkWvpUwQ5wXC', 'admin')
            ");
        }
        
        // Add sample products if no products exist
        $check = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
        if ($check == 0) {
            $conn->exec("INSERT INTO products (name, description, price, sale_price, category_id, stock, is_featured, is_sale) VALUES 
                ('Oversized Logo Tee', 'Áo phông rộng với logo nổi bật, 100% cotton hữu cơ', 450000, 0, 1, 25, 1, 0),
                ('Cargo Pants', 'Quần túi hộp phong cách đường phố, nhiều túi tiện lợi', 620000, 520000, 2, 15, 1, 1),
                ('Graphic Hoodie', 'Áo hoodie in họa tiết đồ họa hiện đại', 850000, 0, 1, 20, 1, 0),
                ('Bucket Hat', 'Mũ bucket dáng rộng với họa tiết táo bạo', 320000, 250000, 4, 30, 0, 1),
                ('High-top Sneakers', 'Giày thể thao cổ cao phong cách retro', 1200000, 0, 5, 18, 1, 0)
            ");
        }
        
        return true;
    }
?>
