<?php 
$page_title = "My Profile";
include 'views/layouts/header.php'; 
?>

<!-- Đảm bảo Bootstrap, Font Awesome và Animate.css được include -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

<div class="container mt-5 mb-5">
    <div class="row">
        <!-- Sidebar Menu -->
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm rounded">
                <div class="card-header bg-primary text-white rounded-top">
                    <h5 class="mb-0">My Account</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="index.php?controller=user&action=profile" class="list-group-item list-group-item-action active rounded py-3">
                        <i class="fas fa-user me-2"></i> My Profile
                    </a>
                    <a href="index.php?controller=user&action=orders" class="list-group-item list-group-item-action rounded py-3">
                        <i class="fas fa-shopping-bag me-2"></i> My Orders
                    </a>
                    <a href="index.php?controller=user&action=changePassword" class="list-group-item list-group-item-action rounded py-3">
                        <i class="fas fa-key me-2"></i> Change Password
                    </a>
                    <a href="index.php?controller=user&action=logout" class="list-group-item list-group-item-action text-danger rounded py-3">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Profile Content -->
        <div class="col-md-9">
            <div class="card border-0 shadow-sm rounded">
                <div class="card-header bg-primary text-white rounded-top">
                    <h5 class="mb-0">Edit Profile</h5>
                </div>
                <div class="card-body p-4">
                    <form action="index.php?controller=user&action=profile" method="POST" id="profile-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label fw-bold">Username</label>
                                <input type="text" class="form-control rounded-pill" id="username" name="username" value="<?php echo htmlspecialchars($this->user->username); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control rounded-pill" id="email" name="email" value="<?php echo htmlspecialchars($this->user->email); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-bold">Full Name</label>
                            <input type="text" class="form-control rounded-pill" id="full_name" name="full_name" value="<?php echo htmlspecialchars($this->user->full_name); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-bold">Phone Number</label>
                                <input type="tel" class="form-control rounded-pill" id="phone" name="phone" value="<?php echo htmlspecialchars($this->user->phone); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label fw-bold">Address</label>
                                <input type="text" class="form-control rounded-pill" id="address" name="address" value="<?php echo htmlspecialchars($this->user->address); ?>">
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm">
                                <i class="fas fa-save me-1"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Account Info -->
            <div class="card border-0 shadow-sm mt-4 rounded">
                <div class="card-header bg-light rounded-top">
                    <h5 class="mb-0">Account Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p><strong>Account Created:</strong> <?php echo date('F d, Y', strtotime($this->user->created_at)); ?></p>
                            <p><strong>Last Updated:</strong> <?php echo date('F d, Y', strtotime($this->user->updated_at)); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p><strong>Account Type:</strong> <?php echo ucfirst($this->user->role); ?></p>
                            <p><strong>Account Status:</strong> <span class="badge bg-success rounded-pill py-2 px-3">Active</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Import Google Fonts */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap');

/* General styles */
body {
    font-family: 'Poppins', sans-serif;
}
.container {
    padding-left: 15px;
    padding-right: 15px;
}

/* Sidebar styles */
.list-group-item {
    transition: all 0.3s ease;
}
.list-group-item:hover {
    transform: scale(1.05);
    background-color: #f8f9fa;
}
.list-group-item.active {
    background-color: #007bff;
    border-color: #007bff;
}
.list-group-item.text-danger:hover {
    background-color: #fff5f5;
}

/* Form styles */
#profile-form .form-control {
    border-color: #007bff;
    transition: all 0.2s ease;
}
#profile-form .form-control:focus {
    border-color: #0056b3;
    box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
}
#profile-form .btn-primary {
    position: relative;
    overflow: hidden;
    transition: all 0.2s ease;
}
#profile-form .btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}
#profile-form .btn-primary::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.4s ease, height 0.4s ease;
}
#profile-form .btn-primary:active::after {
    width: 200px;
    height: 200px;
}

/* Card styles */
.card {
    transition: all 0.3s ease;
}
.card:hover {
    transform: translateY(-5px);
}
.card-header {
    border-bottom: 0;
}
.card-body {
    background-color: #fff;
}

/* Badge styles */
.badge {
    font-size: 0.9rem;
}

/* Notification styles */
.notification {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    animation: slideInRight 0.3s ease-in-out;
}
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .container {
        padding-left: 10px;
        padding-right: 10px;
    }
    .card-header h5 {
        font-size: 1.2rem;
    }
    .list-group-item {
        font-size: 0.9rem;
        padding: 0.75rem 1rem;
    }
    #profile-form .form-control {
        font-size: 0.9rem;
        padding: 0.5rem;
    }
    #profile-form .btn-primary {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
    .card-body {
        padding: 1rem;
    }
    .card-body p {
        font-size: 0.9rem;
    }
    .badge {
        font-size: 0.8rem;
        padding: 0.5rem 1rem;
    }
}

@media (max-width: 576px) {
    .container {
        padding-left: 8px;
        padding-right: 8px;
    }
    .card-header h5 {
        font-size: 1.1rem;
    }
    .list-group-item {
        font-size: 0.85rem;
        padding: 0.6rem 0.8rem;
    }
    #profile-form .form-control {
        font-size: 0.85rem;
        padding: 0.4rem;
    }
    #profile-form .btn-primary {
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
    }
    .card-body {
        padding: 0.8rem;
    }
    .card-body p {
        font-size: 0.85rem;
    }
    .badge {
        font-size: 0.75rem;
        padding: 0.4rem 0.8rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Notification function
    function showNotification(message, type, title = '') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show notification`;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '2000';
        notification.style.minWidth = '300px';
        notification.innerHTML = `
            ${title ? `<strong>${title}</strong><br>` : ''}
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(notification);
        
        // Auto dismiss after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Show error or success message if exists
    <?php if (!empty($error)): ?>
        showNotification(<?php echo json_encode($error); ?>, 'error', 'Lỗi cập nhật hồ sơ');
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        showNotification(<?php echo json_encode($success); ?>, 'success', 'Cập nhật thành công');
    <?php endif; ?>
});
</script>

<?php include 'views/layouts/footer.php'; ?>