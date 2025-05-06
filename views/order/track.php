<?php 
$page_title = "Theo dõi đơn hàng";
include 'views/layouts/header.php'; 
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="mb-4 text-center">Theo dõi đơn hàng</h1>
                    
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if(empty($order_data)): ?>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <p class="text-center mb-4">Nhập mã đơn hàng để theo dõi tình trạng đơn hàng của bạn.</p>
                                <form method="post" action="index.php?controller=order&action=track" class="mb-5">
                                    <div class="mb-3">
                                        <label for="order_number" class="form-label">Mã đơn hàng</label>
                                        <input type="text" class="form-control" id="order_number" name="order_number" placeholder="Ví dụ: ORD-12345678" required>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg">Theo dõi đơn hàng</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Order Information -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">Thông tin đơn hàng</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Mã đơn hàng:</strong> <?php echo htmlspecialchars($order_data['order']->order_number); ?></p>
                                        <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y', strtotime($order_data['order']->created_at)); ?></p>
                                        <p><strong>Tổng tiền:</strong> <?php echo CURRENCY . number_format($order_data['order']->total_amount); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Trạng thái:</strong> 
                                            <span class="badge <?php 
                                                switch($order_data['order']->status) {
                                                    case 'pending': echo 'bg-warning'; break;
                                                    case 'processing': echo 'bg-info'; break;
                                                    case 'shipped': echo 'bg-primary'; break;
                                                    case 'delivered': echo 'bg-success'; break;
                                                    case 'cancelled': echo 'bg-danger'; break;
                                                    default: echo 'bg-secondary';
                                                }
                                            ?>">
                                                <?php 
                                                switch($order_data['order']->status) {
                                                    case 'pending': echo 'Chờ xử lý'; break;
                                                    case 'processing': echo 'Đang xử lý'; break;
                                                    case 'shipped': echo 'Đã giao hàng'; break;
                                                    case 'delivered': echo 'Đã nhận hàng'; break;
                                                    case 'cancelled': echo 'Đã hủy'; break;
                                                    default: echo ucfirst($order_data['order']->status);
                                                }
                                                ?>
                                            </span>
                                        </p>
                                        <p><strong>Phương thức thanh toán:</strong> <?php echo htmlspecialchars($order_data['order']->payment_method); ?></p>
                                        <p><strong>Địa chỉ giao hàng:</strong> <?php echo htmlspecialchars($order_data['order']->shipping_address); ?>, <?php echo htmlspecialchars($order_data['order']->shipping_city); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order Timeline -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">Thời gian xử lý đơn hàng</h5>
                            </div>
                            <div class="card-body py-4">
                                <div class="timeline">
                                    <?php 
                                    // Define all possible statuses
                                    $statuses = ['pending', 'processing', 'shipped', 'delivered'];
                                    $currentStatusIndex = array_search($order_data['order']->status, $statuses);
                                    
                                    // If order is cancelled, show different timeline
                                    if($order_data['order']->status === 'cancelled'): 
                                    ?>
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-success"></div>
                                            <div class="timeline-content">
                                                <h6 class="mb-1">Đã đặt hàng</h6>
                                                <p class="small text-muted mb-0"><?php echo date('d/m/Y', strtotime($order_data['order']->created_at)); ?></p>
                                            </div>
                                        </div>
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-danger"></div>
                                            <div class="timeline-content">
                                                <h6 class="mb-1">Đã hủy đơn hàng</h6>
                                                <p class="small text-muted mb-0"><?php echo date('d/m/Y', strtotime($order_data['order']->updated_at)); ?></p>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach($statuses as $index => $status): ?>
                                            <div class="timeline-item">
                                                <div class="timeline-marker <?php echo ($index <= $currentStatusIndex) ? 'bg-success' : 'bg-secondary'; ?>"></div>
                                                <div class="timeline-content">
                                                    <h6 class="mb-1"><?php 
                                                        switch($status) {
                                                            case 'pending': echo 'Đã đặt hàng'; break;
                                                            case 'processing': echo 'Đang xử lý'; break;
                                                            case 'shipped': echo 'Đã giao hàng'; break;
                                                            case 'delivered': echo 'Đã nhận hàng'; break;
                                                            default: echo ucfirst($status);
                                                        }
                                                    ?></h6>
                                                    <?php if($index <= $currentStatusIndex): ?>
                                                        <p class="small text-muted mb-0"><?php 
                                                            if($index === 0) {
                                                                echo date('d/m/Y', strtotime($order_data['order']->created_at));
                                                            } elseif($index === $currentStatusIndex) {
                                                                echo date('d/m/Y', strtotime($order_data['order']->updated_at));
                                                            } else {
                                                                // For intermediate steps, add some estimated dates
                                                                $daysToAdd = $index * 2; // 2 days per step
                                                                $date = strtotime($order_data['order']->created_at . " +{$daysToAdd} days");
                                                                echo date('d/m/Y', $date);
                                                            }
                                                        ?></p>
                                                    <?php else: ?>
                                                        <p class="small text-muted mb-0">Chờ xử lý</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order Items -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">Sản phẩm đã đặt</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Sản phẩm</th>
                                                <th class="text-center">Số lượng</th>
                                                <th class="text-end">Giá</th>
                                                <th class="text-end">Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($order_data['items'] as $item): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                                                    <td class="text-end"><?php echo CURRENCY . number_format($item['price']); ?></td>
                                                    <td class="text-end"><?php echo CURRENCY . number_format($item['price'] * $item['quantity']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="3" class="text-end">Tổng cộng:</th>
                                                <th class="text-end"><?php echo CURRENCY . number_format($order_data['order']->total_amount); ?></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <a href="index.php" class="btn btn-outline-secondary">Tiếp tục mua sắm</a>
                            <?php if($order_data['order']->status === 'pending'): ?>
                                <a href="index.php?controller=order&action=cancel&id=<?php echo $order_data['order']->id; ?>" class="btn btn-danger ms-2" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?');">Hủy đơn hàng</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Timeline styles */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    left: 15px;
    height: 100%;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    top: 6px;
    left: -30px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 3px #e9ecef;
}

.timeline-marker.bg-success {
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
}

.timeline-marker.bg-danger {
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2);
}
</style>

<?php include 'views/layouts/footer.php'; ?>