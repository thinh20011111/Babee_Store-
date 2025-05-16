<?php
// Promotions management page
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
require_once '../models/Promotion.php';

// Initialize objects
$promotion = new Promotion($conn);

// Process actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = '';
$error_message = '';

// Delete promotion
if ($action == 'delete' && isset($_GET['id'])) {
    $promotion->id = $_GET['id'];
    if ($promotion->delete()) {
        $success_message = "Promotion deleted successfully.";
    } else {
        $error_message = "Failed to delete promotion.";
    }
}

// Edit promotion
$edit_promotion = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $promotion->id = $_GET['id'];
    if ($promotion->readOne()) {
        $edit_promotion = $promotion;
    } else {
        $error_message = "Promotion not found.";
    }
}

// Process add/edit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $promotion->name = $_POST['name'];
        $promotion->code = strtoupper(trim($_POST['code']));
        $promotion->discount_type = $_POST['discount_type'];
        $promotion->discount_value = floatval($_POST['discount_value']);
        $promotion->start_date = $_POST['start_date'];
        $promotion->end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $promotion->is_active = isset($_POST['is_active']) ? 1 : 0;
        $promotion->min_purchase = floatval($_POST['min_purchase']);
        $promotion->usage_limit = intval($_POST['usage_limit']);

        // Validate inputs
        if (empty($promotion->name) || empty($promotion->code) || $promotion->discount_value <= 0) {
            throw new Exception("Please fill all required fields with valid values.");
        }

        if (isset($_POST['edit_id'])) {
            // Update existing promotion
            $promotion->id = $_POST['edit_id'];
            if ($promotion->update()) {
                $success_message = "Promotion updated successfully.";
                $promotion->readOne();
                $edit_promotion = $promotion;
            } else {
                $error_message = "Failed to update promotion.";
            }
        } else {
            // Create new promotion
            if ($promotion->create()) {
                $success_message = "Promotion created successfully.";
                $promotion = new Promotion($conn);
            } else {
                $error_message = "Failed to create promotion. Code may already exist.";
            }
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Get all promotions
$promotions = [];
$stmt = $promotion->read();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $promotions[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotion Management - Admin Dashboard</title>
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
                    <a class="nav-link text-white" href="index.php?page=settings"><i class="fas fa-cog me-2"></i> Cài đặt</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white active" href="index.php?page=promotions"><i class="fas fa-tags me-2"></i> Khuyến mãi</a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 p-4">
            <div class="container-fluid px-4">
                <h1 class="mt-4">Promotion Management</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Promotions</li>
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
                    <!-- Promotion Form -->
                    <div class="col-lg-5">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-tags me-1"></i>
                                <?php echo $edit_promotion ? 'Edit' : 'Add New'; ?> Promotion
                            </div>
                            <div class="card-body">
                                <form action="index.php?page=promotions<?php echo $edit_promotion ? '&action=edit&id=' . $edit_promotion->id : ''; ?>" method="POST">
                                    <?php if ($edit_promotion): ?>
                                    <input type="hidden" name="edit_id" value="<?php echo $edit_promotion->id; ?>">
                                    <?php endif; ?>

                                    <div class="mb-3">
                                        <label for="name" class="form-label">Promotion Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $edit_promotion ? htmlspecialchars($edit_promotion->name) : ''; ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="code" class="form-label">Promotion Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="code" name="code" value="<?php echo $edit_promotion ? htmlspecialchars($edit_promotion->code) : ''; ?>" required pattern="[A-Za-z0-9]+" title="Only letters and numbers allowed">
                                        <div class="form-text">Unique code for customers (e.g., SUMMER2025)</div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="discount_type" class="form-label">Discount Type <span class="text-danger">*</span></label>
                                            <select class="form-select" id="discount_type" name="discount_type" required>
                                                <option value="percentage" <?php echo ($edit_promotion && $edit_promotion->discount_type == 'percentage') ? 'selected' : ''; ?>>Percentage (%)</option>
                                                <option value="fixed" <?php echo ($edit_promotion && $edit_promotion->discount_type == 'fixed') ? 'selected' : ''; ?>>Fixed Amount (VND)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="discount_value" class="form-label">Discount Value <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="discount_value" name="discount_value" step="0.01" min="0.01" value="<?php echo $edit_promotion ? $edit_promotion->discount_value : ''; ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $edit_promotion ? $edit_promotion->start_date : date('Y-m-d'); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="end_date" class="form-label">End Date</label>
                                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo ($edit_promotion && $edit_promotion->end_date) ? $edit_promotion->end_date : ''; ?>">
                                            <div class="form-text">Leave blank for no expiry</div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="min_purchase" class="form-label">Minimum Purchase (VND)</label>
                                            <input type="number" class="form-control" id="min_purchase" name="min_purchase" step="0.01" min="0" value="<?php echo $edit_promotion ? $edit_promotion->min_purchase : '0'; ?>">
                                            <div class="form-text">Minimum order amount (0 for none)</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="usage_limit" class="form-label">Usage Limit</label>
                                            <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="0" value="<?php echo $edit_promotion ? $edit_promotion->usage_limit : '0'; ?>">
                                            <div class="form-text">Max uses (0 for unlimited)</div>
                                        </div>
                                    </div>

                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" <?php echo ($edit_promotion && $edit_promotion->is_active == 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary"><?php echo $edit_promotion ? 'Update' : 'Add'; ?> Promotion</button>
                                        <?php if ($edit_promotion): ?>
                                        <a href="index.php?page=promotions" class="btn btn-secondary">Cancel Edit</a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Promotions List -->
                    <div class="col-lg-7">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-list me-1"></i>
                                Promotions List
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Name</th>
                                                <th>Code</th>
                                                <th>Discount</th>
                                                <th>Validity</th>
                                                <th>Status</th>
                                                <th>Usage</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($promotions)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center">No promotions found</td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach ($promotions as $promo): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($promo['name']); ?></td>
                                                <td><code><?php echo htmlspecialchars($promo['code']); ?></code></td>
                                                <td>
                                                    <?php 
                                                    if ($promo['discount_type'] == 'percentage') {
                                                        echo htmlspecialchars($promo['discount_value']) . '%';
                                                    } else {
                                                        echo 'VND ' . number_format($promo['discount_value']);
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php echo date('d/m/Y', strtotime($promo['start_date'])); ?>
                                                    <?php if (!empty($promo['end_date'])): ?>
                                                    <br>to <?php echo date('d/m/Y', strtotime($promo['end_date'])); ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($promo['is_active'] == 1): ?>
                                                    <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                    <span class="badge bg-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo $promo['usage_count']; ?>
                                                    <?php if ($promo['usage_limit'] > 0): ?>
                                                    / <?php echo $promo['usage_limit']; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="index.php?page=promotions&action=edit&id=<?php echo $promo['id']; ?>" class="btn btn-primary btn-sm me-1">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="index.php?page=promotions&action=delete&id=<?php echo $promo['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this promotion?')">
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

        .form-control, .form-select, .form-check-input {
            border-radius: 5px;
        }

        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
            border-color: #007bff;
        }

        .form-text {
            font-size: 0.85rem;
            color: #6c757d;
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

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
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

        .table td code {
            background-color: #f1f3f5;
            padding: 2px 6px;
            border-radius: 4px;
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

            .form-control, .form-select {
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