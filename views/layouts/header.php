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
    
    <!-- Google Fonts - Cute, child-friendly fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        :root {
            --primary-color: <?php echo isset($site_colors['primary_color']) ? $site_colors['primary_color'] : '#ff6b6b'; ?>;
            --secondary-color: <?php echo isset($site_colors['secondary_color']) ? $site_colors['secondary_color'] : '#4ecdc4'; ?>;
            --text-color: <?php echo isset($site_colors['text_color']) ? $site_colors['text_color'] : '#333333'; ?>;
            --background-color: <?php echo isset($site_colors['background_color']) ? $site_colors['background_color'] : '#ffffff'; ?>;
            --footer-color: <?php echo isset($site_colors['footer_color']) ? $site_colors['footer_color'] : '#292b2c'; ?>;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar bg-light py-2">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">
                        <i class="fas fa-envelope me-2"></i> <?php echo defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'contact@babeestore.com'; ?>
                        <i class="fas fa-phone ms-3 me-2"></i> <?php echo '+84 123 456 789'; ?>
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <small>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="index.php?controller=user&action=profile" class="text-decoration-none me-3">
                                <i class="fas fa-user me-1"></i> <?php echo $_SESSION['username']; ?>
                            </a>
                            <a href="index.php?controller=user&action=orders" class="text-decoration-none me-3">
                                <i class="fas fa-box me-1"></i> Đơn hàng của tôi
                            </a>
                            <a href="index.php?controller=user&action=logout" class="text-decoration-none">
                                <i class="fas fa-sign-out-alt me-1"></i> Đăng xuất
                            </a>
                        <?php else: ?>
                            <a href="index.php?controller=user&action=login" class="text-decoration-none me-3">
                                <i class="fas fa-sign-in-alt me-1"></i> Đăng nhập
                            </a>
                            <a href="index.php?controller=user&action=register" class="text-decoration-none">
                                <i class="fas fa-user-plus me-1"></i> Đăng ký
                            </a>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3 mb-3 mb-md-0">
                    <a href="index.php" class="text-decoration-none">
                        <h1 class="logo m-0">
                            <span class="text-primary">Babee</span> <span class="text-secondary">Store</span>
                        </h1>
                    </a>
                </div>
                <div class="col-md-5 mb-3 mb-md-0">
                    <form action="index.php" method="GET" class="search-form">
                        <input type="hidden" name="controller" value="product">
                        <input type="hidden" name="action" value="list">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Tìm kiếm sản phẩm cho bé..." 
                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 text-end">
                    <?php
                    // Initialize cart
                    $cart = new Cart();
                    $cart_count = $cart->getTotalItems();
                    ?>
                    <a href="index.php?controller=cart&action=index" class="btn btn-outline-primary position-relative">
                        <i class="fas fa-shopping-cart me-1"></i> Giỏ hàng
                        <?php if($cart_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo $cart_count; ?>
                        </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (!isset($_GET['controller']) || $_GET['controller'] == 'home') ? 'active' : ''; ?>" 
                           href="index.php">Trang chủ</a>
                    </li>
                    
                    <?php
                        $category = new Category($conn);
                        $categoryStmt = $category->read(); // Thay $stmt thành $categoryStmt
                        if ($categoryStmt) {
                            while($row = $categoryStmt->fetch(PDO::FETCH_ASSOC)):
                        ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $row['id']) ? 'active' : ''; ?>" 
                            href="index.php?controller=product&action=list&category_id=<?php echo $row['id']; ?>">
                                <?php echo $row['name']; ?>
                            </a>
                        </li>
                    <?php endwhile; } ?>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'product' && isset($_GET['is_sale'])) ? 'active' : ''; ?>" 
                           href="index.php?controller=product&action=list&is_sale=1">Khuyến mãi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'home' && isset($_GET['action']) && $_GET['action'] == 'about') ? 'active' : ''; ?>" 
                           href="index.php?controller=home&action=about">Giới thiệu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'home' && isset($_GET['action']) && $_GET['action'] == 'contact') ? 'active' : ''; ?>" 
                           href="index.php?controller=home&action=contact">Liên hệ</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="index.php?controller=order&action=track" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-truck me-1"></i> Theo dõi đơn hàng
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content py-4">
        <div class="container">
