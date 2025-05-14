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
        if ($page < 1) $page = 1;
        
        // Get search keyword
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        // Set up variables for pagination
        $items_per_page = ITEMS_PER_PAGE;
        $total_rows = 0;
        $products = [];
        
        // Get all categories for sidebar
        $categories = [];
        $category_stmt = $this->category->read();
        while ($row = $category_stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $row;
        }
        
        // Get current category info if category_id is specified
        $category_name = '';
        if ($category_id > 0) {
            $this->category->id = $category_id;
            if ($this->category->readOne()) {
                $category_name = $this->category->name;
            }
        }
        
        // Get products based on search or category
        if (!empty($search)) {
            // Search products
            $stmt = $this->product->search($search, $page, $items_per_page);
            $total_rows = $this->product->countSearch($search);
        } elseif ($category_id > 0) {
            // Get products by category
            $stmt = $this->product->readByCategory($category_id, $page, $items_per_page);
            $total_rows = $this->product->countByCategory($category_id);
        } else {
            // Get all products with pagination
            $stmt = $this->product->read($items_per_page);
            $total_rows = $this->product->countAll();
        }
        
        // Process results
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $row;
        }
        
        // Calculate total pages
        $total_pages = ceil($total_rows / $items_per_page);
        
        // Load product list view
        include 'views/products/list.php';
    }
    
    // View product details
    public function detail() {
        // Bật error reporting
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        
        // Khởi tạo session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Khởi tạo file log
        $log_file = 'logs/debug.log'; // Chuyển sang thư mục logs/ trong dự án để tránh vấn đề quyền
        if (!file_exists(dirname($log_file))) {
            mkdir(dirname($log_file), 0755, true);
        }
        
        // Ghi log bắt đầu
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Bắt đầu ProductController::detail\n", FILE_APPEND);
        
        // Get product ID from URL
        $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Product ID: $product_id\n", FILE_APPEND);
        
        if ($product_id <= 0) {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Product ID không hợp lệ, chuyển hướng\n", FILE_APPEND);
            $_SESSION['error_message'] = "ID sản phẩm không hợp lệ.";
            header("Location: index.php?controller=product&action=list");
            exit;
        }
        
        // Get product details
        try {
            $this->product->id = $product_id;
            if (!$this->product->readOne()) {
                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Không tìm thấy sản phẩm với ID $product_id\n", FILE_APPEND);
                $_SESSION['error_message'] = "Không tìm thấy sản phẩm.";
                header("Location: index.php?controller=product&action=list");
                exit;
            }
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Product data: " . json_encode([
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => $this->product->price,
                'sale_price' => $this->product->sale_price,
                'category_id' => $this->product->category_id,
                'image' => $this->product->image
            ], JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
        } catch (Exception $e) {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi khi đọc sản phẩm: " . $e->getMessage() . "\n", FILE_APPEND);
            $_SESSION['error_message'] = "Lỗi khi đọc dữ liệu sản phẩm: " . htmlspecialchars($e->getMessage());
            header("Location: index.php?controller=product&action=list");
            exit;
        }
        
        // Get variants
        try {
            $variants = $this->product->variants; // Đã được gán trong readOne
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Variants for product ID $product_id: " . json_encode($variants, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
        } catch (Exception $e) {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi khi đọc variants: " . $e->getMessage() . "\n", FILE_APPEND);
            $variants = [];
        }
        
        // Get category name
        try {
            $category_name = $this->product->category_name ?? $this->category->getNameById($this->product->category_id);
            if (empty($category_name)) {
                $category_name = 'Danh mục không xác định';
            }
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Category name: $category_name\n", FILE_APPEND);
        } catch (Exception $e) {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi khi đọc category name: " . $e->getMessage() . "\n", FILE_APPEND);
            $category_name = 'Danh mục không xác định';
        }
        
        // Get related products
        try {
            $related_products = [];
            $stmt = $this->product->readRelatedProducts($product_id, $this->product->category_id, 4);
            if ($stmt) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $related_products[] = $row;
                }
            }
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Related products: " . json_encode($related_products, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
        } catch (Exception $e) {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi khi đọc related products: " . $e->getMessage() . "\n", FILE_APPEND);
            $related_products = [];
        }
        
        // Prepare data for view
        $data = [
            'product' => $this->product,
            'variants' => $variants,
            'category_name' => $category_name,
            'related_products' => $related_products
        ];
        
        // Ghi log dữ liệu truyền vào view
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Data for view: " . json_encode($data, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
        
        // Load product detail view
        try {
            extract($data);
            $view_path = __DIR__ . '/../views/product_detail.php'; // Sửa thành product_detail.php
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Đường dẫn view được thử: $view_path\n", FILE_APPEND);
            if (!file_exists($view_path)) {
                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi: File $view_path không tồn tại\n", FILE_APPEND);
                die("Lỗi: Không tìm thấy file product_detail.php tại $view_path. Vui lòng kiểm tra thư mục /htdocs/views/.");
            }
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Bắt đầu load $view_path\n", FILE_APPEND);
            include $view_path;
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Hoàn thành load $view_path\n", FILE_APPEND);
        } catch (Exception $e) {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi khi load view: " . $e->getMessage() . "\n", FILE_APPEND);
            $_SESSION['error_message'] = "Lỗi khi load trang chi tiết sản phẩm: " . htmlspecialchars($e->getMessage());
            header("Location: index.php?controller=product&action=list");
            exit;
        }
    }
    
    // Add product to cart (AJAX)
    public function addToCart() {
        // Đảm bảo session đã được khởi tạo
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Khởi tạo file log
        $log_file = '/tmp/debug.log';
        if (!file_exists(dirname($log_file))) {
            mkdir(dirname($log_file), 0755, true);
        }
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] ProductController::addToCart called\n", FILE_APPEND);

        // Đặt header JSON ngay đầu để tránh HTML
        header('Content-Type: application/json');

        // Kiểm tra nếu là request AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            // Get product ID, variant ID, and quantity
            $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
            $variant_id = isset($_POST['variant_id']) ? intval($_POST['variant_id']) : 0;
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
            
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Input: product_id=$product_id, variant_id=$variant_id, quantity=$quantity\n", FILE_APPEND);
            
            // Kiểm tra dữ liệu đầu vào
            if ($product_id <= 0 || $quantity <= 0) {
                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Invalid input\n", FILE_APPEND);
                echo json_encode(['success' => false, 'message' => 'Dữ liệu sản phẩm hoặc số lượng không hợp lệ.']);
                exit;
            }
            
            // Get product details
            try {
                $this->product->id = $product_id;
                if (!$this->product->readOne()) {
                    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Product not found: ID $product_id\n", FILE_APPEND);
                    echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm.']);
                    exit;
                }
            } catch (Exception $e) {
                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Error reading product: " . $e->getMessage() . "\n", FILE_APPEND);
                echo json_encode(['success' => false, 'message' => 'Lỗi khi đọc dữ liệu sản phẩm: ' . $e->getMessage()]);
                exit;
            }
            
            // Check variant details and stock availability
            try {
                $query = "SELECT stock, price FROM product_variants WHERE id = ? AND product_id = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$variant_id, $product_id]);
                $variant = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$variant && $variant_id > 0) {
                    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Variant not found: ID $variant_id\n", FILE_APPEND);
                    echo json_encode(['success' => false, 'message' => 'Không tìm thấy biến thể sản phẩm.']);
                    exit;
                }
                
                if ($variant_id > 0 && $variant['stock'] < $quantity) {
                    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Not enough stock for variant ID $variant_id: stock={$variant['stock']}, requested=$quantity\n", FILE_APPEND);
                    echo json_encode(['success' => false, 'message' => 'Không đủ hàng trong kho cho biến thể này.']);
                    exit;
                }
            } catch (Exception $e) {
                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Error querying variant: " . $e->getMessage() . "\n", FILE_APPEND);
                echo json_encode(['success' => false, 'message' => 'Lỗi khi kiểm tra biến thể: ' . $e->getMessage()]);
                exit;
            }
            
            // Create or get cart
            $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
            
            // Prepare product data
            $product_data = [
                'name' => $this->product->name,
                'price' => $variant['price'] ?? $this->product->price,
                'sale_price' => $this->product->sale_price,
                'image' => $this->product->image,
                'variant_id' => $variant_id
            ];
            
            // Create unique key for cart item (product_id + variant_id)
            $cart_key = $product_id . '_' . ($variant_id > 0 ? $variant_id : '0');
            
            // Add or update item in cart
            if (isset($cart[$cart_key])) {
                $cart[$cart_key]['quantity'] += $quantity;
            } else {
                $cart[$cart_key] = [
                    'product_id' => $product_id,
                    'variant_id' => $variant_id,
                    'quantity' => $quantity,
                    'data' => $product_data
                ];
            }
            
            // Update session cart
            $_SESSION['cart'] = $cart;
            
            // Calculate cart totals
            $cart_count = array_sum(array_column($cart, 'quantity'));
            $cart_total = array_sum(array_map(function($item) {
                $price = $item['data']['sale_price'] > 0 ? $item['data']['sale_price'] : $item['data']['price'];
                return $item['quantity'] * $price;
            }, $cart));
            
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Cart updated: count=$cart_count, total=$cart_total\n", FILE_APPEND);
            
            echo json_encode([
                'success' => true,
                'message' => 'Đã thêm sản phẩm vào giỏ hàng.',
                'cart_count' => $cart_count,
                'cart_total' => $cart_total
            ]);
            exit;
        }
        
        // Nếu không phải AJAX
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Non-AJAX request: product_id=$product_id\n", FILE_APPEND);
        
        if ($product_id <= 0) {
            header("Location: index.php?controller=product&action=list");
        } else {
            header("Location: index.php?controller=product&action=detail&id=" . $product_id);
        }
        exit;
    }
}
?>