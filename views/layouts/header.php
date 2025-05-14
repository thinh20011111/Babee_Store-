<?php
// Đảm bảo include file cấu hình cơ sở dữ liệu
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts - Modern, bold fonts for streetwear fashion -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        :root {
            --primary-color: <?php echo isset($site_colors['primary_color']) ? $site_colors['primary_color'] : '#FF2D55'; ?>;
            --secondary-color: <?php echo isset($site_colors['secondary_color']) ? $site_colors['secondary_color'] : '#4A00E0'; ?>;
            --accent-color: <?php echo isset($site_colors['accent_color']) ? $site_colors['accent_color'] : '#FFCC00'; ?>;
            --text-color: <?php echo isset($site_colors['text_color']) ? $site_colors['text_color'] : '#121212'; ?>;
            --background-color: <?php echo isset($site_colors['background_color']) ? $site_colors['background_color'] : '#FFFFFF'; ?>;
            --dark-bg-color: <?php echo isset($site_colors['dark_bg_color']) ? $site_colors['dark_bg_color'] : '#1A1A1A'; ?>;
            --light-bg-color: <?php echo isset($site_colors['light_bg_color']) ? $site_colors['light_bg_color'] : '#F7F7F7'; ?>;
            --footer-color: <?php echo isset($site_colors['footer_color']) ? $site_colors['footer_color'] : '#0D0D0D'; ?>;
            --success-color: <?php echo isset($site_colors['success_color']) ? $site_colors['success_color'] : '#00C851'; ?>;
            --warning-color: <?php echo isset($site_colors['warning_color']) ? $site_colors['warning_color'] : '#FFBB33'; ?>;
            --danger-color: <?php echo isset($site_colors['danger_color']) ? $site_colors['danger_color'] : '#FF3547'; ?>;
        }

        .main-nav {
            z-index: 1000;
            position: sticky;
            top: 0;
            background-color: var(--background-color);
        }

        .nav-container {
            padding: 0.5rem 0;
        }

        .nav-link {
            font-weight: 500;
            color: var(--text-color);
            transition: color 0.3s ease;
            white-space: nowrap;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-color);
            font-weight: 700;
        }

        /* Mobile: Căn trái khi collapse mở */
        @media (max-width: 991px) {
            .navbar-nav {
                align-items: start !important;
            }
            .nav-item {
                margin: 0.25rem 0;
            }
        }

        /* Desktop: Đảm bảo menu hiển thị */
        @media (min-width: 992px) {
            .navbar-nav {
                display: flex !important;
                justify-content: start !important;
            }
        }
    </style>
</head>
<body>
    <!-- Top Bar - Simple with high contrast -->
    <div class="top-bar py-2" style="background-color: var(--dark-bg-color); color: white;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 d-none d-md-block">
                    <div class="d-flex align-items-center">
                        <span class="me-3 small"><i class="fas fa-bolt me-1"></i> MIỄN PHÍ VẬN CHUYỂN CHO ĐƠN HÀNG TRÊN 500.000₫</span>
                        <span class="small"><i class="far fa-clock me-1"></i> SHIP TOÀN QUỐC 1-3 NGÀY</span>
                    </div>
                </div>
                <div class="col-md-6 text-center text-md-end small">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="index.php?controller=user&action=profile" class="text-light me-3">
                            <i class="fas fa-user me-1"></i> <?php echo $_SESSION['username']; ?>
                        </a>
                        <a href="index.php?controller=user&action=orders" class="text-light me-3">
                            <i class="fas fa-box me-1"></i> ĐƠN HÀNG
                        </a>
                        <a href="index.php?controller=user&action=logout" class="text-light">
                            <i class="fas fa-sign-out-alt me-1"></i> ĐĂNG XUẤT
                        </a>
                    <?php else: ?>
                        <a href="index.php?controller=user&action=login" class="text-light me-3">
                            <i class="fas fa-sign-in-alt me-1"></i> ĐĂNG NHẬP
                        </a>
                        <a href="index.php?controller=user&action=register" class="text-light">
                            <i class="fas fa-user-plus me-1"></i> ĐĂNG KÝ
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header - Bold & Modern -->
    <header class="main-header py-3 bg-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3 col-6 mb-2 mb-md-0">
                    <a href="index.php" class="text-decoration-none">
                        <h1 class="site-logo m-0">
                            <span class="text-primary fw-black logo-text">BA</span><span class="text-secondary fw-light logo-text">BEE</span>
                        </h1>
                    </a>
                </div>
                <div class="col-md-5 col-12 order-3 order-md-2 mt-3 mt-md-0">
                    <form action="index.php" method="GET" class="search-form">
                        <input type="hidden" name="controller" value="product">
                        <input type="hidden" name="action" value="list">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control rounded-pill-start search-input" 
                                   placeholder="Tìm kiếm sản phẩm..." 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button class="btn btn-primary rounded-pill-end" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 col-6 text-end order-2 order-md-3">
                    <div class="d-flex justify-content-end">
                        <a href="index.php?controller=user&action=wishlist" class="btn btn-link text-dark me-2 position-relative">
                            <i class="fas fa-heart fs-5"></i>
                        </a>
                        <?php
                        // Initialize cart
                        $cart = new Cart();
                        $cart_count = $cart->getTotalItems();
                        ?>
                        <a href="index.php?controller=cart&action=index" class="btn btn-link text-dark position-relative">
                            <i class="fas fa-shopping-cart fs-5"></i>
                            <?php if($cart_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                <?php echo $cart_count; ?>
                            </span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation - Bold & Colorful -->
    <nav class="main-nav py-0 sticky-top">
        <div class="container">
            <div class="nav-container bg-white py-2 px-3 rounded-bottom shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="fas fa-bars"></i> MENU
                    </button>
                    <div class="d-none d-lg-block">
                        <ul class="navbar-nav nav-pills justify-content-start">
                            <li class="nav-item ms-2">
                                <a class="nav-link py-2 <?php echo (!isset($_GET['controller']) || $_GET['controller'] == 'home') ? 'active fw-bold' : ''; ?>" 
                                   href="index.php">TRANG CHỦ</a>
                            </li>
                            <?php
                            try {
                                $category = new Category($conn);
                                $categoryStmt = $category->read();
                                if ($categoryStmt === false) {
                                    error_log("Lỗi: Không thể thực thi truy vấn danh mục");
                                    echo '<li class="nav-item ms-2"><a class="nav-link py-2" href="#">Lỗi: Không tải được danh mục</a></li>';
                                } else {
                                    if ($categoryStmt->rowCount() == 0) {
                                        error_log("Cảnh báo: Bảng danh mục trống");
                                        echo '<li class="nav-item ms-2"><a class="nav-link py-2" href="#">Không có danh mục</a></li>';
                                    } else {
                                        while($row = $categoryStmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <li class="nav-item ms-2">
                                <a class="nav-link py-2 <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $row['id']) ? 'active fw-bold' : ''; ?>" 
                                   href="index.php?controller=product&action=list&category_id=<?php echo $row['id']; ?>">
                                    <?php echo strtoupper(htmlspecialchars($row['name'])); ?>
                                </a>
                            </li>
                            <?php 
                                        endwhile;
                                    }
                                }
                            } catch (Exception $e) {
                                error_log("Lỗi danh mục: " . $e->getMessage());
                                echo '<li class="nav-item ms-2"><a class="nav-link py-2" href="#">Lỗi: ' . htmlspecialchars($e->getMessage()) . '</a></li>';
                            }
                            ?>
                            <li class="nav-item ms-2">
                                <a class="nav-link py-2 <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'product' && isset($_GET['is_sale'])) ? 'active fw-bold' : ''; ?> sale-link" 
                                   href="index.php?controller=product&action=list&is_sale=1">
                                    <span class="sale-text">SALE</span>
                                </a>
                            </li>
                            <li class="nav-item ms-2">
                                <a class="nav-link py-2" href="index.php?controller=order&action=track">
                                    <i class="fas fa-truck me-1"></i> THEO DÕI ĐƠN HÀNG
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="collapse navbar-collapse d-lg-none" id="mainNavigation">
                        <ul class="navbar-nav nav-pills justify-content-start">
                            <li class="nav-item ms-2">
                                <a class="nav-link py-2 <?php echo (!isset($_GET['controller']) || $_GET['controller'] == 'home') ? 'active fw-bold' : ''; ?>" 
                                   href="index.php">TRANG CHỦ</a>
                            </li>
                            <?php
                            try {
                                $category = new Category($conn);
                                $categoryStmt = $category->read();
                                if ($categoryStmt === false) {
                                    error_log("Lỗi: Không thể thực thi truy vấn danh mục");
                                    echo '<li class="nav-item ms-2"><a class="nav-link py-2" href="#">Lỗi: Không tải được danh mục</a></li>';
                                } else {
                                    if ($categoryStmt->rowCount() == 0) {
                                        error_log("Cảnh báo: Bảng danh mục trống");
                                        echo '<li class="nav-item ms-2"><a class="nav-link py-2" href="#">Không có danh mục</a></li>';
                                    } else {
                                        while($row = $categoryStmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <li class="nav-item ms-2">
                                <a class="nav-link py-2 <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $row['id']) ? 'active fw-bold' : ''; ?>" 
                                   href="index.php?controller=product&action=list&category_id=<?php echo $row['id']; ?>">
                                    <?php echo strtoupper(htmlspecialchars($row['name'])); ?>
                                </a>
                            </li>
                            <?php 
                                        endwhile;
                                    }
                                }
                            } catch (Exception $e) {
                                error_log("Lỗi danh mục: " . $e->getMessage());
                                echo '<li class="nav-item ms-2"><a class="nav-link py-2" href="#">Lỗi: ' . htmlspecialchars($e->getMessage()) . '</a></li>';
                            }
                            ?>
                            <li class="nav-item ms-2">
                                <a class="nav-link py-2 <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'product' && isset($_GET['is_sale'])) ? 'active fw-bold' : ''; ?> sale-link" 
                                   href="index.php?controller=product&action=list&is_sale=1">
                                    <span class="sale-text">SALE</span>
                                </a>
                            </li>
                            <li class="nav-item ms-2">
                                <a class="nav-link py-2" href="index.php?controller=order&action=track">
                                    <i class="fas fa-truck me-1"></i> THEO DÕI ĐƠN HÀNG
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content py-4">
        <div class="container">
            <!-- Nội dung chính sẽ được thêm vào đây -->
        </div>
    </main>

    <!-- Bootstrap 5 JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>