</main>

    <!-- Footer - Bold, Modern and Clean -->
    <footer class="site-footer pt-5 pb-3" style="background-color: var(--dark-bg-color, #212529);">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <div class="footer-brand mb-4">
                        <h2 class="text-white"><span class="text-primary fw-black">BA</span><span style="color: var(--accent-color, #ff2d55);">BEE</span></h2>
                    </div>
                    <p class="text-white-50 mb-4">Thời trang cho bé. Nổi bật, phá cách và luôn dẫn đầu xu hướng.</p>
                    <div class="footer-social-links">
                        <a href="https://web.facebook.com/babeemoon.studio" class="me-2 text-decoration-none"><i class="fab fa-facebook-f footer-social-icon"></i></a>
                        <a href="https://www.tiktok.com/@babee_studio" class="me-2 text-decoration-none"><i class="fab fa-tiktok footer-social-icon"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                    <h5 class="text-white mb-4 footer-heading">SHOP</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="index.php?controller=product&action=list&category_id=1" class="footer-link">Áo</a></li>
                        <li class="mb-2"><a href="index.php?controller=product&action=list&category_id=2" class="footer-link">Quần</a></li>
                        <li class="mb-2"><a href="index.php?controller=product&action=list&category_id=3" class="footer-link">Áo khoác</a></li>
                        <li class="mb-2"><a href="index.php?controller=product&action=list&category_id=4" class="footer-link">Phụ kiện</a></li>
                        <li class="mb-2"><a href="index.php?controller=product&action=list&category_id=5" class="footer-link">Giày dép</a></li>
                        <li class="mb-2"><a href="index.php?controller=product&action=list&is_sale=1" class="footer-link">Khuyến mãi</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                    <h5 class="text-white mb-4 footer-heading">COMPANY</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="index.php?controller=home&action=about" class="footer-link">About Us</a></li>
                        <li class="mb-2"><a href="index.php?controller=home&action=contact" class="footer-link">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white mb-4 footer-heading">LIÊN HỆ VỚI CHÚNG TÔI</h5>
                    <ul class="list-unstyled footer-contact-info">
                        <?php
                        $settings = new Settings($conn);
                        $contact_address = $settings->getValue('contact_address', '');
                        $contact_phone = $settings->getValue('contact_phone', '');
                        $contact_email = $settings->getValue('contact_email', ADMIN_EMAIL);
                        ?>
                        <?php if(!empty($contact_address)): ?>
                        <li class="mb-3"><i class="fas fa-map-marker-alt me-2 text-primary"></i> <span class="text-white-50"><?php echo $contact_address; ?></span></li>
                        <?php endif; ?>
                        <?php if(!empty($contact_phone)): ?>
                        <li class="mb-3"><i class="fas fa-phone me-2 text-primary"></i> <span class="text-white-50"><?php echo $contact_phone; ?></span></li>
                        <?php endif; ?>
                        <li class="mb-3"><i class="fas fa-envelope me-2 text-primary"></i> <span class="text-white-50"><?php echo $contact_email; ?></span></li>
                        <li class="mb-3"><i class="fas fa-clock me-2 text-primary"></i> <span class="text-white-50">Thứ 2 - Chủ nhật: 10:00 - 22:00</span></li>
                    </ul>
                </div>
            </div>
            
            <hr class="mt-4 mb-3 border-secondary">
            
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0 text-white-50">© <?php echo date('Y'); ?> BABEE - Thời trang cho bé.</p>
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

    <style>
        /* Scoped styles for footer */
        .site-footer {
            padding-top: 3rem;
            padding-bottom: 1.5rem;
            background-color: var(--dark-bg-color, #212529);
        }

        .site-footer .footer-brand h2 {
            font-size: 2rem;
            margin-bottom: 0;
            line-height: 1.2;
            font-family: 'Quicksand', sans-serif;
            font-weight: 700;
        }

        .site-footer .footer-heading {
            font-size: 1.25rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #ffffff;
            font-family: 'Quicksand', sans-serif;
        }

        .site-footer .footer-links {
            padding-left: 0;
        }

        .site-footer .footer-links .footer-link {
            color: #adb5bd;
            text-decoration: none;
            transition: opacity 0.2s ease;
            font-size: 0.95rem;
            font-family: 'Quicksand', sans-serif;
            font-weight: 500;
        }

        .site-footer .footer-links .footer-link:hover {
            opacity: 0.8;
            color: #ffffff;
        }

        .site-footer .footer-social-links .footer-social-icon {
            color: #adb5bd;
            font-size: 1.5rem;
            transition: color 0.2s ease;
        }

        .site-footer .footer-social-links .footer-social-icon:hover {
            color: #ffffff;
        }

        .site-footer .footer-contact-info {
            padding-left: 0;
        }

        .site-footer .footer-contact-info li {
            color: #adb5bd;
            font-size: 0.95rem;
            font-family: 'Quicksand', sans-serif;
            font-weight: 500;
        }

        .site-footer .footer-contact-info i {
            color: var(--primary-color, #0d6efd);
            width: 1.5rem;
            text-align: center;
        }

        .site-footer hr {
            border-color: #495057;
            opacity: 0.5;
        }

        .site-footer .text-white-50 {
            color: #adb5bd !important;
            font-family: 'Quicksand', sans-serif;
            font-weight: 500;
        }

        /* Responsive adjustments */
        @media (max-width: 575.98px) {
            .site-footer {
                padding-top: 2rem;
                padding-bottom: 1rem;
            }

            .site-footer .footer-brand h2 {
                font-size: 1.5rem;
                text-align: center;
            }

            .site-footer .footer-brand p {
                text-align: center;
                font-size: 0.9rem;
            }

            .site-footer .footer-social-links {
                text-align: center;
            }

            .site-footer .footer-heading {
                font-size: 1.1rem;
                text-align: center;
            }

            .site-footer .footer-links,
            .site-footer .footer-contact-info {
                text-align: center;
            }

            .site-footer .footer-links .footer-link,
            .site-footer .footer-contact-info li {
                font-size: 0.9rem;
            }

            .site-footer .footer-social-links .footer-social-icon {
                font-size: 1.25rem;
            }

            .site-footer .row > div {
                margin-bottom: 1.5rem;
            }
        }

        @media (min-width: 576px) and (max-width: 767.98px) {
            .site-footer .footer-brand h2 {
                font-size: 1.75rem;
            }

            .site-footer .footer-heading {
                font-size: 1.15rem;
            }

            .site-footer .footer-links .footer-link,
            .site-footer .footer-contact-info li {
                font-size: 0.9rem;
            }

            .site-footer .footer-social-links .footer-social-icon {
                font-size: 1.35rem;
            }
        }

        @media (min-width: 768px) {
            .site-footer .footer-brand h2 {
                font-size: 2rem;
            }

            .site-footer .footer-heading {
                font-size: 1.25rem;
            }

            .site-footer .footer-links .footer-link,
            .site-footer .footer-contact-info li {
                font-size: 0.95rem;
            }

            .site-footer .footer-social-links .footer-social-icon {
                font-size: 1.5rem;
            }
        }
    </style>
</body>
</html>