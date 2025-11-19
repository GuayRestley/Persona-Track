<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/logger.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $logger = new Logger();
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->getConnection()->prepare("SELECT account_id, password_hash, role_name FROM account a JOIN roles r ON a.role_id = r.role_id WHERE username = ? AND status = 'Active'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['account_id'] = $user['account_id'];
            $_SESSION['role_name'] = $user['role_name'];
            $db->getConnection()->query("UPDATE account SET last_login = NOW() WHERE account_id = " . $user['account_id']);
            $logger->logAction($user['account_id'], 'Login');
            header('Location: dashboard.php');
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Barangay Profiling</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <h2 class="text-center">Login</h2>
                <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>