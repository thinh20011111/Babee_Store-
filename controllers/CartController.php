<?php
class CartController {
    private $conn;
    private $cart;
    
    public function __construct($db) {
        $this->conn = $db;
        if (!$db) {
            error_log("[" . date('Y-m-d H:i:s') . "] CartController: Database connection failed\n", 3, 'logs/debug.log');
            die("Database connection failed");
        }
        
        try {
            $this->cart = new Cart();
            try {
                $this->cart->loadProductsData($db);
            } catch (Exception $e) {
                error_log("[" . date('Y-m-d H:i:s') . "] CartController: Failed to load products data: " . $e->getMessage() . "\n", 3, 'logs/debug.log');
                $items = $this->cart->getItems();
                foreach ($items as &$item) {
                    $item['data']['stock'] = 10;
                }
                $this->cart->setItems($items);
            }
        } catch (Exception $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] CartController: Constructor error: " . $e->getMessage() . "\n", 3, 'logs/debug.log');
            die("Constructor error: " . htmlspecialchars($e->getMessage()));
        }
    }
    
    public function index() {
        try {
            $cart_items = $this->cart->getItems();
            $cart_total = $this->cart->getTotalPrice();
            
            $view_path = 'views/cart/index.php';
            if (!file_exists($view_path)) {
                error_log("[" . date('Y-m-d H:i:s') . "] CartController: View file not found: $view_path\n", 3, 'logs/debug.log');
                die("View file not found");
            }
            include $view_path;
        } catch (Exception $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] CartController: Index error: " . $e->getMessage() . "\n", 3, 'logs/debug.log');
            die("Index error: " . htmlspecialchars($e->getMessage()));
        }
    }
    
    public function update() {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
            $variant_id = isset($_POST['variant_id']) ? intval($_POST['variant_id']) : 0;
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
            
            error_log("[" . date('Y-m-d H:i:s') . "] CartController::update: product_id=$product_id, variant_id=$variant_id, quantity=$quantity\n", 3, 'logs/debug.log');
            
            if ($product_id <= 0 || $variant_id <= 0 || $quantity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product, variant, or quantity.']);
                exit;
            }
            
            $this->cart->updateItem($product_id, $quantity, $variant_id);
            
            $cart_items = $this->cart->getItems();
            $cart_subtotal = 0;
            
            if (isset($cart_items[$product_id . '_' . $variant_id])) {
                $item = $cart_items[$product_id . '_' . $variant_id];
                $price = (!empty($item['data']['sale_price']) && $item['data']['sale_price'] > 0) ? $item['data']['sale_price'] : $item['data']['price'];
                $item_total = $price * $item['quantity'];
                $cart_subtotal = $this->cart->getTotalPrice();
            }
            
            echo json_encode([
                'success' => true,
                'item_total' => $item_total ?? 0,
                'cart_total' => $cart_subtotal,
                'cart_count' => $this->cart->getTotalItems()
            ]);
            exit;
        }
        
        header("Location: index.php?controller=cart&action=index");
        exit;
    }
    
    public function remove() {
        $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
        $variant_id = isset($_GET['variant_id']) ? intval($_GET['variant_id']) : 0;
        
        error_log("[" . date('Y-m-d H:i:s') . "] CartController::remove: product_id=$product_id, variant_id=$variant_id\n", 3, 'logs/debug.log');
        
        if ($product_id > 0 && $variant_id >= 0) {
            $this->cart->removeItem($product_id, $variant_id);
        }
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode([
                'success' => true,
                'cart_total' => $this->cart->getTotalPrice(),
                'cart_count' => $this->cart->getTotalItems()
            ]);
            exit;
        }
        
        header("Location: index.php?controller=cart&action=index");
        exit;
    }
    
    public function clear() {
        $this->cart->clear();
        
        error_log("[" . date('Y-m-d H:i:s') . "] CartController::clear: Cart cleared\n", 3, 'logs/debug.log');
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode([
                'success' => true,
                'cart_count' => 0
            ]);
            exit;
        }
        
        header("Location: index.php?controller=cart&action=index");
        exit;
    }
    
    public function getCartCount() {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $cart_count = $this->cart->getTotalItems();
            
            error_log("[" . date('Y-m-d H:i:s') . "] CartController::getCartCount: cart_count=$cart_count\n", 3, 'logs/debug.log');
            
            echo json_encode([
                'success' => true,
                'cart_count' => $cart_count
            ]);
            exit;
        }
        
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);
        exit;
    }
    
    public function applyPromotion() {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $code = isset($_POST['code']) ? trim($_POST['code']) : '';
            
            error_log("[" . date('Y-m-d H:i:s') . "] CartController::applyPromotion: code=$code\n", 3, 'logs/debug.log');
            
            if (empty($code)) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã khuyến mãi.']);
                exit;
            }
            
            $promotion = new Promotion($this->conn);
            $promotion->code = $code;
            
            $cart_total = $this->cart->getTotalPrice();
            $result = $promotion->validateCode($cart_total);
            
            if ($result['valid']) {
                $discount = $promotion->calculateDiscount($cart_total);
                
                $_SESSION['promotion'] = [
                    'id' => $promotion->id,
                    'code' => $promotion->code,
                    'discount_type' => $promotion->discount_type,
                    'discount_value' => $promotion->discount_value,
                    'discount_amount' => $discount
                ];
                
                echo json_encode([
                    'success' => true,
                    'message' => $result['message'],
                    'discount_amount' => $discount,
                    'new_total' => $cart_total - $discount,
                    'cart_count' => $this->cart->getTotalItems()
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => $result['message']]);
            }
            exit;
        }
        
        header("Location: index.php?controller=cart&action=index");
        exit;
    }
    
    public function removePromotion() {
        unset($_SESSION['promotion']);
        
        error_log("[" . date('Y-m-d H:i:s') . "] CartController::removePromotion: Promotion removed\n", 3, 'logs/debug.log');
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode([
                'success' => true,
                'cart_total' => $this->cart->getTotalPrice(),
                'cart_count' => $this->cart->getTotalItems()
            ]);
            exit;
        }
        
        header("Location: index.php?controller=cart&action=index");
        exit;
    }
    
    public function checkout() {
        if ($this->cart->getTotalItems() == 0) {
            header("Location: index.php?controller=cart&action=index");
            exit;
        }
        
        $cart_items = $this->cart->getItems();
        $cart_subtotal = $this->cart->getTotalPrice();
        
        $promotion_discount = 0;
        if (isset($_SESSION['promotion'])) {
            $promotion_discount = $_SESSION['promotion']['discount_amount'];
        }
        
        $cart_total = $cart_subtotal - $promotion_discount;
        
        error_log("[" . date('Y-m-d H:i:s') . "] CartController::checkout - cart_items: " . print_r($cart_items, true) . "\n", 3, 'logs/debug.log');
        error_log("[" . date('Y-m-d H:i:s') . "] CartController::checkout - cart_subtotal: $cart_subtotal, promotion_discount: $promotion_discount, cart_total: $cart_total\n", 3, 'logs/debug.log');
        
        $user_data = [];
        if (isset($_SESSION['user_id'])) {
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
        }
        
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
            $address = isset($_POST['address']) ? trim($_POST['address']) : '';
            $city = isset($_POST['city']) ? trim($_POST['city']) : '';
            $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
            $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
            
            error_log("[" . date('Y-m-d H:i:s') . "] CartController::checkout - full_name: $full_name\n", 3, 'logs/debug.log');
            
            if (empty($full_name)) {
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
            } elseif ($cart_total <= 0) {
                $error = "Tổng giá đơn hàng không hợp lệ.";
            } else {
                $order = new Order($this->conn);
                $order->user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
                $order->total_amount = $cart_total;
                $order->status = 'pending';
                $order->payment_method = $payment_method;
                $order->shipping_address = $address;
                $order->shipping_city = $city;
                $order->shipping_phone = $phone;
                $order->notes = $notes;
                
                $this->conn->beginTransaction();
                try {
                    $order_id = $order->create();
                    if (!$order_id) {
                        throw new Exception("Không thể tạo đơn hàng.");
                    }
                    
                    foreach ($cart_items as $item) {
                        $price = (!empty($item['data']['sale_price']) && $item['data']['sale_price'] > 0) ? $item['data']['sale_price'] : $item['data']['price'];
                        $order->addOrderDetails($item['product_id'], $item['quantity'], $price, $item['variant_id']);
                        
                        $product = new Product($this->conn);
                        $product->id = $item['product_id'];
                        if (!$product->updateVariantStock($item['variant_id'], $item['quantity'])) {
                            throw new Exception("Không thể cập nhật tồn kho cho biến thể ID {$item['variant_id']}.");
                        }
                    }
                    
                    if (isset($_SESSION['promotion'])) {
                        $promotion = new Promotion($this->conn);
                        $promotion->id = $_SESSION['promotion']['id'];
                        $promotion->incrementUsage();
                    }
                    
                    $this->conn->commit();
                    
                    $this->cart->clear();
                    unset($_SESSION['promotion']);
                    header("Location: index.php?controller=order&action=success&id=$order_id");
                    exit;
                } catch (Exception $e) {
                    $this->conn->rollBack();
                    $error = "Không thể xử lý đơn hàng: " . $e->getMessage();
                }
            }
        }
        
        $view_path = 'views/checkout/index.php';
        if (!file_exists($view_path)) {
            error_log("[" . date('Y-m-d H:i:s') . "] CartController: View file not found: $view_path\n", 3, 'logs/debug.log');
            die("View file not found");
        }
        include $view_path;
    }
}
?>