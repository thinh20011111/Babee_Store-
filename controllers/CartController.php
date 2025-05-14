<?php
class CartController {
    private $conn;
    private $cart;
    
    public function __construct($db) {
        error_log("DEBUG: CartController::__construct started\n", 3, '/tmp/cart_debug.log');
        echo "<pre>DEBUG: CartController::__construct started</pre>";
        
        $this->conn = $db;
        if (!$db) {
            error_log("ERROR: Database connection is null\n", 3, '/tmp/cart_debug.log');
            echo "<pre>ERROR: Database connection is null</pre>";
            die("Database connection failed");
        }
        
        try {
            $this->cart = new Cart();
            error_log("DEBUG: Cart class instantiated\n", 3, '/tmp/cart_debug.log');
            echo "<pre>DEBUG: Cart class instantiated</pre>";
            
            try {
                $this->cart->loadProductsData($db);
                error_log("DEBUG: loadProductsData completed\n", 3, '/tmp/cart_debug.log');
                echo "<pre>DEBUG: loadProductsData completed</pre>";
            } catch (Exception $e) {
                error_log("WARNING: loadProductsData failed: " . $e->getMessage() . ". Proceeding with default stock.\n", 3, '/tmp/cart_debug.log');
                echo "<pre>WARNING: loadProductsData failed: " . htmlspecialchars($e->getMessage()) . ". Using default stock.</pre>";
                // Mock stock data
                $items = $this->cart->getItems();
                foreach ($items as &$item) {
                    $item['data']['stock'] = 10; // Default stock value
                }
                $this->cart->setItems($items); // Assuming a setter method
            }
        } catch (Exception $e) {
            error_log("ERROR: CartController::__construct failed: " . $e->getMessage() . "\n", 3, '/tmp/cart_debug.log');
            echo "<pre>ERROR: CartController::__construct failed: " . htmlspecialchars($e->getMessage()) . "</pre>";
            die("Constructor error: " . htmlspecialchars($e->getMessage()));
        }
    }
    
    // View cart
    public function index() {
        error_log("DEBUG: CartController::index started\n", 3, '/tmp/cart_debug.log');
        echo "<pre>DEBUG: CartController::index started</pre>";
        
        try {
            // Get cart items
            $cart_items = $this->cart->getItems();
            error_log("DEBUG: getItems returned: " . print_r($cart_items, true) . "\n", 3, '/tmp/cart_debug.log');
            echo "<pre>DEBUG: getItems returned: " . htmlspecialchars(print_r($cart_items, true)) . "</pre>";
            
            $cart_total = $this->cart->getTotalPrice();
            error_log("DEBUG: getTotalPrice returned: $cart_total\n", 3, '/tmp/cart_debug.log');
            echo "<pre>DEBUG: getTotalPrice returned: $cart_total</pre>";
            
            // Load cart view
            $view_path = 'views/cart/index.php';
            if (!file_exists($view_path)) {
                error_log("ERROR: View file $view_path not found\n", 3, '/tmp/cart_debug.log');
                echo "<pre>ERROR: View file $view_path not found</pre>";
                die("View file not found");
            }
            error_log("DEBUG: Including $view_path\n", 3, '/tmp/cart_debug.log');
            echo "<pre>DEBUG: Including $view_path</pre>";
            include $view_path;
            
            error_log("DEBUG: CartController::index completed\n", 3, '/tmp/cart_debug.log');
            echo "<pre>DEBUG: CartController::index completed</pre>";
        } catch (Exception $e) {
            error_log("ERROR: CartController::index failed: " . $e->getMessage() . "\n", 3, '/tmp/cart_debug.log');
            echo "<pre>ERROR: CartController::index failed: " . htmlspecialchars($e->getMessage()) . "</pre>";
            die("Index error: " . htmlspecialchars($e->getMessage()));
        }
    }
    
    // Update cart item
    public function update() {
        error_log("DEBUG: CartController::update started\n", 3, '/tmp/cart_debug.log');
        // Check if request is AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            // Get product ID, variant ID, and quantity
            $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
            $variant_id = isset($_POST['variant_id']) ? intval($_POST['variant_id']) : 0;
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
            
            error_log("DEBUG: update params - product_id: $product_id, variant_id: $variant_id, quantity: $quantity\n", 3, '/tmp/cart_debug.log');
            
            if ($product_id <= 0 || $variant_id <= 0 || $quantity <= 0) {
                error_log("ERROR: Invalid product, variant, or quantity\n", 3, '/tmp/cart_debug.log');
                echo json_encode(['success' => false, 'message' => 'Invalid product, variant, or quantity.']);
                exit;
            }
            
            // Update cart item
            $this->cart->updateItem($product_id, $quantity, $variant_id);
            
            // Get updated cart totals
            $cart_items = $this->cart->getItems();
            $cart_subtotal = 0;
            
            if (isset($cart_items[$product_id . '_' . $variant_id])) {
                $item = $cart_items[$product_id . '_' . $variant_id];
                $price = (!empty($item['data']['sale_price']) && $item['data']['sale_price'] > 0) ? $item['data']['sale_price'] : $item['data']['price'];
                $item_total = $price * $item['quantity'];
                $cart_subtotal = $this->cart->getTotalPrice();
            }
            
            error_log("DEBUG: update response - item_total: " . ($item_total ?? 0) . ", cart_total: $cart_subtotal, cart_count: " . $this->cart->getTotalItems() . "\n", 3, '/tmp/cart_debug.log');
            
            // Return updated data
            echo json_encode([
                'success' => true,
                'item_total' => $item_total ?? 0,
                'cart_total' => $cart_subtotal,
                'cart_count' => $this->cart->getTotalItems()
            ]);
            exit;
        }
        
        // If not AJAX, redirect to cart page
        error_log("DEBUG: update redirect to cart page\n", 3, '/tmp/cart_debug.log');
        header("Location: index.php?controller=cart&action=index");
        exit;
    }
    
    // Remove cart item
    public function remove() {
        error_log("DEBUG: CartController::remove started\n", 3, '/tmp/cart_debug.log');
        // Get product ID and variant ID
        $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
        $variant_id = isset($_GET['variant_id']) ? intval($_GET['variant_id']) : 0;
        
        error_log("DEBUG: remove params - product_id: $product_id, variant_id: $variant_id\n", 3, '/tmp/cart_debug.log');
        
        if ($product_id > 0 && $variant_id > 0) {
            // Remove item from cart
            $this->cart->removeItem($product_id, $variant_id);
            error_log("DEBUG: Item removed from cart\n", 3, '/tmp/cart_debug.log');
        }
        
        // Check if request is AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            error_log("DEBUG: remove AJAX response\n", 3, '/tmp/cart_debug.log');
            echo json_encode([
                'success' => true,
                'cart_total' => $this->cart->getTotalPrice(),
                'cart_count' => $this->cart->getTotalItems()
            ]);
            exit;
        }
        
        // Redirect to cart page
        error_log("DEBUG: remove redirect to cart page\n", 3, '/tmp/cart_debug.log');
        header("Location: index.php?controller=cart&action=index");
        exit;
    }
    
    // Clear cart
    public function clear() {
        error_log("DEBUG: CartController::clear started\n", 3, '/tmp/cart_debug.log');
        // Clear cart
        $this->cart->clear();
        error_log("DEBUG: Cart cleared\n", 3, '/tmp/cart_debug.log');
        
        // Check if request is AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            error_log("DEBUG: clear AJAX response\n", 3, '/tmp/cart_debug.log');
            echo json_encode(['success' => true]);
            exit;
        }
        
        // Redirect to cart page
        error_log("DEBUG: clear redirect to cart page\n", 3, '/tmp/cart_debug.log');
        header("Location: index.php?controller=cart&action=index");
        exit;
    }
    
    // Apply promotion code
    public function applyPromotion() {
        error_log("DEBUG: CartController::applyPromotion started\n", 3, '/tmp/cart_debug.log');
        // Check if request is AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            // Get promotion code
            $code = isset($_POST['code']) ? trim($_POST['code']) : '';
            
            error_log("DEBUG: applyPromotion code: $code\n", 3, '/tmp/cart_debug.log');
            
            if (empty($code)) {
                error_log("ERROR: Empty promotion code\n", 3, '/tmp/cart_debug.log');
                echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã khuyến mãi.']);
                exit;
            }
            
            // Validate promotion code
            $promotion = new Promotion($this->conn);
            $promotion->code = $code;
            
            $cart_total = $this->cart->getTotalPrice();
            $result = $promotion->validateCode($cart_total);
            
            error_log("DEBUG: validateCode result: " . print_r($result, true) . "\n", 3, '/tmp/cart_debug.log');
            
            if ($result['valid']) {
                // Calculate discount
                $discount = $promotion->calculateDiscount($cart_total);
                
                // Store promotion in session
                $_SESSION['promotion'] = [
                    'id' => $promotion->id,
                    'code' => $promotion->code,
                    'discount_type' => $promotion->discount_type,
                    'discount_value' => $promotion->discount_value,
                    'discount_amount' => $discount
                ];
                
                error_log("DEBUG: Promotion applied - discount: $discount, new_total: " . ($cart_total - $discount) . "\n", 3, '/tmp/cart_debug.log');
                
                // Return success with discount info
                echo json_encode([
                    'success' => true,
                    'message' => $result['message'],
                    'discount_amount' => $discount,
                    'new_total' => $cart_total - $discount
                ]);
            } else {
                error_log("ERROR: Invalid promotion code: " . $result['message'] . "\n", 3, '/tmp/cart_debug.log');
                echo json_encode(['success' => false, 'message' => $result['message']]);
            }
            exit;
        }
        
        // If not AJAX, redirect to cart page
        error_log("DEBUG: applyPromotion redirect to cart page\n", 3, '/tmp/cart_debug.log');
        header("Location: index.php?controller=cart&action=index");
        exit;
    }
    
    // Remove promotion
    public function removePromotion() {
        error_log("DEBUG: CartController::removePromotion started\n", 3, '/tmp/cart_debug.log');
        // Remove promotion from session
        unset($_SESSION['promotion']);
        error_log("DEBUG: Promotion removed\n", 3, '/tmp/cart_debug.log');
        
        // Check if request is AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            error_log("DEBUG: removePromotion AJAX response\n", 3, '/tmp/cart_debug.log');
            echo json_encode([
                'success' => true,
                'cart_total' => $this->cart->getTotalPrice()
            ]);
            exit;
        }
        
        // Redirect to cart page
        error_log("DEBUG: removePromotion redirect to cart page\n", 3, '/tmp/cart_debug.log');
        header("Location: index.php?controller=cart&action=index");
        exit;
    }
    
    // Checkout page
    public function checkout() {
        error_log("DEBUG: CartController::checkout started\n", 3, '/tmp/cart_debug.log');
        // Check if cart is not empty
        if ($this->cart->getTotalItems() == 0) {
            error_log("DEBUG: Empty cart, redirecting to cart page\n", 3, '/tmp/cart_debug.log');
            header("Location: index.php?controller=cart&action=index");
            exit;
        }
        
        // Get cart data
        $cart_items = $this->cart->getItems();
        $cart_subtotal = $this->cart->getTotalPrice();
        
        // Get promotion data if applied
        $promotion_discount = 0;
        if (isset($_SESSION['promotion'])) {
            $promotion_discount = $_SESSION['promotion']['discount_amount'];
        }
        
        // Calculate final total
        $cart_total = $cart_subtotal - $promotion_discount;
        
        error_log("DEBUG: checkout - cart_items: " . print_r($cart_items, true) . ", subtotal: $cart_subtotal, discount: $promotion_discount, total: $cart_total\n", 3, '/tmp/cart_debug.log');
        
        // Check if user is logged in
        $user_data = [];
        if (isset($_SESSION['user_id'])) {
            // Get user data for pre-filling checkout form
            $user = new User($this->conn);
            $user->id = $_SESSION['user_id'];
            if ($user->readOne()) {
                $user_data = [
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'city' => $user->city ?? ''
                ];
            }
            error_log("DEBUG: User data loaded: " . print_r($user_data, true) . "\n", 3, '/tmp/cart_debug.log');
        }
        
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            error_log("DEBUG: Processing checkout form\n", 3, '/tmp/cart_debug.log');
            // Process checkout form
            $shipping_name = isset($_POST['shipping_name']) ? trim($_POST['shipping_name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
            $address = isset($_POST['address']) ? trim($_POST['address']) : '';
            $city = isset($_POST['city']) ? trim($_POST['city']) : '';
            $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
            $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
            
            // Validate form data
            if (empty($shipping_name)) {
                $error = "Vui lòng nhập tên người nhận.";
            } elseif (empty($email)) {
                $error = "Vui lòng nhập email.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Định dạng email không hợp lệ.";
            } elseif (empty($phone)) {
                $error = "Vui lòng nhập số điện thoại.";
            } elseif (empty($address)) {
                $error = "Vui lòng nhập địa chỉ giao hàng.";
            } elseif (empty($city)) {
                $error = "Vui lòng nhập thành phố.";
            } elseif (empty($payment_method)) {
                $error = "Vui lòng chọn phương thức thanh toán.";
            } else {
                // Create order
                $order = new Order($this->conn);
                $order->user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
                $order->total_amount = $cart_total;
                $order->status = 'pending';
                $order->payment_method = $payment_method;
                $order->shipping_address = $address;
                $order->shipping_city = $city;
                $order->shipping_phone = $phone;
                $order->shipping_name = $shipping_name;
                $order->notes = $notes;
                
                $this->conn->beginTransaction();
                try {
                    $order_id = $order->create();
                    if (!$order_id) {
                        throw new Exception("Không thể tạo đơn hàng.");
                    }
                    
                    // Add order details
                    foreach ($cart_items as $item) {
                        $price = (!empty($item['data']['sale_price']) && $item['data']['sale_price'] > 0) ? $item['data']['sale_price'] : $item['data']['price'];
                        $order->addOrderDetails($item['product_id'], $item['quantity'], $price, $item['variant_id']);
                        
                        // Update product variant stock
                        $product = new Product($this->conn);
                        $product->id = $item['product_id'];
                        if (!$product->updateVariantStock($item['variant_id'], $item['quantity'])) {
                            throw new Exception("Không thể cập nhật tồn kho cho biến thể ID {$item['variant_id']}.");
                        }
                    }
                    
                    // Update promotion usage if applied
                    if (isset($_SESSION['promotion'])) {
                        $promotion = new Promotion($this->conn);
                        $promotion->id = $_SESSION['promotion']['id'];
                        $promotion->incrementUsage();
                    }
                    
                    $this->conn->commit();
                    
                    // Clear cart and promotion
                    $this->cart->clear();
                    unset($_SESSION['promotion']);
                    
                    error_log("DEBUG: Order created successfully, order_id: $order_id\n", 3, '/tmp/cart_debug.log');
                    
                    // Redirect to success page
                    header("Location: index.php?controller=order&action=success&id=$order_id");
                    exit;
                } catch (Exception $e) {
                    $this->conn->rollBack();
                    error_log("ERROR: Order creation failed: " . $e->getMessage() . "\n", 3, '/tmp/cart_debug.log');
                    $error = "Không thể xử lý đơn hàng: " . $e->getMessage();
                }
            }
            error_log("DEBUG: Checkout form processed, error: $error\n", 3, '/tmp/cart_debug.log');
        }
        
        // Load checkout view
        $view_path = 'views/checkout/index.php';
        if (!file_exists($view_path)) {
            error_log("ERROR: View file $view_path not found\n", 3, '/tmp/cart_debug.log');
            echo "<pre>ERROR: View file $view_path not found</pre>";
            die("View file not found");
        }
        error_log("DEBUG: Including $view_path\n", 3, '/tmp/cart_debug.log');
        include $view_path;
        
        error_log("DEBUG: CartController::checkout completed\n", 3, '/tmp/cart_debug.log');
    }
}
?>