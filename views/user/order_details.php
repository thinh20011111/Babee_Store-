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
                        </table>
                    </div>
                </div>
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
                    <input type="hidden" name="product_id" id="product_id">
                    <input type="hidden" name="order_id" id="order_id">

                    <div class="mb-3">
                        <label class="form-label">Sản phẩm:</label>
                        <div id="product-name" class="fw-bold"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Đánh giá:</label>
                        <div class="rating">
                            <i class="far fa-star fs-4 me-1" data-rating="1"></i>
                            <i class="far fa-star fs-4 me-1" data-rating="2"></i>
                            <i class="far fa-star fs-4 me-1" data-rating="3"></i>
                            <i class="far fa-star fs-4 me-1" data-rating="4"></i>
                            <i class="far fa-star fs-4" data-rating="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="rating" required>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Nhận xét:</label>
                        <textarea class="form-control" id="content" name="content" rows="3" minlength="10" maxlength="500" required></textarea>
                        <div class="form-text">Tối thiểu 10 ký tự, tối đa 500 ký tự</div>
                    </div>

                    <div class="mb-3">
                        <label for="photos" class="form-label">Hình ảnh (tối đa 3 ảnh):</label>
                        <input type="file" class="form-control" id="photos" name="photos[]" accept="image/*" multiple>
                        <div class="form-text">Chấp nhận các định dạng: JPG, PNG, GIF. Kích thước tối đa: 5MB/ảnh</div>
                        <div id="preview" class="d-flex gap-2 mt-2"></div>
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

<!-- CSS cho rating stars -->
<style>
    .rating {
        cursor: pointer;
    }

    .rating i {
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .rating i:hover,
    .rating i.active {
        color: #ffc107;
    }

    #preview img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
    }
</style>

<!-- JavaScript cho xử lý đánh giá -->
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

        // Xử lý modal đánh giá
        const feedbackModal = new bootstrap.Modal(document.getElementById('feedbackModal'));
        const feedbackForm = document.getElementById('feedback-form');
        const ratingStars = document.querySelectorAll('.rating i');
        const ratingInput = document.getElementById('rating');
        const photoInput = document.getElementById('photos');
        const previewDiv = document.getElementById('preview');

        // Xử lý nút đánh giá
        document.querySelectorAll('.feedback-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('product_id').value = this.dataset.productId;
                document.getElementById('order_id').value = this.dataset.orderId;
                document.getElementById('product-name').textContent = this.dataset.productName;
                resetForm();
                feedbackModal.show();
            });
        });

        // Xử lý rating stars
        ratingStars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                ratingInput.value = rating;
                ratingStars.forEach(s => {
                    if (s.dataset.rating <= rating) {
                        s.classList.remove('far');
                        s.classList.add('fas');
                        s.classList.add('active');
                    } else {
                        s.classList.add('far');
                        s.classList.remove('fas');
                        s.classList.remove('active');
                    }
                });
            });
        });

        // Xử lý preview ảnh
        photoInput.addEventListener('change', function() {
            if (this.files.length > 3) {
                showNotification('Chỉ được chọn tối đa 3 ảnh', 'error');
                this.value = '';
                return;
            }

            previewDiv.innerHTML = '';
            Array.from(this.files).forEach(file => {
                if (file.size > 5 * 1024 * 1024) {
                    showNotification(`Ảnh ${file.name} vượt quá 5MB`, 'error');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    previewDiv.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        });

        // Xử lý submit form
        document.getElementById('submit-feedback').addEventListener('click', function() {
            if (!feedbackForm.checkValidity()) {
                feedbackForm.reportValidity();
                return;
            }

            if (!ratingInput.value) {
                showNotification('Vui lòng chọn số sao đánh giá', 'error');
                return;
            }

            const formData = new FormData(feedbackForm);
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang gửi...';

            fetch('index.php?controller=feedback&action=submit', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        feedbackModal.hide();
                        // Disable the feedback button for this product
                        const btn = document.querySelector(`.feedback-btn[data-product-id="${formData.get('product_id')}"]`);
                        if (btn) {
                            btn.disabled = true;
                            btn.innerHTML = '<i class="fas fa-check me-1"></i> Đã đánh giá';
                        }
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    showNotification('Có lỗi xảy ra khi gửi đánh giá', 'error');
                })
                .finally(() => {
                    this.disabled = false;
                    this.innerHTML = 'Gửi đánh giá';
                });
        });

        // Reset form
        function resetForm() {
            feedbackForm.reset();
            ratingInput.value = '';
            previewDiv.innerHTML = '';
            ratingStars.forEach(star => {
                star.classList.add('far');
                star.classList.remove('fas');
                star.classList.remove('active');
            });
        }
    });
</script>

<?php include 'views/layouts/footer.php'; ?>