<?php 
$page_title = htmlspecialchars($this->product->name);
include 'views/layouts/header.php'; 
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
        <li class="breadcrumb-item"><a href="index.php?controller=product&action=list">Sản phẩm</a></li>
        <li class="breadcrumb-item"><a href="index.php?controller=product&action=list&category_id=<?php echo $this->product->category_id; ?>"><?php echo htmlspecialchars($category_name); ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($this->product->name); ?></li>
    </ol>
</nav>

<div class="row">
    <!-- Product Image -->
    <div class="col-lg-6 mb-4">
        <div class="product-image-container border rounded p-3 bg-white text-center">
            <?php if(!empty($this->product->image)): ?>
            <img src="<?php echo htmlspecialchars($this->product->image); ?>" class="img-fluid product-detail-image" alt="<?php echo htmlspecialchars($this->product->name); ?>">
            <?php else: ?>
            <div class="product-placeholder bg-light p-5 d-flex align-items-center justify-content-center" style="height: 400px;">
                <i class="fas fa-tshirt fa-6x text-secondary"></i>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Product Thumbnails - In a real application, you might have multiple images -->
        <div class="product-thumbnails mt-3 d-flex">
            <div class="thumbnail-item me-2 border rounded p-2 active">
                <?php if(!empty($this->product->image)): ?>
                <img src="<?php echo htmlspecialchars($this->product->image); ?>" class="img-fluid" alt="Hình thu nhỏ">
                <?php else: ?>
                <div class="thumbnail-placeholder bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="fas fa-tshirt fa-2x text-secondary"></i>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Product Details -->
    <div class="col-lg-6">
        <h1 class="product-title mb-3"><?php echo htmlspecialchars($this->product->name); ?></h1>
        
        <!-- Price -->
        <div class="product-price mb-4">
            <?php if($this->product->is_sale == 1 && !empty($this->product->sale_price) && $this->product->sale_price < $this->product->price): ?>
            <span class="text-danger fs-4 fw-bold"><?php echo CURRENCY . number_format($this->product->sale_price); ?></span>
            <span class="text-muted text-decoration-line-through fs-5 ms-2"><?php echo CURRENCY . number_format($this->product->price); ?></span>
            <span class="badge bg-danger ms-2">Giảm giá</span>
            <?php else: ?>
            <span class="fs-4 fw-bold"><?php echo CURRENCY . number_format($this->product->price); ?></span>
            <?php endif; ?>
        </div>
        
        <!-- Availability -->
        <div class="product-availability mb-4">
            <p class="mb-1">Tình trạng: 
                <?php if($this->product->stock > 0): ?>
                <span class="text-success">Còn hàng (<?php echo $this->product->stock; ?> sản phẩm)</span>
                <?php else: ?>
                <span class="text-danger">Hết hàng</span>
                <?php endif; ?>
            </p>
            <p class="mb-1">Danh mục: <a href="index.php?controller=product&action=list&category_id=<?php echo $this->product->category_id; ?>"><?php echo htmlspecialchars($category_name); ?></a></p>
        </div>
        
        <!-- Description -->
        <div class="product-description mb-4">
            <h5>Mô tả sản phẩm</h5>
            <p><?php echo nl2br(htmlspecialchars($this->product->description)); ?></p>
        </div>
        
        <!-- Add to Cart Form -->
        <?php if($this->product->stock > 0): ?>
        <form id="add-to-cart-form" class="mb-4">
            <input type="hidden" name="product_id" value="<?php echo $this->product->id; ?>">
            <div class="row align-items-center">
                <div class="col-md-3 mb-3 mb-md-0">
                    <label for="quantity" class="form-label">Số lượng</label>
                    <div class="input-group">
                        <button type="button" class="btn btn-outline-secondary qty-btn" data-action="decrease">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" id="quantity" name="quantity" class="form-control text-center" value="1" min="1" max="<?php echo $this->product->stock; ?>">
                        <button type="button" class="btn btn-outline-secondary qty-btn" data-action="increase">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-9">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-shopping-cart me-2"></i> Thêm vào giỏ hàng
                    </button>
                </div>
            </div>
        </form>
        <?php else: ?>
        <div class="alert alert-danger">
            <p class="mb-0">Sản phẩm này hiện đã hết hàng. Vui lòng quay lại sau.</p>
        </div>
        <?php endif; ?>
        
        <!-- Shipping & Returns -->
        <div class="product-info mb-4">
            <div class="accordion" id="productAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingShipping">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseShipping" aria-expanded="false" aria-controls="collapseShipping">
                            <i class="fas fa-truck me-2"></i> Thông tin vận chuyển
                        </button>
                    </h2>
                    <div id="collapseShipping" class="accordion-collapse collapse" aria-labelledby="headingShipping" data-bs-parent="#productAccordion">
                        <div class="accordion-body">
                            <p>Miễn phí vận chuyển cho đơn hàng trên <?php echo CURRENCY; ?>500.000.</p>
                            <p>Giao hàng tiêu chuẩn: 2-5 ngày làm việc.</p>
                            <p>Giao hàng nhanh: 1-2 ngày làm việc (phí bổ sung).</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingReturns">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReturns" aria-expanded="false" aria-controls="collapseReturns">
                            <i class="fas fa-undo me-2"></i> Chính sách đổi trả
                        </button>
                    </h2>
                    <div id="collapseReturns" class="accordion-collapse collapse" aria-labelledby="headingReturns" data-bs-parent="#productAccordion">
                        <div class="accordion-body">
                            <p>Chính sách đổi trả trong 30 ngày cho các sản phẩm chưa qua sử dụng và còn nguyên bao bì.</p>
                            <p>Miễn phí đổi trả cho các trường hợp đổi size.</p>
                            <p>Liên hệ với đội ngũ chăm sóc khách hàng của chúng tôi để bắt đầu quy trình đổi trả.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingSize">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSize" aria-expanded="false" aria-controls="collapseSize">
                            <i class="fas fa-ruler me-2"></i> Bảng size
                        </button>
                    </h2>
                    <div id="collapseSize" class="accordion-collapse collapse" aria-labelledby="headingSize" data-bs-parent="#productAccordion">
                        <div class="accordion-body">
                            <p>0-3 tháng: 50-56 cm</p>
                            <p>3-6 tháng: 56-62 cm</p>
                            <p>6-9 tháng: 62-68 cm</p>
                            <p>9-12 tháng: 68-74 cm</p>
                            <p>12-18 tháng: 74-80 cm</p>
                            <p>18-24 tháng: 80-86 cm</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Share Buttons -->
        <div class="product-share">
            <p class="mb-2">Chia sẻ sản phẩm:</p>
            <a href="#" class="btn btn-sm btn-outline-primary me-2"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="btn btn-sm btn-outline-info me-2"><i class="fab fa-twitter"></i></a>
            <a href="#" class="btn btn-sm btn-outline-danger me-2"><i class="fab fa-pinterest"></i></a>
            <a href="#" class="btn btn-sm btn-outline-success"><i class="fab fa-whatsapp"></i></a>
        </div>
    </div>
</div>

<!-- Related Products -->
<?php if(!empty($related_products)): ?>
<section class="related-products mt-5">
    <h3 class="mb-4">Sản phẩm liên quan</h3>
    <div class="row">
        <?php foreach($related_products as $product): ?>
        <div class="col-6 col-md-3 mb-4">
            <div class="product-card h-100">
                <div class="card border-0 shadow-sm h-100">
                    <div class="position-relative">
                        <a href="index.php?controller=product&action=detail&id=<?php echo $product['id']; ?>">
                            <?php if(!empty($product['image'])): ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                            <div class="card-img-top bg-light p-4 d-flex align-items-center justify-content-center" style="height: 180px;">
                                <i class="fas fa-tshirt fa-3x text-secondary"></i>
                            </div>
                            <?php endif; ?>
                        </a>
                        <?php if($product['is_sale'] == 1 && !empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                        <div class="product-badge bg-danger text-white position-absolute top-0 end-0 m-2 px-2 py-1 rounded">Giảm giá</div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <a href="index.php?controller=product&action=detail&id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($product['name']); ?></a>
                        </h5>
                        <div class="price-block mb-3">
                            <?php if($product['is_sale'] == 1 && !empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                            <span class="text-danger fw-bold"><?php echo CURRENCY . number_format($product['sale_price']); ?></span>
                            <span class="text-muted text-decoration-line-through ms-2"><?php echo CURRENCY . number_format($product['price']); ?></span>
                            <?php else: ?>
                            <span class="fw-bold"><?php echo CURRENCY . number_format($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-auto">
                            <button class="btn btn-primary btn-sm w-100 add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-shopping-cart me-1"></i> Thêm vào giỏ hàng
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Customer Reviews Section - This would be implemented in a real application -->
<section class="customer-reviews mt-5">
    <h3 class="mb-4">Đánh giá của khách hàng</h3>
    <div class="alert alert-info">
        <p class="mb-0">Sản phẩm này chưa có đánh giá nào. Hãy là người đầu tiên đánh giá!</p>
    </div>
</section>

<script>
// Add to cart form handling
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('add-to-cart-form');
    const quantityInput = document.getElementById('quantity');
    const maxQuantity = <?php echo $this->product->stock; ?>;
    
    // Quantity buttons
    document.querySelectorAll('.qty-btn').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action;
            let currentQuantity = parseInt(quantityInput.value);
            
            if (action === 'increase' && currentQuantity < maxQuantity) {
                quantityInput.value = currentQuantity + 1;
            } else if (action === 'decrease' && currentQuantity > 1) {
                quantityInput.value = currentQuantity - 1;
            }
        });
    });
    
    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const productId = this.querySelector('[name="product_id"]').value;
            const quantity = parseInt(this.querySelector('[name="quantity"]').value);
            
            // AJAX request to add to cart
            fetch('index.php?controller=product&action=addToCart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alert(data.message);
                    
                    // Update cart count in header
                    const cartBadge = document.querySelector('.fa-shopping-cart').nextElementSibling;
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                    }
                } else {
                    // Show error message
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                alert('Đã xảy ra lỗi. Vui lòng thử lại.');
            });
        });
    }
});
</script>

<?php include 'views/layouts/footer.php'; ?>
