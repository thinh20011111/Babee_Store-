<?php
// Settings management page
// Check direct script access
if (!defined('ADMIN_INCLUDED')) {
    define('ADMIN_INCLUDED', true);
}

// Restrict access to admin only
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    echo "<div class='alert alert-danger'>You don't have permission to access this page.</div>";
    exit;
}

// Load required models
require_once '../models/Settings.php';

// Initialize objects
$settings = new Settings($conn);

// Process actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = '';
$error_message = '';

// Delete setting
if ($action == 'delete' && isset($_GET['id'])) {
    $settings->id = $_GET['id'];
    if ($settings->delete()) {
        $success_message = "Setting deleted successfully.";
    } else {
        $error_message = "Failed to delete setting.";
    }
}

// Process update form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $keys = [
        'site_name', 'site_description', 'contact_email', 'contact_phone', 'contact_address',
        'primary_color', 'secondary_color', 'accent_color', 'text_color', 'background_color',
        'dark_bg_color', 'light_bg_color', 'footer_color'
    ];

    $updated = 0;
    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            if ($settings->set($key, $_POST[$key])) {
                $updated++;
            } else {
                $error_message = "Failed to update some settings.";
                break;
            }
        }
    }

    if ($updated === count($keys)) {
        $success_message = "All settings updated successfully.";
    } elseif ($updated > 0) {
        $success_message = "$updated settings updated successfully.";
    }
}

// Get all settings
$all_settings = [];
$stmt = $settings->read();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $all_settings[] = $row;
}

// Load current settings for form
$current_settings = $settings->loadSettings();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings Management - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgo=">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="bg-dark sidebar p-3 text-white" style="width: 250px;">
            <h4 class="text-center mb-4">Admin Panel</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=dashboard"><i class="fas fa-home me-2"></i> Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=orders"><i class="fas fa-shopping-cart me-2"></i> Đơn hàng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=products"><i class="fas fa-box me-2"></i> Sản phẩm</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=users"><i class="fas fa-users me-2"></i> Người dùng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=traffic"><i class="fas fa-chart-line me-2"></i> Lượt truy cập</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=banners"><i class="fas fa-images me-2"></i> Giao diện</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white active" href="index.php?page=settings"><i class="fas fa-cog me-2"></i> Cài đặt</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=promotions"><i class="fas fa-tags me-2"></i> Khuyến mãi</a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 p-4">
            <div class="container-fluid px-4">
                <h1 class="mt-4">Settings Management</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>

                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Settings Form -->
                    <div class="col-lg-5">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-cog me-1"></i>
                                Update Settings
                            </div>
                            <div class="card-body">
                                <form action="index.php?page=settings" method="POST">
                                    <h5 class="mb-3">Site Information</h5>
                                    <div class="mb-3">
                                        <label for="site_name" class="form-label">Site Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($current_settings['site_name'] ?? 'StreetStyle'); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="site_description" class="form-label">Site Description</label>
                                        <textarea class="form-control" id="site_description" name="site_description" rows="4"><?php echo htmlspecialchars($current_settings['site_description'] ?? 'Thời trang đường phố dành cho giới trẻ - Bold & Colorful'); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="contact_email" class="form-label">Contact Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($current_settings['contact_email'] ?? 'contact@streetstyle.com'); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="contact_phone" class="form-label">Contact Phone</label>
                                        <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($current_settings['contact_phone'] ?? '+84 123 456 789'); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="contact_address" class="form-label">Contact Address</label>
                                        <input type="text" class="form-control" id="contact_address" name="contact_address" value="<?php echo htmlspecialchars($current_settings['contact_address'] ?? 'Hanoi, Vietnam'); ?>">
                                    </div>

                                    <h5 class="mb-3">Site Colors</h5>
                                    <div class="mb-3">
                                        <label for="primary_color" class="form-label">Primary Color</label>
                                        <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color" value="<?php echo htmlspecialchars($current_settings['primary_color'] ?? '#FF2D55'); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="secondary_color" class="form-label">Secondary Color</label>
                                        <input type="color" class="form-control form-control-color" id="secondary_color" name="secondary_color" value="<?php echo htmlspecialchars($current_settings['secondary_color'] ?? '#4A00E0'); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="accent_color" class="form-label">Accent Color</label>
                                        <input type="color" class="form-control form-control-color" id="accent_color" name="accent_color" value="<?php echo htmlspecialchars($current_settings['accent_color'] ?? '#FFCC00'); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="text_color" class="form-label">Text Color</label>
                                        <input type="color" class="form-control form-control-color" id="text_color" name="text_color" value="<?php echo htmlspecialchars($current_settings['text_color'] ?? '#121212'); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="background_color" class="form-label">Background Color</label>
                                        <input type="color" class="form-control form-control-color" id="background_color" name="background_color" value="<?php echo htmlspecialchars($current_settings['background_color'] ?? '#FFFFFF'); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="dark_bg_color" class="form-label">Dark Background Color</label>
                                        <input type="color" class="form-control form-control-color" id="dark_bg_color" name="dark_bg_color" value="<?php echo htmlspecialchars($current_settings['dark_bg_color'] ?? '#1A1A1A'); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="light_bg_color" class="form-label">Light Background Color</label>
                                        <input type="color" class="form-control form-control-color" id="light_bg_color" name="light_bg_color" value="<?php echo htmlspecialchars($current_settings['light_bg_color'] ?? '#F7F7F7'); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="footer_color" class="form-label">Footer Color</label>
                                        <input type="color" class="form-control form-control-color" id="footer_color" name="footer_color" value="<?php echo htmlspecialchars($current_settings['footer_color'] ?? '#0D0D0D'); ?>">
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">Update Settings</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Settings List -->
                    <div class="col-lg-7">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-list me-1"></i>
                                Current Settings
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Key</th>
                                                <th>Value</th>
                                                <th>Created At</th>
                                                <th>Updated At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($all_settings)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No settings found</td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach ($all_settings as $setting): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($setting['setting_key']); ?></td>
                                                <td>
                                                    <?php if (strpos($setting['setting_key'], '_color') !== false): ?>
                                                    <div style="width: 30px; height: 30px; background-color: <?php echo htmlspecialchars($setting['setting_value']); ?>; border: 1px solid #dee2e6; display: inline-block; vertical-align: middle;"></div>
                                                    <span class="ms-2"><?php echo htmlspecialchars($setting['setting_value']); ?></span>
                                                    <?php else: ?>
                                                    <?php echo htmlspecialchars($setting['setting_value']); ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($setting['created_at']); ?></td>
                                                <td><?php echo htmlspecialchars($setting['updated_at']); ?></td>
                                                <td>
                                                    <a href="index.php?page=settings&action=delete&id=<?php echo $setting['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this setting?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* General Improvements */
        .container-fluid {
            padding: 20px;
        }

        .card {
            border-radius: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            font-size: 1.1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            padding: 15px;
        }

        .card-body {
            padding: 20px;
        }

        /* Form Styling */
        .form-label {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .form-control, .form-control-color, .form-check-input {
            border-radius: 5px;
        }

        .form-control:focus, .form-control-color:focus {
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
            border-color: #007bff;
        }

        .form-text {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .form-control-color {
            width: 100%;
            height: 40px;
            padding: 5px;
        }

        .d-grid .btn {
            padding: 10px;
            font-size: 1rem;
            border-radius: 5px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        /* Table Styling */
        .table {
            margin-bottom: 0;
            font-size: 0.95rem;
        }

        .table thead th {
            background-color: #343a40;
            color: white;
            font-weight: 500;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table td {
            vertical-align: middle;
            padding: 12px;
        }

        /* Badges */
        .badge.bg-success {
            background-color: #28a745 !important;
            padding: 5px 10px;
            font-size: 0.9rem;
        }

        .badge.bg-danger {
            background-color: #dc3545 !important;
            padding: 5px 10px;
            font-size: 0.9rem;
        }

        /* Action Buttons */
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.9rem;
            border-radius: 4px;
        }

        .btn-sm i {
            font-size: 0.9rem;
        }

        .btn-sm.me-1 {
            margin-right: 8px;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .row {
                flex-direction: column;
            }

            .col-lg-5, .col-lg-7 {
                width: 100%;
                max-width: 100%;
            }

            .col-lg-5 {
                margin-bottom: 20px;
            }

            .table-responsive {
                border: none;
            }

            .table td, .table th {
                font-size: 0.9rem;
            }

            .btn-sm {
                padding: 5px 10px;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 576px) {
            .container-fluid {
                padding: 15px;
            }

            h1.mt-4 {
                font-size: 1.5rem;
            }

            .card-header {
                font-size: 1rem;
                padding: 12px;
            }

            .card-body {
                padding: 15px;
            }

            .form-label {
                font-size: 0.9rem;
            }

            .form-control, .form-control-color {
                font-size: 0.9rem;
            }

            .d-grid .btn {
                font-size: 0.9rem;
                padding: 8px;
            }
        }

        /* Sidebar Styling */
        .sidebar {
            min-height: 100vh;
            position: sticky;
            top: 0;
        }

        .sidebar .nav-link {
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link.active {
            background-color: #007bff;
            color: white !important;
            font-weight: 500;
        }

        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
        }
    </style>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>