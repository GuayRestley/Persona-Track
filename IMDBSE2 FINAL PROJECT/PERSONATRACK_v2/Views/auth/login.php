<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

// Handle login form submission
if (is_post()) {
    if (!verify_csrf_token(post_param('csrf_token'))) {
        set_flash_message('error', 'Invalid request. Please try again.');
    } else {
        $authController = new AuthController();
        $result = $authController->login(
            post_param('username'),
            post_param('password'),
            isset($_POST['remember'])
        );
        
        if ($result['success']) {
            set_flash_message('success', $result['message']);
            redirect('dashboard.php');
        } else {
            set_flash_message('error', $result['message']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 30px;
            text-align: center;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="bi bi-shield-lock"></i> <?php echo APP_NAME; ?></h2>
                <p class="mb-0 mt-2">Please login to continue</p>
            </div>
            <div class="card-body p-4">
                <?php echo display_flash_message(); ?>
                
                <form method="POST" action="">
                    <?php echo csrf_field(); ?>
                    
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-person"></i> Username</label>
                        <input type="text" name="username" class="form-control form-control-lg" 
                               placeholder="Enter your username" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-lock"></i> Password</label>
                        <input type="password" name="password" class="form-control form-control-lg" 
                               placeholder="Enter your password" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login w-100 btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <p class="text-muted mb-0">Don't have an account? 
                        <a href="register.php" class="text-decoration-none">Register here</a>
                    </p>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        <strong>Demo Credentials:</strong><br>
                        Username: <code>admin</code> | Password: <code>admin123</code>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
