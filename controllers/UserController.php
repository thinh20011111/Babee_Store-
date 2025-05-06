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
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get form data
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';
            
            // Validate form data
            if(empty($email) || empty($password)) {
                $error = "Please enter both email and password.";
            } else {
                // Check if email exists
                $this->user->email = $email;
                if($this->user->emailExists()) {
                    // Verify password
                    if($this->user->verifyPassword($password)) {
                        // Password is correct, create session
                        $_SESSION['user_id'] = $this->user->id;
                        $_SESSION['username'] = $this->user->username;
                        $_SESSION['user_role'] = $this->user->role;
                        
                        // Redirect based on user role
                        if($this->user->role == 'admin' || $this->user->role == 'staff') {
                            header("Location: admin/index.php");
                        } else {
                            // Redirect to home page or last visited page
                            $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
                            unset($_SESSION['redirect_after_login']);
                            header("Location: $redirect");
                        }
                        exit;
                    } else {
                        $error = "Invalid password.";
                    }
                } else {
                    $error = "Email not found.";
                }
            }
        }
        
        // Load login view
        include 'views/user/login.php';
    }
    
    // Register page
    public function register() {
        $error = '';
        $success = '';
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get form data
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';
            $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
            $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
            $address = isset($_POST['address']) ? trim($_POST['address']) : '';
            
            // Validate form data
            if(empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
                $error = "Please fill all required fields.";
            } elseif($password !== $confirm_password) {
                $error = "Passwords do not match.";
            } elseif(strlen($password) < 6) {
                $error = "Password must be at least 6 characters.";
            } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } else {
                // Check if email exists
                $this->user->email = $email;
                if($this->user->emailExists()) {
                    $error = "Email already exists.";
                } else {
                    // Create new user
                    $this->user->username = $username;
                    $this->user->email = $email;
                    $this->user->password = $password;
                    $this->user->full_name = $full_name;
                    $this->user->phone = $phone;
                    $this->user->address = $address;
                    $this->user->role = 'customer'; // Default role
                    
                    if($this->user->create()) {
                        $success = "Registration successful. Please login.";
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
                }
            }
        }
        
        // Load register view
        include 'views/user/register.php';
    }
    
    // Logout
    public function logout() {
        // Clear user session
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['user_role']);
        session_destroy();
        
        // Redirect to home page
        header("Location: index.php");
        exit;
    }
    
    // Profile page
    public function profile() {
        // Check if user is logged in
        if(!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = 'index.php?controller=user&action=profile';
            header("Location: index.php?controller=user&action=login");
            exit;
        }
        
        $error = '';
        $success = '';
        
        // Get user data
        $this->user->id = $_SESSION['user_id'];
        $this->user->readOne();
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get form data
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
            $address = isset($_POST['address']) ? trim($_POST['address']) : '';
            
            // Validate form data
            if(empty($username) || empty($email)) {
                $error = "Please fill all required fields.";
            } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } else {
                // Update user data
                $this->user->username = $username;
                $this->user->email = $email;
                $this->user->full_name = $full_name;
                $this->user->phone = $phone;
                $this->user->address = $address;
                
                if($this->user->update()) {
                    $success = "Profile updated successfully.";
                    // Refresh user data
                    $this->user->readOne();
                } else {
                    $error = "Profile update failed. Please try again.";
                }
            }
        }
        
        // Load profile view
        include 'views/user/profile.php';
    }
    
    // Change password
    public function changePassword() {
        // Check if user is logged in
        if(!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = 'index.php?controller=user&action=profile';
            header("Location: index.php?controller=user&action=login");
            exit;
        }
        
        $error = '';
        $success = '';
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get form data
            $current_password = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
            $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
            $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
            
            // Validate form data
            if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $error = "Please fill all required fields.";
            } elseif($new_password !== $confirm_password) {
                $error = "New passwords do not match.";
            } elseif(strlen($new_password) < 6) {
                $error = "Password must be at least 6 characters.";
            } else {
                // Get user data
                $this->user->id = $_SESSION['user_id'];
                $this->user->readOne();
                
                // Verify current password
                if(!$this->user->verifyPassword($current_password)) {
                    $error = "Current password is incorrect.";
                } else {
                    // Update password
                    $this->user->password = $new_password;
                    
                    if($this->user->updatePassword()) {
                        $success = "Password changed successfully.";
                    } else {
                        $error = "Password change failed. Please try again.";
                    }
                }
            }
        }
        
        // Load change password view
        include 'views/user/change_password.php';
    }
    
    // View orders
    public function orders() {
        // Check if user is logged in
        if(!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = 'index.php?controller=user&action=orders';
            header("Location: index.php?controller=user&action=login");
            exit;
        }
        
        // Get user orders
        $order = new Order($this->conn);
        $stmt = $order->readUserOrders($_SESSION['user_id']);
        
        // Load orders view
        include 'views/user/orders.php';
    }
    
    // View order details
    public function orderDetails() {
        // Check if user is logged in
        if(!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = 'index.php?controller=user&action=orders';
            header("Location: index.php?controller=user&action=login");
            exit;
        }
        
        // Get order ID
        $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if($order_id <= 0) {
            header("Location: index.php?controller=user&action=orders");
            exit;
        }
        
        // Get order details
        $order = new Order($this->conn);
        $order->id = $order_id;
        
        if(!$order->readOne() || $order->user_id != $_SESSION['user_id']) {
            header("Location: index.php?controller=user&action=orders");
            exit;
        }
        
        // Get order items
        $order_items = $order->getOrderDetails();
        
        // Load order details view
        include 'views/user/order_details.php';
    }
}
?>
