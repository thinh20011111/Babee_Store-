<?php 
$page_title = "Đổi mật khẩu";
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
                    <h5 class="mb-0">Tài khoản của tôi</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="index.php?controller=user&action=profile" class="list-group-item list-group-item-action rounded py-3 px-4 mb-1">
                            <i class="fas fa-user fa-lg me-2"></i> Hồ sơ cá nhân
                        </a>
                        <a href="index.php?controller=user&action=orders" class="list-group-item list-group-item-action rounded py-3 px-4 mb-1">
                            <i class="fas fa-shopping-bag fa-lg me-2"></i> Đơn hàng của tôi
                        </a>
                        <a href="index.php?controller=user&action=changePassword" class="list-group-item list-group-item-action active rounded py-3 px-4 mb-1">
                            <i class="fas fa-key fa-lg me-2"></i> Đổi mật khẩu
                        </a>
                        <a href="index.php?controller=user&action=logout" class="list-group-item list-group-item-action text-danger rounded py-3 px-4 mb-1">
                            <i class="fas fa-sign-out-alt fa-lg me-2"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Change Password Content -->
        <div class="col-md-9">
            <div class="card border-0 shadow-sm rounded">
                <div class="card-header bg-primary text-white rounded-top">
                    <h5 class="mb-0">Đổi mật khẩu</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger animate__animated animate__fadeIn rounded">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                    <div class="alert alert-success animate__animated animate__fadeIn rounded">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                    <?php endif; ?>
                    
                    <form action="index.php?controller=user&action=changePassword" method="POST" id="changePasswordForm">
                        <div class="mb-4 position-relative">
                            <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <i class="fas fa-eye toggle-password position-absolute" style="right: 15px; top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                        </div>
                        <div class="mb-4 position-relative">
                            <label for="new_password" class="form-label">Mật khẩu mới</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <i class="fas fa-eye toggle-password position-absolute" style="right: 15px; top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                        </div>
                        <div class="mb-4 position-relative">
                            <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <i class="fas fa-eye toggle-password position-absolute" style="right: 15px; top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Đổi mật khẩu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Import Google Fonts */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

/* General styles */
body {
    font-family: 'Poppins', sans-serif;
}
.container {
    padding-left: 15px;
    padding-right: 15px;
}

/* Sidebar styles */
.card .card-body {
    padding: 0.5rem;
}
.list-group-item {
    display: flex;
    align-items: center;
    transition: background-color 0.2s ease, color 0.2s ease;
    position: relative;
    border: none;
    padding: 0.75rem 1.25rem;
    border-radius: 10px;
    min-height: 48px;
    margin-bottom: 0.25rem;
}
.list-group-item i {
    min-width: 24px;
    color: #495057;
    transition: color 0.2s ease;
}
.list-group-item:hover {
    background-color: #e9ecef;
}
.list-group-item:hover i {
    color: #007bff;
}
.list-group-item.active {
    background-color: #007bff;
    color: #fff;
}
.list-group-item.active i {
    color: #fff;
}
.list-group-item.active:hover {
    background-color: #0069d9;
}
.list-group-item.text-danger {
    color: #dc3545;
}
.list-group-item.text-danger:hover {
    background-color: #fff1f1;
    color: #c82333;
}
.list-group-item.text-danger:hover i {
    color: #c82333;
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

/* Form styles */
.form-label {
    font-weight: 500;
    color: #495057;
}
.form-control {
    border-radius: 8px;
    border: 1px solid #ced4da;
    transition: all 0.2s ease;
}
.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    outline: none;
}
.toggle-password {
    color: #6c757d;
    transition: color 0.2s ease;
}
.toggle-password:hover {
    color: #007bff;
}

/* Button styles */
.btn-primary {
    position: relative;
    overflow: hidden;
    transition: all 0.2s ease;
}
.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}
.btn-primary::after {
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
.btn-primary:active::after {
    width: 200px;
    height: 200px;
}

/* Alert styles */
.alert {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
        padding: 0.6rem 1rem;
    }
    .form-label {
        font-size: 0.9rem;
    }
    .form-control {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }
    .btn-primary {
        font-size: 0.9rem;
        padding: 0.5rem 1.25rem;
    }
    .alert {
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .container {
        padding-left: 8px;
        padding-right: 8px;
    }
    .col-md-3 {
        width: 100%;
    }
    .card-header h5 {
        font-size: 1.1rem;
    }
    .list-group-item {
        font-size: 0.85rem;
        padding: 0.5rem 0.8rem;
    }
    .form-label {
        font-size: 0.85rem;
    }
    .form-control {
        font-size: 0.85rem;
        padding: 0.4rem 0.6rem;
    }
    .toggle-password {
        top: 65%;
        right: 10px;
    }
    .btn-primary {
        font-size: 0.85rem;
        padding: 0.4rem 1rem;
    }
    .alert {
        font-size: 0.85rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    });

    // Form validation
    const form = document.getElementById('changePasswordForm');
    form.addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (newPassword !== confirmPassword) {
            e.preventDefault();
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger animate__animated animate__fadeIn rounded';
            errorDiv.textContent = 'Mật khẩu mới và xác nhận mật khẩu không khớp.';
            const existingAlert = form.querySelector('.alert');
            if (existingAlert) existingAlert.remove();
            form.insertBefore(errorDiv, form.firstChild);
        }
    });
});
</script>

<?php include 'views/layouts/footer.php'; ?>