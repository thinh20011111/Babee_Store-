<?php
//hihi
// Banners management page
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
require_once '../models/Banner.php';

// Initialize objects
$banner = new Banner($conn);

// Process actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = '';
$error_message = '';

// Delete banner
if ($action == 'delete' && isset($_GET['id'])) {
    $banner->id = $_GET['id'];
    if ($banner->delete()) {
        $success_message = "Banner deleted successfully.";
    } else {
        $error_message = "Failed to delete banner.";
    }
}

// Edit banner
$edit_banner = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $banner->id = $_GET['id'];
    if ($banner->readOne()) {
        $edit_banner = $banner;
    } else {
        $error_message = "Banner not found.";
    }
}

// Process add/edit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $banner->title = $_POST['title'];
    $banner->subtitle = $_POST['subtitle'];
    $banner->image = $_POST['image'];
    $banner->link = $_POST['link'];
    $banner->position = $_POST['position'];
    $banner->is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (isset($_POST['edit_id'])) {
        // Update existing banner
        $banner->id = $_POST['edit_id'];
        if ($banner->update()) {
            $success_message = "Banner updated successfully.";
            // Refresh banner data
            $banner->readOne();
            $edit_banner = $banner;
        } else {
            $error_message = "Failed to update banner.";
        }
    } else {
        // Create new banner
        if ($banner->create()) {
            $success_message = "Banner created successfully.";
            // Reset form
            $banner = new Banner($conn);
        } else {
            $error_message = "Failed to create banner.";
        }
    }
}

// Get all banners
$banners = [];
$stmt = $banner->read();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $banners[] = $row;
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Banner Management</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Banners</li>
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
        <!-- Banner Form -->
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-image me-1"></i>
                    <?php echo $edit_banner ? 'Edit' : 'Add New'; ?> Banner
                </div>
                <div class="card-body">
                    <form action="index.php?page=banners<?php echo $edit_banner ? '&action=edit&id=' . $edit_banner->id : ''; ?>" method="POST">
                        <?php if ($edit_banner): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_banner->id; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo $edit_banner ? htmlspecialchars($edit_banner->title) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subtitle" class="form-label">Subtitle</label>
                            <input type="text" class="form-control" id="subtitle" name="subtitle" value="<?php echo $edit_banner ? htmlspecialchars($edit_banner->subtitle) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Image URL <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="image" name="image" value="<?php echo $edit_banner ? htmlspecialchars($edit_banner->image) : ''; ?>" required>
                            <div class="form-text">Enter a URL to the banner image. For new images, upload via File Manager and paste the URL here.</div>
                        </div>
                        
                        <div class="mb-3">
                            <?php if ($edit_banner && !empty($edit_banner->image)): ?>
                            <label class="form-label">Current Image</label>
                            <div class="border p-2 mb-3 text-center">
                                <img src="<?php echo htmlspecialchars($edit_banner->image); ?>" alt="<?php echo htmlspecialchars($edit_banner->title); ?>" class="img-fluid" style="max-height: 150px;">
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="link" class="form-label">Link URL</label>
                            <input type="text" class="form-control" id="link" name="link" value="<?php echo $edit_banner ? htmlspecialchars($edit_banner->link) : ''; ?>">
                            <div class="form-text">Where should the banner link to? Leave empty for no link.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="position" class="form-label">Display Order <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="position" name="position" min="1" value="<?php echo $edit_banner ? $edit_banner->position : $banner->getNextPosition(); ?>" required>
                            <div class="form-text">Banners are displayed in ascending order (1 shown first).</div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" <?php echo ($edit_banner && $edit_banner->is_active == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary"><?php echo $edit_banner ? 'Update' : 'Add'; ?> Banner</button>
                            <?php if ($edit_banner): ?>
                            <a href="index.php?page=banners" class="btn btn-secondary">Cancel Edit</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Banners List -->
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-list me-1"></i>
                    Banners List
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($banners)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No banners found</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($banners as $b): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($b['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($b['image']); ?>" alt="<?php echo htmlspecialchars($b['title']); ?>" class="img-thumbnail" style="width: 100px;">
                                        <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100px; height: 60px;">
                                            <i class="fas fa-image text-secondary fa-2x"></i>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($b['title']); ?></strong>
                                        <?php if (!empty($b['subtitle'])): ?>
                                        <br><small><?php echo htmlspecialchars($b['subtitle']); ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($b['link'])): ?>
                                        <br><small class="text-muted">Link: <?php echo htmlspecialchars($b['link']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $b['position']; ?></td>
                                    <td>
                                        <?php if ($b['is_active'] == 1): ?>
                                        <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="index.php?page=banners&action=edit&id=<?php echo $b['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="index.php?page=banners&action=delete&id=<?php echo $b['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this banner?')">
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
