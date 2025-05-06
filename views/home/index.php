<?php 
$page_title = "Trang chủ";
include 'views/layouts/header.php'; 
?>

<!-- Carousel/Banner Section -->
<?php if(!empty($banners)): ?>
<div id="mainCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <?php foreach($banners as $index => $banner): ?>
        <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="<?php echo $index; ?>" <?php echo $index === 0 ? 'class="active"' : ''; ?>></button>
        <?php endforeach; ?>
    </div>
    <div class="carousel-inner rounded shadow">
        <?php foreach($banners as $index => $banner): ?>
        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
            <img src="<?php echo htmlspecialchars($banner['image']); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($banner['title']); ?>">
            <div class="carousel-caption d-none d-md-block">
                <h2><?php echo htmlspecialchars($banner['title']); ?></h2>
                <p><?php echo htmlspecialchars($banner['subtitle']); ?></p>
                <?php if(!empty($banner['link'])): ?>
                <a href="<?php echo htmlspecialchars($banner['link']); ?>" class="btn btn-primary">Mua ngay</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Trước</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Sau</span>
    </button>
</div>
<?php endif; ?>

<!-- Categories Section -->
<?php if(!empty($categories)): ?>
<section class="categories-section mb-5">
    <div class="section-title text-center mb-4">
        <h2>Mua sắm theo danh mục</h2>
        <p class="text-muted">Tìm trang phục hoàn hảo cho bé yêu của bạn</p>
    </div>
    <div class="row">
        <?php foreach($categories as $category): ?>
        <div class="col-6 col-md-4 col-lg-3 mb-4">
            <div class="category-card text-center">
                <a href="index.php?controller=product&action=list&category_id=<?php echo $category['id']; ?>" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm">
                        <?php if(!empty($category['image'])): ?>
                        <img src="<?php echo htmlspecialchars($category['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($category['name']); ?>">
                        <?php else: ?>
                        <div class="card-img-top bg-light p-4 d-flex align-items-center justify-content-center" style="height: 180px;">
                            <i class="fas fa-tshirt fa-4x text-primary"></i>
                        </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Featured Products Section -->
<?php if(!empty($featured_products)): ?>
<section class="featured-products mb-5">
    <div class="section-title text-center mb-4">
        <h2>Sản phẩm nổi bật</h2>
        <p class="text-muted">Được lựa chọn cẩn thận với tình yêu cho bé yêu của bạn</p>
    </div>
    <div class="row">
        <?php foreach($featured_products as $product): ?>
        <div class="col-6 col-md-4 col-lg-3 mb-4">
            <div class="product-card h-100">
                <div class="card border-0 shadow-sm h-100">
                    <div class="position-relative">
                        <a href="index.php?controller=product&action=detail&id=<?php echo $product['id']; ?>">
                            <?php if(!empty($product['image'])): ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                            <div class="card-img-top bg-light p-4 d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-tshirt fa-4x text-secondary"></i>
                            </div>
                            <?php endif; ?>
                        </a>
                        <?php if($product['is_sale'] == 1 && !empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                        <div class="product-badge bg-danger text-white position-absolute top-0 end-0 m-2 px-2 py-1 rounded">Sale</div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="product-category text-muted small mb-1"><?php echo htmlspecialchars($product['category_name'] ?? ''); ?></div>
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
                                <i class="fas fa-shopping-cart me-1"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
        <a href="index.php?controller=product&action=list" class="btn btn-outline-primary">View All Products</a>
    </div>
</section>
<?php endif; ?>

<!-- Sale Products Section -->
<?php if(!empty($sale_products)): ?>
<section class="sale-products mb-5">
    <div class="section-title text-center mb-4">
        <h2>Khuyến mãi</h2>
        <p class="text-muted">Ưu đãi có thời hạn cho quần áo trẻ em</p>
    </div>
    <div class="row">
        <?php foreach($sale_products as $product): ?>
        <div class="col-6 col-md-4 col-lg-3 mb-4">
            <div class="product-card h-100">
                <div class="card border-0 shadow-sm h-100">
                    <div class="position-relative">
                        <a href="index.php?controller=product&action=detail&id=<?php echo $product['id']; ?>">
                            <?php if(!empty($product['image'])): ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                            <div class="card-img-top bg-light p-4 d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-tshirt fa-4x text-secondary"></i>
                            </div>
                            <?php endif; ?>
                        </a>
                        <div class="product-badge bg-danger text-white position-absolute top-0 end-0 m-2 px-2 py-1 rounded">Sale</div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="product-category text-muted small mb-1"><?php echo htmlspecialchars($product['category_name'] ?? ''); ?></div>
                        <h5 class="card-title">
                            <a href="index.php?controller=product&action=detail&id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($product['name']); ?></a>
                        </h5>
                        <div class="price-block mb-3">
                            <span class="text-danger fw-bold"><?php echo CURRENCY . number_format($product['sale_price']); ?></span>
                            <span class="text-muted text-decoration-line-through ms-2"><?php echo CURRENCY . number_format($product['price']); ?></span>
                        </div>
                        <div class="mt-auto">
                            <button class="btn btn-primary btn-sm w-100 add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-shopping-cart me-1"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
        <a href="index.php?controller=product&action=list&is_sale=1" class="btn btn-outline-danger">View All Sale Items</a>
    </div>
</section>
<?php endif; ?>

<!-- Features Section -->
<section class="features-section mb-5">
    <div class="row text-center">
        <div class="col-md-3 mb-4 mb-md-0">
            <div class="feature-box p-3">
                <i class="fas fa-truck fa-3x text-primary mb-3"></i>
                <h5>Free Shipping</h5>
                <p class="text-muted small">On orders over <?php echo CURRENCY; ?>500,000</p>
            </div>
        </div>
        <div class="col-md-3 mb-4 mb-md-0">
            <div class="feature-box p-3">
                <i class="fas fa-undo fa-3x text-primary mb-3"></i>
                <h5>Easy Returns</h5>
                <p class="text-muted small">30 days return policy</p>
            </div>
        </div>
        <div class="col-md-3 mb-4 mb-md-0">
            <div class="feature-box p-3">
                <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                <h5>Secure Shopping</h5>
                <p class="text-muted small">Your data is protected</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="feature-box p-3">
                <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                <h5>24/7 Support</h5>
                <p class="text-muted small">Dedicated customer service</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section mb-5 py-5 bg-light rounded">
    <div class="section-title text-center mb-4">
        <h2>What Parents Say</h2>
        <p class="text-muted">Trusted by families across Vietnam</p>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="testimonial-item text-center p-4">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="testimonial-text mb-3">"The quality of the clothes is amazing! My baby looks so cute in them and they've lasted through multiple washes without fading."</p>
                            <div class="testimonial-author">
                                <h6 class="mb-0">Tran Minh Anh</h6>
                                <small class="text-muted">Mother of 1</small>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="testimonial-item text-center p-4">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="testimonial-text mb-3">"Fast shipping and great customer service. I had a question about sizing and they were so helpful. Will definitely shop here again!"</p>
                            <div class="testimonial-author">
                                <h6 class="mb-0">Nguyen Van Hai</h6>
                                <small class="text-muted">Father of twins</small>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="testimonial-item text-center p-4">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star-half-alt text-warning"></i>
                            </div>
                            <p class="testimonial-text mb-3">"I love the variety of styles available. The fabric is soft and gentle on my baby's skin. Perfect for every season!"</p>
                            <div class="testimonial-author">
                                <h6 class="mb-0">Le Thi Hong</h6>
                                <small class="text-muted">Mother of 2</small>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Instagram Section -->
<section class="instagram-section mb-5">
    <div class="section-title text-center mb-4">
        <h2>Follow Us on Instagram</h2>
        <p class="text-muted">@babeestore</p>
    </div>
    <div class="row g-2">
        <div class="col-4 col-md-2">
            <a href="#" class="instagram-item d-block position-relative">
                <div class="ratio ratio-1x1 bg-light"></div>
                <div class="instagram-overlay d-flex align-items-center justify-content-center">
                    <i class="fab fa-instagram text-white fa-2x"></i>
                </div>
            </a>
        </div>
        <div class="col-4 col-md-2">
            <a href="#" class="instagram-item d-block position-relative">
                <div class="ratio ratio-1x1 bg-light"></div>
                <div class="instagram-overlay d-flex align-items-center justify-content-center">
                    <i class="fab fa-instagram text-white fa-2x"></i>
                </div>
            </a>
        </div>
        <div class="col-4 col-md-2">
            <a href="#" class="instagram-item d-block position-relative">
                <div class="ratio ratio-1x1 bg-light"></div>
                <div class="instagram-overlay d-flex align-items-center justify-content-center">
                    <i class="fab fa-instagram text-white fa-2x"></i>
                </div>
            </a>
        </div>
        <div class="col-4 col-md-2">
            <a href="#" class="instagram-item d-block position-relative">
                <div class="ratio ratio-1x1 bg-light"></div>
                <div class="instagram-overlay d-flex align-items-center justify-content-center">
                    <i class="fab fa-instagram text-white fa-2x"></i>
                </div>
            </a>
        </div>
        <div class="col-4 col-md-2">
            <a href="#" class="instagram-item d-block position-relative">
                <div class="ratio ratio-1x1 bg-light"></div>
                <div class="instagram-overlay d-flex align-items-center justify-content-center">
                    <i class="fab fa-instagram text-white fa-2x"></i>
                </div>
            </a>
        </div>
        <div class="col-4 col-md-2">
            <a href="#" class="instagram-item d-block position-relative">
                <div class="ratio ratio-1x1 bg-light"></div>
                <div class="instagram-overlay d-flex align-items-center justify-content-center">
                    <i class="fab fa-instagram text-white fa-2x"></i>
                </div>
            </a>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>
