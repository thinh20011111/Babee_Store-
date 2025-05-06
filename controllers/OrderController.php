<?php
class OrderController {
    private $conn;
    private $order;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->order = new Order($db);
    }
    
    // View order details
    public function view() {
        // Check if user is logged in
        if(!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = 'index.php?controller=order&action=view&id=' . intval($_GET['id']);
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
        $this->order->id = $order_id;
        
        if(!$this->order->readOne() || ($this->order->user_id != $_SESSION['user_id'] && $_SESSION['user_role'] != 'admin')) {
            header("Location: index.php?controller=user&action=orders");
            exit;
        }
        
        // Get order items
        $order_items = $this->order->getOrderDetails();
        
        // Load order view
        include 'views/order/view.php';
    }
    
    // Track order
    public function track() {
        $error = '';
        $order_data = null;
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get order number
            $order_number = isset($_POST['order_number']) ? trim($_POST['order_number']) : '';
            
            if(empty($order_number)) {
                $error = "Please enter an order number.";
            } else {
                // Search for order by order number
                $query = "SELECT * FROM orders WHERE order_number = ? LIMIT 0,1";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(1, $order_number);
                $stmt->execute();
                
                if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Create order object
                    $this->order->id = $row['id'];
                    $this->order->user_id = $row['user_id'];
                    $this->order->order_number = $row['order_number'];
                    $this->order->total_amount = $row['total_amount'];
                    $this->order->status = $row['status'];
                    $this->order->payment_method = $row['payment_method'];
                    $this->order->shipping_address = $row['shipping_address'];
                    $this->order->shipping_city = $row['shipping_city'];
                    $this->order->shipping_phone = $row['shipping_phone'];
                    $this->order->notes = $row['notes'];
                    $this->order->created_at = $row['created_at'];
                    $this->order->updated_at = $row['updated_at'];
                    
                    // Get order items
                    $order_items = $this->order->getOrderDetails();
                    
                    // Prepare order data for display
                    $order_data = [
                        'order' => $this->order,
                        'items' => []
                    ];
                    
                    while($item = $order_items->fetch(PDO::FETCH_ASSOC)) {
                        $order_data['items'][] = $item;
                    }
                } else {
                    $error = "Order not found.";
                }
            }
        }
        
        // Load track order view
        include 'views/order/track.php';
    }
    
    // Cancel order
    public function cancel() {
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
        $this->order->id = $order_id;
        
        if(!$this->order->readOne() || $this->order->user_id != $_SESSION['user_id']) {
            header("Location: index.php?controller=user&action=orders");
            exit;
        }
        
        // Check if order can be cancelled (only pending orders)
        if($this->order->status != 'pending') {
            $_SESSION['order_message'] = "Only pending orders can be cancelled.";
            header("Location: index.php?controller=user&action=orders");
            exit;
        }
        
        // Update order status to 'cancelled'
        $this->order->status = 'cancelled';
        if($this->order->updateStatus()) {
            $_SESSION['order_message'] = "Order has been cancelled successfully.";
        } else {
            $_SESSION['order_message'] = "Failed to cancel order. Please try again.";
        }
        
        // Redirect to orders page
        header("Location: index.php?controller=user&action=orders");
        exit;
    }
}
?>
