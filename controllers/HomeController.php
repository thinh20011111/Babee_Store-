<?php
class HomeController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Home page
    public function index() {
        // Get featured products
        $product = new Product($this->conn);
        $featured_products = [];
        $featured_stmt = $product->readFeatured(8);
        if ($featured_stmt) {
            while($row = $featured_stmt->fetch(PDO::FETCH_ASSOC)) {
                $featured_products[] = $row;
            }
        }
        
        // Get sale products
        $sale_products = [];
        $sale_stmt = $product->readSale(8);
        if ($sale_stmt) {
            while($row = $sale_stmt->fetch(PDO::FETCH_ASSOC)) {
                $sale_products[] = $row;
            }
        }
        
        // Get banners
        $banner = new Banner($this->conn);
        $banners = [];
        $banner_stmt = $banner->readActive();
        if ($banner_stmt) {
            while($row = $banner_stmt->fetch(PDO::FETCH_ASSOC)) {
                $banners[] = $row;
            }
        }
        
        // Get categories
        $category = new Category($this->conn);
        $categories = [];
        $category_stmt = $category->read();
        if ($category_stmt) {
            while($row = $category_stmt->fetch(PDO::FETCH_ASSOC)) {
                $categories[] = $row;
            }
        }
        
        // Get site settings
        $settings = new Settings($this->conn);
        $site_colors = $settings->getSiteColors();
        
        // Load home view
        include 'views/home/index.php';
    }
    
    // About page
    public function about() {
        // Get site settings
        $settings = new Settings($this->conn);
        $about_content = $settings->getValue('about_content', '');
        
        // Load about view
        include 'views/home/about.php';
    }
    
    // Contact page
    public function contact() {
        $error = '';
        $success = '';
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get form data
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
            $message = isset($_POST['message']) ? trim($_POST['message']) : '';
            
            // Validate form data
            if(empty($name) || empty($email) || empty($subject) || empty($message)) {
                $error = "Please fill all required fields.";
            } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } else {
                // In a real application, you would send an email or save to database
                // For this demo, we'll just show a success message
                $success = "Thank you for your message. We will get back to you soon.";
            }
        }
        
        // Get site settings
        $settings = new Settings($this->conn);
        $contact_email = $settings->getValue('contact_email', ADMIN_EMAIL);
        $contact_phone = $settings->getValue('contact_phone', '');
        $contact_address = $settings->getValue('contact_address', '');
        
        // Load contact view
        include 'views/home/contact.php';
    }
    
    // Error 404 page
    public function error404() {
        // Load 404 view
        include 'views/404.php';
    }
}
?>
