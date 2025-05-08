
<?php
session_start();
require_once '../config/config.php';

// Check if already logged in
if(isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'admin') {
    header("Location: index.php");
    exit;
}

// Include database connection
require_once '../config/database.php';
require_once '../models/User.php';

$db = new Database();
$conn = $db->getConnection();

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    if(empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $user = new User($conn);
        $user->email = $email;
        
        if($user->emailExists()) {
            // Verify password using password_verify
            if($user->verifyPassword($password)) {
                if($user->role == 'admin' || $user->role == 'staff') {
                    $_SESSION['user_id'] = $user->id;
                    $_SESSION['username'] = $user->username; 
                    $_SESSION['user_role'] = $user->role;
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Access denied. Admin privileges required.";
                }
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Email not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Admin Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
