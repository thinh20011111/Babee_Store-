<?php
class ProductController
{
    private $conn;
    private $product;
    private $category;
    private $feedback;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->product = new Product($db);
        $this->category = new Category($db);
        $this->feedback = new Feedback($db);
    }

    // List products by category
    public function list()
    {
        // Get category ID from URL
        $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

        // Get current page
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        if ($page < 1) $page = 1;

        // Get search keyword
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        // Set up variables for pagination
        $items_per_page = defined('ITEMS_PER_PAGE') ? ITEMS_PER_PAGE : 12;
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
            $stmt = $this->product->read($items_per_page, $page);
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
    public function detail()
    {
        // Bật error reporting
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        // Khởi tạo session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Khởi tạo file log
        $log_file = 'logs/debug.log';
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

            // Lấy thông tin đánh giá sản phẩm
            $feedback_stats = $this->feedback->getProductFeedbackStats($product_id);
            $feedbacks = $this->feedback->getProductFeedbacks($product_id, 1, 3); // Chỉ lấy 3 đánh giá mới nhất

            // Đảm bảo xử lý giá trị mặc định cho feedbacks
            foreach ($feedbacks as &$feedback) {
                $feedback['username'] = $feedback['username'] ?? 'Khách ẩn danh';
                $feedback['avatar'] = $feedback['avatar'] ?? 'assets/images/default-avatar.png';
            }

            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Product data: " . json_encode([
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => $this->product->price,
                'sale_price' => $this->product->sale_price,
                'category_id' => $this->product->category_id,
                'image' => $this->product->image,
                'images' => $this->product->images,
                'feedback_stats' => $feedback_stats,
                'feedback_count' => count($feedbacks),
                'feedbacks' => array_map(function ($f) {
                    return [
                        'id' => $f['id'],
                        'user_id' => $f['user_id'],
                        'username' => $f['username'],
                        'rating' => $f['rating'],
                        'content' => substr($f['content'], 0, 50),
                        'media_count' => count($f['media'])
                    ];
                }, $feedbacks)
            ], JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
        } catch (Exception $e) {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi khi đọc sản phẩm: " . $e->getMessage() . "\n", FILE_APPEND);
            $_SESSION['error_message'] = "Lỗi khi đọc dữ liệu sản phẩm: " . htmlspecialchars($e->getMessage());
            header("Location: index.php?controller=product&action=list");
            exit;
        }

        // Get variants
        try {
            $variants = $this->product->variants;
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
            'related_products' => $related_products,
            'feedback_stats' => $feedback_stats,
            'feedbacks' => $feedbacks
        ];

        // Ghi log dữ liệu truyền vào view
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Data for view: " . json_encode([
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'category_name' => $category_name,
            'feedback_stats' => $feedback_stats,
            'feedback_count' => count($feedbacks),
            'related_products_count' => count($related_products)
        ], JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

        // Load product detail view
        try {
            extract($data);
            $view_path = __DIR__ . '/../views/products/detail.php';
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Đường dẫn view được thử: $view_path\n", FILE_APPEND);
            if (!file_exists($view_path)) {
                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi: File $view_path không tồn tại\n", FILE_APPEND);
                die("Lỗi: Không tìm thấy file detail.php tại $view_path. Vui lòng kiểm tra thư mục /htdocs/views/.");
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

    // Create new product (Admin)
    public function create()
    {
        // Khởi tạo session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Khởi tạo file log
        $log_file = 'logs/debug.log';
        if (!file_exists(dirname($log_file))) {
            mkdir(dirname($log_file), 0755, true);
        }

        // Ghi log bắt đầu
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Bắt đầu ProductController::create\n", FILE_APPEND);

        // Kiểm tra quyền admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Không có quyền admin\n", FILE_APPEND);
            $_SESSION['error_message'] = "Bạn không có quyền thêm sản phẩm.";
            header("Location: index.php?controller=product&action=list");
            exit;
        }

        // Xử lý form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->product->name = trim($_POST['name'] ?? '');
                $this->product->description = trim($_POST['description'] ?? '');
                $this->product->price = floatval($_POST['price'] ?? 0);
                $this->product->sale_price = floatval($_POST['sale_price'] ?? 0);
                $this->product->category_id = intval($_POST['category_id'] ?? 0);
                $this->product->is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $this->product->is_sale = isset($_POST['is_sale']) ? 1 : 0;

                // Kiểm tra dữ liệu đầu vào
                if (empty($this->product->name) || $this->product->price <= 0 || $this->product->category_id <= 0) {
                    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Dữ liệu đầu vào không hợp lệ\n", FILE_APPEND);
                    $_SESSION['error_message'] = "Vui lòng điền đầy đủ thông tin sản phẩm.";
                    header("Location: index.php?controller=product&action=create");
                    exit;
                }

                // Xử lý upload ảnh
                $upload_dir = 'uploads/images/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // Ảnh chính
                if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == UPLOAD_ERR_OK) {
                    $main_image_path = $upload_dir . time() . '_' . basename($_FILES['main_image']['name']);
                    if (!move_uploaded_file($_FILES['main_image']['tmp_name'], $main_image_path)) {
                        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi upload ảnh chính\n", FILE_APPEND);
                        $_SESSION['error_message'] = "Lỗi khi upload ảnh chính.";
                        header("Location: index.php?controller=product&action=create");
                        exit;
                    }
                    $this->product->image = $main_image_path;
                } else {
                    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Thiếu ảnh chính\n", FILE_APPEND);
                    $_SESSION['error_message'] = "Vui lòng chọn ảnh chính.";
                    header("Location: index.php?controller=product&action=create");
                    exit;
                }

                // Ảnh bổ sung
                $this->product->images = [];
                if (isset($_FILES['additional_images']) && is_array($_FILES['additional_images']['name'])) {
                    $image_count = 0;
                    foreach ($_FILES['additional_images']['name'] as $key => $name) {
                        if ($image_count >= 3) {
                            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Vượt quá giới hạn 3 ảnh bổ sung\n", FILE_APPEND);
                            $_SESSION['error_message'] = "Chỉ được phép upload tối đa 3 ảnh bổ sung.";
                            header("Location: index.php?controller=product&action=create");
                            exit;
                        }
                        if ($_FILES['additional_images']['error'][$key] == UPLOAD_ERR_OK && !empty($name)) {
                            $tmp_name = $_FILES['additional_images']['tmp_name'][$key];
                            $image_path = $upload_dir . time() . '_' . basename($name);
                            if (move_uploaded_file($tmp_name, $image_path)) {
                                $this->product->images[] = $image_path;
                                $image_count++;
                            } else {
                                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi upload ảnh bổ sung: $name\n", FILE_APPEND);
                            }
                        }
                    }
                }
                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Additional images: " . json_encode($this->product->images) . "\n", FILE_APPEND);

                // Tạo sản phẩm
                if ($product_id = $this->product->create()) {
                    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Tạo sản phẩm thành công, ID: $product_id\n", FILE_APPEND);
                    $_SESSION['success_message'] = "Tạo sản phẩm thành công.";
                    header("Location: index.php?controller=product&action=list");
                    exit;
                } else {
                    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi khi tạo sản phẩm\n", FILE_APPEND);
                    $_SESSION['error_message'] = "Lỗi khi tạo sản phẩm.";
                    header("Location: index.php?controller=product&action=create");
                    exit;
                }
            } catch (Exception $e) {
                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi khi tạo sản phẩm: " . $e->getMessage() . "\n", FILE_APPEND);
                $_SESSION['error_message'] = "Lỗi khi tạo sản phẩm: " . htmlspecialchars($e->getMessage());
                header("Location: index.php?controller=product&action=create");
                exit;
            }
        }

        // Load form tạo sản phẩm
        $categories = $this->category->read()->fetchAll(PDO::FETCH_ASSOC);
        include 'views/admin/products/create.php';
    }

    // Update existing product (Admin)
    public function update()
    {
        // Khởi tạo session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Khởi tạo file log
        $log_file = 'logs/debug.log';
        if (!file_exists(dirname($log_file))) {
            mkdir(dirname($log_file), 0755, true);
        }

        // Ghi log bắt đầu
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Bắt đầu ProductController::update\n", FILE_APPEND);

        // Kiểm tra quyền admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Không có quyền admin\n", FILE_APPEND);
            $_SESSION['error_message'] = "Bạn không có quyền chỉnh sửa sản phẩm.";
            header("Location: index.php?controller=product&action=list");
            exit;
        }

        // Get product ID
        $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($product_id <= 0) {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Product ID không hợp lệ\n", FILE_APPEND);
            $_SESSION['error_message'] = "ID sản phẩm không hợp lệ.";
            header("Location: index.php?controller=product&action=list");
            exit;
        }

        // Load product data
        $this->product->id = $product_id;
        if (!$this->product->readOne()) {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Không tìm thấy sản phẩm với ID $product_id\n", FILE_APPEND);
            $_SESSION['error_message'] = "Không tìm thấy sản phẩm.";
            header("Location: index.php?controller=product&action=list");
            exit;
        }

        // Xử lý form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->product->name = trim($_POST['name'] ?? '');
                $this->product->description = trim($_POST['description'] ?? '');
                $this->product->price = floatval($_POST['price'] ?? 0);
                $this->product->sale_price = floatval($_POST['sale_price'] ?? 0);
                $this->product->category_id = intval($_POST['category_id'] ?? 0);
                $this->product->is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $this->product->is_sale = isset($_POST['is_sale']) ? 1 : 0;

                // Kiểm tra dữ liệu đầu vào
                if (empty($this->product->name) || $this->product->price <= 0 || $this->product->category_id <= 0) {
                    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Dữ liệu đầu vào không hợp lệ\n", FILE_APPEND);
                    $_SESSION['error_message'] = "Vui lòng điền đầy đủ thông tin sản phẩm.";
                    header("Location: index.php?controller=product&action=update&id=$product_id");
                    exit;
                }

                // Xử lý upload ảnh
                $upload_dir = 'uploads/images/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // Ảnh chính
                if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == UPLOAD_ERR_OK) {
                    $main_image_path = $upload_dir . time() . '_' . basename($_FILES['main_image']['name']);
                    if (!move_uploaded_file($_FILES['main_image']['tmp_name'], $main_image_path)) {
                        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi upload ảnh chính\n", FILE_APPEND);
                        $_SESSION['error_message'] = "Lỗi khi upload ảnh chính.";
                        header("Location: index.php?controller=product&action=update&id=$product_id");
                        exit;
                    }
                    $this->product->image = $main_image_path;
                }

                // Ảnh bổ sung
                $this->product->images = [];
                if (isset($_FILES['additional_images']) && is_array($_FILES['additional_images']['name'])) {
                    $image_count = 0;
                    foreach ($_FILES['additional_images']['name'] as $key => $name) {
                        if ($image_count >= 3) {
                            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Vượt quá giới hạn 3 ảnh bổ sung\n", FILE_APPEND);
                            $_SESSION['error_message'] = "Chỉ được phép upload tối đa 3 ảnh bổ sung.";
                            header("Location: index.php?controller=product&action=update&id=$product_id");
                            exit;
                        }
                        if ($_FILES['additional_images']['error'][$key] == UPLOAD_ERR_OK && !empty($name)) {
                            $tmp_name = $_FILES['additional_images']['tmp_name'][$key];
                            $image_path = $upload_dir . time() . '_' . basename($name);
                            if (move_uploaded_file($tmp_name, $image_path)) {
                                $this->product->images[] = $image_path;
                                $image_count++;
                            } else {
                                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi upload ảnh bổ sung: $name\n", FILE_APPEND);
                            }
                        }
                    }
                }
                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Additional images: " . json_encode($this->product->images) . "\n", FILE_APPEND);

                // Xử lý xóa ảnh bổ sung
                $delete_image_ids = isset($_POST['delete_image_ids']) && is_array($_POST['delete_image_ids']) ? $_POST['delete_image_ids'] : [];
                if (!empty($delete_image_ids)) {
                    foreach ($delete_image_ids as $image_id) {
                        $this->product->deleteImage($image_id);
                    }
                }

                // Cập nhật sản phẩm
                if ($this->product->update()) {
                    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Cập nhật sản phẩm thành công, ID: $product_id\n", FILE_APPEND);
                    $_SESSION['success_message'] = "Cập nhật sản phẩm thành công.";
                    header("Location: index.php?controller=product&action=list");
                    exit;
                } else {
                    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi khi cập nhật sản phẩm\n", FILE_APPEND);
                    $_SESSION['error_message'] = "Lỗi khi cập nhật sản phẩm.";
                    header("Location: index.php?controller=product&action=update&id=$product_id");
                    exit;
                }
            } catch (Exception $e) {
                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi khi cập nhật sản phẩm: " . $e->getMessage() . "\n", FILE_APPEND);
                $_SESSION['error_message'] = "Lỗi khi cập nhật sản phẩm: " . htmlspecialchars($e->getMessage());
                header("Location: index.php?controller=product&action=update&id=$product_id");
                exit;
            }
        }

        // Load form cập nhật sản phẩm
        $categories = $this->category->read()->fetchAll(PDO::FETCH_ASSOC);
        include 'views/admin/products/update.php';
    }

    // Add product to cart (AJAX)
    public function addToCart()
    {
        // Đảm bảo session đã được khởi tạo
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Khởi tạo file log
        $log_file = 'logs/debug.log';
        if (!file_exists(dirname($log_file))) {
            mkdir(dirname($log_file), 0755, true);
        }
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] ProductController::addToCart called\n", FILE_APPEND);

        // Kiểm tra yêu cầu AJAX
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Không phải yêu cầu AJAX\n", FILE_APPEND);
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);
            exit;
        }

        // Lấy dữ liệu từ POST
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $variant_id = isset($_POST['variant_id']) ? intval($_POST['variant_id']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Add to cart: product_id=$product_id, variant_id=$variant_id, quantity=$quantity\n", FILE_APPEND);

        // Kiểm tra dữ liệu đầu vào
        if ($product_id <= 0 || $quantity <= 0) {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Dữ liệu đầu vào không hợp lệ\n", FILE_APPEND);
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        // Kiểm tra sản phẩm
        $this->product->id = $product_id;
        if (!$this->product->readOne()) {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Không tìm thấy sản phẩm với ID $product_id\n", FILE_APPEND);
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
            exit;
        }

        // Khởi tạo giỏ hàng nếu chưa có
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Tạo khóa duy nhất cho mục trong giỏ hàng
        $cart_key = $product_id . '_' . $variant_id;
        // Lấy số lượng hiện tại trong giỏ hàng (nếu có)
        $current_quantity = isset($_SESSION['cart'][$cart_key]) ? $_SESSION['cart'][$cart_key]['quantity'] : 0;

        if ($variant_id > 0) {
            // Xử lý khi có biến thể
            $variants = $this->product->getVariants();
            $variant_exists = false;
            $selected_variant = null;
            foreach ($variants as $variant) {
                if ($variant['id'] == $variant_id) {
                    $variant_exists = true;
                    $selected_variant = $variant;
                    break;
                }
            }

            if (!$variant_exists) {
                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Không tìm thấy biến thể với ID $variant_id\n", FILE_APPEND);
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Biến thể không tồn tại']);
                exit;
            }

            // Kiểm tra tổng số lượng so với tồn kho của biến thể
            $total_quantity = $current_quantity + $quantity;
            if ($selected_variant['stock'] < $total_quantity) {
                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Tổng số lượng ($total_quantity) vượt quá tồn kho biến thể ({$selected_variant['stock']})\n", FILE_APPEND);
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => "Số lượng yêu cầu vượt quá tồn kho còn lại ({$selected_variant['stock']} sản phẩm)."
                ]);
                exit;
            }

            // Cập nhật hoặc thêm mới mục trong giỏ hàng
            if (isset($_SESSION['cart'][$cart_key])) {
                $_SESSION['cart'][$cart_key]['quantity'] = $total_quantity;
            } else {
                $_SESSION['cart'][$cart_key] = [
                    'product_id' => $product_id,
                    'variant_id' => $variant_id,
                    'quantity' => $quantity,
                    'name' => $this->product->name,
                    'price' => $selected_variant['price'] > 0 ? $selected_variant['price'] : $this->product->price,
                    'color' => $selected_variant['color'],
                    'size' => $selected_variant['size'],
                    'image' => $this->product->image
                ];
            }
        } else {
            // Xử lý khi không có biến thể
            // Giả sử sản phẩm có thuộc tính stock tổng quát
            $product_stock = $this->product->getTotalStock();
            $total_quantity = $current_quantity + $quantity;
            if ($product_stock < $total_quantity) {
                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Tổng số lượng ($total_quantity) vượt quá tồn kho sản phẩm ($product_stock)\n", FILE_APPEND);
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => "Số lượng yêu cầu vượt quá tồn kho còn lại ($product_stock sản phẩm)."
                ]);
                exit;
            }

            // Cập nhật hoặc thêm mới mục trong giỏ hàng
            if (isset($_SESSION['cart'][$cart_key])) {
                $_SESSION['cart'][$cart_key]['quantity'] = $total_quantity;
            } else {
                $_SESSION['cart'][$cart_key] = [
                    'product_id' => $product_id,
                    'variant_id' => 0,
                    'quantity' => $quantity,
                    'name' => $this->product->name,
                    'price' => $this->product->sale_price > 0 ? $this->product->sale_price : $this->product->price,
                    'color' => null,
                    'size' => null,
                    'image' => $this->product->image
                ];
            }
        }

        // Tính tổng số lượng trong giỏ hàng
        $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));

        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Đã thêm vào giỏ hàng, cart_count=$cart_count\n", FILE_APPEND);

        // Trả về phản hồi JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Đã thêm sản phẩm vào giỏ hàng',
            'cart_count' => $cart_count
        ]);
        exit;
    }

    // Delete product (Admin)
    public function delete()
    {
        // Khởi tạo session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Khởi tạo file log
        $log_file = 'logs/debug.log';
        if (!file_exists(dirname($log_file))) {
            mkdir(dirname($log_file), 0755, true);
        }

        // Kiểm tra quyền admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Không có quyền admin\n", FILE_APPEND);
            $_SESSION['error_message'] = "Bạn không có quyền xóa sản phẩm.";
            header("Location: index.php?controller=product&action=list");
            exit;
        }

        // Get product ID
        $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($product_id <= 0) {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Product ID không hợp lệ\n", FILE_APPEND);
            $_SESSION['error_message'] = "ID sản phẩm không hợp lệ.";
            header("Location: index.php?controller=product&action=list");
            exit;
        }

        // Xóa sản phẩm
        $this->product->id = $product_id;
        try {
            if ($this->product->delete()) {
                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Xóa sản phẩm thành công, ID: $product_id\n", FILE_APPEND);
                $_SESSION['success_message'] = "Xóa sản phẩm thành công.";
            } else {
                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi khi xóa sản phẩm\n", FILE_APPEND);
                $_SESSION['error_message'] = "Lỗi khi xóa sản phẩm.";
            }
        } catch (Exception $e) {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi khi xóa sản phẩm: " . $e->getMessage() . "\n", FILE_APPEND);
            $_SESSION['error_message'] = "Lỗi khi xóa sản phẩm: " . htmlspecialchars($e->getMessage());
        }

        header("Location: index.php?controller=product&action=list");
        exit;
    }

    public function loadMoreReviews()
    {
        // Khởi tạo file log
        $log_file = 'logs/debug.log';
        if (!file_exists(dirname($log_file))) {
            mkdir(dirname($log_file), 0755, true);
        }

        // Kiểm tra tham số đầu vào
        if (!isset($_GET['product_id']) || !isset($_GET['page'])) {
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Error: Missing required parameters\n", FILE_APPEND);
            http_response_code(400);
            echo json_encode(['error' => 'Missing required parameters']);
            return;
        }

        $product_id = intval($_GET['product_id']);
        $page = intval($_GET['page']);

        // Log thông tin request
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Loading more reviews - Product ID: $product_id, Page: $page\n", FILE_APPEND);

        try {
            // Lấy thêm 3 đánh giá
            $feedbacks = $this->feedback->getProductFeedbacks($product_id, $page, 3);

            // Đảm bảo xử lý giá trị mặc định cho feedbacks
            foreach ($feedbacks as &$feedback) {
                $feedback['username'] = $feedback['username'] ?? 'Khách ẩn danh';
                $feedback['avatar'] = $feedback['avatar'] ?? 'assets/images/default-avatar.png';
            }

            // Log kết quả
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Successfully loaded " . count($feedbacks) . " feedbacks: " . json_encode(array_map(function ($f) {
                return [
                    'id' => $f['id'],
                    'user_id' => $f['user_id'],
                    'username' => $f['username'],
                    'rating' => $f['rating'],
                    'content' => substr($f['content'], 0, 50),
                    'media_count' => count($f['media'])
                ];
            }, $feedbacks), JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

            // Trả về kết quả dạng JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'feedbacks' => $feedbacks
            ]);
        } catch (Exception $e) {
            // Log lỗi
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage() . "\n", FILE_APPEND);

            // Trả về thông báo lỗi
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
?>