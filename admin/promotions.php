<?php
// Promotions management page
// Check direct script access
if (!defined('ADMIN_INCLUDED')) {
    define('ADMIN_INCLUDED', true);
}

// Restrict access to admin only
if ($_SESSION['user_role'] != 'admin') {
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
    $promotion->name = $_POST['name'];
    $promotion->code = $_POST['code'];
    $promotion->discount_type = $_POST['discount_type'];
    $promotion->discount_value = $_POST['discount_value'];
    $promotion->start_date = $_POST['start_date'];
    $promotion->end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $promotion->is_active = isset($_POST['is_active']) ? 1 : 0;
    $promotion->min_purchase = $_POST['min_purchase'];
    $promotion->usage_limit = $_POST['usage_limit'];
    
    if (isset($_POST['edit_id'])) {
        // Update existing promotion
        $promotion->id = $_POST['edit_id'];
        if ($promotion->update()) {
            $success_message = "Promotion updated successfully.";
            // Refresh promotion data
            $promotion->readOne();
            $edit_promotion = $promotion;
        } else {
            $error_message = "Failed to update promotion.";
        }
    } else {
        // Create new promotion
        if ($promotion->create()) {
            $success_message = "Promotion created successfully.";
            // Reset form
            $promotion = new Promotion($conn);
        } else {
            $error_message = "Failed to create promotion.";
        }
    }
}

// Get all promotions
$promotions = [];
$stmt = $promotion->read();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $promotions[] = $row;
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Promotion Management</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Promotions</li>
    </ol>
    
    <?php if (!empty($success_message)): ?>
    <div class="alert alert-success">
        <?php echo $success_message; ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger">
        <?php echo $error_message; ?>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Promotion Form -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
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
                            <input type="text" class="form-control" id="code" name="code" value="<?php echo $edit_promotion ? htmlspecialchars($edit_promotion->code) : ''; ?>" required>
                            <div class="form-text">Code that customers will enter at checkout (e.g., SUMMER2023)</div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="discount_type" class="form-label">Discount Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="discount_type" name="discount_type" required>
                                    <option value="percentage" <?php echo ($edit_promotion && $edit_promotion->discount_type == 'percentage') ? 'selected' : ''; ?>>Percentage (%)</option>
                                    <option value="fixed" <?php echo ($edit_promotion && $edit_promotion->discount_type == 'fixed') ? 'selected' : ''; ?>>Fixed Amount</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="discount_value" class="form-label">Discount Value <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="discount_value" name="discount_value" step="0.01" min="0" value="<?php echo $edit_promotion ? $edit_promotion->discount_value : ''; ?>" required>
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
                                <label for="min_purchase" class="form-label">Minimum Purchase</label>
                                <input type="number" class="form-control" id="min_purchase" name="min_purchase" step="0.01" min="0" value="<?php echo $edit_promotion ? $edit_promotion->min_purchase : '0'; ?>">
                                <div class="form-text">Minimum order amount required (0 for no minimum)</div>
                            </div>
                            <div class="col-md-6">
                                <label for="usage_limit" class="form-label">Usage Limit</label>
                                <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="0" value="<?php echo $edit_promotion ? $edit_promotion->usage_limit : '0'; ?>">
                                <div class="form-text">Maximum times code can be used (0 for unlimited)</div>
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
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
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
                                            echo $promo['discount_value'] . '%';
                                        } else {
                                            echo CURRENCY . number_format($promo['discount_value']);
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($promo['start_date'])); ?>
                                        <?php if (!empty($promo['end_date'])): ?>
                                        <br>to <?php echo date('M d, Y', strtotime($promo['end_date'])); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($promo['is_active'] == 1): ?>
                                        <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $promo['usage_count']; ?>
                                        <?php if ($promo['usage_limit'] > 0): ?>
                                        / <?php echo $promo['usage_limit']; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="index.php?page=promotions&action=edit&id=<?php echo $promo['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="index.php?page=promotions&action=delete&id=<?php echo $promo['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this promotion?')">
                                            <i class="fas fa-trash"></i>
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
