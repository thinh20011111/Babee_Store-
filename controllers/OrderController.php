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
            $raw_shipping_name = isset($_POST['shipping_name']) ? $_POST['shipping_name'] : '';
            $raw_shipping_name = preg_replace('/^\xEF\xBB\xBF/', '', $raw_shipping_name);
            $raw_shipping_name = preg_replace('/[\x00-\x1F\x7F]/u', '', $raw_shipping_name);
            $shipping_name_encoding = mb_detect_encoding($raw_shipping_name, 'UTF-8, ISO-8859-1', true);
            if ($shipping_name_encoding !== 'UTF-8' && $shipping_name_encoding !== false) {
                $raw_shipping_name = iconv($shipping_name_encoding, 'UTF-8//IGNORE', $raw_shipping_name);
            }
            $this->order->shipping_name = $raw_shipping_name;

            $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
            if (empty($cart)) {
                $response = ['status' => 'error', 'message' => 'Giỏ hàng trống.'];
                if ($isAjax) {
                    header('Content-Type: application/json');
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

            $is_mbstring_enabled = extension_loaded('mbstring');
            $shipping_name_length = $is_mbstring_enabled ? mb_strlen($this->order->shipping_name, 'UTF-8') : strlen($this->order->shipping_name);
            if ($shipping_name_length === 0) {
                $response = ['status' => 'error', 'message' => 'Vui lòng nhập tên người nhận.'];
                if ($isAjax) {
                    header('Content-Type: application/json');
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
                if ($isAjax) {
                    header('Content-Type: application/json');
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
                if ($isAjax) {
                    header('Content-Type: application/json');
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
                if ($isAjax) {
                    header('Content-Type: application/json');
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
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                } else {
                    $_SESSION['order_message'] = $response['message'];
                    header("Location: index.php?controller=cart&action=checkout");
                    exit;
                }
            }

            $this->conn->beginTransaction();
            try {
                if ($order_id = $this->order->create()) {
                    foreach ($cart as $item) {
                        $query = "INSERT INTO order_details (order_id, product_id, variant_id, quantity, price) 
                                  VALUES (:order_id, :product_id, :variant_id, :quantity, :price)";
                        $stmt = $this->conn->prepare($query);
                        $price = $item['data']['sale_price'] > 0 ? $item['data']['sale_price'] : $item['data']['price'];
                        if (!$stmt->execute([
                            ':order_id' => $order_id,
                            ':product_id' => $item['product_id'],
                            ':variant_id' => $item['variant_id'],
                            ':quantity' => $item['quantity'],
                            ':price' => $price
                        ])) {
                            throw new Exception("Không thể lưu chi tiết đơn hàng cho sản phẩm ID {$item['product_id']}. Error: " . print_r($stmt->errorInfo(), true));
                        }

                        $product = new Product($this->conn);
                        $product->id = $item['product_id'];
                        if (!$product->updateVariantStock($item['variant_id'], $item['quantity'])) {
                            throw new Exception("Không thể cập nhật tồn kho cho biến thể ID {$item['variant_id']}.");
                        }
                    }

                    try {
                        $this->sendOrderConfirmationEmail($order_id, $this->order->customer_email);
                    } catch (Exception $e) {
                        // Ghi log nhưng không làm thất bại giao dịch
                    }

                    $this->conn->commit();
                    unset($_SESSION['cart']);
                    $response = [
                        'status' => 'success',
                        'message' => 'Đơn hàng đã được tạo thành công.',
                        'redirect' => "index.php?controller=order&action=success&id=$order_id"
                    ];
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode($response);
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
                $response = ['status' => 'error', 'message' => $error_message];
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                } else {
                    $_SESSION['order_message'] = $response['message'];
                    header("Location: index.php?controller=cart&action=checkout");
                    exit;
                }
            } catch (Throwable $t) {
                $this->conn->rollBack();
                $error_message = "Lỗi nghiêm trọng khi tạo đơn hàng: " . $t->getMessage();
                $response = ['status' => 'error', 'message' => $error_message];
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                } else {
                    $_SESSION['order_message'] = $response['message'];
                    header("Location: index.php?controller=cart&action=checkout");
                    exit;
                }
            }
        }

        $response = ['status' => 'error', 'message' => 'Yêu cầu không hợp lệ.'];
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($response);
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
            header("Location: index.php?controller=home");
            exit;
        }
        
        $this->order->id = $order_id;
        if (!$this->order->readOne()) {
            header("Location: index.php?controller=home");
            exit;
        }
        
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
        } catch (Exception $e) {
            throw new Exception("Không thể gửi email xác nhận: {$mail->ErrorInfo}");
        }
    }
}
?>