<?php 
$page_title = "Xác minh hủy đơn hàng";
include 'views/layouts/header.php'; 
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="mb-4">Xác minh hủy đơn hàng</h1>
            
            <?php if (isset($_SESSION['order_message'])): ?>
                <div class="alert alert-info">
                    <?php echo htmlspecialchars($_SESSION['order_message']); ?>
                    <?php unset($_SESSION['order_message']); ?>
                </div>
            <?php endif; ?>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <p>Vui lòng nhập mã đơn hàng và email để xác minh quyền hủy đơn hàng.</p>
                    <form action="index.php?controller=order&action=cancel&id=<?php echo $this->order->id; ?>" method="POST">
                        <div class="mb-3">
                            <label for="order_number" class="form-label">Mã đơn hàng</label>
                            <input type="text" class="form-control" id="order_number" name="order_number" value="<?php echo htmlspecialchars($this->order->order_number); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="customer_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="customer_email" name="customer_email" required>
                        </div>
                        <div class="text-end">
                            <a href="index.php?controller=order&action=success&id=<?php echo $this->order->id; ?>" class="btn btn-secondary me-2">Quay lại</a>
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?');">Xác nhận hủy</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>