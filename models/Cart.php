<?php
class Cart {
    private $items = [];
    private $total_items = 0;
    private $total_price = 0;
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
            $_SESSION['cart_total_items'] = 0;
            $_SESSION['cart_total_price'] = 0;
        }
        
        $this->items = $_SESSION['cart'];
        $this->total_items = $_SESSION['cart_total_items'];
        $this->total_price = $_SESSION['cart_total_price'];
        
        error_log("DEBUG: Cart::__construct - items: " . print_r($this->items, true) . "\n", 3, '/tmp/cart_debug.log');
        error_log("DEBUG: Cart::__construct - total_items: $this->total_items, total_price: $this->total_price\n", 3, '/tmp/cart_debug.log');
    }
    
    public function addItem($product_id, $quantity = 1, $variant_id = 0, $product_data = []) {
        $product_id = (int)$product_id;
        $variant_id = (int)$variant_id;
        $quantity = (int)$quantity;
        
        if ($product_id <= 0 || $quantity <= 0) {
            error_log("ERROR: Cart::addItem - Invalid product_id: $product_id, variant_id: $variant_id or quantity: $quantity\n", 3, '/tmp/cart_debug.log');
            return false;
        }
        
        $key = $product_id . '_' . $variant_id;
        if (isset($this->items[$key])) {
            $this->items[$key]['quantity'] += $quantity;
        } else {
            $this->items[$key] = [
                'product_id' => $product_id,
                'variant_id' => $variant_id,
                'quantity' => $quantity,
                'data' => [
                    'name' => $product_data['name'] ?? '',
                    'price' => $product_data['price'] ?? 0,
                    'sale_price' => $product_data['sale_price'] ?? 0,
                    'image' => $product_data['image'] ?? '',
                    'stock' => $product_data['stock'] ?? 10
                ]
            ];
        }
        
        error_log("DEBUG: Cart::addItem - Added/Updated key: $key, quantity: $quantity, product_data: " . print_r($product_data, true) . "\n", 3, '/tmp/cart_debug.log');
        
        $this->updateTotals();
        $this->saveCart();
        return true;
    }
    
    public function updateItem($product_id, $quantity, $variant_id = 0) {
        $product_id = (int)$product_id;
        $variant_id = (int)$variant_id;
        $quantity = (int)$quantity;
        
        $key = $product_id . '_' . $variant_id;
        if ($product_id <= 0 || $quantity <= 0) {
            error_log("ERROR: Cart::updateItem - Invalid product_id: $product_id, variant_id: $variant_id or quantity: $quantity\n", 3, '/tmp/cart_debug.log');
            return false;
        }
        
        if (isset($this->items[$key])) {
            $this->items[$key]['quantity'] = $quantity;
            $this->updateTotals();
            $this->saveCart();
            
            error_log("DEBUG: Cart::updateItem - Updated key: $key, quantity: $quantity\n", 3, '/tmp/cart_debug.log');
            return true;
        }
        
        error_log("ERROR: Cart::updateItem - Key: $key not found\n", 3, '/tmp/cart_debug.log');
        return false;
    }
    
    public function removeItem($product_id, $variant_id = 0) {
        $product_id = (int)$product_id;
        $variant_id = (int)$variant_id;
        
        $key = $product_id . '_' . $variant_id;
        if ($product_id <= 0) {
            error_log("ERROR: Cart::removeItem - Invalid product_id: $product_id, variant_id: $variant_id\n", 3, '/tmp/cart_debug.log');
            return false;
        }
        
        if (isset($this->items[$key])) {
            unset($this->items[$key]);
            $this->updateTotals();
            $this->saveCart();
            
            error_log("DEBUG: Cart::removeItem - Removed key: $key\n", 3, '/tmp/cart_debug.log');
            return true;
        }
        
        error_log("ERROR: Cart::removeItem - Key: $key not found\n", 3, '/tmp/cart_debug.log');
        return false;
    }
    
    public function clear() {
        $this->items = [];
        $this->total_items = 0;
        $this->total_price = 0;
        $this->saveCart();
        
        error_log("DEBUG: Cart::clear - Cart cleared\n", 3, '/tmp/cart_debug.log');
        return true;
    }
    
    public function getItems() {
        return $this->items;
    }
    
    public function getItemQuantity($product_id, $variant_id = 0) {
        $product_id = (int)$product_id;
        $variant_id = (int)$variant_id;
        
        $key = $product_id . '_' . $variant_id;
        if ($product_id <= 0) {
            return 0;
        }
        
        if (isset($this->items[$key])) {
            return $this->items[$key]['quantity'];
        }
        
        return 0;
    }
    
    public function getTotalItems() {
        return $this->total_items;
    }
    
    public function getTotalPrice() {
        error_log("DEBUG: Cart::getTotalPrice - total_price: $this->total_price\n", 3, '/tmp/cart_debug.log');
        return $this->total_price;
    }
    
    private function updateTotals() {
        $this->total_items = 0;
        $this->total_price = 0;
        
        foreach ($this->items as $key => $item) {
            $this->total_items += $item['quantity'];
            $price = (!empty($item['data']['sale_price']) && $item['data']['sale_price'] > 0) ? $item['data']['sale_price'] : $item['data']['price'];
            $subtotal = $price * $item['quantity'];
            $this->total_price += $subtotal;
            
            error_log("DEBUG: Cart::updateTotals - key: $key, price: $price, quantity: {$item['quantity']}, subtotal: $subtotal\n", 3, '/tmp/cart_debug.log');
        }
        
        error_log("DEBUG: Cart::updateTotals - total_items: $this->total_items, total_price: $this->total_price\n", 3, '/tmp/cart_debug.log');
    }
    
    private function saveCart() {
        $_SESSION['cart'] = $this->items;
        $_SESSION['cart_total_items'] = $this->total_items;
        $_SESSION['cart_total_price'] = $this->total_price;
        
        error_log("DEBUG: Cart::saveCart - Saved cart to session\n", 3, '/tmp/cart_debug.log');
    }

    public function setItems($items) {
        if (!is_array($items)) {
            error_log("ERROR: Cart::setItems - Invalid items array\n", 3, '/tmp/cart_debug.log');
            return false;
        }
        
        $this->items = $items;
        $this->updateTotals();
        $this->saveCart();
        
        error_log("DEBUG: Cart::setItems - Set items: " . print_r($items, true) . "\n", 3, '/tmp/cart_debug.log');
        return true;
    }
    
    public function loadProductsData($conn) {
        if (empty($this->items)) {
            error_log("DEBUG: Cart::loadProductsData - No items in cart\n", 3, '/tmp/cart_debug.log');
            return;
        }
        
        $product_ids = array_column($this->items, 'product_id');
        $variant_ids = array_column($this->items, 'variant_id');
        $ids_string = implode(',', array_fill(0, count($product_ids), '?'));
        $variant_ids_string = implode(',', array_fill(0, count($variant_ids), '?'));
        
        $query = "SELECT p.id AS product_id, pv.id AS variant_id, p.name, p.price, p.sale_price, p.image, pv.stock 
                  FROM products p 
                  LEFT JOIN product_variants pv ON p.id = pv.product_id 
                  WHERE p.id IN ($ids_string) AND pv.id IN ($variant_ids_string)";
        $stmt = $conn->prepare($query);
        
        $params = array_merge($product_ids, $variant_ids);
        foreach ($params as $index => $param) {
            $stmt->bindValue($index + 1, $param);
        }
        
        $stmt->execute();
        
        $products_found = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $key = $row['product_id'] . '_' . $row['variant_id'];
            if (isset($this->items[$key])) {
                $this->items[$key]['data'] = [
                    'name' => $row['name'],
                    'price' => $row['price'],
                    'sale_price' => $row['sale_price'],
                    'image' => $row['image'],
                    'stock' => $row['stock']
                ];
                
                if ($this->items[$key]['quantity'] > $row['stock']) {
                    $this->items[$key]['quantity'] = $row['stock'];
                }
                
                error_log("DEBUG: Cart::loadProductsData - Loaded key: $key, data: " . print_r($row, true) . "\n", 3, '/tmp/cart_debug.log');
                $products_found++;
            }
        }
        
        error_log("DEBUG: Cart::loadProductsData - Found $products_found products for " . count($this->items) . " items\n", 3, '/tmp/cart_debug.log');
        
        if ($products_found === 0) {
            foreach ($this->items as $key => &$item) {
                $item['data'] = [
                    'name' => 'Unknown',
                    'price' => 100000,
                    'sale_price' => 0,
                    'image' => '',
                    'stock' => 10
                ];
                error_log("WARNING: Cart::loadProductsData - Set default data for key: $key\n", 3, '/tmp/cart_debug.log');
            }
        }
        
        $this->updateTotals();
        $this->saveCart();
    }
}
?>