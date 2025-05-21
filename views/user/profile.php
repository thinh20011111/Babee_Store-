<?php 
$page_title = "Hồ sơ cá nhân";
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
                <div class="list-group list-group-flush">
                    <a href="index.php?controller=user&action=profile" class="list-group-item list-group-item-action active rounded py-3">
                        <i class="fas fa-user fa-lg me-2"></i> Hồ sơ cá nhân
                    </a>
                    <a href="index.php?controller=user&action=orders" class="list-group-item list-group-item-action rounded py-3">
                        <i class="fas fa-shopping-bag fa-lg me-2"></i> Đơn hàng của tôi
                    </a>
                    <a href="index.php?controller=user&action=changePassword" class="list-group-item list-group-item-action rounded py-3">
                        <i class="fas fa-key fa-lg me-2"></i> Đổi mật khẩu
                    </a>
                    <a href="index.php?controller=user&action=logout" class="list-group-item list-group-item-action text-danger rounded py-3">
                        <i class="fas fa-sign-out-alt fa-lg me-2"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Profile Content -->
        <div class="col-md-9">
            <div class="card border-0 shadow-sm rounded">
                <div class="card-header bg-primary text-white rounded-top">
                    <h5 class="mb-0">Chỉnh sửa hồ sơ</h5>
                </div>
                <div class="card-body p-4">
                    <form action="index.php?controller=user&action=profile" method="POST" id="profile-form" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label fw-bold">Tên người dùng</label>
                                <input type="text" class="form-control rounded-pill" id="username" name="username" value="<?php echo htmlspecialchars($this->user->username); ?>" required pattern="[a-zA-Z0-9]{3,20}" data-error="Tên người dùng phải có 3-20 ký tự, chỉ bao gồm chữ cái và số.">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-bold">Địa chỉ Email</label>
                                <input type="email" class="form-control rounded-pill" id="email" name="email" value="<?php echo htmlspecialchars($this->user->email); ?>" required data-error="Vui lòng nhập địa chỉ email hợp lệ.">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-bold">Họ và tên</label>
                            <input type="text" class="form-control rounded-pill" id="full_name" name="full_name" value="<?php echo htmlspecialchars($this->user->full_name); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-bold">Số điện thoại</label>
                                <input type="tel" class="form-control rounded-pill" id="phone" name="phone" value="<?php echo htmlspecialchars($this->user->phone); ?>" pattern="[0-9]{10,15}" data-error="Số điện thoại phải có 10-15 chữ số.">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label fw-bold">Địa chỉ</label>
                                <input type="text" class="form-control rounded-pill" id="address" name="address" value="<?php echo htmlspecialchars($this->user->address); ?>">
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm" id="submit-btn">
                                <i class="fas fa-save me-1"></i> Cập nhật hồ sơ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Account Info -->
            <div class="card border-0 shadow-sm mt-4 rounded">
                <div class="card-header bg-light rounded-top">
                    <h5 class="mb-0">Thông tin tài khoản</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p><i class="fas fa-calendar-alt me-2 text-primary"></i><strong>Tài khoản được tạo:</strong> <?php echo date('d/m/Y', strtotime($this->user->created_at)); ?></p>
                            <p><i class="fas fa-calendar-check me-2 text-primary"></i><strong>Cập nhật lần cuối:</strong> <?php echo date('d/m/Y', strtotime($this->user->updated_at)); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p><i class="fas fa-user-tag me-2 text-primary"></i><strong>Loại tài khoản:</strong> <?php echo ucfirst($this->user->role == 'user' ? 'Người dùng' : ($this->user->role == 'admin' ? 'Quản trị viên' : $this->user->role)); ?></p>
                            <p><i class="fas fa-check-circle me-2 text-primary"></i><strong>Trạng thái tài khoản:</strong> <span class="badge bg-success rounded-pill py-2 px-3">Hoạt động</span></p>
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
    position: relative;
    z-index: 1;
}
.list-group-item:hover {
    transform: scale(1.05);
    background-color: #f8f9fa;
}
.list-group-item.active {
    background-color: #007bff;
    border-color: #007bff;
    color: #fff;
}
.list-group-item.text-danger:hover {
    background-color: #fff5f5;
}
.list-group-item i {
    transition: transform 0.2s ease;
}
.list-group-item:hover i {
    transform: scale(1.2);
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
#profile-form .form-control.is-invalid {
    border-color: #dc3545;
}
#profile-form .invalid-feedback {
    font-size: 0.85rem;
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
#profile-form .btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
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
    .notification {
        min-width: 250px;
        top: 10px;
        right: 10px;
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
    .notification {
        min-width: 200px;
        font-size: 0.8rem;
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

    // Show server-side error or success message if exists
    <?php if (!empty($error)): ?>
        showNotification(<?php echo json_encode($error); ?>, 'error', 'Lỗi cập nhật hồ sơ');
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        showNotification(<?php echo json_encode($success); ?>, 'success', 'Cập nhật thành công');
    <?php endif; ?>

    // Form validation and AJAX submission
    const form = document.getElementById('profile-form');
    const submitBtn = document.getElementById('submit-btn');
    
    if (form) {
        // Client-side validation
        form.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                if (!this.checkValidity()) {
                    this.classList.add('is-invalid');
                    this.nextElementSibling.textContent = this.dataset.error || 'Dữ liệu không hợp lệ.';
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        });

        // Form submission with AJAX
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (!form.checkValidity()) {
                form.querySelectorAll('input').forEach(input => {
                    if (!input.checkValidity()) {
                        input.classList.add('is-invalid');
                        input.nextElementSibling.textContent = this.dataset.error || 'Dữ liệu không hợp lệ.';
                    }
                });
                return;
            }

            // Disable button and show loading state
            submitBtn.disabled = true;
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang cập nhật...';

            // Collect form data
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => data[key] = value);

            // AJAX request
            fetch('index.php?controller=user&action=profile', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                console.log('AJAX response received', { status: response.status, ok: response.ok });
                return response.text().then(text => ({ response, text }));
            })
            .then(({ response, text }) => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('AJAX data:', data);
                    if (data.success) {
                        showNotification(data.message || 'Hồ sơ đã được cập nhật thành công!', 'success', 'Cập nhật thành công');
                    } else {
                        // Check if error is related to stock (unlikely in profile, but included for consistency)
                        const isStockError = data.message && (
                            data.message.includes('vượt quá tồn kho') || 
                            data.message.includes('stock') || 
                            data.message.includes('tồn kho')
                        );
                        showNotification(
                            data.message || 'Không thể cập nhật hồ sơ.',
                            'error',
                            isStockError ? 'Tồn kho không đủ' : 'Lỗi cập nhật hồ sơ'
                        );
                    }
                } catch (e) {
                    console.error('Error parsing JSON:', e, 'Response text:', text);
                    // Check for stock-related error in raw response
                    const isStockError = response.status === 400 && (
                        text.includes('vượt quá tồn kho') || 
                        text.includes('stock') || 
                        text.includes('tồn kho')
                    );
                    showNotification(
                        text || 'Lỗi máy chủ: Phản hồi không hợp lệ.',
                        'error',
                        isStockError ? 'Tồn kho không đủ' : 'Lỗi hệ thống'
                    );
                }
            })
            .catch(error => {
                console.error('AJAX error:', error);
                const isStockError = error.message && (
                    error.message.includes('vượt quá tồn kho') || 
                    error.message.includes('stock') || 
                    error.message.includes('tồn kho')
                );
                showNotification(
                    'Đã xảy ra lỗi: ' + error.message,
                    'error',
                    isStockError ? 'Tồn kho không đủ' : 'Lỗi hệ thống'
                );
            })
            .finally(() => {
                // Restore button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }
});
</script>

<?php include 'views/layouts/footer.php'; ?>