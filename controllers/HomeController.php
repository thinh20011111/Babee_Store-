<?php
class HomeController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Home page
    public function index() {
        try {
            // Đảm bảo các class cần thiết tồn tại
            if (!class_exists('Product') || !class_exists('Banner') || 
                !class_exists('Category') || !class_exists('Settings')) {
                error_log("Missing required class in HomeController::index()");
                throw new Exception("One or more required classes (Product, Banner, Category, Settings) not found.");
            }

            // Get featured products
            $product = new Product($this->conn);
            $featured_products = [];
            $featured_stmt = $product->readFeatured(8);
            if ($featured_stmt) {
                while ($row = $featured_stmt->fetch(PDO::FETCH_ASSOC)) {
                    $featured_products[] = $row;
                }
            } else {
                error_log("Failed to fetch featured products");
            }

            // Get sale products
            $sale_products = [];
            $sale_stmt = $product->readSale(8);
            if ($sale_stmt) {
                while ($row = $sale_stmt->fetch(PDO::FETCH_ASSOC)) {
                    $sale_products[] = $row;
                }
            } else {
                error_log("Failed to fetch sale products");
            }

            // Get banners
            $banner = new Banner($this->conn);
            $banners = [];
            $banner_stmt = $banner->readActive();
            if ($banner_stmt) {
                while ($row = $banner_stmt->fetch(PDO::FETCH_ASSOC)) {
                    $banners[] = $row;
                }
            } else {
                error_log("Failed to fetch banners");
            }

            // Get categories
            $category = new Category($this->conn);
            $categories = [];
            $category_stmt = $category->read();
            if ($category_stmt) {
                while ($row = $category_stmt->fetch(PDO::FETCH_ASSOC)) {
                    $categories[] = $row;
                }
            } else {
                error_log("Failed to fetch categories");
            }

            // Get site settings
            $settings = new Settings($this->conn);
            $site_colors = $settings->getSiteColors() ?: []; // Fallback nếu không lấy được

            // Load home view
            $view_file = 'views/home/index.php';
            if (file_exists($view_file)) {
                include $view_file;
            } else {
                error_log("View file not found: $view_file");
                throw new Exception("View file not found: $view_file");
            }
        } catch (Exception $e) {
            error_log("Error in HomeController::index(): " . $e->getMessage());
            // Hiển thị thông báo lỗi hoặc load view lỗi
            echo "Error: " . $e->getMessage();
            // Có thể include view lỗi: include 'views/error.php';
        }
    }
    
    // About page
    public function about() {
        try {
            if (!class_exists('Settings')) {
                error_log("Missing Settings class in HomeController::about()");
                throw new Exception("Settings class not found.");
            }

            // Get site settings
            $settings = new Settings($this->conn);
            $about_content = $settings->getValue('about_content', '');

            // Load about view
            $view_file = 'views/home/about.php';
            if (file_exists($view_file)) {
                include $view_file;
            } else {
                error_log("View file not found: $view_file");
                throw new Exception("View file not found: $view_file");
            }
        } catch (Exception $e) {
            error_log("Error in HomeController::about(): " . $e->getMessage());
            echo "Error: " . $e->getMessage();
        }
    }
    
    // Contact page
    public function contact() {
        try {
            if (!class_exists('Settings')) {
                error_log("Missing Settings class in HomeController::contact()");
                throw new Exception("Settings class not found.");
            }

            $error = '';
            $success = '';
            
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // Get form data
                $name = isset($_POST['name']) ? trim($_POST['name']) : '';
                $email = isset($_POST['email']) ? trim($_POST['email']) : '';
                $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
                $message = isset($_POST['message']) ? trim($_POST['message']) : '';
                
                // Validate form data
                if (empty($name) || empty($email) || empty($subject) || empty($message)) {
                    $error = "Please fill all required fields.";
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = "Invalid email format.";
                } else {
                    $success = "Thank you for your message. We will get back to you soon.";
                }
            }
            
            // Get site settings
            $settings = new Settings($this->conn);
            $contact_email = $settings->getValue('contact_email', defined('ADMIN_EMAIL') ? ADMIN_EMAIL : '');
            $contact_phone = $settings->getValue('contact_phone', '');
            $contact_address = $settings->getValue('contact_address', '');
            
            // Load contact view
            $view_file = 'views/home/contact.php';
            if (file_exists($view_file)) {
                include $view_file;
            } else {
                error_log("View file not found: $view_file");
                throw new Exception("View file not found: $view_file");
            }
        } catch (Exception $e) {
            error_log("Error in HomeController::contact(): " . $e->getMessage());
            echo "Error: " . $e->getMessage();
        }
    }
    
    // Error 404 page
    public function error404() {
        try {
            $view_file = 'views/404.php';
            if (file_exists($view_file)) {
                include $view_file;
            } else {
                error_log("View file not found: $view_file");
                echo "404 - Page not found";
            }
        } catch (Exception $e) {
            error_log("Error in HomeController::error404(): " . $e->getMessage());
            echo "Error: " . $e->getMessage();
        }
    }
}
?>