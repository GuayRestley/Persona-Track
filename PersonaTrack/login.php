<?php
session_start();

// FIX: Add sanitizeInput function
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

require_once 'includes/db_conn.php';

$error = '';

// If already logged in → redirect based on role
if (isset($_SESSION['role'])) {   // FIX: your column name is `role`
    $role = $_SESSION['role'];

    switch ($role) {
        case 'admin':
            header("Location: dashboards/admin_dashboard.php");
            break;
        case 'CAPTAIN':
            header("Location: dashboards/captain_dashboard.php");
            break;
        case 'SECRETARY':
            header("Location: dashboards/secretary_dashboard.php");
            break;
        case 'KAGAWAD':
            header("Location: dashboards/kagawad_dashboard.php");
            break;
        default:
            session_destroy();
            header("Location: login.php");
    }
    exit();
}

// Handle login submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    // FIXED QUERY – your accounts table DOES NOT use role_id or roles table
    $query = "
        SELECT *
        FROM accounts
        WHERE username = '$username'
        AND status = 'Active'
    ";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password_hash'])) {

            // Set session variables
            $_SESSION['account_id'] = $user['account_id'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['role']       = $user['role']; // FIX: your column is `role`

            // Update last login
            $update = "UPDATE accounts SET last_login = NOW() 
                       WHERE account_id = '{$user['account_id']}'";
            mysqli_query($conn, $update);

            // Log login activity (only if function exists)
            if (function_exists("logActivity")) {
                logActivity($user['account_id'], 'Login', null, 'User logged in');
            }

            // Redirect to dashboard based on role
            header("Location: index.php");
            exit();
        }
    }

    $error = "Invalid username or password";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PersonaTrack</title>
    <link rel="stylesheet" href="CSS/login.css">

</head>
<body>
    <div class="login-container">
        <div class="login-wrapper">
            <div class="login-left">
                <div class="login-logo">🏘️</div>
                <h1>PersonaTrack</h1>
                <p>Barangay Profiling System</p>
                <p style="margin-top: 2rem; font-size: 1rem;">Secure, Efficient, and Transparent Resident Data Management</p>
            </div>

            <div class="login-right">
                <div class="login-header">
                    <h2>Welcome Back!</h2>
                    <p>Please login to access your account</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <span style="font-size: 1.2rem;">⚠️</span>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <span class="input-icon">👤</span>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="username" 
                                name="username" 
                                placeholder="Enter your username"
                                required 
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔒</span>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                placeholder="Enter your password"
                                required
                            >
                            <span class="password-toggle" onclick="togglePassword()">👁️</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        🔐 Login to Dashboard
                    </button>
                </form>

                <div class="login-footer">
                    <p><strong>Default Login:</strong></p>
                    <p>Username: <strong>admin</strong> | Password: <strong>admin123</strong></p>
                    <a href="homepage.php" class="back-home">← Back to Homepage</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleIcon.textContent = '👁️';
            }
        }
    </script>
</body>
</html>
