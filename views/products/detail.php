<?php 
$page_title = htmlspecialchars($this->product->name);
include 'views/layouts/header.php'; 
?>

<!-- Page Header with Breadcrumb -->
<div class="category-header position-relative mb-5">
    <div class="category-header-bg" style="background-color: var(--light-bg-color); height: 120px; position: relative; overflow: hidden;">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                            <li class="breadcrumb-item"><a href="index.php?controller=product&action=list" class="text-decoration-none">Shop</a></li>
                            <li class="breadcrumb-item"><a href="index.php?controller=product&action=list&category_id=<?php echo $this->product->category_id; ?>" class="text-decoration-none"><?php echo htmlspecialchars($category_name); ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($this->product->name); ?></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="position-absolute" style="top:0; right:0; bottom:0; left:0; background: linear-gradient(135deg, rgba(255,45,85,0.1) 0%, rgba(74,0,224,0.05) 100%);"></div>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="product-image-container">
                <?php if(!empty($this->product->image)): ?>
                <img src="<?php echo htmlspecialchars($this->product->image); ?>" class="product-detail-image" alt="<?php echo htmlspecialchars($this->product->name); ?>">
                <?php else: ?>
                <div class="product-placeholder d-flex align-items-center justify-content-center" style="height: 500px;">
                    <i class="fas fa-tshirt fa-6x text-secondary"></i>
                </div>
                <?php endif; ?>
                
                <?php if($this->product->is_sale == 1 && !empty($this->product->sale_price) && $this->product->sale_price < $this->product->price): ?>
                <div class="sale-badge product-badge position-absolute">SALE</div>
                <?php endif; ?>
            </div>
            
            <!-- Product Thumbnails -->
            <div class="product-thumbnails mt-3 d-flex">
                <div class="thumbnail-item me-2 active">
                    <?php if(!empty($this->product->image)): ?>
                    <img src="<?php echo htmlspecialchars($this->product->image); ?>" alt="Thumbnail">
                    <?php else: ?>
                    <div class="thumbnail-placeholder d-flex align-items-center justify-content-center">
                        <i class="fas fa-tshirt fa-2x text-secondary"></i>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Product Details -->
        <div class="col-lg-6">
            <div class="product-category text-uppercase mb-2"><?php echo htmlspecialchars($category_name); ?></div>
            <h1 class="product-title mb-3"><?php echo htmlspecialchars($this->product->name); ?></h1>
            
            <!-- Price -->
            <div class="product-price mb-4">
                <?php if($this->product->is_sale == 1 && !empty($this->product->sale_price) && $this->product->sale_price < $this->product->price): ?>
                <span class="text-danger fs-3 fw-bold"><?php echo CURRENCY . number_format($this->product->sale_price); ?></span>
                <span class="text-muted text-decoration-line-through fs-5 ms-2"><?php echo CURRENCY . number_format($this->product->price); ?></span>
                <?php else: ?>
                <span class="fs-3 fw-bold"><?php echo CURRENCY . number_format($this->product->price); ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Availability -->
            <div class="product-availability mb-4">
                <div class="d-flex align-items-center mb-2">
                    <span class="me-2 fw-bold">Tình trạng:</span>
                    <?php
                    // Tính tổng tồn kho từ product_variants
                    $total_stock = array_sum(array_column($this->variants, 'stock'));
                    ?>
                    <?php if($total_stock > 0): ?>
                    <span class="badge bg-success rounded-0 py-2 px-3">CÒN HÀNG</span>
                    <?php else: ?>
                    <span class="badge bg-danger rounded-0 py-2 px-3">HẾT HÀNG</span>
                    <?php endif; ?>
                </div>
                <div class="mb-2">
                    <span class="fw-bold">Danh mục:</span> 
                    <a href="index.php?controller=product&action=list&category_id=<?php echo $this->product->category_id; ?>" class="ms-2 badge bg-light text-dark text-decoration-none py-2 px-3 rounded-0"><?php echo htmlspecialchars($category_name); ?></a>
                </div>
            </div>
            
            <!-- Short Description -->
            <div class="product-description mb-4">
                <p class="lead"><?php echo nl2br(htmlspecialchars($this->product->description)); ?></p>
            </div>
        
            <!-- Add to Cart Form -->
            <?php if($total_stock > 0): ?>
            <form id="add-to-cart-form" class="mb-4">
                <input type="hidden" name="product_id" value="<?php echo $this->product->id; ?>">
                
                <!-- Variant Selector -->
                <div class="product-variants mb-4">
                    <label class="fw-bold d-block mb-2">Biến thể:</label>
                    <div class="row">
                        <!-- Size Selector -->
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold d-block mb-2">Kích cỡ:</label>
                            <select class="form-select" name="size" id="variant-size" required>
                                <option value="" disabled selected>Chọn kích cỡ</option>
                                <?php
                                $sizes = array_unique(array_column($this->variants, 'size'));
                                foreach($sizes as $size):
                                ?>
                                <option value="<?php echo htmlspecialchars($size); ?>"><?php echo htmlspecialchars($size); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Color Selector -->
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold d-block mb-2">Màu sắc:</label>
                            <select class="form-select" name="color" id="variant-color" required>
                                <option value="" disabled selected>Chọn màu sắc</option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="variant_id" id="variant-id">
                </div>
                
                <div class="row align-items-end mb-4">
                    <div class="col-5 col-md-3">
                        <label for="quantity" class="form-label fw-bold mb-2">Số lượng:</label>
                        <div class="quantity-selector d-flex">
                            <button type="button" class="qty-btn" data-action="decrease">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" id="quantity" name="quantity" class="form-control text-center" value="1" min="1" max="1">
                            <button type="button" class="qty-btn" data-action="increase">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-7 col-md-4 mb-3 mb-md-0">
                        <button type="button" class="btn btn-outline-dark w-100 py-3 fw-bold">
                            <i class="far fa-heart"></i> WISHLIST
                        </button>
                    </div>
                    <div class="col-12 col-md-5">
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">
                            THÊM VÀO GIỎ HÀNG
                        </button>
                    </div>
                </div>
            </form>
            <?php else: ?>
            <div class="product-out-of-stock mb-4 p-3 bg-light text-center">
                <p class="mb-2 fw-bold text-danger">SẢN PHẨM TẠM HẾT HÀNG</p>
                <p class="mb-0 small">Vui lòng để lại email để nhận thông báo khi sản phẩm có hàng trở lại</p>
                <form class="mt-3 d-flex gap-2">
                    <input type="email" class="form-control" placeholder="Email của bạn">
                    <button type="submit" class="btn btn-primary">Thông báo cho tôi</button>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- Product Features -->
            <div class="product-features mb-4">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="feature-item text-center p-3">
                            <i class="fas fa-truck-fast fs-3 mb-2 text-primary"></i>
                            <p class="mb-0 small">FREESHIP ĐƠN > 500K</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="feature-item text-center p-3">
                            <i class="fas fa-shield-alt fs-3 mb-2 text-primary"></i>
                            <p class="mb-0 small">BẢO HÀNH CHÍNH HÃNG</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="feature-item text-center p-3">
                            <i class="fas fa-undo fs-3 mb-2 text-primary"></i>
                            <p class="mb-0 small">ĐỔI TRẢ 30 NGÀY</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="feature-item text-center p-3">
                            <i class="fas fa-credit-card fs-3 mb-2 text-primary"></i>
                            <p class="mb-0 small">THANH TOÁN AN TOÀN</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product Information Tabs -->
            <div class="product-info mb-4">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button">Mô tả</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button">Vận chuyển</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sizing-tab" data-bs-toggle="tab" data-bs-target="#sizing" type="button">Bảng size</button>
                    </li>
                </ul>
                <div class="tab-content p-4 border border-top-0" id="productTabContent">
                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                        <h5 class="fw-bold mb-3">Thông tin chi tiết sản phẩm</h5>
                        <p><?php echo nl2br(htmlspecialchars($this->product->description)); ?></p>
                        <ul class="mb-0">
                            <li>Chất liệu: 100% Cotton</li>
                            <li>Sản xuất tại Việt Nam</li>
                            <li>Phù hợp với phong cách đường phố</li>
                            <li>Hướng dẫn giặt: Giặt máy ở nhiệt độ thấp, không tẩy</li>
                        </ul>
                    </div>
                    <div class="tab-pane fade" id="shipping" role="tabpanel">
                        <h5 class="fw-bold mb-3">Thông tin vận chuyển</h5>
                        <p>Miễn phí vận chuyển cho đơn hàng trên <?php echo CURRENCY; ?>500.000.</p>
                        <ul>
                            <li>Giao hàng tiêu chuẩn: 2-3 ngày làm việc</li>
                            <li>Giao hàng nhanh: 1-2 ngày làm việc (phí bổ sung)</li>
                            <li>Giao hàng hỏa tốc: Trong ngày (chỉ áp dụng tại Hà Nội & TP.HCM)</li>
                        </ul>
                    </div>
                    <div class="tab-pane fade" id="sizing" role="tabpanel">
                        <h5 class="fw-bold mb-3">Bảng kích cỡ áo</h5>
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Size</th>
                                    <th>Chiều cao (cm)</th>
                                    <th>Cân nặng (kg)</th>
                                    <th>Ngực (cm)</th>
                                    <th>Eo (cm)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>S</td>
                                    <td>155-165</td>
                                    <td>45-55</td>
                                    <td>86-91</td>
                                    <td>71-76</td>
                                </tr>
                                <tr>
                                    <td>M</td>
                                    <td>165-170</td>
                                    <td>55-65</td>
                                    <td>91-97</td>
                                    <td>76-81</td>
                                </tr>
                                <tr>
                                    <td>L</td>
                                    <td>170-175</td>
                                    <td>65-75</td>
                                    <td>97-102</td>
                                    <td>81-86</td>
                                </tr>
                                <tr>
                                    <td>XL</td>
                                    <td>175-180</td>
                                    <td>75-85</td>
                                    <td>102-107</td>
                                    <td>86-91</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Social Sharing -->
            <div class="product-share border-top pt-4">
                <div class="d-flex align-items-center">
                    <span class="fw-bold me-3">CHIA SẺ:</span>
                    <div class="social-icons d-flex gap-2">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
            </div>
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

<!-- Customer Reviews Section -->
<section class="customer-reviews mt-5">
    <h3 class="mb-4">Đánh giá của khách hàng</h3>
    <div class="alert alert-info">
        <p class="mb-0">Sản phẩm này chưa có đánh giá nào. Hãy là người đầu tiên đánh giá!</p>
    </div>
</section>

<style>
.product-variants .form-select {
    padding: 0.5rem;
    font-size: 1rem;
}
.quantity-selector .qty-btn {
    border: 1px solid #dee2e6;
    background: #f8f9fa;
    padding: 0.5rem;
}
.quantity-selector .form-control {
    border-radius: 0;
    border-left: 0;
    border-right: 0;
}
</style>

<script>
// Add to cart form handling
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('add-to-cart-form');
    const sizeSelect = document.getElementById('variant-size');
    const colorSelect = document.getElementById('variant-color');
    const variantIdInput = document.getElementById('variant-id');
    const quantityInput = document.getElementById('quantity');
    const variants = <?php echo json_encode($this->variants); ?>;
    
    // Update color options based on size
    sizeSelect.addEventListener('change', function() {
        const selectedSize = this.value;
        colorSelect.innerHTML = '<option value="" disabled selected>Chọn màu sắc</option>';
        const availableColors = variants
            .filter(v => v.size === selectedSize && v.stock > 0)
            .map(v => v.color);
        const uniqueColors = [...new Set(availableColors)];
        
        uniqueColors.forEach(color => {
            const option = document.createElement('option');
            option.value = color;
            option.textContent = color;
            colorSelect.appendChild(option);
        });
        
        colorSelect.disabled = uniqueColors.length === 0;
        updateVariant();
    });
    
    // Update variant ID and max quantity
    colorSelect.addEventListener('change', updateVariant);
    
    function updateVariant() {
        const selectedSize = sizeSelect.value;
        const selectedColor = colorSelect.value;
        const variant = variants.find(v => v.size === selectedSize && v.color === selectedColor);
        
        if(variant) {
            variantIdInput.value = variant.id;
            quantityInput.max = variant.stock;
            quantityInput.value = 1;
        } else {
            variantIdInput.value = '';
            quantityInput.max = 1;
            quantityInput.value = 1;
        }
    }
    
    // Quantity buttons
    document.querySelectorAll('.qty-btn').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action;
            let currentQuantity = parseInt(quantityInput.value);
            const maxQuantity = parseInt(quantityInput.max);
            
            if (action === 'increase' && currentQuantity < maxQuantity) {
                quantityInput.value = currentQuantity + 1;
            } else if (action === 'decrease' && currentQuantity > 1) {
                quantityInput.value = currentQuantity - 1;
            }
        });
    });
    
    // Form submission
    if(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const productId = this.querySelector('[name="product_id"]').value;
            const variantId = this.querySelector('[name="variant_id"]').value;
            const quantity = parseInt(this.querySelector('[name="quantity"]').value);
            
            if(!variantId) {
                alert('Vui lòng chọn kích cỡ và màu sắc.');
                return;
            }
            
            // AJAX request to add to cart
            fetch('index.php?controller=product&action=addToCart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `product_id=${productId}&variant_id=${variantId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert(data.message);
                    const cartBadge = document.querySelector('.fa-shopping-cart').nextElementSibling;
                    if(cartBadge) {
                        cartBadge.textContent = data.cart_count;
                    }
                } else {
                    alert(data.message || 'Không thể thêm vào giỏ hàng. Vui lòng thử lại.');
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