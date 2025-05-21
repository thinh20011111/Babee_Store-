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
    
    <!-- Google Fonts - Quicksand for entire site -->
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <link rel="icon" href="https://i.pinimg.com/736x/54/61/79/546179c1886e7675a0c887e381f0e176.jpg" type="image/jpg">
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

        /* General Font Styling - Quicksand for all elements */
        body, h1, h2, h3, h4, h5, h6, p, a, button, input {
            font-family: 'Quicksand', sans-serif !important;
        }

        /* Navigation Categories */
        .main-nav .nav-link {
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            color: var(--text-color, #121212);
            transition: color 0.3s ease;
        }

        .main-nav .nav-link:hover,
        .main-nav .nav-link.active {
            color: var(--primary-color, #FF2D55);
        }

        /* Tùy chỉnh header */
        .main-header {
            padding: 10px 0;
        }

        .site-logo {
            font-size: 1.5rem;
            line-height: 1;
        }

        .logo-text {
            font-weight: 700;
        }

        .search-form .search-input {
            border: 1px solid var(--light-bg-color);
            padding: 8px 15px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .search-form .btn {
            padding: 8px 15px;
        }

        /* Tùy chỉnh top bar */
        .top-bar .small {
            font-size: 0.75rem;
            font-weight: 500;
        }

        .top-bar .admin-link {
            color: var(--accent-color);
            transition: color 0.3s ease;
        }

        .top-bar .admin-link:hover {
            color: #FFD700;
            text-decoration: none;
        }

        /* Tùy chỉnh cho mobile */
        @media (max-width: 767.98px) {
            .site-logo {
                font-size: 1.2rem;
            }

            .main-header .col-8 {
                display: flex;
                align-items: center;
            }

            .main-header .col-4 {
                display: flex;
                align-items: center;
                justify-content: flex-end;
            }

            .search-form {
                margin-top: 10px;
            }

            .search-form .search-input {
                font-size: 0.85rem;
            }

            .btn-link i {
                font-size: 1.2rem;
            }

            .badge {
                font-size: 0.65rem;
                padding: 3px 6px;
            }

            .top-bar .small {
                font-size: 0.7rem;
            }

            .top-bar .admin-link {
                margin-left: 1rem;
            }

            .main-nav .nav-link {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <?php
    // Đảm bảo session được khởi tạo
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Khởi tạo kết nối cơ sở dữ liệu nếu chưa có
    if (!isset($conn)) {
        require_once 'config/database.php';
        $db = new Database();
        $conn = $db->getConnection();
        if (!$conn) {
            echo "<div class='alert alert-danger'>Lỗi: Không thể kết nối cơ sở dữ liệu. Vui lòng kiểm tra cấu hình.</div>";
        }
    }
    ?>

    <!-- Top Bar - Simple with high contrast -->
    <div class="top-bar py-2" style="background-color: var(--dark-bg-color); color: white;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 d-none d-md-block">
                    <div class="d-flex align-items-center">
                        <span class="me-3 small"><i class="fas fa-bolt me-1"></i> Miễn phí vận chuyển cho đơn hàng trên 500.000₫</span>
                        <span class="small"><i class="far fa-clock me-1"></i> Vận chuyển toàn quốc 1-3 ngày</span>
                    </div>
                </div>
                <div class="col-md-6 text-center text-md-end small">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="index.php?controller=user&action=profile" class="text-light me-3">
                            <i class="fas fa-user me-1"></i> <?php echo $_SESSION['username']; ?>
                        </a>
                        <a href="index.php?controller=user&action=orders" class="text-light me-3">
                            <i class="fas fa-box me-1"></i> Đơn hàng
                        </a>
                        <a href="index.php?controller=user&action=logout" class="text-light me-3">
                            <i class="fas fa-sign-out-alt me-1"></i> Đăng xuất
                        </a>
                        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                            <a href="/admin/index.php?page=dashboard" class="admin-link">
                                <i class="fas fa-cog me-1"></i> Admin
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="index.php?controller=user&action=login" class="text-light me-3">
                            <i class="fas fa-sign-in-alt me-1"></i> Đăng nhập
                        </a>
                        <a href="index.php?controller=user&action=register" class="text-light me-3">
                            <i class="fas fa-user-plus me-1"></i> Đăng ký
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header - Bold & Modern -->
    <header class="main-header bg-white">
        <div class="container">
            <div class="row align-items-center">
                <!-- Logo -->
                <div class="col-8 col-md-3">
                    <a href="index.php" class="text-decoration-none">
                        <h1 class="site-logo m-0">
                            <span class="text-primary fw-black logo-text">Ba</span><span class="text-secondary fw-light logo-text">Bee</span>
                        </h1>
                    </a>
                </div>

                <!-- Wishlist và Cart -->
                <div class="col-4 col-md-3 order-md-3 text-end">
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

                <!-- Thanh tìm kiếm -->
                <div class="col-md-6 order-md-2">
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
            </div>
        </div>
    </header>

    <!-- Navigation - Bold & Colorful -->
    <nav class="main-nav navbar navbar-expand-lg navbar-light sticky-top shadow-sm" style="background-color: var(--background-color);">
        <div class="container">
            <a class="navbar-brand d-lg-none fw-bold text-primary" href="index.php">MENU</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavigation">
                <ul class="navbar-nav mx-auto gap-2">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (!isset($_GET['controller']) || $_GET['controller'] == 'home') ? 'active fw-bold text-primary' : ''; ?>" href="index.php">HOME</a>
                    </li>

                    <?php
                    if (!$conn) {
                        echo "<li class='nav-item text-danger'>Lỗi: Kết nối cơ sở dữ liệu thất bại.</li>";
                    } else {
                        $category = new Category($conn);
                        $categoryStmt = $category->read();

                        if ($categoryStmt === false) {
                            echo "<li class='nav-item text-danger'>Lỗi: Không thể lấy danh sách danh mục.</li>";
                        } else {
                            $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

                            if ($categoryStmt->rowCount() > 0) {
                                while ($row = $categoryStmt->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($category_id == $row['id']) ? 'active fw-bold text-primary' : ''; ?>" 
                           href="index.php?controller=product&action=list&category_id=<?php echo $row['id']; ?>">
                            <?php echo strtoupper(htmlspecialchars($row['name'])); ?>
                        </a>
                    </li>
                    <?php
                                endwhile;
                            } else {
                                echo "<li class='nav-item'>Không có danh mục nào.</li>";
                            }
                        }
                    }
                    ?>

                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'product' && isset($_GET['is_sale'])) ? 'active fw-bold text-danger' : ''; ?>" 
                           href="index.php?controller=product&action=list&is_sale=1">
                            SALE
                        </a>
                    </li>

                    <li class="nav-item d-none d-lg-block">
                        <a class="nav-link" href="index.php?controller=order&action=track">
                            <i class="fas fa-truck me-1"></i> THEO DÕI ĐƠN HÀNG
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content py-4">
        <div class="container">