<?php
// Yêu cầu PHPMailer để gửi email
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

class UserController {
    private $conn;
    private $user;

    public function __construct($db) {
        $this->conn = $db;
        $this->user = new User($db);
    }

    // Login page
    public function login() {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

            // Validate input
            if (empty($email) || empty($password)) {
                $error = "Please enter both email and password.";
            } else {
                $this->user->email = $email;
                if ($this->user->emailExists()) {
                    if ($this->user->verifyPassword($password)) {
                        // Set session variables
                        $_SESSION['user_id'] = $this->user->id;
                        $_SESSION['username'] = $this->user->username;
                        $_SESSION['user_role'] = $this->user->role;

                        // Redirect based on role
                        $redirect = ($this->user->role === 'admin' || $this->user->role === 'staff')
                            ? 'admin/dashboard.php'
                            : (isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php');
                        unset($_SESSION['redirect_after_login']);
                        header("Location: $redirect");
                        exit;
                    } else {
                        $error = "Invalid password.";
                    }
                } else {
                    $error = "Email not found.";
                }
            }
        }

        // Load view
        $this->loadView('user/login', ['error' => $error]);
    }

    // Register page
    public function register() {
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
            $confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);
            $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
            $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
            $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);

            // Validate input
            if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
                $error = "Please fill all required fields.";
            } elseif ($password !== $confirm_password) {
                $error = "Passwords do not match.";
            } elseif (strlen($password) < 6) {
                $error = "Password must be at least 6 characters.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } else {
                $this->user->email = $email;
                if ($this->user->emailExists()) {
                    $error = "Email already exists.";
                } else {
                    // Set user properties
                    $this->user->username = $username;
                    $this->user->email = $email;
                    $this->user->password = $password;
                    $this->user->full_name = $full_name;
                    $this->user->phone = $phone;
                    $this->user->address = $address;
                    $this->user->role = 'customer';

                    if ($this->user->create()) {
                        $success = "Registration successful. Please login.";
                        // Gửi email chào mừng
                        $this->sendWelcomeEmail($email, $username);
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
                }
            }
        }

        // Load view
        $this->loadView('user/register', ['error' => $error, 'success' => $success]);
    }

    // Logout
    public function logout() {
        // Clear session
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        // Redirect
        header("Location: index.php");
        exit;
    }

    // Profile page
    public function profile() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = 'index.php?controller=user&action=profile';
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $error = '';
        $success = '';

        // Load user data
        $this->user->id = $_SESSION['user_id'];
        if (!$this->user->readOne()) {
            $error = "Failed to load user data.";
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
            $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
            $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);

            // Validate input
            if (empty($username) || empty($email)) {
                $error = "Please fill all required fields.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } else {
                // Update user
                $this->user->username = $username;
                $this->user->email = $email;
                $this->user->full_name = $full_name;
                $this->user->phone = $phone;
                $this->user->address = $address;

                if ($this->user->update()) {
                    $success = "Profile updated successfully.";
                    $_SESSION['username'] = $username; // Update session
                } else {
                    $error = "Profile update failed. Please try again.";
                }
            }
        }

        // Load view
        $this->loadView('user/profile', [
            'error' => $error,
            'success' => $success,
            'user' => $this->user
        ]);
    }

    // Change password
    public function changePassword() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = 'index.php?controller=user&action=changePassword';
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $current_password = filter_input(INPUT_POST, 'current_password', FILTER_SANITIZE_STRING);
            $new_password = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING);
            $confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);

            // Validate input
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $error = "Please fill all required fields.";
            } elseif ($new_password !== $confirm_password) {
                $error = "New passwords do not match.";
            } elseif (strlen($new_password) < 6) {
                $error = "Password must be at least 6 characters.";
            } else {
                // Load user
                $this->user->id = $_SESSION['user_id'];
                if ($this->user->readOne()) {
                    if ($this->user->verifyPassword($current_password)) {
                        $this->user->password = $new_password;
                        if ($this->user->updatePassword()) {
                            $success = "Password changed successfully.";
                            // Gửi email thông báo đổi mật khẩu
                            $this->sendPasswordChangedEmail($this->user->email);
                        } else {
                            $error = "Password change failed. Please try again.";
                        }
                    } else {
                        $error = "Current password is incorrect.";
                    }
                } else {
                    $error = "Failed to load user data.";
                }
            }
        }

        // Load view
        $this->loadView('user/change_password', ['error' => $error, 'success' => $success]);
    }

    // Forgot password page
    public function forgot_password() {
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

            // Validate input
            if (empty($email)) {
                $error = "Please enter your email address.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } else {
                $this->user->email = $email;
                if ($this->user->emailExists()) {
                    // Tạo token và lưu vào database
                    $token = bin2hex(random_bytes(32));
                    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    // Lưu token vào bảng password_resets
                    $stmt = $this->conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                    if ($stmt->execute([$email, $token, $expires_at])) {
                        // Gửi email chứa liên kết đặt lại mật khẩu
                        $resetLink = "https://babee.wuaze.com/index.php?controller=user&action=reset_password&token=$token";
                        if ($this->sendResetEmail($email, $resetLink)) {
                            $success = "A password reset link has been sent to your email.";
                        } else {
                            $error = "Failed to send reset email. Please try again.";
                        }
                    } else {
                        $error = "Failed to process request. Please try again.";
                    }
                } else {
                    $error = "Email not found.";
                }
            }
        }

        // Load view
        $this->loadView('user/forgot_password', ['error' => $error, 'success' => $success]);
    }

    // Reset password page
    public function reset_password() {
        $error = '';
        $success = '';

        // Kiểm tra token
        $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
        if (empty($token)) {
            $error = "Invalid or missing reset token.";
            $this->loadView('user/reset_password', ['error' => $error, 'success' => $success]);
            return;
        }

        // Kiểm tra token trong database
        $stmt = $this->conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            $error = "Invalid or expired reset token.";
            $this->loadView('user/reset_password', ['error' => $error, 'success' => $success]);
            return;
        }

        $email = $result['email'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $new_password = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING);
            $confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);

            // Validate input
            if (empty($new_password) || empty($confirm_password)) {
                $error = "Please fill all required fields.";
            } elseif ($new_password !== $confirm_password) {
                $error = "Passwords do not match.";
            } elseif (strlen($new_password) < 6) {
                $error = "Password must be at least 6 characters.";
            } else {
                // Cập nhật mật khẩu
                $this->user->email = $email;
                if ($this->user->emailExists()) {
                    $this->user->password = $new_password;
                    if ($this->user->updatePassword()) {
                        // Xóa token sau khi sử dụng
                        $stmt = $this->conn->prepare("DELETE FROM password_resets WHERE token = ?");
                        $stmt->execute([$token]);

                        // Gửi email thông báo đổi mật khẩu
                        $this->sendPasswordChangedEmail($email);

                        $success = "Password reset successfully. Please login.";
                    } else {
                        $error = "Failed to reset password. Please try again.";
                    }
                } else {
                    $error = "User not found.";
                }
            }
        }

        // Load view
        $this->loadView('user/reset_password', ['error' => $error, 'success' => $success]);
    }

    // View orders
    public function orders() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = 'index.php?controller=user&action=orders';
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        // Get orders
        $order = new Order($this->conn);
        $orders = $order->readUserOrders($_SESSION['user_id']);

        // Load view
        $this->loadView('user/orders', ['orders' => $orders]);
    }

    // View order details
    public function orderDetails() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = 'index.php?controller=user&action=orders';
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        // Get order ID
        $order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$order_id) {
            header("Location: index.php?controller=user&action=orders");
            exit;
        }

        // Get order
        $order = new Order($this->conn);
        $order->id = $order_id;
        if (!$order->readOne() || $order->user_id != $_SESSION['user_id']) {
            header("Location: index.php?controller=user&action=orders");
            exit;
        }

        // Get order items
        $order_items = $order->getOrderDetails();

        // Load view
        $this->loadView('user/order_details', [
            'order' => $order,
            'order_items' => $order_items
        ]);
    }

    // Gửi email chào mừng
    private function sendWelcomeEmail($email, $username) {
        $mail = new PHPMailer(true);
        try {
            // Cấu hình SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Thay bằng SMTP server của bạn
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@gmail.com'; // Thay bằng email của bạn
            $mail->Password = 'your-app-password'; // Thay bằng App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Người gửi và người nhận
            $mail->setFrom('no-reply@babee.wuaze.com', 'Babee Shop');
            $mail->addAddress($email);

            // Nội dung email
            $mail->isHTML(true);
            $mail->Subject = 'Welcome to Babee Shop!';
            $mail->Body = "
                <h2>Welcome, $username!</h2>
                <p>Thank you for joining Babee Shop. We're excited to have you!</p>
                <p>Start shopping now: <a href='https://babee.wuaze.com'>Visit our store</a></p>
                <p>Best regards,<br>Babee Shop</p>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Failed to send welcome email: {$mail->ErrorInfo}");
            return false;
        }
    }

    // Gửi email đặt lại mật khẩu
    private function sendResetEmail($email, $resetLink) {
        $mail = new PHPMailer(true);
        try {
            // Cấu hình SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Thay bằng SMTP server của bạn
            $mail->SMTPAuth = true;
            $mail->Username = 'babeemoonstore@gmail.com'; // Thay bằng email của bạn
            $mail->Password = 'hlsw gjpq smqt norf'; // Thay bằng App Password
            $mail->SMTPSecure = 'PHPMailer::ENCRYPTION_STARTTLS';
            $mail->Port = 587;

            // Người gửi và người nhận
            $mail->setFrom('babeemoonstore@gmail.com', 'Babee Shop');
            $mail->addAddress($email);

            // Nội dung email
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "
                <h2>Password Reset Request</h2>
                <p>You requested to reset your password. Click the link below to set a new password:</p>
                <p><a href='$resetLink'>Reset Password</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you did not request this, please ignore this email.</p>
                <p>Best regards,<br>Babee Shop</p>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Failed to send reset email: {$mail->ErrorInfo}");
            return false;
        }
    }

    // Gửi email thông báo đổi mật khẩu
    private function sendPasswordChangedEmail($email) {
        $mail = new PHPMailer(true);
        try {
            // Cấu hình SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Thay bằng SMTP server của bạn
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@gmail.com'; // Thay bằng email của bạn
            $mail->Password = 'your-app-password'; // Thay bằng App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Người gửi và người nhận
            $mail->setFrom('no-reply@babee.wuaze.com', 'Babee Shop');
            $mail->addAddress($email);

            // Nội dung email
            $mail->isHTML(true);
            $mail->Subject = 'Password Changed Successfully';
            $mail->Body = "
                <h2>Password Changed</h2>
                <p>Your password has been changed successfully on " . date('Y-m-d H:i:s') . ".</p>
                <p>If you did not make this change, please contact our support team immediately.</p>
                <p>Best regards,<br>Babee Shop</p>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Failed to send password changed email: {$mail->ErrorInfo}");
            return false;
        }
    }

    // Helper method to load views
    private function loadView($view, $data = []) {
        $viewFile = "views/$view.php";
        if (file_exists($viewFile)) {
            extract($data);
            include $viewFile;
        } else {
            // Log error and show user-friendly message
            error_log("View file not found: $viewFile");
            http_response_code(500);
            echo "Error: Page cannot be loaded. Please try again later.";
            exit;
        }
    }
}
?>