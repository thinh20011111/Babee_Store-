<?php
// Users management page
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
require_once '../models/User.php';

// Initialize objects
$user = new User($conn);

// Process actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = '';
$error_message = '';

// Delete user
if ($action == 'delete' && isset($_GET['id'])) {
    $user->id = $_GET['id'];
    
    // Prevent self-deletion
    if ($user->id == $_SESSION['user_id']) {
        $error_message = "You cannot delete your own account.";
    } else {
        if ($user->delete()) {
            $success_message = "User deleted successfully.";
        } else {
            $error_message = "Failed to delete user.";
        }
    }
}

// View/edit single user
$edit_user = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $user->id = $_GET['id'];
    if ($user->readOne()) {
        $edit_user = $user;
    } else {
        $error_message = "User not found.";
    }
}

// Process edit form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $user->id = $_POST['user_id'];
    $user->username = $_POST['username'];
    $user->email = $_POST['email'];
    $user->full_name = $_POST['full_name'];
    $user->phone = $_POST['phone'];
    $user->address = $_POST['address'];
    $user->role = $_POST['role'];
    
    if ($user->update()) {
        $success_message = "User updated successfully.";
        
        // Refresh user data
        $user->readOne();
        $edit_user = $user;
    } else {
        $error_message = "Failed to update user.";
    }
}

// Process password reset
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $user->id = $_POST['user_id'];
    $user->password = $_POST['new_password'];
    
    if ($user->updatePassword()) {
        $success_message = "Password reset successfully.";
    } else {
        $error_message = "Failed to reset password.";
    }
}

// Process add user form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $user->username = $_POST['username'];
    $user->email = $_POST['email'];
    $user->password = $_POST['password'];
    $user->full_name = $_POST['full_name'];
    $user->phone = $_POST['phone'];
    $user->address = $_POST['address'];
    $user->role = $_POST['role'];
    
    // Check if email exists
    $temp_user = new User($conn);
    $temp_user->email = $user->email;
    if ($temp_user->emailExists()) {
        $error_message = "Email already exists.";
    } else {
        if ($user->create()) {
            $success_message = "User created successfully.";
        } else {
            $error_message = "Failed to create user.";
        }
    }
}

// Get search parameter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$items_per_page = 10;

// Get users
$users = [];
if (!empty($search)) {
    $stmt = $user->search($search);
} else {
    $stmt = $user->read();
}

// Process users
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $users[] = $row;
}

// Apply pagination
$total_rows = count($users);
$total_pages = ceil($total_rows / $items_per_page);
$offset = ($page - 1) * $items_per_page;
$users = array_slice($users, $offset, $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            position: sticky;
            top: 0;
        }
        .card {
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .badge {
            padding: 8px 12px;
        }
        .pagination .page-link {
            color: #007bff;
        }
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
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
                    <a class="nav-link text-white active" href="index.php?page=banners"><i class="fas fa-images me-2"></i> Giao diện</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white active" href="index.php?page=settings"><i class="fas fa-cog me-2"></i> Cài đặt</a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 p-4">
            <div class="container-fluid">
                <h1 class="mt-4 mb-3">User Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Users</li>
                    </ol>
                </nav>

                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($edit_user): ?>
                <!-- Edit User Form -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-user-edit me-2"></i> Edit User: <?php echo htmlspecialchars($edit_user->username); ?></h5>
                        <a href="index.php?page=users" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Users
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <form action="index.php?page=users&action=edit&id=<?php echo $edit_user->id; ?>" method="POST">
                                    <input type="hidden" name="user_id" value="<?php echo $edit_user->id; ?>">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($edit_user->username); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($edit_user->email); ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="full_name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($edit_user->full_name); ?>">
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="phone" class="form-label">Phone</label>
                                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($edit_user->phone); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                            <select class="form-select" id="role" name="role" required>
                                                <option value="customer" <?php echo ($edit_user->role == 'customer') ? 'selected' : ''; ?>>Customer</option>
                                                <option value="staff" <?php echo ($edit_user->role == 'staff') ? 'selected' : ''; ?>>Staff</option>
                                                <option value="admin" <?php echo ($edit_user->role == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($edit_user->address); ?></textarea>
                                    </div>
                                    <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
                                </form>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="m-0 fw-bold"><i class="fas fa-key me-2"></i> Reset Password</h6>
                                    </div>
                                    <div class="card-body">
                                        <form action="index.php?page=users&action=edit&id=<?php echo $edit_user->id; ?>" method="POST">
                                            <input type="hidden" name="user_id" value="<?php echo $edit_user->id; ?>">
                                            <div class="mb-3">
                                                <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            </div>
                                            <div class="d-grid">
                                                <button type="submit" name="reset_password" class="btn btn-warning" onclick="return validatePasswordReset()">Reset Password</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- Users List View -->
                <div class="row mb-4">
                    <!-- Add User Form -->
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-user-plus me-2"></i> Add New User</h6>
                            </div>
                            <div class="card-body">
                                <form action="index.php?page=users" method="POST">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="full_name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name">
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone">
                                    </div>
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="customer">Customer</option>
                                            <option value="staff">Staff</option>
                                            <option value="admin">Administrator</option>
                                        </select>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" name="add_user" class="btn btn-success">Add User</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Users List Table -->
                    <div class="col-md-8">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-users me-2"></i> Users List</h6>
                            </div>
                            <div class="card-body">
                                <!-- Search Form -->
                                <div class="mb-4">
                                    <form action="index.php" method="GET" class="d-flex">
                                        <input type="hidden" name="page" value="users">
                                        <input type="text" name="search" class="form-control me-2" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                                        <button type="submit" class="btn btn-primary">Search</button>
                                    </form>
                                </div>
                                
                                <!-- Users Table -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Registered</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($users)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No users found</td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach ($users as $user_item): ?>
                                            <tr>
                                                <td><?php echo $user_item['id']; ?></td>
                                                <td><?php echo htmlspecialchars($user_item['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user_item['email']); ?></td>
                                                <td>
                                                    <?php
                                                    $role_class = '';
                                                    switch($user_item['role']) {
                                                        case 'admin': $role_class = 'bg-danger text-white'; break;
                                                        case 'staff': $role_class = 'bg-warning text-dark'; break;
                                                        default: $role_class = 'bg-info text-dark';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $role_class; ?>"><?php echo ucfirst($user_item['role']); ?></span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($user_item['created_at'])); ?></td>
                                                <td>
                                                    <a href="index.php?page=users&action=edit&id=<?php echo $user_item['id']; ?>" class="btn btn-primary btn-sm me-1">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($user_item['id'] != $_SESSION['user_id']): ?>
                                                    <a href="index.php?page=users&action=delete&id=<?php echo $user_item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center mt-4">
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                            <a class="page-link" href="index.php?page=users&<?php 
                                                echo (!empty($search)) ? 'search=' . urlencode($search) . '&' : '';
                                                echo 'page=' . $i;
                                            ?>"><?php echo $i; ?></a>
                                        </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function validatePasswordReset() {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (newPassword.length < 6) {
            alert('Password must be at least 6 characters long');
            return false;
        }
        
        if (newPassword !== confirmPassword) {
            alert('Passwords do not match');
            return false;
        }
        
        return true;
    }
    </script>
</body>
</html>