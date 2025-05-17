<?php
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banner Management - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgo=">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-grow-1 p-4">
            <div class="container-fluid px-4">
                <h1 class="mt-4">Banner Management</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
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
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-primary text-white">
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
                                            <img src="<?php echo htmlspecialchars($edit_banner->image); ?>" alt="<?php echo htmlspecialchars($edit_banner->title); ?>" class="img-fluid banner-preview">
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
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-primary text-white">
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
                                                    <img src="<?php echo htmlspecialchars($b['image']); ?>" alt="<?php echo htmlspecialchars($b['title']); ?>" class="img-thumbnail banner-list-image">
                                                    <?php else: ?>
                                                    <div class="banner-placeholder d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-image text-secondary fa-2x"></i>
                                                    </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($b['title']); ?></strong>
                                                    <?php if (!empty($b['subtitle'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($b['subtitle']); ?></small>
                                                    <?php endif; ?>
                                                    <?php if (!empty($b['link'])): ?>
                                                    <br><small class="text-muted">Link: <a href="<?php echo htmlspecialchars($b['link']); ?>" target="_blank"><?php echo htmlspecialchars($b['link']); ?></a></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $b['position']; ?></td>
                                                <td>
                                                    <?php if ($b['is_active'] == 1): ?>
                                                    <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                    <span class="badge bg-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="index.php?page=banners&action=edit&id=<?php echo $b['id']; ?>" class="btn btn-primary btn-sm me-1">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="index.php?page=banners&action=delete&id=<?php echo $b['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this banner?')">
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

        .form-control, .form-check-input {
            border-radius: 5px;
        }

        .form-control:focus {
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

        /* Banner Preview Image */
        .banner-preview {
            max-height: 150px;
            max-width: 100%;
            object-fit: contain;
            border-radius: 5px;
            border: 1px solid #dee2e6;
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

        /* Banner List Image */
        .banner-list-image {
            width: 100px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }

        .banner-placeholder {
            width: 100px;
            height: 60px;
            background-color: #f1f3f5;
            border-radius: 5px;
            border: 1px dashed #ced4da;
        }

        /* Typography in Table */
        .table td strong {
            font-weight: 600;
            color: #212529;
        }

        .table td small {
            display: block;
            margin-top: 3px;
            line-height: 1.4;
        }

        .table td small.text-muted a {
            color: #007bff;
            text-decoration: none;
        }

        .table td small.text-muted a:hover {
            text-decoration: underline;
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

            .banner-list-image, .banner-placeholder {
                width: 80px;
                height: 48px;
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

            .form-control {
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