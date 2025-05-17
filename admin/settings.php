<?php
// Settings management page
// Check direct script access
if (!defined('ADMIN_INCLUDED')) {
    define('ADMIN_INCLUDED', true);
}

// Include database connection
require_once '../config/database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();
    // Set UTF-8 encoding
    $conn->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    error_log("Database connection or charset error: " . $e->getMessage());
    die("Internal Server Error - Check logs for details.");
}

// Restrict access to admin only
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    echo "<div class='alert alert-danger'>You don't have permission to access this page.</div>";
    exit;
}

// Load required models
require_once '../models/Settings.php';

// Initialize objects
try {
    $settings = new Settings($conn);
} catch (Exception $e) {
    error_log("Model initialization error: " . $e->getMessage());
    die("Internal Server Error - Check logs for details.");
}

// Process actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = '';
$error_message = '';

// Delete setting
if ($action == 'delete' && isset($_GET['id'])) {
    $settings->id = $_GET['id'];
    try {
        if ($settings->delete()) {
            $success_message = "Setting deleted successfully.";
        } else {
            $error_message = "Failed to delete setting.";
        }
    } catch (Exception $e) {
        $error_message = "Error deleting setting: " . $e->getMessage();
        error_log("Delete setting error: " . $e->getMessage());
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
    try {
        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                $value = trim($_POST[$key]);
                if ($key === 'contact_email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Invalid email format for contact email.");
                }
                if (in_array($key, ['site_name', 'contact_email']) && empty($value)) {
                    throw new Exception("Site name and contact email are required.");
                }
                if ($settings->set($key, $value)) {
                    $updated++;
                } else {
                    throw new Exception("Failed to update setting: $key");
                }
            }
        }

        if ($updated === count($keys)) {
            $success_message = "All settings updated successfully.";
        } elseif ($updated > 0) {
            $success_message = "$updated settings updated successfully.";
        } else {
            $error_message = "No settings were updated.";
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        error_log("Update settings error: " . $e->getMessage());
    }
}

// Get all settings
$all_settings = [];
try {
    $stmt = $settings->read();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $all_settings[] = $row;
    }
} catch (Exception $e) {
    $error_message = "Error loading settings: " . $e->getMessage();
    error_log("Fetch settings error: " . $e->getMessage());
}

// Load current settings for form
try {
    $current_settings = $settings->loadSettings();
} catch (Exception $e) {
    $error_message = "Error loading current settings: " . $e->getMessage();
    error_log("Load settings error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings Management - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgo=">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-grow-1 p-4 bg-light">
            <div class="container-fluid px-4">
                <h1 class="mt-4 mb-3 fw-bold">Settings Management</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>

                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Settings Form -->
                    <div class="col-lg-5">
                        <div class="card mb-4 shadow-sm border-0">
                            <div class="card-header bg-primary text-white d-flex align-items-center">
                                <i class="fas fa-cog me-2"></i>
                                <h5 class="mb-0">Update Settings</h5>
                            </div>
                            <div class="card-body">
                                <form action="index.php?page=settings" method="POST">
                                    <h5 class="mb-3 fw-semibold text-primary">Site Information</h5>
                                    <div class="mb-3 position-relative">
                                        <label for="site_name" class="form-label fw-medium">Site Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                            <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($current_settings['site_name'] ?? 'StreetStyle'); ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 position-relative">
                                        <label for="site_description" class="form-label fw-medium">Site Description</label>
                                        <textarea class="form-control" id="site_description" name="site_description" rows="4"><?php echo htmlspecialchars($current_settings['site_description'] ?? 'Thời trang đường phố dành cho giới trẻ - Bold & Colorful'); ?></textarea>
                                    </div>
                                    <div class="mb-3 position-relative">
                                        <label for="contact_email" class="form-label fw-medium">Contact Email <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($current_settings['contact_email'] ?? 'contact@streetstyle.com'); ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 position-relative">
                                        <label for="contact_phone" class="form-label fw-medium">Contact Phone</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($current_settings['contact_phone'] ?? '+84 123 456 789'); ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3 position-relative">
                                        <label for="contact_address" class="form-label fw-medium">Contact Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                            <input type="text" class="form-control" id="contact_address" name="contact_address" value="<?php echo htmlspecialchars($current_settings['contact_address'] ?? 'Hanoi, Vietnam'); ?>">
                                        </div>
                                    </div>

                                    <h5 class="mb-3 fw-semibold text-primary">Site Colors</h5>
                                    <div class="mb-3 position-relative">
                                        <label for="primary_color" class="form-label fw-medium">Primary Color</label>
                                        <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color" value="<?php echo htmlspecialchars($current_settings['primary_color'] ?? '#FF2D55'); ?>" title="Primary color for buttons and highlights">
                                    </div>
                                    <div class="mb-3 position-relative">
                                        <label for="secondary_color" class="form-label fw-medium">Secondary Color</label>
                                        <input type="color" class="form-control form-control-color" id="secondary_color" name="secondary_color" value="<?php echo htmlspecialchars($current_settings['secondary_color'] ?? '#4A00E0'); ?>" title="Secondary color for backgrounds">
                                    </div>
                                    <div class="mb-3 position-relative">
                                        <label for="accent_color" class="form-label fw-medium">Accent Color</label>
                                        <input type="color" class="form-control form-control-color" id="accent_color" name="accent_color" value="<?php echo htmlspecialchars($current_settings['accent_color'] ?? '#FFCC00'); ?>" title="Accent color for hover effects">
                                    </div>
                                    <div class="mb-3 position-relative">
                                        <label for="text_color" class="form-label fw-medium">Text Color</label>
                                        <input type="color" class="form-control form-control-color" id="text_color" name="text_color" value="<?php echo htmlspecialchars($current_settings['text_color'] ?? '#121212'); ?>" title="Primary text color">
                                    </div>
                                    <div class="mb-3 position-relative">
                                        <label for="background_color" class="form-label fw-medium">Background Color</label>
                                        <input type="color" class="form-control form-control-color" id="background_color" name="background_color" value="<?php echo htmlspecialchars($current_settings['background_color'] ?? '#FFFFFF'); ?>" title="Main background color">
                                    </div>
                                    <div class="mb-3 position-relative">
                                        <label for="dark_bg_color" class="form-label fw-medium">Dark Background Color</label>
                                        <input type="color" class="form-control form-control-color" id="dark_bg_color" name="dark_bg_color" value="<?php echo htmlspecialchars($current_settings['dark_bg_color'] ?? '#1A1A1A'); ?>" title="Dark background for headers">
                                    </div>
                                    <div class="mb-3 position-relative">
                                        <label for="light_bg_color" class="form-label fw-medium">Light Background Color</label>
                                        <input type="color" class="form-control form-control-color" id="light_bg_color" name="light_bg_color" value="<?php echo htmlspecialchars($current_settings['light_bg_color'] ?? '#F7F7F7'); ?>" title="Light background for cards">
                                    </div>
                                    <div class="mb-3 position-relative">
                                        <label for="footer_color" class="form-label fw-medium">Footer Color</label>
                                        <input type="color" class="form-control form-control-color" id="footer_color" name="footer_color" value="<?php echo htmlspecialchars($current_settings['footer_color'] ?? '#0D0D0D'); ?>" title="Footer background color">
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg">Update Settings</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Settings List -->
                    <div class="col-lg-7">
                        <div class="card mb-4 shadow-sm border-0">
                            <div class="card-header bg-primary text-white d-flex align-items-center">
                                <i class="fas fa-list me-2"></i>
                                <h5 class="mb-0">Current Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="table-dark sticky-top">
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
                                                    <div class="color-swatch" style="background-color: <?php echo htmlspecialchars($setting['setting_value']); ?>;" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($setting['setting_value']); ?>"></div>
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
        /* Modernized General Styling */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7eb 100%);
            color: #333;
        }

        .container-fluid {
            padding: 30px;
            max-width: 1400px;
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a1a;
        }

        .breadcrumb {
            background: transparent;
            padding: 0;
            font-size: 0.9rem;
        }

        .breadcrumb-item a {
            color: #007bff;
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: #495057;
        }

        /* Card Styling */
        .card {
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            border-radius: 12px 12px 0 0;
            padding: 15px 20px;
            background: linear-gradient(90deg, #007bff, #0056b3);
        }

        .card-header h5 {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .card-body {
            padding: 25px;
        }

        /* Form Styling */
        .form-label {
            font-weight: 500;
            font-size: 0.95rem;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .input-group-text {
            background: #f1f3f5;
            border: 1px solid #ced4da;
            color: #495057;
        }

        .form-control, .form-control-color {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 10px;
            font-size: 0.95rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus, .form-control-color:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.2);
            outline: none;
        }

        .form-control-color {
            width: 100%;
            height: 48px;
            padding: 8px;
            border-radius: 8px;
            cursor: pointer;
        }

        .form-control-color::-webkit-color-swatch {
            border-radius: 6px;
            border: none;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .d-grid .btn {
            padding: 12px;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 8px;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .btn-primary {
            background: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        /* Table Styling */
        .table {
            font-size: 0.95rem;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            background: #343a40;
            color: #fff;
            font-weight: 600;
            padding: 12px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table tbody tr:nth-child(odd) {
            background: #f8f9fa;
        }

        .table tbody tr:hover {
            background: #e9ecef;
        }

        .table td {
            vertical-align: middle;
            padding: 12px;
            border-color: #e9ecef;
        }

        .color-swatch {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: inline-block;
            vertical-align: middle;
            border: 1px solid #dee2e6;
            transition: transform 0.2s ease;
        }

        .color-swatch:hover {
            transform: scale(1.1);
        }

        /* Action Buttons */
        .btn-sm {
            padding: 8px 14px;
            font-size: 0.9rem;
            border-radius: 6px;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .btn-danger {
            background: #dc3545;
            border: none;
        }

        .btn-danger:hover {
            background: #b02a37;
            transform: translate414px 6px;
        }

        /* Alerts */
        .alert {
            border-radius: 8px;
            padding: 15px;
            font-size: 0.95rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        /* Sidebar Styling */
        .sidebar {
            min-height: 100vh;
            position: sticky;
            top: 0;
            background: #1a1a1a;
        }

        .sidebar .nav-link {
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 0.95rem;
            color: #e9ecef;
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
                margin-bottom: 30px;
            }

            .table-responsive {
                border: none;
            }

            .table td, .table th {
                font-size: 0.9rem;
            }

            .btn-sm {
                padding: 6px 12px;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 576px) {
            .container-fluid {
                padding: 20px;
            }

            h1 {
                font-size: 1.75rem;
            }

            .card-header h5 {
                font-size: 1.1rem;
            }

            .card-body {
                padding: 20px;
            }

            .form-label {
                font-size: 0.9rem;
            }

            .form-control, .form-control-color {
                font-size: 0.9rem;
                padding: 8px;
            }

            .d-grid .btn {
                font-size: 0.9rem;
                padding: 10px;
            }
        }
    </style>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Initialize Bootstrap tooltips for color swatches
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>