<?php 
$page_title = "Register";
include 'views/layouts/header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Create an Account</h4>
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
                    <p class="mt-2 mb-0">
                        <a href="index.php?controller=user&action=login" class="alert-link">Click here to login</a>
                    </p>
                </div>
                <?php else: ?>
                
                <form action="index.php?controller=user&action=register" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Password must be at least 6 characters long.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address">
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="agree_terms" name="agree_terms" required>
                        <label class="form-check-label" for="agree_terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a> <span class="text-danger">*</span></label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <p>Already have an account? <a href="index.php?controller=user&action=login">Login here</a></p>
                </div>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password validation
    const form = document.querySelector('form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (form) {
        form.addEventListener('submit', function(event) {
            if (password.value.length < 6) {
                alert('Password must be at least 6 characters long.');
                event.preventDefault();
                return;
            }
            
            if (password.value !== confirmPassword.value) {
                alert('Passwords do not match.');
                event.preventDefault();
            }
        });
    }
});
</script>

<?php include 'views/layouts/footer.php'; ?>
