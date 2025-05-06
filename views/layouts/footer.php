        </div>
    </main>

    <!-- Newsletter -->
    <section class="newsletter-section py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h3>Đăng ký nhận tin</h3>
                    <p class="mb-4">Nhận thông tin cập nhật, mã giảm giá độc quyền và nhiều ưu đãi khác.</p>
                    <form action="#" method="POST" class="newsletter-form">
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="Địa chỉ email của bạn" required>
                            <button class="btn btn-primary" type="submit">Đăng ký</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h5 class="mb-4">Babee Store</h5>
                    <p class="text-muted">Quần áo trẻ em chất lượng với giá cả phải chăng. Chúng tôi cung cấp những sản phẩm tốt nhất cho bé yêu của bạn.</p>
                    <ul class="list-inline social-links">
                        <li class="list-inline-item"><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                        <li class="list-inline-item"><a href="#"><i class="fab fa-instagram"></i></a></li>
                        <li class="list-inline-item"><a href="#"><i class="fab fa-twitter"></i></a></li>
                        <li class="list-inline-item"><a href="#"><i class="fab fa-pinterest"></i></a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h5 class="mb-4">Liên kết nhanh</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-decoration-none">Trang chủ</a></li>
                        <li class="mb-2"><a href="index.php?controller=product&action=list" class="text-decoration-none">Cửa hàng</a></li>
                        <li class="mb-2"><a href="index.php?controller=product&action=list&is_sale=1" class="text-decoration-none">Khuyến mãi</a></li>
                        <li class="mb-2"><a href="index.php?controller=home&action=about" class="text-decoration-none">Giới thiệu</a></li>
                        <li class="mb-2"><a href="index.php?controller=home&action=contact" class="text-decoration-none">Liên hệ</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h5 class="mb-4">Dịch vụ khách hàng</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php?controller=order&action=track" class="text-decoration-none">Theo dõi đơn hàng</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none">Chính sách vận chuyển</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none">Chính sách đổi trả</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none">Câu hỏi thường gặp</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none">Chính sách bảo mật</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h5 class="mb-4">Liên hệ với chúng tôi</h5>
                    <ul class="list-unstyled contact-info">
                        <?php
                        $settings = new Settings($conn);
                        $contact_address = $settings->getValue('contact_address', '');
                        $contact_phone = $settings->getValue('contact_phone', '');
                        $contact_email = $settings->getValue('contact_email', ADMIN_EMAIL);
                        ?>
                        <?php if(!empty($contact_address)): ?>
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> <?php echo $contact_address; ?></li>
                        <?php endif; ?>
                        <?php if(!empty($contact_phone)): ?>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> <?php echo $contact_phone; ?></li>
                        <?php endif; ?>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> <?php echo $contact_email; ?></li>
                        <li class="mb-2"><i class="fas fa-clock me-2"></i> Thứ Hai - Thứ Sáu: 9:00 - 18:00</li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Babee Store. Tất cả các quyền được bảo lưu.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <img src="https://via.placeholder.com/300x35/ffffff/888888?text=Phương+thức+thanh+toán" alt="Phương thức thanh toán" class="img-fluid payment-methods">
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (required for some plugins) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>
