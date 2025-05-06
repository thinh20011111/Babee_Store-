<?php 
$page_title = "Giới thiệu";
include 'views/layouts/header.php'; 
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h1 class="display-5 mb-4 text-center">Về Babee Store</h1>
                    
                    <?php if(!empty($about_content)): ?>
                        <?php echo nl2br(htmlspecialchars($about_content)); ?>
                    <?php else: ?>
                        <div class="row mb-5">
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <div class="about-icon-box">
                                    <div class="icon-wrapper mb-3">
                                        <i class="fas fa-baby fa-3x text-primary"></i>
                                    </div>
                                    <h5>Chất lượng tốt nhất</h5>
                                    <p class="text-muted">Chúng tôi cung cấp quần áo chất lượng cao, an toàn và thoải mái cho bé yêu của bạn.</p>
                                </div>
                            </div>
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <div class="about-icon-box">
                                    <div class="icon-wrapper mb-3">
                                        <i class="fas fa-heart fa-3x text-danger"></i>
                                    </div>
                                    <h5>Làm với tình yêu</h5>
                                    <p class="text-muted">Mỗi sản phẩm được chọn lựa và thiết kế cẩn thận với tình yêu và sự quan tâm.</p>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="about-icon-box">
                                    <div class="icon-wrapper mb-3">
                                        <i class="fas fa-leaf fa-3x text-success"></i>
                                    </div>
                                    <h5>Thân thiện với môi trường</h5>
                                    <p class="text-muted">Chúng tôi cam kết sử dụng vật liệu bền vững và thân thiện với môi trường.</p>
                                </div>
                            </div>
                        </div>
                        
                        <h3 class="mb-3">Câu chuyện của chúng tôi</h3>
                        <p>Babee Store được thành lập vào năm 2015 bởi hai người mẹ đam mê thời trang trẻ em chất lượng cao. Chúng tôi nhận thấy sự thiếu hụt của quần áo trẻ em vừa đẹp vừa thoải mái, vừa an toàn cho làn da nhạy cảm của trẻ nhỏ.</p>
                        
                        <p>Tầm nhìn của chúng tôi rất đơn giản: cung cấp quần áo trẻ em chất lượng cao, thiết kế đẹp mắt với giá cả phải chăng. Chúng tôi tin rằng mọi đứa trẻ đều xứng đáng mặc những bộ quần áo thoải mái, an toàn và đáng yêu.</p>
                        
                        <h3 class="mb-3">Sứ mệnh của chúng tôi</h3>
                        <p>Tại Babee Store, sứ mệnh của chúng tôi là truyền cảm hứng và nuôi dưỡng sự sáng tạo và niềm vui của trẻ em qua thời trang. Chúng tôi cam kết:</p>
                        
                        <ul>
                            <li>Cung cấp quần áo chất lượng cao, an toàn và thoải mái</li>
                            <li>Sử dụng vật liệu bền vững và thân thiện với môi trường</li>
                            <li>Hỗ trợ các gia đình cần giúp đỡ thông qua các chương trình từ thiện</li>
                            <li>Đảm bảo điều kiện làm việc công bằng và đạo đức trong chuỗi cung ứng của chúng tôi</li>
                        </ul>
                        
                        <h3 class="mb-3">Đội ngũ của chúng tôi</h3>
                        <p>Đội ngũ Babee Store bao gồm các nhà thiết kế, chuyên gia thời trang và những người cha mẹ đam mê tạo ra những sản phẩm tốt nhất cho con em bạn. Chúng tôi hiểu rằng quần áo trẻ em cần phải chịu được sự vận động và khám phá không ngừng, đồng thời phải đảm bảo sự an toàn và thoải mái.</p>
                    <?php endif; ?>
                    
                    <div class="text-center mt-5">
                        <a href="index.php?controller=home&action=contact" class="btn btn-primary">Liên hệ với chúng tôi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>