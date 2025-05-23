<?php
$page_title = "Chi tiết đơn hàng";
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
                        <a href="index.php?controller=user&action=orders" class="list-group-item list-group-item-action active rounded py-3 px-4 mb-1">
                            <i class="fas fa-shopping-bag fa-lg me-2"></i> Đơn hàng của tôi
                        </a>
                        <a href="index.php?controller=user&action=changePassword" class="list-group-item list-group-item-action rounded py-3 px-4 mb-1">
                            <i class="fas fa-key fa-lg me-2"></i> Đổi mật khẩu
                        </a>
                        <a href="index.php?controller=user&action=logout" class="list-group-item list-group-item-action text-danger rounded py-3 px-4 mb-1">
                            <i class="fas fa-sign-out-alt fa-lg me-2"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details Content -->
        <div class="col-md-9">
            <div class="card border-0 shadow-sm mb-4 rounded">
                <div class="card-header bg-primary text-white rounded-top d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Đơn hàng #<?php echo htmlspecialchars($order->order_number); ?></h5>
                    <a href="index.php?controller=user&action=orders" class="btn btn-sm btn-light rounded-pill">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại đơn hàng
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i>Thông tin đơn hàng</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0"><strong>Mã đơn hàng:</strong> <?php echo htmlspecialchars($order->order_number); ?></li>
                                <li class="list-group-item px-0"><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order->created_at)); ?></li>
                                <li class="list-group-item px-0">
                                    <strong>Trạng thái:</strong>
                                    <?php
                                    $status_text = '';
                                    $status_class = '';
                                    switch ($order->status) {
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
                                </li>
                                <li class="list-group-item px-0"><strong>Phương thức thanh toán:</strong> <?php echo ucfirst($order->payment_method); ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-3"><i class="fas fa-map-marker-alt me-2"></i>Thông tin giao hàng</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order->shipping_address); ?></li>
                                <li class="list-group-item px-0"><strong>Thành phố:</strong> <?php echo htmlspecialchars($order->shipping_city); ?></li>
                                <li class="list-group-item px-0"><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order->shipping_phone); ?></li>
                                <?php if (!empty($order->notes)): ?>
                                    <li class="list-group-item px-0"><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order->notes); ?></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    <h6 class="text-muted mb-3"><i class="fas fa-shopping-cart me-2"></i>Sản phẩm trong đơn hàng</h6>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Số lượng</th>
                                    <th>Tổng</th>
                                    <th>Đánh giá</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $subtotal = 0;
                                while ($item = $order_items->fetch(PDO::FETCH_ASSOC)):
                                    $item_total = $item['price'] * $item['quantity'];
                                    $subtotal += $item_total;
                                ?>
                                    <tr class="rounded">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" class="img-thumbnail me-2" alt="<?php echo htmlspecialchars($item['product_name']); ?>" style="width: 60px; border: 1px solid #e9ecef;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center me-2" style="width: 60px; height: 60px; border: 1px solid #e9ecef;">
                                                        <i class="fas fa-tshirt text-secondary fa-2x"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($item['product_name']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo (defined('CURRENCY') ? CURRENCY : '₫') . number_format($item['price']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo (defined('CURRENCY') ? CURRENCY : '₫') . number_format($item_total); ?></td>
                                        <td>
                                            <?php if ($order->status == 'delivered'): ?>
                                                <button class="btn btn-primary btn-sm rounded-pill feedback-btn"
                                                    data-product-id="<?php echo $item['product_id']; ?>"
                                                    data-order-id="<?php echo $order->id; ?>"
                                                    data-product-name="<?php echo htmlspecialchars($item['product_name']); ?>">
                                                    <i class="fas fa-star me-1"></i> Đánh giá
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Tạm tính:</td>
                                    <td colspan="2"><?php echo (defined('CURRENCY') ? CURRENCY : '₫') . number_format($subtotal); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Phí vận chuyển:</td>
                                    <td colspan="2">Miễn phí</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Tổng cộng:</td>
                                    <td colspan="2" class="fw-bold"><?php echo (defined('CURRENCY') ? CURRENCY : '₫') . number_format($order->total_amount); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <?php if ($order->status == 'pending'): ?>
                        <div class="text-end mt-3">
                            <button class="btn btn-danger rounded-pill px-4 cancel-order-btn" data-id="<?php echo $order->id; ?>" data-order-number="<?php echo htmlspecialchars($order->order_number); ?>">
                                <i class="fas fa-times me-1"></i> Hủy đơn hàng
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Timeline -->
            <div class="card border-0 shadow-sm rounded">
                <div class="card-header bg-light rounded-top">
                    <h5 class="mb-0">Tiến trình đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="order-timeline">
                        <div class="timeline-item animate__animated animate__fadeInUp" style="animation-delay: 0.05s;">
                            <div class="timeline-icon <?php echo in_array($order->status, ['pending', 'processing', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                                <i class="fas fa-shopping-cart fa-lg"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Đã đặt hàng</h6>
                                <p class="text-muted"><?php echo date('d/m/Y H:i', strtotime($order->created_at)); ?></p>
                            </div>
                        </div>
                        <div class="timeline-item animate__animated animate__fadeInUp" style="animation-delay: 0.10s;">
                            <div class="timeline-icon <?php echo in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                                <i class="fas fa-cogs fa-lg"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Đang xử lý</h6>
                                <p class="text-muted">Đơn hàng đã được xác nhận và đang xử lý</p>
                            </div>
                        </div>
                        <div class="timeline-item animate__animated animate__fadeInUp" style="animation-delay: 0.15s;">
                            <div class="timeline-icon <?php echo in_array($order->status, ['shipped', 'delivered']) ? 'active' : ''; ?>">
                                <i class="fas fa-truck fa-lg"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Đã giao hàng</h6>
                                <p class="text-muted">Đơn hàng đã được gửi đi</p>
                            </div>
                        </div>
                        <div class="timeline-item animate__animated animate__fadeInUp" style="animation-delay: 0.20s;">
                            <div class="timeline-icon <?php echo $order->status == 'delivered' ? 'active' : ''; ?>">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Hoàn thành</h6>
                                <p class="text-muted">Đơn hàng đã được giao thành công</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận hủy đơn hàng -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded">
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

<!-- Modal đánh giá sản phẩm -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded">
            <div class="modal-header">
                <h5 class="modal-title" id="feedbackModalLabel">Đánh giá sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="feedback-form" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" id="feedback-product-id">
                    <input type="hidden" name="order_id" id="feedback-order-id">

                    <div class="mb-3">
                        <label class="form-label">Sản phẩm:</label>
                        <span id="feedback-product-name" class="fw-bold"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Đánh giá:</label>
                        <div class="rating">
                            <i class="far fa-star" data-rating="1"></i>
                            <i class="far fa-star" data-rating="2"></i>
                            <i class="far fa-star" data-rating="3"></i>
                            <i class="far fa-star" data-rating="4"></i>
                            <i class="far fa-star" data-rating="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="rating-value" required>
                    </div>

                    <div class="mb-3">
                        <label for="feedback-content" class="form-label">Nội dung đánh giá:</label>
                        <textarea class="form-control" id="feedback-content" name="content" rows="3"
                            minlength="10" maxlength="500" required></textarea>
                        <div class="form-text">Tối thiểu 10 ký tự, tối đa 500 ký tự</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hình ảnh (tối đa 3 ảnh):</label>
                        <input type="file" class="form-control" id="feedback-images" name="images[]"
                            multiple accept="image/jpeg,image/png,image/gif" />
                        <div class="form-text">Định dạng: JPG, PNG, GIF. Kích thước tối đa: 5MB/ảnh</div>
                        <div id="image-preview" class="mt-2 d-flex gap-2 flex-wrap"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary rounded-pill" id="submit-feedback">Gửi đánh giá</button>
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

    /* List group for info */
    .list-group-item {
        border: none;
        padding: 0.5rem 0;
    }

    /* Table styles */
    .table tr {
        transition: all 0.2s ease;
    }

    .table tr:hover {
        background-color: #f8f9fa;
    }

    .table td,
    .table th {
        vertical-align: middle;
    }

    .table .img-thumbnail {
        border-radius: 8px;
    }

    .table tfoot td {
        font-weight: 600;
    }

    .badge {
        font-size: 0.9rem;
    }

    /* Button styles */
    .btn-primary,
    .btn-danger,
    .btn-light {
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

    .btn-light:hover {
        background-color: #e9ecef;
    }

    .btn-primary::after,
    .btn-danger::after,
    .btn-light::after {
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

    .btn-primary:active::after,
    .btn-danger:active::after,
    .btn-light:active::after {
        width: 200px;
        height: 200px;
    }

    .btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    /* Timeline styles */
    .order-timeline {
        position: relative;
        padding-left: 40px;
    }

    .order-timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 24px;
        transition: background-color 0.2s ease;
    }

    .timeline-item:hover {
        background-color: #f8f9fa;
        border-radius: 6px;
    }

    .timeline-icon {
        position: absolute;
        left: -40px;
        top: 0;
        width: 32px;
        height: 32px;
        background: #dee2e6;
        border: 2px solid #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #495057;
        transition: all 0.3s ease;
    }

    .timeline-icon.active {
        background: #007bff;
        color: #fff;
    }

    .timeline-icon i {
        font-size: 1.1rem;
    }

    .timeline-content {
        padding-left: 40px;
    }

    .timeline-content h6 {
        margin-bottom: 5px;
        font-weight: 600;
        font-size: 1rem;
    }

    .timeline-content p.text-muted {
        font-size: 0.85rem;
        margin-bottom: 0;
    }

    /* Modal styles */
    .modal-content {
        border-radius: 12px;
    }

    /* Rating stars styles */
    .rating {
        display: flex;
        gap: 5px;
        font-size: 24px;
        color: #ffc107;
        cursor: pointer;
    }

    .rating i:hover,
    .rating i.fas {
        color: #ffc107;
    }

    /* Image preview styles */
    #image-preview img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 4px;
    }

    /* Notification styles */
    .notification {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        animation: slideInRight 0.3s ease-in-out;
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 2000;
        min-width: 300px;
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
            padding: 0.6rem 1rem;
        }

        .table {
            font-size: 0.9rem;
        }

        .btn-sm,
        .btn {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
        }

        .badge {
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
        }

        .timeline-icon {
            width: 28px;
            height: 28px;
            left: -38px;
        }

        .timeline-icon i {
            font-size: 1rem;
        }

        .order-timeline {
            padding-left: 38px;
        }

        .order-timeline::before {
            left: 13px;
            width: 3px;
        }

        .timeline-content {
            padding-left: 20px;
        }

        .timeline-content h6 {
            font-size: 0.95rem;
        }

        .timeline-content p.text-muted {
            font-size: 0.8rem;
        }

        .notification {
            min-width: 250px;
            top: 10px;
            right: 10px;
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

            .table {
                font-size: 0.85rem;
            }

            .table .img-thumbnail,
            .table .bg-light {
                width: 50px;
                height: 50px;
            }

            .btn-sm,
            .btn {
                font-size: 0.75rem;
                padding: 0.3rem 0.6rem;
            }

            .badge {
                font-size: 0.75rem;
                padding: 0.4rem 0.8rem;
            }

            .timeline-icon {
                width: 26px;
                height: 26px;
                left: -36px;
            }

            .timeline-icon i {
                font-size: 0.9rem;
            }

            .order-timeline {
                padding-left: 36px;
            }

            .order-timeline::before {
                left: 11px;
                width: 3px;
            }

            .timeline-content {
                padding-left: 16px;
            }

            .timeline-content h6 {
                font-size: 0.9rem;
            }

            .timeline-content p.text-muted {
                font-size: 0.8rem;
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

        // Handle cancel order
        const cancelButton = document.querySelector('.cancel-order-btn');
        const modal = new bootstrap.Modal(document.getElementById('cancelOrderModal'));
        const confirmCancelBtn = document.getElementById('confirm-cancel-btn');
        let currentOrderId = null;

        if (cancelButton) {
            cancelButton.addEventListener('click', function() {
                currentOrderId = this.dataset.id;
                document.getElementById('order-number').textContent = this.dataset.orderNumber;
                modal.show();
            });
        }

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
                        const statusBadge = document.querySelector('.badge');
                        statusBadge.className = 'badge bg-danger rounded-pill py-2 px-3';
                        statusBadge.textContent = 'Đã hủy';
                        document.querySelector('.cancel-order-btn').remove();
                        // Update timeline
                        document.querySelectorAll('.timeline-icon').forEach(icon => {
                            icon.classList.remove('active');
                        });
                        document.querySelector('.timeline-item:first-child .timeline-icon').classList.add('active');
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

        // Feedback functionality
        const feedbackModal = new bootstrap.Modal(document.getElementById('feedbackModal'));
        const feedbackForm = document.getElementById('feedback-form');
        const ratingStars = document.querySelectorAll('.rating i');
        const imageInput = document.getElementById('feedback-images');
        const imagePreview = document.getElementById('image-preview');
        const submitFeedbackBtn = document.getElementById('submit-feedback');

        // Handle feedback button click
        document.querySelectorAll('.feedback-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const orderId = this.dataset.orderId;
                const productName = this.dataset.productName;

                document.getElementById('feedback-product-id').value = productId;
                document.getElementById('feedback-order-id').value = orderId;
                document.getElementById('feedback-product-name').textContent = productName;

                feedbackModal.show();
            });
        });

        // Handle rating selection
        ratingStars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                document.getElementById('rating-value').value = rating;

                ratingStars.forEach(s => {
                    if (s.dataset.rating <= rating) {
                        s.classList.remove('far');
                        s.classList.add('fas');
                    } else {
                        s.classList.remove('fas');
                        s.classList.add('far');
                    }
                });
            });

            star.addEventListener('mouseenter', function() {
                const rating = this.dataset.rating;
                ratingStars.forEach(s => {
                    if (s.dataset.rating <= rating) {
                        s.classList.remove('far');
                        s.classList.add('fas');
                    }
                });
            });

            star.addEventListener('mouseleave', function() {
                const currentRating = document.getElementById('rating-value').value;
                ratingStars.forEach(s => {
                    if (!currentRating || s.dataset.rating > currentRating) {
                        s.classList.remove('fas');
                        s.classList.add('far');
                    }
                });
            });
        });

        // Handle image preview
        imageInput.addEventListener('change', function() {
            if (this.files.length > 3) {
                showNotification('Chỉ được chọn tối đa 3 ảnh', 'error');
                this.value = '';
                return;
            }

            imagePreview.innerHTML = '';
            Array.from(this.files).forEach(file => {
                if (file.size > 5 * 1024 * 1024) {
                    showNotification('Kích thước ảnh không được vượt quá 5MB', 'error');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = e => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    imagePreview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });

        // Handle form submission
        submitFeedbackBtn.addEventListener('click', function() {
            if (!feedbackForm.checkValidity()) {
                feedbackForm.reportValidity();
                return;
            }

            const formData = new FormData(feedbackForm);
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang gửi...';

            $.ajax({
                url: 'index.php?controller=feedback&action=submit',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                        if (data.success) {
                            showNotification(data.message, 'success');
                            feedbackModal.hide();
                            // Disable the feedback button for this product
                            const productId = formData.get('product_id');
                            const feedbackBtn = document.querySelector(`.feedback-btn[data-product-id="${productId}"]`);
                            if (feedbackBtn) {
                                feedbackBtn.disabled = true;
                                feedbackBtn.innerHTML = '<i class="fas fa-check me-1"></i> Đã đánh giá';
                            }
                        } else {
                            showNotification(data.message || 'Không thể gửi đánh giá', 'error');
                        }
                    } catch (e) {
                        showNotification('Lỗi xử lý phản hồi từ server', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showNotification('Lỗi kết nối: ' + error, 'error');
                },
                complete: function() {
                    submitFeedbackBtn.disabled = false;
                    submitFeedbackBtn.innerHTML = originalText;
                }
            });
        });

        // Reset form when modal is closed
        document.getElementById('feedbackModal').addEventListener('hidden.bs.modal', function() {
            feedbackForm.reset();
            imagePreview.innerHTML = '';
            document.getElementById('rating-value').value = '';
            ratingStars.forEach(star => {
                star.classList.remove('fas');
                star.classList.add('far');
            });
        });
    });
</script>

<?php include 'views/layouts/footer.php'; ?>