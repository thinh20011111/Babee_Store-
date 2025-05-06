<?php
class ProductController {
    private $conn;
    private $product;
    private $category;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->product = new Product($db);
        $this->category = new Category($db);
    }
    
    // List products by category
    public function list() {
        // Get category ID from URL
        $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
        
        // Get current page
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        if($page < 1) $page = 1;
        
        // Get search keyword
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        // Set up variables for pagination
        $items_per_page = ITEMS_PER_PAGE;
        $total_rows = 0;
        $products = [];
        
        // Get all categories for sidebar
        $categories = [];
        $category_stmt = $this->category->read();
        while($row = $category_stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $row;
        }
        
        // Get current category info if category_id is specified
        $category_name = '';
        if($category_id > 0) {
            $this->category->id = $category_id;
            if($this->category->readOne()) {
                $category_name = $this->category->name;
            }
        }
        
        // Get products based on search or category
        if(!empty($search)) {
            // Search products
            $stmt = $this->product->search($search, $page, $items_per_page);
            $total_rows = $this->product->countSearch($search);
        } elseif($category_id > 0) {
            // Get products by category
            $stmt = $this->product->readByCategory($category_id, $page, $items_per_page);
            $total_rows = $this->product->countByCategory($category_id);
        } else {
            // Get all products with pagination
            $stmt = $this->product->read($items_per_page);
            $total_rows = $this->product->countAll();
        }
        
        // Process results
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $row;
        }
        
        // Calculate total pages
        $total_pages = ceil($total_rows / $items_per_page);
        
        // Load product list view
        include 'views/products/list.php';
    }
    
    // View product details
    public function detail() {
        // Get product ID from URL
        $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if($product_id <= 0) {
            header("Location: index.php?controller=product&action=list");
            exit;
        }
        
        // Get product details
        $this->product->id = $product_id;
        if(!$this->product->readOne()) {
            header("Location: index.php?controller=product&action=list");
            exit;
        }
        
        // Get category name
        $category_name = $this->category->getNameById($this->product->category_id);
        
        // Get related products (same category)
        $related_products = [];
        $stmt = $this->product->readByCategory($this->product->category_id, 1, 4);
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Skip current product
            if($row['id'] != $product_id) {
                $related_products[] = $row;
            }
        }
        
        // Load product detail view
        include 'views/products/detail.php';
    }
    
    // Add product to cart (AJAX)
    public function addToCart() {
        // Check if request is AJAX
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            // Get product ID and quantity
            $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
            
            if($product_id <= 0 || $quantity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product or quantity.']);
                exit;
            }
            
            // Get product details
            $this->product->id = $product_id;
            if(!$this->product->readOne()) {
                echo json_encode(['success' => false, 'message' => 'Product not found.']);
                exit;
            }
            
            // Check stock availability
            if($this->product->stock < $quantity) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock available.']);
                exit;
            }
            
            // Create or get cart
            $cart = new Cart();
            
            // Prepare product data
            $product_data = [
                'name' => $this->product->name,
                'price' => $this->product->price,
                'sale_price' => $this->product->sale_price,
                'image' => $this->product->image
            ];
            
            // Add item to cart
            $cart->addItem($product_id, $quantity, $product_data);
            
            // Return success response
            echo json_encode([
                'success' => true, 
                'message' => 'Product added to cart.',
                'cart_count' => $cart->getTotalItems(),
                'cart_total' => $cart->getTotalPrice()
            ]);
            exit;
        }
        
        // If not AJAX, redirect to product page
        header("Location: index.php?controller=product&action=detail&id=" . $product_id);
        exit;
    }
}
?>
