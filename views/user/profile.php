<?php 
$page_title = "My Profile";
include 'views/layouts/header.php'; 
?>

<div class="row">
    <!-- Sidebar Menu -->
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">My Account</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="index.php?controller=user&action=profile" class="list-group-item list-group-item-action active">
                    <i class="fas fa-user me-2"></i> My Profile
                </a>
                <a href="index.php?controller=user&action=orders" class="list-group-item list-group-item-action">
                    <i class="fas fa-shopping-bag me-2"></i> My Orders
                </a>
                <a href="index.php?controller=user&action=changePassword" class="list-group-item list-group-item-action">
                    <i class="fas fa-key me-2"></i> Change Password
                </a>
                <a href="index.php?controller=user&action=logout" class="list-group-item list-group-item-action text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>
    </div>
    
    <!-- Profile Content -->
    <div class="col-md-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Edit Profile</h5>
            </div>
            <div class="card-body p-4">
                <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <form action="index.php?controller=user&action=profile" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($this->user->username); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($this->user->email); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($this->user->full_name); ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($this->user->phone); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($this->user->address); ?>">
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Account Info -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Account Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Account Created:</strong> <?php echo date('F d, Y', strtotime($this->user->created_at)); ?></p>
                        <p><strong>Last Updated:</strong> <?php echo date('F d, Y', strtotime($this->user->updated_at)); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Account Type:</strong> <?php echo ucfirst($this->user->role); ?></p>
                        <p><strong>Account Status:</strong> <span class="badge bg-success">Active</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
