<?php 
$page_title = "Page Not Found";
include 'views/layouts/header.php'; 
?>

<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="error-template">
                <h1 class="display-1 text-muted">404</h1>
                <h2 class="mb-4">Oops! Page Not Found</h2>
                <div class="error-details mb-4">
                    <p>Sorry, the page you requested could not be found.</p>
                    <p>It might have been removed, renamed, or is temporarily unavailable.</p>
                </div>
                <div class="error-actions">
                    <a href="index.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-home me-2"></i>Go Home
                    </a>
                    <a href="index.php?controller=home&action=contact" class="btn btn-outline-secondary btn-lg ms-2">
                        <i class="fas fa-envelope me-2"></i>Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>