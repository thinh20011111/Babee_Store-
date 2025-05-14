<?php
class Cart {
    // Properties
    private $items = [];
    private $total_items = 0;
    private $total_price = 0;
    
    // Constructor
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Initialize cart in session if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
            $_SESSION['cart_total_items'] = 0;
            $_SESSION['cart_total_price'] = 0;
        }
        
        // Load cart from session
        $this->items = $_SESSION['cart'];
        $this->total_items = $_SESSION['cart_total_items'];
        $this->total_price = $_SESSION['cart_total_price'];
    }
    
    // Add item to cart
    public function addItem($product_id, $quantity = 1, $product_data = []) {
        // Ensure product_id and quantity are valid
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;
        
        if ($product_id <= 0 || $quantity <= 0) {
            return false;
        }
        
        // Check if product already exists in cart
        if (isset($this->items[$product_id])) {
            // Update quantity
            $this->items[$product_id]['quantity'] += $quantity;
        } else {
            // Add new item
            $this->items[$product_id] = [
                'id' => $product_id,
                'quantity' => $quantity,
                'name' => $product_data['name'] ?? '',
                'price' => $product_data['price'] ?? 0,
                'sale_price' => $product_data['sale_price'] ?? 0,
                'image' => $product_data['image'] ?? ''
            ];
        }
        
        // Update totals
        $this->updateTotals();
        
        // Save cart to session
        $this->saveCart();
        
        return true;
    }
    
    // Update item quantity
    public function updateItem($product_id, $quantity) {
        // Ensure product_id and quantity are valid
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;
        
        if ($product_id <= 0 || $quantity <= 0) {
            return false;
        }
        
        // Check if product exists in cart
        if (isset($this->items[$product_id])) {
            // Update quantity
            $this->items[$product_id]['quantity'] = $quantity;
            
            // Update totals
            $this->updateTotals();
            
            // Save cart to session
            $this->saveCart();
            
            return true;
        }
        
        return false;
    }
    
    // Remove item from cart
    public function removeItem($product_id) {
        // Ensure product_id is valid
        $product_id = (int)$product_id;
        
        if ($product_id <= 0) {
            return false;
        }
        
        // Check if product exists in cart
        if (isset($this->items[$product_id])) {
            // Remove item
            unset($this->items[$product_id]);
            
            // Update totals
            $this->updateTotals();
            
            // Save cart to session
            $this->saveCart();
            
            return true;
        }
        
        return false;
    }
    
    // Clear cart
    public function clear() {
        $this->items = [];
        $this->total_items = 0;
        $this->total_price = 0;
        
        // Save cart to session
        $this->saveCart();
        
        return true;
    }
    
    // Get cart items
    public function getItems() {
        return $this->items;
    }
    
    // Get item quantity
    public function getItemQuantity($product_id) {
        // Ensure product_id is valid
        $product_id = (int)$product_id;
        
        if ($product_id <= 0) {
            return 0;
        }
        
        // Check if product exists in cart
        if (isset($this->items[$product_id])) {
            return $this->items[$product_id]['quantity'];
        }
        
        return 0;
    }
    
    // Get total items
    public function getTotalItems() {
        return $this->total_items;
    }
    
    // Get total price
    public function getTotalPrice() {
        return $this->total_price;
    }
    
    // Update cart totals
    private function updateTotals() {
        $this->total_items = 0;
        $this->total_price = 0;
        
        foreach ($this->items as $item) {
            $this->total_items += $item['quantity'];
            
            // Use sale price if available, otherwise use regular price
            $price = (!empty($item['sale_price']) && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
            $this->total_price += $price * $item['quantity'];
        }
    }
    
    // Save cart to session
    private function saveCart() {
        $_SESSION['cart'] = $this->items;
        $_SESSION['cart_total_items'] = $this->total_items;
        $_SESSION['cart_total_price'] = $this->total_price;
    }

    // Set cart items
    public function setItems($items) {
        // Validate input
        if (!is_array($items)) {
            error_log("ERROR: Invalid items array provided to setItems\n", 3, '/tmp/cart_debug.log');
            return false;
        }
        
        // Set items
        $this->items = $items;
        
        // Update totals
        $this->updateTotals();
        
        // Save cart to session
        $this->saveCart();
        
        return true;
    }
    
    // Load products data from database
    public function loadProductsData($conn) {
        if (empty($this->items)) {
            return;
        }
        
        // Get product IDs in cart
        $product_ids = array_keys($this->items);
        $ids_string = implode(',', array_fill(0, count($product_ids), '?'));
        
        $query = "SELECT id, name, price, sale_price, image, stock FROM products WHERE id IN ($ids_string)";
        $stmt = $conn->prepare($query);
        
        // Bind product IDs
        foreach ($product_ids as $index => $id) {
            $stmt->bindValue($index + 1, $id);
        }
        
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $product_id = $row['id'];
            
            // Update product data in cart
            $this->items[$product_id]['name'] = $row['name'];
            $this->items[$product_id]['price'] = $row['price'];
            $this->items[$product_id]['sale_price'] = $row['sale_price'];
            $this->items[$product_id]['image'] = $row['image'];
            $this->items[$product_id]['stock'] = $row['stock'];
            
            // Adjust quantity if it exceeds stock
            if ($this->items[$product_id]['quantity'] > $row['stock']) {
                $this->items[$product_id]['quantity'] = $row['stock'];
            }
        }
        
        // Update totals
        $this->updateTotals();
        
        // Save cart to session
        $this->saveCart();
    }
}
?>
