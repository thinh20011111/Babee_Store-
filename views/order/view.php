<?php 
$page_title = "Đặt hàng thành công";
include 'views/layouts/header.php'; 
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h1 class="mb-4">Đặt hàng thành công</h1>
            
            <div class="alert alert-success mb-4">
                Cảm ơn bạn đã đặt hàng! Đơn hàng của bạn đã được ghi nhận.
            </div>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Mã đơn hàng:</strong> <?php echo htmlspecialchars($this->order->order_number); ?></p>
                            <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y', strtotime($this->order->created_at)); ?></p>
                            <p><strong>Tổng tiền:</strong> <?php echo CURRENCY . number_format($this->order->total_amount, 0, ',', '.'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Trạng thái:</strong> 
                                <span class="badge <?php 
                                    switch($this->order->status) {
                                        case 'pending': echo 'bg-warning'; break;
                                        case 'processing': echo 'bg-info'; break;
                                        case 'shipped': echo 'bg-primary'; break;
                                        case 'delivered': echo 'bg-success'; break;
                                        case 'cancelled': echo 'bg-danger'; break;
                                        default: echo 'bg-secondary';
                                    }
                                ?>">
                                    <?php echo ucfirst($this->order->status); ?>
                                </span>
                            </p>
                            <p><strong>Phương thức thanh toán:</strong> <?php echo htmlspecialchars($this->order->payment_method); ?></p>
                            <p><strong>Địa chỉ giao hàng:</strong> <?php echo htmlspecialchars($this->order->shipping_address . ', ' . $this->order->shipping_city); ?></p>
                            <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($this->order->shipping_phone); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Sản phẩm trong đơn hàng</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end">Giá</th>
                                    <th class="text-end">Tổng cộng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($order_items->rowCount() > 0): ?>
                                    <?php while($item = $order_items->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if(!empty($item['image'])): ?>
                                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                                            <i class="fas fa-tshirt text-secondary"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                        <?php if(!empty($item['category_name'])): ?>
                                                            <small class="text-muted"><?php echo htmlspecialchars($item['category_name']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                                            <td class="text-end"><?php echo CURRENCY . number_format($item['price'], 0, ',', '.'); ?></td>
                                            <td class="text-end"><?php echo CURRENCY . number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Không có sản phẩm trong đơn hàng.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Tổng cộng:</th>
                                    <th class="text-end"><?php echo CURRENCY . number_format($this->order->total_amount, 0, ',', '.'); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <?php if(!empty($this->order->notes)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Ghi chú đơn hàng</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($this->order->notes)); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="text-center">
                <a href="index.php" class="btn btn-primary me-2">Tiếp tục mua sắm</a>
                <a href="index.php?controller=user&action=orders" class="btn btn-outline-secondary">Xem đơn hàng</a>
                <?php if($this->order->status === 'pending'): ?>
                    <a href="index.php?controller=order&action=cancel&id=<?php echo $this->order->id; ?>" class="btn btn-danger ms-2" onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?');">Hủy đơn hàng</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>