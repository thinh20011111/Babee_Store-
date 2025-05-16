<?php
// Đặt múi giờ PHP mặc định cho toàn bộ ứng dụng
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Hàm tạo bảng cho SQLite
function createLocalTables($conn) {
    try {
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
            is_featured INTEGER DEFAULT 0,
            is_sale INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Create product_variants table
        $conn->exec("CREATE TABLE IF NOT EXISTS product_variants (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            size TEXT NOT NULL,
            color TEXT,
            stock INTEGER DEFAULT 0,
            price REAL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id)
        )");

        // Create orders table with customer_email and shipping_name
        $conn->exec("CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_number TEXT NOT NULL,
            user_id INTEGER,
            total_amount REAL NOT NULL,
            status TEXT DEFAULT 'pending',
            payment_method TEXT,
            payment_status TEXT DEFAULT 'pending',
            shipping_address TEXT NOT NULL,
            shipping_city TEXT NOT NULL,
            shipping_phone TEXT NOT NULL,
            customer_email TEXT NOT NULL,
            shipping_name TEXT NOT NULL,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");

        // Create order_items table
        $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            variant_id INTEGER,
            quantity INTEGER NOT NULL,
            price REAL NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id),
            FOREIGN KEY (product_id) REFERENCES products(id),
            FOREIGN KEY (variant_id) REFERENCES product_variants(id)
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
            $conn->exec("INSERT INTO products (name, description, price, sale_price, category_id, is_featured, is_sale) VALUES 
                ('Oversized Logo Tee', 'Áo phông rộng với logo nổi bật, 100% cotton hữu cơ', 450000, 0, 1, 1, 0),
                ('Cargo Pants', 'Quần túi hộp phong cách đường phố, nhiều túi tiện lợi', 620000, 520000, 2, 1, 1),
                ('Graphic Hoodie', 'Áo hoodie in họa tiết đồ họa hiện đại', 850000, 0, 1, 1, 0),
                ('Bucket Hat', 'Mũ bucket dáng rộng với họa tiết táo bạo', 320000, 250000, 4, 0, 1),
                ('High-top Sneakers', 'Giày thể thao cổ cao phong cách retro', 1200000, 0, 5, 1, 0)
            ");
        }

        // Add sample product variants if no variants exist
        $check = $conn->query("SELECT COUNT(*) FROM product_variants")->fetchColumn();
        if ($check == 0) {
            $conn->exec("INSERT INTO product_variants (product_id, size, color, stock, price) VALUES 
                (1, 'S', 'Đỏ', 10, 450000),
                (1, 'M', 'Đỏ', 15, 450000),
                (1, 'L', 'Xanh', 5, 460000),
                (2, '28', 'Đen', 5, 520000),
                (2, '30', 'Đen', 10, 520000),
                (3, 'S', 'Trắng', 8, 850000),
                (3, 'M', 'Trắng', 12, 850000),
                (4, 'One Size', 'Cam', 30, 250000),
                (5, '39', 'Trắng', 5, 1200000),
                (5, '40', 'Trắng', 13, 1200000)
            ");
        }

        // Create traffic_logs table
        $conn->exec("CREATE TABLE IF NOT EXISTS traffic_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address TEXT,
            user_agent TEXT,
            page_url TEXT,
            referer_url TEXT,
            user_id INTEGER NULL,
            session_id TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        return true;
    } catch (PDOException $e) {
        error_log("Error creating tables: " . $e->getMessage());
        return false;
    }
}

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
            // For local development, use SQLite
            if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '0.0.0.0' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
                $this->conn = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->createLocalTables();
            } else {
                // Remote connection for production (InfinityFree)
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                    $this->username,
                    $this->password
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // Đặt múi giờ cho phiên MySQL
                $this->conn->exec("SET SESSION time_zone = '+07:00'");
            }
        } catch (PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            die("Connection error: Please check the server logs for details.");
        }

        return $this->conn;
    }

    // Create local database tables for development
    private function createLocalTables() {
        return createLocalTables($this->conn);
    }
}
?>