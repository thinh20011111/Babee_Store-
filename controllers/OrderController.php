<?php
require_once 'vendor/autoload.php'; // Đảm bảo PHPMailer được tải
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

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Debug: Hiển thị $_POST raw và hex
            $raw_post = file_get_contents('php://input');
            $hex_shipping_name = isset($_POST['shipping_name']) ? bin2hex($_POST['shipping_name']) : 'not set';
            echo "<pre>DEBUG: Raw POST (php://input): $raw_post\n";
            echo "DEBUG: Raw POST ($_POST):\n";
            var_dump($_POST);
            echo "DEBUG: Hex of shipping_name: $hex_shipping_name\n";
            echo "</pre>";

            $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
            if (empty($cart)) {
                $_SESSION['order_message'] = "Giỏ hàng trống.";
                header("Location: index.php?controller=cart");
                exit;
            }

            // Tạo đơn hàng
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
            $raw_shipping_name = isset($_POST['shipping_name']) ? $_POST['shipping_name'] : '';
            // Loại bỏ BOM và ký tự ẩn
            $raw_shipping_name = preg_replace('/^\xEF\xBB\xBF/', '', $raw_shipping_name);
            $this->order->shipping_name = trim($raw_shipping_name, " \t\n\r\0\x0B\xC2\xA0");
            $this->order->notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

            // Debug: Hiển thị shipping_name
            echo "<pre>DEBUG: Raw shipping_name (before trim): '$raw_shipping_name' (length: " . mb_strlen($raw_shipping_name, 'UTF-8') . ")\n";
            echo "DEBUG: Trimmed shipping_name: '{$this->order->shipping_name}' (length: " . mb_strlen($this->order->shipping_name, 'UTF-8') . ")\n";
            echo "</pre>";

            // Validate required fields
            if (empty($this->order->shipping_name) || mb_strlen($this->order->shipping_name, 'UTF-8') === 0) {
                $_SESSION['order_message'] = "Vui lòng nhập tên người nhận.";
                error_log("ERROR: OrderController::create - Validation failed: shipping_name is empty or not set (raw: '$raw_shipping_name', trimmed: '{$this->order->shipping_name}')\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                header("Location: index.php?controller=cart&action=checkout");
                exit;
            }
            if (empty($this->order->shipping_address) || empty($this->order->shipping_city) || 
                empty($this->order->shipping_phone)) {
                $_SESSION['order_message'] = "Vui lòng điền đầy đủ thông tin giao hàng.";
                error_log("ERROR: OrderController::create - Validation failed: Missing shipping details\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                header("Location: index.php?controller=cart&action=checkout");
                exit;
            }
            if (empty($this->order->customer_email) || !filter_var($this->order->customer_email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['order_message'] = "Vui lòng nhập email hợp lệ.";
                error_log("ERROR: OrderController::create - Validation failed: Invalid email\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                header("Location: index.php?controller=cart&action=checkout");
                exit;
            }
            if (empty($this->order->payment_method)) {
                $_SESSION['order_message'] = "Vui lòng chọn phương thức thanh toán.";
                error_log("ERROR: OrderController::create - Validation failed: Missing payment method\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                header("Location: index.php?controller=cart&action=checkout");
                exit;
            }
            if ($this->order->total_amount <= 0) {
                $_SESSION['order_message'] = "Tổng giá đơn hàng không hợp lệ.";
                error_log("ERROR: OrderController::create - Validation failed: Invalid total amount\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                header("Location: index.php?controller=cart&action=checkout");
                exit;
            }

            // Lưu đơn hàng
            $this->conn->beginTransaction();
            try {
                if ($order_id = $this->order->create()) {
                    foreach ($cart as $item) {
                        $query = "INSERT INTO order_items (order_id, product_id, variant_id, quantity, price) 
                                  VALUES (:order_id, :product_id, :variant_id, :quantity, :price)";
                        $stmt = $this->conn->prepare($query);
                        $price = $item['data']['sale_price'] > 0 ? $item['data']['sale_price'] : $item['data']['price'];
                        $stmt->execute([
                            ':order_id' => $order_id,
                            ':product_id' => $item['product_id'],
                            ':variant_id' => $item['variant_id'],
                            ':quantity' => $item['quantity'],
                            ':price' => $price
                        ]);

                        $product = new Product($this->conn);
                        $product->id = $item['product_id'];
                        if (!$product->updateVariantStock($item['variant_id'], $item['quantity'])) {
                            throw new Exception("Không thể cập nhật tồn kho cho biến thể ID {$item['variant_id']}.");
                        }
                    }

                    $this->sendOrderConfirmationEmail($order_id, $this->order->customer_email);

                    $this->conn->commit();
                    unset($_SESSION['cart']);
                    $_SESSION['order_message'] = "Đơn hàng đã được tạo thành công.";
                    error_log("DEBUG: OrderController::create - Order created successfully, ID: $order_id\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                    header("Location: index.php?controller=order&action=success&id=$order_id");
                } else {
                    throw new Exception("Không thể tạo đơn hàng.");
                }
            } catch (Exception $e) {
                $this->conn->rollBack();
                error_log("ERROR: OrderController::create - Failed: " . $e->getMessage() . "\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
                $_SESSION['order_message'] = "Lỗi khi tạo đơn hàng: " . $e->getMessage();
                header("Location: index.php?controller=cart&action=checkout");
            }
            exit;
        }

        error_log("DEBUG: OrderController::create - Not a POST request\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
        header("Location: index.php?controller=cart");
        exit;
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
                    $order_items = $this->order->getOrderDetails();
                    $order_data = ['order' => $this->order, 'items' => []];
                    while ($item = $order_items->fetch(PDO::FETCH_ASSOC)) {
                        $order_data['items'][] = $item;
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
            $order_items = $this->order->getOrderDetails();
            while ($item = $order_items->fetch(PDO::FETCH_ASSOC)) {
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
        
        $order_items = $this->order->getOrderDetails();
        $items_debug = [];
        while ($item = $order_items->fetch(PDO::FETCH_ASSOC)) {
            $items_debug[] = $item;
        }
        error_log("DEBUG: OrderController::success - order_items for order_id $order_id: " . print_r($items_debug, true) . "\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
        $order_items = $this->order->getOrderDetails();
        include 'views/order/view.php';
    }
    
    private function sendOrderConfirmationEmail($order_id, $customer_email) {
        $mail = new PHPMailer(true);
        try {
            $query = "SELECT setting_value FROM settings WHERE setting_key = 'contact_email'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $admin_email = $stmt->fetchColumn() ?: 'babeemoonstore@gmail.com';

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'babeemoonstore@gmail.com';
            $mail->Password = 'your-app-password';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->CharSet = 'UTF-8';

            $this->order->id = $order_id;
            $this->order->readOne();
            $order_items = $this->order->getOrderDetails();
            $items_list = '';
            while ($item = $order_items->fetch(PDO::FETCH_ASSOC)) {
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
            error_log("Lỗi gửi email: {$mail->ErrorInfo}\n", 3, '/home/vol1000_36631514/babee.wuaze.com/logs/cart_debug.log');
            throw new Exception("Không thể gửi email xác nhận: {$mail->ErrorInfo}");
        }
    }
}
?>