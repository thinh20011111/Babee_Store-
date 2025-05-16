<?php
require_once 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class OrderController {
    private $conn;
    private $order;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->order = new Order($db);
    }
    
    public function create() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_POST['ajax']) && $_POST['ajax'] === 'true');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // [Giữ nguyên logic debug và validation từ code cũ]
            $raw_post = file_get_contents('php://input');
            $debug_output = "DEBUG: Raw POST (php://input): $raw_post\n";
            $debug_output .= "DEBUG: X-Requested-With header: " . ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'not set') . "\n";
            $hex_shipping_name = isset($_POST['shipping_name']) ? bin2hex($_POST['shipping_name']) : 'not set';
            $debug_output .= "DEBUG: Raw POST (\$_POST):\n" . print_r($_POST, true) . "\n";
            $debug_output .= "DEBUG: Hex of shipping_name: $hex_shipping_name\n";

            $chars = isset($_POST['shipping_name']) && extension_loaded('mbstring') ? mb_str_split($_POST['shipping_name'], 1, 'UTF-8') : (isset($_POST['shipping_name']) ? str_split($_POST['shipping_name']) : []);
            $debug_output .= "DEBUG: Character-by-character (shipping_name):\n";
            foreach ($chars as $index => $char) {
                $hex = bin2hex($char);
                $debug_output .= "Char at position $index: '$char' (hex: $hex)\n";
            }

            $mbstring_enabled = extension_loaded('mbstring') ? 'Yes' : 'No';
            $mbstring_encoding = mb_internal_encoding();
            $shipping_name_encoding = isset($_POST['shipping_name']) ? mb_detect_encoding($_POST['shipping_name'], 'UTF-8, ISO-8859-1', true) : 'not set';
            $debug_output .= "DEBUG: mbstring enabled: $mbstring_enabled\n";
            $debug_output .= "DEBUG: mbstring internal encoding: $mbstring_encoding\n";
            $debug_output .= "DEBUG: Detected encoding of shipping_name: $shipping_name_encoding\n";

            $raw_shipping_name = isset($_POST['shipping_name']) ? $_POST['shipping_name'] : '';
            $debug_output .= "DEBUG: Step 1 - Raw shipping_name (before any processing): '$raw_shipping_name' (length: " . strlen($raw_shipping_name) . ")\n";

            $raw_shipping_name = preg_replace('/^\xEF\xBB\xBF/', '', $raw_shipping_name);
            $raw_shipping_name = preg_replace('/[\x00-\x1F\x7F]/u', '', $raw_shipping_name);
            $debug_output .= "DEBUG: Step 2 - After removing BOM/control chars: '$raw_shipping_name' (length: " . strlen($raw_shipping_name) . ", mb_length: " . (extension_loaded('mbstring') ? mb_strlen($raw_shipping_name, 'UTF-8') : 'mbstring not loaded') . ")\n";

            if ($shipping_name_encoding !== 'UTF-8' && $shipping_name_encoding !== false) {
                $converted_shipping_name = iconv($shipping_name_encoding, 'UTF-8//IGNORE', $raw_shipping_name);
                $debug_output .= "DEBUG: Step 3 - After iconv conversion (from $shipping_name_encoding to UTF-8): '$converted_shipping_name' (length: " . strlen($converted_shipping_name) . ", mb_length: " . (extension_loaded('mbstring') ? mb_strlen($converted_shipping_name, 'UTF-8') : 'mbstring not loaded') . ")\n";
                $raw_shipping_name = $converted_shipping_name;
            } else {
                $debug_output .= "DEBUG: Step 3 - No iconv conversion needed (already UTF-8 or undetected)\n";
            }

            $this->order->shipping_name = $raw_shipping_name;
            $debug_output .= "DEBUG: Step 4 - Final shipping_name: '{$this->order->shipping_name}' (length: " . strlen($this->order->shipping_name) . ", mb_length: " . (extension_loaded('mbstring') ? mb_strlen($this->order->shipping_name, 'UTF-8') : 'mbstring not loaded') . ")\n";

            error_log($debug_output, 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
            if (!$isAjax) {
                echo "<pre>$debug_output</pre>";
            }

            $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
            error_log("DEBUG: Cart content before order creation: " . print_r($cart, true) . "\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
            if (empty($cart)) {
                $response = ['status' => 'error', 'message' => 'Giỏ hàng trống.'];
                if ($isAjax) {
                    header('Content-Type: application/json');
                    error_log("DEBUG: JSON response: " . json_encode($response) . "\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                    echo json_encode($response);
                    exit;
                } else {
                    $_SESSION['order_message'] = $response['message'];
                    header("Location: index.php?controller=cart");
                    exit;
                }
            }

            $this->order->user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $this->order->total_amount = array_sum(array_map(function($item) {
                $price = $item['data']['sale_price'] > 0 ? $item['data']['sale_price'] : $item['data']['price'];
                return $item['quantity'] * $price;
            }, $cart));
            $this->order->status = 'pending';
            $this->order->payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
            $this->order->shipping_address = isset($_POST['shipping_address']) ? trim($_POST['shipping_address']) : '';
            $this->order->shipping_city = isset($_POST['shipping_city']) ? trim($_POST['shipping_city']) : '';
            $this->order->shipping_phone = isset($_POST['shipping_phone']) ? trim($_POST['shipping_phone']) : '';
            $this->order->customer_email = isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '';
            $this->order->notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

            $debug_validation = "DEBUG: Before validation - shipping_name: '{$this->order->shipping_name}' (mb_length: " . (extension_loaded('mbstring') ? mb_strlen($this->order->shipping_name, 'UTF-8') : 'mbstring not loaded') . ", empty() result: " . (empty($this->order->shipping_name) ? 'true' : 'false') . ")\n";
            error_log($debug_validation, 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
            if (!$isAjax) {
                echo "<pre>$debug_validation</pre>";
            }

            $is_mbstring_enabled = extension_loaded('mbstring');
            $shipping_name_length = $is_mbstring_enabled ? mb_strlen($this->order->shipping_name, 'UTF-8') : strlen($this->order->shipping_name);
            if ($shipping_name_length === 0) {
                $response = ['status' => 'error', 'message' => 'Vui lòng nhập tên người nhận.'];
                error_log("ERROR: OrderController::create - Validation failed: shipping_name length is 0 (raw: '$raw_shipping_name', final: '{$this->order->shipping_name}', mb_length: $shipping_name_length, mbstring_enabled: $is_mbstring_enabled)\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                if ($isAjax) {
                    header('Content-Type: application/json');
                    error_log("DEBUG: JSON response: " . json_encode($response) . "\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                    echo json_encode($response);
                    exit;
                } else {
                    $_SESSION['order_message'] = $response['message'];
                    header("Location: index.php?controller=cart&action=checkout");
                    exit;
                }
            }
            if (trim($this->order->shipping_address) === '' || trim($this->order->shipping_city) === '' || trim($this->order->shipping_phone) === '') {
                $response = ['status' => 'error', 'message' => 'Vui lòng điền đầy đủ thông tin giao hàng.'];
                error_log("ERROR: OrderController::create - Validation failed: Missing shipping details\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                if ($isAjax) {
                    header('Content-Type: application/json');
                    error_log("DEBUG: JSON response: " . json_encode($response) . "\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                    echo json_encode($response);
                    exit;
                } else {
                    $_SESSION['order_message'] = $response['message'];
                    header("Location: index.php?controller=cart&action=checkout");
                    exit;
                }
            }
            if (trim($this->order->customer_email) === '' || !filter_var($this->order->customer_email, FILTER_VALIDATE_EMAIL)) {
                $response = ['status' => 'error', 'message' => 'Vui lòng nhập email hợp lệ.'];
                error_log("ERROR: OrderController::create - Validation failed: Invalid email\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                if ($isAjax) {
                    header('Content-Type: application/json');
                    error_log("DEBUG: JSON response: " . json_encode($response) . "\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                    echo json_encode($response);
                    exit;
                } else {
                    $_SESSION['order_message'] = $response['message'];
                    header("Location: index.php?controller=cart&action=checkout");
                    exit;
                }
            }
            if (trim($this->order->payment_method) === '') {
                $response = ['status' => 'error', 'message' => 'Vui lòng chọn phương thức thanh toán.'];
                error_log("ERROR: OrderController::create - Validation failed: Missing payment method\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                if ($isAjax) {
                    header('Content-Type: application/json');
                    error_log("DEBUG: JSON response: " . json_encode($response) . "\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                    echo json_encode($response);
                    exit;
                } else {
                    $_SESSION['order_message'] = $response['message'];
                    header("Location: index.php?controller=cart&action=checkout");
                    exit;
                }
            }
            if ($this->order->total_amount <= 0) {
                $response = ['status' => 'error', 'message' => 'Tổng giá đơn hàng không hợp lệ.'];
                error_log("ERROR: OrderController::create - Validation failed: Invalid total amount\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                if ($isAjax) {
                    header('Content-Type: application/json');
                    error_log("DEBUG: JSON response: " . json_encode($response) . "\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                    echo json_encode($response);
                    exit;
                } else {
                    $_SESSION['order_message'] = $response['message'];
                    header("Location: index.php?controller=cart&action=checkout");
                    exit;
                }
            }

            $debug_before_save = "DEBUG: Before saving order - shipping_name: '{$this->order->shipping_name}' (mb_length: " . (extension_loaded('mbstring') ? mb_strlen($this->order->shipping_name, 'UTF-8') : 'mbstring not loaded') . ")\n";
            error_log($debug_before_save, 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
            if (!$isAjax) {
                echo "<pre>$debug_before_save</pre>";
            }

            $this->conn->beginTransaction();
            try {
                error_log("DEBUG: Starting transaction for order creation\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                if ($order_id = $this->order->create()) {
                    error_log("DEBUG: Order created successfully, ID: $order_id\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                    foreach ($cart as $item) {
                        error_log("DEBUG: Processing cart item - product_id: {$item['product_id']}, variant_id: {$item['variant_id']}\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                        $query = "INSERT INTO order_details (order_id, product_id, variant_id, quantity, price) 
                                  VALUES (:order_id, :product_id, :variant_id, :quantity, :price)";
                        $stmt = $this->conn->prepare($query);
                        $price = $item['data']['sale_price'] > 0 ? $item['data']['sale_price'] : $item['data']['price'];
                        error_log("DEBUG: Preparing to insert order detail - product_id: {$item['product_id']}, variant_id: {$item['variant_id']}, quantity: {$item['quantity']}, price: $price\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                        $stmt->execute([
                            ':order_id' => $order_id,
                            ':product_id' => $item['product_id'],
                            ':variant_id' => $item['variant_id'],
                            ':quantity' => $item['quantity'],
                            ':price' => $price
                        ]);

                        $product = new Product($this->conn);
                        $product->id = $item['product_id'];
                        error_log("DEBUG: Updating stock for product_id: {$item['product_id']}, variant_id: {$item['variant_id']}, quantity: {$item['quantity']}\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                        if (!$product->updateVariantStock($item['variant_id'], $item['quantity'])) {
                            throw new Exception("Không thể cập nhật tồn kho cho biến thể ID {$item['variant_id']}.");
                        }
                    }

                    try {
                        $this->sendOrderConfirmationEmail($order_id, $this->order->customer_email);
                        error_log("DEBUG: Email xác nhận đã được gửi thành công cho {$this->order->customer_email}\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                    } catch (Exception $e) {
                        error_log("CẢNH BÁO: Không gửi được email xác nhận, nhưng đơn hàng vẫn được tạo: {$e->getMessage()}\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                    }

                    $this->conn->commit();
                    error_log("DEBUG: Transaction committed successfully\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                    unset($_SESSION['cart']);
                    $response = [
                        'status' => 'success',
                        'message' => 'Đơn hàng đã được tạo thành công.',
                        'redirect' => "index.php?controller=order&action=success&id=$order_id"
                    ];
                    error_log("DEBUG: JSON response: " . json_encode($response) . "\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode($response);
                        echo "<!-- DEBUG: Ajax response sent -->";
                        exit;
                    } else {
                        $_SESSION['order_message'] = $response['message'];
                        header("Location: index.php?controller=order&action=success&id=$order_id");
                        exit;
                    }
                } else {
                    throw new Exception("Không thể tạo đơn hàng.");
                }
            } catch (Exception $e) {
                $this->conn->rollBack();
                $error_message = "Lỗi khi tạo đơn hàng: " . $e->getMessage();
                error_log("ERROR: OrderController::create - Failed: $error_message\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                $response = ['status' => 'error', 'message' => $error_message];
                if ($isAjax) {
                    header('Content-Type: application/json');
                    error_log("DEBUG: JSON response: " . json_encode($response) . "\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                    echo json_encode($response);
                    echo "<!-- DEBUG: Ajax error response sent -->";
                    exit;
                } else {
                    $_SESSION['order_message'] = $response['message'];
                    header("Location: index.php?controller=cart&action=checkout");
                    exit;
                }
            } catch (Throwable $t) {
                $this->conn->rollBack();
                $error_message = "Lỗi nghiêm trọng khi tạo đơn hàng: " . $t->getMessage();
                error_log("FATAL: OrderController::create - Uncaught error: $error_message\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                $response = ['status' => 'error', 'message' => $error_message];
                if ($isAjax) {
                    header('Content-Type: application/json');
                    error_log("DEBUG: JSON response: " . json_encode($response) . "\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                    echo json_encode($response);
                    echo "<!-- DEBUG: Ajax fatal error response sent -->";
                    exit;
                } else {
                    $_SESSION['order_message'] = $response['message'];
                    header("Location: index.php?controller=cart&action=checkout");
                    exit;
                }
            }
        }

        $response = ['status' => 'error', 'message' => 'Yêu cầu không hợp lệ.'];
        error_log("DEBUG: OrderController::create - Not a POST request\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
        if ($isAjax) {
            header('Content-Type: application/json');
            error_log("DEBUG: JSON response: " . json_encode($response) . "\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
            echo json_encode($response);
            echo "<!-- DEBUG: Ajax invalid request response sent -->";
            exit;
        } else {
            header("Location: index.php?controller=cart");
            exit;
        }
    }
    
    public function view() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = 'index.php?controller=order&action=view&id=' . intval($_GET['id']);
            header("Location: index.php?controller=user&action=login");
            exit;
        }
        
        $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($order_id <= 0) {
            header("Location: index.php?controller=user&action=orders");
            exit;
        }
        
        $this->order->id = $order_id;
        if (!$this->order->readOne() || ($this->order->user_id != $_SESSION['user_id'] && $_SESSION['user_role'] != 'admin')) {
            header("Location: index.php?controller=user&action=orders");
            exit;
        }
        
        $order_items = $this->order->getOrderDetails();
        include 'views/order/view.php';
    }
    
    public function track() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $error = '';
        $order_data = null;
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_number = isset($_POST['order_number']) ? trim($_POST['order_number']) : '';
            if (empty($order_number)) {
                $error = "Vui lòng nhập mã đơn hàng.";
            } else {
                $query = "SELECT * FROM orders WHERE order_number = ? LIMIT 0,1";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(1, $order_number);
                $stmt->execute();
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $this->order->id = $row['id'];
                    $this->order->user_id = $row['user_id'];
                    $this->order->order_number = $row['order_number'];
                    $this->order->total_amount = $row['total_amount'];
                    $this->order->status = $row['status'];
                    $this->order->payment_method = $row['payment_method'];
                    $this->order->shipping_address = $row['shipping_address'];
                    $this->order->shipping_city = $row['shipping_city'];
                    $this->order->shipping_phone = $row['shipping_phone'];
                    $this->order->customer_email = $row['customer_email'];
                    $this->order->shipping_name = $row['shipping_name'];
                    $this->order->notes = $row['notes'];
                    $this->order->created_at = $row['created_at'];
                    $this->order->updated_at = $row['updated_at'];
                    // Lấy chi tiết đơn hàng
                    $order_items = $this->order->getOrderDetails();
                    $order_data = ["order" => $this->order, "items" => []];
                    
                    if ($order_items) {
                        while ($item = $order_items->fetch(PDO::FETCH_ASSOC)) {
                            $order_data["items"][] = [
                                "name" => $item["product_name"] ?? $item["name"] ?? "Không xác định",
                                "quantity" => $item["quantity"],
                                "price" => $item["price"],
                                "image" => $item["image"] ?? ""
                            ];
                        }
                    }

                } else {
                    $error = "Không tìm thấy đơn hàng.";
                }
            }
        }
        include 'views/order/track.php';
    }
    
    public function cancel() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = 'index.php?controller=user&action=orders';
            header("Location: index.php?controller=user&action=login");
            exit;
        }
        
        $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($order_id <= 0) {
            header("Location: index.php?controller=user&action=orders");
            exit;
        }
        
        $this->order->id = $order_id;
        if (!$this->order->readOne() || $this->order->user_id != $_SESSION['user_id']) {
            header("Location: index.php?controller=user&action=orders");
            exit;
        }
        
        if ($this->order->status != 'pending') {
            $_SESSION['order_message'] = "Chỉ các đơn hàng đang chờ xử lý mới có thể bị hủy.";
            header("Location: index.php?controller=user&action=orders");
            exit;
        }
        
        $this->conn->beginTransaction();
        try {
            $this->order->status = 'cancelled';
            if (!$this->order->updateStatus()) {
                throw new Exception("Không thể cập nhật trạng thái đơn hàng.");
            }
            $order_details = $this->order->getOrderDetails();
            while ($item = $order_details->fetch(PDO::FETCH_ASSOC)) {
                if ($item['variant_id']) {
                    $product = new Product($this->conn);
                    $product->id = $item['product_id'];
                    if (!$product->updateVariantStock($item['variant_id'], -$item['quantity'])) {
                        throw new Exception("Không thể khôi phục tồn kho cho biến thể ID {$item['variant_id']}.");
                    }
                }
            }
            $this->conn->commit();
            $_SESSION['order_message'] = "Đơn hàng đã được hủy thành công.";
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Lỗi hủy đơn hàng: " . $e->getMessage(), 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
            $_SESSION['order_message'] = "Lỗi khi hủy đơn hàng: " . $e->getMessage();
        }
        header("Location: index.php?controller=user&action=orders");
        exit;
    }
    
    public function success() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($order_id <= 0) {
            error_log("DEBUG: OrderController::success - Invalid order_id: $order_id\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
            header("Location: index.php?controller=home");
            exit;
        }
        
        $this->order->id = $order_id;
        if (!$this->order->readOne()) {
            error_log("DEBUG: OrderController::success - readOne failed for order_id: $order_id\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
            header("Location: index.php?controller=home");
            exit;
        }
        
        $order_details = $this->order->getOrderDetails();
        $items_debug = [];
        while ($item = $order_details->fetch(PDO::FETCH_ASSOC)) {
            $items_debug[] = $item;
        }
        error_log("DEBUG: OrderController::success - order_details for order_id $order_id: " . print_r($items_debug, true) . "\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
        $order_details = $this->order->getOrderDetails();
        include 'views/order/view.php';
    }
    
    private function sendOrderConfirmationEmail($order_id, $customer_email) {
        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function($str, $level) {
                error_log("SMTP DEBUG [$level]: $str\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
            };

            $query = "SELECT setting_value FROM settings WHERE setting_key = 'contact_email'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $admin_email = $stmt->fetchColumn() ?: 'babeemoonstore@gmail.com';

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'babeemoonstore@gmail.com';
            $mail->Password = 'hlsw gjpq smqt norf';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            $this->order->id = $order_id;
            $this->order->readOne();
            $order_details = $this->order->getOrderDetails();
            $items_list = '';
            while ($item = $order_details->fetch(PDO::FETCH_ASSOC)) {
                $items_list .= "- {$item['product_name']} (x{$item['quantity']}): ₫" . number_format($item['price'], 0, ',', '.') . "\n";
            }

            $mail->setFrom($admin_email, 'StreetStyle');
            $mail->addAddress($customer_email, $this->order->shipping_name);
            $mail->isHTML(true);
            $mail->Subject = "Xác nhận đơn hàng #{$this->order->order_number}";
            $mail->Body = "
                <h2>Xác nhận đơn hàng</h2>
                <p>Cảm ơn bạn đã đặt hàng tại StreetStyle!</p>
                <p><strong>Mã đơn hàng:</strong> {$this->order->order_number}</p>
                <p><strong>Tên người nhận:</strong> {$this->order->shipping_name}</p>
                <p><strong>Tổng tiền:</strong> ₫" . number_format($this->order->total_amount, 0, ',', '.') . "</p>
                <p><strong>Địa chỉ giao hàng:</strong> {$this->order->shipping_address}, {$this->order->shipping_city}</p>
                <p><strong>Điện thoại:</strong> {$this->order->shipping_phone}</p>
                <p><strong>Email:</strong> {$this->order->customer_email}</p>
                <h3>Chi tiết đơn hàng:</h3>
                <pre>$items_list</pre>
                <p>Chúng tôi sẽ xử lý đơn hàng của bạn sớm nhất có thể.</p>
            ";
            $mail->send();

            $mail->clearAddresses();
            $mail->addAddress($admin_email, 'Admin StreetStyle');
            $mail->Subject = "Đơn hàng mới #{$this->order->order_number}";
            $mail->Body = "
                <h2>Đơn hàng mới</h2>
                <p><strong>Mã đơn hàng:</strong> {$this->order->order_number}</p>
                <p><strong>Tên người nhận:</strong> {$this->order->shipping_name}</p>
                <p><strong>Email:</strong> {$this->order->customer_email}</p>
                <p><strong>Tổng tiền:</strong> ₫" . number_format($this->order->total_amount, 0, ',', '.') . "</p>
                <p><strong>Địa chỉ giao hàng:</strong> {$this->order->shipping_address}, {$this->order->shipping_city}</p>
                <p><strong>Điện thoại:</strong> {$this->order->shipping_phone}</p>
                <h3>Chi tiết đơn hàng:</h3>
                <pre>$items_list</pre>
            ";
            $mail->send();

            error_log("DEBUG: OrderController::sendOrderConfirmationEmail - Email sent to $customer_email and $admin_email\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
        } catch (Exception $e) {
            error_log("LỖI: OrderController::sendOrderConfirmationEmail - Failed: {$mail->ErrorInfo}\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
            throw new Exception("Không thể gửi email xác nhận: {$mail->ErrorInfo}");
        }
    }
}
?>