<?php
class CartController {
    private $conn;
    private $cart;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->cart = new Cart();
        
        // Load product data for items in cart
        $this->cart->loadProductsData($db);
    }
    
    // View cart
    public function index() {
        // Get cart items
        $cart_items = $this->cart->getItems();
        $cart_total = $this->cart->getTotalPrice();
        
        // Load cart view
        include 'views/cart/index.php';
    }
    
    // Update cart item
    public function update() {
        // Check if request is AJAX
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            // Get product ID and quantity
            $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
            
            if($product_id <= 0 || $quantity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product or quantity.']);
                exit;
            }
            
            // Update cart item
            $this->cart->updateItem($product_id, $quantity);
            
            // Get updated cart totals
            $cart_items = $this->cart->getItems();
            $cart_subtotal = 0;
            
            if(isset($cart_items[$product_id])) {
                $item = $cart_items[$product_id];
                $price = (!empty($item['sale_price']) && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
                $item_total = $price * $item['quantity'];
                $cart_subtotal = $this->cart->getTotalPrice();
            }
            
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
        header("Location: index.php?controller=cart&action=index");
        exit;
    }
    
    // Remove cart item
    public function remove() {
        // Get product ID
        $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if($product_id > 0) {
            // Remove item from cart
            $this->cart->removeItem($product_id);
        }
        
        // Check if request is AJAX
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode([
                'success' => true,
                'cart_total' => $this->cart->getTotalPrice(),
                'cart_count' => $this->cart->getTotalItems()
            ]);
            exit;
        }
        
        // Redirect to cart page
        header("Location: index.php?controller=cart&action=index");
        exit;
    }
    
    // Clear cart
    public function clear() {
        // Clear cart
        $this->cart->clear();
        
        // Check if request is AJAX
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true]);
            exit;
        }
        
        // Redirect to cart page
        header("Location: index.php?controller=cart&action=index");
        exit;
    }
    
    // Apply promotion code
    public function applyPromotion() {
        // Check if request is AJAX
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            // Get promotion code
            $code = isset($_POST['code']) ? trim($_POST['code']) : '';
            
            if(empty($code)) {
                echo json_encode(['success' => false, 'message' => 'Please enter a promotion code.']);
                exit;
            }
            
            // Validate promotion code
            $promotion = new Promotion($this->conn);
            $promotion->code = $code;
            
            $cart_total = $this->cart->getTotalPrice();
            $result = $promotion->validateCode($cart_total);
            
            if($result['valid']) {
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
                
                // Return success with discount info
                echo json_encode([
                    'success' => true,
                    'message' => $result['message'],
                    'discount_amount' => $discount,
                    'new_total' => $cart_total - $discount
                ]);
            } else {
                // Return error message
                echo json_encode(['success' => false, 'message' => $result['message']]);
            }
            exit;
        }
        
        // If not AJAX, redirect to cart page
        header("Location: index.php?controller=cart&action=index");
        exit;
    }
    
    // Remove promotion
    public function removePromotion() {
        // Remove promotion from session
        unset($_SESSION['promotion']);
        
        // Check if request is AJAX
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode([
                'success' => true,
                'cart_total' => $this->cart->getTotalPrice()
            ]);
            exit;
        }
        
        // Redirect to cart page
        header("Location: index.php?controller=cart&action=index");
        exit;
    }
    
    // Checkout page
    public function checkout() {
        // Check if cart is not empty
        if($this->cart->getTotalItems() == 0) {
            header("Location: index.php?controller=cart&action=index");
            exit;
        }
        
        // Get cart data
        $cart_items = $this->cart->getItems();
        $cart_subtotal = $this->cart->getTotalPrice();
        
        // Get promotion data if applied
        $promotion_discount = 0;
        if(isset($_SESSION['promotion'])) {
            $promotion_discount = $_SESSION['promotion']['discount_amount'];
        }
        
        // Calculate final total
        $cart_total = $cart_subtotal - $promotion_discount;
        
        // Check if user is logged in
        $user_data = [];
        if(isset($_SESSION['user_id'])) {
            // Get user data for pre-filling checkout form
            $user = new User($this->conn);
            $user->id = $_SESSION['user_id'];
            if($user->readOne()) {
                $user_data = [
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address
                ];
            }
        }
        
        $error = '';
        $success = '';
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process checkout form
            $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
            $address = isset($_POST['address']) ? trim($_POST['address']) : '';
            $city = isset($_POST['city']) ? trim($_POST['city']) : '';
            $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
            $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
            
            // Validate form data
            if(empty($full_name) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($payment_method)) {
                $error = "Please fill all required fields.";
            } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
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
                $order->notes = $notes;
                
                $order_id = $order->create();
                
                if($order_id) {
                    // Add order details
                    foreach($cart_items as $item) {
                        $price = (!empty($item['sale_price']) && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
                        $order->addOrderDetails($item['id'], $item['quantity'], $price);
                        
                        // Update product stock
                        $product = new Product($this->conn);
                        $product->id = $item['id'];
                        $product->updateStock($item['quantity']);
                    }
                    
                    // Update promotion usage if applied
                    if(isset($_SESSION['promotion'])) {
                        $promotion = new Promotion($this->conn);
                        $promotion->id = $_SESSION['promotion']['id'];
                        $promotion->incrementUsage();
                    }
                    
                    // Clear cart and promotion
                    $this->cart->clear();
                    unset($_SESSION['promotion']);
                    
                    // Set success message
                    $success = "Order placed successfully. Your order number is " . $order->order_number;
                } else {
                    $error = "Failed to create order. Please try again.";
                }
            }
        }
        
        // Load checkout view
        include 'views/checkout/index.php';
    }
}
?>
