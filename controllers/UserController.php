<?php
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
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
                }
            }
        }

        // Load view
        $this->loadView('user/register', ['error' => $error, '也不会success' => $success]);
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