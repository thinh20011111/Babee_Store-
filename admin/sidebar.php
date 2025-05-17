<?php
// Prevent direct access to sidebar.php
if (!defined('ADMIN_INCLUDED')) {
    header("Location: ../index.php");
    exit;
}

// Define accessible pages for roles
$accessible_pages = [
    'admin' => ['dashboard', 'orders', 'products', 'users', 'traffic', 'banners', 'settings', 'promotions'],
    'staff' => ['dashboard', 'orders', 'products', 'traffic']
];

// Get current user role
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'staff';

// Get current page for active state
$current_page = isset($_GET['page']) ? htmlspecialchars($_GET['page']) : 'dashboard';
?>

<div class="bg-dark sidebar p-3 text-white" style="width: 250px; min-height: 100vh; position: sticky; top: 0;">
    <h4 class="text-center mb-4">Admin Panel</h4>
    <ul class="nav flex-column">
        <?php if (in_array('dashboard', $accessible_pages[$user_role])): ?>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo ($current_page === 'dashboard') ? 'active bg-primary rounded' : ''; ?>" href="index.php?page=dashboard">
                <i class="fas fa-home me-2"></i> Trang chủ
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (in_array('orders', $accessible_pages[$user_role])): ?>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo ($current_page === 'orders') ? 'active bg-primary rounded' : ''; ?>" href="index.php?page=orders">
                <i class="fas fa-shopping-cart me-2"></i> Đơn hàng
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (in_array('products', $accessible_pages[$user_role])): ?>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo ($current_page === 'products') ? 'active bg-primary rounded' : ''; ?>" href="index.php?page=products">
                <i class="fas fa-box me-2"></i> Sản phẩm
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (in_array('users', $accessible_pages[$user_role])): ?>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo ($current_page === 'users') ? 'active bg-primary rounded' : ''; ?>" href="index.php?page=users">
                <i class="fas fa-users me-2"></i> Người dùng
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (in_array('traffic', $accessible_pages[$user_role])): ?>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo ($current_page === 'traffic') ? 'active bg-primary rounded' : ''; ?>" href="index.php?page=traffic">
                <i class="fas fa-chart-line me-2"></i> Lượt truy cập
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (in_array('banners', $accessible_pages[$user_role])): ?>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo ($current_page === 'banners') ? 'active bg-primary rounded' : ''; ?>" href="index.php?page=banners">
                <i class="fas fa-images me-2"></i> Giao diện
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (in_array('settings', $accessible_pages[$user_role])): ?>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo ($current_page === 'settings') ? 'active bg-primary rounded' : ''; ?>" href="index.php?page=settings">
                <i class="fas fa-cog me-2"></i> Cài đặt
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (in_array('promotions', $accessible_pages[$user_role])): ?>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo ($current_page === 'promotions') ? 'active bg-primary rounded' : ''; ?>" href="index.php?page=promotions">
                <i class="fas fa-tags me-2"></i> Khuyến mãi
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>

<style>
    .sidebar .nav-link {
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    .sidebar .nav-link:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
    }
    .sidebar .nav-link.active {
        background: #007bff;
        color: #fff !important;
        font-weight: 600;
    }
    .sidebar .nav-link i {
        width: 24px;
        text-align: center;
    }
</style>