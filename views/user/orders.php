<?php 
$page_title = "Đơn hàng của tôi";
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
                    <a href="index.php?controller=user&action=profile" class="list-group-item list-group-item-action rounded py-3">
                        <i class="fas fa-user fa-lg me-2"></i> Hồ sơ cá nhân
                    </a>
                    <a href="index.php?controller=user&action=orders" class="list-group-item list-group-item-action active rounded py-3">
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
        
        <!-- Orders Content -->
        <div class="col-md-9">
            <div class="card border-0 shadow-sm rounded">
                <div class="card-header bg-primary text-white rounded-top d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Đơn hàng của tôi</h5>
                </div>
                <div class="card-body p-4">
                    <?php if(!$stmt): ?>
                        <!-- Thông báo lỗi sẽ được xử lý qua JS -->
                    <?php elseif($stmt->rowCount() == 0): ?>
                        <!-- Thông báo không có đơn hàng sẽ được xử lý qua JS -->
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã đơn hàng</th>
                                    <th>Thời gian đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($order = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr class="rounded">
                                    <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo (defined('CURRENCY') ? CURRENCY : '₫') . number_format($order['total_amount']); ?></td>
                                    <td>
                                        <?php
                                        $status_text = '';
                                        $status_class = '';
                                        switch($order['status']) {
                                            case 'pending':
                                                $status_text = 'Chờ xử lý';
                                                $status_class = 'bg-warning text-dark';
                                                break;
                                            case 'processing':
                                                $status_text = 'Đang xử lý';
                                                $status_class = 'bg-info text-dark';
                                                break;
                                            case 'shipped':
                                                $status_text = 'Đã giao hàng';
                                                $status_class = 'bg-primary';
                                                break;
                                            case 'delivered':
                                                $status_text = 'Hoàn thành';
                                                $status_class = 'bg-success';
                                                break;
                                            case 'cancelled':
                                                $status_text = 'Đã hủy';
                                                $status_class = 'bg-danger';
                                                break;
                                            default:
                                                $status_text = 'Không xác định';
                                                $status_class = 'bg-secondary';
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?> rounded-pill py-2 px-3"><?php echo $status_text; ?></span>
                                    </td>
                                    <td>
                                        <a href="index.php?controller=user&action=orderDetails&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary rounded-pill px-3">
                                            <i class="fas fa-eye me-1"></i> Xem chi tiết
                                        </a>
                                        <?php if($order['status'] == 'pending'): ?>
                                        <button class="btn btn-sm btn-danger rounded-pill px-3 cancel-order-btn" data-id="<?php echo $order['id']; ?>" data-order-number="<?php echo htmlspecialchars($order['order_number']); ?>">
                                            <i class="fas fa-times me-1"></i> Hủy đơn hàng
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận hủy đơn hàng -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelOrderModalLabel">Xác nhận hủy đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Bạn có chắc muốn hủy đơn hàng <strong id="order-number"></strong> không?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-danger rounded-pill" id="confirm-cancel-btn">Hủy đơn hàng</button>
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

/* Table styles */
.table tr {
    transition: all 0.2s ease;
}
.table tr:hover {
    background-color: #f8f9fa;
}
.table td, .table th {
    vertical-align: middle;
}
.badge {
    font-size: 0.9rem;
}

/* Button styles */
.btn-primary, .btn-danger {
    position: relative;
    overflow: hidden;
    transition: all 0.2s ease;
}
.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}
.btn-danger:hover {
    background-color: #c82333;
    border-color: #c82333;
}
.btn-primary::after, .btn-danger::after {
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
.btn-primary:active::after, .btn-danger:active::after {
    width: 200px;
    height: 200px;
}
.btn:disabled {
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

/* Modal styles */
.modal-content {
    border-radius: 12px;
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
    .table {
        font-size: 0.9rem;
    }
    .btn-sm {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
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
    .table {
        font-size: 0.85rem;
    }
    .btn-sm {
        font-size: 0.75rem;
        padding: 0.3rem 0.6rem;
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

    // Show session messages or errors
    <?php if(isset($_SESSION['order_message'])): ?>
        <?php
        $message = $_SESSION['order_message'];
        $isStockError = strpos(strtolower($message), 'tồn kho') !== false || strpos(strtolower($message), 'stock') !== false;
        ?>
        showNotification(<?php echo json_encode($message); ?>, <?php echo $isStockError ? "'error'" : "'success'"; ?>, <?php echo $isStockError ? "'Tồn kho không đủ'" : "'Thông báo'"; ?>);
        <?php unset($_SESSION['order_message']); ?>
    <?php endif; ?>
    <?php if(!$stmt): ?>
        showNotification('Không thể tải dữ liệu đơn hàng.', 'error', 'Lỗi hệ thống');
    <?php elseif($stmt->rowCount() == 0): ?>
        showNotification('Bạn chưa có đơn hàng nào. <a href="index.php?controller=product&action=list" class="alert-link">Bắt đầu mua sắm</a>', 'info', 'Chưa có đơn hàng');
    <?php endif; ?>

    // Handle cancel order
    const cancelButtons = document.querySelectorAll('.cancel-order-btn');
    const modal = new bootstrap.Modal(document.getElementById('cancelOrderModal'));
    const confirmCancelBtn = document.getElementById('confirm-cancel-btn');
    let currentOrderId = null;

    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            currentOrderId = this.dataset.id;
            document.getElementById('order-number').textContent = this.dataset.orderNumber;
            modal.show();
        });
    });

    confirmCancelBtn.addEventListener('click', function() {
        if (!currentOrderId) return;

        // Disable button and show loading state
        this.disabled = true;
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang hủy...';

        // AJAX request to cancel order
        fetch(`index.php?controller=order&action=cancel&id=${currentOrderId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Đơn hàng đã được hủy thành công.', 'success', 'Hủy đơn hàng');
                // Update UI without reload
                const row = document.querySelector(`.cancel-order-btn[data-id="${currentOrderId}"]`).closest('tr');
                const statusBadge = row.querySelector('.badge');
                statusBadge.className = 'badge bg-danger rounded-pill py-2 px-3';
                statusBadge.textContent = 'Đã hủy';
                row.querySelector('.cancel-order-btn').remove();
            } else {
                const isStockError = data.message && (
                    data.message.includes('tồn kho') || 
                    data.message.includes('stock')
                );
                showNotification(
                    data.message || 'Không thể hủy đơn hàng.',
                    'error',
                    isStockError ? 'Tồn kho không đủ' : 'Lỗi hủy đơn hàng'
                );
            }
        })
        .catch(error => {
            const isStockError = error.message && (
                error.message.includes('tồn kho') || 
                error.message.includes('stock')
            );
            showNotification(
                'Lỗi hệ thống: ' + error.message,
                'error',
                isStockError ? 'Tồn kho không đủ' : 'Lỗi hệ thống'
            );
        })
        .finally(() => {
            // Restore button state
            this.disabled = false;
            this.innerHTML = originalText;
            modal.hide();
            currentOrderId = null;
        });
    });
});
</script>

<?php include 'views/layouts/footer.php'; ?>