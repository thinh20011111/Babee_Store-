<?php 
$page_title = "Login";
include 'views/layouts/header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Login to Your Account</h4>
            </div>
            <div class="card-body p-4">
                <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form action="index.php?controller=user&action=login" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                        <label class="form-check-label" for="remember_me">Remember me</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <p>Don't have an account? <a href="index.php?controller=user&action=register">Register here</a></p>
                    <p><a href="#">Forgot your password?</a></p>
                </div>
                
                <hr>
                
                <div class="social-login text-center">
                    <p class="mb-3">Or login with:</p>
                    <div class="d-flex justify-content-center">
                        <a href="#" class="btn btn-outline-primary me-2"><i class="fab fa-facebook-f"></i> Facebook</a>
                        <a href="#" class="btn btn-outline-danger"><i class="fab fa-google"></i> Google</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
