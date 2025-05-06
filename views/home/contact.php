<?php 
$page_title = "Liên hệ";
include 'views/layouts/header.php'; 
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h1 class="display-5 mb-4 text-center">Liên hệ với chúng tôi</h1>
                    
                    <?php if(!empty($success)): ?>
                    <div class="alert alert-success mb-4">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($error)): ?>
                    <div class="alert alert-danger mb-4">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row mb-5">
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <div class="contact-icon-box">
                                <div class="icon-wrapper mb-3">
                                    <i class="fas fa-map-marker-alt fa-3x text-primary"></i>
                                </div>
                                <h5>Địa chỉ</h5>
                                <p class="text-muted"><?php echo !empty($contact_address) ? htmlspecialchars($contact_address) : 'Số 123 Đường ABC, Quận 1, TP. Hồ Chí Minh, Việt Nam'; ?></p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <div class="contact-icon-box">
                                <div class="icon-wrapper mb-3">
                                    <i class="fas fa-phone fa-3x text-success"></i>
                                </div>
                                <h5>Điện thoại</h5>
                                <p class="text-muted"><?php echo !empty($contact_phone) ? htmlspecialchars($contact_phone) : '+84 123 456 789'; ?></p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="contact-icon-box">
                                <div class="icon-wrapper mb-3">
                                    <i class="fas fa-envelope fa-3x text-danger"></i>
                                </div>
                                <h5>Email</h5>
                                <p class="text-muted"><?php echo htmlspecialchars($contact_email); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <form action="index.php?controller=home&action=contact" method="POST" class="contact-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-4">
                            <label for="message" class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">Gửi tin nhắn</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Map Section -->
<div class="container mb-5">
    <div class="row">
        <div class="col-12">
            <div class="map-container rounded shadow">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4241674797607!2d106.69881707590795!3d10.777185989360447!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f4670702e31%3A0xe4b6e61c5150e13a!2sSaigon%20Centre!5e0!3m2!1sen!2s!4v1682419804890!5m2!1sen!2s" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>