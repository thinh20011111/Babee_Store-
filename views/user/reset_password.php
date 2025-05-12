<?php 
$page_title = "Reset Password";
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
                    <p><a href="index.php?controller=user&action=login">Click here to login</a></p>
                </div>
                <?php else: ?>
                <form action="index.php?controller=user&action=reset_password&token=<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>" method="POST">
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </div>
                </form>
                <?php endif; ?>
                
                <div class="mt-4 text-center">
                    <p>Remember your password? <a href="index.php?controller=user&action=login">Login here</a></p>
                    <p>Don't have an account? <a href="index.php?controller=user&action=register">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>