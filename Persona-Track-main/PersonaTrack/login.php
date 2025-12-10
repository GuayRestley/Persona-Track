<?php
session_start();

require_once 'includes/db_conn.php';

/**
 * Sanitize input helper
 */
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Tiny inline logger (keeps your "no external logger file" preference)
 * Note: this requires session account_id to be set before calling.
 */
function log_action($type, $desc = '', $remarks = '') {
    global $conn;
    if (!isset($_SESSION['account_id'])) return;
    $id = (int)$_SESSION['account_id'];
    $type = $conn->real_escape_string($type);
    $desc = $conn->real_escape_string($desc);
    $remarks = $conn->real_escape_string($remarks);
    $conn->query("INSERT INTO activity_logs (account_id, action_type, action_description, remarks, created_at)
                  VALUES ($id, '$type', '$desc', '$remarks', NOW())");
}

$error = '';

/* If already logged in → redirect based on role */
if (isset($_SESSION['role'])) {
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
            header("Location: dashboard.php");
    }
    exit();
}

/* Handle login submit */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($username === '' || $password === '') {
        $error = "Please enter username and password.";
    } else {
        // Use prepared statement to avoid SQL injection
        if ($stmt = $conn->prepare("SELECT account_id, username, password_hash, role FROM accounts WHERE username = ? AND status = 'Active' LIMIT 1")) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res && $res->num_rows === 1) {
                $user = $res->fetch_assoc();

                // Verify password
                if (isset($user['password_hash']) && password_verify($password, $user['password_hash'])) {

                    // Set session variables first
                    $_SESSION['account_id'] = (int)$user['account_id'];
                    $_SESSION['username']   = $user['username'];
                    $_SESSION['role']       = $user['role'];

                    // Update last_login safely
                    if ($upd = $conn->prepare("UPDATE accounts SET last_login = NOW() WHERE account_id = ?")) {
                        $upd->bind_param('i', $_SESSION['account_id']);
                        $upd->execute();
                        $upd->close();
                    }

                    // Log login action (session account_id now exists)
                    log_action('Login', 'User logged in', 'Success');

                    // Redirect immediately based on role
                    switch ($user['role']) {
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
                            header("Location: homepage.php");
                    }
                    exit();
                } else {
                    $error = "Invalid username or password";
                }
            } else {
                $error = "Invalid username or password";
            }

            $stmt->close();
        } else {
            // Prepared statement failed — fallback with generic error
            $error = "System error (DB). Please try again later.";
        }
    }
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
                                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
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
