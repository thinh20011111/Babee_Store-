<?php 
$page_title = "Trang chủ";
include 'views/layouts/header.php'; 
?>

<!-- Hero Banner Section -->
<?php if(!empty($banners)): ?>
<div id="mainCarousel" class="carousel slide banner-carousel mb-5" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <?php foreach($banners as $index => $banner): ?>
        <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="<?php echo $index; ?>" <?php echo $index === 0 ? 'class="active"' : ''; ?>></button>
        <?php endforeach; ?>
    </div>
    <div class="carousel-inner">
        <?php foreach($banners as $index => $banner): ?>
        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
            <img src="<?php echo htmlspecialchars($banner['image']); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($banner['title']); ?>">
            <div class="carousel-caption d-flex flex-column justify-content-center align-items-center">
                <h2 class="fade-in text-shadow"><?php echo htmlspecialchars($banner['title']); ?></h2>
                <p class="fade-in text-shadow"><?php echo htmlspecialchars($banner['subtitle']); ?></p>
                <?php if(!empty($banner['link'])): ?>
                <a href="<?php echo htmlspecialchars($banner['link']); ?>" class="btn btn-light btn-lg fade-in"><?php echo isset($banner['button_text']) ? htmlspecialchars($banner['button_text']) : 'SHOP NOW'; ?></a>
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
<?php else: ?>
<!-- Fallback Hero Banner -->
<div class="hero-banner mb-5 position-relative">
    <div class="hero-banner-image" style="background-image: url('https://via.placeholder.com/1200x500/000000/FFFFFF/?text=STREETSTYLE'); background-size: cover; background-position: center; height: 500px;">
        <div class="position-absolute top-50 start-50 translate-middle text-center text-white p-4" style="background-color: rgba(0,0,0,0.4); max-width: 600px; width: 70%;">
            <h1 class="display-4 fw-bold mb-3">NEW ARRIVALS</h1>
            <p class="lead mb-4">Phong cách thời trang đường phố táo bạo và nổi bật dành cho giới trẻ</p>
            <a href="index.php?controller=product&action=list" class="btn btn-light btn-lg">SHOP NOW</a>
        </div>
    </div>
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
                    <div class="card h-100 border-0 shadow-sm d-flex flex-column">
                        <?php if(!empty($category['image'])): ?>
                        <div class="category-image-wrapper">
                            <img src="<?php echo htmlspecialchars($category['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($category['name']); ?>">
                        </div>
                        <?php else: ?>
                        <div class="card-img-top bg-light p-4 d-flex align-items-center justify-content-center category-image-wrapper">
                            <i class="fas fa-tshirt fa-4x text-primary"></i>
                        </div>
                        <?php endif; ?>
                        <div class="card-body flex-grow-1 d-flex align-items-center justify-content-center">
                            <h5 class="card-title category-title"><?php echo htmlspecialchars($category['name']); ?></h5>
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
                        <div class="product-badge bg-danger text-white position-absolute top-0 m-2 px-2 py-1 rounded">Sale</div>
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
                        <div class="product-badge bg-danger text-white position-absolute top-0 m-2 px-2 py-1 rounded">Sale</div>
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
                <h5>Miễn phí vận chuyển</h5>
                <p class="text-muted small">Áp dụng cho đơn hàng trên <?php echo CURRENCY; ?>500,000</p>
            </div>
        </div>
        <div class="col-md-3 mb-4 mb-md-0">
            <div class="feature-box p-3">
                <i class="fas fa-undo fa-3x text-primary mb-3"></i>
                <h5>Đổi trả dễ dàng</h5>
                <p class="text-muted small">Chính sách đổi trả trong 7 ngày</p>
            </div>
        </div>
        <div class="col-md-3 mb-4 mb-md-0">
            <div class="feature-box p-3">
                <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                <h5>Mua sắm an toàn</h5>
                <p class="text-muted small">Dữ liệu của bạn sẽ được bảo mật</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="feature-box p-3">
                <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                <h5>Hỗ trợ 24/7</h5>
                <p class="text-muted small">Dịch vụ chăm sóc khách hàng tận tâm</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section mb-5 py-5 bg-light rounded">
    <div class="section-title text-center mb-4">
        <h2>Đánh giá từ khách hàng</h2>
        <p class="text-muted">Được các ba mẹ Việt Nam tin dùng</p>
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
                            <p class="testimonial-text mb-3">"Chất lượng quần áo thật tuyệt vời! Con tôi trông thật dễ thương khi mặc chúng và chúng mặc được qua nhiều lần giặt mà không bị phai màu."</p>
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
                            <p class="testimonial-text mb-3">"Giao hàng nhanh và dịch vụ khách hàng tuyệt vời. Tôi có thắc mắc về kích thước và họ rất hữu ích. Chắc chắn sẽ mua sắm ở đây lần nữa!"</p>
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
                            <p class="testimonial-text mb-3">"Tôi thích sự đa dạng về kiểu dáng. Chất vải mềm mại và dịu nhẹ với làn da của bé. Hoàn hảo cho mọi mùa!"</p>
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
        <h2>Follow Us on Tiktok</h2>
        <p class="text-muted">@babee_studio</p>
    </div>
    <div class="row g-2">
        <div class="col-4 col-md-2">
            <a href="https://www.tiktok.com/@babee_studio" class="instagram-item d-block position-relative">
                <div class="ratio ratio-1x1 bg-light"></div>
                <div class="instagram-overlay d-flex align-items-center justify-content-center">
                    <i class="fab fa-tiktok text-white fa-2x"></i>
                </div>
            </a>
        </div>
        <div class="col-4 col-md-2">
            <a href="https://www.tiktok.com/@babee_studio" class="instagram-item d-block position-relative">
                <div class="ratio ratio-1x1 bg-light"></div>
                <div class="instagram-overlay d-flex align-items-center justify-content-center">
                    <i class="fab fa-tiktok text-white fa-2x"></i>
                </div>
            </a>
        </div>
        <div class="col-4 col-md-2">
            <a href="https://www.tiktok.com/@babee_studio" class="instagram-item d-block position-relative">
                <div class="ratio ratio-1x1 bg-light"></div>
                <div class="instagram-overlay d-flex align-items-center justify-content-center">
                    <i class="fab fa-tiktok text-white fa-2x"></i>
                </div>
            </a>
        </div>
        <div class="col-4 col-md-2">
            <a href="https://www.tiktok.com/@babee_studio" class="instagram-item d-block position-relative">
                <div class="ratio ratio-1x1 bg-light"></div>
                <div class="instagram-overlay d-flex align-items-center justify-content-center">
                    <i class="fab fa-tiktok text-white fa-2x"></i>
                </div>
            </a>
        </div>
        <div class="col-4 col-md-2">
            <a href="https://www.tiktok.com/@babee_studio" class="instagram-item d-block position-relative">
                <div class="ratio ratio-1x1 bg-light"></div>
                <div class="instagram-overlay d-flex align-items-center justify-content-center">
                    <i class="fab fa-tiktok text-white fa-2x"></i>
                </div>
            </a>
        </div>
        <div class="col-4 col-md-2">
            <a href="https://www.tiktok.com/@babee_studio" class="instagram-item d-block position-relative">
                <div class="ratio ratio-1x1 bg-light"></div>
                <div class="instagram-overlay d-flex align-items-center justify-content-center">
                    <i class="fab fa-tiktok text-white fa-2x"></i>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter-section py-5" style="background-color: var(--primary-color, #0d6efd);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h3 class="text-white fw-bold mb-3 newsletter-heading">STAY IN THE LOOP</h3>
                <p class="text-white mb-4">Đăng ký nhận tin để cập nhật về hàng mới, thông tin khuyến mãi và mã giảm giá độc quyền.</p>
                <form action="#" method="POST" class="newsletter-form">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control form-control-lg rounded-start" placeholder="Email của bạn" required>
                        <button class="btn btn-dark btn-lg rounded-end px-4" type="submit">ĐĂNG KÝ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'views/layouts/footer.php'; ?>

<style>
    /* Import Google Fonts - Using Open Sans as fallback for ValueSansProVN */
    @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap');

    /* General Font Styling */
    body, h1, h2, h3, h4, h5, h6, p, a, button, input, .card-title, .card-text {
        font-family: 'Open Sans', sans-serif !important;
    }

    /* Banner Carousel Styling */
    .banner-carousel {
        position: relative;
        overflow: hidden;
    }

    .banner-carousel .carousel-item img {
        object-fit: cover;
        width: 100%;
        height: 500px;
    }

    .banner-carousel .carousel-caption {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        color: white;
        padding: 60px 40px 0;
        background: rgba(0, 0, 0, 0.4);
        border-radius: 10px;
        max-width: 600px;
        width: 70%;
        margin-top: 20px;
    }

    .banner-carousel .carousel-caption h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 20px 0 15px;
        line-height: 1.2;
        color: #f8f9fa;
    }

    .banner-carousel .carousel-caption p {
        font-size: 1.2rem;
        font-weight: 400;
        margin: 15px 0 20px;
        line-height: 1.5;
        color: #e9ecef;
    }

    .banner-carousel .carousel-caption .btn {
        padding: 10px 25px;
        font-weight: 500;
        background-color: #f8f9fa;
        color: #212529;
        border: none;
        margin: 0;
        transform: translateY(50%);
        position: relative;
        z-index: 1;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .banner-carousel .carousel-caption .btn:hover {
        background-color: #dee2e6;
        transform: translateY(48%);
    }

    .banner-carousel .fade-in {
        animation: fadeIn 1s ease-in-out;
    }

    .banner-carousel .text-shadow {
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Fallback Hero Banner Styling */
    .hero-banner .hero-banner-image {
        position: relative;
        height: 500px;
    }

    .hero-banner .hero-banner-image .position-absolute {
        transform: translate(-50%, -50%);
        padding: 50px 20px 0;
        background: rgba(0, 0, 0, 0.4);
        border-radius: 10px;
        max-width: 600px;
        width: 70%;
        margin-top: 20px;
    }

    .hero-banner .hero-banner-image .position-absolute h1 {
        margin-top: 20px;
    }

    .hero-banner .hero-banner-image .position-absolute p {
        margin-top: 15px;
    }

    .hero-banner .hero-banner-image .position-absolute a {
        margin: 0;
        transform: translateY(50%);
        position: relative;
        z-index: 1;
    }

    /* Category Card Styling - Updated for uniform height and better typography */
    .category-card .card {
        display: flex;
        flex-direction: column;
        height: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .category-card .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .category-image-wrapper {
        position: relative;
        overflow: hidden;
        aspect-ratio: 1 / 1;
        height: 180px;
    }

    .category-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .category-card .card-body {
        flex-grow: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .category-title {
        font-size: 1.1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: var(--text-color, #121212);
        margin: 0;
        padding: 10px 0;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .banner-carousel .carousel-item img,
        .hero-banner .hero-banner-image {
            height: 300px;
        }

        .banner-carousel .carousel-caption,
        .hero-banner .hero-banner-image .position-absolute {
            width: 80%;
            padding: 50px 30px 0;
            margin-top: 15px;
        }

        .banner-carousel .carousel-caption h2 {
            font-size: 1.5rem;
            margin: 15px 0 10px;
        }

        .banner-carousel .carousel-caption p {
            font-size: 1rem;
            margin: 10px 0 15px;
        }

        .banner-carousel .carousel-caption .btn {
            font-size: 1rem;
            padding: 8px 16px;
            margin: 0;
            transform: translateY(50%);
        }

        .hero-banner .hero-banner-image .position-absolute h1 {
            margin-top: 15px;
        }

        .hero-banner .hero-banner-image .position-absolute p {
            margin-top: 10px;
        }

        .hero-banner .hero-banner-image .position-absolute a {
            margin: 0;
            transform: translateY(50%);
        }

        .category-image-wrapper {
            height: 150px;
        }

        .category-title {
            font-size: 1rem;
        }
    }

    @media (max-width: 576px) {
        .banner-carousel .carousel-item img,
        .hero-banner .hero-banner-image {
            height: 250px;
        }

        .banner-carousel .carousel-caption,
        .hero-banner .hero-banner-image .position-absolute {
            width: 90%;
            padding: 40px 20px 0;
            margin-top: 10px;
        }

        .banner-carousel .carousel-caption h2 {
            font-size: 1.2rem;
            margin: 10px 0 10px;
        }

        .banner-carousel .carousel-caption p {
            font-size: 0.9rem;
            margin: 8px 0 12px;
        }

        .banner-carousel .carousel-caption .btn {
            font-size: 0.9rem;
            padding: 6px 12px;
            margin: 0;
            transform: translateY(50%);
        }

        .hero-banner .hero-banner-image .position-absolute h1 {
            margin-top: 10px;
        }

        .hero-banner .hero-banner-image .position-absolute p {
            margin-top: 8px;
        }

        .hero-banner .hero-banner-image .position-absolute a {
            margin: 0;
            transform: translateY(50%);
        }

        .category-image-wrapper {
            height: 120px;
        }

        .category-title {
            font-size: 0.9rem;
        }
    }
</style>