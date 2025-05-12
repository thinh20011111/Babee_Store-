<?php 
$page_title = "Forgot Password";
include 'views/layouts/header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Reset Your Password</h4>
            </div>
            <div class="card-body p-4">
                <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <form action="index.php?controller=user&action=forgot_password" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <small class="form-text text-muted">Enter the email address associated with your account, and we'll send you a link to reset your password.</small>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Send Reset Link</button>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <p>Remember your password? <a href="index.php?controller=user&action=login">Login here</a></p>
                    <p>Don't have an account? <a href="index.php?controller=user&action=register">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>